@props(['member', 'trail' => []])
@php
    $isCycle = in_array($member->id, $trail, true);
    $nextTrail = [...$trail, $member->id];
    $children = $isCycle ? collect() : $member->leadershipChildren;
@endphp
<div class="leadership-node">
    <article class="leadership-hierarchy-card">
        @if($member->photo)<img src="{{ Storage::disk('public')->url($member->photo) }}" alt="{{ $member->photo_alt ?: $member->name }}" class="leadership-hierarchy-photo">@else<span class="leadership-hierarchy-photo leadership-photo-placeholder"><x-ughi-icon name="users" class="h-10 w-10"/></span>@endif
        <h3 class="mt-3 text-sm font-bold">{{ $member->name }}</h3>
        <p class="mt-1 text-xs font-semibold leading-5 text-primary">{{ $member->leadership_role ?: ($member->position?->title ?: 'UGHI leadership') }}</p>
    </article>
    @if($children->isNotEmpty())
        <div class="leadership-children">
            @foreach($children as $child)
                @if(!in_array($child->id, $nextTrail, true))@include('components.leadership-node', ['member' => $child, 'trail' => $nextTrail])@endif
            @endforeach
        </div>
    @endif
</div>
