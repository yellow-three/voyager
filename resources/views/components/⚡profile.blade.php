@php
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

new class extends Component {
    use WithFileUploads;

    public string $name = '';
    public string $email = '';
    public $avatar = null;
    public ?string $password = null;
    public ?string $password_confirmation = null;

    public function mount(): void
    {
        $user = Auth::user();
        $this->name = $user->name;
        $this->email = $user->email;
    }

    public function save(): void
    {
        $user = Auth::user();

        $rules = [
            'name' => 'required|min:2',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'avatar' => 'nullable|image|max:1024', // max 1MB
        ];

        if ($this->password) {
            $rules['password'] = ['required', 'confirmed', Password::defaults()];
        }

        $this->validate($rules);

        $user->name = $this->name;
        $user->email = $this->email;

        if ($this->avatar) {
            $path = $this->avatar->store('users', 'public');
            $user->avatar = $path;
        }

        if ($this->password) {
            $user->password = Hash::make($this->password);
        }

        $user->save();

        $this->reset(['password', 'password_confirmation', 'avatar']);
        session()->flash('message', __('voyager::profile.successfully_saved') ?? 'Profile successfully updated.');
    }
};
@endphp

<div class="max-w-4xl space-y-8">
    <!-- Header -->
    <div class="flex items-center justify-between border-b border-gray-100 pb-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 tracking-tight flex items-center gap-2">
                <i class="voyager-person text-primary"></i>
                Profile Manager
            </h1>
            <p class="text-sm text-gray-500 mt-1">Manage your administrative profile and security credentials</p>
        </div>
    </div>

    @if (session()->has('message'))
        <div class="p-4 bg-emerald-50 text-emerald-700 border border-emerald-200/50 rounded-2xl flex items-center gap-2 text-sm font-semibold">
            <i class="voyager-check-circle"></i>
            {{ session('message') }}
        </div>
    @endif

    <!-- Profile Forms Grid -->
    <form wire:submit="save" class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 md:p-8 space-y-8">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <!-- Avatar Upload Section -->
            <div class="md:col-span-1 flex flex-col items-center text-center space-y-4">
                <div class="relative w-32 h-32 rounded-3xl overflow-hidden border-2 border-gray-100 shadow-sm">
                    @if ($avatar)
                        <img src="{{ $avatar->temporaryUrl() }}" class="w-full h-full object-cover">
                    @elseif (Auth::user()->avatar)
                        <img src="{{ Storage::url(Auth::user()->avatar) }}" class="w-full h-full object-cover">
                    @else
                        <div class="w-full h-full bg-gray-50 flex items-center justify-center text-gray-400">
                            <i class="voyager-person text-5xl"></i>
                        </div>
                    @endif
                </div>

                <div class="space-y-1">
                    <label class="text-xs font-bold text-gray-700 block">Administrator Avatar</label>
                    <input type="file" wire:model="avatar" class="hidden" id="avatar-file-input">
                    <button type="button" onclick="document.getElementById('avatar-file-input').click()" class="text-xs text-primary font-bold hover:underline">Change Photo</button>
                    @error('avatar')
                        <p class="text-xs text-red-500 font-semibold">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Details Section -->
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

                <hr class="border-gray-100">

                <div class="space-y-4">
                    <h3 class="text-sm font-bold text-gray-900">Update Password</h3>
                    <p class="text-xs text-gray-400">Leave blank if you do not want to update password</p>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Password -->
                        <div class="space-y-1">
                            <label for="password" class="block text-xs font-bold text-gray-700 uppercase tracking-wider">New Password</label>
                            <input type="password" id="password" wire:model="password" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:ring-primary/20 focus:border-primary transition-all text-sm">
                            @error('password')
                                <p class="text-xs text-red-500 font-semibold mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Confirm Password -->
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
                Save Profile
            </button>
        </div>
    </form>
</div>
