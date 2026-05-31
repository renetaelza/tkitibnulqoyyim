<!-- Form Navigation -->
<div class="form-navigation">
    <!-- Hidden field to track current step -->
    <input type="hidden" name="current_step" id="currentStep" value="{{ $currentStep }}">
    <input type="hidden" name="next_step" id="nextStep" value="">

    @if($currentStep > 1)
        <button type="button" class="btn btn-secondary" id="prevBtn">
            <svg width="20" height="20" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M15 5l-7 7 7 7"></path>
            </svg>
            Kembali
        </button>
    @endif

    @if($currentStep < $totalSteps)
        <button type="button" class="btn btn-primary" id="nextBtn">
            Lanjutkan
            <svg width="20" height="20" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M5 5l7 7-7 7"></path>
            </svg>
        </button>
    @else
        <button type="submit" class="btn btn-success" id="submitBtn">
            <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                <path d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"/>
            </svg>
            Kirim Pendaftaran
        </button>
    @endif
</div>

<script>
    // Handle navigation between steps
    const form = document.getElementById('registrationForm');
    const currentStep = parseInt(document.getElementById('currentStep').value);
    const nextStepInput = document.getElementById('nextStep');
    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');

    if (prevBtn) {
        prevBtn.addEventListener('click', (e) => {
            e.preventDefault();
            nextStepInput.value = currentStep - 1;
            form.submit();
        });
    }

    if (nextBtn) {
        nextBtn.addEventListener('click', (e) => {
            e.preventDefault();
            nextStepInput.value = currentStep + 1;
            form.submit();
        });
    }
</script>
