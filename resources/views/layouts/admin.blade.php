<!DOCTYPE html>
<html lang="{{ config('app.locale', 'en') }}" dir="{{ __('voyager::generic.is_rtl') == 'true' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('page_title', setting('admin.title', 'Voyager') . " - " . setting('admin.description', 'Laravel Admin'))</title>

    <!-- Premium Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700&display=swap" rel="stylesheet">

    <!-- Favicon -->
    @php $admin_favicon = Voyager::setting('admin.icon_image', ''); @endphp
    @if($admin_favicon == '')
        <link rel="shortcut icon" href="{{ voyager_asset('images/logo-icon.png') }}" type="image/png">
    @else
        <link rel="shortcut icon" href="{{ Voyager::image($admin_favicon) }}" type="image/png">
    @endif

    <!-- Vite Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @livewireStyles
    @yield('css')
    @stack('css')

    <!-- Custom/Additional Head Assets -->
    @if(!empty(config('voyager.additional_css')))
        @foreach(config('voyager.additional_css') as $css)
            <link rel="stylesheet" type="text/css" href="{{ asset($css) }}">
        @endforeach
    @endif

    @yield('head')
</head>
<body class="font-sans antialiased bg-gray-50 text-gray-900 voyager @if(isset($dataType) && isset($dataType->slug)){{ $dataType->slug }}@endif">

    <!-- Premium Loader -->
    <div id="voyager-loader" class="fixed inset-0 z-50 flex items-center justify-center bg-white transition-opacity duration-300 pointer-events-none">
        @php $admin_loader_img = Voyager::setting('admin.loader', ''); @endphp
        @if($admin_loader_img == '')
            <img class="w-16 h-16 animate-pulse" src="{{ voyager_asset('images/logo-icon.png') }}" alt="Voyager Loader">
        @else
            <img class="w-16 h-16 animate-pulse" src="{{ Voyager::image($admin_loader_img) }}" alt="Voyager Loader">
        @endif
    </div>

    <!-- Main Container -->
    <div class="min-h-screen flex flex-col md:flex-row">
        <!-- Sidebar Layout Include -->
        @include('voyager::dashboard.sidebar')

        <!-- App Body wrapper -->
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
            <!-- Navbar Include -->
            @include('voyager::dashboard.navbar')

            <!-- Page Content Body -->
            <main class="flex-1 overflow-y-auto p-4 md:p-6 lg:p-8">
                <!-- Impersonation Banner -->
                <livewire:voyager::⚡impersonation />

                <!-- Notifications container -->
                <div id="voyager-notifications"></div>

                @yield('page_header')
                @yield('content')
            </main>

            <!-- Footer Include -->
            @include('voyager::partials.app-footer')
        </div>
    </div>

    @livewireScripts
    
    <!-- Alpine and App Scripts loaded via Vite -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const loader = document.getElementById('voyager-loader');
            if (loader) {
                loader.classList.add('opacity-0');
                setTimeout(() => loader.remove(), 300);
            }
        });

        @if(Session::has('alerts'))
            let alerts = {!! json_encode(Session::get('alerts')) !!};
            helpers.displayAlerts(alerts, toastr);
        @endif

        @if(Session::has('message'))
            var alertType = {!! json_encode(Session::get('alert-type', 'info')) !!};
            var alertMessage = {!! json_encode(Session::get('message')) !!};
            var alerter = toastr[alertType];
            if (alerter) {
                alerter(alertMessage);
            }
        @endif
    </script>

    @yield('javascript')
    @stack('javascript')

    @if(!empty(config('voyager.additional_js')))
        @foreach(config('voyager.additional_js') as $js)
            <script type="text/javascript" src="{{ asset($js) }}"></script>
        @endforeach
    @endif
</body>
</html>
