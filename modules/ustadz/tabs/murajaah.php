<?php
/**
 * tabs/murajaah.php
 * Tab Murajaah – 2 kolom, struktur & styling identik dengan tab Hafalan.
 * Variabel dari index.php: $santri_list, $riwayat_setoran
 */

// ── Kelompokkan riwayat setoran berdasarkan nama_siswa ───────
$setoran_per_siswa = [];
foreach ($riwayat_setoran as $baris) {
    $nama = trim($baris['nama_siswa'] ?? '');
    if ($nama === '') continue;
    $awal    = (int)($baris['ayat_awal']  ?? 1);
    $akhir   = (int)($baris['ayat_akhir'] ?? 1);
    $tgl_raw = $baris['tanggal_setor'] ?? date('Y-m-d');
    $setoran_per_siswa[$nama][] = [
        'id'          => (int)($baris['id'] ?? 0),
        'tanggal'     => date('d M Y', strtotime($tgl_raw)),
        'tanggal_iso' => date('Y-m-d', strtotime($tgl_raw)),
        'surah'       => htmlspecialchars($baris['surah'] ?? '–'),
        'ayat_awal'   => $awal,
        'ayat_akhir'  => $akhir,
        'rentang'     => "Ayat {$awal}–{$akhir}",
        'kualitas'    => htmlspecialchars($baris['kualitas'] ?? 'Lancar'),
        'catatan'     => htmlspecialchars($baris['catatan'] ?? ''),
    ];
}

// ── Fallback 3 baris contoh ──────────────────────────────────
$fallback_setoran_default = [
    ['id'=>0,'tanggal'=>date('d M Y'),'tanggal_iso'=>date('Y-m-d'),
     'surah'=>'Al-Baqarah','ayat_awal'=>1,'ayat_akhir'=>10,
     'rentang'=>'Ayat 1–10','kualitas'=>'Kurang Lancar','catatan'=>''],
    ['id'=>0,'tanggal'=>date('d M Y',strtotime('-2 days')),'tanggal_iso'=>date('Y-m-d',strtotime('-2 days')),
     'surah'=>'Al-Fatihah','ayat_awal'=>1,'ayat_akhir'=>7,
     'rentang'=>'Ayat 1–7','kualitas'=>'Lancar','catatan'=>''],
    ['id'=>0,'tanggal'=>date('d M Y',strtotime('-5 days')),'tanggal_iso'=>date('Y-m-d',strtotime('-5 days')),
     'surah'=>'An-Nas','ayat_awal'=>1,'ayat_akhir'=>6,
     'rentang'=>'Ayat 1–6','kualitas'=>'Lancar','catatan'=>''],
];

$setoran_json  = json_encode($setoran_per_siswa,       JSON_UNESCAPED_UNICODE | JSON_HEX_APOS | JSON_HEX_QUOT);
$fallback_json = json_encode($fallback_setoran_default, JSON_UNESCAPED_UNICODE | JSON_HEX_APOS | JSON_HEX_QUOT);
$total_santri  = count($santri_list);
?>

<!-- ═══════════════════════════════════════════════════════════
     LAYOUT 2 KOLOM — identik dengan tab Hafalan
     ═══════════════════════════════════════════════════════════ -->
<div class="hafalan-layout">

    <!-- ══ PANEL KIRI: PILIH SISWA ══════════════════════════════ -->
    <section class="hafalan-panel hafalan-panel-siswa" aria-label="Pilih siswa murajaah">
        <h2 class="hafalan-panel-title">
            <i class="bi bi-person"></i>
            <span>Pilih siswa</span>
        </h2>

        <!-- Input pencarian — struktur identik #searchHafalanSiswa -->
        <div class="hafalan-search-wrap">
            <i class="bi bi-search"></i>
            <input type="search" id="searchMurajaahSiswa"
                   placeholder="Cari nama atau NIS" autocomplete="off">
        </div>

        <!-- Daftar kartu siswa -->
        <div id="listMurajaahSiswa">
            <?php foreach ($santri_list as $s):
                $kata    = explode(' ', trim($s['nama_lengkap']));
                $inisial = strtoupper(substr($kata[0], 0, 1));
                if (isset($kata[1])) $inisial .= strtoupper(substr($kata[1], 0, 1));
            ?>
                <button type="button"
                        class="hafalan-siswa-card"
                        data-nama="<?= htmlspecialchars($s['nama_lengkap'], ENT_QUOTES) ?>"
                        data-nis="<?= htmlspecialchars($s['nip_nis'], ENT_QUOTES) ?>"
                        data-id="<?= (int)$s['id'] ?>">
                    <span class="hafalan-avatar" aria-hidden="true"><?= $inisial ?></span>
                    <span class="hafalan-siswa-meta">
                        <span class="hafalan-siswa-nama"><?= htmlspecialchars($s['nama_lengkap']) ?></span>
                        <span class="hafalan-siswa-nis">NIS: <?= htmlspecialchars($s['nip_nis']) ?></span>
                    </span>
                </button>
            <?php endforeach; ?>
        </div>

        <!-- Footer info panel kiri -->
        <p id="infoMurajaahSiswa" class="mt-3 mb-0"
           style="font-size:12px; color:#94A3B8; text-align:center;">
            Menampilkan <?= $total_santri ?> dari <?= $total_santri ?> santri
        </p>
    </section>

    <!-- ══ PANEL KANAN: RIWAYAT SETORAN & EVALUASI ══════════════ -->
    <section class="hafalan-panel hafalan-panel-surah"
             aria-label="Riwayat setoran dan evaluasi murajaah">

        <!-- State kosong -->
        <div id="murajaahEmpty" class="hafalan-empty">
            <p>Pilih santri di panel kiri untuk melihat riwayat setoran dan menambahkan evaluasi murajaah.</p>
        </div>

        <!-- State aktif -->
        <div id="murajaahRiwayatArea" hidden>
            <div class="hafalan-surah-head">
                <div>
                    <h2>Riwayat Setoran &amp; Evaluasi Murajaah</h2>
                    <p>Pilih baris setoran untuk memperbaiki kualitas hafalan melalui murajaah</p>
                </div>
                <span class="hafalan-santri-pill" id="badgeSantriMurajaah">Santri: –</span>
            </div>

            <div class="table-responsive">
                <table class="hafalan-table">
                    <thead>
                        <tr>
                            <th style="width:130px;">Tanggal</th>
                            <th>Nama Surah</th>
                            <th style="width:135px;">Rentang Ayat</th>
                            <th style="width:165px;">Kualitas Hafalan</th>
                            <th class="col-aksi">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="tbodyRiwayatMurajaah"></tbody>
                </table>
            </div>

            <!-- Footer info panel kanan -->
            <div class="d-flex justify-content-between align-items-center mt-3"
                 style="font-size:12px; color:#94A3B8;">
                <span id="infoJumlahSetoran">Menampilkan 0 setoran</span>
                <span>Tekan <strong style="color:#11756D;">+</strong> untuk menambahkan evaluasi murajaah</span>
            </div>
        </div>
    </section>
</div>

<!-- ═══════════════════════════════════════════════════════════
     MODAL EVALUASI MURAJAAH — identik 100% dengan #modalInputSetoran
     ═══════════════════════════════════════════════════════════ -->
<div class="modal fade" id="modalEvaluasiMurajaah" tabindex="-1"
     aria-labelledby="judulEvaluasiMurajaah" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered hafalan-modal-setoran">
        <div class="modal-content">
            <form action="?tab=murajaah" method="POST">

                <!-- Hidden inputs -->
                <input type="hidden" name="action"       value="simpan_murajaah">
                <input type="hidden" name="setoran_id"   id="murajaahSetoranId"    value="0">
                <input type="hidden" name="santri_nama"  id="murajaahSantriNama"   value="">
                <input type="hidden" name="surah_diulang" id="murajaahSurahHidden" value="">
                <input type="hidden" name="ayat_awal"    id="murajaahAyatAwalHidden"  value="">
                <input type="hidden" name="ayat_akhir"   id="murajaahAyatAkhirHidden" value="">
                <input type="hidden" name="ayat_range"   id="murajaahAyatRangeHidden" value="">

                <!-- Header -->
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title" id="judulEvaluasiMurajaah">Evaluasi Murajaah</h5>
                        <p class="hafalan-modal-sub" id="subjudulEvaluasiMurajaah"></p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>

                <!-- Body -->
                <div class="modal-body">
                    <div class="hafalan-form-grid">

                        <!-- Kiri atas: Ayat Awal (dikunci) -->
                        <div>
                            <label class="hafalan-label" for="murajaahAyatAwal">Ayat Awal</label>
                            <select id="murajaahAyatAwal"
                                    class="form-select hafalan-field"
                                    disabled
                                    style="background:#F1F5F9; color:#64748B; cursor:not-allowed;">
                            </select>
                        </div>

                        <!-- Kanan atas: Ayat Akhir (dikunci) -->
                        <div>
                            <label class="hafalan-label" for="murajaahAyatAkhir">Ayat Akhir</label>
                            <select id="murajaahAyatAkhir"
                                    class="form-select hafalan-field"
                                    disabled
                                    style="background:#F1F5F9; color:#64748B; cursor:not-allowed;">
                            </select>
                        </div>

                        <!-- Kiri bawah: Kualitas Hafalan (aktif) -->
                        <div>
                            <label class="hafalan-label" for="inputKualitasMurajaah">Kualitas Hafalan</label>
                            <select id="inputKualitasMurajaah" name="evaluasi"
                                    class="form-select hafalan-field" required>
                                <option value="Sangat Mutqin">Sangat Mutqin</option>
                                <option value="Lancar" selected>Lancar</option>
                                <option value="Kurang Lancar">Kurang Lancar</option>
                                <option value="Tidak Lancar">Tidak Lancar</option>
                            </select>
                        </div>

                        <!-- Kanan bawah: Tanggal (aktif) -->
                        <div>
                            <label class="hafalan-label" for="inputTanggalMurajaah">Tanggal Murajaah</label>
                            <input type="date" id="inputTanggalMurajaah"
                                   name="tanggal_murajaah"
                                   class="form-control hafalan-field"
                                   value="<?= date('Y-m-d') ?>"
                                   data-hari-ini="<?= date('Y-m-d') ?>"
                                   required>
                        </div>

                        <!-- Baris penuh: Catatan -->
                        <div class="hafalan-form-full">
                            <label class="hafalan-label" for="inputCatatanMurajaah">Catatan (Opsional)</label>
                            <textarea id="inputCatatanMurajaah" name="catatan_murajaah"
                                      class="form-control hafalan-field" rows="3"
                                      placeholder="Catatan murajaah (opsional)..."></textarea>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="modal-footer">
                    <button type="submit" class="btn-simpan-setoran">
                        <i class="bi bi-floppy"></i>
                        <span>Simpan Evaluasi</span>
                    </button>
                    <button type="button" class="btn-batal-setoran" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle"></i>
                        <span>Batal</span>
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>

<script>
(function () {
    const SETORAN_PER_SISWA = <?= $setoran_json ?>;
    const FALLBACK_DEFAULT  = <?= $fallback_json ?>;
    let siswaMurajaahAktif  = null;
    let daftarAktif         = [];

    // ── Helper: class badge ──────────────────────────────────
    function kelasBadge(kualitas) {
        const q = (kualitas || '').toLowerCase();
        if (q.includes('sangat') || (q.includes('mutqin') && !q.includes('kurang'))) return 'hafalan-badge-selesai';
        if (q.includes('lancar') && !q.includes('kurang'))                           return 'hafalan-badge-selesai';
        if (q.includes('kurang'))                                                    return 'hafalan-badge-berjalan';
        return 'hafalan-badge-belum';
    }

    // ── Render baris tabel ───────────────────────────────────
    function renderBaris(daftar) {
        daftarAktif = (daftar && daftar.length > 0) ? daftar : FALLBACK_DEFAULT;
        const html = daftarAktif.map(function (s, idx) {
            const kelas = kelasBadge(s.kualitas);
            return '<tr>'
                + '<td style="font-size:13px;color:#64748B;">' + s.tanggal + '</td>'
                + '<td><span class="hafalan-surah-nama" style="font-weight:600;color:#1E293B;">' + s.surah + '</span></td>'
                + '<td style="font-size:13px;color:#64748B;">' + s.rentang + '</td>'
                + '<td><span class="hafalan-badge ' + kelas + '">' + s.kualitas + '</span></td>'
                + '<td class="col-aksi">'
                +   '<button type="button" class="btn-setoran-plus" data-idx="' + idx + '"'
                +   ' aria-label="Evaluasi murajaah ' + s.surah + '">'
                +   '<i class="bi bi-plus-lg"></i></button>'
                + '</td>'
                + '</tr>';
        }).join('');
        document.getElementById('tbodyRiwayatMurajaah').innerHTML = html;
        document.getElementById('infoJumlahSetoran').textContent =
            'Menampilkan ' + daftarAktif.length + ' setoran';
    }

    // ── Buka modal evaluasi ──────────────────────────────────
    function bukaModalEvaluasi(idx) {
        const item = daftarAktif[idx];
        if (!item || !siswaMurajaahAktif) return;

        // Subjudul
        document.getElementById('subjudulEvaluasiMurajaah').textContent =
            'Santri: ' + siswaMurajaahAktif.nama + ' \u2022 Surah ' + item.surah;

        // Dropdown Ayat Awal (dikunci)
        const selAwal = document.getElementById('murajaahAyatAwal');
        selAwal.innerHTML = '<option value="' + item.ayat_awal + '" selected>' + item.ayat_awal + '</option>';
        selAwal.disabled  = true;

        // Dropdown Ayat Akhir (dikunci)
        const selAkhir = document.getElementById('murajaahAyatAkhir');
        selAkhir.innerHTML = '<option value="' + item.ayat_akhir + '" selected>' + item.ayat_akhir + '</option>';
        selAkhir.disabled  = true;

        // Kualitas — cocokkan nilai atau default Lancar
        const selKualitas = document.getElementById('inputKualitasMurajaah');
        const opsi = Array.from(selKualitas.options).find(function (o) {
            return o.value.toLowerCase() === (item.kualitas || '').toLowerCase();
        });
        selKualitas.value = opsi ? opsi.value : 'Lancar';

        // Tanggal — reset ke hari ini
        const tglInput = document.getElementById('inputTanggalMurajaah');
        tglInput.value = tglInput.dataset.hariIni || '<?= date('Y-m-d') ?>';

        // Catatan — kosongkan
        document.getElementById('inputCatatanMurajaah').value = '';

        // Hidden inputs
        document.getElementById('murajaahSetoranId').value       = item.id;
        document.getElementById('murajaahSantriNama').value      = siswaMurajaahAktif.nama;
        document.getElementById('murajaahSurahHidden').value     = item.surah;
        document.getElementById('murajaahAyatAwalHidden').value  = item.ayat_awal;
        document.getElementById('murajaahAyatAkhirHidden').value = item.ayat_akhir;
        document.getElementById('murajaahAyatRangeHidden').value = item.rentang;

        bootstrap.Modal.getOrCreateInstance(
            document.getElementById('modalEvaluasiMurajaah')
        ).show();
    }

    // ── Pilih siswa ──────────────────────────────────────────
    function pilihSiswa(kartu) {
        document.querySelectorAll('#listMurajaahSiswa .hafalan-siswa-card')
            .forEach(function (el) { el.classList.remove('aktif'); });
        kartu.classList.add('aktif');

        siswaMurajaahAktif = { nama: kartu.dataset.nama, nis: kartu.dataset.nis };
        document.getElementById('badgeSantriMurajaah').textContent =
            'Santri: ' + siswaMurajaahAktif.nama;

        renderBaris(SETORAN_PER_SISWA[siswaMurajaahAktif.nama] || null);

        document.getElementById('murajaahEmpty').hidden       = true;
        document.getElementById('murajaahRiwayatArea').hidden = false;
    }

    // ── Event: klik kartu siswa ──────────────────────────────
    document.getElementById('listMurajaahSiswa').addEventListener('click', function (e) {
        const kartu = e.target.closest('.hafalan-siswa-card');
        if (kartu) pilihSiswa(kartu);
    });

    // ── Event: klik tombol + di baris tabel ──────────────────
    document.getElementById('tbodyRiwayatMurajaah').addEventListener('click', function (e) {
        const btn = e.target.closest('.btn-setoran-plus');
        if (btn) bukaModalEvaluasi(parseInt(btn.dataset.idx, 10));
    });

    // ── Event: pencarian real-time ───────────────────────────
    document.getElementById('searchMurajaahSiswa').addEventListener('input', function () {
        const kata = this.value.trim().toLowerCase();
        let tampil  = 0;
        const total = <?= $total_santri ?>;
        document.querySelectorAll('#listMurajaahSiswa .hafalan-siswa-card').forEach(function (kartu) {
            const cocok = (kartu.dataset.nama || '').toLowerCase().includes(kata)
                       || (kartu.dataset.nis  || '').toLowerCase().includes(kata);
            kartu.style.display = cocok ? '' : 'none';
            if (cocok) tampil++;
        });
        document.getElementById('infoMurajaahSiswa').textContent =
            'Menampilkan ' + tampil + ' dari ' + total + ' santri';
    });

    // ── Auto-pilih dari URL ?santri=... ──────────────────────
    (function () {
        const preset = new URLSearchParams(window.location.search).get('santri');
        if (!preset) return;
        const kunci = preset.toLowerCase();
        const kartu = Array.from(document.querySelectorAll('#listMurajaahSiswa .hafalan-siswa-card'));
        const cocok = kartu.find(function (el) {
                return (el.dataset.nama || '').toLowerCase() === kunci;
            }) || kartu.find(function (el) {
                return (el.dataset.nama || '').toLowerCase().includes(kunci);
            });
        if (cocok) pilihSiswa(cocok);
    })();
})();
</script>
