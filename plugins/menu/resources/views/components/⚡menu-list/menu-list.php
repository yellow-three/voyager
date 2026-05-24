<?php

use Livewire\Component;
use Livewire\WithPagination;
use YellowThree\VoyagerMenu\Models\Menu;

new class extends Component {
    use WithPagination;

    public string $search = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function deleteMenu(int $id): void
    {
        Menu::findOrFail($id)->delete();
        session()->flash('message', __('voyager::generic.successfully_deleted'));
    }

    public function getMenusProperty()
    {
        $query = Menu::query();

        if ($this->search) {
            $query->where('name', 'like', '%' . $this->search . '%');
        }

        return $query->withCount('items')->orderBy('name')->paginate(10);
    }

    public function render(): mixed
    {
        return view('voyager-menu::components.⚡menu-list.menu-list');
    }
};
