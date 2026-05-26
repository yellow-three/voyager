<?php

namespace YellowThree\Voyager\Plugins;

class ModelRegistry
{
    /**
     * Custom models registry map.
     *
     * @var array<string, string>
     */
    protected array $models = [];

    /**
     * Register or override a model class under an alias.
     *
     * @param string $alias (e.g. 'User', 'Role')
     * @param string $class (e.g. 'App\Models\User')
     * @return void
     */
    public function register(string $alias, string $class): void
    {
        $this->models[$alias] = $class;
    }

    /**
     * Resolve the registered model class name by its alias.
     * Fallbacks to standard Voyager models if not overridden.
     *
     * @param string $alias
     * @return string|null
     */
    public function get(string $alias): ?string
    {
        if (isset($this->models[$alias])) {
            return $this->models[$alias];
        }

        // Standard core model names mappings fallback
        $coreClass = 'YellowThree\\Voyager\\Models\\' . $alias;
        if (class_exists($coreClass)) {
            return $coreClass;
        }

        return null;
    }

    /**
     * Get all registered models.
     *
     * @return array<string, string>
     */
    public function all(): array
    {
        return $this->models;
    }
}
