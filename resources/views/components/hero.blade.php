<section id="home" class="hero">
    <div class="hero-bg-shapes">
        <div class="shape"></div>
        <div class="shape"></div>
        <div class="shape"></div>
        <div class="shape"></div>
    </div>
    
    <div class="hero-container">
        <div class="hero-content">
            <div class="hero-badge">{{ $badge ?? '🎉 Penerimaan Siswa Baru 2025/2026' }}</div>
            
            <h1 class="hero-title">
                {{ $titlePrefix ?? 'Sekolah' }} 
                <span class="highlight">{{ $titleHighlight ?? 'Terbaik' }}</span>
                @if($titleSuffix ?? null)
                    <br>{{ $titleSuffix }}
                @else
                    <br>untuk Buah Hati Anda
                @endif
            </h1>
            
            <p class="hero-subtitle">
                {{ $subtitle ?? 'TK Ibnul Qoyyim Sulawesi hadir dengan pendidikan Islam terpadu yang menyenangkan, kreatif, dan berkarakter. Membentuk generasi Qurani sejak dini.' }}
            </p>
            
            <div class="hero-buttons">
                <a href="{{ route('register') }}" class="btn-primary">✨ Daftar Sekarang</a>
                <a href="#profil" class="btn-secondary">📚 Lihat Profil</a>
            </div>
            
            @if($stats ?? null)
                <div class="hero-stats">
                    @foreach($stats as $index => $stat)
                        @if($index > 0)
                            <div class="stat-divider"></div>
                        @endif
                        <div class="stat-item">
                            <div class="stat-number">{{ $stat['number'] }}</div>
                            <div class="stat-label">{{ $stat['label'] }}</div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="hero-stats">
                    <div class="stat-item">
                        <div class="stat-number">12+</div>
                        <div class="stat-label">Tahun Berpengalaman</div>
                    </div>
                    <div class="stat-divider"></div>
                    <div class="stat-item">
                        <div class="stat-number">300+</div>
                        <div class="stat-label">Alumni Sukses</div>
                    </div>
                    <div class="stat-divider"></div>
                    <div class="stat-item">
                        <div class="stat-number">15</div>
                        <div class="stat-label">Guru Bersertifikat</div>
                    </div>
                </div>
            @endif
        </div>
        
        <div class="hero-visual">
            <div class="hero-main-card">
                <div class="school-illustration">
                    <span class="emoji-float">🏫</span>
                    <span class="emoji-float">⭐</span>
                    <span class="emoji-float">📚</span>
                    <span class="emoji-float">🎨</span>
                    <span class="emoji-float">🌈</span>
                </div>
                <div class="hero-card-info">
                    <div class="info-badge">🕌 Berbasis Islam</div>
                    <div class="info-badge">🎓 Terakreditasi A</div>
                    <div class="info-badge">💚 Terpercaya</div>
                </div>
            </div>
            <div class="float-card card-1">
                <span style="font-size:24px">🏆</span>
                <div>
                    <div style="font-size:13px;color:#1a9e52">Prestasi Terbaik</div>
                    <div style="font-size:11px;color:#636E72;font-weight:600">Juara Nasional 2024</div>
                </div>
            </div>
            <div class="float-card card-2">
                <span style="font-size:24px">❤️</span>
                <div>
                    <div style="font-size:13px;color:#2ba8a0">Orang Tua Puas</div>
                    <div style="font-size:11px;color:#636E72;font-weight:600">98% Rekomendasi</div>
                </div>
            </div>
        </div>
    </div>
</section>
