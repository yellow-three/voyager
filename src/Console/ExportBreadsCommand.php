<?php

namespace YellowThree\Voyager\Console;

use Illuminate\Console\Command;
use YellowThree\Voyager\Bread\BreadManager;
use YellowThree\Voyager\Bread\Sources\DatabaseBreadSource;

class ExportBreadsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'voyager:export-breads {--slug= : Export a specific BREAD by its slug}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Export database BREAD definitions to JSON files';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $manager = app(BreadManager::class);
        $slug = $this->option('slug');

        $this->info("Starting BREAD export process...");

        if ($slug) {
            $dbSource = app(DatabaseBreadSource::class);
            $bread = $dbSource->find($slug);

            if (!$bread) {
                $this->error("BREAD with slug '{$slug}' not found in database.");
                return 1;
            }

            $manager->save($bread);
            $this->info("BREAD '{$slug}' successfully exported to JSON.");
        } else {
            $manager->exportAllToJson();
            $this->info("All database BREAD configurations successfully exported to JSON files.");
        }

        return 0;
    }
}
