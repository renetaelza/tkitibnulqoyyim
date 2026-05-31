@props([
    'variant' => 'success',
    'duration' => 3000,
])

{{--
  Toast auto-dismiss. Untuk session flash:
    @if(session('success'))
        <x-ui.toast variant="success">{{ session('success') }}</x-ui.toast>
    @endif
--}}

<div
    x-data="{ show: true }"
    x-show="show"
    x-init="setTimeout(() => show = false, {{ (int) $duration }})"
    x-cloak
    @click="show = false"
    class="ui-toast ui-toast--{{ $variant }}"
    role="alert"
>
    {{ $slot }}
</div>
