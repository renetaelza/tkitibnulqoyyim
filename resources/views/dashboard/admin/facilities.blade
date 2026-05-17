@extends('layouts.dashboard')

@section('title', 'Sarana & Prasarana - TK Ibnul Qoyyim')
@section('page_title', 'Sarana & Prasarana')

@section('content')

<div class="admin-facilities-page">
    <div class="admin-section-header">
        <p class="admin-section-subtitle">Kelola fasilitas/inventaris yang ditampilkan di landing page</p>
    </div>

    @include('components.dashboard.admin.section-header', [
        'type' => 'facility',
        'search' => $search ?? '',
        'status' => $status ?? 'all',
        'exportUrl' => route('admin.facilities.export', request()->query()),
        'resetUrl' => route('admin.facilities.index'),
    ])

    @include('components.dashboard.admin.management-table', [
        'type' => 'facility',
        'items' => $facilities ?? collect(),
        'showDelete' => true,
    ])

    @if($facilities)
        @include('components.dashboard.admin.pagination-controls', [
            'items' => $facilities,
            'search' => $search ?? '',
            'status' => $status ?? 'all',
            'per_page' => $per_page ?? 10,
        ])
    @endif
</div>

@include('components.dashboard.admin.admin-scripts')

@endsection
