@extends('layouts.dashboard')

@section('title', 'Honor Guru - TK Ibnul Qoyyim')
@section('page_title', 'Honor Guru')

@section('content')

<div class="admin-teacher-honors-page">
    <div class="admin-section-header">
        <p class="admin-section-subtitle">Kelola data honor per guru per bulan</p>
    </div>

    @include('components.dashboard.admin.section-header', [
        'type' => 'teacher-honor',
        'search' => $search ?? '',
        'status' => $status ?? 'all',
        'month' => $month ?? 'all',
        'year' => $year ?? 'all',
        'years' => $years ?? collect(),
        'exportUrl' => route('admin.teacher-honors.export', request()->query()),
        'resetUrl' => route('admin.teacher-honors.index'),
    ])

    @include('components.dashboard.admin.management-table', [
        'type' => 'teacher-honor',
        'items' => $honors ?? collect(),
        'showDelete' => false,
    ])

    @if($honors)
        @include('components.dashboard.admin.pagination-controls', [
            'items' => $honors,
            'search' => $search ?? '',
            'status' => $status ?? 'all',
            'month' => $month ?? 'all',
            'year' => $year ?? 'all',
            'per_page' => $per_page ?? 10,
        ])
    @endif
</div>

@include('components.dashboard.admin.admin-scripts')

@endsection
