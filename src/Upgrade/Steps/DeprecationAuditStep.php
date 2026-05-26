<?php

namespace YellowThree\Voyager\Upgrade\Steps;

use Illuminate\Console\Command;
use YellowThree\Voyager\Upgrade\Contracts\StepContract;

class DeprecationAuditStep implements StepContract
{
    public function getLabel(): string
    {
        return 'Checking deprecation audits';
    }

    public function execute(Command $command): int
    {
        if (is_dir(app_path('FormFields'))) {
            $command->warn("[Warning] Folder 'app/FormFields' detected! Auto-discovery is deprecated in v3. Use Voyager v3 plugins instead.");
        }

        return 0;
    }
}
