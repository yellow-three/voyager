<?php

use Livewire\Component;
use YellowThree\VoyagerBlog\Models\Post;
use YellowThree\VoyagerBlog\Models\Category;

new class extends Component {
    public ?int $postId = null;
    public string $title = '';
    public string $slug = '';
    public ?string $excerpt = null;
    public ?string $body = null;
    public ?string $status = 'DRAFT';
    public ?int $category_id = null;
    public bool $featured = false;
    public ?string $seo_title = null;
    public ?string $meta_description = null;
    public ?string $meta_keywords = null;

    public function mount(?int $id = null): void
    {
        if ($id) {
            $post = Post::findOrFail($id);
            $this->postId = $post->id;
            $this->title = $post->title;
            $this->slug = $post->slug;
            $this->excerpt = $post->excerpt;
            $this->body = $post->body;
            $this->status = $post->status;
            $this->category_id = $post->category_id;
            $this->featured = $post->featured;
            $this->seo_title = $post->seo_title;
            $this->meta_description = $post->meta_description;
            $this->meta_keywords = $post->meta_keywords;
        }
    }

    public function save(): void
    {
        $rules = [
            'title' => 'required|max:255',
            'slug' => 'required|unique:posts,slug,' . ($this->postId ?? 'NULL'),
            'status' => 'nullable|in:PUBLISHED,DRAFT,PENDING',
            'category_id' => 'nullable|exists:categories,id',
        ];

        $this->validate($rules);

        $data = [
            'title' => $this->title,
            'slug' => $this->slug,
            'excerpt' => $this->excerpt,
            'body' => $this->body,
            'status' => $this->status,
            'category_id' => $this->category_id,
            'featured' => $this->featured,
            'seo_title' => $this->seo_title,
            'meta_description' => $this->meta_description,
            'meta_keywords' => $this->meta_keywords,
        ];

        if ($this->postId) {
            Post::findOrFail($this->postId)->update($data);
            session()->flash('message', __('voyager::generic.successfully_updated'));
        } else {
            $data['author_id'] = auth()->id();
            Post::create($data);
            session()->flash('message', __('voyager::generic.successfully_created'));
        }

        $this->redirect(route('voyager.posts.index'));
    }

    public function getCategoriesProperty()
    {
        return Category::all();
    }

    public function render(): mixed
    {
        return view('voyager-blog::components.⚡blog-post-form.blog-post-form');
    }
};
