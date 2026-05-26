<?php

namespace YellowThree\Voyager\Plugins;

use Illuminate\Support\Collection;
use YellowThree\Voyager\Plugins\Contracts\AuthenticationPlugin;
use YellowThree\Voyager\Plugins\Contracts\AuthorizationPlugin;
use YellowThree\Voyager\Plugins\Contracts\FormfieldPlugin;
use YellowThree\Voyager\Plugins\Contracts\MenuPlugin;
use YellowThree\Voyager\Plugins\Contracts\WidgetPlugin;
use YellowThree\Voyager\Plugins\Contracts\FilterPlugin;

class PluginManager
{
    /**
     * Registered plugins array.
     *
     * @var BasePlugin[]
     */
    protected array $plugins = [];

    /**
     * Register a new plugin and boot it.
     *
     * @param BasePlugin $plugin
     * @return void
     */
    public function register(BasePlugin $plugin): void
    {
        $this->plugins[$plugin->name()] = $plugin;
        $plugin->boot();
    }

    /**
     * Get all registered plugins.
     *
     * @return BasePlugin[]
     */
    public function all(): array
    {
        return $this->plugins;
    }

    /**
     * Get registered FormField handler classes from FormfieldPlugins.
     *
     * @return string[]
     */
    public function getFormFields(): array
    {
        return collect($this->plugins)
            ->filter(fn($p) => $p instanceof FormfieldPlugin)
            ->flatMap(fn($p) => $p->formFields())
            ->all();
    }

    /**
     * Get the active custom AuthenticationPlugin.
     *
     * @return AuthenticationPlugin|null
     */
    public function getAuthPlugin(): ?AuthenticationPlugin
    {
        return collect($this->plugins)
            ->first(fn($p) => $p instanceof AuthenticationPlugin);
    }

    /**
     * Get the active custom AuthorizationPlugin.
     *
     * @return AuthorizationPlugin|null
     */
    public function getAuthorizationPlugin(): ?AuthorizationPlugin
    {
        return collect($this->plugins)
            ->first(fn($p) => $p instanceof AuthorizationPlugin);
    }

    /**
     * Get all dynamically injected menu items.
     *
     * @return array
     */
    public function getMenuItems(): array
    {
        return collect($this->plugins)
            ->filter(fn($p) => $p instanceof MenuPlugin)
            ->flatMap(fn($p) => $p->menuItems())
            ->all();
    }

    /**
     * Get all dynamically injected dashboard widgets.
     *
     * @return array
     */
    public function getWidgets(): array
    {
        return collect($this->plugins)
            ->filter(fn($p) => $p instanceof WidgetPlugin)
            ->flatMap(fn($p) => $p->widgets())
            ->all();
    }
}
