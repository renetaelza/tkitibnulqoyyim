@extends('layouts.dashboard')

@section('title', 'Manajemen Payment - TK Ibnul Qoyyim')
@section('page_title', 'Manajemen Payment')

@section('content')

<div class="admin-users-page">
    <div class="admin-section-header">
        <p class="admin-section-subtitle">Kelola master payment (nama, jenis, komponen biaya, dan default amount)</p>
    </div>

    @include('components.dashboard.admin.section-header', [
        'type' => 'payment',
        'search' => $search ?? '',
        'period_mode' => $period_mode ?? 'all',
        'status' => $status ?? 'all',
        'exportUrl' => route('admin.payments.export', request()->query()),
        'resetUrl' => route('admin.payments.index'),
    ])

    @include('components.dashboard.admin.management-table', [
        'type' => 'payment',
        'items' => $payments ?? collect(),
    ])

    @if($payments && $payments->total() > 0)
        @include('components.dashboard.admin.pagination-controls', [
            'items' => $payments,
            'search' => $search ?? '',
            'period_mode' => $period_mode ?? 'all',
            'status' => $status ?? 'all',
            'per_page' => $per_page ?? 10,
        ])
    @endif
</div>

@include('components.dashboard.admin.admin-scripts')

@endsection
