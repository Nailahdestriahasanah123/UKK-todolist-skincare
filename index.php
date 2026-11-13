<?php
/**
 * ==============================================================
 * PROYEK: UKK 2026 - Asisten Pemrograman Junior
 * APLIKASI: To-Do List Skincare & Self-Care Daily Routine Tracker
 * FILE: index.php (Halaman Utama Aplikasi)
 * ==============================================================
 * PENJELASAN UNTUK UJIAN UKK:
 * 1. requireLogin(): Memastikan pengguna harus login terlebih dahulu.
 * 2. Multi-User Support: Seluruh query tugas difilter berdasarkan `user_id`
 *    dari session yang aktif, sehingga setiap pengguna memiliki daftar tugas tersendiri.
 */

require_once 'config/koneksi.php';
require_once 'config/auth.php';

// Proteksi Halaman: Jika belum login, redirect ke login.php
requireLogin();

$user_id   = (int)$_SESSION['user_id'];
$user_name = htmlspecialchars($_SESSION['user_name'] ?? 'Sahabat Glow');
$skin_type = htmlspecialchars($_SESSION['skin_type'] ?? 'Normal Skin');

// ------------------------------------------------------------------
// 1. AMBIL SEMUA KATEGORI DARI DATABASE (Tabel: categories)
// ------------------------------------------------------------------
try {
    $stmtCat = $pdo->query("SELECT * FROM categories ORDER BY id ASC");
    $categories = $stmtCat->fetchAll();
} catch (PDOException $e) {
    die("Error mengambil kategori: " . $e->getMessage());
}

// ------------------------------------------------------------------
// 2. LOGIKA FILTER & PENCARIAN (READ & FILTER BERDASARKAN USER_ID)
// ------------------------------------------------------------------
$filter_kategori = isset($_GET['kategori']) ? trim($_GET['kategori']) : 'all';
$filter_status   = isset($_GET['status']) ? trim($_GET['status']) : 'all';
$search_query    = isset($_GET['q']) ? trim($_GET['q']) : '';

// Query Dasar dengan JOIN dan Filter User ID
$sql = "SELECT tasks.*, 
               categories.name AS category_name, 
               categories.slug AS category_slug, 
               categories.badge_icon, 
               categories.theme_color
        FROM tasks 
        JOIN categories ON tasks.category_id = categories.id 
        WHERE tasks.user_id = :user_id";

$params = [':user_id' => $user_id];

// Filter berdasarkan Kategori
if (!empty($filter_kategori) && $filter_kategori !== 'all') {
    $sql .= " AND categories.slug = :cat_slug";
    $params[':cat_slug'] = $filter_kategori;
}

// Filter berdasarkan Status Selesai / Belum
if ($filter_status === 'selesai') {
    $sql .= " AND tasks.is_completed = 1";
} elseif ($filter_status === 'belum') {
    $sql .= " AND tasks.is_completed = 0";
}

// Pencarian kata kunci
if (!empty($search_query)) {
    $sql .= " AND (tasks.task_name LIKE :keyword OR tasks.notes LIKE :keyword)";
    $params[':keyword'] = '%' . $search_query . '%';
}

// Urutkan: yang belum selesai tampil di atas, lalu berdasarkan urutan Drag & Drop (order_index), lalu ID terbaru
$sql .= " ORDER BY tasks.is_completed ASC, tasks.order_index ASC, tasks.id DESC";

try {
    $stmtTasks = $pdo->prepare($sql);
    $stmtTasks->execute($params);
    $tasks = $stmtTasks->fetchAll();
} catch (PDOException $e) {
    die("Error mengambil data tugas: " . $e->getMessage());
}

// ------------------------------------------------------------------
// 3. HITUNG STATISTIK PROGRESS HARIAN (KHUSUS USER_ID YANG LOGIN)
// ------------------------------------------------------------------
try {
    $stmtStats = $pdo->prepare("SELECT 
        COUNT(*) AS total,
        SUM(CASE WHEN is_completed = 1 THEN 1 ELSE 0 END) AS completed,
        SUM(CASE WHEN is_completed = 0 THEN 1 ELSE 0 END) AS pending
        FROM tasks WHERE user_id = :user_id");
    $stmtStats->execute([':user_id' => $user_id]);
    $stats = $stmtStats->fetch();
    
    $totalTasks      = (int)($stats['total'] ?? 0);
    $completedTasks  = (int)($stats['completed'] ?? 0);
    $pendingTasks    = (int)($stats['pending'] ?? 0);
    $progressPercent = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100) : 0;
} catch (PDOException $e) {
    $totalTasks = $completedTasks = $pendingTasks = $progressPercent = 0;
}

// ------------------------------------------------------------------
// 4. UCAPAN WAKTU DINAMIS (Sesuai Jam Lokal)
// ------------------------------------------------------------------
$hour = (int)date('H');
if ($hour >= 4 && $hour < 11) {
    $greeting = "Selamat Pagi, {$user_name}! ☀️";
    $quote    = "Kulit sehat berawal dari rutinitas pagi yang disiplin. Jangan lewatkan sunscreen ya!";
} elseif ($hour >= 11 && $hour < 15) {
    $greeting = "Selamat Siang, {$user_name}! 🌤️";
    $quote    = "Waktunya re-apply sunscreen dan minum air putih agar kulit tetap lembap terhidrasi!";
} elseif ($hour >= 15 && $hour < 18) {
    $greeting = "Selamat Sore, {$user_name}! 🌅";
    $quote    = "Aktivitas hampir usai! Bersiaplah bersihkan wajah dari debu dan polusi seharian.";
} else {
    $greeting = "Selamat Malam, {$user_name}! 🌙";
    $quote    = "Waktunya double cleansing dan pakai moisturizer sebelum tidur. Glow up saat tidur!";
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GlowTask 🌸 | Skincare & Self-Care Daily Routine</title>
    <link rel="stylesheet" href="assets/css/style.css?v=<?= time() ?>">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>🌸</text></svg>">
</head>
<body>

    <!-- ==============================================================
         NAVBAR / HEADER ATAS (DENGAN PROFIL & TOMBOL LOGOUT)
         ============================================================== -->
    <header class="navbar">
        <div class="container nav-container">
            <a href="index.php" class="brand">
                <div class="brand-icon">🌸</div>
                <div>
                    <span class="brand-title">GlowTask</span>
                    <span class="brand-subtitle">Skincare Routine</span>
                </div>
            </a>

            <!-- Profil User & Tombol Logout -->
            <div class="nav-user-area">
                <div class="user-profile-badge">
                    <span class="user-avatar-circle">💖</span>
                    <div class="user-meta-info">
                        <span class="user-name-text"><?= $user_name ?></span>
                        <span class="user-skin-tag">✨ <?= $skin_type ?></span>
                    </div>
                </div>
                <a href="logout.php" class="btn-logout" title="Keluar Akun" onclick="return confirm('Apakah kamu yakin ingin keluar dari GlowTask? 🌸')">
                    <span>🚪 Keluar</span>
                </a>
            </div>
        </div>
    </header>

    <main class="container">
        <!-- ==============================================================
             HERO BANNER & PROGRESS TRACKER
             ============================================================== -->
        <section class="hero-banner">
            <div>
                <h1 class="hero-greeting"><?= $greeting ?></h1>
                <p class="hero-quote">"<?= $quote ?>"</p>
            </div>

            <!-- Kartu Progress Persentase Harian -->
            <div class="progress-card">
                <div class="progress-header">
                    <span class="progress-title">✨ Progress Glow-Up Hari Ini</span>
                    <span class="progress-percent"><?= $progressPercent ?>%</span>
                </div>
                <div class="progress-bar-bg">
                    <div class="progress-bar-fill" style="width: <?= $progressPercent ?>%;"></div>
                </div>
                <div class="progress-stats">
                    <span><?= $completedTasks ?> dari <?= $totalTasks ?> Selesai ✨</span>
                    <span><?= $pendingTasks ?> Belum</span>
                </div>
            </div>
        </section>

        <!-- ==============================================================
             NOTIFIKASI FLASH MESSAGE (SUKSES / ERROR)
             ============================================================== -->
        <?php if (isset($_GET['status']) && isset($_GET['pesan'])): ?>
            <div class="alert alert-<?= $_GET['status'] === 'success' ? 'success' : 'error' ?>">
                <span><?= htmlspecialchars($_GET['pesan']) ?></span>
                <button type="button" class="alert-close">&times;</button>
            </div>
        <?php endif; ?>

        <!-- ==============================================================
             GRID UTAMA (FORM TAMBAH DI KIRI, DAFTAR DI KANAN)
             ============================================================== -->
        <div class="main-grid">

            <!-- ----------------------------------------------------------
                 KOLOM KIRI: FORM TAMBAH RUTINITAS (CREATE)
                 ---------------------------------------------------------- -->
            <aside>
                <div class="cute-card">
                    <div class="card-header-cute">
                        <div class="card-header-icon">➕</div>
                        <div>
                            <h2 class="card-title">Tambah Rutinitas</h2>
                            <small style="color: var(--text-muted); font-size: 0.78rem;">Buat langkah perawatan barumu</small>
                        </div>
                    </div>

                    <form action="actions/tambah_task.php" method="POST" id="formTambahTask">
                        <!-- Nama Rutinitas / Produk -->
                        <div class="form-group">
                            <label class="form-label" for="task_name">
                                Langkah Perawatan / Produk <span class="req">*</span>
                            </label>
                            <input type="text" 
                                   id="task_name" 
                                   name="task_name" 
                                   class="form-control" 
                                   placeholder="Contoh: Pakai Sunscreen SPF 50+" 
                                   required 
                                   autocomplete="off">
                        </div>

                        <!-- Kategori Rutinitas (Relasi ke tabel categories) -->
                        <div class="form-group">
                            <label class="form-label" for="category_id">
                                Kategori Sesi <span class="req">*</span>
                            </label>
                            <select id="category_id" name="category_id" class="form-control" required>
                                <option value="" disabled selected>-- Pilih Kategori --</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat['id'] ?>">
                                        <?= $cat['badge_icon'] ?> <?= htmlspecialchars($cat['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-row">
                            <!-- Target Jam / Waktu Pemakaian -->
                            <div class="form-group">
                                <label class="form-label" for="target_time">Target Jam / Hari</label>
                                <input type="text" 
                                       id="target_time" 
                                       name="target_time" 
                                       class="form-control" 
                                       placeholder="Contoh: 07:00 WIB" 
                                       autocomplete="off">
                            </div>

                            <!-- Prioritas -->
                            <div class="form-group">
                                <label class="form-label" for="priority">Prioritas</label>
                                <select id="priority" name="priority" class="form-control">
                                    <option value="wajib">🔴 Wajib</option>
                                    <option value="rutin" selected>🟡 Rutin</option>
                                    <option value="opsional">⚪ Opsional</option>
                                </select>
                            </div>
                        </div>

                        <!-- Catatan / Tips Pemakaian -->
                        <div class="form-group">
                            <label class="form-label" for="notes">Catatan Tambahan / Tips</label>
                            <textarea id="notes" 
                                      name="notes" 
                                      class="form-control" 
                                      placeholder="Contoh: Gunakan 2 ruas jari, jangan digosok terlalu keras..."></textarea>
                        </div>

                        <button type="submit" class="btn btn-pink">
                            <span>🌸 Simpan Rutinitas</span>
                        </button>
                    </form>
                </div>

                <!-- Box Mini: Tips Perawatan Kulit Hari Ini -->
                <div class="cute-card" style="margin-top: 20px; background: linear-gradient(135deg, #fff5f8, #fdf2f8);">
                    <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px;">
                        <span style="font-size: 1.2rem;">💡</span>
                        <strong style="color: var(--pink-600); font-size: 0.9rem;">Tips Cantik Hari Ini</strong>
                    </div>
                    <p style="font-size: 0.82rem; color: var(--text-muted); line-height: 1.5;">
                        "Kunci kulit glowing adalah konsistensi, hidrasi yang cukup, dan perlindungan sunscreen setiap hari bahkan saat cuaca mendung!"
                    </p>
                </div>
            </aside>

            <!-- ----------------------------------------------------------
                 KOLOM KANAN: DAFTAR RUTINITAS & FILTER (READ & FILTER)
                 ---------------------------------------------------------- -->
            <section>
                <!-- Area Filter Kategori & Pencarian -->
                <div class="filter-wrapper">
                    <!-- Search Input Realtime -->
                    <div class="search-box-wrap">
                        <span class="search-icon">🔍</span>
                        <input type="text" 
                               id="searchTaskInput" 
                               class="search-input" 
                               placeholder="Cari langkah atau produk skincare..." 
                               value="<?= htmlspecialchars($search_query) ?>">
                    </div>

                    <!-- Tombol Filter Kategori (Pill Tabs) -->
                    <div class="category-tabs">
                        <a href="index.php?kategori=all&status=<?= urlencode($filter_status) ?>" 
                           class="tab-btn <?= $filter_kategori === 'all' ? 'active' : '' ?>">
                            ✨ Semua Kategori
                        </a>

                        <?php foreach ($categories as $cat): ?>
                            <a href="index.php?kategori=<?= urlencode($cat['slug']) ?>&status=<?= urlencode($filter_status) ?>" 
                               class="tab-btn <?= $filter_kategori === $cat['slug'] ? 'active' : '' ?>">
                                <?= $cat['badge_icon'] ?> <?= htmlspecialchars($cat['name']) ?>
                            </a>
                        <?php endforeach; ?>
                    </div>

                    <!-- Sub-Filter Berdasarkan Status -->
                    <div style="display: flex; gap: 8px; align-items: center; margin-top: 4px;">
                        <span style="font-size: 0.8rem; color: var(--text-muted); font-weight: 700;">Status:</span>
                        <a href="index.php?kategori=<?= urlencode($filter_kategori) ?>&status=all" 
                           style="font-size: 0.78rem; text-decoration: none; padding: 4px 10px; border-radius: 20px; <?= $filter_status === 'all' ? 'background: var(--pink-500); color: white;' : 'background: #fff; color: var(--text-muted); border: 1px solid var(--border-color);' ?>">
                            Semua (<?= $totalTasks ?>)
                        </a>
                        <a href="index.php?kategori=<?= urlencode($filter_kategori) ?>&status=belum" 
                           style="font-size: 0.78rem; text-decoration: none; padding: 4px 10px; border-radius: 20px; <?= $filter_status === 'belum' ? 'background: var(--pink-500); color: white;' : 'background: #fff; color: var(--text-muted); border: 1px solid var(--border-color);' ?>">
                            Belum Selesai (<?= $pendingTasks ?>)
                        </a>
                        <a href="index.php?kategori=<?= urlencode($filter_kategori) ?>&status=selesai" 
                           style="font-size: 0.78rem; text-decoration: none; padding: 4px 10px; border-radius: 20px; <?= $filter_status === 'selesai' ? 'background: var(--pink-500); color: white;' : 'background: #fff; color: var(--text-muted); border: 1px solid var(--border-color);' ?>">
                            Selesai (<?= $completedTasks ?>)
                        </a>
                    </div>
                </div>

                <!-- Hint Penggunaan Drag and Drop -->
                <div class="drag-hint-bar">
                    <span>💡 <b>Tips:</b> Tahan dan geser (Drag & Drop ⠿) kartu rutinitas untuk mengatur urutan langkah skincare sesuai keinginanmu!</span>
                </div>

                <!-- Daftar Tugas / To-Do List (Drag and Drop Container) -->
                <div class="task-list" id="taskListContainer">
                    <?php if (empty($tasks)): ?>
                        <!-- Tampilan Jika Data Kosong -->
                        <div class="empty-state">
                            <span class="empty-icon">🌸</span>
                            <h3 class="empty-title">Belum ada rutinitas di sini</h3>
                            <p class="empty-desc">
                                Yuk tambahkan langkah perawatan diri barumu lewat formulir di sebelah kiri agar kulitmu makin glowing!
                            </p>
                        </div>
                    <?php else: ?>
                        <!-- Loop Setiap Rutinitas dari Database -->
                        <?php foreach ($tasks as $task): 
                            $isDone = (int)$task['is_completed'] === 1;
                            $badgeClass = 'badge-' . htmlspecialchars($task['category_slug']);
                        ?>
                            <div class="task-item <?= $isDone ? 'completed' : '' ?>" 
                                 id="task-<?= $task['id'] ?>"
                                 data-id="<?= $task['id'] ?>"
                                 data-name="<?= htmlspecialchars($task['task_name']) ?>"
                                 data-notes="<?= htmlspecialchars($task['notes'] ?? '') ?>"
                                 data-category="<?= htmlspecialchars($task['category_name']) ?>"
                                 draggable="true">
                                
                                <!-- Drag & Drop Handle + Tombol Geser Cepat -->
                                <div class="drag-controls">
                                    <div class="drag-handle" title="Tahan dan geser (Drag & Drop) untuk mengatur urutan langkah">
                                        <span>⠿</span>
                                    </div>
                                    <div class="arrow-reorder-wrap">
                                        <button type="button" class="btn-reorder-arrow btn-arrow-up" title="Pindah ke atas" data-id="<?= $task['id'] ?>">▲</button>
                                        <button type="button" class="btn-reorder-arrow btn-arrow-down" title="Pindah ke bawah" data-id="<?= $task['id'] ?>">▼</button>
                                    </div>
                                </div>

                                <div class="task-item-left">
                                    <!-- Custom Cute Checkbox (Checklist Selesai) -->
                                    <div class="custom-checkbox-wrap">
                                        <input type="checkbox" 
                                               class="custom-checkbox" 
                                               data-id="<?= $task['id'] ?>"
                                               <?= $isDone ? 'checked' : '' ?>
                                               title="Tandai selesai">
                                    </div>

                                    <!-- Konten Tugas -->
                                    <div class="task-content">
                                        <h3 class="task-title">
                                            <?php if ($isDone): ?>
                                                <del><?= htmlspecialchars($task['task_name']) ?></del>
                                            <?php else: ?>
                                                <?= htmlspecialchars($task['task_name']) ?>
                                            <?php endif; ?>
                                        </h3>

                                        <!-- Meta Info: Kategori, Target Jam, Prioritas -->
                                        <div class="task-meta">
                                            <!-- Badge Kategori -->
                                            <span class="task-badge <?= $badgeClass ?>">
                                                <?= $task['badge_icon'] ?> <?= htmlspecialchars($task['category_name']) ?>
                                            </span>

                                            <!-- Badge Prioritas -->
                                            <span class="task-badge badge-priority-<?= htmlspecialchars($task['priority']) ?>">
                                                Prioritas: <?= ucfirst(htmlspecialchars($task['priority'])) ?>
                                            </span>

                                            <!-- Target Waktu -->
                                            <?php if (!empty($task['target_time'])): ?>
                                                <span class="task-time">
                                                    ⏰ <?= htmlspecialchars($task['target_time']) ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>

                                        <!-- Catatan Tambahan Jika Ada -->
                                        <?php if (!empty($task['notes'])): ?>
                                            <div class="task-notes">
                                                💡 <strong>Tips:</strong> <?= nl2br(htmlspecialchars($task['notes'])) ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <!-- Tombol Aksi: Edit & Hapus -->
                                <div class="task-actions">
                                    <button type="button" 
                                            class="action-btn edit-btn" 
                                            title="Edit Rutinitas"
                                            data-id="<?= $task['id'] ?>"
                                            data-name="<?= htmlspecialchars($task['task_name']) ?>"
                                            data-category="<?= $task['category_id'] ?>"
                                            data-time="<?= htmlspecialchars($task['target_time'] ?? '') ?>"
                                            data-notes="<?= htmlspecialchars($task['notes'] ?? '') ?>"
                                            data-priority="<?= htmlspecialchars($task['priority']) ?>">
                                        ✏️
                                    </button>

                                    <button type="button" 
                                            class="action-btn delete-btn" 
                                            title="Hapus Rutinitas"
                                            data-id="<?= $task['id'] ?>"
                                            data-name="<?= htmlspecialchars($task['task_name']) ?>">
                                        🗑️
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>

                    <!-- Empty state khusus untuk pencarian yang tidak menemukan hasil -->
                    <div id="emptySearchState" class="empty-state" style="display: none;">
                        <span class="empty-icon">🔍</span>
                        <h3 class="empty-title">Rutinitas tidak ditemukan</h3>
                        <p class="empty-desc">Coba gunakan kata kunci pencarian yang lain yaa 🌸</p>
                    </div>
                </div>
            </section>
        </div>
    </main>

    <!-- ==============================================================
         MODAL POPUP: EDIT RUTINITAS (UPDATE DATA)
         ============================================================== -->
    <div class="modal-overlay" id="editModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">✏️ Edit Rutinitas Skincare</h3>
                <button type="button" class="modal-close" id="closeEditModal">&times;</button>
            </div>
            <form action="actions/edit_task.php" method="POST" id="editForm">
                <div class="modal-body">
                    <!-- ID Tersembunyi (Hidden Input) -->
                    <input type="hidden" name="id" id="edit_id">

                    <div class="form-group">
                        <label class="form-label" for="edit_task_name">Nama Langkah / Produk <span class="req">*</span></label>
                        <input type="text" name="task_name" id="edit_task_name" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="edit_category_id">Kategori Rutinitas <span class="req">*</span></label>
                        <select name="category_id" id="edit_category_id" class="form-control" required>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>">
                                    <?= $cat['badge_icon'] ?> <?= htmlspecialchars($cat['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" for="edit_target_time">Target Jam / Hari</label>
                            <input type="text" name="target_time" id="edit_target_time" class="form-control">
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="edit_priority">Prioritas</label>
                            <select name="priority" id="edit_priority" class="form-control">
                                <option value="wajib">🔴 Wajib</option>
                                <option value="rutin">🟡 Rutin</option>
                                <option value="opsional">⚪ Opsional</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="edit_notes">Catatan Tambahan / Tips</label>
                        <textarea name="notes" id="edit_notes" class="form-control"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" id="cancelEditBtn">Batal</button>
                    <button type="submit" class="btn btn-pink" style="width: auto;">Simpan Perubahan 💖</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ==============================================================
         MODAL POPUP: KONFIRMASI HAPUS (DELETE)
         ============================================================== -->
    <div class="modal-overlay" id="deleteModal">
        <div class="modal-content" style="max-width: 420px; text-align: center;">
            <div class="modal-body" style="padding-top: 32px;">
                <div style="font-size: 54px; margin-bottom: 12px;">🗑️</div>
                <h3 style="margin-bottom: 8px; color: var(--text-main);">Hapus Rutinitas Ini?</h3>
                <p style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 18px;">
                    Apakah kamu yakin ingin menghapus <span id="deleteItemName" style="font-weight: 700; color: var(--pink-600);"></span> dari daftar perawatanmu? Tindakan ini tidak bisa dibatalkan.
                </p>
                <div style="display: flex; gap: 12px; justify-content: center;">
                    <button type="button" class="btn btn-secondary" id="cancelDeleteBtn">Batal</button>
                    <a href="#" id="confirmDeleteLink" class="btn btn-danger">Ya, Hapus Saja</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast Notifikasi Drag and Drop -->
    <div id="dragToast" class="drag-toast">
        <span class="drag-toast-icon">✨</span>
        <span id="dragToastMsg">Urutan langkah skincare berhasil disimpan!</span>
    </div>

    <!-- Script JavaScript Interaktif (dengan Anti-Cache) -->
    <script src="assets/js/script.js?v=<?= time() ?>"></script>
</body>
</html>
