<?php

use Livewire\Component;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

new class extends Component {
    public int $currentStep = 1;
    public array $steps = [];

    public function mount(): void
    {
        $this->steps = [
            1 => ['title' => 'Backup Config', 'status' => 'pending', 'desc' => 'Safeguard settings configurations'],
            2 => ['title' => 'Check Schema', 'status' => 'pending', 'desc' => 'Validate database connectivity'],
            3 => ['title' => 'Run Migrations', 'status' => 'pending', 'desc' => 'Execute Voyager v3 schemas'],
            4 => ['title' => 'Namespace Updates', 'status' => 'pending', 'desc' => 'Rename namespaces from TCG to YellowThree'],
            5 => ['title' => 'Export BREADs', 'status' => 'pending', 'desc' => 'Convert BREAD tables into runtime JSON definitions'],
            6 => ['title' => 'Deprecation Audits', 'status' => 'pending', 'desc' => 'Analyze custom fields and shims'],
        ];
    }

    public function startUpgrade(): void
    {
        $this->currentStep = 1;
        $this->runStep(1);
    }

    public function runStep(int $stepId): void
    {
        $this->steps[$stepId]['status'] = 'running';

        try {
            switch ($stepId) {
                case 1:
                    if (config('voyager')) {
                        $this->steps[1]['status'] = 'success';
                        $this->currentStep = 2;
                        $this->runStep(2);
                    } else {
                        throw new \Exception('No active Voyager config detected.');
                    }
                    break;

                case 2:
                    DB::connection()->getPdo();
                    $this->steps[2]['status'] = 'success';
                    $this->currentStep = 3;
                    $this->runStep(3);
                    break;

                case 3:
                    Artisan::call('migrate', ['--force' => true]);
                    $this->steps[3]['status'] = 'success';
                    $this->currentStep = 4;
                    $this->runStep(4);
                    break;

                case 4:
                    if (Schema::hasTable('data_types')) {
                        DB::table('data_types')
                          ->where('model_name', 'like', 'TCG\\Voyager\\%')
                          ->update([
                              'model_name' => DB::raw("REPLACE(model_name, 'TCG\\\\Voyager', 'YellowThree\\\\Voyager')")
                          ]);
                    }
                    $this->steps[4]['status'] = 'success';
                    $this->currentStep = 5;
                    $this->runStep(5);
                    break;

                case 5:
                    try {
                        Artisan::call('voyager:export-breads');
                    } catch (\Exception $e) {
                        //
                    }
                    $this->steps[5]['status'] = 'success';
                    $this->currentStep = 6;
                    $this->runStep(6);
                    break;

                case 6:
                    $this->steps[6]['status'] = 'success';
                    session()->flash('message', 'Voyager v3 Upgrade Wizard completed successfully!');
                    break;
            }
        } catch (\Exception $e) {
            $this->steps[$stepId]['status'] = 'failed';
            session()->flash('error', "Upgrade failed at Step {$stepId}: " . $e->getMessage());
        }
    }

    public function render(): mixed
    {
        return view('voyager::components.⚡upgrade-wizard.upgrade-wizard');
    }
};
