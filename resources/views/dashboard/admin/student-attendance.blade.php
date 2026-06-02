@extends('layouts.dashboard')

@section('title', 'Absensi Murid - TK Ibnul Qoyyim')
@section('page_title', 'Absensi Murid')

@section('content')

<div class="admin-student-attendance-page">
    <div class="admin-section-header">
        <p class="admin-section-subtitle">Kelola data absensi murid</p>
    </div>

    @include('components.dashboard.admin.section-header', [
        'type' => 'student-attendance',
        'search' => $search ?? '',
        'status' => $status ?? 'all',
        'date_from' => $date_from ?? '',
        'date_to' => $date_to ?? '',
        'id_class' => $id_class ?? 'all',
        'classes' => $classes ?? [],
        'exportUrl' => route('admin.student-attendance.export', request()->query()),
        'resetUrl' => route('admin.student-attendance.index'),
    ])

    @include('components.dashboard.admin.management-table', [
        'type' => 'student-attendance',
        'items' => $attendances ?? collect(),
    ])

    @if($attendances)
        @include('components.dashboard.admin.pagination-controls', [
            'items' => $attendances,
            'search' => $search ?? '',
            'status' => $status ?? 'all',
            'date_from' => $date_from ?? '',
            'date_to' => $date_to ?? '',
            'id_class' => $id_class ?? 'all',
            'per_page' => $per_page ?? 10,
        ])
    @endif
</div>

@include('components.dashboard.admin.admin-scripts')

@endsection
