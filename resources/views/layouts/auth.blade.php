<!DOCTYPE html>
<html lang="{{ config('app.locale', 'en') }}" dir="{{ __('voyager::generic.is_rtl') == 'true' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Admin Login - ' . Voyager::setting('admin.title', 'Voyager'))</title>

    <!-- Premium Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700&display=swap" rel="stylesheet">

    <!-- Build Assets -->
    <link rel="stylesheet" href="{{ voyager_asset('build/app.css') }}">
    <script defer src="{{ voyager_asset('build/app2.js') }}"></script>

    @yield('pre_css')

    <style>
        body {
            background-image: linear-gradient(rgba(0, 0, 0, 0.4), rgba(0, 0, 0, 0.6)), url('{{ Voyager::image( Voyager::setting("admin.bg_image"), voyager_asset("images/bg.jpg") ) }}');
            background-color: {{ Voyager::setting("admin.bg_color", "#1e1e2e" ) }};
            background-size: cover;
            background-position: center;
        }
    </style>
</head>
<body class="font-sans antialiased min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-6xl bg-white/10 backdrop-blur-md rounded-2xl shadow-2xl border border-white/20 overflow-hidden flex flex-col md:flex-row">
        <!-- Logo and Intro (Hidden on mobile) -->
        <div class="hidden md:flex md:w-3/5 p-12 flex-col justify-between text-white relative overflow-hidden">
            <div class="space-y-4 z-10">
                <div class="flex items-center space-x-3">
                    @php $admin_logo_img = Voyager::setting('admin.icon_image', ''); @endphp
                    @if($admin_logo_img == '')
                        <img class="w-12 h-12" src="{{ voyager_asset('images/logo-icon-light.png') }}" alt="Logo Icon">
                    @else
                        <img class="w-12 h-12" src="{{ Voyager::image($admin_logo_img) }}" alt="Logo Icon">
                    @endif
                    <span class="text-2xl font-bold tracking-wider">{{ Voyager::setting('admin.title', 'Voyager') }}</span>
                </div>
            </div>
            <div class="space-y-2 mt-auto z-10 max-w-lg">
                <h1 class="text-4xl font-extrabold leading-tight tracking-tight">{{ Voyager::setting('admin.title', 'Voyager') }}</h1>
                <p class="text-lg text-white/80 font-medium">{{ Voyager::setting('admin.description', __('voyager::login.welcome')) }}</p>
            </div>
            <div class="absolute -right-16 -bottom-16 w-64 h-64 bg-primary/20 rounded-full blur-3xl"></div>
        </div>

        <!-- Auth Form Sidebar (Login form container) -->
        <div class="w-full md:w-2/5 bg-white p-8 md:p-12 flex flex-col justify-center">
            @yield('content')
        </div>
    </div>

    @yield('post_js')
</body>
</html>
