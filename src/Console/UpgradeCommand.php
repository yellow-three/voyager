<?php

namespace YellowThree\Voyager\Console;

use Illuminate\Console\Command;
use YellowThree\Voyager\Upgrade\Contracts\StepContract;
use YellowThree\Voyager\Upgrade\Steps\BackupConfigStep;
use YellowThree\Voyager\Upgrade\Steps\CheckDatabaseStep;
use YellowThree\Voyager\Upgrade\Steps\RunMigrationsStep;
use YellowThree\Voyager\Upgrade\Steps\UpdateNamespaceStep;
use YellowThree\Voyager\Upgrade\Steps\ExportBreadsStep;
use YellowThree\Voyager\Upgrade\Steps\DeprecationAuditStep;

class UpgradeCommand extends Command
{
    protected $signature = 'voyager:upgrade';

    protected $description = 'Upgrade Voyager v2 installation to Voyager v3';

    /** @var array<int, StepContract> */
    protected array $steps;

    public function __construct()
    {
        parent::__construct();

        $this->steps = [
            new BackupConfigStep(),
            new CheckDatabaseStep(),
            new RunMigrationsStep(),
            new UpdateNamespaceStep(),
            new ExportBreadsStep(),
            new DeprecationAuditStep(),
        ];
    }

    public function handle(): int
    {
        $this->info("=== Starting Voyager v3 Upgrade Process ===\n");

        foreach ($this->steps as $i => $step) {
            $stepNumber = $i + 1;
            $total = count($this->steps);

            $this->info("Step {$stepNumber}/{$total}: {$step->getLabel()}...");

            $result = $step->execute($this);

            if ($result !== 0) {
                $this->error("Step {$stepNumber} failed. Aborting.");
                return 1;
            }

            $this->newLine();
        }

        $this->info("Upgrade completed successfully!");

        return 0;
    }
}
