@extends('layouts.dashboard')

@section('title', 'Dashboard - TK Ibnul Qoyyim')
@section('page_title', ($role === 'guest') ? 'Halaman Orang Tua' : 'Dashboard')

@section('content')

<!-- Welcome Section -->
@include('components.dashboard.welcome', [
    'greeting' => $greeting,
    'user' => $user,
    'role' => $role
])

<!-- Role-Based Content -->
@if($role === 'guest')
    @php
        $canRegister = true;
        $hasApproved = (bool)($approvedRegistration ?? null);
        $currentStepResolved = $currentStep ?? session('current_step', 1);
        $hasDraft = session()->has('registration.candidate_data') || session()->has('registration.parents_data');
        $forceShowForm = session()->has('new_registration');
        $showFormByDefault = ($errors->any() || $hasDraft || ((int)$currentStepResolved > 1) || $forceShowForm);

        $detailRegistration = $pendingRegistration ?? $approvedRegistration ?? null;
    @endphp

    <!-- Guest Dashboard (Status Section) -->
    <div id="guest-registration-section" @if($showFormByDefault) hidden aria-hidden="true" @endif>
        @include('components.dashboard.guest-registration', [
            'pendingRegistration' => $pendingRegistration ?? null,
            'approvedRegistration' => $approvedRegistration ?? null,
            'hasChild' => $hasChild ?? false,
            'studentInfo' => $studentInfo ?? null,
        ])
    </div>

    <!-- Guest Registration Form (Embedded Section) -->
    @if($canRegister)
        <div id="registration-form-section" @if(!$showFormByDefault) hidden aria-hidden="true" @endif>
            @include('components.dashboard.registration-form-section', [
                'currentStep' => $currentStepResolved,
            ])
        </div>
    @endif

    @if(($hasStudent ?? false) || ($approvedRegistration ?? null) || ($pendingRegistration ?? null))
        @php
            $rp = static fn ($n) => 'Rp ' . number_format((float) $n, 0, ',', '.');
            $bs = $billSummary ?? [];
        @endphp

        {{-- Ringkasan KPI Orangtua --}}
        <div class="ui-stat-card-grid" style="margin-bottom: var(--ui-space-lg);">
            <x-ui.stat-card
                label="Tagihan Belum Lunas"
                :value="(int)($bs['pending_bills'] ?? 0) + (int)($bs['failed_bills'] ?? 0)"
                :hint="$rp($bs['outstanding_amount'] ?? 0) . ' outstanding'"
            />
            <x-ui.stat-card
                label="Anak Terdaftar"
                :value="(int)($bs['student_count'] ?? 0) . ' anak'"
                hint="status aktif"
            />
            <x-ui.stat-card
                label="Menunggu Verifikasi"
                :value="(int)($bs['waiting_verification'] ?? 0)"
                hint="bukti bayar di-review"
            />
        </div>

        {{-- Aksi cepat --}}
        <div class="ui-section">
            <h3 class="ui-section__title">Aksi Cepat</h3>
            <div style="display: flex; gap: var(--ui-space-sm); flex-wrap: wrap;">
                <a href="{{ route('dashboard.bills') }}" class="ui-btn ui-btn--primary">💳 Bayar Tagihan</a>
                <a href="{{ route('dashboard.students') }}" class="ui-btn ui-btn--secondary">📝 Absensi Anak</a>
                <a href="{{ route('dashboard.info') }}" class="ui-btn ui-btn--secondary">👤 Profil & Info</a>
                <a href="{{ route('registration.create') }}" class="ui-btn ui-btn--ghost">+ Daftar Anak Lain</a>
            </div>
        </div>

        {{-- Ringkasan per anak --}}
        @if(($studentSummaries ?? collect())->count() > 0)
            <div class="ui-section">
                <h3 class="ui-section__title">Anak Saya</h3>
                <div class="ui-table-wrapper" style="box-shadow: none; border: none;">
                    <table class="ui-table">
                        <thead>
                            <tr>
                                <th>Nama</th>
                                <th>Grup</th>
                                <th>Status</th>
                                <th>Tagihan Pending</th>
                                <th>Absensi Terakhir</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($studentSummaries as $sum)
                                @php
                                    $statusBadge = match (strtolower($sum['status_label'] ?? '')) {
                                        'aktif' => 'success',
                                        'ditolak', 'rejected' => 'danger',
                                        default => 'neutral',
                                    };
                                @endphp
                                <tr>
                                    <td><strong>{{ $sum['name'] ?? '-' }}</strong></td>
                                    <td>{{ $sum['group'] ?? '-' }}</td>
                                    <td><x-ui.badge :variant="$statusBadge">{{ $sum['status_label'] ?? '-' }}</x-ui.badge></td>
                                    <td>
                                        @if((int)($sum['pending_bills'] ?? 0) > 0)
                                            <x-ui.badge variant="warning">{{ $sum['pending_bills'] }}</x-ui.badge>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td style="color: var(--color-muted);">
                                        {{ $sum['latest_attendance'] ?? '-' }}
                                        @if(!empty($sum['latest_attendance_date']))
                                            <span class="ui-stat-card__hint">({{ $sum['latest_attendance_date'] }})</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    @endif

    @if($detailRegistration)
        @include('components.dashboard.registration-detail-modal', [
            'registration' => $detailRegistration,
        ])
    @endif

@else
    <!-- Staff/Admin Dashboard -->
    @include('components.dashboard.stats', [
        'stats' => $stats
    ])

    @include('components.dashboard.placeholder')
@endif

<script>
    (function () {
        const guestSection = document.getElementById('guest-registration-section');
        const formSection = document.getElementById('registration-form-section');

        // If the server rendered the form visible (errors/draft/step>1), keep it visible
        // even when the URL hash is empty (fragments are not preserved on validation redirects).
        const serverWantsFormVisible = !!(formSection && !formSection.hidden);

        function setVisibility(showForm) {
            if (!guestSection || !formSection) return;

            if (showForm) {
                guestSection.hidden = true;
                guestSection.setAttribute('aria-hidden', 'true');
                formSection.hidden = false;
                formSection.setAttribute('aria-hidden', 'false');
            } else {
                formSection.hidden = true;
                formSection.setAttribute('aria-hidden', 'true');
                guestSection.hidden = false;
                guestSection.setAttribute('aria-hidden', 'false');
            }
        }

        function syncFromHash() {
            const hash = window.location.hash;

            if (hash === '#registration-form') {
                setVisibility(true);
                return;
            }

            if (!hash && serverWantsFormVisible) {
                setVisibility(true);
                return;
            }

            setVisibility(false);
        }

        window.addEventListener('hashchange', syncFromHash);
        syncFromHash();

        // Expose tombol "Tutup Formulir" → hide form, show status guest section.
        window.closeRegistrationForm = function () {
            // Reset hash dulu agar syncFromHash tidak otomatis show form lagi.
            if (window.location.hash === '#registration-form') {
                history.replaceState(null, '', window.location.pathname + window.location.search);
            }
            setVisibility(false);
            // Scroll ke atas supaya status section terlihat.
            window.scrollTo({ top: 0, behavior: 'smooth' });
        };

        // Registration detail modal (popup)
        const modal = document.getElementById('registration-detail-modal');
        const openButtons = document.querySelectorAll('[data-modal-open="registration-detail"]');
        const closeButtons = document.querySelectorAll('[data-modal-close="registration-detail"]');

        function openModal() {
            if (!modal) return;
            modal.hidden = false;
            modal.setAttribute('aria-hidden', 'false');
        }

        function closeModal() {
            if (!modal) return;
            modal.hidden = true;
            modal.setAttribute('aria-hidden', 'true');
        }

        openButtons.forEach((btn) => btn.addEventListener('click', openModal));
        closeButtons.forEach((btn) => btn.addEventListener('click', closeModal));

        if (modal) {
            modal.addEventListener('click', (e) => {
                if (e.target === modal) closeModal();
            });
        }

        window.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') closeModal();
        });
    })();
</script>

@endsection
