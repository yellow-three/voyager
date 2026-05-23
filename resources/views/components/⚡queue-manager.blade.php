@php
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
};
@endphp

<div class="space-y-6" x-data="{ showModal: @entangle('activeJobDetails').live }">
    <!-- Header -->
    <div class="flex items-center justify-between border-b border-gray-100 pb-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 tracking-tight flex items-center gap-2">
                <i class="voyager-paperplane text-primary"></i>
                Queue Manager
            </h1>
            <p class="text-sm text-gray-500 mt-1">Audit, retry, or purge failed background database jobs</p>
        </div>

        <div class="flex items-center gap-2">
            <button type="button" wire:confirm="Are you sure you want to retry all failed jobs?" wire:click="retryAll" class="bg-indigo-50 text-indigo-750 hover:bg-indigo-100 px-4 py-2.5 rounded-xl text-sm font-semibold transition-all">
                <i class="voyager-paperplane mr-1"></i>
                Retry All
            </button>
            <button type="button" wire:confirm="Are you sure you want to forget all failed jobs?" wire:click="forgetAll" class="bg-red-50 text-red-750 hover:bg-red-100 px-4 py-2.5 rounded-xl text-sm font-semibold transition-all">
                <i class="voyager-trash mr-1"></i>
                Flush Queue
            </button>
        </div>
    </div>

    @if (session()->has('message'))
        <div class="p-4 bg-emerald-50 text-emerald-700 border border-emerald-200/50 rounded-2xl flex items-center gap-2 text-sm font-semibold">
            <i class="voyager-check-circle"></i>
            {{ session('message') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="p-4 bg-red-50 text-red-700 border border-red-200 rounded-2xl flex items-center gap-2 text-sm font-semibold">
            <i class="voyager-warning"></i>
            {{ session('error') }}
        </div>
    @endif

    <!-- Controls -->
    <div class="flex items-center justify-between gap-4">
        <div class="relative max-w-md w-full">
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search failed jobs by queue or payload..." class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:ring-primary/20 focus:border-primary transition-all text-sm bg-white">
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-gray-50 text-gray-500 font-bold text-xs uppercase">
                    <tr>
                        <th class="px-6 py-4">Job Class</th>
                        <th class="px-6 py-4">Queue Scope</th>
                        <th class="px-6 py-4">Connection</th>
                        <th class="px-6 py-4">Failed At</th>
                        <th class="px-6 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($this->jobs as $job)
                        @php $payload = json_decode($job->payload, true) ?: []; @endphp
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-6 py-4 font-semibold text-gray-800">
                                {{ $payload['displayName'] ?? ($payload['job'] ?? 'Unknown Job') }}
                            </td>
                            <td class="px-6 py-4 text-gray-650 font-medium">{{ $job->queue }}</td>
                            <td class="px-6 py-4 text-gray-500 text-xs uppercase">{{ $job->connection }}</td>
                            <td class="px-6 py-4 text-gray-400 text-xs">{{ $job->failed_at }}</td>
                            <td class="px-6 py-4 text-right space-x-2">
                                <button type="button" wire:click="showDetails({{ $job->id }})" class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-gray-50 text-gray-700 hover:bg-gray-100 transition-colors">
                                    <i class="voyager-eye mr-1"></i>
                                    Inspect
                                </button>
                                <button type="button" wire:click="retryJob({{ $job->id }})" class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-indigo-50 text-indigo-650 hover:bg-indigo-100 transition-colors">
                                    <i class="voyager-paperplane mr-1"></i>
                                    Retry
                                </button>
                                <button type="button" wire:confirm="Forget job #{{ $job->id }}?" wire:click="forgetJob({{ $job->id }})" class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-red-50 text-red-650 hover:bg-red-100 transition-colors">
                                    <i class="voyager-trash mr-1"></i>
                                    Forget
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-gray-500 font-medium bg-gray-50/30">
                                <i class="voyager-warning text-2xl text-amber-500 block mb-2"></i>
                                No failed jobs registered in queue
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($this->jobs->hasPages())
            <div class="p-6 border-t border-gray-50 bg-gray-50/30">
                {{ $this->jobs->links() }}
            </div>
        @endif
    </div>

    <!-- Inspect Modal -->
    <div x-show="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/50 backdrop-blur-sm p-4" x-cloak>
        <div @click.outside="showModal = false" class="bg-white rounded-2xl max-w-4xl w-full border shadow-xl p-6 space-y-6">
            <div class="flex items-center justify-between border-b pb-4">
                <div>
                    <h3 class="text-lg font-bold text-gray-900">Failed Job Details #{{ $activeJobDetails['id'] ?? '' }}</h3>
                    <p class="text-xs text-gray-500">{{ $activeJobDetails['display_name'] ?? '' }}</p>
                </div>
                <button @click="showModal = false" class="text-gray-400 hover:text-gray-600"><i class="voyager-x"></i></button>
            </div>

            <!-- Stack Trace -->
            <div class="space-y-2">
                <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block">Exception Stack Trace</span>
                <pre class="bg-gray-50 p-4 rounded-xl border font-mono text-xs overflow-auto max-h-[350px] text-gray-600 leading-relaxed">{{ $activeJobDetails['exception'] ?? 'No exception details' }}</pre>
            </div>

            <div class="flex items-center justify-end gap-3 border-t pt-4">
                <button type="button" wire:click="forgetJob({{ $activeJobDetails['id'] ?? 0 }})" class="bg-red-50 text-red-750 px-4 py-2 rounded-xl text-xs font-bold hover:bg-red-100 transition-colors">Forget</button>
                <button type="button" wire:click="retryJob({{ $activeJobDetails['id'] ?? 0 }})" class="bg-primary text-white px-4 py-2 rounded-xl text-xs font-bold hover:bg-primary/95 transition-colors">Retry Job</button>
            </div>
        </div>
    </div>
</div>
