<?php
// Pastikan tidak ada output sebelum session_start()
ob_start();

// Jika session belum dimulai, mulai session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Hapus semua data sesi
$_SESSION = array();

// Hancurkan sesi
session_destroy();

// Hapus cookie sesi jika ada
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Bersihkan output buffer
ob_end_clean();

// Redirect ke halaman login
header("Location: index.php?page=LoginUser.php");
exit();
?>