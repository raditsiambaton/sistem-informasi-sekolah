<?php
/**
 * Header Public - SMA IT Al-Fadhilah Pekanbaru
 * Komponen header statis (Navbar Sticky-Top) untuk halaman publik.
 * Gunakan variabel $current_page di halaman pemanggil untuk menandai menu aktif.
 * Contoh: $current_page = 'beranda'; sebelum require/include file ini.
 *
 * Nilai $current_page yang valid: 'beranda' | 'akademik' | 'spmb' | 'tahfiz'
 */

// Pastikan $current_page selalu terdefinisi agar tidak terjadi PHP Notice
if (!isset($current_page)) {
    $current_page = '';
}

/**
 * Helper: Mengembalikan class aktif jika $page cocok dengan $current_page.
 */
function navActive(string $page, string $current): string {
    return ($current === $page) ? 'active fw-bold text-primary' : '';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Website Resmi SMA IT Al-Fadhilah Pekanbaru – Sekolah Islam Terpadu berbasis Tahfiz Al-Qur'an di Pekanbaru, Riau.">
    <meta name="author" content="SMA IT Al-Fadhilah Pekanbaru">
    <title><?= isset($page_title) ? htmlspecialchars($page_title) . ' – SMA IT Al-Fadhilah Pekanbaru' : 'SMA IT Al-Fadhilah Pekanbaru' ?></title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="assets/images/logo-sekolah.png">

    <!-- ═══════════════════════════════════════════════
         Bootstrap 5.3 CSS (CDN)
    ═══════════════════════════════════════════════ -->
    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH"
        crossorigin="anonymous"
    >

    <!-- ═══════════════════════════════════════════════
         Bootstrap Icons 1.11 (CDN)
    ═══════════════════════════════════════════════ -->
    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
    >

    <!-- ═══════════════════════════════════════════════
         Custom CSS Publik
    ═══════════════════════════════════════════════ -->
    <link rel="stylesheet" href="assets/css/public-style.css">
</head>
<body>

<!-- ═══════════════════════════════════════════════════════════════════
     NAVBAR – Sticky Top | Latar Putih | Shadow Halus
═══════════════════════════════════════════════════════════════════ -->
<nav class="navbar navbar-expand-lg bg-white sticky-top shadow-sm" id="navbarUtama">
    <div class="container">

        <!-- ── BRAND / LOGO ───────────────────────────────────────── -->
        <a class="navbar-brand d-flex align-items-center gap-2 text-decoration-none" href="index.php">
            <img src="assets/images/logo-sekolah.png" alt="Logo SMA IT Al-Fadhilah" width="40" height="40" class="rounded-circle object-fit-contain me-2">
            <span class="fw-bold lh-sm" style="color: #1F4172; font-size: 1.05rem;">
                SMA IT<br>
                <small class="fw-bold" style="font-size: 0.75rem; letter-spacing: 0.03em;">Al-Fadhilah</small>
            </span>
        </a>

        <!-- ── TOGGLER (Mobile) ───────────────────────────────────── -->
        <button
            class="navbar-toggler border-0"
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
                       href="index.php"
                       <?= ($current_page === 'beranda') ? 'aria-current="page"' : '' ?>>
                        <i class="bi bi-house-door me-1"></i>Beranda
                    </a>
                </li>

                <!-- Akademik -->
                <li class="nav-item">
                    <a class="nav-link px-3 <?= navActive('akademik', $current_page) ?>"
                       href="akademik.php"
                       <?= ($current_page === 'akademik') ? 'aria-current="page"' : '' ?>>
                        <i class="bi bi-book me-1"></i>Akademik
                    </a>
                </li>

                <!-- SPMB -->
                <li class="nav-item">
                    <a class="nav-link px-3 <?= navActive('spmb', $current_page) ?>"
                       href="spmb.php"
                       <?= ($current_page === 'spmb') ? 'aria-current="page"' : '' ?>>
                        <i class="bi bi-pencil-square me-1"></i>SPMB
                    </a>
                </li>

                <!-- Tahfiz (Login Portal) – Tampil sebagai tombol menonjol -->
                <li class="nav-item ms-lg-2">
                    <a class="btn btn-sm px-4 py-2 fw-semibold <?= ($current_page === 'tahfiz')
                        ? 'btn-primary active'
                        : 'btn-outline-primary' ?>"
                       href="modules/auth/login.php"
                       <?= ($current_page === 'tahfiz') ? 'aria-current="page"' : '' ?>>
                        <i class="bi bi-journal-bookmark-fill me-1"></i>Tahfiz
                    </a>
                </li>

            </ul>
        </div>
        <!-- /navMenu -->

    </div>
</nav>
<!-- /NAVBAR -->
