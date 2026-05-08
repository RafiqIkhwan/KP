<?php
// 1. Inisialisasi session
session_start();

// 2. Hapus semua variabel session
$_SESSION = array();

// 3. Jika ingin menghapus session secara total, hancurkan juga cookie session-nya
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 4. Hancurkan session di server
session_destroy();

// 5. Alihkan pengguna kembali ke halaman login (index.php)
header("Location: index.php");
exit();
?>