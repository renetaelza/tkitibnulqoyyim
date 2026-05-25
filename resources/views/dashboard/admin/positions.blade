@extends('layouts.dashboard')

@section('title', 'Posisi Guru - TK Ibnul Qoyyim')
@section('page_title', 'Posisi Guru')

@section('content')

<div class="admin-positions-page">
    <div class="admin-section-header">
        <p class="admin-section-subtitle">Kelola daftar posisi guru.</p>
    </div>

    @include('components.dashboard.admin.section-header', [
        'type' => 'position',
        'search' => $search ?? '',
        'status' => $status ?? 'all',
        'exportUrl' => route('admin.positions.export', request()->query()),
        'resetUrl' => route('admin.positions.index'),
    ])

    @include('components.dashboard.admin.management-table', [
        'type' => 'position',
        'items' => $positions ?? collect(),
        'showDelete' => true,
    ])

    @if($positions)
        @include('components.dashboard.admin.pagination-controls', [
            'items' => $positions,
            'search' => $search ?? '',
            'status' => $status ?? 'all',
            'per_page' => $per_page ?? 10,
        ])
    @endif
</div>

@include('components.dashboard.admin.admin-scripts')

@endsection
