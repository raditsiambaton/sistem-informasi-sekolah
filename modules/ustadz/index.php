<?php
/**
 * modules/ustadz/index.php
 * Ruang Asatidz – SIM Tahfiz SMA IT Al-Fadhilah Pekanbaru
 * Arsitektur modular: data/, actions/, tabs/
 */

require_once '../../config/session.php';
require_once '../../config/database.php';

// ── Validasi sesi & role ──────────────────────────────────────
wajib_login('../auth/login.php');
$role = peran_pengguna();
if ($role !== 'ustadz' && $role !== 'guru') {
    if ($role === 'admin') {
        header('Location: ../admin/index.php'); exit;
    } elseif ($role === 'siswa') {
        header('Location: ../siswa/index.php'); exit;
    } else {
        header('Location: ../auth/login.php'); exit;
    }
}

// ── Variabel profil (fallback dari session) ───────────────────
$ustadz_name   = $_SESSION['user_name']    ?? 'Ust. Rival Alvian Melfandy';
$halaqah_name  = $_SESSION['user_halaqah'] ?? 'Al-Furqan';
$ustadz_nip    = $_SESSION['user_nip_nis'] ?? '199205142023031004';
$ustadz_kontak = $_SESSION['user_no_hp']   ?? '0812-7890-1234';
$ustadz_id     = $_SESSION['user_id']      ?? 2;

// ── Routing tab ───────────────────────────────────────────────
$page_tab = $_GET['tab'] ?? 'rekap';
if ($page_tab === 'setoran' || $page_tab === 'input-hafalan') {
    $page_tab = 'hafalan';
} elseif ($page_tab === 'input-murajaah') {
    $page_tab = 'murajaah';
}

$notif_sukses = '';
$notif_error  = '';

// ── Koneksi database & setup tabel ───────────────────────────
$pdo = null;
try {
    $pdo = koneksi_database();
    if ($pdo) {
        $u_stmt = $pdo->prepare("SELECT nama_lengkap, nip_nis, halaqah, no_hp FROM users WHERE id = :id LIMIT 1");
        $u_stmt->execute(['id' => $ustadz_id]);
        $u_row = $u_stmt->fetch();
        if ($u_row) {
            $ustadz_name  = $u_row['nama_lengkap'] ?? $ustadz_name;
            $halaqah_name = $u_row['halaqah']       ?? $halaqah_name;
            $ustadz_nip   = $u_row['nip_nis']       ?? $ustadz_nip;
            if (!empty($u_row['no_hp'])) $ustadz_kontak = $u_row['no_hp'];
        }
        try {
            $pdo->exec("CREATE TABLE IF NOT EXISTS `target_hafalan` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `siswa_id` INT NOT NULL, `ustadz_id` INT NOT NULL,
                `surah_awal` VARCHAR(180) NOT NULL, `surah_akhir` VARCHAR(180) NOT NULL,
                `tanggal_mulai` DATE NOT NULL, `tanggal_akhir` DATE NOT NULL,
                `status` VARCHAR(20) NOT NULL DEFAULT 'Aktif',
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        } catch (Throwable $e) {}
    }
} catch (Throwable $e) {}

// ── Kurikulum & opsi target ───────────────────────────────────
$target_fokus_id = 0;
$target_old      = ['surah_awal' => '', 'surah_akhir' => '', 'tanggal_mulai' => '', 'tanggal_akhir' => ''];
require_once 'data/kurikulum.php';

// ── Proses POST ───────────────────────────────────────────────
require_once 'actions/post_handler.php';

// ── Query data utama ──────────────────────────────────────────
$santri_list = [];
if ($pdo) {
    try {
        $stmt = $pdo->prepare("SELECT id, username, nama_lengkap, nip_nis, halaqah FROM users WHERE role = 'siswa' ORDER BY id ASC");
        $stmt->execute();
        $santri_list = $stmt->fetchAll();
    } catch (Throwable $e) {}
}
if (empty($santri_list)) {
    $santri_list = $santri_list_fallback;
}

$riwayat_setoran  = [];
$riwayat_murajaah = [];
if ($pdo) {
    try { $riwayat_setoran  = $pdo->query("SELECT * FROM setoran_hafalan ORDER BY id DESC LIMIT 50")->fetchAll(); } catch (Throwable $e) {}
    try { $riwayat_murajaah = $pdo->query("SELECT * FROM murajaah ORDER BY id DESC LIMIT 50")->fetchAll();        } catch (Throwable $e) {}
}

$target_aktif_map = [];
if ($pdo) {
    try {
        $stmt_target = $pdo->query("SELECT siswa_id, surah_awal, surah_akhir, tanggal_mulai, tanggal_akhir, status FROM target_hafalan WHERE status = 'Aktif' ORDER BY id DESC");
        foreach ($stmt_target->fetchAll() as $baris_target) {
            $k = (string)$baris_target['siswa_id'];
            if (!isset($target_aktif_map[$k])) $target_aktif_map[$k] = $baris_target;
        }
    } catch (Throwable $e) {}
}

$total_santri_binaan  = count($santri_list);
$total_setoran_count  = count($riwayat_setoran);
$total_murajaah_count = count($riwayat_murajaah);

// ── Whitelist tab ─────────────────────────────────────────────
$tab_whitelist = ['profil', 'hafalan', 'murajaah', 'rekap', 'target'];
if (!in_array($page_tab, $tab_whitelist, true)) {
    $page_tab = 'rekap';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ruang Asatidz – SIM Tahfiz SMA IT Al-Fadhilah</title>
    <link rel="icon" type="image/png" href="../../assets/images/logo-tahfiz-baru.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Amiri:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../../assets/css/tahfiz.css?v=<?= time() ?>">
</head>
<body>
<div class="d-flex">

    <!-- ═══ SIDEBAR ═══════════════════════════════════════════ -->
    <aside class="ustadz-sidebar d-flex flex-column">
        <div class="sidebar-brand">
            <div class="sidebar-brand-logo">
                <img src="../../assets/images/logo-tahfiz-baru.png" alt="Logo Tahfiz">
            </div>
            <div class="overflow-hidden">
                <div class="sidebar-brand-title text-truncate">Tahfiz Yuk</div>
                <div class="sidebar-brand-subtitle text-truncate">Sistem monitoring tahfiz</div>
            </div>
        </div>

        <div class="ustadz-user-info">
            <div class="ustadz-user-avatar">RA</div>
            <div class="ustadz-user-text overflow-hidden">
                <div class="ustadz-user-role text-white fw-semibold text-truncate"><?= htmlspecialchars($ustadz_name) ?></div>
                <div class="ustadz-user-name fw-semibold text-truncate">● Halaqah <?= htmlspecialchars($halaqah_name) ?></div>
            </div>
        </div>

        <nav class="ustadz-nav flex-grow-1">
            <span class="ustadz-nav-label">Menu Utama</span>
            <a href="?tab=profil"   class="ustadz-nav-link <?= $page_tab === 'profil'   ? 'active' : '' ?>"><i class="bi bi-person-circle"></i><span>Profil Saya</span></a>
            <a href="?tab=hafalan"  class="ustadz-nav-link <?= $page_tab === 'hafalan'  ? 'active' : '' ?>"><i class="bi bi-book"></i><span>Hafalan</span></a>
            <a href="?tab=murajaah" class="ustadz-nav-link <?= $page_tab === 'murajaah' ? 'active' : '' ?>"><i class="bi bi-arrow-counterclockwise"></i><span>Murajaah</span></a>
            <a href="?tab=rekap"    class="ustadz-nav-link <?= $page_tab === 'rekap'    ? 'active' : '' ?>"><i class="bi bi-file-earmark-text"></i><span>Rekap Tahfiz</span></a>
            <a href="?tab=target"   class="ustadz-nav-link <?= $page_tab === 'target'   ? 'active' : '' ?>"><i class="bi bi-bullseye"></i><span>Target</span></a>
        </nav>

        <div class="ustadz-sidebar-footer">
            <a href="../auth/logout.php" class="btn-sidebar-logout">
                <i class="bi bi-box-arrow-right me-1"></i>Keluar Sesi
            </a>
        </div>
    </aside>

    <!-- ═══ KONTEN UTAMA ═══════════════════════════════════════ -->
    <div class="ustadz-content-wrapper">

        <!-- Top Bar -->
        <header class="bg-white border-bottom px-4 py-3 d-flex justify-content-between align-items-center shadow-sm">
            <div class="d-flex align-items-center gap-3">
                <img src="../../assets/images/logo-tahfiz-baru.png" alt="Logo" class="topbar-logo">
                <div class="d-flex align-items-center gap-2 small text-muted">
                    <span>Ruang Ustadz</span>
                    <span>›</span>
                    <span class="fw-bold text-dark text-capitalize">
                        <?php
                        $tab_titles = [
                            'hafalan'  => 'Catat Hafalan Baru',
                            'murajaah' => 'Dashboard & Catat Murajaah',
                            'target'   => 'Target Hafalan Santri',
                            'rekap'    => 'Rekap Tahfiz',
                            'profil'   => 'Profil & Kata Sandi',
                        ];
                        echo $tab_titles[$page_tab] ?? 'Dashboard';
                        ?>
                    </span>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="badge px-3 py-2 fw-semibold text-white" style="background-color: #11756D;">
                    <i class="bi bi-bookmark-star-fill me-1"></i>Halaqah <?= htmlspecialchars($halaqah_name) ?> (<?= $total_santri_binaan ?> Santri)
                </span>
                <a href="?tab=hafalan" class="btn btn-tahfiz btn-sm px-3 fw-bold rounded-3">
                    <i class="bi bi-plus-circle me-1"></i>Catat Setoran
                </a>
            </div>
        </header>

        <!-- Main Body -->
        <main class="p-4 flex-grow-1">

            <?php if (!empty($notif_sukses)): ?>
                <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm d-flex align-items-center gap-2 mb-4" role="alert">
                    <i class="bi bi-check-circle-fill fs-5 text-success"></i>
                    <div><?= htmlspecialchars($notif_sukses) ?></div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            <?php if (!empty($notif_error)): ?>
                <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm d-flex align-items-center gap-2 mb-4" role="alert">
                    <i class="bi bi-exclamation-triangle-fill fs-5 text-danger"></i>
                    <div><?= htmlspecialchars($notif_error) ?></div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php require_once "tabs/{$page_tab}.php"; ?>

        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
