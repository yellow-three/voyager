<?php

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

new class extends Component {
    use WithFileUploads;

    public string $currentPath = '';
    public string $newFolderName = '';
    public string $renamePath = '';
    public string $renameName = '';
    public string $newName = '';
    public string $deletePath = '';
    public bool $showRenameModal = false;
    public bool $showDeleteModal = false;
    public $upload;
    public int $uploadProgress = 0;
    public bool $uploading = false;
    public bool $showFiles = false;

    protected $listeners = ['fileUploaded' => '$refresh'];

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
        $mapped = [];

        foreach ($files as $file) {
            $path = $this->disk->path($file);
            $mapped[] = [
                'name' => basename($file),
                'path' => $file,
                'url' => $this->disk->url($file),
                'size' => $this->disk->size($file),
                'type' => File::exists($path) ? (File::mimeType($path) ?? 'application/octet-stream') : 'application/octet-stream',
                'lastModified' => $this->disk->lastModified($file),
            ];
        }

        return $mapped;
    }

    public function getBreadcrumbsProperty(): array
    {
        $crumbs = [];
        $parts = array_filter(explode('/', trim($this->currentPath, '/')));

        $crumbs[] = ['label' => 'Root', 'path' => '/'];
        $accumulated = '';
        foreach ($parts as $part) {
            $accumulated .= '/' . $part;
            $crumbs[] = ['label' => $part, 'path' => $accumulated];
        }

        return $crumbs;
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

    public function createFolder(): void
    {
        $this->validate(['newFolderName' => 'required|string|min:1|max:255']);

        $folderPath = trim($this->currentPath, '/');
        $fullPath = ($folderPath ? $folderPath . '/' : '') . $this->newFolderName;

        if ($this->disk->exists($fullPath)) {
            session()->flash('error', __('voyager::media.folder_exists_already'));
            return;
        }

        $this->disk->makeDirectory($fullPath);
        $this->newFolderName = '';
        session()->flash('message', __('voyager::generic.successfully_created'));
    }

    public function confirmDelete(string $path): void
    {
        $this->deletePath = $path;
        $this->showDeleteModal = true;
    }

    public function cancelDelete(): void
    {
        $this->deletePath = '';
        $this->showDeleteModal = false;
    }

    public function delete(): void
    {
        if (empty($this->deletePath)) return;

        if ($this->disk->directoryExists($this->deletePath)) {
            $this->disk->deleteDirectory($this->deletePath);
        } else {
            $this->disk->delete($this->deletePath);
        }

        $this->deletePath = '';
        $this->showDeleteModal = false;
        session()->flash('message', __('voyager::generic.successfully_deleted'));
    }

    public function confirmRename(string $path): void
    {
        $this->renamePath = $path;
        $this->renameName = basename($path);
        $this->newName = basename($path);
        $this->showRenameModal = true;
    }

    public function cancelRename(): void
    {
        $this->renamePath = '';
        $this->renameName = '';
        $this->newName = '';
        $this->showRenameModal = false;
    }

    public function rename(): void
    {
        $this->validate(['newName' => 'required|string|min:1|max:255']);

        if ($this->newName === $this->renameName) {
            $this->cancelRename();
            return;
        }

        $dir = dirname($this->renamePath);
        $newPath = ($dir === '.' ? '' : $dir . '/') . $this->newName;

        if ($this->disk->exists($newPath)) {
            session()->flash('error', __('voyager::media.error_may_exist'));
            return;
        }

        $this->disk->move($this->renamePath, $newPath);
        $this->cancelRename();
        session()->flash('message', __('voyager::generic.successfully_updated'));
    }

    public function uploadFile(): void
    {
        $this->validate(['upload' => 'required|file|max:102400']);

        $this->uploading = true;
        $this->uploadProgress = 0;

        $extension = $this->upload->getClientOriginalExtension();
        $name = Str::replaceLast('.' . $extension, '', $this->upload->getClientOriginalName());
        $dir = trim($this->currentPath, '/');
        $path = ($dir ? $dir . '/' : '') . $name . '.' . $extension;

        $counter = 1;
        while ($this->disk->exists($path)) {
            $path = ($dir ? $dir . '/' : '') . $name . '_' . $counter . '.' . $extension;
            $counter++;
        }

        $this->upload->storeAs(
            $dir ?: '/',
            basename($path),
            config('voyager.storage.disk', 'public')
        );

        $this->upload = null;
        $this->uploading = false;
        $this->uploadProgress = 100;

        $this->dispatch('fileUploaded');
        session()->flash('message', __('voyager::media.success_uploaded_file'));
    }

    public function render(): mixed
    {
        return view('voyager::components.⚡media-manager.media-manager');
    }
};
