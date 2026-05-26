<?php

namespace YellowThree\Voyager\Upgrade\Steps;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use YellowThree\Voyager\Upgrade\Contracts\StepContract;

class CheckDatabaseStep implements StepContract
{
    public function getLabel(): string
    {
        return 'Checking database connectivity';
    }

    public function execute(Command $command): int
    {
        try {
            DB::connection()->getPdo();
            $command->info("Database connection is operational.");
            return 0;
        } catch (\Exception $e) {
            $command->error("Database connection failed: " . $e->getMessage());
            return 1;
        }
    }
}
