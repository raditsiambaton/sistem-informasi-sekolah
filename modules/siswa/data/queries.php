<?php
/**
 * modules/siswa/data/queries.php
 * Query & helper data Modul Siswa:
 *   - Profil siswa dari tabel users
 *   - Riwayat setoran (setoran_hafalan) & murajaah
 *   - $data_siswa, $siswa_initials
 *   - resolveKualitas(), kualitasClass()
 * Guard: hanya boleh di-require dari modules/siswa/index.php
 */

if (!defined('SISWA_INDEX')) exit;

// ── Ambil data profil siswa terkini dari MySQL ───────────────
try {
    $pdo = koneksi_database();
    if ($pdo) {
        // Pastikan kolom no_hp tersedia
        try {
            $colCheck = $pdo->query("SHOW COLUMNS FROM users LIKE 'no_hp'")->fetchAll();
            if (empty($colCheck)) {
                $pdo->exec("ALTER TABLE users ADD COLUMN no_hp VARCHAR(20) NULL AFTER nama_lengkap");
            }
        } catch (Throwable $e) {}

        $u_stmt = $pdo->prepare(
            "SELECT id, nama_lengkap, nip_nis, halaqah, no_hp FROM users WHERE id = :id LIMIT 1"
        );
        $u_stmt->execute(['id' => $siswa_id]);
        $u_row = $u_stmt->fetch();
        if ($u_row) {
            $siswa_name   = $u_row['nama_lengkap'] ?? $siswa_name;
            $siswa_nis    = $u_row['nip_nis']      ?? $siswa_nis;
            $halaqah_name = $u_row['halaqah']      ?? $halaqah_name;
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

// ── Ambil riwayat setoran & murajaah ────────────────────────
$siswa_setoran  = [];
$siswa_murajaah = [];
try {
    $pdo = koneksi_database();
    if ($pdo) {
        $st = $pdo->prepare(
            "SELECT id, siswa_id, nama_siswa, ustadz_id, nama_ustadz, surah, ayat_awal, ayat_akhir,
                    nilai, kualitas, catatan, tanggal_setor
             FROM setoran_hafalan
             WHERE nama_siswa LIKE :n OR siswa_id = :id
             ORDER BY id DESC"
        );
        $st->execute(['n' => "%{$siswa_name}%", 'id' => $_SESSION['user_id'] ?? 3]);
        $siswa_setoran = $st->fetchAll();

        // Kolom catatan di tabel murajaah mungkin belum ada — tangkap error gracefully
        try {
            $sm = $pdo->prepare(
                "SELECT id, siswa_id, nama_siswa, ustadz_id, nama_ustadz, surah, ayat_range,
                        status_mutqin, tanggal_murajaah, catatan
                 FROM murajaah
                 WHERE nama_siswa LIKE :n OR siswa_id = :id
                 ORDER BY id DESC"
            );
            $sm->execute(['n' => "%{$siswa_name}%", 'id' => $_SESSION['user_id'] ?? 3]);
            $siswa_murajaah = $sm->fetchAll();
        } catch (Throwable $e2) {
            // Fallback: tanpa kolom catatan
            $sm2 = $pdo->prepare(
                "SELECT id, siswa_id, nama_siswa, ustadz_id, nama_ustadz, surah, ayat_range,
                         status_mutqin, tanggal_murajaah
                 FROM murajaah
                 WHERE nama_siswa LIKE :n OR siswa_id = :id
                 ORDER BY id DESC"
            );
            $sm2->execute(['n' => "%{$siswa_name}%", 'id' => $_SESSION['user_id'] ?? 3]);
            $rows = $sm2->fetchAll();
            foreach ($rows as &$r) { $r['catatan'] = ''; }
            unset($r);
            $siswa_murajaah = $rows;
        }
    }
} catch (Throwable $e) {}

// ── Helper: konversi nilai/status_mutqin → label kualitas ───
function resolveKualitas(string $kualitas, ?int $nilai): string
{
    $k = trim($kualitas);
    if ($k === '' && $nilai !== null) {
        if ($nilai >= 80) return 'Lancar';
        if ($nilai >= 65) return 'Kurang Lancar';
        return 'Tidak Lancar';
    }
    $low = mb_strtolower($k);
    if (in_array($low, ['lancar', 'sangat mutqin', 'mutqin'], true)) return 'Lancar';
    if (in_array($low, ['kurang lancar'], true))                      return 'Kurang Lancar';
    if (in_array($low, ['tidak lancar'], true))                       return 'Tidak Lancar';
    if ($nilai !== null) {
        if ($nilai >= 80) return 'Lancar';
        if ($nilai >= 65) return 'Kurang Lancar';
        return 'Tidak Lancar';
    }
    return $k !== '' ? $k : 'Lancar';
}

// ── Helper: class badge CSS sesuai label kualitas ────────────
function kualitasClass(string $label): string
{
    switch ($label) {
        case 'Kurang Lancar': return 'badge-kualitas-kurang';
        case 'Tidak Lancar':  return 'badge-kualitas-tidak';
        default:              return 'badge-kualitas-lancar';
    }
}

// ── Inisial avatar siswa (2 huruf pertama nama) ──────────────
$siswa_initials = strtoupper(mb_substr(preg_replace('/[^A-Za-z ]/', '', $siswa_name), 0, 1));
$name_parts = explode(' ', trim($siswa_name));
if (count($name_parts) >= 2) {
    $siswa_initials = strtoupper(
        mb_substr($name_parts[0], 0, 1) . mb_substr($name_parts[1], 0, 1)
    );
}
