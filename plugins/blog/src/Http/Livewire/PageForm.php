<?php

namespace YellowThree\VoyagerBlog\Http\Livewire;

use Livewire\Component;
use YellowThree\VoyagerBlog\Models\Page;

class PageForm extends Component
{
    public ?int $pageId = null;
    public string $title = '';
    public string $slug = '';
    public ?string $excerpt = null;
    public ?string $body = null;
    public ?string $status = 'PUBLISHED';
    public ?string $meta_description = null;
    public ?string $meta_keywords = null;

    public function mount(?int $id = null): void
    {
        if ($id) {
            $page = Page::findOrFail($id);
            $this->pageId = $page->id;
            $this->title = $page->title;
            $this->slug = $page->slug;
            $this->excerpt = $page->excerpt;
            $this->body = $page->body;
            $this->status = $page->status;
            $this->meta_description = $page->meta_description;
            $this->meta_keywords = $page->meta_keywords;
        }
    }

    public function save(): void
    {
        $rules = [
            'title' => 'required|max:255',
            'slug' => 'required|unique:pages,slug,' . ($this->pageId ?? 'NULL'),
            'status' => 'nullable|in:PUBLISHED,DRAFT',
        ];

        $this->validate($rules);

        $data = [
            'title' => $this->title,
            'slug' => $this->slug,
            'excerpt' => $this->excerpt,
            'body' => $this->body,
            'status' => $this->status,
            'meta_description' => $this->meta_description,
            'meta_keywords' => $this->meta_keywords,
        ];

        if ($this->pageId) {
            Page::findOrFail($this->pageId)->update($data);
            session()->flash('message', __('voyager::generic.successfully_updated'));
        } else {
            $data['author_id'] = auth()->id();
            Page::create($data);
            session()->flash('message', __('voyager::generic.successfully_created'));
        }

        $this->redirect(route('voyager.pages.index'));
    }

    public function render()
    {
        return view('voyager-blog::components.page-form')
            ->layout('voyager::layouts.admin');
    }
}
