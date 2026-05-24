

<div class="space-y-8">
    <!-- Premium Welcome Header -->
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-gray-900 via-slate-800 to-indigo-950 p-8 md:p-12 shadow-xl shadow-slate-950/10 text-white">
        <div class="absolute -right-10 -top-10 w-40 h-40 bg-indigo-500/10 rounded-full blur-3xl"></div>
        <div class="absolute right-20 bottom-0 w-60 h-60 bg-blue-500/10 rounded-full blur-3xl"></div>
        
        <div class="relative z-10 max-w-2xl space-y-4">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-indigo-500/20 text-indigo-200 border border-indigo-400/20 backdrop-blur-md">
                <span class="w-1.5 h-1.5 rounded-full bg-indigo-400 animate-ping"></span>
                System Operational
            </span>
            <h1 class="text-3xl md:text-5xl font-extrabold tracking-tight bg-clip-text bg-gradient-to-r from-white via-slate-100 to-indigo-200">
                Welcome back, {{ Auth::user()->name }}
            </h1>
            <p class="text-slate-300 text-sm md:text-base font-medium max-w-md">
                Manage your content, configuration, roles, and plugins from this central premium control deck.
            </p>
        </div>
    </div>

    <!-- Metrics Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        @foreach($metrics as $metric)
            <a href="{{ route($metric['route']) }}" class="group relative overflow-hidden bg-white dark:bg-gray-800 p-6 rounded-2xl border border-gray-100 dark:border-gray-700/50 shadow-sm hover:shadow-md hover:border-indigo-500/30 transition-all duration-300">
                <div class="flex items-center justify-between">
                    <div class="space-y-2">
                        <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ $metric['title'] }}</span>
                        <h3 class="text-3xl font-bold text-gray-900 dark:text-white tracking-tight">{{ $metric['count'] }}</h3>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br {{ $metric['color'] }} text-white flex items-center justify-center shadow-lg shadow-indigo-500/10 transition-transform duration-300 group-hover:scale-110">
                        <i class="voyager-{{ $metric['icon'] }} text-xl"></i>
                    </div>
                </div>
                <div class="mt-4 pt-4 border-t border-gray-50 dark:border-gray-700/30 flex items-center justify-between text-xs text-indigo-600 dark:text-indigo-400 font-semibold group-hover:translate-x-1 transition-transform">
                    <span>Manage all entries</span>
                    <i class="voyager-angle-right"></i>
                </div>
            </a>
        @endforeach
    </div>

    <!-- Dashboard Body Columns -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- System Activity Log panel -->
        <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700/50 p-6 shadow-sm space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-bold text-gray-900 dark:text-white tracking-tight flex items-center gap-2">
                        <i class="voyager-activity text-indigo-500"></i>
                        Recent Actions
                    </h2>
                    <p class="text-xs text-gray-500 mt-0.5">Real-time system actions performed by administrators</p>
                </div>
            </div>
            
            <div class="flow-root">
                <ul role="list" class="-mb-8">
                    @foreach($activities as $index => $activity)
                        <li>
                            <div class="relative pb-8">
                                @if($index !== count($activities) - 1)
                                    <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-150 dark:bg-gray-700" aria-hidden="true"></span>
                                @endif
                                <div class="relative flex space-x-3">
                                    <div>
                                        <span class="h-8 w-8 rounded-full bg-indigo-50 dark:bg-indigo-950 flex items-center justify-center ring-8 ring-white dark:ring-gray-800 text-indigo-600 dark:text-indigo-400">
                                            <i class="voyager-info text-sm"></i>
                                        </span>
                                    </div>
                                    <div class="flex-1 min-w-0 pt-1.5 flex justify-between space-x-4">
                                        <div>
                                            <p class="text-sm text-gray-600 dark:text-gray-300 font-medium">{{ $activity['description'] }}</p>
                                        </div>
                                        <div class="text-right text-xs whitespace-nowrap text-gray-400 font-semibold">
                                            <time>{{ $activity['time'] }}</time>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>

        <!-- Quick Resources Card -->
        <div class="bg-gradient-to-b from-indigo-50/50 to-white dark:from-gray-800 dark:to-gray-800/80 rounded-2xl border border-indigo-100/30 dark:border-gray-700/50 p-6 shadow-sm space-y-6">
            <div>
                <h2 class="text-lg font-bold text-gray-900 dark:text-white tracking-tight">Quick Operations</h2>
                <p class="text-xs text-gray-500 mt-0.5">Rapid access to core utility tools</p>
            </div>

            <div class="grid grid-cols-1 gap-3">
                <a href="{{ route('voyager.settings.index') }}" class="flex items-center gap-3 p-3 rounded-xl bg-white dark:bg-gray-700 border border-gray-100 dark:border-gray-600 hover:border-indigo-500/20 hover:shadow-sm transition-all group">
                    <span class="w-10 h-10 rounded-lg bg-amber-50 dark:bg-amber-950 text-amber-500 flex items-center justify-center group-hover:scale-105 transition-transform">
                        <i class="voyager-settings"></i>
                    </span>
                    <div class="flex-1 min-w-0">
                        <h4 class="text-sm font-semibold text-gray-800 dark:text-gray-200">Global Settings</h4>
                        <p class="text-xs text-gray-400 truncate">Configure titles, assets, email settings</p>
                    </div>
                </a>

                <a href="{{ route('voyager.media.index') }}" class="flex items-center gap-3 p-3 rounded-xl bg-white dark:bg-gray-700 border border-gray-100 dark:border-gray-600 hover:border-indigo-500/20 hover:shadow-sm transition-all group">
                    <span class="w-10 h-10 rounded-lg bg-indigo-50 dark:bg-indigo-950 text-indigo-500 flex items-center justify-center group-hover:scale-105 transition-transform">
                        <i class="voyager-images"></i>
                    </span>
                    <div class="flex-1 min-w-0">
                        <h4 class="text-sm font-semibold text-gray-800 dark:text-gray-200">Media Library</h4>
                        <p class="text-xs text-gray-400 truncate">Upload and crop files, manage folders</p>
                    </div>
                </a>

                <a href="{{ route('voyager.database.index') }}" class="flex items-center gap-3 p-3 rounded-xl bg-white dark:bg-gray-700 border border-gray-100 dark:border-gray-600 hover:border-indigo-500/20 hover:shadow-sm transition-all group">
                    <span class="w-10 h-10 rounded-lg bg-emerald-50 dark:bg-emerald-950 text-emerald-500 flex items-center justify-center group-hover:scale-105 transition-transform">
                        <i class="voyager-data"></i>
                    </span>
                    <div class="flex-1 min-w-0">
                        <h4 class="text-sm font-semibold text-gray-800 dark:text-gray-200">Database Tools</h4>
                        <p class="text-xs text-gray-400 truncate">Manage schemas, create new tables</p>
                    </div>
                </a>
            </div>
        </div>
    </div>
</div>
