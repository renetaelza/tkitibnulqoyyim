@extends('layouts.dashboard')

@section('title', 'Manajemen Pengguna - TK Ibnul Qoyyim')
@section('page_title', 'Manajemen Pengguna')

@php
    $roleLabel = static fn (?string $r) => match ($r) {
        'superadmin' => 'Super Admin',
        'headmaster' => 'Kepala Sekolah',
        'administration' => 'Administrasi',
        'bendahara' => 'Bendahara',
        'teacher' => 'Guru',
        'guest' => 'Orang Tua',
        default => ucfirst((string) $r),
    };

    $roleBadge = static fn (?string $r) => match ($r) {
        'superadmin' => 'danger',
        'headmaster' => 'info',
        'administration', 'bendahara' => 'warning',
        'teacher' => 'success',
        'guest' => 'neutral',
        default => 'neutral',
    };
@endphp

@section('content')

<x-ui.page-header title="Manajemen Pengguna">
    <x-slot:action>
        <a href="{{ route('admin.users.export', request()->query()) }}" class="ui-btn ui-btn--secondary">Export CSV</a>
        <button type="button" class="ui-btn ui-btn--primary" onclick="loadCreateUser()" style="margin-left: var(--ui-space-sm);">+ Tambah Pengguna</button>
    </x-slot:action>
</x-ui.page-header>

@if(session('success'))
    <x-ui.toast variant="success">{{ session('success') }}</x-ui.toast>
@endif

<div class="ui-toolbar">
    <form method="GET" action="{{ route('admin.users.index') }}" class="ui-toolbar__search">
        <input type="hidden" name="role" value="{{ $role }}">
        <input type="hidden" name="status" value="{{ $status }}">
        <input type="search" name="search" value="{{ $search }}" class="ui-input" placeholder="Cari nama / email / telepon...">
    </form>
    <div class="ui-toolbar__filters">
        <form method="GET" action="{{ route('admin.users.index') }}">
            <input type="hidden" name="search" value="{{ $search }}">
            <input type="hidden" name="status" value="{{ $status }}">
            <select name="role" class="ui-select" onchange="this.form.submit()">
                <option value="all" @selected($role === 'all')>Semua Role</option>
                <option value="superadmin" @selected($role === 'superadmin')>Super Admin</option>
                <option value="headmaster" @selected($role === 'headmaster')>Kepala Sekolah</option>
                <option value="administration" @selected($role === 'administration')>Administrasi</option>
                <option value="bendahara" @selected($role === 'bendahara')>Bendahara</option>
                <option value="teacher" @selected($role === 'teacher')>Guru</option>
                <option value="guest" @selected($role === 'guest')>Orang Tua</option>
            </select>
        </form>
        <form method="GET" action="{{ route('admin.users.index') }}">
            <input type="hidden" name="search" value="{{ $search }}">
            <input type="hidden" name="role" value="{{ $role }}">
            <select name="status" class="ui-select" onchange="this.form.submit()">
                <option value="all" @selected($status === 'all')>Semua Status</option>
                <option value="active" @selected($status === 'active')>Aktif</option>
                <option value="inactive" @selected($status === 'inactive')>Nonaktif</option>
            </select>
        </form>
    </div>
</div>

@if(($users?->isEmpty()) ?? true)
    <x-ui.empty-state message="Belum ada pengguna." />
@else
    <div class="ui-table-wrapper">
        <table class="ui-table">
            <thead>
                <tr>
                    <th>Nama</th>
                    <th>Email</th>
                    <th>Telepon</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $u)
                    @php
                        $statusBadgeClass = ($u->status ?? 'active') === 'active' ? 'success' : 'neutral';
                    @endphp
                    <tr>
                        <td><strong>{{ $u->name ?? '-' }}</strong></td>
                        <td>{{ $u->email ?? '-' }}</td>
                        <td style="color: var(--color-muted);">{{ $u->phone_num ?? '-' }}</td>
                        <td><x-ui.badge :variant="$roleBadge($u->role)">{{ $roleLabel($u->role) }}</x-ui.badge></td>
                        <td><x-ui.badge :variant="$statusBadgeClass">{{ ucfirst($u->status ?? 'active') }}</x-ui.badge></td>
                        <td>
                            <div class="ui-table__actions">
                                <button type="button" class="ui-btn ui-btn--ghost ui-btn--sm" onclick="loadDetailUser({{ $u->id }})">Lihat</button>
                                <button type="button" class="ui-btn ui-btn--ghost ui-btn--sm" onclick="loadEditUser({{ $u->id }})">Edit</button>
                                <button type="button" class="ui-btn ui-btn--ghost ui-btn--sm" style="color: var(--color-danger);" onclick="confirmDeleteUser('{{ route('admin.users.destroy', $u) }}', '{{ addslashes($u->name ?? '-') }}')">Hapus</button>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @include('components.dashboard.admin.pagination-controls', [
        'items' => $users,
        'search' => $search,
        'role' => $role,
        'status' => $status,
        'per_page' => $per_page ?? 10,
    ])
@endif

<x-ui.modal name="user-modal" title="Pengguna" maxWidth="640px">
    <div id="user-modal-content">
        <p class="ui-stat-card__hint">Memuat...</p>
    </div>
</x-ui.modal>

<script>
    function loadCreateUser() {
        window.loadFormIntoModal('{{ route('admin.users.create') }}', 'user-modal-content', 'user-modal');
    }
    function loadEditUser(id) {
        window.loadFormIntoModal(`/admin/users/${id}/edit`, 'user-modal-content', 'user-modal');
    }
    function loadDetailUser(id) {
        window.loadDetailIntoModal(`/admin/users/${id}`, 'user-modal-content', 'user-modal');
    }
    function confirmDeleteUser(url, label) {
        if (!confirm(`Hapus pengguna "${label}"? Aksi ini tidak bisa dibatalkan.`)) return;
        const token = document.querySelector('meta[name="csrf-token"]')?.content;
        fetch(url, { method: 'DELETE', headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': token }})
            .then(r => r.json()).then(() => location.reload())
            .catch(() => alert('Gagal menghapus.'));
    }
</script>

@include('components.dashboard.admin.admin-scripts')

@endsection
