

<div class="space-y-8" x-data="{ showCreateModal: false }">
    <!-- Header -->
    <div class="flex items-center justify-between border-b border-gray-100 pb-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 tracking-tight flex items-center gap-2">
                <i class="voyager-settings text-primary"></i>
                {{ __('voyager::generic.settings') }}
            </h1>
            <p class="text-sm text-gray-500 mt-1">Configure global application settings</p>
        </div>

        <button @click="showCreateModal = true" class="bg-primary hover:bg-primary/90 text-white px-4 py-2.5 rounded-xl text-sm font-semibold shadow-lg shadow-primary/25 transition-all flex items-center gap-2">
            <i class="voyager-plus"></i>
            Add Setting
        </button>
    </div>

    @if (session()->has('message'))
        <div class="p-4 bg-emerald-50 text-emerald-700 border border-emerald-200/50 rounded-2xl flex items-center gap-2 text-sm font-semibold">
            <i class="voyager-check-circle"></i>
            {{ session('message') }}
        </div>
    @endif

    <!-- Settings Group Tabs -->
    <div class="flex flex-wrap items-center gap-2 border-b border-gray-100 pb-px">
        @foreach($this->groups as $group)
            <button wire:click="$set('activeGroup', '{{ $group }}')" class="px-5 py-3 border-b-2 font-semibold text-sm transition-all -mb-px {{ $activeGroup === $group ? 'border-primary text-primary' : 'border-transparent text-gray-400 hover:text-gray-600' }}">
                {{ $group }}
            </button>
        @endforeach
    </div>

    <!-- Settings Form -->
    <form wire:submit="save" class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 md:p-8 space-y-8 max-w-5xl">
        <div class="space-y-6">
            @foreach($settings as $index => $setting)
                @if($setting['group'] === $activeGroup)
                    <div class="group relative bg-gray-50/30 dark:bg-gray-800/20 p-4 rounded-xl border border-gray-100 hover:border-gray-200 transition-all flex flex-col md:flex-row md:items-start justify-between gap-4">
                        <div class="flex-1 space-y-2">
                            <div class="flex items-center gap-2">
                                <label class="text-sm font-bold text-gray-800">{{ $setting['display_name'] }}</label>
                                <code class="text-xs text-gray-400 bg-gray-100 dark:bg-gray-800 px-2 py-0.5 rounded">setting('{{ $setting['key'] }}')</code>
                            </div>

                            <!-- Inputs based on setting type -->
                            @if($setting['type'] === 'text')
                                <input type="text" wire:model="settings.{{ $index }}.value" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:ring-primary/20 focus:border-primary transition-all text-sm bg-white">
                            @elseif($setting['type'] === 'text_area')
                                <textarea wire:model="settings.{{ $index }}.value" rows="3" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:ring-primary/20 focus:border-primary transition-all text-sm bg-white"></textarea>
                            @elseif($setting['type'] === 'code_editor')
                                <textarea wire:model="settings.{{ $index }}.value" rows="4" class="w-full font-mono text-xs px-4 py-2.5 rounded-xl border border-gray-200 focus:ring-primary/20 focus:border-primary transition-all bg-gray-50"></textarea>
                            @elseif($setting['type'] === 'image')
                                <div class="flex items-center gap-4">
                                    @if(isset($setting['value']) && $setting['value'])
                                        <img src="{{ Storage::url($setting['value']) }}" class="w-16 h-16 rounded-xl object-cover border">
                                    @endif
                                    <input type="file" wire:model="uploadedFiles.{{ $setting['id'] }}" class="text-xs text-gray-500">
                                </div>
                            @elseif($setting['type'] === 'checkbox')
                                <button type="button" wire:click="$set('settings.{{ $index }}.value', !{{ $setting['value'] ? 'true' : 'false' }})" class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-primary/20 {{ $setting['value'] ? 'bg-primary' : 'bg-gray-200' }}">
                                    <span class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ $setting['value'] ? 'translate-x-5' : 'translate-x-0' }}"></span>
                                </button>
                            @else
                                <input type="text" wire:model="settings.{{ $index }}.value" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:ring-primary/20 focus:border-primary transition-all text-sm bg-white">
                            @endif
                        </div>

                        <!-- Actions (Move/Delete) -->
                        <div class="flex items-center md:self-center gap-1 opacity-60 group-hover:opacity-100 transition-opacity">
                            <button type="button" wire:click="moveUp({{ $setting['id'] }})" class="w-8 h-8 rounded-lg text-gray-500 hover:bg-gray-100 hover:text-gray-800 flex items-center justify-center transition-colors">
                                <i class="voyager-angle-up text-lg"></i>
                            </button>
                            <button type="button" wire:click="moveDown({{ $setting['id'] }})" class="w-8 h-8 rounded-lg text-gray-500 hover:bg-gray-100 hover:text-gray-800 flex items-center justify-center transition-colors">
                                <i class="voyager-angle-down text-lg"></i>
                            </button>
                            <button type="button" wire:click="deleteSetting({{ $setting['id'] }})" class="w-8 h-8 rounded-lg text-red-500 hover:bg-red-50 flex items-center justify-center transition-colors">
                                <i class="voyager-trash"></i>
                            </button>
                        </div>
                    </div>
                @endif
            @endforeach
        </div>

        <div class="border-t border-gray-150 pt-6 flex items-center justify-end">
            <button type="submit" class="bg-primary hover:bg-primary/90 text-white px-6 py-3 rounded-xl text-sm font-semibold shadow-lg shadow-primary/25 transition-all">
                <i class="voyager-save mr-1"></i>
                Save Settings
            </button>
        </div>
    </form>

    <!-- Create Setting Modal (Alpine.js) -->
    <div x-show="showCreateModal" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/50 backdrop-blur-sm p-4" x-cloak>
        <div @click.outside="showCreateModal = false" class="bg-white rounded-2xl max-w-md w-full border shadow-xl p-6 space-y-6">
            <div>
                <h3 class="text-lg font-bold text-gray-900">Add New Setting</h3>
                <p class="text-xs text-gray-500">Create a custom parameter accessible globally</p>
            </div>

            <div class="space-y-4">
                <div class="space-y-1">
                    <label class="text-xs font-bold text-gray-700">Display Name</label>
                    <input type="text" wire:model="newDisplayName" class="w-full px-4 py-2 rounded-xl border text-sm">
                </div>

                <div class="space-y-1">
                    <label class="text-xs font-bold text-gray-700">Key</label>
                    <input type="text" wire:model="newKey" placeholder="e.g. site.custom_title" class="w-full px-4 py-2 rounded-xl border text-sm">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-1">
                        <label class="text-xs font-bold text-gray-700">Type</label>
                        <select wire:model="newType" class="w-full px-4 py-2 rounded-xl border text-sm">
                            <option value="text">Text</option>
                            <option value="text_area">Textarea</option>
                            <option value="code_editor">Code Editor</option>
                            <option value="image">Image</option>
                            <option value="checkbox">Toggle</option>
                        </select>
                    </div>

                    <div class="space-y-1">
                        <label class="text-xs font-bold text-gray-700">Group</label>
                        <input type="text" wire:model="newGroup" class="w-full px-4 py-2 rounded-xl border text-sm">
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3">
                <button type="button" @click="showCreateModal = false" class="px-4 py-2 rounded-xl border text-sm font-semibold hover:bg-gray-50">Cancel</button>
                <button type="button" wire:click="addSetting" @click="showCreateModal = false" class="bg-primary text-white px-4 py-2 rounded-xl text-sm font-semibold hover:bg-primary/95">Add Setting</button>
            </div>
        </div>
    </div>
</div>
