<div class="max-w-4xl space-y-8">
    <div class="flex items-center justify-between border-b border-gray-100 pb-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 tracking-tight flex items-center gap-2">
                <i class="voyager-file-text text-primary"></i>
                {{ $this->pageId ? 'Edit Page' : 'Create Page' }}
            </h1>
        </div>
    </div>

    <form wire:submit="save" class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 md:p-8 space-y-6">
        <div class="space-y-1">
            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider">Title</label>
            <input type="text" wire:model.blur="title" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:ring-primary/20 focus:border-primary transition-all text-sm">
            @error('title')<p class="text-xs text-red-500 font-semibold mt-1">{{ $message }}</p>@enderror
        </div>

        <div class="space-y-1">
            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider">Slug</label>
            <input type="text" wire:model.blur="slug" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:ring-primary/20 focus:border-primary transition-all text-sm">
            @error('slug')<p class="text-xs text-red-500 font-semibold mt-1">{{ $message }}</p>@enderror
        </div>

        <div class="space-y-1">
            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider">Excerpt</label>
            <textarea wire:model="excerpt" rows="3" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm"></textarea>
        </div>

        <div class="space-y-1">
            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider">Body</label>
            <textarea wire:model="body" rows="12" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm font-mono"></textarea>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="space-y-1">
                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider">Status</label>
                <select wire:model="status" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm bg-white">
                    <option value="PUBLISHED">Published</option>
                    <option value="DRAFT">Draft</option>
                </select>
            </div>
        </div>

        <div class="border-t border-gray-100 pt-6 flex items-center justify-end gap-3">
            <a href="{{ route('voyager.pages.index') }}" class="px-4 py-2.5 rounded-xl text-sm font-semibold text-gray-600 hover:text-gray-800 transition-colors">Cancel</a>
            <button type="submit" class="bg-primary hover:bg-primary/90 text-white px-6 py-2.5 rounded-xl text-sm font-semibold shadow-lg shadow-primary/25 transition-all">
                <i class="voyager-save mr-1"></i>
                {{ $this->pageId ? 'Update Page' : 'Create Page' }}
            </button>
        </div>
    </form>
</div>
