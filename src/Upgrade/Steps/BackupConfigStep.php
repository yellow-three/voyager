<?php

namespace YellowThree\Voyager\Upgrade\Steps;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use YellowThree\Voyager\Upgrade\Contracts\StepContract;

class BackupConfigStep implements StepContract
{
    public function getLabel(): string
    {
        return 'Backing up configurations';
    }

    public function execute(Command $command): int
    {
        if (File::exists(config_path('voyager.php'))) {
            File::copy(config_path('voyager.php'), config_path('voyager.backup.php'));
            $command->info("Backup saved to 'config/voyager.backup.php'.");
        } else {
            $command->warn("No existing configuration detected to backup.");
        }

        return 0;
    }
}
