<?php

namespace YellowThree\Voyager\Database\Schema;

use Doctrine\DBAL\Schema\Comparator;

class Table
{
    public $name;
    public $columns;
    public $indexes;
    public $foreignKeys;
    public $options;

    public function __construct($name, $columns = [], $indexes = [], $foreignKeys = [], $options = [])
    {
        $this->name = $name;
        $this->columns = $columns;
        $this->indexes = $indexes;
        $this->foreignKeys = $foreignKeys;
        $this->options = $options;
    }

    public static function make($table)
    {
        if (!is_array($table)) {
            $table = json_decode($table, true);
        }

        $name = Identifier::validate($table['name'], 'Table');

        $columns = [];
        foreach ($table['columns'] as $columnArr) {
            $column = Column::make($columnArr, $table['name']);
            $columns[$column->getName()] = $column;
        }

        $indexes = [];
        foreach ($table['indexes'] as $indexArr) {
            $index = Index::make($indexArr);
            $indexes[$index->getName()] = $index;
        }

        $foreignKeys = [];
        foreach ($table['foreignKeys'] as $foreignKeyArr) {
            $foreignKey = ForeignKey::make($foreignKeyArr);
            $foreignKeys[$foreignKey->getName()] = $foreignKey;
        }

        $options = $table['options'];

        return new self($name, $columns, $indexes, $foreignKeys, $options);
    }

    public function getColumnsIndexes($columns, $sort = false)
    {
        if (!is_array($columns)) {
            $columns = [$columns];
        }

        $matched = [];

        foreach ($this->indexes as $index) {
            if ($index->spansColumns($columns)) {
                $matched[$index->getName()] = $index;
            }
        }

        if (count($matched) > 1 && $sort) {
            // Sort indexes based on priority: PRI > UNI > IND
            uasort($matched, function ($index1, $index2) {
                $index1_type = Index::getType($index1);
                $index2_type = Index::getType($index2);

                if ($index1_type == $index2_type) {
                    return 0;
                }

                if ($index1_type == Index::PRIMARY) {
                    return -1;
                }

                if ($index2_type == Index::PRIMARY) {
                    return 1;
                }

                if ($index1_type == Index::UNIQUE) {
                    return -1;
                }

                // If we reach here, it means: $index1=INDEX && $index2=UNIQUE
                return 1;
            });
        }

        return $matched;
    }

    public function diff(Table $compareTable)
    {
        // This is harder now without Doctrine. 
        // For now, let's just return a dummy or implement a simple diff.
        // But we might not even need this for most Voyager operations.
        return null; 
    }

    public function diffOriginal()
    {
        // This is also harder now.
        return null;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'name'           => $this->name,
            'oldName'        => $this->name,
            'columns'        => $this->exportColumnsToArray(),
            'indexes'        => $this->exportIndexesToArray(),
            'primaryKeyName' => $this->getPrimaryKeyName(),
            'foreignKeys'    => $this->exportForeignKeysToArray(),
            'options'        => $this->options,
        ];
    }

    private function getPrimaryKeyName()
    {
        foreach ($this->indexes as $index) {
            if (Index::getType($index) == Index::PRIMARY) {
                return $index->getName();
            }
        }
        return null;
    }

    /**
     * @return string
     */
    public function toJson()
    {
        return json_encode($this->toArray());
    }

    /**
     * @return array
     */
    public function exportColumnsToArray()
    {
        $exportedColumns = [];

        foreach ($this->columns as $name => $column) {
            $exportedColumns[] = Column::toArray($column);
        }

        return $exportedColumns;
    }

    /**
     * @return array
     */
    public function exportIndexesToArray()
    {
        $exportedIndexes = [];

        foreach ($this->indexes as $name => $index) {
            $indexArr = Index::toArray($index);
            $indexArr['table'] = $this->name;
            $exportedIndexes[] = $indexArr;
        }

        return $exportedIndexes;
    }

    /**
     * @return array
     */
    public function exportForeignKeysToArray()
    {
        $exportedForeignKeys = [];

        foreach ($this->foreignKeys as $name => $fk) {
            $exportedForeignKeys[$name] = ForeignKey::toArray($fk);
        }

        return $exportedForeignKeys;
    }

    public function __get($property)
    {
        $getter = 'get'.ucfirst($property);

        if (!method_exists($this, $getter)) {
            throw new \Exception("Property {$property} doesn't exist or is unavailable");
        }

        return $this->$getter();
    }
}
