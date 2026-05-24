

<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between border-b border-gray-100 pb-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 tracking-tight flex items-center gap-2">
                <i class="voyager-paint-bucket text-primary"></i>
                Themes Manager
            </h1>
            <p class="text-sm text-gray-500 mt-1">Configure global platform styles, typography, and color palettes</p>
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

    <!-- Themes Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @foreach($themes as $theme)
            <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm flex flex-col justify-between gap-6 hover:shadow-md hover:border-gray-250 transition-all group">
                <div class="space-y-4">
                    <div class="flex items-start justify-between">
                        <div>
                            <h3 class="text-sm font-bold text-gray-800 flex items-center gap-2 uppercase tracking-wide">
                                {{ $theme['name'] }}
                                <span class="text-[10px] text-gray-400 bg-gray-150 px-2 py-0.5 rounded font-bold font-mono">v{{ $theme['version'] }}</span>
                            </h3>
                        </div>

                        <!-- Status Badge -->
                        <div>
                            @if($theme['is_active'])
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-primary text-white shadow-sm shadow-primary/20">Active Theme</span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-gray-100 text-gray-500">Available</span>
                            @endif
                        </div>
                    </div>

                    <!-- Colors Preview Palette -->
                    <div class="space-y-1.5">
                        <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block">Palette Scheme</span>
                        <div class="flex items-center gap-1.5">
                            @foreach($theme['colors'] as $key => $color)
                                <div class="w-8 h-8 rounded-lg shadow-sm border border-gray-100/50 hover:scale-105 transition-transform" style="background-color: {{ $color }}" title="{{ $key }}: {{ $color }}"></div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Metadata Details -->
                    <div class="space-y-2 text-xs font-semibold text-gray-500">
                        <div class="flex items-center justify-between">
                            <span>Font Family:</span>
                            <span class="text-gray-800 font-mono">{{ $theme['fonts']['sans'] ?? 'Inter' }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span>Dark Mode Support:</span>
                            <span class="text-gray-800">{{ $theme['dark_mode'] ? 'YES' : 'NO' }}</span>
                        </div>
                    </div>
                </div>

                <div class="border-t border-gray-50 pt-4 flex items-center justify-end">
                    @if(!$theme['is_active'])
                        <button type="button" wire:click="activateTheme('{{ $theme['name'] }}')" class="bg-primary text-white px-5 py-2.5 rounded-xl text-xs font-bold hover:bg-primary/95 shadow-md shadow-primary/20 transition-all">
                            Activate Theme
                        </button>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
</div>
