<!-- Step 2: Parent Data -->
<div class="form-step step-2">
    <h2 class="step-title">Data Orang Tua/Wali</h2>

    <!-- Parent Tabs -->
    <div class="parent-tabs">
        <button type="button" class="tab-button active" data-tab="father">
            👨 Ayah
        </button>
        <button type="button" class="tab-button" data-tab="mother">
            👩 Ibu
        </button>
    </div>

    <!-- Father Data Tab -->
    <div class="tab-content active" id="father-tab">
        <h3 class="parent-subtitle">Data Ayah/Wali</h3>

        <div class="form-group">
            <label for="father_name" class="form-label">Nama Lengkap <span class="required">*</span></label>
            <input 
                type="text" 
                id="father_name" 
                name="parents_data[father_name]" 
                class="form-input {{ $errors->has('parents_data.father_name') ? 'input-error' : '' }}"
                value="{{ old('parents_data.father_name', $parentsData['father_name'] ?? '') }}"
                placeholder="Masukkan nama ayah/wali"
                required
            >
            @if($errors->has('parents_data.father_name'))
                <span class="form-error">{{ $errors->first('parents_data.father_name') }}</span>
            @endif
        </div>

        <div class="form-group">
            <label for="father_phone" class="form-label">Nomor Telepon <span class="required">*</span></label>
            <input 
                type="tel" 
                id="father_phone" 
                name="parents_data[father_phone]" 
                class="form-input {{ $errors->has('parents_data.father_phone') ? 'input-error' : '' }}"
                value="{{ old('parents_data.father_phone', $parentsData['father_phone'] ?? '') }}"
                placeholder="Contoh: 081234567890"
                required
            >
            @if($errors->has('parents_data.father_phone'))
                <span class="form-error">{{ $errors->first('parents_data.father_phone') }}</span>
            @endif
        </div>

        <div class="form-group">
            <label for="father_job" class="form-label">Pekerjaan</label>
            <input 
                type="text" 
                id="father_job" 
                name="parents_data[father_job]" 
                class="form-input"
                value="{{ old('parents_data.father_job', $parentsData['father_job'] ?? '') }}"
                placeholder="Masukkan pekerjaan (opsional)"
            >
        </div>

        <div class="form-group">
            <label for="father_address" class="form-label">Alamat</label>
            <textarea 
                id="father_address" 
                name="parents_data[father_address]" 
                class="form-input form-textarea"
                placeholder="Masukkan alamat (opsional)"
                rows="3"
            >{{ old('parents_data.father_address', $parentsData['father_address'] ?? '') }}</textarea>
        </div>
    </div>

    <!-- Mother Data Tab -->
    <div class="tab-content" id="mother-tab">
        <h3 class="parent-subtitle">Data Ibu</h3>

        <div class="form-group">
            <label for="mother_name" class="form-label">Nama Lengkap <span class="required">*</span></label>
            <input 
                type="text" 
                id="mother_name" 
                name="parents_data[mother_name]" 
                class="form-input {{ $errors->has('parents_data.mother_name') ? 'input-error' : '' }}"
                value="{{ old('parents_data.mother_name', $parentsData['mother_name'] ?? '') }}"
                placeholder="Masukkan nama ibu"
                required
            >
            @if($errors->has('parents_data.mother_name'))
                <span class="form-error">{{ $errors->first('parents_data.mother_name') }}</span>
            @endif
        </div>

        <div class="form-group">
            <label for="mother_phone" class="form-label">Nomor Telepon <span class="required">*</span></label>
            <input 
                type="tel" 
                id="mother_phone" 
                name="parents_data[mother_phone]" 
                class="form-input {{ $errors->has('parents_data.mother_phone') ? 'input-error' : '' }}"
                value="{{ old('parents_data.mother_phone', $parentsData['mother_phone'] ?? '') }}"
                placeholder="Contoh: 081234567890"
                required
            >
            @if($errors->has('parents_data.mother_phone'))
                <span class="form-error">{{ $errors->first('parents_data.mother_phone') }}</span>
            @endif
        </div>

        <div class="form-group">
            <label for="mother_job" class="form-label">Pekerjaan</label>
            <input 
                type="text" 
                id="mother_job" 
                name="parents_data[mother_job]" 
                class="form-input"
                value="{{ old('parents_data.mother_job', $parentsData['mother_job'] ?? '') }}"
                placeholder="Masukkan pekerjaan (opsional)"
            >
        </div>

        <div class="form-group">
            <label for="mother_address" class="form-label">Alamat</label>
            <textarea 
                id="mother_address" 
                name="parents_data[mother_address]" 
                class="form-input form-textarea"
                placeholder="Masukkan alamat (opsional)"
                rows="3"
            >{{ old('parents_data.mother_address', $parentsData['mother_address'] ?? '') }}</textarea>
        </div>
    </div>
</div>

<script>
    document.querySelectorAll('.tab-button').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const tab = this.dataset.tab;
            
            // Remove active class from all buttons and contents
            document.querySelectorAll('.tab-button').forEach(btn => btn.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
            
            // Add active class to clicked button and corresponding content
            this.classList.add('active');
            document.getElementById(tab + '-tab').classList.add('active');
        });
    });
</script>
