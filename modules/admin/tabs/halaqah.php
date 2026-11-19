<?php
/**
 * modules/admin/tabs/halaqah.php
 * TAB 3: Kelompok Halaqah Belajar
 *
 * Variabel yang digunakan (disiapkan oleh index.php via data/queries.php):
 *   $halaqah_list
 *
 * Catatan: Modal #modalTambahHalaqah tetap berada di index.php
 * agar Bootstrap JS dapat menemukannya.
 */

if (!defined('ADMIN_INDEX')) {
    http_response_code(403);
    exit('403 Forbidden — Akses langsung tidak diizinkan.');
}
?>
<div class="page-heading d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
        <h1>Kelompok Halaqah Belajar</h1>
        <p>Pembagian siswa dan asatidz pembimbing penanggung jawab mutabaah.</p>
    </div>
    <button class="btn-em" data-bs-toggle="modal" data-bs-target="#modalTambahHalaqah">
        <i class="bi bi-plus-circle-fill"></i> Tambah Halaqah
    </button>
</div>

<div class="panel">
    <div class="table-responsive">
        <table class="tbl">
            <thead>
                <tr>
                    <th class="ps-4">Kode</th>
                    <th>Nama Halaqah</th>
                    <th>Ustadz Pembimbing</th>
                    <th>Target Hafalan</th>
                    <th>Jumlah Siswa</th>
                    <th>Status</th>
                    <th class="text-end pe-4">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($halaqah_list)): ?>
                    <?php foreach ($halaqah_list as $h): 
                        $nama_h     = $h['nama_halaqah'];
                        $jml_siswa  = $jumlah_siswa_per_halaqah[$nama_h] ?? 0;
                        $member_ids = $halaqah_members_map[$nama_h] ?? [];
                    ?>
                    <tr>
                        <td class="ps-4"><strong><?= htmlspecialchars($h['kode_halaqah']) ?></strong></td>
                        <td>
                            <div class="fw-bold text-dark"><?= htmlspecialchars($h['nama_halaqah']) ?></div>
                        </td>
                        <td><?= htmlspecialchars($h['nama_ustadz']) ?></td>
                        <td><?= htmlspecialchars($h['target_juz']) ?></td>
                        <td>
                            <span class="badge-pill" style="background:var(--em-50);color:var(--em-800);border:1px solid var(--em-100);">
                                <i class="bi bi-people-fill me-1"></i><?= $jml_siswa ?> Siswa
                            </span>
                        </td>
                        <td><span class="badge-pill badge-aktif">Aktif</span></td>
                        <td class="text-end pe-4">
                            <button type="button" class="btn-outline-em btn-edit-halaqah"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalEditHalaqah"
                                    data-id="<?= $h['id'] ?>"
                                    data-kode="<?= htmlspecialchars($h['kode_halaqah']) ?>"
                                    data-nama="<?= htmlspecialchars($h['nama_halaqah']) ?>"
                                    data-ustadz-id="<?= htmlspecialchars($h['ustadz_id'] ?? '') ?>"
                                    data-target="<?= htmlspecialchars($h['target_juz'] ?? 'Juz 29 - 30') ?>"
                                    data-member-ids='<?= json_encode($member_ids) ?>'>
                                <i class="bi bi-pencil-square"></i> Edit
                            </button>
                            <form action="?tab=halaqah" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus kelompok halaqah <?= htmlspecialchars($h['nama_halaqah']) ?>?');">
                                <input type="hidden" name="action" value="hapus_halaqah">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
                                <input type="hidden" name="halaqah_id" value="<?= $h['id'] ?>">
                                <button type="submit" class="btn-outline-danger-sm ms-1" 
                                        <?= ($jml_siswa > 0 ? 'title="Pindahkan atau keluarkan siswa terlebih dahulu sebelum menghapus"' : 'title="Hapus Kelompok"') ?>>
                                    <i class="bi bi-trash-fill"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td class="ps-4"><strong>HLQ-01</strong></td>
                        <td><div class="fw-bold text-dark">Al-Furqan</div></td>
                        <td>Ust. Rival Alvian Melfandy</td>
                        <td>Juz 29 – 30</td>
                        <td>
                            <span class="badge-pill" style="background:var(--em-50);color:var(--em-800);border:1px solid var(--em-100);">
                                <i class="bi bi-people-fill me-1"></i>0 Siswa
                            </span>
                        </td>
                        <td><span class="badge-pill badge-aktif">Aktif</span></td>
                        <td class="text-end pe-4">
                            <span class="text-muted small">Data Demo</span>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
