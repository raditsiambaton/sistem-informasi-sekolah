<?php
/**
 * Konfigurasi Database
 * Sistem Informasi Sekolah & Monitoring Tahfiz
 * SMA IT Al-Fadhilah Pekanbaru
 *
 * File ini berisi konfigurasi koneksi database (MySQL/MariaDB) menggunakan PDO.
 * Sesuaikan nilai konstanta di bawah dengan lingkungan server Anda.
 */

// Konfigurasi koneksi database
define('DB_HOST', 'localhost');
define('DB_NAME', 'sistem_informasi_sekolah');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Data Source Name (DSN) untuk PDO
define('DB_DSN', 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET);

// Opsi koneksi PDO
define('DB_OPTIONS', [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
]);

/**
 * Membuat dan mengembalikan koneksi PDO ke database (singleton).
 *
 * @return PDO
 */
function koneksi_database(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        try {
            $pdo = new PDO(DB_DSN, DB_USER, DB_PASS, DB_OPTIONS);
        } catch (PDOException $e) {
            // TODO: ganti dengan halaman error yang ramah pengguna di lingkungan produksi
            die('Koneksi database gagal: ' . $e->getMessage());
        }
    }

    return $pdo;
}
