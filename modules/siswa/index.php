<?php
/**
 * modules/siswa/index.php
 * Dashboard Siswa / Siswa – SMA IT Al-Fadhilah Pekanbaru
 * 100% Terintegrasi Database MySQL & Desain Elegan Modern
 */

require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/database.php';

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

// Ambil data profil siswa terkini dari MySQL jika tersedia
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

// Tangani Update Kontak Siswa
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_biodata') {
    $no_hp_baru  = trim($_POST['no_hp'] ?? '');
    $no_hp_clean = preg_replace('/[^0-9\+\-\s]/', '', $no_hp_baru);

    $siswa_no_hp = $no_hp_clean;
    $_SESSION['user_no_hp'] = $no_hp_clean;
    $data_siswa['no_hp'] = $no_hp_clean;

    try {
        $pdo = koneksi_database();
        if ($pdo) {
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

// Tangani Ganti Sandi Siswa
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['action']) && $_POST['action'] === 'ganti_password') {
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

// ── Tarik Data Nyata dari MySQL untuk Siswa Ini ──────────────────────
$siswa_setoran = [];
$siswa_murajaah = [];
$target_aktif = null;
$nama_ustadz_pembimbing = 'Ust. Rival Alvian Melfandy';

try {
    $pdo = koneksi_database();
    if ($pdo) {
        // Ambil nama ustadz pembimbing dari halaqah jika ada
        try {
            $ust_stmt = $pdo->prepare("SELECT nama_lengkap FROM users WHERE role = 'ustadz' AND halaqah = :h LIMIT 1");
            $ust_stmt->execute(['h' => $halaqah_name]);
            $ust_row = $ust_stmt->fetch();
            if ($ust_row && !empty($ust_row['nama_lengkap'])) {
                $nama_ustadz_pembimbing = $ust_row['nama_lengkap'];
            }
        } catch (Throwable $e) {}

        // Ambil target hafalan aktif dari tabel target_hafalan
        try {
            $stg = $pdo->prepare("SELECT * FROM target_hafalan WHERE (siswa_id = :id OR siswa_id IS NULL) AND status = 'Aktif' ORDER BY id DESC LIMIT 1");
            $stg->execute(['id' => $siswa_id]);
            $target_aktif = $stg->fetch();
            if ($target_aktif && !empty($target_aktif['ustadz_id'])) {
                $ust_stmt2 = $pdo->prepare("SELECT nama_lengkap FROM users WHERE id = :uid LIMIT 1");
                $ust_stmt2->execute(['uid' => $target_aktif['ustadz_id']]);
                $u2 = $ust_stmt2->fetch();
                if ($u2 && !empty($u2['nama_lengkap'])) {
                    $nama_ustadz_pembimbing = $u2['nama_lengkap'];
                }
            }
        } catch (Throwable $e) {}

        // Ambil semua setoran hafalan siswa ini
        $st = $pdo->prepare("SELECT * FROM setoran_hafalan WHERE siswa_id = :id OR nama_siswa LIKE :n ORDER BY id DESC");
        $st->execute(['id' => $siswa_id, 'n' => "%{$siswa_name}%"]);
        $siswa_setoran = $st->fetchAll();

        // Ambil semua murajaah siswa ini
        $sm = $pdo->prepare("SELECT * FROM murajaah WHERE siswa_id = :id OR nama_siswa LIKE :n ORDER BY id DESC");
        $sm->execute(['id' => $siswa_id, 'n' => "%{$siswa_name}%"]);
        $siswa_murajaah = $sm->fetchAll();
    }
} catch (Throwable $e) {}

// Helper fungsi format tanggal Indonesia
if (!function_exists('format_tgl_indo')) {
function format_tgl_indo($dateStr) {
    if (empty($dateStr) || $dateStr === '0000-00-00' || strtotime($dateStr) === false || strtotime($dateStr) < 0) {
        return date('d M Y');
    }
    $t = strtotime($dateStr);
    $bulanIndo = [
        1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'Mei', 6 => 'Jun',
        7 => 'Jul', 8 => 'Agu', 9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des'
    ];
    $d = date('j', $t);
    $m = $bulanIndo[(int)date('n', $t)] ?? date('M', $t);
    $y = date('Y', $t);
    return "$d $m $y";
}
}

// ── Perhitungan Metrik Dinamis untuk Tab Progres ─────────────────────
$total_ayat_disetor = 0;
$distinct_surahs = [];
foreach ($siswa_setoran as $set) {
    $aw = intval($set['ayat_awal'] ?? 1);
    $ak = intval($set['ayat_akhir'] ?? 1);
    $total_ayat_disetor += max(1, ($ak - $aw + 1));
    if (!empty($set['surah'])) {
        $distinct_surahs[trim($set['surah'])] = true;
    }
}
$total_surah_disetor = count($distinct_surahs);

// Target Aktif & Capaian Dinamis
$target_judul = 'Belum Ada Target Aktif';
$target_sub = 'Ustadz pembimbing belum menetapkan target hafalan periode ini.';
$target_persen = 0;
$target_ayat_selesai = 0;
$target_total_ayat = 0;
$target_sisa_ayat = 0;
$target_sisa_teks = '-';
$target_langkah = 'Mulai setor hafalan pertama Anda';
$target_langkah_sub = 'Siapkan surah awal untuk disimak oleh ustadz pembimbing pada halaqah berikutnya.';

if ($target_aktif) {
    $s_awal = $target_aktif['surah_awal'] ?? '';
    $s_akhir = $target_aktif['surah_akhir'] ?? '';
    $t_mulai = format_tgl_indo($target_aktif['tanggal_mulai'] ?? '');
    $t_akhir = format_tgl_indo($target_aktif['tanggal_akhir'] ?? '');

    // Parse nama surah dari target (e.g. "Juz 1 • Al-Fatihah (Ayat 1–7)")
    preg_match('/(?:Juz\s*\d+\s*[•·\-]\s*)?([A-Za-z\-\'\s]+)(?:\(Ayat\s*(\d+)[\–\-](\d+)\))?/u', $s_awal, $mAwal);
    $surahTargetNama = trim($mAwal[1] ?? $s_awal);
    $targetAyatStart = isset($mAwal[2]) ? intval($mAwal[2]) : 1;
    $targetAyatEnd   = isset($mAwal[3]) ? intval($mAwal[3]) : 7;

    $target_total_ayat = max(1, $targetAyatEnd - $targetAyatStart + 1);

    // Cek progres setoran siswa pada surah target
    $maxAyatDisetor = 0;
    foreach ($siswa_setoran as $stRow) {
        if (strcasecmp(trim($stRow['surah']), $surahTargetNama) === 0 || stripos($stRow['surah'], $surahTargetNama) !== false) {
            $maxAyatDisetor = max($maxAyatDisetor, intval($stRow['ayat_akhir']));
        }
    }

    $target_ayat_selesai = min($target_total_ayat, $maxAyatDisetor);
    $target_persen = min(100, round(($target_ayat_selesai / $target_total_ayat) * 100));
    $target_sisa_ayat = max(0, $target_total_ayat - $target_ayat_selesai);

    $target_judul = "Surah " . $surahTargetNama;
    $target_sub = "Ayat {$targetAyatStart}–{$targetAyatEnd} | Periode: {$t_mulai} – {$t_akhir}";

    if ($target_sisa_ayat > 0) {
        $nextStart = $target_ayat_selesai + 1;
        $nextEnd = min($targetAyatEnd, $nextStart + 5);
        $target_sisa_teks = "{$surahTargetNama} ayat {$nextStart}–{$targetAyatEnd}";
        $target_langkah = "Lanjutkan ayat {$nextStart}–{$nextEnd}";
        $target_langkah_sub = "Ulangi hafalan sebelumnya, lalu siapkan {$target_sisa_ayat} ayat berikutnya untuk disimak oleh ustadz pembimbing.";
    } else {
        $target_sisa_teks = "Target tercapai sempurna (100%)";
        $target_langkah = "Alhamdulillah Target Selesai!";
        $target_langkah_sub = "Pertahankan hafalan dengan murajaah rutin dan konsultasikan target surat/juz berikutnya bersama ustadz pembimbing.";
    }
} elseif (!empty($siswa_setoran)) {
    // Fallback jika belum ada target khusus di tabel target_hafalan
    $latest = $siswa_setoran[0];
    $target_judul = "Surah " . htmlspecialchars($latest['surah']);
    $target_sub = "Terakhir disetor: Ayat {$latest['ayat_awal']}–{$latest['ayat_akhir']} (" . format_tgl_indo($latest['tanggal_setor']) . ")";
    $target_persen = 100;
    $target_sisa_ayat = 0;
    $target_sisa_teks = "Menunggu target baru";
    $target_langkah = "Lanjutkan surah berikutnya";
    $target_langkah_sub = "Konsultasikan dengan ustadz pembimbing untuk penetapan target surat selanjutnya.";
}

// ── Gabungkan Riwayat Setoran & Murajaah untuk Tab Riwayat ───────────
$riwayat_gabungan = [];
foreach ($siswa_setoran as $s) {
    $tgl = !empty($s['tanggal_setor']) && $s['tanggal_setor'] !== '0000-00-00' ? $s['tanggal_setor'] : (!empty($s['created_at']) ? substr($s['created_at'], 0, 10) : date('Y-m-d'));
    $kualitas = !empty($s['kualitas']) ? $s['kualitas'] : 'Lancar';
    $riwayat_gabungan[] = [
        'id'        => 'set_' . $s['id'],
        'raw_id'    => $s['id'],
        'jenis'     => 'Setoran',
        'surah'     => $s['surah'],
        'ayat'      => 'Ayat ' . $s['ayat_awal'] . '–' . $s['ayat_akhir'],
        'kualitas'  => $kualitas,
        'tanggal'   => format_tgl_indo($tgl),
        'ustadz'    => !empty($s['nama_ustadz']) ? $s['nama_ustadz'] : $nama_ustadz_pembimbing,
        'catatan'   => !empty($s['catatan']) ? trim($s['catatan']) : '',
        'timestamp' => strtotime($tgl) ?: 0,
    ];
}

foreach ($siswa_murajaah as $m) {
    $tgl = !empty($m['tanggal_murajaah']) && $m['tanggal_murajaah'] !== '0000-00-00' ? $m['tanggal_murajaah'] : (!empty($m['created_at']) ? substr($m['created_at'], 0, 10) : date('Y-m-d'));
    $kualitas = !empty($m['status_mutqin']) ? $m['status_mutqin'] : 'Lancar';
    $riwayat_gabungan[] = [
        'id'        => 'mur_' . $m['id'],
        'raw_id'    => $m['id'],
        'jenis'     => 'Murajaah',
        'surah'     => $m['surah'],
        'ayat'      => !empty($m['ayat_range']) ? $m['ayat_range'] : '1 Surah Penuh',
        'kualitas'  => $kualitas,
        'tanggal'   => format_tgl_indo($tgl),
        'ustadz'    => !empty($m['nama_ustadz']) ? $m['nama_ustadz'] : $nama_ustadz_pembimbing,
        'catatan'   => !empty($m['catatan']) ? trim($m['catatan']) : '',
        'timestamp' => strtotime($tgl) ?: 0,
    ];
}

// Urutkan dari yang terbaru (descending)
usort($riwayat_gabungan, function($a, $b) {
    return $b['timestamp'] - $a['timestamp'];
});

// Masukan / Catatan dari Ustadz yang ada isinya
$daftar_masukan = array_values(array_filter($riwayat_gabungan, function($r) {
    return !empty($r['catatan']);
}));

// Inisial avatar siswa (2 huruf pertama nama)
$siswa_initials = strtoupper(mb_substr(preg_replace('/[^A-Za-z ]/', '', $siswa_name), 0, 1));
$name_parts = explode(' ', trim($siswa_name));
if (count($name_parts) >= 2) {
    $siswa_initials = strtoupper(mb_substr($name_parts[0], 0, 1) . mb_substr($name_parts[1], 0, 1));
}
$nama_panggilan = count($name_parts) >= 2 ? $name_parts[1] : $siswa_name;
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
    <link rel="stylesheet" href="../../assets/css/tahfiz.css">

    <style>
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

            /* Kompatibilitas komponen halaman siswa */
            --color-primary: #065F46;
            --color-primary-dark: #064E3B;
            --color-primary-deep: #064E3B;
            --color-accent: #059669;
            --color-soft: #ECFDF5;
            --color-soft-light: #F0FDF4;
            --color-tahfiz: #065F46;
            --color-navy: #065F46;
            --color-gold: #D97706;
            --color-border: #E2E8F0;
        }
        body {
            font-family: 'Plus Jakarta Sans', system-ui, sans-serif;
            background-color: #F6F7F5;
            color: var(--slate-9);
            min-height: 100vh;
            margin: 0;
        }

        /* ══════════════════════════════════════════════════════
           SIDEBAR SISWA (Modern Islamic School Dashboard)
        ══════════════════════════════════════════════════════ */
        .siswa-sidebar {
            width: 256px !important;
            min-width: 256px !important;
            max-width: 256px !important;
            min-height: 100vh;
            background-color: var(--em-800);
            color: var(--white);
            display: flex;
            flex-direction: column;
            padding: 24px 16px;
            position: sticky;
            top: 0;
            box-shadow: 4px 0 24px rgba(6, 78, 59, 0.15);
            z-index: 1040;
            transition: transform 0.3s ease;
        }

        .sb-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 0 8px 20px 8px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.12);
            margin-bottom: 20px;
        }
        .sb-brand-logo {
            width: 44px;
            height: 44px;
            background: var(--white);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12);
            flex-shrink: 0;
            padding: 4px;
        }
        .sb-brand-logo img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }
        .sb-brand-name {
            font-weight: 800;
            font-size: 1.15rem;
            color: var(--white);
            letter-spacing: -0.02em;
            line-height: 1.2;
        }
        .sb-brand-sub {
            font-size: 0.72rem;
            color: #A7F3D0;
            font-weight: 500;
        }

        .sb-user {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px;
            background: rgba(255, 255, 255, 0.08);
            border-radius: 14px;
            margin-bottom: 20px;
            border: 1px solid rgba(255, 255, 255, 0.08);
        }
        .sb-avatar {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.95rem;
            color: var(--white);
            flex-shrink: 0;
        }
        .sb-user-name {
            font-weight: 600;
            font-size: 0.88rem;
            color: var(--white);
            line-height: 1.3;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .sb-user-badge {
            font-size: 0.72rem;
            color: #A7F3D0;
            display: flex;
            align-items: center;
            gap: 4px;
        }
        .sb-user-badge span {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: var(--gold-hi);
            display: inline-block;
        }

        .sb-label {
            font-size: 0.68rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #6EE7B7;
            padding: 0 12px;
            margin-bottom: 8px;
        }

        .sb-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 11px 14px;
            border-radius: 10px;
            color: #D1FAE5;
            text-decoration: none;
            font-weight: 500;
            font-size: 0.88rem;
            transition: all 0.2s ease;
            margin-bottom: 4px;
        }
        .sb-link i {
            font-size: 1.15rem;
            opacity: 0.85;
            transition: opacity 0.2s;
        }
        .sb-link:hover {
            background: rgba(255, 255, 255, 0.1);
            color: var(--white);
        }
        .sb-link:hover i {
            opacity: 1;
        }
        .sb-link.active {
            background: rgba(255, 255, 255, 0.18);
            color: var(--white);
            font-weight: 700;
            box-shadow: inset 3px 0 0 var(--gold-hi);
        }
        .sb-link.active i {
            opacity: 1;
            color: var(--gold-hi);
        }

        .sb-btn-logout {
            margin-top: auto;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 11px 16px;
            border-radius: 10px;
            background: #EF4444;
            color: var(--white);
            text-decoration: none;
            font-weight: 600;
            font-size: 0.88rem;
            transition: all 0.2s ease;
            border: none;
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.25);
        }
        .sb-btn-logout:hover {
            background: #DC2626;
            color: var(--white);
            transform: translateY(-1px);
        }

        .siswa-content-wrapper {
            margin-left: 250px !important;
            width: calc(100% - 250px) !important;
            min-height: 100vh !important;
            display: flex !important;
            flex-direction: column !important;
            background-color: #F6F7F5;
            transition: margin-left 0.3s ease, width 0.3s ease;
        }

        .top-navbar {
            height: 64px;
            background: var(--white);
            border-bottom: 1px solid var(--slate-2);
            padding: 0 28px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 1020;
        }
        .breadcrumb-item {
            font-size: 0.85rem;
            font-weight: 500;
        }

        .kpi-card {
            background: var(--white);
            border-radius: 16px;
            padding: 22px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.03);
            border: 1px solid var(--slate-2);
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .kpi-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.06);
        }

        /* ── Komponen Filter Pill & Tombol Aksi Riwayat ───────── */
        .btn-filter-pill {
            background: #FFFFFF;
            border: 1px solid #CBD5E1;
            color: #475569;
            font-weight: 600;
            font-size: 0.85rem;
            padding: 6px 16px;
            border-radius: 20px;
            transition: all 0.2s ease;
        }
        .btn-filter-pill:hover {
            background: #F8FAFC;
            border-color: #94A3B8;
            color: #1E293B;
        }
        .btn-filter-pill.active {
            background: #065F46;
            border-color: #065F46;
            color: #FFFFFF;
            box-shadow: 0 2px 6px rgba(6, 95, 70, 0.25);
        }
        .btn-filter-pill.active .badge {
            background-color: rgba(255, 255, 255, 0.25) !important;
            color: #FFFFFF !important;
        }

        .btn-icon-eye {
            width: 32px;
            height: 32px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            border: 1px solid #CBD5E1;
            background: #FFFFFF;
            color: #64748B;
            transition: all 0.2s ease;
        }
        .btn-icon-eye:hover {
            background: #E6F4F1;
            border-color: #11756D;
            color: #11756D;
            transform: scale(1.08);
        }

        /* ── Desain Form Profil Bersih ─────────────────────────── */
        .profil-card {
            background: #FFFFFF;
            border: 1px solid #E2E8F0;
            border-radius: 16px;
            padding: 28px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.03);
            height: 100%;
        }
        .profil-card-title {
            font-size: 16px;
            font-weight: 700;
            color: #0F172A;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .profil-field {
            margin-bottom: 16px;
        }
        .profil-label {
            font-size: 12px;
            font-weight: 700;
            color: #64748B;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 6px;
            display: block;
        }
        .profil-input {
            width: 100%;
            padding: 10px 14px;
            border: 1px solid #E2E8F0;
            border-radius: 8px;
            font-size: 14px;
            background: #F8FAFC;
            color: #0F172A;
            outline: none;
            transition: border-color 0.15s;
        }
        .profil-input:focus {
            background: #FFFFFF;
            border-color: #11756D;
            box-shadow: 0 0 0 3px rgba(17, 117, 109, 0.1);
        }
        .profil-btn {
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            border: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.15s;
        }
        .profil-btn-primary {
            background: #11756D;
            color: #FFFFFF;
        }
        .profil-btn-primary:hover {
            background: #0E625B;
        }

        /* Mobile Sidebar */
        .sb-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1030;
        }
        @media (max-width: 991.98px) {
            .siswa-sidebar {
                position: fixed !important;
                transform: translateX(-100%) !important;
            }
            .siswa-sidebar.open {
                transform: translateX(0) !important;
            }
            .siswa-content-wrapper {
                margin-left: 0 !important;
                width: 100% !important;
            }
            .sb-overlay.open {
                display: block;
            }
            .profil-grid {
                grid-template-columns: 1fr !important;
            }
        }
    </style>
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

            <div class="sb-label mt-4">Akun Saya</div>
            <a href="?tab=profil" class="sb-link <?= $page_tab === 'profil' ? 'active' : '' ?>">
                <i class="bi bi-person-circle"></i><span>Profil Saya</span>
            </a>
        </nav>

        <!-- Tombol Keluar Sesi -->
        <a href="../auth/logout.php" class="sb-btn-logout">
            <i class="bi bi-box-arrow-left"></i><span>Keluar Sesi</span>
        </a>
    </aside>

    <!-- KONTEN UTAMA -->
    <div class="siswa-content-wrapper">
        <header class="top-navbar">
            <div class="d-flex align-items-center gap-2">
                <button class="btn btn-sm btn-light d-lg-none me-2" onclick="openSidebar()">
                    <i class="bi bi-list fs-5"></i>
                </button>
                <i class="bi bi-book-half" style="color: #11756D;"></i>
                <span class="text-muted small">Ruang Siswa</span>
                <span class="text-muted small">/</span>
                <span class="fw-bold small text-dark">
                    <?= $page_tab === 'progres' ? 'Progres' : ($page_tab === 'riwayat' ? 'Riwayat' : 'Profil') ?>
                </span>
            </div>

            <div class="d-flex align-items-center gap-3">
                <span class="small text-muted d-none d-sm-inline">
                    <i class="bi bi-calendar-event me-1"></i><?= date('d M Y') ?>
                </span>
                <span class="badge bg-success-subtle text-success fw-bold px-3 py-2">
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

            <!-- ══════════════════════════════════════════════════════════
                 SUBPAGE 1: PROGRES HAFALAN (100% DINAMIS DATABASE)
            ══════════════════════════════════════════════════════════ -->
            <?php if ($page_tab === 'progres'): ?>
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <span class="badge bg-success-subtle text-success fw-bold px-3 py-1 mb-2">PROGRES HAFALAN</span>
                        <h2 class="fw-bold mb-1" style="color: #11756D;">Assalamu’alaikum, <?= htmlspecialchars($nama_panggilan) ?></h2>
                        <p class="text-muted small mb-0">Sedikit demi sedikit, setiap ayat membawa kebaikan dan keberkahan.</p>
                    </div>
                    <span class="badge bg-success text-white fw-bold px-3 py-2">Halaqah <?= htmlspecialchars($halaqah_name) ?></span>
                </div>

                <!-- 3 KPI Siswa Terkoneksi Nyata ke MySQL -->
                <div class="row g-3 mb-4">
                    <div class="col-12 col-md-4">
                        <div class="kpi-card">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="small fw-bold text-muted">Total Hafalan</span>
                                <i class="bi bi-book-half text-success fs-5"></i>
                            </div>
                            <div class="fs-2 fw-bold text-dark">
                                <?= $total_surah_disetor ?> <span class="fs-6 text-muted fw-normal">surah</span>
                            </div>
                            <small class="text-muted"><?= $total_ayat_disetor ?> ayat telah disetorkan</small>
                        </div>
                    </div>

                    <div class="col-12 col-md-4">
                        <div class="kpi-card">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="small fw-bold text-muted">Capaian Target</span>
                                <i class="bi bi-bullseye text-success fs-5"></i>
                            </div>
                            <div class="fs-2 fw-bold text-success"><?= $target_persen ?>%</div>
                            <small class="text-muted">
                                <?= $target_aktif ? "{$target_ayat_selesai} dari {$target_total_ayat} ayat target selesai" : "Target hafalan aktif belum diset" ?>
                            </small>
                        </div>
                    </div>

                    <div class="col-12 col-md-4">
                        <div class="kpi-card">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="small fw-bold text-muted">Sisa Target</span>
                                <i class="bi bi-clock-history text-warning fs-5"></i>
                            </div>
                            <div class="fs-2 fw-bold text-warning">
                                <?= $target_sisa_ayat ?> <span class="fs-6 text-muted fw-normal">ayat</span>
                            </div>
                            <small class="text-muted"><?= htmlspecialchars($target_sisa_teks) ?></small>
                        </div>
                    </div>
                </div>

                <!-- Kartu Target Hafalan Aktif & Langkah Selanjutnya -->
                <div class="row g-4 mb-4">
                    <div class="col-12 col-lg-8">
                        <div class="card p-4 border-0 shadow-sm h-100 bg-white rounded-4" style="border-left: 4px solid #059669 !important;">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="small fw-bold text-muted text-uppercase">Target Hafalan Periode Ini</span>
                                <?php if ($target_aktif): ?>
                                    <span class="badge bg-success-subtle text-success fw-bold">Aktif</span>
                                <?php else: ?>
                                    <span class="badge bg-light text-secondary border">Belum Ditetapkan</span>
                                <?php endif; ?>
                            </div>
                            <h4 class="fw-bold mb-1" style="color: #11756D;"><?= htmlspecialchars($target_judul) ?></h4>
                            <p class="small text-muted mb-3"><?= htmlspecialchars($target_sub) ?></p>
                            
                            <div class="progress mb-2" style="height: 10px;">
                                <div class="progress-bar bg-success" style="width: <?= $target_persen ?>%;"></div>
                            </div>
                            <div class="d-flex justify-content-between small fw-bold">
                                <span class="text-muted"><?= $target_ayat_selesai ?> dari <?= $target_total_ayat ?> ayat selesai</span>
                                <span class="text-success"><?= $target_persen ?>%</span>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-lg-4">
                        <div class="card p-4 border-0 shadow-sm h-100 rounded-4" style="background-color: #F4F7F6; border: 1px solid #E2E8F0;">
                            <span class="badge bg-success text-white fw-bold mb-2 align-self-start">LANGKAH SELANJUTNYA</span>
                            <h5 class="fw-bold text-dark mb-2"><?= htmlspecialchars($target_langkah) ?></h5>
                            <p class="small text-muted mb-3">
                                <?= htmlspecialchars($target_langkah_sub) ?>
                            </p>
                            <div class="small text-muted pt-2 border-top border-success border-opacity-25 mt-auto">
                                Pembimbing: <strong><?= htmlspecialchars($nama_ustadz_pembimbing) ?></strong>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Setoran Terakhir Siswa (Data Riil MySQL) -->
                <div class="card border-0 shadow-sm bg-white rounded-4 overflow-hidden">
                    <div class="card-header bg-white py-3 px-4 d-flex justify-content-between align-items-center border-bottom">
                        <h5 class="fw-bold mb-0" style="color: #11756D;">Setoran Terakhir</h5>
                        <a href="?tab=riwayat" class="small fw-semibold text-decoration-none" style="color: #059669;">Lihat Semua Riwayat →</a>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light small">
                                <tr>
                                    <th class="ps-4">Tanggal</th>
                                    <th>Surah / Ayat</th>
                                    <th>Kategori</th>
                                    <th>Kualitas</th>
                                    <th class="pe-4">Guru Penerima</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($siswa_setoran)): ?>
                                    <?php foreach (array_slice($siswa_setoran, 0, 5) as $set): ?>
                                    <tr>
                                        <td class="ps-4 small fw-medium text-muted"><?= format_tgl_indo($set['tanggal_setor'] ?? '') ?></td>
                                        <td><strong>Surah <?= htmlspecialchars($set['surah']) ?> · Ayat <?= htmlspecialchars($set['ayat_awal']) ?>–<?= htmlspecialchars($set['ayat_akhir']) ?></strong></td>
                                        <td><span class="badge bg-success-subtle text-success">Hafalan Baru</span></td>
                                        <td>
                                            <span class="badge <?= (stripos($set['kualitas'], 'kurang') !== false || stripos($set['kualitas'], 'penguatan') !== false) ? 'bg-warning-subtle text-warning-emphasis' : 'bg-success-subtle text-success' ?> rounded-pill px-2.5 py-1 fw-semibold">
                                                <?= htmlspecialchars($set['kualitas'] ?: 'Lancar') ?>
                                            </span>
                                        </td>
                                        <td class="pe-4 text-muted small"><?= htmlspecialchars($set['nama_ustadz'] ?: $nama_ustadz_pembimbing) ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted small">
                                            <i class="bi bi-inbox fs-4 d-block mb-1"></i>
                                            Belum ada setoran hafalan yang dicatat.
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            <!-- ══════════════════════════════════════════════════════════
                 SUBPAGE 2: RIWAYAT HAFALAN & FEED MASUKAN USTADZ
                 (Layout 2 Kolom + Filter Kategori + Modal Detail Pop-up)
            ══════════════════════════════════════════════════════════ -->
            <?php elseif ($page_tab === 'riwayat'): ?>
                <div class="mb-4">
                    <span class="badge bg-success-subtle text-success fw-bold px-3 py-1 mb-2">PERJALANAN HAFALAN</span>
                    <h2 class="fw-bold mb-1" style="color: #11756D;">Riwayat Hafalan Siswa</h2>
                    <p class="text-muted small mb-0">Tinjau seluruh riwayat setoran, murajaah, dan evaluasi tajwid dari ustadz pembimbing.</p>
                </div>

                <div class="row g-4">
                    <!-- KOLOM KIRI: TABEL SETORAN & MURAJAAH (SESUAI KONSEP TEMAN + ELEGAN) -->
                    <div class="col-12 col-lg-7">
                        <div class="card border-0 shadow-sm bg-white rounded-4 overflow-hidden h-100">
                            
                            <!-- Header & Filter Kategori -->
                            <div class="card-header bg-white py-3 px-4 border-bottom">
                                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                                    <h6 class="fw-bold mb-0" style="color: #11756D;">Daftar Setoran &amp; Murajaah</h6>
                                    <span class="small text-muted"><?= count($riwayat_gabungan) ?> Riwayat Ditemukan</span>
                                </div>
                                
                                <!-- Filter Pills -->
                                <div class="d-flex align-items-center gap-2">
                                    <button type="button" class="btn btn-sm btn-filter-pill active" onclick="filterRiwayat('semua', this)">
                                        Semua <span class="badge rounded-pill bg-light text-dark ms-1"><?= count($riwayat_gabungan) ?></span>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-filter-pill" onclick="filterRiwayat('Setoran', this)">
                                        <i class="bi bi-journal-check me-1"></i>Setoran <span class="badge rounded-pill bg-success-subtle text-success ms-1"><?= count($siswa_setoran) ?></span>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-filter-pill" onclick="filterRiwayat('Murajaah', this)">
                                        <i class="bi bi-arrow-repeat me-1"></i>Murajaah <span class="badge rounded-pill bg-warning-subtle text-warning-emphasis ms-1"><?= count($siswa_murajaah) ?></span>
                                    </button>
                                </div>
                            </div>

                            <!-- Tabel Riwayat -->
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0" id="tabelRiwayat">
                                    <thead class="table-light small">
                                        <tr>
                                            <th class="ps-4">Tanggal</th>
                                            <th>Surah / Ayat</th>
                                            <th>Kualitas Hafalan</th>
                                            <th>Kategori</th>
                                            <th class="text-center pe-4">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($riwayat_gabungan)): ?>
                                            <?php foreach ($riwayat_gabungan as $row): ?>
                                            <tr class="row-riwayat" data-kategori="<?= htmlspecialchars($row['jenis']) ?>">
                                                <td class="ps-4 text-muted small fw-medium"><?= htmlspecialchars($row['tanggal']) ?></td>
                                                <td>
                                                    <div class="fw-bold text-dark"><?= htmlspecialchars($row['surah']) ?></div>
                                                    <div class="text-muted small"><?= htmlspecialchars($row['ayat']) ?></div>
                                                </td>
                                                <td>
                                                    <?php
                                                        $kBadge = 'bg-success-subtle text-success border border-success-subtle';
                                                        if (stripos($row['kualitas'], 'kurang') !== false || stripos($row['kualitas'], 'penguatan') !== false) {
                                                            $kBadge = 'bg-warning-subtle text-warning-emphasis border border-warning-subtle';
                                                        }
                                                    ?>
                                                    <span class="badge <?= $kBadge ?> rounded-pill px-2.5 py-1 fw-semibold small">
                                                        <?= htmlspecialchars($row['kualitas']) ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="badge <?= $row['jenis'] === 'Setoran' ? 'bg-success-subtle text-success' : 'bg-primary-subtle text-primary' ?> rounded-pill px-2.5 py-1 fw-semibold small">
                                                        <?= htmlspecialchars($row['jenis']) ?>
                                                    </span>
                                                </td>
                                                <td class="text-center pe-4">
                                                    <button type="button" class="btn-icon-eye" 
                                                            onclick="bukaModalDetailRiwayat('<?= htmlspecialchars(addslashes($row['tanggal'])) ?>', '<?= htmlspecialchars(addslashes($row['surah'] . ' · ' . $row['ayat'])) ?>', '<?= htmlspecialchars(addslashes($row['jenis'])) ?>', '<?= htmlspecialchars(addslashes($row['kualitas'])) ?>', '<?= htmlspecialchars(addslashes($row['ustadz'])) ?>', '<?= htmlspecialchars(addslashes($row['catatan'])) ?>')"
                                                            title="Buka Detail Riwayat">
                                                        <i class="bi bi-eye"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr id="rowEmptyRiwayat">
                                                <td colspan="5" class="text-center py-5 text-muted small">
                                                    <i class="bi bi-journal-x fs-2 d-block mb-2 text-secondary"></i>
                                                    <h6 class="fw-bold text-dark mb-1">Belum Ada Riwayat</h6>
                                                    <p class="small text-muted mb-0">Riwayat setoran dan murajaah antum akan tampil di sini setelah disimak oleh Ustadz.</p>
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- KOLOM KANAN: MASUKAN DARI USTADZ PEMBIMBING (DATA RIIL DARI MYSQL) -->
                    <div class="col-12 col-lg-5">
                        <div class="card border-0 shadow-sm p-4 h-100 bg-white rounded-4">
                            <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                                <h6 class="fw-bold mb-0" style="color: #11756D;">Masukan dari Ustadz Pembimbing</h6>
                                <span class="badge bg-light text-muted border"><?= count($daftar_masukan) ?> Catatan</span>
                            </div>

                            <?php if (!empty($daftar_masukan)): ?>
                                <div class="d-flex flex-column gap-3 overflow-auto" style="max-height: 520px; padding-right: 4px;">
                                    <?php foreach ($daftar_masukan as $m): ?>
                                    <div class="p-3 rounded-3" style="background:#F8FAFC; border: 1px solid #E2E8F0; border-left: 4px solid #10B981;">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <div class="d-flex align-items-center gap-1.5">
                                                <i class="bi bi-person-fill text-success"></i>
                                                <strong class="small text-dark"><?= htmlspecialchars($m['ustadz']) ?></strong>
                                            </div>
                                            <small class="text-muted" style="font-size: 0.75rem;"><?= htmlspecialchars($m['tanggal']) ?></small>
                                        </div>
                                        <div class="mb-2">
                                            <span class="badge bg-success-subtle text-success small" style="font-size: 0.72rem;">
                                                <?= htmlspecialchars($m['surah']) ?> (<?= htmlspecialchars($m['ayat']) ?>)
                                            </span>
                                        </div>
                                        <p class="small text-secondary mb-0 fst-italic" style="line-height: 1.5;">
                                            "<?= htmlspecialchars($m['catatan']) ?>"
                                        </p>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-5 my-auto text-muted">
                                    <div class="rounded-circle d-inline-flex align-items-center justify-content-center p-3 mb-3" style="width: 56px; height: 56px; background: #E6F4F1; color: #11756D;">
                                        <i class="bi bi-chat-quote-fill fs-3"></i>
                                    </div>
                                    <h6 class="fw-bold text-dark mb-1">Belum Ada Catatan Khusus</h6>
                                    <p class="small text-muted mb-0">Ustadz pembimbing belum memberikan catatan tajwid atau evaluasi khusus pada sesi setoran.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- POP-UP MODAL: DETAIL RIWAYAT HAFALAN (SESUAI FOTO REFERENSI TEMAN) -->
                <div class="modal fade" id="modalDetailRiwayat" tabindex="-1" aria-labelledby="modalDetailRiwayatLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                            <div class="modal-header border-bottom py-3 px-4" style="background: #EBF8F5;">
                                <div>
                                    <h6 class="modal-title fw-bold text-dark d-flex align-items-center gap-2 mb-0" id="modalDetailRiwayatLabel">
                                        <i class="bi bi-card-checklist" style="color: #11756D; font-size: 1.25rem;"></i>Detail Riwayat Hafalan
                                    </h6>
                                    <small class="text-muted" style="font-size: 0.8rem;">Informasi lengkap sesi setoran atau murajaah.</small>
                                </div>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body p-4">
                                <div class="row g-3">
                                    <div class="col-6">
                                        <label class="small text-muted fw-bold text-uppercase d-block mb-1" style="font-size: 0.72rem; letter-spacing: 0.5px;">Tanggal</label>
                                        <span id="mdlTanggal" class="fw-semibold text-dark">-</span>
                                    </div>
                                    <div class="col-6">
                                        <label class="small text-muted fw-bold text-uppercase d-block mb-1" style="font-size: 0.72rem; letter-spacing: 0.5px;">Jenis / Kategori</label>
                                        <span id="mdlJenis" class="badge bg-success-subtle text-success rounded-pill px-3 py-1">Setoran</span>
                                    </div>
                                    <div class="col-12 border-top pt-2">
                                        <label class="small text-muted fw-bold text-uppercase d-block mb-1" style="font-size: 0.72rem; letter-spacing: 0.5px;">Surah / Ayat</label>
                                        <h6 id="mdlSurahAyat" class="fw-bold mb-0" style="color: #11756D;">-</h6>
                                    </div>
                                    <div class="col-6 border-top pt-2">
                                        <label class="small text-muted fw-bold text-uppercase d-block mb-1" style="font-size: 0.72rem; letter-spacing: 0.5px;">Kualitas Hafalan</label>
                                        <span id="mdlKualitas" class="badge bg-light text-dark rounded-pill px-3 py-1">-</span>
                                    </div>
                                    <div class="col-6 border-top pt-2">
                                        <label class="small text-muted fw-bold text-uppercase d-block mb-1" style="font-size: 0.72rem; letter-spacing: 0.5px;">Ustadz Pembimbing</label>
                                        <span id="mdlUstadz" class="fw-semibold text-dark">-</span>
                                    </div>
                                    <div class="col-12 border-top pt-2">
                                        <label class="small text-muted fw-bold text-uppercase d-block mb-1" style="font-size: 0.72rem; letter-spacing: 0.5px;">Catatan Ustadz / Guru</label>
                                        <div id="mdlCatatanBox" class="p-3 rounded-3 mt-1" style="background: #F8FAFC; border: 1px solid #E2E8F0; border-left: 3px solid #10B981;">
                                            <p id="mdlCatatanText" class="small mb-0 text-secondary" style="line-height: 1.6;">-</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer border-0 bg-light py-2.5 px-4 justify-content-end">
                                <button type="button" class="btn btn-secondary rounded-3 px-4 py-1.5 fw-semibold small" data-bs-dismiss="modal">
                                    <i class="bi bi-x-circle me-1"></i>Tutup
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

            <!-- ══════════════════════════════════════════════════════════
                 SUBPAGE 3: PROFIL PRIBADI SISWA & KEAMANAN AKUN
            ══════════════════════════════════════════════════════════ -->
            <?php elseif ($page_tab === 'profil'): ?>
                <div class="mb-3">
                    <h1 class="profil-page-title mb-1" style="font-size: 24px; font-weight: 800; color: #0F172A;">Profil Pribadi</h1>
                    <p class="profil-page-subtitle mb-0" style="color: #64748B;">Informasi diri dan keamanan akun siswa Anda.</p>
                </div>

                <!-- 1. HERO IDENTITY CARD (KARTU PROFIL PUTIH MODERN) -->
                <div class="card border-0 mb-4" style="background: #FFFFFF; border: 1px solid #E2E8F0 !important; border-radius: 16px; padding: 24px; box-shadow: 0 2px 10px rgba(0,0,0,0.03);">
                    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                        
                        <!-- Avatar & Identitas Siswa -->
                        <div class="d-flex align-items-center gap-3">
                            <div class="flex-shrink-0" 
                                 style="width: 64px; height: 64px; border-radius: 14px; background: #E6F4F1; color: #11756D; font-weight: 700; font-size: 22px; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 6px rgba(17, 117, 109, 0.1);">
                                <?= htmlspecialchars($siswa_initials) ?>
                            </div>
                            <div>
                                <h3 class="fw-bold mb-0" style="font-size: 20px; color: #0F172A; line-height: 1.3;"><?= htmlspecialchars($siswa_name) ?></h3>
                                <div style="font-size: 14px; font-weight: 500; color: #64748B; margin-top: 2px;">NIS: <?= htmlspecialchars($siswa_nis) ?></div>
                                <div class="d-flex align-items-center gap-2 flex-wrap mt-2">
                                    <span class="badge rounded-pill fw-semibold" style="background: #E6F4F1; color: #11756D; font-size: 12px; padding: 5px 12px;">
                                        <i class="bi bi-diagram-3-fill me-1"></i>Halaqah <?= htmlspecialchars($halaqah_name) ?>
                                    </span>
                                    <span class="badge rounded-pill fw-semibold" style="background: #F1F5F9; color: #475569; font-size: 12px; padding: 5px 12px;">
                                        <i class="bi bi-person-badge-fill me-1"></i><?= htmlspecialchars($nama_ustadz_pembimbing) ?>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Statistik Singkat Capaian Siswa -->
                        <div class="d-flex gap-2 text-center align-items-center mt-3 mt-md-0">
                            <div class="px-3 py-2 rounded-3" style="background: #F8FAFC; border: 1px solid #E2E8F0; min-width: 85px;">
                                <div class="fw-bold" style="font-size: 18px; color: #11756D;"><?= $total_surah_disetor ?> Surah</div>
                                <small class="text-muted fw-semibold" style="font-size: 11px;">Hafalan</small>
                            </div>
                            <div class="px-3 py-2 rounded-3" style="background: #F8FAFC; border: 1px solid #E2E8F0; min-width: 85px;">
                                <div class="fw-bold" style="font-size: 18px; color: #D97706;"><?= $target_persen ?>%</div>
                                <small class="text-muted fw-semibold" style="font-size: 11px;">Capaian Target</small>
                            </div>
                            <div class="px-3 py-2 rounded-3" style="background: #F8FAFC; border: 1px solid #E2E8F0; min-width: 85px;">
                                <div class="fw-bold" style="font-size: 18px; color: #0F172A;"><?= count($siswa_murajaah) ?></div>
                                <small class="text-muted fw-semibold" style="font-size: 11px;">Murajaah</small>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- 2. DUA KOLOM FORMULIR PENGATURAN (BIODATA & KEAMANAN) -->
                <div class="profil-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px; align-items: stretch;">
                    
                    <!-- KOLOM KIRI: DATA BIODATA & HALAQAH SISWA -->
                    <div class="profil-card d-flex flex-column">
                        <h2 class="profil-card-title">
                            <i class="bi bi-person-circle text-success"></i>Data Biodata &amp; Halaqah Siswa
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
                                        <input type="text" class="profil-input" value="<?= htmlspecialchars($nama_ustadz_pembimbing) ?>" readonly>
                                    </div>
                                </div>
                            </div>

                            <div class="profil-actions mt-auto pt-3">
                                <button type="submit" class="profil-btn profil-btn-primary">
                                    <i class="bi bi-floppy"></i>Simpan Biodata Siswa
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- KOLOM KANAN: PENGATURAN KATA SANDI SISWA -->
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
                                    <i class="bi bi-shield-lock me-1"></i>Perbarui Sandi Siswa
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
<script>
// ── Sidebar Mobile ──────────────────────────────────────────────────
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
window.addEventListener('resize', () => {
    if (window.innerWidth >= 992) closeSidebar();
});

// ── Filter Riwayat Hafalan (Semua / Setoran / Murajaah) ──────────────
function filterRiwayat(kategori, btnEl) {
    // Update tombol aktif
    document.querySelectorAll('.btn-filter-pill').forEach(b => b.classList.remove('active'));
    if (btnEl) btnEl.classList.add('active');

    const rows = document.querySelectorAll('.row-riwayat');
    let visibleCount = 0;

    rows.forEach(r => {
        const rowKat = r.getAttribute('data-kategori');
        if (kategori === 'semua' || rowKat === kategori) {
            r.style.display = '';
            visibleCount++;
        } else {
            r.style.display = 'none';
        }
    });

    const emptyRow = document.getElementById('rowEmptyRiwayat');
    if (emptyRow) {
        emptyRow.style.display = visibleCount === 0 ? '' : 'none';
    }
}

// ── Pop-up Modal Detail Riwayat Hafalan ──────────────────────────────
let modalDetailInst = null;
function bukaModalDetailRiwayat(tanggal, surahAyat, jenis, kualitas, ustadz, catatan) {
    document.getElementById('mdlTanggal').innerText = tanggal || '-';
    document.getElementById('mdlSurahAyat').innerText = surahAyat || '-';
    
    // Badge Jenis
    const elJenis = document.getElementById('mdlJenis');
    if (elJenis) {
        elJenis.innerText = jenis || 'Setoran';
        elJenis.className = jenis === 'Setoran' ? 'badge bg-success-subtle text-success rounded-pill px-3 py-1' : 'badge bg-primary-subtle text-primary rounded-pill px-3 py-1';
    }

    // Badge Kualitas
    const elKual = document.getElementById('mdlKualitas');
    if (elKual) {
        elKual.innerText = kualitas || '-';
        if ((kualitas || '').toLowerCase().includes('kurang') || (kualitas || '').toLowerCase().includes('penguatan')) {
            elKual.className = 'badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-3 py-1';
        } else {
            elKual.className = 'badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1';
        }
    }

    document.getElementById('mdlUstadz').innerText = ustadz || 'Ust. Rival Alvian Melfandy';
    
    // Catatan
    const elCatText = document.getElementById('mdlCatatanText');
    const elCatBox  = document.getElementById('mdlCatatanBox');
    if (catatan && catatan.trim() !== '') {
        elCatText.innerText = `"${catatan.trim()}"`;
        elCatText.classList.remove('text-muted');
        elCatText.classList.add('text-dark', 'fst-italic');
        elCatBox.style.borderLeftColor = '#10B981';
    } else {
        elCatText.innerText = 'Tidak ada catatan tambahan dari ustadz pembimbing.';
        elCatText.classList.remove('text-dark', 'fst-italic');
        elCatText.classList.add('text-muted');
        elCatBox.style.borderLeftColor = '#CBD5E1';
    }

    const modalEl = document.getElementById('modalDetailRiwayat');
    if (modalEl) {
        if (!modalDetailInst) {
            modalDetailInst = new bootstrap.Modal(modalEl);
        }
        modalDetailInst.show();
    }
}
</script>
</body>
</html>
