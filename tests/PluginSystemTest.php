<?php

use YellowThree\Voyager\Plugins\PluginManager;
use YellowThree\Voyager\Plugins\BasePlugin;
use YellowThree\Voyager\Plugins\Contracts\MenuPlugin;
use YellowThree\Voyager\Plugins\Contracts\FormfieldPlugin;
use YellowThree\Voyager\Plugins\Contracts\WidgetPlugin;
use YellowThree\Voyager\Plugins\VoyagerCorePlugin;

// ─── Helpers ────────────────────────────────────────────────────────────────

/**
 * A minimal plugin stub for testing.
 */
class StubPlugin extends BasePlugin implements MenuPlugin
{
    public function name(): string    { return 'stub-plugin'; }
    public function version(): string { return '0.0.1'; }
    public function menuItems(): array
    {
        return [['title' => 'Stub', 'route' => 'voyager.dashboard', 'icon' => 'circle']];
    }
}

/**
 * A stub plugin that also registers a custom FormField handler.
 */
class FormFieldStubPlugin extends BasePlugin implements FormfieldPlugin
{
    public function name(): string    { return 'formfield-stub'; }
    public function version(): string { return '0.0.1'; }
    public function formFields(): array
    {
        return ['StubHandler'];   // symbolic class name for testing
    }
}

/**
 * A stub widget plugin.
 */
class WidgetStubPlugin extends BasePlugin implements WidgetPlugin
{
    public function name(): string    { return 'widget-stub'; }
    public function version(): string { return '0.0.1'; }
    public function widgets(): array
    {
        return [['view' => 'stub::widget', 'data' => [], 'width' => 'w-full']];
    }
}

// ─── Tests ────────────────────────────────────────────────────────────────────

describe('PluginManager', function () {

    beforeEach(function () {
        $this->manager = new PluginManager();
    });

    it('can register a plugin', function () {
        $plugin = new StubPlugin();
        $this->manager->register($plugin);

        expect($this->manager->all())->toHaveKey('stub-plugin');
    });

    it('can retrieve all registered plugins', function () {
        $this->manager->register(new StubPlugin());
        $this->manager->register(new FormFieldStubPlugin());

        expect($this->manager->all())->toHaveCount(2);
    });

    it('returns menu items from MenuPlugin plugins', function () {
        $this->manager->register(new StubPlugin());

        $items = $this->manager->getMenuItems();

        expect($items)->toBeArray()
            ->and($items[0]['title'])->toBe('Stub');
    });

    it('returns form fields from FormfieldPlugin plugins', function () {
        $this->manager->register(new FormFieldStubPlugin());

        $fields = $this->manager->getFormFields();

        expect($fields)->toContain('StubHandler');
    });

    it('returns widgets from WidgetPlugin plugins', function () {
        $this->manager->register(new WidgetStubPlugin());

        $widgets = $this->manager->getWidgets();

        expect($widgets)->toBeArray()
            ->and($widgets[0]['view'])->toBe('stub::widget');
    });

    it('does not duplicate plugins registered with the same name', function () {
        $this->manager->register(new StubPlugin());
        $this->manager->register(new StubPlugin());  // register same name twice

        expect($this->manager->all())->toHaveCount(1);
    });

});

describe('BasePlugin defaults', function () {

    it('has sensible default values', function () {
        $plugin = new StubPlugin();

        expect($plugin->label())->toBe('stub-plugin')
            ->and($plugin->description())->toBe('')
            ->and($plugin->author())->toBe('')
            ->and($plugin->widgets())->toBe([])
            ->and($plugin->actions())->toBe([])
            ->and($plugin->migrations())->toBe([]);
    });

});

describe('VoyagerCorePlugin', function () {

    it('registers built-in form fields', function () {
        $plugin = new VoyagerCorePlugin();
        $fields = $plugin->formFields();

        expect($fields)->toBeArray()
            ->and(count($fields))->toBeGreaterThan(10);
    });

    it('registers core menu items', function () {
        $plugin = new VoyagerCorePlugin();
        $items  = $plugin->menuItems();

        expect($items)->toBeArray()
            ->and(count($items))->toBeGreaterThan(0);

        $routes = array_column($items, 'route');
        expect($routes)->toContain('voyager.dashboard');
    });

    it('has correct name and version', function () {
        $plugin = new VoyagerCorePlugin();

        expect($plugin->name())->toBe('voyager-core')
            ->and($plugin->version())->toStartWith('3.');
    });

});
