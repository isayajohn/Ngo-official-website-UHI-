@props(['title' => null, 'description' => null, 'image' => null])
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
<link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
<meta name="theme-color" content="#06477F">
@vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
<a href="#main" class="sr-only focus:not-sr-only focus:fixed focus:top-4 focus:left-4 focus:z-[100] focus:rounded-lg focus:bg-white focus:p-4 focus:text-primary">Skip to content</a>
<x-header />
@if(app()->environment(['local', 'testing']))<div class="demo-preview-notice" role="note"><strong>DEMO PREVIEW</strong><span>Sample content and figures are illustrative only.</span></div>@endif
@if(app()->getLocale() === 'sw')<div class="bg-canvas px-4 py-3 text-center text-sm text-muted" role="status">Kiswahili content is being prepared. English is shown where a translation is not yet available.</div>@endif
<main id="main">{{ $slot }}</main>
<x-footer />
</body>
</html>
