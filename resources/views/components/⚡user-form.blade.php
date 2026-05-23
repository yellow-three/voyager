@php
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use YellowThree\Voyager\Facades\Voyager;

new class extends Component {
    use WithFileUploads;

    public ?int $userId = null;
    public string $name = '';
    public string $email = '';
    public ?int $roleId = null;
    public $avatar = null;
    public ?string $password = null;
    public ?string $password_confirmation = null;
    public bool $isEdit = false;

    public function mount(?int $id = null): void
    {
        $this->userId = $id;
        $this->isEdit = !is_null($id);

        if ($this->isEdit) {
            $user = Voyager::model('User')::findOrFail($id);
            $this->name = $user->name;
            $this->email = $user->email;
            $this->roleId = $user->role_id;
        }
    }

    public function getRolesProperty()
    {
        return Voyager::model('Role')::all();
    }

    public function save(): void
    {
        $rules = [
            'name' => 'required|min:2',
            'email' => 'required|email|unique:users,email,' . ($this->userId ?: 'NULL'),
            'roleId' => 'required|exists:roles,id',
            'avatar' => 'nullable|image|max:1024', // max 1MB
        ];

        if (!$this->isEdit || $this->password) {
            $rules['password'] = ['required', 'confirmed', Password::defaults()];
        }

        $this->validate($rules);

        $user = $this->isEdit ? Voyager::model('User')::findOrFail($this->userId) : Voyager::model('User');
        $user->name = $this->name;
        $user->email = $this->email;
        $user->role_id = $this->roleId;

        if ($this->avatar) {
            $path = $this->avatar->store('users', 'public');
            $user->avatar = $path;
        }

        if ($this->password) {
            $user->password = Hash::make($this->password);
        }

        $user->save();

        session()->flash('message', 'User successfully saved.');
        $this->redirect(route('voyager.users.index'));
    }
};
@endphp

<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between border-b border-gray-100 pb-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 tracking-tight flex items-center gap-2">
                <i class="voyager-users text-primary"></i>
                {{ $isEdit ? 'Edit User' : 'Add User' }}
            </h1>
            <p class="text-sm text-gray-500 mt-1">Configure profile details, role scopes, and login security</p>
        </div>

        <a href="{{ route('voyager.users.index') }}" class="text-gray-500 hover:text-gray-900 px-4 py-2 rounded-xl text-sm font-medium border border-gray-200/50 hover:bg-gray-50 transition-colors flex items-center gap-2">
            <i class="voyager-angle-left"></i>
            Cancel
        </a>
    </div>

    <!-- Form -->
    <form wire:submit="save" class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 md:p-8 space-y-8 max-w-5xl">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <!-- Avatar Upload Segment -->
            <div class="md:col-span-1 flex flex-col items-center text-center space-y-4">
                <div class="relative w-32 h-32 rounded-3xl overflow-hidden border-2 border-gray-100 shadow-sm">
                    @if ($avatar)
                        <img src="{{ $avatar->temporaryUrl() }}" class="w-full h-full object-cover">
                    @elseif ($isEdit && Voyager::model('User')::findOrFail($userId)->avatar)
                        <img src="{{ Storage::url(Voyager::model('User')::findOrFail($userId)->avatar) }}" class="w-full h-full object-cover">
                    @else
                        <div class="w-full h-full bg-gray-50 flex items-center justify-center text-gray-400">
                            <i class="voyager-person text-5xl"></i>
                        </div>
                    @endif
                </div>

                <div class="space-y-1">
                    <label class="text-xs font-bold text-gray-700 block">Avatar Photo</label>
                    <input type="file" wire:model="avatar" class="hidden" id="user-avatar-input">
                    <button type="button" onclick="document.getElementById('user-avatar-input').click()" class="text-xs text-primary font-bold hover:underline">Upload Avatar</button>
                    @error('avatar')
                        <p class="text-xs text-red-500 font-semibold">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Profile Fields Segment -->
            <div class="md:col-span-2 space-y-6">
                <!-- Name -->
                <div class="space-y-1">
                    <label for="name" class="block text-xs font-bold text-gray-700 uppercase tracking-wider">Full Name</label>
                    <input type="text" id="name" wire:model.blur="name" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:ring-primary/20 focus:border-primary transition-all text-sm">
                    @error('name')
                        <p class="text-xs text-red-500 font-semibold mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Email -->
                <div class="space-y-1">
                    <label for="email" class="block text-xs font-bold text-gray-700 uppercase tracking-wider">Email Address</label>
                    <input type="email" id="email" wire:model.blur="email" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:ring-primary/20 focus:border-primary transition-all text-sm">
                    @error('email')
                        <p class="text-xs text-red-500 font-semibold mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Role Selection -->
                <div class="space-y-1">
                    <label for="roleId" class="block text-xs font-bold text-gray-700 uppercase tracking-wider">User Role</label>
                    <select id="roleId" wire:model.live="roleId" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:ring-primary/20 focus:border-primary transition-all text-sm bg-white">
                        <option value="">Select a user role</option>
                        @foreach($this->roles as $role)
                            <option value="{{ $role->id }}">{{ $role->display_name }}</option>
                        @endforeach
                    </select>
                    @error('roleId')
                        <p class="text-xs text-red-500 font-semibold mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <hr class="border-gray-100">

                <!-- Password Controls -->
                <div class="space-y-4">
                    <h3 class="text-sm font-bold text-gray-900">Define Login Password</h3>
                    <p class="text-xs text-gray-400 mt-0.5">{{ $isEdit ? 'Optional. Fill only to update password' : 'Required for new user profile credentials' }}</p>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <label for="password" class="block text-xs font-bold text-gray-700 uppercase tracking-wider">Password</label>
                            <input type="password" id="password" wire:model="password" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:ring-primary/20 focus:border-primary transition-all text-sm">
                            @error('password')
                                <p class="text-xs text-red-500 font-semibold mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="space-y-1">
                            <label for="password_confirmation" class="block text-xs font-bold text-gray-700 uppercase tracking-wider">Confirm Password</label>
                            <input type="password" id="password_confirmation" wire:model="password_confirmation" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:ring-primary/20 focus:border-primary transition-all text-sm">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Submit Button -->
        <div class="border-t border-gray-100 pt-6 flex items-center justify-end">
            <button type="submit" class="bg-primary hover:bg-primary/90 text-white px-6 py-3 rounded-xl text-sm font-semibold shadow-lg shadow-primary/25 transition-all">
                <i class="voyager-save mr-1"></i>
                Save User
            </button>
        </div>
    </form>
</div>
