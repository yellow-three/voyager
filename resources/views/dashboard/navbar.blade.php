@php
    $user_avatar = Auth::user()->avatar
        ? Voyager::image(Auth::user()->avatar)
        : voyager_asset('images/logo-icon.png');
@endphp
<nav class="sticky top-0 z-30 bg-white border-b border-gray-100 shadow-sm" x-data="{ open: false }">
    <div class="flex items-center justify-between px-4 lg:px-6 h-16">
        <!-- Left: Hamburger + Breadcrumbs -->
        <div class="flex items-center gap-3 min-w-0">
            <button class="lg:hidden p-2 rounded-lg text-gray-500 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-primary" @click="open = !open">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>
            @section('breadcrumbs')
                <ol class="hidden sm:flex items-center gap-1.5 text-sm text-gray-500">
                    @php
                        $segments = array_filter(explode('/', str_replace(route('voyager.dashboard'), '', Request::url())));
                        $url = route('voyager.dashboard');
                    @endphp
                    @if(count($segments) == 0)
                        <li class="flex items-center gap-1.5 text-gray-900 font-medium">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                            </svg>
                            {{ __('voyager::generic.dashboard') }}
                        </li>
                    @else
                        <li>
                            <a href="{{ route('voyager.dashboard') }}" class="flex items-center gap-1.5 hover:text-primary transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                            </svg>
                            {{ __('voyager::generic.dashboard') }}
                        </a>
                        @foreach ($segments as $segment)
                            @php $url .= '/'.$segment; @endphp
                            <svg class="w-3 h-3 text-gray-300" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd"/>
                            </svg>
                            @if ($loop->last)
                                <li class="text-gray-900 font-medium truncate max-w-[200px]">{{ ucfirst(urldecode($segment)) }}</li>
                            @else
                                <li>
                                    <a href="{{ $url }}" class="hover:text-primary transition-colors truncate max-w-[150px]">{{ ucfirst(urldecode($segment)) }}</a>
                                </li>
                            @endif
                        @endforeach
                    @endif
                </ol>
            @show
        </div>

        <!-- Right: User Profile Dropdown -->
        <div class="flex items-center gap-2">
            <div class="relative" x-data="{ profileOpen: false }">
                <button class="flex items-center gap-2 p-1.5 rounded-xl hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-primary transition-colors" @click="profileOpen = !profileOpen" @click.away="profileOpen = false">
                    <div class="w-8 h-8 rounded-lg overflow-hidden ring-2 ring-gray-100">
                        <img src="{{ $user_avatar }}" class="w-full h-full object-cover" alt="Avatar">
                    </div>
                    <span class="hidden md:block text-sm font-medium text-gray-700">{{ Auth::user()->name }}</span>
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <div x-show="profileOpen" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95" class="absolute right-0 mt-2 w-64 bg-white rounded-xl shadow-lg border border-gray-100 py-2 z-50" @click.away="profileOpen = false">
                    <div class="px-4 py-3 border-b border-gray-100">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-xl overflow-hidden ring-2 ring-gray-100">
                                <img src="{{ $user_avatar }}" class="w-full h-full object-cover">
                            </div>
                            <div class="min-w-0">
                                <p class="text-sm font-bold text-gray-900 truncate">{{ Auth::user()->name }}</p>
                                <p class="text-xs text-gray-500 truncate">{{ Auth::user()->email }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="py-1">
                        <?php $nav_items = config('voyager.dashboard.navbar_items'); ?>
                        @if(is_array($nav_items) && !empty($nav_items))
                            @foreach($nav_items as $name => $item)
                                @if(isset($item['route']) && $item['route'] == 'voyager.logout')
                                    <form action="{{ route('voyager.logout') }}" method="POST">
                                        {{ csrf_field() }}
                                        <button type="submit" class="w-full flex items-center gap-3 px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition-colors">
                                            @if(isset($item['icon_class']) && !empty($item['icon_class']))
                                                <i class="{!! $item['icon_class'] !!} w-4 h-4"></i>
                                            @else
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                                </svg>
                                            @endif
                                            {{ __('voyager::generic.logout') }}
                                        </button>
                                    </form>
                                @else
                                    <a href="{{ isset($item['route']) && Route::has($item['route']) ? route($item['route']) : (isset($item['route']) ? $item['route'] : '#') }}" {!! isset($item['target_blank']) && $item['target_blank'] ? 'target="_blank"' : '' !!} class="flex items-center gap-3 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                                        @if(isset($item['icon_class']) && !empty($item['icon_class']))
                                            <i class="{!! $item['icon_class'] !!} w-4 h-4"></i>
                                        @endif
                                        {{ __($name) }}
                                    </a>
                                @endif
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</nav>
