@extends('layouts.dashboard')

@section('title', 'Daftar Guru - TK Ibnul Qoyyim')
@section('page_title', 'Daftar Guru')

@php
    $currentRole = auth()->user()?->role ?? null;
    $canManage = in_array($currentRole, ['superadmin', 'administration'], true);
@endphp

@section('content')

<x-ui.page-header title="Daftar Guru">
    <x-slot:action>
        <a href="{{ route('admin.teachers.export', request()->query()) }}" class="ui-btn ui-btn--secondary">Export CSV</a>
        @if($canManage)
            <button type="button" class="ui-btn ui-btn--primary" onclick="loadCreateTeacher()" style="margin-left: var(--ui-space-sm);">+ Tambah Guru</button>
        @endif
    </x-slot:action>
</x-ui.page-header>

@if(session('success'))
    <x-ui.toast variant="success">{{ session('success') }}</x-ui.toast>
@endif

<div class="ui-toolbar">
    <form method="GET" action="{{ route('admin.teachers.index') }}" class="ui-toolbar__search">
        <input type="hidden" name="status" value="{{ $status }}">
        <input type="search" name="search" value="{{ $search }}" class="ui-input" placeholder="Cari nama / NIP / jabatan...">
    </form>
    <div class="ui-toolbar__filters">
        <form method="GET" action="{{ route('admin.teachers.index') }}">
            <input type="hidden" name="search" value="{{ $search }}">
            <select name="status" class="ui-select" onchange="this.form.submit()">
                <option value="all" @selected($status === 'all')>Semua Status</option>
                <option value="active" @selected($status === 'active')>Aktif</option>
                <option value="inactive" @selected($status === 'inactive')>Nonaktif</option>
            </select>
        </form>
    </div>
</div>

@if(($teachers?->isEmpty()) ?? true)
    <x-ui.empty-state message="Belum ada data guru." />
@else
    <div class="ui-table-wrapper">
        <table class="ui-table">
            <thead>
                <tr>
                    <th>Nama</th>
                    <th>Jabatan</th>
                    <th>NIP / NUPTK</th>
                    <th>Pendidikan</th>
                    <th>Masa Kerja</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($teachers as $t)
                    @php
                        $statusValue = $t->status ?? 'active';
                        $statusBadge = $statusValue === 'active' ? 'success' : 'neutral';
                        $statusLabel = $statusValue === 'active' ? 'Aktif' : 'Nonaktif';
                    @endphp
                    <tr>
                        <td>
                            <strong>{{ $t->name ?? '-' }}</strong>
                            @if($t->phone_num)
                                <div class="ui-stat-card__hint">📞 {{ $t->phone_num }}</div>
                            @endif
                        </td>
                        <td>{{ $t->position ?? '-' }}</td>
                        <td style="color: var(--color-muted); font-size: var(--ui-font-xs);">
                            {{ $t->nip ?? '-' }}
                            @if($t->nuptk)
                                <br>{{ $t->nuptk }}
                            @endif
                        </td>
                        <td>{{ \Illuminate\Support\Str::limit($t->education ?? '-', 30) }}</td>
                        <td>{{ $t->masa_kerja ?? '-' }}</td>
                        <td><x-ui.badge :variant="$statusBadge">{{ $statusLabel }}</x-ui.badge></td>
                        <td>
                            <div class="ui-table__actions">
                                <button type="button" class="ui-btn ui-btn--ghost ui-btn--sm" onclick="loadDetailTeacher({{ $t->id_teacher }})">Lihat</button>
                                @if($canManage)
                                    <button type="button" class="ui-btn ui-btn--ghost ui-btn--sm" onclick="loadEditTeacher({{ $t->id_teacher }})">Edit</button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @include('components.dashboard.admin.pagination-controls', [
        'items' => $teachers,
        'search' => $search,
        'status' => $status,
        'per_page' => $per_page ?? 10,
    ])
@endif

<x-ui.modal name="teacher-modal" title="Detail Guru" maxWidth="720px">
    <div id="teacher-modal-content">
        <p class="ui-stat-card__hint">Memuat...</p>
    </div>
</x-ui.modal>

<script>
    function loadDetailTeacher(id) {
        window.loadDetailIntoModal(`/admin/teachers/${id}`, 'teacher-modal-content', 'teacher-modal');
    }
    @if($canManage)
    function loadCreateTeacher() {
        window.loadFormIntoModal('{{ route('admin.teachers.create') }}', 'teacher-modal-content', 'teacher-modal');
    }
    function loadEditTeacher(id) {
        window.loadFormIntoModal(`/admin/teachers/${id}/edit`, 'teacher-modal-content', 'teacher-modal');
    }
    @endif
</script>

@include('components.dashboard.admin.admin-scripts')

@endsection
