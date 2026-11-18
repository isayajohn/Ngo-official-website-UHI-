@props(['label' => null])

<div {{ $attributes->class(['ughi-data-table-shell']) }}>
    @isset($toolbar)
        <div class="ughi-data-table-toolbar">
            {{ $toolbar }}
        </div>
    @endisset

    <div class="ughi-data-table-scroll">
        <table class="ughi-data-table" @if($label) aria-label="{{ $label }}" @endif>
            {{ $slot }}
        </table>
    </div>
</div>
