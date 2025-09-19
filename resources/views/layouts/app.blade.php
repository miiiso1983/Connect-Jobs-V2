<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        @if(config('services.google.site_verification'))
            <meta name="google-site-verification" content="{{ config('services.google.site_verification') }}" />
        @endif

        <title>{{ config('app.name', 'Connect Jobs') }}</title>
        <link rel="canonical" href="{{ url()->current() }}" />
        <meta property="og:type" content="website" />
        <meta property="og:title" content="@yield('meta_title', config('app.name','Connect Jobs'))" />
        <meta property="og:description" content="@yield('meta_description','منصة وظائف تربط الشركات بالكوادر الموهوبة في العراق والشرق الأوسط')" />
        <meta property="og:url" content="{{ request()->fullUrl() }}" />
        <meta property="og:site_name" content="{{ config('app.name','Connect Jobs') }}" />
        <meta name="twitter:card" content="summary_large_image" />
        <meta name="twitter:title" content="@yield('meta_title', config('app.name','Connect Jobs'))" />
        <meta name="twitter:description" content="@yield('meta_description','منصة وظائف تربط الشركات بالكوادر الموهوبة في العراق والشرق الأوسط')" />


        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        <!-- CDN styles/scripts instead of Vite for production -->
        <link href="https://cdn.jsdelivr.net/npm/daisyui@4.12.10/dist/full.min.css" rel="stylesheet" type="text/css" />
        <script src="https://cdn.tailwindcss.com"></script>
        <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
        <style>
            :root[data-theme='brand']{
                --p: 222 76% 21%; /* primary (navy) */
                --pc: 0 0% 100%;   /* primary content (white) */
                --s: 39 57% 59%;   /* secondary (gold) */
                --sc: 222 76% 21%; /* secondary content (navy) */
                --a: 44 72% 66%;   /* accent (gold light) */
                --ac: 222 76% 21%;
                --b1: 0 0% 100%;   /* base-100 */
                --b2: 220 20% 98%;
                --b3: 220 14% 96%;
                --bc: 222 43% 20%;  /* base-content */
            }
            :root[data-theme='brand-dark']{
                --p: 222 76% 21%;  /* primary (navy) */
                --pc: 0 0% 100%;   /* white */
                --s: 39 57% 59%;   /* secondary (gold) */
                --sc: 222 76% 21%; /* navy */
                --a: 44 72% 66%;
                --ac: 220 34% 16%;
                --b1: 220 34% 16%;
                --b2: 220 34% 12%;
                --b3: 220 34% 10%;
                --bc: 0 0% 100%;
            }
        </style>
        <script>(function(){var t=localStorage.getItem('theme')||'brand';document.documentElement.setAttribute('data-theme',t);})();</script>
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-base-200">
            @include('layouts.navigation')

            @if (session('status'))
                <div class="max-w-7xl mx-auto mt-4 px-4">
                    <div class="alert alert-success shadow">{{ session('status') }}</div>
                </div>
            @endif

            <!-- Page Heading -->
            @isset($header)
                <header class="bg-white/70 backdrop-blur border-b">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Page Content -->
            <main>
                {{ $slot }}
            </main>
            @include('layouts.footer')

            @stack('scripts')
        </div>
    </body>
</html>
