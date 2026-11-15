<?php
/**
 * modules/ustadz/index.php
 * Dashboard Asatidz Pembimbing Halaqah – SMA IT Al-Fadhilah Pekanbaru
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

$ustadz_name = $_SESSION['user_name'] ?? 'Ust. Rival Alvian Melfandy';
$halaqah_name = $_SESSION['user_halaqah'] ?? 'Al-Furqan';
$page_tab = $_GET['tab'] ?? 'binaan';
$notif = '';

// Simulasi & penyimpanan setoran ke database
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'simpan_setoran') {
        $santri = htmlspecialchars($_POST['santri'] ?? 'Muhammad Fadhlan');
        $surah = htmlspecialchars($_POST['surah'] ?? 'Al-Mulk');
        $ayat_awal = (int)($_POST['ayat_awal'] ?? 1);
        $ayat_akhir = (int)($_POST['ayat_akhir'] ?? 30);
        $nilai = (int)($_POST['nilai'] ?? 90);
        $kualitas = htmlspecialchars($_POST['kualitas'] ?? 'A (Mumtaz)');
        $catatan = htmlspecialchars($_POST['catatan'] ?? '');

        try {
            $pdo = koneksi_database();
            if ($pdo) {
                $stmt = $pdo->prepare("INSERT INTO setoran_hafalan (siswa_id, nama_siswa, ustadz_id, nama_ustadz, surah, ayat_awal, ayat_akhir, nilai, kualitas, catatan) VALUES (3, :siswa, 2, :ustadz, :surah, :awal, :akhir, :nilai, :kualitas, :catatan)");
                $stmt->execute([
                    'siswa'    => $santri,
                    'ustadz'   => $ustadz_name,
                    'surah'    => $surah,
                    'awal'     => $ayat_awal,
                    'akhir'    => $ayat_akhir,
                    'nilai'    => $nilai,
                    'kualitas' => $kualitas,
                    'catatan'  => $catatan
                ]);
            }
        } catch (Throwable $e) {}
        $notif = "Alhamdulillah! Setoran hafalan {$santri} ({$surah} ayat {$ayat_awal}–{$ayat_akhir}) berhasil disimpan ke database dengan nilai {$nilai}.";
        $page_tab = 'catat';
    }

    elseif ($action === 'simpan_murajaah') {
        $santri = htmlspecialchars($_POST['santri'] ?? 'Muhammad Fadhlan');
        $surah = htmlspecialchars($_POST['surah_diulang'] ?? 'Juz 30');
        $evaluasi = htmlspecialchars($_POST['evaluasi'] ?? 'Lancar');

        try {
            $pdo = koneksi_database();
            if ($pdo) {
                $stmt = $pdo->prepare("INSERT INTO murajaah (siswa_id, nama_siswa, ustadz_id, nama_ustadz, juz_surah, status_kelancaran) VALUES (3, :siswa, 2, :ustadz, :juz, :eval)");
                $stmt->execute([
                    'siswa'  => $santri,
                    'ustadz' => $ustadz_name,
                    'juz'    => $surah,
                    'eval'   => $evaluasi
                ]);
            }
        } catch (Throwable $e) {}
        $notif = "Catatan murajaah {$santri} ({$surah}) berhasil disimpan ke database!";
        $page_tab = 'murajaah';
    }

    elseif ($action === 'simpan_target') {
        $santri = htmlspecialchars($_POST['santri'] ?? 'Muhammad Fadhlan');
        $target = htmlspecialchars($_POST['target'] ?? 'Juz 29');
        $notif = "Target hafalan ({$target}) untuk {$santri} berhasil ditetapkan!";
        $page_tab = 'target';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ruang Asatidz – SMA IT Al-Fadhilah Pekanbaru</title>
    
    <link rel="icon" type="image/png" href="../../assets/images/logo-sekolah.png">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Amiri:wght@400;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5.3 & Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        :root {
            --color-navy: #1F4172;
            --color-navy-dark: #122847;
            --color-emerald: #059669;
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
            background: #0f2b25;
            min-height: 100vh;
            color: #ffffff;
            flex-shrink: 0;
            padding: 24px 16px;
        }
        .ustadz-nav-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 14px;
            color: #94A3B8;
            font-weight: 600;
            font-size: 0.9rem;
            border-radius: 8px;
            text-decoration: none;
            transition: all 0.2s;
            margin-bottom: 4px;
        }
        .ustadz-nav-link:hover {
            color: #ffffff;
            background: rgba(255, 255, 255, 0.08);
        }
        .ustadz-nav-link.active {
            background: #059669;
            color: #ffffff;
            font-weight: 700;
        }
        .kpi-card {
            background: #ffffff;
            border: 1px solid var(--color-border);
            border-radius: 14px;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(31, 65, 114, 0.04);
            height: 100%;
        }
        .progress-bar-clean {
            background: #059669;
            border-radius: 4px;
        }
    </style>
</head>
<body>

<div class="d-flex">
    <!-- SIDEBAR USTADZ -->
    <aside class="ustadz-sidebar d-flex flex-column">
        <!-- Brand -->
        <div class="d-flex align-items-center gap-2 pb-3 mb-3 border-bottom border-secondary border-opacity-25">
            <img src="../../assets/images/logo-sekolah.png" alt="Logo" width="40" height="40" class="rounded-circle object-fit-contain bg-white p-1">
            <div>
                <div class="fw-bold fs-6 text-white lh-1">SMA IT Al-Fadhilah</div>
                <small class="text-success fw-semibold" style="font-size: 0.72rem;">Ruang Asatidz</small>
            </div>
        </div>

        <!-- User Info -->
        <div class="p-2 rounded-3 mb-4 bg-dark bg-opacity-25 d-flex align-items-center gap-2">
            <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center fw-bold" style="width: 36px; height: 36px; font-size: 0.85rem;">
                RA
            </div>
            <div class="overflow-hidden">
                <div class="fw-bold text-white small text-truncate"><?= htmlspecialchars($ustadz_name) ?></div>
                <small class="text-warning fw-semibold" style="font-size: 0.7rem;">Halaqah <?= htmlspecialchars($halaqah_name) ?></small>
            </div>
        </div>

        <!-- Navigation Menu -->
        <nav class="flex-grow-1">
            <small class="text-uppercase fw-bold text-muted px-2 mb-2 d-block" style="font-size: 0.68rem; letter-spacing: 0.06em;">Menu Pembimbing</small>
            <a href="?tab=binaan" class="ustadz-nav-link <?= $page_tab === 'binaan' ? 'active' : '' ?>">
                <i class="bi bi-people-fill"></i>Siswa Binaan
            </a>
            <a href="?tab=catat" class="ustadz-nav-link <?= $page_tab === 'catat' ? 'active' : '' ?>">
                <i class="bi bi-pencil-square"></i>Catat Hafalan Baru
            </a>
            <a href="?tab=murajaah" class="ustadz-nav-link <?= $page_tab === 'murajaah' ? 'active' : '' ?>">
                <i class="bi bi-arrow-repeat"></i>Catat Murajaah
            </a>
            <a href="?tab=target" class="ustadz-nav-link <?= $page_tab === 'target' ? 'active' : '' ?>">
                <i class="bi bi-bullseye"></i>Target Hafalan
            </a>
            <a href="?tab=profil" class="ustadz-nav-link <?= $page_tab === 'profil' ? 'active' : '' ?>">
                <i class="bi bi-person-circle"></i>Profil Saya
            </a>
        </nav>

        <!-- Bottom Actions -->
        <div class="pt-3 border-top border-secondary border-opacity-25 mt-auto">
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
            <div class="d-flex align-items-center gap-2 small text-muted">
                <span>Ruang Guru / Ustadz</span>
                <span>/</span>
                <span class="fw-bold text-dark text-capitalize"><?= htmlspecialchars($page_tab) ?></span>
            </div>
            <div class="d-flex align-items-center gap-3">
                <span class="badge bg-success-subtle text-success px-3 py-2 fw-bold">
                    <i class="bi bi-check-circle-fill me-1"></i>Halaqah <?= htmlspecialchars($halaqah_name) ?> (15 Santri)
                </span>
                <a href="?tab=catat" class="btn btn-sm btn-primary fw-bold">
                    <i class="bi bi-plus-circle me-1"></i>Setoran Baru
                </a>
            </div>
        </header>

        <main class="p-4 flex-grow-1">

            <?php if (!empty($notif)): ?>
                <div class="alert alert-success alert-dismissible fade show d-flex align-items-center py-2 px-3 small rounded-3 mb-4" role="alert">
                    <i class="bi bi-check-circle-fill me-2 fs-5"></i>
                    <div><?= htmlspecialchars($notif) ?></div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- SUBPAGE 1: SISWA BINAAN -->
            <?php if ($page_tab === 'binaan'): ?>
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h2 class="fw-bold mb-1" style="color: #1F4172;">Santri Halaqah <?= htmlspecialchars($halaqah_name) ?></h2>
                        <p class="text-muted small mb-0">Pantau perkembangan capaian target hafalan dan rekapitulasi nilai setoran santri binaan Anda.</p>
                    </div>
                </div>

                <!-- 4 KPI Ustadz -->
                <div class="row g-3 mb-4">
                    <div class="col-12 col-sm-6 col-xl-3">
                        <div class="kpi-card">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="small fw-bold text-muted">Santri Binaan</span>
                                <i class="bi bi-people-fill text-success fs-5"></i>
                            </div>
                            <div class="fs-2 fw-bold text-dark">15</div>
                            <small class="text-muted">Kelas X-A · Halaqah Al-Furqan</small>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6 col-xl-3">
                        <div class="kpi-card">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="small fw-bold text-muted">Setoran Pekan Ini</span>
                                <i class="bi bi-journal-check text-primary fs-5"></i>
                            </div>
                            <div class="fs-2 fw-bold text-dark">28</div>
                            <small class="text-muted">Hafalan baru &amp; Murajaah</small>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6 col-xl-3">
                        <div class="kpi-card">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="small fw-bold text-muted">Capaian Halaqah</span>
                                <i class="bi bi-award-fill text-warning fs-5"></i>
                            </div>
                            <div class="fs-2 fw-bold text-dark">82%</div>
                            <small class="text-success fw-bold">Sesuai Target Kurikulum</small>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6 col-xl-3">
                        <div class="kpi-card">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="small fw-bold text-muted">Perlu Bimbingan</span>
                                <i class="bi bi-hourglass-split text-danger fs-5"></i>
                            </div>
                            <div class="fs-2 fw-bold text-danger">2</div>
                            <small class="text-muted">Perlu tambahan murajaah</small>
                        </div>
                    </div>
                </div>

                <!-- Tabel Siswa Binaan -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold mb-0" style="color: #1F4172;">Daftar Santri Binaan</h5>
                        <input type="text" class="form-control form-control-sm" placeholder="Cari nama santri..." style="max-width: 240px;">
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light small">
                                <tr>
                                    <th>No</th>
                                    <th>NIS</th>
                                    <th>Nama Santri</th>
                                    <th>Target Aktif</th>
                                    <th>Progres</th>
                                    <th>Setoran Terakhir</th>
                                    <th>Nilai</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>1</td>
                                    <td>102601</td>
                                    <td><strong>Muhammad Fadhlan</strong></td>
                                    <td>Al-Mulk (Ayat 1-30)</td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="progress flex-grow-1" style="height: 6px;">
                                                <div class="progress-bar bg-success" style="width: 60%;"></div>
                                            </div>
                                            <small class="fw-bold">60%</small>
                                        </div>
                                    </td>
                                    <td>Al-Mulk: 13–18</td>
                                    <td><span class="badge bg-success-subtle text-success fw-bold">90 / 100</span></td>
                                    <td>
                                        <a href="?tab=catat&santri=Muhammad+Fadhlan" class="btn btn-sm btn-outline-success py-1 px-2">Catat</a>
                                    </td>
                                </tr>
                                <tr>
                                    <td>2</td>
                                    <td>102602</td>
                                    <td><strong>Ahmad Zaid</strong></td>
                                    <td>Al-Qalam (Ayat 1-25)</td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="progress flex-grow-1" style="height: 6px;">
                                                <div class="progress-bar bg-success" style="width: 85%;"></div>
                                            </div>
                                            <small class="fw-bold">85%</small>
                                        </div>
                                    </td>
                                    <td>Al-Qalam: 16–20</td>
                                    <td><span class="badge bg-success-subtle text-success fw-bold">88 / 100</span></td>
                                    <td>
                                        <a href="?tab=catat&santri=Ahmad+Zaid" class="btn btn-sm btn-outline-success py-1 px-2">Catat</a>
                                    </td>
                                </tr>
                                <tr>
                                    <td>3</td>
                                    <td>102603</td>
                                    <td><strong>Radit Gilang Ramadhan S</strong></td>
                                    <td>Al-Haqqah (Ayat 1-20)</td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="progress flex-grow-1" style="height: 6px;">
                                                <div class="progress-bar bg-warning" style="width: 45%;"></div>
                                            </div>
                                            <small class="fw-bold text-warning">45%</small>
                                        </div>
                                    </td>
                                    <td>Al-Haqqah: 1–9</td>
                                    <td><span class="badge bg-warning-subtle text-warning fw-bold">78 / 100</span></td>
                                    <td>
                                        <a href="?tab=catat&santri=Radit+Gilang" class="btn btn-sm btn-outline-success py-1 px-2">Catat</a>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            <!-- SUBPAGE 2: CATAT HAFALAN BARU -->
            <?php elseif ($page_tab === 'catat'): ?>
                <div class="mb-4">
                    <h2 class="fw-bold mb-1" style="color: #1F4172;">Catat Setoran Hafalan Baru</h2>
                    <p class="text-muted small mb-0">Simak hafalan santri dan berikan penilaian tajwid serta makhraj.</p>
                </div>

                <div class="card p-4 border-0 shadow-sm" style="max-width: 750px;">
                    <form action="index.php?tab=binaan" method="POST">
                        <input type="hidden" name="action" value="simpan_setoran">
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted">Santri yang Menyetor</label>
                            <select class="form-select" name="santri">
                                <option value="Muhammad Fadhlan" <?= ($_GET['santri'] ?? '') === 'Muhammad Fadhlan' ? 'selected' : '' ?>>Muhammad Fadhlan (102601)</option>
                                <option value="Ahmad Zaid" <?= ($_GET['santri'] ?? '') === 'Ahmad Zaid' ? 'selected' : '' ?>>Ahmad Zaid (102602)</option>
                                <option value="Radit Gilang Ramadhan S" <?= ($_GET['santri'] ?? '') === 'Radit Gilang' ? 'selected' : '' ?>>Radit Gilang Ramadhan S (102603)</option>
                            </select>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-8">
                                <label class="form-label fw-bold small text-muted">Surah</label>
                                <select class="form-select" name="surah">
                                    <option value="Al-Mulk">67. Al-Mulk (30 Ayat)</option>
                                    <option value="Al-Qalam">68. Al-Qalam (52 Ayat)</option>
                                    <option value="An-Naba">78. An-Naba' (40 Ayat)</option>
                                </select>
                            </div>
                            <div class="col-2">
                                <label class="form-label fw-bold small text-muted">Ayat Awal</label>
                                <input type="number" class="form-control" name="ayat_awal" value="19" min="1">
                            </div>
                            <div class="col-2">
                                <label class="form-label fw-bold small text-muted">Ayat Akhir</label>
                                <input type="number" class="form-control" name="ayat_akhir" value="24" min="1">
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-6">
                                <label class="form-label fw-bold small text-muted">Kualitas Hafalan</label>
                                <select class="form-select" name="kualitas">
                                    <option value="Sangat Lancar">Sangat Lancar (Nilai 90-100)</option>
                                    <option value="Lancar" selected>Lancar (Nilai 80-89)</option>
                                    <option value="Kurang Lancar">Kurang Lancar (Nilai 70-79)</option>
                                </select>
                            </div>
                            <div class="col-3">
                                <label class="form-label fw-bold small text-muted">Nilai Angka</label>
                                <input type="number" class="form-control" name="nilai" value="90" min="0" max="100">
                            </div>
                            <div class="col-3">
                                <label class="form-label fw-bold small text-muted">Tanggal Setor</label>
                                <input type="date" class="form-control" name="tanggal" value="<?= date('Y-m-d') ?>">
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold small text-muted">Catatan Tajwid / Makhraj Ustadz (Opsional)</label>
                            <textarea class="form-control" name="catatan" rows="3" placeholder="Contoh: Makhraj huruf 'ain dan ghain sudah mantap. Perhatikan panjang mad pada ayat 22..."></textarea>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-success fw-bold px-4">
                                <i class="bi bi-check2-circle me-1"></i>Simpan Hafalan Santri
                            </button>
                            <a href="?tab=binaan" class="btn btn-outline-secondary">Batal</a>
                        </div>
                    </form>
                </div>

            <!-- SUBPAGE 3: CATAT MURAJAAH -->
            <?php elseif ($page_tab === 'murajaah'): ?>
                <div class="mb-4">
                    <h2 class="fw-bold mb-1" style="color: #1F4172;">Catat Pengulangan (Murajaah)</h2>
                    <p class="text-muted small mb-0">Pastikan hafalan lama santri tetap mutqin dan terjaga.</p>
                </div>
                <div class="card p-4 border-0 shadow-sm rounded-4" style="max-width: 700px;">
                    <form action="?tab=murajaah" method="POST">
                        <input type="hidden" name="action" value="simpan_murajaah">
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted">Santri Murajaah</label>
                            <select name="santri" class="form-select">
                                <option value="Muhammad Fadhlan">Muhammad Fadhlan (102601)</option>
                                <option value="Ahmad Zaid">Ahmad Zaid (102602)</option>
                            </select>
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-8">
                                <label class="form-label fw-bold small text-muted">Surah yang Diulang</label>
                                <select name="surah_diulang" class="form-select">
                                    <option value="78. An-Naba' (Juz 30)">78. An-Naba' (Juz 30)</option>
                                    <option value="79. An-Nazi'at">79. An-Nazi'at</option>
                                    <option value="67. Al-Mulk">67. Al-Mulk (Juz 29)</option>
                                    <option value="Juz 30 Lengkap">Juz 30 Lengkap</option>
                                </select>
                            </div>
                            <div class="col-4">
                                <label class="form-label fw-bold small text-muted">Hasil Evaluasi</label>
                                <select name="evaluasi" class="form-select">
                                    <option value="Sangat Mutqin">Sangat Mutqin</option>
                                    <option value="Lancar" selected>Lancar</option>
                                    <option value="Perlu Diulang">Perlu Diulang</option>
                                </select>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-success fw-bold px-4 align-self-start">
                            <i class="bi bi-save me-1"></i>Simpan Catatan Murajaah
                        </button>
                    </form>
                </div>

            <!-- SUBPAGE 4: TARGET HAFALAN -->
            <?php elseif ($page_tab === 'target'): ?>
                <div class="mb-4">
                    <h2 class="fw-bold mb-1" style="color: #1F4172;">Target Hafalan Santri</h2>
                    <p class="text-muted small mb-0">Tetapkan sasaran juz/surah dan batas waktu bagi setiap santri halaqah.</p>
                </div>
                <div class="card p-4 border-0 shadow-sm rounded-4" style="max-width: 700px;">
                    <form action="?tab=target" method="POST">
                        <input type="hidden" name="action" value="simpan_target">
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted">Santri</label>
                            <select name="santri" class="form-select">
                                <option value="Muhammad Fadhlan">Muhammad Fadhlan</option>
                                <option value="Ahmad Zaid">Ahmad Zaid</option>
                            </select>
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-8">
                                <label class="form-label fw-bold small text-muted">Surah Target</label>
                                <input type="text" name="target" class="form-control" value="Surah Al-Mulk (Ayat 1–30)">
                            </div>
                            <div class="col-4">
                                <label class="form-label fw-bold small text-muted">Tenggat Waktu</label>
                                <input type="date" name="deadline" class="form-control" value="2026-11-20">
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary fw-bold px-4 align-self-start">
                            <i class="bi bi-bullseye me-1"></i>Tetapkan Target
                        </button>
                    </form>
                </div>

            <!-- SUBPAGE 5: PROFIL USTADZ -->
            <?php elseif ($page_tab === 'profil'): ?>
                <div class="mb-4">
                    <h2 class="fw-bold mb-1" style="color: #1F4172;">Profil Saya</h2>
                    <p class="text-muted small mb-0">Informasi akun asatidz pembimbing.</p>
                </div>
                <div class="card p-4 border-0 shadow-sm" style="max-width: 500px;">
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">Nama Lengkap</label>
                        <input type="text" class="form-control" value="<?= htmlspecialchars($ustadz_name) ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">NIP</label>
                        <input type="text" class="form-control" value="199205142023031004" readonly style="background: #F1F5F9;">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">Halaqah Binaan</label>
                        <input type="text" class="form-control" value="Halaqah <?= htmlspecialchars($halaqah_name) ?>" readonly style="background: #F1F5F9;">
                    </div>
                    <button class="btn btn-primary btn-sm fw-bold align-self-start" onclick="alert('Profil berhasil diperbarui!')">
                        Simpan Perubahan
                    </button>
                </div>
            <?php endif; ?>

        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

