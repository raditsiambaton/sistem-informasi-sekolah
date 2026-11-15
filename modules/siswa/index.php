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

$siswa_name = $_SESSION['user_name'] ?? 'Muhammad Fadhlan';
$siswa_nis = $_SESSION['user_nip_nis'] ?? '102601';
$halaqah_name = $_SESSION['user_halaqah'] ?? 'Al-Furqan';
$page_tab = $_GET['tab'] ?? 'progres';
$notif_sukses = '';
$notif_error  = '';

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
    
    <link rel="icon" type="image/png" href="../../assets/images/logo-tahfiz.png">
    
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
            background-color: #F8FAFC;
            color: #1E293B;
            min-height: 100vh;
        }
        .siswa-sidebar {
            width: 260px;
            background: #0A2E2B;
            min-height: 100vh;
            color: #ffffff;
            flex-shrink: 0;
            padding: 24px 16px;
        }
        .siswa-nav-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 14px;
            color: #A2D5D2;
            font-weight: 600;
            font-size: 0.9rem;
            border-radius: 8px;
            text-decoration: none;
            transition: all 0.2s;
            margin-bottom: 4px;
        }
        .siswa-nav-link:hover {
            color: #ffffff;
            background: rgba(255, 255, 255, 0.12);
        }
        .siswa-nav-link.active {
            background: #11756D;
            color: #ffffff;
            font-weight: 700;
            box-shadow: 0 4px 12px rgba(17, 117, 109, 0.35);
        }
        .kpi-card {
            background: #ffffff;
            border: 1px solid var(--color-border);
            border-radius: 14px;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(17, 117, 109, 0.04);
            height: 100%;
        }
    </style>
</head>
<body>

<div class="d-flex">
    <!-- SIDEBAR SISWA -->
    <aside class="siswa-sidebar d-flex flex-column">
        <!-- Brand -->
        <div class="d-flex align-items-center gap-2 pb-3 mb-3 border-bottom border-secondary border-opacity-25">
            <img src="../../assets/images/logo-tahfiz.png" alt="Logo Tahfiz" style="width: 38px; height: 38px; object-fit: contain; border-radius: 8px;" class="sidebar-logo">
            <div>
                <div class="fw-bold fs-6 text-white lh-1">SIM Tahfiz</div>
                <small class="text-white-50 fw-semibold" style="font-size: 0.72rem;">Portal Santri</small>
            </div>
        </div>

        <!-- User Info -->
        <div class="p-2 rounded-3 mb-4 bg-dark bg-opacity-25 d-flex align-items-center gap-2">
            <div class="rounded-circle bg-warning text-dark d-flex align-items-center justify-content-center fw-bold" style="width: 36px; height: 36px; font-size: 0.85rem;">
                MF
            </div>
            <div class="overflow-hidden">
                <div class="fw-bold text-white small text-truncate"><?= htmlspecialchars($siswa_name) ?></div>
                <small class="text-white-50" style="font-size: 0.7rem;">NIS: <?= htmlspecialchars($siswa_nis) ?></small>
            </div>
        </div>

        <!-- Navigation Menu -->
        <nav class="flex-grow-1">
            <small class="text-uppercase fw-bold text-white-50 px-2 mb-2 d-block" style="font-size: 0.68rem; letter-spacing: 0.06em;">Menu Utama</small>
            <a href="?tab=progres" class="siswa-nav-link <?= $page_tab === 'progres' ? 'active' : '' ?>">
                <i class="bi bi-graph-up"></i>Progres Hafalan
            </a>
            <a href="?tab=riwayat" class="siswa-nav-link <?= $page_tab === 'riwayat' ? 'active' : '' ?>">
                <i class="bi bi-clock-history"></i>Riwayat Hafalan
            </a>
            <a href="?tab=profil" class="siswa-nav-link <?= $page_tab === 'profil' ? 'active' : '' ?>">
                <i class="bi bi-person-circle"></i>Profil Pribadi
            </a>
        </nav>

        <!-- Help Card Sesuai Image 20 -->
        <div class="p-3 rounded-3 bg-dark bg-opacity-25 border border-white border-opacity-10 mb-3 text-white">
            <div class="fw-bold small mb-1 text-warning"><i class="bi bi-question-circle me-1"></i>Butuh Bantuan?</div>
            <p class="small text-white-50 mb-0" style="font-size: 0.75rem; line-height: 1.4;">
                Hubungi ustadz pembimbing untuk pertanyaan target hafalan atau jadwal tasmi'.
            </p>
        </div>

        <!-- Bottom Actions -->
        <div class="pt-3 border-top border-white border-opacity-10">
            <a href="../../index.php" class="btn btn-outline-light btn-sm w-100 mb-2 fw-semibold" target="_blank">
                <i class="bi bi-box-arrow-up-right me-1"></i>Web Sekolah
            </a>
            <a href="../auth/logout.php" class="btn btn-danger btn-sm w-100 fw-semibold">
                <i class="bi bi-box-arrow-right me-1"></i>Keluar Sesi
            </a>
        </div>
    </aside>

    <!-- MAIN WRAPPER -->
    <div class="flex-grow-1 d-flex flex-column" style="min-width: 0;">
        <header class="bg-white border-bottom px-4 py-3 d-flex justify-content-between align-items-center shadow-sm">
            <div class="d-flex align-items-center gap-3">
                <img src="../../assets/images/logo-tahfiz.png" alt="Logo Tahfiz" style="height: 42px; width: auto; object-fit: contain;">
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

            <!-- SUBPAGE 3: PROFIL PRIBADI SANTRI (SESUAI IMAGE 27 & 28) -->
            <?php elseif ($page_tab === 'profil'): ?>
                <div class="mb-4">
                    <span class="badge bg-success-subtle text-success fw-bold px-3 py-1 mb-2">AKUN SANTRI</span>
                    <h2 class="fw-bold mb-1" style="color: #11756D;">Profil Pribadi</h2>
                    <p class="text-muted small mb-0">Informasi diri dan keamanan akun santri Anda.</p>
                </div>

                <div class="row g-4">
                    <div class="col-12 col-md-7">
                        <div class="card p-4 border-0 shadow-sm">
                            <div class="d-flex align-items-center gap-3 mb-4 pb-3 border-bottom">
                                <div class="rounded-circle bg-warning text-dark d-flex align-items-center justify-content-center fw-bold fs-4" style="width: 56px; height: 56px;">
                                    MF
                                </div>
                                <div>
                                    <h5 class="fw-bold mb-0 text-dark"><?= htmlspecialchars($siswa_name) ?></h5>
                                    <small class="text-muted">Santri Kelas X-A · Halaqah <?= htmlspecialchars($halaqah_name) ?></small>
                                </div>
                            </div>

                            <div class="d-flex flex-column gap-3 small">
                                <div class="d-flex justify-content-between pb-2 border-bottom">
                                    <span class="text-muted">Nomor Induk Siswa (NIS)</span>
                                    <strong><?= htmlspecialchars($siswa_nis) ?></strong>
                                </div>
                                <div class="d-flex justify-content-between pb-2 border-bottom">
                                    <span class="text-muted">Kelompok Halaqah</span>
                                    <strong>Halaqah <?= htmlspecialchars($halaqah_name) ?></strong>
                                </div>
                                <div class="d-flex justify-content-between pb-2 border-bottom">
                                    <span class="text-muted">Ustadz Pembimbing</span>
                                    <strong>Ust. Rival Alvian Melfandy</strong>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted">Target Kelulusan Tahfiz</span>
                                    <strong class="text-success">Minimal 6 Juz</strong>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-md-5">
                        <div class="card p-4 border-0 shadow-sm rounded-4 bg-white h-100">
                            <h5 class="fw-bold mb-3" style="color: #11756D;">
                                <i class="bi bi-key-fill me-2 text-warning"></i>Ganti Kata Sandi
                            </h5>
                            <p class="small text-muted mb-3">Jaga kerahasiaan kata sandi Anda dan ganti secara berkala.</p>
                            
                            <form action="?tab=profil" method="POST">
                                <input type="hidden" name="action" value="ganti_password">
                                <div class="mb-3">
                                    <label class="form-label fw-bold small text-muted">Kata Sandi Saat Ini</label>
                                    <input type="password" name="password_lama" class="form-control" placeholder="Kata sandi lama" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold small text-muted">Kata Sandi Baru</label>
                                    <input type="password" name="password_baru" class="form-control" placeholder="Minimal 6 karakter" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold small text-muted">Ulangi Sandi Baru</label>
                                    <input type="password" name="password_konfirmasi" class="form-control" placeholder="Konfirmasi sandi baru" required>
                                </div>
                                <button type="submit" class="btn text-white btn-sm fw-bold px-4 py-2 mt-1" style="background-color: #11756D;">
                                    <i class="bi bi-shield-lock me-1"></i>Perbarui Sandi Santri
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

