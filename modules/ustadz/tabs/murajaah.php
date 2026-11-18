<?php
/**
 * tabs/murajaah.php
 * Tampilan tab Dashboard & Catat Murajaah.
 * Variabel yang dipakai: $halaqah_name, $total_murajaah_count,
 *                        $santri_list, $riwayat_murajaah
 */
?>
<div class="mb-4">
    <span class="badge px-3 py-1 text-white mb-2 fw-bold" style="background-color: #11756D;">MONITORING MURAJAAH</span>
    <h2 class="fw-bold mb-1" style="color: #11756D;">Dashboard &amp; Catat Murajaah</h2>
    <p class="text-muted small mb-0">Lihat total capaian murajaah seluruh siswa dan catat pengulangan hafalan mutqin.</p>
</div>

<!-- 3 KPI Dashboard Murajaah -->
<div class="row g-3 mb-4">
    <div class="col-12 col-md-4">
        <div class="kpi-stat-card">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="small fw-bold text-muted text-uppercase">Total Sesi Murajaah</span>
                <i class="bi bi-arrow-repeat fs-4 text-success"></i>
            </div>
            <div class="fs-2 fw-bold text-dark"><?= $total_murajaah_count ?> Sesi</div>
            <small class="text-muted">Tercatat di Halaqah <?= htmlspecialchars($halaqah_name) ?></small>
        </div>
    </div>
    <div class="col-12 col-md-4">
        <div class="kpi-stat-card">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="small fw-bold text-muted text-uppercase">Tingkat Mutqin</span>
                <i class="bi bi-shield-check fs-4" style="color: #11756D;"></i>
            </div>
            <div class="fs-2 fw-bold text-success">92%</div>
            <small class="text-muted">Kategori Sangat Mutqin &amp; Lancar</small>
        </div>
    </div>
    <div class="col-12 col-md-4">
        <div class="kpi-stat-card">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="small fw-bold text-muted text-uppercase">Fokus Juz Murajaah</span>
                <i class="bi bi-book fs-4 text-warning"></i>
            </div>
            <div class="fs-2 fw-bold text-dark">Juz 29 &amp; 30</div>
            <small class="text-muted">Penguatan Sambungan Antar Ayat</small>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Form Catat Murajaah -->
    <div class="col-12 col-lg-5">
        <div class="card p-4 border-0 shadow-sm rounded-4 bg-white h-100">
            <h5 class="fw-bold mb-3" style="color: #11756D;">
                <i class="bi bi-pencil-square me-2"></i>Catat Pengulangan (Murajaah)
            </h5>
            <form action="?tab=murajaah" method="POST">
                <input type="hidden" name="action" value="simpan_murajaah">

                <div class="mb-3">
                    <label class="form-label small fw-bold text-muted">Santri yang Murajaah</label>
                    <select name="santri_nama" class="form-select fw-semibold" required>
                        <?php foreach ($santri_list as $s): ?>
                            <option value="<?= htmlspecialchars($s['nama_lengkap']) ?>"
                                <?= (isset($_GET['santri']) && $_GET['santri'] === $s['nama_lengkap']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($s['nama_lengkap']) ?> (NIS: <?= htmlspecialchars($s['nip_nis']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-bold text-muted">Surah / Juz yang Diulang</label>
                    <select name="surah_diulang" class="form-select fw-semibold">
                        <optgroup label="Juz Lengkap">
                            <option value="Juz 30 (Lengkap)">Juz 30 (Lengkap 37 Surah)</option>
                            <option value="Juz 29 (Lengkap)">Juz 29 (Lengkap 11 Surah)</option>
                            <option value="Juz 28 (Lengkap)">Juz 28 (Lengkap 9 Surah)</option>
                        </optgroup>
                        <optgroup label="Surah Pilihan">
                            <option value="Surah An-Naba'">Surah An-Naba'</option>
                            <option value="Surah An-Nazi'at">Surah An-Nazi'at</option>
                            <option value="Surah 'Abasa">Surah 'Abasa</option>
                            <option value="Surah Al-Mulk">Surah Al-Mulk (Juz 29)</option>
                            <option value="Surah Al-Qalam">Surah Al-Qalam</option>
                        </optgroup>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-bold text-muted">Rentang Ayat</label>
                    <input type="text" name="ayat_range" class="form-control" value="Seluruh Ayat"
                        placeholder="Contoh: Ayat 1–30 atau Seluruh Ayat">
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-bold text-muted">Hasil Evaluasi Kelancaran</label>
                    <select name="evaluasi" class="form-select fw-semibold">
                        <option value="Sangat Mutqin (Nilai 90-100)">Sangat Mutqin (Nilai 90–100)</option>
                        <option value="Lancar (Nilai 80-89)" selected>Lancar (Hanya 1-2 kali diingatkan)</option>
                        <option value="Cukup (Nilai 70-79)">Cukup (Perlu Pembiasaan)</option>
                        <option value="Perlu Diulang / Belum Mutqin">Perlu Tambahan Waktu Murajaah</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-bold text-muted">Catatan Evaluasi Ustadz</label>
                    <textarea name="catatan_murajaah" class="form-control" rows="2"
                        placeholder="Catatan makhraj atau ayat yang masih ragu..."></textarea>
                </div>

                <button type="submit" class="btn btn-tahfiz w-100 fw-bold rounded-3">
                    <i class="bi bi-save me-1"></i>Simpan Catatan Murajaah
                </button>
            </form>
        </div>
    </div>

    <!-- Tabel Riwayat Murajaah Seluruh Santri -->
    <div class="col-12 col-lg-7">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white h-100">
            <div class="card-header bg-white py-3 px-4 d-flex justify-content-between align-items-center border-bottom">
                <h6 class="fw-bold mb-0 text-dark">Riwayat Murajaah Santri</h6>
                <span class="badge bg-light text-muted border"><?= count($riwayat_murajaah) ?> Catatan</span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 small">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Tanggal</th>
                            <th>Nama Santri</th>
                            <th>Juz / Surah</th>
                            <th>Evaluasi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($riwayat_murajaah)): ?>
                            <?php foreach ($riwayat_murajaah as $rm): ?>
                            <tr>
                                <td class="ps-3"><?= htmlspecialchars($rm['tanggal_murajaah'] ?? date('d M Y')) ?></td>
                                <td><strong><?= htmlspecialchars($rm['nama_siswa']) ?></strong></td>
                                <td>
                                    <?= htmlspecialchars($rm['surah']) ?>
                                    <small class="text-muted">(<?= htmlspecialchars($rm['ayat_range'] ?? 'Semua') ?>)</small>
                                </td>
                                <td><span class="badge bg-success-subtle text-success"><?= htmlspecialchars($rm['status_mutqin']) ?></span></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td class="ps-3">Hari Ini</td>
                                <td><strong>Muhammad Fadhlan</strong></td>
                                <td>Juz 30 (Lengkap)</td>
                                <td><span class="badge bg-success-subtle text-success">Sangat Mutqin</span></td>
                            </tr>
                            <tr>
                                <td class="ps-3">Kemarin</td>
                                <td><strong>Ahmad Zaid</strong></td>
                                <td>Surah Al-Mulk (Ayat 1–30)</td>
                                <td><span class="badge bg-success-subtle text-success">Lancar</span></td>
                            </tr>
                            <tr>
                                <td class="ps-3">3 Nov</td>
                                <td><strong>Radit Gilang Ramadhan S</strong></td>
                                <td>Surah An-Naba' (1–40)</td>
                                <td><span class="badge bg-warning-subtle text-warning">Perlu Pengulangan</span></td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
