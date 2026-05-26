<?php

namespace YellowThree\Voyager\Database\Schema;

class ForeignKey
{
    public $name;
    public $localTable;
    public $localColumns;
    public $foreignTable;
    public $foreignColumns;
    public $options;

    public function __construct($name, $localTable, $localColumns, $foreignTable, $foreignColumns, $options = [])
    {
        $this->name = $name;
        $this->localTable = $localTable;
        $this->localColumns = $localColumns;
        $this->foreignTable = $foreignTable;
        $this->foreignColumns = $foreignColumns;
        $this->options = $options;
    }

    public static function make(array $foreignKey)
    {
        $localTable = $foreignKey['localTable'] ?? null;
        $localColumns = $foreignKey['localColumns'];
        $foreignTable = $foreignKey['foreignTable'];
        $foreignColumns = $foreignKey['foreignColumns'];
        $options = $foreignKey['options'] ?? [];

        // Set the name
        $name = isset($foreignKey['name']) ? trim($foreignKey['name']) : '';
        if (empty($name)) {
            $name = Index::createName($localColumns, 'foreign', $localTable);
        } else {
            $name = Identifier::validate($name, 'Foreign Key');
        }

        return new self($name, $localTable, $localColumns, $foreignTable, $foreignColumns, $options);
    }

    /**
     * @return array
     */
    public static function toArray(ForeignKey $fk)
    {
        return [
            'name'           => $fk->name,
            'localTable'     => $fk->localTable,
            'localColumns'   => $fk->localColumns,
            'foreignTable'   => $fk->foreignTable,
            'foreignColumns' => $fk->foreignColumns,
            'options'        => $fk->options,
        ];
    }
}
