@extends('layouts.dashboard')

@section('title', 'Bayar Honor - TK Ibnul Qoyyim')
@section('page_title', 'Bayar Honor')

@php
    $rp = static fn ($n) => 'Rp ' . number_format((float) $n, 0, ',', '.');
    $teacherName = $teacher?->name ?? '-';
    $periodLabel = sprintf('%02d/%d', (int) $honor->month, (int) $honor->year);

    // Untuk Alpine.js: data sources sebagai JSON
    $sourcesJson = $sources->map(fn ($s) => [
        'id' => (int) $s->id,
        'name' => $s->name,
        'balance' => (float) ($balances[$s->code] ?? 0),
    ])->values();
@endphp

@section('content')

<x-ui.page-header :title="'Bayar Honor: ' . $teacherName">
    <x-slot:action>
        <a href="{{ route('admin.bendahara.honors.index') }}" class="ui-btn ui-btn--ghost">
            ← Kembali
        </a>
    </x-slot:action>
</x-ui.page-header>

@if(session('success'))
    <x-ui.toast variant="success">{{ session('success') }}</x-ui.toast>
@endif

@if($errors->any())
    <x-ui.toast variant="danger">
        @foreach($errors->all() as $err)
            <div>{{ $err }}</div>
        @endforeach
    </x-ui.toast>
@endif

{{-- Header info: periode + status --}}
<div class="ui-section" style="display: flex; align-items: center; justify-content: space-between; gap: var(--ui-space-md); flex-wrap: wrap;">
    <div>
        <div class="ui-stat-card__label">Periode</div>
        <div style="font-size: var(--ui-font-lg); font-weight: 600;">{{ $periodLabel }}</div>
    </div>
    <div>
        @if($isLocked)
            <x-ui.badge variant="success">Sudah dibayar {{ $honor->payment_date?->format('d M Y') }}</x-ui.badge>
        @else
            <x-ui.badge variant="warning">Belum dibayar</x-ui.badge>
        @endif
    </div>
</div>

{{-- Statistik absensi compact 1 baris --}}
<div class="ui-section">
    <h3 class="ui-section__title">Statistik Absensi</h3>
    <div style="display: flex; flex-wrap: wrap; gap: var(--ui-space-lg); font-size: var(--ui-font-sm);">
        <div><strong>{{ (int) $honor->attendance_count }}</strong> Hadir</div>
        <div><strong>{{ (int) $honor->permission_count }}</strong> Izin</div>
        <div><strong>{{ (int) $honor->sickness_count }}</strong> Sakit</div>
        <div><strong>{{ (int) $honor->absence_count }}</strong> Alpa</div>
        <div><strong>{{ (int) $honor->late_count }}</strong> Telat</div>
        <div><strong>{{ (int) $honor->holiday_credit_count }}</strong> Kredit Libur</div>
        <div style="color: var(--color-primary); font-weight: 600;">
            Hadir Efektif: {{ (int) $honor->effective_attendance_count }}
        </div>
    </div>
</div>

{{-- Detail gaji (3 komponen + total) --}}
<div class="ui-section">
    <h3 class="ui-section__title">Detail Gaji</h3>
    <div class="ui-table-wrapper" style="box-shadow: none; border: none;">
        <table class="ui-table">
            <tbody>
                @foreach($components as $key => $row)
                    @php
                        $isPotongan = $key === 'potongan';
                        $sign = $isPotongan ? '−' : '+';
                        $amount = (float) ($row['amount'] ?? 0);
                    @endphp
                    <tr>
                        <td style="width: 45%;">
                            <strong>{{ $row['label'] }}</strong>
                            <div class="ui-stat-card__hint" style="margin-top: 2px;">{{ $row['detail'] }}</div>
                        </td>
                        <td style="width: 35%; text-align: right; font-weight: 600;">
                            {{ $sign }} {{ $rp($amount) }}
                        </td>
                        <td style="width: 20%; text-align: right;">
                            @unless($isLocked)
                                <button
                                    type="button"
                                    class="ui-btn ui-btn--ghost ui-btn--sm"
                                    onclick="window.dispatchEvent(new CustomEvent('open-modal', { detail: 'isi-gaji-{{ $key }}' }))"
                                >
                                    Edit
                                </button>
                            @endunless
                        </td>
                    </tr>
                @endforeach
                <tr style="background: var(--color-primary-soft);">
                    <td><strong>TOTAL</strong></td>
                    <td style="text-align: right; font-size: var(--ui-font-lg); font-weight: 700; color: var(--color-primary-dark);">
                        {{ $rp($honor->amount) }}
                    </td>
                    <td></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

{{-- Alokasi sumber dana + tombol Bayar --}}
@unless($isLocked)
<div
    class="ui-section"
    x-data='honorPayForm({
        amount: {{ (float) $honor->amount }},
        sources: @json($sourcesJson)
    })'
>
    <h3 class="ui-section__title">Alokasi Sumber Dana</h3>
    <p class="ui-stat-card__hint" style="margin-bottom: var(--ui-space-md);">
        Total alokasi harus = <strong>{{ $rp($honor->amount) }}</strong>
    </p>

    <form method="POST" action="{{ route('admin.bendahara.honors.pay', $honor) }}" @submit="onSubmit">
        @csrf

        <template x-for="(split, idx) in splits" :key="idx">
            <div style="display: grid; grid-template-columns: 2fr 1fr auto; gap: var(--ui-space-sm); margin-bottom: var(--ui-space-sm);">
                <select :name="`splits[${idx}][id_fund_source]`" x-model.number="split.id_fund_source" required class="ui-select">
                    <option value="">— Pilih Sumber —</option>
                    <template x-for="s in sources" :key="s.id">
                        <option :value="s.id" x-text="`${s.name} (saldo ${formatRp(s.balance)})`"></option>
                    </template>
                </select>
                <input
                    type="number"
                    :name="`splits[${idx}][amount]`"
                    x-model.number="split.amount"
                    min="1"
                    step="0.01"
                    required
                    class="ui-input"
                    placeholder="Nominal"
                >
                <button type="button" @click="removeSplit(idx)" class="ui-btn ui-btn--ghost ui-btn--sm" x-show="splits.length > 1">
                    Hapus
                </button>
            </div>
        </template>

        <div style="margin-bottom: var(--ui-space-md);">
            <button type="button" @click="addSplit" class="ui-btn ui-btn--secondary ui-btn--sm">
                + Tambah Alokasi
            </button>
        </div>

        <div style="padding: var(--ui-space-md); background: var(--surface-hover); border-radius: var(--ui-radius-sm); margin-bottom: var(--ui-space-md);">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <span>Sisa alokasi:</span>
                <strong x-text="formatRp(remaining)" :style="remaining === 0 ? 'color: var(--color-primary-dark);' : 'color: var(--color-warning);'"></strong>
            </div>
            <div x-show="warning" style="margin-top: var(--ui-space-xs); color: var(--color-warning); font-size: var(--ui-font-xs);" x-text="warning"></div>
        </div>

        <div class="ui-form-group">
            <label class="ui-form-label">Tanggal Bayar</label>
            <input type="date" name="payment_date" value="{{ now()->toDateString() }}" required class="ui-input" style="max-width: 240px;">
        </div>

        <button type="submit" class="ui-btn ui-btn--primary" :disabled="remaining !== 0">
            Bayar Sekarang
        </button>
    </form>
</div>

{{-- Modal isi gaji per komponen --}}
@foreach($components as $key => $row)
    <x-ui.modal name="isi-gaji-{{ $key }}" :title="'Isi ' . $row['label']" maxWidth="360px">
        <form method="POST" action="{{ route('admin.bendahara.honors.update-component', $honor) }}">
            @csrf
            @method('PUT')
            <input type="hidden" name="component" value="{{ $key }}">

            <div class="ui-form-group">
                <label class="ui-form-label">Nominal</label>
                <input
                    type="number"
                    name="amount"
                    min="0"
                    step="0.01"
                    value="{{ (float) $row['amount'] }}"
                    required
                    class="ui-input"
                    autofocus
                >
            </div>

            <div style="display: flex; gap: var(--ui-space-sm); justify-content: flex-end;">
                <button type="button" class="ui-btn ui-btn--secondary" onclick="window.dispatchEvent(new CustomEvent('close-modal', { detail: 'isi-gaji-{{ $key }}' }))">Batal</button>
                <button type="submit" class="ui-btn ui-btn--primary">Simpan</button>
            </div>
        </form>
    </x-ui.modal>
@endforeach

<script>
    function honorPayForm({ amount, sources }) {
        return {
            sources,
            splits: [
                { id_fund_source: '', amount: amount }
            ],
            get totalSplit() {
                return this.splits.reduce((sum, s) => sum + (Number(s.amount) || 0), 0);
            },
            get remaining() {
                return Math.round((amount - this.totalSplit) * 100) / 100;
            },
            get warning() {
                for (const s of this.splits) {
                    const src = this.sources.find(x => Number(x.id) === Number(s.id_fund_source));
                    if (src && Number(s.amount) > Number(src.balance)) {
                        return `${src.name} tidak cukup (saldo ${this.formatRp(src.balance)}). Pembayaran tetap bisa dilakukan, saldo akan minus.`;
                    }
                }
                return null;
            },
            formatRp(n) {
                return 'Rp ' + Number(n).toLocaleString('id-ID', { maximumFractionDigits: 0 });
            },
            addSplit() {
                this.splits.push({ id_fund_source: '', amount: this.remaining > 0 ? this.remaining : 0 });
            },
            removeSplit(idx) {
                this.splits.splice(idx, 1);
            },
            onSubmit(e) {
                if (this.remaining !== 0) {
                    e.preventDefault();
                    alert('Sisa alokasi harus 0 (total split = nominal honor).');
                    return false;
                }
                if (!confirm(`Yakin bayar honor sebesar ${this.formatRp(amount)}?`)) {
                    e.preventDefault();
                    return false;
                }
                return true;
            },
        };
    }
</script>
@endunless

@endsection
