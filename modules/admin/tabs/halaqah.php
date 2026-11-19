<?php
/**
 * modules/admin/tabs/halaqah.php
 * TAB 3: Kelompok Halaqah Belajar
 *
 * Variabel yang digunakan (disiapkan oleh index.php via data/queries.php):
 *   $halaqah_list
 *
 * Catatan: Modal #modalTambahHalaqah dan #modalEditHalaqah beserta
 * JavaScript-nya telah dipindahkan ke bagian bawah file ini.
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

<!-- ════════════════════════════════════════════════════════════
     MODAL: TAMBAH HALAQAH
════════════════════════════════════════════════════════════ -->
<div class="modal fade" id="modalTambahHalaqah" tabindex="-1"
     aria-labelledby="lblTambahHalaqah" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="?tab=halaqah" method="POST">
                <input type="hidden" name="action" value="tambah_halaqah">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
                <div class="modal-header-em">
                    <h5 class="modal-title" id="lblTambahHalaqah">
                        <i class="bi bi-diagram-3-fill me-2"></i>Tambah Kelompok Halaqah
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label-em">Kode Halaqah</label>
                        <input type="text" name="kode_halaqah" class="form-control"
                            placeholder="Contoh: HLQ-04" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label-em">Nama Halaqah</label>
                        <input type="text" name="nama_halaqah" class="form-control"
                            placeholder="Contoh: An-Nas" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label-em">Ustadz Pembimbing</label>
                        <select name="ustadz_id" class="form-select" required>
                            <option value="">-- Pilih Ustadz Pembimbing --</option>
                            <?php foreach ($ustadz_list as $u): ?>
                                <option value="<?= $u['id'] ?>">
                                    <?= htmlspecialchars($u['nama_lengkap']) ?>
                                    (NIP: <?= htmlspecialchars($u['nip_nis'] ?? '—') ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label-em">Target Hafalan</label>
                        <input type="text" name="target_juz" class="form-control" value="Juz 29 - 30">
                    </div>
                    <div class="mb-2">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="form-label-em mb-0">Anggota Siswa</label>
                            <span class="badge bg-success-subtle text-success border counter-tambah-siswa"
                                style="font-size:11px;">0 siswa dipilih</span>
                        </div>
                        <div class="input-group input-group-sm mb-2">
                            <span class="input-group-text bg-white border-end-0">
                                <i class="bi bi-search text-muted"></i>
                            </span>
                            <input type="text" class="form-control border-start-0 search-siswa-tambah"
                                placeholder="Cari nama atau NIS siswa...">
                        </div>
                        <div class="border rounded-3 p-2 list-siswa-tambah-container"
                            style="max-height:200px; overflow-y:auto; background:var(--slate-1);">
                            <?php if (!empty($siswa_list)): ?>
                                <?php foreach ($siswa_list as $s): ?>
                                    <label class="d-flex align-items-center justify-content-between p-2 rounded-2 mb-1 bg-white border item-siswa-tambah"
                                        data-nama="<?= strtolower(htmlspecialchars($s['nama_lengkap'])) ?>"
                                        data-nis="<?= strtolower(htmlspecialchars($s['nip_nis'] ?? '')) ?>"
                                        style="cursor:pointer;">
                                        <div class="d-flex align-items-center gap-2">
                                            <input type="checkbox" name="siswa_ids[]" value="<?= $s['id'] ?>"
                                                class="form-check-input check-siswa-tambah mt-0">
                                            <div>
                                                <div class="fw-semibold text-dark" style="font-size:13px;">
                                                    <?= htmlspecialchars($s['nama_lengkap']) ?></div>
                                                <small class="text-muted" style="font-size:11.5px;">
                                                    NIS: <?= htmlspecialchars($s['nip_nis'] ?? '—') ?></small>
                                            </div>
                                        </div>
                                        <?php if (!empty($s['halaqah']) && $s['halaqah'] !== '—'): ?>
                                            <span class="badge bg-light text-muted border" style="font-size:11px;">
                                                Halaqah: <?= htmlspecialchars($s['halaqah']) ?></span>
                                        <?php else: ?>
                                            <span class="badge bg-warning-subtle text-warning border" style="font-size:11px;">
                                                Belum Ada Halaqah</span>
                                        <?php endif; ?>
                                    </label>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="text-center py-3 text-muted small">Tidak ada data siswa aktif.</div>
                            <?php endif; ?>
                        </div>
                        <small class="text-muted d-block mt-1" style="font-size:11px;">
                            * Siswa yang dicentang akan dimasukkan ke kelompok ini.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light btn-sm fw-semibold"
                        data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn-em btn-em-sm">
                        <i class="bi bi-save-fill"></i> Simpan Halaqah
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ════════════════════════════════════════════════════════════
     MODAL: EDIT HALAQAH
════════════════════════════════════════════════════════════ -->
<div class="modal fade" id="modalEditHalaqah" tabindex="-1"
     aria-labelledby="lblEditHalaqah" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="?tab=halaqah" method="POST">
                <input type="hidden" name="action" value="edit_halaqah">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
                <input type="hidden" name="halaqah_id" id="edit_halaqah_id">
                <div class="modal-header-em">
                    <h5 class="modal-title" id="lblEditHalaqah">
                        <i class="bi bi-pencil-square me-2"></i>Edit Kelompok Halaqah
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label-em">Kode Halaqah</label>
                        <input type="text" name="kode_halaqah" id="edit_kode_halaqah"
                            class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label-em">Nama Halaqah</label>
                        <input type="text" name="nama_halaqah" id="edit_nama_halaqah"
                            class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label-em">Ustadz Pembimbing</label>
                        <select name="ustadz_id" id="edit_ustadz_id" class="form-select" required>
                            <option value="">-- Pilih Ustadz Pembimbing --</option>
                            <?php foreach ($ustadz_list as $u): ?>
                                <option value="<?= $u['id'] ?>">
                                    <?= htmlspecialchars($u['nama_lengkap']) ?>
                                    (NIP: <?= htmlspecialchars($u['nip_nis'] ?? '—') ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label-em">Target Hafalan</label>
                        <input type="text" name="target_juz" id="edit_target_juz"
                            class="form-control" value="Juz 29 - 30">
                    </div>
                    <div class="mb-2">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="form-label-em mb-0">Anggota Siswa</label>
                            <span class="badge bg-success-subtle text-success border counter-edit-siswa"
                                style="font-size:11px;">0 siswa dipilih</span>
                        </div>
                        <div class="input-group input-group-sm mb-2">
                            <span class="input-group-text bg-white border-end-0">
                                <i class="bi bi-search text-muted"></i>
                            </span>
                            <input type="text" class="form-control border-start-0 search-siswa-edit"
                                placeholder="Cari nama atau NIS siswa...">
                        </div>
                        <div class="border rounded-3 p-2 list-siswa-edit-container"
                            style="max-height:200px; overflow-y:auto; background:var(--slate-1);">
                            <?php if (!empty($siswa_list)): ?>
                                <?php foreach ($siswa_list as $s): ?>
                                    <label class="d-flex align-items-center justify-content-between p-2 rounded-2 mb-1 bg-white border item-siswa-edit"
                                        data-nama="<?= strtolower(htmlspecialchars($s['nama_lengkap'])) ?>"
                                        data-nis="<?= strtolower(htmlspecialchars($s['nip_nis'] ?? '')) ?>"
                                        style="cursor:pointer;">
                                        <div class="d-flex align-items-center gap-2">
                                            <input type="checkbox" name="siswa_ids[]" value="<?= $s['id'] ?>"
                                                class="form-check-input check-siswa-edit mt-0">
                                            <div>
                                                <div class="fw-semibold text-dark" style="font-size:13px;">
                                                    <?= htmlspecialchars($s['nama_lengkap']) ?></div>
                                                <small class="text-muted" style="font-size:11.5px;">
                                                    NIS: <?= htmlspecialchars($s['nip_nis'] ?? '—') ?></small>
                                            </div>
                                        </div>
                                        <?php if (!empty($s['halaqah']) && $s['halaqah'] !== '—'): ?>
                                            <span class="badge bg-light text-muted border" style="font-size:11px;">
                                                Halaqah: <?= htmlspecialchars($s['halaqah']) ?></span>
                                        <?php else: ?>
                                            <span class="badge bg-warning-subtle text-warning border" style="font-size:11px;">
                                                Belum Ada Halaqah</span>
                                        <?php endif; ?>
                                    </label>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="text-center py-3 text-muted small">Tidak ada data siswa aktif.</div>
                            <?php endif; ?>
                        </div>
                        <small class="text-muted d-block mt-1" style="font-size:11px;">
                            * Centang untuk menambahkan, hilangkan centang untuk mengeluarkan siswa dari kelompok ini.</small>
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
// ── Counter & Search Halaqah — Modal Tambah ───────────────────
function updateCounterTambahHalaqah() {
    var total = document.querySelectorAll('.check-siswa-tambah:checked').length;
    var badge = document.querySelector('.counter-tambah-siswa');
    if (badge) badge.textContent = total + ' siswa dipilih';
}

function updateCounterEditHalaqah() {
    var total = document.querySelectorAll('.check-siswa-edit:checked').length;
    var badge = document.querySelector('.counter-edit-siswa');
    if (badge) badge.textContent = total + ' siswa dipilih';
}

document.querySelectorAll('.check-siswa-tambah').forEach(function(cb) {
    cb.addEventListener('change', updateCounterTambahHalaqah);
});

document.querySelectorAll('.check-siswa-edit').forEach(function(cb) {
    cb.addEventListener('change', updateCounterEditHalaqah);
});

// Filter pencarian siswa di modal tambah
var searchSiswaTambah = document.querySelector('.search-siswa-tambah');
if (searchSiswaTambah) {
    searchSiswaTambah.addEventListener('input', function() {
        var q = this.value.toLowerCase().trim();
        document.querySelectorAll('.item-siswa-tambah').forEach(function(item) {
            var nama = item.dataset.nama || '';
            var nis  = item.dataset.nis  || '';
            item.style.display = (nama.includes(q) || nis.includes(q)) ? 'flex' : 'none';
        });
    });
}

// Filter pencarian siswa di modal edit
var searchSiswaEdit = document.querySelector('.search-siswa-edit');
if (searchSiswaEdit) {
    searchSiswaEdit.addEventListener('input', function() {
        var q = this.value.toLowerCase().trim();
        document.querySelectorAll('.item-siswa-edit').forEach(function(item) {
            var nama = item.dataset.nama || '';
            var nis  = item.dataset.nis  || '';
            item.style.display = (nama.includes(q) || nis.includes(q)) ? 'flex' : 'none';
        });
    });
}

// Modal Edit Halaqah: populasikan data dan centang ID siswa
document.querySelectorAll('.btn-edit-halaqah').forEach(function(btn) {
    btn.addEventListener('click', function() {
        document.getElementById('edit_halaqah_id').value    = this.dataset.id;
        document.getElementById('edit_kode_halaqah').value  = this.dataset.kode;
        document.getElementById('edit_nama_halaqah').value  = this.dataset.nama;
        document.getElementById('edit_ustadz_id').value     = this.dataset.ustadzId;
        document.getElementById('edit_target_juz').value    = this.dataset.target;

        var memberIds = [];
        try { memberIds = JSON.parse(this.dataset.memberIds || '[]'); } catch(e) {}

        document.querySelectorAll('.check-siswa-edit').forEach(function(cb) {
            cb.checked = memberIds.includes(parseInt(cb.value, 10));
        });

        if (searchSiswaEdit) {
            searchSiswaEdit.value = '';
            document.querySelectorAll('.item-siswa-edit').forEach(function(item) {
                item.style.display = 'flex';
            });
        }

        updateCounterEditHalaqah();
    });
});
</script>
