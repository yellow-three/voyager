<?php
use Livewire\Component;
use Illuminate\Support\Facades\Schema;
use YellowThree\Voyager\Facades\Voyager;
use YellowThree\Voyager\Bread\BreadManager;
use YellowThree\Voyager\Bread\Bread;

new class extends Component {
    public array $tables = [];
    public ?string $selectedTable = null;
    
    // BREAD Form State
    public string $name = '';
    public string $slug = '';
    public string $displayNameSingular = '';
    public string $displayNamePlural = '';
    public ?string $modelName = null;
    public ?string $controller = null;
    public ?string $icon = null;
    public bool $generatePermissions = true;
    public bool $serverSide = false;
    public array $rows = [];

    public function mount(): void
    {
        $this->loadTables();
    }

    public function loadTables(): void
    {
        $this->tables = [];
        try {
            $tableNames = Schema::getTables();
            $manager = app(BreadManager::class);
            $allBreads = $manager->all()->pluck('slug', 'name')->toArray();

            foreach ($tableNames as $tableInfo) {
                $name = is_array($tableInfo) ? ($tableInfo['name'] ?? null) : ($tableInfo->name ?? $tableInfo);
                if (empty($name)) continue;

                // Match with registered BREADs
                $hasBread = in_array($name, array_keys($allBreads)) || in_array(str_replace('_', '-', $name), $allBreads);
                $breadSlug = $hasBread ? ($allBreads[$name] ?? str_replace('_', '-', $name)) : null;

                $this->tables[] = [
                    'name' => $name,
                    'has_bread' => $hasBread,
                    'bread_slug' => $breadSlug,
                ];
            }
        } catch (\Exception $e) {}
    }

    public function buildBread(string $tableName): void
    {
        $this->selectedTable = $tableName;
        $manager = app(BreadManager::class);
        $bread = $manager->find(str_replace('_', '-', $tableName)) ?? $manager->find($tableName);

        if ($bread) {
            $this->name = $bread->name;
            $this->slug = $bread->slug;
            $this->displayNameSingular = $bread->display_name_singular;
            $this->displayNamePlural = $bread->display_name_plural;
            $this->modelName = $bread->model_name;
            $this->controller = $bread->controller;
            $this->icon = $bread->icon;
            $this->generatePermissions = $bread->generate_permissions;
            $this->serverSide = $bread->server_side;
            $this->rows = $bread->rows;
        } else {
            // Setup defaults
            $this->name = $tableName;
            $this->slug = str_replace('_', '-', $tableName);
            $this->displayNameSingular = ucwords(str_replace('_', ' ', $tableName));
            $this->displayNamePlural = ucwords(str_replace('_', ' ', $tableName));
            $this->modelName = 'App\\Models\\' . studly_case(str_singular($tableName));
            $this->controller = '';
            $this->icon = 'file';
            $this->generatePermissions = true;
            $this->serverSide = false;
            
            // Build default rows from database columns
            $this->rows = [];
            try {
                $columns = Schema::getColumns($tableName);
                foreach ($columns as $index => $column) {
                    $this->rows[] = [
                        'field' => $column['name'],
                        'type' => $this->mapColumnTypeToField($column['type_name'] ?? $column['type']),
                        'display_name' => ucwords(str_replace('_', ' ', $column['name'])),
                        'required' => !$column['nullable'],
                        'browse' => true,
                        'read' => true,
                        'edit' => true,
                        'add' => true,
                        'delete' => true,
                        'details' => [],
                        'order' => $index + 1,
                    ];
                }
            } catch (\Exception $e) {}
        }
    }

    private function mapColumnTypeToField(string $type): string
    {
        $type = strtolower($type);
        if (str_contains($type, 'int')) return 'number';
        if (str_contains($type, 'text') || str_contains($type, 'blob')) return 'text_area';
        if (str_contains($type, 'date') || str_contains($type, 'time')) return 'timestamp';
        if (str_contains($type, 'bool') || str_contains($type, 'tinyint(1)')) return 'checkbox';
        
        return 'text';
    }

    public function saveBread(): void
    {
        $this->validate([
            'slug' => 'required',
            'displayNameSingular' => 'required',
            'displayNamePlural' => 'required',
        ]);

        $bread = new Bread([
            'slug' => $this->slug,
            'name' => $this->name,
            'display_name_singular' => $this->displayNameSingular,
            'display_name_plural' => $this->displayNamePlural,
            'model_name' => $this->modelName,
            'controller' => $this->controller,
            'icon' => $this->icon,
            'generate_permissions' => $this->generatePermissions,
            'server_side' => $this->serverSide,
            'rows' => $this->rows,
        ]);

        $manager = app(BreadManager::class);
        $manager->save($bread);

        $this->selectedTable = null;
        $this->loadTables();
        session()->flash('message', 'BREAD configuration successfully saved to JSON!');
    }
};
?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <!-- Tables List -->
    <div class="lg:col-span-1 bg-white p-6 rounded-2xl border border-gray-100 shadow-sm space-y-6">
        <div>
            <h2 class="text-xl font-bold text-gray-900 tracking-tight flex items-center gap-2">
                <i class="voyager-tools text-primary"></i>
                BREAD Builder
            </h2>
            <p class="text-xs text-gray-500 mt-1">Configure layout fields, names, and models for any database table</p>
        </div>

        @if (session()->has('message'))
            <div class="p-3 bg-emerald-50 text-emerald-700 border border-emerald-100 rounded-xl text-xs font-semibold">
                {{ session('message') }}
            </div>
        @endif

        <div class="divide-y divide-gray-50 max-h-[600px] overflow-y-auto pr-2 space-y-2">
            @foreach($tables as $table)
                <div class="flex items-center justify-between p-3 rounded-xl hover:bg-gray-50 transition-all group">
                    <div class="flex-1 text-left">
                        <span class="text-sm font-semibold text-gray-800">{{ $table['name'] }}</span>
                        <div class="flex items-center gap-2 mt-1">
                            @if($table['has_bread'])
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-emerald-50 text-emerald-700">Active BREAD</span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-gray-100 text-gray-500">No BREAD</span>
                            @endif
                        </div>
                    </div>

                    <button type="button" wire:click="buildBread('{{ $table['name'] }}')" class="px-3 py-1.5 rounded-lg text-xs font-bold bg-primary text-white hover:bg-primary/95 transition-all">
                        {{ $table['has_bread'] ? 'Edit BREAD' : 'Add BREAD' }}
                    </button>
                </div>
            @endforeach
        </div>
    </div>

    <!-- BREAD Config Panel -->
    <div class="lg:col-span-2 space-y-6">
        @if($selectedTable)
            <form wire:submit="saveBread" class="bg-white rounded-2xl border border-gray-100 p-6 md:p-8 shadow-sm space-y-8">
                <div>
                    <h2 class="text-xl font-bold text-gray-900 tracking-tight">Configure BREAD: {{ $selectedTable }}</h2>
                    <p class="text-xs text-gray-500 mt-1">Setup plural names, Eloquent bindings, and controller namespaces</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-1">
                        <label class="text-xs font-bold text-gray-700 block">Singular Display Name</label>
                        <input type="text" wire:model="displayNameSingular" class="w-full px-4 py-2 rounded-xl border text-sm">
                    </div>

                    <div class="space-y-1">
                        <label class="text-xs font-bold text-gray-700 block">Plural Display Name</label>
                        <input type="text" wire:model="displayNamePlural" class="w-full px-4 py-2 rounded-xl border text-sm">
                    </div>

                    <div class="space-y-1">
                        <label class="text-xs font-bold text-gray-700 block">URL Slug</label>
                        <input type="text" wire:model="slug" class="w-full px-4 py-2 rounded-xl border text-sm">
                    </div>

                    <div class="space-y-1">
                        <label class="text-xs font-bold text-gray-700 block">Model Namespace Class</label>
                        <input type="text" wire:model="modelName" class="w-full px-4 py-2 rounded-xl border text-sm">
                    </div>
                </div>

                <hr class="border-gray-150">

                <!-- Columns Mapping -->
                <div class="space-y-4">
                    <div>
                        <h3 class="text-sm font-bold text-gray-900">Define Column Fields</h3>
                        <p class="text-xs text-gray-400 mt-0.5">Toggle columns display scopes and select FormField types</p>
                    </div>

                    <div class="overflow-x-auto border rounded-xl divide-y">
                        <table class="w-full text-xs text-left">
                            <thead class="bg-gray-50 text-gray-500 font-bold uppercase">
                                <tr>
                                    <th class="px-4 py-3">Column</th>
                                    <th class="px-4 py-3">Display Name</th>
                                    <th class="px-4 py-3">Type</th>
                                    <th class="px-4 py-3 text-center">Browse</th>
                                    <th class="px-4 py-3 text-center">Read</th>
                                    <th class="px-4 py-3 text-center">Edit</th>
                                    <th class="px-4 py-3 text-center">Add</th>
                                    <th class="px-4 py-3 text-center">Delete</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach($rows as $index => $row)
                                    <tr class="hover:bg-gray-50/50">
                                        <td class="px-4 py-3 font-semibold text-gray-800">{{ $row['field'] }}</td>
                                        <td class="px-4 py-3">
                                            <input type="text" wire:model="rows.{{ $index }}.display_name" class="px-2 py-1 border rounded text-xs">
                                        </td>
                                        <td class="px-4 py-3">
                                            <select wire:model="rows.{{ $index }}.type" class="px-2 py-1 border rounded text-xs bg-white">
                                                <option value="text">Text</option>
                                                <option value="text_area">Textarea</option>
                                                <option value="number">Number</option>
                                                <option value="password">Password</option>
                                                <option value="checkbox">Toggle</option>
                                                <option value="select_dropdown">Select</option>
                                                <option value="image">Image</option>
                                                <option value="timestamp">Timestamp</option>
                                            </select>
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <input type="checkbox" wire:model="rows.{{ $index }}.browse" class="h-4 w-4 text-primary rounded">
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <input type="checkbox" wire:model="rows.{{ $index }}.read" class="h-4 w-4 text-primary rounded">
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <input type="checkbox" wire:model="rows.{{ $index }}.edit" class="h-4 w-4 text-primary rounded">
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <input type="checkbox" wire:model="rows.{{ $index }}.add" class="h-4 w-4 text-primary rounded">
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <input type="checkbox" wire:model="rows.{{ $index }}.delete" class="h-4 w-4 text-primary rounded">
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="border-t border-gray-150 pt-6 flex items-center justify-end">
                    <button type="submit" class="bg-primary hover:bg-primary/90 text-white px-6 py-3 rounded-xl text-sm font-semibold shadow-lg shadow-primary/25 transition-all">
                        <i class="voyager-save mr-1"></i>
                        Save BREAD
                    </button>
                </div>
            </form>
        @else
            <div class="bg-white rounded-2xl border border-gray-100 p-12 shadow-sm flex flex-col items-center justify-center text-center space-y-4">
                <div class="w-16 h-16 rounded-2xl bg-indigo-50 text-indigo-500 flex items-center justify-center text-2xl">
                    <i class="voyager-tools"></i>
                </div>
                <div class="space-y-1">
                    <h3 class="text-lg font-bold text-gray-900">Setup BREAD Configuration</h3>
                    <p class="text-sm text-gray-500 max-w-sm">Select any table from the sidebar list to configure its BREAD metadata, Eloquent mappings, and column structures.</p>
                </div>
            </div>
        @endif
    </div>
</div>
