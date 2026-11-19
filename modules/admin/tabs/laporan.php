<?php
/**
 * modules/admin/tabs/laporan.php
 * TAB 4: Laporan Rekapitulasi Tahfiz
 *
 * Variabel yang digunakan (disiapkan oleh index.php via data/queries.php):
 *   $users_list, $total_santri, $bulan_id
 */

if (!defined('ADMIN_INDEX')) {
    http_response_code(403);
    exit('403 Forbidden — Akses langsung tidak diizinkan.');
}
?>
<div class="page-heading d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
        <h1>Laporan Rekapitulasi Tahfiz</h1>
        <p>Cetak rekapitulasi capaian hafalan dan lembar mutabaah siswa.</p>
    </div>
    <button class="btn-em" onclick="window.print()">
        <i class="bi bi-printer-fill"></i> Cetak / Simpan PDF
    </button>
</div>

<div class="panel">
    <div class="panel-header">
        <h2 class="panel-title">Rekap Capaian Siswa — <?= $bulan_id[(int)date('n')] . ' ' . date('Y') ?></h2>
        <span class="badge-pill" style="background:var(--em-50);color:var(--em-700);font-size:11.5px;">
            <?= $total_santri ?> Siswa Terdaftar
        </span>
    </div>
    <div class="table-responsive">
        <table class="tbl">
            <thead>
                <tr>
                    <th class="ps-4">No</th>
                    <th>Nama Siswa</th>
                    <th>NIS</th>
                    <th>Halaqah</th>
                    <th>Capaian Target</th>
                    <th>Status Evaluasi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $no = 1;
                foreach ($users_list as $u):
                    if ($u['role'] === 'siswa'):
                ?>
                <tr>
                    <td class="ps-4"><?= $no++ ?></td>
                    <td><strong><?= htmlspecialchars($u['nama_lengkap']) ?></strong></td>
                    <td><code style="font-size:12.5px;"><?= htmlspecialchars($u['nip_nis'] ?? '—') ?></code></td>
                    <td><?= htmlspecialchars($u['halaqah'] ?? '—') ?></td>
                    <td><strong style="color:var(--em-600);">80%</strong></td>
                    <td><span class="badge-pill badge-aktif">Sesuai Target</span></td>
                </tr>
                <?php
                    endif;
                endforeach;
                if ($total_santri === 0):
                ?>
                <tr>
                    <td class="ps-4" colspan="6" style="text-align:center;color:var(--slate-5);padding:32px;">
                        <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                        Belum ada data siswa. Tambahkan pengguna dengan peran <strong>Siswa</strong> terlebih dahulu.
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
