<div class="w-64 min-h-screen bg-slate-900 text-white flex flex-col border-r border-slate-800 shadow-xl">
    <!-- Brand / Header -->
    <div class="px-6 py-5 border-b border-slate-800">
        <a href="{{ route('voyager.dashboard') }}" class="flex items-center gap-3 group">
            <div class="w-10 h-10 rounded-xl bg-primary flex items-center justify-center shadow-lg shadow-primary/20 group-hover:scale-105 transition-transform">
                @php $admin_logo_img = Voyager::setting('admin.icon_image', ''); @endphp
                @if($admin_logo_img == '')
                    <img class="w-6 h-6 object-contain" src="{{ voyager_asset('images/logo-icon-light.png') }}" alt="Logo Icon">
                @else
                    <img class="w-6 h-6 object-contain animate-pulse" src="{{ Voyager::image($admin_logo_img) }}" alt="Logo Icon">
                @endif
            </div>
            <div>
                <h1 class="text-sm font-black tracking-widest text-slate-100 uppercase">{{ Voyager::setting('admin.title', 'VOYAGER') }}</h1>
                <p class="text-[10px] text-slate-500 font-bold uppercase tracking-wider">Laravel Admin</p>
            </div>
        </a>
    </div>

    <!-- Active User Profiling -->
    <div class="p-6 border-b border-slate-800 flex items-center gap-4 bg-slate-950/20">
        <div class="relative w-12 h-12 rounded-xl overflow-hidden border border-slate-800 shadow-inner">
            @if(Auth::user()->avatar)
                <img src="{{ Storage::url(Auth::user()->avatar) }}" class="w-full h-full object-cover">
            @else
                <div class="w-full h-full bg-slate-800 flex items-center justify-center text-slate-400">
                    <i class="voyager-person"></i>
                </div>
            @endif
        </div>
        <div class="flex-1 min-w-0">
            <h4 class="text-sm font-bold text-slate-200 truncate">{{ ucwords(Auth::user()->name) }}</h4>
            <a href="{{ route('voyager.profile') }}" class="text-[10px] text-primary font-bold hover:underline">Edit Profile</a>
        </div>
    </div>

    <!-- Dynamic Admin Menu -->
    <nav class="flex-1 overflow-y-auto">
        <livewire:voyager::admin-menu />
    </nav>
</div>
