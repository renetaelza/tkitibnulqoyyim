@extends('layouts.dashboard')

@section('title', 'Daftar Murid - TK Ibnul Qoyyim')
@section('page_title', 'Daftar Murid')

@php
    $currentRole = auth()->user()?->role ?? null;
    $canManage = in_array($currentRole, ['superadmin', 'administration'], true);
@endphp

@section('content')

<x-ui.page-header title="Daftar Murid">
    <x-slot:action>
        <a href="{{ route('admin.students.export', request()->query()) }}" class="ui-btn ui-btn--secondary">Export CSV</a>
        @if($canManage)
            <button type="button" class="ui-btn ui-btn--primary" onclick="loadCreateStudent()" style="margin-left: var(--ui-space-sm);">+ Tambah Murid</button>
        @endif
    </x-slot:action>
</x-ui.page-header>

@if(session('success'))
    <x-ui.toast variant="success">{{ session('success') }}</x-ui.toast>
@endif

<div class="ui-toolbar">
    <form method="GET" action="{{ route('admin.students.index') }}" class="ui-toolbar__search">
        <input type="hidden" name="status" value="{{ $status ?? 'all' }}">
        <input type="hidden" name="group" value="{{ $group ?? 'all' }}">
        <input type="search" name="search" value="{{ $search }}" class="ui-input" placeholder="Cari nama murid...">
    </form>
    <div class="ui-toolbar__filters">
        <form method="GET" action="{{ route('admin.students.index') }}">
            <input type="hidden" name="search" value="{{ $search }}">
            <input type="hidden" name="status" value="{{ $status ?? 'all' }}">
            <select name="group" class="ui-select" onchange="this.form.submit()">
                <option value="all" @selected(($group ?? 'all') === 'all')>Semua Grup</option>
                <option value="A" @selected(($group ?? '') === 'A')>Grup A</option>
                <option value="B" @selected(($group ?? '') === 'B')>Grup B</option>
            </select>
        </form>
        <form method="GET" action="{{ route('admin.students.index') }}">
            <input type="hidden" name="search" value="{{ $search }}">
            <input type="hidden" name="group" value="{{ $group ?? 'all' }}">
            <select name="status" class="ui-select" onchange="this.form.submit()">
                <option value="all" @selected(($status ?? 'all') === 'all')>Semua Status</option>
                <option value="aktif" @selected(($status ?? '') === 'aktif')>Aktif</option>
                <option value="non-aktif" @selected(($status ?? '') === 'non-aktif')>Nonaktif</option>
                <option value="rejected" @selected(($status ?? '') === 'rejected')>Rejected</option>
            </select>
        </form>
    </div>
</div>

@if(($students?->isEmpty()) ?? true)
    <x-ui.empty-state message="Belum ada data murid." />
@else
    <div class="ui-table-wrapper">
        <table class="ui-table">
            <thead>
                <tr>
                    <th>Nama Murid</th>
                    <th>Gender</th>
                    <th>Grup</th>
                    <th>Agama</th>
                    <th>Orangtua</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($students as $s)
                    @php
                        $parentName = $s->parent
                            ? trim(($s->parent->father_name ?? '') . (($s->parent->father_name && $s->parent->mother_name) ? ' / ' : '') . ($s->parent->mother_name ?? ''))
                            : '-';
                        $statusBadge = match ($s->status ?? '') {
                            'aktif' => 'success',
                            'rejected' => 'danger',
                            default => 'neutral',
                        };
                    @endphp
                    <tr>
                        <td><strong>{{ $s->name ?? '-' }}</strong></td>
                        <td>{{ $s->gender ?? '-' }}</td>
                        <td>{{ $s->group ?? '-' }}</td>
                        <td>{{ $s->religion ?? '-' }}</td>
                        <td style="color: var(--color-muted);">{{ $parentName ?: '-' }}</td>
                        <td><x-ui.badge :variant="$statusBadge">{{ ucfirst($s->status ?? '-') }}</x-ui.badge></td>
                        <td>
                            <div class="ui-table__actions">
                                <button type="button" class="ui-btn ui-btn--ghost ui-btn--sm" onclick="loadDetailStudent({{ $s->id_student }})">Lihat</button>
                                @if($canManage)
                                    <button type="button" class="ui-btn ui-btn--ghost ui-btn--sm" onclick="loadEditStudent({{ $s->id_student }})">Edit</button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @include('components.dashboard.admin.pagination-controls', [
        'items' => $students,
        'search' => $search,
        'status' => $status ?? 'all',
        'group' => $group ?? 'all',
        'per_page' => $per_page ?? 10,
    ])
@endif

<x-ui.modal name="student-modal" title="Detail Murid" maxWidth="720px">
    <div id="student-modal-content">
        <p class="ui-stat-card__hint">Memuat...</p>
    </div>
</x-ui.modal>

<script>
    function loadDetailStudent(id) {
        window.loadDetailIntoModal(`/admin/students/${id}`, 'student-modal-content', 'student-modal');
    }
    @if($canManage)
    function loadCreateStudent() {
        window.loadFormIntoModal('{{ route('admin.students.create') }}', 'student-modal-content', 'student-modal');
    }
    function loadEditStudent(id) {
        window.loadFormIntoModal(`/admin/students/${id}/edit`, 'student-modal-content', 'student-modal');
    }
    @endif
</script>

@include('components.dashboard.admin.admin-scripts')

@endsection
