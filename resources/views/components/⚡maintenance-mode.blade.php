<?php
use Livewire\Component;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\App;

new class extends Component {
    public bool $isDown = false;
    public string $secret = '';
    public string $message = 'System is down for maintenance. Please check back soon.';
    public string $allowIps = '';

    public function mount(): void
    {
        $this->isDown = App::isDownForMaintenance();
    }

    public function toggleMaintenance(): void
    {
        try {
            if ($this->isDown) {
                // Go Live
                Artisan::call('up');
                $this->isDown = false;
                session()->flash('message', 'Application is now LIVE!');
            } else {
                // Go Down
                $params = [];
                if ($this->secret) {
                    $params['--secret'] = $this->secret;
                }
                if ($this->message) {
                    // Laravel down command accepts message in newer versions or bypass
                }
                
                // Trigger down
                Artisan::call('down', $params);
                $this->isDown = true;
                session()->flash('message', 'Application is now DOWN for maintenance.');
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Operation failed: ' . $e->getMessage());
        }
    }
};
?>

<div class="max-w-4xl space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between border-b border-gray-100 pb-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 tracking-tight flex items-center gap-2">
                <i class="voyager-helm text-primary"></i>
                Maintenance Deck
            </h1>
            <p class="text-sm text-gray-500 mt-1">Configure maintenance status, bypass key tokens, and custom messages</p>
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

    <!-- Main Deck -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 md:p-8 space-y-8">
        <!-- Status Indicator -->
        <div class="flex items-center justify-between p-4 rounded-xl border {{ $isDown ? 'bg-amber-50 border-amber-250 text-amber-900' : 'bg-emerald-50 border-emerald-250 text-emerald-900' }}">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-white/80 shadow flex items-center justify-center text-lg">
                    @if($isDown)
                        <i class="voyager-helm text-amber-500 animate-spin"></i>
                    @else
                        <i class="voyager-check-circle text-emerald-500"></i>
                    @endif
                </div>
                <div>
                    <h4 class="text-sm font-bold">System Status: <span class="uppercase font-black">{{ $isDown ? 'Down Mode' : 'Live Mode' }}</span></h4>
                    <p class="text-xs opacity-70">{{ $isDown ? 'Public traffic is redirected to maintenance screen' : 'System is accepting normal user traffic' }}</p>
                </div>
            </div>

            <button type="button" wire:click="toggleMaintenance" class="px-5 py-2.5 rounded-xl text-xs font-bold transition-all shadow-md {{ $isDown ? 'bg-emerald-600 hover:bg-emerald-700 text-white shadow-emerald-600/25' : 'bg-amber-600 hover:bg-amber-700 text-white shadow-amber-600/25' }}">
                {{ $isDown ? 'Activate Live Mode' : 'Activate Down Mode' }}
            </button>
        </div>

        @if(!$isDown)
            <!-- Down Parameters Config -->
            <div class="space-y-6">
                <hr class="border-gray-150">
                <div>
                    <h3 class="text-sm font-bold text-gray-900">Maintenance Options</h3>
                    <p class="text-xs text-gray-400 mt-0.5">Parameters applied when taking the application down</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Secret key -->
                    <div class="space-y-1">
                        <label for="secret" class="block text-xs font-bold text-gray-700 uppercase tracking-wider">Secret Bypass Cookie Key</label>
                        <input type="text" id="secret" wire:model="secret" placeholder="e.g. bypass-token" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:ring-primary/20 focus:border-primary transition-all text-sm">
                        <p class="text-[10px] text-gray-400">If set, developers can access via: yourdomain.com/{secret}</p>
                    </div>

                    <!-- Custom Message -->
                    <div class="space-y-1">
                        <label for="message" class="block text-xs font-bold text-gray-700 uppercase tracking-wider">Custom Maintenance Message</label>
                        <input type="text" id="message" wire:model="message" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:ring-primary/20 focus:border-primary transition-all text-sm">
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
