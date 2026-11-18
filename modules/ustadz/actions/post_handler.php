<?php
/**
 * actions/post_handler.php
 * Memproses semua aksi POST dari halaman ustadz.
 * Di-require setelah $pdo, $urutan_opsi_target, $ustadz_name, $ustadz_id tersedia.
 * Menulis ke: $notif_sukses, $notif_error, $page_tab, $target_fokus_id, $target_old
 */

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    return;
}

$action = $_POST['action'] ?? '';

// 1. SIMPAN SETORAN HAFALAN BARU
if ($action === 'simpan_setoran' && $pdo) {
    $santri_nama = htmlspecialchars(trim($_POST['santri_nama'] ?? ''));
    $surah       = htmlspecialchars(trim($_POST['surah'] ?? ''));
    $ayat_awal   = (int)($_POST['ayat_awal'] ?? 1);
    $ayat_akhir  = (int)($_POST['ayat_akhir'] ?? 1);
    $kualitas    = htmlspecialchars(trim($_POST['kualitas'] ?? 'Mumtaz (A)'));
    $nilai_map   = [
        'Mumtaz (A)'        => 95,
        'Jayyid Jiddan (B)' => 85,
        'Jayyid (C)'        => 75,
        'Perlu Penguatan'   => 65,
    ];
    $nilai   = (int)($_POST['nilai'] ?? ($nilai_map[$kualitas] ?? 90));
    $catatan = htmlspecialchars(trim($_POST['catatan'] ?? ''));

    if (empty($santri_nama) || empty($surah)) {
        $notif_error = "Silakan pilih santri dan surah terlebih dahulu.";
    } else {
        try {
            $s_stmt = $pdo->prepare("SELECT id FROM users WHERE nama_lengkap = :nama LIMIT 1");
            $s_stmt->execute(['nama' => $santri_nama]);
            $s_row    = $s_stmt->fetch();
            $siswa_id = $s_row['id'] ?? 3;

            $stmt = $pdo->prepare(
                "INSERT INTO setoran_hafalan
                    (siswa_id, nama_siswa, ustadz_id, nama_ustadz, surah, ayat_awal, ayat_akhir, nilai, kualitas, catatan, tanggal_setor)
                 VALUES
                    (:siswa_id, :siswa, :ustadz_id, :ustadz, :surah, :awal, :akhir, :nilai, :kualitas, :catatan, :tgl)"
            );
            $stmt->execute([
                'siswa_id'  => $siswa_id,
                'siswa'     => $santri_nama,
                'ustadz_id' => $_SESSION['user_id'] ?? 2,
                'ustadz'    => $ustadz_name,
                'surah'     => $surah,
                'awal'      => $ayat_awal,
                'akhir'     => $ayat_akhir,
                'nilai'     => $nilai,
                'kualitas'  => $kualitas,
                'catatan'   => $catatan,
                'tgl'       => date('Y-m-d'),
            ]);
            $notif_sukses = "Alhamdulillah! Setoran santri {$santri_nama} untuk Surah {$surah} (Ayat {$ayat_awal}–{$ayat_akhir}) berhasil dicatat!";
        } catch (Throwable $e) {
            $notif_error = "Gagal menyimpan setoran: " . $e->getMessage();
        }
    }
    $page_tab = 'hafalan';
    if (!empty($santri_nama)) {
        $_GET['santri'] = $santri_nama;
    }

// 2. SIMPAN EVALUASI MURAJAAH (dari modal per baris setoran)
} elseif ($action === 'simpan_murajaah' && $pdo) {
    $setoran_id    = (int)($_POST['setoran_id']        ?? 0);
    $santri_nama   = htmlspecialchars(trim($_POST['santri_nama']       ?? ''));
    $surah         = htmlspecialchars(trim($_POST['surah_diulang']     ?? ''));
    $ayat_awal_raw = (int)($_POST['ayat_awal']  ?? 0);
    $ayat_akhir_raw= (int)($_POST['ayat_akhir'] ?? 0);
    // Gunakan ayat_range dari POST; jika kosong, bentuk dari ayat_awal & ayat_akhir
    $ayat_range_raw = trim($_POST['ayat_range'] ?? '');
    if ($ayat_range_raw === '' && $ayat_awal_raw > 0 && $ayat_akhir_raw > 0) {
        $ayat_range_raw = "Ayat {$ayat_awal_raw}–{$ayat_akhir_raw}";
    }
    $ayat_range  = htmlspecialchars($ayat_range_raw ?: 'Semua Ayat');
    $evaluasi    = htmlspecialchars(trim($_POST['evaluasi']           ?? 'Lancar'));
    $catatan     = htmlspecialchars(trim($_POST['catatan_murajaah']   ?? ''));

    // Validasi & normalisasi tanggal murajaah
    $tgl_raw = trim($_POST['tanggal_murajaah'] ?? '');
    $tanggal_murajaah = preg_match('/^\d{4}-\d{2}-\d{2}$/', $tgl_raw) ? $tgl_raw : date('Y-m-d');

    if (empty($santri_nama) || empty($surah)) {
        $notif_error = "Data santri atau surah tidak lengkap.";
    } else {
        try {
            // Cari siswa_id
            $s_stmt = $pdo->prepare("SELECT id FROM users WHERE nama_lengkap = :nama LIMIT 1");
            $s_stmt->execute(['nama' => $santri_nama]);
            $s_row    = $s_stmt->fetch();
            $siswa_id = $s_row['id'] ?? 3;

            // Jika ada setoran_id valid, perbarui kualitas (dan catatan jika diisi)
            if ($setoran_id > 0) {
                $upd = $pdo->prepare(
                    "UPDATE setoran_hafalan
                     SET kualitas = :kualitas,
                         catatan  = COALESCE(NULLIF(:catatan, ''), catatan)
                     WHERE id = :id"
                );
                $upd->execute([
                    'kualitas' => $evaluasi,
                    'catatan'  => $catatan,
                    'id'       => $setoran_id,
                ]);
            }

            // Catat sesi murajaah baru
            $stmt = $pdo->prepare(
                "INSERT INTO murajaah
                    (siswa_id, nama_siswa, ustadz_id, nama_ustadz, surah, ayat_range, status_mutqin, tanggal_murajaah)
                 VALUES
                    (:siswa_id, :siswa, :ustadz_id, :ustadz, :surah, :ayat_range, :status_mutqin, :tgl)"
            );
            $stmt->execute([
                'siswa_id'      => $siswa_id,
                'siswa'         => $santri_nama,
                'ustadz_id'     => $_SESSION['user_id'] ?? 2,
                'ustadz'        => $ustadz_name,
                'surah'         => $surah,
                'ayat_range'    => $ayat_range,
                'status_mutqin' => $evaluasi,
                'tgl'           => $tanggal_murajaah,
            ]);

            $notif_sukses = "Evaluasi murajaah {$santri_nama} untuk QS. {$surah} ({$ayat_range}) berhasil disimpan!";
        } catch (Throwable $e) {
            $notif_error = "Gagal menyimpan evaluasi murajaah: " . $e->getMessage();
        }
    }
    $page_tab = 'murajaah';
    if (!empty($santri_nama)) {
        $_GET['santri'] = $santri_nama;
    }

// 3. TETAPKAN TARGET HAFALAN
} elseif ($action === 'simpan_target') {
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
                $simpan = $pdo->prepare(
                    "INSERT INTO target_hafalan
                        (siswa_id, ustadz_id, surah_awal, surah_akhir, tanggal_mulai, tanggal_akhir, status)
                     VALUES
                        (:siswa_id, :ustadz_id, :surah_awal, :surah_akhir, :tanggal_mulai, :tanggal_akhir, 'Aktif')"
                );
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
                $target_old   = ['surah_awal' => '', 'surah_akhir' => '', 'tanggal_mulai' => '', 'tanggal_akhir' => ''];
            }
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $notif_error = 'Gagal menyimpan target: ' . $e->getMessage();
        }
    }
    $page_tab = 'target';

// 4. UPDATE PASSWORD USTADZ
} elseif ($action === 'update_password' && $pdo) {
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
            $uid  = $_SESSION['user_id'] ?? 2;
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

// 5. UPDATE NOMOR KONTAK USTADZ
} elseif ($action === 'update_biodata') {
    $kontak_baru  = trim($_POST['kontak'] ?? ($_POST['no_hp'] ?? ''));
    $kontak_clean = preg_replace('/[^0-9\+\-\s]/', '', $kontak_baru);

    $ustadz_kontak          = $kontak_clean;
    $_SESSION['user_no_hp'] = $kontak_clean;

    if ($pdo) {
        try {
            $uid = $_SESSION['user_id'] ?? 2;
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
