<?php
/**
 * ==============================================================
 * PROYEK: UKK 2026 - Asisten Pemrograman Junior
 * FILE: register.php (Halaman Pendaftaran Akun Baru)
 * ==============================================================
 * PENJELASAN UNTUK UJIAN UKK:
 * 1. password_hash(): Mengamankan kata sandi menggunakan algoritma Bcrypt kuat.
 *    Kata sandi tidak pernah disimpan dalam bentuk teks biasa (plaintext) di database!
 * 2. Validasi Duplikasi: Memastikan tidak ada dua pengguna dengan username yang sama.
 * 3. Starter Seed: Secara otomatis memberikan beberapa rutinitas dasar untuk akun baru.
 */

require_once 'config/koneksi.php';
require_once 'config/auth.php';

redirectIfLoggedIn();

$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name             = isset($_POST['name']) ? trim($_POST['name']) : '';
    $username         = isset($_POST['username']) ? trim(strtolower($_POST['username'])) : '';
    $skin_type        = isset($_POST['skin_type']) ? trim($_POST['skin_type']) : 'Normal Skin';
    $password         = isset($_POST['password']) ? $_POST['password'] : '';
    $confirm_password = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';

    // Validasi Formulir
    if (empty($name) || empty($username) || empty($password)) {
        $error_message = "Semua kolom bertanda bintang (*) wajib diisi yaa!";
    } elseif (strlen($username) < 3) {
        $error_message = "Username minimal harus 3 karakter!";
    } elseif (strlen($password) < 6) {
        $error_message = "Kata sandi minimal harus 6 karakter demi keamanan!";
    } elseif ($password !== $confirm_password) {
        $error_message = "Konfirmasi kata sandi tidak cocok!";
    } else {
        try {
            // 1. Cek apakah username sudah pernah terdaftar
            $stmtCheck = $pdo->prepare("SELECT id FROM users WHERE username = :username LIMIT 1");
            $stmtCheck->execute([':username' => $username]);
            
            if ($stmtCheck->fetch()) {
                $error_message = "Yah, username '{$username}' sudah dipakai. Coba username yang lain yaa 🌸";
            } else {
                // 2. Hash password menggunakan algoritma Bcrypt standar PHP
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                // 3. Simpan data pengguna baru ke database
                $sqlUser = "INSERT INTO users (name, username, password, skin_type) 
                            VALUES (:name, :username, :password, :skin_type)";
                $stmtUser = $pdo->prepare($sqlUser);
                $stmtUser->execute([
                    ':name'      => $name,
                    ':username'  => $username,
                    ':password'  => $hashed_password,
                    ':skin_type' => $skin_type
                ]);

                $newUserId = (int)$pdo->lastInsertId();

                // 4. Berikan starter routines (bonus rutinitas awal) agar to-do list tidak kosong melompong
                $starterTasks = [
                    [$newUserId, 1, 'Gentle Cleanser', '06:30 WIB', 'Cuci muka lembut di pagi hari', 'wajib', 0],
                    [$newUserId, 1, 'Sunscreen UV Shield', '07:00 WIB', 'Pakai 2 ruas jari sebelum keluar rumah', 'wajib', 0],
                    [$newUserId, 2, 'Double Cleansing', '20:00 WIB', 'Angkat debu dan sisa sunscreen seharian', 'wajib', 0],
                    [$newUserId, 3, 'Minum Air 8 Gelas', 'Sepanjang hari', 'Menjaga kelembapan kulit dari dalam', 'rutin', 0]
                ];

                $sqlStarter = "INSERT INTO tasks (user_id, category_id, task_name, target_time, notes, priority, is_completed) 
                               VALUES (?, ?, ?, ?, ?, ?, ?)";
                $stmtStarter = $pdo->prepare($sqlStarter);
                foreach ($starterTasks as $taskItem) {
                    $stmtStarter->execute($taskItem);
                }

                // Redirect ke halaman login dengan pesan sukses
                header("Location: login.php?status=success&pesan=" . urlencode("Akun berhasil dibuat! Silakan masuk dengan akun barumu 🌸"));
                exit;
            }
        } catch (PDOException $e) {
            $error_message = "Gagal mendaftarkan akun: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun GlowTask 🌸 | Skincare Routine Tracker</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>🌸</text></svg>">
</head>
<body class="auth-body">

    <!-- Ornamen Bulatan Aesthetic Lembut di Latar Belakang -->
    <div class="auth-bubble bubble-1"></div>
    <div class="auth-bubble bubble-2"></div>
    <div class="auth-bubble bubble-3"></div>

    <div class="auth-container">
        <div class="auth-card" style="max-width: 480px;">
            <div class="auth-header">
                <div class="auth-logo-badge">💖</div>
                <div class="auth-brand-tag">GlowTask App ✨</div>
                <h1 class="auth-title">Daftar Akun Baru</h1>
                <p class="auth-subtitle">Mulai perjalanan kulit sehat dan glowing bersama kami 🌸</p>
            </div>

            <?php if (!empty($error_message)): ?>
                <div class="alert alert-error" style="margin-bottom: 20px;">
                    <span><?= htmlspecialchars($error_message) ?></span>
                </div>
            <?php endif; ?>

            <form action="register.php" method="POST" class="auth-form">
                <!-- Nama Lengkap / Panggilan -->
                <div class="form-group">
                    <label class="form-label" for="name">Nama Panggilan / Lengkap <span class="req">*</span></label>
                    <div class="input-with-icon">
                        <span class="input-icon">✨</span>
                        <input type="text" 
                               id="name" 
                               name="name" 
                               class="form-control" 
                               placeholder="Contoh: Alya Putri" 
                               required
                               value="<?= isset($_POST['name']) ? htmlspecialchars($_POST['name']) : '' ?>">
                    </div>
                </div>

                <div class="form-row">
                    <!-- Username -->
                    <div class="form-group">
                        <label class="form-label" for="username">Username <span class="req">*</span></label>
                        <div class="input-with-icon">
                            <span class="input-icon">👤</span>
                            <input type="text" 
                                   id="username" 
                                   name="username" 
                                   class="form-control" 
                                   placeholder="Contoh: alya" 
                                   required 
                                   autocomplete="username"
                                   value="<?= isset($_POST['username']) ? htmlspecialchars($_POST['username']) : '' ?>">
                        </div>
                    </div>

                    <!-- Jenis Kulit (Fitur Cute Personalization) -->
                    <div class="form-group">
                        <label class="form-label" for="skin_type">Jenis Kulit</label>
                        <select name="skin_type" id="skin_type" class="form-control">
                            <option value="Normal Skin">💧 Kulit Normal</option>
                            <option value="Dry Skin">🏜️ Kulit Kering</option>
                            <option value="Oily Skin">✨ Kulit Berminyak</option>
                            <option value="Combination Skin" selected>🌿 Kulit Kombinasi</option>
                            <option value="Sensitive Skin">🌸 Kulit Sensitif</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <!-- Kata Sandi -->
                    <div class="form-group">
                        <label class="form-label" for="password">Kata Sandi <span class="req">*</span></label>
                        <div class="input-with-icon">
                            <span class="input-icon">🔒</span>
                            <input type="password" 
                                   id="password" 
                                   name="password" 
                                   class="form-control" 
                                   placeholder="Min. 6 karakter" 
                                   required 
                                   autocomplete="new-password">
                        </div>
                    </div>

                    <!-- Konfirmasi Kata Sandi -->
                    <div class="form-group">
                        <label class="form-label" for="confirm_password">Konfirmasi Sandi <span class="req">*</span></label>
                        <div class="input-with-icon">
                            <span class="input-icon">🔑</span>
                            <input type="password" 
                                   id="confirm_password" 
                                   name="confirm_password" 
                                   class="form-control" 
                                   placeholder="Ulangi sandi" 
                                   required 
                                   autocomplete="new-password">
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-pink btn-auth-submit">
                    <span>💖 Daftarkan Akunku</span>
                </button>
            </form>

            <div class="auth-footer">
                <p>Sudah punya akun? 
                    <a href="login.php" class="auth-link">Masuk di sini 🌸</a>
                </p>
            </div>
        </div>

        <!-- Watermark / Slogan Manis di Bawah Kartu -->
        <div class="auth-bottom-tag">
            <span>✨ Self-Care & Skincare Routine Tracker</span>
        </div>
    </div>

</body>
</html>
