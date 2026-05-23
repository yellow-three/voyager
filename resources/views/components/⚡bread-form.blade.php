@php
use Livewire\Component;
use YellowThree\Voyager\Facades\Voyager;
use Illuminate\Support\Facades\Schema;

new class extends Component {
    public string $slug;
    public ?int $recordId = null;
    public array $data = [];
    public bool $isEdit = false;

    public function mount(string $slug, ?int $id = null): void
    {
        $this->slug = $slug;
        $this->recordId = $id;
        $this->isEdit = !is_null($id);

        $dataType = $this->dataType;
        if (!$dataType) {
            abort(404, 'BREAD data type not found.');
        }

        // Initialize record data
        if ($this->isEdit) {
            $record = $this->model->findOrFail($id);
            foreach ($this->formRows as $row) {
                $this->data[$row->field] = $record->{$row->field};
            }
        } else {
            foreach ($this->formRows as $row) {
                // Default value from configuration if set
                $this->data[$row->field] = $row->details->default ?? null;
            }
        }
    }

    public function getDataTypeProperty()
    {
        return Voyager::model('DataType')->where('slug', $this->slug)->first();
    }

    public function getFormRowsProperty()
    {
        return $this->isEdit ? $this->dataType->editRows() : $this->dataType->addRows();
    }

    public function getModelProperty()
    {
        $modelClass = $this->dataType->model_name;
        return new $modelClass;
    }

    protected function rules(): array
    {
        $rules = [];
        foreach ($this->formRows as $row) {
            $rowRules = [];
            
            if ($row->required) {
                $rowRules[] = 'required';
            } else {
                $rowRules[] = 'nullable';
            }

            // Extract custom rules from validation settings
            if (isset($row->details->validation)) {
                $customRules = is_array($row->details->validation) 
                    ? $row->details->validation 
                    : explode('|', $row->details->validation);
                $rowRules = array_merge($rowRules, $customRules);
            }

            $rules['data.' . $row->field] = implode('|', array_unique($rowRules));
        }
        return $rules;
    }

    protected function validationAttributes(): array
    {
        $attributes = [];
        foreach ($this->formRows as $row) {
            $attributes['data.' . $row->field] = $row->display_name;
        }
        return $attributes;
    }

    public function save(): void
    {
        $this->validate();

        $record = $this->isEdit ? $this->model->findOrFail($this->recordId) : $this->model;

        foreach ($this->data as $field => $value) {
            $record->{$field} = $value;
        }

        $record->save();

        session()->flash('message', __('voyager::generic.successfully_saved'));
        $this->redirect(route('voyager.'.$this->dataType->slug.'.index'));
    }
};
@endphp

<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between border-b border-gray-100 pb-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 tracking-tight flex items-center gap-2">
                <i class="voyager-{{ $this->dataType->icon }} text-primary"></i>
                {{ $this->isEdit ? __('voyager::generic.edit') : __('voyager::generic.add') }} {{ $this->dataType->display_name_singular }}
            </h1>
            <p class="text-sm text-gray-500 mt-1">
                {{ __('voyager::generic.manage') }} {{ $this->dataType->display_name_plural }}
            </p>
        </div>

        <a href="{{ route('voyager.'.$this->dataType->slug.'.index') }}" class="text-gray-500 hover:text-gray-900 px-4 py-2 rounded-xl text-sm font-medium border border-gray-200/50 hover:bg-gray-50 transition-colors flex items-center gap-2">
            <i class="voyager-angle-left"></i>
            {{ __('voyager::generic.cancel') }}
        </a>
    </div>

    <!-- Form Body -->
    <form wire:submit="save" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 md:p-8 space-y-6 max-w-4xl">
        <div class="grid grid-cols-1 gap-6">
            @foreach($this->formRows as $row)
                @php
                    $fieldName = 'data.'.$row->field;
                    $errorClass = $errors->has($fieldName) ? 'border-red-300 focus:ring-red-200 focus:border-red-500' : 'border-gray-200 focus:ring-primary/20 focus:border-primary';
                @endphp

                <div class="space-y-2">
                    <label for="field-{{ $row->field }}" class="block text-sm font-semibold text-gray-700 flex items-center gap-1">
                        {{ $row->display_name }}
                        @if($row->required)
                            <span class="text-red-500">*</span>
                        @endif
                    </label>

                    <!-- Render appropriate inputs based on row type -->
                    @if($row->type === 'text')
                        <input id="field-{{ $row->field }}" type="text" wire:model.blur="{{ $fieldName }}" class="w-full px-4 py-2.5 rounded-xl border {{ $errorClass }} transition-all text-sm bg-gray-50/50 focus:bg-white">
                    @elseif($row->type === 'text_area')
                        <textarea id="field-{{ $row->field }}" wire:model.blur="{{ $fieldName }}" rows="4" class="w-full px-4 py-2.5 rounded-xl border {{ $errorClass }} transition-all text-sm bg-gray-50/50 focus:bg-white"></textarea>
                    @elseif($row->type === 'number')
                        <input id="field-{{ $row->field }}" type="number" wire:model.blur="{{ $fieldName }}" class="w-full px-4 py-2.5 rounded-xl border {{ $errorClass }} transition-all text-sm bg-gray-50/50 focus:bg-white">
                    @elseif($row->type === 'password')
                        <input id="field-{{ $row->field }}" type="password" wire:model="{{ $fieldName }}" placeholder="{{ $this->isEdit ? 'Leave empty to keep current password' : '' }}" class="w-full px-4 py-2.5 rounded-xl border {{ $errorClass }} transition-all text-sm bg-gray-50/50 focus:bg-white">
                    @elseif($row->type === 'toggle')
                        <button type="button" wire:click="$set('{{ $fieldName }}', !{{ $this->data[$row->field] ?? false }})" class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-primary/20 {{ ($this->data[$row->field] ?? false) ? 'bg-primary' : 'bg-gray-200' }}">
                            <span class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ ($this->data[$row->field] ?? false) ? 'translate-x-5' : 'translate-x-0' }}"></span>
                        </button>
                    @elseif($row->type === 'select_dropdown')
                        <select id="field-{{ $row->field }}" wire:model.live="{{ $fieldName }}" class="w-full px-4 py-2.5 rounded-xl border {{ $errorClass }} transition-all text-sm bg-white">
                            <option value="">Select option</option>
                            @if(isset($row->details->options))
                                @foreach($row->details->options as $key => $option)
                                    <option value="{{ $key }}">{{ $option }}</option>
                                @endforeach
                            @endif
                        </select>
                    @else
                        <!-- Placeholder/Fallback for unsupported/complex fields -->
                        <div class="p-4 bg-gray-50 rounded-xl text-xs text-gray-500 border border-dashed border-gray-200 flex items-center gap-2">
                            <i class="voyager-warning text-lg text-amber-500"></i>
                            <span>BREAD field type '{{ $row->type }}' fallback container.</span>
                        </div>
                    @endif

                    @error($fieldName)
                        <p class="text-xs font-medium text-red-500 mt-1 flex items-center gap-1">
                            <i class="voyager-warning"></i>
                            {{ $message }}
                        </p>
                    @enderror
                </div>
            @endforeach
        </div>

        <!-- Action buttons -->
        <div class="border-t border-gray-100 pt-6 flex items-center justify-end gap-3">
            <button type="submit" class="bg-primary hover:bg-primary-hover text-white px-6 py-3 rounded-xl text-sm font-semibold shadow-lg shadow-primary/25 transition-all duration-200">
                <i class="voyager-save mr-1"></i>
                {{ __('voyager::generic.save') }}
            </button>
        </div>
    </form>
</div>
