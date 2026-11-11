<?php
/**
 * Konfigurasi Sesi
 * Sistem Informasi Sekolah & Monitoring Tahfiz
 * SMA IT Al-Fadhilah Pekanbaru
 *
 * File ini menginisialisasi dan mengkonfigurasi sesi PHP dengan pengaturan
 * keamanan yang baik. Sertakan file ini di setiap halaman yang membutuhkan autentikasi.
 */

// Pengaturan keamanan sesi
ini_set('session.use_strict_mode', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_samesite', 'Lax');

// Mulai sesi jika belum dimulai
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Memeriksa apakah pengguna sudah login.
 *
 * @return bool
 */
function sudah_login(): bool
{
    return isset($_SESSION['user_id']);
}

/**
 * Mengembalikan peran (role) pengguna yang sedang login, atau null.
 *
 * @return string|null
 */
function peran_pengguna(): ?string
{
    return $_SESSION['user_role'] ?? null;
}

/**
 * Mengarahkan pengguna yang belum login ke halaman login.
 *
 * @param string $halaman_login URL halaman login
 */
function wajib_login(string $halaman_login = '/modules/auth/index.php'): void
{
    if (!sudah_login()) {
        header('Location: ' . $halaman_login);
        exit;
    }
}
