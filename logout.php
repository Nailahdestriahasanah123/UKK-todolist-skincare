<?php
/**
 * ==============================================================
 * PROYEK: UKK 2026 - Asisten Pemrograman Junior
 * FILE: logout.php (Proses Keluar Akun)
 * ==============================================================
 * PENJELASAN UNTUK UJIAN UKK:
 * 1. session_start(): Membuka sesi yang sedang aktif.
 * 2. session_unset(): Mengosongkan semua data di array $_SESSION.
 * 3. session_destroy(): Menghancurkan file session fisik di server.
 * 4. header("Location: ..."): Mengalihkan pengguna kembali ke halaman login.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Hapus seluruh variabel sesi
$_SESSION = [];

// Jika menggunakan cookie session, hapus cookie tersebut
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Hancurkan session di server
session_destroy();

// Redirect ke halaman login dengan pesan manis
header("Location: login.php?status=success&pesan=" . urlencode("Kamu berhasil keluar. Sampai jumpa lagi, Cantik! 🌸"));
exit;
