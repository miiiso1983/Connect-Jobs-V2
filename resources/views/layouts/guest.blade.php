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
        @php($metaDescription = trim($__env->yieldContent('meta_description','')))
        @php($metaDescription = $metaDescription !== '' ? $metaDescription : 'منصة وظائف تربط الشركات بالكوادر الموهوبة في العراق والشرق الأوسط')
        <meta property="og:description" content="{{ $metaDescription }}" />

        {{-- <meta property="og:description" content="@yield('meta_description','
















منصة وظائف تربط الشركات بالكوادر الموهوبة في العراق والشرق الأوسط')" /> --}}
        <meta property="og:url" content="{{ request()->fullUrl() }}" />
        <meta property="og:site_name" content="{{ config('app.name','Connect Jobs') }}" />
        @php($metaImage = trim($__env->yieldContent('meta_image','')))
        @php($metaImage = $metaImage !== '' ? $metaImage : asset('favicon.ico'))
        <meta property="og:image" content="{{ $metaImage }}" />
        <meta name="twitter:image" content="{{ $metaImage }}" />
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
	                --p: 264 100% 36%; /* primary (purple) #4A00B8 */
                --pc: 0 0% 100%;   /* primary content (white) */
	                --s: 198 93% 60%;   /* secondary (light blue) #38BDF8 */
	                --sc: 264 100% 29%;  /* secondary content (purple dark) #3C0094 */
	                --a: 199 89% 48%;   /* accent (blue) #0EA5E9 */
	                --ac: 0 0% 100%;    /* accent content (white) */
                --b1: 0 0% 100%;
                --b2: 220 20% 98%;
                --b3: 220 14% 96%;
                --bc: 222 43% 20%;
            }
            :root[data-theme='brand-dark']{
	                --p: 264 100% 44%;  /* primary (purple light) #5A00E1 */
                --pc: 0 0% 100%;   /* white */
	                --s: 198 93% 60%;   /* secondary (light blue) #38BDF8 */
	                --sc: 264 100% 29%;  /* purple dark */
	                --a: 199 89% 48%;   /* accent (blue) #0EA5E9 */
	                --ac: 0 0% 100%;    /* white */
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
	        <header class="w-full text-white bg-gradient-to-r from-[#4A00B8] via-[#5A00E1] to-[#3C0094]">
            <div class="max-w-7xl mx-auto px-4 py-3 flex items-center justify-between">
                <a href="/" class="flex items-center gap-2">
                    <x-application-logo class="h-10 w-auto" />
                    <span class="font-semibold text-white">Connect Jobs</span>
                </a>
                <div class="flex items-center gap-2">
                    <a href="{{ route('login') }}" class="btn btn-ghost btn-sm text-white">تسجيل الدخول</a>
                    <a href="{{ route('register') }}" class="btn btn-secondary btn-sm">إنشاء حساب</a>
                </div>
            </div>
        </header>
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-base-200">
            <div>
                <a href="/">
                    <x-application-logo class="w-20 h-20 fill-current text-gray-500" />
                </a>
            </div>

            <div class="w-full sm:max-w-6xl mt-6 px-0 py-0 bg-transparent shadow-none overflow-visible">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
