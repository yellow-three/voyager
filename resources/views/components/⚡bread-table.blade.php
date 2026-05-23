@php
use Livewire\Component;
use Livewire\WithPagination;
use YellowThree\Voyager\Facades\Voyager;
use Illuminate\Support\Facades\Schema;

new class extends Component {
    use WithPagination;

    public string $slug;
    public string $search = '';
    public string $searchKey = '';
    public string $orderBy = '';
    public string $orderDir = '';
    public int $perPage = 15;
    public array $selected = [];

    protected $queryString = [
        'search' => ['except' => ''],
        'searchKey' => ['except' => ''],
        'orderBy' => ['except' => ''],
        'orderDir' => ['except' => ''],
    ];

    public function mount(string $slug): void
    {
        $this->slug = $slug;
        $dataType = $this->dataType;

        if (!$dataType) {
            abort(404, 'BREAD data type not found.');
        }

        $this->searchKey = $dataType->default_search_key ?? '';
        if (empty($this->searchKey)) {
            $firstSearchable = $dataType->browseRows()->where('searchable', 1)->first();
            $this->searchKey = $firstSearchable ? $firstSearchable->field : 'id';
        }

        $this->orderBy = $dataType->order_column ?? 'id';
        $this->orderDir = $dataType->order_direction ?? 'desc';
    }

    public function getDataTypeProperty()
    {
        return Voyager::model('DataType')->where('slug', $this->slug)->first();
    }

    public function getBrowseRowsProperty()
    {
        return $this->dataType->browseRows();
    }

    public function getModelProperty()
    {
        $modelClass = $this->dataType->model_name;
        return new $modelClass;
    }

    public function sortBy(string $column): void
    {
        if ($this->orderBy === $column) {
            $this->orderDir = $this->orderDir === 'asc' ? 'desc' : 'asc';
        } else {
            $this->orderBy = $column;
            $this->orderDir = 'asc';
        }
    }

    public function delete(mixed $id): void
    {
        $record = $this->model->findOrFail($id);
        
        // Trigger event/hook if registered
        $record->delete();
        
        $this->selected = array_diff($this->selected, [$id]);
        session()->flash('message', __('voyager::generic.successfully_deleted'));
    }

    public function bulkDelete(): void
    {
        if (empty($this->selected)) {
            return;
        }

        $this->model->whereIn('id', $this->selected)->delete();
        $this->selected = [];
        session()->flash('message', __('voyager::generic.successfully_deleted'));
    }

    public function selectAll(bool $value): void
    {
        if ($value) {
            $this->selected = $this->records->pluck('id')->map(fn($id) => (string)$id)->toArray();
        } else {
            $this->selected = [];
        }
    }

    public function getRecordsProperty()
    {
        $query = $this->model->query();

        // Apply custom scope if defined in BREAD config
        if ($scope = $this->dataType->scope) {
            $query->{$scope}();
        }

        // Apply Search
        if (!empty($this->search) && !empty($this->searchKey)) {
            $query->where($this->searchKey, 'like', '%' . $this->search . '%');
        }

        // Apply Sorting
        if (Schema::hasColumn($this->model->getTable(), $this->orderBy)) {
            $query->orderBy($this->orderBy, $this->orderDir);
        }

        return $query->paginate($this->perPage);
    }
};
@endphp

<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 tracking-tight flex items-center gap-2">
                <i class="voyager-{{ $this->dataType->icon }} text-primary"></i>
                {{ $this->dataType->display_name_plural }}
            </h1>
            <p class="text-sm text-gray-500 mt-1">
                {{ __('voyager::generic.total') }}: <span class="font-semibold text-gray-900">{{ $this->records->total() }}</span>
            </p>
        </div>

        <div class="flex items-center gap-3">
            @if(!empty($this->selected))
                <button wire:click="bulkDelete" wire:confirm="Are you sure you want to delete selected records?" class="bg-red-50 hover:bg-red-100 text-red-600 px-4 py-2 rounded-xl text-sm font-medium transition-colors border border-red-200/50 flex items-center gap-2">
                    <i class="voyager-trash"></i>
                    {{ __('voyager::generic.bulk_delete') }} ({{ count($this->selected) }})
                </button>
            @endif

            <a href="{{ route('voyager.'.$this->dataType->slug.'.create') }}" class="bg-primary hover:bg-primary-hover text-white px-5 py-2.5 rounded-xl text-sm font-semibold shadow-lg shadow-primary/25 transition-all duration-200 flex items-center gap-2">
                <i class="voyager-plus"></i>
                {{ __('voyager::generic.add_new') }}
            </a>
        </div>
    </div>

    <!-- Filters & Search -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 flex flex-col md:flex-row items-center gap-4">
        <div class="relative flex-1 w-full">
            <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                <i class="voyager-search text-lg"></i>
            </span>
            <input type="search" wire:model.live.debounce.300ms="search" placeholder="{{ __('voyager::generic.search') }}..." class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all text-sm bg-gray-50/50">
        </div>

        <div class="flex items-center gap-3 w-full md:w-auto">
            <select wire:model.live="searchKey" class="px-3.5 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all text-sm bg-white min-w-[140px]">
                @foreach($this->browseRows as $row)
                    @if($row->type !== 'relationship')
                        <option value="{{ $row->field }}">{{ $row->display_name }}</option>
                    @endif
                @endforeach
            </select>

            <select wire:model.live="perPage" class="px-3.5 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all text-sm bg-white">
                <option value="10">10</option>
                <option value="15">15</option>
                <option value="25">25</option>
                <option value="50">50</option>
            </select>
        </div>
    </div>

    <!-- Table Card -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto min-w-full">
            <table class="min-w-full divide-y divide-gray-100 text-left text-sm">
                <thead class="bg-gray-50/70">
                    <tr>
                        <th class="px-6 py-4 w-4">
                            <input type="checkbox" wire:click="selectAll($event.target.checked)" class="rounded border-gray-300 text-primary focus:ring-primary/20">
                        </th>
                        @foreach($this->browseRows as $row)
                            <th wire:click="sortBy('{{ $row->field }}')" class="px-6 py-4 font-semibold text-gray-500 cursor-pointer select-none hover:text-gray-900 transition-colors">
                                <div class="flex items-center gap-1.5">
                                    {{ $row->display_name }}
                                    @if($this->orderBy === $row->field)
                                        <i class="voyager-angle-{{ $this->orderDir === 'asc' ? 'up' : 'down' }} text-primary"></i>
                                    @endif
                                </div>
                            </th>
                        @endforeach
                        <th class="px-6 py-4 font-semibold text-gray-500 text-right">{{ __('voyager::generic.actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($this->records as $record)
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-6 py-4">
                                <input type="checkbox" wire:model.live="selected" value="{{ $record->id }}" class="rounded border-gray-300 text-primary focus:ring-primary/20">
                            </td>
                            @foreach($this->browseRows as $row)
                                <td class="px-6 py-4 text-gray-700 whitespace-nowrap">
                                    @if($row->type === 'image')
                                        @if(!empty($record->{$row->field}))
                                            <img src="{{ Voyager::image($record->{$row->field}) }}" class="w-10 h-10 rounded-lg object-cover border border-gray-100">
                                        @else
                                            <span class="text-xs text-gray-400">-</span>
                                        @endif
                                    @elseif($row->type === 'toggle')
                                        <span class="px-2.5 py-1 rounded-full text-xs font-semibold {{ $record->{$row->field} ? 'bg-green-50 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                                            {{ $record->{$row->field} ? __('voyager::generic.yes') : __('voyager::generic.no') }}
                                        </span>
                                    @else
                                        {{ Str::limit(strip_tags($record->{$row->field}), 40) }}
                                    @endif
                                </td>
                            @endforeach
                            <td class="px-6 py-4 text-right whitespace-nowrap">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('voyager.'.$this->dataType->slug.'.show', $record->id) }}" class="text-gray-500 hover:text-primary p-1.5 rounded-lg hover:bg-gray-100 transition-all" title="{{ __('voyager::generic.view') }}">
                                        <i class="voyager-eye text-lg"></i>
                                    </a>
                                    <a href="{{ route('voyager.'.$this->dataType->slug.'.edit', $record->id) }}" class="text-gray-500 hover:text-amber-600 p-1.5 rounded-lg hover:bg-amber-50 transition-all" title="{{ __('voyager::generic.edit') }}">
                                        <i class="voyager-edit text-lg"></i>
                                    </a>
                                    <button wire:click="delete('{{ $record->id }}')" wire:confirm="Are you sure you want to delete this record?" class="text-gray-500 hover:text-red-600 p-1.5 rounded-lg hover:bg-red-50 transition-all" title="{{ __('voyager::generic.delete') }}">
                                        <i class="voyager-trash text-lg"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ count($this->browseRows) + 2 }}" class="px-6 py-12 text-center text-gray-400">
                                <div class="flex flex-col items-center gap-2">
                                    <i class="voyager-warning text-4xl"></i>
                                    <span>{{ __('voyager::generic.no_results') }}</span>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($this->records->hasPages())
            <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/50">
                {{ $this->records->links() }}
            </div>
        @endif
    </div>
</div>
