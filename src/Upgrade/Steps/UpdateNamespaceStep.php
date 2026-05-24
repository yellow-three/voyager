<?php

namespace YellowThree\Voyager\Upgrade\Steps;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use YellowThree\Voyager\Upgrade\Contracts\StepContract;

class UpdateNamespaceStep implements StepContract
{
    public function getLabel(): string
    {
        return 'Updating database namespaces TCG → YellowThree';
    }

    public function execute(Command $command): int
    {
        try {
            if (Schema::hasTable('data_types')) {
                $affected = DB::table('data_types')
                    ->where('model_name', 'like', 'TCG\\Voyager\\%')
                    ->update([
                        'model_name' => DB::raw("REPLACE(model_name, 'TCG\\\\Voyager', 'YellowThree\\\\Voyager')")
                    ]);
                $command->info("Updated {$affected} model namespace mappings.");
            } else {
                $command->warn("data_types table not found, skipping namespaces update.");
            }

            return 0;
        } catch (\Exception $e) {
            $command->error("Failed updating namespaces: " . $e->getMessage());
            return 1;
        }
    }
}
