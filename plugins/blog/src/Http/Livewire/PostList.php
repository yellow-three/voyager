<?php

namespace YellowThree\VoyagerBlog\Http\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use YellowThree\VoyagerBlog\Models\Post;

class PostList extends Component
{
    use WithPagination;

    public string $search = '';
    public string $status = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    public function deletePost(int $id): void
    {
        Post::findOrFail($id)->delete();
        session()->flash('message', __('voyager::generic.successfully_deleted'));
    }

    public function getPostsProperty()
    {
        $query = Post::with('category');

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('title', 'like', '%' . $this->search . '%')
                  ->orWhere('slug', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->status) {
            $query->where('status', $this->status);
        }

        return $query->latest()->paginate(10);
    }

    public function render()
    {
        return view('voyager-blog::components.post-list')
            ->layout('voyager::layouts.admin');
    }
}
