@props(['eyebrow', 'title', 'description' => null])
<div {{ $attributes }}><p class="eyebrow">{{ $eyebrow }}</p><h2 class="heading">{{ $title }}</h2>@if($description)<p class="mt-5 max-w-2xl text-base leading-7 text-muted">{{ $description }}</p>@endif</div>
