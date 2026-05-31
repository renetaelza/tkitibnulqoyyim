<!-- Step 3: Review & Confirm -->
<div class="form-step step-3">
    <h2 class="step-title">Konfirmasi Data Pendaftaran</h2>

    <div class="review-section">
        <h3 class="review-subtitle">Data Calon Siswa</h3>
        <div class="review-grid">
            <div class="review-item">
                <div class="review-label">Nama Lengkap</div>
                <div class="review-value">{{ $candidateData['name'] ?? '-' }}</div>
            </div>
            <div class="review-item">
                <div class="review-label">Tempat Lahir</div>
                <div class="review-value">{{ $candidateData['birth_place'] ?? '-' }}</div>
            </div>
            <div class="review-item">
                <div class="review-label">Tanggal Lahir</div>
                <div class="review-value">
                    @if(isset($candidateData['birth_date']))
                        {{ \Carbon\Carbon::parse($candidateData['birth_date'])->locale('id_ID')->translatedFormat('d F Y') }}
                    @else
                        -
                    @endif
                </div>
            </div>
            <div class="review-item">
                <div class="review-label">Jenis Kelamin</div>
                <div class="review-value">{{ $candidateData['gender'] === 'pria' ? 'Laki-laki' : 'Perempuan' }}</div>
            </div>
            <div class="review-item">
                <div class="review-label">Kelompok</div>
                <div class="review-value">Kelompok {{ $group ?? '-' }}</div>
            </div>
        </div>
    </div>

    <div class="review-section">
        <h3 class="review-subtitle">Data Ayah/Wali</h3>
        <div class="review-grid">
            <div class="review-item">
                <div class="review-label">Nama</div>
                <div class="review-value">{{ $parentsData['father_name'] ?? '-' }}</div>
            </div>
            <div class="review-item">
                <div class="review-label">Telepon</div>
                <div class="review-value">{{ $parentsData['father_phone'] ?? '-' }}</div>
            </div>
            <div class="review-item">
                <div class="review-label">Pekerjaan</div>
                <div class="review-value">{{ $parentsData['father_job'] ?? '-' }}</div>
            </div>
            <div class="review-item">
                <div class="review-label">Alamat</div>
                <div class="review-value">{{ $parentsData['father_address'] ?? '-' }}</div>
            </div>
        </div>
    </div>

    <div class="review-section">
        <h3 class="review-subtitle">Data Ibu</h3>
        <div class="review-grid">
            <div class="review-item">
                <div class="review-label">Nama</div>
                <div class="review-value">{{ $parentsData['mother_name'] ?? '-' }}</div>
            </div>
            <div class="review-item">
                <div class="review-label">Telepon</div>
                <div class="review-value">{{ $parentsData['mother_phone'] ?? '-' }}</div>
            </div>
            <div class="review-item">
                <div class="review-label">Pekerjaan</div>
                <div class="review-value">{{ $parentsData['mother_job'] ?? '-' }}</div>
            </div>
            <div class="review-item">
                <div class="review-label">Alamat</div>
                <div class="review-value">{{ $parentsData['mother_address'] ?? '-' }}</div>
            </div>
        </div>
    </div>

    <div class="review-confirmation">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="12" r="10"></circle>
            <path d="M12 6v6l4 2"></path>
        </svg>
        <div>
            <p><strong>Mohon periksa kembali data Anda</strong></p>
            <p>Pastikan semua data sudah benar sebelum mengirimkan formulir pendaftaran.</p>
        </div>
    </div>

    <input type="hidden" name="candidate_data[name]" value="{{ $candidateData['name'] ?? '' }}">
    <input type="hidden" name="candidate_data[birth_place]" value="{{ $candidateData['birth_place'] ?? '' }}">
    <input type="hidden" name="candidate_data[birth_date]" value="{{ $candidateData['birth_date'] ?? '' }}">
    <input type="hidden" name="candidate_data[gender]" value="{{ $candidateData['gender'] ?? '' }}">
    <input type="hidden" name="group" value="{{ $group ?? '' }}">
    
    <input type="hidden" name="parents_data[father_name]" value="{{ $parentsData['father_name'] ?? '' }}">
    <input type="hidden" name="parents_data[father_phone]" value="{{ $parentsData['father_phone'] ?? '' }}">
    <input type="hidden" name="parents_data[father_job]" value="{{ $parentsData['father_job'] ?? '' }}">
    <input type="hidden" name="parents_data[father_address]" value="{{ $parentsData['father_address'] ?? '' }}">
    
    <input type="hidden" name="parents_data[mother_name]" value="{{ $parentsData['mother_name'] ?? '' }}">
    <input type="hidden" name="parents_data[mother_phone]" value="{{ $parentsData['mother_phone'] ?? '' }}">
    <input type="hidden" name="parents_data[mother_job]" value="{{ $parentsData['mother_job'] ?? '' }}">
    <input type="hidden" name="parents_data[mother_address]" value="{{ $parentsData['mother_address'] ?? '' }}">
</div>
