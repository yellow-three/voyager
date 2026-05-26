<?php

namespace YellowThree\Voyager\Upgrade\Steps;

use Illuminate\Console\Command;
use YellowThree\Voyager\Bread\BreadManager;
use YellowThree\Voyager\Upgrade\Contracts\StepContract;

class ExportBreadsStep implements StepContract
{
    public function getLabel(): string
    {
        return 'Exporting DB BREAD definitions to JSON';
    }

    public function execute(Command $command): int
    {
        try {
            $manager = app(BreadManager::class);
            $manager->exportAllToJson();
            $command->info("Export completed. Definitions saved to 'storage/voyager/breads/'.");
            return 0;
        } catch (\Exception $e) {
            $command->error("BREAD export failed: " . $e->getMessage());
            return 1;
        }
    }
}
