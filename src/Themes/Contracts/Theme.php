<?php

namespace YellowThree\Voyager\Themes\Contracts;

interface Theme
{
    public function name(): string;
    public function version(): string;

    /**
     * Tailwind @theme override CSS variables array.
     *
     * @return array
     */
    public function colors(): array;

    /**
     * Optional layout view override map.
     *
     * @return array
     */
    public function layoutOverrides(): array;

    /**
     * Optional custom CSS asset path.
     *
     * @return string|null
     */
    public function css(): ?string;

    /**
     * Font definitions array.
     *
     * @return array
     */
    public function fonts(): array;

    /**
     * Is dark mode enabled.
     *
     * @return bool
     */
    public function darkMode(): bool;
}
