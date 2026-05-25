@extends('layouts.dashboard')

@section('title', 'Honor Saya - TK Ibnul Qoyyim')
@section('page_title', 'Honor Saya')

@section('content')

@php
    $monthNames = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
        7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
    ];

    $formatPeriod = static function (?int $month, ?int $year) use ($monthNames): string {
        $m = (int)($month ?? 0);
        $y = (int)($year ?? 0);
        if ($m < 1 || $m > 12 || $y < 1) {
            return '-';
        }
        return ($monthNames[$m] ?? (string)$m) . ' ' . $y;
    };

    $formatPeriodRange = static function ($row) use ($formatPeriod): string {
        if ($row?->period_start && $row?->period_end) {
            return $row->period_start->format('Y-m-d') . ' s/d ' . $row->period_end->format('Y-m-d');
        }
        return $formatPeriod((int)($row?->month ?? 0), (int)($row?->year ?? 0));
    };
@endphp

<div class="admin-my-honor-page">
    <div class="admin-section-header">
        <p class="admin-section-subtitle">Ringkasan dan riwayat honor Anda.</p>
    </div>

    @include('components.dashboard.admin.teacher-honor-summary', [
        'user' => $user ?? auth()->user(),
        'myTeacherDetail' => $myTeacherDetail ?? null,
        'myHonor' => $myHonor ?? null,
        'myHonorLatest' => $myHonorLatest ?? null,
        'myHonorList' => $myHonorList ?? collect(),
        'showAllLink' => false,
    ])

    <div class="admin-section-card">
        <div class="admin-section-card-header">
            <h3>Daftar Honor</h3>
        </div>

        @if(!$teacher)
            <p class="text-empty">Data guru belum terhubung ke akun ini.</p>
        @elseif(!$honors || ($honors->total() ?? 0) === 0)
            <p class="text-empty">Belum ada data honor.</p>
        @else
            <div class="admin-table-wrapper admin-table-wrapper-fixed-10">
                <table class="admin-management-table admin-management-table-center-ends">
                    <thead class="admin-table-header">
                        <tr>
                            <th class="col-id">No</th>
                            <th>Periode</th>
                            <th>Hadir</th>
                            <th>Izin</th>
                            <th>Sakit</th>
                            <th>Alpa</th>
                            <th>Nominal</th>
                            <th>Status</th>
                            <th>Tanggal Bayar</th>
                        </tr>
                    </thead>
                    <tbody class="admin-table-body">
                        @foreach($honors as $i => $row)
                            @php
                                $paid = (bool)($row->payment_date);
                            @endphp
                            <tr class="admin-table-row">
                                <td class="col-id">{{ ($honors->firstItem() ?? 1) + $i }}</td>
                                <td>{{ $formatPeriodRange($row) }}</td>
                                <td>{{ (int)($row->attendance_count ?? 0) }}</td>
                                <td>{{ (int)($row->permission_count ?? 0) }}</td>
                                <td>{{ (int)($row->sickness_count ?? 0) }}</td>
                                <td>{{ (int)($row->absence_count ?? 0) }}</td>
                                <td>Rp {{ number_format((float)($row->amount ?? 0), 0, ',', '.') }}</td>
                                <td>
                                    <span class="admin-badge {{ $paid ? 'admin-badge-success' : 'admin-badge-warning' }}">
                                        {{ $paid ? 'paid' : 'unpaid' }}
                                    </span>
                                </td>
                                <td>{{ $row->payment_date ? $row->payment_date->format('d/m/Y') : '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @include('components.dashboard.admin.pagination-controls', [
                'items' => $honors,
                'per_page' => $per_page ?? 10,
            ])
        @endif
    </div>
</div>

@endsection
