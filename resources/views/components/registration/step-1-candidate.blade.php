<!-- Step 1: Candidate Data -->
<div class="form-step step-1">
    <h2 class="step-title">Data Calon Siswa</h2>

    <!-- Nama Lengkap -->
    <div class="form-group">
        <label for="name" class="form-label">Nama Lengkap <span class="required">*</span></label>
        <input 
            type="text" 
            id="name" 
            name="candidate_data[name]" 
            class="form-input {{ $errors->has('candidate_data.name') ? 'input-error' : '' }}"
            value="{{ old('candidate_data.name', $candidateData['name'] ?? '') }}"
            placeholder="Masukkan nama lengkap siswa"
            required
        >
        @if($errors->has('candidate_data.name'))
            <span class="form-error">{{ $errors->first('candidate_data.name') }}</span>
        @endif
    </div>

    <!-- Tempat Lahir -->
    <div class="form-group">
        <label for="birth_place" class="form-label">Tempat Lahir <span class="required">*</span></label>
        <input 
            type="text" 
            id="birth_place" 
            name="candidate_data[birth_place]" 
            class="form-input {{ $errors->has('candidate_data.birth_place') ? 'input-error' : '' }}"
            value="{{ old('candidate_data.birth_place', $candidateData['birth_place'] ?? '') }}"
            placeholder="Masukkan tempat lahir"
            required
        >
        @if($errors->has('candidate_data.birth_place'))
            <span class="form-error">{{ $errors->first('candidate_data.birth_place') }}</span>
        @endif
    </div>

    <!-- Tanggal Lahir -->
    <div class="form-group">
        <label for="birth_date" class="form-label">Tanggal Lahir <span class="required">*</span></label>
        <input 
            type="date" 
            id="birth_date" 
            name="candidate_data[birth_date]" 
            class="form-input {{ $errors->has('candidate_data.birth_date') ? 'input-error' : '' }}"
            value="{{ old('candidate_data.birth_date', $candidateData['birth_date'] ?? '') }}"
            required
        >
        @if($errors->has('candidate_data.birth_date'))
            <span class="form-error">{{ $errors->first('candidate_data.birth_date') }}</span>
        @endif
    </div>

    <!-- Jenis Kelamin -->
    <div class="form-group">
        <label for="gender" class="form-label">Jenis Kelamin <span class="required">*</span></label>
        <select 
            id="gender" 
            name="candidate_data[gender]" 
            class="form-input {{ $errors->has('candidate_data.gender') ? 'input-error' : '' }}"
            required
        >
            <option value="">-- Pilih Jenis Kelamin --</option>
            <option value="pria" {{ old('candidate_data.gender', $candidateData['gender'] ?? '') === 'pria' ? 'selected' : '' }}>Laki-laki</option>
            <option value="perempuan" {{ old('candidate_data.gender', $candidateData['gender'] ?? '') === 'perempuan' ? 'selected' : '' }}>Perempuan</option>
        </select>
        @if($errors->has('candidate_data.gender'))
            <span class="form-error">{{ $errors->first('candidate_data.gender') }}</span>
        @endif
    </div>

    <!-- Kelompok/Kelas -->
    <div class="form-group">
        <label for="group" class="form-label">Kelompok <span class="required">*</span></label>
        <select 
            id="group" 
            name="group" 
            class="form-input {{ $errors->has('group') ? 'input-error' : '' }}"
            required
        >
            <option value="">-- Pilih Kelompok --</option>
            <option value="A" {{ old('group') === 'A' || ($candidateData['group'] ?? '') === 'A' ? 'selected' : '' }}>Kelompok A (3-4 Tahun)</option>
            <option value="B" {{ old('group') === 'B' || ($candidateData['group'] ?? '') === 'B' ? 'selected' : '' }}>Kelompok B (4-5 Tahun)</option>
        </select>
        @if($errors->has('group'))
            <span class="form-error">{{ $errors->first('group') }}</span>
        @endif
    </div>

    <div class="form-info-box">
        <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
            <circle cx="8" cy="8" r="7.5" stroke="currentColor" stroke-width="1"/>
            <path d="M8 5V8M8 11H8.01" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
        </svg>
        <p>Pastikan data yang Anda masukkan sudah benar sebelum melanjutkan ke langkah berikutnya.</p>
    </div>
</div>
