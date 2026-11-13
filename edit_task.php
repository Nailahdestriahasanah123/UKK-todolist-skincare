<?php
/**
 * ==============================================================
 * FILE: actions/edit_task.php
 * FUNGSI: Memperbarui detail rutinitas skincare (UPDATE DATA)
 * ==============================================================
 */

require_once '../config/koneksi.php';
require_once '../config/auth.php';

if (!isLoggedIn()) {
    header("Location: ../login.php");
    exit;
}

$user_id = (int)$_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id          = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $task_name   = isset($_POST['task_name']) ? trim($_POST['task_name']) : '';
    $category_id = isset($_POST['category_id']) ? intval($_POST['category_id']) : 0;
    $target_time = isset($_POST['target_time']) ? trim($_POST['target_time']) : '';
    $notes       = isset($_POST['notes']) ? trim($_POST['notes']) : '';
    $priority    = isset($_POST['priority']) && in_array($_POST['priority'], ['wajib', 'rutin', 'opsional']) 
                    ? $_POST['priority'] 
                    : 'rutin';

    if ($id <= 0 || empty($task_name) || $category_id <= 0) {
        header("Location: ../index.php?status=error&pesan=" . urlencode("Data tidak lengkap untuk diperbarui"));
        exit;
    }

    try {
        $sql = "UPDATE tasks 
                SET category_id = :category_id,
                    task_name   = :task_name,
                    target_time = :target_time,
                    notes       = :notes,
                    priority    = :priority
                WHERE id = :id AND user_id = :user_id";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':category_id' => $category_id,
            ':task_name'   => $task_name,
            ':target_time' => $target_time,
            ':notes'       => $notes,
            ':priority'    => $priority,
            ':id'          => $id,
            ':user_id'     => $user_id
        ]);

        header("Location: ../index.php?status=success&pesan=" . urlencode("Rutinitas berhasil diperbarui! 💖"));
        exit;

    } catch (PDOException $e) {
        header("Location: ../index.php?status=error&pesan=" . urlencode("Gagal memperbarui data: " . $e->getMessage()));
        exit;
    }
} else {
    header("Location: ../index.php");
    exit;
}
