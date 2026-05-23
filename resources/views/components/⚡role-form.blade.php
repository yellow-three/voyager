@php
use Livewire\Component;
use YellowThree\Voyager\Facades\Voyager;

new class extends Component {
    public ?int $roleId = null;
    public string $name = '';
    public string $displayName = '';
    public array $selectedPermissions = [];
    public bool $isEdit = false;

    public function mount(?int $id = null): void
    {
        $this->roleId = $id;
        $this->isEdit = !is_null($id);

        if ($this->isEdit) {
            $role = Voyager::model('Role')::findOrFail($id);
            $this->name = $role->name;
            $this->displayName = $role->display_name;
            $this->selectedPermissions = $role->permissions()->pluck('id')->map(fn($id) => (int)$id)->toArray();
        }
    }

    public function getPermissionsProperty(): array
    {
        $all = Voyager::model('Permission')::all();
        $grouped = [];

        foreach ($all as $permission) {
            // Group permissions by their table or primary prefix
            $table = $permission->table_name ?: 'Global';
            $grouped[$table][] = [
                'id' => $permission->id,
                'key' => $permission->key,
                'title' => str_replace('_', ' ', $permission->key),
            ];
        }

        return $grouped;
    }

    public function save(): void
    {
        $rules = [
            'name' => 'required|min:3|unique:roles,name,' . ($this->roleId ?: 'NULL'),
            'displayName' => 'required|min:3',
        ];

        $this->validate($rules);

        $role = $this->isEdit ? Voyager::model('Role')::findOrFail($this->roleId) : Voyager::model('Role');
        $role->name = $this->name;
        $role->display_name = $this->displayName;
        $role->save();

        // Sync selected permissions
        $role->permissions()->sync($this->selectedPermissions);

        session()->flash('message', 'Role successfully saved.');
        $this->redirect(route('voyager.roles.index'));
    }
};
@endphp

<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between border-b border-gray-100 pb-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 tracking-tight flex items-center gap-2">
                <i class="voyager-shield text-primary"></i>
                {{ $isEdit ? 'Edit Role' : 'Add Role' }}
            </h1>
            <p class="text-sm text-gray-500 mt-1">Configure role metadata and core access permissions</p>
        </div>

        <a href="{{ route('voyager.roles.index') }}" class="text-gray-500 hover:text-gray-900 px-4 py-2 rounded-xl text-sm font-medium border border-gray-200/50 hover:bg-gray-50 transition-colors flex items-center gap-2">
            <i class="voyager-angle-left"></i>
            Cancel
        </a>
    </div>

    <!-- Form -->
    <form wire:submit="save" class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 md:p-8 space-y-8 max-w-5xl">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Display Name -->
            <div class="space-y-1">
                <label for="displayName" class="block text-xs font-bold text-gray-700 uppercase tracking-wider">Role Display Name</label>
                <input type="text" id="displayName" wire:model.blur="displayName" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:ring-primary/20 focus:border-primary transition-all text-sm">
                @error('displayName')
                    <p class="text-xs text-red-500 font-semibold mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Key Name -->
            <div class="space-y-1">
                <label for="name" class="block text-xs font-bold text-gray-700 uppercase tracking-wider">Role Slug / System Name</label>
                <input type="text" id="name" wire:model.blur="name" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:ring-primary/20 focus:border-primary transition-all text-sm">
                @error('name')
                    <p class="text-xs text-red-500 font-semibold mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <hr class="border-gray-100">

        <!-- Permissions Checkbox System -->
        <div class="space-y-6">
            <div>
                <h3 class="text-sm font-bold text-gray-900">Define Access Permissions</h3>
                <p class="text-xs text-gray-400 mt-0.5">Toggle system access permission scopes for this role</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($this->permissions as $group => $perms)
                    <div class="p-4 rounded-xl border border-gray-100/60 bg-gray-50/20 space-y-3">
                        <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block border-b border-gray-100 pb-1.5">{{ $group }}</span>
                        
                        <div class="space-y-2">
                            @foreach($perms as $perm)
                                <label class="flex items-center gap-2 cursor-pointer text-xs font-semibold text-gray-650 hover:text-gray-900">
                                    <input type="checkbox" wire:model="selectedPermissions" value="{{ $perm['id'] }}" class="h-4 w-4 text-primary focus:ring-primary border-gray-300 rounded-lg">
                                    <span class="capitalize">{{ $perm['title'] }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Submit Button -->
        <div class="border-t border-gray-100 pt-6 flex items-center justify-end">
            <button type="submit" class="bg-primary hover:bg-primary/90 text-white px-6 py-3 rounded-xl text-sm font-semibold shadow-lg shadow-primary/25 transition-all">
                <i class="voyager-save mr-1"></i>
                Save Role
            </button>
        </div>
    </form>
</div>
