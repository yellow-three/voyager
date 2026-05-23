<?php

namespace YellowThree\Voyager\Themes;

use YellowThree\Voyager\Themes\Contracts\Theme;

class ThemeManager
{
    /**
     * Registered themes map.
     *
     * @var Theme[]
     */
    protected array $themes = [];

    /**
     * Currently active theme name.
     *
     * @var string|null
     */
    protected ?string $activeTheme = null;

    /**
     * Register a new theme.
     *
     * @param Theme $theme
     * @return void
     */
    public function register(Theme $theme): void
    {
        $this->themes[$theme->name()] = $theme;
    }

    /**
     * Set the currently active theme.
     *
     * @param string $name
     * @return void
     */
    public function setActive(string $name): void
    {
        $this->activeTheme = $name;
    }

    /**
     * Get the active Theme instance.
     *
     * @return Theme|null
     */
    public function getActiveTheme(): ?Theme
    {
        if ($this->activeTheme && isset($this->themes[$this->activeTheme])) {
            return $this->themes[$this->activeTheme];
        }

        // Return first registered theme if any, or null
        return !empty($this->themes) ? reset($this->themes) : null;
    }

    /**
     * Get all registered themes.
     *
     * @return Theme[]
     */
    public function all(): array
    {
        return $this->themes;
    }
}
