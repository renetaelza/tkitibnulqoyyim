@extends('layouts.landing')

@section('content')
    @include('components.hero')

    <!-- PROFIL -->
    <section id="profil" class="section section-white">
        <div class="section-header reveal">
            <div class="section-tag">📋 Tentang Kami</div>
            <h2 class="section-title">Profil <span class="accent">Sekolah</span></h2>
            <p class="section-desc">Mengenal lebih dekat TK Ibnul Qoyyim Sulawesi, sekolah Islam terpadu yang berdedikasi membentuk generasi emas.</p>
        </div>

        <div class="profil-grid">
            <div class="profil-visual reveal" style="position: relative;">
                <div class="profil-main-img">
                    <span class="profil-emoji">🏫</span>
                    <div class="motto-box">
                        <div class="motto-title">✦ MOTTO SEKOLAH</div>
                        <div class="motto-value">"Cerdas, Berkarakter, Qurani"</div>
                    </div>
                </div>
            </div>
            <div class="profil-content">
                <h3 class="profil-title">TK Ibnul Qoyyim Sulawesi</h3>
                <p class="profil-desc">Didirikan sejak tahun 2012, TK Ibnul Qoyyim Sulawesi hadir sebagai lembaga pendidikan anak usia dini berbasis Islam yang memadukan kurikulum nasional dengan nilai-nilai Islami. Berlokasi di Sulawesi, kami berkomitmen memberikan pendidikan terbaik untuk generasi penerus bangsa.</p>
                <div class="profil-info-grid">
                    <div class="profil-info-item">
                        <div class="label">📍 Lokasi</div>
                        <div class="value">Sulawesi, Indonesia</div>
                    </div>
                    <div class="profil-info-item">
                        <div class="label">🏢 Berdiri</div>
                        <div class="value">Tahun 2012</div>
                    </div>
                    <div class="profil-info-item">
                        <div class="label">🎓 Akreditasi</div>
                        <div class="value">A (Unggul)</div>
                    </div>
                    <div class="profil-info-item">
                        <div class="label">⏰ Jam Belajar</div>
                        <div class="value">07.30 – 12.00 WIT</div>
                    </div>
                    <div class="profil-info-item">
                        <div class="label">👶 Kelompok</div>
                        <div class="value">A (4–5 th) & B (5–6 th)</div>
                    </div>
                    <div class="profil-info-item">
                        <div class="label">☎️ Kontak</div>
                        <div class="value">0812-3456-7890</div>
                    </div>
                </div>
                <div class="visi-misi-tabs">
                    <button class="tab-btn active" data-tab="visi" onclick="switchTab('visi')">🌟 Visi</button>
                    <button class="tab-btn" data-tab="misi" onclick="switchTab('misi')">🎯 Misi</button>
                    <button class="tab-btn" data-tab="nilai" onclick="switchTab('nilai')">💙 Nilai</button>
                </div>
                <div class="tab-content active" id="visi">
                    Menjadi lembaga pendidikan anak usia dini Islam terbaik yang melahirkan generasi Qurani, cerdas, berakhlak mulia, dan siap menghadapi tantangan zaman dengan keimanan yang kuat.
                </div>
                <div class="tab-content" id="misi">
                    <ul>
                        <li>Menanamkan nilai-nilai Islam dan akhlak mulia sejak dini.</li>
                        <li>Mengembangkan potensi akademik, seni, dan olahraga anak.</li>
                        <li>Membentuk karakter kepemimpinan dan kemandirian.</li>
                        <li>Menciptakan lingkungan belajar yang menyenangkan, aman, dan islami.</li>
                    </ul>
                </div>
                <div class="tab-content" id="nilai">
                    <ul>
                        <li>Keimanan dan ketakwaan</li>
                        <li>Kejujuran dan tanggung jawab</li>
                        <li>Kedisiplinan dan kemandirian</li>
                        <li>Kreativitas dan kepedulian sosial</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- SARANA PRASARANA -->
    <section id="sarpras" class="section">
        <div class="section-header reveal">
            <div class="section-tag">🏗️ Fasilitas</div>
            <h2 class="section-title">Sarana & <span class="accent">Prasarana</span></h2>
            <p class="section-desc">Fasilitas lengkap dan modern yang mendukung proses belajar mengajar yang optimal dan menyenangkan.</p>
        </div>

        @php
            $facilities = $facilities ?? collect();
            $gradients = [
                'linear-gradient(135deg, #e8faf0, #c8f0dc)',
                'linear-gradient(135deg, #fff8e1, #ffe0b2)',
                'linear-gradient(135deg, #e3f2fd, #bbdefb)',
                'linear-gradient(135deg, #fce4ec, #f8bbd0)',
                'linear-gradient(135deg, #f3e5f5, #e1bee7)',
                'linear-gradient(135deg, #e8f5e9, #c8e6c9)',
                'linear-gradient(135deg, #fff3e0, #ffe0b2)',
                'linear-gradient(135deg, #e0f7fa, #b2ebf2)',
            ];
            $radials = [
                'radial-gradient(circle, #2ECC71 0%, transparent 70%)',
                'radial-gradient(circle, #FFD93D 0%, transparent 70%)',
                'radial-gradient(circle, #4ECDC4 0%, transparent 70%)',
                'radial-gradient(circle, #FF8FAB 0%, transparent 70%)',
                'radial-gradient(circle, #A78BFA 0%, transparent 70%)',
                'radial-gradient(circle, #2ECC71 0%, transparent 70%)',
                'radial-gradient(circle, #FF6B35 0%, transparent 70%)',
                'radial-gradient(circle, #4ECDC4 0%, transparent 70%)',
            ];
            $fallbackEmojis = ['🏫', '🕌', '📚', '🎪', '💻', '🍽️', '🏥', '🚗'];
        @endphp

        <div class="sarpras-grid">
            @forelse($facilities as $facility)
                @php
                    $idx = $loop->index % max(count($gradients), 1);
                    $bgGradient = $gradients[$idx] ?? $gradients[0];
                    $bgRadial = $radials[$idx] ?? $radials[0];
                    $emoji = $fallbackEmojis[$idx] ?? '🏫';

                    $rawPath = (string)($facility->image_path ?? '');
                    $hasImage = trim($rawPath) !== '';
                    $imageUrl = $hasImage
                        ? (preg_match('~^https?://~i', $rawPath) ? $rawPath : asset(ltrim($rawPath, '/')))
                        : null;
                @endphp

                <div class="sarpras-card {{ $loop->iteration <= 2 ? 'sarpras-featured' : '' }} reveal">
                    <div class="sarpras-img" style="background: {{ $bgGradient }};">
                        <div class="bg-decor" style="background: {{ $bgRadial }};"></div>

                        @if($hasImage)
                            <img class="sarpras-photo" src="{{ $imageUrl }}" alt="{{ $facility->name ?? 'Fasilitas' }}">
                        @else
                            <span class="emoji-main">{{ $emoji }}</span>
                        @endif
                    </div>
                    <div class="sarpras-info">
                        <div class="sarpras-name">{{ $facility->name ?? '-' }}</div>
                        <div class="sarpras-detail">{{ $facility->description ?? '' }}</div>
                        <span class="sarpras-badge">
                            {{ (int)($facility->quantity ?? 0) }} Unit • {{ $facility->condition ?? '-' }}
                        </span>
                    </div>
                </div>
            @empty
                <div class="sarpras-card reveal">
                    <div class="sarpras-img" style="background: linear-gradient(135deg, #e8faf0, #c8f0dc);">
                        <div class="bg-decor" style="background: radial-gradient(circle, #2ECC71 0%, transparent 70%);"></div>
                        <span class="emoji-main">🏗️</span>
                    </div>
                    <div class="sarpras-info">
                        <div class="sarpras-name">Belum ada data</div>
                        <div class="sarpras-detail">Fasilitas akan tampil di sini setelah ditambahkan dari Dashboard.</div>
                        <span class="sarpras-badge">Sarpras</span>
                    </div>
                </div>
            @endforelse
        </div>
    </section>

    <!-- GURU -->
    <section id="guru" class="section section-white">
        <div class="section-header reveal">
            <div class="section-tag">👩‍🏫 Tim Pengajar</div>
            <h2 class="section-title">Guru <span class="accent">Kami</span></h2>
            <p class="section-desc">Tim pengajar berpengalaman dan bersertifikat yang berdedikasi untuk perkembangan optimal setiap anak.</p>
        </div>
        
        <div class="guru-grid">
            <div class="guru-card reveal">
                <div class="guru-avatar" style="background: linear-gradient(135deg, #e8faf0, #c8f0dc);">👩‍💼</div>
                <div class="guru-name">Ustadzah Fatimah</div>
                <div class="guru-jabatan">Kepala Sekolah</div>
                <div class="guru-pendidikan">S2 Pendidikan Islam<br>15 Tahun Pengalaman</div>
            </div>
            <div class="guru-card reveal">
                <div class="guru-avatar" style="background: linear-gradient(135deg, #e3f2fd, #bbdefb);">👩‍🏫</div>
                <div class="guru-name">Ustadzah Aisyah</div>
                <div class="guru-jabatan">Guru Kelas A</div>
                <div class="guru-pendidikan">S1 PAUD<br>8 Tahun Pengalaman</div>
            </div>
            <div class="guru-card reveal">
                <div class="guru-avatar" style="background: linear-gradient(135deg, #fff8e1, #ffe0b2);">👩‍🏫</div>
                <div class="guru-name">Ustadzah Khadijah</div>
                <div class="guru-jabatan">Guru Kelas B</div>
                <div class="guru-pendidikan">S1 PAUD<br>6 Tahun Pengalaman</div>
            </div>
            <div class="guru-card reveal">
                <div class="guru-avatar" style="background: linear-gradient(135deg, #fce4ec, #f8bbd0);">👨‍🏫</div>
                <div class="guru-name">Ustadz Yusuf</div>
                <div class="guru-jabatan">Guru Tahfidz</div>
                <div class="guru-pendidikan">Hafidz 30 Juz<br>10 Tahun Pengalaman</div>
            </div>
        </div>
    </section>

    <!-- GALERI -->
    <section id="galeri" class="section section-light">
        <div class="section-header reveal">
            <div class="section-tag">📸 Dokumentasi</div>
            <h2 class="section-title">Galeri <span class="accent">Kegiatan</span></h2>
            <p class="section-desc">Momen-momen berharga kegiatan belajar dan bermain siswa TK Ibnul Qoyyim Sulawesi.</p>
        </div>
        
        <div class="galeri-scroll reveal">
            <div class="galeri-item" style="background: linear-gradient(135deg, #e8faf0, #c8f0dc);">
                <span class="galeri-emoji">🎨</span>
                <div class="galeri-label">Seni & Kreasi</div>
            </div>
            <div class="galeri-item" style="background: linear-gradient(135deg, #e3f2fd, #bbdefb);">
                <span class="galeri-emoji">📖</span>
                <div class="galeri-label">Belajar Quran</div>
            </div>
            <div class="galeri-item" style="background: linear-gradient(135deg, #fff8e1, #ffe0b2);">
                <span class="galeri-emoji">🏃</span>
                <div class="galeri-label">Olahraga Pagi</div>
            </div>
            <div class="galeri-item" style="background: linear-gradient(135deg, #fce4ec, #f8bbd0);">
                <span class="galeri-emoji">🎭</span>
                <div class="galeri-label">Pentas Seni</div>
            </div>
            <div class="galeri-item" style="background: linear-gradient(135deg, #f3e5f5, #e1bee7);">
                <span class="galeri-emoji">🎪</span>
                <div class="galeri-label">Wisuda TK</div>
            </div>
            <div class="galeri-item" style="background: linear-gradient(135deg, #e0f7fa, #b2ebf2);">
                <span class="galeri-emoji">🌱</span>
                <div class="galeri-label">Berkebun</div>
            </div>
            <div class="galeri-item" style="background: linear-gradient(135deg, #fff3e0, #ffe0b2);">
                <span class="galeri-emoji">🕌</span>
                <div class="galeri-label">Shalat Berjamaah</div>
            </div>
            <div class="galeri-item" style="background: linear-gradient(135deg, #e8f5e9, #c8e6c9);">
                <span class="galeri-emoji">🏆</span>
                <div class="galeri-label">Lomba & Prestasi</div>
            </div>
        </div>
    </section>
@endsection
