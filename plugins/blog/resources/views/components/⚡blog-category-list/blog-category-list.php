<?php

use Livewire\Component;
use Livewire\WithPagination;
use YellowThree\VoyagerBlog\Models\Category;

new class extends Component {
    use WithPagination;

    public string $search = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function deleteCategory(int $id): void
    {
        Category::findOrFail($id)->delete();
        session()->flash('message', __('voyager::generic.successfully_deleted'));
    }

    public function getCategoriesProperty()
    {
        $query = Category::withCount('posts');

        if ($this->search) {
            $query->where('name', 'like', '%' . $this->search . '%');
        }

        return $query->orderBy('order')->paginate(10);
    }

    public function render(): mixed
    {
        return view('voyager-blog::components.⚡blog-category-list.blog-category-list');
    }
};
