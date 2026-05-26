

<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between border-b border-gray-100 pb-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 tracking-tight flex items-center gap-2">
                <i class="voyager-extension text-primary"></i>
                Plugins Manager
            </h1>
            <p class="text-sm text-gray-500 mt-1">Activate, configure, or extend core systems via pluggable packages</p>
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

    <!-- Plugins Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @foreach($plugins as $plugin)
            <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm flex flex-col justify-between gap-6 hover:shadow-md hover:border-gray-250 transition-all group">
                <div class="space-y-3">
                    <div class="flex items-start justify-between">
                        <div>
                            <h3 class="text-sm font-bold text-gray-800 flex items-center gap-2">
                                {{ $plugin['label'] }}
                                <span class="text-[10px] text-gray-400 bg-gray-150 px-2 py-0.5 rounded font-bold font-mono">v{{ $plugin['version'] }}</span>
                            </h3>
                            <span class="text-[10px] text-gray-400 font-semibold block mt-0.5">Author: {{ $plugin['author'] ?: 'Unknown' }}</span>
                        </div>

                        <!-- Status Badge -->
                        <div>
                            @if($plugin['is_core'])
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-indigo-50 text-indigo-750">Core Plugin</span>
                            @elseif($plugin['is_active'])
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-750">Active</span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-gray-100 text-gray-500">Inactive</span>
                            @endif
                        </div>
                    </div>

                    <p class="text-xs text-gray-500 leading-relaxed font-medium">{{ $plugin['description'] }}</p>
                </div>

                <div class="border-t border-gray-50 pt-4 flex items-center justify-between">
                    <code class="text-[10px] text-gray-400 bg-gray-50 px-2 py-0.5 rounded font-mono">{{ $plugin['name'] }}</code>

                    @if(!$plugin['is_core'])
                        <button type="button" wire:click="togglePlugin('{{ $plugin['name'] }}')" class="px-4 py-2 rounded-xl text-xs font-bold transition-all shadow-md {{ $plugin['is_active'] ? 'bg-red-50 text-red-750 hover:bg-red-100 shadow-red-50/10' : 'bg-primary text-white hover:bg-primary/95 shadow-primary/20' }}">
                            {{ $plugin['is_active'] ? 'Deactivate' : 'Activate' }}
                        </button>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
</div>
