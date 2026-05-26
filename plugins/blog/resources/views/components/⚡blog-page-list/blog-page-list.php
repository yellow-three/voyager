<?php

use Livewire\Component;
use Livewire\WithPagination;
use YellowThree\VoyagerBlog\Models\Page;

new class extends Component {
    use WithPagination;

    public string $search = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function deletePage(int $id): void
    {
        Page::findOrFail($id)->delete();
        session()->flash('message', __('voyager::generic.successfully_deleted'));
    }

    public function getPagesProperty()
    {
        $query = Page::query();

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('title', 'like', '%' . $this->search . '%')
                  ->orWhere('slug', 'like', '%' . $this->search . '%');
            });
        }

        return $query->latest()->paginate(10);
    }

    public function render(): mixed
    {
        return view('voyager-blog::components.⚡blog-page-list.blog-page-list');
    }
};
