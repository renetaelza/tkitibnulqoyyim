@extends('layouts.dashboard')

@section('title', 'Hari Libur - TK Ibnul Qoyyim')
@section('page_title', 'Hari Libur')

@section('content')

<x-ui.page-header title="Hari Libur">
    <x-slot:action>
        <button type="button" class="ui-btn ui-btn--primary" onclick="loadCreateHoliday()">+ Tambah</button>
    </x-slot:action>
</x-ui.page-header>

@if(session('success'))
    <x-ui.toast variant="success">{{ session('success') }}</x-ui.toast>
@endif

<div class="ui-toolbar">
    <form method="GET" action="{{ route('admin.holidays.index') }}" class="ui-toolbar__search">
        <input type="hidden" name="year" value="{{ $year }}">
        <input type="search" name="search" value="{{ $search }}" class="ui-input" placeholder="Cari nama libur...">
    </form>
    <div class="ui-toolbar__filters">
        <form method="GET" action="{{ route('admin.holidays.index') }}">
            <input type="hidden" name="search" value="{{ $search }}">
            <select name="year" class="ui-select" onchange="this.form.submit()">
                <option value="all" @selected($year === 'all')>Semua Tahun</option>
                @foreach($years as $y)
                    <option value="{{ $y }}" @selected((string)$year === (string)$y)>{{ $y }}</option>
                @endforeach
            </select>
        </form>
    </div>
</div>

@if($holidays->isEmpty())
    <x-ui.empty-state icon="🗓️" message="Belum ada hari libur." />
@else
    <div class="ui-table-wrapper">
        <table class="ui-table">
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Hari</th>
                    <th>Nama Libur</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($holidays as $h)
                    <tr>
                        <td><strong>{{ $h->date?->format('Y-m-d') ?? '-' }}</strong></td>
                        <td style="color: var(--color-muted);">{{ $h->date?->translatedFormat('l') ?? '-' }}</td>
                        <td>{{ $h->name }}</td>
                        <td>
                            @if($h->is_active)
                                <x-ui.badge variant="success">Aktif</x-ui.badge>
                            @else
                                <x-ui.badge variant="neutral">Nonaktif</x-ui.badge>
                            @endif
                        </td>
                        <td>
                            <div class="ui-table__actions">
                                <button type="button" class="ui-btn ui-btn--ghost ui-btn--sm" onclick="loadEditHoliday({{ $h->id }})">Edit</button>
                                <button type="button" class="ui-btn ui-btn--ghost ui-btn--sm" style="color: var(--color-danger);" onclick="confirmDeleteHoliday('{{ route('admin.holidays.destroy', $h) }}', '{{ addslashes($h->name) }}')">Hapus</button>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @include('components.dashboard.admin.pagination-controls', [
        'items' => $holidays,
        'search' => $search,
        'year' => $year,
        'per_page' => $per_page ?? 15,
    ])
@endif

<x-ui.modal name="holiday-modal" title="Hari Libur" maxWidth="480px">
    <div id="holiday-modal-content">
        <p class="ui-stat-card__hint">Memuat...</p>
    </div>
</x-ui.modal>

<script>
    function loadCreateHoliday() {
        window.loadFormIntoModal('{{ route('admin.holidays.create') }}', 'holiday-modal-content', 'holiday-modal');
    }
    function loadEditHoliday(id) {
        window.loadFormIntoModal(`/admin/holidays/${id}/edit`, 'holiday-modal-content', 'holiday-modal');
    }
    function confirmDeleteHoliday(url, label) {
        if (!confirm(`Hapus hari libur "${label}"?`)) return;
        const token = document.querySelector('meta[name="csrf-token"]')?.content;
        fetch(url, { method: 'DELETE', headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': token }})
            .then(r => r.json()).then(() => location.reload())
            .catch(() => alert('Gagal menghapus.'));
    }
</script>

@endsection
