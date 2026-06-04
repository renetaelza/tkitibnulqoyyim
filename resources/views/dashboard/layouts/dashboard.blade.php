<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard - TK Ibnul Qoyyim')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        /* ===== DASHBOARD LAYOUT ===== */
        body.dashboard {
            display: flex;
            min-height: 100vh;
        }

        .dashboard-wrapper {
            display: flex;
            width: 100%;
            min-height: 100vh;
            background: var(--bg);
        }

        /* SIDEBAR */
        .sidebar {
            width: 260px;
            background: white;
            border-right: 2px solid var(--green-light);
            box-shadow: 2px 0 8px rgba(46,204,113,0.1);
            position: fixed;
            left: 0;
            top: 0;
            height: 100vh;
            display: flex;
            flex-direction: column;
            overflow-y: hidden;
            overflow-x: visible;
            z-index: 100;
            transition: transform 0.3s ease, width 0.2s ease;
        }

        .sidebar-collapse-handle-global {
            position: fixed;
            top: 50%;
            left: 260px;
            transform: translate(-50%, -50%);
            width: 36px;
            height: 36px;
            border-radius: 999px;
            border: 2px solid var(--green-light);
            background: white;
            color: var(--green-dark);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 900;
            cursor: pointer;
            transition: all 0.2s;
            z-index: 110;
            box-shadow: 0 6px 18px rgba(0,0,0,0.10);
        }

        .sidebar-collapse-handle-global:hover {
            background: var(--green-light);
        }

        .sidebar-collapse-handle-global:active {
            transform: translate(-50%, -50%) scale(0.98);
        }

        body.sidebar-collapsed .sidebar-collapse-handle-global {
            left: 104px;
            transform: translate(-50%, -50%) rotate(180deg);
        }

        body.sidebar-collapsed .sidebar {
            width: 104px;
        }

        /* MAIN CONTENT */
        .dashboard-main {
            flex: 1;
            min-width: 0;
            margin-left: 260px;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            transition: margin-left 0.2s ease;
        }

        body.sidebar-collapsed .dashboard-main {
            margin-left: 104px;
        }

        .dashboard-topbar {
            background: white;
            border-bottom: 2px solid var(--green-light);
            padding: 16px 32px;
            box-shadow: 0 2px 8px rgba(46,204,113,0.08);
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 90;
        }

        .sidebar-mobile-toggle {
            display: none;
            width: 40px;
            height: 40px;
            border-radius: 12px;
            border: 2px solid var(--green-light);
            background: white;
            color: var(--green-dark);
            font-size: 18px;
            font-weight: 900;
            cursor: pointer;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
            box-shadow: 0 4px 12px rgba(46,204,113,0.12);
        }

        .sidebar-mobile-toggle:hover {
            background: var(--green-light);
        }

        .sidebar-mobile-toggle:active {
            transform: scale(0.98);
        }

        .topbar-left {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .topbar-title {
            font-family: 'Fredoka One', cursive;
            font-size: 24px;
            color: var(--dark);
        }

        .topbar-right {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .user-info {
            text-align: right;
        }

        .user-name {
            font-weight: 800;
            color: var(--dark);
            font-size: 14px;
        }

        .user-role {
            font-size: 12px;
            color: var(--gray);
            font-weight: 600;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--green), var(--blue));
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 18px;
        }

        .dashboard-content {
            flex: 1;
            min-width: 0;
            padding: 32px;
            overflow-x: hidden;
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 900px) {
            .sidebar {
                width: 220px;
            }

            .sidebar-collapse-handle-global {
                left: 220px;
            }

            body.sidebar-collapsed .sidebar {
                width: 96px;
            }

            body.sidebar-collapsed .sidebar-collapse-handle-global {
                left: 96px;
            }

            .dashboard-main {
                margin-left: 220px;
            }

            body.sidebar-collapsed .dashboard-main {
                margin-left: 96px;
            }

            .dashboard-content {
                padding: 24px;
            }

            .dashboard-topbar {
                padding: 14px 24px;
            }
        }

        @media (max-width: 600px) {
            .sidebar {
                width: 100%;
                left: -100%;
                height: 100%;
                border-right: none;
                border-bottom: 2px solid var(--green-light);
            }

            .sidebar.active {
                left: 0;
            }

            .sidebar-collapse-handle-global {
                display: none;
            }

            .dashboard-main {
                margin-left: 0;
            }

            body.sidebar-collapsed .dashboard-main {
                margin-left: 0;
            }

            .dashboard-content {
                padding: 16px;
            }

            .dashboard-topbar {
                padding: 12px 16px;
            }

            .topbar-title {
                font-size: 18px;
            }

            .sidebar-mobile-toggle {
                display: inline-flex;
            }

            .user-info {
                display: none;
            }
        }

        /* SCROLLBAR */
        .sidebar-nav::-webkit-scrollbar {
            width: 6px;
        }

        .sidebar-nav::-webkit-scrollbar-track {
            background: var(--light);
        }

        .sidebar-nav::-webkit-scrollbar-thumb {
            background: var(--green-light);
            border-radius: 3px;
        }

        .sidebar-nav::-webkit-scrollbar-thumb:hover {
            background: var(--green);
        }

    </style>
</head>
<body class="dashboard">
    <div class="dashboard-wrapper">
        <!-- SIDEBAR -->
        @include('components.sidebar', [
            'userRole' => auth()->user()->role ?? 'guest',
            'currentRoute' => request()->route()?->getName() ?? ''
        ])

        <button type="button" class="sidebar-collapse-handle-global" onclick="toggleSidebarCollapse()" aria-label="Toggle sidebar" title="Minimize / Expand">
            ❮
        </button>

        <!-- MAIN CONTENT -->
        <div class="dashboard-main">
            <!-- TOPBAR -->
            <div class="dashboard-topbar">
                <div class="topbar-left">
                    <button type="button" class="sidebar-mobile-toggle" onclick="toggleSidebar()" aria-label="Buka menu" title="Menu">
                        ☰
                    </button>
                    <h1 class="topbar-title">@yield('page_title', 'Dashboard')</h1>
                </div>
                <div class="topbar-right">
                    <div class="user-info">
                        <div class="user-name">{{ auth()->user()->name ?? 'User' }}</div>
                        <div class="user-role">{{ ucfirst(str_replace('_', ' ', auth()->user()->role ?? 'guest')) }}</div>
                    </div>
                    <div class="user-avatar">{{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}</div>
                </div>
            </div>

            <!-- CONTENT -->
            <div class="dashboard-content">
                @yield('content')
            </div>
        </div>
    </div>

    <script>
        // Mobile sidebar toggle
        function toggleSidebar() {
            const sidebar = document.querySelector('.sidebar');
            if (sidebar) {
                sidebar.classList.toggle('active');
            }
        }

        // Desktop sidebar collapse toggle
        function toggleSidebarCollapse() {
            document.body.classList.toggle('sidebar-collapsed');
            try {
                const collapsed = document.body.classList.contains('sidebar-collapsed') ? '1' : '0';
                localStorage.setItem('sidebar_collapsed', collapsed);
            } catch (_) {
                // ignore
            }
        }

        (function initSidebarCollapsedState() {
            try {
                const collapsed = localStorage.getItem('sidebar_collapsed');
                if (collapsed === '1') {
                    document.body.classList.add('sidebar-collapsed');
                }
            } catch (_) {
                // ignore
            }
        })();
    </script>
</body>
</html>
