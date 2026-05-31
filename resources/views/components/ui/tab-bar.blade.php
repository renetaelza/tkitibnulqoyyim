@props([
    'tabs' => [],
    'active' => null,
])

{{--
  Tab bar horizontal. Item dari array:
    $tabs = [
        ['key' => 'daftar', 'label' => 'Daftar', 'url' => '?tab=daftar'],
        ['key' => 'bayar', 'label' => 'Pembayaran', 'url' => '?tab=bayar'],
    ];
    <x-ui.tab-bar :tabs="$tabs" :active="$activeTab" />
--}}

<div class="ui-tab-bar" role="tablist">
    @foreach($tabs as $tab)
        <a
            href="{{ $tab['url'] ?? '#' }}"
            class="ui-tab-bar__item {{ ($tab['key'] ?? null) === $active ? 'ui-tab-bar__item--active' : '' }}"
            role="tab"
            aria-selected="{{ ($tab['key'] ?? null) === $active ? 'true' : 'false' }}"
        >
            {{ $tab['label'] ?? '' }}
            @if(!empty($tab['count']))
                <span class="ui-badge ui-badge--neutral" style="margin-left: 4px;">{{ $tab['count'] }}</span>
            @endif
        </a>
    @endforeach
</div>
