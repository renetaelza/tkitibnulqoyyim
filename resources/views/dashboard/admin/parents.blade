@extends('layouts.dashboard')

@section('title', 'Data Orang Tua - TK Ibnul Qoyyim')
@section('page_title', 'Data Orang Tua')

@section('content')

<div class="admin-parents-page">
    <div class="admin-section-header">
        <p class="admin-section-subtitle">Kelola data orang tua / wali murid</p>
    </div>

    @include('components.dashboard.admin.section-header', [
        'type' => 'parent',
        'search' => $search ?? '',
        'contact' => $contact ?? 'all',
        'exportUrl' => route('admin.parents.export', request()->query()),
        'resetUrl' => route('admin.parents.index'),
    ])

    @include('components.dashboard.admin.management-table', [
        'type' => 'parent',
        'items' => $parents ?? collect(),
        'showDelete' => false,
    ])

    @if($parents && $parents->total() > 0)
        @include('components.dashboard.admin.pagination-controls', [
            'items' => $parents,
            'search' => $search ?? '',
            'contact' => $contact ?? 'all',
            'per_page' => $per_page ?? 10,
        ])
    @endif
</div>

@include('components.dashboard.admin.admin-scripts')

@endsection
