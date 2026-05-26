<?php

use Livewire\Component;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use YellowThree\Voyager\Themes\ThemeManager;

new class extends Component {
    public array $themes = [];

    public function mount(): void
    {
        $this->loadThemes();
    }

    public function loadThemes(): void
    {
        $manager = app(ThemeManager::class);
        $allThemes = $manager->all();

        $this->themes = [];

        $activeThemeName = null;
        if (Schema::hasTable('themes')) {
            $activeThemeRecord = DB::table('themes')
                ->where('is_active', 1)
                ->first();
            if ($activeThemeRecord) {
                $activeThemeName = $activeThemeRecord->name;
            }
        }

        foreach ($allThemes as $theme) {
            $isActive = $theme->name() === $activeThemeName;

            $this->themes[] = [
                'name' => $theme->name(),
                'version' => $theme->version(),
                'colors' => $theme->colors(),
                'fonts' => $theme->fonts(),
                'dark_mode' => $theme->darkMode(),
                'is_active' => $isActive,
            ];
        }
    }

    public function activateTheme(string $name): void
    {
        try {
            if (!Schema::hasTable('themes')) {
                session()->flash('error', 'Themes database table does not exist. Run migrations first.');
                return;
            }

            DB::table('themes')->update([
                'is_active' => 0,
                'updated_at' => now(),
            ]);

            $themeRecord = DB::table('themes')->where('name', $name)->first();

            if ($themeRecord) {
                DB::table('themes')->where('name', $name)->update([
                    'is_active' => 1,
                    'updated_at' => now(),
                ]);
            } else {
                DB::table('themes')->insert([
                    'name' => $name,
                    'is_active' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            app(ThemeManager::class)->setActive($name);

            $this->loadThemes();
            session()->flash('message', "Theme successfully activated.");
        } catch (\Exception $e) {
            session()->flash('error', "Failed to activate theme: " . $e->getMessage());
        }
    }

    public function render(): mixed
    {
        return view('voyager::components.⚡themes-manager.themes-manager');
    }
};
