@props([
    'title' => '',
])

{{--
  Header halaman: judul kiri + tombol primary kanan.
  Contoh:
    <x-ui.page-header title="Honor Guru">
        <x-slot:action>
            <a href="..." class="ui-btn ui-btn--primary">+ Generate</a>
        </x-slot:action>
    </x-ui.page-header>
--}}

<div class="ui-page-header">
    <h1 class="ui-page-header__title">{{ $title }}</h1>
    @isset($action)
        <div>{{ $action }}</div>
    @endisset
</div>
