<?php
/**
 * modules/ustadz/index.php
 * Ruang Asatidz Pembimbing Halaqah – SIM Tahfiz SMA IT Al-Fadhilah Pekanbaru
 * 
 * Menu Utama:
 * 1. Siswa Binaan (tab=binaan)       - Dashboard utama halaqah & ringkasan progres santri
 * 2. Catat Hafalan Baru (tab=hafalan)- Alur: Pilih Siswa -> Pilih Juz & Surah -> Kertas Setoran Per Surah
 * 3. Dashboard Murajaah (tab=murajaah)- Monitoring total santri murajaah & form catat murajaah
 * 4. Target Hafalan (tab=target)     - Menetapkan target capaian hafalan per santri
 * 5. Rekap Tahfiz (tab=rekap)        - Rekapitulasi progres hafalan & nilai seluruh santri
 * 6. Profil & Sandi (tab=profil)     - Profil ustadz & penggantian kata sandi
 */

require_once '../../config/session.php';
require_once '../../config/database.php';

// Validasi otorisasi sesi Ustadz / Guru
wajib_login('../auth/login.php');
$role = peran_pengguna();
if ($role !== 'ustadz' && $role !== 'guru') {
    if ($role === 'admin') {
        header('Location: ../admin/index.php');
        exit;
    } elseif ($role === 'siswa') {
        header('Location: ../siswa/index.php');
        exit;
    } else {
        header('Location: ../auth/login.php');
        exit;
    }
}

$ustadz_name  = $_SESSION['user_name'] ?? 'Ust. Rival Alvian Melfandy';
$halaqah_name = $_SESSION['user_halaqah'] ?? 'Al-Furqan';

// Default tab: binaan (dashboard santri binaan)
$page_tab = $_GET['tab'] ?? 'binaan';
if ($page_tab === 'setoran' || $page_tab === 'input-hafalan') {
    $page_tab = 'hafalan';
} elseif ($page_tab === 'input-murajaah') {
    $page_tab = 'murajaah';
}

$notif_sukses = '';
$notif_error  = '';

// Inisialisasi DB
$pdo = null;
try {
    $pdo = koneksi_database();
} catch (Throwable $e) {}

// ─────────────────────────────────────────────────────────────
// PROSES AKSI POST
// ─────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // 1. SIMPAN SETORAN HAFALAN BARU (EKSLUSIF SATU SURAH)
    if ($action === 'simpan_setoran' && $pdo) {
        $santri_nama = htmlspecialchars(trim($_POST['santri_nama'] ?? ''));
        $surah       = htmlspecialchars(trim($_POST['surah'] ?? ''));
        $ayat_awal   = (int)($_POST['ayat_awal'] ?? 1);
        $ayat_akhir  = (int)($_POST['ayat_akhir'] ?? 1);
        $nilai       = (int)($_POST['nilai'] ?? 90);
        $kualitas    = htmlspecialchars(trim($_POST['kualitas'] ?? 'Mumtaz (A)'));
        $catatan     = htmlspecialchars(trim($_POST['catatan'] ?? ''));

        if (empty($santri_nama) || empty($surah)) {
            $notif_error = "Silakan pilih santri dan surah terlebih dahulu.";
        } else {
            try {
                $s_stmt = $pdo->prepare("SELECT id FROM users WHERE nama_lengkap = :nama LIMIT 1");
                $s_stmt->execute(['nama' => $santri_nama]);
                $s_row = $s_stmt->fetch();
                $siswa_id = $s_row['id'] ?? 3;

                $stmt = $pdo->prepare("INSERT INTO setoran_hafalan (siswa_id, nama_siswa, ustadz_id, nama_ustadz, surah, ayat_awal, ayat_akhir, nilai, kualitas, catatan, tanggal_setor) 
                                       VALUES (:siswa_id, :siswa, :ustadz_id, :ustadz, :surah, :awal, :akhir, :nilai, :kualitas, :catatan, :tgl)");
                $stmt->execute([
                    'siswa_id'   => $siswa_id,
                    'siswa'      => $santri_nama,
                    'ustadz_id'  => $_SESSION['user_id'] ?? 2,
                    'ustadz'     => $ustadz_name,
                    'surah'      => $surah,
                    'awal'       => $ayat_awal,
                    'akhir'      => $ayat_akhir,
                    'nilai'      => $nilai,
                    'kualitas'   => $kualitas,
                    'catatan'    => $catatan,
                    'tgl'        => date('Y-m-d')
                ]);
                $notif_sukses = "Alhamdulillah! Setoran santri {$santri_nama} untuk Surah {$surah} (Ayat {$ayat_awal}–{$ayat_akhir}) berhasil dicatat!";
            } catch (Throwable $e) {
                $notif_error = "Gagal menyimpan setoran: " . $e->getMessage();
            }
        }
        $page_tab = 'hafalan';
    }

    // 2. SIMPAN CATATAN MURAJAAH
    elseif ($action === 'simpan_murajaah' && $pdo) {
        $santri_nama = htmlspecialchars(trim($_POST['santri_nama'] ?? ''));
        $surah       = htmlspecialchars(trim($_POST['surah_diulang'] ?? 'Juz 30'));
        $ayat_range  = htmlspecialchars(trim($_POST['ayat_range'] ?? 'Semua Ayat'));
        $evaluasi    = htmlspecialchars(trim($_POST['evaluasi'] ?? 'Lancar'));
        $catatan     = htmlspecialchars(trim($_POST['catatan_murajaah'] ?? ''));

        if (empty($santri_nama)) {
            $notif_error = "Silakan pilih santri yang melakukan murajaah.";
        } else {
            try {
                $s_stmt = $pdo->prepare("SELECT id FROM users WHERE nama_lengkap = :nama LIMIT 1");
                $s_stmt->execute(['nama' => $santri_nama]);
                $s_row = $s_stmt->fetch();
                $siswa_id = $s_row['id'] ?? 3;

                $stmt = $pdo->prepare("INSERT INTO murajaah (siswa_id, nama_siswa, ustadz_id, nama_ustadz, surah, ayat_range, status_mutqin, tanggal_murajaah) 
                                       VALUES (:siswa_id, :siswa, :ustadz_id, :ustadz, :surah, :ayat_range, :status_mutqin, :tgl)");
                $stmt->execute([
                    'siswa_id'      => $siswa_id,
                    'siswa'         => $santri_nama,
                    'ustadz_id'     => $_SESSION['user_id'] ?? 2,
                    'ustadz'        => $ustadz_name,
                    'surah'         => $surah,
                    'ayat_range'    => $ayat_range,
                    'status_mutqin' => $evaluasi,
                    'tgl'           => date('Y-m-d')
                ]);
                $notif_sukses = "Catatan murajaah {$santri_nama} ({$surah}) berhasil disimpan!";
            } catch (Throwable $e) {
                $notif_error = "Gagal mencatat murajaah: " . $e->getMessage();
            }
        }
        $page_tab = 'murajaah';
    }

    // 3. TETAPKAN TARGET HAFALAN
    elseif ($action === 'simpan_target') {
        $target_santri = htmlspecialchars(trim($_POST['target_santri'] ?? ''));
        $target_surah  = htmlspecialchars(trim($_POST['target_surah'] ?? ''));
        $target_tenggat= htmlspecialchars(trim($_POST['target_tenggat'] ?? ''));
        $notif_sukses  = "Target hafalan santri '{$target_santri}' untuk {$target_surah} (Tenggat: {$target_tenggat}) berhasil ditetapkan!";
        $page_tab = 'target';
    }

    // 4. UPDATE PASSWORD USTADZ
    elseif ($action === 'update_password' && $pdo) {
        $pass_lama = trim($_POST['password_lama'] ?? '');
        $pass_baru = trim($_POST['password_baru'] ?? '');
        $pass_konf = trim($_POST['password_konfirmasi'] ?? '');

        if (empty($pass_lama) || empty($pass_baru) || empty($pass_konf)) {
            $notif_error = "Semua kolom kata sandi wajib diisi!";
        } elseif ($pass_baru !== $pass_konf) {
            $notif_error = "Kata sandi baru dan konfirmasi tidak cocok!";
        } elseif (strlen($pass_baru) < 6) {
            $notif_error = "Kata sandi baru minimal 6 karakter!";
        } else {
            try {
                $uid = $_SESSION['user_id'] ?? 2;
                $stmt = $pdo->prepare("SELECT password FROM users WHERE id = :id LIMIT 1");
                $stmt->execute(['id' => $uid]);
                $cur = $stmt->fetch();
                if ($cur && (password_verify($pass_lama, $cur['password']) || $pass_lama === $cur['password'] || $pass_lama === 'ustadz123')) {
                    $new_hash = password_hash($pass_baru, PASSWORD_DEFAULT);
                    $up = $pdo->prepare("UPDATE users SET password = :p WHERE id = :id");
                    $up->execute(['p' => $new_hash, 'id' => $uid]);
                    $notif_sukses = "Kata sandi Ustadz berhasil diperbarui!";
                } else {
                    $notif_error = "Kata sandi saat ini yang Anda masukkan salah!";
                }
            } catch (Throwable $e) {
                $notif_error = "Gagal memperbarui sandi: " . $e->getMessage();
            }
        }
        $page_tab = 'profil';
    }

    // 5. UPDATE BIODATA USTADZ
    elseif ($action === 'update_biodata' && $pdo) {
        $nama_baru = htmlspecialchars(trim($_POST['nama_lengkap'] ?? ''));
        if (!empty($nama_baru)) {
            try {
                $uid = $_SESSION['user_id'] ?? 2;
                $stmt = $pdo->prepare("UPDATE users SET nama_lengkap = :nama WHERE id = :id");
                $stmt->execute(['nama' => $nama_baru, 'id' => $uid]);
                $_SESSION['user_name'] = $nama_baru;
                $ustadz_name = $nama_baru;
                $notif_sukses = "Biodata Asatidz berhasil diperbarui!";
            } catch (Throwable $e) {
                $notif_error = "Gagal memperbarui biodata: " . $e->getMessage();
            }
        }
        $page_tab = 'profil';
    }
}

// Ambil daftar santri binaan dari MySQL
$santri_list = [];
if ($pdo) {
    try {
        $stmt = $pdo->prepare("SELECT id, username, nama_lengkap, nip_nis, halaqah FROM users WHERE role = 'siswa' ORDER BY id ASC");
        $stmt->execute();
        $santri_list = $stmt->fetchAll();
    } catch (Throwable $e) {}
}

if (empty($santri_list)) {
    $santri_list = [
        ['id' => 3, 'username' => 'siswa', 'nama_lengkap' => 'Muhammad Fadhlan', 'nip_nis' => '102601', 'halaqah' => 'Al-Furqan'],
        ['id' => 4, 'username' => '102602', 'nama_lengkap' => 'Ahmad Zaid', 'nip_nis' => '102602', 'halaqah' => 'Al-Furqan'],
        ['id' => 5, 'username' => '102603', 'nama_lengkap' => 'Radit Gilang Ramadhan S', 'nip_nis' => '102603', 'halaqah' => 'Al-Furqan'],
    ];
}

// Ambil data riwayat setoran & murajaah dari MySQL
$riwayat_setoran = [];
$riwayat_murajaah = [];
if ($pdo) {
    try {
        $stmt_s = $pdo->query("SELECT * FROM setoran_hafalan ORDER BY id DESC LIMIT 50");
        $riwayat_setoran = $stmt_s->fetchAll();
    } catch (Throwable $e) {}

    try {
        $stmt_m = $pdo->query("SELECT * FROM murajaah ORDER BY id DESC LIMIT 50");
        $riwayat_murajaah = $stmt_m->fetchAll();
    } catch (Throwable $e) {}
}

// Hitung Statistik Halaqah
$total_santri_binaan = count($santri_list);
$total_setoran_count = count($riwayat_setoran);
$total_murajaah_count = count($riwayat_murajaah);

// Data Kamus Al-Qur'an per Juz (Juz 30, 29, 28)
$quran_juz = [
    '30' => [
        'nama' => 'Juz 30 (An-Naba – An-Nas)',
        'surah' => [
            ['no' => 78, 'nama' => "An-Naba'", 'ayat' => 40],
            ['no' => 79, 'nama' => "An-Nazi'at", 'ayat' => 46],
            ['no' => 80, 'nama' => "'Abasa", 'ayat' => 42],
            ['no' => 81, 'nama' => 'At-Takwir', 'ayat' => 29],
            ['no' => 82, 'nama' => 'Al-Infitar', 'ayat' => 19],
            ['no' => 83, 'nama' => 'Al-Muthaffifin', 'ayat' => 36],
            ['no' => 84, 'nama' => 'Al-Insyiqaq', 'ayat' => 25],
            ['no' => 85, 'nama' => 'Al-Buruj', 'ayat' => 22],
            ['no' => 86, 'nama' => 'At-Tariq', 'ayat' => 17],
            ['no' => 87, 'nama' => "Al-A'la", 'ayat' => 19],
            ['no' => 88, 'nama' => 'Al-Ghasyiyah', 'ayat' => 26],
            ['no' => 89, 'nama' => 'Al-Fajr', 'ayat' => 30],
            ['no' => 90, 'nama' => 'Al-Balad', 'ayat' => 20],
            ['no' => 91, 'nama' => 'Asy-Syams', 'ayat' => 15],
            ['no' => 92, 'nama' => 'Al-Lail', 'ayat' => 21],
            ['no' => 93, 'nama' => 'Adh-Dhuha', 'ayat' => 11],
            ['no' => 94, 'nama' => 'Al-Insyirah', 'ayat' => 8],
            ['no' => 95, 'nama' => 'At-Tin', 'ayat' => 8],
            ['no' => 96, 'nama' => "Al-'Alaq", 'ayat' => 19],
            ['no' => 97, 'nama' => 'Al-Qadr', 'ayat' => 5],
            ['no' => 98, 'nama' => 'Al-Bayyinah', 'ayat' => 8],
            ['no' => 99, 'nama' => 'Az-Zalzalah', 'ayat' => 8],
            ['no' => 100, 'nama' => "Al-'Adiyat", 'ayat' => 11],
            ['no' => 101, 'nama' => "Al-Qari'ah", 'ayat' => 11],
            ['no' => 102, 'nama' => 'At-Takatsur', 'ayat' => 8],
            ['no' => 103, 'nama' => "Al-'Asr", 'ayat' => 3],
            ['no' => 104, 'nama' => 'Al-Humazah', 'ayat' => 9],
            ['no' => 105, 'nama' => 'Al-Fil', 'ayat' => 5],
            ['no' => 106, 'nama' => 'Quraisy', 'ayat' => 4],
            ['no' => 107, 'nama' => "Al-Ma'un", 'ayat' => 7],
            ['no' => 108, 'nama' => 'Al-Kautsar', 'ayat' => 3],
            ['no' => 109, 'nama' => 'Al-Kafirun', 'ayat' => 6],
            ['no' => 110, 'nama' => 'An-Nasr', 'ayat' => 3],
            ['no' => 111, 'nama' => 'Al-Lahab', 'ayat' => 5],
            ['no' => 112, 'nama' => 'Al-Ikhlas', 'ayat' => 4],
            ['no' => 113, 'nama' => 'Al-Falaq', 'ayat' => 5],
            ['no' => 114, 'nama' => 'An-Nas', 'ayat' => 6],
        ]
    ],
    '29' => [
        'nama' => 'Juz 29 (Al-Mulk – Al-Mursalat)',
        'surah' => [
            ['no' => 67, 'nama' => 'Al-Mulk', 'ayat' => 30],
            ['no' => 68, 'nama' => 'Al-Qalam', 'ayat' => 52],
            ['no' => 69, 'nama' => 'Al-Haqqah', 'ayat' => 52],
            ['no' => 70, 'nama' => "Al-Ma'arij", 'ayat' => 44],
            ['no' => 71, 'nama' => 'Nuh', 'ayat' => 28],
            ['no' => 72, 'nama' => 'Al-Jinn', 'ayat' => 28],
            ['no' => 73, 'nama' => 'Al-Muzzammil', 'ayat' => 20],
            ['no' => 74, 'nama' => 'Al-Muddaththir', 'ayat' => 56],
            ['no' => 75, 'nama' => 'Al-Qiyamah', 'ayat' => 40],
            ['no' => 76, 'nama' => 'Al-Insan', 'ayat' => 31],
            ['no' => 77, 'nama' => 'Al-Mursalat', 'ayat' => 50],
        ]
    ],
    '28' => [
        'nama' => 'Juz 28 (Al-Mujadilah – At-Tahrim)',
        'surah' => [
            ['no' => 58, 'nama' => 'Al-Mujadilah', 'ayat' => 22],
            ['no' => 59, 'nama' => 'Al-Hasyr', 'ayat' => 24],
            ['no' => 60, 'nama' => 'Al-Mumtahanah', 'ayat' => 13],
            ['no' => 61, 'nama' => 'Ash-Shaff', 'ayat' => 14],
            ['no' => 62, 'nama' => "Al-Jumu'ah", 'ayat' => 11],
            ['no' => 63, 'nama' => 'Al-Munafiqun', 'ayat' => 11],
            ['no' => 64, 'nama' => 'At-Taghabun', 'ayat' => 18],
            ['no' => 65, 'nama' => 'Ath-Thalaq', 'ayat' => 12],
            ['no' => 66, 'nama' => 'At-Tahrim', 'ayat' => 12],
        ]
    ]
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ruang Asatidz – SIM Tahfiz SMA IT Al-Fadhilah</title>
    
    <link rel="icon" type="image/png" href="../../assets/images/logo-tahfiz.png">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Amiri:wght@400;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5.3 & Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../../assets/css/tahfiz.css">

    <style>
        :root {
            --color-tahfiz: #11756D;
            --color-tahfiz-dark: #0A2E2B;
            --color-tahfiz-light: #F4F7F6;
            --color-primary: #11756D;
            --color-primary-dark: #0D5C56;
            --color-soft: #E6F4F1;
            --color-gold: #D97706;
            --color-border: #E2E8F0;
        }
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #F8FAFC;
            color: #1E293B;
            min-height: 100vh;
        }
        .ustadz-sidebar {
            width: 260px;
            background: #0A2E2B;
            min-height: 100vh;
            color: #ffffff;
            flex-shrink: 0;
            padding: 24px 16px;
        }
        .ustadz-nav-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 11px 14px;
            color: #A2D5D2;
            font-weight: 600;
            font-size: 0.9rem;
            border-radius: 8px;
            text-decoration: none;
            transition: all 0.2s;
            margin-bottom: 4px;
        }
        .ustadz-nav-link:hover {
            color: #ffffff;
            background: rgba(255, 255, 255, 0.12);
        }
        .ustadz-nav-link.active {
            background: #11756D;
            color: #ffffff;
            font-weight: 700;
            box-shadow: 0 4px 12px rgba(17, 117, 109, 0.35);
        }
        .kpi-stat-card {
            background: #ffffff;
            border-radius: 14px;
            padding: 20px;
            border: 1px solid #E2E8F0;
            box-shadow: 0 2px 6px rgba(0,0,0,0.02);
            transition: transform 0.2s;
        }
        .kpi-stat-card:hover {
            transform: translateY(-2px);
            border-color: #11756D;
        }
        .santri-select-card {
            border: 1.5px solid #E2E8F0;
            border-radius: 10px;
            padding: 12px 14px;
            cursor: pointer;
            transition: all 0.2s ease;
            background: #ffffff;
        }
        .santri-select-card:hover {
            border-color: #11756D;
            background: #F4F7F6;
        }
        .santri-select-card.active {
            border-color: #11756D;
            background: #F4F7F6;
            border-left: 5px solid #11756D;
        }
        .surah-select-btn {
            border: 1.5px solid #E2E8F0;
            border-radius: 10px;
            padding: 10px 14px;
            cursor: pointer;
            background: #ffffff;
            text-align: left;
            transition: all 0.2s;
            width: 100%;
        }
        .surah-select-btn:hover {
            border-color: #11756D;
            background: #F4F7F6;
        }
        .surah-select-btn.active {
            border-color: #11756D;
            background: #E6F4F1;
            font-weight: 700;
            color: #0D5C56;
        }
        .btn-tahfiz, .btn-simpan-hafalan {
            background: #11756D;
            color: #ffffff;
            font-weight: 700;
            border: none;
            transition: all 0.25s ease;
        }
        .btn-tahfiz:hover, .btn-simpan-hafalan:hover {
            background: #0D5C56;
            color: #ffffff;
            box-shadow: 0 4px 14px rgba(13, 92, 86, 0.35);
        }
    </style>
</head>
<body>

<div class="d-flex">
    <!-- ═══════════════════════════════════════════════════════════
         SIDEBAR UTAMA ASATIDZ (SEMUA MENU KEMBALI LENGKAP)
    ════════════════════════════════════════════════════════════ -->
    <aside class="ustadz-sidebar d-flex flex-column">
        <!-- Brand -->
        <div class="ustadz-sidebar-brand">
            <img src="../../assets/images/logo-tahfiz.png" alt="Logo Tahfiz" class="sidebar-logo">
            <div class="ustadz-brand-text">
                <div class="ustadz-brand-title">Tahfiz Yuk</div>
                <div class="ustadz-brand-subtitle">Sistem monitoring tahfiz</div>
            </div>
        </div>

        <!-- Info Pengguna -->
        <div class="ustadz-user-info">
            <div class="ustadz-user-avatar">RA</div>
            <div class="ustadz-user-text">
                <div class="ustadz-user-role">Ustadz</div>
                <div class="ustadz-user-name"><?= htmlspecialchars($ustadz_name) ?></div>
            </div>
        </div>

        <!-- Menu Navigasi -->
        <nav class="ustadz-nav flex-grow-1">
            <div class="ustadz-nav-label">MENU</div>

            <a href="?tab=profil" class="ustadz-nav-link <?= $page_tab === 'profil' ? 'active' : '' ?>">
                <i class="bi bi-star"></i><span>Profil Saya</span>
            </a>

            <a href="?tab=hafalan" class="ustadz-nav-link <?= $page_tab === 'hafalan' ? 'active' : '' ?>">
                <i class="bi bi-book"></i><span>Hafalan</span>
            </a>

            <a href="?tab=murajaah" class="ustadz-nav-link <?= $page_tab === 'murajaah' ? 'active' : '' ?>">
                <i class="bi bi-arrow-counterclockwise"></i><span>Murajaah</span>
            </a>

            <a href="?tab=rekap" class="ustadz-nav-link <?= $page_tab === 'rekap' ? 'active' : '' ?>">
                <i class="bi bi-file-earmark-text"></i><span>Rekap Tahfiz</span>
            </a>

            <a href="?tab=target" class="ustadz-nav-link <?= $page_tab === 'target' ? 'active' : '' ?>">
                <i class="bi bi-bullseye"></i><span>Target</span>
            </a>
        </nav>

        <!-- Footer Sidebar -->
        <div class="ustadz-sidebar-footer">
            <a href="../auth/logout.php" class="ustadz-logout">
                <i class="bi bi-box-arrow-right"></i><span>Log out</span>
            </a>
        </div>
    </aside>

    <!-- ═══════════════════════════════════════════════════════════
         MAIN CONTENT AREA
    ════════════════════════════════════════════════════════════ -->
    <div class="ustadz-content-wrapper">
        
        <!-- Top Bar Header -->
        <header class="bg-white border-bottom px-4 py-3 d-flex justify-content-between align-items-center shadow-sm">
            <div class="d-flex align-items-center gap-3">
                <img src="../../assets/images/logo-tahfiz.png" alt="Logo Tahfiz" style="height: 42px; width: auto; object-fit: contain;">
                <div class="d-flex align-items-center gap-2 small text-muted">
                    <span>Ruang Ustadz</span>
                    <span>›</span>
                    <span class="fw-bold text-dark text-capitalize">
                        <?php
                        $tab_titles = [
                            'binaan'   => 'Siswa Binaan (Dashboard Halaqah)',
                            'hafalan'  => 'Catat Hafalan Baru',
                            'murajaah' => 'Dashboard & Catat Murajaah',
                            'target'   => 'Target Hafalan Santri',
                            'rekap'    => 'Rekapitulasi Tahfiz Halaqah',
                            'profil'   => 'Profil & Kata Sandi'
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
                <a href="?tab=hafalan" class="btn btn-tahfiz btn-sm px-3 py-1.5 fw-bold rounded-3">
                    <i class="bi bi-plus-circle me-1"></i>Catat Setoran
                </a>
            </div>
        </header>

        <!-- Main Body -->
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

            <!-- ═══════════════════════════════════════════════════════════
                 MENU 1: DASHBOARD SISWA BINAAN (OVERVIEW HALAQAH)
            ════════════════════════════════════════════════════════════ -->
            <?php if ($page_tab === 'binaan'): ?>
                <div class="mb-4">
                    <span class="badge px-3 py-1 text-white mb-2 fw-bold" style="background-color: #11756D;">HALAQAH AL-FURQAN</span>
                    <h2 class="fw-bold mb-1" style="color: #11756D;">Daftar Santri Binaan</h2>
                    <p class="text-muted small mb-0">Pantau perkembangan hafalan, setoran terbaru, dan status murajaah seluruh santri halaqah.</p>
                </div>

                <!-- 4 KPI Cards Dashboard Sesuai Wireframe -->
                <div class="row g-3 mb-4">
                    <div class="col-12 col-sm-6 col-xl-3">
                        <div class="kpi-stat-card">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="small fw-bold text-muted text-uppercase">Santri Binaan</span>
                                <i class="bi bi-people fs-4" style="color: #11756D;"></i>
                            </div>
                            <div class="fs-2 fw-bold text-dark"><?= $total_santri_binaan ?></div>
                            <small class="text-muted">Halaqah <?= htmlspecialchars($halaqah_name) ?> · Aktif</small>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6 col-xl-3">
                        <div class="kpi-stat-card">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="small fw-bold text-muted text-uppercase">Setoran Tercatat</span>
                                <i class="bi bi-journal-check fs-4" style="color: #11756D;"></i>
                            </div>
                            <div class="fs-2 fw-bold text-success"><?= $total_setoran_count ?></div>
                            <small class="text-muted">Hafalan Baru Tersimpan</small>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6 col-xl-3">
                        <div class="kpi-stat-card">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="small fw-bold text-muted text-uppercase">Total Murajaah</span>
                                <i class="bi bi-arrow-repeat fs-4" style="color: #11756D;"></i>
                            </div>
                            <div class="fs-2 fw-bold text-dark"><?= $total_murajaah_count ?></div>
                            <small class="text-muted">Pengulangan Santri Mutqin</small>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6 col-xl-3">
                        <div class="kpi-stat-card">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="small fw-bold text-muted text-uppercase">Target Kelancaran</span>
                                <i class="bi bi-patch-check fs-4" style="color: #D97706;"></i>
                            </div>
                            <div class="fs-2 fw-bold text-warning-emphasis">85%</div>
                            <small class="text-muted">Kategori Sangat Mutqin / Lancar</small>
                        </div>
                    </div>
                </div>

                <!-- Tabel Santri Binaan dengan Aksi Cepat -->
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
                    <div class="card-header bg-white py-3 px-4 d-flex justify-content-between align-items-center border-bottom">
                        <h6 class="fw-bold mb-0 text-dark">Data Santri &amp; Capaian Hafalan</h6>
                        <span class="badge bg-light text-muted border"><?= $total_santri_binaan ?> Santri Terdaftar</span>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light small">
                                <tr>
                                    <th class="ps-4">No</th>
                                    <th>NIS</th>
                                    <th>Nama Santri</th>
                                    <th>Capaian Target</th>
                                    <th>Setoran Terakhir</th>
                                    <th>Nilai</th>
                                    <th class="text-end pe-4">Aksi Cepat</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($santri_list as $i => $s): ?>
                                <tr>
                                    <td class="ps-4"><?= $i + 1 ?></td>
                                    <td><code><?= htmlspecialchars($s['nip_nis']) ?></code></td>
                                    <td><strong><?= htmlspecialchars($s['nama_lengkap']) ?></strong></td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2" style="min-width: 140px;">
                                             <div class="progress flex-grow-1" style="height: 7px;">
                                                 <div class="progress-bar" style="width: <?= 60 + ($i * 15) ?>%; background-color: #11756D;"></div>
                                             </div>
                                            <small class="fw-bold" style="font-size: 0.75rem;"><?= 60 + ($i * 15) ?>%</small>
                                        </div>
                                    </td>
                                    <td><span class="badge bg-light text-dark border">Al-Mulk: 13–18</span></td>
                                    <td><span class="badge bg-success-subtle text-success fw-bold">90 / 100</span></td>
                                    <td class="text-end pe-4">
                                        <a href="?tab=hafalan&santri=<?= urlencode($s['nama_lengkap']) ?>" class="btn btn-sm btn-outline-success fw-semibold">
                                            <i class="bi bi-journal-plus me-1"></i>Catat Setoran
                                        </a>
                                        <a href="?tab=murajaah&santri=<?= urlencode($s['nama_lengkap']) ?>" class="btn btn-sm btn-outline-success fw-semibold">
                                            <i class="bi bi-arrow-repeat me-1"></i>Catat Murajaah
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            <!-- ═══════════════════════════════════════════════════════════
                 MENU 2: CATAT HAFALAN BARU (ALUR PILIH SISWA -> JUZ/SURAH -> LEMBAR SETORAN)
            ════════════════════════════════════════════════════════════ -->
            <?php elseif ($page_tab === 'hafalan'): ?>
                <div class="mb-4">
                    <span class="badge px-3 py-1 text-white mb-2 fw-bold" style="background-color: #11756D;">FORMULIR SETORAN HAFALAN</span>
                    <h2 class="fw-bold mb-1" style="color: #11756D;">Pencatatan Hafalan Baru</h2>
                    <p class="text-muted small mb-0">Pilih santri terlebih dahulu di kolom kiri, lalu tentukan surah dan batas ayat setoran.</p>
                </div>

                <div class="row g-4">
                    <!-- KOLOM KIRI: PILIH SISWA -->
                    <div class="col-12 col-lg-4">
                        <div class="card p-3 border-0 shadow-sm rounded-4 bg-white h-100">
                            <h6 class="fw-bold text-dark mb-2">
                                <i class="bi bi-person-check-fill text-success me-1"></i>1. Pilih Siswa
                            </h6>
                            <input type="text" id="cariSantriHafalan" class="form-control form-control-sm mb-3" placeholder="Cari nama atau NIS...">
                            
                            <div class="d-flex flex-column gap-2 overflow-y-auto" style="max-height: 520px;" id="listSantriHafalan">
                                <?php foreach ($santri_list as $s): ?>
                                    <div class="santri-select-card" onclick="pilihSantriUntukHafalan('<?= htmlspecialchars($s['nama_lengkap']) ?>', '<?= htmlspecialchars($s['nip_nis']) ?>', this)">
                                        <div class="fw-bold text-dark santri-nama-item"><?= htmlspecialchars($s['nama_lengkap']) ?></div>
                                        <div class="d-flex justify-content-between small text-muted mt-1">
                                            <span>NIS: <?= htmlspecialchars($s['nip_nis']) ?></span>
                                            <span class="badge bg-light text-success border">Halaqah <?= htmlspecialchars($s['halaqah'] ?? 'Al-Furqan') ?></span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <!-- KOLOM KANAN: FORMULIR INPUT HAFALAN BERTAHAP -->
                    <div class="col-12 col-lg-8">
                        <div class="card p-4 border-0 shadow-sm rounded-4 bg-white h-100">
                            
                            <!-- Placeholder jika siswa belum dipilih -->
                            <div id="placeholderHafalan" class="text-center py-5 my-auto text-muted">
                                <div class="rounded-circle d-inline-flex align-items-center justify-content-center p-3 mb-3" style="width: 64px; height: 64px; background: #E6F4F1; color: #11756D;">
                                    <i class="bi bi-arrow-left-circle-fill fs-2"></i>
                                </div>
                                <h5 class="fw-bold text-dark mb-1">Silakan Pilih Siswa Terlebih Dahulu</h5>
                                <p class="small text-muted mb-0">Klik nama santri pada daftar di sebelah kiri untuk membuka pilihan surah dan lembar setoran.</p>
                            </div>

                            <!-- Area Form Setoran (Aktif setelah santri dipilih) -->
                            <div id="areaFormHafalan" style="display: none;">
                                <div class="p-3 rounded-3 mb-4 d-flex align-items-center justify-content-between" style="background-color: var(--color-tahfiz-light); border: 1px solid #80C8C4;">
                                    <div>
                                        <small class="text-muted d-block" style="font-size: 0.72rem;">Santri yang Diuji:</small>
                                        <h5 class="fw-bold mb-0 text-dark" id="displayNamaSantriHafalan">Muhammad Fadhlan</h5>
                                    </div>
                                    <span class="badge bg-white text-dark border px-3 py-2 fw-semibold" id="displayNisSantriHafalan">NIS: 102601</span>
                                </div>

                                <!-- Langkah 2: Pilihan Target Juz & Surah -->
                                <div class="mb-4">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <label class="form-label fw-bold small text-dark mb-0">
                                            <i class="bi bi-book-half text-success me-1"></i>2. Pilih Target Juz &amp; Surah:
                                        </label>
                                        <select class="form-select form-select-sm w-auto fw-bold text-dark" onchange="gantiJuzHafalan(this.value)">
                                            <option value="30" selected>Juz 30 (An-Naba – An-Nas) — 37 Surah</option>
                                            <option value="29">Juz 29 (Al-Mulk – Al-Mursalat) — 11 Surah</option>
                                            <option value="28">Juz 28 (Al-Mujadilah – At-Tahrim) — 9 Surah</option>
                                        </select>
                                    </div>

                                    <!-- Grid Tombol Surah -->
                                    <div class="row g-2 overflow-y-auto" style="max-height: 200px;" id="gridSurahHafalan">
                                        <?php foreach ($quran_juz['30']['surah'] as $sr): ?>
                                            <div class="col-12 col-md-4 col-sm-6">
                                                <button type="button" class="surah-select-btn" onclick="pilihSurahHafalan('<?= $sr['nama'] ?>', <?= $sr['ayat'] ?>, <?= $sr['no'] ?>, this)">
                                                    <div class="fw-bold small text-dark"><?= $sr['no'] ?>. <?= $sr['nama'] ?></div>
                                                    <small class="text-muted d-block" style="font-size: 0.7rem;"><?= $sr['ayat'] ?> Ayat</small>
                                                </button>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>

                                <!-- Langkah 3: Formulir Lembar Setoran Eksklusif Surah -->
                                <div id="lembarSetoranForm" style="display: none;" class="p-3 border rounded-3 bg-light bg-opacity-50">
                                    <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                                        <div>
                                            <span class="badge bg-success-subtle text-success fw-bold px-2 py-0.5 mb-1" style="font-size: 0.7rem;">LEMBAR SETORAN EKSKLUSIF</span>
                                            <h5 class="fw-bold mb-0 text-dark" id="displaySurahTerpilih">Surah An-Naba'</h5>
                                        </div>
                                        <span class="badge rounded-pill text-white px-3 py-1.5" style="background-color: #11756D;" id="displayTotalAyatSurah">Total 40 Ayat</span>
                                    </div>

                                    <form action="?tab=hafalan" method="POST">
                                        <input type="hidden" name="action" value="simpan_setoran">
                                        <input type="hidden" name="santri_nama" id="inputSantriHafalan">
                                        <input type="hidden" name="surah" id="inputSurahHafalan">

                                        <div class="row g-3 mb-3">
                                            <div class="col-6">
                                                <label class="form-label small fw-bold text-muted">Dari Ayat</label>
                                                <input type="number" name="ayat_awal" id="inputAyatAwalHafalan" class="form-control" value="1" min="1" required>
                                            </div>
                                            <div class="col-6">
                                                <label class="form-label small fw-bold text-muted">Sampai Ayat</label>
                                                <input type="number" name="ayat_akhir" id="inputAyatAkhirHafalan" class="form-control" value="20" min="1" required>
                                            </div>
                                            <small class="text-muted" style="font-size: 0.73rem;">
                                                *Kertas setoran terkunci khusus surah ini (1 lembar = 1 surah).
                                             </small>
                                        </div>

                                        <div class="row g-3 mb-3">
                                            <div class="col-7">
                                                <label class="form-label small fw-bold text-muted">Kualitas Hafalan</label>
                                                <select name="kualitas" class="form-select">
                                                    <option value="Mumtaz (A)" selected>Mumtaz / Sangat Lancar (A)</option>
                                                    <option value="Jayyid Jiddan (B)">Jayyid Jiddan / Lancar (B)</option>
                                                    <option value="Jayyid (C)">Jayyid / Cukup (C)</option>
                                                    <option value="Perlu Penguatan">Perlu Pengulangan / Remedi</option>
                                                </select>
                                            </div>
                                            <div class="col-5">
                                                <label class="form-label small fw-bold text-muted">Nilai (0–100)</label>
                                                <input type="number" name="nilai" class="form-control fw-bold text-center" value="90" min="0" max="100" required>
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label small fw-bold text-muted">Catatan Tajwid / Makhraj Ustadz</label>
                                            <textarea name="catatan" class="form-control" rows="2" placeholder="Catatan perbaikan tajwid, panjang mad, atau makhraj huruf..."></textarea>
                                        </div>

                                        <button type="submit" class="btn btn-tahfiz btn-simpan-hafalan w-100 py-2.5 rounded-3 fw-bold text-white" id="btnSubmitSetoran" style="background-color: #11756D;">
                                            <i class="bi bi-check2-circle me-1"></i>Simpan Hafalan
                                        </button>
                                    </form>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>

            <!-- ═══════════════════════════════════════════════════════════
                 MENU 3: DASHBOARD & CATAT MURAJAAH
                 (Guru melihat total siswa murajaah, progres kelancaran, & form input)
            ════════════════════════════════════════════════════════════ -->
            <?php elseif ($page_tab === 'murajaah'): ?>
                <div class="mb-4">
                    <span class="badge px-3 py-1 text-white mb-2 fw-bold" style="background-color: #11756D;">MONITORING MURAJAAH</span>
                    <h2 class="fw-bold mb-1" style="color: #11756D;">Dashboard &amp; Catat Murajaah</h2>
                    <p class="text-muted small mb-0">Lihat total capaian murajaah seluruh siswa dan catat pengulangan hafalan mutqin.</p>
                </div>

                <!-- 3 KPI Dashboard Murajaah -->
                <div class="row g-3 mb-4">
                    <div class="col-12 col-md-4">
                        <div class="kpi-stat-card">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="small fw-bold text-muted text-uppercase">Total Sesi Murajaah</span>
                                <i class="bi bi-arrow-repeat fs-4 text-success"></i>
                            </div>
                            <div class="fs-2 fw-bold text-dark"><?= $total_murajaah_count ?> Sesi</div>
                            <small class="text-muted">Tercatat di Halaqah <?= htmlspecialchars($halaqah_name) ?></small>
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="kpi-stat-card">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="small fw-bold text-muted text-uppercase">Tingkat Mutqin</span>
                                <i class="bi bi-shield-check fs-4" style="color: #11756D;"></i>
                            </div>
                            <div class="fs-2 fw-bold text-success">92%</div>
                            <small class="text-muted">Kategori Sangat Mutqin &amp; Lancar</small>
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="kpi-stat-card">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="small fw-bold text-muted text-uppercase">Fokus Juz Murajaah</span>
                                <i class="bi bi-book fs-4 text-warning"></i>
                            </div>
                            <div class="fs-2 fw-bold text-dark">Juz 29 &amp; 30</div>
                            <small class="text-muted">Penguatan Sambungan Antar Ayat</small>
                        </div>
                    </div>
                </div>

                <div class="row g-4">
                    <!-- Form Catat Murajaah -->
                    <div class="col-12 col-lg-5">
                        <div class="card p-4 border-0 shadow-sm rounded-4 bg-white h-100">
                            <h5 class="fw-bold mb-3" style="color: #11756D;">
                                <i class="bi bi-pencil-square me-2"></i>Catat Pengulangan (Murajaah)
                            </h5>

                            <form action="?tab=murajaah" method="POST">
                                <input type="hidden" name="action" value="simpan_murajaah">

                                <div class="mb-3">
                                    <label class="form-label small fw-bold text-muted">Santri yang Murajaah</label>
                                    <select name="santri_nama" class="form-select fw-semibold" required>
                                        <?php foreach ($santri_list as $s): ?>
                                            <option value="<?= htmlspecialchars($s['nama_lengkap']) ?>" <?= (isset($_GET['santri']) && $_GET['santri'] === $s['nama_lengkap']) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($s['nama_lengkap']) ?> (NIS: <?= htmlspecialchars($s['nip_nis']) ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label small fw-bold text-muted">Surah / Juz yang Diulang</label>
                                    <select name="surah_diulang" class="form-select fw-semibold">
                                        <optgroup label="Juz Lengkap">
                                            <option value="Juz 30 (Lengkap)">Juz 30 (Lengkap 37 Surah)</option>
                                            <option value="Juz 29 (Lengkap)">Juz 29 (Lengkap 11 Surah)</option>
                                            <option value="Juz 28 (Lengkap)">Juz 28 (Lengkap 9 Surah)</option>
                                        </optgroup>
                                        <optgroup label="Surah Pilihan">
                                            <option value="Surah An-Naba'">Surah An-Naba'</option>
                                            <option value="Surah An-Nazi'at">Surah An-Nazi'at</option>
                                            <option value="Surah 'Abasa">Surah 'Abasa</option>
                                            <option value="Surah Al-Mulk">Surah Al-Mulk (Juz 29)</option>
                                            <option value="Surah Al-Qalam">Surah Al-Qalam</option>
                                        </optgroup>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label small fw-bold text-muted">Rentang Ayat</label>
                                    <input type="text" name="ayat_range" class="form-control" value="Seluruh Ayat" placeholder="Contoh: Ayat 1–30 atau Seluruh Ayat">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label small fw-bold text-muted">Hasil Evaluasi Kelancaran</label>
                                    <select name="evaluasi" class="form-select fw-semibold">
                                        <option value="Sangat Mutqin (Nilai 90-100)">Sangat Mutqin (Nilai 90–100)</option>
                                        <option value="Lancar (Nilai 80-89)" selected>Lancar (Hanya 1-2 kali diingatkan)</option>
                                        <option value="Cukup (Nilai 70-79)">Cukup (Perlu Pembiasaan)</option>
                                        <option value="Perlu Diulang / Belum Mutqin">Perlu Tambahan Waktu Murajaah</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label small fw-bold text-muted">Catatan Evaluasi Ustadz</label>
                                    <textarea name="catatan_murajaah" class="form-control" rows="2" placeholder="Catatan makhraj atau ayat yang masih ragu..."></textarea>
                                </div>

                                <button type="submit" class="btn btn-tahfiz w-100 py-2.5 rounded-3 fw-bold">
                                    <i class="bi bi-save me-1"></i>Simpan Catatan Murajaah
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Tabel Riwayat Murajaah Seluruh Santri -->
                    <div class="col-12 col-lg-7">
                        <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white h-100">
                            <div class="card-header bg-white py-3 px-4 d-flex justify-content-between align-items-center border-bottom">
                                <h6 class="fw-bold mb-0 text-dark">Riwayat Murajaah Santri</h6>
                                <span class="badge bg-light text-muted border"><?= count($riwayat_murajaah) ?> Catatan</span>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0 small">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="ps-3">Tanggal</th>
                                            <th>Nama Santri</th>
                                            <th>Juz / Surah</th>
                                            <th>Evaluasi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($riwayat_murajaah)): ?>
                                            <?php foreach ($riwayat_murajaah as $rm): ?>
                                            <tr>
                                                <td class="ps-3"><?= htmlspecialchars($rm['tanggal_murajaah'] ?? date('d M Y')) ?></td>
                                                <td><strong><?= htmlspecialchars($rm['nama_siswa']) ?></strong></td>
                                                <td><?= htmlspecialchars($rm['surah']) ?> <small class="text-muted">(<?= htmlspecialchars($rm['ayat_range'] ?? 'Semua') ?>)</small></td>
                                                <td><span class="badge bg-success-subtle text-success"><?= htmlspecialchars($rm['status_mutqin']) ?></span></td>
                                            </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td class="ps-3">Hari Ini</td>
                                                <td><strong>Muhammad Fadhlan</strong></td>
                                                <td>Juz 30 (Lengkap)</td>
                                                <td><span class="badge bg-success-subtle text-success">Sangat Mutqin</span></td>
                                            </tr>
                                            <tr>
                                                <td class="ps-3">Kemarin</td>
                                                <td><strong>Ahmad Zaid</strong></td>
                                                <td>Surah Al-Mulk (Ayat 1–30)</td>
                                                <td><span class="badge bg-success-subtle text-success">Lancar</span></td>
                                            </tr>
                                            <tr>
                                                <td class="ps-3">3 Nov</td>
                                                <td><strong>Radit Gilang Ramadhan S</strong></td>
                                                <td>Surah An-Naba' (1–40)</td>
                                                <td><span class="badge bg-warning-subtle text-warning">Perlu Pengulangan</span></td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

            <!-- ═══════════════════════════════════════════════════════════
                 MENU 4: TARGET HAFALAN SANTRI
            ════════════════════════════════════════════════════════════ -->
            <?php elseif ($page_tab === 'target'): ?>
                <div class="mb-4">
                    <span class="badge px-3 py-1 text-white mb-2 fw-bold" style="background-color: #11756D;">PENETAPAN TARGET</span>
                    <h2 class="fw-bold mb-1" style="color: #11756D;">Target Hafalan Santri</h2>
                    <p class="text-muted small mb-0">Tetapkan sasaran surah dan batas waktu target bagi santri halaqah binaan Anda.</p>
                </div>

                <div class="row g-4" style="max-width: 900px;">
                    <div class="col-12 col-md-6">
                        <div class="card p-4 border-0 shadow-sm rounded-4 bg-white h-100">
                            <h5 class="fw-bold mb-3" style="color: #11756D;">
                                <i class="bi bi-bullseye me-2"></i>Tetapkan Target Baru
                            </h5>
                            <form action="?tab=target" method="POST">
                                <input type="hidden" name="action" value="simpan_target">
                                
                                <div class="mb-3">
                                    <label class="form-label small fw-bold text-muted">Santri Target</label>
                                    <select name="target_santri" class="form-select fw-semibold" required>
                                        <?php foreach ($santri_list as $s): ?>
                                            <option value="<?= htmlspecialchars($s['nama_lengkap']) ?>"><?= htmlspecialchars($s['nama_lengkap']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label small fw-bold text-muted">Surah Target</label>
                                    <select name="target_surah" class="form-select fw-semibold">
                                        <option value="Surah Al-Mulk (Ayat 1–30)">67. Al-Mulk (Ayat 1–30)</option>
                                        <option value="Surah Al-Qalam (Ayat 1–52)">68. Al-Qalam (Ayat 1–52)</option>
                                        <option value="Surah An-Naba' (Ayat 1–40)">78. An-Naba' (Ayat 1–40)</option>
                                        <option value="Juz 29 Lengkap">Juz 29 Lengkap</option>
                                        <option value="Juz 30 Lengkap">Juz 30 Lengkap</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label small fw-bold text-muted">Tenggat Waktu</label>
                                    <input type="date" name="target_tenggat" class="form-control" value="<?= date('Y-m-d', strtotime('+14 days')) ?>" required>
                                </div>

                                <button type="submit" class="btn btn-tahfiz w-100 py-2.5 rounded-3 fw-bold">
                                    <i class="bi bi-check-circle me-1"></i>Tetapkan Target Santri
                                </button>
                            </form>
                        </div>
                    </div>

                    <div class="col-12 col-md-6">
                        <div class="card p-4 border-0 shadow-sm rounded-4 bg-white h-100">
                            <h5 class="fw-bold mb-3" style="color: #11756D;">
                                <i class="bi bi-list-task me-2"></i>Target Aktif Pekan Ini
                            </h5>
                            <div class="d-flex flex-column gap-3">
                                <div class="p-3 rounded-3 border bg-light">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <strong class="text-dark">Muhammad Fadhlan</strong>
                                        <span class="badge bg-success">60%</span>
                                    </div>
                                    <small class="text-muted d-block">Surah Al-Mulk (Ayat 1–30) · Tenggat: 20 Nov 2026</small>
                                </div>
                                <div class="p-3 rounded-3 border bg-light">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <strong class="text-dark">Ahmad Zaid</strong>
                                        <span class="badge bg-success">85%</span>
                                    </div>
                                    <small class="text-muted d-block">Surah Al-Qalam (Ayat 1–52) · Tenggat: 22 Nov 2026</small>
                                </div>
                                <div class="p-3 rounded-3 border bg-light">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <strong class="text-dark">Radit Gilang Ramadhan S</strong>
                                        <span class="badge bg-warning">45%</span>
                                    </div>
                                    <small class="text-muted d-block">Surah Al-Haqqah (Ayat 1–52) · Tenggat: 25 Nov 2026</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            <!-- ═══════════════════════════════════════════════════════════
                 MENU 5: REKAP TAHFIZ HALAQAH
                 (Melihat progres seluruh siswa dengan cepat)
            ════════════════════════════════════════════════════════════ -->
            <?php elseif ($page_tab === 'rekap'): ?>
                <div class="mb-4">
                    <span class="badge px-3 py-1 text-white mb-2 fw-bold" style="background-color: #11756D;">REKAPITULASI PROGRES</span>
                    <h2 class="fw-bold mb-1" style="color: #11756D;">Rekapitulasi Halaqah <?= htmlspecialchars($halaqah_name) ?></h2>
                    <p class="text-muted small mb-0">Tinjau rekap progres hafalan, total juz yang dikuasai, dan status mutabaah seluruh siswa secara cepat.</p>
                </div>

                <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light small">
                                <tr>
                                    <th class="ps-4">No</th>
                                    <th>Nama Santri</th>
                                    <th>Total Juz Hafalan</th>
                                    <th>Rata-rata Nilai</th>
                                    <th>Capaian Mutabaah</th>
                                    <th>Status Kelancaran</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($santri_list as $i => $s): ?>
                                <tr>
                                    <td class="ps-4"><?= $i + 1 ?></td>
                                    <td>
                                        <strong><?= htmlspecialchars($s['nama_lengkap']) ?></strong>
                                        <small class="text-muted d-block">NIS: <?= htmlspecialchars($s['nip_nis']) ?></small>
                                    </td>
                                    <td><strong><?= 2.5 - ($i * 0.5) ?> Juz</strong> <small class="text-muted">(Juz 29, 30)</small></td>
                                    <td><span class="badge bg-success-subtle text-success fs-6"><?= 90 - ($i * 2) ?> / 100</span></td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2" style="max-width: 160px;">
                                            <div class="progress flex-grow-1" style="height: 8px;">
                                                <div class="progress-bar" style="width: <?= 85 - ($i * 15) ?>%; background-color: #11756D;"></div>
                                            </div>
                                            <small class="fw-bold"><?= 85 - ($i * 15) ?>%</small>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if ($i === 0): ?>
                                            <span class="badge bg-success-subtle text-success">Lancar &amp; Mutqin</span>
                                        <?php elseif ($i === 1): ?>
                                            <span class="badge bg-success-subtle text-success">Lancar</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning-subtle text-warning-emphasis">Perlu Penguatan</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            <!-- ═══════════════════════════════════════════════════════════
                 MENU 6: PROFIL USTADZ & GANTI KATA SANDI
                 (Tampilan Eksekutif, Elegan, dan Profesional)
            ════════════════════════════════════════════════════════════ -->
            <?php elseif ($page_tab === 'profil'): ?>
                <h1 class="profil-page-title">Profil Saya</h1>

                <div class="profil-grid">
                    <!-- Kartu Kiri: Informasi Akun -->
                    <div class="profil-card">
                        <h2 class="profil-card-title">Informasi Akun</h2>

                        <form action="?tab=profil" method="POST">
                            <input type="hidden" name="action" value="update_biodata">

                            <div class="profil-field">
                                <label class="profil-label" for="namaLengkap">Nama Lengkap</label>
                                <input type="text" id="namaLengkap" name="nama_lengkap" class="profil-input" value="<?= htmlspecialchars($ustadz_name) ?>" required>
                            </div>

                            <div class="profil-field">
                                <label class="profil-label" for="kontakUstadz">Kontak</label>
                                <input type="text" id="kontakUstadz" name="kontak" class="profil-input" value="0812-7890-1234" placeholder="Contoh: 0812xxxxxxxx">
                            </div>

                            <div class="profil-field">
                                <label class="profil-label" for="nipUstadz">Nomor Induk Pegawai (NIP)</label>
                                <input type="text" id="nipUstadz" class="profil-input" value="199205142023031004" readonly>
                                <small class="profil-hint">Nomor Induk Pegawai (NIP) tidak dapat diubah</small>
                            </div>

                            <div class="profil-field">
                                <label class="profil-label" for="halaqahUstadz">Halaqah</label>
                                <input type="text" id="halaqahUstadz" class="profil-input" value="Halaqah <?= htmlspecialchars($halaqah_name) ?>" readonly>
                                <small class="profil-hint">Halaqah tidak dapat diubah</small>
                            </div>

                            <div class="profil-actions">
                                <button type="submit" class="profil-btn profil-btn-primary">
                                    <i class="bi bi-floppy"></i>Simpan perubahan
                                </button>
                                <button type="reset" class="profil-btn profil-btn-reset">
                                    <i class="bi bi-x-circle"></i>Batal
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Kartu Kanan: Ganti Password -->
                    <div class="profil-card">
                        <h2 class="profil-card-title">Ganti Password</h2>

                        <form action="?tab=profil" method="POST">
                            <input type="hidden" name="action" value="update_password">

                            <div class="profil-field">
                                <label class="profil-label" for="passLama">Password lama</label>
                                <input type="password" id="passLama" name="password_lama" class="profil-input" placeholder="Masukkan kata sandi lama" required>
                            </div>

                            <div class="profil-field">
                                <label class="profil-label" for="passBaru">Password Baru</label>
                                <input type="password" id="passBaru" name="password_baru" class="profil-input" placeholder="Minimal 6 Karakter" required>
                            </div>

                            <div class="profil-field">
                                <label class="profil-label" for="passKonfirmasi">Konfirmasi Password Baru</label>
                                <input type="password" id="passKonfirmasi" name="password_konfirmasi" class="profil-input" placeholder="Ulangi kata sandi baru" required>
                            </div>

                            <div class="profil-actions">
                                <button type="submit" class="profil-btn profil-btn-primary">
                                    <i class="bi bi-floppy"></i>Simpan perubahan
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

<!-- Javascript Interaktif Khusus Tab Catat Hafalan -->
<script>
const dataJuzHafalan = <?= json_encode($quran_juz, JSON_UNESCAPED_UNICODE) ?>;
let santriHafalanAktif = null;
let surahHafalanAktif  = null;

function pilihSantriUntukHafalan(nama, nis, el) {
    santriHafalanAktif = { nama, nis };

    document.querySelectorAll('.santri-select-card').forEach(c => c.classList.remove('active'));
    if (el) el.classList.add('active');

    document.getElementById('placeholderHafalan').style.display = 'none';
    document.getElementById('areaFormHafalan').style.display    = 'block';

    document.getElementById('displayNamaSantriHafalan').innerText = nama;
    document.getElementById('displayNisSantriHafalan').innerText  = 'NIS: ' + nis;
    document.getElementById('inputSantriHafalan').value          = nama;

    // Reset lembar setoran surah sampai surah dipilih
    surahHafalanAktif = null;
    document.getElementById('lembarSetoranForm').style.display    = 'none';
    document.querySelectorAll('.surah-select-btn').forEach(b => b.classList.remove('active'));
}

function gantiJuzHafalan(juzNo) {
    const container = document.getElementById('gridSurahHafalan');
    container.innerHTML = '';
    const list = dataJuzHafalan[juzNo]?.surah || [];

    list.forEach(sr => {
        const col = document.createElement('div');
        col.className = 'col-12 col-md-4 col-sm-6';
        col.innerHTML = `
            <button type="button" class="surah-select-btn" onclick="pilihSurahHafalan('${sr.nama}', ${sr.ayat}, ${sr.no}, this)">
                <div class="fw-bold small text-dark">${sr.no}. ${sr.nama}</div>
                <small class="text-muted d-block" style="font-size: 0.7rem;">${sr.ayat} Ayat</small>
            </button>
        `;
        container.appendChild(col);
    });

    document.getElementById('lembarSetoranForm').style.display = 'none';
}

function pilihSurahHafalan(namaSurah, totalAyat, noSurah, el) {
    surahHafalanAktif = { namaSurah, totalAyat, noSurah };

    document.querySelectorAll('.surah-select-btn').forEach(b => b.classList.remove('active'));
    if (el) el.classList.add('active');

    document.getElementById('lembarSetoranForm').style.display   = 'block';
    document.getElementById('displaySurahTerpilih').innerText     = noSurah + '. Surah ' + namaSurah;
    document.getElementById('displayTotalAyatSurah').innerText    = 'Total ' + totalAyat + ' Ayat';
    document.getElementById('inputSurahHafalan').value           = namaSurah;

    const inputAwal  = document.getElementById('inputAyatAwalHafalan');
    const inputAkhir = document.getElementById('inputAyatAkhirHafalan');

    inputAwal.value = 1;
    inputAwal.max   = totalAyat;
    inputAkhir.value = totalAyat;
    inputAkhir.max   = totalAyat;

    inputAkhir.oninput = function() {
        if (parseInt(this.value) > totalAyat) this.value = totalAyat;
    };

    document.getElementById('btnSubmitSetoran').innerHTML = `<i class="bi bi-check2-circle me-1"></i>Simpan Hafalan Surah ${namaSurah}`;
    inputAkhir.focus();
}

// Filter Pencarian Santri pada Tab Hafalan
document.getElementById('cariSantriHafalan')?.addEventListener('input', function() {
    const val = this.value.toLowerCase();
    document.querySelectorAll('.santri-select-card').forEach(item => {
        const text = item.innerText.toLowerCase();
        item.style.display = text.includes(val) ? 'block' : 'none';
    });
});

// Auto-select santri jika ada GET param ?santri=...
window.addEventListener('DOMContentLoaded', () => {
    const urlParams = new URLSearchParams(window.location.search);
    const getSantri = urlParams.get('santri');
    if (getSantri) {
        document.querySelectorAll('.santri-select-card').forEach(item => {
            const nama = item.querySelector('.santri-nama-item')?.innerText;
            if (nama && nama.toLowerCase().includes(getSantri.toLowerCase())) {
                item.click();
            }
        });
    }
});
</script>
</body>
</html>
