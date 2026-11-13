<?php
/**
 * ==============================================================
 * PROYEK: UKK 2026 - Asisten Pemrograman Junior
 * FILE: login.php (Halaman Masuk Pengguna)
 * ==============================================================
 * PENJELASAN UNTUK UJIAN UKK:
 * 1. session_start(): Memulai sesi untuk memeriksa status autentikasi.
 * 2. password_verify(): Memvalidasi password plaintext dari input pengguna
 *    dengan password hash aman di database.
 * 3. Prepared Statements: Mencegah serangan SQL Injection pada proses login.
 */

require_once 'config/koneksi.php';
require_once 'config/auth.php';

// Jika pengguna sudah login, langsung arahkan ke beranda index.php
redirectIfLoggedIn();

$error_message = '';

// Proses Login saat formulir disubmit (Metode POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    if (empty($username) || empty($password)) {
        $error_message = "Username dan password wajib diisi!";
    } else {
        try {
            // Ambil data pengguna dari database berdasarkan username
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username LIMIT 1");
            $stmt->execute([':username' => $username]);
            $user = $stmt->fetch();

            // Verifikasi kecocokan password menggunakan password_verify()
            if ($user && password_verify($password, $user['password'])) {
                // Regenerate session ID untuk mencegah serangan Session Fixation (Standar Keamanan UKK)
                session_regenerate_id(true);

                // Simpan data esensial pengguna ke dalam SESSION
                $_SESSION['user_id']   = (int)$user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['username']  = $user['username'];
                $_SESSION['skin_type'] = $user['skin_type'] ?? 'Normal Skin';

                header("Location: index.php?status=success&pesan=" . urlencode("Selamat datang kembali, " . $user['name'] . "! 🌸"));
                exit;
            } else {
                $error_message = "Username atau password kamu salah, coba periksa lagi yaa! 🥺";
            }
        } catch (PDOException $e) {
            $error_message = "Terjadi kendala sistem: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk ke GlowTask 🌸 | Skincare Routine Tracker</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>🌸</text></svg>">
</head>
<body class="auth-body">

    <!-- Ornamen Bulatan Aesthetic Lembut di Latar Belakang -->
    <div class="auth-bubble bubble-1"></div>
    <div class="auth-bubble bubble-2"></div>
    <div class="auth-bubble bubble-3"></div>

    <div class="auth-container">
        <!-- Kartu Login Cute Pink Aesthetic -->
        <div class="auth-card">
            <!-- Header Kartu -->
            <div class="auth-header">
                <div class="auth-logo-badge">🌸</div>
                <div class="auth-brand-tag">GlowTask App ✨</div>
                <h1 class="auth-title">Halo Cantik!</h1>
                <p class="auth-subtitle">Masuk ke akunmu untuk merawat kulit glowing hari ini 💖</p>
            </div>

            <!-- Pesan Notifikasi Sukses / Error -->
            <?php if (!empty($error_message)): ?>
                <div class="alert alert-error">
                    <span><?= htmlspecialchars($error_message) ?></span>
                </div>
            <?php elseif (isset($_GET['pesan'])): ?>
                <div class="alert alert-<?= ($_GET['status'] ?? '') === 'success' ? 'success' : 'error' ?>">
                    <span><?= htmlspecialchars($_GET['pesan']) ?></span>
                </div>
            <?php endif; ?>

            <form action="login.php" method="POST" class="auth-form">
                <!-- Input Username -->
                <div class="form-group">
                    <label class="form-label" for="username">Username <span class="req">*</span></label>
                    <div class="input-with-icon">
                        <span class="input-icon">👤</span>
                        <input type="text" 
                               id="username" 
                               name="username" 
                               class="form-control" 
                               placeholder="Masukkan username kamu" 
                               required 
                               autocomplete="username"
                               value="<?= isset($_POST['username']) ? htmlspecialchars($_POST['username']) : '' ?>">
                    </div>
                </div>

                <!-- Input Password -->
                <div class="form-group">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 7px;">
                        <label class="form-label" for="password" style="margin-bottom: 0;">Kata Sandi <span class="req">*</span></label>
                    </div>
                    <div class="input-with-icon">
                        <span class="input-icon">🔒</span>
                        <input type="password" 
                               id="password" 
                               name="password" 
                               class="form-control" 
                               placeholder="Masukkan kata sandi" 
                               required 
                               autocomplete="current-password">
                        <button type="button" class="btn-toggle-pwd" id="togglePasswordBtn" title="Lihat kata sandi">👁️</button>
                    </div>
                </div>

                <!-- Tombol Submit Login -->
                <button type="submit" class="btn btn-pink btn-auth-submit">
                    <span>🌸 Masuk Sekarang</span>
                </button>
            </form>

            <!-- Footer Tautan ke Registrasi -->
            <div class="auth-footer">
                <p>Belum punya akun GlowTask? 
                    <a href="register.php" class="auth-link">Daftar Akun Baru 💖</a>
                </p>
            </div>
        </div>

        <!-- Watermark / Slogan Manis di Bawah Kartu -->
        <div class="auth-bottom-tag">
            <span>✨ Self-Care & Skincare Routine Tracker</span>
        </div>
    </div>

    <!-- Script untuk Show/Hide Password -->
    <script>
        const toggleBtn = document.getElementById('togglePasswordBtn');
        const pwdInput = document.getElementById('password');
        if (toggleBtn && pwdInput) {
            toggleBtn.addEventListener('click', () => {
                const isPassword = pwdInput.getAttribute('type') === 'password';
                pwdInput.setAttribute('type', isPassword ? 'text' : 'password');
                toggleBtn.textContent = isPassword ? '🙈' : '👁️';
            });
        }
    </script>
</body>
</html>
