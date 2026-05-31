@extends('layouts.dashboard')

@section('title', 'Honor Guru - TK Ibnul Qoyyim')
@section('page_title', 'Honor Guru')

@php
    $rp = static fn ($n) => 'Rp ' . number_format((float) $n, 0, ',', '.');

    $tabs = [
        [
            'key' => 'bayar',
            'label' => 'Pembayaran',
            'url' => route('admin.bendahara.honors.index', ['tab' => 'bayar']),
            'count' => $tabCounts['bayar'] ?? 0,
        ],
        [
            'key' => 'daftar',
            'label' => 'Daftar Honor',
            'url' => route('admin.bendahara.honors.index', ['tab' => 'daftar']),
            'count' => $tabCounts['daftar'] ?? 0,
        ],
    ];
@endphp

@section('content')

<x-ui.page-header title="Honor Guru">
    <x-slot:action>
        <span style="color: var(--color-muted); font-size: var(--ui-font-sm); margin-right: var(--ui-space-md);">
            Saldo tersedia: <strong style="color: var(--color-text);">{{ $rp($totalBalance) }}</strong>
        </span>
        <button type="button" class="ui-btn ui-btn--primary" onclick="loadGenerateHonor()">
            + Generate Honor
        </button>
    </x-slot:action>
</x-ui.page-header>

@if(session('success'))
    <x-ui.toast variant="success">{{ session('success') }}</x-ui.toast>
@endif

<x-ui.tab-bar :tabs="$tabs" :active="$tab" />

<div class="ui-toolbar">
    <form method="GET" action="{{ route('admin.bendahara.honors.index') }}" class="ui-toolbar__search">
        <input type="hidden" name="tab" value="{{ $tab }}">
        <input type="hidden" name="bulan" value="{{ $bulan }}">
        <input
            type="search"
            name="search"
            value="{{ $search }}"
            class="ui-input"
            placeholder="Cari guru..."
        >
    </form>

    <div class="ui-toolbar__filters">
        <form method="GET" action="{{ route('admin.bendahara.honors.index') }}">
            <input type="hidden" name="tab" value="{{ $tab }}">
            <input type="hidden" name="search" value="{{ $search }}">
            <input
                type="month"
                name="bulan"
                value="{{ $bulan }}"
                class="ui-input"
                onchange="this.form.submit()"
                style="width: auto;"
            >
            @if($bulan)
                <a href="{{ route('admin.bendahara.honors.index', ['tab' => $tab]) }}" class="ui-btn ui-btn--ghost ui-btn--sm">Reset</a>
            @endif
        </form>
    </div>
</div>

@if($honors->isEmpty())
    <x-ui.empty-state
        :icon="$tab === 'bayar' ? '✓' : '📭'"
        :message="$tab === 'bayar' ? 'Semua honor sudah dibayar.' : 'Belum ada honor.'"
    />
@else
    <div class="ui-table-wrapper">
        <table class="ui-table">
            <thead>
                <tr>
                    <th>Periode</th>
                    <th>Guru</th>
                    <th>Hadir Efektif</th>
                    <th>Nominal</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($honors as $h)
                    @php
                        $paid = !is_null($h->payment_date);
                    @endphp
                    <tr>
                        <td>{{ sprintf('%02d/%d', (int) $h->month, (int) $h->year) }}</td>
                        <td><strong>{{ $h->teacher?->name ?? '-' }}</strong></td>
                        <td>{{ (int) ($h->effective_attendance_count ?? 0) }}</td>
                        <td><strong>{{ $rp($h->amount) }}</strong></td>
                        <td>
                            @if($paid)
                                <x-ui.badge variant="success">Paid {{ $h->payment_date?->format('d M') }}</x-ui.badge>
                            @else
                                <x-ui.badge variant="warning">Unpaid</x-ui.badge>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('admin.bendahara.honors.show', $h) }}" class="ui-btn {{ $paid ? 'ui-btn--ghost' : 'ui-btn--primary' }} ui-btn--sm">
                                {{ $paid ? 'Detail' : 'Bayar' }}
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @include('components.dashboard.admin.pagination-controls', [
        'items' => $honors,
        'tab' => $tab,
        'search' => $search,
        'bulan' => $bulan,
        'per_page' => $per_page ?? 15,
    ])
@endif

<x-ui.modal name="generate-honor-modal" title="Generate Honor" maxWidth="640px">
    <div id="generate-honor-content">
        <p class="ui-stat-card__hint">Memuat formulir...</p>
    </div>
</x-ui.modal>

<script>
    function loadGenerateHonor() {
        window.loadFormIntoModal(
            '{{ route('admin.teacher-honors.create') }}',
            'generate-honor-content',
            'generate-honor-modal'
        );
    }
</script>

@include('components.dashboard.admin.admin-scripts')

@endsection
