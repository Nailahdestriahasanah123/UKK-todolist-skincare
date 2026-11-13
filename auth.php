<?php
/**
 * ==============================================================
 * FILE: config/auth.php
 * FUNGSI: Mengelola Session dan Proteksi Halaman (Middleware Login)
 * ==============================================================
 * PENJELASAN UNTUK UJIAN UKK:
 * 1. session_start(): Mengaktifkan penyimpanan session di server.
 *    Session digunakan untuk mengingat data user (seperti ID dan nama)
 *    selama browser masih dibuka.
 * 2. requireLogin(): Fungsi penjaga (Gatekeeper / Middleware).
 *    Jika pengguna belum memiliki $_SESSION['user_id'], mereka akan
 *    otomatis diarahkan (redirect) ke halaman login.php.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Cek apakah pengguna sudah login
 * @return bool
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Proteksi halaman: Paksa user login jika belum punya session
 */
function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: login.php?status=error&pesan=" . urlencode("Silakan login terlebih dahulu untuk mengakses GlowTask 🌸"));
        exit;
    }
}

/**
 * Redirect jika user SUDAH login tapi mencoba akses login/register
 */
function redirectIfLoggedIn() {
    if (isLoggedIn()) {
        header("Location: index.php");
        exit;
    }
}
