@extends('layouts.dashboard')

@section('title', 'Detail Pendaftaran')
@section('page_title', 'Detail Pendaftaran')

@section('content')
    <div class="card" style="max-width: 900px; margin: 0 auto;">
        <h2 style="margin-bottom: 16px;">Status: {{ strtoupper(str_replace('_', ' ', $registration->status ?? '-')) }}</h2>

        @php
            $candidate = $registration->candidate_data ?? [];
            $parents = $registration->parents_data ?? [];

            $candidateBirthDateRaw = $candidate['birth_date'] ?? null;
            $candidateBirthDateLabel = '-';
            if ($candidateBirthDateRaw) {
                if (is_string($candidateBirthDateRaw)) {
                    $candidateBirthDateLabel = trim(explode(' ', $candidateBirthDateRaw)[0]);
                } elseif (is_object($candidateBirthDateRaw) && method_exists($candidateBirthDateRaw, 'format')) {
                    $candidateBirthDateLabel = $candidateBirthDateRaw->format('Y-m-d');
                } else {
                    $candidateBirthDateLabel = (string)$candidateBirthDateRaw;
                }
            }
        @endphp

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
            <div>
                <h3>Data Calon Siswa</h3>
                <div>Nama: {{ $candidate['name'] ?? '-' }}</div>
                <div>TTL: {{ $candidate['birth_place'] ?? '-' }}, {{ $candidateBirthDateLabel }}</div>
                <div>Gender: {{ $candidate['gender'] ?? '-' }}</div>
                <div>Kelompok: {{ $registration->group ?? '-' }}</div>
            </div>
            <div>
                <h3>Data Orang Tua</h3>
                <div>Ayah: {{ $parents['father_name'] ?? '-' }} ({{ $parents['father_phone'] ?? '-' }})</div>
                <div>Ibu: {{ $parents['mother_name'] ?? '-' }} ({{ $parents['mother_phone'] ?? '-' }})</div>
            </div>
        </div>

        <hr style="margin: 20px 0;" />

        @php
            $registrationDocs = [
                ['label' => 'Kartu Keluarga', 'path' => $registration?->kk_file_path, 'alt' => 'Kartu Keluarga'],
                ['label' => 'Pas Foto Anak', 'path' => $registration?->photo_file_path, 'alt' => 'Pas Foto Anak'],
                ['label' => 'Akta Kelahiran', 'path' => $registration?->birth_certificate_file_path, 'alt' => 'Akta Kelahiran'],
            ];
        @endphp

        <h3 style="margin-bottom: 12px;">Dokumen yang Diunggah</h3>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 12px;">
            @foreach($registrationDocs as $doc)
                @php
                    $docPath = $doc['path'];
                    $docExt = $docPath ? strtolower(pathinfo($docPath, PATHINFO_EXTENSION)) : '';
                    $docIsImg = in_array($docExt, ['jpg','jpeg','png','webp','gif']);
                @endphp
                <div style="border:1px solid #e5e7eb;border-radius:8px;padding:12px;background:#fff;">
                    <div style="font-weight:700;margin-bottom:8px;font-size:14px;">{{ $doc['label'] }}</div>
                    @if($docPath)
                        @if($docIsImg)
                            <a href="{{ asset($docPath) }}" target="_blank" rel="noopener" title="Buka di jendela baru">
                                <img src="{{ asset($docPath) }}" alt="{{ $doc['alt'] }}"
                                     style="width:100%;max-height:180px;object-fit:cover;border-radius:6px;border:1px solid #e5e7eb;display:block;">
                            </a>
                            <a href="{{ asset($docPath) }}" target="_blank" rel="noopener"
                               style="display:inline-block;margin-top:8px;font-size:13px;color:#2563eb;">
                                🔍 Lihat ukuran penuh
                            </a>
                        @else
                            <a href="{{ asset($docPath) }}" target="_blank" rel="noopener"
                               style="display:inline-block;padding:10px 14px;background:#f3f4f6;border-radius:6px;color:#1f2937;text-decoration:none;font-weight:600;">
                                📄 Lihat PDF
                            </a>
                        @endif
                    @else
                        <div style="color:#9ca3af;font-size:13px;">Belum diunggah</div>
                    @endif
                </div>
            @endforeach
        </div>

        @if(($registration->reject_reason ?? null))
            <div style="margin-top: 16px; color: #b91c1c; font-weight: 600;">Alasan ditolak: {{ $registration->reject_reason }}</div>
        @endif

        <div style="margin-top: 20px;">
            <a href="{{ route('dashboard') }}" class="btn-primary">Kembali ke Dashboard</a>
        </div>
    </div>
@endsection
