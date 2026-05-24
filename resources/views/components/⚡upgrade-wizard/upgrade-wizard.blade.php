

<div class="max-w-4xl space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between border-b border-gray-100 pb-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 tracking-tight flex items-center gap-2">
                <i class="voyager-helm text-primary"></i>
                Voyager v3 Upgrade Wizard
            </h1>
            <p class="text-sm text-gray-500 mt-1">Upgrade your Voyager v2 implementation codebase seamlessly to v3</p>
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

    <!-- Steps Progress Card -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 md:p-8 space-y-8">
        <div class="space-y-4">
            @foreach($steps as $id => $step)
                <div class="flex items-center justify-between p-4 rounded-xl border transition-all 
                    {{ $step['status'] === 'running' ? 'bg-primary/5 border-primary/20 ring-1 ring-primary/10' : 'border-gray-100 bg-gray-50/10' }}
                ">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg flex items-center justify-center font-bold text-sm 
                            {{ $step['status'] === 'success' ? 'bg-emerald-50 text-emerald-600' : '' }}
                            {{ $step['status'] === 'failed' ? 'bg-red-55 text-red-600' : '' }}
                            {{ $step['status'] === 'running' ? 'bg-primary text-white animate-pulse' : '' }}
                            {{ $step['status'] === 'pending' ? 'bg-gray-100 text-gray-400' : '' }}
                        ">
                            @if($step['status'] === 'success')
                                <i class="voyager-check text-xs"></i>
                            @elseif($step['status'] === 'failed')
                                <i class="voyager-warning text-xs"></i>
                            @else
                                {{ $id }}
                            @endif
                        </div>
                        <div>
                            <h4 class="text-xs font-bold text-gray-800">{{ $step['title'] }}</h4>
                            <p class="text-[10px] text-gray-400">{{ $step['desc'] }}</p>
                        </div>
                    </div>

                    <div class="text-xs font-bold uppercase tracking-wider">
                        @if($step['status'] === 'success')
                            <span class="text-emerald-600">Completed</span>
                        @elseif($step['status'] === 'failed')
                            <span class="text-red-650 animate-pulse">Failed</span>
                        @elseif($step['status'] === 'running')
                            <span class="text-primary animate-pulse">Processing...</span>
                        @else
                            <span class="text-gray-400">Waiting</span>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        <div class="border-t border-gray-150 pt-6 flex items-center justify-end">
            <button type="button" wire:click="startUpgrade" class="bg-primary hover:bg-primary/95 text-white px-6 py-3 rounded-xl text-sm font-semibold shadow-lg shadow-primary/25 transition-all">
                <i class="voyager-helm mr-1"></i>
                Start Upgrade Process
            </button>
        </div>
    </div>
</div>
