<?php
/**
 * spmb.php – Halaman Penerimaan Peserta Didik Baru (Publik)
 * Website Profil SMA IT Al-Fadhilah Pekanbaru — Data Statis
 */

$current_page = 'spmb';
$page_title   = 'SPMB – Penerimaan Peserta Didik Baru';

require_once 'includes/header_public.php';
?>

<!-- ═══════════════════════════════════════════════════════════════
     SECTION 1 — HERO BANNER CARD
════════════════════════════════════════════════════════════════ -->
<section class="py-5" style="background-color: #F8FAFC;">
    <div class="container">
        <div class="rounded-4 p-4 p-md-5 text-center" style="background-color: #E0F2FE; border: 1.5px solid #BAE6FD;">

            <!-- Badge -->
            <span class="badge rounded-pill fw-semibold px-4 py-2 mb-3 d-inline-block"
                  style="background:#1F4172; color:#fff; letter-spacing:0.06em; font-size:0.78rem;">
                <i class="bi bi-megaphone-fill me-1"></i> PENERIMAAN RESMI
            </span>

            <!-- Label atas -->
            <p class="fw-semibold mb-1" style="color:#0369A1; font-size:1rem; letter-spacing:0.02em;">
                Informasi Pendaftaran Murid Baru
            </p>

            <!-- H1 Utama -->
            <h1 class="fw-bold my-2" style="color:#1F4172; font-size: clamp(1.8rem, 5vw, 3rem);">
                Tahun Ajaran 2026/2027
            </h1>

            <!-- Subtitle / Periode -->
            <div class="d-inline-flex align-items-center gap-2 px-4 py-2 rounded-pill mb-4"
                 style="background:#fff; border: 1.5px solid #BAE6FD;">
                <i class="bi bi-calendar-range-fill" style="color:#0EA5E9;"></i>
                <span class="fw-semibold" style="color:#0369A1;">
                    Dibuka: 1 November 2026 – 31 Desember 2026
                </span>
            </div>

            <!-- Countdown label -->
            <div class="d-flex flex-wrap justify-content-center gap-3 mt-2">
                <a href="#alur-pendaftaran"
                   class="btn btn-primary btn-lg px-5 fw-bold shadow-sm">
                    <i class="bi bi-pencil-square me-2"></i>Mulai Pendaftaran
                </a>
                <a href="#syarat-pendaftaran"
                   class="btn btn-outline-primary btn-lg px-5 fw-semibold">
                    <i class="bi bi-file-earmark-text me-2"></i>Lihat Syarat
                </a>
            </div>

        </div>
    </div>
</section>
<!-- /HERO SPMB -->


<!-- ═══════════════════════════════════════════════════════════════
     SECTION 2 — ALUR PENDAFTARAN
════════════════════════════════════════════════════════════════ -->
<section id="alur-pendaftaran" class="py-5">
    <div class="container">

        <!-- Heading -->
        <div class="text-center mb-5">
            <span class="badge rounded-pill fw-semibold px-4 py-2 mb-2"
                  style="background:#EBF3FA; color:#2563EB; letter-spacing:0.06em; font-size:0.78rem;">
                <i class="bi bi-signpost-split-fill me-1"></i> TAHAPAN
            </span>
            <h2 class="fw-bold" style="color:#1F4172;">Alur Pendaftaran</h2>
            <p class="text-muted mx-auto" style="max-width:520px;">
                Ikuti langkah-langkah berikut untuk menyelesaikan proses pendaftaran
                secara mudah dan teratur.
            </p>
        </div>

        <?php
        $langkah = [
            [
                'no'     => 1,
                'judul'  => 'Pengambilan Formulir',
                'deskripsi' => 'Ambil formulir pendaftaran secara langsung di Kantor Tata Usaha SMA IT Al-Fadhilah atau unduh dari website resmi sekolah pada hari dan jam kerja.',
                'icon'   => 'bi-file-earmark-arrow-down-fill',
                'warna'  => '#2563EB',
            ],
            [
                'no'     => 2,
                'judul'  => 'Pengisian & Penyerahan Formulir',
                'deskripsi' => 'Isi formulir dengan lengkap dan benar, lampirkan seluruh berkas persyaratan yang telah ditentukan, kemudian serahkan ke Panitia SPMB.',
                'icon'   => 'bi-pencil-fill',
                'warna'  => '#0369A1',
            ],
            [
                'no'     => 3,
                'judul'  => 'Seleksi Administrasi',
                'deskripsi' => 'Panitia akan memverifikasi kelengkapan dan keabsahan dokumen yang diserahkan. Calon siswa akan dihubungi jika terdapat dokumen yang perlu dilengkapi.',
                'icon'   => 'bi-search',
                'warna'  => '#7C3AED',
            ],
            [
                'no'     => 4,
                'judul'  => 'Tes Seleksi & Wawancara',
                'deskripsi' => 'Calon siswa yang lolos seleksi administrasi akan mengikuti tes tertulis (Matematika, Bahasa Indonesia, Tahfiz) dan wawancara bersama orang tua/wali.',
                'icon'   => 'bi-clipboard2-check-fill',
                'warna'  => '#16A34A',
            ],
            [
                'no'     => 5,
                'judul'  => 'Pengumuman & Daftar Ulang',
                'deskripsi' => 'Hasil seleksi diumumkan melalui website dan papan pengumuman sekolah. Calon siswa yang dinyatakan lulus wajib melakukan daftar ulang sesuai jadwal.',
                'icon'   => 'bi-check-circle-fill',
                'warna'  => '#1F4172',
            ],
        ];
        ?>

        <div class="d-flex flex-column gap-3 position-relative">
            <!-- Garis penghubung vertikal (hanya tampil di layar md ke atas) -->
            <div class="d-none d-md-block position-absolute"
                 style="left:27px; top:50px; bottom:50px; width:2px; background: linear-gradient(to bottom, #2563EB, #1F4172); opacity:0.2; z-index:0;">
            </div>

            <?php foreach ($langkah as $l): ?>
            <div class="card border-0 shadow-sm rounded-4 position-relative langkah-card"
                 style="z-index:1;">
                <div class="card-body p-4">
                    <div class="d-flex align-items-start gap-4">

                        <!-- Nomor Lingkaran -->
                        <div class="flex-shrink-0">
                            <div class="d-flex align-items-center justify-content-center rounded-circle fw-bold text-white shadow-sm"
                                 style="width:56px; height:56px; background:<?= $l['warna'] ?>; font-size:1.3rem; flex-shrink:0;">
                                <?= $l['no'] ?>
                            </div>
                        </div>

                        <!-- Konten -->
                        <div class="flex-grow-1">
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <i class="<?= $l['icon'] ?> fs-5" style="color:<?= $l['warna'] ?>;"></i>
                                <h5 class="fw-bold mb-0" style="color:#1F4172;"><?= $l['judul'] ?></h5>
                            </div>
                            <p class="mb-0 small" style="color:#475569; line-height:1.75;">
                                <?= $l['deskripsi'] ?>
                            </p>
                        </div>

                        <!-- Badge Langkah -->
                        <div class="flex-shrink-0 d-none d-sm-block">
                            <span class="badge rounded-pill small fw-semibold px-3 py-1"
                                  style="background:<?= $l['warna'] ?>1A; color:<?= $l['warna'] ?>;">
                                Langkah <?= $l['no'] ?>
                            </span>
                        </div>

                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

    </div>
</section>
<!-- /ALUR PENDAFTARAN -->


<!-- ═══════════════════════════════════════════════════════════════
     SECTION 3 — SYARAT PENDAFTARAN
════════════════════════════════════════════════════════════════ -->
<section id="syarat-pendaftaran" class="py-5" style="background-color:#F8FAFC;">
    <div class="container">

        <!-- Heading -->
        <div class="text-center mb-5">
            <span class="badge rounded-pill fw-semibold px-4 py-2 mb-2"
                  style="background:#FEF9C3; color:#CA8A04; letter-spacing:0.06em; font-size:0.78rem;">
                <i class="bi bi-folder-fill me-1"></i> PERSIAPAN BERKAS
            </span>
            <h2 class="fw-bold" style="color:#1F4172;">Syarat Pendaftaran</h2>
            <p class="text-muted mx-auto" style="max-width:520px;">
                Pastikan seluruh dokumen di bawah ini telah disiapkan sebelum
                menyerahkan formulir pendaftaran.
            </p>
        </div>

        <?php
        $syarat = [
            [
                'no'     => 1,
                'judul'  => 'Mengisi Formulir Pendaftaran',
                'detail' => 'Mengisi formulir pendaftaran calon murid baru secara lengkap dan valid.',
                'icon'   => 'bi-pencil-square',
            ],
            [
                'no'     => 2,
                'judul'  => 'Surat Keterangan Lulus (Menyusul jika belum ada)',
                'detail' => 'Surat Keterangan Lulus (SKL) resmi dari SMP/MTs sederajat asal siswa.',
                'icon'   => 'bi-file-earmark-check-fill',
            ],
            [
                'no'     => 3,
                'judul'  => 'Fotokopi Rapor Kelas IX SMP/MTs (1 Rangkap)',
                'detail' => 'Fotokopi buku rapor kelas IX semester ganjil/genap yang telah dilegalisir.',
                'icon'   => 'bi-journal-bookmark-fill',
            ],
            [
                'no'     => 4,
                'judul'  => 'Fotokopi Akta Kelahiran dan Kartu Keluarga (KK) (Masing-masing 2 lembar)',
                'detail' => 'Fotokopi akta kelahiran dan kartu keluarga yang masih berlaku dan terbaca jelas.',
                'icon'   => 'bi-person-vcard-fill',
            ],
            [
                'no'     => 5,
                'judul'  => 'Fotokopi KTP Kedua Orang Tua (Masing-masing 2 lembar)',
                'detail' => 'Fotokopi KTP ayah dan ibu atau wali calon peserta didik baru.',
                'icon'   => 'bi-card-heading',
            ],
            [
                'no'     => 6,
                'judul'  => 'Fotokopi Kartu Indonesia Pintar (KIP) (Jika ada)',
                'detail' => 'Bagi calon siswa yang memiliki kartu program bantuan pendidikan KIP dari pemerintah.',
                'icon'   => 'bi-award-fill',
            ],
        ];
        ?>

        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-header py-3 px-4 border-0"
                 style="background: linear-gradient(90deg, #1F4172, #2563EB);">
                <div class="d-flex align-items-center gap-2 text-white">
                    <i class="bi bi-folder2-open fs-5"></i>
                    <span class="fw-bold">Daftar Dokumen yang Diperlukan</span>
                    <span class="badge bg-white ms-auto fw-bold" style="color:#1F4172;">
                        6 Dokumen
                    </span>
                </div>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    <?php foreach ($syarat as $i => $s): ?>
                    <li class="list-group-item px-4 py-3 syarat-item">
                        <div class="d-flex align-items-start gap-3">

                            <!-- Nomor + Icon -->
                            <div class="flex-shrink-0 d-flex align-items-center justify-content-center rounded-2 text-white"
                                 style="width:40px; height:40px; background:#EBF3FA; min-width:40px;">
                                <i class="<?= $s['icon'] ?> fs-5" style="color:#2563EB;"></i>
                            </div>

                            <!-- Konten -->
                            <div class="flex-grow-1">
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    <span class="badge rounded-pill small fw-bold px-2"
                                          style="background:#1F4172; color:#fff;">
                                        Dokumen <?= $s['no'] ?>
                                    </span>
                                    <span class="fw-semibold" style="color:#1F4172;">
                                        <?= htmlspecialchars($s['judul']) ?>
                                    </span>
                                </div>
                                <p class="small mb-0" style="color:#64748B; line-height:1.6;">
                                    <?= htmlspecialchars($s['detail']) ?>
                                </p>
                            </div>

                            <!-- Checklist ikon -->
                            <div class="flex-shrink-0 d-none d-sm-flex align-items-center">
                                <i class="bi bi-square fs-5 text-muted checklist-icon" title="Tandai sudah disiapkan"></i>
                            </div>

                        </div>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <div class="card-footer border-0 px-4 py-3"
                 style="background:#F8FAFC;">
                <p class="small mb-0 text-muted">
                    <i class="bi bi-info-circle-fill me-1 text-primary"></i>
                    Seluruh dokumen asli wajib dibawa pada saat proses verifikasi berkas.
                    Panitia berhak menolak pendaftaran jika berkas tidak lengkap.
                </p>
            </div>
        </div>

    </div>
</section>
<!-- /SYARAT PENDAFTARAN -->


<!-- ═══════════════════════════════════════════════════════════════
     SECTION 4 — RINCIAN BIAYA
════════════════════════════════════════════════════════════════ -->
<section id="rincian-biaya" class="py-5">
    <div class="container">

        <!-- Heading -->
        <div class="text-center mb-5">
            <span class="badge rounded-pill fw-semibold px-4 py-2 mb-2"
                  style="background:#F0FDF4; color:#16A34A; letter-spacing:0.06em; font-size:0.78rem;">
                <i class="bi bi-currency-exchange me-1"></i> TRANSPARAN
            </span>
            <h2 class="fw-bold" style="color:#1F4172;">Biaya Pendidikan</h2>
            <p class="text-muted mx-auto" style="max-width:520px;">
                Rincian biaya pendidikan calon peserta didik baru disajikan secara
                transparan agar orang tua dapat mempersiapkan dengan baik.
            </p>
        </div>

        <?php
        $biaya_pendidikan = [
            [
                'no'         => 1,
                'nama'       => 'Pembangunan',
                'laki'       => 'Rp 1.500.000',
                'perempuan'  => 'Rp 1.500.000',
                'catatan_p'  => '',
                'keterangan' => 'Sekali Bayar',
            ],
            [
                'no'         => 2,
                'nama'       => 'Seragam 5 Stel',
                'laki'       => 'Rp 1.550.000',
                'perempuan'  => 'Rp 2.000.000',
                'catatan_p'  => 'Plus 6 Jilbab',
                'keterangan' => 'Sekali Bayar',
            ],
            [
                'no'         => 3,
                'nama'       => 'SPP',
                'laki'       => 'Rp 300.000',
                'perempuan'  => 'Rp 300.000',
                'catatan_p'  => '',
                'keterangan' => 'Per Bulan',
            ],
            [
                'no'         => 4,
                'nama'       => 'Ekstrakurikuler',
                'laki'       => 'Rp 500.000',
                'perempuan'  => 'Rp 500.000',
                'catatan_p'  => '',
                'keterangan' => 'Per Semester',
            ],
            [
                'no'         => 5,
                'nama'       => 'Formulir',
                'laki'       => 'Rp 150.000',
                'perempuan'  => 'Rp 150.000',
                'catatan_p'  => '',
                'keterangan' => 'Sekali Bayar',
            ],
        ];
        ?>

        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <!-- Header Card -->
            <div class="card-header py-3 px-4 border-0"
                 style="background: linear-gradient(90deg, #16A34A, #15803D);">
                <div class="d-flex align-items-center gap-2 text-white">
                    <i class="bi bi-wallet2 fs-5"></i>
                    <span class="fw-bold">Tabel Biaya Pendidikan</span>
                    <span class="badge bg-white ms-auto fw-bold" style="color:#16A34A;">
                        Tahun 2026/2027
                    </span>
                </div>
            </div>

            <!-- Tabel Biaya -->
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead style="background:#F1F5F9;">
                            <tr>
                                <th class="px-4 py-3 fw-semibold small text-uppercase text-center"
                                    style="color:#64748B; letter-spacing:.04em; width:60px;">No.</th>
                                <th class="px-3 py-3 fw-semibold small text-uppercase"
                                    style="color:#64748B; letter-spacing:.04em;">Biaya</th>
                                <th class="px-3 py-3 fw-semibold small text-uppercase text-end"
                                    style="color:#64748B; letter-spacing:.04em;">Laki-Laki</th>
                                <th class="px-3 py-3 fw-semibold small text-uppercase text-end"
                                    style="color:#64748B; letter-spacing:.04em;">Perempuan</th>
                                <th class="px-4 py-3 fw-semibold small text-uppercase text-center"
                                    style="color:#64748B; letter-spacing:.04em;">Keterangan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($biaya_pendidikan as $b): ?>
                            <tr class="biaya-row">
                                <td class="px-4 py-3 text-center">
                                    <span class="fw-semibold text-muted small">
                                        <?= $b['no'] ?>
                                    </span>
                                </td>
                                <td class="px-3 py-3">
                                    <span class="fw-semibold" style="color:#1F4172;">
                                        <?= htmlspecialchars($b['nama']) ?>
                                    </span>
                                </td>
                                <td class="px-3 py-3 text-end">
                                    <span class="fw-bold" style="color:#334155; font-size:0.95rem;">
                                        <?= $b['laki'] ?>
                                    </span>
                                </td>
                                <td class="px-3 py-3 text-end">
                                    <span class="fw-bold" style="color:#334155; font-size:0.95rem;">
                                        <?= $b['perempuan'] ?>
                                    </span>
                                    <?php if (!empty($b['catatan_p'])): ?>
                                    <small class="text-muted d-block" style="font-size:0.75rem;">(<?= htmlspecialchars($b['catatan_p']) ?>)</small>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="badge bg-light text-dark border px-3 py-1 rounded-pill small fw-normal">
                                        <?= htmlspecialchars($b['keterangan']) ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr style="background:#F0FDF4; border-top: 2px solid #BBF7D0;">
                                <td colspan="2" class="px-4 py-3">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="bi bi-calculator-fill" style="color:#16A34A;"></i>
                                        <span class="fw-bold" style="color:#1F4172;">Total Biaya Pendidikan</span>
                                    </div>
                                </td>
                                <td class="px-3 py-3 text-end">
                                    <span class="fw-bold fs-5" style="color:#16A34A;">
                                        Rp 4.000.000
                                    </span>
                                </td>
                                <td class="px-3 py-3 text-end">
                                    <span class="fw-bold fs-5" style="color:#16A34A;">
                                        Rp 4.450.000
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="text-muted fw-bold">-</span>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <!-- Footer Card -->
            <div class="card-footer border-0 px-4 py-3" style="background:#F8FAFC;">
                <div class="row g-3 align-items-center">
                    <div class="col-12 col-md-8">
                        <div class="d-flex align-items-start gap-2">
                            <i class="bi bi-info-circle-fill me-1 text-success fs-5 flex-shrink-0 mt-1"></i>
                            <div>
                                <span class="fw-bold text-dark d-block mb-1">Ketentuan Pembayaran:</span>
                                <ul class="small mb-0 ps-3 text-muted" style="line-height: 1.7;">
                                    <li>Pembayaran dapat diangsur, membayar minimal Rp 500.000 saat pengembalian formulir sebagai tanda jadi.</li>
                                    <li>Jika pembayaran awal minimal Rp 2.000.000, maka calon siswa langsung dites dan ukur seragam.</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-4 text-md-end">
                        <a href="#" class="btn btn-sm btn-outline-success fw-semibold px-3">
                            <i class="bi bi-telephone-fill me-1"></i> Hubungi Kami
                        </a>
                    </div>
                </div>
            </div>

        </div>

        <!-- CTA Daftar Sekarang -->
        <div class="text-center mt-5">
            <div class="rounded-4 p-4 p-md-5"
                 style="background: linear-gradient(135deg, #1F4172, #2563EB);">
                <i class="bi bi-mortarboard-fill text-white mb-3 d-block" style="font-size:2.5rem;"></i>
                <h4 class="fw-bold text-white mb-2">Siap Bergabung Bersama Al-Fadhilah?</h4>
                <p class="text-white opacity-75 mb-4" style="max-width:480px; margin-inline:auto;">
                    Jangan lewatkan kesempatan emas ini. Daftar sekarang dan wujudkan
                    mimpi bersama kami sebelum kuota penuh!
                </p>
                <a href="#alur-pendaftaran"
                   class="btn btn-light btn-lg px-5 fw-bold"
                   style="color:#1F4172;">
                    <i class="bi bi-pencil-square me-2"></i>Daftar Sekarang
                </a>
            </div>
        </div>

    </div>
</section>
<!-- /RINCIAN BIAYA -->


<style>
/* Hover card langkah */
.langkah-card { transition: transform .25s ease, box-shadow .25s ease; }
.langkah-card:hover { transform: translateX(6px); box-shadow: 0 8px 24px rgba(31,65,114,.1) !important; }

/* Hover row syarat */
.syarat-item { transition: background-color .2s; }
.syarat-item:hover { background-color: #F8FAFC !important; }

/* Hover row biaya */
.biaya-row { transition: background-color .2s; }
</style>

<?php require_once 'includes/footer_public.php'; ?>
