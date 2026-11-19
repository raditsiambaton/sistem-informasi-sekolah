<?php
/**
 * modules/admin/index.php
 * Dashboard Administrator – SMA IT Al-Fadhilah Pekanbaru
 *
 * Bertanggung jawab atas:
 *   1. Autentikasi & validasi peran
 *   2. Routing tab
 *   3. Pemanggilan handler POST  → actions/post_handler.php
 *   4. Pemanggilan query data    → data/queries.php
 *   5. Layout HTML (sidebar, topbar, notifikasi)
 *   6. Pemanggilan tampilan tab  → tabs/{$page_tab}.php
 *   7. Modal Bootstrap (Tambah/Edit Pengguna, Tambah Halaqah)
 *   8. JavaScript inline
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

// Inisialisasi CSRF Token untuk seluruh operasi form
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

// ─────────────────────────────────────────────────────────────
// ROUTING TAB
// ─────────────────────────────────────────────────────────────
$valid_tabs = ['rekap', 'pengguna', 'halaqah', 'laporan', 'profil', 'sandi'];
$page_tab = $_GET['tab'] ?? 'rekap';
if (!in_array($page_tab, $valid_tabs)) {
    $page_tab = 'rekap';
}

$success_msg = '';
$error_msg = '';

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
    <link
        href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400&display=swap"
        rel="stylesheet">

    <!-- Bootstrap 5.3 & Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../../assets/css/tahfiz.css">

    <style>
        /* ══════════════════════════════════════════════════════
           CSS VARIABEL & RESET DASAR
        ══════════════════════════════════════════════════════ */
        :root {
            --em-900: #064E3B;
            --em-800: #065F46;
            --em-700: #047857;
            --em-600: #059669;
            --em-500: #10B981;
            --em-100: #D1FAE5;
            --em-50: #ECFDF5;
            --gold: #D97706;
            --gold-lt: #FEF3C7;
            --gold-hi: #FACC15;
            --slate-9: #0F172A;
            --slate-7: #334155;
            --slate-5: #64748B;
            --slate-2: #E2E8F0;
            --slate-1: #F1F5F9;
            --white: #FFFFFF;
            --bg-page: #F0FDF4;
            --radius-card: 16px;
            --radius-btn: 10px;
            --shadow-card: 0 2px 10px rgba(6, 78, 59, 0.07);
        }

        *,
        *::before,
        *::after {
            box-sizing: border-box;
        }

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
            top: 0;
            left: 0;
            bottom: 0;
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

        .admin-sidebar::-webkit-scrollbar {
            display: none;
        }

        /* Brand */
        .sb-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 20px 18px 16px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.12);
            flex-shrink: 0;
        }

        .sb-brand-logo {
            width: 42px;
            height: 42px;
            background: #fff;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
            flex-shrink: 0;
        }

        .sb-brand-logo img {
            width: 30px;
            height: 30px;
            object-fit: contain;
        }

        .sb-brand-name {
            font-size: 15px;
            font-weight: 700;
            color: var(--gold-hi);
            line-height: 1.2;
        }

        .sb-brand-sub {
            font-size: 11.5px;
            color: rgba(255, 255, 255, 0.78);
            font-weight: 500;
            margin-top: 2px;
        }

        /* User card */
        .sb-user {
            margin: 14px 12px 8px;
            padding: 10px 12px;
            background: rgba(255, 255, 255, 0.09);
            border: 1px solid rgba(255, 255, 255, 0.14);
            border-radius: 12px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .sb-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: var(--em-700);
            border: 2px solid rgba(255, 255, 255, 0.25);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 13px;
            color: #fff;
            flex-shrink: 0;
        }

        .sb-user-name {
            font-size: 13px;
            font-weight: 600;
            color: #fff;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            line-height: 1.3;
        }

        .sb-user-badge {
            font-size: 11px;
            color: #6EE7B7;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        /* Nav label */
        .sb-label {
            padding: 12px 20px 4px;
            font-size: 10.5px;
            letter-spacing: 0.08em;
            color: rgba(255, 255, 255, 0.5);
            text-transform: uppercase;
            font-weight: 700;
        }

        /* Nav links */
        .sb-link {
            display: flex;
            align-items: center;
            gap: 11px;
            padding: 10px 14px;
            margin: 2px 10px;
            border-radius: 10px;
            font-size: 13.5px;
            font-weight: 500;
            color: rgba(255, 255, 255, 0.82);
            text-decoration: none;
            transition: all 0.18s ease;
            border: 1px solid transparent;
            position: relative;
        }

        .sb-link i {
            font-size: 16px;
            width: 20px;
            text-align: center;
            flex-shrink: 0;
        }

        .sb-link:hover {
            background: rgba(255, 255, 255, 0.10);
            color: #fff;
        }

        .sb-link.active {
            background: rgba(255, 255, 255, 0.16);
            color: #fff;
            font-weight: 600;
            border-color: rgba(255, 255, 255, 0.22);
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.12);
        }

        .sb-link.active::before {
            content: '';
            position: absolute;
            left: -10px;
            top: 50%;
            transform: translateY(-50%);
            width: 3px;
            height: 24px;
            background: var(--gold-hi);
            border-radius: 0 3px 3px 0;
        }

        .sb-link-danger {
            color: rgba(252, 165, 165, 0.90);
        }

        .sb-link-danger:hover {
            background: rgba(239, 68, 68, 0.15);
            color: #FCA5A5;
        }

        /* Sidebar footer */
        .sb-footer {
            margin-top: auto;
            padding: 14px 10px 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.12);
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .sb-logout {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            height: 42px;
            background: rgba(239, 68, 68, 0.15);
            border: 1px solid rgba(239, 68, 68, 0.3);
            border-radius: 10px;
            color: #FCA5A5;
            font-size: 13.5px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s;
        }

        .sb-logout:hover {
            background: #DC2626;
            border-color: #DC2626;
            color: #fff;
        }

        /* Hamburger overlay (mobile) */
        .sb-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.5);
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
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 1px 3px rgba(6, 78, 59, 0.05);
            position: sticky;
            top: 0;
            z-index: 100;
            flex-shrink: 0;
        }

        .topbar-left {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .topbar-logo {
            height: 28px;
            width: auto;
            object-fit: contain;
        }

        .topbar-sep {
            color: var(--slate-2);
            font-size: 18px;
        }

        .topbar-breadcrumb {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 13.5px;
            color: var(--slate-5);
        }

        .topbar-breadcrumb .crumb-active {
            color: var(--slate-9);
            font-weight: 600;
        }

        .topbar-right {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .topbar-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12.5px;
            font-weight: 600;
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
            background: none;
            border: none;
            padding: 6px;
            cursor: pointer;
            color: var(--slate-7);
            font-size: 22px;
            border-radius: 8px;
        }

        .btn-ham:hover {
            background: var(--slate-1);
        }

        /* ══════════════════════════════════════════════════════
           MAIN CONTENT AREA
        ══════════════════════════════════════════════════════ */
        .content-area {
            padding: 28px;
            flex: 1;
        }

        /* Page heading */
        .page-heading {
            margin-bottom: 24px;
        }

        .page-heading h1 {
            font-size: 22px;
            font-weight: 700;
            color: var(--em-800);
            margin: 0 0 4px;
        }

        .page-heading p {
            font-size: 13.5px;
            color: var(--slate-5);
            margin: 0;
        }

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

        .kpi-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(6, 78, 59, 0.10);
        }

        .kpi-icon {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            flex-shrink: 0;
        }

        .kpi-icon-green {
            background: var(--em-50);
            color: var(--em-600);
        }

        .kpi-icon-yellow {
            background: var(--gold-lt);
            color: var(--gold);
        }

        .kpi-icon-blue {
            background: #EFF6FF;
            color: #3B82F6;
        }

        .kpi-icon-teal {
            background: #F0FDFA;
            color: #0D9488;
        }

        .kpi-num {
            font-size: 30px;
            font-weight: 800;
            color: var(--slate-9);
            line-height: 1;
            margin: 10px 0 4px;
        }

        .kpi-label {
            font-size: 11.5px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: var(--slate-5);
        }

        .kpi-sub {
            font-size: 12.5px;
            color: var(--slate-5);
            margin-top: 2px;
        }

        /* ══════════════════════════════════════════════════════
           CHART BARS
        ══════════════════════════════════════════════════════ */
        .chart-bar-track {
            width: 36px;
            height: 130px;
            background: var(--em-50);
            border-radius: 8px 8px 0 0;
            display: flex;
            align-items: flex-end;
            overflow: hidden;
            margin: 0 auto;
        }

        .chart-bar-fill {
            width: 100%;
            background: linear-gradient(180deg, var(--em-500) 0%, var(--em-700) 100%);
            border-radius: 8px 8px 0 0;
            transition: height 0.6s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        .chart-bar-fill:hover {
            filter: brightness(1.1);
        }

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
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .panel-title {
            font-size: 15px;
            font-weight: 700;
            color: var(--em-800);
            margin: 0;
        }

        /* ══════════════════════════════════════════════════════
           TABLES
        ══════════════════════════════════════════════════════ */
        .tbl {
            width: 100%;
            border-collapse: collapse;
        }

        .tbl thead th {
            background: var(--em-50);
            color: var(--em-800);
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
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

        .tbl tbody tr:last-child td {
            border-bottom: none;
        }

        .tbl tbody tr:hover {
            background: var(--em-50);
        }

        /* ══════════════════════════════════════════════════════
           BADGES
        ══════════════════════════════════════════════════════ */
        .badge-role-admin {
            background: #EDE9FE;
            color: #6D28D9;
            border: 1px solid #DDD6FE;
        }

        .badge-role-ustadz {
            background: #EFF6FF;
            color: #1D4ED8;
            border: 1px solid #BFDBFE;
        }

        .badge-role-siswa {
            background: var(--em-50);
            color: var(--em-700);
            border: 1px solid var(--em-100);
        }

        .badge-aktif {
            background: var(--em-50);
            color: var(--em-700);
            border: 1px solid var(--em-100);
        }

        .badge-nonaktif {
            background: #FEF3C7;
            color: #B45309;
            border: 1px solid #FDE68A;
        }

        .badge-pill {
            display: inline-flex;
            align-items: center;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 11.5px;
            font-weight: 600;
        }

        /* ══════════════════════════════════════════════════════
           BUTTONS
        ══════════════════════════════════════════════════════ */
        .btn-em {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 8px 16px;
            background: var(--em-700);
            color: #fff;
            border: none;
            border-radius: var(--radius-btn);
            font-size: 13.5px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s;
        }

        .btn-em:hover {
            background: var(--em-800);
            color: #fff;
            box-shadow: 0 4px 12px rgba(6, 78, 59, 0.3);
        }

        .btn-em-sm {
            padding: 5px 12px;
            font-size: 12.5px;
        }

        .btn-outline-em {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 5px 12px;
            background: transparent;
            border: 1.5px solid var(--em-600);
            color: var(--em-700);
            border-radius: var(--radius-btn);
            font-size: 12.5px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s;
        }

        .btn-outline-em:hover {
            background: var(--em-700);
            color: #fff;
        }

        .btn-outline-danger-sm {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 5px 10px;
            background: transparent;
            border: 1.5px solid #FCA5A5;
            color: #DC2626;
            border-radius: var(--radius-btn);
            font-size: 12.5px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s;
        }

        .btn-outline-danger-sm:hover {
            background: #DC2626;
            color: #fff;
            border-color: #DC2626;
        }

        /* ══════════════════════════════════════════════════════
           MODALS — override Bootstrap
        ══════════════════════════════════════════════════════ */
        .modal-content {
            border-radius: 16px;
            border: none;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.15);
        }

        .modal-header-em {
            background: linear-gradient(135deg, var(--em-800), var(--em-700));
            padding: 18px 24px;
            border-radius: 16px 16px 0 0;
        }

        .modal-header-em .modal-title {
            color: #fff;
            font-weight: 700;
            font-size: 16px;
        }

        .modal-header-em .btn-close {
            filter: invert(1) brightness(2);
        }

        .modal-body {
            padding: 24px;
        }

        .modal-footer {
            padding: 14px 24px 20px;
            border-top: 1px solid var(--slate-2);
        }

        .form-label-em {
            font-size: 12.5px;
            font-weight: 600;
            color: var(--slate-7);
            margin-bottom: 5px;
            display: block;
        }

        .form-control,
        .form-select {
            border-color: var(--slate-2);
            border-radius: 8px;
            font-size: 13.5px;
            padding: 8px 12px;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: var(--em-600);
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.15);
        }

        /* ══════════════════════════════════════════════════════
           PROFIL & SANDI
        ══════════════════════════════════════════════════════ */
        .profil-wrap {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            align-items: start;
        }

        .profil-card {
            background: #fff;
            border-radius: var(--radius-card);
            padding: 28px;
            border: 1px solid var(--slate-2);
            box-shadow: var(--shadow-card);
        }

        .profil-card-title {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 16px;
            font-weight: 700;
            color: var(--em-800);
            margin: 0 0 20px;
            padding-bottom: 14px;
            border-bottom: 1px solid var(--slate-2);
        }

        .profil-card-title .ic {
            font-size: 20px;
            color: var(--em-600);
        }

        .profil-field {
            margin-bottom: 16px;
        }

        .profil-label {
            display: block;
            font-size: 12.5px;
            font-weight: 600;
            color: var(--slate-7);
            margin-bottom: 5px;
        }

        .profil-input {
            width: 100%;
            height: 42px;
            border: 1px solid var(--slate-2);
            border-radius: 8px;
            padding: 0 12px;
            font-size: 13.5px;
            color: var(--slate-9);
            background: var(--slate-1);
            transition: all 0.2s;
        }

        .profil-input:focus {
            outline: none;
            border-color: var(--em-600);
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.15);
            background: #fff;
        }

        .profil-input[readonly] {
            background: #F8FAFC;
            color: var(--slate-5);
            cursor: default;
        }

        .profil-hint {
            font-size: 11px;
            color: #94A3B8;
            margin-top: 3px;
            display: block;
        }

        /* Alert box info kecil */
        .info-box {
            background: var(--gold-lt);
            border: 1px solid #FDE68A;
            border-radius: 10px;
            padding: 12px 14px;
            font-size: 12.5px;
            color: #92400E;
            display: flex;
            align-items: flex-start;
            gap: 8px;
            margin-bottom: 16px;
        }

        /* ══════════════════════════════════════════════════════
           ALERT NOTIFIKASI
        ══════════════════════════════════════════════════════ */
        .notif-sukses,
        .notif-error {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 16px;
            border-radius: 12px;
            font-size: 13.5px;
            font-weight: 500;
            margin-bottom: 20px;
            animation: slideDown 0.3s ease;
        }

        .notif-sukses {
            background: #D1FAE5;
            border: 1px solid #6EE7B7;
            color: #065F46;
        }

        .notif-error {
            background: #FEE2E2;
            border: 1px solid #FCA5A5;
            color: #991B1B;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-8px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

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
            display: flex;
            flex-direction: column;
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

            .btn-ham {
                display: flex;
            }

            .topbar {
                padding: 0 16px;
            }

            .content-area {
                padding: 16px;
            }

            .profil-wrap {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 575.98px) {
            .topbar-date {
                display: none;
            }

            .kpi-num {
                font-size: 26px;
            }
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

                <!-- Tampilan Tab -->
                <?php require_once __DIR__ . '/tabs/' . $page_tab . '.php'; ?>

            </main>
        </div><!-- /admin-main -->
    </div><!-- /d-flex -->


    <!-- ════════════════════════════════════════════════════════════
     MODAL: TAMBAH PENGGUNA BARU
════════════════════════════════════════════════════════════ -->
    <div class="modal fade" id="modalTambahPengguna" tabindex="-1" aria-labelledby="lblTambahPengguna"
        aria-hidden="true">
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
                            <input type="text" name="nama_lengkap" class="form-control"
                                placeholder="Contoh: Muhammad Ilham" required>
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label-em">Username</label>
                                <input type="text" name="username" class="form-control" placeholder="ilham123" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label-em">Kata Sandi</label>
                                <input type="password" name="password" class="form-control" placeholder="••••••••"
                                    required>
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
                        <button type="button" class="btn btn-light btn-sm fw-semibold"
                            data-bs-dismiss="modal">Batal</button>
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
                                <input type="password" name="password" class="form-control"
                                    placeholder="Kosongkan jika tidak ubah">
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
                        <button type="button" class="btn btn-light btn-sm fw-semibold"
                            data-bs-dismiss="modal">Batal</button>
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
    <div class="modal fade" id="modalTambahHalaqah" tabindex="-1" aria-labelledby="lblTambahHalaqah" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form action="?tab=halaqah" method="POST">
                    <input type="hidden" name="action" value="tambah_halaqah">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
                    <div class="modal-header-em">
                        <h5 class="modal-title" id="lblTambahHalaqah">
                            <i class="bi bi-diagram-3-fill me-2"></i>Tambah Kelompok Halaqah
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label-em">Kode Halaqah</label>
                            <input type="text" name="kode_halaqah" class="form-control" placeholder="Contoh: HLQ-04"
                                required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label-em">Nama Halaqah</label>
                            <input type="text" name="nama_halaqah" class="form-control" placeholder="Contoh: An-Nas"
                                required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label-em">Ustadz Pembimbing</label>
                            <select name="ustadz_id" class="form-select" required>
                                <option value="">-- Pilih Ustadz Pembimbing --</option>
                                <?php foreach ($ustadz_list as $u): ?>
                                    <option value="<?= $u['id'] ?>">
                                        <?= htmlspecialchars($u['nama_lengkap']) ?> (NIP:
                                        <?= htmlspecialchars($u['nip_nis'] ?? '—') ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label-em">Target Hafalan</label>
                            <input type="text" name="target_juz" class="form-control" value="Juz 29 - 30">
                        </div>
                        <div class="mb-2">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label-em mb-0">Anggota Siswa</label>
                                <span class="badge bg-success-subtle text-success border counter-tambah-siswa"
                                    style="font-size:11px;">0 siswa dipilih</span>
                            </div>
                            <div class="input-group input-group-sm mb-2">
                                <span class="input-group-text bg-white border-end-0"><i
                                        class="bi bi-search text-muted"></i></span>
                                <input type="text" class="form-control border-start-0 search-siswa-tambah"
                                    placeholder="Cari nama atau NIS siswa...">
                            </div>
                            <div class="border rounded-3 p-2 list-siswa-tambah-container"
                                style="max-height: 200px; overflow-y: auto; background: var(--slate-1);">
                                <?php if (!empty($siswa_list)): ?>
                                    <?php foreach ($siswa_list as $s): ?>
                                        <label
                                            class="d-flex align-items-center justify-content-between p-2 rounded-2 mb-1 bg-white border item-siswa-tambah"
                                            data-nama="<?= strtolower(htmlspecialchars($s['nama_lengkap'])) ?>"
                                            data-nis="<?= strtolower(htmlspecialchars($s['nip_nis'] ?? '')) ?>"
                                            style="cursor: pointer;">
                                            <div class="d-flex align-items-center gap-2">
                                                <input type="checkbox" name="siswa_ids[]" value="<?= $s['id'] ?>"
                                                    class="form-check-input check-siswa-tambah mt-0">
                                                <div>
                                                    <div class="fw-semibold text-dark" style="font-size: 13px;">
                                                        <?= htmlspecialchars($s['nama_lengkap']) ?></div>
                                                    <small class="text-muted" style="font-size: 11.5px;">NIS:
                                                        <?= htmlspecialchars($s['nip_nis'] ?? '—') ?></small>
                                                </div>
                                            </div>
                                            <?php if (!empty($s['halaqah']) && $s['halaqah'] !== '—'): ?>
                                                <span class="badge bg-light text-muted border" style="font-size: 11px;">Halaqah:
                                                    <?= htmlspecialchars($s['halaqah']) ?></span>
                                            <?php else: ?>
                                                <span class="badge bg-warning-subtle text-warning border"
                                                    style="font-size: 11px;">Belum Ada Halaqah</span>
                                            <?php endif; ?>
                                        </label>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="text-center py-3 text-muted small">Tidak ada data siswa aktif.</div>
                                <?php endif; ?>
                            </div>
                            <small class="text-muted d-block mt-1" style="font-size:11px;">* Siswa yang dicentang akan
                                dimasukkan ke kelompok ini.</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light btn-sm fw-semibold"
                            data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn-em btn-em-sm">
                            <i class="bi bi-save-fill"></i> Simpan Halaqah
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ════════════════════════════════════════════════════════════
     MODAL: EDIT HALAQAH
════════════════════════════════════════════════════════════ -->
    <div class="modal fade" id="modalEditHalaqah" tabindex="-1" aria-labelledby="lblEditHalaqah" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form action="?tab=halaqah" method="POST">
                    <input type="hidden" name="action" value="edit_halaqah">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
                    <input type="hidden" name="halaqah_id" id="edit_halaqah_id">
                    <div class="modal-header-em">
                        <h5 class="modal-title" id="lblEditHalaqah">
                            <i class="bi bi-pencil-square me-2"></i>Edit Kelompok Halaqah
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label-em">Kode Halaqah</label>
                            <input type="text" name="kode_halaqah" id="edit_kode_halaqah" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label-em">Nama Halaqah</label>
                            <input type="text" name="nama_halaqah" id="edit_nama_halaqah" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label-em">Ustadz Pembimbing</label>
                            <select name="ustadz_id" id="edit_ustadz_id" class="form-select" required>
                                <option value="">-- Pilih Ustadz Pembimbing --</option>
                                <?php foreach ($ustadz_list as $u): ?>
                                    <option value="<?= $u['id'] ?>">
                                        <?= htmlspecialchars($u['nama_lengkap']) ?> (NIP:
                                        <?= htmlspecialchars($u['nip_nis'] ?? '—') ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label-em">Target Hafalan</label>
                            <input type="text" name="target_juz" id="edit_target_juz" class="form-control"
                                value="Juz 29 - 30">
                        </div>
                        <div class="mb-2">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label-em mb-0">Anggota Siswa</label>
                                <span class="badge bg-success-subtle text-success border counter-edit-siswa"
                                    style="font-size:11px;">0 siswa dipilih</span>
                            </div>
                            <div class="input-group input-group-sm mb-2">
                                <span class="input-group-text bg-white border-end-0"><i
                                        class="bi bi-search text-muted"></i></span>
                                <input type="text" class="form-control border-start-0 search-siswa-edit"
                                    placeholder="Cari nama atau NIS siswa...">
                            </div>
                            <div class="border rounded-3 p-2 list-siswa-edit-container"
                                style="max-height: 200px; overflow-y: auto; background: var(--slate-1);">
                                <?php if (!empty($siswa_list)): ?>
                                    <?php foreach ($siswa_list as $s): ?>
                                        <label
                                            class="d-flex align-items-center justify-content-between p-2 rounded-2 mb-1 bg-white border item-siswa-edit"
                                            data-nama="<?= strtolower(htmlspecialchars($s['nama_lengkap'])) ?>"
                                            data-nis="<?= strtolower(htmlspecialchars($s['nip_nis'] ?? '')) ?>"
                                            style="cursor: pointer;">
                                            <div class="d-flex align-items-center gap-2">
                                                <input type="checkbox" name="siswa_ids[]" value="<?= $s['id'] ?>"
                                                    class="form-check-input check-siswa-edit mt-0">
                                                <div>
                                                    <div class="fw-semibold text-dark" style="font-size: 13px;">
                                                        <?= htmlspecialchars($s['nama_lengkap']) ?></div>
                                                    <small class="text-muted" style="font-size: 11.5px;">NIS:
                                                        <?= htmlspecialchars($s['nip_nis'] ?? '—') ?></small>
                                                </div>
                                            </div>
                                            <?php if (!empty($s['halaqah']) && $s['halaqah'] !== '—'): ?>
                                                <span class="badge bg-light text-muted border" style="font-size: 11px;">Halaqah:
                                                    <?= htmlspecialchars($s['halaqah']) ?></span>
                                            <?php else: ?>
                                                <span class="badge bg-warning-subtle text-warning border"
                                                    style="font-size: 11px;">Belum Ada Halaqah</span>
                                            <?php endif; ?>
                                        </label>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="text-center py-3 text-muted small">Tidak ada data siswa aktif.</div>
                                <?php endif; ?>
                            </div>
                            <small class="text-muted d-block mt-1" style="font-size:11px;">* Centang untuk menambahkan,
                                hilangkan centang untuk mengeluarkan siswa dari kelompok ini.</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light btn-sm fw-semibold"
                            data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn-em btn-em-sm">
                            <i class="bi bi-check2-circle"></i> Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    <!-- Bootstrap 5.3 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // ── Modal Edit Pengguna: isi data secara dinamis ──────────────
        document.querySelectorAll('.btn-edit').forEach(btn => {
            btn.addEventListener('click', function () {
                document.getElementById('edit_user_id').value = this.dataset.id;
                document.getElementById('edit_nama_lengkap').value = this.dataset.nama;
                document.getElementById('edit_username').value = this.dataset.username;
                document.getElementById('edit_role').value = this.dataset.role;
                document.getElementById('edit_nip_nis').value = this.dataset.nipnis;
                document.getElementById('edit_halaqah').value = this.dataset.halaqah;
                document.getElementById('edit_status').value = this.dataset.status;
            });
        });

        // ── UC-05: Kelola Halaqah — Counter & Search Interaksi ────────
        function updateCounterTambahHalaqah() {
            const total = document.querySelectorAll('.check-siswa-tambah:checked').length;
            const badge = document.querySelector('.counter-tambah-siswa');
            if (badge) badge.textContent = total + ' siswa dipilih';
        }

        function updateCounterEditHalaqah() {
            const total = document.querySelectorAll('.check-siswa-edit:checked').length;
            const badge = document.querySelector('.counter-edit-siswa');
            if (badge) badge.textContent = total + ' siswa dipilih';
        }

        // Event change pada checkbox tambah
        document.querySelectorAll('.check-siswa-tambah').forEach(cb => {
            cb.addEventListener('change', updateCounterTambahHalaqah);
        });

        // Event change pada checkbox edit
        document.querySelectorAll('.check-siswa-edit').forEach(cb => {
            cb.addEventListener('change', updateCounterEditHalaqah);
        });

        // Filter pencarian siswa di modal tambah
        const searchSiswaTambah = document.querySelector('.search-siswa-tambah');
        if (searchSiswaTambah) {
            searchSiswaTambah.addEventListener('input', function () {
                const q = this.value.toLowerCase().trim();
                document.querySelectorAll('.item-siswa-tambah').forEach(item => {
                    const nama = item.dataset.nama || '';
                    const nis = item.dataset.nis || '';
                    item.style.display = (nama.includes(q) || nis.includes(q)) ? 'flex' : 'none';
                });
            });
        }

        // Filter pencarian siswa di modal edit
        const searchSiswaEdit = document.querySelector('.search-siswa-edit');
        if (searchSiswaEdit) {
            searchSiswaEdit.addEventListener('input', function () {
                const q = this.value.toLowerCase().trim();
                document.querySelectorAll('.item-siswa-edit').forEach(item => {
                    const nama = item.dataset.nama || '';
                    const nis = item.dataset.nis || '';
                    item.style.display = (nama.includes(q) || nis.includes(q)) ? 'flex' : 'none';
                });
            });
        }

        // ── Modal Edit Halaqah: Populasikan data dan centang ID siswa ───
        document.querySelectorAll('.btn-edit-halaqah').forEach(btn => {
            btn.addEventListener('click', function () {
                document.getElementById('edit_halaqah_id').value = this.dataset.id;
                document.getElementById('edit_kode_halaqah').value = this.dataset.kode;
                document.getElementById('edit_nama_halaqah').value = this.dataset.nama;
                document.getElementById('edit_ustadz_id').value = this.dataset.ustadzId;
                document.getElementById('edit_target_juz').value = this.dataset.target;

                let memberIds = [];
                try {
                    memberIds = JSON.parse(this.dataset.memberIds || '[]');
                } catch (e) {
                    memberIds = [];
                }

                // Tandai checkbox murni mengacu pada ID siswa
                document.querySelectorAll('.check-siswa-edit').forEach(cb => {
                    const sId = parseInt(cb.value, 10);
                    cb.checked = memberIds.includes(sId);
                });

                // Kembalikan filter pencarian ke semula jika sebelumnya diisi
                if (searchSiswaEdit) searchSiswaEdit.value = '';
                document.querySelectorAll('.item-siswa-edit').forEach(item => {
                    item.style.display = 'flex';
                });

                updateCounterEditHalaqah();
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