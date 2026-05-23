<div class="max-w-4xl space-y-8">
    <div class="flex items-center justify-between border-b border-gray-100 pb-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 tracking-tight flex items-center gap-2">
                <i class="voyager-edit text-primary"></i>
                {{ $this->postId ? 'Edit Post' : 'Create Post' }}
            </h1>
            <p class="text-sm text-gray-500 mt-1">{{ $this->postId ? 'Update your blog post' : 'Write a new blog post' }}</p>
        </div>
    </div>

    <form wire:submit="save" class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 md:p-8 space-y-6">
        <!-- Title -->
        <div class="space-y-1">
            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider">Title</label>
            <input type="text" wire:model.blur="title" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:ring-primary/20 focus:border-primary transition-all text-sm">
            @error('title')<p class="text-xs text-red-500 font-semibold mt-1">{{ $message }}</p>@enderror
        </div>

        <!-- Slug -->
        <div class="space-y-1">
            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider">Slug</label>
            <input type="text" wire:model.blur="slug" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:ring-primary/20 focus:border-primary transition-all text-sm">
            @error('slug')<p class="text-xs text-red-500 font-semibold mt-1">{{ $message }}</p>@enderror
        </div>

        <!-- Excerpt -->
        <div class="space-y-1">
            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider">Excerpt</label>
            <textarea wire:model="excerpt" rows="3" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:ring-primary/20 focus:border-primary transition-all text-sm"></textarea>
        </div>

        <!-- Body -->
        <div class="space-y-1">
            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider">Body</label>
            <textarea wire:model="body" rows="12" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:ring-primary/20 focus:border-primary transition-all text-sm font-mono"></textarea>
        </div>

        <!-- Meta fields grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="space-y-1">
                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider">Status</label>
                <select wire:model="status" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm bg-white">
                    <option value="PUBLISHED">Published</option>
                    <option value="DRAFT">Draft</option>
                    <option value="PENDING">Pending</option>
                </select>
            </div>
            <div class="space-y-1">
                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider">Category</label>
                <select wire:model="category_id" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm bg-white">
                    <option value="">— None —</option>
                    @foreach($this->categories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="space-y-1 flex items-end pb-2">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" wire:model="featured" class="rounded border-gray-300 text-primary focus:ring-primary/20">
                    <span class="text-xs font-bold text-gray-700 uppercase tracking-wider">Featured</span>
                </label>
            </div>
        </div>

        <!-- SEO Section -->
        <hr class="border-gray-100">
        <div class="space-y-4">
            <h3 class="text-sm font-bold text-gray-900">SEO Settings</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="space-y-1">
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider">SEO Title</label>
                    <input type="text" wire:model="seo_title" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm">
                </div>
                <div class="space-y-1">
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider">Meta Keywords</label>
                    <input type="text" wire:model="meta_keywords" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm">
                </div>
            </div>
            <div class="space-y-1">
                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider">Meta Description</label>
                <textarea wire:model="meta_description" rows="2" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm"></textarea>
            </div>
        </div>

        <!-- Submit -->
        <div class="border-t border-gray-100 pt-6 flex items-center justify-end gap-3">
            <a href="{{ route('voyager.posts.index') }}" class="px-4 py-2.5 rounded-xl text-sm font-semibold text-gray-600 hover:text-gray-800 transition-colors">Cancel</a>
            <button type="submit" class="bg-primary hover:bg-primary/90 text-white px-6 py-2.5 rounded-xl text-sm font-semibold shadow-lg shadow-primary/25 transition-all">
                <i class="voyager-save mr-1"></i>
                {{ $this->postId ? 'Update Post' : 'Create Post' }}
            </button>
        </div>
    </form>
</div>
