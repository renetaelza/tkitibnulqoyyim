@extends('layouts.dashboard')

@section('title', 'Pendaftaran - TK Ibnul Qoyyim')
@section('page_title', 'Pendaftaran')

@php
    $activeStatus = $status === 'all' ? 'pending' : $status;

    $tabs = [
        [
            'key' => 'pending',
            'label' => 'Pending',
            'url' => route('admin.registrations.index', ['status' => 'pending']),
            'count' => $statusCounts['pending'] ?? 0,
        ],
        [
            'key' => 'active',
            'label' => 'Approved',
            'url' => route('admin.registrations.index', ['status' => 'active']),
            'count' => $statusCounts['active'] ?? 0,
        ],
        [
            'key' => 'rejected',
            'label' => 'Rejected',
            'url' => route('admin.registrations.index', ['status' => 'rejected']),
            'count' => $statusCounts['rejected'] ?? 0,
        ],
    ];

    $statusBadge = static function (string $s): array {
        return match ($s) {
            'pending', 'pending_due' => ['warning', 'Pending'],
            'approved_awaiting_payment' => ['info', 'Menunggu Bayar'],
            'active' => ['success', 'Aktif'],
            'rejected' => ['danger', 'Rejected'],
            default => ['neutral', ucfirst($s)],
        };
    };
@endphp

@section('content')

<x-ui.page-header title="Pendaftaran">
    <x-slot:action>
        <a href="{{ route('admin.registrations.export', request()->query()) }}" class="ui-btn ui-btn--secondary">
            Export CSV
        </a>
    </x-slot:action>
</x-ui.page-header>

@if(session('success'))
    <x-ui.toast variant="success">{{ session('success') }}</x-ui.toast>
@endif

@if(session('error'))
    <x-ui.toast variant="danger">{{ session('error') }}</x-ui.toast>
@endif

<x-ui.tab-bar :tabs="$tabs" :active="$activeStatus" />

<div class="ui-toolbar">
    <form method="GET" action="{{ route('admin.registrations.index') }}" class="ui-toolbar__search">
        <input type="hidden" name="status" value="{{ $activeStatus }}">
        <input
            type="search"
            name="search"
            value="{{ $search }}"
            class="ui-input"
            placeholder="Cari nama calon / orangtua..."
        >
    </form>

    <div class="ui-toolbar__filters">
        <form method="GET" action="{{ route('admin.registrations.index') }}">
            <input type="hidden" name="status" value="{{ $activeStatus }}">
            <input type="hidden" name="search" value="{{ $search }}">
            <select name="group" class="ui-select" onchange="this.form.submit()">
                <option value="all" @selected($group === 'all')>Semua Grup</option>
                <option value="A" @selected($group === 'A')>Grup A</option>
                <option value="B" @selected($group === 'B')>Grup B</option>
            </select>
        </form>
    </div>
</div>

@if($registrations->isEmpty())
    <x-ui.empty-state
        :icon="$activeStatus === 'pending' ? '✓' : '📭'"
        :message="$activeStatus === 'pending' ? 'Tidak ada pendaftaran pending.' : 'Belum ada data.'"
    />
@else
    <div class="ui-table-wrapper">
        <table class="ui-table">
            <thead>
                <tr>
                    <th>Nama Calon</th>
                    <th>Grup</th>
                    <th>Pendaftar</th>
                    <th>Tanggal</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($registrations as $reg)
                    @php
                        $candidateName = $reg->candidate_data['name'] ?? '-';
                        $fatherName = $reg->parents_data['father_name'] ?? null;
                        $motherName = $reg->parents_data['mother_name'] ?? null;
                        $registrarName = $reg->user?->name
                            ?? $fatherName
                            ?? $motherName
                            ?? '-';
                        [$badgeVariant, $badgeLabel] = $statusBadge($reg->status);
                    @endphp
                    <tr>
                        <td><strong>{{ $candidateName }}</strong></td>
                        <td>{{ $reg->group ?? '-' }}</td>
                        <td>{{ $registrarName }}</td>
                        <td>{{ $reg->created_at?->format('d M Y') ?? '-' }}</td>
                        <td><x-ui.badge :variant="$badgeVariant">{{ $badgeLabel }}</x-ui.badge></td>
                        <td>
                            <a
                                href="{{ route('admin.registrations.show', $reg) }}"
                                class="ui-btn ui-btn--ghost ui-btn--sm"
                                onclick="event.preventDefault(); openRegistrationDetail({{ $reg->id_registration }});"
                            >
                                Lihat
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @include('components.dashboard.admin.pagination-controls', [
        'items' => $registrations,
        'search' => $search,
        'status' => $activeStatus,
        'group' => $group,
        'per_page' => $per_page ?? 10,
    ])
@endif

{{-- Modal placeholder untuk detail (loaded via AJAX). --}}
<x-ui.modal name="registration-detail" title="Detail Pendaftaran" maxWidth="640px">
    <div id="registration-detail-content">
        <p class="ui-stat-card__hint">Memuat...</p>
    </div>
</x-ui.modal>

<script>
    function openRegistrationDetail(id) {
        window.loadDetailIntoModal(`/admin/registrations/${id}`, 'registration-detail-content', 'registration-detail');
    }
</script>

@include('components.dashboard.admin.admin-scripts')

@endsection
