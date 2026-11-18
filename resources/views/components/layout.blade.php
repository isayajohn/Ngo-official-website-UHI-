@props(['title' => null, 'description' => null, 'image' => null])
@php
    $faviconPath = $site->get('favicon');
    $faviconUrl = filled($faviconPath) && strtolower(pathinfo($faviconPath, PATHINFO_EXTENSION)) === 'ico'
        ? Storage::disk('public')->url($faviconPath)
        : asset('favicon.ico');
@endphp
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>{{ $title ?: $site->get('default_meta_title') }} | {{ $site->get('abbreviation') }}</title>
<meta name="description" content="{{ $description ?: $site->get('default_meta_description') }}">
<link rel="canonical" href="{{ url()->current() }}">
<meta property="og:type" content="website"><meta property="og:site_name" content="{{ $site->get('organization_name') }}">
<meta property="og:title" content="{{ $title ?: $site->get('default_meta_title') }} | {{ $site->get('abbreviation') }}">
<meta property="og:description" content="{{ $description ?: $site->get('default_meta_description') }}"><meta property="og:url" content="{{ url()->current() }}">
<meta property="og:image" content="{{ $image ?: $site->image('default_og_image', 'images/community-care.jpg') }}">
<meta name="twitter:card" content="summary_large_image">
<link rel="icon" href="{{ $faviconUrl }}" type="image/x-icon" sizes="any">
<meta name="theme-color" content="#06477F">
@vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body x-data="scrollChrome" @scroll.window.passive="update()">
<div class="scroll-progress-track" aria-hidden="true"><span class="scroll-progress-bar" :style="`width: ${progress}%`"></span></div>
<a href="#main" class="sr-only focus:not-sr-only focus:fixed focus:top-4 focus:left-4 focus:z-[100] focus:rounded-lg focus:bg-white focus:p-4 focus:text-primary">Skip to content</a>
<x-header />
@if(app()->getLocale() === 'sw')<div class="bg-canvas px-4 py-3 text-center text-sm text-muted" role="status">Kiswahili content is being prepared. English is shown where a translation is not yet available.</div>@endif
<main id="main">{{ $slot }}</main>
<x-footer />
<button x-cloak x-show="visible" x-transition.opacity.duration.200ms type="button" class="back-to-top" aria-label="Back to top" title="Back to top" @click="toTop()"><x-ughi-icon name="arrow-up" class="h-5 w-5"/></button>
</body>
</html>
