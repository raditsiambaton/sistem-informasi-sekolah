<?php
/**
 * modules/siswa/index.php
 * Router Utama Dashboard Siswa — SIM Tahfiz "Tahfiz Yuk"
 * SMA IT Al-Fadhilah Pekanbaru
 *
 * Arsitektur modular (seragam dengan modules/ustadz/):
 *   actions/post_handler.php — pemroses POST (update_biodata, ganti_password)
 *   data/queries.php         — query DB, helper resolveKualitas(), kualitasClass()
 *   tabs/{progres|riwayat|profil}.php — tampilan per tab
 */

define('SISWA_INDEX', true);

require_once '../../config/session.php';
require_once '../../config/database.php';

// ── Validasi otorisasi sesi Siswa ───────────────────────────
wajib_login('../auth/login.php');
$role = peran_pengguna();
if ($role !== 'siswa') {
    if ($role === 'admin') {
        header('Location: ../admin/index.php');
    } elseif ($role === 'ustadz' || $role === 'guru') {
        header('Location: ../ustadz/index.php');
    } else {
        header('Location: ../auth/login.php');
    }
    exit;
}

// ── Variabel dasar dari sesi ────────────────────────────────
$siswa_id     = $_SESSION['user_id']     ?? 3;
$siswa_name   = $_SESSION['user_name']   ?? 'Muhammad Fadhlan';
$siswa_nis    = $_SESSION['user_nip_nis'] ?? '102601';
$halaqah_name = $_SESSION['user_halaqah'] ?? 'Al-Furqan';
$siswa_no_hp  = $_SESSION['user_no_hp']  ?? '081234567890';

// ── Routing tab dengan whitelist ────────────────────────────
$valid_tabs = ['progres', 'riwayat', 'profil'];
$page_tab   = in_array($_GET['tab'] ?? '', $valid_tabs, true)
    ? $_GET['tab']
    : 'progres';

// ── Inisialisasi variabel notifikasi ───────────────────────
$notif_sukses = '';
$notif_error  = '';

// ── Pemroses POST ────────────────────────────────────────────
require_once __DIR__ . '/actions/post_handler.php';

// ── Query & helper data ──────────────────────────────────────
require_once __DIR__ . '/data/queries.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ruang Siswa – SIM Tahfiz SMA IT Al-Fadhilah</title>

    <link rel="icon" type="image/png" href="../../assets/images/logo-tahfiz-baru.png">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Bootstrap 5.3 & Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Stylesheet Tahfiz (shared) & Siswa (modul-spesifik) -->
    <link rel="stylesheet" href="../../assets/css/tahfiz.css?v=<?= time() ?>">
    <link rel="stylesheet" href="../../assets/css/siswa.css?v=<?= time() ?>">
</head>
<body>

<!-- Overlay untuk mobile sidebar -->
<div class="sb-overlay" id="sbOverlay" onclick="closeSidebar()"></div>

<div class="d-flex">
    <!-- SIDEBAR SISWA -->
    <aside class="siswa-sidebar" id="siswaSidebar">

        <!-- Brand -->
        <div class="sb-brand">
            <div class="sb-brand-logo">
                <img src="../../assets/images/logo-tahfiz-baru.png" alt="Logo Tahfiz Yuk">
            </div>
            <div class="overflow-hidden">
                <div class="sb-brand-name">Tahfiz Yuk</div>
                <div class="sb-brand-sub">Portal Siswa</div>
            </div>
        </div>

        <!-- Kartu Profil Siswa -->
        <div class="sb-user">
            <div class="sb-avatar"><?= htmlspecialchars($siswa_initials) ?></div>
            <div class="overflow-hidden" style="min-width:0;">
                <div class="sb-user-name"><?= htmlspecialchars($siswa_name) ?></div>
                <div class="sb-user-badge"><span>●</span> Siswa Aktif</div>
            </div>
        </div>

        <!-- Navigasi Menu Siswa -->
        <nav class="flex-grow-1 mt-1">
            <div class="sb-label">Menu Utama</div>

            <a href="?tab=progres" class="sb-link <?= $page_tab === 'progres' ? 'active' : '' ?>">
                <i class="bi bi-graph-up"></i><span>Progres Hafalan</span>
            </a>
            <a href="?tab=riwayat" class="sb-link <?= $page_tab === 'riwayat' ? 'active' : '' ?>">
                <i class="bi bi-clock-history"></i><span>Riwayat Hafalan</span>
            </a>

            <div class="sb-label" style="margin-top:8px;">Akun Saya</div>

            <a href="?tab=profil" class="sb-link <?= $page_tab === 'profil' ? 'active' : '' ?>">
                <i class="bi bi-person-circle"></i><span>Profil Saya</span>
            </a>
        </nav>

        <!-- Footer Sidebar -->
        <div class="sb-footer">
            <a href="../auth/logout.php" class="sb-logout">
                <i class="bi bi-box-arrow-right"></i> Keluar Sesi
            </a>
        </div>
    </aside>

    <!-- MAIN WRAPPER -->
    <div class="flex-grow-1 d-flex flex-column siswa-content-wrapper">
        <header class="bg-white border-bottom px-4 py-3 d-flex justify-content-between align-items-center shadow-sm">
            <div class="d-flex align-items-center gap-3">
                <button class="btn-ham" onclick="openSidebar()" aria-label="Buka menu">
                    <i class="bi bi-list"></i>
                </button>
                <img src="../../assets/images/logo-tahfiz-baru.png" alt="Logo Tahfiz" class="topbar-logo" style="height: 24px; width: auto; object-fit: contain;">
                <div class="d-flex align-items-center gap-2 small text-muted">
                    <span>Ruang Siswa</span>
                    <span>/</span>
                    <span class="fw-bold text-dark text-capitalize"><?= htmlspecialchars($page_tab) ?></span>
                </div>
            </div>
            <div class="d-flex align-items-center gap-3">
                <span class="badge px-3 py-2 fw-semibold" style="background:#E6F4F1; color:#11756D; border:1px solid #A2D5D2;">
                    <i class="bi bi-calendar3 me-1"></i><?= date('d M Y') ?>
                </span>
                <span class="badge px-3 py-2 fw-semibold text-white" style="background-color: #11756D;">
                    <i class="bi bi-person-check-fill me-1"></i>Siswa Halaqah <?= htmlspecialchars($halaqah_name) ?>
                </span>
            </div>
        </header>

        <main class="p-4 flex-grow-1">

            <!-- Alert Notifikasi -->
            <?php if (!empty($notif_sukses)): ?>
                <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm d-flex align-items-center gap-2 mb-4" role="alert">
                    <i class="bi bi-check-circle-fill fs-5 text-success"></i>
                    <div><?= htmlspecialchars($notif_sukses) ?></div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?php if (!empty($notif_error)): ?>
                <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm d-flex align-items-center gap-2 mb-4" role="alert">
                    <i class="bi bi-exclamation-triangle-fill fs-5 text-danger"></i>
                    <div><?= htmlspecialchars($notif_error) ?></div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <!-- Dispatcher Tab -->
            <?php require_once __DIR__ . '/tabs/' . $page_tab . '.php'; ?>

        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// ── Sidebar mobile ───────────────────────────────────────────
function openSidebar() {
    document.getElementById('siswaSidebar').classList.add('open');
    document.getElementById('sbOverlay').classList.add('open');
    document.body.style.overflow = 'hidden';
}
function closeSidebar() {
    document.getElementById('siswaSidebar').classList.remove('open');
    document.getElementById('sbOverlay').classList.remove('open');
    document.body.style.overflow = '';
}
window.addEventListener('resize', function () {
    if (window.innerWidth >= 992) closeSidebar();
});
</script>
</body>
</html>
