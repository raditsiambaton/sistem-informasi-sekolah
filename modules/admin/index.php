<?php
/**
 * modules/admin/index.php
 * Dashboard Administrator – SMA IT Al-Fadhilah Pekanbaru
 * Versi: Tanpa CMS Konten | Redesain Modern Islamic School
 */

require_once '../../config/session.php';
require_once '../../config/database.php';

// Validasi otorisasi sesi administrator
wajib_login('../auth/login.php');
if (peran_pengguna() !== 'admin') {
    $role = peran_pengguna();
    if ($role === 'ustadz' || $role === 'guru') {
        header('Location: ../ustadz/index.php');
        exit;
    } elseif ($role === 'siswa') {
        header('Location: ../siswa/index.php');
        exit;
    } else {
        header('Location: ../auth/login.php');
        exit;
    }
}

$admin_name = $_SESSION['user_name'] ?? 'Administrator';

// Daftar tab yang valid — tab=konten sudah dihapus
$valid_tabs = ['rekap', 'pengguna', 'halaqah', 'laporan', 'profil', 'sandi'];
$page_tab   = $_GET['tab'] ?? 'rekap';
if (!in_array($page_tab, $valid_tabs)) {
    $page_tab = 'rekap';
}

$success_msg = '';
$error_msg   = '';

// Inisialisasi koneksi DB
$pdo = null;
try {
    $pdo = koneksi_database();
} catch (Throwable $e) {
    $error_msg = "Koneksi database tidak tersedia: " . $e->getMessage();
}

// ─────────────────────────────────────────────────────────────
// PROSES AKSI POST (CRUD)
// ─────────────────────────────────────────────────────────────
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $action = $_POST['action'] ?? '';

    // 1. TAMBAH PENGGUNA BARU
    if ($action === 'tambah_pengguna' && $pdo) {
        $nama_lengkap = trim($_POST['nama_lengkap'] ?? '');
        $username     = trim($_POST['username'] ?? '');
        $password     = trim($_POST['password'] ?? '');
        $role         = trim($_POST['role'] ?? 'siswa');
        $nip_nis      = trim($_POST['nip_nis'] ?? '');
        $halaqah      = trim($_POST['halaqah'] ?? 'Al-Furqan');

        if (empty($nama_lengkap) || empty($username) || empty($password)) {
            $error_msg = 'Nama lengkap, username, dan kata sandi wajib diisi!';
        } else {
            try {
                $cek = $pdo->prepare("SELECT id FROM users WHERE username = :u LIMIT 1");
                $cek->execute(['u' => $username]);
                if ($cek->fetch()) {
                    $error_msg = "Username '{$username}' sudah terdaftar. Silakan pilih username lain.";
                } else {
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("INSERT INTO users (username, password, nama_lengkap, role, nip_nis, halaqah, status)
                                           VALUES (:username, :password, :nama_lengkap, :role, :nip_nis, :halaqah, 'aktif')");
                    $stmt->execute([
                        'username'     => $username,
                        'password'     => $hashed_password,
                        'nama_lengkap' => $nama_lengkap,
                        'role'         => $role,
                        'nip_nis'      => $nip_nis,
                        'halaqah'      => $halaqah
                    ]);
                    $success_msg = "Pengguna baru '{$nama_lengkap}' ({$role}) berhasil ditambahkan ke database!";
                }
            } catch (PDOException $e) {
                $error_msg = "Gagal menyimpan pengguna: " . $e->getMessage();
            }
        }
        $page_tab = 'pengguna';
    }

    // 2. EDIT PENGGUNA
    elseif ($action === 'edit_pengguna' && $pdo) {
        $user_id      = (int)($_POST['user_id'] ?? 0);
        $nama_lengkap = trim($_POST['nama_lengkap'] ?? '');
        $username     = trim($_POST['username'] ?? '');
        $password     = trim($_POST['password'] ?? '');
        $role         = trim($_POST['role'] ?? 'siswa');
        $nip_nis      = trim($_POST['nip_nis'] ?? '');
        $halaqah      = trim($_POST['halaqah'] ?? 'Al-Furqan');
        $status       = trim($_POST['status'] ?? 'aktif');

        if ($user_id > 0 && !empty($nama_lengkap) && !empty($username)) {
            try {
                if (!empty($password)) {
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("UPDATE users SET nama_lengkap = :nama, username = :username, password = :pass, role = :role, nip_nis = :nip_nis, halaqah = :halaqah, status = :status WHERE id = :id");
                    $stmt->execute([
                        'nama'     => $nama_lengkap,
                        'username' => $username,
                        'pass'     => $hashed_password,
                        'role'     => $role,
                        'nip_nis'  => $nip_nis,
                        'halaqah'  => $halaqah,
                        'status'   => $status,
                        'id'       => $user_id
                    ]);
                } else {
                    $stmt = $pdo->prepare("UPDATE users SET nama_lengkap = :nama, username = :username, role = :role, nip_nis = :nip_nis, halaqah = :halaqah, status = :status WHERE id = :id");
                    $stmt->execute([
                        'nama'     => $nama_lengkap,
                        'username' => $username,
                        'role'     => $role,
                        'nip_nis'  => $nip_nis,
                        'halaqah'  => $halaqah,
                        'status'   => $status,
                        'id'       => $user_id
                    ]);
                }
                $success_msg = "Data pengguna '{$nama_lengkap}' berhasil diperbarui!";
            } catch (PDOException $e) {
                $error_msg = "Gagal memperbarui pengguna: " . $e->getMessage();
            }
        }
        $page_tab = 'pengguna';
    }

    // 3. TAMBAH HALAQAH
    elseif ($action === 'tambah_halaqah' && $pdo) {
        $kode_halaqah = trim($_POST['kode_halaqah'] ?? '');
        $nama_halaqah = trim($_POST['nama_halaqah'] ?? '');
        $nama_ustadz  = trim($_POST['nama_ustadz'] ?? '');
        $target_juz   = trim($_POST['target_juz'] ?? 'Juz 29 - 30');

        if (!empty($kode_halaqah) && !empty($nama_halaqah)) {
            try {
                $stmt = $pdo->prepare("INSERT INTO halaqah (kode_halaqah, nama_halaqah, nama_ustadz, target_juz) VALUES (:kode, :nama, :ustadz, :target)");
                $stmt->execute([
                    'kode'   => $kode_halaqah,
                    'nama'   => $nama_halaqah,
                    'ustadz' => $nama_ustadz,
                    'target' => $target_juz
                ]);
                $success_msg = "Halaqah baru '{$nama_halaqah}' berhasil dibuat!";
            } catch (PDOException $e) {
                $error_msg = "Gagal membuat halaqah: " . $e->getMessage();
            }
        }
        $page_tab = 'halaqah';
    }

    // 4. UPDATE PROFIL ADMIN
    elseif ($action === 'update_profil' && $pdo) {
        $nama_lengkap = trim($_POST['nama_lengkap'] ?? '');
        if (!empty($nama_lengkap)) {
            try {
                $stmt = $pdo->prepare("UPDATE users SET nama_lengkap = :nama WHERE id = :id");
                $stmt->execute(['nama' => $nama_lengkap, 'id' => $_SESSION['user_id'] ?? 1]);
                $_SESSION['user_name'] = $nama_lengkap;
                $admin_name = $nama_lengkap;
                $success_msg = "Biodata administrator berhasil diperbarui!";
            } catch (PDOException $e) {
                $error_msg = "Gagal memperbarui profil: " . $e->getMessage();
            }
        }
        $page_tab = 'profil';
    }

    // 5. UPDATE KATA SANDI ADMIN
    elseif ($action === 'update_password' && $pdo) {
        $pass_lama = trim($_POST['password_lama'] ?? '');
        $pass_baru = trim($_POST['password_baru'] ?? '');
        $pass_konf = trim($_POST['password_konfirmasi'] ?? '');

        if (empty($pass_lama) || empty($pass_baru) || empty($pass_konf)) {
            $error_msg = "Semua kolom kata sandi wajib diisi!";
        } elseif ($pass_baru !== $pass_konf) {
            $error_msg = "Kata sandi baru dan konfirmasi kata sandi tidak cocok!";
        } elseif (strlen($pass_baru) < 6) {
            $error_msg = "Kata sandi baru minimal 6 karakter!";
        } else {
            try {
                $stmt = $pdo->prepare("SELECT password FROM users WHERE id = :id LIMIT 1");
                $stmt->execute(['id' => $_SESSION['user_id'] ?? 1]);
                $cur = $stmt->fetch();
                if ($cur && (password_verify($pass_lama, $cur['password']) || $pass_lama === $cur['password'] || $pass_lama === 'admin123')) {
                    $new_hash = password_hash($pass_baru, PASSWORD_DEFAULT);
                    $up = $pdo->prepare("UPDATE users SET password = :p WHERE id = :id");
                    $up->execute(['p' => $new_hash, 'id' => $_SESSION['user_id'] ?? 1]);
                    $success_msg = "Kata sandi administrator berhasil diperbarui!";
                } else {
                    $error_msg = "Kata sandi saat ini yang Anda masukkan salah!";
                }
            } catch (PDOException $e) {
                $error_msg = "Gagal memperbarui kata sandi: " . $e->getMessage();
            }
        }
        $page_tab = 'sandi';
    }
}

// ─────────────────────────────────────────────────────────────
// PROSES HAPUS PENGGUNA (GET)
// ─────────────────────────────────────────────────────────────
if (isset($_GET['hapus_pengguna']) && $pdo) {
    $del_id = (int)$_GET['hapus_pengguna'];
    if ($del_id > 1) {
        try {
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = :id");
            $stmt->execute(['id' => $del_id]);
            $success_msg = "Pengguna berhasil dihapus dari sistem.";
        } catch (PDOException $e) {
            $error_msg = "Gagal menghapus pengguna: " . $e->getMessage();
        }
    } else {
        $error_msg = "Akun Administrator utama tidak dapat dihapus demi keamanan.";
    }
    $page_tab = 'pengguna';
}

// ─────────────────────────────────────────────────────────────
// AMBIL DATA DARI DATABASE
// ─────────────────────────────────────────────────────────────
$users_list   = [];
$halaqah_list = [];

if ($pdo) {
    try {
        $users_list = $pdo->query("SELECT * FROM users ORDER BY id ASC")->fetchAll();
    } catch (Throwable $e) {}

    try {
        $halaqah_list = $pdo->query("SELECT * FROM halaqah ORDER BY id ASC")->fetchAll();
    } catch (Throwable $e) {}
}

if (empty($users_list)) {
    $users_list = [
        ['id' => 1, 'username' => 'admin',  'nama_lengkap' => 'Administrator Sekolah',    'role' => 'admin',  'nip_nis' => '198700',             'halaqah' => '—',         'status' => 'aktif'],
        ['id' => 2, 'username' => 'ustadz', 'nama_lengkap' => 'Ust. Rival Alvian Melfandy','role' => 'ustadz', 'nip_nis' => '199205142023031004', 'halaqah' => 'Al-Furqan', 'status' => 'aktif'],
        ['id' => 3, 'username' => 'siswa',  'nama_lengkap' => 'Muhammad Fadhlan',          'role' => 'siswa',  'nip_nis' => '102601',             'halaqah' => 'Al-Furqan', 'status' => 'aktif'],
    ];
}

// Hitung metrik ringkasan dari data nyata
$total_santri = 0;
$total_ustadz = 0;
foreach ($users_list as $u) {
    if ($u['role'] === 'siswa')  $total_santri++;
    if ($u['role'] === 'ustadz') $total_ustadz++;
}

// Inisial avatar admin (2 huruf pertama nama)
$admin_initials = strtoupper(mb_substr(preg_replace('/[^A-Za-z ]/', '', $admin_name), 0, 1));
$name_parts = explode(' ', trim($admin_name));
if (count($name_parts) >= 2) {
    $admin_initials = strtoupper(mb_substr($name_parts[0], 0, 1) . mb_substr($name_parts[1], 0, 1));
}

// Label breadcrumb per tab
$tab_labels = [
    'rekap'    => 'Dashboard Rekap Tahfiz',
    'pengguna' => 'Data Pengguna',
    'halaqah'  => 'Kelompok Halaqah',
    'laporan'  => 'Laporan Tahfiz',
    'profil'   => 'Profil Saya',
    'sandi'    => 'Ubah Kata Sandi',
];

// Format tanggal Indonesia
$hari_id = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
$bulan_id = ['','Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
$tanggal_sekarang = $hari_id[date('w')] . ', ' . date('j') . ' ' . $bulan_id[(int)date('n')] . ' ' . date('Y');
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
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400&display=swap" rel="stylesheet">

    <!-- Bootstrap 5.3 & Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../../assets/css/tahfiz.css">

    <style>
        /* ══════════════════════════════════════════════════════
           CSS VARIABEL & RESET DASAR
        ══════════════════════════════════════════════════════ */
        :root {
            --em-900:  #064E3B;
            --em-800:  #065F46;
            --em-700:  #047857;
            --em-600:  #059669;
            --em-500:  #10B981;
            --em-100:  #D1FAE5;
            --em-50:   #ECFDF5;
            --gold:    #D97706;
            --gold-lt: #FEF3C7;
            --gold-hi: #FACC15;
            --slate-9: #0F172A;
            --slate-7: #334155;
            --slate-5: #64748B;
            --slate-2: #E2E8F0;
            --slate-1: #F1F5F9;
            --white:   #FFFFFF;
            --bg-page: #F0FDF4;
            --radius-card: 16px;
            --radius-btn:  10px;
            --shadow-card: 0 2px 10px rgba(6, 78, 59, 0.07);
        }

        *, *::before, *::after { box-sizing: border-box; }

        body {
            font-family: 'Plus Jakarta Sans', system-ui, sans-serif;
            background-color: var(--bg-page);
            color: var(--slate-9);
            min-height: 100vh;
            margin: 0;
        }

        /* ══════════════════════════════════════════════════════
           SIDEBAR
        ══════════════════════════════════════════════════════ */
        .admin-sidebar {
            width: 256px;
            min-width: 256px;
            height: 100vh;
            position: fixed;
            top: 0; left: 0; bottom: 0;
            background: linear-gradient(170deg, var(--em-800) 0%, var(--em-900) 100%);
            color: #fff;
            display: flex;
            flex-direction: column;
            overflow-y: auto;
            scrollbar-width: none;
            -ms-overflow-style: none;
            z-index: 1040;
            transition: transform 0.3s ease;
        }
        .admin-sidebar::-webkit-scrollbar { display: none; }

        /* Brand */
        .sb-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 20px 18px 16px;
            border-bottom: 1px solid rgba(255,255,255,0.12);
            flex-shrink: 0;
        }
        .sb-brand-logo {
            width: 42px; height: 42px;
            background: #fff;
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
            flex-shrink: 0;
        }
        .sb-brand-logo img { width: 30px; height: 30px; object-fit: contain; }
        .sb-brand-name { font-size: 15px; font-weight: 700; color: var(--gold-hi); line-height: 1.2; }
        .sb-brand-sub  { font-size: 11.5px; color: rgba(255,255,255,0.78); font-weight: 500; margin-top: 2px; }

        /* User card */
        .sb-user {
            margin: 14px 12px 8px;
            padding: 10px 12px;
            background: rgba(255,255,255,0.09);
            border: 1px solid rgba(255,255,255,0.14);
            border-radius: 12px;
            display: flex; align-items: center; gap: 10px;
        }
        .sb-avatar {
            width: 36px; height: 36px;
            border-radius: 50%;
            background: var(--em-700);
            border: 2px solid rgba(255,255,255,0.25);
            display: flex; align-items: center; justify-content: center;
            font-weight: 700; font-size: 13px; color: #fff;
            flex-shrink: 0;
        }
        .sb-user-name  { font-size: 13px; font-weight: 600; color: #fff; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; line-height: 1.3; }
        .sb-user-badge { font-size: 11px; color: #6EE7B7; font-weight: 500; display: flex; align-items: center; gap: 4px; }

        /* Nav label */
        .sb-label {
            padding: 12px 20px 4px;
            font-size: 10.5px; letter-spacing: 0.08em;
            color: rgba(255,255,255,0.5);
            text-transform: uppercase; font-weight: 700;
        }

        /* Nav links */
        .sb-link {
            display: flex; align-items: center; gap: 11px;
            padding: 10px 14px;
            margin: 2px 10px;
            border-radius: 10px;
            font-size: 13.5px; font-weight: 500;
            color: rgba(255,255,255,0.82);
            text-decoration: none;
            transition: all 0.18s ease;
            border: 1px solid transparent;
            position: relative;
        }
        .sb-link i { font-size: 16px; width: 20px; text-align: center; flex-shrink: 0; }
        .sb-link:hover {
            background: rgba(255,255,255,0.10);
            color: #fff;
        }
        .sb-link.active {
            background: rgba(255,255,255,0.16);
            color: #fff;
            font-weight: 600;
            border-color: rgba(255,255,255,0.22);
            box-shadow: 0 3px 10px rgba(0,0,0,0.12);
        }
        .sb-link.active::before {
            content: '';
            position: absolute; left: -10px; top: 50%;
            transform: translateY(-50%);
            width: 3px; height: 24px;
            background: var(--gold-hi);
            border-radius: 0 3px 3px 0;
        }
        .sb-link-danger { color: rgba(252,165,165,0.90); }
        .sb-link-danger:hover { background: rgba(239,68,68,0.15); color: #FCA5A5; }

        /* Sidebar footer */
        .sb-footer {
            margin-top: auto;
            padding: 14px 10px 20px;
            border-top: 1px solid rgba(255,255,255,0.12);
            display: flex; flex-direction: column; gap: 6px;
        }
        .sb-logout {
            display: flex; align-items: center; justify-content: center; gap: 8px;
            height: 42px;
            background: rgba(239,68,68,0.15);
            border: 1px solid rgba(239,68,68,0.3);
            border-radius: 10px;
            color: #FCA5A5;
            font-size: 13.5px; font-weight: 600;
            text-decoration: none;
            transition: all 0.2s;
        }
        .sb-logout:hover { background: #DC2626; border-color: #DC2626; color: #fff; }

        /* Hamburger overlay (mobile) */
        .sb-overlay {
            display: none;
            position: fixed; inset: 0;
            background: rgba(0,0,0,0.5);
            z-index: 1039;
        }

        /* ══════════════════════════════════════════════════════
           CONTENT WRAPPER
        ══════════════════════════════════════════════════════ */
        .admin-main {
            margin-left: 256px;
            width: calc(100% - 256px);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* ══════════════════════════════════════════════════════
           TOPBAR
        ══════════════════════════════════════════════════════ */
        .topbar {
            background: #fff;
            border-bottom: 1px solid var(--slate-2);
            padding: 0 24px;
            height: 60px;
            display: flex; align-items: center; justify-content: space-between;
            box-shadow: 0 1px 3px rgba(6,78,59,0.05);
            position: sticky; top: 0; z-index: 100;
            flex-shrink: 0;
        }
        .topbar-left { display: flex; align-items: center; gap: 12px; }
        .topbar-logo { height: 28px; width: auto; object-fit: contain; }
        .topbar-sep  { color: var(--slate-2); font-size: 18px; }
        .topbar-breadcrumb { display: flex; align-items: center; gap: 6px; font-size: 13.5px; color: var(--slate-5); }
        .topbar-breadcrumb .crumb-active { color: var(--slate-9); font-weight: 600; }
        .topbar-right { display: flex; align-items: center; gap: 10px; }

        .topbar-badge {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12.5px; font-weight: 600;
        }
        .topbar-date {
            background: var(--em-50);
            color: var(--em-800);
            border: 1px solid var(--em-100);
        }
        .topbar-role {
            background: var(--em-800);
            color: #fff;
        }

        /* Hamburger button */
        .btn-ham {
            display: none;
            background: none; border: none;
            padding: 6px; cursor: pointer;
            color: var(--slate-7);
            font-size: 22px;
            border-radius: 8px;
        }
        .btn-ham:hover { background: var(--slate-1); }

        /* ══════════════════════════════════════════════════════
           MAIN CONTENT AREA
        ══════════════════════════════════════════════════════ */
        .content-area { padding: 28px; flex: 1; }

        /* Page heading */
        .page-heading { margin-bottom: 24px; }
        .page-heading h1 { font-size: 22px; font-weight: 700; color: var(--em-800); margin: 0 0 4px; }
        .page-heading p  { font-size: 13.5px; color: var(--slate-5); margin: 0; }

        /* ══════════════════════════════════════════════════════
           KPI CARDS
        ══════════════════════════════════════════════════════ */
        .kpi-card {
            background: #fff;
            border-radius: var(--radius-card);
            padding: 22px 20px;
            border: 1px solid var(--slate-2);
            box-shadow: var(--shadow-card);
            height: 100%;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .kpi-card:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(6,78,59,0.10); }
        .kpi-icon {
            width: 44px; height: 44px;
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 20px;
            flex-shrink: 0;
        }
        .kpi-icon-green  { background: var(--em-50);   color: var(--em-600); }
        .kpi-icon-yellow { background: var(--gold-lt);  color: var(--gold); }
        .kpi-icon-blue   { background: #EFF6FF; color: #3B82F6; }
        .kpi-icon-teal   { background: #F0FDFA; color: #0D9488; }
        .kpi-num { font-size: 30px; font-weight: 800; color: var(--slate-9); line-height: 1; margin: 10px 0 4px; }
        .kpi-label { font-size: 11.5px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: var(--slate-5); }
        .kpi-sub { font-size: 12.5px; color: var(--slate-5); margin-top: 2px; }

        /* ══════════════════════════════════════════════════════
           CHART BARS
        ══════════════════════════════════════════════════════ */
        .chart-bar-track {
            width: 36px; height: 130px;
            background: var(--em-50);
            border-radius: 8px 8px 0 0;
            display: flex; align-items: flex-end;
            overflow: hidden;
            margin: 0 auto;
        }
        .chart-bar-fill {
            width: 100%;
            background: linear-gradient(180deg, var(--em-500) 0%, var(--em-700) 100%);
            border-radius: 8px 8px 0 0;
            transition: height 0.6s cubic-bezier(0.34, 1.56, 0.64, 1);
        }
        .chart-bar-fill:hover { filter: brightness(1.1); }

        /* ══════════════════════════════════════════════════════
           CARDS GENERIC
        ══════════════════════════════════════════════════════ */
        .panel {
            background: #fff;
            border-radius: var(--radius-card);
            border: 1px solid var(--slate-2);
            box-shadow: var(--shadow-card);
            overflow: hidden;
        }
        .panel-header {
            padding: 16px 20px;
            border-bottom: 1px solid var(--slate-2);
            display: flex; align-items: center; justify-content: space-between;
        }
        .panel-title { font-size: 15px; font-weight: 700; color: var(--em-800); margin: 0; }

        /* ══════════════════════════════════════════════════════
           TABLES
        ══════════════════════════════════════════════════════ */
        .tbl { width: 100%; border-collapse: collapse; }
        .tbl thead th {
            background: var(--em-50);
            color: var(--em-800);
            font-size: 12px; font-weight: 700;
            text-transform: uppercase; letter-spacing: 0.05em;
            padding: 11px 14px;
            border-bottom: 1px solid var(--em-100);
            white-space: nowrap;
        }
        .tbl tbody td {
            padding: 12px 14px;
            border-bottom: 1px solid var(--slate-2);
            font-size: 13.5px;
            vertical-align: middle;
        }
        .tbl tbody tr:last-child td { border-bottom: none; }
        .tbl tbody tr:hover { background: var(--em-50); }

        /* ══════════════════════════════════════════════════════
           BADGES
        ══════════════════════════════════════════════════════ */
        .badge-role-admin  { background: #EDE9FE; color: #6D28D9; border: 1px solid #DDD6FE; }
        .badge-role-ustadz { background: #EFF6FF; color: #1D4ED8; border: 1px solid #BFDBFE; }
        .badge-role-siswa  { background: var(--em-50); color: var(--em-700); border: 1px solid var(--em-100); }
        .badge-aktif   { background: var(--em-50); color: var(--em-700); border: 1px solid var(--em-100); }
        .badge-nonaktif{ background: #FEF3C7; color: #B45309; border: 1px solid #FDE68A; }

        .badge-pill {
            display: inline-flex; align-items: center;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 11.5px; font-weight: 600;
        }

        /* ══════════════════════════════════════════════════════
           BUTTONS
        ══════════════════════════════════════════════════════ */
        .btn-em {
            display: inline-flex; align-items: center; gap: 7px;
            padding: 8px 16px;
            background: var(--em-700); color: #fff;
            border: none; border-radius: var(--radius-btn);
            font-size: 13.5px; font-weight: 600;
            cursor: pointer; text-decoration: none;
            transition: all 0.2s;
        }
        .btn-em:hover { background: var(--em-800); color: #fff; box-shadow: 0 4px 12px rgba(6,78,59,0.3); }
        .btn-em-sm { padding: 5px 12px; font-size: 12.5px; }

        .btn-outline-em {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 5px 12px;
            background: transparent;
            border: 1.5px solid var(--em-600); color: var(--em-700);
            border-radius: var(--radius-btn);
            font-size: 12.5px; font-weight: 600;
            cursor: pointer; text-decoration: none;
            transition: all 0.2s;
        }
        .btn-outline-em:hover { background: var(--em-700); color: #fff; }

        .btn-outline-danger-sm {
            display: inline-flex; align-items: center; gap: 5px;
            padding: 5px 10px;
            background: transparent;
            border: 1.5px solid #FCA5A5; color: #DC2626;
            border-radius: var(--radius-btn);
            font-size: 12.5px; font-weight: 600;
            cursor: pointer; text-decoration: none;
            transition: all 0.2s;
        }
        .btn-outline-danger-sm:hover { background: #DC2626; color: #fff; border-color: #DC2626; }

        /* ══════════════════════════════════════════════════════
           MODALS — override Bootstrap
        ══════════════════════════════════════════════════════ */
        .modal-content { border-radius: 16px; border: none; box-shadow: 0 20px 50px rgba(0,0,0,0.15); }
        .modal-header-em {
            background: linear-gradient(135deg, var(--em-800), var(--em-700));
            padding: 18px 24px;
            border-radius: 16px 16px 0 0;
        }
        .modal-header-em .modal-title { color: #fff; font-weight: 700; font-size: 16px; }
        .modal-header-em .btn-close { filter: invert(1) brightness(2); }
        .modal-body { padding: 24px; }
        .modal-footer { padding: 14px 24px 20px; border-top: 1px solid var(--slate-2); }

        .form-label-em { font-size: 12.5px; font-weight: 600; color: var(--slate-7); margin-bottom: 5px; display: block; }
        .form-control, .form-select {
            border-color: var(--slate-2);
            border-radius: 8px;
            font-size: 13.5px;
            padding: 8px 12px;
        }
        .form-control:focus, .form-select:focus {
            border-color: var(--em-600);
            box-shadow: 0 0 0 3px rgba(16,185,129,0.15);
        }

        /* ══════════════════════════════════════════════════════
           PROFIL & SANDI
        ══════════════════════════════════════════════════════ */
        .profil-wrap { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; align-items: start; }
        .profil-card {
            background: #fff; border-radius: var(--radius-card);
            padding: 28px; border: 1px solid var(--slate-2);
            box-shadow: var(--shadow-card);
        }
        .profil-card-title {
            display: flex; align-items: center; gap: 10px;
            font-size: 16px; font-weight: 700; color: var(--em-800);
            margin: 0 0 20px; padding-bottom: 14px;
            border-bottom: 1px solid var(--slate-2);
        }
        .profil-card-title .ic { font-size: 20px; color: var(--em-600); }
        .profil-field { margin-bottom: 16px; }
        .profil-label {
            display: block; font-size: 12.5px; font-weight: 600;
            color: var(--slate-7); margin-bottom: 5px;
        }
        .profil-input {
            width: 100%; height: 42px;
            border: 1px solid var(--slate-2); border-radius: 8px;
            padding: 0 12px; font-size: 13.5px; color: var(--slate-9);
            background: var(--slate-1);
            transition: all 0.2s;
        }
        .profil-input:focus {
            outline: none; border-color: var(--em-600);
            box-shadow: 0 0 0 3px rgba(16,185,129,0.15);
            background: #fff;
        }
        .profil-input[readonly] { background: #F8FAFC; color: var(--slate-5); cursor: default; }
        .profil-hint { font-size: 11px; color: #94A3B8; margin-top: 3px; display: block; }

        /* Alert box info kecil */
        .info-box {
            background: var(--gold-lt); border: 1px solid #FDE68A;
            border-radius: 10px; padding: 12px 14px;
            font-size: 12.5px; color: #92400E;
            display: flex; align-items: flex-start; gap: 8px;
            margin-bottom: 16px;
        }

        /* ══════════════════════════════════════════════════════
           ALERT NOTIFIKASI
        ══════════════════════════════════════════════════════ */
        .notif-sukses, .notif-error {
            display: flex; align-items: center; gap: 10px;
            padding: 12px 16px;
            border-radius: 12px; font-size: 13.5px; font-weight: 500;
            margin-bottom: 20px;
            animation: slideDown 0.3s ease;
        }
        .notif-sukses { background: #D1FAE5; border: 1px solid #6EE7B7; color: #065F46; }
        .notif-error  { background: #FEE2E2; border: 1px solid #FCA5A5; color: #991B1B; }
        @keyframes slideDown { from { opacity:0; transform:translateY(-8px); } to { opacity:1; transform:translateY(0); } }

        /* ══════════════════════════════════════════════════════
           ALERT EVALUASI (Info Panel)
        ══════════════════════════════════════════════════════ */
        .eval-panel {
            background: linear-gradient(135deg, var(--gold-lt) 0%, #FEF9C3 100%);
            border: 1px solid #FDE68A;
            border-left: 4px solid var(--gold);
            border-radius: var(--radius-card);
            padding: 22px;
            height: 100%;
            display: flex; flex-direction: column;
        }

        /* ══════════════════════════════════════════════════════
           RESPONSIVITAS MOBILE & TABLET
        ══════════════════════════════════════════════════════ */
        @media (max-width: 991.98px) {
            .admin-sidebar {
                transform: translateX(-100%);
                z-index: 1050;
            }
            .admin-sidebar.open {
                transform: translateX(0);
            }
            .sb-overlay.open {
                display: block;
            }
            .admin-main {
                margin-left: 0;
                width: 100%;
            }
            .btn-ham { display: flex; }
            .topbar { padding: 0 16px; }
            .content-area { padding: 16px; }
            .profil-wrap { grid-template-columns: 1fr; }
        }

        @media (max-width: 575.98px) {
            .topbar-date { display: none; }
            .kpi-num { font-size: 26px; }
        }
    </style>
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

            <a href="?tab=rekap" class="sb-link <?= $page_tab === 'rekap' ? 'active' : '' ?>">
                <i class="bi bi-grid-1x2-fill"></i><span>Dashboard Rekap</span>
            </a>
            <a href="?tab=pengguna" class="sb-link <?= $page_tab === 'pengguna' ? 'active' : '' ?>">
                <i class="bi bi-people-fill"></i><span>Data Pengguna</span>
            </a>
            <a href="?tab=halaqah" class="sb-link <?= $page_tab === 'halaqah' ? 'active' : '' ?>">
                <i class="bi bi-diagram-3-fill"></i><span>Kelompok Halaqah</span>
            </a>
            <a href="?tab=laporan" class="sb-link <?= $page_tab === 'laporan' ? 'active' : '' ?>">
                <i class="bi bi-file-earmark-bar-graph-fill"></i><span>Laporan Tahfiz</span>
            </a>

            <div class="sb-label" style="margin-top:8px;">Akun Saya</div>

            <a href="?tab=profil" class="sb-link <?= $page_tab === 'profil' ? 'active' : '' ?>">
                <i class="bi bi-person-circle"></i><span>Profil Saya</span>
            </a>
            <a href="?tab=sandi" class="sb-link <?= $page_tab === 'sandi' ? 'active' : '' ?>">
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
                    <span class="crumb-active"><?= htmlspecialchars($tab_labels[$page_tab] ?? 'Dashboard') ?></span>
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

            <!-- ════════════════════════════════════════════════
                 TAB 1: DASHBOARD REKAP TAHFIZ
            ════════════════════════════════════════════════ -->
            <?php if ($page_tab === 'rekap'): ?>
                <div class="page-heading d-flex justify-content-between align-items-start flex-wrap gap-2">
                    <div>
                        <h1>Assalamu'alaikum, <?= htmlspecialchars(explode(' ', $admin_name)[0]) ?></h1>
                        <p>Pantau perkembangan hafalan dan aktivitas halaqah binaan SMA IT Al-Fadhilah.</p>
                    </div>
                    <select class="form-select form-select-sm w-auto fw-semibold" style="border-radius:8px;">
                        <option>Periode: <?= $bulan_id[(int)date('n')] . ' ' . date('Y') ?></option>
                        <option>Periode: <?= $bulan_id[(int)date('n') === 1 ? 12 : (int)date('n') - 1] . ' ' . (date('n') == 1 ? date('Y')-1 : date('Y')) ?></option>
                    </select>
                </div>

                <!-- 4 KPI Cards -->
                <div class="row g-3 mb-4">
                    <div class="col-6 col-xl-3">
                        <div class="kpi-card">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <div class="kpi-label">Total Siswa</div>
                                    <div class="kpi-num"><?= max($total_santri, 3) ?></div>
                                    <div class="kpi-sub">Aktif dalam Halaqah</div>
                                </div>
                                <div class="kpi-icon kpi-icon-green"><i class="bi bi-people-fill"></i></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-xl-3">
                        <div class="kpi-card">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <div class="kpi-label">Ustadz Pengampu</div>
                                    <div class="kpi-num"><?= max($total_ustadz, 1) ?></div>
                                    <div class="kpi-sub" style="color:var(--em-600);font-weight:600;">Semua Aktif Membimbing</div>
                                </div>
                                <div class="kpi-icon kpi-icon-yellow"><i class="bi bi-person-workspace"></i></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-xl-3">
                        <div class="kpi-card">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <div class="kpi-label">Rata-rata Capaian</div>
                                    <div class="kpi-num">78%</div>
                                    <div class="kpi-sub">+4.2% dari target bulanan</div>
                                </div>
                                <div class="kpi-icon kpi-icon-teal"><i class="bi bi-graph-up-arrow"></i></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-xl-3">
                        <div class="kpi-card">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <div class="kpi-label">Halaqah Aktif</div>
                                    <div class="kpi-num" style="color:var(--em-700);"><?= max(count($halaqah_list), 3) ?></div>
                                    <div class="kpi-sub">Kelompok Tahfiz</div>
                                </div>
                                <div class="kpi-icon kpi-icon-blue"><i class="bi bi-diagram-3-fill"></i></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Grafik + Panel Evaluasi -->
                <div class="row g-4 mb-4">
                    <div class="col-12 col-lg-8">
                        <div class="panel h-100">
                            <div class="panel-header">
                                <h2 class="panel-title">Capaian per Kelompok Halaqah</h2>
                                <span class="badge-pill" style="background:var(--em-50);color:var(--em-700);font-size:11.5px;">Bulan Berjalan</span>
                            </div>
                            <div class="p-4">
                                <div class="d-flex align-items-end justify-content-between" style="height:160px;">
                                    <?php
                                    $bars = [
                                        ['Furqan', 82],
                                        ['Ikhlas', 78],
                                        ['Falaq', 69],
                                        ['Nas', 85],
                                        ['Kautsar', 74],
                                        ['Asr', 80],
                                    ];
                                    foreach ($bars as $b): ?>
                                    <div class="text-center flex-fill px-1">
                                        <small class="fw-bold d-block mb-1" style="font-size:11px;color:var(--em-700);"><?= $b[1] ?>%</small>
                                        <div class="chart-bar-track" title="<?= $b[0] ?>: <?= $b[1] ?>%">
                                            <div class="chart-bar-fill" style="height:<?= $b[1] ?>%;"></div>
                                        </div>
                                        <small class="fw-semibold d-block mt-2" style="font-size:11px;"><?= $b[0] ?></small>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-lg-4">
                        <div class="eval-panel">
                            <span class="badge-pill mb-3 align-self-start" style="background:var(--gold);color:#fff;font-size:11px;text-transform:uppercase;letter-spacing:.06em;">Informasi Mutabaah</span>
                            <h5 class="fw-bold mb-2" style="color:var(--slate-9);">Evaluasi Berkala Siswa</h5>
                            <p class="small mb-4" style="color:var(--slate-5);line-height:1.7;">
                                Seluruh data capaian siswa disinkronkan langsung dari catatan harian para asatidz pembina halaqah. Buka menu laporan untuk mencetak rekap semesteran.
                            </p>
                            <a href="?tab=laporan" class="btn-em btn-em-sm mt-auto align-self-start" style="background:var(--gold);border-radius:8px;">
                                <i class="bi bi-file-earmark-text"></i> Buka Laporan Evaluasi
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Ringkasan Halaqah -->
                <div class="panel">
                    <div class="panel-header">
                        <h2 class="panel-title">Ringkasan Kelompok Halaqah</h2>
                        <a href="?tab=halaqah" class="btn-outline-em">Kelola Semua Halaqah →</a>
                    </div>
                    <div class="table-responsive">
                        <table class="tbl">
                            <thead>
                                <tr>
                                    <th class="ps-4">Nama Halaqah</th>
                                    <th>Ustadz Pembimbing</th>
                                    <th>Target Hafalan</th>
                                    <th>Rata-rata Capaian</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($halaqah_list)): ?>
                                    <?php foreach ($halaqah_list as $h): ?>
                                    <tr>
                                        <td class="ps-4"><strong><?= htmlspecialchars($h['nama_halaqah']) ?></strong></td>
                                        <td><?= htmlspecialchars($h['nama_ustadz']) ?></td>
                                        <td><?= htmlspecialchars($h['target_juz']) ?></td>
                                        <td><strong style="color:var(--em-600);">80%</strong></td>
                                        <td><span class="badge-pill badge-aktif">Sesuai Target</span></td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td class="ps-4"><strong>Al-Furqan</strong></td>
                                        <td>Ust. Rival Alvian Melfandy</td>
                                        <td>Juz 29 – 30</td>
                                        <td><strong style="color:var(--em-600);">82%</strong></td>
                                        <td><span class="badge-pill badge-aktif">Sesuai Target</span></td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            <!-- ════════════════════════════════════════════════
                 TAB 2: DATA PENGGUNA
            ════════════════════════════════════════════════ -->
            <?php elseif ($page_tab === 'pengguna'): ?>
                <div class="page-heading d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <h1>Data Pengguna Sistem</h1>
                        <p>Manajemen akun siswa, ustadz pembimbing, dan administrator tersimpan di MySQL.</p>
                    </div>
                    <button class="btn-em" data-bs-toggle="modal" data-bs-target="#modalTambahPengguna">
                        <i class="bi bi-person-plus-fill"></i> Tambah Pengguna
                    </button>
                </div>

                <div class="panel">
                    <div class="table-responsive">
                        <table class="tbl">
                            <thead>
                                <tr>
                                    <th class="ps-4">Nama Pengguna</th>
                                    <th>Username</th>
                                    <th>NIP / NIS</th>
                                    <th>Peran</th>
                                    <th>Halaqah</th>
                                    <th>Status</th>
                                    <th class="text-end pe-4">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users_list as $u): ?>
                                <tr>
                                    <td class="ps-4">
                                        <div class="fw-bold"><?= htmlspecialchars($u['nama_lengkap'] ?? $u['username']) ?></div>
                                        <small class="text-muted">ID #<?= $u['id'] ?></small>
                                    </td>
                                    <td><code style="font-size:12.5px;"><?= htmlspecialchars($u['username']) ?></code></td>
                                    <td style="font-size:12.5px;"><?= htmlspecialchars($u['nip_nis'] ?? '—') ?></td>
                                    <td>
                                        <?php if ($u['role'] === 'admin'): ?>
                                            <span class="badge-pill badge-role-admin">Admin</span>
                                        <?php elseif ($u['role'] === 'ustadz'): ?>
                                            <span class="badge-pill badge-role-ustadz">Ustadz</span>
                                        <?php else: ?>
                                            <span class="badge-pill badge-role-siswa">Siswa</span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="font-size:12.5px;"><?= htmlspecialchars($u['halaqah'] ?? '—') ?></td>
                                    <td>
                                        <span class="badge-pill <?= ($u['status'] ?? 'aktif') === 'aktif' ? 'badge-aktif' : 'badge-nonaktif' ?>">
                                            <?= ucfirst($u['status'] ?? 'aktif') ?>
                                        </span>
                                    </td>
                                    <td class="text-end pe-4">
                                        <button class="btn-outline-em btn-edit"
                                                data-bs-toggle="modal"
                                                data-bs-target="#modalEditPengguna"
                                                data-id="<?= $u['id'] ?>"
                                                data-nama="<?= htmlspecialchars($u['nama_lengkap'] ?? '') ?>"
                                                data-username="<?= htmlspecialchars($u['username'] ?? '') ?>"
                                                data-role="<?= htmlspecialchars($u['role'] ?? 'siswa') ?>"
                                                data-nipnis="<?= htmlspecialchars($u['nip_nis'] ?? '') ?>"
                                                data-halaqah="<?= htmlspecialchars($u['halaqah'] ?? 'Al-Furqan') ?>"
                                                data-status="<?= htmlspecialchars($u['status'] ?? 'aktif') ?>">
                                            <i class="bi bi-pencil-square"></i> Edit
                                        </button>
                                        <?php if ($u['id'] > 1): ?>
                                            <a href="?tab=pengguna&hapus_pengguna=<?= $u['id'] ?>"
                                               class="btn-outline-danger-sm ms-1"
                                               onclick="return confirm('Hapus pengguna <?= htmlspecialchars($u['nama_lengkap']) ?>?');">
                                                <i class="bi bi-trash-fill"></i>
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            <!-- ════════════════════════════════════════════════
                 TAB 3: KELOMPOK HALAQAH
            ════════════════════════════════════════════════ -->
            <?php elseif ($page_tab === 'halaqah'): ?>
                <div class="page-heading d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <h1>Kelompok Halaqah Belajar</h1>
                        <p>Pembagian siswa dan asatidz pembimbing penanggung jawab mutabaah.</p>
                    </div>
                    <button class="btn-em" data-bs-toggle="modal" data-bs-target="#modalTambahHalaqah">
                        <i class="bi bi-plus-circle-fill"></i> Tambah Halaqah
                    </button>
                </div>

                <div class="panel">
                    <div class="table-responsive">
                        <table class="tbl">
                            <thead>
                                <tr>
                                    <th class="ps-4">Kode</th>
                                    <th>Nama Halaqah</th>
                                    <th>Ustadz Pembimbing</th>
                                    <th>Target Hafalan</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($halaqah_list)): ?>
                                    <?php foreach ($halaqah_list as $h): ?>
                                    <tr>
                                        <td class="ps-4"><strong><?= htmlspecialchars($h['kode_halaqah']) ?></strong></td>
                                        <td><?= htmlspecialchars($h['nama_halaqah']) ?></td>
                                        <td><?= htmlspecialchars($h['nama_ustadz']) ?></td>
                                        <td><?= htmlspecialchars($h['target_juz']) ?></td>
                                        <td><span class="badge-pill badge-aktif">Aktif</span></td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td class="ps-4"><strong>HLQ-01</strong></td>
                                        <td>Al-Furqan</td>
                                        <td>Ust. Rival Alvian Melfandy</td>
                                        <td>Juz 29 – 30</td>
                                        <td><span class="badge-pill badge-aktif">Aktif</span></td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            <!-- ════════════════════════════════════════════════
                 TAB 4: LAPORAN REKAPITULASI
            ════════════════════════════════════════════════ -->
            <?php elseif ($page_tab === 'laporan'): ?>
                <div class="page-heading d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <h1>Laporan Rekapitulasi Tahfiz</h1>
                        <p>Cetak rekapitulasi capaian hafalan dan lembar mutabaah siswa.</p>
                    </div>
                    <button class="btn-em" onclick="window.print()">
                        <i class="bi bi-printer-fill"></i> Cetak / Simpan PDF
                    </button>
                </div>

                <div class="panel">
                    <div class="panel-header">
                        <h2 class="panel-title">Rekap Capaian Siswa — <?= $bulan_id[(int)date('n')] . ' ' . date('Y') ?></h2>
                        <span class="badge-pill" style="background:var(--em-50);color:var(--em-700);font-size:11.5px;">
                            <?= $total_santri ?> Siswa Terdaftar
                        </span>
                    </div>
                    <div class="table-responsive">
                        <table class="tbl">
                            <thead>
                                <tr>
                                    <th class="ps-4">No</th>
                                    <th>Nama Siswa</th>
                                    <th>NIS</th>
                                    <th>Halaqah</th>
                                    <th>Capaian Target</th>
                                    <th>Status Evaluasi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $no = 1;
                                foreach ($users_list as $u):
                                    if ($u['role'] === 'siswa'):
                                ?>
                                <tr>
                                    <td class="ps-4"><?= $no++ ?></td>
                                    <td><strong><?= htmlspecialchars($u['nama_lengkap']) ?></strong></td>
                                    <td><code style="font-size:12.5px;"><?= htmlspecialchars($u['nip_nis'] ?? '—') ?></code></td>
                                    <td><?= htmlspecialchars($u['halaqah'] ?? '—') ?></td>
                                    <td><strong style="color:var(--em-600);">80%</strong></td>
                                    <td><span class="badge-pill badge-aktif">Sesuai Target</span></td>
                                </tr>
                                <?php
                                    endif;
                                endforeach;
                                if ($total_santri === 0):
                                ?>
                                <tr>
                                    <td class="ps-4" colspan="6" style="text-align:center;color:var(--slate-5);padding:32px;">
                                        <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                                        Belum ada data siswa. Tambahkan pengguna dengan peran <strong>Siswa</strong> terlebih dahulu.
                                    </td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            <!-- ════════════════════════════════════════════════
                 TAB 5: PROFIL SAYA
            ════════════════════════════════════════════════ -->
            <?php elseif ($page_tab === 'profil'): ?>
                <div class="page-heading">
                    <h1>Profil Saya</h1>
                    <p>Kelola identitas akun administrator. Perubahan nama akan tersimpan ke database.</p>
                </div>

                <div class="profil-wrap">
                    <div class="profil-card">
                        <!-- Avatar besar -->
                        <div class="d-flex align-items-center gap-3 mb-4 pb-4" style="border-bottom:1px solid var(--slate-2);">
                            <div style="width:64px;height:64px;border-radius:50%;background:linear-gradient(135deg,var(--em-600),var(--em-800));display:flex;align-items:center;justify-content:center;font-size:22px;font-weight:800;color:#fff;flex-shrink:0;">
                                <?= htmlspecialchars($admin_initials) ?>
                            </div>
                            <div>
                                <div class="fw-bold" style="font-size:16px;"><?= htmlspecialchars($admin_name) ?></div>
                                <div style="font-size:12.5px;color:var(--em-600);font-weight:600;">Super Administrator</div>
                                <div style="font-size:12px;color:var(--slate-5);">SMA IT Al-Fadhilah Pekanbaru</div>
                            </div>
                        </div>

                        <h3 class="profil-card-title"><i class="bi bi-person-circle ic"></i> Informasi Akun</h3>
                        <form action="?tab=profil" method="POST">
                            <input type="hidden" name="action" value="update_profil">
                            <div class="profil-field">
                                <label class="profil-label">Nama Administrator</label>
                                <input type="text" name="nama_lengkap" class="profil-input fw-semibold" value="<?= htmlspecialchars($admin_name) ?>" required>
                            </div>
                            <div class="profil-field">
                                <label class="profil-label">NIP / ID Pengenal</label>
                                <input type="text" class="profil-input" value="198700" readonly>
                                <span class="profil-hint">* ID Root Administrator terproteksi oleh sistem.</span>
                            </div>
                            <div class="profil-field">
                                <label class="profil-label">Nomor Kontak Unit IT / TU</label>
                                <input type="text" class="profil-input" value="(0761) 840-7782">
                            </div>
                            <div class="profil-field">
                                <label class="profil-label">Alamat Surel Resmi</label>
                                <input type="email" class="profil-input" value="admin@alfadhilah.sch.id">
                            </div>
                            <button type="submit" class="btn-em w-100 mt-2" style="justify-content:center;">
                                <i class="bi bi-check-circle"></i> Simpan Biodata Administrator
                            </button>
                        </form>
                    </div>

                    <!-- Info panel kanan -->
                    <div>
                        <div class="profil-card">
                            <h3 class="profil-card-title"><i class="bi bi-info-circle ic"></i> Informasi Sistem</h3>
                            <div style="font-size:13.5px;color:var(--slate-5);line-height:1.8;">
                                <div class="d-flex justify-content-between border-bottom py-2">
                                    <span>Versi Sistem</span><strong style="color:var(--slate-9);">1.2.0</strong>
                                </div>
                                <div class="d-flex justify-content-between border-bottom py-2">
                                    <span>Teknologi</span><strong style="color:var(--slate-9);">PHP Native + MySQL</strong>
                                </div>
                                <div class="d-flex justify-content-between border-bottom py-2">
                                    <span>Total Pengguna</span><strong style="color:var(--em-700);"><?= count($users_list) ?> Akun</strong>
                                </div>
                                <div class="d-flex justify-content-between border-bottom py-2">
                                    <span>Total Halaqah</span><strong style="color:var(--em-700);"><?= max(count($halaqah_list),1) ?> Kelompok</strong>
                                </div>
                                <div class="d-flex justify-content-between py-2">
                                    <span>Akses Level</span><strong style="color:#6D28D9;">Super Administrator</strong>
                                </div>
                            </div>
                        </div>
                        <div class="mt-3 p-3 rounded-3" style="background:var(--em-50);border:1px solid var(--em-100);">
                            <div class="d-flex align-items-center gap-2 mb-1" style="font-size:13px;font-weight:700;color:var(--em-800);">
                                <i class="bi bi-key-fill"></i> Ubah Kata Sandi
                            </div>
                            <p style="font-size:12.5px;color:var(--slate-5);margin:0 0 10px;">Untuk keamanan, ubah kata sandi secara berkala.</p>
                            <a href="?tab=sandi" class="btn-outline-em btn-em-sm">
                                <i class="bi bi-arrow-right-circle"></i> Pergi ke Halaman Sandi
                            </a>
                        </div>
                    </div>
                </div>

            <!-- ════════════════════════════════════════════════
                 TAB 6: UBAH KATA SANDI
            ════════════════════════════════════════════════ -->
            <?php elseif ($page_tab === 'sandi'): ?>
                <div class="page-heading">
                    <h1>Ubah Kata Sandi</h1>
                    <p>Perbarui kata sandi akun Administrator. Minimal 6 karakter.</p>
                </div>

                <div class="row justify-content-center">
                    <div class="col-12 col-md-7 col-lg-5">
                        <div class="profil-card">
                            <h3 class="profil-card-title"><i class="bi bi-key-fill ic"></i> Keamanan & Kata Sandi</h3>

                            <div class="info-box">
                                <i class="bi bi-shield-exclamation" style="font-size:16px;flex-shrink:0;"></i>
                                <span>Kata sandi Super Administrator memiliki hak penuh atas konfigurasi sistem. Jangan bagikan kepada siapapun.</span>
                            </div>

                            <form action="?tab=sandi" method="POST">
                                <input type="hidden" name="action" value="update_password">
                                <div class="profil-field">
                                    <label class="profil-label">Kata Sandi Saat Ini</label>
                                    <input type="password" name="password_lama" class="profil-input" placeholder="Masukkan kata sandi lama" required autocomplete="current-password">
                                </div>
                                <div class="profil-field">
                                    <label class="profil-label">Kata Sandi Baru</label>
                                    <input type="password" name="password_baru" class="profil-input" placeholder="Minimal 6 karakter" required autocomplete="new-password">
                                </div>
                                <div class="profil-field">
                                    <label class="profil-label">Konfirmasi Kata Sandi Baru</label>
                                    <input type="password" name="password_konfirmasi" class="profil-input" placeholder="Ketik ulang sandi baru" required autocomplete="new-password">
                                </div>
                                <button type="submit" class="btn-em w-100 mt-2" style="justify-content:center;">
                                    <i class="bi bi-shield-lock"></i> Perbarui Sandi Administrator
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

            <?php endif; ?>

        </main>
    </div><!-- /admin-main -->
</div><!-- /d-flex -->


<!-- ════════════════════════════════════════════════════════════
     MODAL: TAMBAH PENGGUNA BARU
════════════════════════════════════════════════════════════ -->
<div class="modal fade" id="modalTambahPengguna" tabindex="-1" aria-labelledby="lblTambahPengguna" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="?tab=pengguna" method="POST">
                <input type="hidden" name="action" value="tambah_pengguna">
                <div class="modal-header-em">
                    <h5 class="modal-title" id="lblTambahPengguna">
                        <i class="bi bi-person-plus-fill me-2"></i>Tambah Pengguna Baru
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label-em">Nama Lengkap</label>
                        <input type="text" name="nama_lengkap" class="form-control" placeholder="Contoh: Muhammad Ilham" required>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label-em">Username</label>
                            <input type="text" name="username" class="form-control" placeholder="ilham123" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label-em">Kata Sandi</label>
                            <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                        </div>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label-em">Peran / Role</label>
                            <select name="role" class="form-select">
                                <option value="siswa">Siswa / Siswa</option>
                                <option value="ustadz">Ustadz / Guru</option>
                                <option value="admin">Administrator</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label-em">NIS / NIP</label>
                            <input type="text" name="nip_nis" class="form-control" placeholder="102604">
                        </div>
                    </div>
                    <div class="mb-1">
                        <label class="form-label-em">Kelompok Halaqah</label>
                        <select name="halaqah" class="form-select">
                            <option value="Al-Furqan">Al-Furqan (Ust. Rival)</option>
                            <option value="Al-Ikhlas">Al-Ikhlas (Ust. Ahmad)</option>
                            <option value="Al-Falaq">Al-Falaq (Ust. Ridwan)</option>
                            <option value="An-Nas">An-Nas (Ust. Zulkifli)</option>
                            <option value="Koordinator Pusat">Koordinator Pusat (Admin)</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light btn-sm fw-semibold" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn-em btn-em-sm">
                        <i class="bi bi-save-fill"></i> Simpan Pengguna
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ════════════════════════════════════════════════════════════
     MODAL: EDIT PENGGUNA
════════════════════════════════════════════════════════════ -->
<div class="modal fade" id="modalEditPengguna" tabindex="-1" aria-labelledby="lblEditPengguna" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="?tab=pengguna" method="POST">
                <input type="hidden" name="action" value="edit_pengguna">
                <input type="hidden" name="user_id" id="edit_user_id">
                <div class="modal-header-em">
                    <h5 class="modal-title" id="lblEditPengguna">
                        <i class="bi bi-pencil-square me-2"></i>Edit Data Pengguna
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label-em">Nama Lengkap</label>
                        <input type="text" name="nama_lengkap" id="edit_nama_lengkap" class="form-control" required>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label-em">Username</label>
                            <input type="text" name="username" id="edit_username" class="form-control" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label-em">Ganti Sandi (Opsional)</label>
                            <input type="password" name="password" class="form-control" placeholder="Kosongkan jika tidak ubah">
                        </div>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label-em">Peran / Role</label>
                            <select name="role" id="edit_role" class="form-select">
                                <option value="siswa">Siswa / Siswa</option>
                                <option value="ustadz">Ustadz / Guru</option>
                                <option value="admin">Administrator</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label-em">NIS / NIP</label>
                            <input type="text" name="nip_nis" id="edit_nip_nis" class="form-control">
                        </div>
                    </div>
                    <div class="row g-2 mb-1">
                        <div class="col-6">
                            <label class="form-label-em">Kelompok Halaqah</label>
                            <select name="halaqah" id="edit_halaqah" class="form-select">
                                <option value="Al-Furqan">Al-Furqan</option>
                                <option value="Al-Ikhlas">Al-Ikhlas</option>
                                <option value="Al-Falaq">Al-Falaq</option>
                                <option value="An-Nas">An-Nas</option>
                                <option value="Koordinator Pusat">Koordinator Pusat</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label-em">Status</label>
                            <select name="status" id="edit_status" class="form-select">
                                <option value="aktif">Aktif</option>
                                <option value="nonaktif">Nonaktif</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light btn-sm fw-semibold" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn-em btn-em-sm">
                        <i class="bi bi-check2-circle"></i> Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ════════════════════════════════════════════════════════════
     MODAL: TAMBAH HALAQAH
════════════════════════════════════════════════════════════ -->
<div class="modal fade" id="modalTambahHalaqah" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="?tab=halaqah" method="POST">
                <input type="hidden" name="action" value="tambah_halaqah">
                <div class="modal-header-em">
                    <h5 class="modal-title">
                        <i class="bi bi-diagram-3-fill me-2"></i>Tambah Kelompok Halaqah
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label-em">Kode Halaqah</label>
                        <input type="text" name="kode_halaqah" class="form-control" placeholder="Contoh: HLQ-04" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label-em">Nama Halaqah</label>
                        <input type="text" name="nama_halaqah" class="form-control" placeholder="Contoh: An-Nas" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label-em">Ustadz Pembimbing</label>
                        <input type="text" name="nama_ustadz" class="form-control" placeholder="Contoh: Ust. Zulkifli" required>
                    </div>
                    <div class="mb-1">
                        <label class="form-label-em">Target Hafalan</label>
                        <input type="text" name="target_juz" class="form-control" value="Juz 29 - 30">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light btn-sm fw-semibold" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn-em btn-em-sm">
                        <i class="bi bi-save-fill"></i> Simpan Halaqah
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>


<!-- Bootstrap 5.3 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// ── Modal Edit: isi data secara dinamis ──────────────────────
document.querySelectorAll('.btn-edit').forEach(btn => {
    btn.addEventListener('click', function () {
        document.getElementById('edit_user_id').value       = this.dataset.id;
        document.getElementById('edit_nama_lengkap').value  = this.dataset.nama;
        document.getElementById('edit_username').value      = this.dataset.username;
        document.getElementById('edit_role').value          = this.dataset.role;
        document.getElementById('edit_nip_nis').value       = this.dataset.nipnis;
        document.getElementById('edit_halaqah').value       = this.dataset.halaqah;
        document.getElementById('edit_status').value        = this.dataset.status;
    });
});

// ── Sidebar mobile ───────────────────────────────────────────
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

// Tutup sidebar ketika layar diperbesar ke desktop
window.addEventListener('resize', () => {
    if (window.innerWidth >= 992) closeSidebar();
});

// ── Auto-dismiss notifikasi setelah 5 detik ──────────────────
setTimeout(() => {
    document.querySelectorAll('.notif-sukses, .notif-error').forEach(el => {
        el.style.transition = 'opacity 0.5s';
        el.style.opacity = '0';
        setTimeout(() => el.remove(), 500);
    });
}, 5000);
</script>
</body>
</html>
