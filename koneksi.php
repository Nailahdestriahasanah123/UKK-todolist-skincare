<?php
/**
 * ==============================================================
 * FILE: config/koneksi.php
 * FUNGSI: Menghubungkan aplikasi web PHP dengan database MySQL
 * TEKNOLOGI: PDO (PHP Data Objects)
 * ==============================================================
 * PENJELASAN UNTUK UJIAN UKK:
 * 1. PDO adalah standar modern koneksi database di PHP yang fleksibel & aman.
 * 2. Menggunakan try-catch: Jika database gagal terhubung (misal MySQL XAMPP mati),
 *    aplikasi tidak crash berantakan, melainkan menampilkan pesan error yang ramah.
 * 3. PDO::ATTR_ERRMODE => ERRMODE_EXCEPTION: Memaksa PDO melempar error saat query gagal.
 * 4. PDO::ATTR_DEFAULT_FETCH_MODE => FETCH_ASSOC: Data query otomatis dikembalikan 
 *    sebagai array asosiatif (misal $data['task_name']).
 */

// Konfigurasi Database (Sesuaikan dengan XAMPP / Laragon Anda)
$db_host = 'localhost';
$db_user = 'root';          // Default username XAMPP adalah 'root'
$db_pass = '';              // Default password XAMPP adalah kosong
$db_name = 'db_skincare_todolist';
$db_port = 3306;

// Data Source Name (DSN)
$dsn = "mysql:host={$db_host};port={$db_port};dbname={$db_name};charset=utf8mb4";

try {
    // Membuat instance PDO
    $pdo = new PDO($dsn, $db_user, $db_pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);

    // Auto-migration sederhana untuk UKK:
    // Menambahkan kolom order_index otomatis jika belum ada di tabel tasks
    try {
        $pdo->exec("ALTER TABLE `tasks` ADD COLUMN `order_index` INT NOT NULL DEFAULT 0");
    } catch (Exception $colEx) {
        // Kolom sudah ada, tidak perlu tindakan apapun
    }

} catch (PDOException $e) {
    // Tampilan error ramah jika belum import database atau MySQL mati
    die("
        <div style='font-family: sans-serif; text-align: center; margin-top: 50px; color: #db2777;'>
            <h2>Oops! Gagal Terhubung ke Database 🌸</h2>
            <p>Pastikan MySQL di XAMPP / Laragon sudah dijalankan (Start) dan database <b>{$db_name}</b> sudah diimport.</p>
            <p style='color: #666; font-size: 13px;'>Pesan Teknis: " . htmlspecialchars($e->getMessage()) . "</p>
        </div>
    ");
}
