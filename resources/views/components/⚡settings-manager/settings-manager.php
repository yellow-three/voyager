<?php

use Livewire\Component;
use Livewire\WithFileUploads;
use YellowThree\Voyager\Facades\Voyager;

new class extends Component {
    use WithFileUploads;

    public array $settings = [];
    public string $activeGroup = 'Site';

    public string $newKey = '';
    public string $newDisplayName = '';
    public string $newGroup = 'Site';
    public string $newType = 'text';
    public ?string $newDetails = null;

    public $uploadedFiles = [];

    protected $rules = [
        'settings.*.value' => 'nullable',
    ];

    public function mount(): void
    {
        $this->loadSettings();
    }

    public function loadSettings(): void
    {
        $all = Voyager::model('Setting')::orderBy('order', 'ASC')->get();

        $this->settings = $all->map(function ($setting) {
            return [
                'id' => $setting->id,
                'key' => $setting->key,
                'display_name' => $setting->display_name,
                'value' => $setting->value,
                'details' => $setting->details,
                'type' => $setting->type,
                'order' => $setting->order,
                'group' => $setting->group ?: 'Site',
            ];
        })->toArray();

        $groups = $this->groups;
        if (!empty($groups) && !in_array($this->activeGroup, $groups)) {
            $this->activeGroup = $groups[0];
        }
    }

    public function getGroupsProperty(): array
    {
        return array_values(array_unique(array_map(fn($s) => $s['group'], $this->settings)));
    }

    public function save(): void
    {
        foreach ($this->settings as $settingData) {
            $setting = Voyager::model('Setting')::findOrFail($settingData['id']);

            if ($settingData['type'] === 'image' && isset($this->uploadedFiles[$settingData['id']])) {
                $path = $this->uploadedFiles[$settingData['id']]->store('settings', 'public');
                $setting->value = $path;
            } else {
                $setting->value = $settingData['value'];
            }

            $setting->save();
        }

        $this->uploadedFiles = [];
        $this->loadSettings();
        session()->flash('message', __('voyager::settings.successfully_saved'));
    }

    public function addSetting(): void
    {
        $this->validate([
            'newKey' => 'required|unique:settings,key',
            'newDisplayName' => 'required',
            'newGroup' => 'required',
            'newType' => 'required',
        ]);

        $maxOrder = Voyager::model('Setting')::max('order') ?: 0;

        Voyager::model('Setting')::create([
            'key' => $this->newKey,
            'display_name' => $this->newDisplayName,
            'group' => $this->newGroup,
            'type' => $this->newType,
            'details' => $this->newDetails,
            'order' => $maxOrder + 1,
            'value' => '',
        ]);

        $this->reset(['newKey', 'newDisplayName', 'newDetails']);
        $this->loadSettings();
        session()->flash('message', __('voyager::settings.successfully_created'));
    }

    public function deleteSetting(int $id): void
    {
        $setting = Voyager::model('Setting')::findOrFail($id);
        $setting->delete();
        $this->loadSettings();
        session()->flash('message', 'Setting deleted successfully.');
    }

    public function moveUp(int $id): void
    {
        $setting = Voyager::model('Setting')::findOrFail($id);
        $previousSetting = Voyager::model('Setting')
            ->where('order', '<', $setting->order)
            ->orderBy('order', 'DESC')
            ->first();

        if ($previousSetting) {
            $tempOrder = $setting->order;
            $setting->order = $previousSetting->order;
            $previousSetting->order = $tempOrder;
            $setting->save();
            $previousSetting->save();
        }

        $this->loadSettings();
    }

    public function moveDown(int $id): void
    {
        $setting = Voyager::model('Setting')::findOrFail($id);
        $nextSetting = Voyager::model('Setting')
            ->where('order', '>', $setting->order)
            ->orderBy('order', 'ASC')
            ->first();

        if ($nextSetting) {
            $tempOrder = $setting->order;
            $setting->order = $nextSetting->order;
            $nextSetting->order = $tempOrder;
            $setting->save();
            $nextSetting->save();
        }

        $this->loadSettings();
    }

    public function render(): mixed
    {
        return view('voyager::components.⚡settings-manager.settings-manager');
    }
};
