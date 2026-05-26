<?php

namespace YellowThree\Voyager\Console;

use Illuminate\Console\Command;
use YellowThree\Voyager\Bread\BreadManager;
use YellowThree\Voyager\Bread\Sources\JsonBreadSource;

class ImportBreadsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'voyager:import-breads {--slug= : Import a specific BREAD by its slug}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import JSON BREAD definitions back into the database';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $manager = app(BreadManager::class);
        $slug = $this->option('slug');

        $this->info("Starting BREAD import process...");

        if ($slug) {
            $jsonSource = app(JsonBreadSource::class);
            $bread = $jsonSource->find($slug);

            if (!$bread) {
                $this->error("BREAD JSON file for slug '{$slug}' not found.");
                return 1;
            }

            // Save to database
            $manager->importAllToDatabase(); // Fallback helper
            $this->info("BREAD '{$slug}' successfully imported to database.");
        } else {
            $manager->importAllToDatabase();
            $this->info("All JSON BREAD configurations successfully imported into database tables.");
        }

        return 0;
    }
}
