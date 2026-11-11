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
                'img'       => 'https://placehold.co/400x250/e2e8f0/1e293b?text=Tahfiz+%26+Al-Quran',
                'judul'     => "Tahfiz & Al-Qur'an",
                'deskripsi' => "Program pembentukan karakter Qur'ani melalui tahsin perbaikan bacaan, tasmi' simakan hafalan, mutaba'ah harian, serta bimbingan intensif hafalan Al-Qur'an.",
            ],
            [
                'img'       => 'https://placehold.co/400x250/e2e8f0/1e293b?text=Leadership+%26+Kepemimpinan',
                'judul'     => 'Leadership & Kepemimpinan',
                'deskripsi' => "Mencetak calon pemimpin muslim melalui program Leaders' Camp, Latihan Dasar Kepemimpinan (LDK), kegiatan ROHIS, dan kedisiplinan Paskibra Sekolah.",
            ],
            [
                'img'       => 'https://placehold.co/400x250/e2e8f0/1e293b?text=Bahasa+%26+Public+Speaking',
                'judul'     => 'Bahasa & Public Speaking',
                'deskripsi' => 'Membangun kepercayaan diri dan kecakapan komunikasi global siswa melalui English & Arabic Day, muhadharah pidato dua bahasa, dan Sanggar Fadhilah.',
            ],
            [
                'img'       => 'https://placehold.co/400x250/e2e8f0/1e293b?text=Sukses+SNBT+%26+Akademik',
                'judul'     => 'Sukses SNBT & Akademik',
                'deskripsi' => 'Mempersiapkan siswa menembus PTN favorit melalui bimbingan Tes Kemampuan Akademik (TKA), strategi kelulusan SNBT, dan penyusunan Karya Tulis Ilmiah.',
            ],
            [
                'img'       => 'https://placehold.co/400x250/e2e8f0/1e293b?text=Bina+Iman+%26+Taqwa',
                'judul'     => 'Bina Iman & Taqwa (BIMTAQ)',
                'deskripsi' => 'Memperkuat pondasi keimanan dan ketaqwaan siswa melalui program Malam Bina Iman dan Taqwa (MABIT), Taujihad spiritual, dan pembiasaan Shalat Dhuha.',
            ],
            [
                'img'       => 'https://placehold.co/400x250/e2e8f0/1e293b?text=Sosial+%26+Karakter+Muslim',
                'judul'     => 'Sosial & Karakter Muslim',
                'deskripsi' => "Membangun kepekaan sosial dan jiwa berbagi sebagai muslim sejati melalui program Jum'at Berkah sedekah mingguan, Pesantren Ramadhan, dan ibadah Qurban bersama.",
            ],
        ];
        ?>

        <!-- Grid 6 Card — 3 Kolom × 2 Baris -->
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
            <?php foreach ($programs as $p): ?>
            <div class="col">
                <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden program-card">

                    <!-- Gambar Placeholder -->
                    <img src="<?= $p['img'] ?>"
                         class="card-img-top"
                         alt="<?= htmlspecialchars($p['judul']) ?>"
                         style="height:200px; object-fit:cover;">

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
            ['nama' => 'MUHADHOROH IKHWAN'],
            ['nama' => 'MUHADHOROH AKHWAT'],
            ['nama' => 'PENCAK SILAT'],
            ['nama' => 'MTQ'],
            ['nama' => 'PRAMUKA'],
            ['nama' => 'PASKIBRA'],
            ['nama' => 'SANGGAR FADHILAH'],
            ['nama' => 'FUTSAL'],
            ['nama' => 'ENGLISH CLUB'],
            ['nama' => 'HADROH'],
            ['nama' => 'VOKAL'],
            ['nama' => 'MUSIK / DRUMBAND'],
            ['nama' => 'BADMINTON'],
        ];
        ?>

        <!-- ── Horizontal Scroll Gallery (Square Cards 1:1) ──── -->
        <div class="ekskul-scroll-wrapper">

            <div class="d-flex flex-nowrap overflow-x-auto pb-4 gap-3 ekskul-scroll"
                 style="scroll-snap-type: x mandatory; -webkit-overflow-scrolling: touch; cursor: grab;">

                <?php foreach ($ekskul as $e):
                    /* Encode nama untuk URL placeholder: spasi→+, /→%2F */
                    $slug = str_replace(['/', ' '], ['%2F', '+'], $e['nama']);
                ?>
                <div class="rounded-4 overflow-hidden position-relative shadow-sm border-0 ekskul-card-sq flex-shrink-0"
                     style="width:280px; height:280px; flex:0 0 auto; scroll-snap-align:start;">

                    <!-- Foto Placeholder Persegi -->
                    <img src="https://placehold.co/300x300/cbd5e1/475569?text=Foto+<?= $slug ?>"
                         alt="Ekstrakurikuler <?= htmlspecialchars($e['nama']) ?>"
                         class="w-100 h-100 object-fit-cover ekskul-sq-img"
                         style="display:block; transition:transform .4s ease;">

                    <!-- Overlay Gradien Bawah -->
                    <div class="position-absolute bottom-0 start-0 end-0 bg-gradient"
                         style="height:55%;
                                background: linear-gradient(to top, rgba(0,0,0,.78) 0%, transparent 100%);
                                pointer-events:none;">
                    </div>

                    <!-- Label Teks Pojok Kiri Bawah -->
                    <div class="position-absolute bottom-0 start-0 end-0 p-3">
                        <div class="border-start border-2 border-white ps-2
                                    text-white fw-bold text-uppercase small lh-sm"
                             style="letter-spacing:0.05em; font-size:0.78rem;">
                            <?= htmlspecialchars($e['nama']) ?>
                        </div>
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

        <!-- Grid 3×2 — Galeri Foto Murni -->
        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 g-4">
            <?php for ($i = 1; $i <= 6; $i++): ?>
            <div class="col">
                <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden position-relative prestasi-card">
                    <img src="https://placehold.co/400x300/cbd5e1/1e293b?text=Dokumentasi+Prestasi+<?= $i ?>"
                         class="img-fluid w-100 h-100 object-fit-cover prestasi-img"
                         alt="Dokumentasi Prestasi"
                         style="transition: transform .4s ease; display:block;">
                </div>
            </div>
            <?php endfor; ?>
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
