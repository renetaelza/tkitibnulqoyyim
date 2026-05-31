@extends('layouts.dashboard')

@section('title', 'Riwayat Dana - TK Ibnul Qoyyim')
@section('page_title', 'Riwayat Dana')

@php
    $rp = static fn ($n) => 'Rp ' . number_format((float) $n, 0, ',', '.');
@endphp

@section('content')

<x-ui.page-header title="Riwayat Dana">
    <x-slot:action>
        <a href="{{ route('admin.bendahara.transactions.export', ['bulan' => $periode->format('Y-m')]) }}" class="ui-btn ui-btn--secondary">
            Export CSV
        </a>
    </x-slot:action>
</x-ui.page-header>

<div class="ui-toolbar">
    <form method="GET" action="{{ route('admin.bendahara.transactions.index') }}">
        <input
            type="month"
            name="bulan"
            value="{{ $periode->format('Y-m') }}"
            class="ui-input"
            onchange="this.form.submit()"
            style="width: auto;"
        >
    </form>
    <div style="color: var(--color-muted); font-size: var(--ui-font-sm);">
        Periode: <strong style="color: var(--color-text);">{{ $periodeLabel }}</strong>
    </div>
</div>

{{-- Summary --}}
<div class="ui-stat-card-grid" style="margin-bottom: var(--ui-space-lg);">
    <x-ui.stat-card label="Total Masuk" :value="$rp($totalIn)" hint="↑ uang yang diterima" />
    <x-ui.stat-card label="Total Keluar" :value="$rp($totalOut)" hint="↓ uang yang dibayarkan" />
    <x-ui.stat-card label="Net" :value="$rp($totalNet)" :hint="$totalNet >= 0 ? 'surplus' : 'defisit'" />
</div>

{{-- Charts --}}
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(360px, 1fr)); gap: var(--ui-space-md); margin-bottom: var(--ui-space-lg);">
    <div class="ui-section">
        <h3 class="ui-section__title">Net per Sumber Dana ({{ $periodeLabel }})</h3>
        <p class="ui-stat-card__hint" style="margin-bottom: var(--ui-space-md);">
            Klik segment untuk lihat detail transaksi.
        </p>
        <div style="position: relative; height: 320px;">
            <canvas id="donutChart"></canvas>
        </div>
    </div>

    <div class="ui-section">
        <h3 class="ui-section__title">Trend Masuk vs Keluar (6 Bulan)</h3>
        <p class="ui-stat-card__hint" style="margin-bottom: var(--ui-space-md);">&nbsp;</p>
        <div style="position: relative; height: 320px;">
            <canvas id="trendChart"></canvas>
        </div>
    </div>
</div>

{{-- Modal drill-down --}}
<x-ui.modal name="tx-detail-modal" title="Detail Transaksi" maxWidth="720px">
    <div id="tx-detail-content">
        <p class="ui-stat-card__hint">Memuat...</p>
    </div>
</x-ui.modal>

<script>
    const DONUT_DATA = @json($donutData);
    const TREND_DATA = @json($trendData);
    const BULAN_PARAM = '{{ $periode->format('Y-m') }}';
    const BY_SOURCE_URL = '{{ url('/admin/bendahara/transactions/by-source') }}';

    function formatRp(n) {
        return 'Rp ' + Number(n).toLocaleString('id-ID', { maximumFractionDigits: 0 });
    }

    // Tunggu Chart.js loaded dari window (akan di-import di app.js).
    document.addEventListener('DOMContentLoaded', () => {
        if (typeof window.Chart === 'undefined') {
            document.getElementById('donutChart').parentElement.innerHTML =
                '<p class="ui-form-error">Chart.js belum termuat. Jalankan <code>npm install</code> lalu <code>npm run build</code>.</p>';
            return;
        }

        // Donut: net per sumber (gunakan absolute value untuk display, warna by sign)
        const donutValues = DONUT_DATA.net.map(Math.abs);
        const donutColors = DONUT_DATA.net.map(v =>
            v >= 0 ? 'rgba(46, 204, 113, 0.7)' : 'rgba(198, 40, 40, 0.7)'
        );

        const donutCtx = document.getElementById('donutChart').getContext('2d');
        const donutChart = new window.Chart(donutCtx, {
            type: 'doughnut',
            data: {
                labels: DONUT_DATA.labels,
                datasets: [{
                    data: donutValues,
                    backgroundColor: donutColors,
                    borderWidth: 1,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'right' },
                    tooltip: {
                        callbacks: {
                            label: (ctx) => {
                                const idx = ctx.dataIndex;
                                const net = DONUT_DATA.net[idx];
                                return `${ctx.label}: ${formatRp(net)}`;
                            },
                        },
                    },
                },
                onClick: (evt, elements) => {
                    if (!elements.length) return;
                    const idx = elements[0].index;
                    const sourceId = DONUT_DATA.sourceIds[idx];
                    openSourceDetail(sourceId);
                },
            },
        });

        // Line: trend 6 bulan
        const trendCtx = document.getElementById('trendChart').getContext('2d');
        new window.Chart(trendCtx, {
            type: 'line',
            data: {
                labels: TREND_DATA.labels,
                datasets: [
                    {
                        label: 'Masuk',
                        data: TREND_DATA.in,
                        borderColor: 'rgb(46, 204, 113)',
                        backgroundColor: 'rgba(46, 204, 113, 0.1)',
                        tension: 0.3,
                        fill: true,
                    },
                    {
                        label: 'Keluar',
                        data: TREND_DATA.out,
                        borderColor: 'rgb(198, 40, 40)',
                        backgroundColor: 'rgba(198, 40, 40, 0.1)',
                        tension: 0.3,
                        fill: true,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: (v) => 'Rp ' + Number(v).toLocaleString('id-ID', { notation: 'compact' }),
                        },
                    },
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: (ctx) => `${ctx.dataset.label}: ${formatRp(ctx.parsed.y)}`,
                        },
                    },
                },
            },
        });
    });

    function openSourceDetail(sourceId) {
        const container = document.getElementById('tx-detail-content');
        container.innerHTML = '<p class="ui-stat-card__hint">Memuat...</p>';
        window.dispatchEvent(new CustomEvent('open-modal', { detail: 'tx-detail-modal' }));

        fetch(`${BY_SOURCE_URL}/${sourceId}?bulan=${BULAN_PARAM}`, {
            headers: { 'Accept': 'application/json' },
        })
            .then(r => r.json())
            .then(data => renderTxTable(container, data))
            .catch(() => {
                container.innerHTML = '<p class="ui-form-error">Gagal memuat detail.</p>';
            });
    }

    function renderTxTable(container, data) {
        if (!data.items || data.items.length === 0) {
            container.innerHTML = `<h4 style="margin-top:0;">${data.source.name} · ${data.periode}</h4>
                <p class="ui-stat-card__hint">Tidak ada transaksi di periode ini.</p>`;
            return;
        }

        const rows = data.items.map(it => `
            <tr>
                <td>${it.date ?? '-'}</td>
                <td>${it.direction === 'in'
                    ? '<span class="ui-badge ui-badge--success">Masuk</span>'
                    : '<span class="ui-badge ui-badge--danger">Keluar</span>'}</td>
                <td style="text-align: right; font-weight: 600;">
                    ${it.direction === 'in' ? '+' : '−'}${formatRp(it.amount)}
                </td>
                <td style="color: var(--color-muted);">${(it.description ?? '-').substring(0, 60)}</td>
                <td>${it.creator ?? 'Sistem'}</td>
            </tr>
        `).join('');

        container.innerHTML = `
            <h4 style="margin-top:0;">${data.source.name} · ${data.periode}</h4>
            <p class="ui-stat-card__hint">${data.count} transaksi (max 50 ditampilkan)</p>
            <div class="ui-table-wrapper" style="box-shadow:none;border:none;">
                <table class="ui-table">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Arah</th>
                            <th style="text-align: right;">Nominal</th>
                            <th>Keterangan</th>
                            <th>Dicatat oleh</th>
                        </tr>
                    </thead>
                    <tbody>${rows}</tbody>
                </table>
            </div>
        `;
    }
</script>

@endsection
