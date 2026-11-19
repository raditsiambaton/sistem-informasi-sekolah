<?php
/**
 * modules/siswa/tabs/progres.php
 * Tab "Progres Hafalan" — Dashboard Siswa
 * Guard: hanya boleh di-require dari modules/siswa/index.php
 */

if (!defined('SISWA_INDEX')) exit;
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <span class="badge text-white fw-bold px-3 py-1 mb-2 d-inline-block" style="background-color:#11756D; border-radius:20px; font-size:11px; letter-spacing:0.05em;">PROGRES HAFALAN</span>
        <h2 class="fw-bold mb-1" style="color: #0F172A;">Assalamu'alaikum, <?= htmlspecialchars(explode(' ', $siswa_name)[1] ?? $siswa_name) ?></h2>
        <p class="text-muted small mb-0">Sedikit demi sedikit, setiap ayat membawa kebaikan.</p>
    </div>
    <span class="badge fw-bold px-3 py-2 text-white" style="background-color:#11756D;">Halaqah <?= htmlspecialchars($halaqah_name) ?></span>
</div>

<!-- 3 KPI Siswa -->
<div class="row g-3 mb-4">
    <div class="col-12 col-md-4">
        <div class="kpi-card">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="small fw-bold text-muted">Total Hafalan</span>
                <i class="bi bi-book-half fs-5" style="color:#11756D;"></i>
            </div>
            <div class="fs-2 fw-bold text-dark">2 <span class="fs-6 text-muted fw-normal">juz</span></div>
            <small class="text-muted">Hafalan yang telah disetorkan</small>
        </div>
    </div>
    <div class="col-12 col-md-4">
        <div class="kpi-card">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="small fw-bold text-muted">Capaian Target</span>
                <i class="bi bi-bullseye fs-5" style="color:#11756D;"></i>
            </div>
            <div class="fs-2 fw-bold" style="color:#11756D;">60%</div>
            <small class="text-muted">18 dari 30 ayat sudah selesai</small>
        </div>
    </div>
    <div class="col-12 col-md-4">
        <div class="kpi-card kpi-card--warning">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="small fw-bold text-muted">Sisa Target</span>
                <i class="bi bi-clock-history text-warning fs-5"></i>
            </div>
            <div class="fs-2 fw-bold text-warning">12 <span class="fs-6 text-muted fw-normal">ayat</span></div>
            <small class="text-muted">Al-Mulk ayat 19–30</small>
        </div>
    </div>
</div>

<!-- Target Aktif & Langkah Selanjutnya -->
<div class="row g-4 mb-4">
    <div class="col-12 col-lg-8">
        <div class="card p-4 border-0 shadow-sm h-100" style="border-left: 4px solid #11756D !important;">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="small fw-bold text-muted text-uppercase">Target Hafalan Minggu Ini</span>
                <span class="badge bg-success-subtle text-success fw-bold">Aktif</span>
            </div>
            <h4 class="fw-bold mb-1" style="color: #11756D;">Surah Al-Mulk</h4>
            <p class="small text-muted mb-3">Ayat 1–30 · Juz 29 | Periode: 1–15 November 2026</p>
            <div class="progress mb-2" style="height: 10px;">
                <div class="progress-bar" style="width:60%; background-color:#11756D;"></div>
            </div>
            <div class="d-flex justify-content-between small fw-bold">
                <span class="text-muted">18 dari 30 ayat selesai</span>
                <span style="color:#11756D; font-weight:700;">60%</span>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-4">
        <div class="card p-4 border-0 shadow-sm h-100" style="background-color: #F4F7F6;">
            <span class="badge text-white fw-bold mb-2 align-self-start" style="background-color:#11756D;">LANGKAH SELANJUTNYA</span>
            <h5 class="fw-bold text-dark mb-2">Lanjutkan ayat 19–24</h5>
            <p class="small text-muted mb-3">
                Ulangi hafalan sebelumnya, lalu siapkan 6 ayat berikutnya untuk disimak oleh ustadz pembimbing.
            </p>
            <div class="small text-muted pt-2 border-top border-success border-opacity-25 mt-auto">
                Pembimbing: <strong>Ust. Rival Alvian Melfandy</strong>
            </div>
        </div>
    </div>
</div>

<!-- Tabel Setoran Terakhir -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h5 class="fw-bold mb-0" style="color:#11756D;">Setoran Terakhir</h5>
        <a href="?tab=riwayat" class="small fw-semibold text-decoration-none" style="color:#11756D;">Lihat Semua Riwayat →</a>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light small">
                <tr>
                    <th>Tanggal</th>
                    <th>Surah / Ayat</th>
                    <th>Kategori</th>
                    <th>Kualitas Hafalan</th>
                    <th>Guru Penerima</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($siswa_setoran)): ?>
                    <?php foreach (array_slice($siswa_setoran, 0, 5) as $set):
                        $kualitas_label = resolveKualitas(
                            (string)($set['kualitas'] ?? ''),
                            isset($set['nilai']) ? (int)$set['nilai'] : null
                        );
                        $kualitas_cls = kualitasClass($kualitas_label);
                    ?>
                    <tr>
                        <td><?= !empty($set['tanggal_setor']) ? date('d M Y', strtotime($set['tanggal_setor'])) : date('d M Y') ?></td>
                        <td><strong><?= htmlspecialchars($set['surah'], ENT_QUOTES, 'UTF-8') ?> · Ayat <?= htmlspecialchars($set['ayat_awal'], ENT_QUOTES, 'UTF-8') ?>–<?= htmlspecialchars($set['ayat_akhir'], ENT_QUOTES, 'UTF-8') ?></strong></td>
                        <td><span class="badge-kategori-hafalan-baru">Hafalan Baru</span></td>
                        <td><span class="<?= $kualitas_cls ?>"><?= htmlspecialchars($kualitas_label, ENT_QUOTES, 'UTF-8') ?></span></td>
                        <td><?= htmlspecialchars($set['nama_ustadz'], ENT_QUOTES, 'UTF-8') ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td>5 Nov 2026</td>
                        <td><strong>Al-Mulk · Ayat 13–18</strong></td>
                        <td><span class="badge-kategori-hafalan-baru">Hafalan Baru</span></td>
                        <td><span class="badge-kualitas-lancar">Lancar</span></td>
                        <td>Ust. Rival Alvian Melfandy</td>
                    </tr>
                    <tr>
                        <td>4 Nov 2026</td>
                        <td><strong>Al-Mulk · Ayat 7–12</strong></td>
                        <td><span class="badge-kategori-hafalan-baru">Hafalan Baru</span></td>
                        <td><span class="badge-kualitas-lancar">Lancar</span></td>
                        <td>Ust. Rival Alvian Melfandy</td>
                    </tr>
                    <tr>
                        <td>3 Nov 2026</td>
                        <td><strong>Al-Mulk · Ayat 1–6</strong></td>
                        <td><span class="badge-kategori-hafalan-baru">Hafalan Baru</span></td>
                        <td><span class="badge-kualitas-lancar">Lancar</span></td>
                        <td>Ust. Rival Alvian Melfandy</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
