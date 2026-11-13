<?php
/**
 * ==============================================================
 * FILE: actions/hapus_task.php
 * FUNGSI: Menghapus rutinitas skincare dari database (DELETE)
 * ==============================================================
 */

require_once '../config/koneksi.php';
require_once '../config/auth.php';

if (!isLoggedIn()) {
    header("Location: ../login.php");
    exit;
}

$user_id = (int)$_SESSION['user_id'];

// Ambil ID dari GET atau POST
$id = 0;
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
} elseif (isset($_POST['id'])) {
    $id = intval($_POST['id']);
}

if ($id <= 0) {
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'ID tidak ditemukan']);
        exit;
    }
    header("Location: ../index.php?status=error&pesan=" . urlencode("ID rutinitas tidak ditemukan"));
    exit;
}

try {
    $sql = "DELETE FROM tasks WHERE id = :id AND user_id = :user_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $id, ':user_id' => $user_id]);

    if ($stmt->rowCount() > 0) {
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'Rutinitas berhasil dihapus! 🗑️']);
            exit;
        }
        header("Location: ../index.php?status=success&pesan=" . urlencode("Rutinitas berhasil dihapus! 🗑️"));
        exit;
    } else {
        header("Location: ../index.php?status=error&pesan=" . urlencode("Rutinitas tidak ditemukan atau bukan milikmu"));
        exit;
    }

} catch (PDOException $e) {
    header("Location: ../index.php?status=error&pesan=" . urlencode("Gagal menghapus: " . $e->getMessage()));
    exit;
}
