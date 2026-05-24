<?php
use Livewire\Component;
use YellowThree\Voyager\Facades\Voyager;
use Illuminate\Support\Facades\Schema;

new class extends Component {
    public string $slug;
    public array $orderedIds = [];

    public function mount(string $slug): void
    {
        $this->slug = $slug;

        if (!$this->dataType) {
            abort(404, 'BREAD data type not found.');
        }

        if (empty($this->dataType->order_column)) {
            abort(400, 'Sorting is not enabled or configured for this BREAD.');
        }

        $this->orderedIds = $this->records->pluck('id')->toArray();
    }

    public function getDataTypeProperty()
    {
        return Voyager::model('DataType')->where('slug', $this->slug)->first();
    }

    public function getModelProperty()
    {
        $modelClass = $this->dataType->model_name;
        return new $modelClass;
    }

    public function getRecordsProperty()
    {
        $query = $this->model->query();
        $orderCol = $this->dataType->order_column ?? 'id';
        $orderDir = $this->dataType->order_direction ?? 'asc';

        return $query->orderBy($orderCol, $orderDir)->get();
    }

    public function updateOrder(array $ids): void
    {
        $this->orderedIds = $ids;
        $orderCol = $this->dataType->order_column;

        foreach ($ids as $index => $id) {
            $record = $this->model->find($id);
            if ($record) {
                $record->{$orderCol} = $index + 1;
                $record->save();
            }
        }

        session()->flash('message', __('voyager::generic.successfully_saved'));
    }
};
?>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between border-b border-gray-100 pb-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 tracking-tight flex items-center gap-2">
                <i class="voyager-list text-primary"></i>
                {{ __('voyager::generic.order') }} {{ $this->dataType->display_name_plural }}
            </h1>
            <p class="text-sm text-gray-500 mt-1">
                Drag and drop items below to reorder them.
            </p>
        </div>

        <a href="{{ route('voyager.'.$this->dataType->slug.'.index') }}" class="text-gray-500 hover:text-gray-900 px-4 py-2 rounded-xl text-sm font-medium border border-gray-200/50 hover:bg-gray-50 transition-colors flex items-center gap-2">
            <i class="voyager-angle-left"></i>
            {{ __('voyager::generic.back') }}
        </a>
    </div>

    <!-- Order Card -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 md:p-8 max-w-2xl" x-data="{
        orderedIds: @entangle('orderedIds'),
        saveOrder() {
            $wire.updateOrder(this.orderedIds);
        }
    }">
        <div class="space-y-3">
            @php $orderDisplayCol = $this->dataType->order_display_column ?? 'id'; @endphp
            @foreach($this->records as $record)
                <div class="p-4 bg-gray-50 hover:bg-gray-100/70 border border-gray-200/50 rounded-xl flex items-center justify-between cursor-move shadow-sm transition-all" data-id="{{ $record->id }}">
                    <div class="flex items-center gap-3">
                        <i class="voyager-move text-gray-400 text-lg"></i>
                        <span class="text-sm font-semibold text-gray-700">{{ $record->{$orderDisplayCol} }}</span>
                    </div>
                    <div class="flex items-center gap-1.5 text-xs text-gray-400 font-mono">
                        ID: #{{ $record->id }}
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Action buttons -->
        <div class="border-t border-gray-100 pt-6 mt-6 flex items-center justify-end gap-3">
            <button type="button" @click="saveOrder" class="bg-primary hover:bg-primary-hover text-white px-6 py-3 rounded-xl text-sm font-semibold shadow-lg shadow-primary/25 transition-all duration-200">
                <i class="voyager-save mr-1"></i>
                {{ __('voyager::generic.save') }}
            </button>
        </div>
    </div>
</div>
