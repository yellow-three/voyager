<?php

namespace YellowThree\Voyager\Database\Schema;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use YellowThree\Voyager\Database\Types\Type;

abstract class SchemaManager
{
    public static function __callStatic($method, $args)
    {
        return static::manager()->$method(...$args);
    }

    public static function manager()
    {
        return Schema::getConnection()->getSchemaBuilder();
    }

    public static function getDatabaseConnection()
    {
        return DB::connection();
    }

    public static function getDatabasePlatform()
    {
        // This is a dummy method for backward compatibility.
        // It returns an object that responds to getName().
        return new class {
            public function getName() {
                return Schema::getConnection()->getDriverName();
            }
        };
    }

    public static function tableExists($table)
    {
        if (!is_array($table)) {
            $table = [$table];
        }

        foreach ($table as $tableName) {
            if (Schema::hasTable($tableName)) {
                return true;
            }
        }

        return false;
    }

    public static function listTables()
    {
        $tables = [];

        foreach (Schema::getTables() as $table) {
            $tables[] = $table['name'];
        }

        return $tables;
    }

    public static function listTableNames()
    {
        return static::listTables();
    }

    /**
     * @param string $tableName
     *
     * @return \YellowThree\Voyager\Database\Schema\Table
     */
    public static function listTableDetails($tableName)
    {
        $columns = [];
        $schemaBuilder = Schema::getConnection()->getSchemaBuilder();
        
        $laravelColumns = $schemaBuilder->getColumns($tableName);
        foreach ($laravelColumns as $column) {
            $columns[] = Column::make([
                'name' => $column['name'],
                'type' => $column['type_name'] ?? $column['type'],
                'null' => $column['nullable'],
                'extra' => ($column['auto_increment'] ?? false) ? 'auto_increment' : '',
            ], $tableName);
        }

        $indexes = [];
        $laravelIndexes = $schemaBuilder->getIndexes($tableName);
        foreach ($laravelIndexes as $index) {
            $indexes[] = Index::make([
                'name' => $index['name'],
                'columns' => $index['columns'],
                'type' => self::mapIndexType($index['type']),
                'isPrimary' => $index['primary'],
                'isUnique' => $index['unique'],
                'flags' => [],
                'options' => [],
            ]);
        }

        $foreignKeys = [];
        $laravelForeignKeys = $schemaBuilder->getForeignKeys($tableName);
        foreach ($laravelForeignKeys as $fk) {
            $foreignKeys[] = ForeignKey::make([
                'name' => $fk['name'],
                'localTable' => $tableName,
                'localColumns' => $fk['columns'],
                'foreignTable' => $fk['foreign_table'],
                'foreignColumns' => $fk['foreign_columns'],
                'options' => [
                    'onUpdate' => $fk['on_update'] ?? null,
                    'onDelete' => $fk['on_delete'] ?? null,
                ],
            ]);
        }

        return new Table($tableName, $columns, $indexes, $foreignKeys, []);
    }

    private static function mapIndexType($type)
    {
        $type = strtolower($type);
        if ($type === 'primary') {
            return Index::PRIMARY;
        } elseif ($type === 'unique') {
            return Index::UNIQUE;
        }
        return Index::INDEX;
    }

    /**
     * @param string $tableName
     *
     * @return \Illuminate\Support\Collection
     */
    public static function describeTable($tableName)
    {
        Type::registerCustomPlatformTypes();

        $table = static::listTableDetails($tableName);
        if (!$table) return collect([]);

        return collect($table->columns)->map(function ($column) use ($table) {
            $columnArr = Column::toArray($column);

            $columnArr['field'] = $columnArr['name'];
            $columnArr['type'] = $columnArr['type']['name'];

            // Set the indexes and key
            $columnArr['indexes'] = [];
            $columnArr['key'] = null;
            if ($columnArr['indexes'] = $table->getColumnsIndexes($columnArr['name'], true)) {
                // Convert indexes to Array
                foreach ($columnArr['indexes'] as $name => $index) {
                    $columnArr['indexes'][$name] = Index::toArray($index);
                }

                // If there are multiple indexes for the column
                // the Key will be one with highest priority
                $indexType = array_values($columnArr['indexes'])[0]['type'];
                $columnArr['key'] = substr($indexType, 0, 3);
            }

            return $columnArr;
        });
    }

    public static function listTableColumnNames($tableName)
    {
        Type::registerCustomPlatformTypes();

        $columnNames = [];

        foreach (Schema::getConnection()->getSchemaBuilder()->getColumns($tableName) as $column) {
            $columnNames[] = $column['name'];
        }

        return $columnNames;
    }

    public static function createTable($table)
    {
        // This is still hard to implement without Doctrine or a very complete Laravel implementation.
        // For now, let's leave it empty or throw an exception.
    }

    public static function getDoctrineTable($table)
    {
        // This is for backward compatibility.
        return null;
    }

    public static function getDoctrineColumn($table, $column)
    {
        return null;
    }

    public static function dropTable($tableName)
    {
        Schema::drop($tableName);
    }

    public static function alterTable($diff)
    {
        // Alter table implementation if needed.
    }
}
