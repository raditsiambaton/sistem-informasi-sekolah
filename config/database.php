<?php
/**
 * Konfigurasi Database
 * Sistem Informasi Sekolah & Monitoring Tahfiz
 * SMA IT Al-Fadhilah Pekanbaru
 *
 * File ini berisi konfigurasi koneksi database (MySQL/MariaDB) menggunakan PDO.
 * Dilengkapi kemampuan auto-create database dan auto-import tabel.
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
 * Dilengkapi kemampuan auto-create database & auto-import skema jika database belum ada.
 *
 * @return PDO|null
 */
function koneksi_database(): ?PDO
{
    static $pdo = null;

    if ($pdo !== null) {
        return $pdo;
    }

    try {
        $pdo = new PDO(DB_DSN, DB_USER, DB_PASS, DB_OPTIONS);
        return $pdo;
    } catch (PDOException $e) {
        // Jika database belum ada (Error 1049: Unknown database), buat secara otomatis
        if ($e->getCode() == 1049 || strpos($e->getMessage(), 'Unknown database') !== false) {
            try {
                $rootPdo = new PDO('mysql:host=' . DB_HOST . ';charset=' . DB_CHARSET, DB_USER, DB_PASS, DB_OPTIONS);
                $rootPdo->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                
                $sqlFile = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'database.sql';
                if (file_exists($sqlFile)) {
                    $sqlContent = file_get_contents($sqlFile);
                    $rootPdo->exec("USE `" . DB_NAME . "`");
                    $rootPdo->exec($sqlContent);
                }

                $pdo = new PDO(DB_DSN, DB_USER, DB_PASS, DB_OPTIONS);
                return $pdo;
            } catch (Throwable $innerError) {
                error_log("Gagal membuat database otomatis: " . $innerError->getMessage());
            }
        }

        // Lemparkan exception agar pemanggil dapat menangani fallback secara elegan
        throw $e;
    }
}
