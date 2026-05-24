<?php
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

new class extends Component {
    public string $email = '';
    public string $password = '';
    public bool $remember = false;

    public function login(): void
    {
        $this->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $guard = app('VoyagerGuard');
        
        if (Auth::guard($guard)->attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
            session()->regenerate();
            $this->redirect(route('voyager.dashboard'));
            return;
        }

        throw ValidationException::withMessages([
            'email' => [trans('auth.failed')],
        ]);
    }
};
?>

<div class="min-h-screen bg-gray-50 flex flex-col justify-center py-12 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-md space-y-4">
        <!-- Logo -->
        <div class="flex justify-center">
            <img class="w-16 h-16 object-contain drop-shadow" src="{{ voyager_asset('images/logo-icon.png') }}" alt="Voyager Logo">
        </div>
        <h2 class="text-center text-3xl font-extrabold text-gray-900 tracking-tight">
            Sign in to Voyager
        </h2>
        <p class="text-center text-sm text-gray-500 font-medium">
            Manage your Laravel application with elegance
        </p>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
        <div class="bg-white py-8 px-4 shadow-xl shadow-slate-100/50 rounded-3xl border border-gray-100 sm:px-10 space-y-6">
            <form wire:submit="login" class="space-y-6">
                <!-- Email -->
                <div class="space-y-1">
                    <label for="email" class="block text-xs font-bold text-gray-700 uppercase tracking-wider">
                        Email Address
                    </label>
                    <div class="relative">
                        <input id="email" type="email" wire:model.blur="email" required autocomplete="email" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:ring-primary/20 focus:border-primary transition-all text-sm @error('email') border-red-300 @enderror">
                    </div>
                    @error('email')
                        <p class="text-xs font-semibold text-red-500 mt-1 flex items-center gap-1">
                            <i class="voyager-warning"></i>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <!-- Password -->
                <div class="space-y-1">
                    <label for="password" class="block text-xs font-bold text-gray-700 uppercase tracking-wider">
                        Password
                    </label>
                    <div class="relative">
                        <input id="password" type="password" wire:model.blur="password" required autocomplete="current-password" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:ring-primary/20 focus:border-primary transition-all text-sm @error('password') border-red-300 @enderror">
                    </div>
                    @error('password')
                        <p class="text-xs font-semibold text-red-500 mt-1 flex items-center gap-1">
                            <i class="voyager-warning"></i>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <!-- Remember & Forgot -->
                <div class="flex items-center justify-between text-sm">
                    <div class="flex items-center">
                        <input id="remember_me" type="checkbox" wire:model="remember" class="h-4 w-4 text-primary focus:ring-primary border-gray-300 rounded-lg">
                        <label for="remember_me" class="ml-2 block text-sm font-semibold text-gray-600">
                            Remember me
                        </label>
                    </div>
                </div>

                <!-- Submit Button -->
                <div>
                    <button type="submit" class="w-full bg-primary hover:bg-primary/95 text-white py-3 px-4 rounded-xl text-sm font-bold shadow-lg shadow-primary/25 transition-all">
                        Sign In
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
