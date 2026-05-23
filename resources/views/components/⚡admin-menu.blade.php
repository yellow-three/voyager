@php
use Livewire\Component;
use YellowThree\Voyager\Facades\Voyager;

new class extends Component {
    public array $items = [];

    public function mount(?array $items = null): void
    {
        if (is_null($items)) {
            // Safe fallback: fetch default core registered items from VoyagerCorePlugin if any, or construct default list
            $this->items = [
                ['title' => 'Dashboard', 'route' => 'voyager.dashboard', 'icon' => 'home'],
                ['title' => 'Media', 'route' => 'voyager.media.index', 'icon' => 'images'],
                ['title' => 'Database', 'route' => 'voyager.database.index', 'icon' => 'data'],
                ['title' => 'Settings', 'route' => 'voyager.settings.index', 'icon' => 'settings'],
            ];

            // Append BREAD items dynamically
            try {
                foreach (Voyager::model('DataType')::all() as $dataType) {
                    $this->items[] = [
                        'title' => $dataType->display_name_plural,
                        'route' => 'voyager.'.$dataType->slug.'.index',
                        'icon' => $dataType->icon ?: 'file',
                    ];
                }
            } catch (\Exception $e) {}
        } else {
            $this->items = $items;
        }
    }
};
@endphp

<div class="px-4 py-6 space-y-1.5" x-data="{ activeDropdown: null }">
    @foreach($items as $index => $item)
        @php
            $hasChildren = isset($item['children']) && !empty($item['children']);
            $routeExists = isset($item['route']) && Route::has($item['route']);
            $url = $routeExists ? route($item['route']) : ($item['url'] ?? '#');
            $isActive = $routeExists && request()->routeIs($item['route']);
        @endphp

        <div class="space-y-1">
            @if($hasChildren)
                <!-- Accordion Link -->
                <button @click="activeDropdown = (activeDropdown === {{ $index }} ? null : {{ $index }})" class="w-full flex items-center justify-between px-4 py-3 rounded-xl text-sm font-semibold text-gray-400 hover:text-white hover:bg-gray-800/50 transition-all">
                    <div class="flex items-center gap-3">
                        <i class="voyager-{{ $item['icon'] ?? 'file' }} text-base"></i>
                        <span>{{ $item['title'] }}</span>
                    </div>
                    <i class="voyager-angle-down transition-transform duration-200" :class="activeDropdown === {{ $index }} ? 'rotate-180' : ''"></i>
                </button>

                <!-- Children Container -->
                <div x-show="activeDropdown === {{ $index }}" x-collapse class="pl-8 space-y-1" x-cloak>
                    @foreach($item['children'] as $child)
                        @php
                            $childRouteExists = isset($child['route']) && Route::has($child['route']);
                            $childUrl = $childRouteExists ? route($child['route']) : ($child['url'] ?? '#');
                            $isChildActive = $childRouteExists && request()->routeIs($child['route']);
                        @endphp
                        <a href="{{ $childUrl }}" class="block px-4 py-2.5 rounded-lg text-xs font-semibold {{ $isChildActive ? 'text-primary bg-primary/10' : 'text-gray-400 hover:text-white hover:bg-gray-800/30' }} transition-all">
                            {{ $child['title'] }}
                        </a>
                    @endforeach
                </div>
            @else
                <!-- Single Link -->
                <a href="{{ $url }}" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold transition-all {{ $isActive ? 'bg-primary text-white shadow-lg shadow-primary/20' : 'text-gray-400 hover:text-white hover:bg-gray-800/50' }}">
                    <i class="voyager-{{ $item['icon'] ?? 'file' }} text-base"></i>
                    <span>{{ $item['title'] }}</span>
                </a>
            @endif
        </div>
    @endforeach
</div>
