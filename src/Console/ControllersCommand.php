<?php

namespace YellowThree\Voyager\Console;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputOption;

class ControllersCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'voyager:controllers';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Publish all the controllers from Voyager.';

    /**
     * The Filesystem instance.
     *
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * Filename of stub-file.
     *
     * @var string
     */
    protected $stub = 'controller.stub';

    /**
     * Create a new command instance.
     *
     * @param Filesystem $filesystem
     */
    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;

        parent::__construct();
    }

    public function fire()
    {
        return $this->handle();
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $stub = $this->getStub();
        $files = $this->filesystem->files(base_path('vendor/tcg/voyager/src/Http/Controllers'));
        $namespace = config('voyager.controllers.namespace', 'YellowThree\\Voyager\\Http\\Controllers');
        $files = $this->filesystem->files(base_path('vendor/yellow-three/voyager/src/Http/Controllers'));
        $namespace = config('voyager.controllers.namespace', 'YellowThree\\Voyager\\Http\\Controllers');
            'YellowThree\\Voyager\\Http\\Controllers\\'.$class,
        return $this->filesystem->get(base_path('vendor/yellow-three/voyager/stubs/'.$this->stub));
        $namespace = config('voyager.controllers.namespace', 'YellowThree\\Voyager\\Http\\Controllers');

        $content = str_replace(
            'DummyNamespace',
            $namespace,
            $stub
        );

        $content = str_replace(
            'FullBaseDummyClass',
            'TCG\\Voyager\\Http\\Controllers\\'.$class,
            $content
        );

        $content = str_replace(
            'BaseDummyClass',
            'Base'.$class,
            $content
        );

        $content = str_replace(
            'DummyClass',
            $class,
            $content
        );

        return $content;
    }

    /**
     * Get command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['force', 'f', InputOption::VALUE_NONE, 'Overwrite existing controller files'],
        ];
    }
}
