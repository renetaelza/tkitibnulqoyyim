@extends('layouts.dashboard')

@section('title', 'Absen Saya - TK Ibnul Qoyyim')
@section('page_title', 'Absen Saya')

@php
    $statusLabel = ['hadir' => 'Hadir', 'izin' => 'Izin', 'sakit' => 'Sakit', 'alpa' => 'Alpa'];
    $tz = $policy['timezone'] ?? 'Asia/Makassar';
@endphp

@section('content')

<x-ui.page-header :title="$now->translatedFormat('l, d F Y') . ' · ' . $now->format('H:i') . ' WITA'" />

@if(session('success'))
    <x-ui.toast variant="success">{{ session('success') }}</x-ui.toast>
@endif

@if($errors->any())
    <x-ui.toast variant="danger">
        @foreach($errors->all() as $err)
            <div>{{ $err }}</div>
        @endforeach
    </x-ui.toast>
@endif

@if(!$teacher)
    <x-ui.empty-state message="Akun Anda belum tertaut ke data guru. Hubungi admin." />
@else
    {{-- Status hari ini --}}
    <div class="ui-section">
        @php
            $checkInTz = $todayAttendance?->check_in_time?->copy()->setTimezone($tz);
            $statusToday = $todayAttendance?->status;
        @endphp

        @if($todayAttendance)
            <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: var(--ui-space-md);">
                <div>
                    <div class="ui-stat-card__label">Status Hari Ini</div>
                    <div style="font-size: var(--ui-font-xl); font-weight: 700; margin-top: var(--ui-space-xs);">
                        @if($statusToday === 'hadir')
                            @if($todayAttendance->is_late)
                                <span style="color: var(--color-warning);">Hadir (Telat)</span>
                            @else
                                <span style="color: var(--color-primary-dark);">Hadir Tepat Waktu ✓</span>
                            @endif
                        @else
                            {{ $statusLabel[$statusToday] ?? $statusToday }}
                        @endif
                    </div>
                </div>
                <div style="text-align: right;">
                    @if($checkInTz)
                        <div class="ui-stat-card__hint">Check-in</div>
                        <div style="font-size: var(--ui-font-lg); font-weight: 600;">{{ $checkInTz->format('H:i') }} WITA</div>
                        @if($todayAttendance->is_late)
                            <x-ui.badge variant="warning">Telat {{ (int) $todayAttendance->late_minutes }} menit</x-ui.badge>
                        @endif
                    @endif
                </div>
            </div>
            <p class="ui-stat-card__hint" style="margin-top: var(--ui-space-md);">
                Absensi hari ini sudah tercatat. Edit hanya bisa dilakukan oleh admin/kepsek.
            </p>
        @else
            <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: var(--ui-space-md);">
                <div>
                    <div class="ui-stat-card__label">Status Hari Ini</div>
                    <div style="font-size: var(--ui-font-xl); font-weight: 700; color: var(--color-muted); margin-top: var(--ui-space-xs);">
                        Belum Absen
                    </div>
                    <div class="ui-stat-card__hint" style="margin-top: var(--ui-space-xs);">
                        Jendela: {{ $policy['check_in_open'] }} – {{ $policy['check_in_close'] }} WITA ·
                        Batas tepat waktu: {{ $policy['late_after'] }} WITA
                    </div>
                </div>
                <div style="display: flex; gap: var(--ui-space-sm); flex-wrap: wrap;">
                    @if($window['can_check_in'])
                        <form method="POST" action="{{ route('admin.my-attendance.check-in') }}">
                            @csrf
                            <button type="submit" class="ui-btn ui-btn--primary">🕒 Absen Sekarang</button>
                        </form>
                    @endif
                    <button type="button" class="ui-btn ui-btn--secondary" onclick="window.dispatchEvent(new CustomEvent('open-modal', { detail: 'modal-izin' }))">📝 Ajukan Izin</button>
                    <button type="button" class="ui-btn ui-btn--secondary" onclick="window.dispatchEvent(new CustomEvent('open-modal', { detail: 'modal-sakit' }))">🤒 Lapor Sakit</button>
                </div>
            </div>
            @if(!$window['can_check_in'])
                <p class="ui-stat-card__hint" style="margin-top: var(--ui-space-md); color: var(--color-warning);">
                    ⚠ {{ $window['reason'] }}
                </p>
            @endif
        @endif
    </div>

    {{-- Riwayat absensi --}}
    <div class="ui-section">
        <h3 class="ui-section__title">Riwayat Absensi</h3>

        @if(!$attendances || $attendances->total() === 0)
            <x-ui.empty-state icon="📭" message="Belum ada riwayat absensi." />
        @else
            <div class="ui-table-wrapper" style="box-shadow: none; border: none;">
                <table class="ui-table">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Status</th>
                            <th>Check-in</th>
                            <th>Telat</th>
                            <th>Keterangan</th>
                            <th>Bukti</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($attendances as $row)
                            @php
                                $checkInTz = $row->check_in_time?->copy()->setTimezone($tz);
                                $statusVariant = match ($row->status) {
                                    'hadir' => 'success',
                                    'izin' => 'info',
                                    'sakit' => 'warning',
                                    'alpa' => 'danger',
                                    default => 'neutral',
                                };
                            @endphp
                            <tr>
                                <td>{{ $row->date?->format('d M Y') ?? '-' }}</td>
                                <td><x-ui.badge :variant="$statusVariant">{{ $statusLabel[$row->status] ?? $row->status }}</x-ui.badge></td>
                                <td>{{ $checkInTz?->format('H:i') ?? '-' }}</td>
                                <td>
                                    @if($row->is_late)
                                        <x-ui.badge variant="warning">{{ (int) $row->late_minutes }} mnt</x-ui.badge>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td style="color: var(--color-muted);">{{ \Illuminate\Support\Str::limit($row->information ?? '-', 50) }}</td>
                                <td>
                                    @if($row->attachment_path)
                                        <a href="{{ asset($row->attachment_path) }}" target="_blank" class="ui-btn ui-btn--ghost ui-btn--sm">Lihat</a>
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @include('components.dashboard.admin.pagination-controls', [
                'items' => $attendances,
                'per_page' => $per_page ?? 10,
            ])
        @endif
    </div>

    {{-- Modal Izin --}}
    <x-ui.modal name="modal-izin" title="Ajukan Izin" maxWidth="480px">
        <form method="POST" action="{{ route('admin.my-attendance.permission') }}" enctype="multipart/form-data">
            @csrf
            <div class="ui-form-group">
                <label class="ui-form-label">Tanggal</label>
                <input type="date" name="date" value="{{ $now->toDateString() }}" required class="ui-input">
            </div>
            <div class="ui-form-group">
                <label class="ui-form-label">Keterangan</label>
                <textarea name="information" rows="2" class="ui-textarea" placeholder="Alasan izin"></textarea>
            </div>
            <div class="ui-form-group">
                <label class="ui-form-label">Bukti (wajib)</label>
                <input type="file" name="attachment" accept=".pdf,.jpg,.jpeg,.png" required class="ui-input">
                <span class="ui-form-hint">PDF/JPG/PNG, max 2 MB</span>
            </div>
            <div style="display: flex; gap: var(--ui-space-sm); justify-content: flex-end;">
                <button type="button" class="ui-btn ui-btn--secondary" onclick="window.dispatchEvent(new CustomEvent('close-modal', { detail: 'modal-izin' }))">Batal</button>
                <button type="submit" class="ui-btn ui-btn--primary">Kirim Izin</button>
            </div>
        </form>
    </x-ui.modal>

    {{-- Modal Sakit --}}
    <x-ui.modal name="modal-sakit" title="Lapor Sakit" maxWidth="480px">
        <form method="POST" action="{{ route('admin.my-attendance.sick') }}" enctype="multipart/form-data">
            @csrf
            <div class="ui-form-group">
                <label class="ui-form-label">Tanggal</label>
                <input type="date" name="date" value="{{ $now->toDateString() }}" required class="ui-input">
            </div>
            <div class="ui-form-group">
                <label class="ui-form-label">Keterangan</label>
                <textarea name="information" rows="2" class="ui-textarea" placeholder="Keluhan singkat"></textarea>
            </div>
            <div class="ui-form-group">
                <label class="ui-form-label">Surat Sakit (opsional)</label>
                <input type="file" name="attachment" accept=".pdf,.jpg,.jpeg,.png" class="ui-input">
                <span class="ui-form-hint">PDF/JPG/PNG, max 2 MB</span>
            </div>
            <div style="display: flex; gap: var(--ui-space-sm); justify-content: flex-end;">
                <button type="button" class="ui-btn ui-btn--secondary" onclick="window.dispatchEvent(new CustomEvent('close-modal', { detail: 'modal-sakit' }))">Batal</button>
                <button type="submit" class="ui-btn ui-btn--primary">Kirim Sakit</button>
            </div>
        </form>
    </x-ui.modal>
@endif

@endsection
