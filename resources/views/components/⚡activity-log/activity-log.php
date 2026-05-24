<?php

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

new class extends Component {
    use WithPagination;

    public string $search = '';
    public string $actionFilter = '';
    public ?array $activeLogDetails = null;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingActionFilter(): void
    {
        $this->resetPage();
    }

    public function showDetails(int $id): void
    {
        try {
            $log = DB::table('activity_logs')->where('id', $id)->first();
            if ($log) {
                $this->activeLogDetails = [
                    'id' => $log->id,
                    'action' => $log->action,
                    'model_type' => $log->model_type,
                    'old_values' => json_decode($log->old_values, true) ?: [],
                    'new_values' => json_decode($log->new_values, true) ?: [],
                    'ip' => $log->ip,
                    'user_agent' => $log->user_agent,
                    'created_at' => $log->created_at,
                ];
            }
        } catch (\Exception $e) {}
    }

    public function getLogsProperty()
    {
        try {
            if (!Schema::hasTable('activity_logs')) {
                return collect()->paginate(10);
            }

            $query = DB::table('activity_logs')
                ->leftJoin('users', 'activity_logs.user_id', '=', 'users.id')
                ->select('activity_logs.*', 'users.name as user_name')
                ->orderBy('activity_logs.created_at', 'DESC');

            if ($this->search) {
                $query->where(function($q) {
                    $q->where('users.name', 'like', '%' . $this->search . '%')
                      ->orWhere('activity_logs.model_type', 'like', '%' . $this->search . '%')
                      ->orWhere('activity_logs.ip', 'like', '%' . $this->search . '%');
                });
            }

            if ($this->actionFilter) {
                $query->where('activity_logs.action', $this->actionFilter);
            }

            return $query->paginate(10);
        } catch (\Exception $e) {
            return collect()->paginate(10);
        }
    }

    public function render(): mixed
    {
        return view('voyager::components.⚡activity-log.activity-log');
    }
};
