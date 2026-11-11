<?php
/**
 * akademik.php – Halaman Program Akademik (Publik)
 * Website Profil SMA IT Al-Fadhilah Pekanbaru — Data Statis
 */

$current_page = 'akademik';
$page_title   = 'Akademik';

require_once 'includes/header_public.php';
?>

<!-- ═══════════════════════════════════════════════════════════════
     PAGE HERO BANNER
════════════════════════════════════════════════════════════════ -->
<section class="py-4" style="background: linear-gradient(135deg, #1F4172 0%, #2563EB 100%);">
    <div class="container py-3 text-white text-center">
        <span class="badge bg-white bg-opacity-25 text-white fw-semibold mb-2 px-3 py-2 small">
            <i class="bi bi-book-fill me-1"></i> Program Pendidikan
        </span>
        <h1 class="fw-bold mb-1" style="font-size: clamp(1.6rem, 4vw, 2.5rem);">Program Akademik</h1>
        <p class="mb-0 opacity-75">SMA IT Al-Fadhilah Pekanbaru</p>
    </div>
</section>


<!-- ═══════════════════════════════════════════════════════════════
     SECTION 1 — PROGRAM AKADEMIK
════════════════════════════════════════════════════════════════ -->
<section id="program-akademik" class="py-5">
    <div class="container">

        <!-- Heading -->
        <div class="text-center mb-5">
            <span class="badge rounded-pill fw-semibold px-3 py-2 mb-2"
                  style="background:#EBF3FA; color:#2563EB; letter-spacing:0.05em;">
                <i class="bi bi-mortarboard me-1"></i> KURIKULUM
            </span>
            <h2 class="fw-bold" style="color:#1F4172;">Program Akademik Sekolah</h2>
            <p class="text-muted mx-auto" style="max-width:580px;">
                Kurikulum terintegrasi antara standar nasional dan nilai-nilai Islam
                untuk membentuk lulusan yang kompeten dan berakhlak.
            </p>
        </div>

        <?php
        $programs = [
            [
                'icon'  => 'bi-journal-bookmark-fill',
                'warna' => '#1F4172',
                'judul' => 'Tahfiz Al-Qur\'an',
                'deskripsi' => 'Program unggulan hafalan Al-Qur\'an terstruktur dengan target minimal 5 juz selama masa studi, dibimbing oleh pengajar hafiz bersanad.',
                'img'   => 'https://placehold.co/400x250/EBF3FA/1F4172?text=Tahfiz',
            ],
            [
                'icon'  => 'bi-calculator-fill',
                'warna' => '#0369A1',
                'judul' => 'MIPA (Matematika & IPA)',
                'deskripsi' => 'Jurusan saintek dengan pembelajaran mendalam pada Matematika, Fisika, Kimia, dan Biologi. Disiapkan untuk seleksi PTN favorit.',
                'img'   => 'https://placehold.co/400x250/EBF3FA/0369A1?text=MIPA',
            ],
            [
                'icon'  => 'bi-graph-up-arrow',
                'warna' => '#047857',
                'judul' => 'IPS (Ilmu Pengetahuan Sosial)',
                'deskripsi' => 'Membangun pemahaman mendalam tentang Ekonomi, Geografi, Sosiologi, dan Sejarah dengan pendekatan kritis dan analitis.',
                'img'   => 'https://placehold.co/400x250/ECFDF5/047857?text=IPS',
            ],
            [
                'icon'  => 'bi-translate',
                'warna' => '#9333EA',
                'judul' => 'Bahasa & Sastra',
                'deskripsi' => 'Program pengembangan kemampuan linguistik mencakup Bahasa Indonesia, Bahasa Inggris, dan Bahasa Arab sebagai bahasa Al-Qur\'an.',
                'img'   => 'https://placehold.co/400x250/F5F3FF/9333EA?text=Bahasa',
            ],
            [
                'icon'  => 'bi-laptop-fill',
                'warna' => '#D97706',
                'judul' => 'Teknologi Informasi',
                'deskripsi' => 'Pembelajaran literasi digital, dasar pemrograman, dan kecakapan teknologi untuk mempersiapkan siswa menghadapi era industri 4.0.',
                'img'   => 'https://placehold.co/400x250/FFFBEB/D97706?text=Teknologi',
            ],
        ];
        ?>

        <!-- Grid 5 Card Program (2-3 layout) -->
        <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-3 g-4 mb-2">
            <?php foreach (array_slice($programs, 0, 3) as $p): ?>
            <div class="col">
                <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden program-card">
                    <div class="overflow-hidden" style="height:180px;">
                        <img src="<?= $p['img'] ?>"
                             alt="<?= htmlspecialchars($p['judul']) ?>"
                             class="card-img-top w-100 h-100 program-img"
                             style="object-fit:cover; transition:transform .4s ease;">
                    </div>
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <i class="<?= $p['icon'] ?> fs-5" style="color:<?= $p['warna'] ?>;"></i>
                            <h5 class="fw-bold mb-0 fs-6" style="color:#1F4172;"><?= $p['judul'] ?></h5>
                        </div>
                        <p class="small mb-0" style="color:#64748B; line-height:1.7;"><?= $p['deskripsi'] ?></p>
                    </div>
                    <div class="card-footer bg-transparent border-0 pb-4 px-4">
                        <a href="#" class="small fw-semibold text-decoration-none" style="color:<?= $p['warna'] ?>;">
                            Selengkapnya <i class="bi bi-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="row row-cols-1 row-cols-sm-2 g-4 justify-content-center">
            <?php foreach (array_slice($programs, 3) as $p): ?>
            <div class="col col-lg-4">
                <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden program-card">
                    <div class="overflow-hidden" style="height:180px;">
                        <img src="<?= $p['img'] ?>"
                             alt="<?= htmlspecialchars($p['judul']) ?>"
                             class="card-img-top w-100 h-100 program-img"
                             style="object-fit:cover; transition:transform .4s ease;">
                    </div>
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <i class="<?= $p['icon'] ?> fs-5" style="color:<?= $p['warna'] ?>;"></i>
                            <h5 class="fw-bold mb-0 fs-6" style="color:#1F4172;"><?= $p['judul'] ?></h5>
                        </div>
                        <p class="small mb-0" style="color:#64748B; line-height:1.7;"><?= $p['deskripsi'] ?></p>
                    </div>
                    <div class="card-footer bg-transparent border-0 pb-4 px-4">
                        <a href="#" class="small fw-semibold text-decoration-none" style="color:<?= $p['warna'] ?>;">
                            Selengkapnya <i class="bi bi-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

    </div>
</section>
<!-- /PROGRAM AKADEMIK -->


<!-- ═══════════════════════════════════════════════════════════════
     SECTION 2 — EKSTRAKURIKULER
════════════════════════════════════════════════════════════════ -->
<section id="ekskul" class="py-5" style="background-color:#F8FAFC;">
    <div class="container">

        <!-- Heading -->
        <div class="text-center mb-5">
            <span class="badge rounded-pill fw-semibold px-3 py-2 mb-2"
                  style="background:#EBF3FA; color:#2563EB; letter-spacing:0.05em;">
                <i class="bi bi-stars me-1"></i> KEGIATAN SISWA
            </span>
            <h2 class="fw-bold" style="color:#1F4172;">Ekstrakurikuler</h2>
            <p class="text-muted mx-auto" style="max-width:560px;">
                Pengembangan bakat dan potensi siswa di luar jam akademik melalui
                kegiatan terstruktur yang dipandu oleh pelatih berpengalaman.
            </p>
        </div>

        <?php
        $ekskul = [
            [
                'label'   => 'Taekwondo',
                'img'     => 'https://placehold.co/400x300/334155/334155?text=Taekwondo',
                'deskripsi' => 'Melatih kedisiplinan, ketangkasan, dan bela diri islami. Tim Taekwondo Al-Fadhilah telah meraih berbagai medali di tingkat kota dan provinsi.',
                'jadwal'  => 'Selasa & Kamis, 15.30 – 17.30 WIB',
                'icon'    => 'bi-trophy-fill',
                'warna'   => '#1F4172',
            ],
            [
                'label'   => 'Pramuka',
                'img'     => 'https://placehold.co/400x300/334155/334155?text=Pramuka',
                'deskripsi' => 'Membentuk karakter kepemimpinan, kemandirian, dan jiwa sosial melalui kegiatan kepramukaan berbasis nilai-nilai Islam dan keindonesiaan.',
                'jadwal'  => 'Jumat, 14.00 – 16.00 WIB',
                'icon'    => 'bi-geo-alt-fill',
                'warna'   => '#16A34A',
            ],
            [
                'label'   => 'Robotika & Coding',
                'img'     => 'https://placehold.co/400x300/334155/334155?text=Robotika',
                'deskripsi' => 'Mengasah logika komputasional dan kreativitas teknologi siswa melalui desain robot dan pemrograman dasar, mempersiapkan talenta digital masa depan.',
                'jadwal'  => 'Sabtu, 08.00 – 11.00 WIB',
                'icon'    => 'bi-cpu-fill',
                'warna'   => '#D97706',
            ],
        ];
        ?>

        <div class="d-flex flex-column gap-4">
            <?php foreach ($ekskul as $e): ?>
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden ekskul-card">
                <div class="row g-0">

                    <!-- Thumbnail dengan Overlay + Label -->
                    <div class="col-12 col-md-4 position-relative" style="min-height:220px;">
                        <img src="<?= $e['img'] ?>"
                             alt="Ekstrakurikuler <?= $e['label'] ?>"
                             class="w-100 h-100 ekskul-img"
                             style="object-fit:cover; transition:transform .4s ease; display:block;">
                        <!-- Overlay gelap -->
                        <div class="position-absolute top-0 start-0 w-100 h-100"
                             style="background: linear-gradient(to top, rgba(0,0,0,.65) 0%, rgba(0,0,0,.15) 60%, transparent 100%);"></div>
                        <!-- Label di pojok kiri bawah -->
                        <div class="position-absolute bottom-0 start-0 p-3">
                            <span class="badge px-3 py-2 fw-bold fs-6"
                                  style="background-color: <?= $e['warna'] ?>; color:#fff; border-radius:8px; font-size:0.85rem !important;">
                                <i class="<?= $e['icon'] ?> me-1"></i><?= $e['label'] ?>
                            </span>
                        </div>
                    </div>

                    <!-- Konten -->
                    <div class="col-12 col-md-8">
                        <div class="card-body p-4 p-lg-5 d-flex flex-column justify-content-center h-100">
                            <h4 class="fw-bold mb-2" style="color:#1F4172;"><?= $e['label'] ?></h4>
                            <p class="mb-3" style="color:#475569; line-height:1.75;"><?= $e['deskripsi'] ?></p>
                            <div class="d-flex align-items-center gap-2 text-muted small">
                                <i class="bi bi-clock-fill" style="color:<?= $e['warna'] ?>;"></i>
                                <span><?= $e['jadwal'] ?></span>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
            <?php endforeach; ?>
        </div>

    </div>
</section>
<!-- /EKSTRAKURIKULER -->


<!-- ═══════════════════════════════════════════════════════════════
     SECTION 3 — PENCAPAIAN / PRESTASI
════════════════════════════════════════════════════════════════ -->
<section id="pencapaian" class="py-5">
    <div class="container">

        <!-- Heading -->
        <div class="text-center mb-5">
            <span class="badge rounded-pill fw-semibold px-3 py-2 mb-2"
                  style="background:#FEF9C3; color:#CA8A04; letter-spacing:0.05em;">
                <i class="bi bi-trophy-fill me-1"></i> KEBANGGAAN SEKOLAH
            </span>
            <h2 class="fw-bold" style="color:#1F4172;">Pencapaian</h2>
            <p class="text-muted mx-auto" style="max-width:560px;">
                Dokumentasi prestasi dan penghargaan yang telah diraih siswa-siswi
                SMA IT Al-Fadhilah di berbagai bidang.
            </p>
        </div>

        <?php
        $pencapaian = [
            ['img' => 'https://placehold.co/400x300/FEF9C3/92400E?text=Juara+Tahfiz',       'judul' => 'Juara 1 MHQ Tingkat Provinsi 2026',    'ket' => 'Bidang Tahfiz Al-Qur\'an'],
            ['img' => 'https://placehold.co/400x300/EBF3FA/1F4172?text=OSN+MIPA',           'judul' => 'Medali Perak OSN Kimia Tingkat Nasional','ket' => 'Olimpiade Sains Nasional 2025'],
            ['img' => 'https://placehold.co/400x300/F0FDF4/14532D?text=Taekwondo+Emas',     'judul' => 'Medali Emas Taekwondo Porkot 2026',     'ket' => 'Olahraga Bela Diri'],
            ['img' => 'https://placehold.co/400x300/F5F3FF/4C1D95?text=Lomba+Debat',        'judul' => 'Juara 2 Debat Bahasa Inggris Provinsi', 'ket' => 'Bidang Bahasa & Sastra'],
            ['img' => 'https://placehold.co/400x300/FFF7ED/9A3412?text=Robotika+Juara',     'judul' => 'Juara 1 Lomba Robotika Wilayah Riau',   'ket' => 'Bidang Teknologi & Sains'],
            ['img' => 'https://placehold.co/400x300/FFF1F2/881337?text=Sekolah+Terbaik',    'judul' => 'Sekolah IT Terbaik Pekanbaru 2026',     'ket' => 'Penghargaan Dinas Pendidikan'],
        ];
        ?>

        <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-3 g-4">
            <?php foreach ($pencapaian as $item): ?>
            <div class="col">
                <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden prestasi-card">
                    <div class="position-relative overflow-hidden" style="height:200px;">
                        <img src="<?= $item['img'] ?>"
                             alt="<?= htmlspecialchars($item['judul']) ?>"
                             class="w-100 h-100 prestasi-img"
                             style="object-fit:cover; transition:transform .4s ease;">
                        <div class="position-absolute top-0 start-0 w-100 h-100"
                             style="background:linear-gradient(to top, rgba(0,0,0,.5) 0%, transparent 55%);"></div>
                    </div>
                    <div class="card-body p-4">
                        <div class="d-flex align-items-start gap-2">
                            <i class="bi bi-trophy-fill mt-1 flex-shrink-0" style="color:#CA8A04;"></i>
                            <div>
                                <h6 class="fw-bold mb-1 lh-sm" style="color:#1F4172;">
                                    <?= htmlspecialchars($item['judul']) ?>
                                </h6>
                                <span class="badge small fw-normal"
                                      style="background:#F1F5F9; color:#475569;">
                                    <?= $item['ket'] ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

    </div>
</section>
<!-- /PENCAPAIAN -->


<style>
/* Hover efek card program */
.program-card { transition: transform .3s ease, box-shadow .3s ease; }
.program-card:hover { transform: translateY(-5px); box-shadow: 0 12px 32px rgba(31,65,114,.12) !important; }
.program-card:hover .program-img { transform: scale(1.06); }

/* Hover efek card ekskul */
.ekskul-card { transition: box-shadow .3s ease; }
.ekskul-card:hover { box-shadow: 0 10px 30px rgba(31,65,114,.12) !important; }
.ekskul-card:hover .ekskul-img { transform: scale(1.05); }

/* Hover efek card prestasi */
.prestasi-card { transition: transform .3s ease, box-shadow .3s ease; }
.prestasi-card:hover { transform: translateY(-5px); box-shadow: 0 12px 30px rgba(31,65,114,.12) !important; }
.prestasi-card:hover .prestasi-img { transform: scale(1.07); }
</style>

<?php require_once 'includes/footer_public.php'; ?>
