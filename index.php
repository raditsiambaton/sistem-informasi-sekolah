<?php
/**
 * index.php – Halaman Beranda (Publik)
 * Website Profil SMA IT Al-Fadhilah Pekanbaru
 *
 * Tidak mengandung koneksi database — data statis / HTML murni.
 */

$current_page = 'beranda';
$page_title   = 'Beranda';

require_once 'includes/header_public.php';
?>

<!-- ═══════════════════════════════════════════════════════════════
     SECTION 1 — HERO
════════════════════════════════════════════════════════════════ -->
<section id="hero" class="py-5 py-md-6">
    <div class="container">
        <div class="row align-items-center g-5">

            <!-- Kolom Kiri: Teks -->
            <div class="col-12 col-lg-6" data-aos="fade-right">
                <span class="badge text-uppercase fw-semibold mb-3 px-3 py-2"
                      style="background-color: #EBF3FA; color: #1F4172; letter-spacing: 0.06em; font-size: 0.75rem;">
                    <i class="bi bi-shield-shaded me-1"></i> Sekolah Islam Terpadu
                </span>

                <h1 class="fw-bold lh-sm mb-3" style="font-size: clamp(2rem, 5vw, 3rem); color: #1F4172;">
                    SMA IT<br>Al-Fadhilah
                    <span style="color: #2563EB;">Pekanbaru</span>
                </h1>

                <p class="lead mb-4" style="color: #475569; font-size: 1.05rem; line-height: 1.8;">
                    Sekolah Islam Terpadu berbasis Tahfiz Al-Qur'an yang membentuk generasi
                    berakhlak mulia, berprestasi akademis, dan berjiwa mandiri. Kami mengintegrasikan
                    kurikulum nasional dengan nilai-nilai Islam dalam lingkungan belajar yang kondusif
                    di jantung Kota Pekanbaru, Riau.
                </p>

                <div class="d-flex flex-wrap gap-3">
                    <a href="spmb.php"
                       class="btn btn-primary btn-lg px-4 fw-semibold shadow-sm">
                        <i class="bi bi-pencil-square me-2"></i>Daftar Sekarang
                    </a>
                    <a href="akademik.php"
                       class="btn btn-outline-secondary btn-lg px-4 fw-semibold">
                        <i class="bi bi-book me-2"></i>Lihat Program
                    </a>
                </div>

                <!-- Statistik Singkat -->
                <div class="row row-cols-3 g-3 mt-4">
                    <?php
                    $stats = [
                        ['icon' => 'bi-people-fill',      'angka' => '500+',  'label' => 'Siswa Aktif'],
                        ['icon' => 'bi-person-workspace', 'angka' => '40+',   'label' => 'Tenaga Pengajar'],
                        ['icon' => 'bi-trophy-fill',      'angka' => '100+',  'label' => 'Prestasi'],
                    ];
                    foreach ($stats as $s): ?>
                    <div class="col">
                        <div class="p-3 rounded-3 text-center border border-1"
                             style="background:#F8FAFC; border-color:#E2E8F0 !important;">
                            <i class="<?= $s['icon'] ?> fs-4 mb-1 d-block" style="color:#1F4172;"></i>
                            <div class="fw-bold fs-5" style="color:#1F4172;"><?= $s['angka'] ?></div>
                            <div class="small text-muted"><?= $s['label'] ?></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Kolom Kanan: Gambar -->
            <div class="col-12 col-lg-6 text-center">
                <div class="position-relative d-inline-block">
                    <!-- Dekorasi lingkaran di belakang gambar -->
                    <div class="position-absolute top-0 start-0 translate-middle"
                         style="width:120px; height:120px; border-radius:50%;
                                background:rgba(31,65,114,0.07); z-index:0;">
                    </div>
                    <div class="position-absolute bottom-0 end-0 translate-middle-y"
                         style="width:80px; height:80px; border-radius:50%;
                                background:rgba(37,99,235,0.08); z-index:0;">
                    </div>

                    <img src="assets/images/profil/hero-sekolah.jpg"
                         alt="Foto Sekolah SMA IT Al-Fadhilah Pekanbaru"
                         class="img-fluid rounded-4 shadow-sm w-100 object-fit-cover position-relative"
                         style="max-height: 400px; z-index: 1;">
                </div>
            </div>

        </div>
    </div>
</section>
<!-- /HERO -->


<!-- ═══════════════════════════════════════════════════════════════
     SECTION 2 — VISI & MISI
════════════════════════════════════════════════════════════════ -->
<section id="visi-misi" class="py-5">
    <div class="container">

        <div class="text-center mb-4">
            <h2 class="fw-bold" style="color:#1F4172;">Visi &amp; Misi Sekolah</h2>
            <p class="text-muted">Pondasi nilai dan arah perjalanan SMA IT Al-Fadhilah Pekanbaru.</p>
        </div>

        <!-- Card Utama -->
        <div class="rounded-4 p-4 p-md-5" style="background-color: #F1F5F9;">
            <div class="row align-items-center g-4 g-lg-5">

                <!-- Kolom Kiri: Teks Visi & Misi -->
                <div class="col-12 col-lg-7">

                    <!-- VISI -->
                    <div class="mb-4">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span class="d-flex align-items-center justify-content-center rounded-2"
                                  style="width:36px; height:36px; background:#1F4172;">
                                <i class="bi bi-eye-fill text-white fs-6"></i>
                            </span>
                            <h3 class="fw-bold mb-0 fs-5 text-uppercase" style="color:#1F4172; letter-spacing:0.06em;">
                                Visi
                            </h3>
                        </div>
                        <p class="mb-0 fst-italic ps-5" style="color:#334155; line-height:1.8; border-left: 3px solid #2563EB; padding-left: 1rem !important;">
                            "Menjadi lembaga pendidikan Islam terpadu yang unggul dalam membentuk
                            generasi Qur'ani, berprestasi, dan berakhlakul karimah sebagai bekal
                            kehidupan dunia dan akhirat."
                        </p>
                    </div>

                    <!-- MISI -->
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <span class="d-flex align-items-center justify-content-center rounded-2"
                                  style="width:36px; height:36px; background:#2563EB;">
                                <i class="bi bi-bullseye text-white fs-6"></i>
                            </span>
                            <h3 class="fw-bold mb-0 fs-5 text-uppercase" style="color:#1F4172; letter-spacing:0.06em;">
                                Misi
                            </h3>
                        </div>

                        <?php
                        $misi = [
                            'Menyelenggarakan program Tahfiz Al-Qur\'an yang terstruktur dan terintegrasi dengan kurikulum akademik nasional.',
                            'Membangun lingkungan belajar yang islami, disiplin, dan kondusif bagi pengembangan potensi seluruh peserta didik.',
                            'Mengembangkan kompetensi tenaga pendidik yang profesional, berdedikasi, dan berlandaskan nilai-nilai Islam.',
                            'Menjalin kemitraan strategis dengan orang tua, masyarakat, dan instansi terkait untuk mendukung mutu pendidikan.',
                            'Mempersiapkan lulusan yang siap melanjutkan ke perguruan tinggi terbaik dan berkontribusi positif bagi masyarakat.',
                        ];
                        ?>
                        <ul class="list-unstyled mb-0 ps-1">
                            <?php foreach ($misi as $i => $item): ?>
                            <li class="d-flex align-items-start gap-3 mb-3">
                                <span class="d-flex align-items-center justify-content-center flex-shrink-0 rounded-circle fw-bold small"
                                      style="width:28px; height:28px; background:#EBF3FA; color:#1F4172; border: 1.5px solid #1F4172; margin-top:2px;">
                                    <?= $i + 1 ?>
                                </span>
                                <span style="color:#334155; line-height:1.75;"><?= $item ?></span>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>

                </div>

                <!-- Kolom Kanan: Gambar Gedung -->
                <div class="col-12 col-lg-5 text-center">
                    <img src="assets/images/profil/visi-misi-sekolah.jpg"
                         alt="Gedung SMA IT Al-Fadhilah Pekanbaru"
                         class="img-fluid rounded-3 shadow-sm w-100 h-100 object-fit-cover"
                         style="min-height: 320px;">
                </div>

            </div>
        </div>

    </div>
</section>
<!-- /VISI MISI -->


<!-- ═══════════════════════════════════════════════════════════════
     SECTION 3 — KABAR TERBARU
════════════════════════════════════════════════════════════════ -->
<section id="berita" class="py-5" style="background-color: #F8FAFC;">
    <div class="container">

        <!-- Heading -->
        <div class="d-flex flex-column flex-md-row align-items-md-end justify-content-md-between gap-3 mb-4">
            <div>
                <span class="text-uppercase fw-semibold small" style="color:#2563EB; letter-spacing:0.06em;">
                    <i class="bi bi-newspaper me-1"></i>Informasi Terkini
                </span>
                <h3 class="fw-bold mb-0" style="color:#1F4172;">Kabar Terbaru dari Sekolah</h3>
            </div>
            <a href="#" class="btn btn-sm btn-outline-primary fw-semibold px-4 flex-shrink-0">
                Lihat Semua <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>

        <!-- Grid 3 Card Berita Statis -->
        <?php
        $berita = [
            [
                'img'     => 'https://placehold.co/400x250/f1f5f9/475569?text=Berita',
                'tanggal' => '3 November 2026',
                'judul'   => 'SMA IT Al-Fadhilah Raih Juara 1 Lomba Tahfiz Tingkat Provinsi Riau',
                'ringkas' => 'Alhamdulillah, siswa-siswi SMA IT Al-Fadhilah berhasil meraih juara pertama dalam ajang Musabaqah Hifzil Qur\'an tingkat Provinsi Riau yang diselenggarakan di Pekanbaru.',
                'badge'   => 'Prestasi',
                'badge_warna' => '#16A34A',
            ],
            [
                'img'     => 'https://placehold.co/400x250/f1f5f9/475569?text=Berita',
                'tanggal' => '21 Oktober 2026',
                'judul'   => 'Penerimaan Peserta Didik Baru (SPMB) Tahun Ajaran 2027/2028 Resmi Dibuka',
                'ringkas' => 'SMA IT Al-Fadhilah Pekanbaru dengan bangga mengumumkan pembukaan pendaftaran siswa baru untuk tahun ajaran 2027/2028. Daftar sekarang dan bergabunglah bersama keluarga besar Al-Fadhilah.',
                'badge'   => 'Pengumuman',
                'badge_warna' => '#D97706',
            ],
            [
                'img'     => 'https://placehold.co/400x250/f1f5f9/475569?text=Berita',
                'tanggal' => '5 Oktober 2026',
                'judul'   => 'Workshop Peningkatan Kompetensi Guru: Implementasi Kurikulum Merdeka 2026',
                'ringkas' => 'Seluruh tenaga pendidik SMA IT Al-Fadhilah mengikuti workshop intensif selama dua hari tentang implementasi Kurikulum Merdeka dalam pembelajaran berbasis nilai-nilai Islam.',
                'badge'   => 'Kegiatan',
                'badge_warna' => '#1F4172',
            ],
        ];
        ?>

        <div class="row row-cols-1 row-cols-md-3 g-4">
            <?php foreach ($berita as $b): ?>
            <div class="col">
                <article class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden berita-card">

                    <!-- Gambar -->
                    <div class="overflow-hidden" style="height: 200px;">
                        <img src="<?= $b['img'] ?>"
                             alt="<?= htmlspecialchars($b['judul']) ?>"
                             class="card-img-top w-100 h-100 berita-img"
                             style="object-fit: cover; transition: transform 0.4s ease;">
                    </div>

                    <div class="card-body d-flex flex-column p-4">

                        <!-- Badge Kategori & Tanggal -->
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="badge rounded-pill fw-semibold small px-3 py-1"
                                  style="background-color: <?= $b['badge_warna'] ?>1A; color: <?= $b['badge_warna'] ?>;">
                                <?= $b['badge'] ?>
                            </span>
                            <small class="text-muted">
                                <i class="bi bi-calendar3 me-1"></i><?= $b['tanggal'] ?>
                            </small>
                        </div>

                        <!-- Judul -->
                        <h5 class="card-title fw-bold mb-2 lh-sm" style="color:#1F4172; font-size:1rem;">
                            <?= htmlspecialchars($b['judul']) ?>
                        </h5>

                        <!-- Ringkasan -->
                        <p class="card-text small flex-grow-1 mb-3" style="color:#64748B; line-height:1.7;">
                            <?= htmlspecialchars($b['ringkas']) ?>
                        </p>

                        <!-- Link Baca Selengkapnya -->
                        <a href="#"
                           class="d-inline-flex align-items-center gap-1 fw-semibold small text-decoration-none baca-link"
                           style="color:#2563EB;">
                            Baca Selengkapnya
                            <i class="bi bi-arrow-right transition-arrow"></i>
                        </a>

                    </div>
                </article>
            </div>
            <?php endforeach; ?>
        </div>

    </div>
</section>
<!-- /BERITA -->


<!-- ═══════════════════════════════════════════════════════════════
     SECTION 4 — CTA (Call to Action) DAFTAR
════════════════════════════════════════════════════════════════ -->
<section id="cta" class="py-5">
    <div class="container">
        <div class="rounded-4 p-4 p-md-5 text-center text-white"
             style="background: linear-gradient(135deg, #1F4172 0%, #2563EB 100%);">
            <i class="bi bi-mortarboard-fill mb-3 d-block" style="font-size: 2.5rem;"></i>
            <h3 class="fw-bold mb-2">Bergabunglah Bersama Kami!</h3>
            <p class="mb-4 opacity-75" style="max-width:520px; margin-inline:auto;">
                Daftarkan putra-putri Anda ke SMA IT Al-Fadhilah Pekanbaru dan wujudkan
                generasi Qur'ani yang berprestasi dan berakhlak mulia.
            </p>
            <div class="d-flex flex-wrap justify-content-center gap-3">
                <a href="spmb.php"
                   class="btn btn-light btn-lg px-5 fw-bold"
                   style="color:#1F4172;">
                    <i class="bi bi-pencil-square me-2"></i>Daftar Sekarang
                </a>
                <a href="akademik.php"
                   class="btn btn-outline-light btn-lg px-5 fw-semibold">
                    <i class="bi bi-info-circle me-2"></i>Info Lebih Lanjut
                </a>
            </div>
        </div>
    </div>
</section>
<!-- /CTA -->


<?php
// ── Tambahkan style interaktif card berita inline ──────────────────
?>
<style>
    /* Hero spacing */
    .py-md-6 { padding-top: 5rem !important; padding-bottom: 5rem !important; }

    /* Hover efek card berita */
    .berita-card {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    .berita-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 12px 32px rgba(31, 65, 114, 0.12) !important;
    }
    .berita-card:hover .berita-img {
        transform: scale(1.06);
    }
    .baca-link:hover {
        color: #1F4172 !important;
    }
    .baca-link:hover .transition-arrow {
        transform: translateX(4px);
        transition: transform 0.2s;
    }
    .transition-arrow {
        transition: transform 0.2s;
    }
</style>

<?php require_once 'includes/footer_public.php'; ?>
