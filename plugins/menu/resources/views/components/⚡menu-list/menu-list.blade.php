<div class="space-y-6">
    <div class="flex items-center justify-between border-b border-gray-100 pb-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 tracking-tight flex items-center gap-2">
                <i class="voyager-list text-primary"></i>
                Menu Manager
            </h1>
            <p class="text-sm text-gray-500 mt-1">Create and manage navigation menus</p>
        </div>
        <a href="{{ route('voyager.menus.create') }}" class="bg-primary hover:bg-primary/90 text-white px-4 py-2.5 rounded-xl text-sm font-semibold shadow-lg shadow-primary/25 transition-all flex items-center gap-2">
            <i class="voyager-plus"></i> Add Menu
        </a>
    </div>

    @if (session()->has('message'))
        <div class="p-4 bg-emerald-50 text-emerald-700 border border-emerald-200/50 rounded-2xl flex items-center gap-2 text-sm font-semibold">
            <i class="voyager-check-circle"></i> {{ session('message') }}
        </div>
    @endif

    <div class="relative max-w-md w-full">
        <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search menus..." class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:ring-primary/20 focus:border-primary transition-all text-sm bg-white">
    </div>

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <table class="w-full text-sm text-left">
            <thead class="bg-gray-50 text-gray-500 font-bold text-xs uppercase">
                <tr>
                    <th class="px-6 py-4">Name</th>
                    <th class="px-6 py-4 text-center">Items</th>
                    <th class="px-6 py-4 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($this->menus as $menu)
                    <tr class="hover:bg-gray-50/50 transition-colors">
                        <td class="px-6 py-4 font-semibold text-gray-800">{{ $menu->name }}</td>
                        <td class="px-6 py-4 text-center text-gray-600">{{ $menu->items_count }}</td>
                        <td class="px-6 py-4 text-right space-x-2">
                            <a href="{{ route('voyager.menus.edit', $menu->id) }}" class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-indigo-50 text-indigo-600 hover:bg-indigo-100 transition-colors">
                                <i class="voyager-edit mr-1"></i> Edit
                            </a>
                            <button type="button" wire:confirm="Are you sure?" wire:click="deleteMenu({{ $menu->id }})" class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-red-50 text-red-600 hover:bg-red-100 transition-colors">
                                <i class="voyager-trash mr-1"></i> Delete
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="px-6 py-12 text-center text-gray-500 font-medium bg-gray-50/30">
                            <i class="voyager-warning text-2xl text-amber-500 block mb-2"></i>
                            No menus found
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        @if($this->menus->hasPages())
            <div class="p-6 border-t border-gray-50 bg-gray-50/30">{{ $this->menus->links() }}</div>
        @endif
    </div>
</div>
