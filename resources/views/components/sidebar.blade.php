@php
    // ===== MENU CONFIGURATION PER ROLE =====
    // Define menu structure for each role
    $menus = [
        'superadmin' => [
            // Sidebar role superadmin = power user & system admin. 11 menu sesuai wireframe-superadmin-pages.md.
            [
                'group' => 'Utama',
                'items' => [
                    ['icon' => '🏦', 'label' => 'Dashboard', 'route' => route('admin.dashboard'), 'key' => 'admin.dashboard'],
                ],
            ],
            [
                'group' => 'Pengguna',
                'items' => [
                    ['icon' => '👥', 'label' => 'Manajemen Pengguna', 'route' => route('admin.users.index'), 'key' => 'admin.users.index'],
                ],
            ],
            [
                'group' => 'Master Data',
                'items' => [
                    ['icon' => '🧒', 'label' => 'Data Murid', 'route' => route('admin.students.index'), 'key' => 'admin.students.index'],
                    ['icon' => '👨‍🏫', 'label' => 'Data Guru', 'route' => route('admin.teachers.index'), 'key' => 'admin.teachers.index'],
                    ['icon' => '🏗️', 'label' => 'Sarpras', 'route' => route('admin.facilities.index'), 'key' => 'admin.facilities.index'],
                    ['icon' => '🗓️', 'label' => 'Hari Libur', 'route' => Route::has('admin.holidays.index') ? route('admin.holidays.index') : '#', 'key' => 'admin.holidays.index'],
                ],
            ],
            [
                'group' => 'Honor & Tunjangan',
                'items' => [
                    ['icon' => '💼', 'label' => 'Posisi & Tunjangan', 'route' => route('admin.positions.index'), 'key' => 'admin.positions.index'],
                    ['icon' => '📌', 'label' => 'Tarif Kehadiran', 'route' => route('admin.teacher-attendance-rates.index'), 'key' => 'admin.teacher-attendance-rates.index'],
                    ['icon' => '💰', 'label' => 'Honor Guru', 'route' => route('admin.teacher-honors.index'), 'key' => 'admin.teacher-honors.index'],
                ],
            ],
            [
                'group' => 'Keuangan',
                'items' => [
                    ['icon' => '🧾', 'label' => 'Master Payment', 'route' => route('admin.payments.index'), 'key' => 'admin.payments.index'],
                    ['icon' => '💳', 'label' => 'Tagihan Murid', 'route' => route('admin.student-payments.index'), 'key' => 'admin.student-payments.index'],
                ],
            ],
            [
                'group' => 'Pengaturan',
                'items' => [
                    ['icon' => '⚙️', 'label' => 'Info Pembayaran', 'route' => route('admin.settings.payment-info.edit'), 'key' => 'admin.settings.payment-info.edit'],
                ],
            ],
        ],
        'headmaster' => [
            // Sidebar role headmaster = laporan & monitoring. 4 menu sesuai wireframe-headmaster-pages.md.
            [
                'group' => 'Utama',
                'items' => [
                    ['icon' => '📊', 'label' => 'Dashboard', 'route' => Route::has('admin.headmaster.dashboard') ? route('admin.headmaster.dashboard') : route('admin.dashboard'), 'key' => 'admin.headmaster.dashboard'],
                ],
            ],
            [
                'group' => 'Laporan',
                'items' => [
                    ['icon' => '📋', 'label' => 'Laporan Bulanan', 'route' => Route::has('admin.headmaster.reports') ? route('admin.headmaster.reports') : '#', 'key' => 'admin.headmaster.reports'],
                ],
            ],
            [
                'group' => 'Data',
                'items' => [
                    ['icon' => '👨‍🏫', 'label' => 'Daftar Guru', 'route' => route('admin.teachers.index'), 'key' => 'admin.teachers.index'],
                    ['icon' => '🧒', 'label' => 'Daftar Murid', 'route' => route('admin.students.index'), 'key' => 'admin.students.index'],
                ],
            ],
        ],
        'administration' => [
            // Sidebar role administration = Bendahara (sesuai kebijakan operasional sekolah).
            // 9 menu utama sesuai wireframe-bendahara-pages.md.
            [
                'group' => 'Utama',
                'items' => [
                    ['icon' => '🏦', 'label' => 'Dashboard', 'route' => Route::has('admin.bendahara.dashboard') ? route('admin.bendahara.dashboard') : route('admin.dashboard'), 'key' => 'admin.bendahara.dashboard'],
                ],
            ],
            [
                'group' => 'Operasional',
                'items' => [
                    ['icon' => '📝', 'label' => 'Pendaftaran', 'route' => route('admin.registrations.index'), 'key' => 'admin.registrations.index'],
                    ['icon' => '💳', 'label' => 'Tagihan Murid', 'route' => route('admin.student-payments.index'), 'key' => 'admin.student-payments.index'],
                    ['icon' => '💸', 'label' => 'Honor Guru', 'route' => Route::has('admin.bendahara.honors.index') ? route('admin.bendahara.honors.index') : '#', 'key' => 'admin.bendahara.honors.index'],
                ],
            ],
            [
                'group' => 'Data & Pengaturan',
                'items' => [
                    ['icon' => '💼', 'label' => 'Posisi & Tunjangan', 'route' => route('admin.positions.index'), 'key' => 'admin.positions.index'],
                    ['icon' => '📌', 'label' => 'Tarif Kehadiran', 'route' => route('admin.teacher-attendance-rates.index'), 'key' => 'admin.teacher-attendance-rates.index'],
                    ['icon' => '🏗️', 'label' => 'Sarpras', 'route' => route('admin.facilities.index'), 'key' => 'admin.facilities.index'],
                    ['icon' => '👨‍👩‍👧', 'label' => 'Data Orangtua-Murid', 'route' => route('admin.parents.index'), 'key' => 'admin.parents.index'],
                ],
            ],
            [
                'group' => 'Laporan',
                'items' => [
                    ['icon' => '📊', 'label' => 'Riwayat Dana', 'route' => Route::has('admin.bendahara.transactions.index') ? route('admin.bendahara.transactions.index') : '#', 'key' => 'admin.bendahara.transactions.index'],
                ],
            ],
        ],
        'teacher' => [
            // Sidebar role teacher = self-service. 5 menu sesuai wireframe-teacher-pages.md.
            [
                'group' => 'Utama',
                'items' => [
                    ['icon' => '🏠', 'label' => 'Dashboard Saya', 'route' => Route::has('admin.teacher.dashboard') ? route('admin.teacher.dashboard') : route('admin.dashboard'), 'key' => 'admin.teacher.dashboard'],
                ],
            ],
            [
                'group' => 'Aktivitas',
                'items' => [
                    ['icon' => '🕒', 'label' => 'Absen Saya', 'route' => route('admin.my-attendance.index'), 'key' => 'admin.my-attendance.index'],
                    ['icon' => '🧒', 'label' => 'Murid Kelas Saya', 'route' => Route::has('admin.teacher.students') ? route('admin.teacher.students') : '#', 'key' => 'admin.teacher.students'],
                ],
            ],
            [
                'group' => 'Akun',
                'items' => [
                    ['icon' => '💰', 'label' => 'Honor Saya', 'route' => route('admin.my-honor.index'), 'key' => 'admin.my-honor.index'],
                    ['icon' => '👤', 'label' => 'Profil Saya', 'route' => Route::has('admin.teacher.profile.edit') ? route('admin.teacher.profile.edit') : '#', 'key' => 'admin.teacher.profile.edit'],
                ],
            ],
        ],
        'bendahara' => [
            [
                'group' => 'Utama',
                'items' => [
                    ['icon' => '📊', 'label' => 'Dashboard Bendahara', 'route' => Route::has('admin.bendahara.dashboard') ? route('admin.bendahara.dashboard') : '#', 'key' => 'admin.bendahara.dashboard'],
                ],
            ],
            [
                'group' => 'Pembayaran',
                'items' => [
                    ['icon' => '💰', 'label' => 'Pembayaran Honor', 'route' => Route::has('admin.bendahara.honors.index') ? route('admin.bendahara.honors.index') : '#', 'key' => 'admin.bendahara.honors.index'],
                    ['icon' => '🧾', 'label' => 'Honor (Generate)', 'route' => route('admin.teacher-honors.index'), 'key' => 'admin.teacher-honors.index'],
                ],
            ],
            [
                'group' => 'Pengaturan Honor',
                'items' => [
                    ['icon' => '📌', 'label' => 'Tarif Kehadiran', 'route' => route('admin.teacher-attendance-rates.index'), 'key' => 'admin.teacher-attendance-rates.index'],
                    ['icon' => '🧭', 'label' => 'Posisi Guru', 'route' => route('admin.positions.index'), 'key' => 'admin.positions.index'],
                    ['icon' => '🧩', 'label' => 'Penugasan Posisi', 'route' => route('admin.teacher-positions.index'), 'key' => 'admin.teacher-positions.index'],
                    ['icon' => '🎁', 'label' => 'Jenis Tunjangan', 'route' => route('admin.allowance-types.index'), 'key' => 'admin.allowance-types.index'],
                    ['icon' => '💼', 'label' => 'Tunjangan Posisi', 'route' => route('admin.position-allowances.index'), 'key' => 'admin.position-allowances.index'],
                ],
            ],
        ],
        'guest' => [
            // Sidebar role guest = orangtua. 4 menu sesuai wireframe-guest-pages.md.
            [
                'group' => 'Utama',
                'items' => [
                    ['icon' => '🏠', 'label' => 'Beranda', 'route' => route('dashboard'), 'key' => 'dashboard'],
                ],
            ],
            [
                'group' => 'Anak Saya',
                'items' => [
                    ['icon' => '📝', 'label' => 'Absensi Anak', 'route' => route('dashboard.students'), 'key' => 'dashboard.students'],
                    ['icon' => '💳', 'label' => 'Tagihan & Bayar', 'route' => route('dashboard.bills'), 'key' => 'dashboard.bills'],
                ],
            ],
            [
                'group' => 'Akun',
                'items' => [
                    ['icon' => '👤', 'label' => 'Profil & Info', 'route' => route('dashboard.info'), 'key' => 'dashboard.info'],
                ],
            ],
        ],
    ];

    // Get menu for current user role
    $userRole = $userRole ?? 'guest';
    $currentMenu = $menus[$userRole] ?? $menus['guest'];
@endphp

<style>
    /* ===== SIDEBAR STYLING ===== */
    .sidebar-header {
        padding: 24px 20px;
        border-bottom: 2px solid var(--green-light);
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .sidebar-logo {
        font-size: 32px;
    }

    .sidebar-brand {
        flex: 1;
    }

    .sidebar-brand-title {
        font-family: 'Fredoka One', cursive;
        font-size: 16px;
        color: var(--green-dark);
        line-height: 1.2;
    }

    .sidebar-brand-subtitle {
        font-size: 11px;
        color: var(--gray);
        font-weight: 700;
    }


    .sidebar-nav {
        flex: 1;
        min-height: 0;
        overflow-y: auto;
    }

    .sidebar-menu {
        padding: 20px 12px;
        list-style: none;
    }

    .sidebar-menu-item {
        list-style: none;
        margin-bottom: 6px;
    }

    .sidebar-group-title {
        padding: 10px 16px 8px;
        margin-top: 10px;
        font-size: 11px;
        font-weight: 900;
        color: var(--gray);
        letter-spacing: 0.08em;
        text-transform: uppercase;
        border-top: 1px solid var(--green-light);
    }

    .sidebar-group-title:first-child {
        margin-top: 0;
        border-top: none;
        padding-top: 0;
    }

    .sidebar-menu-link {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 16px;
        border-radius: 12px;
        color: var(--dark);
        text-decoration: none;
        font-size: 14px;
        font-weight: 600;
        transition: all 0.2s;
    }

    .sidebar-menu-link:hover {
        background: var(--green-light);
        color: var(--green-dark);
    }

    .sidebar-menu-link.active {
        background: linear-gradient(135deg, var(--green), var(--green-dark));
        color: white;
        font-weight: 800;
        box-shadow: 0 4px 12px rgba(46,204,113,0.3);
    }

    .sidebar-menu-icon {
        font-size: 20px;
        min-width: 24px;
    }

    /* Collapsed sidebar: icons only */
    body.sidebar-collapsed .sidebar-header {
        padding: 16px 12px;
        justify-content: space-between;
    }

    body.sidebar-collapsed .sidebar-brand {
        display: none;
    }

    body.sidebar-collapsed .sidebar-menu {
        padding: 16px 10px;
    }

    body.sidebar-collapsed .sidebar-menu-link {
        justify-content: center;
        padding: 12px;
    }

    body.sidebar-collapsed .sidebar-menu-text {
        display: none;
    }

    body.sidebar-collapsed .sidebar-group-title {
        display: none;
    }

    body.sidebar-collapsed .sidebar-logout-btn {
        justify-content: center;
    }

    body.sidebar-collapsed .sidebar-logout-text {
        display: none;
    }

    .sidebar-footer {
        padding: 20px 12px;
        border-top: 2px solid var(--green-light);
        margin-top: auto;
    }

    .sidebar-logout-btn {
        display: flex;
        align-items: center;
        gap: 12px;
        width: 100%;
        padding: 12px 16px;
        border: 2px solid var(--green);
        background: white;
        border-radius: 12px;
        color: var(--green-dark);
        font-size: 14px;
        font-weight: 800;
        cursor: pointer;
        transition: all 0.2s;
        text-decoration: none;
        text-align: left;
    }

    .sidebar-logout-btn:hover {
        background: var(--green-light);
    }

    .sidebar-logout-btn:active {
        transform: scale(0.98);
    }

    .sidebar-logout-form {
        margin: 0;
    }

    .sidebar-logout-icon {
        font-size: 20px;
    }

    /* Mobile sidebar adjustments */
    @media (max-width: 600px) {
        .sidebar {
            box-shadow: -2px 0 8px rgba(0,0,0,0.1);
        }

        .sidebar-header {
            padding: 20px 16px;
        }

        .sidebar-menu {
            padding: 16px 8px;
        }

        .sidebar-menu-link {
            padding: 10px 14px;
            font-size: 13px;
        }

        body.sidebar-collapsed .sidebar-brand {
            display: block;
        }

        body.sidebar-collapsed .sidebar-menu-text,
        body.sidebar-collapsed .sidebar-logout-text {
            display: inline;
        }

        body.sidebar-collapsed .sidebar-group-title {
            display: block;
        }

    }
</style>

<!-- SIDEBAR COMPONENT -->
<aside class="sidebar">
    <!-- SIDEBAR HEADER -->
    <header class="sidebar-header">
        <div class="sidebar-logo">🕌</div>
        <div class="sidebar-brand">
            <div class="sidebar-brand-title">TK Ibnul Qoyyim</div>
            <div class="sidebar-brand-subtitle">Sulawesi</div>
        </div>
    </header>

    <!-- SIDEBAR MENU -->
    <nav aria-label="Menu" class="sidebar-nav">
        <ul class="sidebar-menu">
            @foreach($currentMenu as $group)
                <li class="sidebar-group-title">{{ $group['group'] }}</li>
                @foreach($group['items'] as $item)
                    <li class="sidebar-menu-item">
                        <a href="{{ $item['route'] }}" class="sidebar-menu-link {{ $currentRoute === $item['key'] ? 'active' : '' }}" title="{{ $item['label'] }}">
                            <span class="sidebar-menu-icon">{{ $item['icon'] }}</span>
                            <span class="sidebar-menu-text">{{ $item['label'] }}</span>
                        </a>
                    </li>
                @endforeach
            @endforeach
        </ul>
    </nav>

    <!-- SIDEBAR FOOTER -->
    <div class="sidebar-footer">
        <form action="{{ route('logout') }}" method="POST" class="sidebar-logout-form">
            @csrf
            <button type="submit" class="sidebar-logout-btn">
                <span class="sidebar-logout-icon">🚪</span>
                <span class="sidebar-logout-text">Logout</span>
            </button>
        </form>
    </div>
</aside>
