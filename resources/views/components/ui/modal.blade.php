@props([
    'name' => 'modal',
    'title' => null,
    'maxWidth' => '480px',
])

{{--
  Modal Alpine.js. Pemicu: $dispatch('open-modal', '<name>').
  Penutup: $dispatch('close-modal', '<name>') atau klik backdrop / Esc.
  Contoh:
    <button @click="$dispatch('open-modal', 'input-dana-spp')">Input Dana</button>
    <x-ui.modal name="input-dana-spp" title="Input Dana SPP">
        <form>...</form>
    </x-ui.modal>
--}}

<div
    x-data="{ open: false }"
    x-on:open-modal.window="if ($event.detail === '{{ $name }}') open = true"
    x-on:close-modal.window="if ($event.detail === '{{ $name }}') open = false"
    x-show="open"
    x-cloak
    @keydown.escape.window="open = false"
    style="display: none;"
>
    <div class="ui-modal__backdrop" @click.self="open = false">
        <div class="ui-modal__dialog" style="max-width: {{ $maxWidth }}">
            @if($title)
                <div class="ui-modal__header">
                    <h3 class="ui-modal__title">{{ $title }}</h3>
                    <button type="button" class="ui-modal__close" @click="open = false" aria-label="Tutup">&times;</button>
                </div>
            @endif
            {{ $slot }}
        </div>
    </div>
</div>
