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
    <main class="maintenance-page relative flex min-h-screen items-center justify-center overflow-hidden px-4 py-10 sm:py-14">
        <section class="maintenance-content relative z-10 w-full max-w-lg text-center" aria-labelledby="maintenance-title">
            <div class="maintenance-illustration mx-auto" role="img" aria-label="An animated person works on a laptop beside a safety cone while the website is under maintenance">
                <svg viewBox="0 0 440 290" aria-hidden="true" class="h-auto w-full overflow-visible">
                    <g class="maintenance-leaves" fill="none" stroke="#53a7ca" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M329 253c7-38 24-74 60-102m-44 76c-2-24 4-42 18-55m-2 38c17-8 28-19 37-36m-55 12c-13-14-19-27-19-42m29 68c20-2 34-10 47-24" stroke-width="3"/>
                        <path d="M365 145c-22 5-34 20-35 42 22-4 36-18 35-42Zm29 17c-5 20-18 33-38 39 3-21 17-35 38-39Zm-50 27c-17-2-29-12-36-29 18 1 30 11 36 29Zm42 25c-15 12-31 13-49 5 14-13 30-15 49-5Z" fill="#78bad7" stroke-width="2"/>
                    </g>
                    <g class="maintenance-browser" transform="rotate(-4 214 190)">
                        <path d="M48 98a8 8 0 0 1 8-8h294a8 8 0 0 1 8 8v145H48V98Z" fill="#fff" stroke="#b8ddec" stroke-width="3"/>
                        <path d="M48 99a8 8 0 0 1 8-8h294a8 8 0 0 1 8 8v13H48V99Z" fill="#addcf0"/>
                        <circle cx="63" cy="101" r="3" fill="#e5242b"/><circle cx="74" cy="101" r="3" fill="#ffc94a"/><circle cx="85" cy="101" r="3" fill="#42b883"/>
                        <path d="M153 131h123v91H153z" fill="#f4f7f9"/>
                        <path d="M164 143h43v8h-43zm0 16h94v3h-94zm0 10h103v3H164zm0 10h95v3h-95zm0 10h101v3H164zm0 10h80v3h-80z" fill="#dce3e7"/>
                        <rect x="209" y="207" width="30" height="7" rx="3.5" fill="#a9dff2" class="maintenance-progress"/>
                        <path d="M48 243h310l-14 9H62l-14-9Z" fill="#d6eaf2"/>
                    </g>
                    <g class="maintenance-cone">
                        <ellipse cx="103" cy="249" rx="43" ry="9" fill="#2a7292" opacity=".18"/>
                        <path d="m75 233 15-70c2-8 14-8 16 0l18 70H75Z" fill="#fff" stroke="#4b9cbb" stroke-width="3"/>
                        <path d="m82 203 6-26h21l7 26H82Zm-7 30 5-18h39l5 18H75Z" fill="#f2bd32"/>
                        <path d="M69 234h57c5 0 9 4 9 9v3H60v-3c0-5 4-9 9-9Z" fill="#2d7898"/>
                        <path d="M83 160h27l-4-11H88l-5 11Z" fill="#f2bd32" stroke="#d69b18" stroke-width="2"/>
                        <path d="M88 149v-6m18 6v-6m-23 10h34" stroke="#d69b18" stroke-linecap="round" stroke-width="3"/>
                    </g>
                    <g class="maintenance-worker">
                        <path d="M215 46c0-11 8-20 19-20s20 8 20 20v19h-39V46Z" fill="#e6a77f"/>
                        <path d="M214 45c-3-16 5-27 17-29 13-2 22 7 23 20-6-2-10-6-13-11-5 7-14 11-27 12Z" fill="#254d68"/>
                        <path d="M226 48c2-2 4-2 6 0m10 0c2-2 4-2 6 0m-12 8c3 3 6 3 9 0" fill="none" stroke="#35566a" stroke-linecap="round" stroke-width="2"/>
                        <path d="M211 67c8-8 22-9 32-2l16 38-40 12-14-26 6-22Z" fill="#287a9b"/>
                        <path d="m219 65 14 18-12 9-10-21 8-6Z" fill="#fff"/>
                        <g class="maintenance-laptop">
                            <path d="m251 68 44 2c3 0 5 3 4 6l-11 39-47-2 8-39c0-3 2-6 2-6Z" fill="#fff" stroke="#a6cddd" stroke-width="2"/>
                            <path d="m238 113 55 2 7 5-56-2-6-5Z" fill="#b5dce9"/>
                            <circle cx="274" cy="91" r="3" fill="#d2e2e8"/>
                        </g>
                        <path d="m221 109-7 37 14 38 12-3-7-39 2-29-14-4Z" fill="#344c5a"/>
                        <path d="m240 107 13 3 4 38-8 36-12-2 2-36-7-24 8-15Z" fill="#263f50"/>
                        <path d="m228 181 12 2 3 16-17-1 2-17Zm18 1 12 1 8 16-17-1-3-16Z" fill="#4d6877"/>
                    </g>
                    <g class="maintenance-sparkles" fill="#fff">
                        <path d="m133 127 3 8 8 3-8 3-3 8-3-8-8-3 8-3 3-8Z"/>
                        <path d="m322 79 2 6 6 2-6 2-2 6-2-6-6-2 6-2 2-6Z"/>
                    </g>
                </svg>
            </div>
            <h1 id="maintenance-title" class="maintenance-title mt-2 text-2xl font-extrabold tracking-[.12em] text-primary-dark sm:text-3xl">SITE UNDER MAINTENANCE</h1>
            <p class="maintenance-copy mx-auto mt-5 max-w-md whitespace-pre-line text-sm leading-6 text-primary-dark">{{ $site->get('public_maintenance_message') ?: 'We sincerely apologize for the inconvenience. Our site is currently undergoing scheduled maintenance and upgrades, but will return shortly. Thank you for your patience.' }}</p>
            <div class="maintenance-brand mt-8 flex justify-center"><x-logo /></div>
            @if($site->get('email') || $site->get('phone'))
                <div class="maintenance-contact mt-5 flex flex-wrap justify-center gap-x-6 gap-y-2 text-sm font-medium">
                    @if($site->get('email'))<a class="text-primary hover:text-primary-dark" href="mailto:{{ $site->get('email') }}">{{ $site->get('email') }}</a>@endif
                    @if($site->get('phone'))<a class="text-primary hover:text-primary-dark" href="tel:{{ $site->get('phone') }}">{{ $site->get('phone') }}</a>@endif
                </div>
            @endif
        </section>
    </main>
</body>
</html>
