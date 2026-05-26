<div x-data="mediaManager()" class="space-y-6">
    @if(session('message'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl text-sm flex items-center gap-2">
            <i class="voyager-check text-emerald-500"></i>
            <span>{{ session('message') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl text-sm flex items-center gap-2">
            <i class="voyager-x text-red-500"></i>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 border-b border-gray-100 pb-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 tracking-tight flex items-center gap-2">
                <i class="voyager-images text-primary"></i>
                {{ __('voyager::generic.media') }}
            </h1>
            <p class="text-sm text-gray-500 mt-1">{{ __('voyager::media.media_manager_description') }}</p>
        </div>

        <div class="flex items-center gap-3">
            <div class="flex items-center gap-2">
                <input type="text"
                    wire:model="newFolderName"
                    placeholder="{{ __('voyager::media.new_folder_name') }}"
                    wire:keydown.enter="createFolder"
                    class="px-4 py-2 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary">
                <button wire:click="createFolder"
                    class="bg-primary hover:bg-primary/90 text-white px-4 py-2 rounded-xl text-sm font-semibold shadow-sm transition-all duration-200">
                    <i class="voyager-folder-add"></i>
                    {{ __('voyager::media.add_new_folder') }}
                </button>
            </div>
        </div>
    </div>

    <!-- Breadcrumbs -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 flex items-center gap-2 text-sm">
        <button wire:click="goBack"
            class="text-gray-500 hover:text-gray-900 p-1.5 rounded-lg hover:bg-gray-100 transition-all"
            @disabled($this->currentPath === '/')>
            <i class="voyager-angle-left text-lg {{ $this->currentPath === '/' ? 'opacity-30' : '' }}"></i>
        </button>

        <div class="flex items-center gap-1.5 text-gray-400 font-medium flex-wrap">
            @foreach($this->breadcrumbs as $crumb)
                @if(!$loop->first)
                    <i class="voyager-angle-right text-xs"></i>
                @endif
                <button wire:click="changeDirectory('{{ $crumb['path'] }}')"
                    class="hover:text-primary transition-colors {{ $loop->last ? 'text-gray-900 font-semibold' : '' }}">
                    {{ $crumb['label'] }}
                </button>
            @endforeach
        </div>
    </div>

    <!-- Upload Zone -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <div
            x-on:dragover.prevent="$el.classList.add('border-primary', 'bg-primary/5')"
            x-on:dragleave.prevent="$el.classList.remove('border-primary', 'bg-primary/5')"
            x-on:drop.prevent="
                $el.classList.remove('border-primary', 'bg-primary/5');
                const files = event.dataTransfer.files;
                if (files.length > 0) {
                    $wire.upload = files[0];
                    $wire.uploadFile();
                }
            "
            class="border-2 border-dashed border-gray-200 rounded-2xl p-8 text-center transition-all duration-200 cursor-pointer hover:border-primary/50 hover:bg-gray-50/50">

            <input type="file"
                x-ref="fileInput"
                @change="
                    if ($refs.fileInput.files.length > 0) {
                        $wire.upload = $refs.fileInput.files[0];
                        $wire.uploadFile();
                    }
                "
                class="hidden">

            <div x-show="!$wire.uploading" class="flex flex-col items-center gap-3">
                <i class="voyager-cloud-upload text-5xl text-gray-300"></i>
                <div>
                    <p class="text-gray-600 font-medium">{{ __('voyager::media.drop_files_here') }}</p>
                    <p class="text-gray-400 text-sm mt-1">{{ __('voyager::media.or') }}</p>
                </div>
                <button @click="$refs.fileInput.click()"
                    class="bg-primary hover:bg-primary/90 text-white px-6 py-2.5 rounded-xl text-sm font-semibold shadow-sm transition-all duration-200">
                    <i class="voyager-upload"></i>
                    {{ __('voyager::media.browse_files') }}
                </button>
            </div>

            <div x-show="$wire.uploading" class="flex flex-col items-center gap-3">
                <div class="w-64">
                    <div class="bg-gray-100 rounded-full h-2 overflow-hidden">
                        <div class="bg-primary h-full rounded-full transition-all duration-300"
                            :style="'width: ' + ($wire.uploadProgress || 0) + '%'">
                        </div>
                    </div>
                </div>
                <p class="text-gray-500 text-sm">{{ __('voyager::media.uploading') }}</p>
            </div>
        </div>
    </div>

    <!-- Media Grid -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-6">
            @foreach($this->directories as $dir)
                @php $dirName = basename($dir); @endphp
                <div class="group relative bg-gray-50/50 hover:bg-gray-50 border border-gray-200/50 hover:border-gray-300 rounded-2xl p-4 flex flex-col items-center justify-center text-center cursor-pointer shadow-sm transition-all duration-200"
                    wire:click="changeDirectory('{{ $dir }}')">
                    <i class="voyager-folder text-amber-500 text-5xl mb-2 group-hover:scale-105 transition-transform"></i>
                    <span class="text-xs font-semibold text-gray-700 truncate w-full" title="{{ $dirName }}">{{ $dirName }}</span>

                    <div class="absolute top-2 right-2 flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-all">
                        <button wire:click.stop="confirmRename('{{ $dir }}')"
                            class="text-gray-500 hover:text-primary bg-white shadow-sm border border-gray-100 rounded-lg p-1 transition-all"
                            title="{{ __('voyager::generic.rename') }}">
                            <i class="voyager-edit"></i>
                        </button>
                        <button wire:click.stop="confirmDelete('{{ $dir }}')"
                            class="text-red-500 hover:text-red-700 bg-white shadow-sm border border-gray-100 rounded-lg p-1 transition-all"
                            title="{{ __('voyager::generic.delete') }}">
                            <i class="voyager-trash"></i>
                        </button>
                    </div>
                </div>
            @endforeach

            @foreach($this->files as $file)
                <div class="group relative bg-gray-50/50 hover:bg-gray-50 border border-gray-200/50 hover:border-gray-300 rounded-2xl p-4 flex flex-col items-center justify-center text-center shadow-sm transition-all duration-200">
                    @if(Str::startsWith($file['type'], 'image/'))
                        <img src="{{ $file['url'] }}"
                            class="w-16 h-16 rounded-xl object-cover border border-gray-100 shadow-sm mb-2 group-hover:scale-105 transition-transform">
                    @else
                        <i class="voyager-file-text text-primary text-5xl mb-2 group-hover:scale-105 transition-transform"></i>
                    @endif
                    <span class="text-xs font-semibold text-gray-700 truncate w-full" title="{{ $file['name'] }}">{{ $file['name'] }}</span>
                    <span class="text-[10px] text-gray-400 font-mono mt-0.5">{{ number_format($file['size'] / 1024, 1) }} KB</span>

                    <div class="absolute top-2 right-2 flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-all">
                        <a href="{{ $file['url'] }}" target="_blank"
                            class="text-gray-500 hover:text-primary bg-white shadow-sm border border-gray-100 rounded-lg p-1 transition-all"
                            title="{{ __('voyager::generic.view') }}">
                            <i class="voyager-eye"></i>
                        </a>
                        <button wire:click.stop="confirmRename('{{ $file['path'] }}')"
                            class="text-gray-500 hover:text-primary bg-white shadow-sm border border-gray-100 rounded-lg p-1 transition-all"
                            title="{{ __('voyager::generic.rename') }}">
                            <i class="voyager-edit"></i>
                        </button>
                        <button wire:click.stop="confirmDelete('{{ $file['path'] }}')"
                            class="text-red-500 hover:text-red-700 bg-white shadow-sm border border-gray-100 rounded-lg p-1 transition-all"
                            title="{{ __('voyager::generic.delete') }}">
                            <i class="voyager-trash"></i>
                        </button>
                    </div>
                </div>
            @endforeach

            @if(empty($this->directories) && empty($this->files))
                <div class="col-span-full py-16 text-center text-gray-400">
                    <div class="flex flex-col items-center gap-2">
                        <i class="voyager-images text-5xl"></i>
                        <span>{{ __('voyager::media.empty_folder') }}</span>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Rename Modal -->
    @if($showRenameModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm"
            wire:click.self="cancelRename">
            <div class="bg-white rounded-2xl shadow-xl border border-gray-100 p-6 w-full max-w-md mx-4">
                <h3 class="text-lg font-bold text-gray-900 mb-4">{{ __('voyager::generic.rename') }}</h3>

                <label class="block text-sm font-medium text-gray-700 mb-1.5">{{ __('voyager::generic.name') }}</label>
                <input type="text" wire:model="newName" wire:keydown.enter="rename"
                    class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary">

                @error('newName')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror

                <div class="flex items-center justify-end gap-3 mt-6">
                    <button wire:click="cancelRename"
                        class="px-4 py-2 rounded-xl text-sm font-medium text-gray-600 hover:text-gray-900 hover:bg-gray-50 transition-all">
                        {{ __('voyager::generic.cancel') }}
                    </button>
                    <button wire:click="rename"
                        class="bg-primary hover:bg-primary/90 text-white px-4 py-2 rounded-xl text-sm font-semibold shadow-sm transition-all">
                        {{ __('voyager::generic.save') }}
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- Delete Confirmation Modal -->
    @if($showDeleteModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm"
            wire:click.self="cancelDelete">
            <div class="bg-white rounded-2xl shadow-xl border border-gray-100 p-6 w-full max-w-md mx-4">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center">
                        <i class="voyager-warning text-red-500"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">{{ __('voyager::generic.delete') }}</h3>
                        <p class="text-sm text-gray-500 mt-0.5">{{ __('voyager::media.delete_confirm') }}</p>
                    </div>
                </div>

                <p class="text-sm text-gray-600 bg-gray-50 rounded-xl px-4 py-3 border border-gray-100">
                    <code class="text-gray-800 font-medium">{{ basename($deletePath) }}</code>
                </p>

                <div class="flex items-center justify-end gap-3 mt-6">
                    <button wire:click="cancelDelete"
                        class="px-4 py-2 rounded-xl text-sm font-medium text-gray-600 hover:text-gray-900 hover:bg-gray-50 transition-all">
                        {{ __('voyager::generic.cancel') }}
                    </button>
                    <button wire:click="delete"
                        class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-xl text-sm font-semibold shadow-sm transition-all">
                        <i class="voyager-trash"></i>
                        {{ __('voyager::generic.delete') }}
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>

@script
<script>
    Alpine.data('mediaManager', () => ({
        //
    }));
</script>
@endscript
