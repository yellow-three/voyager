<?php

namespace YellowThree\Voyager\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use YellowThree\Voyager\Bread\BreadManager;

class UpgradeCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'voyager:upgrade';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Upgrade Voyager v2 installation to Voyager v3';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info("=== Starting Voyager v3 Upgrade Process ===");

        // Step 1: Backup config
        $this->info("Step 1/6: Backing up configurations...");
        if (File::exists(config_path('voyager.php'))) {
            File::copy(config_path('voyager.php'), config_path('voyager.backup.php'));
            $this->info("Backup successfully saved to 'config/voyager.backup.php'.");
        } else {
            $this->warn("No existing configuration detected to backup.");
        }

        // Step 2: Check database connection
        $this->info("Step 2/6: Checking database connectivity...");
        try {
            DB::connection()->getPdo();
            $this->info("Database connection is operational.");
        } catch (\Exception $e) {
            $this->error("Database connection failed: " . $e->getMessage());
            return 1;
        }

        // Step 3: Run new migrations
        $this->info("Step 3/6: Running migrations...");
        try {
            Artisan::call('migrate', ['--force' => true]);
            $this->info("Migrations ran successfully.");
        } catch (\Exception $e) {
            $this->error("Failed running migrations: " . $e->getMessage());
            return 1;
        }

        // Step 4: Update TCG to YellowThree Namespace
        $this->info("Step 4/6: Updating database namespaces...");
        try {
            if (Schema::hasTable('data_types')) {
                $affected = DB::table('data_types')
                  ->where('model_name', 'like', 'TCG\\Voyager\\%')
                  ->update([
                      'model_name' => DB::raw("REPLACE(model_name, 'TCG\\\\Voyager', 'YellowThree\\\\Voyager')")
                  ]);
                $this->info("Updated {$affected} model namespace mappings.");
            } else {
                $this->warn("data_types table not found, skipping namespaces update.");
            }
        } catch (\Exception $e) {
            $this->error("Failed updating namespaces: " . $e->getMessage());
        }

        // Step 5: Export DB BREAD to JSON files
        $this->info("Step 5/6: Exporting BREAD schemas to JSON...");
        try {
            $manager = app(BreadManager::class);
            $manager->exportAllToJson();
            $this->info("Export completed. Definitions saved to 'storage/voyager/breads/'.");
        } catch (\Exception $e) {
            $this->error("BREAD export failed: " . $e->getMessage());
        }

        // Step 6: Deprecations scans and audits
        $this->info("Step 6/6: Checking deprecation audits...");
        if (is_dir(app_path('FormFields'))) {
            $this->warn("[Warning] Folder 'app/FormFields' detected! Auto-discovery is deprecated in v3. Use Voyager v3 plugins instead.");
        }
        $this->info("Upgrade completed successfully!");

        return 0;
    }
}
