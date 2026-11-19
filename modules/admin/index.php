<?php
/**
 * modules/admin/index.php
 * Dashboard Administrator – SMA IT Al-Fadhilah Pekanbaru
 *
 * Bertanggung jawab atas:
 *   1. Autentikasi & validasi peran
 *   2. Routing tab (whitelist)
 *   3. Pemanggilan handler POST  → actions/post_handler.php
 *   4. Pemanggilan query data    → data/queries.php
 *   5. Layout HTML (sidebar, topbar, notifikasi)
 *   6. Pemanggilan tampilan tab  → tabs/{$page_tab}.php
 *      (Modal & JS tiap tab sudah ada di dalam file tab masing-masing)
 */

// Guard: file-file yang di-require hanya boleh diakses dari sini
define('ADMIN_INDEX', true);

require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/database.php';

// ─────────────────────────────────────────────────────────────
// AUTENTIKASI & VALIDASI PERAN
// ─────────────────────────────────────────────────────────────
wajib_login(__DIR__ . '/../auth/login.php');
if (peran_pengguna() !== 'admin') {
    $role = peran_pengguna();
    if ($role === 'ustadz' || $role === 'guru') {
        header('Location: ../ustadz/index.php'); exit;
    } elseif ($role === 'siswa') {
        header('Location: ../siswa/index.php'); exit;
    } else {
        header('Location: ../auth/login.php'); exit;
    }
}

$admin_name = $_SESSION['user_name'] ?? 'Administrator';

// Inisialisasi CSRF Token untuk seluruh operasi form
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

// ─────────────────────────────────────────────────────────────
// ROUTING TAB
// ─────────────────────────────────────────────────────────────
$valid_tabs = ['rekap', 'pengguna', 'halaqah', 'laporan', 'profil', 'sandi'];
$page_tab   = $_GET['tab'] ?? 'rekap';
if (!in_array($page_tab, $valid_tabs)) {
    $page_tab = 'rekap';
}

$success_msg = '';
$error_msg   = '';

// ─────────────────────────────────────────────────────────────
// KONEKSI DATABASE
// ─────────────────────────────────────────────────────────────
$pdo = null;
try {
    $pdo = koneksi_database();
} catch (Throwable $e) {
    $error_msg = "Koneksi database tidak tersedia: " . $e->getMessage();
}

// ─────────────────────────────────────────────────────────────
// HANDLER AKSI POST & GET HAPUS PENGGUNA
// ─────────────────────────────────────────────────────────────
require_once __DIR__ . '/actions/post_handler.php';

// ─────────────────────────────────────────────────────────────
// QUERY DATA & PERSIAPAN VARIABEL TAMPILAN
// ─────────────────────────────────────────────────────────────
require_once __DIR__ . '/data/queries.php';

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Administrator – SMA IT Al-Fadhilah</title>

    <link rel="icon" type="image/png" href="../../assets/images/logo-tahfiz-baru.png">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400&display=swap"
          rel="stylesheet">

    <!-- Bootstrap 5.3 & Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Stylesheet terpusat Tahfiz & Admin -->
    <link rel="stylesheet" href="../../assets/css/tahfiz.css">
    <link rel="stylesheet" href="../../assets/css/admin.css?v=<?= time() ?>">
</head>
<body>

<!-- Overlay untuk mobile sidebar -->
<div class="sb-overlay" id="sbOverlay" onclick="closeSidebar()"></div>

<div class="d-flex">

    <!-- ════════════════════════════════════════════════════════
         SIDEBAR ADMINISTRATOR
    ════════════════════════════════════════════════════════ -->
    <aside class="admin-sidebar" id="adminSidebar">

        <!-- Brand -->
        <div class="sb-brand">
            <div class="sb-brand-logo">
                <img src="../../assets/images/logo-tahfiz-baru.png" alt="Logo Tahfiz Yuk">
            </div>
            <div class="overflow-hidden">
                <div class="sb-brand-name">Tahfiz Yuk</div>
                <div class="sb-brand-sub">Admin Panel</div>
            </div>
        </div>

        <!-- Kartu Profil Admin -->
        <div class="sb-user">
            <div class="sb-avatar"><?= htmlspecialchars($admin_initials) ?></div>
            <div class="overflow-hidden" style="min-width:0;">
                <div class="sb-user-name"><?= htmlspecialchars($admin_name) ?></div>
                <div class="sb-user-badge"><span>●</span> Super Administrator</div>
            </div>
        </div>

        <!-- Navigasi -->
        <nav class="flex-grow-1 mt-1">
            <div class="sb-label">Menu Utama</div>

            <a href="?tab=rekap"    class="sb-link <?= $page_tab === 'rekap'    ? 'active' : '' ?>">
                <i class="bi bi-grid-1x2-fill"></i><span>Dashboard Rekap</span>
            </a>
            <a href="?tab=pengguna" class="sb-link <?= $page_tab === 'pengguna' ? 'active' : '' ?>">
                <i class="bi bi-people-fill"></i><span>Data Pengguna</span>
            </a>
            <a href="?tab=halaqah"  class="sb-link <?= $page_tab === 'halaqah'  ? 'active' : '' ?>">
                <i class="bi bi-diagram-3-fill"></i><span>Kelompok Halaqah</span>
            </a>
            <a href="?tab=laporan"  class="sb-link <?= $page_tab === 'laporan'  ? 'active' : '' ?>">
                <i class="bi bi-file-earmark-bar-graph-fill"></i><span>Laporan Tahfiz</span>
            </a>

            <div class="sb-label" style="margin-top:8px;">Akun Saya</div>

            <a href="?tab=profil"   class="sb-link <?= $page_tab === 'profil'   ? 'active' : '' ?>">
                <i class="bi bi-person-circle"></i><span>Profil Saya</span>
            </a>
            <a href="?tab=sandi"    class="sb-link <?= $page_tab === 'sandi'    ? 'active' : '' ?>">
                <i class="bi bi-key-fill"></i><span>Ubah Kata Sandi</span>
            </a>
        </nav>

        <!-- Footer Sidebar -->
        <div class="sb-footer">
            <a href="../auth/logout.php" class="sb-logout">
                <i class="bi bi-box-arrow-right"></i> Keluar Sesi
            </a>
        </div>
    </aside>

    <!-- ════════════════════════════════════════════════════════
         MAIN CONTENT
    ════════════════════════════════════════════════════════ -->
    <div class="admin-main">

        <!-- TOPBAR -->
        <header class="topbar">
            <div class="topbar-left">
                <button class="btn-ham" onclick="openSidebar()" aria-label="Buka menu">
                    <i class="bi bi-list"></i>
                </button>
                <img src="../../assets/images/logo-tahfiz-baru.png" alt="Logo" class="topbar-logo">
                <span class="topbar-sep">/</span>
                <div class="topbar-breadcrumb">
                    <span>Ruang Admin</span>
                    <i class="bi bi-chevron-right" style="font-size:11px;"></i>
                    <span class="crumb-active">
                        <?= htmlspecialchars($tab_labels[$page_tab] ?? 'Dashboard') ?>
                    </span>
                </div>
            </div>
            <div class="topbar-right">
                <span class="topbar-badge topbar-date d-none d-md-inline-flex">
                    <i class="bi bi-calendar3"></i><?= $tanggal_sekarang ?>
                </span>
                <span class="topbar-badge topbar-role">
                    <i class="bi bi-shield-lock-fill"></i> Administrator
                </span>
            </div>
        </header>

        <!-- CONTENT AREA -->
        <main class="content-area">

            <!-- Notifikasi -->
            <?php if (!empty($success_msg)): ?>
                <div class="notif-sukses">
                    <i class="bi bi-check-circle-fill fs-5"></i>
                    <span><?= htmlspecialchars($success_msg) ?></span>
                </div>
            <?php endif; ?>
            <?php if (!empty($error_msg)): ?>
                <div class="notif-error">
                    <i class="bi bi-exclamation-triangle-fill fs-5"></i>
                    <span><?= htmlspecialchars($error_msg) ?></span>
                </div>
            <?php endif; ?>

            <!-- Dispatcher Tab (modal & JS sudah ada di dalam file tab masing-masing) -->
            <?php require_once __DIR__ . '/tabs/' . $page_tab . '.php'; ?>

        </main>
    </div><!-- /admin-main -->
</div><!-- /d-flex -->

<!-- Bootstrap 5.3 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // ── Sidebar mobile ────────────────────────────────────────
    function openSidebar() {
        document.getElementById('adminSidebar').classList.add('open');
        document.getElementById('sbOverlay').classList.add('open');
        document.body.style.overflow = 'hidden';
    }
    function closeSidebar() {
        document.getElementById('adminSidebar').classList.remove('open');
        document.getElementById('sbOverlay').classList.remove('open');
        document.body.style.overflow = '';
    }

    // Tutup sidebar saat layar diperbesar ke desktop
    window.addEventListener('resize', function() {
        if (window.innerWidth >= 992) closeSidebar();
    });

    // ── Auto-dismiss notifikasi setelah 5 detik ───────────────
    setTimeout(function() {
        document.querySelectorAll('.notif-sukses, .notif-error').forEach(function(el) {
            el.style.transition = 'opacity 0.5s';
            el.style.opacity    = '0';
            setTimeout(function() { el.remove(); }, 500);
        });
    }, 5000);
</script>
</body>
</html>
