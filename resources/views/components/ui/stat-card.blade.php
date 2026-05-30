@props([
    'label' => '',
    'value' => '',
    'hint' => null,
])

{{--
  Stat card kecil. Untuk dashboard. Slot 'action' opsional untuk tombol di bawah.
  Contoh:
    <x-ui.stat-card label="SPP" value="Rp 5.000.000" hint="auto-credit">
        <x-slot:action>
            <button @click="$dispatch('open-modal', 'spp')" class="ui-btn ui-btn--secondary ui-btn--sm">+ Input</button>
        </x-slot:action>
    </x-ui.stat-card>
--}}

<div class="ui-stat-card">
    <div class="ui-stat-card__label">{{ $label }}</div>
    <div class="ui-stat-card__value">{{ $value }}</div>
    @if($hint)
        <div class="ui-stat-card__hint">{{ $hint }}</div>
    @endif
    @isset($action)
        <div class="ui-stat-card__action">{{ $action }}</div>
    @endisset
</div>
