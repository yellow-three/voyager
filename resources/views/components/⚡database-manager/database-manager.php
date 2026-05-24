<?php

use Livewire\Component;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use YellowThree\Voyager\Facades\Voyager;

new class extends Component {
    public array $tables = [];
    public ?string $selectedTable = null;
    public array $tableDetails = [];
    public bool $showNewTableForm = false;
    public string $newTableName = '';
    public array $newColumns = [];
    public array $availableTypes = [];

    public function mount(): void
    {
        $this->loadTables();
        $this->availableTypes = [
            'bigIncrements', 'bigInteger', 'binary', 'boolean', 'char',
            'date', 'dateTime', 'dateTimeTz', 'decimal', 'double',
            'enum', 'float', 'foreignId', 'foreignUlid', 'foreignUuid',
            'geometry', 'geometryCollection', 'increments', 'integer',
            'ipAddress', 'json', 'jsonb', 'lineString', 'longText',
            'macAddress', 'mediumIncrements', 'mediumInteger', 'mediumText',
            'morphs', 'multiLineString', 'multiPoint', 'multiPolygon',
            'nullableMorphs', 'nullableTimestamps', 'nullableUlids',
            'nullableUuidMorphs', 'point', 'polygon', 'rememberToken',
            'set', 'smallIncrements', 'smallInteger', 'softDeletes',
            'softDeletesTz', 'string', 'text', 'time', 'timeTz',
            'timestamp', 'timestampTz', 'timestamps', 'timestampTz',
            'tinyIncrements', 'tinyInteger', 'tinyText', 'unsignedBigInteger',
            'unsignedInteger', 'unsignedMediumInteger', 'unsignedSmallInteger',
            'unsignedTinyInteger', 'ulidMorphs', 'uuid', 'uuidMorphs',
            'year',
        ];
    }

    public function loadTables(): void
    {
        $this->tables = [];
        try {
            $tableNames = Schema::getTables();
            $breadTables = Voyager::model('DataType')::pluck('name')->toArray();

            foreach ($tableNames as $tableInfo) {
                $name = is_array($tableInfo) ? ($tableInfo['name'] ?? null) : ($tableInfo->name ?? $tableInfo);
                if (empty($name)) continue;

                $this->tables[] = [
                    'name' => $name,
                    'has_bread' => in_array($name, $breadTables),
                    'bread_slug' => in_array($name, $breadTables) ? Voyager::model('DataType')::where('name', $name)->value('slug') : null,
                ];
            }
        } catch (\Exception $e) {
            //
        }
    }

    public function inspectTable(string $name): void
    {
        $this->selectedTable = $name;
        $this->tableDetails = ['columns' => [], 'indexes' => []];

        try {
            $columns = Schema::getColumns($name);
            foreach ($columns as $column) {
                $this->tableDetails['columns'][] = [
                    'name' => $column['name'],
                    'type' => $column['type_name'] ?? $column['type'],
                    'nullable' => $column['nullable'] ? 'YES' : 'NO',
                    'default' => $column['default'],
                ];
            }

            $indexes = Schema::getIndexes($name);
            foreach ($indexes as $index) {
                $this->tableDetails['indexes'][] = [
                    'name' => $index['name'],
                    'columns' => implode(', ', $index['columns']),
                    'unique' => $index['unique'] ? 'YES' : 'NO',
                    'primary' => $index['primary'] ? 'YES' : 'NO',
                ];
            }
        } catch (\Exception $e) {
            //
        }
    }

    public function deleteTable(string $name): void
    {
        try {
            Schema::dropIfExists($name);
            $this->selectedTable = null;
            $this->loadTables();
            session()->flash('message', "Table '{$name}' successfully deleted.");
        } catch (\Exception $e) {
            session()->flash('error', "Failed to delete table: " . $e->getMessage());
        }
    }

    public function showNewTable(): void
    {
        $this->showNewTableForm = true;
        $this->newTableName = '';
        $this->newColumns = [
            ['name' => 'id', 'type' => 'bigIncrements', 'nullable' => false, 'default' => null],
            ['name' => 'created_at', 'type' => 'timestamps', 'nullable' => true, 'default' => null],
        ];
    }

    public function cancelNewTable(): void
    {
        $this->showNewTableForm = false;
        $this->newTableName = '';
        $this->newColumns = [];
    }

    public function addColumn(): void
    {
        $this->newColumns[] = ['name' => '', 'type' => 'string', 'nullable' => false, 'default' => null];
    }

    public function removeColumn(int $index): void
    {
        unset($this->newColumns[$index]);
        $this->newColumns = array_values($this->newColumns);
    }

    public function createTable(): void
    {
        $this->validate([
            'newTableName' => 'required|alpha_dash|min:2|max:64',
            'newColumns.*.name' => 'required|alpha_dash',
            'newColumns.*.type' => 'required|string',
        ]);

        try {
            Schema::create($this->newTableName, function ($table) {
                $hasId = false;
                $hasTimestamps = false;

                foreach ($this->newColumns as $col) {
                    $colName = $col['name'];
                    $colType = $col['type'];

                    if ($colType === 'bigIncrements') {
                        $table->bigIncrements($colName);
                        $hasId = true;
                    } elseif ($colType === 'timestamps') {
                        $table->timestamps();
                        $hasTimestamps = true;
                    } elseif ($colType === 'softDeletes') {
                        $table->softDeletes();
                    } elseif ($colType === 'rememberToken') {
                        $table->rememberToken();
                    } elseif (in_array($colType, ['string', 'text', 'integer', 'bigInteger', 'smallInteger', 'tinyInteger', 'float', 'double', 'decimal', 'boolean', 'date', 'dateTime', 'time', 'json', 'jsonb', 'longText', 'mediumText', 'char'])) {
                        $method = $colType === 'decimal' ? $table->{$colType}($colName, 10, 2) : $table->{$colType}($colName);
                        if ($col['nullable']) {
                            $method->nullable();
                        }
                        if ($col['default'] !== null && $col['default'] !== '') {
                            $method->default($col['default']);
                        }
                    }
                }

                if (!$hasId) {
                    $table->bigIncrements('id');
                }
            });

            $this->cancelNewTable();
            $this->loadTables();
            session()->flash('message', "Table '{$this->newTableName}' created successfully.");
        } catch (\Exception $e) {
            session()->flash('error', "Failed to create table: " . $e->getMessage());
        }
    }

    public function render(): mixed
    {
        return view('voyager::components.⚡database-manager.database-manager');
    }
};
