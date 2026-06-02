@extends('layouts.dashboard')

@section('title', 'Penugasan Posisi Guru - TK Ibnul Qoyyim')
@section('page_title', 'Penugasan Posisi Guru')

@section('content')

<x-ui.page-header title="Penugasan Posisi Guru">
    <x-slot:action>
        <a href="{{ route('admin.positions.index', ['tab' => 'nominal']) }}" class="ui-btn ui-btn--ghost">
            ← Kembali ke Posisi & Tunjangan
        </a>
        <button type="button" class="ui-btn ui-btn--primary" onclick="loadCreateAssignment()" style="margin-left: var(--ui-space-sm);">
            + Tugaskan Guru
        </button>
    </x-slot:action>
</x-ui.page-header>

@if(session('success'))
    <x-ui.toast variant="success">{{ session('success') }}</x-ui.toast>
@endif

<div class="ui-toolbar">
    <form method="GET" action="{{ route('admin.teacher-positions.index') }}" class="ui-toolbar__search">
        <input type="search" name="search" value="{{ $search }}" class="ui-input" placeholder="Cari guru / posisi...">
    </form>
</div>

@if(($assignments?->isEmpty()) ?? true)
    <x-ui.empty-state icon="🧩" message="Belum ada penugasan posisi." />
@else
    <div class="ui-table-wrapper">
        <table class="ui-table">
            <thead>
                <tr>
                    <th>Guru</th>
                    <th>Posisi</th>
                    <th>Berlaku Dari</th>
                    <th>Sampai</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($assignments as $tp)
                    <tr>
                        <td><strong>{{ $tp->teacher?->name ?? '-' }}</strong></td>
                        <td>{{ $tp->position?->name ?? '-' }}</td>
                        <td>{{ $tp->effective_from?->format('d M Y') ?? '-' }}</td>
                        <td>{{ $tp->effective_to?->format('d M Y') ?? '—' }}</td>
                        <td>
                            <div class="ui-table__actions">
                                <button type="button" class="ui-btn ui-btn--ghost ui-btn--sm" onclick="loadEditAssignment({{ $tp->id_teacher_position }})">Edit</button>
                                <button type="button" class="ui-btn ui-btn--ghost ui-btn--sm" style="color: var(--color-danger);" onclick="confirmDeleteAssignment('{{ route('admin.teacher-positions.destroy', $tp) }}', '{{ addslashes($tp->teacher?->name ?? '-') }} → {{ addslashes($tp->position?->name ?? '-') }}')">Hapus</button>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @include('components.dashboard.admin.pagination-controls', [
        'items' => $assignments,
        'search' => $search,
        'per_page' => $per_page ?? 10,
    ])
@endif

<x-ui.modal name="assignment-modal" title="Penugasan Posisi" maxWidth="560px">
    <div id="assignment-modal-content">
        <p class="ui-stat-card__hint">Memuat...</p>
    </div>
</x-ui.modal>

<script>
    function loadCreateAssignment() {
        window.loadFormIntoModal('{{ route('admin.teacher-positions.create') }}', 'assignment-modal-content', 'assignment-modal');
    }
    function loadEditAssignment(id) {
        window.loadFormIntoModal(`/admin/teacher-positions/${id}/edit`, 'assignment-modal-content', 'assignment-modal');
    }
    function confirmDeleteAssignment(url, label) {
        if (!confirm(`Hapus penugasan "${label}"?`)) return;
        const token = document.querySelector('meta[name="csrf-token"]')?.content;
        fetch(url, { method: 'DELETE', headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': token }})
            .then(r => r.json()).then(() => location.reload())
            .catch(() => alert('Gagal menghapus.'));
    }
</script>

@endsection
