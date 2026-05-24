<?php

use Livewire\Component;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use YellowThree\Voyager\Plugins\PluginManager;

new class extends Component {
    public array $plugins = [];

    public function mount(): void
    {
        $this->loadPlugins();
    }

    public function loadPlugins(): void
    {
        $manager = app(PluginManager::class);
        $allPlugins = $manager->all();

        $this->plugins = [];

        $activePlugins = [];
        if (Schema::hasTable('plugins')) {
            $activePlugins = DB::table('plugins')
                ->where('is_active', 1)
                ->pluck('name')
                ->toArray();
        }

        foreach ($allPlugins as $plugin) {
            $isActive = in_array($plugin->name(), $activePlugins) || $plugin->name() === 'voyager-core';

            $this->plugins[] = [
                'name' => $plugin->name(),
                'version' => $plugin->version(),
                'label' => $plugin->label(),
                'description' => $plugin->description(),
                'author' => $plugin->author(),
                'is_active' => $isActive,
                'is_core' => $plugin->name() === 'voyager-core',
            ];
        }
    }

    public function togglePlugin(string $name): void
    {
        if ($name === 'voyager-core') {
            return;
        }

        try {
            if (!Schema::hasTable('plugins')) {
                session()->flash('error', 'Plugins database table does not exist. Run migrations first.');
                return;
            }

            $pluginRecord = DB::table('plugins')->where('name', $name)->first();

            if ($pluginRecord) {
                $newStatus = $pluginRecord->is_active ? 0 : 1;
                DB::table('plugins')->where('name', $name)->update([
                    'is_active' => $newStatus,
                    'updated_at' => now(),
                ]);
            } else {
                DB::table('plugins')->insert([
                    'name' => $name,
                    'is_active' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $this->loadPlugins();
            session()->flash('message', "Plugin status successfully updated.");
        } catch (\Exception $e) {
            session()->flash('error', "Failed to toggle plugin: " . $e->getMessage());
        }
    }

    public function render(): mixed
    {
        return view('voyager::components.⚡plugins-manager.plugins-manager');
    }
};
