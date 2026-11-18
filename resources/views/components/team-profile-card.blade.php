@props(['member'])
<article class="card team-profile-card overflow-hidden">
    @if($member->photo)
        <img src="{{ Storage::disk('public')->url($member->photo) }}" alt="{{ $member->photo_alt ?: $member->name }}" class="aspect-[4/3] w-full bg-white object-cover object-top">
    @else
        <div class="flex aspect-[4/3] items-center justify-center bg-primary/5 text-primary"><x-ughi-icon name="users" class="h-16 w-16"/></div>
    @endif
    <div class="p-6 text-center">
        <h3 class="text-xl font-bold">{{ $member->name }}</h3>
        <p class="mt-2 text-sm font-semibold text-primary">{{ $member->leadership_role ?: ($member->position?->title ?: 'UGHI leadership') }}</p>
        @if($member->short_bio)<p class="mt-4 text-sm leading-7 text-muted">{{ $member->short_bio }}</p>@endif
        @if($member->bio)<details class="mt-4 text-left"><summary class="cursor-pointer text-sm font-semibold text-primary hover:text-primary-dark">Read Full Bio</summary><p class="mt-3 whitespace-pre-line text-sm leading-7 text-muted">{{ $member->bio }}</p></details>@endif
        @if($member->email)<a href="mailto:{{ $member->email }}" class="text-link mt-5">Contact {{ $member->name }}<x-ughi-icon name="arrow-up-right" class="h-4 w-4"/></a>@endif
    </div>
</article>
