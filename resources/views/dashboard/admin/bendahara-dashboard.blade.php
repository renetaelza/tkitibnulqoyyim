@extends('layouts.dashboard')

@section('title', 'Dashboard Bendahara - TK Ibnul Qoyyim')
@section('page_title', 'Dashboard Bendahara')

@php
    $rp = static fn ($n) => 'Rp ' . number_format((float) $n, 0, ',', '.');
@endphp

@section('content')

<x-ui.page-header title="Dashboard Bendahara" />

@if(session('success'))
    <x-ui.toast variant="success">{{ session('success') }}</x-ui.toast>
@endif

@if(session('error'))
    <x-ui.toast variant="danger">{{ session('error') }}</x-ui.toast>
@endif

@if($errors->any())
    <x-ui.toast variant="danger">
        @foreach($errors->all() as $err)
            <div>{{ $err }}</div>
        @endforeach
    </x-ui.toast>
@endif

{{-- Total dana ringkas --}}
<div class="ui-section" style="text-align: center;">
    <div class="ui-stat-card__label">Total Dana</div>
    <div style="font-size: var(--ui-font-2xl); font-weight: 700; color: var(--color-primary); margin-top: var(--ui-space-xs);">
        {{ $rp($totalBalance) }}
    </div>
</div>

{{-- 9 sumber dana --}}
<div class="ui-stat-card-grid" style="margin-bottom: var(--ui-space-lg);">
    @foreach($sources as $source)
        @php
            $balance = $balances[$source->code] ?? 0;
            $modalName = 'input-dana-' . $source->id;
        @endphp
        <x-ui.stat-card
            :label="$source->name"
            :value="$rp($balance)"
            :hint="$source->is_auto_credit ? 'auto-credit' : 'manual input'"
        >
            <x-slot:action>
                <button
                    type="button"
                    class="ui-btn ui-btn--secondary ui-btn--sm"
                    style="width: 100%;"
                    onclick="window.dispatchEvent(new CustomEvent('open-modal', { detail: '{{ $modalName }}' }))"
                >
                    + Input Dana
                </button>
            </x-slot:action>
        </x-ui.stat-card>
    @endforeach
</div>

{{-- Honor outstanding --}}
<div class="ui-section">
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: var(--ui-space-md);">
        <h3 class="ui-section__title" style="margin: 0;">
            Honor Outstanding
            @if($honorsOutstandingCount > 0)
                <x-ui.badge variant="warning">{{ $honorsOutstandingCount }}</x-ui.badge>
            @endif
        </h3>
        @if($honorsOutstandingCount > 0)
            <span class="ui-stat-card__hint">Total {{ $rp($honorsOutstandingTotal) }}</span>
        @endif
    </div>

    @if($honorsOutstanding->isEmpty())
        <x-ui.empty-state icon="✓" message="Tidak ada honor outstanding." />
    @else
        <div class="ui-table-wrapper" style="box-shadow: none; border: none;">
            <table class="ui-table">
                <thead>
                    <tr>
                        <th>Guru</th>
                        <th>Periode</th>
                        <th>Nominal</th>
                        <th style="width: 100px;"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($honorsOutstanding as $honor)
                        <tr>
                            <td>{{ $honor->teacher?->name ?? '-' }}</td>
                            <td>{{ sprintf('%02d/%d', (int) $honor->month, (int) $honor->year) }}</td>
                            <td><strong>{{ $rp($honor->amount) }}</strong></td>
                            <td>
                                <a href="{{ route('admin.bendahara.honors.show', $honor) }}" class="ui-btn ui-btn--primary ui-btn--sm">
                                    Bayar
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div style="margin-top: var(--ui-space-md); text-align: right;">
            <a href="{{ route('admin.bendahara.honors.index') }}" class="ui-btn ui-btn--ghost">
                Lihat semua →
            </a>
        </div>
    @endif
</div>

{{-- Riwayat transaksi singkat --}}
<div class="ui-section">
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: var(--ui-space-md);">
        <h3 class="ui-section__title" style="margin: 0;">5 Transaksi Terakhir</h3>
    </div>

    @if($recentTransactions->isEmpty())
        <x-ui.empty-state icon="📭" message="Belum ada transaksi." />
    @else
        <div class="ui-table-wrapper" style="box-shadow: none; border: none;">
            <table class="ui-table">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Sumber</th>
                        <th>Keterangan</th>
                        <th style="text-align: right;">Nominal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentTransactions as $tx)
                        <tr>
                            <td>{{ $tx->transaction_date?->format('d M Y') ?? '-' }}</td>
                            <td>{{ $tx->fundSource?->name ?? '-' }}</td>
                            <td style="color: var(--color-muted);">{{ \Illuminate\Support\Str::limit($tx->description ?? '-', 40) }}</td>
                            <td style="text-align: right; font-weight: 600;">
                                @if($tx->direction === 'in')
                                    <span style="color: var(--color-primary-dark);">+{{ $rp($tx->amount) }}</span>
                                @else
                                    <span style="color: var(--color-danger);">−{{ $rp($tx->amount) }}</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if(Route::has('admin.bendahara.transactions.index'))
            <div style="margin-top: var(--ui-space-md); text-align: right;">
                <a href="{{ route('admin.bendahara.transactions.index') }}" class="ui-btn ui-btn--ghost">
                    Riwayat lengkap →
                </a>
            </div>
        @endif
    @endif
</div>

{{-- Modal Input Dana per Source --}}
@foreach($sources as $source)
    <x-ui.modal name="input-dana-{{ $source->id }}" title="Input Dana — {{ $source->name }}">
        <form method="POST" action="{{ route('admin.bendahara.fund.store') }}" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="id_fund_source" value="{{ $source->id }}">

            <div class="ui-form-group">
                <label class="ui-form-label">Nominal</label>
                <input type="number" name="amount" min="1" step="1" required class="ui-input" placeholder="Contoh: 150000">
            </div>

            <div class="ui-form-group">
                <label class="ui-form-label">Tanggal</label>
                <input type="date" name="transaction_date" value="{{ now()->toDateString() }}" required class="ui-input">
            </div>

            <div class="ui-form-group">
                <label class="ui-form-label">Keterangan</label>
                <input type="text" name="description" maxlength="500" class="ui-input" placeholder="Opsional">
            </div>

            <div class="ui-form-group">
                <label class="ui-form-label">Bukti</label>
                <input type="file" name="attachment" accept=".pdf,.jpg,.jpeg,.png" required class="ui-input">
                <span class="ui-form-hint">PDF/JPG/PNG, max 2 MB</span>
            </div>

            <div style="display: flex; gap: var(--ui-space-sm); justify-content: flex-end;">
                <button type="button" class="ui-btn ui-btn--secondary" onclick="window.dispatchEvent(new CustomEvent('close-modal', { detail: 'input-dana-{{ $source->id }}' }))">Batal</button>
                <button type="submit" class="ui-btn ui-btn--primary">Simpan</button>
            </div>
        </form>
    </x-ui.modal>
@endforeach

@endsection
