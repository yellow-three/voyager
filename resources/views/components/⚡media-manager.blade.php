@php
use Livewire\Component;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

new class extends Component {
    public string $currentPath = '';
    public string $newFolderName = '';
    public string $renameOldName = '';
    public string $renameNewName = '';

    public function mount(): void
    {
        $this->currentPath = '/';
    }

    public function getDiskProperty()
    {
        $diskName = config('voyager.storage.disk', 'public');
        return Storage::disk($diskName);
    }

    public function getDirectoriesProperty()
    {
        return $this->disk->directories($this->currentPath);
    }

    public function getFilesProperty()
    {
        $files = $this->disk->files($this->currentPath);
        $mappedFiles = [];
        
        foreach ($files as $file) {
            $mappedFiles[] = [
                'name' => basename($file),
                'path' => $file,
                'url' => $this->disk->url($file),
                'size' => $this->disk->size($file),
                'type' => File::mimeType($this->disk->path($file)) ?? 'unknown',
            ];
        }

        return $mappedFiles;
    }

    public function createFolder(): void
    {
        $this->validate([
            'newFolderName' => 'required|string|min:1',
        ]);

        $folderPath = trim($this->currentPath, '/') . '/' . $this->newFolderName;
        $this->disk->makeDirectory($folderPath);
        $this->newFolderName = '';

        session()->flash('message', 'Folder created successfully.');
    }

    public function delete(string $path): void
    {
        if ($this->disk->directoryExists($path)) {
            $this->disk->deleteDirectory($path);
        } else {
            $this->disk->delete($path);
        }

        session()->flash('message', 'Deleted successfully.');
    }

    public function changeDirectory(string $path): void
    {
        $this->currentPath = $path;
    }

    public function goBack(): void
    {
        if ($this->currentPath === '/' || empty($this->currentPath)) {
            return;
        }

        $parts = explode('/', trim($this->currentPath, '/'));
        array_pop($parts);
        
        $this->currentPath = empty($parts) ? '/' : '/' . implode('/', $parts);
    }
};
@endphp

<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 border-b border-gray-100 pb-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 tracking-tight flex items-center gap-2">
                <i class="voyager-images text-primary"></i>
                {{ __('voyager::generic.media') }}
            </h1>
            <p class="text-sm text-gray-500 mt-1">
                Manage your public assets and media.
            </p>
        </div>

        <div class="flex items-center gap-3">
            <div class="flex items-center gap-2">
                <input type="text" wire:model="newFolderName" placeholder="New folder name" class="px-4 py-2 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary">
                <button wire:click="createFolder" class="bg-primary hover:bg-primary-hover text-white px-4 py-2 rounded-xl text-sm font-semibold shadow-sm transition-all duration-200">
                    Create Folder
                </button>
            </div>
        </div>
    </div>

    <!-- Navigation Breadcrumbs -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 flex items-center gap-2 text-sm">
        <button wire:click="goBack" class="text-gray-500 hover:text-gray-900 p-1.5 rounded-lg hover:bg-gray-100 transition-all {{ $currentPath === '/' ? 'opacity-50 cursor-not-allowed' : '' }}" {{ $currentPath === '/' ? 'disabled' : '' }}>
            <i class="voyager-angle-left text-lg"></i>
        </button>

        <div class="flex items-center gap-1.5 text-gray-400 font-medium">
            <button wire:click="changeDirectory('/')" class="hover:text-primary transition-colors">Root</button>
            @php $crumbs = array_filter(explode('/', trim($currentPath, '/'))); $accumulated = ''; @endphp
            @foreach($crumbs as $crumb)
                @php $accumulated .= '/' . $crumb; @endphp
                <i class="voyager-angle-right"></i>
                <button wire:click="changeDirectory('{{ $accumulated }}')" class="hover:text-primary transition-colors">{{ $crumb }}</button>
            @endforeach
        </div>
    </div>

    <!-- Media Grid -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-6">
            <!-- Directories -->
            @foreach($this->directories as $dir)
                @php $dirName = basename($dir); @endphp
                <div class="group relative bg-gray-50/50 hover:bg-gray-50 border border-gray-200/50 hover:border-gray-300 rounded-2xl p-4 flex flex-col items-center justify-center text-center cursor-pointer shadow-sm transition-all duration-200" wire:click="changeDirectory('{{ $dir }}')">
                    <i class="voyager-folder text-amber-500 text-5xl mb-2 group-hover:scale-105 transition-transform duration-200"></i>
                    <span class="text-xs font-semibold text-gray-700 truncate w-full" title="{{ $dirName }}">{{ $dirName }}</span>
                    
                    <button wire:click.stop="delete('{{ $dir }}')" class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 text-red-500 hover:text-red-700 bg-white shadow-sm border border-gray-100 rounded-lg p-1 transition-all" title="Delete Folder">
                        <i class="voyager-trash"></i>
                    </button>
                </div>
            @endforeach

            <!-- Files -->
            @foreach($this->files as $file)
                <div class="group relative bg-gray-50/50 hover:bg-gray-50 border border-gray-200/50 hover:border-gray-300 rounded-2xl p-4 flex flex-col items-center justify-center text-center shadow-sm transition-all duration-200">
                    @if(Str::startsWith($file['type'], 'image/'))
                        <img src="{{ $file['url'] }}" class="w-16 h-16 rounded-xl object-cover border border-gray-100 shadow-sm mb-2 group-hover:scale-105 transition-transform duration-200">
                    @else
                        <i class="voyager-file-text text-primary text-5xl mb-2 group-hover:scale-105 transition-transform duration-200"></i>
                    @endif
                    <span class="text-xs font-semibold text-gray-700 truncate w-full" title="{{ $file['name'] }}">{{ $file['name'] }}</span>
                    <span class="text-[10px] text-gray-400 font-mono mt-0.5">{{ number_format($file['size'] / 1024, 1) }} KB</span>
                    
                    <div class="absolute top-2 right-2 flex items-center gap-1.5 opacity-0 group-hover:opacity-100 transition-all">
                        <a href="{{ $file['url'] }}" target="_blank" class="text-gray-500 hover:text-primary bg-white shadow-sm border border-gray-100 rounded-lg p-1 transition-all" title="View File">
                            <i class="voyager-eye"></i>
                        </a>
                        <button wire:click="delete('{{ $file['path'] }}')" class="text-red-500 hover:text-red-700 bg-white shadow-sm border border-gray-100 rounded-lg p-1 transition-all" title="Delete File">
                            <i class="voyager-trash"></i>
                        </button>
                    </div>
                </div>
            @endforeach

            @if(empty($this->directories) && empty($this->files))
                <div class="col-span-full py-16 text-center text-gray-400">
                    <div class="flex flex-col items-center gap-2">
                        <i class="voyager-images text-5xl"></i>
                        <span>This folder is empty.</span>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
