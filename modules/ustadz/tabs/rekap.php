<?php
/**
 * tabs/rekap.php
 * Tampilan tab Rekap Tahfiz Halaqah.
 * Variabel yang dipakai: $santri_list, $total_setoran_count, $total_murajaah_count
 */

// Data rekap per siswa (fallback statis)
$rekap_data = [
    '102601' => ['capaian'=>60,'tgl_setor'=>'15 Nov 2026','posisi'=>'Juz 29 – Surah Al-Mulk (Ayat 13–18)','target'=>'Surah Al-Mulk (Ayat 1–30)','ayat_hafal'=>18,'ayat_total'=>30,'catatan'=>'Bacaan makhraj dan mad sudah baik, perlu mengulang kelancaran sambungan ayat 15–18 sebelum lanjut ke ayat berikutnya.','riwayat'=>[['tgl'=>'15 Nov 2026','jenis'=>'Ziyadah','surah'=>'Al-Mulk: 13–18','status'=>'Lancar'],['tgl'=>'14 Nov 2026','jenis'=>'Murajaah','surah'=>'Al-Mulk: 7–12','status'=>'Lancar'],['tgl'=>'12 Nov 2026','jenis'=>'Ziyadah','surah'=>'Al-Mulk: 1–6','status'=>'Lancar']]],
    '102602' => ['capaian'=>75,'tgl_setor'=>'14 Nov 2026','posisi'=>'Juz 29 – Surah Al-Mulk (Ayat 19–24)','target'=>'Surah Al-Mulk (Ayat 1–30)','ayat_hafal'=>24,'ayat_total'=>30,'catatan'=>'Irama tartil sangat stabil, perhatikan hukum ghunnah pada ayat 21.','riwayat'=>[['tgl'=>'14 Nov 2026','jenis'=>'Ziyadah','surah'=>'Al-Mulk: 19–24','status'=>'Lancar'],['tgl'=>'12 Nov 2026','jenis'=>'Murajaah','surah'=>'Al-Mulk: 13–18','status'=>'Lancar'],['tgl'=>'10 Nov 2026','jenis'=>'Ziyadah','surah'=>'Al-Mulk: 13–18','status'=>'Lancar']]],
    '102603' => ['capaian'=>90,'tgl_setor'=>'12 Nov 2026','posisi'=>'Juz 29 – Surah Al-Qalam (Ayat 1–42)','target'=>'Surah Al-Qalam (Ayat 1–52)','ayat_hafal'=>42,'ayat_total'=>52,'catatan'=>'Hafalan sangat kuat dan mutqin, siap menyelesaikan 10 ayat terakhir pekan ini.','riwayat'=>[['tgl'=>'12 Nov 2026','jenis'=>'Ziyadah','surah'=>'Al-Qalam: 36–42','status'=>'Lancar'],['tgl'=>'10 Nov 2026','jenis'=>'Ziyadah','surah'=>'Al-Qalam: 29–35','status'=>'Lancar'],['tgl'=>'8 Nov 2026','jenis'=>'Murajaah','surah'=>'Al-Qalam: 1–28','status'=>'Lancar']]],
    '102604' => ['capaian'=>80,'tgl_setor'=>'15 Nov 2026','posisi'=>"Juz 30 – Surah An-Naba' (Ayat 1–32)",'target'=>"Surah An-Naba' (Ayat 1–40)",'ayat_hafal'=>32,'ayat_total'=>40,'catatan'=>'Kelancaran meningkat pesat, pertahankan murojaah sebelum tidur.','riwayat'=>[['tgl'=>'15 Nov 2026','jenis'=>'Ziyadah','surah'=>'An-Naba: 27–32','status'=>'Lancar'],['tgl'=>'13 Nov 2026','jenis'=>'Murajaah','surah'=>'An-Naba: 1–26','status'=>'Lancar'],['tgl'=>'11 Nov 2026','jenis'=>'Ziyadah','surah'=>'An-Naba: 21–26','status'=>'Lancar']]],
    '102605' => ['capaian'=>85,'tgl_setor'=>'14 Nov 2026','posisi'=>"Juz 30 – Surah An-Nazi'at (Ayat 1–39)",'target'=>"Surah An-Nazi'at (Ayat 1–46)",'ayat_hafal'=>39,'ayat_total'=>46,'catatan'=>'Pelafalan huruf qalqalah sudah tepat, lanjutkan 7 ayat terakhir.','riwayat'=>[['tgl'=>'14 Nov 2026','jenis'=>'Ziyadah','surah'=>"An-Nazi'at: 34–39",'status'=>'Lancar'],['tgl'=>'12 Nov 2026','jenis'=>'Murajaah','surah'=>"An-Nazi'at: 1–33",'status'=>'Lancar'],['tgl'=>'10 Nov 2026','jenis'=>'Ziyadah','surah'=>"An-Nazi'at: 28–33",'status'=>'Lancar']]],
    '102606' => ['capaian'=>50,'tgl_setor'=>'11 Nov 2026','posisi'=>"Juz 30 – Surah 'Abasa (Ayat 1–21)",'target'=>"Surah 'Abasa (Ayat 1–42)",'ayat_hafal'=>21,'ayat_total'=>42,'catatan'=>'Perlu menambah porsi murajaah ayat 10–21 agar tidak tertukar akhir ayatnya.','riwayat'=>[['tgl'=>'11 Nov 2026','jenis'=>'Ziyadah','surah'=>"'Abasa: 16–21",'status'=>'Lancar'],['tgl'=>'9 Nov 2026','jenis'=>'Murajaah','surah'=>"'Abasa: 1–15",'status'=>'Lancar'],['tgl'=>'7 Nov 2026','jenis'=>'Ziyadah','surah'=>"'Abasa: 10–15",'status'=>'Lancar']]],
    '102607' => ['capaian'=>95,'tgl_setor'=>'16 Nov 2026','posisi'=>'Juz 30 – Surah At-Takwir (Ayat 1–27)','target'=>'Surah At-Takwir (Ayat 1–29)','ayat_hafal'=>27,'ayat_total'=>29,'catatan'=>'Sangat lancar dan tajwid terjaga, tinggal 2 ayat penutup.','riwayat'=>[['tgl'=>'16 Nov 2026','jenis'=>'Ziyadah','surah'=>'At-Takwir: 22–27','status'=>'Lancar'],['tgl'=>'14 Nov 2026','jenis'=>'Murajaah','surah'=>'At-Takwir: 1–21','status'=>'Lancar'],['tgl'=>'12 Nov 2026','jenis'=>'Ziyadah','surah'=>'At-Takwir: 15–21','status'=>'Lancar']]],
    '102608' => ['capaian'=>65,'tgl_setor'=>'13 Nov 2026','posisi'=>'Juz 29 – Surah Al-Haqqah (Ayat 1–34)','target'=>'Surah Al-Haqqah (Ayat 1–52)','ayat_hafal'=>34,'ayat_total'=>52,'catatan'=>"Panjang pendek mad thabi'i sudah bagus, tingkatkan kecepatan transisi antarayat.",'riwayat'=>[['tgl'=>'13 Nov 2026','jenis'=>'Ziyadah','surah'=>'Al-Haqqah: 28–34','status'=>'Lancar'],['tgl'=>'11 Nov 2026','jenis'=>'Murajaah','surah'=>'Al-Haqqah: 1–27','status'=>'Lancar'],['tgl'=>'9 Nov 2026','jenis'=>'Ziyadah','surah'=>'Al-Haqqah: 21–27','status'=>'Lancar']]],
    '102609' => ['capaian'=>70,'tgl_setor'=>'14 Nov 2026','posisi'=>'Juz 30 – Surah Al-Infithar (Ayat 1–14)','target'=>'Surah Al-Infithar (Ayat 1–19)','ayat_hafal'=>14,'ayat_total'=>19,'catatan'=>'Hafalan baru lancar, terus ulangi surah-surah pendek sebelumnya.','riwayat'=>[['tgl'=>'14 Nov 2026','jenis'=>'Ziyadah','surah'=>'Al-Infithar: 10–14','status'=>'Lancar'],['tgl'=>'12 Nov 2026','jenis'=>'Murajaah','surah'=>'Al-Infithar: 1–9','status'=>'Lancar'],['tgl'=>'10 Nov 2026','jenis'=>'Ziyadah','surah'=>'Al-Infithar: 5–9','status'=>'Lancar']]],
    '102610' => ['capaian'=>55,'tgl_setor'=>'10 Nov 2026','posisi'=>'Juz 30 – Surah Al-Muthaffifin (Ayat 1–20)','target'=>'Surah Al-Muthaffifin (Ayat 1–36)','ayat_hafal'=>20,'ayat_total'=>36,'catatan'=>'Fokus perbaiki waqaf dan ibtida pada ayat-ayat panjang.','riwayat'=>[['tgl'=>'10 Nov 2026','jenis'=>'Ziyadah','surah'=>'Al-Muthaffifin: 15–20','status'=>'Lancar'],['tgl'=>'8 Nov 2026','jenis'=>'Murajaah','surah'=>'Al-Muthaffifin: 1–14','status'=>'Lancar'],['tgl'=>'6 Nov 2026','jenis'=>'Ziyadah','surah'=>'Al-Muthaffifin: 8–14','status'=>'Lancar']]],
];
$rekap_total_siswa    = count($santri_list);
$rekap_total_setoran  = $total_setoran_count  > 0 ? $total_setoran_count  : 28;
$rekap_total_murajaah = $total_murajaah_count > 0 ? $total_murajaah_count : 19;
?>
<!-- Badge & Header -->
<div class="mb-4">
    <span class="badge px-3 py-2 fw-bold rounded-pill" style="background-color: #11756D; font-size: 11px; letter-spacing: 0.05em;">REKAPITULASI HALAQAH AL-FURQAN</span>
    <h2 class="fw-bold mt-3 mb-1" style="color: #1E293B; font-size: 1.65rem;">Rekap Tahfiz Siswa Binaan</h2>
    <p class="text-muted mb-0" style="font-size: 14px;">Ringkasan capaian target dan riwayat setoran hafalan siswa halaqah.</p>
</div>

<!-- 4 KPI Cards -->
<div class="row g-3 mb-4">
    <?php foreach ([
        ['Siswa Binaan',       $rekap_total_siswa,    'Siswa dalam halaqah',       '#1E293B'],
        ['Setoran Tercatat',   $rekap_total_setoran,  'Setoran bulan '.date('F Y'), '#1E293B'],
        ['Total Murajaah',     $rekap_total_murajaah, 'Pengulangan santri mutqin', '#1E293B'],
        ['Target Kelancaran',  '85%',                 'Target capaian halaqah',    '#11756D'],
    ] as [$label, $nilai, $sub, $warna]): ?>
    <div class="col-lg-3 col-md-6">
        <div style="background:#fff; border:1px solid #E2E8F0; border-radius:16px; padding:20px 22px; box-shadow:0 2px 10px rgba(0,0,0,0.04); border-left:4px solid #11756D;">
            <div class="text-uppercase fw-bold text-muted" style="font-size:11px; letter-spacing:0.05em;"><?= $label ?></div>
            <div class="fw-bold mt-1" style="font-size:2rem; color:<?= $warna ?>;"><?= $nilai ?></div>
            <div class="text-muted" style="font-size:12.5px;"><?= $sub ?></div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Kartu Tabel Daftar Siswa & Rekap Capaian -->
<div style="background:#fff; border:1px solid #E2E8F0; border-radius:16px; box-shadow:0 2px 10px rgba(0,0,0,0.04); overflow:hidden;">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 px-4 py-3" style="border-bottom:1px solid #F1F5F9;">
        <h5 class="fw-bold mb-0" style="color:#1E293B; font-size:16px;">Daftar Siswa &amp; Rekap Capaian</h5>
        <div class="d-flex align-items-center gap-3">
            <span class="badge rounded-pill fw-semibold" id="badgeJumlahSiswa"
                  style="background:#E6F4F1; color:#11756D; font-size:12px; padding:6px 14px;">
                <?= $rekap_total_siswa ?> Siswa Terdaftar
            </span>
            <div class="position-relative">
                <i class="bi bi-search position-absolute" style="left:12px; top:50%; transform:translateY(-50%); color:#94A3B8; font-size:14px;"></i>
                <input type="text" id="searchSiswaInput" class="form-control form-control-sm"
                       placeholder="Cari NIS atau nama siswa..."
                       style="padding-left:36px; border-radius:10px; border:1px solid #E2E8F0; min-width:220px; font-size:13px; height:38px;">
            </div>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" id="tabelRekapSiswa">
            <thead style="background:#F8FAFC;">
                <tr style="font-size:12px; color:#64748B; text-transform:uppercase; letter-spacing:0.04em;">
                    <th class="ps-4 py-3 fw-semibold" style="width:50px;">No</th>
                    <th class="py-3 fw-semibold" style="width:100px;">NIS</th>
                    <th class="py-3 fw-semibold">Nama Siswa</th>
                    <th class="py-3 fw-semibold" style="width:220px;">Persentase Capaian</th>
                    <th class="py-3 fw-semibold" style="width:170px;">Tanggal Setoran Terakhir</th>
                    <th class="py-3 pe-4 fw-semibold text-center" style="width:140px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($santri_list as $i => $s):
                    $nis  = htmlspecialchars($s['nip_nis']);
                    $nama = htmlspecialchars($s['nama_lengkap']);
                    $rd   = $rekap_data[$nis] ?? ['capaian'=>0,'tgl_setor'=>'-','posisi'=>'-','target'=>'-','ayat_hafal'=>0,'ayat_total'=>1,'catatan'=>'-','riwayat'=>[]];
                ?>
                <tr class="siswa-row" data-nis="<?= $nis ?>" data-nama="<?= strtolower($nama) ?>">
                    <td class="ps-4 text-muted"><?= $i + 1 ?></td>
                    <td><code style="color:#D946EF; font-weight:600; font-size:13px; background:transparent;"><?= $nis ?></code></td>
                    <td><strong style="color:#1E293B;"><?= $nama ?></strong></td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div class="progress flex-grow-1" style="height:8px; border-radius:4px; max-width:140px; background:#E2E8F0;">
                                <div class="progress-bar" style="width:<?= $rd['capaian'] ?>%; background-color:#11756D; border-radius:4px;"></div>
                            </div>
                            <span class="fw-bold" style="font-size:13px; color:#1E293B; min-width:36px;"><?= $rd['capaian'] ?>%</span>
                        </div>
                    </td>
                    <td class="text-muted" style="font-size:13px;"><?= $rd['tgl_setor'] ?></td>
                    <td class="pe-4 text-center">
                        <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3 btn-lihat-detail"
                            data-nis="<?= $nis ?>"
                            data-nama="<?= $nama ?>"
                            data-posisi="<?= htmlspecialchars($rd['posisi']) ?>"
                            data-target="<?= htmlspecialchars($rd['target']) ?>"
                            data-ayathafal="<?= $rd['ayat_hafal'] ?>"
                            data-ayattotal="<?= $rd['ayat_total'] ?>"
                            data-capaian="<?= $rd['capaian'] ?>"
                            data-catatan="<?= htmlspecialchars($rd['catatan']) ?>"
                            data-riwayat='<?= json_encode($rd['riwayat'], JSON_UNESCAPED_UNICODE) ?>'
                            style="font-size:12.5px; border-color:#CBD5E1; color:#475569;">
                            <i class="bi bi-eye me-1"></i>Lihat Detail
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="px-4 py-3" style="border-top:1px solid #F1F5F9;">
        <small class="text-muted" id="footerInfoSiswa">
            Menampilkan 1–<?= $rekap_total_siswa ?> dari <?= $rekap_total_siswa ?> siswa
        </small>
    </div>
</div>

<!-- Modal: Detail Rekap Tahfiz Siswa -->
<div class="modal fade" id="modalDetailSiswa" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:640px;">
        <div class="modal-content border-0" style="border-radius:16px; overflow:hidden; box-shadow:0 20px 60px rgba(0,0,0,0.15);">
            <div class="modal-header border-0 pb-0 px-4 pt-4">
                <div>
                    <h5 class="modal-title fw-bold" style="color:#1E293B; font-size:18px;">Detail Rekap Tahfiz Siswa</h5>
                    <p class="text-muted mb-0" style="font-size:13px;" id="modalSubtitle"></p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body px-4 py-3">
                <div class="row g-3 mb-3">
                    <div class="col-6">
                        <div style="background:#F0FDF4; border-radius:12px; padding:16px;">
                            <div class="fw-bold mb-1" style="font-size:13px; color:#15803D;">Posisi Hafalan Terakhir</div>
                            <div class="fw-bold mb-2" style="font-size:14.5px; color:#1E293B;" id="modalPosisi"></div>
                            <span class="badge rounded-pill" style="background:#DCFCE7; color:#15803D; font-size:11px;">Lancar</span>
                        </div>
                    </div>
                    <div class="col-6">
                        <div style="background:#FFFBEB; border-radius:12px; padding:16px;">
                            <div class="fw-bold mb-1" style="font-size:13px; color:#B45309;">Target Aktif</div>
                            <div class="fw-bold mb-1" style="font-size:14.5px; color:#1E293B;" id="modalTarget"></div>
                            <div class="mb-1" style="font-size:12px; color:#64748B;" id="modalAyatRatio"></div>
                            <div class="progress mb-1" style="height:6px; border-radius:3px; background:#FDE68A;">
                                <div class="progress-bar" id="modalProgressBar" style="background:#D97706; border-radius:3px;"></div>
                            </div>
                            <div style="font-size:11px; color:#92400E;">Periode: <?= date('F Y') ?></div>
                        </div>
                    </div>
                </div>
                <div style="background:#F8FAFC; border-radius:12px; padding:16px; margin-bottom:16px;">
                    <div class="fw-bold mb-2" style="font-size:13.5px; color:#1E293B;">Catatan Ustadz Pembimbing</div>
                    <p class="mb-0 text-muted" style="font-size:13px; line-height:1.6;" id="modalCatatan"></p>
                </div>
                <div class="fw-bold mb-2" style="font-size:14px; color:#1E293B;">Riwayat Setoran &amp; Murajaah</div>
                <div class="table-responsive" style="border:1px solid #E2E8F0; border-radius:10px; overflow:hidden;">
                    <table class="table table-sm align-middle mb-0" style="font-size:13px;">
                        <thead style="background:#F8FAFC;">
                            <tr style="color:#64748B;">
                                <th class="py-2 ps-3 fw-semibold">Tanggal</th>
                                <th class="py-2 fw-semibold">Jenis</th>
                                <th class="py-2 fw-semibold">Surah &amp; Ayat</th>
                                <th class="py-2 pe-3 fw-semibold">Status</th>
                            </tr>
                        </thead>
                        <tbody id="modalRiwayatBody"></tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer border-0 px-4 pb-4 pt-2 justify-content-center">
                <button type="button" class="btn fw-semibold text-white px-5 py-2" data-bs-dismiss="modal"
                        style="background-color:#11756D; border-radius:10px; font-size:14px;">Tutup Detail</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Filter pencarian
    const searchInput = document.getElementById('searchSiswaInput');
    const rows        = document.querySelectorAll('.siswa-row');
    const footerInfo  = document.getElementById('footerInfoSiswa');
    const badge       = document.getElementById('badgeJumlahSiswa');
    const totalSiswa  = rows.length;

    searchInput.addEventListener('input', function () {
        const q = this.value.toLowerCase().trim();
        let visible = 0;
        rows.forEach(function (row) {
            const match = row.getAttribute('data-nis').includes(q) || row.getAttribute('data-nama').includes(q);
            row.style.display = match ? '' : 'none';
            if (match) visible++;
        });
        footerInfo.textContent = visible === 0
            ? 'Data siswa tidak ditemukan'
            : 'Menampilkan 1\u2013' + visible + ' dari ' + totalSiswa + ' siswa';
        badge.textContent = visible + ' Siswa ' + (q ? 'Ditemukan' : 'Terdaftar');
    });

    // Modal detail
    const bsModal = new bootstrap.Modal(document.getElementById('modalDetailSiswa'));
    document.querySelectorAll('.btn-lihat-detail').forEach(function (btn) {
        btn.addEventListener('click', function () {
            document.getElementById('modalSubtitle').textContent =
                this.dataset.nama + ' \u2022 NIS: ' + this.dataset.nis + ' \u2022 Halaqah Al-Furqan';
            document.getElementById('modalPosisi').textContent = this.dataset.posisi;
            document.getElementById('modalTarget').textContent = this.dataset.target;
            const hafal = parseInt(this.dataset.ayathafal);
            const total = parseInt(this.dataset.ayattotal);
            const pct   = parseInt(this.dataset.capaian);
            document.getElementById('modalAyatRatio').textContent = hafal + ' / ' + total + ' Ayat (' + pct + '%)';
            document.getElementById('modalProgressBar').style.width = pct + '%';
            document.getElementById('modalCatatan').textContent = this.dataset.catatan;

            let riwayat = [];
            try { riwayat = JSON.parse(this.dataset.riwayat); } catch (e) {}
            document.getElementById('modalRiwayatBody').innerHTML = riwayat.map(function (r) {
                const c = r.jenis === 'Ziyadah' ? 'background:#11756D;color:#fff;' : 'background:#D97706;color:#fff;';
                return '<tr>'
                    + '<td class="ps-3 py-2">' + r.tgl + '</td>'
                    + '<td class="py-2"><span class="badge rounded-pill" style="font-size:11px;' + c + '">' + r.jenis + '</span></td>'
                    + '<td class="py-2">' + r.surah + '</td>'
                    + '<td class="pe-3 py-2 fw-semibold" style="color:#15803D;">' + r.status + '</td>'
                    + '</tr>';
            }).join('');
            bsModal.show();
        });
    });
});
</script>
