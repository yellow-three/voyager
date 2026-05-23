<?php

namespace YellowThree\Voyager\Plugins;

abstract class BasePlugin
{
    abstract public function name(): string;
    abstract public function version(): string;

    public function label(): string
    {
        return $this->name();
    }

    public function description(): string
    {
        return '';
    }

    public function author(): string
    {
        return '';
    }

    public function menuItems(): array
    {
        return [];
    }

    public function routes(): void
    {
        // Add custom routes
    }

    public function widgets(): array
    {
        return [];
    }

    public function actions(): array
    {
        return [];
    }

    public function migrations(): array
    {
        return [];
    }

    public function boot(): void
    {
        // Custom boot scripts
    }
}
