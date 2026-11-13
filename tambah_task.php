<?php
/**
 * ==============================================================
 * FILE: actions/tambah_task.php
 * FUNGSI: Menambahkan rutinitas / tugas skincare baru ke database (CREATE)
 * ==============================================================
 * PENJELASAN UNTUK UJIAN UKK:
 * 1. Autentikasi Pengguna: Mengaitkan task baru dengan $_SESSION['user_id']
 *    sehingga to-do list menjadi bersifat personal untuk pengguna yang login.
 * 2. Prepared Statement (PDO): Mencegah SQL Injection.
 */

require_once '../config/koneksi.php';
require_once '../config/auth.php';

// Pastikan hanya user yang login yang bisa menambah data
if (!isLoggedIn()) {
    header("Location: ../login.php");
    exit;
}

$user_id = (int)$_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $task_name   = isset($_POST['task_name']) ? trim($_POST['task_name']) : '';
    $category_id = isset($_POST['category_id']) ? intval($_POST['category_id']) : 0;
    $target_time = isset($_POST['target_time']) ? trim($_POST['target_time']) : '';
    $notes       = isset($_POST['notes']) ? trim($_POST['notes']) : '';
    $priority    = isset($_POST['priority']) && in_array($_POST['priority'], ['wajib', 'rutin', 'opsional']) 
                    ? $_POST['priority'] 
                    : 'rutin';

    if (empty($task_name) || $category_id <= 0) {
        header("Location: ../index.php?status=error&pesan=" . urlencode("Nama langkah dan kategori wajib diisi!"));
        exit;
    }

    try {
        $sql = "INSERT INTO tasks (user_id, category_id, task_name, target_time, notes, priority, is_completed) 
                VALUES (:user_id, :category_id, :task_name, :target_time, :notes, :priority, 0)";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':user_id'     => $user_id,
            ':category_id' => $category_id,
            ':task_name'   => $task_name,
            ':target_time' => $target_time,
            ':notes'       => $notes,
            ':priority'    => $priority
        ]);

        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'Rutinitas baru berhasil ditambahkan! 🌸']);
            exit;
        }

        header("Location: ../index.php?status=success&pesan=" . urlencode("Rutinitas baru berhasil ditambahkan! 🌸"));
        exit;

    } catch (PDOException $e) {
        header("Location: ../index.php?status=error&pesan=" . urlencode("Gagal menyimpan data: " . $e->getMessage()));
        exit;
    }
} else {
    header("Location: ../index.php");
    exit;
}
