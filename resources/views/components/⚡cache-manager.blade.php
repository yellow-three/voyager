<?php
use Livewire\Component;
use Illuminate\Support\Facades\Artisan;

new class extends Component {
    public array $targets = [
        'application' => true,
        'config' => true,
        'route' => true,
        'view' => true,
    ];

    public function clearCache(): void
    {
        $cleared = [];

        try {
            if ($this->targets['application']) {
                Artisan::call('cache:clear');
                $cleared[] = 'Application Cache';
            }
            if ($this->targets['config']) {
                Artisan::call('config:clear');
                $cleared[] = 'Config Cache';
            }
            if ($this->targets['route']) {
                Artisan::call('route:clear');
                $cleared[] = 'Route Cache';
            }
            if ($this->targets['view']) {
                Artisan::call('view:clear');
                $cleared[] = 'View Cache';
            }

            $list = implode(', ', $cleared);
            session()->flash('message', "Purged successfully: {$list}");
        } catch (\Exception $e) {
            session()->flash('error', "Purge failed: " . $e->getMessage());
        }
    }
};
?>

<div class="max-w-4xl space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between border-b border-gray-100 pb-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 tracking-tight flex items-center gap-2">
                <i class="voyager-trash text-primary"></i>
                Cache Manager
            </h1>
            <p class="text-sm text-gray-500 mt-1">Manage compiled resources and bootstrap caching speeds</p>
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

    <!-- Selection Panel -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 md:p-8 space-y-6">
        <div>
            <h3 class="text-sm font-bold text-gray-900">Purge Targets</h3>
            <p class="text-xs text-gray-400 mt-0.5">Toggle memory scopes to clear</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- App Cache -->
            <label class="flex items-start gap-3 p-4 rounded-xl border border-gray-100 hover:border-indigo-500/20 hover:shadow-sm transition-all group bg-gray-50/20 cursor-pointer">
                <input type="checkbox" wire:model="targets.application" class="mt-1 h-4 w-4 text-primary focus:ring-primary border-gray-300 rounded-lg">
                <div>
                    <h4 class="text-xs font-bold text-gray-800">Application Cache</h4>
                    <p class="text-[10px] text-gray-400">Purges compiled variables and query data</p>
                </div>
            </label>

            <!-- Config Cache -->
            <label class="flex items-start gap-3 p-4 rounded-xl border border-gray-100 hover:border-indigo-500/20 hover:shadow-sm transition-all group bg-gray-50/20 cursor-pointer">
                <input type="checkbox" wire:model="targets.config" class="mt-1 h-4 w-4 text-primary focus:ring-primary border-gray-300 rounded-lg">
                <div>
                    <h4 class="text-xs font-bold text-gray-800">Configuration Cache</h4>
                    <p class="text-[10px] text-gray-400">Purges compiled settings config caching</p>
                </div>
            </label>

            <!-- Route Cache -->
            <label class="flex items-start gap-3 p-4 rounded-xl border border-gray-100 hover:border-indigo-500/20 hover:shadow-sm transition-all group bg-gray-50/20 cursor-pointer">
                <input type="checkbox" wire:model="targets.route" class="mt-1 h-4 w-4 text-primary focus:ring-primary border-gray-300 rounded-lg">
                <div>
                    <h4 class="text-xs font-bold text-gray-800">Route Cache</h4>
                    <p class="text-[10px] text-gray-400">Purges compiled routing schemas</p>
                </div>
            </label>

            <!-- View Cache -->
            <label class="flex items-start gap-3 p-4 rounded-xl border border-gray-100 hover:border-indigo-500/20 hover:shadow-sm transition-all group bg-gray-50/20 cursor-pointer">
                <input type="checkbox" wire:model="targets.view" class="mt-1 h-4 w-4 text-primary focus:ring-primary border-gray-300 rounded-lg">
                <div>
                    <h4 class="text-xs font-bold text-gray-800">View Template Cache</h4>
                    <p class="text-[10px] text-gray-400">Purges compiled blade templates</p>
                </div>
            </label>
        </div>

        <div class="border-t border-gray-100 pt-6 flex items-center justify-end">
            <button type="button" wire:click="clearCache" class="bg-red-500 hover:bg-red-650 text-white px-6 py-3 rounded-xl text-sm font-semibold shadow-lg shadow-red-500/25 transition-all">
                <i class="voyager-trash mr-1"></i>
                Purge Selected Scopes
            </button>
        </div>
    </div>
</div>
