<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Maintenance | {{ $site->get('organization_name') }}</title>
    <meta name="theme-color" content="#06477F">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-canvas text-ink">
    <main class="relative flex min-h-screen items-center justify-center overflow-hidden px-4 py-12">
        <div class="pointer-events-none absolute -left-32 -top-32 h-96 w-96 rounded-full border-[3rem] border-primary/10"></div>
        <div class="pointer-events-none absolute -bottom-48 -right-28 h-[32rem] w-[32rem] rounded-full border-[4rem] border-primary/10"></div>
        <section class="relative z-10 w-full max-w-xl rounded-3xl border border-line bg-white p-8 text-center shadow-[0_24px_80px_rgb(6_71_127_/_12%)] sm:p-12" aria-labelledby="maintenance-title">
            <div class="flex justify-center"><x-logo /></div>
            <span class="mx-auto mt-9 flex h-16 w-16 items-center justify-center rounded-2xl bg-primary/10 text-primary"><x-ughi-icon name="wrench" class="h-8 w-8"/></span>
            <p class="eyebrow mt-7 justify-center">WE’LL BE BACK SOON</p>
            <h1 id="maintenance-title" class="text-3xl font-extrabold tracking-[-.04em] sm:text-4xl">We’re making improvements</h1>
            <p class="mt-5 whitespace-pre-line text-base leading-7 text-muted">{{ $site->get('public_maintenance_message') ?: 'Our website is temporarily unavailable while we make improvements. Please check back soon.' }}</p>
            @if($site->get('email') || $site->get('phone'))
                <div class="mt-8 flex flex-wrap justify-center gap-x-6 gap-y-3 border-t border-line pt-6 text-sm font-medium">
                    @if($site->get('email'))<a class="text-primary hover:text-primary-dark" href="mailto:{{ $site->get('email') }}">{{ $site->get('email') }}</a>@endif
                    @if($site->get('phone'))<a class="text-primary hover:text-primary-dark" href="tel:{{ $site->get('phone') }}">{{ $site->get('phone') }}</a>@endif
                </div>
            @endif
        </section>
    </main>
</body>
</html>
