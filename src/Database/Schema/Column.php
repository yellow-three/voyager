<?php

namespace YellowThree\Voyager\Database\Schema;

use YellowThree\Voyager\Database\Types\Type;

class Column
{
    public $name;
    public $type;
    public $options;
    public $null;
    public $extra;
    public $composite = false;
    public $oldName;

    public function __construct($name, $type, $options = [], $null = true, $extra = '', $composite = false, $oldName = null)
    {
        $this->name = $name;
        $this->type = $type;
        $this->options = $options;
        $this->null = $null;
        $this->extra = $extra;
        $this->composite = $composite;
        $this->oldName = $oldName;
    }

    public static function make(array $column, string $tableName = null)
    {
        $name = Identifier::validate($column['name'], 'Column');
        $type = $column['type'];
        
        // If type is a string, convert it to Voyager's Type object
        if (is_string($type)) {
            $type = Type::make($type, $tableName);
        }

        $options = array_diff_key($column, array_flip(['name', 'composite', 'oldName', 'null', 'extra', 'type', 'charset', 'collation']));

        return new self(
            $name,
            $type,
            $options,
            $column['null'] ?? true,
            $column['extra'] ?? '',
            $column['composite'] ?? false,
            $column['oldName'] ?? null
        );
    }

    /**
     * @return array
     */
    public static function toArray(Column $column)
    {
        return [
            'name'           => $column->name,
            'oldName'        => $column->oldName ?? $column->name,
            'type'           => Type::toArray($column->type),
            'null'           => $column->null ? 'YES' : 'NO',
            'extra'          => $column->extra,
            'composite'      => $column->composite,
            'charset'        => $column->options['charset'] ?? null,
            'collation'      => $column->options['collation'] ?? null,
        ];
    }

    public function getName()
    {
        return $this->name;
    }
}
