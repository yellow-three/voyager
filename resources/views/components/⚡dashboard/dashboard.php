<?php

use Livewire\Component;
use YellowThree\Voyager\Facades\Voyager;
use YellowThree\Voyager\Plugins\PluginManager;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

new class extends Component {
    public array $metrics = [];
    public array $activities = [];
    public array $widgets = [];

    public function mount(): void
    {
        $this->loadMetrics();
        $this->loadActivities();
        $this->loadPluginWidgets();
    }

    protected function loadMetrics(): void
    {
        $this->metrics = [
            [
                'title' => __('voyager::generic.users'),
                'count' => $this->getModelCount('User'),
                'icon' => 'users',
                'color' => 'from-blue-500 to-indigo-600',
                'route' => 'voyager.users.index',
            ],
            [
                'title' => __('voyager::generic.roles'),
                'count' => $this->getModelCount('Role'),
                'icon' => 'shield',
                'color' => 'from-emerald-500 to-teal-600',
                'route' => 'voyager.roles.index',
            ],
            [
                'title' => __('voyager::generic.settings'),
                'count' => $this->getModelCount('Setting'),
                'icon' => 'settings',
                'color' => 'from-amber-500 to-orange-600',
                'route' => 'voyager.settings.index',
            ],
        ];
    }

    protected function loadActivities(): void
    {
        try {
            if (DB::getSchemaBuilder()->hasTable('activity_logs')) {
                $this->activities = DB::table('activity_logs')
                    ->orderBy('created_at', 'desc')
                    ->limit(5)
                    ->get()
                    ->map(fn($log) => [
                        'description' => $log->action . ' ' . class_basename($log->model_type),
                        'time' => \Carbon\Carbon::parse($log->created_at)->diffForHumans(),
                        'type' => $log->action === 'deleted' ? 'danger' : 'info',
                    ])
                    ->toArray();
            }
        } catch (\Exception $e) {
            // fallback
        }

        if (empty($this->activities)) {
            $this->activities = [
                ['description' => 'Dashboard loaded', 'time' => 'Just now', 'type' => 'info'],
            ];
        }
    }

    protected function loadPluginWidgets(): void
    {
        try {
            $manager = app(PluginManager::class);
            $this->widgets = $manager->getWidgets();
        } catch (\Exception $e) {
            $this->widgets = [];
        }
    }

    private function getModelCount(string $modelName): int
    {
        try {
            $class = Voyager::model($modelName);
            return $class ? $class::count() : 0;
        } catch (\Exception $e) {
            return 0;
        }
    }

    public function render(): mixed
    {
        return view('voyager::components.⚡dashboard.dashboard');
    }
};
