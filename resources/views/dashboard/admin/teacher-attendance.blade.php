@extends('layouts.dashboard')

@section('title', 'Absensi Guru - TK Ibnul Qoyyim')
@section('page_title', 'Absensi Guru')

@section('content')

<x-ui.page-header title="Absensi Guru">
    <x-slot:action>
        <a href="{{ route('admin.teacher-attendance.export', request()->query()) }}"
           class="ui-btn ui-btn--ghost ui-btn--sm" title="Download Excel">📥 Export</a>
        @if(in_array(auth()->user()?->role, ['superadmin', 'administration', 'teacher']))
            <button type="button" class="ui-btn ui-btn--primary"
                    data-modal-open="add-teacher-attendance-modal">
                + Tambah Absensi
            </button>
        @endif
    </x-slot:action>
</x-ui.page-header>

@if(session('success'))
    <x-ui.toast variant="success">{{ session('success') }}</x-ui.toast>
@endif
@if(session('error'))
    <x-ui.toast variant="danger">{{ session('error') }}</x-ui.toast>
@endif

<div class="ui-toolbar">
    <form method="GET" action="{{ route('admin.teacher-attendance.index') }}" class="ui-toolbar__search">
        <input type="hidden" name="id_teacher" value="{{ $id_teacher }}">
        <input type="hidden" name="status" value="{{ $status }}">
        <input type="hidden" name="date_from" value="{{ $date_from }}">
        <input type="hidden" name="date_to" value="{{ $date_to }}">
        <input type="search" name="search" value="{{ $search }}"
               class="ui-input" placeholder="Cari nama guru...">
    </form>

    <div class="ui-toolbar__filters">
        <form method="GET" action="{{ route('admin.teacher-attendance.index') }}">
            <input type="hidden" name="search" value="{{ $search }}">

            <select name="id_teacher" class="ui-select" onchange="this.form.submit()">
                <option value="all">Semua Guru</option>
                @foreach($teachers as $t)
                    <option value="{{ $t->id_teacher }}"
                        @selected((string) $id_teacher === (string) $t->id_teacher)>
                        {{ $t->name }}
                    </option>
                @endforeach
            </select>

            <select name="status" class="ui-select" onchange="this.form.submit()">
                <option value="all" @selected($status === 'all')>Semua Status</option>
                <option value="hadir" @selected($status === 'hadir')>Hadir</option>
                <option value="izin"  @selected($status === 'izin')>Izin</option>
                <option value="sakit" @selected($status === 'sakit')>Sakit</option>
                <option value="alpa"  @selected($status === 'alpa')>Alpa</option>
            </select>

            <input type="date" name="date_from" value="{{ $date_from }}"
                   class="ui-select" onchange="this.form.submit()" title="Dari Tanggal">
            <input type="date" name="date_to" value="{{ $date_to }}"
                   class="ui-select" onchange="this.form.submit()" title="Sampai Tanggal">

            <a href="{{ route('admin.teacher-attendance.index') }}"
               class="ui-btn ui-btn--ghost ui-btn--sm">Reset</a>
        </form>
    </div>
</div>

@if($attendances->isEmpty())
    <x-ui.empty-state icon="📋" message="Tidak ada data absensi guru." />
@else
    <div class="ui-table-wrapper">
        <table class="ui-table">
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Nama Guru</th>
                    <th>Check-in</th>
                    <th>Ketepatan</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($attendances as $item)
                    @php
                        $tz          = config('attendance.timezone', 'Asia/Makassar');
                        $checkInTz   = $item->check_in_time?->copy()->setTimezone($tz);
                        $statusValue = $item->status ?? 'hadir';
                        [$statusLabel, $badgeVariant] = match ($statusValue) {
                            'hadir' => ['Hadir', 'success'],
                            'izin'  => ['Izin',  'info'],
                            'sakit' => ['Sakit', 'warning'],
                            'alpa'  => ['Alpa',  'danger'],
                            default => [$statusValue, 'neutral'],
                        };
                    @endphp
                    <tr>
                        <td>{{ $item->date?->format('Y-m-d') ?? '-' }}</td>
                        <td><strong>{{ $item->teacher?->name ?? '-' }}</strong></td>
                        <td>{{ $checkInTz?->format('H:i') ?? '-' }}</td>
                        <td>
                            @if($item->is_late)
                                <x-ui.badge variant="danger">Telat {{ (int) $item->late_minutes }} mnt</x-ui.badge>
                            @elseif($checkInTz)
                                <x-ui.badge variant="success">Tepat</x-ui.badge>
                            @else
                                -
                            @endif
                        </td>
                        <td>
                            <x-ui.badge :variant="$badgeVariant">{{ $statusLabel }}</x-ui.badge>
                        </td>
                        <td>
                            <button type="button" class="ui-btn ui-btn--ghost ui-btn--sm"
                                    data-modal-open="view-teacher-attendance-modal"
                                    data-item-id="{{ $item->id_attendance }}">Lihat</button>
                            <button type="button" class="ui-btn ui-btn--ghost ui-btn--sm"
                                    data-modal-open="edit-teacher-attendance-modal"
                                    data-item-id="{{ $item->id_attendance }}">Edit</button>
                            <button type="button" class="ui-btn ui-btn--ghost ui-btn--sm"
                                    data-confirm-delete
                                    data-item-id="{{ $item->id_attendance }}"
                                    data-item-name="{{ $item->date?->format('Y-m-d') }} - {{ $item->teacher?->name ?? '-' }}"
                                    data-delete-type="teacher-attendance">Hapus</button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @include('components.dashboard.admin.pagination-controls', [
        'items'      => $attendances,
        'search'     => $search,
        'status'     => $status,
        'id_teacher' => $id_teacher,
        'date_from'  => $date_from,
        'date_to'    => $date_to,
        'per_page'   => $per_page,
    ])
@endif

@include('components.dashboard.admin.admin-scripts')

@endsection
