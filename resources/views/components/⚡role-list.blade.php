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

    public function deleteRole(int $id): void
    {
        $role = Voyager::model('Role')::findOrFail($id);
        $role->delete();

        session()->flash('message', 'Role successfully deleted.');
    }

    // Helper to get paginated roles
    public function getRolesProperty()
    {
        $query = Voyager::model('Role')::query();

        if ($this->search) {
            $query->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('display_name', 'like', '%' . $this->search . '%');
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
                <i class="voyager-shield text-primary"></i>
                Roles Manager
            </h1>
            <p class="text-sm text-gray-500 mt-1">Manage system user roles and accessibility permissions</p>
        </div>

        <a href="{{ route('voyager.roles.create') }}" class="bg-primary hover:bg-primary/90 text-white px-4 py-2.5 rounded-xl text-sm font-semibold shadow-lg shadow-primary/25 transition-all flex items-center gap-2">
            <i class="voyager-plus"></i>
            Add Role
        </a>
    </div>

    @if (session()->has('message'))
        <div class="p-4 bg-emerald-50 text-emerald-700 border border-emerald-200/50 rounded-2xl flex items-center gap-2 text-sm font-semibold">
            <i class="voyager-check-circle"></i>
            {{ session('message') }}
        </div>
    @endif

    <!-- Controls -->
    <div class="flex items-center justify-between gap-4">
        <div class="relative max-w-md w-full">
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search roles..." class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:ring-primary/20 focus:border-primary transition-all text-sm bg-white">
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-gray-50 text-gray-500 font-bold text-xs uppercase">
                    <tr>
                        <th class="px-6 py-4">Role Name</th>
                        <th class="px-6 py-4">Display Name</th>
                        <th class="px-6 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($this->roles as $role)
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-6 py-4 font-semibold text-gray-800">{{ $role->name }}</td>
                            <td class="px-6 py-4 text-gray-600">{{ $role->display_name }}</td>
                            <td class="px-6 py-4 text-right space-x-2">
                                <a href="{{ route('voyager.roles.edit', $role->id) }}" class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-indigo-50 text-indigo-650 hover:bg-indigo-100 transition-colors">
                                    <i class="voyager-edit mr-1"></i>
                                    Edit
                                </a>
                                <button type="button" wire:confirm="Are you sure you want to delete this role?" wire:click="deleteRole({{ $role->id }})" class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-red-50 text-red-650 hover:bg-red-100 transition-colors">
                                    <i class="voyager-trash mr-1"></i>
                                    Delete
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-6 py-12 text-center text-gray-500 font-medium bg-gray-50/30">
                                <i class="voyager-warning text-2xl text-amber-500 block mb-2"></i>
                                No roles found matching your query
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($this->roles->hasPages())
            <div class="p-6 border-t border-gray-50 bg-gray-50/30">
                {{ $this->roles->links() }}
            </div>
        @endif
    </div>
</div>
