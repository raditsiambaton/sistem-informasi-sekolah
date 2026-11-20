<?php
/**
 * modules/ustadz/index.php
 * Ruang Asatidz Pembimbing Halaqah  SIM Tahfiz SMA IT Al-Fadhilah Pekanbaru
 * 
 * Menu Utama:
 * 1. Siswa Binaan (tab=binaan)       - Dashboard utama halaqah & ringkasan progres santri
 * 2. Hafalan (tab=hafalan)            - Pilih siswa, daftar surah target, input setoran, dan baca ayat
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

$ustadz_name   = $_SESSION['user_name'] ?? 'Ust. Rival Alvian Melfandy';
$halaqah_name  = $_SESSION['user_halaqah'] ?? 'Al-Furqan';
$ustadz_nip    = $_SESSION['user_nip_nis'] ?? '199205142023031004';
$ustadz_kontak = $_SESSION['user_no_hp'] ?? '0812-7890-1234';
$ustadz_id     = $_SESSION['user_id'] ?? 2;

// Default tab: rekap (dashboard rekap tahfiz)
$page_tab = $_GET['tab'] ?? 'rekap';
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
    if ($pdo) {
        $u_stmt = $pdo->prepare("SELECT nama_lengkap, nip_nis, halaqah, no_hp FROM users WHERE id = :id LIMIT 1");
        $u_stmt->execute(['id' => $ustadz_id]);
        $u_row = $u_stmt->fetch();
        if ($u_row) {
            $ustadz_name  = $u_row['nama_lengkap'] ?? $ustadz_name;
            $halaqah_name = $u_row['halaqah'] ?? $halaqah_name;
            $ustadz_nip   = $u_row['nip_nis'] ?? $ustadz_nip;
            if (!empty($u_row['no_hp'])) {
                $ustadz_kontak = $u_row['no_hp'];
            }
        }

        try {
            $pdo->exec("CREATE TABLE IF NOT EXISTS `target_hafalan` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `siswa_id` INT NOT NULL,
                `ustadz_id` INT NOT NULL,
                `surah_awal` VARCHAR(180) NOT NULL,
                `surah_akhir` VARCHAR(180) NOT NULL,
                `tanggal_mulai` DATE NOT NULL,
                `tanggal_akhir` DATE NOT NULL,
                `status` VARCHAR(20) NOT NULL DEFAULT 'Aktif',
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        } catch (Throwable $e) {}
    }
} catch (Throwable $e) {}

// Kurikulum target: Juz 1, 2, 3, 4, dan 30 (urutan ini dipakai validasi rentang)
$juz30_target_sumber = [
    ["An-Naba'", 40], ["An-Nazi'at", 46], ["'Abasa", 42], ['At-Takwir', 29],
    ['Al-Infitar', 19], ['Al-Muthaffifin', 36], ['Al-Insyiqaq', 25], ['Al-Buruj', 22],
    ['At-Tariq', 17], ["Al-A'la", 19], ['Al-Ghasyiyah', 26], ['Al-Fajr', 30],
    ['Al-Balad', 20], ['Asy-Syams', 15], ['Al-Lail', 21], ['Adh-Dhuha', 11],
    ['Al-Insyirah', 8], ['At-Tin', 8], ["Al-'Alaq", 19], ['Al-Qadr', 5],
    ['Al-Bayyinah', 8], ['Az-Zalzalah', 8], ["Al-'Adiyat", 11], ["Al-Qari'ah", 11],
    ['At-Takatsur', 8], ["Al-'Asr", 3], ['Al-Humazah', 9], ['Al-Fil', 5],
    ['Quraisy', 4], ["Al-Ma'un", 7], ['Al-Kautsar', 3], ['Al-Kafirun', 6],
    ['An-Nasr', 3], ['Al-Lahab', 5], ['Al-Ikhlas', 4], ['Al-Falaq', 5], ['An-Nas', 6],
];
$label_juz30_target = [];
foreach ($juz30_target_sumber as $item_juz30) {
$label_juz30_target[] = 'Juz 30 • ' . $item_juz30[0] . ' (Ayat 1–' . $item_juz30[1] . ')';
}
$kurikulum_target = [
    'Juz 1' => [
        'Juz 1 • Al-Fatihah (Ayat 1–7)',
        'Juz 1 • Al-Baqarah (Ayat 1–141)',
    ],
    'Juz 2' => [
        'Juz 2 • Al-Baqarah (Ayat 142–252)',
    ],
    'Juz 3' => [
        'Juz 3 • Al-Baqarah (Ayat 253–286)',
        "Juz 3 • Ali 'Imran (Ayat 1–92)",
    ],
    'Juz 4' => [
        "Juz 4 • Ali 'Imran (Ayat 93–200)",
        "Juz 4 • An-Nisa' (Ayat 1–23)",
    ],
    'Juz 30' => $label_juz30_target,
];
$urutan_opsi_target = [];
foreach ($kurikulum_target as $daftar_opsi) {
    foreach ($daftar_opsi as $label_opsi) {
        $urutan_opsi_target[] = $label_opsi;
    }
}
$target_fokus_id = 0;
$target_old = [
    'surah_awal'    => '',
    'surah_akhir'   => '',
    'tanggal_mulai' => '',
    'tanggal_akhir' => '',
];

// ─────────────────────────────────────────────────────────────
// PROSES AKSI POST
// ─────────────────────────────────────────────────────────────
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $action = $_POST['action'] ?? '';

    // 1. SIMPAN SETORAN HAFALAN BARU (EKSLUSIF SATU SURAH)
    if ($action === 'simpan_setoran' && $pdo) {
        $santri_nama = htmlspecialchars(trim($_POST['santri_nama'] ?? ''));
        $surah       = htmlspecialchars(trim($_POST['surah'] ?? ''));
        $ayat_awal   = (int)($_POST['ayat_awal'] ?? 1);
        $ayat_akhir  = (int)($_POST['ayat_akhir'] ?? 1);
        $kualitas    = htmlspecialchars(trim($_POST['kualitas'] ?? 'Mumtaz (A)'));
        // Nilai default otomatis berdasarkan kualitas untuk integritas kolom database MySQL
        $nilai_map   = [
            'Mumtaz (A)'       => 95,
            'Jayyid Jiddan (B)'=> 85,
            'Jayyid (C)'       => 75,
            'Perlu Penguatan'  => 65
        ];
        $nilai       = (int)($_POST['nilai'] ?? ($nilai_map[$kualitas] ?? 90));
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
                $notif_sukses = "Alhamdulillah! Setoran santri {$santri_nama} untuk Surah {$surah} (Ayat {$ayat_awal}{$ayat_akhir}) berhasil dicatat!";
            } catch (Throwable $e) {
                $notif_error = "Gagal menyimpan setoran: " . $e->getMessage();
            }
        }
        $page_tab = 'hafalan';
        if (!empty($santri_nama)) {
            $_GET['santri'] = $santri_nama;
        }
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

// 3. TETAPKAN TARGET HAFALAN (Juz / Surah → tabel target_hafalan)
    elseif ($action === 'simpan_target') {
        $siswa_id      = (int)($_POST['siswa_id'] ?? 0);
        $surah_awal    = trim($_POST['surah_awal'] ?? '');
        $surah_akhir   = trim($_POST['surah_akhir'] ?? '');
        $tanggal_mulai = trim($_POST['tanggal_mulai'] ?? '');
        $tanggal_akhir = trim($_POST['tanggal_akhir'] ?? '');
        $target_fokus_id = $siswa_id;
        $target_old = [
            'surah_awal'    => $surah_awal,
            'surah_akhir'   => $surah_akhir,
            'tanggal_mulai' => $tanggal_mulai,
            'tanggal_akhir' => $tanggal_akhir,
        ];

        $idx_awal  = array_search($surah_awal, $urutan_opsi_target, true);
        $idx_akhir = array_search($surah_akhir, $urutan_opsi_target, true);

        if (!$pdo) {
            $notif_error = 'Koneksi database tidak tersedia. Target belum disimpan.';
        } elseif ($siswa_id <= 0 || $surah_awal === '' || $surah_akhir === '' || $tanggal_mulai === '' || $tanggal_akhir === '') {
            $notif_error = 'Lengkapi siswa, rentang hafalan, dan tanggal target.';
        } elseif ($idx_awal === false || $idx_akhir === false) {
            $notif_error = 'Pilihan juz atau surah tidak valid.';
        } elseif ($idx_akhir < $idx_awal) {
            $notif_error = 'Batas akhir hafalan tidak boleh lebih kecil dari mulai hafalan.';
        } elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal_mulai) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal_akhir)) {
            $notif_error = 'Format tanggal tidak valid.';
        } elseif ($tanggal_akhir < $tanggal_mulai) {
            $notif_error = 'Tanggal akhir tidak boleh lebih awal dari tanggal mulai.';
        } else {
            try {
                $cek = $pdo->prepare("SELECT id, nama_lengkap FROM users WHERE id = :id AND role = 'siswa' LIMIT 1");
                $cek->execute(['id' => $siswa_id]);
                $siswa_row = $cek->fetch();
                if (!$siswa_row) {
                    $notif_error = 'Siswa yang dipilih tidak ditemukan di database.';
                } else {
                    $pdo->beginTransaction();
                    $nonaktif = $pdo->prepare("UPDATE target_hafalan SET status = 'Nonaktif' WHERE siswa_id = :id AND status = 'Aktif'");
                    $nonaktif->execute(['id' => $siswa_id]);
                    $simpan = $pdo->prepare("INSERT INTO target_hafalan (siswa_id, ustadz_id, surah_awal, surah_akhir, tanggal_mulai, tanggal_akhir, status)
                                             VALUES (:siswa_id, :ustadz_id, :surah_awal, :surah_akhir, :tanggal_mulai, :tanggal_akhir, 'Aktif')");
                    $simpan->execute([
                        'siswa_id'      => $siswa_id,
                        'ustadz_id'     => (int)($_SESSION['user_id'] ?? $ustadz_id),
                        'surah_awal'    => $surah_awal,
                        'surah_akhir'   => $surah_akhir,
                        'tanggal_mulai' => $tanggal_mulai,
                        'tanggal_akhir' => $tanggal_akhir,
                    ]);
                    $pdo->commit();
                    $notif_sukses = 'Target hafalan ' . $siswa_row['nama_lengkap'] . ' berhasil disimpan.';
                    $target_old = [
                        'surah_awal'    => '',
                        'surah_akhir'   => '',
                        'tanggal_mulai' => '',
                        'tanggal_akhir' => '',
                    ];
                }
            } catch (Throwable $e) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                $notif_error = 'Gagal menyimpan target: ' . $e->getMessage();
            }
        }
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

    // 5. UPDATE NOMOR KONTAK USTADZ
    elseif ($action === 'update_biodata') {
        $kontak_baru = trim($_POST['kontak'] ?? ($_POST['no_hp'] ?? ''));
        $kontak_clean = preg_replace('/[^0-9\+\-\s]/', '', $kontak_baru);

        $ustadz_kontak = $kontak_clean;
        $_SESSION['user_no_hp'] = $kontak_clean;

        if ($pdo) {
            try {
                $uid = $_SESSION['user_id'] ?? 2;
                // Pastikan kolom no_hp ada
                try {
                    $colCheck = $pdo->query("SHOW COLUMNS FROM users LIKE 'no_hp'")->fetchAll();
                    if (empty($colCheck)) {
                        $pdo->exec("ALTER TABLE users ADD COLUMN no_hp VARCHAR(20) NULL AFTER nama_lengkap");
                    }
                } catch (Throwable $e) {}

                $stmt = $pdo->prepare("UPDATE users SET no_hp = :no_hp WHERE id = :id");
                $stmt->execute(['no_hp' => $kontak_clean, 'id' => $uid]);
                $notif_sukses = "Nomor kontak berhasil diperbarui.";
            } catch (Throwable $e) {
                $notif_error = "Gagal memperbarui nomor kontak: " . $e->getMessage();
            }
        } else {
            $notif_sukses = "Nomor kontak berhasil diperbarui.";
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
        ['id' => 3,  'username' => 'siswa',  'nama_lengkap' => 'Muhammad Fadhlan',       'nip_nis' => '102601', 'halaqah' => 'Al-Furqan'],
        ['id' => 4,  'username' => '102602', 'nama_lengkap' => 'Ahmad Zaid',              'nip_nis' => '102602', 'halaqah' => 'Al-Furqan'],
        ['id' => 5,  'username' => '102603', 'nama_lengkap' => 'Radit Gilang Ramadhan S', 'nip_nis' => '102603', 'halaqah' => 'Al-Furqan'],
        ['id' => 6,  'username' => '102604', 'nama_lengkap' => 'M Hafizh Habibillah',    'nip_nis' => '102604', 'halaqah' => 'Al-Furqan'],
        ['id' => 7,  'username' => '102605', 'nama_lengkap' => 'Khadijah Azzahra',       'nip_nis' => '102605', 'halaqah' => 'Al-Furqan'],
        ['id' => 8,  'username' => '102606', 'nama_lengkap' => 'Zaid Al-Ghifari',        'nip_nis' => '102606', 'halaqah' => 'Al-Furqan'],
        ['id' => 9,  'username' => '102607', 'nama_lengkap' => 'Aisyah Humaira',         'nip_nis' => '102607', 'halaqah' => 'Al-Furqan'],
        ['id' => 10, 'username' => '102608', 'nama_lengkap' => 'Abdullah Hasan',          'nip_nis' => '102608', 'halaqah' => 'Al-Furqan'],
        ['id' => 11, 'username' => '102609', 'nama_lengkap' => 'Fatimah Zahra',           'nip_nis' => '102609', 'halaqah' => 'Al-Furqan'],
        ['id' => 12, 'username' => '102610', 'nama_lengkap' => 'Muhammad Farhan',         'nip_nis' => '102610', 'halaqah' => 'Al-Furqan'],
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

    // Ambil ringkasan progres hafalan per santri & surah (status Selesai, Sedang Berjalan, Belum Setor)
    $santri_setoran_progres = [];
    try {
        $stmt_sp = $pdo->query("SELECT nama_siswa, surah, MIN(ayat_awal) as min_ayat, MAX(ayat_akhir) as max_ayat, COUNT(*) as jml_setor FROM setoran_hafalan GROUP BY nama_siswa, surah");
        while ($sp = $stmt_sp->fetch(PDO::FETCH_ASSOC)) {
            $s_nama = trim($sp['nama_siswa']);
            $s_surah = preg_replace('/[^a-z0-9]/', '', strtolower(trim($sp['surah'])));
            $santri_setoran_progres[$s_nama][$s_surah] = [
                'min_ayat'   => (int)$sp['min_ayat'],
                'max_ayat'   => (int)$sp['max_ayat'],
                'jml'        => (int)$sp['jml_setor'],
                'surah_asli' => $sp['surah']
            ];
        }
    } catch (Throwable $e) {}
}

$target_aktif_map = [];
if ($pdo) {
    try {
        $stmt_target = $pdo->query("SELECT siswa_id, surah_awal, surah_akhir, tanggal_mulai, tanggal_akhir, status FROM target_hafalan WHERE status = 'Aktif' ORDER BY id DESC");
        foreach ($stmt_target->fetchAll() as $baris_target) {
            $kunci_siswa = (string)$baris_target['siswa_id'];
            if (!isset($target_aktif_map[$kunci_siswa])) {
                $target_aktif_map[$kunci_siswa] = $baris_target;
            }
        }
    } catch (Throwable $e) {}
}

$target_aktif_map = [];
if ($pdo) {
    try {
        $stmt_target = $pdo->query("SELECT siswa_id, surah_awal, surah_akhir, tanggal_mulai, tanggal_akhir, status FROM target_hafalan WHERE status = 'Aktif' ORDER BY id DESC");
        foreach ($stmt_target->fetchAll() as $baris_target) {
            $kunci_siswa = (string)$baris_target['siswa_id'];
            if (!isset($target_aktif_map[$kunci_siswa])) {
                $target_aktif_map[$kunci_siswa] = $baris_target;
            }
        }
    } catch (Throwable $e) {}
}

// Hitung Statistik Halaqah
$total_santri_binaan = count($santri_list);
$total_setoran_count = count($riwayat_setoran);
$total_murajaah_count = count($riwayat_murajaah);

// Data Kamus Al-Qur'an per Juz (Juz 30, 29, 28)
$quran_juz = [
    '30' => [
        'nama' => 'Juz 30 (An-Naba  An-Nas)',
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
        'nama' => 'Juz 29 (Al-Mulk  Al-Mursalat)',
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
        'nama' => 'Juz 28 (Al-Mujadilah  At-Tahrim)',
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
    <title>Ruang Asatidz  SIM Tahfiz SMA IT Al-Fadhilah</title>
    
    <link rel="icon" type="image/png" href="../../assets/images/logo-tahfiz-baru.png">
    
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
        .ustadz-sidebar::-webkit-scrollbar {
            display: none;
        }
        .ustadz-content-wrapper {
            margin-left: 250px;
            width: calc(100% - 250px);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            background-color: #F6F7F5;
        }
        .ustadz-nav-link {
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
        .ustadz-nav-link i {
            width: 20px;
            height: 20px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            font-size: 16px;
        }
        .ustadz-nav-link:hover {
            color: #ffffff;
            background: rgba(255, 255, 255, 0.10);
        }
        .ustadz-nav-link.active {
            background: rgba(255, 255, 255, 0.20);
            color: #ffffff;
            font-weight: 600;
            border: 1px solid rgba(255, 255, 255, 0.28);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.10);
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
background: #E6F4F1;
            border-left: 5px solid #11756D;
        }
        .santri-avatar-initial {
            width: 38px;
            height: 38px;
            min-width: 38px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.82rem;
            font-weight: 700;
            background: #F1F5F9;
            color: #475569;
            border: 1px solid #E2E8F0;
            transition: all 0.2s ease;
        }
        .santri-select-card.active .santri-avatar-initial {
            background: #11756D;
            color: #ffffff;
            border-color: #11756D;
        }
        .row-surah-clickable {
            transition: background-color 0.15s ease;
        }
        .row-surah-clickable:hover {
            background-color: #F8FAFC !important;
        }
        .row-surah-clickable:hover .btn-tahfiz {
            transform: scale(1.08);
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

/* ── Sidebar Brand Logo (seragam dengan Admin) ── */
        .ustadz-sidebar .sidebar-brand {
            padding: 20px 18px 16px !important;
            border-bottom: 1px solid rgba(255, 255, 255, 0.14) !important;
            display: flex !important;
            align-items: center !important;
            gap: 12px !important;
        }
        .ustadz-sidebar .sidebar-brand-logo {
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
        .ustadz-sidebar .sidebar-brand-logo img {
            width: 32px !important;
            height: 32px !important;
            max-width: 32px !important;
            max-height: 32px !important;
            object-fit: contain !important;
            object-position: center !important;
            display: block !important;
        }
        .ustadz-sidebar .sidebar-brand-title {
            font-size: 15px !important;
            font-weight: 700 !important;
            color: #FACC15 !important;
            line-height: 1.2 !important;
        }
        .ustadz-sidebar .sidebar-brand-subtitle {
            font-size: 12px !important;
            color: rgba(255, 255, 255, 0.85) !important;
            font-weight: 500 !important;
            line-height: 1.2 !important;
            margin-top: 2px !important;
        }
        .target-panel {
            background: #FFFFFF;
            border: 1px solid #E2E8F0;
            border-radius: 16px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.04);
        }
        .target-siswa-card {
            width: 100%;
            display: flex;
            align-items: center;
            gap: 12px;
            text-align: left;
            border: 1.5px solid #E2E8F0;
            border-radius: 12px;
            background: #FFFFFF;
            padding: 12px 14px;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .target-siswa-card:hover {
            border-color: #11756D;
            background: #F8FFFD;
transform: translateX(2px);
        }
        .target-siswa-card.aktif {
            border-color: #11756D !important;
            background: #F0FDF9 !important;
border-left: 5px solid #11756D !important;
        }
        .target-field {
            height: 44px;
            border-radius: 10px;
            border: 1px solid #E2E8F0;
            background-color: #F8FAFC;
            font-size: 13.5px;
            color: #1E293B;
            transition: border-color 0.15s ease;
        }
        .target-field:focus {
            border-color: #11756D;
            outline: none;
            box-shadow: 0 0 0 3px rgba(17, 117, 109, 0.15);
            background-color: #FFFFFF;
        }
        .target-select {
            appearance: none;
            -webkit-appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8' viewBox='0 0 12 8'%3E%3Cpath fill='%2364748B' d='M1.4.6 6 5.2 10.6.6 12 2l-6 6-6-6z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 14px center;
            padding-right: 38px;
        }
        .btn-target-simpan {
            height: 44px;
            background-color: #11756D;
            color: #FFFFFF;
            border: none;
            border-radius: 10px;
            font-weight: 700;
            font-size: 14px;
            padding: 0 24px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: all 0.2s ease;
        }
        .btn-target-simpan:hover {
            background-color: #0D5C56;
            color: #FFFFFF;
            box-shadow: 0 4px 12px rgba(17, 117, 109, 0.25);
        }
        .btn-target-batal {
            height: 44px;
            background-color: #E2E8F0;
            color: #EF4444;
            border: none;
            border-radius: 10px;
            font-weight: 700;
            font-size: 14px;
            padding: 0 24px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            text-decoration: none;
            transition: all 0.2s ease;
        }
        .btn-target-batal:hover {
            background-color: #CBD5E1;
            color: #DC2626;
        }
.preset-pill-btn {
            border: 1.5px solid #CBD5E1;
            background: #FFFFFF;
            color: #334155;
            font-size: 12.5px;
            font-weight: 600;
            padding: 7px 14px;
            border-radius: 20px;
            cursor: pointer;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .preset-pill-btn:hover {
            border-color: #11756D;
            background: #F0FDF9;
            color: #11756D;
            transform: translateY(-1px);
        }
        .preset-pill-btn.active {
            border-color: #11756D;
            background: #11756D;
            color: #FFFFFF;
            box-shadow: 0 2px 8px rgba(17, 117, 109, 0.25);
        }
        .duration-pill-btn {
            border: 1px solid #E2E8F0;
            background: #F8FAFC;
            color: #475569;
            font-size: 11.5px;
            font-weight: 600;
            padding: 4px 10px;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.15s ease;
        }
        .duration-pill-btn:hover, .duration-pill-btn.active {
            border-color: #11756D;
            background: #E6F4F1;
            color: #11756D;
        }
        .visual-route-card {
            background: linear-gradient(135deg, #F0FDF4 0%, #ECFDF5 100%);
            border: 1.5px solid #86EFAC;
            border-radius: 14px;
            padding: 16px 20px;
            position: relative;
        }
    </style>
</head>
<body>

<div class="d-flex">
    <!-- -----------------------------------------------------------
         SIDEBAR UTAMA ASATIDZ (SEMUA MENU KEMBALI LENGKAP)
════════════════════════════════════════════════════════════ -->
    <aside class="ustadz-sidebar d-flex flex-column" style="width: 250px; height: 100vh; position: fixed; top: 0; left: 0; bottom: 0; background: linear-gradient(180deg, #11756D 0%, #0E625B 100%) !important; padding: 0; z-index: 1000; overflow-y: auto; scrollbar-width: none; -ms-overflow-style: none;">
        <!-- Brand Header -->
        <div class="sidebar-brand" style="padding: 20px 18px 16px; border-bottom: 1px solid rgba(255, 255, 255, 0.14); display: flex; align-items: center; gap: 12px;">
            <div class="sidebar-brand-logo">
                <img src="../../assets/images/logo-tahfiz-baru.png" alt="Logo Tahfiz">
            </div>
            <div class="overflow-hidden">
                <div class="sidebar-brand-title text-truncate" style="font-size: 15px; font-weight: 700; color: #FACC15; line-height: 1.2;">Tahfiz Yuk</div>
                <div class="sidebar-brand-subtitle text-truncate" style="font-size: 12px; color: rgba(255, 255, 255, 0.85); font-weight: 500; line-height: 1.2; margin-top: 2px;">Sistem monitoring tahfiz</div>
            </div>
        </div>

        <!-- Info Pengguna -->
        <div class="ustadz-user-info" style="margin: 14px 12px; padding: 10px 12px; background: rgba(255, 255, 255, 0.10); border: 1px solid rgba(255, 255, 255, 0.16); border-radius: 12px; display: flex; align-items: center; gap: 12px;">
            <div class="ustadz-user-avatar" style="width: 38px; height: 38px; min-width: 38px; border-radius: 50%; display: flex; align-items: center; justify-content: center; text-align: center; font-weight: 700; font-size: 13px; color: #FFFFFF; background: #0B4F49; flex-shrink: 0;">
                RA
            </div>
            <div class="ustadz-user-text overflow-hidden" style="min-width: 0;">
                <div class="ustadz-user-role text-white fw-semibold text-truncate" style="font-size: 13px; line-height: 1.3;"><?= htmlspecialchars($ustadz_name) ?></div>
<div class="ustadz-user-name fw-semibold text-truncate" style="font-size: 11px; color: #A7F3D0; line-height: 1.2;">● Halaqah <?= htmlspecialchars($halaqah_name) ?></div>
            </div>
        </div>

        <!-- Menu Navigasi -->
        <nav class="ustadz-nav flex-grow-1">
            <div class="ustadz-nav-label text-uppercase fw-bold d-block" style="padding: 10px 20px 6px; font-size: 11px; letter-spacing: 0.08em; color: rgba(255, 255, 255, 0.70);">Menu Utama</div>

            <a href="?tab=profil" class="ustadz-nav-link <?= $page_tab === 'profil' ? 'active' : '' ?>">
                <i class="bi bi-person-circle"></i><span>Profil Saya</span>
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
        <div class="ustadz-sidebar-footer" style="margin-top: auto; padding: 16px 14px 20px; border-top: 1px solid rgba(255, 255, 255, 0.14); display: flex; flex-direction: column;">
            <a href="../auth/logout.php" class="btn-sidebar-logout">
                <i class="bi bi-box-arrow-right me-1"></i>Keluar Sesi
            </a>
        </div>
    </aside>

    <!-- -----------------------------------------------------------
         MAIN CONTENT AREA
════════════════════════════════════════════════════════════ -->
    <div class="ustadz-content-wrapper">
        
        <!-- Top Bar Header -->
        <header class="bg-white border-bottom px-4 py-3 d-flex justify-content-between align-items-center shadow-sm">
            <div class="d-flex align-items-center gap-3">
                <img src="../../assets/images/logo-tahfiz-baru.png" alt="Logo Tahfiz" class="topbar-logo" style="height: 24px; width: auto; object-fit: contain;">
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
                            'rekap'    => 'Rekap Tahfiz',
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

            <!-- -----------------------------------------------------------
                 MENU 1: DASHBOARD SISWA BINAAN (OVERVIEW HALAQAH)
            ------------------------------------------------------------ -->
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
                            <small class="text-muted">Halaqah <?= htmlspecialchars($halaqah_name) ?>  Aktif</small>
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
                                    <th>Kualitas</th>
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
                                    <td><span class="badge bg-success-subtle text-success fw-bold">Mumtaz (A)</span></td>
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
                 MENU 2: CATAT HAFALAN BARU (ALUR PILIH SISWA -> DAFTAR SURAH TARGET -> POP-UP LEMBAR SETORAN)
            ════════════════════════════════════════════════════════════ -->
            <?php elseif ($page_tab === 'hafalan'): ?>
                <div class="mb-4">
                    <span class="badge px-3 py-1 text-white mb-2 fw-bold" style="background-color: #11756D;">FORMULIR SETORAN HAFALAN</span>
                    <h2 class="fw-bold mb-1" style="color: #11756D;">Pencatatan Hafalan Baru</h2>
                    <p class="text-muted small mb-0">Pilih santri terlebih dahulu di kolom kiri, lalu klik baris surah atau tombol tambah untuk membuka lembar setoran.</p>
                </div>

                <div class="row g-4">
                    <!-- KOLOM KIRI: PILIH SISWA (SESUAI FOTO 1) -->
                    <div class="col-12 col-lg-4">
                        <div class="card p-3 border-0 shadow-sm rounded-4 bg-white h-100">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <h6 class="fw-bold text-dark mb-0">
                                    <i class="bi bi-people-fill text-success me-2"></i>Pilih Siswa
                                </h6>
                                <span class="badge bg-light text-muted border"><?= count($santri_list) ?> Santri</span>
                            </div>
                            <input type="text" id="cariSantriHafalan" class="form-control form-control-sm mb-3 rounded-3" placeholder="Cari nama atau NIS...">
                            
                            <div class="d-flex flex-column gap-2 overflow-y-auto" style="max-height: 520px;" id="listSantriHafalan">
                                <?php foreach ($santri_list as $s): 
                                    $nama_s = trim($s['nama_lengkap']);
                                    $words = preg_split("/\s+/", $nama_s);
                                    $inisial = '';
                                    if (!empty($words[0])) $inisial .= mb_strtoupper(mb_substr($words[0], 0, 1));
                                    if (!empty($words[1])) $inisial .= mb_strtoupper(mb_substr($words[1], 0, 1));
                                    if (empty($inisial)) $inisial = 'ST';
                                ?>
                                    <div class="santri-select-card d-flex align-items-center gap-3" onclick="pilihSantriUntukHafalan('<?= htmlspecialchars($s['nama_lengkap'], ENT_QUOTES) ?>', '<?= htmlspecialchars($s['nip_nis'], ENT_QUOTES) ?>', this)">
                                        <div class="santri-avatar-initial">
                                            <?= $inisial ?>
                                        </div>
                                        <div class="flex-grow-1 overflow-hidden">
                                            <div class="fw-bold text-dark santri-nama-item text-truncate"><?= htmlspecialchars($s['nama_lengkap']) ?></div>
                                            <div class="small text-muted" style="font-size: 0.75rem;">NIS: <?= htmlspecialchars($s['nip_nis'] ?: '-') ?></div>
                                        </div>
                                        <i class="bi bi-chevron-right text-muted small"></i>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </section>
                </div>

<!-- KOLOM KANAN: DAFTAR SURAH TARGET HAFALAN (SESUAI FOTO 1) -->
                    <div class="col-12 col-lg-8">
                        <div class="card p-4 border-0 shadow-sm rounded-4 bg-white h-100">
                            
                            <!-- Placeholder jika siswa belum dipilih -->
                            <div id="placeholderHafalan" class="text-center py-5 my-auto text-muted">
                                <div class="rounded-circle d-inline-flex align-items-center justify-content-center p-3 mb-3" style="width: 64px; height: 64px; background: #E6F4F1; color: #11756D;">
                                    <i class="bi bi-person-lines-fill fs-2"></i>
                                </div>
                                <h5 class="fw-bold text-dark mb-1">Silakan Pilih Siswa Terlebih Dahulu</h5>
                                <p class="small text-muted mb-0">Klik salah satu nama santri pada daftar di sebelah kiri untuk menampilkan daftar target surah dan status setorannya.</p>
                            </div>

                            <!-- Area Daftar Target Surah (Aktif setelah santri dipilih) -->
                            <div id="areaFormHafalan" style="display: none;">
                                
                                <!-- Header Ringkasan & Filter Target Juz Sesuai Foto 1 -->
                                <div class="d-flex flex-wrap justify-content-between align-items-center pb-3 mb-3 border-bottom gap-2">
                                    <div>
                                        <h5 class="fw-bold mb-1 text-dark">Daftar Surah Target Hafalan</h5>
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="small text-muted">Target Aktif:</span>
                                            <select class="form-select form-select-sm fw-bold border-0 bg-light text-dark py-0.5 px-2" style="width: auto; cursor: pointer;" id="selectJuzHafalan" onchange="gantiJuzHafalan(this.value)">
                                                <option value="30">Juz 30 (An-Naba – An-Nas) — 37 Surah</option>
                                                <option value="30_dhuha" selected>Juz 30 (Adh-Dhuha – An-Nas) — 22 Surah</option>
                                                <option value="29">Juz 29 (Al-Mulk – Al-Mursalat) — 11 Surah</option>
                                                <option value="28">Juz 28 (Al-Mujadilah – At-Tahrim) — 9 Surah</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 fw-semibold rounded-pill" style="font-size: 0.85rem;">
                                            <i class="bi bi-person-fill me-1"></i>Santri: <span id="displayNamaSantriHafalan" class="fw-bold">Muhammad Fadhlan</span>
                                        </span>
                                    </div>
                                </div>

                                <!-- Bar Keterangan Status & Hint Pop-up -->
                                <div class="d-flex flex-wrap align-items-center justify-content-between p-2.5 px-3 rounded-3 mb-3 gap-2" style="background-color: #F8FAFC; border: 1px dashed #CBD5E1;">
                                    <div class="small text-muted">
                                        <i class="bi bi-info-circle text-success me-1"></i>
                                        Klik baris surah atau tombol <strong><i class="bi bi-plus-lg text-success"></i></strong> untuk membuka <strong>Lembar Setoran Eksklusif (Pop-Up)</strong>.
                                    </div>
                                    <div class="d-flex align-items-center gap-2 small">
                                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1">Selesai</span>
                                        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-2.5 py-1">Sedang Berjalan</span>
                                        <span class="badge bg-light text-secondary border rounded-pill px-2.5 py-1">Belum Setor</span>
                                    </div>
                                </div>

                                <!-- Tabel Daftar Surah Target Sesuai Foto 1 -->
                                <div class="table-responsive rounded-3 border" style="max-height: 480px; overflow-y: auto;">
                                    <table class="table table-hover align-middle mb-0" id="tabelSurahHafalan">
                                        <thead class="table-light sticky-top" style="z-index: 1;">
                                            <tr class="small text-muted text-uppercase" style="letter-spacing: 0.5px; font-size: 0.72rem;">
                                                <th class="ps-3 py-2.5" style="width: 50px;">No</th>
                                                <th class="py-2.5">Nama Surah</th>
                                                <th class="py-2.5" style="width: 140px;">Target Ayat</th>
                                                <th class="py-2.5 text-center" style="width: 180px;">Status</th>
                                                <th class="pe-3 py-2.5 text-center" style="width: 80px;">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody id="tbodySurahHafalan">
                                            <!-- Diisi secara dinamis melalui JavaScript sesuai santri & juz -->
                                        </tbody>
                                    </table>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>

                <!-- ═══════════════════════════════════════════════════════════
                     MODAL POP-UP: LEMBAR SETORAN EKSKLUSIF (SESUAI PERMINTAAN USER)
                ════════════════════════════════════════════════════════════ -->
                <div class="modal fade" id="modalSetoranEksklusif" tabindex="-1" aria-labelledby="modalSetoranEksklusifLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered modal-lg">
                        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                            <!-- Modal Header -->
                            <div class="modal-header border-0 pb-0 pt-4 px-4 align-items-start" style="background: linear-gradient(135deg, #0A2E2B 0%, #11756D 100%); color: #ffffff;">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="rounded-3 p-2.5 d-flex align-items-center justify-content-center" style="background: rgba(255,255,255,0.15); border: 1px solid rgba(255,255,255,0.25); width: 48px; height: 48px;">
                                        <i class="bi bi-journal-bookmark-fill fs-3 text-warning"></i>
                                    </div>
                                    <div>
                                        <span class="badge bg-warning text-dark fw-bold px-2 py-0.5 mb-1" style="font-size: 0.7rem; letter-spacing: 0.5px;">LEMBAR SETORAN EKSKLUSIF</span>
                                        <h4 class="fw-bold mb-0 text-white" id="modalJudulSurah">Surah Adh-Dhuha</h4>
                                        <div class="small text-white-50 mt-0.5" id="modalSubInfo">Target: Ayat 1 s/d 11</div>
                                    </div>
                                </div>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Tutup"></button>
                            </div>

                            <!-- Modal Body -->
                            <div class="modal-body p-4 pt-3">
                                <!-- Card Ringkasan Santri -->
                                <div class="d-flex flex-wrap align-items-center justify-content-between p-3 rounded-3 mb-3" style="background-color: var(--color-tahfiz-light); border: 1px solid #80C8C4;">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold" style="width: 38px; height: 38px; background: #11756D; font-size: 0.85rem;" id="modalAvatarSantri">
                                            MF
                                        </div>
                                        <div>
                                            <small class="text-muted d-block" style="font-size: 0.72rem;">Santri yang Menyetor:</small>
                                            <h6 class="fw-bold mb-0 text-dark" id="modalNamaSantri">Muhammad Fadhlan</h6>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center gap-2 mt-2 mt-sm-0">
                                        <span class="badge bg-white text-dark border px-2.5 py-1.5 fw-semibold" id="modalNisSantri">NIS: 102601</span>
                                        <span class="badge rounded-pill text-white px-3 py-1.5" style="background-color: #11756D;" id="modalBadgeTotalAyat">Total 11 Ayat</span>
                                    </div>
                                </div>

                                <!-- Alert Status Progres Surah (Dinamis via JS: Sedang Berjalan / Selesai / Belum Setor) -->
                                <div id="modalAlertStatus" class="mb-3"></div>

                                <!-- Formulir Lembar Setoran -->
                                <form action="?tab=hafalan" method="POST" id="formSetoranModal">
                                    <input type="hidden" name="action" value="simpan_setoran">
                                    <input type="hidden" name="santri_nama" id="modalInputSantriNama">
                                    <input type="hidden" name="surah" id="modalInputSurahNama">

                                    <div class="row g-3 mb-3">
                                        <div class="col-12 col-md-6">
                                            <label class="form-label small fw-bold text-dark mb-1">
                                                Dari Ayat <span class="text-danger">*</span>
                                            </label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-light text-muted small">Ayat</span>
                                                <input type="number" name="ayat_awal" id="modalInputAyatAwal" class="form-control fw-bold" value="1" min="1" required>
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <label class="form-label small fw-bold text-dark mb-1">
                                                Sampai Ayat <span class="text-danger">*</span>
                                            </label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-light text-muted small">s/d</span>
                                                <input type="number" name="ayat_akhir" id="modalInputAyatAkhir" class="form-control fw-bold text-success" value="11" min="1" required>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <small class="text-muted" style="font-size: 0.74rem;">
                                                <i class="bi bi-info-circle me-1"></i>Aturan: Kertas setoran terkunci khusus surah ini (1 lembar = 1 surah). Jika menyetor surah berbeda, dicatat di lembar surah bersangkutan.
                                            </small>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label small fw-bold text-dark mb-1">Kualitas Hafalan</label>
                                        <select name="kualitas" id="modalSelectKualitas" class="form-select py-2">
                                            <option value="Mumtaz (A)" selected>Mumtaz / Sangat Lancar (A)</option>
                                            <option value="Jayyid Jiddan (B)">Jayyid Jiddan / Lancar (B)</option>
                                            <option value="Jayyid (C)">Jayyid / Cukup (C)</option>
                                            <option value="Perlu Penguatan">Perlu Pengulangan / Remedi</option>
                                        </select>
                                    </div>

                                    <div class="mb-4">
                                        <label class="form-label small fw-bold text-dark mb-1">Catatan Tajwid / Makhraj Ustadz</label>
                                        <textarea name="catatan" id="modalTextareaCatatan" class="form-control" rows="3" placeholder="Catatan perbaikan tajwid, panjang harakat mad, atau makhraj huruf santri..."></textarea>
                                    </div>

                                    <div class="d-flex justify-content-end gap-2 pt-2 border-top">
                                        <button type="button" class="btn btn-light border px-4 py-2 fw-semibold rounded-3" data-bs-dismiss="modal">
                                            Batal
                                        </button>
                                        <button type="submit" class="btn btn-tahfiz px-4 py-2 text-white fw-bold rounded-3 shadow-sm" id="modalBtnSubmit">
                                            <i class="bi bi-check2-circle me-1"></i>Simpan Lembar Setoran
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal fade" id="modalBacaSurah" tabindex="-1" aria-labelledby="mushafJudul" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered hafalan-modal-mushaf">
                        <div class="modal-content">
                            <div class="mushaf-header">
                                <div>
                                    <h5 id="mushafJudul"></h5>
                                    <p id="mushafSubjudul"></p>
                                </div>
                                <div class="mushaf-header-tools">
                                    <div class="mushaf-filter">
                                        <label for="filterAyatMushaf">Ayat</label>
                                        <select id="filterAyatMushaf" class="form-select"></select>
                                    </div>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                                </div>
                            </div>
                            <div class="mushaf-body" id="mushafBody"></div>
                            <div class="mushaf-footer">
                                <button type="button" class="btn-tutup-mushaf" data-bs-dismiss="modal">Tutup Mushaf</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1090;">
                    <div id="toastHafalan" class="toast" role="status" aria-live="polite" aria-atomic="true">
                        <div class="toast-body">
                            <i class="bi bi-check-circle-fill text-success"></i>
                            <span id="toastHafalanPesan"></span>
                        </div>
                    </div>
                </div>

                <script>
                (function () {
                    const MUSHAF_MOCK = {
                        'adh-dhuha': {
                            no: 93, nama: 'Adh-Dhuha', arab: 'الضحى', arti: 'Duha', jumlah: 11, golongan: 'Makkiyah',
                            ayat: [
                                { arab: '???????????', latin: 'Wa?-?u?a.', arti: 'Demi waktu duha' },
                                { arab: '??????????? ????? ??????', latin: 'Wal-laili iza saja.', arti: 'dan demi waktu malam apabila telah sunyi' },
                                { arab: '??? ????????? ??????? ????? ??????', latin: 'Ma waddaaka rabbuka wa ma qala.', arti: 'Tuhanmu tidak meninggalkan engkau dan tidak (pula) membencimu' },
                                { arab: '????????????? ?????? ????? ???? ??????????', latin: 'Wa lal-akhiratu khairul laka minal-ula.', arti: 'Dan sungguh, akhirat itu lebih baik bagimu daripada yang permulaan' },
                                { arab: '?????????? ????????? ??????? ??????????', latin: 'Wa lasaufa yu?ika rabbuka fa tar?a.', arti: 'Dan sungguh, kelak Tuhanmu pasti memberikan karunia-Nya kepadamu, sehingga engkau menjadi puas' },
                                { arab: '?????? ???????? ???????? ???????', latin: 'Alam yajidka yatiman fa awa.', arti: 'Bukankah Dia mendapatimu sebagai seorang yatim, lalu Dia melindungi(-mu)' },
                                { arab: '?????????? ??????? ????????', latin: 'Wa wajadaka ?allan fa hada.', arti: 'Dan Dia mendapatimu sebagai seorang yang bingung, lalu Dia memberikan petunjuk' },
                                { arab: '?????????? ???????? ??????????', latin: 'Wa wajadaka ailan fa aghna.', arti: 'Dan Dia mendapatimu sebagai seorang yang kekurangan, lalu Dia memberikan kecukupan' },
                                { arab: '???????? ?????????? ????? ????????', latin: 'Fa ammal-yatima fa la taqhar.', arti: 'Maka terhadap anak yatim janganlah engkau berlaku sewenang-wenang' },
                                { arab: '???????? ?????????? ????? ????????', latin: 'Wa ammas-saila fa la tanhar.', arti: 'Dan terhadap orang yang meminta-minta janganlah engkau menghardik(-nya)' },
                                { arab: '???????? ?????????? ??????? ?????????', latin: 'Wa amma binimati rabbika fa ?addi?.', arti: 'Dan terhadap nikmat Tuhanmu hendaklah engkau nyatakan (dengan bersyukur)' }
                            ]
                        },
                        'al-insyirah': {
                            no: 94, nama: 'Al-Insyirah', arab: 'الشرح', arti: 'Kelapangan', jumlah: 8, golongan: 'Makkiyah',
                            ayat: [
                                { arab: '?????? ???????? ???? ????????', latin: 'Alam nasyra? laka ?adrak.', arti: 'Bukankah Kami telah melapangkan dadamu (Muhammad)?' },
                                { arab: '??????????? ????? ????????', latin: 'Wa wa?ana anka wizrak.', arti: 'dan Kami pun telah menurunkan bebanmu darimu' },
                                { arab: '??????? ??????? ????????', latin: 'Allazi anqa?a ?ahrak.', arti: 'yang memberatkan punggungmu' },
                                { arab: '??????????? ???? ????????', latin: 'Wa rafana laka zikrak.', arti: 'dan Kami tinggikan sebutan (nama)mu bagimu' },
                                { arab: '??????? ???? ????????? ???????', latin: 'Fa inna maal-usri yusra.', arti: 'Maka sesungguhnya beserta kesulitan ada kemudahan' },
                                { arab: '????? ???? ????????? ???????', latin: 'Inna maal-usri yusra.', arti: 'sesungguhnya beserta kesulitan ada kemudahan' },
                                { arab: '??????? ???????? ????????', latin: 'Fa iza faraghta fan?ab.', arti: 'Maka apabila engkau telah selesai (dari sesuatu urusan), tetaplah bekerja keras (untuk urusan yang lain)' },
                                { arab: '???????? ??????? ????????', latin: 'Wa ila rabbika farghab.', arti: 'dan hanya kepada Tuhanmulah engkau berharap' }
                            ]
                        },
                        'at-tin': {
                            no: 95, nama: 'At-Tin', arab: 'التين', arti: 'Buah Tin', jumlah: 8, golongan: 'Makkiyah',
                            ayat: [
                                { arab: '?????????? ??????????????', latin: 'Wat-tini waz-zaitun.', arti: 'Demi (buah) Tin dan (buah) Zaitun' },
                                { arab: '??????? ????????', latin: 'Wa ?uri sinin.', arti: 'demi gunung Sinai' },
                                { arab: '???????? ????????? ??????????', latin: 'Wa hazal-baladil-amin.', arti: 'dan demi negeri (Mekah) yang aman ini' },
                                { arab: '?????? ????????? ??????????? ??? ???????? ?????????', latin: 'Laqad khalaqnal-insana fi a?sani taqwim.', arti: 'Sungguh, Kami telah menciptakan manusia dalam bentuk yang sebaik-baiknya' },
                                { arab: '????? ??????????? ???????? ??????????', latin: '?umma radadnahu asfala safilin.', arti: 'kemudian Kami kembalikan dia ke tempat yang serendah-rendahnya' },
                                { arab: '?????? ????????? ??????? ?????????? ????????????? ???????? ?????? ?????? ?????????', latin: 'Illal-lazina amanu wa amilu?-?ali?ati falahum ajrun gairu mamnun.', arti: 'kecuali orang-orang yang beriman dan mengerjakan kebajikan; maka mereka akan mendapat pahala yang tidak putus-putusnya' },
                                { arab: '????? ??????????? ?????? ??????????', latin: 'Fama yukazzibuka badu bid-din.', arti: 'Maka apa yang menyebabkan (mereka) mendustakanmu (tentang) hari pembalasan setelah (adanya keterangan-keterangan) itu?' },
                                { arab: '???????? ??????? ?????????? ?????????????', latin: 'Alaisallahu bi-a?kamil-?akimin.', arti: 'Bukankah Allah hakim yang paling adil?' }
                            ]
                        },
                        'al-alaq': {
                            no: 96, nama: "Al-'Alaq", arab: 'العلق', arti: 'Segumpal Darah', jumlah: 19, golongan: 'Makkiyah',
                            ayat: [
                                { arab: '??????? ??????? ??????? ??????? ??????', latin: 'Iqra bismi rabbikal-lazi khalaq.', arti: 'Bacalah dengan (menyebut) nama Tuhanmu yang menciptakan' },
                                { arab: '?????? ??????????? ???? ??????', latin: 'Khalaqal-insana min alaq.', arti: 'Dia telah menciptakan manusia dari segumpal darah' },
                                { arab: '??????? ????????? ???????????', latin: 'Iqra wa rabbukal-akram.', arti: 'Bacalah, dan Tuhanmulah Yang Mahamulia' },
                                { arab: '??????? ??????? ???????????', latin: 'Allazi allama bil-qalam.', arti: 'Yang mengajar (manusia) dengan pena' },
                                { arab: '??????? ??????????? ??? ???? ????????', latin: 'Allamal-insana ma lam yalam.', arti: 'Dia mengajarkan manusia apa yang tidak diketahuinya' },
                                { arab: '?????? ????? ??????????? ??????????', latin: 'Kalla innal-insana laya?ga.', arti: 'Sekali-kali tidak! Sungguh, manusia itu benar-benar melampaui batas' },
                                { arab: '??? ?????? ???????????', latin: 'Ar raahustagna.', arti: 'apabila melihat dirinya serba berkecukupan' },
                                { arab: '????? ?????? ??????? ???????????', latin: 'Inna ila rabbikar-ruja.', arti: 'Sungguh, hanya kepada Tuhanmulah kembalimu' },
                                { arab: '?????????? ??????? ????????', latin: 'Araaital-lazi yanha.', arti: 'Bagaimana pendapatmu tentang orang yang melarang' },
                                { arab: '??????? ????? ???????', latin: 'Abdan iza ?alla.', arti: 'seorang hamba ketika dia melaksanakan salat' },
                                { arab: '?????????? ??? ????? ????? ?????????', latin: 'Araaita in kana alal-huda.', arti: 'bagaimana pendapatmu jika dia (yang dilarang salat itu) berada di atas kebenaran' },
                                { arab: '???? ?????? ?????????????', latin: 'Au amara bit-taqwa.', arti: 'atau dia menyuruh bertakwa (kepada Allah)?' },
                                { arab: '?????????? ??? ??????? ???????????', latin: 'Araaita in kazzaba wa tawalla.', arti: 'Bagaimana pendapatmu jika dia (orang yang melarang) itu mendustakan dan berpaling?' },
                                { arab: '?????? ??????? ??????? ??????? ??????', latin: 'Alam yalam biannallaha yara.', arti: 'Tidakkah dia mengetahui bahwa sesungguhnya Allah melihat (segala perbuatannya)?' },
                                { arab: '?????? ????? ????? ??????? ??????????? ??????????????', latin: 'Kalla lail lam yantahi lanasfaam bin-na?iyah.', arti: 'Sekali-kali tidak! Sungguh, jika dia tidak berhenti (berbuat demikian) niscaya Kami tarik ubun-ubunnya' },
                                { arab: '????????? ????????? ?????????', latin: 'Na?iyatin kazibatin kha?iah.', arti: '(yaitu) ubun-ubun orang yang mendustakan dan durhaka' },
                                { arab: '?????????? ?????????', latin: 'Falyadu nadiyah.', arti: 'maka biarlah dia memanggil golongannya (untuk menolongnya)' },
                                { arab: '???????? ??????????????', latin: 'Sanaduz-zabaniyah.', arti: 'Kelak Kami akan memanggil Malaikat Zabaniyah' },
                                { arab: '?????? ??? ???????? ????????? ?????????? ?', latin: 'Kalla la tu?ihu wasjud waqtarib.', arti: 'Sekali-kali tidak! Janganlah kamu patuh kepadanya; dan sujudlah serta dekatkanlah (dirimu kepada Allah)' }
                            ]
                        },
                        'al-qadr': {
                            no: 97, nama: 'Al-Qadr', arab: 'القدر', arti: 'Kemuliaan', jumlah: 5, golongan: 'Makkiyah',
                            ayat: [
                                { arab: '?????? ???????????? ??? ???????? ?????????', latin: 'Inna anzalnahu fi lailatil-qadr.', arti: 'Sesungguhnya Kami telah menurunkannya (Al-Quran) pada malam qadar' },
                                { arab: '????? ????????? ??? ???????? ?????????', latin: 'Wa ma adraka ma lailatul-qadr.', arti: 'Dan tahukah kamu apakah malam kemuliaan itu?' },
                                { arab: '???????? ????????? ?????? ????? ?????? ??????', latin: 'Lailatul-qadri khairum min alfi syahr.', arti: 'Malam kemuliaan itu lebih baik daripada seribu bulan' },
                                { arab: '????????? ?????????????? ?????????? ?????? ???????? ???????? ???? ????? ??????', latin: 'Tanazzalul-malaikatu war-ru?u fiha biizni rabbihim min kulli amr.', arti: 'Pada malam itu turun para malaikat dan Ruh (Jibril) dengan izin Tuhannya untuk mengatur semua urusan' },
                                { arab: '??????? ???? ??????? ???????? ?????????', latin: 'Salamun hiya ?atta ma?lail-fajr.', arti: 'Sejahteralah (malam itu) sampai terbit fajar' }
                            ]
                        }
                    };

                    const SURAH_TARGET_AWAL = [
                        { key: 'adh-dhuha', status: 'selesai' },
                        { key: 'al-insyirah', status: 'selesai' },
                        { key: 'at-tin', status: 'berjalan' },
                        { key: 'al-alaq', status: 'belum' },
                        { key: 'al-qadr', status: 'belum' }
                    ];

                    const STATUS_TAMPILAN = {
                        selesai: { label: 'Selesai', kelas: 'hafalan-badge-selesai' },
                        berjalan: { label: 'Sedang Berjalan', kelas: 'hafalan-badge-berjalan' },
                        belum: { label: 'Belum Setor', kelas: 'hafalan-badge-belum' }
                    };

                    const hafalanStatusSiswa = {};
                    let siswaHafalanTerpilih = null;
                    let surahSetoranAktif = null;

                    function escHtml(nilai) {
                        return String(nilai)
                            .replace(/&/g, '&amp;')
                            .replace(/</g, '&lt;')
                            .replace(/>/g, '&gt;')
                            .replace(/"/g, '&quot;');
                    }

                    function pastikanState(id) {
                        if (!hafalanStatusSiswa[id]) {
                            hafalanStatusSiswa[id] = SURAH_TARGET_AWAL.map(function (item) {
                                return { key: item.key, status: item.status };
                            });
                        }
                        return hafalanStatusSiswa[id];
                    }

                    function renderTabelSurah() {
                        if (!siswaHafalanTerpilih) return;
                        const daftar = pastikanState(siswaHafalanTerpilih.id);
                        const tubuh = document.getElementById('tbodySurahTarget');
                        tubuh.innerHTML = daftar.map(function (item, indeks) {
                            const meta = MUSHAF_MOCK[item.key];
                            const status = STATUS_TAMPILAN[item.status] || STATUS_TAMPILAN.belum;
                            return '<tr>'
                                + '<td style="color:#94A3B8;font-size:13px;">' + (indeks + 1) + '.</td>'
                                + '<td><div class="hafalan-surah-cell">'
                                + '<span class="hafalan-surah-nama">' + escHtml(meta.nama) + (meta.arab && !meta.arab.includes('?') ? ' &ndash; <span class="hafalan-arab">' + meta.arab + '</span>' : '') + '</span>'
                                + '<button type="button" class="btn-baca-ayat" data-aksi="baca" data-key="' + item.key + '" aria-label="Baca Surah ' + escHtml(meta.nama) + '"><i class="bi bi-book"></i></button>'
                                + '</div></td>'
                                + '<td style="color:#64748B;font-size:13.5px;">Ayat 1\u2013' + meta.jumlah + '</td>'
                                + '<td><span class="hafalan-badge ' + status.kelas + '">' + status.label + '</span></td>'
                                + '<td class="col-aksi"><button type="button" class="btn-setoran-plus" data-aksi="setor" data-key="' + item.key + '" aria-label="Input setoran ' + escHtml(meta.nama) + '"><i class="bi bi-plus-lg"></i></button></td>'
                                + '</tr>';
                        }).join('');
                    }

                    function pilihSiswaHafalan(kartu) {
                        siswaHafalanTerpilih = {
                            id: kartu.dataset.id,
                            nama: kartu.dataset.nama,
                            nis: kartu.dataset.nis
                        };
                        document.querySelectorAll('.hafalan-siswa-card').forEach(function (el) {
                            el.classList.remove('aktif');
                        });
                        kartu.classList.add('aktif');
                        document.getElementById('hafalanEmpty').hidden = true;
                        document.getElementById('hafalanSurahArea').hidden = false;
                        document.getElementById('badgeSantriHafalan').textContent = 'Santri: ' + siswaHafalanTerpilih.nama;
                        renderTabelSurah();
                    }

                    function isiOpsiAyat(idSelect, jumlah, terpilih) {
                        const select = document.getElementById(idSelect);
                        let html = '';
                        for (let n = 1; n <= jumlah; n++) {
                            html += '<option value="' + n + '">' + n + '</option>';
                        }
                        select.innerHTML = html;
                        select.value = String(terpilih);
                    }

                    function validasiRentangAyat() {
                        const awal = parseInt(document.getElementById('inputAyatAwal').value, 10);
                        const akhir = parseInt(document.getElementById('inputAyatAkhir').value, 10);
                        const pesan = document.getElementById('hafalanAyatError');
                        if (akhir < awal) {
                            pesan.textContent = 'Ayat akhir tidak boleh lebih kecil dari ayat awal.';
                            pesan.hidden = false;
                            return false;
                        }
                        pesan.hidden = true;
                        return true;
                    }

                    function bukaSetoran(key) {
                        const meta = MUSHAF_MOCK[key];
                        if (!meta || !siswaHafalanTerpilih) return;
                        surahSetoranAktif = key;
                        document.getElementById('subjudulInputSetoran').textContent =
                            'Santri: ' + siswaHafalanTerpilih.nama + '  Surah ' + meta.nama + ' (' + meta.jumlah + ' Ayat)';
                        isiOpsiAyat('inputAyatAwal', meta.jumlah, 1);
                        isiOpsiAyat('inputAyatAkhir', meta.jumlah, meta.jumlah);
                        document.getElementById('inputKualitas').value = 'Lancar';
                        document.getElementById('inputCatatanSetoran').value = '';
                        const tanggal = document.getElementById('inputTanggalSetoran');
                        tanggal.value = tanggal.dataset.hariIni || tanggal.value;
                        document.getElementById('hafalanAyatError').hidden = true;
                        bootstrap.Modal.getOrCreateInstance(document.getElementById('modalInputSetoran')).show();
                    }

                    function bukaMushaf(key) {
                        const data = MUSHAF_MOCK[key];
                        if (!data) return;
                        document.getElementById('mushafJudul').innerHTML =
                            escHtml(data.nama) + ' - <span class="mushaf-judul-arab">' + data.arab + '</span> (QS. ' + data.no + ')';
                        document.getElementById('mushafSubjudul').textContent =
                            data.arti + '  ' + data.jumlah + ' Ayat  ' + data.golongan;

                        const select = document.getElementById('filterAyatMushaf');
                        const tubuh = document.getElementById('mushafBody');
                        let opsi = '<option value="">Pilih Ayat</option>';
                        tubuh.innerHTML = data.ayat.map(function (ayat, indeks) {
                            const nomor = indeks + 1;
                            opsi += '<option value="' + nomor + '">Ayat ' + nomor + '</option>';
                            return '<article class="mushaf-ayat" id="mushafAyat-' + nomor + '">'
                                + '<div class="mushaf-ayat-top"><span class="mushaf-no">' + nomor + '</span><p class="mushaf-arab">' + ayat.arab + '</p></div>'
                                + '<p class="mushaf-latin">' + escHtml(ayat.latin) + '</p>'
                                + '<p class="mushaf-terjemah">' + escHtml(ayat.arti) + '</p>'
                                + '<p class="mushaf-ket"><i class="bi bi-volume-up" aria-hidden="true"></i> QS. ' + escHtml(data.nama) + ' ayat ke-' + nomor + '</p>'
                                + '</article>';
                        }).join('');
                        select.innerHTML = opsi;
                        select.value = '';
                        tubuh.scrollTop = 0;
                        bootstrap.Modal.getOrCreateInstance(document.getElementById('modalBacaSurah')).show();
                    }

                    function gulirKeAyat() {
                        const nomor = document.getElementById('filterAyatMushaf').value;
                        const tubuh = document.getElementById('mushafBody');
                        tubuh.querySelectorAll('.mushaf-ayat').forEach(function (el) {
                            el.classList.remove('aktif');
                        });
                        if (!nomor) return;
                        const target = document.getElementById('mushafAyat-' + nomor);
                        if (!target) return;
                        target.classList.add('aktif');
                        tubuh.scrollTo({ top: target.offsetTop - 8, behavior: 'smooth' });
                    }

                    function simpanSetoran() {
                        if (!validasiRentangAyat() || !siswaHafalanTerpilih || !surahSetoranAktif) return;
                        const awal = parseInt(document.getElementById('inputAyatAwal').value, 10);
                        const akhir = parseInt(document.getElementById('inputAyatAkhir').value, 10);
                        const meta = MUSHAF_MOCK[surahSetoranAktif];
                        const daftar = pastikanState(siswaHafalanTerpilih.id);
                        const item = daftar.find(function (baris) { return baris.key === surahSetoranAktif; });
                        if (item && meta) {
                            item.status = (awal === 1 && akhir === meta.jumlah) ? 'selesai' : 'berjalan';
                        }
                        renderTabelSurah();
                        const modal = bootstrap.Modal.getInstance(document.getElementById('modalInputSetoran'));
                        if (modal) modal.hide();
                        document.getElementById('toastHafalanPesan').textContent =
                            'Setoran hafalan ' + siswaHafalanTerpilih.nama + ' untuk Surah ' + meta.nama + ' (Ayat ' + awal + '' + akhir + ') berhasil disimpan.';
                        bootstrap.Toast.getOrCreateInstance(document.getElementById('toastHafalan'), { delay: 3400 }).show();
                    }

                    document.getElementById('listHafalanSiswa').addEventListener('click', function (event) {
                        const kartu = event.target.closest('.hafalan-siswa-card');
                        if (kartu) pilihSiswaHafalan(kartu);
                    });

                    document.getElementById('tbodySurahTarget').addEventListener('click', function (event) {
                        const tombol = event.target.closest('[data-aksi]');
                        if (!tombol) return;
                        if (tombol.dataset.aksi === 'baca') bukaMushaf(tombol.dataset.key);
                        if (tombol.dataset.aksi === 'setor') bukaSetoran(tombol.dataset.key);
                    });

                    document.getElementById('searchHafalanSiswa').addEventListener('input', function () {
                        const kata = this.value.trim().toLowerCase();
                        document.querySelectorAll('.hafalan-siswa-card').forEach(function (kartu) {
                            const nama = (kartu.dataset.nama || '').toLowerCase();
                            const nis = (kartu.dataset.nis || '').toLowerCase();
                            kartu.style.display = (nama.includes(kata) || nis.includes(kata)) ? '' : 'none';
                        });
                    });

                    document.getElementById('inputAyatAwal').addEventListener('change', validasiRentangAyat);
                    document.getElementById('inputAyatAkhir').addEventListener('change', validasiRentangAyat);
                    document.getElementById('btnSimpanHafalan').addEventListener('click', simpanSetoran);
                    document.getElementById('filterAyatMushaf').addEventListener('change', gulirKeAyat);

                    const preset = new URLSearchParams(window.location.search).get('santri');
                    if (preset) {
                        const kartu = Array.from(document.querySelectorAll('.hafalan-siswa-card'));
                        const kunci = preset.toLowerCase();
                        const cocok = kartu.find(function (el) { return (el.dataset.nama || '').toLowerCase() === kunci; })
                            || kartu.find(function (el) { return (el.dataset.nama || '').toLowerCase().includes(kunci); });
                        if (cocok) cocok.click();
                    }
                })();
                </script>

            <!-- -----------------------------------------------------------
                 MENU 3: DASHBOARD & CATAT MURAJAAH
                 (Guru melihat total siswa murajaah, progres kelancaran, & form input)
            ------------------------------------------------------------ -->
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
                                    <input type="text" name="ayat_range" class="form-control" value="Seluruh Ayat" placeholder="Contoh: Ayat 130 atau Seluruh Ayat">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label small fw-bold text-muted">Hasil Evaluasi Kelancaran</label>
                                    <select name="evaluasi" class="form-select fw-semibold">
                                        <option value="Sangat Mutqin (Nilai 90-100)">Sangat Mutqin (Nilai 90100)</option>
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
                                                <td>Surah Al-Mulk (Ayat 130)</td>
                                                <td><span class="badge bg-success-subtle text-success">Lancar</span></td>
                                            </tr>
                                            <tr>
                                                <td class="ps-3">3 Nov</td>
                                                <td><strong>Radit Gilang Ramadhan S</strong></td>
                                                <td>Surah An-Naba' (140)</td>
                                                <td><span class="badge bg-warning-subtle text-warning">Perlu Pengulangan</span></td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

            <!-- -----------------------------------------------------------
                 MENU 4: TARGET HAFALAN SANTRI
            ------------------------------------------------------------ -->
            <?php elseif ($page_tab === 'target'): ?>
                <!-- Hero Banner Atas -->
                <div class="mb-4">
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 p-4 rounded-4 text-white position-relative overflow-hidden shadow-sm" style="background: linear-gradient(135deg, #0A2E2B 0%, #11756D 65%, #059669 100%);">
                        <div class="position-absolute end-0 top-0 bottom-0 opacity-10 d-none d-md-flex align-items-center pe-4" style="pointer-events: none;">
                            <i class="bi bi-bullseye" style="font-size: 160px; transform: rotate(15deg);"></i>
                        </div>
                        <div style="position: relative; z-index: 1;">
                            <span class="badge bg-warning text-dark fw-bold px-3 py-1 mb-2 rounded-pill shadow-xs" style="font-size: 0.72rem; letter-spacing: 0.5px;">
                                <i class="bi bi-stars me-1"></i>MODUL PENETAPAN TARGET
                            </span>
                            <h2 class="fw-bold mb-1 text-white">Target Hafalan Santri Binaan</h2>
                            <p class="text-white-50 small mb-0" style="max-width: 580px;">
                                Rencanakan sasaran juz dan surah yang terukur. Pantau tenggat waktu evaluasi dan tingkat capaian setiap santri halaqah.
                            </p>
                        </div>
                        <div class="d-flex align-items-center gap-2" style="position: relative; z-index: 1;">
                            <span class="badge bg-white bg-opacity-20 text-white px-3 py-2 rounded-3 border border-white border-opacity-25">
                                <i class="bi bi-people-fill me-1"></i><?= count($santri_list) ?> Total Santri
                            </span>
                            <span class="badge bg-warning text-dark px-3 py-2 rounded-3 fw-bold">
                                <i class="bi bi-check-circle-fill me-1"></i><?= count($target_aktif_map) ?> Memiliki Target
                            </span>
                        </div>
                    </div>
                </div>

<!-- 3 Kartu Metrik Target -->
                <div class="row g-3 mb-4">
                    <div class="col-12 col-md-4">
                        <div class="card p-3 border-0 shadow-sm rounded-4 bg-white h-100 d-flex flex-row align-items-center gap-3">
                            <div class="rounded-3 d-flex align-items-center justify-content-center text-white flex-shrink-0" style="width: 50px; height: 50px; background: linear-gradient(135deg, #11756D, #059669); font-size: 24px;">
                                <i class="bi bi-bullseye"></i>
                            </div>
                            <div>
                                <div class="text-muted small fw-semibold">Target Terencana</div>
                                <div class="fs-4 fw-bold text-dark"><?= count($target_aktif_map) ?> <span class="text-muted fs-6 fw-normal">/ <?= count($santri_list) ?> Santri</span></div>
                                <div class="small text-success fw-medium"><i class="bi bi-arrow-up-right me-1"></i><?= count($santri_list) > 0 ? round((count($target_aktif_map)/count($santri_list))*100) : 0 ?>% Santri Ditargetkan</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="card p-3 border-0 shadow-sm rounded-4 bg-white h-100 d-flex flex-row align-items-center gap-3">
                            <div class="rounded-3 d-flex align-items-center justify-content-center text-dark flex-shrink-0" style="width: 50px; height: 50px; background: linear-gradient(135deg, #FEF3C7, #FDE68A); font-size: 24px;">
                                <i class="bi bi-clock-history text-warning-emphasis"></i>
                            </div>
                            <div>
                                <div class="text-muted small fw-semibold">Siklus Evaluasi</div>
                                <div class="fs-4 fw-bold text-dark">30 Hari</div>
                                <div class="small text-warning-emphasis fw-medium"><i class="bi bi-calendar-check me-1"></i>Evaluasi Rutin Bulanan</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="card p-3 border-0 shadow-sm rounded-4 bg-white h-100 d-flex flex-row align-items-center gap-3">
                            <div class="rounded-3 d-flex align-items-center justify-content-center text-white flex-shrink-0" style="width: 50px; height: 50px; background: linear-gradient(135deg, #3B82F6, #1D4ED8); font-size: 24px;">
                                <i class="bi bi-journal-bookmark-fill"></i>
                            </div>
                            <div>
                                <div class="text-muted small fw-semibold">Fokus Halaqah</div>
                                <div class="fs-4 fw-bold text-dark">Juz 30 &amp; 29</div>
                                <div class="small text-primary fw-medium"><i class="bi bi-award me-1"></i>Halaqah <?= htmlspecialchars($halaqah_name) ?></div>
                            </div>
                        </div>
                    </div>
                </div>

<div class="row g-4 align-items-start">
                    <!-- PANEL KIRI: DAFTAR PILIH SISWA -->
                    <div class="col-12 col-lg-4">
                        <div class="target-panel p-3 bg-white">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                                    <i class="bi bi-people-fill text-success"></i>
                                    <span>Pilih Siswa</span>
                                </h6>
                                <span class="badge bg-light text-muted border"><?= count($santri_list) ?> Santri</span>
                            </div>

                            <div class="mb-3 position-relative">
                                <i class="bi bi-search position-absolute text-muted" style="top: 13px; left: 14px; font-size: 14px;"></i>
                                <input type="text" id="cariSantriTarget" class="form-control ps-5" placeholder="Cari nama atau NIS..." style="height: 42px; border-radius: 10px; background-color: #F8FAFC; border: 1px solid #E2E8F0; font-size: 13.5px;">
                            </div>

                            <div class="d-flex flex-column gap-2 overflow-y-auto" style="max-height: 560px;" id="listSantriTarget">
                                <?php foreach ($santri_list as $s): 
                                    $s_id_str = (string)$s['id'];
                                    $has_target = isset($target_aktif_map[$s_id_str]);
                                    $is_fokus = ($target_fokus_id === (int)$s['id']);

                                    $words = preg_split("/\s+/", trim($s['nama_lengkap']));
                                    $inisial = '';
                                    if (!empty($words[0])) $inisial .= mb_strtoupper(mb_substr($words[0], 0, 1));
                                    if (!empty($words[1])) $inisial .= mb_strtoupper(mb_substr($words[1], 0, 1));
                                    if (empty($inisial)) $inisial = 'ST';
                                ?>
                                    <div class="target-siswa-card <?= $is_fokus ? 'aktif' : '' ?>"
                                         data-id="<?= (int)$s['id'] ?>"
                                         data-nama="<?= htmlspecialchars($s['nama_lengkap'], ENT_QUOTES) ?>"
                                         data-nis="<?= htmlspecialchars($s['nip_nis'], ENT_QUOTES) ?>"
                                         onclick="pilihSantriTarget(<?= (int)$s['id'] ?>, '<?= htmlspecialchars($s['nama_lengkap'], ENT_QUOTES) ?>', '<?= htmlspecialchars($s['nip_nis'], ENT_QUOTES) ?>', this)">
                                        <div class="santri-avatar-initial flex-shrink-0" style="width: 42px; height: 42px; font-size: 0.88rem;">
                                            <?= $inisial ?>
                                        </div>
                                        <div class="flex-grow-1 overflow-hidden">
                                            <div class="fw-bold text-dark text-truncate santri-target-nama" style="font-size: 14px;"><?= htmlspecialchars($s['nama_lengkap']) ?></div>
                                            <div class="small text-muted text-truncate santri-target-nis" style="font-size: 12px;">NIS: <?= htmlspecialchars($s['nip_nis'] ?: '-') ?></div>
                                            <?php if ($has_target): ?>
                                                <div class="small text-success text-truncate fw-semibold mt-0.5" style="font-size: 11px;">
                                                    <i class="bi bi-bullseye me-1"></i><?= htmlspecialchars($target_aktif_map[$s_id_str]['surah_awal']) ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div>
                                            <?php if ($has_target): ?>
                                                <span class="badge rounded-pill bg-success-subtle text-success border border-success-subtle px-2.5 py-1 fw-bold" style="font-size: 10.5px;">
                                                    Aktif
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-light text-muted border rounded-pill px-2 py-1" style="font-size: 10px;">
                                                    Belum
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <!-- PANEL KANAN: FORM PENETAPAN TARGET INTERAKTIF -->
                    <div class="col-12 col-lg-8">
                        <div class="target-panel p-4 bg-white">
                            <!-- Empty State Sebelum Siswa Dipilih -->
                            <div id="targetEmptyState" class="text-center py-5 my-auto" style="<?= $target_fokus_id > 0 ? 'display: none;' : '' ?>">
                                <div class="rounded-circle d-inline-flex align-items-center justify-content-center p-3 mb-3 shadow-xs" style="width: 72px; height: 72px; background: #E6F4F1; color: #11756D;">
                                    <i class="bi bi-person-lines-fill fs-2"></i>
                                </div>
                                <h5 class="fw-bold text-dark mb-1">Silakan Pilih Siswa Terlebih Dahulu</h5>
                                <p class="text-muted small mb-0" style="max-width: 420px; margin: 0 auto;">Klik salah satu nama santri pada daftar di sebelah kiri untuk melihat target aktif atau menetapkan target hafalan baru.</p>
                            </div>

                            <!-- Form dan Detail Setelah Siswa Dipilih -->
                            <div id="targetFormArea" style="<?= $target_fokus_id > 0 ? '' : 'display: none;' ?>">
                                
                                <!-- Header Profil Santri Terpilih -->
                                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 pb-3 mb-4" style="border-bottom: 1.5px solid #E2E8F0;">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold shadow-xs" style="width: 46px; height: 46px; background: #11756D; font-size: 1rem;" id="targetHeaderAvatar">
                                            MF
                                        </div>
                                        <div>
                                            <h5 class="fw-bold mb-0 text-dark" id="targetHeaderNama">Muhammad Fadhlan</h5>
                                            <div class="small text-muted" id="targetHeaderSub">NIS: 102601 • Halaqah <?= htmlspecialchars($halaqah_name) ?></div>
                                        </div>
                                    </div>
                                    <span class="badge rounded-pill bg-success-subtle text-success border border-success-subtle px-3 py-2 fw-semibold" id="targetBadgeStatusSantri">
                                        <i class="bi bi-shield-check me-1"></i>Santri Terpilih
                                    </span>
                                </div>

                                <!-- KARTU VISUAL TARGET AKTIF (Tampil Jika Santri Sudah Punya Target) -->
                                <div class="visual-route-card mb-4" id="targetAktifSantriBox" style="display: none;">
                                    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 pb-2 border-bottom border-success-subtle gap-2">
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="badge bg-success text-white rounded-pill px-3 py-1 fw-bold" style="font-size: 11px;">
                                                <i class="bi bi-check-circle-fill me-1"></i>TARGET AKTIF SAAT INI
                                            </span>
                                            <span class="small text-success-emphasis fw-bold" id="infoTargetSantriNama">-</span>
                                        </div>
                                        <span class="badge bg-white text-success border border-success-subtle px-3 py-1.5 fw-semibold rounded-pill" id="infoTargetCountdown">
                                            <i class="bi bi-calendar-event me-1"></i><span id="infoTargetRentangTanggal">-</span>
                                        </span>
                                    </div>

                                    <div class="row align-items-center g-3 text-center text-md-start">
                                        <div class="col-12 col-md-5">
                                            <div class="p-3 rounded-3 bg-white border border-success-subtle shadow-xs h-100">
                                                <small class="text-muted d-block fw-semibold mb-1" style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px;">Batas Awal Hafalan:</small>
                                                <strong class="text-dark d-block text-truncate" id="infoTargetSurahAwal" style="font-size: 13.5px;">-</strong>
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-2 text-center">
                                            <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-success text-white shadow-xs" style="width: 38px; height: 38px;">
                                                <i class="bi bi-arrow-right-short fs-3"></i>
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-5">
                                            <div class="p-3 rounded-3 bg-white border border-success-subtle shadow-xs h-100">
                                                <small class="text-muted d-block fw-semibold mb-1" style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px;">Batas Akhir Sasaran:</small>
                                                <strong class="text-success d-block text-truncate" id="infoTargetSurahAkhir" style="font-size: 13.5px;">-</strong>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="small text-muted mt-2 pt-2 border-top border-success-subtle" style="font-size: 11.5px;">
                                        <i class="bi bi-info-circle me-1 text-success"></i>Santri sedang menjalani target ini. Anda dapat menetapkan target baru di bawah untuk memperbarui capaian sasaran.
                                    </div>
                                </div>

                                <!-- PRESET CEPAT TARGET POPULER (ANTI-MONOTON) -->
                                <div class="mb-4 p-3 rounded-3" style="background: #F8FAFC; border: 1.5px dashed #CBD5E1;">
                                    <div class="d-flex align-items-center justify-content-between mb-2.5">
                                        <label class="small fw-bold text-dark mb-0 d-flex align-items-center gap-1.5">
                                            <i class="bi bi-lightning-charge-fill text-warning fs-6"></i>
                                            Pilihan Cepat Target Populer (Klik untuk auto-isi):
                                        </label>
                                        <span class="badge bg-light text-muted border" style="font-size: 10.5px;">Auto-Fill Cepat</span>
                                    </div>
                                    <div class="d-flex flex-wrap gap-2" id="presetButtonsContainer">
                                        <button type="button" class="preset-pill-btn" onclick="applyPresetTarget('juz30_full', this)">
                                            <i class="bi bi-star-fill text-warning"></i> Juz 30 Penuh (An-Naba – An-Nas)
                                        </button>
                                        <button type="button" class="preset-pill-btn" onclick="applyPresetTarget('juz30_pendek', this)">
                                            <i class="bi bi-bookmark-check-fill text-success"></i> Juz 30 Pendek (Adh-Dhuha – An-Nas)
                                        </button>
                                        <button type="button" class="preset-pill-btn" onclick="applyPresetTarget('juz1_full', this)">
                                            <i class="bi bi-book-fill text-primary"></i> Juz 1 Penuh (Al-Fatihah – Al-Baqarah)
                                        </button>
                                        <button type="button" class="preset-pill-btn" onclick="applyPresetTarget('juz2_full', this)">
                                            <i class="bi bi-journal-text text-secondary"></i> Juz 2 Penuh (Al-Baqarah)
                                        </button>
                                    </div>
                                </div>

                                <!-- Form Grid 2 Kolom -->
                                <form action="?tab=target" method="POST" id="formTetapkanTarget" onsubmit="return validasiFormTarget(event)">
                                    <input type="hidden" name="action" value="simpan_target">
                                    <input type="hidden" name="siswa_id" id="targetInputSiswaId" value="<?= $target_fokus_id > 0 ? $target_fokus_id : '' ?>">

                                    <div class="row g-3 mb-4">
                                        <!-- Kolom 1: Mulai Hafalan -->
                                        <div class="col-12 col-md-6">
                                            <label class="form-label small fw-bold text-dark mb-1">
                                                <i class="bi bi-journal-arrow-up text-success me-1"></i>Mulai Hafalan (Batas Awal) <span class="text-danger">*</span>
                                            </label>
                                            <select name="surah_awal" id="targetSurahAwal" class="form-select target-field target-select fw-medium" required onchange="onSurahAwalChange()">
                                                <option value="" disabled <?= empty($target_old['surah_awal']) ? 'selected' : '' ?>>-- Pilih Batas Awal --</option>
                                                <?php foreach ($kurikulum_target as $juz_nama => $daftar_opsi): ?>
                                                    <optgroup label="<?= htmlspecialchars($juz_nama) ?>">
                                                        <?php foreach ($daftar_opsi as $opsi_teks): ?>
                                                            <option value="<?= htmlspecialchars($opsi_teks) ?>" <?= ($target_old['surah_awal'] === $opsi_teks) ? 'selected' : '' ?>>
                                                                <?= htmlspecialchars($opsi_teks) ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </optgroup>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>

                                        <!-- Kolom 2: Tanggal Mulai -->
                                        <div class="col-12 col-md-6">
                                            <label class="form-label small fw-bold text-dark mb-1">
                                                <i class="bi bi-calendar-event text-success me-1"></i>Tanggal Mulai <span class="text-danger">*</span>
                                            </label>
                                            <input type="date" name="tanggal_mulai" id="targetTanggalMulai" class="form-control target-field fw-medium" value="<?= htmlspecialchars($target_old['tanggal_mulai'] ?: date('Y-m-d')) ?>" required onchange="onTanggalMulaiChange()">
                                        </div>

                                        <!-- Kolom 3: Batas Akhir Hafalan -->
                                        <div class="col-12 col-md-6">
                                            <label class="form-label small fw-bold text-dark mb-1">
                                                <i class="bi bi-flag-fill text-success me-1"></i>Batas Akhir Hafalan (Sasaran) <span class="text-danger">*</span>
                                            </label>
                                            <select name="surah_akhir" id="targetSurahAkhir" class="form-select target-field target-select fw-medium" required>
                                                <option value="" disabled <?= empty($target_old['surah_akhir']) ? 'selected' : '' ?>>-- Pilih Batas Akhir --</option>
                                                <?php foreach ($kurikulum_target as $juz_nama => $daftar_opsi): ?>
                                                    <optgroup label="<?= htmlspecialchars($juz_nama) ?>">
                                                        <?php foreach ($daftar_opsi as $opsi_teks): ?>
                                                            <option value="<?= htmlspecialchars($opsi_teks) ?>" <?= ($target_old['surah_akhir'] === $opsi_teks) ? 'selected' : '' ?>>
                                                                <?= htmlspecialchars($opsi_teks) ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </optgroup>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>

                                        <!-- Kolom 4: Tanggal Akhir -->
                                        <div class="col-12 col-md-6">
                                            <label class="form-label small fw-bold text-dark mb-1">
                                                <i class="bi bi-calendar-check text-success me-1"></i>Tanggal Akhir Target <span class="text-danger">*</span>
                                            </label>
                                            <input type="date" name="tanggal_akhir" id="targetTanggalAkhir" class="form-control target-field fw-medium" value="<?= htmlspecialchars($target_old['tanggal_akhir'] ?: date('Y-m-d', strtotime('+30 days'))) ?>" required>
                                            
                                            <!-- Pilihan Cepat Durasi Tanggal -->
                                            <div class="d-flex flex-wrap align-items-center gap-1.5 mt-2">
                                                <span class="small text-muted" style="font-size: 11px;">Pilihan Cepat:</span>
                                                <button type="button" class="duration-pill-btn" onclick="setDurasiTarget(14, this)">+14 Hari</button>
                                                <button type="button" class="duration-pill-btn active" onclick="setDurasiTarget(30, this)">+30 Hari</button>
                                                <button type="button" class="duration-pill-btn" onclick="setDurasiTarget(60, this)">+60 Hari</button>
                                                <button type="button" class="duration-pill-btn" onclick="setDurasiTarget(90, this)">+90 Hari</button>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Tombol Aksi -->
                                    <div class="d-flex align-items-center gap-2 pt-2 border-top">
                                        <button type="submit" class="btn btn-tahfiz px-4 py-2.5 text-white fw-bold rounded-3 shadow-sm d-inline-flex align-items-center gap-2">
                                            <i class="bi bi-check2-circle fs-5"></i>
                                            <span>Simpan &amp; Aktifkan Target</span>
                                        </button>
                                        <button type="button" class="btn btn-light border px-4 py-2.5 fw-semibold rounded-3 text-secondary d-inline-flex align-items-center gap-2" onclick="batalPilihTarget()">
                                            <i class="bi bi-x-circle"></i>
                                            <span>Batal</span>
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ═══════════════════════════════════════════════════════════
                     TABEL MONITORING TARGET SELURUH SANTRI (IKHTISAR HALAQAH)
                ════════════════════════════════════════════════════════════ -->
                <div class="mt-4">
                    <div class="card p-4 border-0 shadow-sm rounded-4 bg-white">
                        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 pb-2 border-bottom gap-2">
                            <div>
                                <h5 class="fw-bold mb-1 text-dark">
                                    <i class="bi bi-table text-success me-2"></i>Status Target Seluruh Santri Halaqah
                                </h5>
                                <p class="text-muted small mb-0">Daftar lengkap komitmen hafalan santri yang sedang berlangsung di Halaqah <?= htmlspecialchars($halaqah_name) ?>.</p>
                            </div>
                            <span class="badge bg-light text-muted border px-3 py-2 fw-semibold">
                                <?= count($santri_list) ?> Total Santri
                            </span>
                        </div>

                        <div class="table-responsive rounded-3 border">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr class="small text-muted text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.5px;">
                                        <th class="ps-3 py-2.5" style="width: 50px;">No</th>
                                        <th class="py-2.5">Santri</th>
                                        <th class="py-2.5">Target Sasaran</th>
                                        <th class="py-2.5">Rentang Tanggal</th>
                                        <th class="py-2.5 text-center">Status</th>
                                        <th class="pe-3 py-2.5 text-center" style="width: 140px;">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($santri_list as $idx => $st): 
                                        $st_id = (string)$st['id'];
                                        $target_data = $target_aktif_map[$st_id] ?? null;
                                        
                                        $words = preg_split("/\s+/", trim($st['nama_lengkap']));
                                        $ini = '';
                                        if (!empty($words[0])) $ini .= mb_strtoupper(mb_substr($words[0], 0, 1));
                                        if (!empty($words[1])) $ini .= mb_strtoupper(mb_substr($words[1], 0, 1));
                                        if (empty($ini)) $ini = 'ST';
                                    ?>
                                        <tr style="cursor: pointer;" onclick="fokuskanSantri(<?= (int)$st['id'] ?>)">
                                            <td class="ps-3 text-muted fw-semibold"><?= $idx + 1 ?>.</td>
                                            <td>
                                                <div class="d-flex align-items-center gap-2.5">
                                                    <div class="santri-avatar-initial" style="width: 36px; height: 36px; min-width: 36px; font-size: 0.8rem;">
                                                        <?= $ini ?>
                                                    </div>
                                                    <div>
                                                        <strong class="text-dark d-block"><?= htmlspecialchars($st['nama_lengkap']) ?></strong>
                                                        <small class="text-muted" style="font-size: 11px;">NIS: <?= htmlspecialchars($st['nip_nis'] ?: '-') ?></small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <?php if ($target_data): ?>
                                                    <div class="fw-semibold text-dark small">
                                                        <span class="text-success"><?= htmlspecialchars($target_data['surah_awal']) ?></span>
                                                        <span class="text-muted mx-1">s/d</span>
                                                        <span class="text-success"><?= htmlspecialchars($target_data['surah_akhir']) ?></span>
                                                    </div>
                                                <?php else: ?>
                                                    <span class="text-muted small fst-italic">Belum ditetapkan target</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($target_data): ?>
                                                    <div class="small text-muted">
                                                        <i class="bi bi-calendar3 me-1"></i><?= htmlspecialchars($target_data['tanggal_mulai']) ?> s/d <?= htmlspecialchars($target_data['tanggal_akhir']) ?>
                                                    </div>
                                                <?php else: ?>
                                                    <span class="text-muted small">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center">
                                                <?php if ($target_data): ?>
                                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1.5 fw-bold rounded-pill">
                                                        <i class="bi bi-check-circle-fill me-1"></i>Aktif
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge bg-light text-secondary border px-3 py-1.5 fw-semibold rounded-pill">
                                                        Belum Ada Target
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="pe-3 text-center">
                                                <button type="button" class="btn btn-sm <?= $target_data ? 'btn-outline-success' : 'btn-tahfiz text-white' ?> rounded-2 px-3 py-1 fw-semibold shadow-xs" onclick="event.stopPropagation(); fokuskanSantri(<?= (int)$st['id'] ?>)">
                                                    <?= $target_data ? '<i class="bi bi-pencil-square me-1"></i>Ubah' : '<i class="bi bi-plus-lg me-1"></i>Tetapkan' ?>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>


            <!-- ═══════════════════════════════════════════════════════════
                 MENU 5: REKAP TAHFIZ HALAQAH (DESAIN FIGMA BARU)
                 Dashboard rekap lengkap dengan KPI, tabel, pencarian, modal
            ════════════════════════════════════════════════════════════ -->
            <?php elseif ($page_tab === 'rekap'): ?>
<?php
// ── DATA REKAP DINAMIS DARI DATABASE MYSQL ──
// Tarik semua target aktif dari tabel target_hafalan
$all_targets_rekap = [];
if ($pdo) {
    try {
        $st_t = $pdo->query("SELECT * FROM target_hafalan WHERE status = 'Aktif' ORDER BY id DESC");
        $all_targets_rekap = $st_t->fetchAll(PDO::FETCH_ASSOC);
    } catch (Throwable $e) {}
}

$rekap_data = [];
$total_capaian_sum = 0;

foreach ($santri_list as $s) {
    $s_id = (int)$s['id'];
    $s_nama = trim($s['nama_lengkap']);
    $s_nis = trim($s['nip_nis']);
    $lookup_key = !empty($s_nis) ? $s_nis : (string)$s_id;

    // Filter seluruh setoran santri ini dari riwayat MySQL
    $setoran_siswa = array_values(array_filter($riwayat_setoran, function($r) use ($s_id, $s_nama) {
        return ((int)($r['siswa_id'] ?? 0) === $s_id) || (strcasecmp(trim($r['nama_siswa'] ?? ''), $s_nama) === 0);
    }));

    // Filter seluruh murajaah santri ini dari riwayat MySQL
    $murajaah_siswa = array_values(array_filter($riwayat_murajaah, function($r) use ($s_id, $s_nama) {
        return ((int)($r['siswa_id'] ?? 0) === $s_id) || (strcasecmp(trim($r['nama_siswa'] ?? ''), $s_nama) === 0);
    }));

    // Filter target aktif santri ini
    $target_siswa = null;
    foreach ($all_targets_rekap as $t) {
        if ((int)($t['siswa_id'] ?? 0) === $s_id) {
            $target_siswa = $t;
            break;
        }
    }
    if (!$target_siswa && !empty($all_targets_rekap)) {
        foreach ($all_targets_rekap as $t) {
            if (empty($t['siswa_id'])) {
                $target_siswa = $t;
                break;
            }
        }
    }

    // Posisi hafalan terakhir & tanggal setor
    $posisi_teks = 'Belum Ada Setoran';
    $tgl_setor_teks = '-';
    $status_kualitas = 'Belum Setor';
    $catatan_terakhir = '';

    if (!empty($setoran_siswa)) {
        $last_set = $setoran_siswa[0];
        $posisi_teks = "Surah " . $last_set['surah'] . " (Ayat " . $last_set['ayat_awal'] . "–" . $last_set['ayat_akhir'] . ")";
        $tgl_setor_teks = !empty($last_set['tanggal_setor']) ? date('d M Y', strtotime($last_set['tanggal_setor'])) : (!empty($last_set['created_at']) ? date('d M Y', strtotime($last_set['created_at'])) : date('d M Y'));
        $status_kualitas = $last_set['kualitas'] ?: 'Lancar';

        // Ambil catatan evaluasi tajwid terbaru yang ada isinya
        foreach ($setoran_siswa as $st) {
            if (!empty(trim($st['catatan'] ?? ''))) {
                $catatan_terakhir = trim($st['catatan']);
                break;
            }
        }
    }

    if (empty($catatan_terakhir)) {
        foreach ($murajaah_siswa as $mr) {
            if (!empty(trim($mr['catatan'] ?? ''))) {
                $catatan_terakhir = trim($mr['catatan']);
                break;
            }
        }
    }
    if (empty($catatan_terakhir)) {
        $catatan_terakhir = 'Belum ada catatan evaluasi khusus dari ustadz pembimbing.';
    }

    // Target aktif & kalkulasi persentase capaian
    $target_teks = 'Belum Ada Target Aktif';
    $ayat_hafal = 0;
    $ayat_total = 0;
    $capaian_pct = 0;

    if ($target_siswa) {
        $s_awal = $target_siswa['surah_awal'] ?? '';
        $s_akhir = $target_siswa['surah_akhir'] ?? '';
        preg_match('/(?:Juz\s*\d+\s*[•·\-]\s*)?([A-Za-z\-\'\s]+)(?:\(Ayat\s*(\d+)[\–\-](\d+)\))?/u', $s_awal, $mAwal);
        $namaSurahTarget = trim($mAwal[1] ?? $s_awal);
        $startAyat = isset($mAwal[2]) ? (int)$mAwal[2] : 1;
        $endAyat = isset($mAwal[3]) ? (int)$mAwal[3] : 7;
        $ayat_total = max(1, $endAyat - $startAyat + 1);

        // Cek progres setoran siswa pada target surah
        $maxAyatDisetor = 0;
        foreach ($setoran_siswa as $st) {
            if (strcasecmp(trim($st['surah']), $namaSurahTarget) === 0 || stripos($st['surah'], $namaSurahTarget) !== false) {
                $maxAyatDisetor = max($maxAyatDisetor, (int)$st['ayat_akhir']);
            }
        }
        $ayat_hafal = min($ayat_total, $maxAyatDisetor);
        $capaian_pct = min(100, round(($ayat_hafal / $ayat_total) * 100));
        $target_teks = "Surah {$namaSurahTarget} (Ayat {$startAyat}–{$endAyat})";
    } elseif (!empty($setoran_siswa)) {
        $capaian_pct = 100;
        $ayat_hafal = 1;
        $ayat_total = 1;
        $target_teks = "Selesai Surah " . $setoran_siswa[0]['surah'];
    }

    $total_capaian_sum += $capaian_pct;

    // Riwayat gabungan untuk modal pop-up (Ziyadah & Murajaah)
    $riwayat_modal = [];
    foreach ($setoran_siswa as $st) {
        $tgl = !empty($st['tanggal_setor']) ? date('d M Y', strtotime($st['tanggal_setor'])) : (!empty($st['created_at']) ? date('d M Y', strtotime($st['created_at'])) : date('d M Y'));
        $riwayat_modal[] = [
            'tgl'    => $tgl,
            'jenis'  => 'Ziyadah',
            'surah'  => $st['surah'] . ': ' . $st['ayat_awal'] . '–' . $st['ayat_akhir'],
            'status' => $st['kualitas'] ?: 'Lancar'
        ];
    }
    foreach ($murajaah_siswa as $mr) {
        $tgl = !empty($mr['tanggal_murajaah']) ? date('d M Y', strtotime($mr['tanggal_murajaah'])) : (!empty($mr['created_at']) ? date('d M Y', strtotime($mr['created_at'])) : date('d M Y'));
        $riwayat_modal[] = [
            'tgl'    => $tgl,
            'jenis'  => 'Murajaah',
            'surah'  => $mr['surah'] . (!empty($mr['ayat_range']) ? ': ' . $mr['ayat_range'] : ''),
            'status' => $mr['status_mutqin'] ?: 'Lancar'
        ];
    }

    $rekap_item = [
        'nis'          => $s_nis,
        'nama'         => $s_nama,
        'capaian'      => $capaian_pct,
        'tgl_setor'    => $tgl_setor_teks,
        'posisi'       => $posisi_teks,
        'kualitas'     => $status_kualitas,
        'target'       => $target_teks,
        'ayat_hafal'   => $ayat_hafal,
        'ayat_total'   => $ayat_total,
        'catatan'      => $catatan_terakhir,
        'riwayat'      => array_slice($riwayat_modal, 0, 15)
    ];

    $rekap_data[$lookup_key] = $rekap_item;
    $rekap_data[$s_id] = $rekap_item;
}

$rekap_total_siswa = count($santri_list);
$rekap_total_setoran = count($riwayat_setoran);
$rekap_total_murajaah = count($riwayat_murajaah);
$rekap_avg_capaian = $rekap_total_siswa > 0 ? round($total_capaian_sum / $rekap_total_siswa) : 0;
?>
                <!-- Badge & Header -->
                <div class="mb-4">
                    <span class="badge px-3 py-2 fw-bold rounded-pill" style="background-color: #11756D; font-size: 11px; letter-spacing: 0.05em;">REKAPITULASI HALAQAH <?= strtoupper(htmlspecialchars($halaqah_name)) ?></span>
                    <h2 class="fw-bold mt-3 mb-1" style="color: #1E293B; font-size: 1.65rem;">Rekap Tahfiz Siswa Binaan</h2>
                    <p class="text-muted mb-0" style="font-size: 14px;">Ringkasan capaian target dan riwayat setoran hafalan siswa halaqah.</p>
                </div>
                <!-- 4 KPI Cards Dinamis dari MySQL -->
                <div class="row g-3 mb-4">
                    <div class="col-lg-3 col-md-6">
                        <div style="background:#fff; border:1px solid #E2E8F0; border-radius:16px; padding:20px 22px; box-shadow:0 2px 10px rgba(0,0,0,0.04); border-left:4px solid #11756D;">
                            <div class="text-uppercase fw-bold text-muted" style="font-size:11px; letter-spacing:0.05em;">Siswa Binaan</div>
                            <div class="fw-bold mt-1" style="font-size:2rem; color:#1E293B;"><?= $rekap_total_siswa ?></div>
                            <div class="text-muted" style="font-size:12.5px;">Siswa dalam halaqah</div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div style="background:#fff; border:1px solid #E2E8F0; border-radius:16px; padding:20px 22px; box-shadow:0 2px 10px rgba(0,0,0,0.04); border-left:4px solid #11756D;">
                            <div class="text-uppercase fw-bold text-muted" style="font-size:11px; letter-spacing:0.05em;">Setoran Tercatat</div>
                            <div class="fw-bold mt-1" style="font-size:2rem; color:#1E293B;"><?= $rekap_total_setoran ?></div>
                            <div class="text-muted" style="font-size:12.5px;">Total setoran tersimpan</div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div style="background:#fff; border:1px solid #E2E8F0; border-radius:16px; padding:20px 22px; box-shadow:0 2px 10px rgba(0,0,0,0.04); border-left:4px solid #11756D;">
                            <div class="text-uppercase fw-bold text-muted" style="font-size:11px; letter-spacing:0.05em;">Total Murajaah</div>
                            <div class="fw-bold mt-1" style="font-size:2rem; color:#1E293B;"><?= $rekap_total_murajaah ?></div>
                            <div class="text-muted" style="font-size:12.5px;">Pengulangan santri mutqin</div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div style="background:#fff; border:1px solid #E2E8F0; border-radius:16px; padding:20px 22px; box-shadow:0 2px 10px rgba(0,0,0,0.04); border-left:4px solid #11756D;">
                            <div class="text-uppercase fw-bold text-muted" style="font-size:11px; letter-spacing:0.05em;">Target Kelancaran</div>
                            <div class="fw-bold mt-1" style="font-size:2rem; color:#11756D;">85%</div>
                            <div class="text-muted" style="font-size:12.5px;">Target capaian halaqah</div>
                        </div>
                    </div>
                </div>

                <!-- Kartu Tabel Daftar Siswa & Rekap Capaian -->
                <div style="background:#fff; border:1px solid #E2E8F0; border-radius:16px; box-shadow:0 2px 10px rgba(0,0,0,0.04); overflow:hidden;">
                    <!-- Header -->
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 px-4 py-3" style="border-bottom:1px solid #F1F5F9;">
                        <div>
                            <h5 class="fw-bold mb-0" style="color:#1E293B; font-size:16px;">Daftar Siswa &amp; Rekap Capaian</h5>
                        </div>
                        <div class="d-flex align-items-center gap-3">
                            <span class="badge rounded-pill fw-semibold" id="badgeJumlahSiswa" style="background:#E6F4F1; color:#11756D; font-size:12px; padding:6px 14px;"><?= $rekap_total_siswa ?> Siswa Terdaftar</span>
                            <div class="position-relative">
                                <i class="bi bi-search position-absolute" style="left:12px; top:50%; transform:translateY(-50%); color:#94A3B8; font-size:14px;"></i>
                                <input type="text" id="searchSiswaInput" class="form-control form-control-sm" placeholder="Cari NIS atau nama siswa..." style="padding-left:36px; border-radius:10px; border:1px solid #E2E8F0; min-width:220px; font-size:13px; height:38px;">
                            </div>
                        </div>
                    </div>
                    <!-- Tabel Siswa -->
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" id="tabelSiswaRekap">
                            <thead style="background:#F8FAFC;">
                                <tr style="font-size:12px; color:#64748B; text-transform:uppercase; letter-spacing:0.04em;">
                                    <th class="ps-4 py-3 fw-semibold" style="width:50px;">No</th>
                                    <th class="py-3 fw-semibold" style="width:100px;">NIS</th>
                                    <th class="py-3 fw-semibold">Nama Siswa</th>
                                    <th class="py-3 fw-semibold" style="width:200px;">Capaian Target</th>
                                    <th class="py-3 fw-semibold" style="width:180px;">Tanggal Setoran Terakhir</th>
                                    <th class="pe-4 py-3 fw-semibold text-center" style="width:120px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($santri_list as $i => $s):
                                    $nis = htmlspecialchars($s['nip_nis'] ?? '');
                                    $nama = htmlspecialchars($s['nama_lengkap']);
                                    $lookup_key = !empty($s['nip_nis']) ? $s['nip_nis'] : (string)$s['id'];
                                    $rd = $rekap_data[$lookup_key] ?? $rekap_data[$s['id']] ?? ['capaian'=>0,'tgl_setor'=>'-','posisi'=>'Belum Ada Setoran','target'=>'Belum Ada Target Aktif','ayat_hafal'=>0,'ayat_total'=>0,'catatan'=>'Belum ada catatan pembimbing.','riwayat'=>[],'kualitas'=>'Belum Setor'];
                                ?>
                                <tr class="siswa-row" data-nis="<?= $nis ?>" data-nama="<?= strtolower($nama) ?>">
                                    <td class="ps-4 text-muted"><?= $i + 1 ?></td>
                                    <td><code style="color:#D946EF; font-weight:600; font-size:13px; background:transparent;"><?= $nis ?: '-' ?></code></td>
                                    <td><strong style="color:#1E293B;"><?= $nama ?></strong></td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="progress flex-grow-1" style="height:8px; border-radius:4px; max-width:140px; background:#E2E8F0;">
                                                <div class="progress-bar" style="width:<?= $rd['capaian'] ?>%; background-color:#11756D; border-radius:4px;"></div>
                                            </div>
                                            <span class="fw-bold" style="font-size:13px; color:#1E293B; min-width:36px;"><?= $rd['capaian'] ?>%</span>
                                        </div>
                                    </td>
                                    <td class="text-muted" style="font-size:13px;"><?= $rd['tgl_setor'] ?></td>
                                    <td class="pe-4 text-center">
                                        <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3 btn-lihat-detail"
                                            data-nis="<?= $nis ?>"
                                            data-nama="<?= $nama ?>"
                                            data-posisi="<?= htmlspecialchars($rd['posisi']) ?>"
                                            data-target="<?= htmlspecialchars($rd['target']) ?>"
                                            data-ayathafal="<?= $rd['ayat_hafal'] ?>"
                                            data-ayattotal="<?= $rd['ayat_total'] ?>"
                                            data-capaian="<?= $rd['capaian'] ?>"
                                            data-kualitas="<?= htmlspecialchars($rd['kualitas']) ?>"
                                            data-catatan="<?= htmlspecialchars($rd['catatan']) ?>"
                                            data-riwayat='<?= json_encode($rd['riwayat'], JSON_UNESCAPED_UNICODE) ?>'
                                            style="font-size:12.5px; border-color:#CBD5E1; color:#475569;">
                                            <i class="bi bi-eye me-1"></i>Lihat Detail
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <!-- Footer -->
                    <div class="px-4 py-3" style="border-top:1px solid #F1F5F9;">
                        <small class="text-muted" id="footerInfoSiswa">Menampilkan 1–<?= $rekap_total_siswa ?> dari <?= $rekap_total_siswa ?> siswa</small>
                    </div>
                </div>

                <!-- ═══ MODAL: Detail Rekap Tahfiz Siswa ═══ -->
                <div class="modal fade" id="modalDetailSiswa" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered" style="max-width:640px;">
                        <div class="modal-content border-0" style="border-radius:16px; overflow:hidden; box-shadow:0 20px 60px rgba(0,0,0,0.15);">
                            <!-- Header -->
                            <div class="modal-header border-0 pb-0 px-4 pt-4">
                                <div>
                                    <h5 class="modal-title fw-bold" style="color:#1E293B; font-size:18px;">Detail Rekap Tahfiz Siswa</h5>
                                    <p class="text-muted mb-0" style="font-size:13px;" id="modalSubtitle"></p>
                                </div>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <!-- Body -->
                            <div class="modal-body px-4 py-3">
                                <!-- 2 Info Cards -->
                                <div class="row g-3 mb-3">
                                    <div class="col-6">
                                        <div style="background:#F0FDF4; border-radius:12px; padding:16px;">
                                            <div class="fw-bold mb-1" style="font-size:13px; color:#15803D;">Posisi Hafalan Terakhir</div>
                                            <div class="fw-bold mb-2" style="font-size:14.5px; color:#1E293B;" id="modalPosisi"></div>
                                            <span class="badge rounded-pill" id="modalKualitasBadge" style="background:#DCFCE7; color:#15803D; font-size:11px;">Lancar</span>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div style="background:#FFFBEB; border-radius:12px; padding:16px;">
                                            <div class="fw-bold mb-1" style="font-size:13px; color:#B45309;">Target Aktif</div>
                                            <div class="fw-bold mb-1" style="font-size:14.5px; color:#1E293B;" id="modalTarget"></div>
                                            <div class="mb-1" style="font-size:12px; color:#64748B;" id="modalAyatRatio"></div>
                                            <div class="progress mb-1" style="height:6px; border-radius:3px; background:#FDE68A;">
                                                <div class="progress-bar" id="modalProgressBar" style="background:#D97706; border-radius:3px;"></div>
                                            </div>
                                            <div style="font-size:11px; color:#92400E;">Periode: <?= date('F Y') ?></div>
                                        </div>
                                    </div>
                                </div>
                                <!-- Catatan Guru -->
                                <div style="background:#F8FAFC; border-radius:12px; padding:16px; margin-bottom:16px;">
                                    <div class="fw-bold mb-2" style="font-size:13.5px; color:#1E293B;">Catatan Ustadz Pembimbing</div>
                                    <p class="mb-0 text-muted" style="font-size:13px; line-height:1.6;" id="modalCatatan"></p>
                                </div>
                                <!-- Tabel Riwayat -->
                                <div class="fw-bold mb-2" style="font-size:14px; color:#1E293B;">Riwayat Setoran &amp; Murajaah</div>
                                <div class="table-responsive" style="border:1px solid #E2E8F0; border-radius:10px; overflow:hidden;">
                                    <table class="table table-sm align-middle mb-0" style="font-size:13px;">
                                        <thead style="background:#F8FAFC;">
                                            <tr style="color:#64748B;">
                                                <th class="py-2 ps-3 fw-semibold">Tanggal</th>
                                                <th class="py-2 fw-semibold">Jenis</th>
                                                <th class="py-2 fw-semibold">Surah &amp; Ayat</th>
                                                <th class="py-2 pe-3 fw-semibold">Status</th>
                                            </tr>
                                        </thead>
                                        <tbody id="modalRiwayatBody"></tbody>
                                    </table>
                                </div>
                            </div>
                            <!-- Footer -->
                            <div class="modal-footer border-0 px-4 pb-4 pt-2 justify-content-center">
                                <button type="button" class="btn fw-semibold text-white px-5 py-2" data-bs-dismiss="modal" style="background-color:#11756D; border-radius:10px; font-size:14px;">Tutup Detail</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- JavaScript: Pencarian & Modal -->
                <script>
                document.addEventListener('DOMContentLoaded', function() {
                    // ── Filter Pencarian ──
                    const searchInput = document.getElementById('searchSiswaInput');
                    const rows = document.querySelectorAll('.siswa-row');
                    const footerInfo = document.getElementById('footerInfoSiswa');
                    const badge = document.getElementById('badgeJumlahSiswa');
                    const totalSiswa = rows.length;

                    if (searchInput) {
                        searchInput.addEventListener('input', function() {
                            const q = this.value.toLowerCase().trim();
                            let visible = 0;
                            rows.forEach(function(row) {
                                const nis = row.getAttribute('data-nis') || '';
                                const nama = row.getAttribute('data-nama') || '';
                                const match = nis.includes(q) || nama.includes(q);
                                row.style.display = match ? '' : 'none';
                                if (match) visible++;
                            });
                            if (footerInfo) {
                                if (visible === 0) {
                                    footerInfo.textContent = 'Data siswa tidak ditemukan';
                                } else {
                                    footerInfo.textContent = 'Menampilkan 1–' + visible + ' dari ' + totalSiswa + ' siswa';
                                }
                            }
                            if (badge) {
                                badge.textContent = visible + ' Siswa ' + (q ? 'Ditemukan' : 'Terdaftar');
                            }
                        });
                    }

                    // ── Modal Detail Rekap ──
                    const modalEl = document.getElementById('modalDetailSiswa');
                    let bsModal = null;
                    if (modalEl && typeof bootstrap !== 'undefined') {
                        bsModal = new bootstrap.Modal(modalEl);
                    }

                    document.querySelectorAll('.btn-lihat-detail').forEach(function(btn) {
                        btn.addEventListener('click', function() {
                            const nama = this.dataset.nama || '-';
                            const nis = this.dataset.nis || '-';
                            const elSub = document.getElementById('modalSubtitle');
                            if (elSub) elSub.textContent = nama + ' • NIS: ' + (nis !== '-' && nis !== '' ? nis : '-') + ' • Halaqah Al-Furqan';
                            
                            const elPos = document.getElementById('modalPosisi');
                            if (elPos) elPos.textContent = this.dataset.posisi || '-';

                            // Badge Kualitas
                            const kVal = this.dataset.kualitas || 'Belum Setor';
                            const kBadge = document.getElementById('modalKualitasBadge');
                            if (kBadge) {
                                kBadge.textContent = kVal;
                                if (kVal.toLowerCase().includes('kurang') || kVal.toLowerCase().includes('penguatan')) {
                                    kBadge.style.background = '#FEF3C7';
                                    kBadge.style.color = '#B45309';
                                } else if (kVal.toLowerCase().includes('belum')) {
                                    kBadge.style.background = '#F1F5F9';
                                    kBadge.style.color = '#64748B';
                                } else {
                                    kBadge.style.background = '#DCFCE7';
                                    kBadge.style.color = '#15803D';
                                }
                            }

                            const elTgt = document.getElementById('modalTarget');
                            if (elTgt) elTgt.textContent = this.dataset.target || '-';

                            const hafal = parseInt(this.dataset.ayathafal || 0);
                            const total = parseInt(this.dataset.ayattotal || 0);
                            const pct = parseInt(this.dataset.capaian || 0);

                            const elRatio = document.getElementById('modalAyatRatio');
                            const elBar = document.getElementById('modalProgressBar');
                            if (total > 0) {
                                if (elRatio) elRatio.textContent = hafal + ' / ' + total + ' Ayat (' + pct + '%)';
                                if (elBar) elBar.style.width = pct + '%';
                            } else {
                                if (elRatio) elRatio.textContent = 'Target belum diatur';
                                if (elBar) elBar.style.width = '0%';
                            }

                            const elCat = document.getElementById('modalCatatan');
                            if (elCat) elCat.textContent = this.dataset.catatan || 'Belum ada catatan evaluasi dari ustadz.';

                            // Riwayat
                            let riwayat = [];
                            try { riwayat = JSON.parse(this.dataset.riwayat || '[]'); } catch(e) {}
                            let html = '';
                            if (riwayat.length === 0) {
                                html = '<tr><td colspan="4" class="text-center text-muted py-3">Belum ada riwayat setoran & murajaah.</td></tr>';
                            } else {
                                riwayat.forEach(function(r) {
                                    const jenisColor = (r.jenis === 'Ziyadah' || r.jenis === 'Setoran') ? 'background:#11756D;color:#fff;' : 'background:#D97706;color:#fff;';
                                    html += '<tr>' +
                                        '<td class="ps-3 py-2 text-muted">' + (r.tgl || '-') + '</td>' +
                                        '<td class="py-2"><span class="badge rounded-pill" style="font-size:11px;' + jenisColor + '">' + (r.jenis || 'Setoran') + '</span></td>' +
                                        '<td class="py-2 fw-semibold text-dark">' + (r.surah || '-') + '</td>' +
                                        '<td class="pe-3 py-2 fw-semibold" style="color:#15803D;">' + (r.status || 'Lancar') + '</td>' +
                                        '</tr>';
                                });
                            }
                            const tbody = document.getElementById('modalRiwayatBody');
                            if (tbody) tbody.innerHTML = html;

                            if (bsModal) bsModal.show();
                        });
                    });
                });
                </script>

            <?php elseif ($page_tab === 'profil'): ?>
                <div class="mb-4">
                    <h1 class="profil-page-title mb-1">Profil Pribadi</h1>
                    <p class="profil-page-subtitle mb-0">Kelola informasi biodata asatidz dan pembaruan kata sandi akun.</p>
                </div>

                <div class="profil-grid">
                    <!-- Kartu Kiri: Informasi Akun -->
                    <div class="profil-card">
                        <h2 class="profil-card-title">
                            <i class="bi bi-person-circle text-success"></i>Informasi Akun
                        </h2>

                        <form action="?tab=profil" method="POST">
                            <input type="hidden" name="action" value="update_biodata">

                            <div class="profil-field">
                                <label class="profil-label" for="namaLengkap">Nama Lengkap</label>
                                <input type="text" id="namaLengkap" class="profil-input" value="<?= htmlspecialchars($ustadz_name) ?>" readonly>
                            </div>

                            <div class="profil-field">
                                <label class="profil-label" for="kontakUstadz">Nomor Kontak</label>
                                <input type="tel" id="kontakUstadz" name="kontak" class="profil-input form-control" value="<?= htmlspecialchars($ustadz_kontak) ?>" placeholder="Contoh: 0812xxxxxxxx" maxlength="20" style="background: #FFFFFF; border: 1px solid #CBD5E1;">
                            </div>

                            <div class="profil-field">
                                <label class="profil-label" for="nipUstadz">Nomor Induk Pegawai (NIP)</label>
                                <input type="text" id="nipUstadz" class="profil-input" value="<?= htmlspecialchars($ustadz_nip) ?>" readonly>
                            </div>

                            <div class="profil-field">
                                <label class="profil-label" for="halaqahUstadz">Halaqah Binaan</label>
                                <input type="text" id="halaqahUstadz" class="profil-input" value="Halaqah <?= htmlspecialchars($halaqah_name) ?>" readonly>
                                <small class="profil-hint">Penugasan halaqah dikelola oleh Admin</small>
                            </div>

                            <div class="profil-actions">
                                <button type="submit" class="profil-btn profil-btn-primary">
                                    <i class="bi bi-floppy"></i>Simpan Perubahan
                                </button>
                                <button type="reset" class="profil-btn profil-btn-reset">
                                    <i class="bi bi-x-circle"></i>Batal
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Kartu Kanan: Ganti Password -->
                    <div class="profil-card">
                        <h2 class="profil-card-title">
                            <i class="bi bi-key-fill text-warning"></i>Ganti Password
                        </h2>

                        <form action="?tab=profil" method="POST">
                            <input type="hidden" name="action" value="update_password">

                            <div class="profil-field">
                                <label class="profil-label" for="passLama">Kata Sandi Saat Ini</label>
                                <input type="password" id="passLama" name="password_lama" class="profil-input" placeholder="Masukkan kata sandi lama" required>
                            </div>

                            <div class="profil-field">
                                <label class="profil-label" for="passBaru">Kata Sandi Baru</label>
                                <input type="password" id="passBaru" name="password_baru" class="profil-input" placeholder="Minimal 6 karakter" required>
                            </div>

                            <div class="profil-field">
                                <label class="profil-label" for="passKonfirmasi">Konfirmasi Kata Sandi Baru</label>
                                <input type="password" id="passKonfirmasi" name="password_konfirmasi" class="profil-input" placeholder="Ulangi kata sandi baru" required>
                            </div>

                            <div class="p-2.5 rounded-3 bg-light border mb-3 small text-muted" style="font-size: 12px;">
                                <i class="bi bi-info-circle me-1" style="color: #11756D;"></i>Pastikan kata sandi Anda kuat dan tidak mudah ditebak demi keamanan nilai hafalan santri.
                            </div>

                            <div class="profil-actions mt-0">
                                <button type="submit" class="profil-btn profil-btn-primary w-100">
                                    <i class="bi bi-shield-lock"></i>Perbarui Kata Sandi
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
const dataJuzHafalan = <?= json_encode($quran_juz, JSON_UNESCAPED_UNICODE) ?>;
// Opsi khusus Juz 30 (Adh-Dhuha – An-Nas) seperti pada foto referensi teman
dataJuzHafalan['30_dhuha'] = {
    nama: 'Juz 30 (Adh-Dhuha – An-Nas)',
    surah: (dataJuzHafalan['30']?.surah || []).filter(sr => sr.no >= 93)
};

const santriSetoranProgres = <?= json_encode($santri_setoran_progres, JSON_UNESCAPED_UNICODE) ?>;
let santriHafalanAktif = null;

function normalizeSurah(str) {
    return (str || '').toLowerCase().replace(/[^a-z0-9]/g, '');
}

function pilihSantriUntukHafalan(nama, nis, el) {
    const parts = (nama || '').trim().split(/\s+/);
    let inisial = '';
    if (parts.length > 0 && parts[0]) inisial += parts[0][0].toUpperCase();
    if (parts.length > 1 && parts[1]) inisial += parts[1][0].toUpperCase();
    if (!inisial) inisial = 'ST';

    santriHafalanAktif = { nama, nis, inisial };

    document.querySelectorAll('#listSantriHafalan .santri-select-card').forEach(c => c.classList.remove('active'));
    if (el) el.classList.add('active');

    // Sembunyikan placeholder dan tampilkan area tabel surah hafalan
    const placeholder = document.getElementById('placeholderHafalan');
    const areaForm    = document.getElementById('areaFormHafalan');
    if (placeholder) placeholder.style.display = 'none';
    if (areaForm)    areaForm.style.display    = 'block';

    const dispNama = document.getElementById('displayNamaSantriHafalan');
    if (dispNama) dispNama.innerText = nama;

    renderTabelSurahHafalan();
}

function gantiJuzHafalan(juzNo) {
    renderTabelSurahHafalan();
}

function renderTabelSurahHafalan() {
    const juzSelect = document.getElementById('selectJuzHafalan');
    const juzKey = juzSelect ? juzSelect.value : '30_dhuha';
    const surahList = dataJuzHafalan[juzKey]?.surah || [];
    const tbody = document.getElementById('tbodySurahHafalan');
    if (!tbody) return;

    tbody.innerHTML = '';

    if (!santriHafalanAktif) return;

    const namaSantri = santriHafalanAktif.nama;
    const santriProgs = santriSetoranProgres[namaSantri] || {};

    surahList.forEach((sr, idx) => {
        const norm = normalizeSurah(sr.nama);
        const prog = santriProgs[norm] || null;
        const maxAyat = prog ? parseInt(prog.max_ayat || 0) : 0;

        let statusBadge = '';
        let statusKey = 'belum_setor';

        if (maxAyat >= sr.ayat) {
            statusBadge = `<span class="badge bg-success-subtle text-success border border-success-subtle fw-bold rounded-pill px-3 py-1.5"><i class="bi bi-check-circle-fill me-1"></i>Selesai</span>`;
            statusKey = 'selesai';
        } else if (maxAyat > 0) {
            statusBadge = `<span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle fw-bold rounded-pill px-3 py-1.5"><i class="bi bi-clock-history me-1"></i>Sedang Berjalan</span>`;
            statusKey = 'sedang_berjalan';
        } else {
            statusBadge = `<span class="badge bg-light text-secondary border fw-semibold rounded-pill px-3 py-1.5">Belum Setor</span>`;
            statusKey = 'belum_setor';
        }

        const tr = document.createElement('tr');
        tr.className = 'row-surah-clickable';
        tr.style.cursor = 'pointer';
        tr.title = `Klik untuk membuka Lembar Setoran Surah ${sr.nama}`;
        tr.onclick = function() {
            bukaModalSetoran(sr.nama, sr.ayat, sr.no, maxAyat, statusKey);
        };

        tr.innerHTML = `
            <td class="ps-3 text-muted fw-semibold">${idx + 1}.</td>
            <td>
                <div class="d-flex align-items-center gap-2">
                    <span class="d-inline-flex align-items-center justify-content-center rounded p-1 text-warning-emphasis" style="background:#FEF3C7; width:26px; height:26px; font-size:0.85rem;">
                        <i class="bi bi-book-half"></i>
                    </span>
                    <span class="fw-bold text-dark">${sr.nama}</span>
                </div>
            </td>
            <td class="text-muted small fw-medium">Ayat 1–${sr.ayat}</td>
            <td class="text-center">${statusBadge}</td>
            <td class="pe-3 text-center">
                <button type="button" class="btn btn-sm btn-tahfiz rounded-2 px-2.5 py-1 text-white shadow-sm" style="background:#11756D;" onclick="event.stopPropagation(); bukaModalSetoran('${sr.nama.replace(/'/g, "\\'")}', ${sr.ayat}, ${sr.no}, ${maxAyat}, '${statusKey}')" title="Catat Setoran Surah ${sr.nama}">
                    <i class="bi bi-plus-lg"></i>
                </button>
            </td>
        `;

        tbody.appendChild(tr);
    });
}

function bukaModalSetoran(namaSurah, totalAyat, noSurah, maxAyat, statusKey) {
    if (!santriHafalanAktif) {
        alert('Silakan pilih siswa terlebih dahulu.');
        return;
    }

    // Set Info Header & Subtitle
    const elJudul = document.getElementById('modalJudulSurah');
    const elSub   = document.getElementById('modalSubInfo');
    if (elJudul) elJudul.innerText = `${noSurah}. Surah ${namaSurah}`;
    if (elSub) elSub.innerText = `Target: Ayat 1 s/d ${totalAyat}`;

    // Set Santri Info
    const elNama   = document.getElementById('modalNamaSantri');
    const elNis    = document.getElementById('modalNisSantri');
    const elTot    = document.getElementById('modalBadgeTotalAyat');
    const elAvatar = document.getElementById('modalAvatarSantri');
    if (elNama) elNama.innerText = santriHafalanAktif.nama;
    if (elNis) elNis.innerText = 'NIS: ' + (santriHafalanAktif.nis || '-');
    if (elTot) elTot.innerText = `Total ${totalAyat} Ayat`;
    if (elAvatar) elAvatar.innerText = santriHafalanAktif.inisial;

    // Set Hidden Inputs
    const elInpNama  = document.getElementById('modalInputSantriNama');
    const elInpSurah = document.getElementById('modalInputSurahNama');
    if (elInpNama) elInpNama.value = santriHafalanAktif.nama;
    if (elInpSurah) elInpSurah.value = namaSurah;

    // Set Status Alert & Form Inputs
    const alertContainer = document.getElementById('modalAlertStatus');
    const inputAwal  = document.getElementById('modalInputAyatAwal');
    const inputAkhir = document.getElementById('modalInputAyatAkhir');

    if (inputAwal) {
        inputAwal.min = 1;
        inputAwal.max = totalAyat;
    }
    if (inputAkhir) {
        inputAkhir.min = 1;
        inputAkhir.max = totalAyat;
    }

    if (statusKey === 'sedang_berjalan') {
        const nextAyat = Math.min(maxAyat + 1, totalAyat);
        if (alertContainer) {
            alertContainer.innerHTML = `
                <div class="alert alert-warning border border-warning-subtle d-flex align-items-center gap-2 py-2 px-3 small rounded-3 mb-0">
                    <i class="bi bi-clock-history fs-5 text-warning-emphasis"></i>
                    <div>
                        <strong>Status: Sedang Berjalan.</strong> Santri telah menyetor sampai <strong>Ayat ${maxAyat}</strong> dari total ${totalAyat} ayat. Disarankan melanjutkan setoran mulai <strong>Ayat ${nextAyat} s/d ${totalAyat}</strong>.
                    </div>
                </div>
            `;
        }
        if (inputAwal) inputAwal.value = nextAyat;
        if (inputAkhir) inputAkhir.value = totalAyat;
    } else if (statusKey === 'selesai') {
        if (alertContainer) {
            alertContainer.innerHTML = `
                <div class="alert alert-success border border-success-subtle d-flex align-items-center gap-2 py-2 px-3 small rounded-3 mb-0">
                    <i class="bi bi-check-circle-fill fs-5 text-success"></i>
                    <div>
                        <strong>Status: Selesai.</strong> Seluruh <strong>${totalAyat} Ayat</strong> surah ini telah lengkap disetor. Ustadz dapat mencatat tasmi / pengulangan bila santri mengulang hafalan.
                    </div>
                </div>
            `;
        }
        if (inputAwal) inputAwal.value = 1;
        if (inputAkhir) inputAkhir.value = totalAyat;
    } else {
        if (alertContainer) {
            alertContainer.innerHTML = `
                <div class="alert alert-secondary border d-flex align-items-center gap-2 py-2 px-3 small rounded-3 mb-0">
                    <i class="bi bi-info-circle-fill fs-5 text-secondary"></i>
                    <div>
                        <strong>Status: Belum Setor.</strong> Santri belum memiliki riwayat setoran untuk surah ini.
                    </div>
                </div>
            `;
        }
        if (inputAwal) inputAwal.value = 1;
        if (inputAkhir) inputAkhir.value = totalAyat;
    }

    if (inputAkhir) {
        inputAkhir.oninput = function() {
            if (parseInt(this.value) > totalAyat) this.value = totalAyat;
        };
    }
    if (inputAwal) {
        inputAwal.oninput = function() {
            if (parseInt(this.value) > totalAyat) this.value = totalAyat;
        };
    }

    // Tampilkan Pop-Up Modal Bootstrap
    const modalEl = document.getElementById('modalSetoranEksklusif');
    if (modalEl) {
        const modalObj = bootstrap.Modal.getOrCreateInstance(modalEl);
        modalObj.show();
    }
}

// Filter Pencarian Santri pada Tab Hafalan
document.getElementById('cariSantriHafalan')?.addEventListener('input', function() {
    const val = this.value.toLowerCase();
    document.querySelectorAll('#listSantriHafalan .santri-select-card').forEach(item => {
        const text = item.innerText.toLowerCase();
        item.style.display = text.includes(val) ? 'flex' : 'none';
    });
});

// Inisialisasi otomatis jika ada siswa yang difokuskan (misalnya setelah POST error/sukses)
window.addEventListener('DOMContentLoaded', () => {
const urlParams = new URLSearchParams(window.location.search);
    const getSantri = urlParams.get('santri');
    if (getSantri) {
        document.querySelectorAll('#listSantriHafalan .santri-select-card').forEach(item => {
            const nama = item.querySelector('.santri-nama-item')?.innerText;
            if (nama && nama.toLowerCase().includes(getSantri.toLowerCase())) {
                item.click();
            }
        });
    }
});

// ─────────────────────────────────────────────────────────────
// LOGIKA JAVASCRIPT TAB TARGET HAFALAN
// ─────────────────────────────────────────────────────────────
const targetAktifMap = <?= json_encode($target_aktif_map, JSON_UNESCAPED_UNICODE) ?>;
const targetUrutanOpsi = <?= json_encode($urutan_opsi_target, JSON_UNESCAPED_UNICODE) ?>;
let santriTargetTerpilih = null;

function setSelectOptionByText(selectEl, textSnippet) {
    if (!selectEl) return;
    for (let i = 0; i < selectEl.options.length; i++) {
        if (selectEl.options[i].text.toLowerCase().includes(textSnippet.toLowerCase())) {
            selectEl.selectedIndex = i;
            return;
        }
    }
}

function applyPresetTarget(presetKey, btnEl) {
    const selAwal  = document.getElementById('targetSurahAwal');
    const selAkhir = document.getElementById('targetSurahAkhir');
    if (!selAwal || !selAkhir) return;

    if (presetKey === 'juz30_full') {
        setSelectOptionByText(selAwal, 'An-Naba');
        setSelectOptionByText(selAkhir, 'An-Nas');
    } else if (presetKey === 'juz30_pendek') {
        setSelectOptionByText(selAwal, 'Adh-Dhuha');
        setSelectOptionByText(selAkhir, 'An-Nas');
    } else if (presetKey === 'juz1_full') {
        setSelectOptionByText(selAwal, 'Al-Fatihah');
        setSelectOptionByText(selAkhir, 'Al-Baqarah (Ayat 1-141');
        if (selAkhir.selectedIndex <= 0) setSelectOptionByText(selAkhir, 'Al-Baqarah');
    } else if (presetKey === 'juz2_full') {
        setSelectOptionByText(selAwal, 'Al-Baqarah (Ayat 142');
        setSelectOptionByText(selAkhir, 'Al-Baqarah (Ayat 142');
    }

    document.querySelectorAll('.preset-pill-btn').forEach(b => b.classList.remove('active'));
    if (btnEl) btnEl.classList.add('active');
}

function setDurasiTarget(days, btnEl) {
    const tglMulaiEl = document.getElementById('targetTanggalMulai');
    const tglAkhirEl = document.getElementById('targetTanggalAkhir');
    if (!tglMulaiEl || !tglAkhirEl) return;

    let baseDate = tglMulaiEl.value ? new Date(tglMulaiEl.value) : new Date();
    if (isNaN(baseDate.getTime())) baseDate = new Date();

    const endDate = new Date(baseDate.getTime() + days * 24 * 60 * 60 * 1000);
    const yyyy = endDate.getFullYear();
    const mm = String(endDate.getMonth() + 1).padStart(2, '0');
    const dd = String(endDate.getDate()).padStart(2, '0');
    tglAkhirEl.value = `${yyyy}-${mm}-${dd}`;

    document.querySelectorAll('.duration-pill-btn').forEach(b => b.classList.remove('active'));
    if (btnEl) btnEl.classList.add('active');
}

function onTanggalMulaiChange() {
    const activeDurBtn = document.querySelector('.duration-pill-btn.active');
    if (activeDurBtn) {
        const text = activeDurBtn.innerText;
        const match = text.match(/\d+/);
        if (match) {
            setDurasiTarget(parseInt(match[0], 10), activeDurBtn);
        }
    }
}

function fokuskanSantri(id) {
    const card = document.querySelector(`.target-siswa-card[data-id="${id}"]`);
    if (card) {
        card.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        card.click();
    }
}

function pilihSantriTarget(id, nama, nis, el) {
    santriTargetTerpilih = { id, nama, nis };

    // Update border aktif #11756D dan latar #F0FDF9
    document.querySelectorAll('.target-siswa-card').forEach(card => card.classList.remove('aktif'));
    if (el) {
        el.classList.add('aktif');
    }

    // Sembunyikan empty state dan munculkan form
    const emptyState = document.getElementById('targetEmptyState');
    const formArea   = document.getElementById('targetFormArea');
    if (emptyState) emptyState.style.display = 'none';
    if (formArea)   formArea.style.display   = 'block';

    // Update ID siswa di input tersembunyi
    const inputSiswaId = document.getElementById('targetInputSiswaId');
    if (inputSiswaId) inputSiswaId.value = id;

    // Header info santri terpilih
    const headerNama = document.getElementById('targetHeaderNama');
    const headerSub  = document.getElementById('targetHeaderSub');
    const headerAvatar = document.getElementById('targetHeaderAvatar');
    if (headerNama) headerNama.textContent = nama;
    if (headerSub)  headerSub.textContent  = `NIS: ${nis || '-'} • Halaqah <?= htmlspecialchars($halaqah_name) ?>`;

    // Compute initials for header avatar
    if (headerAvatar) {
        const words = (nama || '').trim().split(/\s+/);
        let ini = '';
        if (words[0]) ini += words[0].charAt(0).toUpperCase();
        if (words[1]) ini += words[1].charAt(0).toUpperCase();
        headerAvatar.textContent = ini || 'S';
    }

    // Tampilkan kotak target aktif santri terpilih jika sudah ada target aktif
    const targetBox    = document.getElementById('targetAktifSantriBox');
    const infoSantriNama = document.getElementById('infoTargetSantriNama');
    const infoAwal     = document.getElementById('infoTargetSurahAwal');
    const infoAkhir    = document.getElementById('infoTargetSurahAkhir');
    const infoTanggal  = document.getElementById('infoTargetRentangTanggal');

    const keyId = String(id);
    if (targetAktifMap && targetAktifMap[keyId]) {
        const item = targetAktifMap[keyId];
        if (infoSantriNama) infoSantriNama.textContent = nama;
        if (infoAwal)    infoAwal.textContent    = item.surah_awal || '-';
        if (infoAkhir)   infoAkhir.textContent   = item.surah_akhir || '-';
        if (infoTanggal) infoTanggal.textContent = (item.tanggal_mulai || '') + ' s/d ' + (item.tanggal_akhir || '');
        if (targetBox)   targetBox.style.display  = 'block';

        // Auto-select dropdown with current target
        const selAwal  = document.getElementById('targetSurahAwal');
        const selAkhir = document.getElementById('targetSurahAkhir');
        const tglMulai = document.getElementById('targetTanggalMulai');
        const tglAkhir = document.getElementById('targetTanggalAkhir');
        if (selAwal && item.surah_awal) selAwal.value = item.surah_awal;
        if (selAkhir && item.surah_akhir) selAkhir.value = item.surah_akhir;
        if (tglMulai && item.tanggal_mulai) tglMulai.value = item.tanggal_mulai;
        if (tglAkhir && item.tanggal_akhir) tglAkhir.value = item.tanggal_akhir;
    } else {
        if (targetBox) targetBox.style.display = 'none';
    }
}

function batalPilihTarget() {
    santriTargetTerpilih = null;

    // Reset pilihan aktif kartu siswa
    document.querySelectorAll('.target-siswa-card').forEach(card => card.classList.remove('aktif'));

    // Sembunyikan form dan kembalikan ke empty state
    const emptyState = document.getElementById('targetEmptyState');
    const formArea   = document.getElementById('targetFormArea');
    if (emptyState) emptyState.style.display = 'block';
    if (formArea)   formArea.style.display   = 'none';

    // Reset nilai form
    const form = document.getElementById('formTetapkanTarget');
    if (form) form.reset();

    const inputSiswaId = document.getElementById('targetInputSiswaId');
    if (inputSiswaId) inputSiswaId.value = '';

    const targetBox = document.getElementById('targetAktifSantriBox');
    if (targetBox) targetBox.style.display = 'none';
}

function onSurahAwalChange() {
    const selAwal  = document.getElementById('targetSurahAwal');
    const selAkhir = document.getElementById('targetSurahAkhir');
    if (!selAwal || !selAkhir) return;

    const valAwal = selAwal.value;
    const idxAwal = targetUrutanOpsi.indexOf(valAwal);

    // Jika surah akhir belum dipilih atau indeksnya lebih kecil dari surah awal, set sama dengan surah awal
    if (idxAwal !== -1) {
        const valAkhir = selAkhir.value;
        const idxAkhir = targetUrutanOpsi.indexOf(valAkhir);
        if (idxAkhir === -1 || idxAkhir < idxAwal) {
            selAkhir.value = valAwal;
        }
    }
}

function validasiFormTarget(e) {
    const inputSiswaId = document.getElementById('targetInputSiswaId');
    if (!inputSiswaId || !inputSiswaId.value) {
        alert('Silakan pilih siswa terlebih dahulu di panel kiri.');
        if (e) e.preventDefault();
        return false;
    }

    const selAwal  = document.getElementById('targetSurahAwal');
    const selAkhir = document.getElementById('targetSurahAkhir');
    const tglMulai = document.getElementById('targetTanggalMulai');
    const tglAkhir = document.getElementById('targetTanggalAkhir');

    if (!selAwal || !selAwal.value) {
        alert('Silakan pilih batas awal hafalan.');
        selAwal?.focus();
        if (e) e.preventDefault();
        return false;
    }

    if (!selAkhir || !selAkhir.value) {
        alert('Silakan pilih batas akhir hafalan.');
        selAkhir?.focus();
        if (e) e.preventDefault();
        return false;
    }

    const idxAwal  = targetUrutanOpsi.indexOf(selAwal.value);
    const idxAkhir = targetUrutanOpsi.indexOf(selAkhir.value);

    if (idxAwal === -1 || idxAkhir === -1) {
        alert('Pilihan juz atau surah tidak valid.');
        if (e) e.preventDefault();
        return false;
    }

    if (idxAkhir < idxAwal) {
        alert('Batas akhir hafalan tidak boleh lebih kecil dari mulai hafalan.');
        selAkhir.focus();
        if (e) e.preventDefault();
        return false;
    }

    if (tglMulai && tglAkhir && tglMulai.value && tglAkhir.value) {
        if (tglAkhir.value < tglMulai.value) {
            alert('Tanggal akhir tidak boleh lebih awal dari tanggal mulai.');
            tglAkhir.focus();
            if (e) e.preventDefault();
            return false;
        }
    }

    return true;
}

// Pencarian Real-Time Siswa Target
document.getElementById('cariSantriTarget')?.addEventListener('input', function() {
    const val = this.value.trim().toLowerCase();
    document.querySelectorAll('.target-siswa-card').forEach(card => {
        const nama = (card.getAttribute('data-nama') || '').toLowerCase();
        const nis  = (card.getAttribute('data-nis') || '').toLowerCase();
        if (nama.includes(val) || nis.includes(val)) {
            card.style.display = 'flex';
        } else {
            card.style.display = 'none';
        }
    });
});

// Inisialisasi otomatis jika ada siswa yang difokuskan (misalnya setelah POST error/sukses)
window.addEventListener('DOMContentLoaded', () => {
    const cardAktif = document.querySelector('.target-siswa-card.aktif');
    if (cardAktif) {
        const id   = parseInt(cardAktif.getAttribute('data-id'), 10);
        const nama = cardAktif.getAttribute('data-nama');
        const nis  = cardAktif.getAttribute('data-nis');
        pilihSantriTarget(id, nama, nis, cardAktif);
    }
});
</script>
</body>
</html>
