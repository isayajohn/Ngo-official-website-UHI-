@props(['name' => 'arrow', 'class' => 'h-5 w-5'])
<svg {{ $attributes->merge(['class' => $class]) }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
@switch($name)
@case('arrow') <path d="M5 12h14m-6-6 6 6-6 6"/> @break
@case('arrow-left') <path d="M19 12H5m6-6-6 6 6 6"/> @break
@case('arrow-right') <path d="M5 12h14m-6-6 6 6-6 6"/> @break
@case('chevron') <path d="m6 9 6 6 6-6"/> @break
@case('heart') <path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.7l-1.1-1.1a5.5 5.5 0 0 0-7.8 7.8L12 21l8.8-8.6a5.5 5.5 0 0 0 0-7.8Z"/><path d="M4 11h4l2-3 3 7 2-4h5"/> @break
@case('users') <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2m20 0v-2a4 4 0 0 0-3-3.9"/><circle cx="9" cy="7" r="4"/><path d="M16 3.1a4 4 0 0 1 0 7.8"/> @break
@case('shield') <path d="m12 3 8 4v5c0 5-8 9-8 9s-8-4-8-9V7l8-4Z"/><path d="m8 12 3 3 5-6"/> @break
@case('activity') <path d="M2 12h5l3-9 4 18 3-9h5"/> @break
@case('sun') <circle cx="12" cy="12" r="4"/><path d="M12 2v2m0 16v2M2 12h2m16 0h2M5 5l1.4 1.4m11.2 11.2L19 19M5 19l1.4-1.4M17.6 6.4 19 5"/> @break
@case('droplet') <path d="M12 2S4 10 4 15a8 8 0 0 0 16 0c0-5-8-13-8-13Z"/> @break
@case('book') <path d="M12 5c-3-2-6-2-10-1v15c4-1 7-1 10 1 3-2 6-2 10-1V4c-4-1-7-1-10 1Zm0 0v15"/> @break
@case('pin') <path d="M20 10c0 6-8 12-8 12S4 16 4 10a8 8 0 1 1 16 0Z"/><circle cx="12" cy="10" r="2.5"/> @break
@case('hospital') <path d="M3 21h18M5 21V5h14v16M9 5V3h6v2M9 10h2m2 0h2m-6 4h2m2 0h2m-6 7v-4h4v4"/><path d="M11 6v4m-2-2h4"/> @break
@case('branch') <rect x="3" y="3" width="6" height="6" rx="1"/><rect x="15" y="3" width="6" height="6" rx="1"/><rect x="9" y="15" width="6" height="6" rx="1"/><path d="M6 9v2a4 4 0 0 0 4 4h4a4 4 0 0 0 4-4V9"/> @break
@case('grid') <rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/> @break
@case('check') <path d="m5 12 4 4L19 6"/> @break
@case('menu') <path d="M4 6h16M4 12h16M4 18h16"/> @break
@case('close') <path d="m6 6 12 12M6 18 18 6"/> @break
@case('globe') <circle cx="12" cy="12" r="10"/><ellipse cx="12" cy="12" rx="4" ry="10"/><path d="M2 12h20"/> @break
@case('mail') <rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 6 9 7 9-7"/> @break
@case('phone') <path d="M6 3h3l2 5-2.5 1.8a14 14 0 0 0 5.7 5.7L16 13l5 2v3c0 1.1-.9 2-2 2C10.7 20 4 13.3 4 5a2 2 0 0 1 2-2Z"/> @break
@case('message-circle') <path d="M20 11.5a7.5 7.5 0 0 1-8 7.5 8.5 8.5 0 0 1-3.7-.9L4 20l1.5-3.7A7.5 7.5 0 1 1 20 11.5Z"/> @break
@case('wrench') <path d="M14.7 6.3a5 5 0 0 0-6.4 6.4L3.6 17.4a2.1 2.1 0 0 0 3 3l4.7-4.7a5 5 0 0 0 6.4-6.4l-3 3-3-3 3-3Z"/> @break
@case('arrow-up-right') <path d="M7 17 17 7m-8 0h8v8"/> @break
@case('leaf') <path d="M20 3C9 1 2 7 5 15s16 4 15-12ZM4 21 15 10"/> @break
@endswitch
</svg>
