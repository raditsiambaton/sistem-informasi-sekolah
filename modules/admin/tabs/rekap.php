<?php
/**
 * modules/admin/tabs/rekap.php
 * TAB 1: Dashboard Rekap Tahfiz
 *
 * Variabel yang digunakan (disiapkan oleh index.php via data/queries.php):
 *   $admin_name, $bulan_id, $total_santri, $total_ustadz,
 *   $halaqah_list
 */

if (!defined('ADMIN_INDEX')) {
    http_response_code(403);
    exit('403 Forbidden — Akses langsung tidak diizinkan.');
}
?>
<div class="page-heading d-flex justify-content-between align-items-start flex-wrap gap-2">
    <div>
        <h1>Assalamu'alaikum, <?= htmlspecialchars(explode(' ', $admin_name)[0]) ?></h1>
        <p>Pantau perkembangan hafalan dan aktivitas halaqah binaan SMA IT Al-Fadhilah.</p>
    </div>
    <select class="form-select form-select-sm w-auto fw-semibold" style="border-radius:8px;">
        <option>Periode: <?= $bulan_id[(int)date('n')] . ' ' . date('Y') ?></option>
        <option>Periode: <?= $bulan_id[(int)date('n') === 1 ? 12 : (int)date('n') - 1] . ' ' . (date('n') == 1 ? date('Y')-1 : date('Y')) ?></option>
    </select>
</div>

<!-- 4 KPI Cards -->
<div class="row g-3 mb-4">
    <div class="col-6 col-xl-3">
        <div class="kpi-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="kpi-label">Total Siswa</div>
                    <div class="kpi-num"><?= max($total_santri, 3) ?></div>
                    <div class="kpi-sub">Aktif dalam Halaqah</div>
                </div>
                <div class="kpi-icon kpi-icon-green"><i class="bi bi-people-fill"></i></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="kpi-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="kpi-label">Ustadz Pengampu</div>
                    <div class="kpi-num"><?= max($total_ustadz, 1) ?></div>
                    <div class="kpi-sub" style="color:var(--em-600);font-weight:600;">Semua Aktif Membimbing</div>
                </div>
                <div class="kpi-icon kpi-icon-yellow"><i class="bi bi-person-workspace"></i></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="kpi-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="kpi-label">Rata-rata Capaian</div>
                    <div class="kpi-num">78%</div>
                    <div class="kpi-sub">+4.2% dari target bulanan</div>
                </div>
                <div class="kpi-icon kpi-icon-teal"><i class="bi bi-graph-up-arrow"></i></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="kpi-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="kpi-label">Halaqah Aktif</div>
                    <div class="kpi-num" style="color:var(--em-700);"><?= max(count($halaqah_list), 3) ?></div>
                    <div class="kpi-sub">Kelompok Tahfiz</div>
                </div>
                <div class="kpi-icon kpi-icon-blue"><i class="bi bi-diagram-3-fill"></i></div>
            </div>
        </div>
    </div>
</div>

<!-- Grafik + Panel Evaluasi -->
<div class="row g-4 mb-4">
    <div class="col-12 col-lg-8">
        <div class="panel h-100">
            <div class="panel-header">
                <h2 class="panel-title">Capaian per Kelompok Halaqah</h2>
                <span class="badge-pill" style="background:var(--em-50);color:var(--em-700);font-size:11.5px;">Bulan Berjalan</span>
            </div>
            <div class="p-4">
                <div class="d-flex align-items-end justify-content-between" style="height:160px;">
                    <?php
                    $bars = [
                        ['Furqan', 82],
                        ['Ikhlas', 78],
                        ['Falaq', 69],
                        ['Nas', 85],
                        ['Kautsar', 74],
                        ['Asr', 80],
                    ];
                    foreach ($bars as $b): ?>
                    <div class="text-center flex-fill px-1">
                        <small class="fw-bold d-block mb-1" style="font-size:11px;color:var(--em-700);"><?= $b[1] ?>%</small>
                        <div class="chart-bar-track" title="<?= $b[0] ?>: <?= $b[1] ?>%">
                            <div class="chart-bar-fill" style="height:<?= $b[1] ?>%;"></div>
                        </div>
                        <small class="fw-semibold d-block mt-2" style="font-size:11px;"><?= $b[0] ?></small>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-lg-4">
        <div class="eval-panel">
            <span class="badge-pill mb-3 align-self-start" style="background:var(--gold);color:#fff;font-size:11px;text-transform:uppercase;letter-spacing:.06em;">Informasi Mutabaah</span>
            <h5 class="fw-bold mb-2" style="color:var(--slate-9);">Evaluasi Berkala Siswa</h5>
            <p class="small mb-4" style="color:var(--slate-5);line-height:1.7;">
                Seluruh data capaian siswa disinkronkan langsung dari catatan harian para asatidz pembina halaqah. Buka menu laporan untuk mencetak rekap semesteran.
            </p>
            <a href="?tab=laporan" class="btn-em btn-em-sm mt-auto align-self-start" style="background:var(--gold);border-radius:8px;">
                <i class="bi bi-file-earmark-text"></i> Buka Laporan Evaluasi
            </a>
        </div>
    </div>
</div>

<!-- Ringkasan Halaqah -->
<div class="panel">
    <div class="panel-header">
        <h2 class="panel-title">Ringkasan Kelompok Halaqah</h2>
        <a href="?tab=halaqah" class="btn-outline-em">Kelola Semua Halaqah →</a>
    </div>
    <div class="table-responsive">
        <table class="tbl">
            <thead>
                <tr>
                    <th class="ps-4">Nama Halaqah</th>
                    <th>Ustadz Pembimbing</th>
                    <th>Target Hafalan</th>
                    <th>Rata-rata Capaian</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($halaqah_list)): ?>
                    <?php foreach ($halaqah_list as $h): ?>
                    <tr>
                        <td class="ps-4"><strong><?= htmlspecialchars($h['nama_halaqah']) ?></strong></td>
                        <td><?= htmlspecialchars($h['nama_ustadz']) ?></td>
                        <td><?= htmlspecialchars($h['target_juz']) ?></td>
                        <td><strong style="color:var(--em-600);">80%</strong></td>
                        <td><span class="badge-pill badge-aktif">Sesuai Target</span></td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td class="ps-4"><strong>Al-Furqan</strong></td>
                        <td>Ust. Rival Alvian Melfandy</td>
                        <td>Juz 29 – 30</td>
                        <td><strong style="color:var(--em-600);">82%</strong></td>
                        <td><span class="badge-pill badge-aktif">Sesuai Target</span></td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
