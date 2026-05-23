<?php

namespace YellowThree\VoyagerBlog\Http\Livewire;

use Livewire\Component;
use YellowThree\VoyagerBlog\Models\Category;

class CategoryForm extends Component
{
    public ?int $categoryId = null;
    public string $name = '';
    public string $slug = '';
    public ?int $parent_id = null;
    public int $order = 1;

    public function mount(?int $id = null): void
    {
        if ($id) {
            $cat = Category::findOrFail($id);
            $this->categoryId = $cat->id;
            $this->name = $cat->name;
            $this->slug = $cat->slug;
            $this->parent_id = $cat->parent_id;
            $this->order = $cat->order;
        }
    }

    public function save(): void
    {
        $rules = [
            'name' => 'required|max:255',
            'slug' => 'required|unique:categories,slug,' . ($this->categoryId ?? 'NULL'),
            'parent_id' => 'nullable|exists:categories,id',
            'order' => 'nullable|integer',
        ];

        $this->validate($rules);

        $data = [
            'name' => $this->name,
            'slug' => $this->slug,
            'parent_id' => $this->parent_id,
            'order' => $this->order,
        ];

        if ($this->categoryId) {
            Category::findOrFail($this->categoryId)->update($data);
            session()->flash('message', __('voyager::generic.successfully_updated'));
        } else {
            Category::create($data);
            session()->flash('message', __('voyager::generic.successfully_created'));
        }

        $this->redirect(route('voyager.categories.index'));
    }

    public function getParentCategoriesProperty()
    {
        return Category::where('id', '!=', $this->categoryId)->orderBy('name')->get();
    }

    public function render()
    {
        return view('voyager-blog::components.category-form')
            ->layout('voyager::layouts.admin');
    }
}
