<?php
/**
 * modules/siswa/tabs/riwayat.php
 * Tab "Riwayat Hafalan" — Dashboard Siswa
 * Mencakup: tabel Daftar Setoran & Murajaah, panel Masukan Ustadz,
 *           modal #modalDetailRiwayatSiswa, dan script JS modal.
 * Guard: hanya boleh di-require dari modules/siswa/index.php
 */

if (!defined('SISWA_INDEX')) exit;
?>

<div class="mb-4">
    <span class="badge text-white fw-bold px-3 py-1 mb-2 d-inline-block" style="background-color:#11756D; border-radius:20px; font-size:11px; letter-spacing:0.05em;">PERJALANAN HAFALAN</span>
    <h2 class="fw-bold mb-1" style="color: #11756D;">Riwayat Hafalan Siswa</h2>
    <p class="text-muted small mb-0">Tinjau seluruh riwayat setoran, murajaah, dan evaluasi tajwid dari ustadz pembimbing.</p>
</div>

<div class="row g-4">
    <!-- Tabel Daftar Setoran & Murajaah -->
    <div class="col-12 col-lg-7">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h6 class="fw-bold mb-0" style="color:#11756D;">Daftar Setoran &amp; Murajaah</h6>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 small">
                    <thead class="table-light">
                        <tr>
                            <th>Tanggal</th>
                            <th>Surah / Ayat</th>
                            <th>Kualitas Hafalan</th>
                            <th>Kategori</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Gabungkan setoran & murajaah menjadi satu daftar riwayat
                        $riwayat_gabungan = [];

                        foreach ($siswa_setoran as $set) {
                            $kl = resolveKualitas(
                                (string)($set['kualitas'] ?? ''),
                                isset($set['nilai']) ? (int)$set['nilai'] : null
                            );
                            $riwayat_gabungan[] = [
                                'tanggal'    => !empty($set['tanggal_setor'])
                                    ? date('d M Y', strtotime($set['tanggal_setor']))
                                    : date('d M Y'),
                                'surah_ayat' => htmlspecialchars($set['surah'], ENT_QUOTES, 'UTF-8')
                                    . ' · Ayat '
                                    . htmlspecialchars($set['ayat_awal'],  ENT_QUOTES, 'UTF-8')
                                    . '–'
                                    . htmlspecialchars($set['ayat_akhir'], ENT_QUOTES, 'UTF-8'),
                                'kualitas'   => $kl,
                                'kategori'   => 'Setoran',
                                'ustadz'     => htmlspecialchars($set['nama_ustadz'] ?? '', ENT_QUOTES, 'UTF-8'),
                                'catatan'    => htmlspecialchars($set['catatan']     ?? '', ENT_QUOTES, 'UTF-8'),
                            ];
                        }

                        foreach ($siswa_murajaah as $mur) {
                            $kl = resolveKualitas(
                                (string)($mur['status_mutqin'] ?? ''),
                                null
                            );
                            $riwayat_gabungan[] = [
                                'tanggal'    => !empty($mur['tanggal_murajaah'])
                                    ? date('d M Y', strtotime($mur['tanggal_murajaah']))
                                    : date('d M Y'),
                                'surah_ayat' => htmlspecialchars($mur['surah'],     ENT_QUOTES, 'UTF-8')
                                    . ' · '
                                    . htmlspecialchars($mur['ayat_range'] ?? '', ENT_QUOTES, 'UTF-8'),
                                'kualitas'   => $kl,
                                'kategori'   => 'Murajaah',
                                'ustadz'     => htmlspecialchars($mur['nama_ustadz'] ?? '', ENT_QUOTES, 'UTF-8'),
                                'catatan'    => htmlspecialchars($mur['catatan']     ?? '', ENT_QUOTES, 'UTF-8'),
                            ];
                        }

                        if (!empty($riwayat_gabungan)):
                            foreach ($riwayat_gabungan as $rv):
                                $kls     = kualitasClass($rv['kualitas']);
                                $kat_cls = ($rv['kategori'] === 'Murajaah')
                                    ? 'badge-kategori-murajaah'
                                    : 'badge-kategori-setoran';
                        ?>
                        <tr>
                            <td><?= $rv['tanggal'] ?></td>
                            <td><strong><?= $rv['surah_ayat'] ?></strong></td>
                            <td><span class="<?= $kls ?>"><?= htmlspecialchars($rv['kualitas'], ENT_QUOTES, 'UTF-8') ?></span></td>
                            <td><span class="<?= $kat_cls ?>"><?= htmlspecialchars($rv['kategori'], ENT_QUOTES, 'UTF-8') ?></span></td>
                            <td class="text-center">
                                <button type="button"
                                    class="btn-lihat-detail"
                                    title="Lihat Detail"
                                    aria-label="Lihat Detail"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalDetailRiwayatSiswa"
                                    data-tanggal="<?= $rv['tanggal'] ?>"
                                    data-surah-ayat="<?= $rv['surah_ayat'] ?>"
                                    data-kategori="<?= htmlspecialchars($rv['kategori'], ENT_QUOTES, 'UTF-8') ?>"
                                    data-kualitas="<?= htmlspecialchars($rv['kualitas'], ENT_QUOTES, 'UTF-8') ?>"
                                    data-ustadz="<?= $rv['ustadz'] ?>"
                                    data-catatan="<?= $rv['catatan'] ?>">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </td>
                        </tr>
                        <?php
                            endforeach;
                        else:
                            // Data dummy fallback
                            $dummy_riwayat = [
                                [
                                    'tanggal'    => '5 Nov 2026',
                                    'surah_ayat' => 'Al-Mulk · Ayat 13–18',
                                    'kualitas'   => 'Lancar',
                                    'kategori'   => 'Setoran',
                                    'ustadz'     => 'Ust. Rival Alvian Melfandy',
                                    'catatan'    => 'Hafalan sudah lancar. Perhatikan panjang mad pada ayat 15 dan 16. Ulangi perlahan sebelum melanjutkan ke ayat berikutnya.',
                                ],
                                [
                                    'tanggal'    => '4 Nov 2026',
                                    'surah_ayat' => 'Al-Mulk · Ayat 7–12',
                                    'kualitas'   => 'Lancar',
                                    'kategori'   => 'Setoran',
                                    'ustadz'     => 'Ust. Rival Alvian Melfandy',
                                    'catatan'    => 'Alhamdulillah ada peningkatan. Latih kembali makhraj huruf ain agar pelafalannya semakin jelas.',
                                ],
                                [
                                    'tanggal'    => '2 Nov 2026',
                                    'surah_ayat' => 'An-Naba\' · Ayat 1–20',
                                    'kualitas'   => 'Kurang Lancar',
                                    'kategori'   => 'Murajaah',
                                    'ustadz'     => 'Ust. Rival Alvian Melfandy',
                                    'catatan'    => 'Beberapa ayat masih terasa ragu-ragu. Perbanyak muroja\'ah mandiri sebelum sesi berikutnya.',
                                ],
                                [
                                    'tanggal'    => '1 Nov 2026',
                                    'surah_ayat' => '\'Abasa · Ayat 1–16',
                                    'kualitas'   => 'Lancar',
                                    'kategori'   => 'Murajaah',
                                    'ustadz'     => 'Ust. Rival Alvian Melfandy',
                                    'catatan'    => 'Sangat bagus. Lanjutkan ke ayat 17–42 pada sesi berikutnya.',
                                ],
                            ];
                            foreach ($dummy_riwayat as $rv):
                                $kls     = kualitasClass($rv['kualitas']);
                                $kat_cls = ($rv['kategori'] === 'Murajaah')
                                    ? 'badge-kategori-murajaah'
                                    : 'badge-kategori-setoran';
                                $esc_tan = htmlspecialchars($rv['tanggal'],    ENT_QUOTES, 'UTF-8');
                                $esc_sa  = htmlspecialchars($rv['surah_ayat'], ENT_QUOTES, 'UTF-8');
                                $esc_kat = htmlspecialchars($rv['kategori'],   ENT_QUOTES, 'UTF-8');
                                $esc_kua = htmlspecialchars($rv['kualitas'],   ENT_QUOTES, 'UTF-8');
                                $esc_ust = htmlspecialchars($rv['ustadz'],     ENT_QUOTES, 'UTF-8');
                                $esc_cat = htmlspecialchars($rv['catatan'],    ENT_QUOTES, 'UTF-8');
                        ?>
                        <tr>
                            <td><?= $esc_tan ?></td>
                            <td><strong><?= $esc_sa ?></strong></td>
                            <td><span class="<?= $kls ?>"><?= $esc_kua ?></span></td>
                            <td><span class="<?= $kat_cls ?>"><?= $esc_kat ?></span></td>
                            <td class="text-center">
                                <button type="button"
                                    class="btn-lihat-detail"
                                    title="Lihat Detail"
                                    aria-label="Lihat Detail"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalDetailRiwayatSiswa"
                                    data-tanggal="<?= $esc_tan ?>"
                                    data-surah-ayat="<?= $esc_sa ?>"
                                    data-kategori="<?= $esc_kat ?>"
                                    data-kualitas="<?= $esc_kua ?>"
                                    data-ustadz="<?= $esc_ust ?>"
                                    data-catatan="<?= $esc_cat ?>">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Panel Masukan dari Ustadz Pembimbing -->
    <div class="col-12 col-lg-5">
        <div class="card border-0 shadow-sm p-4">
            <h6 class="fw-bold mb-3" style="color:#11756D;">Masukan dari Ustadz Pembimbing</h6>

            <div class="p-3 rounded-3 mb-3" style="background:#F4F7F6; border-left: 3px solid #11756D;">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <strong class="small text-dark">Ust. Rival Alvian Melfandy</strong>
                    <small class="text-muted">5 Nov 2026</small>
                </div>
                <p class="small text-muted mb-2">
                    "Hafalan sudah lancar. Perhatikan panjang mad pada ayat 15 dan 16. Ulangi perlahan sebelum melanjutkan ke ayat berikutnya."
                </p>
                <span class="badge bg-success-subtle text-success small">Fokus: Panjang mad</span>
            </div>

            <div class="p-3 rounded-3" style="background:#F4F7F6; border-left: 3px solid #11756D;">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <strong class="small text-dark">Ust. Rival Alvian Melfandy</strong>
                    <small class="text-muted">4 Nov 2026</small>
                </div>
                <p class="small text-muted mb-2">
                    "Alhamdulillah ada peningkatan. Latih kembali makhraj huruf 'ain agar pelafalannya semakin jelas."
                </p>
                <span class="badge bg-success-subtle text-success small">Fokus: Makhraj huruf</span>
            </div>
        </div>
    </div>
</div>

<!-- ═══════════════════════════════════════════════════════════
     MODAL DETAIL RIWAYAT SISWA (#modalDetailRiwayatSiswa)
════════════════════════════════════════════════════════════ -->
<div class="modal fade" id="modalDetailRiwayatSiswa" tabindex="-1" aria-labelledby="modalDetailRiwayatLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 500px;">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title" id="modalDetailRiwayatLabel">
                        <i class="bi bi-journal-text me-2" style="color:#11756D;"></i>Detail Riwayat Hafalan
                    </h5>
                    <p class="mb-0 mt-1 small text-muted">Informasi lengkap sesi setoran atau murajaah.</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <div class="detail-info-row">
                    <span class="detail-info-label">Tanggal</span>
                    <span class="detail-info-value" id="mdTanggal">—</span>
                </div>
                <div class="detail-info-row">
                    <span class="detail-info-label">Surah / Ayat</span>
                    <span class="detail-info-value" id="mdSurahAyat">—</span>
                </div>
                <div class="detail-info-row">
                    <span class="detail-info-label">Jenis / Kategori</span>
                    <span class="detail-info-value" id="mdKategori">—</span>
                </div>
                <div class="detail-info-row">
                    <span class="detail-info-label">Kualitas Hafalan</span>
                    <span class="detail-info-value" id="mdKualitas">—</span>
                </div>
                <div class="detail-info-row" style="border-bottom:none; padding-bottom:0; flex-direction:column; gap:8px;">
                    <span class="detail-info-label">Catatan Ustadz / Guru</span>
                    <blockquote class="detail-catatan-box mb-0" id="mdCatatan">
                        Tidak ada catatan tambahan dari ustadz pembimbing.
                    </blockquote>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-1"></i>Tutup
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// ── Modal Detail Riwayat Siswa ───────────────────────────────
(function () {
    var modalEl = document.getElementById('modalDetailRiwayatSiswa');
    if (!modalEl) return;

    modalEl.addEventListener('show.bs.modal', function (event) {
        var btn = event.relatedTarget;
        if (!btn) return;

        var tanggal   = btn.getAttribute('data-tanggal')    || '—';
        var surahAyat = btn.getAttribute('data-surah-ayat') || '—';
        var kategori  = btn.getAttribute('data-kategori')   || '—';
        var kualitas  = btn.getAttribute('data-kualitas')   || '—';
        var catatan   = btn.getAttribute('data-catatan')    || '';

        document.getElementById('mdTanggal').textContent   = tanggal;
        document.getElementById('mdSurahAyat').textContent = surahAyat;

        // Badge kategori
        var katBadge = (kategori === 'Murajaah')
            ? '<span class="badge-kategori-murajaah">' + escHtml(kategori) + '</span>'
            : '<span class="badge-kategori-setoran">'  + escHtml(kategori) + '</span>';
        document.getElementById('mdKategori').innerHTML = katBadge;

        // Badge kualitas
        var kualitasClass = 'badge-kualitas-lancar';
        if (kualitas === 'Kurang Lancar') kualitasClass = 'badge-kualitas-kurang';
        else if (kualitas === 'Tidak Lancar') kualitasClass = 'badge-kualitas-tidak';
        document.getElementById('mdKualitas').innerHTML =
            '<span class="' + kualitasClass + '">' + escHtml(kualitas) + '</span>';

        // Catatan
        var catatanEl = document.getElementById('mdCatatan');
        if (catatan && catatan.trim() !== '') {
            catatanEl.textContent  = catatan;
            catatanEl.style.fontStyle = 'italic';
        } else {
            catatanEl.textContent  = 'Tidak ada catatan tambahan dari ustadz pembimbing.';
            catatanEl.style.fontStyle = 'normal';
        }
    });

    function escHtml(str) {
        var d = document.createElement('div');
        d.appendChild(document.createTextNode(str));
        return d.innerHTML;
    }
})();
</script>
