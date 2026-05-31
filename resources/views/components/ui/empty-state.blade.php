@props([
    'icon' => '📭',
    'message' => 'Belum ada data.',
])

<div class="ui-empty-state">
    <div class="ui-empty-state__icon">{{ $icon }}</div>
    <div class="ui-empty-state__message">{{ $message }}</div>
    @isset($action)
        {{ $action }}
    @endisset
</div>
