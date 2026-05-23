<?php

namespace YellowThree\VoyagerBlog\Http\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use YellowThree\VoyagerBlog\Models\Category;

class CategoryList extends Component
{
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

    public function render()
    {
        return view('voyager-blog::components.category-list')
            ->layout('voyager::layouts.admin');
    }
}
