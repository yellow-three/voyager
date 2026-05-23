@php
use Livewire\Component;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use YellowThree\Voyager\Facades\Voyager;

new class extends Component {
    public array $tables = [];
    public ?string $selectedTable = null;
    public array $tableDetails = [];

    public function mount(): void
    {
        $this->loadTables();
    }

    public function loadTables(): void
    {
        $this->tables = [];
        try {
            // Get tables using Laravel native Schema facade (compatible with Laravel 11/12/13)
            $tableNames = Schema::getTables();
            
            // Get BREAD registered tables
            $breadTables = Voyager::model('DataType')::pluck('name')->toArray();

            foreach ($tableNames as $tableInfo) {
                // Compatible with both array/object formats returned by getTables()
                $name = is_array($tableInfo) ? ($tableInfo['name'] ?? null) : ($tableInfo->name ?? $tableInfo);
                if (empty($name)) continue;

                $hasBread = in_array($name, $breadTables);
                $dataType = $hasBread ? Voyager::model('DataType')::where('name', $name)->first() : null;

                $this->tables[] = [
                    'name' => $name,
                    'has_bread' => $hasBread,
                    'bread_slug' => $dataType ? $dataType->slug : null,
                ];
            }
        } catch (\Exception $e) {
            // Fallback
        }
    }

    public function inspectTable(string $name): void
    {
        $this->selectedTable = $name;
        $this->tableDetails = [
            'columns' => [],
            'indexes' => [],
        ];

        try {
            // Fetch table columns
            $columns = Schema::getColumns($name);
            foreach ($columns as $column) {
                $this->tableDetails['columns'][] = [
                    'name' => $column['name'],
                    'type' => $column['type_name'] ?? $column['type'],
                    'nullable' => $column['nullable'] ? 'YES' : 'NO',
                    'default' => $column['default'],
                ];
            }

            // Fetch table indexes
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
            // Error handling
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
};
@endphp

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <!-- Tables List -->
    <div class="lg:col-span-1 bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700/50 p-6 shadow-sm space-y-6">
        <div>
            <h2 class="text-xl font-bold text-gray-900 dark:text-white tracking-tight flex items-center gap-2">
                <i class="voyager-data text-primary"></i>
                Database Tables
            </h2>
            <p class="text-xs text-gray-500 mt-1">Manage database tables and BREAD links</p>
        </div>

        @if (session()->has('message'))
            <div class="p-3 bg-emerald-50 text-emerald-700 border border-emerald-100 rounded-xl text-xs font-semibold">
                {{ session('message') }}
            </div>
        @endif

        @if (session()->has('error'))
            <div class="p-3 bg-red-50 text-red-700 border border-red-100 rounded-xl text-xs font-semibold">
                {{ session('error') }}
            </div>
        @endif

        <div class="divide-y divide-gray-50 dark:divide-gray-700/30 max-h-[600px] overflow-y-auto pr-2 space-y-2">
            @foreach($tables as $table)
                <div class="flex items-center justify-between p-3 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-all group">
                    <button wire:click="inspectTable('{{ $table['name'] }}')" class="flex-1 text-left">
                        <span class="text-sm font-semibold text-gray-800 dark:text-gray-200 group-hover:text-primary transition-colors">{{ $table['name'] }}</span>
                        <div class="flex items-center gap-2 mt-1">
                            @if($table['has_bread'])
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-emerald-50 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300">BREAD Active</span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-gray-100 text-gray-500 dark:bg-gray-700 dark:text-gray-400">No BREAD</span>
                            @endif
                        </div>
                    </button>

                    <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                        @if($table['has_bread'])
                            <a href="{{ route('voyager.bread.edit', $table['name']) }}" class="w-7 h-7 rounded bg-indigo-50 hover:bg-indigo-150 text-indigo-600 flex items-center justify-center transition-colors text-xs" title="Edit BREAD">
                                <i class="voyager-edit"></i>
                            </a>
                        @else
                            <a href="{{ route('voyager.bread.create', $table['name']) }}" class="w-7 h-7 rounded bg-emerald-50 hover:bg-emerald-150 text-emerald-600 flex items-center justify-center transition-colors text-xs" title="Add BREAD">
                                <i class="voyager-plus"></i>
                            </a>
                        @endif
                        <button type="button" wire:confirm="Are you sure you want to drop '{{ $table['name'] }}' table?" wire:click="deleteTable('{{ $table['name'] }}')" class="w-7 h-7 rounded bg-red-50 hover:bg-red-100 text-red-600 flex items-center justify-center transition-colors text-xs" title="Drop Table">
                            <i class="voyager-trash"></i>
                        </button>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Table Details / Inspector -->
    <div class="lg:col-span-2 space-y-6">
        @if($selectedTable)
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700/50 p-6 shadow-sm space-y-6">
                <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-700/30 pb-4">
                    <div>
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white tracking-tight">Table: {{ $selectedTable }}</h2>
                        <p class="text-xs text-gray-500 mt-1">Schema architecture inspection</p>
                    </div>
                </div>

                <!-- Columns -->
                <div class="space-y-3">
                    <h3 class="text-sm font-bold text-gray-700 dark:text-gray-300">Columns</h3>
                    <div class="overflow-x-auto border border-gray-100 dark:border-gray-700/50 rounded-xl">
                        <table class="w-full text-sm text-left">
                            <thead class="bg-gray-50 dark:bg-gray-700/30 text-gray-500 font-bold text-xs uppercase">
                                <tr>
                                    <th class="px-4 py-3">Column</th>
                                    <th class="px-4 py-3">Type</th>
                                    <th class="px-4 py-3">Nullable</th>
                                    <th class="px-4 py-3">Default</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700/30">
                                @foreach($tableDetails['columns'] as $column)
                                    <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-700/10">
                                        <td class="px-4 py-3 font-semibold text-gray-800 dark:text-gray-200">{{ $column['name'] }}</td>
                                        <td class="px-4 py-3 text-gray-600 dark:text-gray-400 font-mono text-xs">{{ $column['type'] }}</td>
                                        <td class="px-4 py-3 text-gray-500 text-xs">{{ $column['nullable'] }}</td>
                                        <td class="px-4 py-3 text-gray-500 text-xs font-mono">{{ $column['default'] ?? 'NULL' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Indexes -->
                @if(!empty($tableDetails['indexes']))
                    <div class="space-y-3">
                        <h3 class="text-sm font-bold text-gray-700 dark:text-gray-300">Indexes</h3>
                        <div class="overflow-x-auto border border-gray-100 dark:border-gray-700/50 rounded-xl">
                            <table class="w-full text-sm text-left">
                                <thead class="bg-gray-50 dark:bg-gray-700/30 text-gray-500 font-bold text-xs uppercase">
                                    <tr>
                                        <th class="px-4 py-3">Index Name</th>
                                        <th class="px-4 py-3">Columns</th>
                                        <th class="px-4 py-3">Unique</th>
                                        <th class="px-4 py-3">Primary</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-gray-700/30">
                                    @foreach($tableDetails['indexes'] as $index)
                                        <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-700/10">
                                            <td class="px-4 py-3 font-semibold text-gray-800 dark:text-gray-200">{{ $index['name'] }}</td>
                                            <td class="px-4 py-3 text-gray-600 dark:text-gray-400 font-mono text-xs">{{ $index['columns'] }}</td>
                                            <td class="px-4 py-3 text-gray-500 text-xs">{{ $index['unique'] }}</td>
                                            <td class="px-4 py-3 text-gray-500 text-xs">{{ $index['primary'] }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
            </div>
        @else
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700/50 p-12 shadow-sm flex flex-col items-center justify-center text-center space-y-4">
                <div class="w-16 h-16 rounded-2xl bg-indigo-50 dark:bg-indigo-950/50 text-indigo-500 flex items-center justify-center text-2xl">
                    <i class="voyager-data"></i>
                </div>
                <div class="space-y-1">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">Inspect a Table</h3>
                    <p class="text-sm text-gray-500 max-w-sm">Select any table from the sidebar list to inspect columns, data types, indexes, and BREAD details.</p>
                </div>
            </div>
        @endif
    </div>
</div>
