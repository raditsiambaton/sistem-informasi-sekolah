<?php
/**
 * tabs/target.php
 * Tampilan tab Target Hafalan Santri + JavaScript logika target.
 * Variabel yang dipakai: $santri_list, $target_aktif_map,
 *                        $target_fokus_id, $target_old, $kurikulum_target,
 *                        $urutan_opsi_target
 */
?>
<!-- Hero Banner Atas -->
<div class="mb-4">
    <span class="badge px-3 py-1 text-white mb-2 fw-bold" style="background-color: #11756D;">PENETAPAN TARGET</span>
    <h2 class="fw-bold mb-1" style="color: #11756D;">Target Hafalan Santri</h2>
    <p class="text-muted small mb-0">Tetapkan sasaran juz atau surah dan rentang tanggal target bagi santri halaqah binaan Anda.</p>
</div>

<div class="row g-4 align-items-start">
    <!-- PANEL KIRI: DAFTAR PILIH SISWA -->
    <div class="col-12 col-lg-4">
        <div class="target-panel p-3 bg-white">
            <h6 class="fw-bold text-dark mb-3 d-flex align-items-center gap-2">
                <i class="bi bi-person-check-fill text-success"></i>
                <span>Pilih siswa</span>
            </h6>
            <div class="mb-3 position-relative">
                <i class="bi bi-search position-absolute text-muted" style="top: 13px; left: 14px; font-size: 14px;"></i>
                <input type="text" id="cariSantriTarget" class="form-control ps-5"
                    placeholder="Cari nama atau NIS"
                    style="height: 42px; border-radius: 10px; background-color: #F1F5F9; border: 1px solid #E2E8F0; font-size: 13.5px;">
            </div>

            <div class="d-flex flex-column gap-2 overflow-y-auto" style="max-height: 540px;" id="listSantriTarget">
                <?php foreach ($santri_list as $s):
                    $s_id_str  = (string)$s['id'];
                    $has_target = isset($target_aktif_map[$s_id_str]);
                    $is_fokus   = ($target_fokus_id === (int)$s['id']);
                ?>
                    <div class="target-siswa-card <?= $is_fokus ? 'aktif' : '' ?>"
                         data-id="<?= (int)$s['id'] ?>"
                         data-nama="<?= htmlspecialchars($s['nama_lengkap'], ENT_QUOTES) ?>"
                         data-nis="<?= htmlspecialchars($s['nip_nis'], ENT_QUOTES) ?>"
                         onclick="pilihSantriTarget(<?= (int)$s['id'] ?>, '<?= htmlspecialchars($s['nama_lengkap'], ENT_QUOTES) ?>', '<?= htmlspecialchars($s['nip_nis'], ENT_QUOTES) ?>', this)">
                        <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                             style="width: 44px; height: 44px; background: #E2E8F0; color: #64748B;">
                            <i class="bi bi-person-fill fs-5"></i>
                        </div>
                        <div class="flex-grow-1 overflow-hidden">
                            <div class="fw-bold text-dark text-truncate santri-target-nama" style="font-size: 14px;"><?= htmlspecialchars($s['nama_lengkap']) ?></div>
                            <div class="small text-muted text-truncate santri-target-nis" style="font-size: 12px;">NIS: <?= htmlspecialchars($s['nip_nis']) ?></div>
                        </div>
                        <?php if ($has_target): ?>
                            <span class="badge rounded-pill bg-success-subtle text-success border border-success-subtle px-2 py-1" style="font-size: 10.5px;">
                                Aktif
                            </span>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- PANEL KANAN: FORM TAMBAH TARGET + INFO TARGET AKTIF -->
    <div class="col-12 col-lg-8">
        <div class="target-panel p-4 bg-white">
            <!-- Empty State Sebelum Siswa Dipilih -->
            <div id="targetEmptyState" class="text-center py-5 my-auto"
                 style="<?= $target_fokus_id > 0 ? 'display: none;' : '' ?>">
                <div class="rounded-circle d-inline-flex align-items-center justify-content-center p-3 mb-3"
                     style="width: 64px; height: 64px; background: #E6F4F1; color: #11756D;">
                    <i class="bi bi-person-lines-fill fs-2"></i>
                </div>
                <h6 class="fw-bold text-dark mb-1">Belum Ada Siswa Dipilih</h6>
                <p class="text-muted small mb-0" style="max-width: 360px; margin: 0 auto;">
                    Pilih siswa terlebih dahulu di panel kiri untuk menetapkan target hafalan.
                </p>
            </div>

            <!-- Form dan Detail Setelah Siswa Dipilih -->
            <div id="targetFormArea" style="<?= $target_fokus_id > 0 ? '' : 'display: none;' ?>">
                <!-- Header Form -->
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 pb-3 mb-4"
                     style="border-bottom: 1.5px solid #E2E8F0;">
                    <h4 class="fw-bold mb-0 text-dark" style="font-size: 1.35rem;">Tambah Target Hafalan</h4>
                    <div class="badge rounded-pill d-inline-flex align-items-center px-3 py-2 fw-semibold"
                         style="background-color: #E6F4F1; color: #11756D; font-size: 12.5px; gap: 6px;">
                        <i class="bi bi-person-check-fill"></i>
                        <span id="targetBadgeNama">-</span>
                        <span class="opacity-75">•</span>
                        <span id="targetBadgeNis">NIS: -</span>
                    </div>
                </div>

                <!-- Form Grid 2 Kolom -->
                <form action="?tab=target" method="POST" id="formTetapkanTarget"
                      onsubmit="return validasiFormTarget(event)">
                    <input type="hidden" name="action" value="simpan_target">
                    <input type="hidden" name="siswa_id" id="targetInputSiswaId"
                           value="<?= $target_fokus_id > 0 ? $target_fokus_id : '' ?>">

                    <div class="row g-3 mb-4">
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold text-secondary mb-1">
                                Mulai Hafalan (Juz / Surah) <span class="text-danger">*</span>
                            </label>
                            <select name="surah_awal" id="targetSurahAwal"
                                    class="form-select target-field target-select"
                                    required onchange="onSurahAwalChange()">
                                <option value="" disabled <?= empty($target_old['surah_awal']) ? 'selected' : '' ?>>
                                    -- Pilih Batas Awal --
                                </option>
                                <?php foreach ($kurikulum_target as $juz_nama => $daftar_opsi): ?>
                                    <optgroup label="<?= htmlspecialchars($juz_nama) ?>">
                                        <?php foreach ($daftar_opsi as $opsi_teks): ?>
                                            <option value="<?= htmlspecialchars($opsi_teks) ?>"
                                                <?= ($target_old['surah_awal'] === $opsi_teks) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($opsi_teks) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </optgroup>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold text-secondary mb-1">
                                Tanggal Mulai <span class="text-danger">*</span>
                            </label>
                            <input type="date" name="tanggal_mulai" id="targetTanggalMulai"
                                   class="form-control target-field"
                                   value="<?= htmlspecialchars($target_old['tanggal_mulai'] ?: date('Y-m-d')) ?>"
                                   required>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold text-secondary mb-1">
                                Batas Akhir Hafalan (Juz / Surah) <span class="text-danger">*</span>
                            </label>
                            <select name="surah_akhir" id="targetSurahAkhir"
                                    class="form-select target-field target-select" required>
                                <option value="" disabled <?= empty($target_old['surah_akhir']) ? 'selected' : '' ?>>
                                    -- Pilih Batas Akhir --
                                </option>
                                <?php foreach ($kurikulum_target as $juz_nama => $daftar_opsi): ?>
                                    <optgroup label="<?= htmlspecialchars($juz_nama) ?>">
                                        <?php foreach ($daftar_opsi as $opsi_teks): ?>
                                            <option value="<?= htmlspecialchars($opsi_teks) ?>"
                                                <?= ($target_old['surah_akhir'] === $opsi_teks) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($opsi_teks) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </optgroup>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold text-secondary mb-1">
                                Tanggal Akhir <span class="text-danger">*</span>
                            </label>
                            <input type="date" name="tanggal_akhir" id="targetTanggalAkhir"
                                   class="form-control target-field"
                                   value="<?= htmlspecialchars($target_old['tanggal_akhir'] ?: date('Y-m-d', strtotime('+30 days'))) ?>"
                                   required>
                        </div>
                    </div>

                    <div class="d-flex align-items-center gap-2 mb-4">
                        <button type="submit" class="btn-target-simpan">
                            <i class="bi bi-floppy"></i>
                            <span>Tambah Target</span>
                        </button>
                        <button type="button" class="btn-target-batal" onclick="batalPilihTarget()">
                            <i class="bi bi-x-circle"></i>
                            <span>Batal</span>
                        </button>
                    </div>
                </form>

                <!-- Kotak Target Aktif Santri Terpilih -->
                <div id="targetAktifSantriBox" class="mt-4 pt-3"
                     style="border-top: 1px dashed #CBD5E1; display: none;">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="small fw-bold text-uppercase text-muted" style="letter-spacing: 0.05em; font-size: 11.5px;">
                            <i class="bi bi-info-circle me-1 text-primary"></i>Target Aktif Saat Ini
                        </span>
                        <span class="badge bg-success rounded-pill px-2 py-1" style="font-size: 11px;">Aktif</span>
                    </div>
                    <div class="p-3 rounded-3" style="background-color: #F8FAFC; border: 1px solid #E2E8F0;">
                        <div class="d-flex flex-column gap-1">
                            <div class="text-dark fw-semibold" style="font-size: 13.5px;">
                                <span id="infoTargetSurahAwal" class="text-primary">-</span>
                                <span class="text-muted mx-1">s/d</span>
                                <span id="infoTargetSurahAkhir" class="text-primary">-</span>
                            </div>
                            <div class="small text-muted" style="font-size: 12px;">
                                <i class="bi bi-calendar-event me-1"></i>
                                <span id="infoTargetRentangTanggal">-</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// ─────────────────────────────────────────────────────────────
// LOGIKA JAVASCRIPT TAB TARGET HAFALAN
// ─────────────────────────────────────────────────────────────
const targetAktifMap    = <?= json_encode($target_aktif_map, JSON_UNESCAPED_UNICODE) ?>;
const targetUrutanOpsi  = <?= json_encode($urutan_opsi_target, JSON_UNESCAPED_UNICODE) ?>;
let santriTargetTerpilih = null;

function pilihSantriTarget(id, nama, nis, el) {
    santriTargetTerpilih = { id, nama, nis };
    document.querySelectorAll('.target-siswa-card').forEach(card => card.classList.remove('aktif'));
    if (el) el.classList.add('aktif');

    const emptyState = document.getElementById('targetEmptyState');
    const formArea   = document.getElementById('targetFormArea');
    if (emptyState) emptyState.style.display = 'none';
    if (formArea)   formArea.style.display   = 'block';

    const inputSiswaId = document.getElementById('targetInputSiswaId');
    if (inputSiswaId) inputSiswaId.value = id;

    const badgeNama = document.getElementById('targetBadgeNama');
    const badgeNis  = document.getElementById('targetBadgeNis');
    if (badgeNama) badgeNama.textContent = nama;
    if (badgeNis)  badgeNis.textContent  = 'NIS: ' + nis;

    const targetBox   = document.getElementById('targetAktifSantriBox');
    const infoAwal    = document.getElementById('infoTargetSurahAwal');
    const infoAkhir   = document.getElementById('infoTargetSurahAkhir');
    const infoTanggal = document.getElementById('infoTargetRentangTanggal');

    const keyId = String(id);
    if (targetAktifMap && targetAktifMap[keyId]) {
        const item = targetAktifMap[keyId];
        if (infoAwal)    infoAwal.textContent    = item.surah_awal   || '-';
        if (infoAkhir)   infoAkhir.textContent   = item.surah_akhir  || '-';
        if (infoTanggal) infoTanggal.textContent = (item.tanggal_mulai || '') + ' s/d ' + (item.tanggal_akhir || '');
        if (targetBox)   targetBox.style.display  = 'block';
    } else {
        if (targetBox) targetBox.style.display = 'none';
    }
}

function batalPilihTarget() {
    santriTargetTerpilih = null;
    document.querySelectorAll('.target-siswa-card').forEach(card => card.classList.remove('aktif'));

    const emptyState = document.getElementById('targetEmptyState');
    const formArea   = document.getElementById('targetFormArea');
    if (emptyState) emptyState.style.display = 'block';
    if (formArea)   formArea.style.display   = 'none';

    const form = document.getElementById('formTetapkanTarget');
    if (form) form.reset();

    const inputSiswaId = document.getElementById('targetInputSiswaId');
    if (inputSiswaId) inputSiswaId.value = '';

    const targetBox = document.getElementById('targetAktifSantriBox');
    if (targetBox) targetBox.style.display = 'none';
}

function onSurahAwalChange() {
    const selAwal  = document.getElementById('targetSurahAwal');
    const selAkhir = document.getElementById('targetSurahAkhir');
    if (!selAwal || !selAkhir) return;
    const idxAwal = targetUrutanOpsi.indexOf(selAwal.value);
    if (idxAwal !== -1) {
        const idxAkhir = targetUrutanOpsi.indexOf(selAkhir.value);
        if (idxAkhir === -1 || idxAkhir < idxAwal) {
            selAkhir.value = selAwal.value;
        }
    }
}

function validasiFormTarget(e) {
    const inputSiswaId = document.getElementById('targetInputSiswaId');
    if (!inputSiswaId || !inputSiswaId.value) {
        alert('Silakan pilih siswa terlebih dahulu di panel kiri.');
        if (e) e.preventDefault();
        return false;
    }
    const selAwal  = document.getElementById('targetSurahAwal');
    const selAkhir = document.getElementById('targetSurahAkhir');
    const tglMulai = document.getElementById('targetTanggalMulai');
    const tglAkhir = document.getElementById('targetTanggalAkhir');

    if (!selAwal?.value)  { alert('Silakan pilih batas awal hafalan.');  selAwal?.focus();  if (e) e.preventDefault(); return false; }
    if (!selAkhir?.value) { alert('Silakan pilih batas akhir hafalan.'); selAkhir?.focus(); if (e) e.preventDefault(); return false; }

    const idxAwal  = targetUrutanOpsi.indexOf(selAwal.value);
    const idxAkhir = targetUrutanOpsi.indexOf(selAkhir.value);

    if (idxAwal === -1 || idxAkhir === -1) {
        alert('Pilihan juz atau surah tidak valid.');
        if (e) e.preventDefault();
        return false;
    }
    if (idxAkhir < idxAwal) {
        alert('Batas akhir hafalan tidak boleh lebih kecil dari mulai hafalan.');
        selAkhir.focus();
        if (e) e.preventDefault();
        return false;
    }
    if (tglMulai && tglAkhir && tglMulai.value && tglAkhir.value && tglAkhir.value < tglMulai.value) {
        alert('Tanggal akhir tidak boleh lebih awal dari tanggal mulai.');
        tglAkhir.focus();
        if (e) e.preventDefault();
        return false;
    }
    return true;
}

document.getElementById('cariSantriTarget')?.addEventListener('input', function () {
    const val = this.value.trim().toLowerCase();
    document.querySelectorAll('.target-siswa-card').forEach(card => {
        const nama = (card.getAttribute('data-nama') || '').toLowerCase();
        const nis  = (card.getAttribute('data-nis')  || '').toLowerCase();
        card.style.display = (nama.includes(val) || nis.includes(val)) ? 'flex' : 'none';
    });
});

window.addEventListener('DOMContentLoaded', () => {
    const cardAktif = document.querySelector('.target-siswa-card.aktif');
    if (cardAktif) {
        const id   = parseInt(cardAktif.getAttribute('data-id'), 10);
        const nama = cardAktif.getAttribute('data-nama');
        const nis  = cardAktif.getAttribute('data-nis');
        pilihSantriTarget(id, nama, nis, cardAktif);
    }
});
</script>
