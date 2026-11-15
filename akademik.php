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
     SECTION 1 — PROGRAM PEMBINAAN UNGGULAN
════════════════════════════════════════════════════════════════ -->
<section id="program-akademik" class="py-5">
    <div class="container">

        <!-- Heading -->
        <div class="text-center mb-5">
            <span class="badge rounded-pill fw-semibold px-3 py-2 mb-2"
                  style="background:#EBF3FA; color:#2563EB; letter-spacing:0.05em;">
                <i class="bi bi-stars me-1"></i> PROGRAM UNGGULAN
            </span>
            <h2 class="fw-bold" style="color:#1F4172;">Program Pembinaan Unggulan</h2>
            <p class="text-muted mx-auto" style="max-width:580px;">
                Program pembentukan karakter, kepemimpinan, dan kualitas akademik
                siswa terintegrasi.
            </p>
        </div>

        <?php
        $programs = [
            [
                'img'       => 'https://placehold.co/600x380/e2e8f0/1e293b?text=Tahfiz+%26+Al-Quran',
                'tag'       => 'Pilar Utama',
                'judul'     => "Tahfiz & Al-Qur'an",
                'deskripsi' => "Program pembentukan karakter Qur'ani melalui tahsin perbaikan bacaan, tasmi' simakan hafalan, mutaba'ah harian, serta bimbingan intensif target minimal 6 Juz.",
            ],
            [
                'img'       => 'https://placehold.co/600x380/e2e8f0/1e293b?text=Leadership+%26+Kepemimpinan',
                'tag'       => 'Karakter',
                'judul'     => 'Leadership & Kepemimpinan',
                'deskripsi' => "Mencetak calon pemimpin muslim melalui program Leaders' Camp, Latihan Dasar Kepemimpinan (LDK), kegiatan kepanduan, dan kedisiplinan berakhlak mulia.",
            ],
            [
                'img'       => 'https://placehold.co/600x380/e2e8f0/1e293b?text=Bahasa+%26+Public+Speaking',
                'tag'       => 'Komunikasi',
                'judul'     => 'Bahasa & Public Speaking',
                'deskripsi' => 'Membangun kepercayaan diri dan kecakapan komunikasi global siswa melalui English & Arabic Day, muhadharah pidato dua bahasa, dan festival literasi.',
            ],
            [
                'img'       => 'https://placehold.co/600x380/e2e8f0/1e293b?text=Sukses+SNBT+%26+PTN+Favorit',
                'tag'       => 'Akademik',
                'judul'     => 'Sukses SNBT & PTN Favorit',
                'deskripsi' => 'Mempersiapkan siswa menembus PTN dan Perguruan Tinggi Kedinasan favorit melalui bimbingan intensif UTBK-SNBT, tryout berkala, dan konsultasi minat bakat.',
            ],
            [
                'img'       => 'https://placehold.co/600x380/e2e8f0/1e293b?text=Bina+Iman+%26+Taqwa+(BIMTAQ)',
                'tag'       => 'Spiritual',
                'judul'     => 'Bina Iman & Taqwa (BIMTAQ)',
                'deskripsi' => 'Memperkuat pondasi keimanan dan ketaqwaan siswa melalui program Malam Bina Iman dan Taqwa (MABIT), Taujihad spiritual, dan pembiasaan shalat berjamaah.',
            ],
            [
                'img'       => 'https://placehold.co/600x380/e2e8f0/1e293b?text=Sosial+%26+Karakter+Muslim',
                'tag'       => 'Sosial',
                'judul'     => 'Sosial & Karakter Muslim',
                'deskripsi' => "Membangun kepekaan sosial dan jiwa berbagi sebagai muslim sejati melalui program Jum'at Berkah sedekah mingguan, bakti sosial, dan kepedulian lingkungan hidup.",
            ],
        ];
        ?>

        <!-- Grid 6 Card — 3 Kolom × 2 Baris -->
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
            <?php foreach ($programs as $p): ?>
            <div class="col">
                <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden program-card bg-white position-relative">
                    <!-- Badge Kategori -->
                    <span class="badge bg-dark bg-opacity-75 text-white fw-medium rounded-pill px-3 py-1 small position-absolute top-0 end-0 m-3" style="z-index: 2;">
                        <?= $p['tag'] ?>
                    </span>

                    <!-- Area Foto Rencana / Placeholder -->
                    <img src="<?= $p['img'] ?>"
                         class="card-img-top program-img"
                         alt="<?= htmlspecialchars($p['judul']) ?>"
                         style="height: 220px; object-fit: cover;">

                    <!-- Card Body -->
                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-2" style="color:#1F4172;">
                            <?= htmlspecialchars($p['judul']) ?>
                        </h5>
                        <p class="mb-0 small" style="color:#64748B; line-height:1.75;">
                            <?= htmlspecialchars($p['deskripsi']) ?>
                        </p>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

    </div>
</section>
<!-- /PROGRAM PEMBINAAN UNGGULAN -->


<!-- ═══════════════════════════════════════════════════════════════
     SECTION 2 — EKSTRAKURIKULER
════════════════════════════════════════════════════════════════ -->
<section id="ekskul" class="py-5" style="background-color:#F8FAFC;">
    <div class="container-fluid px-4 px-md-5">

        <!-- Heading -->
        <div class="text-center mb-5">
            <span class="badge rounded-pill fw-semibold px-3 py-2 mb-2"
                  style="background:#EBF3FA; color:#2563EB; letter-spacing:0.05em;">
                <i class="bi bi-stars me-1"></i> KEGIATAN SISWA
            </span>
            <h2 class="fw-bold" style="color:#1F4172;">Ekstrakurikuler</h2>
            <p class="text-muted mx-auto" style="max-width:560px;">
                Pengembangan bakat dan potensi siswa di luar jam akademik melalui
                13 kegiatan terstruktur yang dipandu oleh pelatih berpengalaman.
            </p>
        </div>

        <?php
        $ekskul = [
            ['nama' => 'Muhadhoroh Ikhwan', 'kategori' => 'Keagamaan', 'icon' => 'bi-mic-fill', 'gradient' => 'linear-gradient(135deg, #1F4172 0%, #1e293b 100%)'],
            ['nama' => 'Muhadhoroh Akhwat', 'kategori' => 'Keagamaan', 'icon' => 'bi-chat-heart-fill', 'gradient' => 'linear-gradient(135deg, #059669 0%, #065f46 100%)'],
            ['nama' => 'Pencak Silat Tapak Suci', 'kategori' => 'Bela Diri', 'icon' => 'bi-shield-shaded', 'gradient' => 'linear-gradient(135deg, #DC2626 0%, #991b1b 100%)'],
            ['nama' => 'MTQ & Tilawah', 'kategori' => 'Al-Qur\'an', 'icon' => 'bi-book-half', 'gradient' => 'linear-gradient(135deg, #0D9488 0%, #115e59 100%)'],
            ['nama' => 'Pramuka Garuda', 'kategori' => 'Kepanduan', 'icon' => 'bi-compass-fill', 'gradient' => 'linear-gradient(135deg, #78350F 0%, #451a03 100%)'],
            ['nama' => 'Paskibra Sekolah', 'kategori' => 'Kedisiplinan', 'icon' => 'bi-flag-fill', 'gradient' => 'linear-gradient(135deg, #B91C1C 0%, #7f1d1d 100%)'],
            ['nama' => 'Sanggar Seni Fadhilah', 'kategori' => 'Seni Islami', 'icon' => 'bi-palette-fill', 'gradient' => 'linear-gradient(135deg, #7C3AED 0%, #5b21b6 100%)'],
            ['nama' => 'Futsal Club', 'kategori' => 'Olahraga', 'icon' => 'bi-trophy-fill', 'gradient' => 'linear-gradient(135deg, #2563EB 0%, #1d4ed8 100%)'],
            ['nama' => 'English & Arabic Club', 'kategori' => 'Bahasa Asing', 'icon' => 'bi-translate', 'gradient' => 'linear-gradient(135deg, #0284C7 0%, #0369a1 100%)'],
            ['nama' => 'Hadroh & Shalawat', 'kategori' => 'Seni Musik', 'icon' => 'bi-music-note-beamed', 'gradient' => 'linear-gradient(135deg, #047857 0%, #064e3b 100%)'],
            ['nama' => 'Klub Badminton', 'kategori' => 'Olahraga', 'icon' => 'bi-dribbble', 'gradient' => 'linear-gradient(135deg, #EA580C 0%, #c2410c 100%)'],
            ['nama' => 'Karya Ilmiah Remaja (KIR)', 'kategori' => 'Sains & Riset', 'icon' => 'bi-lightbulb-fill', 'gradient' => 'linear-gradient(135deg, #4F46E5 0%, #3730a3 100%)'],
        ];
        ?>

        <!-- ── Horizontal Scroll Gallery (Square Cards 1:1) ──── -->
        <div class="ekskul-scroll-wrapper">

            <div class="d-flex flex-nowrap overflow-x-auto pb-4 gap-3 ekskul-scroll"
                 style="scroll-snap-type: x mandatory; -webkit-overflow-scrolling: touch; cursor: grab;">

                <?php foreach ($ekskul as $e): ?>
                <div class="rounded-4 overflow-hidden position-relative shadow-sm border-0 ekskul-card-sq flex-shrink-0 text-white p-4 d-flex flex-column justify-content-between"
                     style="width:260px; height:240px; flex:0 0 auto; scroll-snap-align:start; background: <?= $e['gradient'] ?>;">

                    <!-- Top Tag & Icon -->
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="badge bg-white bg-opacity-20 text-white rounded-pill px-3 py-1 small fw-medium">
                            <?= $e['kategori'] ?>
                        </span>
                        <div class="rounded-circle bg-white bg-opacity-20 d-flex align-items-center justify-content-center" style="width:42px; height:42px;">
                            <i class="bi <?= $e['icon'] ?> fs-5"></i>
                        </div>
                    </div>

                    <!-- Label Nama Ekskul -->
                    <div>
                        <div class="text-white-50 small mb-1">Ekstrakurikuler</div>
                        <h5 class="fw-bold text-white mb-0 lh-sm">
                            <?= htmlspecialchars($e['nama']) ?>
                        </h5>
                    </div>

                </div>
                <?php endforeach; ?>

            </div><!-- /ekskul-scroll -->

            <!-- Hint geser — hanya tampil di mobile -->
            <p class="text-center text-muted small mt-2 d-lg-none mb-0">
                <i class="bi bi-arrow-left-right me-1"></i>Geser untuk melihat semua ekskul
            </p>

        </div><!-- /ekskul-scroll-wrapper -->

    </div>
</section>
<!-- /EKSTRAKURIKULER -->

<style>
/* ── Scrollbar tipis ekskul ──────────────────────────── */
.ekskul-scroll {
    scrollbar-width: thin;
    scrollbar-color: #CBD5E1 transparent;
}
.ekskul-scroll::-webkit-scrollbar       { height: 4px; }
.ekskul-scroll::-webkit-scrollbar-track { background: transparent; }
.ekskul-scroll::-webkit-scrollbar-thumb {
    background: #CBD5E1;
    border-radius: 99px;
}
.ekskul-scroll::-webkit-scrollbar-thumb:hover { background: #94A3B8; }

/* Grab cursor */
.ekskul-scroll:active { cursor: grabbing; }

/* Hover — zoom gambar saja */
.ekskul-card-sq:hover .ekskul-sq-img { transform: scale(1.06); }
</style>

<script>
/* Drag-to-scroll (mouse desktop) */
(function () {
    const el = document.querySelector('.ekskul-scroll');
    if (!el) return;
    let down = false, startX, left;
    el.addEventListener('mousedown',  e => { down = true;  startX = e.pageX - el.offsetLeft; left = el.scrollLeft; });
    el.addEventListener('mouseleave', ()  => { down = false; });
    el.addEventListener('mouseup',    ()  => { down = false; });
    el.addEventListener('mousemove',  e => {
        if (!down) return;
        e.preventDefault();
        el.scrollLeft = left - (e.pageX - el.offsetLeft - startX) * 1.4;
    });
})();
</script>


<!-- ═══════════════════════════════════════════════════════════════
     SECTION 3 — PRESTASI
════════════════════════════════════════════════════════════════ -->
<section id="prestasi" class="py-5">
    <div class="container">

        <!-- Heading -->
        <div class="text-center mb-5">
            <span class="badge rounded-pill fw-semibold px-3 py-2 mb-2"
                  style="background:#FEF9C3; color:#CA8A04; letter-spacing:0.05em;">
                <i class="bi bi-trophy-fill me-1"></i> KEBANGGAAN SEKOLAH
            </span>
            <h2 class="fw-bold" style="color:#1F4172;">Prestasi</h2>
            <p class="text-muted mx-auto" style="max-width:560px;">
                Dokumentasi kebanggaan dan raihan prestasi siswa-siswi SMA IT Al-Fadhilah Pekanbaru.
            </p>
        </div>

        <?php
        $prestasi = [
            ['juara' => 'Juara 1 MTQ Remaja Putra', 'tingkat' => 'Tingkat Nasional', 'kategori' => 'Al-Qur\'an', 'tahun' => '2026', 'peraih' => 'Ahmad Fathurrahman (Kelas XII)', 'icon' => 'bi-trophy-fill', 'color' => '#D97706', 'bg' => '#FEF3C7'],
            ['juara' => 'Juara Umum Turnamen Futsal', 'tingkat' => 'Tingkat Riau', 'kategori' => 'Olahraga', 'tahun' => '2026', 'peraih' => 'Tim Futsal Putra Al-Fadhilah', 'icon' => 'bi-award-fill', 'color' => '#2563EB', 'bg' => '#DBEAFE'],
            ['juara' => 'Medali Emas OSN Biologi', 'tingkat' => 'Tingkat Provinsi', 'kategori' => 'Akademik', 'tahun' => '2025', 'peraih' => 'Zahra Maulida (Kelas XI)', 'icon' => 'bi-mortarboard-fill', 'color' => '#059669', 'bg' => '#D1FAE5'],
            ['juara' => 'Juara 1 Da\'i Muda Bahasa Arab', 'tingkat' => 'Tingkat Pekanbaru', 'kategori' => 'Da\'wah', 'tahun' => '2025', 'peraih' => 'Muhammad Rifqi (Kelas XI)', 'icon' => 'bi-mic-fill', 'color' => '#7C3AED', 'bg' => '#EDE9FE'],
            ['juara' => 'Juara 1 Cipta Karya Tulis Ilmiah', 'tingkat' => 'Tingkat Kota', 'kategori' => 'Riset Sains', 'tahun' => '2025', 'peraih' => 'Tim KIR Fadhilah Cendekia', 'icon' => 'bi-lightbulb-fill', 'color' => '#0284C7', 'bg' => '#E0F2FE'],
            ['juara' => 'Wisudawan Mumtaz 10 Juz', 'tingkat' => 'Wisuda Akbar', 'kategori' => 'Tahfizhul Qur\'an', 'tahun' => '2025', 'peraih' => 'Farhan Al-Kautsar (Kelas XII)', 'icon' => 'bi-stars', 'color' => '#B45309', 'bg' => '#FEF9C3'],
        ];
        ?>

        <!-- Grid 3×2 — Galeri Prestasi Unggulan -->
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
            <?php foreach ($prestasi as $p): ?>
            <div class="col">
                <div class="card h-100 border-0 shadow-sm rounded-4 p-4 bg-white prestasi-card position-relative">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="rounded-3 d-flex align-items-center justify-content-center p-3"
                             style="background: <?= $p['bg'] ?>; color: <?= $p['color'] ?>; width: 50px; height: 50px;">
                            <i class="bi <?= $p['icon'] ?> fs-4"></i>
                        </div>
                        <span class="badge rounded-pill fw-semibold px-3 py-1 small"
                              style="background: <?= $p['bg'] ?>; color: <?= $p['color'] ?>;">
                            <?= $p['tingkat'] ?>
                        </span>
                    </div>

                    <h5 class="fw-bold mb-1" style="color: #1F4172;">
                        <?= htmlspecialchars($p['juara']) ?>
                    </h5>
                    <p class="text-muted small mb-3">
                        <i class="bi bi-person-fill me-1 text-secondary"></i><?= htmlspecialchars($p['peraih']) ?>
                    </p>

                    <div class="mt-auto pt-3 border-top d-flex justify-content-between align-items-center text-muted small">
                        <span><i class="bi bi-tag-fill me-1"></i><?= $p['kategori'] ?></span>
                        <span class="fw-bold text-dark"><?= $p['tahun'] ?></span>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

    </div>
</section>
<!-- /PRESTASI -->


<style>
/* Hover efek card program */
.program-card { transition: transform .3s ease, box-shadow .3s ease; }
.program-card:hover { transform: translateY(-5px); box-shadow: 0 12px 32px rgba(31,65,114,.12) !important; }
.program-card:hover .program-img { transform: scale(1.06); }

/* Hover efek card prestasi — zoom saja tanpa lift */
.prestasi-card { transition: box-shadow .4s ease; }
.prestasi-card:hover { box-shadow: 0 12px 32px rgba(31,65,114,.15) !important; }
.prestasi-card:hover .prestasi-img { transform: scale(1.07); }
</style>

<?php require_once 'includes/footer_public.php'; ?>
