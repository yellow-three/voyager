<footer class="mt-auto py-4 px-6 border-t border-gray-100 bg-white text-gray-500 text-xs flex justify-between items-center">
    <div>
        @if (rand(1,100) == 100)
            <i class="voyager-rum-1 mr-1"></i> {{ __('voyager::theme.footer_copyright2') }}
        @else
            {!! __('voyager::theme.footer_copyright') !!} <a class="text-primary hover:underline font-medium" href="https://github.com/yellow-three/voyager" target="_blank">Yellow Three Voyager v3</a>
        @endif
    </div>
    @php $version = Voyager::getVersion(); @endphp
    @if (!empty($version))
        <div class="font-mono text-gray-400">v{{ $version }}</div>
    @endif
</footer>
