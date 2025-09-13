<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Connect Jobs') }}</title>

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
                --p: 39 57% 59%; /* gold primary */
                --pc: 222 76% 21%; /* primary content (navy) */
                --s: 222 76% 21%; /* secondary (navy) */
                --sc: 0 0% 100%;  /* secondary content (white) */
                --a: 44 72% 66%;  /* accent (gold light) */
                --ac: 222 76% 21%;
                --b1: 0 0% 100%;
                --b2: 220 20% 98%;
                --b3: 220 14% 96%;
                --bc: 222 43% 20%;
            }
            :root[data-theme='brand-dark']{
                --p: 39 57% 59%;
                --pc: 220 34% 16%;
                --s: 222 76% 21%;
                --sc: 0 0% 100%;
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
        <!-- Top Guest Header with Login/Register -->
        <header class="w-full bg-white/80 backdrop-blur border-b">
            <div class="max-w-7xl mx-auto px-4 py-3 flex items-center justify-between">
                <a href="/" class="flex items-center gap-2">
                    <x-application-logo class="w-8 h-8 fill-current text-gray-700" />
                    <span class="font-semibold text-gray-800">Connect Jobs</span>
                </a>
                <div class="flex items-center gap-2">
                    <a href="{{ route('login') }}" class="btn btn-ghost btn-sm">تسجيل الدخول</a>
                    <a href="{{ route('register') }}" class="btn btn-primary btn-sm">إنشاء حساب</a>
                </div>
            </div>
        </header>
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-base-200">
            <div>
                <a href="/">
                    <x-application-logo class="w-20 h-20 fill-current text-gray-500" />
                </a>
            </div>

            <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white dark:bg-gray-800 shadow-md overflow-hidden sm:rounded-lg">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
