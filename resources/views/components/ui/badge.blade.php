@props([
    'variant' => 'neutral',
])

{{-- Badge pill. Variant: success | danger | warning | info | neutral --}}

<span {{ $attributes->merge(['class' => 'ui-badge ui-badge--' . $variant]) }}>
    {{ $slot }}
</span>
