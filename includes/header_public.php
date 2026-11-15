<?php
/**
 * Header Public - SMA IT Al-Fadhilah Pekanbaru
 * Komponen header resmi dengan Top Info Bar & Navbar Sticky.
 *
 * Nilai $current_page yang valid: 'beranda' | 'akademik' | 'spmb' | 'tahfiz'
 */

if (!isset($current_page)) {
    $current_page = '';
}

// Deteksi otomatis base path aset agar konsisten baik di root maupun di dalam folder modules
$base_path = (strpos($_SERVER['PHP_SELF'], '/modules/') !== false) ? '../../' : '';

function navActive(string $page, string $current): string {
    return ($current === $page) ? 'active fw-bold text-primary active-nav-link' : '';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Website Resmi SMA IT Al-Fadhilah Pekanbaru – Sekolah Islam Terpadu berbasis Tahfiz Al-Qur'an dan Karakter Pemimpin di Pekanbaru, Riau.">
    <meta name="author" content="SMA IT Al-Fadhilah Pekanbaru">
    <title><?= isset($page_title) ? htmlspecialchars($page_title) . ' – SMA IT Al-Fadhilah Pekanbaru' : 'SMA IT Al-Fadhilah Pekanbaru' ?></title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?= $base_path ?>assets/images/logo-sekolah.png">

    <!-- Google Fonts: Plus Jakarta Sans & Amiri -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Amiri:wght@400;700&display=swap" rel="stylesheet">

    <!-- Bootstrap 5.3 CSS (CDN) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    <!-- Bootstrap Icons 1.11 (CDN) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Custom CSS Publik Sekolah -->
    <link rel="stylesheet" href="<?= $base_path ?>assets/css/public-style.css">
</head>
<body>

<!-- ═══════════════════════════════════════════════════════════════════
     TOP BAR INFORMASI RESMI (Khas Web Sekolah & Lembaga Pendidikan)
═══════════════════════════════════════════════════════════════════ -->
<div class="top-info-bar py-1 d-none d-md-block text-white" style="background-color: #122847; font-size: 0.8rem; border-bottom: 2px solid #D97706;">
    <div class="container d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center gap-3">
            <span><i class="bi bi-geo-alt-fill text-warning me-1"></i>Jl. Muhajirin, Sidomulyo Barat, Pekanbaru</span>
            <span class="text-white-50">|</span>
            <span><i class="bi bi-telephone-fill text-warning me-1"></i>(0761) 840-7782</span>
            <span class="text-white-50">|</span>
            <span><i class="bi bi-envelope-fill text-warning me-1"></i>info@alfadhilah.sch.id</span>
        </div>
        <div class="d-flex align-items-center gap-3">
            <span class="badge bg-warning text-dark px-2 py-1 fw-bold"><i class="bi bi-stars me-1"></i>SPMB 2026/2027 Dibuka</span>
            <div class="d-flex gap-2 ms-2">
                <a href="https://www.instagram.com/smait_fadhilah" target="_blank" class="text-white-50 text-hover-white"><i class="bi bi-instagram"></i></a>
                <a href="https://www.facebook.com/smaitfadhilahpku" target="_blank" class="text-white-50 text-hover-white"><i class="bi bi-facebook"></i></a>
                <a href="https://www.tiktok.com/@smait_fadhilah" target="_blank" class="text-white-50 text-hover-white"><i class="bi bi-tiktok"></i></a>
            </div>
        </div>
    </div>
</div>

<!-- ═══════════════════════════════════════════════════════════════════
     NAVBAR – Sticky Top | Latar Putih | Shadow Halus
═══════════════════════════════════════════════════════════════════ -->
<nav class="navbar navbar-expand-lg bg-white sticky-top shadow-sm py-2" id="navbarUtama">
    <div class="container">

        <!-- ── BRAND / LOGO ───────────────────────────────────────── -->
        <a class="navbar-brand d-flex align-items-center gap-2 text-decoration-none" href="<?= $base_path ?>index.php">
            <img src="<?= $base_path ?>assets/images/logo-sekolah.png" alt="Logo SMA IT Al-Fadhilah" width="46" height="46" class="rounded-circle object-fit-contain shadow-sm border border-light">
            <div class="d-flex flex-column">
                <span class="fw-extrabold lh-1" style="color: #1F4172; font-size: 1.15rem; letter-spacing: -0.02em;">
                    SMA IT AL-FADHILAH
                </span>
                <small class="fw-semibold text-muted" style="font-size: 0.72rem; letter-spacing: 0.05em;">
                    PEKANBARU · RIAU
                </small>
            </div>
        </a>

        <!-- ── TOGGLER (Mobile) ───────────────────────────────────── -->
        <button
            class="navbar-toggler border-0 p-2"
            type="button"
            data-bs-toggle="collapse"
            data-bs-target="#navMenu"
            aria-controls="navMenu"
            aria-expanded="false"
            aria-label="Toggle navigasi"
        >
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- ── MENU NAVIGASI ──────────────────────────────────────── -->
        <div class="collapse navbar-collapse justify-content-end" id="navMenu">
            <ul class="navbar-nav align-items-lg-center gap-lg-1 py-2 py-lg-0">

                <!-- Beranda -->
                <li class="nav-item">
                    <a class="nav-link px-3 <?= navActive('beranda', $current_page) ?>"
                       href="<?= $base_path ?>index.php"
                       <?= ($current_page === 'beranda') ? 'aria-current="page"' : '' ?>>
                        <i class="bi bi-house-door me-1"></i>Beranda
                    </a>
                </li>

                <!-- Akademik -->
                <li class="nav-item">
                    <a class="nav-link px-3 <?= navActive('akademik', $current_page) ?>"
                       href="<?= $base_path ?>akademik.php"
                       <?= ($current_page === 'akademik') ? 'aria-current="page"' : '' ?>>
                        <i class="bi bi-mortarboard me-1"></i>Akademik
                    </a>
                </li>

                <!-- SPMB -->
                <li class="nav-item">
                    <a class="nav-link px-3 <?= navActive('spmb', $current_page) ?>"
                       href="<?= $base_path ?>spmb.php"
                       <?= ($current_page === 'spmb') ? 'aria-current="page"' : '' ?>>
                        <i class="bi bi-pencil-square me-1"></i>SPMB Online
                    </a>
                </li>

                <!-- Tahfiz (Login Portal Multi-Peran) -->
                <li class="nav-item ms-lg-2 mt-2 mt-lg-0">
                    <a class="btn btn-sm px-4 py-2 fw-bold text-white rounded-pill shadow-sm"
                       style="background: linear-gradient(135deg, #1F4172 0%, #0d9488 100%);"
                       href="<?= $base_path ?>modules/auth/login.php"
                       <?= ($current_page === 'tahfiz') ? 'aria-current="page"' : '' ?>>
                        <i class="bi bi-journal-bookmark-fill me-1 text-warning"></i>Portal Tahfiz
                    </a>
                </li>

            </ul>
        </div>
        <!-- /navMenu -->

    </div>
</nav>
<!-- /NAVBAR -->
