<?php
/**
 * modules/admin/tabs/pengguna.php
 * TAB 2: Data Pengguna Sistem
 *
 * Variabel yang digunakan (disiapkan oleh index.php via data/queries.php):
 *   $users_list
 *
 * Catatan: Modal #modalTambahPengguna dan #modalEditPengguna
 * telah dipindahkan ke bagian bawah file ini.
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

<!-- ════════════════════════════════════════════════════════════
     MODAL: TAMBAH PENGGUNA BARU
════════════════════════════════════════════════════════════ -->
<div class="modal fade" id="modalTambahPengguna" tabindex="-1"
     aria-labelledby="lblTambahPengguna" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="?tab=pengguna" method="POST">
                <input type="hidden" name="action" value="tambah_pengguna">
                <div class="modal-header-em">
                    <h5 class="modal-title" id="lblTambahPengguna">
                        <i class="bi bi-person-plus-fill me-2"></i>Tambah Pengguna Baru
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label-em">Nama Lengkap</label>
                        <input type="text" name="nama_lengkap" class="form-control"
                            placeholder="Contoh: Muhammad Ilham" required>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label-em">Username</label>
                            <input type="text" name="username" class="form-control" placeholder="ilham123" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label-em">Kata Sandi</label>
                            <input type="password" name="password" class="form-control"
                                placeholder="••••••••" required>
                        </div>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label-em">Peran / Role</label>
                            <select name="role" class="form-select">
                                <option value="siswa">Siswa / Siswa</option>
                                <option value="ustadz">Ustadz / Guru</option>
                                <option value="admin">Administrator</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label-em">NIS / NIP</label>
                            <input type="text" name="nip_nis" class="form-control" placeholder="102604">
                        </div>
                    </div>
                    <div class="mb-1">
                        <label class="form-label-em">Kelompok Halaqah</label>
                        <select name="halaqah" class="form-select">
                            <option value="Al-Furqan">Al-Furqan (Ust. Rival)</option>
                            <option value="Al-Ikhlas">Al-Ikhlas (Ust. Ahmad)</option>
                            <option value="Al-Falaq">Al-Falaq (Ust. Ridwan)</option>
                            <option value="An-Nas">An-Nas (Ust. Zulkifli)</option>
                            <option value="Koordinator Pusat">Koordinator Pusat (Admin)</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light btn-sm fw-semibold"
                        data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn-em btn-em-sm">
                        <i class="bi bi-save-fill"></i> Simpan Pengguna
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ════════════════════════════════════════════════════════════
     MODAL: EDIT PENGGUNA
════════════════════════════════════════════════════════════ -->
<div class="modal fade" id="modalEditPengguna" tabindex="-1"
     aria-labelledby="lblEditPengguna" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="?tab=pengguna" method="POST">
                <input type="hidden" name="action" value="edit_pengguna">
                <input type="hidden" name="user_id" id="edit_user_id">
                <div class="modal-header-em">
                    <h5 class="modal-title" id="lblEditPengguna">
                        <i class="bi bi-pencil-square me-2"></i>Edit Data Pengguna
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label-em">Nama Lengkap</label>
                        <input type="text" name="nama_lengkap" id="edit_nama_lengkap"
                            class="form-control" required>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label-em">Username</label>
                            <input type="text" name="username" id="edit_username"
                                class="form-control" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label-em">Ganti Sandi (Opsional)</label>
                            <input type="password" name="password" class="form-control"
                                placeholder="Kosongkan jika tidak ubah">
                        </div>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label-em">Peran / Role</label>
                            <select name="role" id="edit_role" class="form-select">
                                <option value="siswa">Siswa / Siswa</option>
                                <option value="ustadz">Ustadz / Guru</option>
                                <option value="admin">Administrator</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label-em">NIS / NIP</label>
                            <input type="text" name="nip_nis" id="edit_nip_nis" class="form-control">
                        </div>
                    </div>
                    <div class="row g-2 mb-1">
                        <div class="col-6">
                            <label class="form-label-em">Kelompok Halaqah</label>
                            <select name="halaqah" id="edit_halaqah" class="form-select">
                                <option value="Al-Furqan">Al-Furqan</option>
                                <option value="Al-Ikhlas">Al-Ikhlas</option>
                                <option value="Al-Falaq">Al-Falaq</option>
                                <option value="An-Nas">An-Nas</option>
                                <option value="Koordinator Pusat">Koordinator Pusat</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label-em">Status</label>
                            <select name="status" id="edit_status" class="form-select">
                                <option value="aktif">Aktif</option>
                                <option value="nonaktif">Nonaktif</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light btn-sm fw-semibold"
                        data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn-em btn-em-sm">
                        <i class="bi bi-check2-circle"></i> Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// ── Modal Edit Pengguna: isi data secara dinamis ──────────────
document.querySelectorAll('.btn-edit').forEach(function(btn) {
    btn.addEventListener('click', function() {
        document.getElementById('edit_user_id').value    = this.dataset.id;
        document.getElementById('edit_nama_lengkap').value = this.dataset.nama;
        document.getElementById('edit_username').value   = this.dataset.username;
        document.getElementById('edit_role').value       = this.dataset.role;
        document.getElementById('edit_nip_nis').value    = this.dataset.nipnis;
        document.getElementById('edit_halaqah').value    = this.dataset.halaqah;
        document.getElementById('edit_status').value     = this.dataset.status;
    });
});
</script>
