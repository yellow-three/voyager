<?php

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;

new class extends Component {
    use WithPagination;

    public string $search = '';
    public ?array $activeJobDetails = null;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function showDetails(int $id): void
    {
        try {
            $job = DB::table('failed_jobs')->where('id', $id)->first();
            if ($job) {
                $payload = json_decode($job->payload, true) ?: [];
                $this->activeJobDetails = [
                    'id' => $job->id,
                    'connection' => $job->connection,
                    'queue' => $job->queue,
                    'failed_at' => $job->failed_at,
                    'exception' => $job->exception,
                    'display_name' => $payload['displayName'] ?? ($payload['job'] ?? 'Unknown Job'),
                ];
            }
        } catch (\Exception $e) {}
    }

    public function retryJob(int $id): void
    {
        try {
            Artisan::call('queue:retry', ['id' => [$id]]);
            session()->flash('message', "Retried job #{$id} successfully.");
            $this->activeJobDetails = null;
        } catch (\Exception $e) {
            session()->flash('error', "Failed to retry job: " . $e->getMessage());
        }
    }

    public function forgetJob(int $id): void
    {
        try {
            Artisan::call('queue:forget', ['id' => $id]);
            session()->flash('message', "Job #{$id} forgotten successfully.");
            $this->activeJobDetails = null;
        } catch (\Exception $e) {
            session()->flash('error', "Failed to forget job: " . $e->getMessage());
        }
    }

    public function retryAll(): void
    {
        try {
            Artisan::call('queue:retry', ['id' => ['all']]);
            session()->flash('message', "All failed jobs retried.");
        } catch (\Exception $e) {
            session()->flash('error', "Failed to retry jobs: " . $e->getMessage());
        }
    }

    public function forgetAll(): void
    {
        try {
            Artisan::call('queue:flush');
            session()->flash('message', "All failed jobs forgotten.");
        } catch (\Exception $e) {
            session()->flash('error', "Failed to forget jobs: " . $e->getMessage());
        }
    }

    public function getJobsProperty()
    {
        try {
            if (!Schema::hasTable('failed_jobs')) {
                return collect()->paginate(10);
            }

            $query = DB::table('failed_jobs')->orderBy('failed_at', 'DESC');

            if ($this->search) {
                $query->where(function($q) {
                    $q->where('queue', 'like', '%' . $this->search . '%')
                      ->orWhere('payload', 'like', '%' . $this->search . '%');
                });
            }

            return $query->paginate(10);
        } catch (\Exception $e) {
            return collect()->paginate(10);
        }
    }

    public function render(): mixed
    {
        return view('voyager::components.⚡queue-manager.queue-manager');
    }
};
