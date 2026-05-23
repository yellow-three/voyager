@php
use Livewire\Component;
use YellowThree\Voyager\Facades\Voyager;

new class extends Component {
    public string $slug;
    public int $recordId;

    public function mount(string $slug, int $id): void
    {
        $this->slug = $slug;
        $this->recordId = $id;

        if (!$this->dataType) {
            abort(404, 'BREAD data type not found.');
        }
    }

    public function getDataTypeProperty()
    {
        return Voyager::model('DataType')->where('slug', $this->slug)->first();
    }

    public function getReadRowsProperty()
    {
        return $this->dataType->readRows();
    }

    public function getRecordProperty()
    {
        $modelClass = $this->dataType->model_name;
        return $modelClass::findOrFail($this->recordId);
    }
};
@endphp

<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between border-b border-gray-100 pb-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 tracking-tight flex items-center gap-2">
                <i class="voyager-eye text-primary"></i>
                {{ __('voyager::generic.view') }} {{ $this->dataType->display_name_singular }} #{{ $this->recordId }}
            </h1>
            <p class="text-sm text-gray-500 mt-1">
                {{ __('voyager::generic.manage') }} {{ $this->dataType->display_name_plural }}
            </p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('voyager.'.$this->dataType->slug.'.edit', $this->recordId) }}" class="bg-amber-50 hover:bg-amber-100 text-amber-700 px-4 py-2 rounded-xl text-sm font-semibold border border-amber-200/50 transition-colors flex items-center gap-2">
                <i class="voyager-edit"></i>
                {{ __('voyager::generic.edit') }}
            </a>

            <a href="{{ route('voyager.'.$this->dataType->slug.'.index') }}" class="text-gray-500 hover:text-gray-900 px-4 py-2 rounded-xl text-sm font-medium border border-gray-200/50 hover:bg-gray-50 transition-colors flex items-center gap-2">
                <i class="voyager-angle-left"></i>
                {{ __('voyager::generic.back') }}
            </a>
        </div>
    </div>

    <!-- Details Card -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 md:p-8 max-w-4xl space-y-6">
        <div class="divide-y divide-gray-100">
            @foreach($this->readRows as $row)
                <div class="py-4.5 grid grid-cols-1 md:grid-cols-3 gap-2 md:gap-6 first:pt-0 last:pb-0">
                    <span class="block text-sm font-semibold text-gray-500 tracking-wide uppercase">{{ $row->display_name }}</span>
                    <div class="md:col-span-2 text-sm text-gray-800 break-all font-medium">
                        @if($row->type === 'image')
                            @if(!empty($this->record->{$row->field}))
                                <img src="{{ Voyager::image($this->record->{$row->field}) }}" class="max-w-md rounded-xl object-cover border border-gray-100 shadow-sm max-h-64">
                            @else
                                <span class="text-xs text-gray-400 font-normal">-</span>
                            @endif
                        @elseif($row->type === 'toggle')
                            <span class="px-2.5 py-1 rounded-full text-xs font-bold {{ $this->record->{$row->field} ? 'bg-green-50 text-green-700 border border-green-200/50' : 'bg-gray-100 text-gray-600 border border-gray-200/50' }}">
                                {{ $this->record->{$row->field} ? __('voyager::generic.yes') : __('voyager::generic.no') }}
                            </span>
                        @else
                            {{ $this->record->{$row->field} ?? '-' }}
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
