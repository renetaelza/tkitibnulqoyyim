<!-- Progress Bar Component -->
<div class="progress-bar-wrapper">
    <div class="progress-bar">
        @for ($i = 1; $i <= $totalSteps; $i++)
            <div class="progress-step {{ $i <= $currentStep ? 'active' : '' }} {{ $i < $currentStep ? 'completed' : '' }}">
                <div class="step-number">
                    @if($i < $currentStep)
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"/>
                        </svg>
                    @else
                        {{ $i }}
                    @endif
                </div>
                <div class="step-label">
                    @switch($i)
                        @case(1)
                            Data Anak
                            @break
                        @case(2)
                            Data Orang Tua
                            @break
                        @case(3)
                            Konfirmasi
                            @break
                    @endswitch
                </div>
            </div>

            @if($i < $totalSteps)
                <div class="progress-line {{ $i < $currentStep ? 'completed' : '' }}"></div>
            @endif
        @endfor
    </div>
</div>
