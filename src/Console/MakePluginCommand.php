<?php

namespace YellowThree\Voyager\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MakePluginCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'voyager:make:plugin {name : The name of the plugin}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a new Voyager v3 first-party plugin skeleton';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $name = $this->argument('name');
        $slug = Str::slug($name);
        $studly = Str::studly($name);

        $pluginPath = base_path("plugins/{$slug}");

        if (File::exists($pluginPath)) {
            $this->error("Plugin directory 'plugins/{$slug}' already exists!");
            return 1;
        }

        $this->info("Creating skeleton directories for plugin '{$name}'...");

        // Create folders
        File::makeDirectory("{$pluginPath}/src/Models", 0755, true, true);
        File::makeDirectory("{$pluginPath}/src/FormFields", 0755, true, true);
        File::makeDirectory("{$pluginPath}/routes", 0755, true, true);
        File::makeDirectory("{$pluginPath}/resources/views/components", 0755, true, true);
        File::makeDirectory("{$pluginPath}/migrations", 0755, true, true);

        // Write composer.json
        $composerJson = <<<JSON
{
    "name": "yellow-three/voyager-{$slug}",
    "description": "Voyager v3 custom plugin for {$name}",
    "keywords": ["laravel", "voyager-plugin"],
    "license": "MIT",
    "require": {
        "php": "^8.3|^8.4|^8.5"
    },
    "autoload": {
        "psr-4": {
            "YellowThree\\\\Voyager{$studly}\\\\": "src/"
        }
    },
    "extra": {
        "laravel": {
            "providers": [
                "YellowThree\\\\Voyager{$studly}\\\\{$studly}ServiceProvider"
            ]
        }
    }
}
JSON;
        File::put("{$pluginPath}/composer.json", $composerJson);

        // Write ServiceProvider
        $serviceProviderPhp = <<<PHP
<?php

namespace YellowThree\Voyager{$studly};

use Illuminate\Support\ServiceProvider;
use YellowThree\Voyager\Plugins\PluginManager;

class {$studly}ServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        \$this->loadViewsFrom(__DIR__.'/../resources/views', 'voyager-{$slug}');
        \$this->loadMigrationsFrom(__DIR__.'/../migrations');
        \$this->loadRoutesFrom(__DIR__.'/../routes/web.php');

        // Register custom plugin to PluginManager
        if (class_exists(PluginManager::class)) {
            app(PluginManager::class)->register(new {$studly}Plugin());
        }
    }
}
PHP;
        File::put("{$pluginPath}/src/{$studly}ServiceProvider.php", $serviceProviderPhp);

        // Write Plugin base class
        $pluginPhp = <<<PHP
<?php

namespace YellowThree\Voyager{$studly};

use YellowThree\Voyager\Plugins\BasePlugin;

class {$studly}Plugin extends BasePlugin
{
    public function name(): string
    {
        return 'voyager-{$slug}';
    }

    public function version(): string
    {
        return '1.0.0';
    }

    public function label(): string
    {
        return '{$name} Plugin';
    }

    public function description(): string
    {
        return 'Voyager v3 custom extension hook for {$name}.';
    }

    public function author(): string
    {
        return 'Custom Developer';
    }
}
PHP;
        File::put("{$pluginPath}/src/{$studly}Plugin.php", $pluginPhp);

        // Write empty route file
        $routesPhp = <<<PHP
<?php

use Illuminate\Support\Facades\Route;

Route::group([
    'prefix' => 'admin/voyager-{$slug}',
    'middleware' => ['web', 'admin.user']
], function () {
    // Custom plugin routes
});
PHP;
        File::put("{$pluginPath}/routes/web.php", $routesPhp);

        $this->info("Plugin '{$name}' skeleton successfully generated under 'plugins/{$slug}/'!");

        return 0;
    }
}
