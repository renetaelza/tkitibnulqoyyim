@extends('layouts.dashboard')

@section('content')
<div class="registration-container">
    <div class="registration-wrapper">
        <h1 class="registration-title">Formulir Pendaftaran Siswa Baru</h1>
        
        <!-- Progress Bar -->
        @include('components.registration.progress-bar', [
            'currentStep' => $currentStep ?? 1,
            'totalSteps' => 3
        ])

        <form action="{{ route('registration.store') }}" method="POST" id="registrationForm">
            @csrf

            <!-- Step 1: Candidate Data -->
            @if(($currentStep ?? 1) == 1 || session()->has('new_registration'))
                @include('components.registration.step-1-candidate', [
                    'candidateData' => session('registration.candidate_data'),
                    'errors' => $errors
                ])
            @endif

            <!-- Step 2: Parent Data -->
            @if(($currentStep ?? 1) == 2)
                @include('components.registration.step-2-parents', [
                    'parentsData' => session('registration.parents_data'),
                    'errors' => $errors
                ])
            @endif

            <!-- Step 3: Review -->
            @if(($currentStep ?? 1) == 3)
                @include('components.registration.step-3-review', [
                    'candidateData' => session('registration.candidate_data'),
                    'parentsData' => session('registration.parents_data'),
                    'group' => session('registration.group')
                ])
            @endif

            <!-- Form Navigation -->
            @include('components.registration.form-navigation', [
                'currentStep' => $currentStep ?? 1,
                'totalSteps' => 3
            ])
        </form>
    </div>
</div>
@endsection

@push('styles')
<style>
    .registration-container {
        min-height: 100vh;
        padding: 40px 20px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }

    .registration-wrapper {
        max-width: 600px;
        margin: 0 auto;
        background: white;
        border-radius: 12px;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        padding: 40px;
    }

    .registration-title {
        font-size: 28px;
        font-weight: 700;
        color: #1a1a1a;
        margin-bottom: 30px;
        text-align: center;
    }

    @media (max-width: 900px) {
        .registration-wrapper {
            padding: 30px;
        }

        .registration-title {
            font-size: 24px;
            margin-bottom: 25px;
        }
    }

    @media (max-width: 600px) {
        .registration-container {
            padding: 20px;
        }

        .registration-wrapper {
            padding: 20px;
            border-radius: 8px;
        }

        .registration-title {
            font-size: 20px;
            margin-bottom: 20px;
        }
    }
</style>
@endpush
