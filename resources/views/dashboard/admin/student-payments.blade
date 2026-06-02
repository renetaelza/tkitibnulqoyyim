@extends('layouts.dashboard')

@section('title', 'Tagihan Murid - TK Ibnul Qoyyim')
@section('page_title', 'Tagihan Murid')

@php
    $rp = static fn ($n) => 'Rp ' . number_format((float) $n, 0, ',', '.');

    $tabs = [
        [
            'key' => 'unpaid',
            'label' => 'Belum Lunas',
            'url' => route('admin.student-payments.index', ['tab' => 'unpaid']),
            'count' => $tabCounts['unpaid'] ?? 0,
        ],
        [
            'key' => 'awaiting_approval',
            'label' => 'Menunggu Approve',
            'url' => route('admin.student-payments.index', ['tab' => 'awaiting_approval']),
            'count' => $tabCounts['awaiting_approval'] ?? 0,
        ],
        [
            'key' => 'paid',
            'label' => 'Lunas',
            'url' => route('admin.student-payments.index', ['tab' => 'paid']),
            'count' => $tabCounts['paid'] ?? 0,
        ],
    ];

    $statusBadge = static function (string $s): array {
        return match ($s) {
            'pending' => ['warning', 'Pending'],
            'paid' => ['success', 'Lunas'],
            'failed' => ['danger', 'Gagal'],
            default => ['neutral', ucfirst($s)],
        };
    };
@endphp

@section('content')

<x-ui.page-header title="Tagihan Murid">
    <x-slot:action>
        <button
            type="button"
            class="ui-btn ui-btn--primary"
            onclick="loadCreateTagihanForm()"
        >
            + Buat Tagihan
        </button>
    </x-slot:action>
</x-ui.page-header>

@if(session('success'))
    <x-ui.toast variant="success">{{ session('success') }}</x-ui.toast>
@endif

@if(session('error'))
    <x-ui.toast variant="danger">{{ session('error') }}</x-ui.toast>
@endif

<x-ui.tab-bar :tabs="$tabs" :active="$tab" />

<div class="ui-toolbar">
    <form method="GET" action="{{ route('admin.student-payments.index') }}" class="ui-toolbar__search">
        <input type="hidden" name="tab" value="{{ $tab }}">
        <input
            type="search"
            name="search"
            value="{{ $search }}"
            class="ui-input"
            placeholder="Cari murid / payment / periode..."
        >
    </form>

    <div class="ui-toolbar__filters">
        <form method="GET" action="{{ route('admin.student-payments.index') }}">
            <input type="hidden" name="tab" value="{{ $tab }}">
            <input type="hidden" name="search" value="{{ $search }}">
            <select name="id_payment" class="ui-select" onchange="this.form.submit()">
                <option value="all" @selected($id_payment === 'all')>Semua Jenis</option>
                @foreach($payments as $p)
                    <option value="{{ $p->id_payment }}" @selected((int)$id_payment === (int)$p->id_payment)>
                        {{ $p->name }}
                    </option>
                @endforeach
            </select>
        </form>
    </div>
</div>

@if($studentPayments->isEmpty())
    @php
        $emptyConfig = match ($tab) {
            'unpaid' => ['icon' => '✓', 'message' => 'Tidak ada tagihan belum lunas.'],
            'awaiting_approval' => ['icon' => '✓', 'message' => 'Tidak ada bukti pembayaran yang menunggu approve.'],
            'paid' => ['icon' => '📭', 'message' => 'Belum ada tagihan lunas.'],
            default => ['icon' => '📭', 'message' => 'Belum ada tagihan.'],
        };
    @endphp
    <x-ui.empty-state :icon="$emptyConfig['icon']" :message="$emptyConfig['message']" />
@else
    <div class="ui-table-wrapper">
        <table class="ui-table">
            <thead>
                <tr>
                    <th>Murid</th>
                    <th>Jenis Tagihan</th>
                    <th>Periode</th>
                    <th>Nominal</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($studentPayments as $sp)
                    @php
                        $statusValue = $sp->status ?? 'pending';
                        [$badgeVariant, $badgeLabel] = $statusBadge($statusValue);
                        $hasPendingProof = $sp->proofs()->where('status', 'pending')->exists();
                    @endphp
                    <tr>
                        <td><strong>{{ $sp->student?->name ?? '-' }}</strong></td>
                        <td>{{ $sp->payment?->name ?? '-' }}</td>
                        <td>{{ $sp->payment_period ?? '-' }}</td>
                        <td>{{ $rp($sp->final_amount ?? $sp->total_amount) }}</td>
                        <td>
                            <x-ui.badge :variant="$badgeVariant">{{ $badgeLabel }}</x-ui.badge>
                            @if($hasPendingProof)
                                <x-ui.badge variant="info" class="ms-1">Bukti pending</x-ui.badge>
                            @endif
                        </td>
                        <td>
                            <button
                                type="button"
                                class="ui-btn ui-btn--ghost ui-btn--sm"
                                onclick="openStudentPaymentDetail({{ $sp->id_student_payment }})"
                            >
                                Lihat
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @include('components.dashboard.admin.pagination-controls', [
        'items' => $studentPayments,
        'search' => $search,
        'tab' => $tab,
        'id_payment' => $id_payment,
        'per_page' => $per_page ?? 10,
    ])
@endif

{{-- Modal: Detail tagihan (load via AJAX) --}}
<x-ui.modal name="student-payment-detail" title="Detail Tagihan" maxWidth="720px">
    <div id="student-payment-detail-content">
        <p class="ui-stat-card__hint">Memuat...</p>
    </div>
</x-ui.modal>

{{-- Modal: Buat Tagihan (load via AJAX dari create endpoint) --}}
<x-ui.modal name="create-tagihan" title="Buat Tagihan" maxWidth="640px">
    <div id="create-tagihan-content">
        <p class="ui-stat-card__hint">Memuat formulir...</p>
    </div>
</x-ui.modal>

<script>
    function openStudentPaymentDetail(id) {
        window.loadDetailIntoModal(`/admin/student-payments/${id}`, 'student-payment-detail-content', 'student-payment-detail');
    }

    function loadCreateTagihanForm() {
        window.loadFormIntoModal('{{ route('admin.student-payments.create') }}', 'create-tagihan-content', 'create-tagihan');
    }
</script>

@include('components.dashboard.admin.admin-scripts')

@endsection
