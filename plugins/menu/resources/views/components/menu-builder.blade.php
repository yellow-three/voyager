

<div class="max-w-4xl space-y-8">
    <div class="flex items-center justify-between border-b border-gray-100 pb-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 tracking-tight flex items-center gap-2">
                <i class="voyager-list text-primary"></i>
                {{ $this->menuId ? 'Edit Menu' : 'Create Menu' }}
            </h1>
            <p class="text-sm text-gray-500 mt-1">{{ $this->menuId ? 'Manage menu items and structure' : 'Create a new navigation menu' }}</p>
        </div>
    </div>

    @if (session()->has('message'))
        <div class="p-4 bg-emerald-50 text-emerald-700 border border-emerald-200/50 rounded-2xl flex items-center gap-2 text-sm font-semibold">
            <i class="voyager-check-circle"></i> {{ session('message') }}
        </div>
    @endif

    <form wire:submit="save" class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 md:p-8 space-y-6">
        <!-- Menu Name -->
        <div class="space-y-1">
            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider">Menu Name</label>
            <input type="text" wire:model.blur="name" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:ring-primary/20 focus:border-primary transition-all text-sm" {{ $this->menuId ? 'readonly' : '' }}>
            @error('name')<p class="text-xs text-red-500 font-semibold mt-1">{{ $message }}</p>@enderror
        </div>

        @if($this->menuId)
            <!-- Menu Items -->
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-bold text-gray-900">Menu Items</h3>
                    <button type="button" wire:click="addMenuItem" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-1.5 rounded-lg text-xs font-semibold transition-colors flex items-center gap-1">
                        <i class="voyager-plus"></i> Add Item
                    </button>
                </div>

                @if(count($this->items) > 0)
                    <div class="space-y-2">
                        @foreach($this->items as $index => $item)
                            <div class="flex items-center gap-3 p-3 rounded-xl border border-gray-200 bg-gray-50/30 hover:bg-gray-50 transition-colors group" wire:key="item-{{ $item['id'] }}">
                                <span class="text-gray-400 cursor-move"><i class="voyager-sort"></i></span>
                                <div class="flex-1 grid grid-cols-1 md:grid-cols-3 gap-2">
                                    <input type="text" value="{{ $item['title'] }}"
                                        wire:blur="updateItem({{ $item['id'] }}, 'title', $event.target.value)"
                                        class="px-3 py-1.5 rounded-lg border border-gray-200 text-sm font-semibold" placeholder="Title">
                                    <input type="text" value="{{ $item['url'] }}"
                                        wire:blur="updateItem({{ $item['id'] }}, 'url', $event.target.value)"
                                        class="px-3 py-1.5 rounded-lg border border-gray-200 text-sm" placeholder="URL or route">
                                    <input type="text" value="{{ $item['icon_class'] ?? '' }}"
                                        wire:blur="updateItem({{ $item['id'] }}, 'icon_class', $event.target.value)"
                                        class="px-3 py-1.5 rounded-lg border border-gray-200 text-sm" placeholder="Icon class">
                                </div>
                                <button type="button" wire:click="deleteItem({{ $item['id'] }})" wire:confirm="Delete this menu item?"
                                    class="text-red-400 hover:text-red-600 opacity-0 group-hover:opacity-100 transition-all">
                                    <i class="voyager-trash"></i>
                                </button>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8 text-gray-400 text-sm">
                        <i class="voyager-warning text-2xl text-amber-500 block mb-2"></i>
                        No items yet. Click "Add Item" to create your first menu item.
                    </div>
                @endif
            </div>
        @endif

        <!-- Submit -->
        <div class="border-t border-gray-100 pt-6 flex items-center justify-end gap-3">
            <a href="{{ route('voyager.menus.index') }}" class="px-4 py-2.5 rounded-xl text-sm font-semibold text-gray-600 hover:text-gray-800 transition-colors">Cancel</a>
            <button type="submit" class="bg-primary hover:bg-primary/90 text-white px-6 py-2.5 rounded-xl text-sm font-semibold shadow-lg shadow-primary/25 transition-all">
                <i class="voyager-save mr-1"></i>
                {{ $this->menuId ? 'Update Menu' : 'Create Menu' }}
            </button>
        </div>
    </form>
</div>
