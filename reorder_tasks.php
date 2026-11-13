<?php
/**
 * ==============================================================
 * FILE: actions/reorder_tasks.php
 * FUNGSI: Menyimpan urutan baru hasil Drag and Drop ke database
 * ==============================================================
 * PENJELASAN UNTUK UJIAN UKK:
 * 1. Menerima payload JSON dari JavaScript Fetch API berisi daftar ID yang telah disusun ulang.
 * 2. Memperbarui kolom `order_index` sesuai posisi baru (indeks array).
 * 3. Keamanan IDOR: Hanya memperbarui tugas dengan `user_id = $_SESSION['user_id']`.
 */

require_once '../config/koneksi.php';
require_once '../config/auth.php';

header('Content-Type: application/json');

// Pastikan user sudah login
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Sesi login telah berakhir']);
    exit;
}

$user_id = (int)$_SESSION['user_id'];

// Ambil data JSON dari body request
$rawPayload = file_get_contents('php://input');
$data = json_decode($rawPayload, true);

if (!isset($data['task_ids']) || !is_array($data['task_ids'])) {
    echo json_encode(['success' => false, 'message' => 'Format data urutan tidak valid']);
    exit;
}

$taskIds = $data['task_ids'];

try {
    // Mulai transaksi database agar proses update urutan konsisten
    $pdo->beginTransaction();

    $sql = "UPDATE tasks SET order_index = :order_index WHERE id = :id AND user_id = :user_id";
    $stmt = $pdo->prepare($sql);

    foreach ($taskIds as $orderPos => $taskId) {
        $stmt->execute([
            ':order_index' => $orderPos + 1,
            ':id'          => (int)$taskId,
            ':user_id'     => $user_id
        ]);
    }

    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Urutan langkah skincare berhasil disimpan! ✨'
    ]);
    exit;

} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode([
        'success' => false,
        'message' => 'Gagal memperbarui urutan: ' . $e->getMessage()
    ]);
    exit;
}
