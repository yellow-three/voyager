@php
use Livewire\Component;
use Livewire\WithPagination;
use YellowThree\Voyager\Facades\Voyager;

new class extends Component {
    use WithPagination;

    public string $search = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function deleteUser(int $id): void
    {
        $user = Voyager::model('User')::findOrFail($id);
        
        // Prevent self deletion
        if ($user->id === auth()->id()) {
            session()->flash('error', 'You cannot delete yourself!');
            return;
        }

        $user->delete();
        session()->flash('message', 'User successfully deleted.');
    }

    public function impersonate(int $id): void
    {
        // Safe check
        if ($id === auth()->id()) {
            return;
        }
        
        session()->put('impersonator_id', auth()->id());
        auth()->loginUsingId($id);
        
        $this->redirect(route('voyager.dashboard'));
    }

    // Helper to get paginated users
    public function getUsersProperty()
    {
        $query = Voyager::model('User')::query()->with('role');

        if ($this->search) {
            $query->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('email', 'like', '%' . $this->search . '%');
        }

        return $query->paginate(10);
    }
};
@endphp

<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between border-b border-gray-100 pb-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 tracking-tight flex items-center gap-2">
                <i class="voyager-users text-primary"></i>
                Users Manager
            </h1>
            <p class="text-sm text-gray-500 mt-1">Manage system administrators, editors, and customer accounts</p>
        </div>

        <a href="{{ route('voyager.users.create') }}" class="bg-primary hover:bg-primary/90 text-white px-4 py-2.5 rounded-xl text-sm font-semibold shadow-lg shadow-primary/25 transition-all flex items-center gap-2">
            <i class="voyager-plus"></i>
            Add User
        </a>
    </div>

    @if (session()->has('message'))
        <div class="p-4 bg-emerald-50 text-emerald-700 border border-emerald-200/50 rounded-2xl flex items-center gap-2 text-sm font-semibold">
            <i class="voyager-check-circle"></i>
            {{ session('message') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="p-4 bg-red-50 text-red-700 border border-red-250 rounded-2xl flex items-center gap-2 text-sm font-semibold">
            <i class="voyager-warning"></i>
            {{ session('error') }}
        </div>
    @endif

    <!-- Controls -->
    <div class="flex items-center justify-between gap-4">
        <div class="relative max-w-md w-full">
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search users by name or email..." class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:ring-primary/20 focus:border-primary transition-all text-sm bg-white">
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-gray-50 text-gray-500 font-bold text-xs uppercase">
                    <tr>
                        <th class="px-6 py-4">User</th>
                        <th class="px-6 py-4">Email</th>
                        <th class="px-6 py-4">Role</th>
                        <th class="px-6 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($this->users as $user)
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-6 py-4 flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl overflow-hidden border bg-gray-50 flex items-center justify-center text-gray-400">
                                    @if($user->avatar)
                                        <img src="{{ Storage::url($user->avatar) }}" class="w-full h-full object-cover">
                                    @else
                                        <i class="voyager-person"></i>
                                    @endif
                                </div>
                                <span class="font-semibold text-gray-800">{{ $user->name }}</span>
                            </td>
                            <td class="px-6 py-4 text-gray-650 font-medium">{{ $user->email }}</td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                                    {{ $user->role ? $user->role->display_name : 'No Role' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right space-x-2">
                                @if($user->id !== auth()->id())
                                    <button type="button" wire:click="impersonate({{ $user->id }})" class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-amber-50 text-amber-650 hover:bg-amber-100 transition-colors" title="Login As User">
                                        <i class="voyager-eye mr-1"></i>
                                        Impersonate
                                    </button>
                                @endif
                                <a href="{{ route('voyager.users.edit', $user->id) }}" class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-indigo-50 text-indigo-650 hover:bg-indigo-100 transition-colors">
                                    <i class="voyager-edit mr-1"></i>
                                    Edit
                                </a>
                                @if($user->id !== auth()->id())
                                    <button type="button" wire:confirm="Are you sure you want to delete this user?" wire:click="deleteUser({{ $user->id }})" class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-red-50 text-red-650 hover:bg-red-100 transition-colors">
                                        <i class="voyager-trash mr-1"></i>
                                        Delete
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center text-gray-500 font-medium bg-gray-50/30">
                                <i class="voyager-warning text-2xl text-amber-500 block mb-2"></i>
                                No users found matching your query
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($this->users->hasPages())
            <div class="p-6 border-t border-gray-50 bg-gray-50/30">
                {{ $this->users->links() }}
            </div>
        @endif
    </div>
</div>
