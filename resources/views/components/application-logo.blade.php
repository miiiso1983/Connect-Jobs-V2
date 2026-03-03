@php
    // Prefer the horizontal web logo when available; fallback to the square logo.
    $logoSrc = file_exists(public_path('images/brand/logo-horizontal.png'))
        ? asset('images/brand/logo-horizontal.png')
        : asset('images/brand/logo.png');
@endphp

<img src="{{ $logoSrc }}" alt="Connect Jobs" {{ $attributes->merge(['class' => 'h-10 w-auto object-contain']) }}>
