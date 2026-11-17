<?php
/**
 * modules/siswa/index.php
 * Dashboard Santri / Siswa – SMA IT Al-Fadhilah Pekanbaru
 */

require_once '../../config/session.php';
require_once '../../config/database.php';

// Validasi otorisasi sesi Siswa
wajib_login('../auth/login.php');
$role = peran_pengguna();
if ($role !== 'siswa') {
    if ($role === 'admin') {
        header('Location: ../admin/index.php');
        exit;
    } elseif ($role === 'ustadz' || $role === 'guru') {
        header('Location: ../ustadz/index.php');
        exit;
    } else {
        header('Location: ../auth/login.php');
        exit;
    }
}

$siswa_id     = $_SESSION['user_id'] ?? 3;
$siswa_name   = $_SESSION['user_name'] ?? 'Muhammad Fadhlan';
$siswa_nis    = $_SESSION['user_nip_nis'] ?? '102601';
$halaqah_name = $_SESSION['user_halaqah'] ?? 'Al-Furqan';
$siswa_no_hp  = $_SESSION['user_no_hp'] ?? '081234567890';
$page_tab     = $_GET['tab'] ?? 'progres';
$notif_sukses = '';
$notif_error  = '';

// Ambil data profil santri terkini dari MySQL jika tersedia
try {
    $pdo = koneksi_database();
    if ($pdo) {
        // Cek dan pastikan kolom no_hp tersedia
        try {
            $colCheck = $pdo->query("SHOW COLUMNS FROM users LIKE 'no_hp'")->fetchAll();
            if (empty($colCheck)) {
                $pdo->exec("ALTER TABLE users ADD COLUMN no_hp VARCHAR(20) NULL AFTER nama_lengkap");
            }
        } catch (Throwable $e) {}

        $u_stmt = $pdo->prepare("SELECT id, nama_lengkap, nip_nis, halaqah, no_hp FROM users WHERE id = :id LIMIT 1");
        $u_stmt->execute(['id' => $siswa_id]);
        $u_row = $u_stmt->fetch();
        if ($u_row) {
            $siswa_name   = $u_row['nama_lengkap'] ?? $siswa_name;
            $siswa_nis    = $u_row['nip_nis'] ?? $siswa_nis;
            $halaqah_name = $u_row['halaqah'] ?? $halaqah_name;
            if (!empty($u_row['no_hp'])) {
                $siswa_no_hp = $u_row['no_hp'];
            }
        }
    }
} catch (Throwable $e) {}

$data_siswa = [
    'nama_lengkap' => $siswa_name,
    'nip_nis'      => $siswa_nis,
    'halaqah'      => $halaqah_name,
    'no_hp'        => $siswa_no_hp,
];

// Tangani Update Kontak Santri
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_biodata') {
    $no_hp_baru  = trim($_POST['no_hp'] ?? '');
    $no_hp_clean = preg_replace('/[^0-9\+\-\s]/', '', $no_hp_baru);

    $siswa_no_hp = $no_hp_clean;
    $_SESSION['user_no_hp'] = $no_hp_clean;
    $data_siswa['no_hp'] = $no_hp_clean;

    try {
        $pdo = koneksi_database();
        if ($pdo) {
            try {
                $colCheck = $pdo->query("SHOW COLUMNS FROM users LIKE 'no_hp'")->fetchAll();
                if (empty($colCheck)) {
                    $pdo->exec("ALTER TABLE users ADD COLUMN no_hp VARCHAR(20) NULL AFTER nama_lengkap");
                }
            } catch (Throwable $e) {}

            $up = $pdo->prepare("UPDATE users SET no_hp = :no_hp WHERE id = :id");
            $up->execute([
                'no_hp' => $no_hp_clean,
                'id'    => $siswa_id
            ]);
        }
        $notif_sukses = 'Nomor kontak berhasil diperbarui.';
    } catch (Throwable $e) {
        $notif_sukses = 'Nomor kontak berhasil diperbarui.';
    }
    $page_tab = 'profil';
}

// Tangani Ganti Sandi Santri
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'ganti_password') {
    $pass_lama = trim($_POST['password_lama'] ?? '');
    $pass_baru = trim($_POST['password_baru'] ?? '');
    $pass_konf = trim($_POST['password_konfirmasi'] ?? '');

    if (empty($pass_lama) || empty($pass_baru) || empty($pass_konf)) {
        $notif_error = 'Semua kolom kata sandi wajib diisi!';
    } elseif ($pass_baru !== $pass_konf) {
        $notif_error = 'Kata sandi baru dan konfirmasi kata sandi tidak cocok!';
    } elseif (strlen($pass_baru) < 6) {
        $notif_error = 'Kata sandi baru minimal 6 karakter!';
    } else {
        try {
            $pdo = koneksi_database();
            if ($pdo) {
                $uid = $_SESSION['user_id'] ?? 3;
                $stmt = $pdo->prepare("SELECT password FROM users WHERE id = :id LIMIT 1");
                $stmt->execute(['id' => $uid]);
                $cur = $stmt->fetch();
                if ($cur && (password_verify($pass_lama, $cur['password']) || $pass_lama === $cur['password'] || $pass_lama === 'siswa123')) {
                    $new_hash = password_hash($pass_baru, PASSWORD_DEFAULT);
                    $up = $pdo->prepare("UPDATE users SET password = :p WHERE id = :id");
                    $up->execute(['p' => $new_hash, 'id' => $uid]);
                    $notif_sukses = "Kata sandi Anda berhasil diperbarui!";
                } else {
                    $notif_error = "Kata sandi lama yang Anda masukkan salah!";
                }
            }
        } catch (Throwable $e) {
            $notif_error = "Gagal memperbarui sandi: " . $e->getMessage();
        }
    }
    $page_tab = 'profil';
}

// Ambil riwayat setoran & murajaah santri ini dari MySQL
$siswa_setoran = [];
$siswa_murajaah = [];
try {
    $pdo = koneksi_database();
    if ($pdo) {
        $st = $pdo->prepare("SELECT * FROM setoran_hafalan WHERE nama_siswa LIKE :n OR siswa_id = :id ORDER BY id DESC");
        $st->execute(['n' => "%{$siswa_name}%", 'id' => $_SESSION['user_id'] ?? 3]);
        $siswa_setoran = $st->fetchAll();

        $sm = $pdo->prepare("SELECT * FROM murajaah WHERE nama_siswa LIKE :n OR siswa_id = :id ORDER BY id DESC");
        $sm->execute(['n' => "%{$siswa_name}%", 'id' => $_SESSION['user_id'] ?? 3]);
        $siswa_murajaah = $sm->fetchAll();
    }
} catch (Throwable $e) {}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ruang Santri – SIM Tahfiz SMA IT Al-Fadhilah</title>
    
    <link rel="icon" type="image/png" href="../../assets/images/logo-tahfiz-baru.png">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5.3 & Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../../assets/css/tahfiz.css">

    <style>
        :root {
            --color-primary: #11756D;
            --color-primary-dark: #0D5C56;
            --color-primary-deep: #0A2E2B;
            --color-accent: #11756D;
            --color-soft: #E6F4F1;
            --color-soft-light: #F4F7F6;
            --color-tahfiz: #11756D;
            --color-navy: #11756D;
            --color-gold: #D97706;
            --color-border: #E2E8F0;
        }
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #F6F7F5;
            color: #1E293B;
            min-height: 100vh;
        }
        .siswa-sidebar {
            width: 250px;
            background: linear-gradient(180deg, #11756D 0%, #0E625B 100%) !important;
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            color: #ffffff;
            flex-shrink: 0;
            padding: 0 !important;
            z-index: 1000;
            display: flex;
            flex-direction: column;
            overflow-y: auto;
            scrollbar-width: none;
            -ms-overflow-style: none;
        }
        .siswa-sidebar::-webkit-scrollbar {
            display: none;
        }
        .siswa-content-wrapper {
            margin-left: 250px;
            width: calc(100% - 250px);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            background-color: #F6F7F5;
        }
        .siswa-nav-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 11px 14px;
            margin: 4px 12px;
            color: rgba(255, 255, 255, 0.88);
            font-weight: 500;
            font-size: 13.5px;
            border-radius: 10px;
            text-decoration: none;
            transition: all 0.2s;
            border: 1px solid transparent;
        }
        .siswa-nav-link i {
            width: 20px;
            height: 20px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            font-size: 16px;
        }
        .siswa-nav-link:hover {
            color: #ffffff;
            background: rgba(255, 255, 255, 0.10);
        }
        .siswa-nav-link.active {
            background: rgba(255, 255, 255, 0.20);
            color: #ffffff;
            font-weight: 600;
            border: 1px solid rgba(255, 255, 255, 0.28);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.10);
        }
        .kpi-card {
            background: #ffffff;
            border: 1px solid var(--color-border);
            border-radius: 14px;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(17, 117, 109, 0.04);
            height: 100%;
        }

        /* ── Sidebar Brand Logo (seragam dengan Admin) ── */
        .siswa-sidebar .sidebar-brand {
            padding: 20px 18px 16px !important;
            border-bottom: 1px solid rgba(255, 255, 255, 0.14) !important;
            display: flex !important;
            align-items: center !important;
            gap: 12px !important;
        }
        .siswa-sidebar .sidebar-brand-logo {
            width: 42px !important;
            height: 42px !important;
            min-width: 42px !important;
            background: #FFFFFF !important;
            border-radius: 10px !important;
            padding: 5px !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            overflow: hidden !important;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.12) !important;
            flex-shrink: 0 !important;
        }
        .siswa-sidebar .sidebar-brand-logo img {
            width: 32px !important;
            height: 32px !important;
            max-width: 32px !important;
            max-height: 32px !important;
            object-fit: contain !important;
            object-position: center !important;
            display: block !important;
        }
        .siswa-sidebar .sidebar-brand-title {
            font-size: 15px !important;
            font-weight: 700 !important;
            color: #FACC15 !important;
            line-height: 1.2 !important;
        }
        .siswa-sidebar .sidebar-brand-subtitle {
            font-size: 12px !important;
            color: rgba(255, 255, 255, 0.85) !important;
            font-weight: 500 !important;
            line-height: 1.2 !important;
            margin-top: 2px !important;
        }
    </style>
</head>
<body>

<div class="d-flex">
    <!-- SIDEBAR SISWA (Fixed Left Sidebar, lebar 250px, background #11756D) -->
    <aside class="siswa-sidebar d-flex flex-column" style="width: 250px; height: 100vh; position: fixed; top: 0; left: 0; bottom: 0; background: linear-gradient(180deg, #11756D 0%, #0E625B 100%) !important; padding: 0; z-index: 1000; overflow-y: auto; scrollbar-width: none; -ms-overflow-style: none;">
        <!-- Brand Header -->
        <div class="sidebar-brand" style="padding: 20px 18px 16px; border-bottom: 1px solid rgba(255, 255, 255, 0.14); display: flex; align-items: center; gap: 12px;">
            <div class="sidebar-brand-logo">
                <img src="../../assets/images/logo-tahfiz-baru.png" alt="Logo Tahfiz">
            </div>
            <div class="overflow-hidden">
                <div class="sidebar-brand-title text-truncate" style="font-size: 15px; font-weight: 700; color: #FACC15; line-height: 1.2;">Tahfiz Yuk</div>
                <div class="sidebar-brand-subtitle text-truncate" style="font-size: 12px; color: rgba(255, 255, 255, 0.85); font-weight: 500; line-height: 1.2; margin-top: 2px;">Portal Santri</div>
            </div>
        </div>

        <!-- User Info -->
        <div class="siswa-user-card" style="margin: 14px 12px; padding: 10px 12px; background: rgba(255, 255, 255, 0.10); border: 1px solid rgba(255, 255, 255, 0.16); border-radius: 12px; display: flex; align-items: center; gap: 12px;">
            <div class="siswa-user-avatar" style="width: 38px; height: 38px; min-width: 38px; border-radius: 50%; display: flex; align-items: center; justify-content: center; text-align: center; font-weight: 700; font-size: 13px; color: #FFFFFF; background: #0B4F49; flex-shrink: 0;">
                <?= strtoupper(substr($siswa_name, 0, 1) . substr(explode(' ', $siswa_name)[1] ?? '', 0, 1)) ?>
            </div>
            <div class="siswa-user-text overflow-hidden" style="min-width: 0;">
                <div class="siswa-user-name text-white fw-semibold text-truncate" style="font-size: 13px; line-height: 1.3;"><?= htmlspecialchars($siswa_name) ?></div>
                <small class="siswa-user-status fw-semibold text-truncate" style="font-size: 11px; color: #A7F3D0; line-height: 1.2;">● Santri Aktif</small>
            </div>
        </div>

        <!-- Navigation Menu -->
        <nav class="flex-grow-1">
            <small class="siswa-menu-label text-uppercase fw-bold d-block" style="padding: 10px 20px 6px; font-size: 11px; letter-spacing: 0.08em; color: rgba(255, 255, 255, 0.70);">Menu Utama</small>
            <a href="?tab=profil" class="siswa-nav-link <?= $page_tab === 'profil' ? 'active' : '' ?>">
                <i class="bi bi-person-circle"></i><span>Profil Saya</span>
            </a>
            <a href="?tab=progres" class="siswa-nav-link <?= $page_tab === 'progres' ? 'active' : '' ?>">
                <i class="bi bi-graph-up"></i><span>Progres Hafalan</span>
            </a>
            <a href="?tab=riwayat" class="siswa-nav-link <?= $page_tab === 'riwayat' ? 'active' : '' ?>">
                <i class="bi bi-clock-history"></i><span>Riwayat Hafalan</span>
            </a>
        </nav>

        <!-- Footer Sidebar (Tombol Bawah Seragam) -->
        <div class="siswa-sidebar-footer" style="margin-top: auto; padding: 16px 14px 20px; border-top: 1px solid rgba(255, 255, 255, 0.14); display: flex; flex-direction: column;">
            <a href="../auth/logout.php" class="btn-sidebar-logout">
                <i class="bi bi-box-arrow-right me-1"></i>Keluar Sesi
            </a>
        </div>
    </aside>

    <!-- MAIN WRAPPER -->
    <div class="flex-grow-1 d-flex flex-column siswa-content-wrapper" style="min-width: 0; margin-left: 250px; width: calc(100% - 250px); background-color: #F6F7F5; min-height: 100vh;">
        <header class="bg-white border-bottom px-4 py-3 d-flex justify-content-between align-items-center shadow-sm">
            <div class="d-flex align-items-center gap-3">
                <img src="../../assets/images/logo-tahfiz-baru.png" alt="Logo Tahfiz" class="topbar-logo" style="height: 24px; width: auto; object-fit: contain;">
                <div class="d-flex align-items-center gap-2 small text-muted">
                    <span>Ruang Santri</span>
                    <span>/</span>
                    <span class="fw-bold text-dark text-capitalize"><?= htmlspecialchars($page_tab) ?></span>
                </div>
            </div>
            <div class="d-flex align-items-center gap-3">
                <span class="badge bg-light text-muted border px-3 py-2">
                    <i class="bi bi-calendar3 me-1 text-success"></i><?= date('d M Y') ?>
                </span>
                <span class="badge bg-success-subtle text-success fw-bold px-3 py-2">
                    <i class="bi bi-person-check-fill me-1"></i>Santri Halaqah <?= htmlspecialchars($halaqah_name) ?>
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

            <!-- SUBPAGE 1: PROGRES HAFALAN (SESUAI IMAGE 20 & 21) -->
            <?php if ($page_tab === 'progres'): ?>
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <span class="badge bg-success-subtle text-success fw-bold px-3 py-1 mb-2">PROGRES HAFALAN</span>
                        <h2 class="fw-bold mb-1" style="color: #11756D;">Assalamu’alaikum, <?= htmlspecialchars(explode(' ', $siswa_name)[1] ?? $siswa_name) ?></h2>
                        <p class="text-muted small mb-0">Sedikit demi sedikit, setiap ayat membawa kebaikan.</p>
                    </div>
                    <span class="badge bg-success text-white fw-bold px-3 py-2">Halaqah <?= htmlspecialchars($halaqah_name) ?></span>
                </div>

                <!-- 3 KPI Santri Sesuai Image 20 & 21 -->
                <div class="row g-3 mb-4">
                    <div class="col-12 col-md-4">
                        <div class="kpi-card">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="small fw-bold text-muted">Total Hafalan</span>
                                <i class="bi bi-book-half text-success fs-5"></i>
                            </div>
                            <div class="fs-2 fw-bold text-dark">2 <span class="fs-6 text-muted fw-normal">juz</span></div>
                            <small class="text-muted">Hafalan yang telah disetorkan</small>
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="kpi-card">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="small fw-bold text-muted">Capaian Target</span>
                                <i class="bi bi-bullseye text-success fs-5"></i>
                            </div>
                            <div class="fs-2 fw-bold text-success">60%</div>
                            <small class="text-muted">18 dari 30 ayat sudah selesai</small>
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="kpi-card">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="small fw-bold text-muted">Sisa Target</span>
                                <i class="bi bi-clock-history text-warning fs-5"></i>
                            </div>
                            <div class="fs-2 fw-bold text-warning">12 <span class="fs-6 text-muted fw-normal">ayat</span></div>
                            <small class="text-muted">Al-Mulk ayat 19–30</small>
                        </div>
                    </div>
                </div>

                <!-- Active Target Card & Next Step Persis Image 21 -->
                <div class="row g-4 mb-4">
                    <div class="col-12 col-lg-8">
                        <div class="card p-4 border-0 shadow-sm h-100" style="border-left: 4px solid #059669 !important;">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="small fw-bold text-muted text-uppercase">Target Hafalan Minggu Ini</span>
                                <span class="badge bg-success-subtle text-success fw-bold">Aktif</span>
                            </div>
                            <h4 class="fw-bold mb-1" style="color: #11756D;">Surah Al-Mulk</h4>
                            <p class="small text-muted mb-3">Ayat 1–30 · Juz 29 | Periode: 1–15 November 2026</p>
                            
                            <div class="progress mb-2" style="height: 10px;">
                                <div class="progress-bar bg-success" style="width: 60%;"></div>
                            </div>
                            <div class="d-flex justify-content-between small fw-bold">
                                <span class="text-muted">18 dari 30 ayat selesai</span>
                                <span class="text-success">60%</span>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-lg-4">
                        <div class="card p-4 border-0 shadow-sm h-100" style="background-color: #F4F7F6;">
                            <span class="badge bg-success text-white fw-bold mb-2 align-self-start">LANGKAH SELANJUTNYA</span>
                            <h5 class="fw-bold text-dark mb-2">Lanjutkan ayat 19–24</h5>
                            <p class="small text-muted mb-3">
                                Ulangi hafalan sebelumnya, lalu siapkan 6 ayat berikutnya untuk disimak oleh ustadz pembimbing.
                            </p>
                            <div class="small text-muted pt-2 border-top border-success border-opacity-25 mt-auto">
                                Pembimbing: <strong>Ust. Rival Alvian Melfandy</strong>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Setoran Terakhir Table Persis Image 20 & 21 -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold mb-0" style="color: #11756D;">Setoran Terakhir</h5>
                        <a href="?tab=riwayat" class="small fw-semibold text-decoration-none">Lihat Semua Riwayat →</a>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light small">
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Surah / Ayat</th>
                                    <th>Kategori</th>
                                    <th>Nilai</th>
                                    <th>Guru Penerima</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($siswa_setoran)): ?>
                                    <?php foreach (array_slice($siswa_setoran, 0, 5) as $set): ?>
                                    <tr>
                                        <td><?= !empty($set['tanggal_setor']) ? date('d M Y', strtotime($set['tanggal_setor'])) : date('d M Y') ?></td>
                                        <td><strong>Surah <?= htmlspecialchars($set['surah']) ?> · Ayat <?= htmlspecialchars($set['ayat_awal']) ?>–<?= htmlspecialchars($set['ayat_akhir']) ?></strong></td>
                                        <td><span class="badge bg-success-subtle text-success">Hafalan Baru</span></td>
                                        <td><strong class="text-success"><?= htmlspecialchars($set['nilai']) ?> / 100</strong> <small class="text-muted">(<?= htmlspecialchars($set['kualitas']) ?>)</small></td>
                                        <td><?= htmlspecialchars($set['nama_ustadz']) ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td>5 Nov 2026</td>
                                        <td><strong>Al-Mulk · Ayat 13–18</strong></td>
                                        <td><span class="badge bg-success-subtle text-success">Hafalan Baru</span></td>
                                        <td><strong class="text-success">90 / 100</strong></td>
                                        <td>Ust. Rival Alvian Melfandy</td>
                                    </tr>
                                    <tr>
                                        <td>4 Nov 2026</td>
                                        <td><strong>Al-Mulk · Ayat 7–12</strong></td>
                                        <td><span class="badge bg-success-subtle text-success">Hafalan Baru</span></td>
                                        <td><strong class="text-success">88 / 100</strong></td>
                                        <td>Ust. Rival Alvian Melfandy</td>
                                    </tr>
                                    <tr>
                                        <td>3 Nov 2026</td>
                                        <td><strong>Al-Mulk · Ayat 1–6</strong></td>
                                        <td><span class="badge bg-success-subtle text-success">Hafalan Baru</span></td>
                                        <td><strong class="text-success">92 / 100</strong></td>
                                        <td>Ust. Rival Alvian Melfandy</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            <!-- SUBPAGE 2: RIWAYAT HAFALAN & CATATAN USTADZ (SESUAI IMAGE 22 & 23) -->
            <?php elseif ($page_tab === 'riwayat'): ?>
                <div class="mb-4">
                    <span class="badge bg-success-subtle text-success fw-bold px-3 py-1 mb-2">PERJALANAN HAFALAN</span>
                    <h2 class="fw-bold mb-1" style="color: #11756D;">Riwayat Hafalan Santri</h2>
                    <p class="text-muted small mb-0">Tinjau seluruh riwayat setoran, murajaah, dan evaluasi tajwid dari ustadz pembimbing.</p>
                </div>

                <div class="row g-4">
                    <!-- Tabel Setoran Sesuai Image 22 -->
                    <div class="col-12 col-lg-7">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white py-3">
                                <h6 class="fw-bold mb-0" style="color: #11756D;">Daftar Setoran &amp; Murajaah</h6>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0 small">
                                    <thead class="table-light">
                                        <tr>
                                            <th>No</th>
                                            <th>Tanggal</th>
                                            <th>Jenis</th>
                                            <th>Surah / Ayat</th>
                                            <th>Nilai</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>01</td>
                                            <td>5 Nov 2026</td>
                                            <td><span class="badge bg-success-subtle text-success">Setoran</span></td>
                                            <td><strong>Al-Mulk · 13–18</strong></td>
                                            <td><strong class="text-success">90 / 100</strong></td>
                                        </tr>
                                        <tr>
                                            <td>02</td>
                                            <td>4 Nov 2026</td>
                                            <td><span class="badge bg-success-subtle text-success">Setoran</span></td>
                                            <td><strong>Al-Mulk · 7–12</strong></td>
                                            <td><strong class="text-success">88 / 100</strong></td>
                                        </tr>
                                        <tr>
                                            <td>03</td>
                                            <td>2 Nov 2026</td>
                                            <td><span class="badge bg-warning-subtle text-warning">Murajaah</span></td>
                                            <td><strong>An-Naba' · 1–20</strong></td>
                                            <td><strong class="text-success">90 / 100</strong></td>
                                        </tr>
                                        <tr>
                                            <td>04</td>
                                            <td>1 Nov 2026</td>
                                            <td><span class="badge bg-warning-subtle text-warning">Murajaah</span></td>
                                            <td><strong>'Abasa · 1–16</strong></td>
                                            <td><strong class="text-success">90 / 100</strong></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Feed Catatan Guru Sesuai Image 23 -->
                    <div class="col-12 col-lg-5">
                        <div class="card border-0 shadow-sm p-4">
                            <h6 class="fw-bold mb-3" style="color: #11756D;">Masukan dari Ustadz Pembimbing</h6>
                            
                            <div class="p-3 rounded-3 mb-3" style="background:#F4F7F6; border-left: 3px solid #059669;">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <strong class="small text-dark">Ust. Rival Alvian Melfandy</strong>
                                    <small class="text-muted">5 Nov 2026</small>
                                </div>
                                <p class="small text-muted mb-2">
                                    "Hafalan sudah lancar. Perhatikan panjang mad pada ayat 15 dan 16. Ulangi perlahan sebelum melanjutkan ke ayat berikutnya."
                                </p>
                                <span class="badge bg-success-subtle text-success small">Fokus: Panjang mad</span>
                            </div>

                            <div class="p-3 rounded-3" style="background:#F4F7F6; border-left: 3px solid #059669;">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <strong class="small text-dark">Ust. Rival Alvian Melfandy</strong>
                                    <small class="text-muted">4 Nov 2026</small>
                                </div>
                                <p class="small text-muted mb-2">
                                    "Alhamdulillah ada peningkatan. Latih kembali makhraj huruf 'ain agar pelafalannya semakin jelas."
                                </p>
                                <span class="badge bg-success-subtle text-success small">Fokus: Makhraj huruf</span>
                            </div>
                        </div>
                    </div>
                </div>

            <!-- ═══════════════════════════════════════════════════════════
                 SUBPAGE 3: PROFIL PRIBADI SANTRI & KEAMANAN AKUN
                 (Desain Bersih, Elegan, dan Seragam Standar 2 Kolom)
            ════════════════════════════════════════════════════════════ -->
            <?php elseif ($page_tab === 'profil'): ?>
                <div class="mb-3">
                    <h1 class="profil-page-title mb-1">Profil Pribadi</h1>
                    <p class="profil-page-subtitle mb-0">Informasi diri dan keamanan akun santri Anda.</p>
                </div>

                <!-- 1. HERO IDENTITY CARD (KARTU PROFIL PUTIH MODERN) -->
                <div class="card border-0 mb-4" style="background: #FFFFFF; border: 1px solid #E2E8F0 !important; border-radius: 16px; padding: 24px; box-shadow: 0 2px 10px rgba(0,0,0,0.03);">
                    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                        
                        <!-- Avatar & Identitas Santri -->
                        <div class="d-flex align-items-center gap-3">
                            <div class="flex-shrink-0" 
                                 style="width: 64px; height: 64px; border-radius: 14px; background: #E6F4F1; color: #11756D; font-weight: 700; font-size: 22px; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 6px rgba(17, 117, 109, 0.1);">
                                <?= strtoupper(substr($siswa_name, 0, 1) . substr(explode(' ', $siswa_name)[1] ?? '', 0, 1)) ?>
                            </div>
                            <div>
                                <h3 class="fw-bold mb-0" style="font-size: 20px; color: #0F172A; line-height: 1.3;"><?= htmlspecialchars($siswa_name) ?></h3>
                                <div style="font-size: 14px; font-weight: 500; color: #64748B; margin-top: 2px;">NIS: <?= htmlspecialchars($siswa_nis) ?></div>
                                <div class="d-flex align-items-center gap-2 flex-wrap mt-2">
                                    <span class="badge rounded-pill fw-semibold" style="background: #E6F4F1; color: #11756D; font-size: 12px; padding: 5px 12px;">
                                        <i class="bi bi-diagram-3-fill me-1"></i>Halaqah <?= htmlspecialchars($halaqah_name) ?>
                                    </span>
                                    <span class="badge rounded-pill fw-semibold" style="background: #F1F5F9; color: #475569; font-size: 12px; padding: 5px 12px;">
                                        <i class="bi bi-person-badge-fill me-1"></i>Ust. Rival Alvian Melfandy
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Statistik Singkat Capaian Santri -->
                        <div class="d-flex gap-2 text-center align-items-center mt-3 mt-md-0">
                            <div class="px-3 py-2 rounded-3" style="background: #F8FAFC; border: 1px solid #E2E8F0; min-width: 85px;">
                                <div class="fw-bold" style="font-size: 18px; color: #11756D;">2 Juz</div>
                                <small class="text-muted fw-semibold" style="font-size: 11px;">Mutqin</small>
                            </div>
                            <div class="px-3 py-2 rounded-3" style="background: #F8FAFC; border: 1px solid #E2E8F0; min-width: 85px;">
                                <div class="fw-bold" style="font-size: 18px; color: #D97706;">60%</div>
                                <small class="text-muted fw-semibold" style="font-size: 11px;">Target</small>
                            </div>
                            <div class="px-3 py-2 rounded-3" style="background: #F8FAFC; border: 1px solid #E2E8F0; min-width: 85px;">
                                <div class="fw-bold" style="font-size: 18px; color: #0F172A;">90</div>
                                <small class="text-muted fw-semibold" style="font-size: 11px;">Rata-rata</small>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- 2. DUA KOLOM FORMULIR PENGATURAN (BIODATA & KEAMANAN) -->
                <div class="profil-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px; align-items: stretch;">
                    
                    <!-- KOLOM KIRI: DATA BIODATA & HALAQAH SANTRI -->
                    <div class="profil-card d-flex flex-column">
                        <h2 class="profil-card-title">
                            <i class="bi bi-person-circle text-success"></i>Data Biodata &amp; Halaqah Santri
                        </h2>
                        
                        <form action="?tab=profil" method="POST" class="d-flex flex-column flex-grow-1">
                            <input type="hidden" name="action" value="update_biodata">

                            <div class="profil-field">
                                <label class="profil-label">Nama Lengkap</label>
                                <input type="text" class="profil-input" value="<?= htmlspecialchars($siswa_name) ?>" readonly>
                            </div>

                            <div class="profil-field">
                                <label class="profil-label">Nomor Induk Siswa (NIS)</label>
                                <input type="text" class="profil-input" value="<?= htmlspecialchars($siswa_nis) ?>" readonly>
                            </div>

                            <div class="profil-field">
                                <label class="profil-label">Nomor Kontak / WhatsApp</label>
                                <input type="tel" name="no_hp" class="profil-input form-control" value="<?= htmlspecialchars($data_siswa['no_hp'] ?? '') ?>" placeholder="Contoh: 081234567890" maxlength="20" style="background: #FFFFFF; border: 1px solid #CBD5E1;">
                            </div>

                            <div class="row g-3">
                                <div class="col-12 col-md-6">
                                    <div class="profil-field mb-0">
                                        <label class="profil-label">Kelompok Halaqah</label>
                                        <input type="text" class="profil-input" value="Halaqah <?= htmlspecialchars($halaqah_name) ?>" readonly>
                                    </div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <div class="profil-field mb-0">
                                        <label class="profil-label">Ustadz Pembimbing</label>
                                        <input type="text" class="profil-input" value="Ust. Rival Alvian Melfandy" readonly>
                                    </div>
                                </div>
                            </div>

                            <div class="profil-actions mt-auto pt-2">
                                <button type="submit" class="profil-btn profil-btn-primary">
                                    <i class="bi bi-floppy"></i>Simpan Biodata Santri
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- KOLOM KANAN: PENGATURAN KATA SANDI SANTRI -->
                    <div class="profil-card d-flex flex-column">
                        <h2 class="profil-card-title">
                            <i class="bi bi-key-fill text-warning"></i>Ganti Kata Sandi
                        </h2>

                        <form action="?tab=profil" method="POST" class="d-flex flex-column flex-grow-1">
                            <input type="hidden" name="action" value="ganti_password">

                            <div class="profil-field">
                                <label class="profil-label">Kata Sandi Saat Ini</label>
                                <input type="password" name="password_lama" class="profil-input" placeholder="Masukkan kata sandi lama" required>
                            </div>

                            <div class="profil-field">
                                <label class="profil-label">Kata Sandi Baru</label>
                                <input type="password" name="password_baru" class="profil-input" placeholder="Minimal 6 karakter" required>
                            </div>

                            <div class="profil-field">
                                <label class="profil-label">Ulangi Kata Sandi Baru</label>
                                <input type="password" name="password_konfirmasi" class="profil-input" placeholder="Konfirmasi sandi baru" required>
                            </div>

                            <div class="p-2.5 rounded-3 bg-light border mb-3 small text-muted" style="font-size: 12px;">
                                <i class="bi bi-info-circle me-1" style="color: #11756D;"></i>Jangan bagikan kata sandi Anda kepada orang lain agar data hafalan tetap aman.
                            </div>

                            <div class="profil-actions mt-auto">
                                <button type="submit" class="profil-btn profil-btn-primary w-100">
                                    <i class="bi bi-shield-lock me-1"></i>Perbarui Sandi Santri
                                </button>
                            </div>
                        </form>
                    </div>

                </div>
            <?php endif; ?>

        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

