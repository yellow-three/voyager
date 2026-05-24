<?php

namespace YellowThree\Voyager\Upgrade\Steps;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use YellowThree\Voyager\Upgrade\Contracts\StepContract;

class RunMigrationsStep implements StepContract
{
    public function getLabel(): string
    {
        return 'Running migrations';
    }

    public function execute(Command $command): int
    {
        try {
            Artisan::call('migrate', ['--force' => true]);
            $command->info("Migrations ran successfully.");
            return 0;
        } catch (\Exception $e) {
            $command->error("Failed running migrations: " . $e->getMessage());
            return 1;
        }
    }
}
