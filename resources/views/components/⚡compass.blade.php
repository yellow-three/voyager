@php
use Livewire\Component;
use Illuminate\Support\Facades\DB;
use YellowThree\Voyager\Facades\Voyager;

new class extends Component {
    public array $systemInfo = [];

    public function mount(): void
    {
        $this->systemInfo = [
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'voyager_version' => '3.0.0-alpha',
            'db_connection' => config('database.default'),
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'N/A',
            'db_version' => $this->getDbVersion(),
        ];
    }

    private function getDbVersion(): string
    {
        try {
            $results = DB::select(DB::raw("select version() as version"));
            return $results[0]->version ?? 'N/A';
        } catch (\Exception $e) {
            return 'N/A';
        }
    }
};
@endphp

<div class="space-y-8">
    <!-- Header -->
    <div class="flex items-center justify-between border-b border-gray-100 pb-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 tracking-tight flex items-center gap-2">
                <i class="voyager-compass text-primary"></i>
                Voyager Compass
            </h1>
            <p class="text-sm text-gray-500 mt-1">Overview of the system environment and documentation logs</p>
        </div>
    </div>

    <!-- System info cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- PHP Card -->
        <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm space-y-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-500 flex items-center justify-center text-lg">
                    <i class="voyager-info"></i>
                </div>
                <div>
                    <h4 class="text-sm font-bold text-gray-800">Environment</h4>
                    <p class="text-xs text-gray-400">Core system details</p>
                </div>
            </div>

            <div class="space-y-2 text-xs">
                <div class="flex items-center justify-between font-semibold">
                    <span class="text-gray-400">PHP Version:</span>
                    <span class="text-gray-800">{{ $systemInfo['php_version'] }}</span>
                </div>
                <div class="flex items-center justify-between font-semibold">
                    <span class="text-gray-400">Laravel Version:</span>
                    <span class="text-gray-800">{{ $systemInfo['laravel_version'] }}</span>
                </div>
                <div class="flex items-center justify-between font-semibold">
                    <span class="text-gray-400">Voyager Version:</span>
                    <span class="text-gray-800">{{ $systemInfo['voyager_version'] }}</span>
                </div>
            </div>
        </div>

        <!-- Database Card -->
        <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm space-y-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-500 flex items-center justify-center text-lg">
                    <i class="voyager-data"></i>
                </div>
                <div>
                    <h4 class="text-sm font-bold text-gray-800">Database</h4>
                    <p class="text-xs text-gray-400">Connection specs</p>
                </div>
            </div>

            <div class="space-y-2 text-xs">
                <div class="flex items-center justify-between font-semibold">
                    <span class="text-gray-400">Default Driver:</span>
                    <span class="text-gray-800 uppercase">{{ $systemInfo['db_connection'] }}</span>
                </div>
                <div class="flex items-center justify-between font-semibold text-right">
                    <span class="text-gray-400 text-left">DB Version:</span>
                    <span class="text-gray-800 truncate max-w-[150px] inline-block">{{ $systemInfo['db_version'] }}</span>
                </div>
            </div>
        </div>

        <!-- Platform Card -->
        <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm space-y-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-500 flex items-center justify-center text-lg">
                    <i class="voyager-helm"></i>
                </div>
                <div>
                    <h4 class="text-sm font-bold text-gray-800">Server OS</h4>
                    <p class="text-xs text-gray-400">Platform software specs</p>
                </div>
            </div>

            <div class="space-y-2 text-xs">
                <div class="flex items-center justify-between font-semibold">
                    <span class="text-gray-400">Software:</span>
                    <span class="text-gray-800 truncate max-w-[160px] inline-block">{{ $systemInfo['server_software'] }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Developer links & manuals -->
    <div class="bg-white rounded-2xl border border-gray-100 p-6 md:p-8 shadow-sm space-y-6">
        <div>
            <h3 class="text-lg font-bold text-gray-900">Developer Assets & Manuals</h3>
            <p class="text-xs text-gray-500 mt-1">Useful links to learn and build within Voyager v3 framework</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <a href="https://github.com/yellow-three/voyager" target="_blank" class="flex items-center gap-3 p-4 rounded-xl border border-gray-100 hover:border-indigo-500/20 hover:shadow-sm transition-all group bg-gray-50/20">
                <span class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-500 flex items-center justify-center group-hover:scale-105 transition-transform">
                    <i class="voyager-github"></i>
                </span>
                <div>
                    <h4 class="text-xs font-bold text-gray-800">GitHub Repository</h4>
                    <p class="text-[10px] text-gray-400">Voyager open source base</p>
                </div>
            </a>

            <a href="https://laravel.com" target="_blank" class="flex items-center gap-3 p-4 rounded-xl border border-gray-100 hover:border-indigo-500/20 hover:shadow-sm transition-all group bg-gray-50/20">
                <span class="w-8 h-8 rounded-lg bg-red-50 text-red-500 flex items-center justify-center group-hover:scale-105 transition-transform">
                    <i class="voyager-helm"></i>
                </span>
                <div>
                    <h4 class="text-xs font-bold text-gray-800">Laravel Framework</h4>
                    <p class="text-[10px] text-gray-400">Documentation and manuals</p>
                </div>
            </a>
        </div>
    </div>
</div>
