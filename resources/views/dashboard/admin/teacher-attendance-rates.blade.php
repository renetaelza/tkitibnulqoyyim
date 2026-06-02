@extends('layouts.dashboard')

@section('title', 'Tarif Kehadiran - TK Ibnul Qoyyim')
@section('page_title', 'Tarif Kehadiran')

@php
    $rp = static fn ($n) => 'Rp ' . number_format((float) $n, 0, ',', '.');
@endphp

@section('content')

<x-ui.page-header title="Tarif Kehadiran">
    <x-slot:action>
        <button type="button" class="ui-btn ui-btn--primary" onclick="loadCreateRate()">+ Set Tarif</button>
    </x-slot:action>
</x-ui.page-header>

@if(session('success'))
    <x-ui.toast variant="success">{{ session('success') }}</x-ui.toast>
@endif

<div class="ui-toolbar">
    <form method="GET" action="{{ route('admin.teacher-attendance-rates.index') }}" class="ui-toolbar__search">
        <input type="search" name="search" value="{{ $search }}" class="ui-input" placeholder="Cari guru...">
    </form>
</div>

@if(($rates?->isEmpty()) ?? true)
    <x-ui.empty-state message="Belum ada tarif yang diset." />
@else
    <div class="ui-table-wrapper">
        <table class="ui-table">
            <thead>
                <tr>
                    <th>Guru</th>
                    <th>Rate / Hari</th>
                    <th>Berlaku Dari</th>
                    <th>Sampai</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($rates as $r)
                    <tr>
                        <td><strong>{{ $r->teacher?->name ?? '-' }}</strong></td>
                        <td>{{ $rp($r->amount_per_attendance) }}</td>
                        <td>{{ $r->effective_from?->format('d M Y') ?? '-' }}</td>
                        <td>{{ $r->effective_to?->format('d M Y') ?? '—' }}</td>
                        <td>
                            <div class="ui-table__actions">
                                <button type="button" class="ui-btn ui-btn--ghost ui-btn--sm" onclick="loadEditRate({{ $r->id_teacher_attendance_rate }})">Edit</button>
                                <button type="button" class="ui-btn ui-btn--ghost ui-btn--sm" style="color: var(--color-danger);" onclick="confirmDelete('{{ route('admin.teacher-attendance-rates.destroy', $r) }}', '{{ $r->teacher?->name ?? '-' }}')">Hapus</button>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @include('components.dashboard.admin.pagination-controls', [
        'items' => $rates,
        'search' => $search,
        'per_page' => $per_page ?? 10,
    ])
@endif

<x-ui.modal name="rate-modal" title="Tarif Kehadiran" maxWidth="480px">
    <div id="rate-modal-content">
        <p class="ui-stat-card__hint">Memuat...</p>
    </div>
</x-ui.modal>

<script>
    function loadCreateRate() {
        window.loadFormIntoModal('{{ route('admin.teacher-attendance-rates.create') }}', 'rate-modal-content', 'rate-modal');
    }
    function loadEditRate(id) {
        window.loadFormIntoModal(`/admin/teacher-attendance-rates/${id}/edit`, 'rate-modal-content', 'rate-modal');
    }
    function confirmDelete(url, label) {
        if (!confirm(`Hapus tarif untuk "${label}"?`)) return;
        const token = document.querySelector('meta[name="csrf-token"]')?.content;
        fetch(url, { method: 'DELETE', headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': token }})
            .then(r => r.json()).then(() => location.reload())
            .catch(() => alert('Gagal menghapus.'));
    }
</script>

@include('components.dashboard.admin.admin-scripts')

@endsection
