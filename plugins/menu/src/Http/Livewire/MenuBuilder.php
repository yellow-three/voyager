<?php

namespace YellowThree\VoyagerMenu\Http\Livewire;

use Livewire\Component;
use YellowThree\VoyagerMenu\Models\Menu;
use YellowThree\VoyagerMenu\Models\MenuItem;

class MenuBuilder extends Component
{
    public ?int $menuId = null;
    public string $name = '';
    public array $items = [];

    public function mount(?int $id = null): void
    {
        if ($id) {
            $menu = Menu::with('items')->findOrFail($id);
            $this->menuId = $menu->id;
            $this->name = $menu->name;
            $this->items = $menu->items->toArray();
        }
    }

    public function addMenuItem(): void
    {
        if (!$this->menuId) {
            $this->validate(['name' => 'required|max:255|unique:menus,name']);
            $menu = Menu::create(['name' => $this->name]);
            $this->menuId = $menu->id;
        }

        MenuItem::create([
            'menu_id' => $this->menuId,
            'title' => 'New Item',
            'url' => '#',
            'target' => '_self',
            'order' => count($this->items) + 1,
        ]);

        $this->refreshItems();
        session()->flash('message', 'Menu item added.');
    }

    public function updateItem(int $itemId, string $field, string $value): void
    {
        MenuItem::findOrFail($itemId)->update([$field => $value]);
        $this->refreshItems();
    }

    public function deleteItem(int $itemId): void
    {
        MenuItem::findOrFail($itemId)->delete();
        $this->refreshItems();
        session()->flash('message', 'Menu item deleted.');
    }

    public function save(): void
    {
        if ($this->menuId) {
            Menu::findOrFail($this->menuId)->update(['name' => $this->name]);
            session()->flash('message', __('voyager::generic.successfully_updated'));
        } else {
            $this->validate(['name' => 'required|max:255|unique:menus,name']);
            Menu::create(['name' => $this->name]);
            session()->flash('message', __('voyager::generic.successfully_created'));
        }

        $this->redirect(route('voyager.menus.index'));
    }

    private function refreshItems(): void
    {
        if ($this->menuId) {
            $this->items = MenuItem::where('menu_id', $this->menuId)
                ->orderBy('order')
                ->get()
                ->toArray();
        }
    }

    public function render()
    {
        return view('voyager-menu::components.menu-builder')
            ->layout('voyager::layouts.admin');
    }
}
