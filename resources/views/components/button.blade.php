@props(['href', 'secondary' => false, 'arrow' => true])
<a href="{{ $href }}" {{ $attributes->class(['btn', 'btn-secondary' => $secondary, 'btn-primary' => !$secondary]) }}>{{ $slot }}@if($arrow)<x-ughi-icon class="h-4 w-4" />@endif</a>
