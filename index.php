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
                    Sekolah Islam yang memadukan kecerdasan intelektual, emosional, dan spiritual melalui integrasi kurikulum nasional, 
                    kurikulum Kementerian Agama (Kemenag), serta kekhasan pendidikan Islam terpadu. Perpaduan ini dirancang untuk mewujudkan 
                    generasi berjiwa pemimpin islami, berprestasi, serta berguna bagi masyarakat, agama, dan bangsa.

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
                            "Mencetak pemimpin masa depan yang religius serta mewujudkan warga sekolah yang peduli terhadap ibadah, 
                            akademik, dan lingkungan."
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
                            'Menerapkan konsep keislaman di dalam keseharian.',
                            'Memadupadankan kurikulum dinas dengan nilai-nilai Al-Quran.',
                            'Menjadikan peserta didik berguna bagi masyarakat.',
                            'Membangun mental pemimpin sejak dini.',
                            'Memberikan pendidikan berupa sains dan keterampilan (skill) sebagai persiapan ke jenjang pendidikan yang lebih tinggi.',
                            'Menciptakan lingkungan dan suasana sekolah yang agamis.',
                            'Mengembangkan seluruh komponen sekolah secara optimal, baik di bidang akademik maupun nonakademik..',
                            'Meningkatkan mutu pendidikan yang mengintegrasikan sistem nilai, agama, dan budaya dengan ilmu pengetahuan dan teknologi.',
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
     SECTION 3 — CTA (Call to Action) DAFTAR
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

<style>
    /* Hero spacing */
    .py-md-6 { padding-top: 5rem !important; padding-bottom: 5rem !important; }
</style>

<?php require_once 'includes/footer_public.php'; ?>
