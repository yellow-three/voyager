@php
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
};
@endphp

<div class="space-y-6" x-data="{ showModal: @entangle('activeLogDetails').live }">
    <!-- Header -->
    <div class="flex items-center justify-between border-b border-gray-100 pb-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 tracking-tight flex items-center gap-2">
                <i class="voyager-activity text-primary"></i>
                Activity Logs
            </h1>
            <p class="text-sm text-gray-500 mt-1">Audit trail tracking all modifications done by system administrators</p>
        </div>
    </div>

    <!-- Controls -->
    <div class="flex flex-col md:flex-row items-center justify-between gap-4">
        <div class="relative max-w-md w-full">
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search by administrator, model, or IP..." class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:ring-primary/20 focus:border-primary transition-all text-sm bg-white">
        </div>

        <div class="w-full md:w-auto">
            <select wire:model.live="actionFilter" class="w-full md:w-48 px-4 py-2.5 rounded-xl border border-gray-200 focus:ring-primary/20 focus:border-primary transition-all text-sm bg-white">
                <option value="">All Actions</option>
                <option value="created">Created</option>
                <option value="updated">Updated</option>
                <option value="deleted">Deleted</option>
            </select>
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-gray-50 text-gray-500 font-bold text-xs uppercase">
                    <tr>
                        <th class="px-6 py-4">Administrator</th>
                        <th class="px-6 py-4">Action</th>
                        <th class="px-6 py-4">Model Scope</th>
                        <th class="px-6 py-4">IP Address</th>
                        <th class="px-6 py-4">Timestamp</th>
                        <th class="px-6 py-4 text-right">Details</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($this->logs as $log)
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-6 py-4 font-semibold text-gray-800">{{ $log->user_name ?? 'System' }}</td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold 
                                    {{ $log->action === 'created' ? 'bg-emerald-50 text-emerald-700' : '' }}
                                    {{ $log->action === 'updated' ? 'bg-amber-50 text-amber-700' : '' }}
                                    {{ $log->action === 'deleted' ? 'bg-red-50 text-red-700' : '' }}
                                ">
                                    {{ $log->action }}
                                </span>
                            </td>
                            <td class="px-6 py-4 font-mono text-xs text-gray-500">{{ $log->model_type }}</td>
                            <td class="px-6 py-4 text-gray-650 font-medium">{{ $log->ip }}</td>
                            <td class="px-6 py-4 text-gray-400 text-xs">{{ $log->created_at }}</td>
                            <td class="px-6 py-4 text-right">
                                <button type="button" wire:click="showDetails({{ $log->id }})" class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-gray-50 text-gray-700 hover:bg-gray-100 transition-colors">
                                    <i class="voyager-eye mr-1"></i>
                                    Inspect
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-gray-500 font-medium bg-gray-50/30">
                                <i class="voyager-warning text-2xl text-amber-500 block mb-2"></i>
                                No activity logs recorded
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($this->logs->hasPages())
            <div class="p-6 border-t border-gray-50 bg-gray-50/30">
                {{ $this->logs->links() }}
            </div>
        @endif
    </div>

    <!-- Inspect Modal -->
    <div x-show="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/50 backdrop-blur-sm p-4" x-cloak>
        <div @click.outside="showModal = false" class="bg-white rounded-2xl max-w-4xl w-full border shadow-xl p-6 space-y-6">
            <div class="flex items-center justify-between border-b pb-4">
                <div>
                    <h3 class="text-lg font-bold text-gray-900">Audit Log Details #{{ $activeLogDetails['id'] ?? '' }}</h3>
                    <p class="text-xs text-gray-500">{{ $activeLogDetails['model_type'] ?? '' }} ({{ $activeLogDetails['action'] ?? '' }})</p>
                </div>
                <button @click="showModal = false" class="text-gray-400 hover:text-gray-600"><i class="voyager-x"></i></button>
            </div>

            <!-- Diff View -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Old Values -->
                <div class="space-y-2">
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block">Before (Old Values)</span>
                    <pre class="bg-gray-50 p-4 rounded-xl border font-mono text-xs overflow-auto max-h-[300px] text-gray-600">@json($activeLogDetails['old_values'] ?? [], JSON_PRETTY_PRINT)</pre>
                </div>

                <!-- New Values -->
                <div class="space-y-2">
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block">After (New Values)</span>
                    <pre class="bg-gray-50 p-4 rounded-xl border font-mono text-xs overflow-auto max-h-[300px] text-gray-600">@json($activeLogDetails['new_values'] ?? [], JSON_PRETTY_PRINT)</pre>
                </div>
            </div>

            <!-- Server metadata -->
            <div class="p-4 rounded-xl bg-gray-50/50 border grid grid-cols-2 gap-4 text-xs font-semibold text-gray-500">
                <div>IP Address: <span class="text-gray-800">{{ $activeLogDetails['ip'] ?? '' }}</span></div>
                <div class="truncate">User Agent: <span class="text-gray-800" title="{{ $activeLogDetails['user_agent'] ?? '' }}">{{ $activeLogDetails['user_agent'] ?? '' }}</span></div>
            </div>
        </div>
    </div>
</div>
