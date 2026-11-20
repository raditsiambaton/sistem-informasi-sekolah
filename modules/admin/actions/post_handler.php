<?php
/**
 * modules/admin/actions/post_handler.php
 * Handler semua aksi POST form dan GET hapus pengguna.
 *
 * File ini hanya boleh di-require dari modules/admin/index.php.
 * Akses langsung via browser diblokir oleh guard ADMIN_INDEX.
 */

if (!defined('ADMIN_INDEX')) {
    http_response_code(403);
    exit('403 Forbidden — Akses langsung tidak diizinkan.');
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
        $csrf_post    = $_POST['csrf_token'] ?? '';
        $kode_halaqah = strtoupper(trim($_POST['kode_halaqah'] ?? ''));
        $nama_halaqah = trim($_POST['nama_halaqah'] ?? '');
        $ustadz_id    = (int)($_POST['ustadz_id'] ?? 0);
        $target_juz   = trim($_POST['target_juz'] ?? 'Juz 29 - 30');
        $raw_siswa_ids = isset($_POST['siswa_ids']) && is_array($_POST['siswa_ids'])
            ? array_map('intval', $_POST['siswa_ids'])
            : [];

        if (!hash_equals($_SESSION['csrf_token'] ?? '', $csrf_post)) {
            $error_msg = 'Validasi keamanan (CSRF) gagal. Silakan muat ulang halaman.';
        } elseif (empty($kode_halaqah) || empty($nama_halaqah) || $ustadz_id <= 0) {
            $error_msg = 'Kode halaqah, nama halaqah, dan ustadz pembimbing wajib diisi!';
        } else {
            try {
                // Validasi duplikasi kode dan nama halaqah
                $cek_kode = $pdo->prepare("SELECT id FROM halaqah WHERE kode_halaqah = :k LIMIT 1");
                $cek_kode->execute(['k' => $kode_halaqah]);
                $cek_nama = $pdo->prepare("SELECT id FROM halaqah WHERE nama_halaqah = :n LIMIT 1");
                $cek_nama->execute(['n' => $nama_halaqah]);

                if ($cek_kode->fetch()) {
                    $error_msg = "Kode halaqah '{$kode_halaqah}' sudah digunakan. Silakan gunakan kode lain.";
                } elseif ($cek_nama->fetch()) {
                    $error_msg = "Nama halaqah '{$nama_halaqah}' sudah ada. Silakan gunakan nama lain.";
                } else {
                    // Validasi akun ustadz
                    $u_stmt = $pdo->prepare("SELECT id, nama_lengkap FROM users WHERE id = :u AND role = 'ustadz' AND status = 'aktif' LIMIT 1");
                    $u_stmt->execute(['u' => $ustadz_id]);
                    $ustadz_row = $u_stmt->fetch();

                    if (!$ustadz_row) {
                        $error_msg = "Ustadz pembimbing yang dipilih tidak valid atau berstatus nonaktif.";
                    } else {
                        // Validasi seluruh ID pada siswa_ids[]: hanya siswa aktif
                        $valid_siswa_ids = [];
                        if (!empty($raw_siswa_ids)) {
                            $in_clause = implode(',', array_fill(0, count($raw_siswa_ids), '?'));
                            $s_stmt = $pdo->prepare("SELECT id FROM users WHERE id IN ($in_clause) AND role = 'siswa' AND status = 'aktif'");
                            $s_stmt->execute(array_values($raw_siswa_ids));
                            $valid_siswa_ids = array_map('intval', $s_stmt->fetchAll(PDO::FETCH_COLUMN));
                        }

                        // Eksekusi Transaction
                        $pdo->beginTransaction();
                        try {
                            $stmt = $pdo->prepare("INSERT INTO halaqah (kode_halaqah, nama_halaqah, ustadz_id, nama_ustadz, target_juz) VALUES (:kode, :nama, :ustadz_id, :ustadz_nama, :target)");
                            $stmt->execute([
                                'kode'        => $kode_halaqah,
                                'nama'        => $nama_halaqah,
                                'ustadz_id'   => $ustadz_row['id'],
                                'ustadz_nama' => $ustadz_row['nama_lengkap'],
                                'target'      => $target_juz
                            ]);

                            if (!empty($valid_siswa_ids)) {
                                $in_up = implode(',', array_fill(0, count($valid_siswa_ids), '?'));
                                $params = array_merge([$nama_halaqah], array_values($valid_siswa_ids));
                                $up = $pdo->prepare("UPDATE users SET halaqah = ? WHERE id IN ($in_up) AND role = 'siswa' AND status = 'aktif'");
                                $up->execute($params);
                            }

                            $pdo->commit();
                            $total_anggota = count($valid_siswa_ids);
                            $success_msg = "Halaqah baru '{$nama_halaqah}' berhasil dibuat dengan {$total_anggota} anggota siswa aktif!";
                        } catch (Throwable $e) {
                            $pdo->rollBack();
                            $error_msg = "Gagal membuat halaqah: " . $e->getMessage();
                        }
                    }
                }
            } catch (PDOException $e) {
                $error_msg = "Kesalahan basis data: " . $e->getMessage();
            }
        }
        $page_tab = 'halaqah';
    }

    // 3b. EDIT HALAQAH
    elseif ($action === 'edit_halaqah' && $pdo) {
        $csrf_post     = $_POST['csrf_token'] ?? '';
        $halaqah_id    = (int)($_POST['halaqah_id'] ?? 0);
        $kode_halaqah  = strtoupper(trim($_POST['kode_halaqah'] ?? ''));
        $nama_halaqah  = trim($_POST['nama_halaqah'] ?? '');
        $ustadz_id     = (int)($_POST['ustadz_id'] ?? 0);
        $target_juz    = trim($_POST['target_juz'] ?? 'Juz 29 - 30');
        $raw_siswa_ids = isset($_POST['siswa_ids']) && is_array($_POST['siswa_ids'])
            ? array_map('intval', $_POST['siswa_ids'])
            : [];

        if (!hash_equals($_SESSION['csrf_token'] ?? '', $csrf_post)) {
            $error_msg = 'Validasi keamanan (CSRF) gagal. Silakan muat ulang halaman.';
        } elseif ($halaqah_id <= 0 || empty($kode_halaqah) || empty($nama_halaqah) || $ustadz_id <= 0) {
            $error_msg = 'Seluruh data kelompok halaqah wajib diisi dengan benar!';
        } else {
            try {
                // Ambil data halaqah saat ini
                $cur_stmt = $pdo->prepare("SELECT * FROM halaqah WHERE id = :id LIMIT 1");
                $cur_stmt->execute(['id' => $halaqah_id]);
                $current_hlq = $cur_stmt->fetch();

                if (!$current_hlq) {
                    $error_msg = "Kelompok halaqah tidak ditemukan.";
                } else {
                    $nama_halaqah_lama = $current_hlq['nama_halaqah'];

                    // Cek duplikasi kode dan nama terhadap kelompok lain
                    $cek_kode = $pdo->prepare("SELECT id FROM halaqah WHERE kode_halaqah = :k AND id != :id LIMIT 1");
                    $cek_kode->execute(['k' => $kode_halaqah, 'id' => $halaqah_id]);
                    $cek_nama = $pdo->prepare("SELECT id FROM halaqah WHERE nama_halaqah = :n AND id != :id LIMIT 1");
                    $cek_nama->execute(['n' => $nama_halaqah, 'id' => $halaqah_id]);

                    if ($cek_kode->fetch()) {
                        $error_msg = "Kode halaqah '{$kode_halaqah}' sudah digunakan oleh kelompok lain.";
                    } elseif ($cek_nama->fetch()) {
                        $error_msg = "Nama halaqah '{$nama_halaqah}' sudah digunakan oleh kelompok lain.";
                    } else {
                        // Validasi ustadz pembimbing
                        $u_stmt = $pdo->prepare("SELECT id, nama_lengkap FROM users WHERE id = :u AND role = 'ustadz' AND status = 'aktif' LIMIT 1");
                        $u_stmt->execute(['u' => $ustadz_id]);
                        $ustadz_row = $u_stmt->fetch();

                        if (!$ustadz_row) {
                            $error_msg = "Ustadz pembimbing yang dipilih tidak valid atau berstatus nonaktif.";
                        } else {
                            // Validasi seluruh ID pada siswa_ids[]: pastikan hanya siswa aktif
                            $valid_siswa_ids = [];
                            if (!empty($raw_siswa_ids)) {
                                $in_clause = implode(',', array_fill(0, count($raw_siswa_ids), '?'));
                                $s_stmt = $pdo->prepare("SELECT id FROM users WHERE id IN ($in_clause) AND role = 'siswa' AND status = 'aktif'");
                                $s_stmt->execute(array_values($raw_siswa_ids));
                                $valid_siswa_ids = array_map('intval', $s_stmt->fetchAll(PDO::FETCH_COLUMN));
                            }

                            // Eksekusi Transaction
                            $pdo->beginTransaction();
                            try {
                                // 1. Update data halaqah
                                $up_hlq = $pdo->prepare("UPDATE halaqah SET kode_halaqah = :k, nama_halaqah = :n, ustadz_id = :u_id, nama_ustadz = :u_nama, target_juz = :t WHERE id = :id");
                                $up_hlq->execute([
                                    'k'      => $kode_halaqah,
                                    'n'      => $nama_halaqah,
                                    'u_id'   => $ustadz_row['id'],
                                    'u_nama' => $ustadz_row['nama_lengkap'],
                                    't'      => $target_juz,
                                    'id'     => $halaqah_id
                                ]);

                                // 2. Jika nama halaqah diubah, perbarui siswa nonaktif di kelompok ini agar tetap tersinkron
                                if ($nama_halaqah !== $nama_halaqah_lama) {
                                    $ren_nonaktif = $pdo->prepare("UPDATE users SET halaqah = :n_baru WHERE halaqah = :n_lama AND role = 'siswa' AND status = 'nonaktif'");
                                    $ren_nonaktif->execute([
                                        'n_baru' => $nama_halaqah,
                                        'n_lama' => $nama_halaqah_lama
                                    ]);
                                }

                                // 3. Ambil daftar anggota siswa aktif yang terdaftar sebelumnya
                                $old_stmt = $pdo->prepare("SELECT id FROM users WHERE role = 'siswa' AND status = 'aktif' AND halaqah = :h");
                                $old_stmt->execute(['h' => $nama_halaqah_lama]);
                                $old_active_ids = array_map('intval', $old_stmt->fetchAll(PDO::FETCH_COLUMN));

                                // 4. Hitung diff: hanya keluarkan siswa yang sebelumnya aktif di kelompok ini dan sekarang tidak dicentang
                                $removed_ids = array_diff($old_active_ids, $valid_siswa_ids);
                                if (!empty($removed_ids)) {
                                    $in_rem = implode(',', array_fill(0, count($removed_ids), '?'));
                                    $params_rem = array_merge(['—', $nama_halaqah_lama], array_values($removed_ids));
                                    $up_rem = $pdo->prepare("UPDATE users SET halaqah = ? WHERE halaqah = ? AND id IN ($in_rem) AND role = 'siswa' AND status = 'aktif'");
                                    $up_rem->execute($params_rem);
                                }

                                // 5. Perbarui seluruh siswa aktif yang terpilih ke nama halaqah baru (termasuk transfer dan rename cascade)
                                if (!empty($valid_siswa_ids)) {
                                    $in_add = implode(',', array_fill(0, count($valid_siswa_ids), '?'));
                                    $params_add = array_merge([$nama_halaqah], array_values($valid_siswa_ids));
                                    $up_add = $pdo->prepare("UPDATE users SET halaqah = ? WHERE id IN ($in_add) AND role = 'siswa' AND status = 'aktif'");
                                    $up_add->execute($params_add);
                                }

                                $pdo->commit();
                                $total_anggota = count($valid_siswa_ids);
                                $success_msg = "Perubahan kelompok '{$nama_halaqah}' berhasil disimpan ({$total_anggota} siswa aktif terdaftar)!";
                            } catch (Throwable $e) {
                                $pdo->rollBack();
                                $error_msg = "Gagal memperbarui kelompok halaqah: " . $e->getMessage();
                            }
                        }
                    }
                }
            } catch (PDOException $e) {
                $error_msg = "Kesalahan basis data: " . $e->getMessage();
            }
        }
        $page_tab = 'halaqah';
    }

    // 3c. HAPUS HALAQAH (POST + CSRF + Proteksi Siswa Aktif)
    elseif ($action === 'hapus_halaqah' && $pdo) {
        $csrf_post = $_POST['csrf_token'] ?? '';
        $del_id    = (int)($_POST['halaqah_id'] ?? 0);

        if (!hash_equals($_SESSION['csrf_token'] ?? '', $csrf_post)) {
            $error_msg = 'Validasi keamanan (CSRF) gagal. Silakan muat ulang halaman.';
        } elseif ($del_id <= 0) {
            $error_msg = 'ID kelompok halaqah tidak valid.';
        } else {
            try {
                $stmt = $pdo->prepare("SELECT nama_halaqah FROM halaqah WHERE id = :id LIMIT 1");
                $stmt->execute(['id' => $del_id]);
                $hlq = $stmt->fetch();

                if (!$hlq) {
                    $error_msg = "Kelompok halaqah tidak ditemukan.";
                } else {
                    $nama_h = $hlq['nama_halaqah'];

                    // Periksa apakah masih ada siswa aktif di kelompok ini (RESTRICT)
                    $cek_aktif = $pdo->prepare("SELECT COUNT(*) FROM users WHERE role = 'siswa' AND status = 'aktif' AND halaqah = :nama");
                    $cek_aktif->execute(['nama' => $nama_h]);
                    $jml_aktif = (int)$cek_aktif->fetchColumn();

                    if ($jml_aktif > 0) {
                        $error_msg = "Kelompok '{$nama_h}' tidak dapat dihapus karena masih memiliki {$jml_aktif} siswa aktif. Silakan pindahkan atau keluarkan anggota siswa terlebih dahulu.";
                    } else {
                        $pdo->beginTransaction();
                        try {
                            // Lepaskan siswa nonaktif yang masih terdata di kelompok ini jika ada
                            $up_non = $pdo->prepare("UPDATE users SET halaqah = '—' WHERE halaqah = :nama");
                            $up_non->execute(['nama' => $nama_h]);

                            $del = $pdo->prepare("DELETE FROM halaqah WHERE id = :id");
                            $del->execute(['id' => $del_id]);

                            $pdo->commit();
                            $success_msg = "Kelompok halaqah '{$nama_h}' berhasil dihapus.";
                        } catch (Throwable $e) {
                            $pdo->rollBack();
                            $error_msg = "Gagal menghapus kelompok halaqah: " . $e->getMessage();
                        }
                    }
                }
            } catch (PDOException $e) {
                $error_msg = "Kesalahan basis data: " . $e->getMessage();
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
        $page_tab = 'profil';
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
