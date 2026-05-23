<?php

namespace YellowThree\Voyager\Database\Schema;

class Index
{
    public const PRIMARY = 'PRIMARY';
    public const UNIQUE = 'UNIQUE';
    public const INDEX = 'INDEX';

    public $name;
    public $columns;
    public $type;
    public $isPrimary;
    public $isUnique;
    public $isComposite;
    public $flags;
    public $options;

    public function __construct($name, $columns, $type, $isPrimary, $isUnique, $isComposite, $flags = [], $options = [])
    {
        $this->name = $name;
        $this->columns = $columns;
        $this->type = $type;
        $this->isPrimary = $isPrimary;
        $this->isUnique = $isUnique;
        $this->isComposite = $isComposite;
        $this->flags = $flags;
        $this->options = $options;
    }

    public static function make(array $index)
    {
        $columns = $index['columns'];
        if (!is_array($columns)) {
            $columns = [$columns];
        }

        if (isset($index['type'])) {
            $type = $index['type'];

            $isPrimary = ($type == static::PRIMARY);
            $isUnique = $isPrimary || ($type == static::UNIQUE);
        } else {
            $isPrimary = $index['isPrimary'];
            $isUnique = $index['isUnique'];

            // Set the type
            if ($isPrimary) {
                $type = static::PRIMARY;
            } elseif ($isUnique) {
                $type = static::UNIQUE;
            } else {
                $type = static::INDEX;
            }
        }

        // Set the name
        $name = trim($index['name'] ?? '');
        if (empty($name)) {
            $table = $index['table'] ?? null;
            $name = static::createName($columns, $type, $table);
        } else {
            $name = Identifier::validate($name, 'Index');
        }

        $flags = $index['flags'] ?? [];
        $options = $index['options'] ?? [];
        $isComposite = count($columns) > 1;

        return new self($name, $columns, $type, $isPrimary, $isUnique, $isComposite, $flags, $options);
    }

    /**
     * @return array
     */
    public static function toArray(Index $index)
    {
        return [
            'name'        => $index->name,
            'oldName'     => $index->name,
            'columns'     => $index->columns,
            'type'        => $index->type,
            'isPrimary'   => $index->isPrimary,
            'isUnique'    => $index->isUnique,
            'isComposite' => $index->isComposite,
            'flags'       => $index->flags,
            'options'     => $index->options,
        ];
    }

    public static function getType(Index $index)
    {
        return $index->type;
    }

    /**
     * Create a default index name.
     *
     * @param array  $columns
     * @param string $type
     * @param string $table
     *
     * @return string
     */
    public static function createName(array $columns, $type, $table = null)
    {
        $table = isset($table) ? trim($table).'_' : '';
        $type = trim($type);
        $name = strtolower($table.implode('_', $columns).'_'.$type);

        return str_replace(['-', '.'], '_', $name);
    }

    public static function availableTypes()
    {
        return [
            static::PRIMARY,
            static::UNIQUE,
            static::INDEX,
        ];
    }

    public function getName()
    {
        return $this->name;
    }

    public function getColumns()
    {
        return $this->columns;
    }

    public function isPrimary()
    {
        return $this->isPrimary;
    }

    public function isUnique()
    {
        return $this->isUnique;
    }

    public function getFlags()
    {
        return $this->flags;
    }

    public function getOptions()
    {
        return $this->options;
    }
}
