<?php
/**
 * ==============================================================
 * FILE: actions/toggle_status.php
 * FUNGSI: Mengubah status rutinitas: Belum Selesai (0) <-> Selesai (1) (UPDATE)
 * ==============================================================
 * PENJELASAN UNTUK UJIAN UKK:
 * 1. Logika Toggle: Menggunakan ekspresi SQL `is_completed = 1 - is_completed`.
 * 2. Keamanan IDOR (Insecure Direct Object Reference):
 *    Menambahkan klausul `AND user_id = :user_id` agar user tidak bisa
 *    mengubah status tugas milik pengguna lain.
 */

require_once '../config/koneksi.php';
require_once '../config/auth.php';

if (!isLoggedIn()) {
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Sesi berakhir, silakan login kembali']);
        exit;
    }
    header("Location: ../login.php");
    exit;
}

$user_id = (int)$_SESSION['user_id'];

// Ambil ID dari POST atau GET
$id = 0;
if (isset($_POST['id'])) {
    $id = intval($_POST['id']);
} elseif (isset($_GET['id'])) {
    $id = intval($_GET['id']);
}

if ($id <= 0) {
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'ID tidak valid']);
        exit;
    }
    header("Location: ../index.php?status=error&pesan=" . urlencode("ID rutinitas tidak valid"));
    exit;
}

try {
    // 1. Eksekusi SQL untuk toggle nilai is_completed khusus milik user_id yang aktif
    $sql = "UPDATE tasks SET is_completed = 1 - is_completed WHERE id = :id AND user_id = :user_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $id, ':user_id' => $user_id]);

    // 2. Ambil data terbaru untuk feedback
    $checkSql = "SELECT is_completed, task_name FROM tasks WHERE id = :id AND user_id = :user_id";
    $checkStmt = $pdo->prepare($checkSql);
    $checkStmt->execute([':id' => $id, ':user_id' => $user_id]);
    $updatedTask = $checkStmt->fetch();

    if (!$updatedTask) {
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Tugas tidak ditemukan atau bukan milikmu']);
            exit;
        }
        header("Location: ../index.php?status=error&pesan=" . urlencode("Tugas tidak ditemukan"));
        exit;
    }

    // 3. Response untuk AJAX (JavaScript fetch)
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        header('Content-Type: application/json');
        echo json_encode([
            'success'      => true,
            'id'           => $id,
            'is_completed' => (int)$updatedTask['is_completed'],
            'message'      => $updatedTask['is_completed'] == 1 ? 'Hebat! Langkah skincare terselesaikan! ✨' : 'Status diubah ke belum selesai'
        ]);
        exit;
    }

    // 4. Response untuk redirect biasa
    header("Location: ../index.php?status=success&pesan=" . urlencode("Status rutinitas berhasil diperbarui! ✨"));
    exit;

} catch (PDOException $e) {
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        exit;
    }
    header("Location: ../index.php?status=error&pesan=" . urlencode("Gagal memperbarui status: " . $e->getMessage()));
    exit;
}
