<?php
/**
 * modules/auth/logout.php
 * Script Pemutus Sesi (Logout)
 */

require_once '../../config/session.php';

// Hapus seluruh variabel sesi
$_SESSION = [];

// Hapus cookie sesi jika ada
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Hancurkan sesi
session_destroy();

// Redirect ke halaman login dengan notifikasi
header('Location: login.php?status=logged_out');
exit;

