<?php
/**
 * modules/admin/data/queries.php
 * Pengambilan data dari database dan persiapan variabel tampilan.
 *
 * File ini hanya boleh di-require dari modules/admin/index.php.
 * Akses langsung via browser diblokir oleh guard ADMIN_INDEX.
 *
 * Variabel yang dihasilkan (tersedia di index.php dan semua tabs):
 *   $users_list, $halaqah_list, $total_santri, $total_ustadz,
 *   $admin_initials, $tab_labels, $tanggal_sekarang, $hari_id, $bulan_id
 */

if (!defined('ADMIN_INDEX')) {
    http_response_code(403);
    exit('403 Forbidden — Akses langsung tidak diizinkan.');
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

// Fallback data demo jika database kosong atau tidak terhubung
if (empty($users_list)) {
    $users_list = [
        ['id' => 1, 'username' => 'admin',  'nama_lengkap' => 'Administrator Sekolah',     'role' => 'admin',  'nip_nis' => '198700',             'halaqah' => '—',         'status' => 'aktif'],
        ['id' => 2, 'username' => 'ustadz', 'nama_lengkap' => 'Ust. Rival Alvian Melfandy','role' => 'ustadz', 'nip_nis' => '199205142023031004', 'halaqah' => 'Al-Furqan', 'status' => 'aktif'],
        ['id' => 3, 'username' => 'siswa',  'nama_lengkap' => 'Muhammad Fadhlan',           'role' => 'siswa',  'nip_nis' => '102601',             'halaqah' => 'Al-Furqan', 'status' => 'aktif'],
    ];
}

// ─────────────────────────────────────────────────────────────
// HITUNG METRIK RINGKASAN & DATA ANGGOTA KELOMPOK
// ─────────────────────────────────────────────────────────────
$total_santri = 0;
$total_ustadz = 0;
$ustadz_list  = [];
$siswa_list   = [];
$jumlah_siswa_per_halaqah = [];
$halaqah_members_map      = [];

foreach ($users_list as $u) {
    $role   = $u['role'] ?? '';
    $status = $u['status'] ?? 'aktif';

    if ($role === 'siswa') {
        $total_santri++;
        if ($status === 'aktif') {
            $siswa_list[] = $u;
            $hlq = trim($u['halaqah'] ?? '');
            if (!empty($hlq) && $hlq !== '—') {
                if (!isset($jumlah_siswa_per_halaqah[$hlq])) {
                    $jumlah_siswa_per_halaqah[$hlq] = 0;
                }
                $jumlah_siswa_per_halaqah[$hlq]++;

                if (!isset($halaqah_members_map[$hlq])) {
                    $halaqah_members_map[$hlq] = [];
                }
                $halaqah_members_map[$hlq][] = (int)$u['id'];
            }
        }
    } elseif ($role === 'ustadz') {
        $total_ustadz++;
        if ($status === 'aktif') {
            $ustadz_list[] = $u;
        }
    }
}

// ─────────────────────────────────────────────────────────────
// INISIAL AVATAR ADMIN (2 HURUF PERTAMA NAMA)
// ─────────────────────────────────────────────────────────────
$admin_initials = strtoupper(mb_substr(preg_replace('/[^A-Za-z ]/', '', $admin_name), 0, 1));
$name_parts = explode(' ', trim($admin_name));
if (count($name_parts) >= 2) {
    $admin_initials = strtoupper(mb_substr($name_parts[0], 0, 1) . mb_substr($name_parts[1], 0, 1));
}

// ─────────────────────────────────────────────────────────────
// LABEL BREADCRUMB PER TAB
// ─────────────────────────────────────────────────────────────
$tab_labels = [
    'rekap'    => 'Dashboard Rekap Tahfiz',
    'pengguna' => 'Data Pengguna',
    'halaqah'  => 'Kelompok Halaqah',
    'laporan'  => 'Laporan Tahfiz',
    'profil'   => 'Profil Saya',
];

// ─────────────────────────────────────────────────────────────
// FORMAT TANGGAL INDONESIA
// ─────────────────────────────────────────────────────────────
$hari_id  = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
$bulan_id = ['','Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
$tanggal_sekarang = $hari_id[date('w')] . ', ' . date('j') . ' ' . $bulan_id[(int)date('n')] . ' ' . date('Y');
