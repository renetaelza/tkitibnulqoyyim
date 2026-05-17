@extends('layouts.dashboard')

@section('title', 'Data Kelas - TK Ibnul Qoyyim')
@section('page_title', 'Data Kelas')

@section('content')

<div class="admin-classes-page">
    <div class="admin-section-header">
        <p class="admin-section-subtitle">Kelola data kelas dan tahun ajaran</p>
    </div>

    @include('components.dashboard.admin.section-header', [
        'type' => 'class',
        'search' => $search ?? '',
        'school_year' => $school_year ?? 'all',
        'schoolYears' => $schoolYears ?? [],
        'exportUrl' => route('admin.classes.export', request()->query()),
        'resetUrl' => route('admin.classes.index'),
    ])

    @include('components.dashboard.admin.management-table', [
        'type' => 'class',
        'items' => $classes ?? collect(),
    ])

    @if($classes && $classes->total() > 0)
        @include('components.dashboard.admin.pagination-controls', [
            'items' => $classes,
            'search' => $search ?? '',
            'school_year' => $school_year ?? 'all',
            'per_page' => $per_page ?? 10,
        ])
    @endif
</div>

@include('components.dashboard.admin.admin-scripts')

@endsection
