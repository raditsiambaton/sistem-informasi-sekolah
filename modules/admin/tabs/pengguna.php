<?php
/**
 * modules/admin/tabs/pengguna.php
 * TAB 2: Data Pengguna Sistem
 *
 * Variabel yang digunakan (disiapkan oleh index.php via data/queries.php):
 *   $users_list
 *
 * Catatan: Modal #modalTambahPengguna dan #modalEditPengguna
 * tetap berada di index.php agar Bootstrap JS dapat menemukannya.
 */

if (!defined('ADMIN_INDEX')) {
    http_response_code(403);
    exit('403 Forbidden — Akses langsung tidak diizinkan.');
}
?>
<div class="page-heading d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
        <h1>Data Pengguna Sistem</h1>
        <p>Manajemen akun siswa, ustadz pembimbing, dan administrator tersimpan di MySQL.</p>
    </div>
    <button class="btn-em" data-bs-toggle="modal" data-bs-target="#modalTambahPengguna">
        <i class="bi bi-person-plus-fill"></i> Tambah Pengguna
    </button>
</div>

<div class="panel">
    <div class="table-responsive">
        <table class="tbl">
            <thead>
                <tr>
                    <th class="ps-4">Nama Pengguna</th>
                    <th>Username</th>
                    <th>NIP / NIS</th>
                    <th>Peran</th>
                    <th>Halaqah</th>
                    <th>Status</th>
                    <th class="text-end pe-4">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users_list as $u): ?>
                <tr>
                    <td class="ps-4">
                        <div class="fw-bold"><?= htmlspecialchars($u['nama_lengkap'] ?? $u['username']) ?></div>
                        <small class="text-muted">ID #<?= $u['id'] ?></small>
                    </td>
                    <td><code style="font-size:12.5px;"><?= htmlspecialchars($u['username']) ?></code></td>
                    <td style="font-size:12.5px;"><?= htmlspecialchars($u['nip_nis'] ?? '—') ?></td>
                    <td>
                        <?php if ($u['role'] === 'admin'): ?>
                            <span class="badge-pill badge-role-admin">Admin</span>
                        <?php elseif ($u['role'] === 'ustadz'): ?>
                            <span class="badge-pill badge-role-ustadz">Ustadz</span>
                        <?php else: ?>
                            <span class="badge-pill badge-role-siswa">Siswa</span>
                        <?php endif; ?>
                    </td>
                    <td style="font-size:12.5px;"><?= htmlspecialchars($u['halaqah'] ?? '—') ?></td>
                    <td>
                        <span class="badge-pill <?= ($u['status'] ?? 'aktif') === 'aktif' ? 'badge-aktif' : 'badge-nonaktif' ?>">
                            <?= ucfirst($u['status'] ?? 'aktif') ?>
                        </span>
                    </td>
                    <td class="text-end pe-4">
                        <button class="btn-outline-em btn-edit"
                                data-bs-toggle="modal"
                                data-bs-target="#modalEditPengguna"
                                data-id="<?= $u['id'] ?>"
                                data-nama="<?= htmlspecialchars($u['nama_lengkap'] ?? '') ?>"
                                data-username="<?= htmlspecialchars($u['username'] ?? '') ?>"
                                data-role="<?= htmlspecialchars($u['role'] ?? 'siswa') ?>"
                                data-nipnis="<?= htmlspecialchars($u['nip_nis'] ?? '') ?>"
                                data-halaqah="<?= htmlspecialchars($u['halaqah'] ?? 'Al-Furqan') ?>"
                                data-status="<?= htmlspecialchars($u['status'] ?? 'aktif') ?>">
                            <i class="bi bi-pencil-square"></i> Edit
                        </button>
                        <?php if ($u['id'] > 1): ?>
                            <a href="?tab=pengguna&hapus_pengguna=<?= $u['id'] ?>"
                               class="btn-outline-danger-sm ms-1"
                               onclick="return confirm('Hapus pengguna <?= htmlspecialchars($u['nama_lengkap']) ?>?');">
                                <i class="bi bi-trash-fill"></i>
                            </a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
