# 🌸 PANDUAN LENGKAP BELAJAR UJIAN UKK 2026
## Proyek: Aplikasi To-Do List Skincare & Self-Care Daily Routine (GlowTask)
> **Skema Sertifikasi:** Asisten Pemrograman Junior (Junior Web Developer)  
> **Fitur Tambahan:** Sistem Autentikasi Pengguna Multi-User (Login, Register, Logout)  
> **Panduan ini dibuat khusus untuk mempermudah kamu memahami setiap baris kode dan percaya diri saat menghadapi sesi tanya-jawab dengan Penguji / Assessor UKK!**

---

## 📑 DAFTAR ISI
1. [Konsep Utama & Keunggulan Proyek](#1-konsep-utama--keunggulan-proyek)
2. [Arsitektur Database & Relasi Tabel (Multi-User)](#2-arsitektur-database--relasi-tabel-multi-user)
3. [Bedah Kode Bagian per Bagian (Step-by-Step)](#3-bedah-kode-bagian-per-bagian)
   - [A. Koneksi Database (`config/koneksi.php`)](#a-koneksi-database-configkoneksiphp)
   - [B. Manajemen Session & Proteksi Login (`config/auth.php`)](#b-manajemen-session--proteksi-login-configauthphp)
   - [C. Alur Login & Verifikasi Sandi (`login.php`)](#c-alur-login--verifikasi-sandi-loginphp)
   - [D. Alur Pendaftaran Akun & Hashing Sandi (`register.php`)](#d-alur-pendaftaran-akun--hashing-sandi-registerphp)
   - [E. Alur Logout (`logout.php`)](#e-alur-logout-logoutphp)
   - [F. Menampilkan & Menyaring Data Per-User (`index.php`)](#f-menampilkan--menyaring-data-per-user-indexphp)
   - [G. Menambah Rutinitas Baru (`actions/tambah_task.php`)](#g-menambah-rutinitas-baru-actionstambah_taskphp)
   - [H. Checklist Centang Selesai Tanpa Reload (`actions/toggle_status.php` & AJAX JS)](#h-checklist-centang-selesai-tanpa-reload)
   - [I. Mengedit & Menghapus Rutinitas (`edit_task.php` & `hapus_task.php`)](#i-mengedit--menghapus-rutinitas)
   - [J. Desain Cute Pink Soft Aesthetic (`assets/css/style.css`)](#j-desain-cute-pink-soft-aesthetic)
   - [K. Fitur Drag & Drop Reordering (`script.js` & `reorder_tasks.php`)](#k-fitur-drag--drop-reordering-scriptjs--reorder_tasksphp)
4. [Simulasi Tanya Jawab Penguji UKK (Cheat Sheet Ujian)](#4-simulasi-tanya-jawab-penguji-ukk)

---

## 1. KONSEP UTAMA & KEUNGGULAN PROYEK

### Mengapa Memilih Konsep Skincare To-Do List Multi-User?
Saat ujian UKK, penguji biasanya memeriksa puluhan siswa dengan to-do list tugas sekolah biasa yang tanpa login atau hanya 1 user bersama. Dengan menambahkan sistem **Autentikasi Multi-User**:
1. **Pribadi & Realistis:** Setiap pengguna (misal Alya, Bella, dll) memiliki daftar rutinitas skincare dan jenis kulitnya masing-masing. Tugas Alya tidak akan bercampur dengan tugas Bella.
2. **Keamanan Standar Industri:** Sandi dienkripsi menggunakan algoritma *Bcrypt* (`password_hash`), halaman dilindungi session guard, dan setiap query dipagari `user_id` untuk mencegah peretasan akses data orang lain.
3. **UI/UX Cute Pink Soft Aesthetic:** Membuat antarmuka terasa hidup, manis, modern, dan memanjakan mata siapa pun yang menggunakannya.

---

## 2. ARSITEKTUR DATABASE & RELASI TABEL (MULTI-USER)

Database `db_skincare_todolist` memiliki 3 tabel yang saling terhubung:

```
+--------------------+              +-------------------------+              +--------------------+
|       users        |              |          tasks          |              |     categories     |
+--------------------+              +-------------------------+              +--------------------+
| id (PK)            | 1 -------- * | id (PK)                 | * -------- 1 | id (PK)            |
| name               |              | user_id (FK)            |              | name               |
| username (UNIQUE)  |              | category_id (FK)        |              | slug (UNIQUE)      |
| password (HASH)    |              | task_name               |              | badge_icon         |
| skin_type          |              | target_time             |              | theme_color        |
| created_at         |              | notes                   |              | description        |
+--------------------+              | priority                |              | created_at         |
                                    | is_completed            |              +--------------------+
                                    | created_at              |
                                    +-------------------------+
```

### Relasi Tabel:
1. **`users` -> `tasks` (One-to-Many):** Satu user bisa memiliki banyak daftar tugas rutinitas. Dihubungkan oleh Foreign Key `tasks.user_id = users.id`.
2. **`categories` -> `tasks` (One-to-Many):** Satu kategori (Pagi, Malam, Health) bisa memayungi banyak tugas. Dihubungkan oleh Foreign Key `tasks.category_id = categories.id`.

---

## 3. BEDAH KODE BAGIAN PER BAGIAN

### A. Koneksi Database (`config/koneksi.php`)
Menggunakan **PDO (PHP Data Objects)** dengan error handler `try-catch`.

```php
$dsn = "mysql:host={$db_host};port={$db_port};dbname={$db_name};charset=utf8mb4";
try {
    $pdo = new PDO($dsn, $db_user, $db_pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
} catch (PDOException $e) {
    die("Pesan Error Ramah...");
}
```
- **Poin Ujian:** PDO mendukung *Prepared Statements* bawaan yang mencegah SQL Injection dan memiliki fleksibilitas tinggi jika di masa depan ingin beralih database (misal ke PostgreSQL/SQLite).

---

### B. Manajemen Session & Proteksi Login (`config/auth.php`)
File ini berfungsi sebagai **Middleware / Satpam Penjaga Halaman**.

```php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: login.php?status=error&pesan=" . urlencode("Silakan login terlebih dahulu 🌸"));
        exit;
    }
}
```
- **Poin Ujian:** Jika ada orang yang mencoba membuka `index.php` secara langsung lewat URL tanpa login, fungsi `requireLogin()` akan mencegatnya dan langsung mengarahkan ke `login.php`.

---

### C. Alur Login & Verifikasi Sandi (`login.php`)
Proses pengecekan akun pengguna:

```php
$stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username LIMIT 1");
$stmt->execute([':username' => $username]);
$user = $stmt->fetch();

if ($user && password_verify($password, $user['password'])) {
    session_regenerate_id(true); // Cegah Session Fixation
    $_SESSION['user_id']   = (int)$user['id'];
    $_SESSION['user_name'] = $user['name'];
    $_SESSION['skin_type'] = $user['skin_type'];
    header("Location: index.php");
    exit;
}
```
- **Poin Ujian:** Menggunakan `password_verify($password, $user['password'])`. Fungsi ini secara otomatis membandingkan kata sandi yang diketik dengan hash Bcrypt di database tanpa pernah mendekripsi hash tersebut. Sangat aman!
- **`session_regenerate_id(true)`:** Memperbarui ID sesi setelah login berhasil untuk mencegah serangan pembajakan sesi (*Session Fixation*).

---

### D. Alur Pendaftaran Akun & Hashing Sandi (`register.php`)
Proses registrasi akun baru:

```php
// 1. Cek apakah username sudah ada
$stmtCheck = $pdo->prepare("SELECT id FROM users WHERE username = :username LIMIT 1");
// ...
// 2. Hash sandi sebelum disimpan ke database
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// 3. Simpan ke tabel users
$stmtUser = $pdo->prepare("INSERT INTO users (name, username, password, skin_type) VALUES (...)");
```
- **Poin Ujian:** **JANGAN PERNAH** menyimpan password dalam bentuk teks polos atau MD5 kuno. Gunakan `password_hash($password, PASSWORD_DEFAULT)` yang menghasilkan hash 60 karakter berbasis algoritma Bcrypt dengan *salt* acak otomatis.

---

### E. Alur Logout (`logout.php`)
```php
session_start();
$_SESSION = [];
session_destroy();
header("Location: login.php");
exit;
```
- **Poin Ujian:** `$_SESSION = []` mengosongkan variabel di memori, dan `session_destroy()` menghancurkan berkas sesi di server sehingga user benar-benar keluar dari sistem.

---

### F. Menampilkan & Menyaring Data Per-User (`index.php`)
```php
$sql = "SELECT tasks.*, categories.name AS category_name, categories.badge_icon 
        FROM tasks 
        JOIN categories ON tasks.category_id = categories.id 
        WHERE tasks.user_id = :user_id";
```
- **Poin Ujian:** Klausa `WHERE tasks.user_id = :user_id` memastikan bahwa pengguna yang sedang login hanya dapat melihat tugas yang dibuat oleh akunnya sendiri. Data antar-pengguna terisolasi sempurna.

---

### G. Menambah Rutinitas Baru (`actions/tambah_task.php`)
```php
$sql = "INSERT INTO tasks (user_id, category_id, task_name, target_time, notes, priority, is_completed) 
        VALUES (:user_id, :category_id, :task_name, :target_time, :notes, :priority, 0)";
```
- **Poin Ujian:** Mengaitkan rutinitas baru dengan `$_SESSION['user_id']` secara otomatis di sisi server (bukan dari input form tersembunyi yang bisa dimanipulasi user).

---

### H. Checklist Centang Selesai Tanpa Reload (`actions/toggle_status.php`)
```sql
UPDATE tasks SET is_completed = 1 - is_completed 
WHERE id = :id AND user_id = :user_id
```
- **Trik Matematika:** `1 - 0 = 1` (selesai) dan `1 - 1 = 0` (belum).
- **Keamanan IDOR:** Ditambahkan `AND user_id = :user_id` agar user tidak bisa iseng mencentang tugas milik akun orang lain dengan mengganti ID.
- **Frontend AJAX:** Menggunakan `fetch()` di JavaScript sehingga layar tidak berkedip/reload dan progress bar bergerak naik secara langsung.

---

### I. Mengedit & Menghapus Rutinitas
- **Edit (`edit_task.php`):** Menjalankan query `UPDATE tasks ... WHERE id = :id AND user_id = :user_id`. Didukung modal popup interaktif.
- **Hapus (`hapus_task.php`):** Menjalankan query `DELETE FROM tasks WHERE id = :id AND user_id = :user_id`. Didukung modal konfirmasi cute.

---

### J. Desain Cute Pink Soft Aesthetic (`assets/css/style.css`)
- **Desain Khusus:** Menggunakan palet pastel (`#fff5f8`, `#fecddf`, `#f472a1`), glassmorphism, input box cantik dengan icon, tombol show/hide password, dan responsive layout untuk HP dan Desktop.

---

### K. Fitur Drag & Drop Reordering (`script.js` & `reorder_tasks.php`)
Fitur ini memungkinkan pengguna mengatur urutan langkah skincare (misal: Facial Wash -> Toner -> Serum -> Sunscreen) hanya dengan menyeret kartu tugas!

1. **HTML5 Native Drag & Drop API:**
   - Setiap kartu tugas memiliki atribut `draggable="true"` dan pegangan geser `⠿` (`.drag-handle`).
   - Event `dragstart`: Elemen yang ditarik diberi kelas `.dragging` (tampilan transparan dan garis putus-putus pink).
   - Event `dragover`: Mendeteksi posisi kursor mouse secara real-time menggunakan fungsi pembantu `getDragAfterElement()` dan menyisipkan kartu di posisi baru sebelum elemen berikutnya.
2. **Penyimpanan Permanen ke Database (AJAX Fetch):**
   - Event `dragend`: Mengumpulkan seluruh ID tugas sesuai urutan barunya di layar.
   - Mengirim request POST dengan payload JSON ke `actions/reorder_tasks.php`.
   - Backend PHP menjalankan query UPDATE kolom `order_index` dengan transaksi database aman.
   - Muncul notifikasi toast cute: *"Urutan langkah skincare berhasil disimpan! ✨"*.

---

## 4. SIMULASI TANYA JAWAB PENGUJI UKK (CHEAT SHEET)

Berikut adalah pertanyaan yang **paling sering ditanyakan oleh penguji UKK** beserta jawaban terbaik yang bisa kamu ucapkan:

---

### ❓ Pertanyaan 1: "Bagaimana alur kerja sistem login di aplikasi ini?"
> **Jawaban Kamu:**  
> *"Pengguna memasukkan username dan password pada form login. Sistem akan mencari data user berdasarkan username menggunakan prepared statements di database. Jika ditemukan, sistem mencocokkan password yang diinput dengan hash di database menggunakan fungsi `password_verify()`. Jika cocok, server meregenerasi session ID untuk keamanan dan menyimpan data penting seperti `user_id` dan `user_name` ke dalam array `$_SESSION`. Setelah itu, pengguna diarahkan ke `index.php`."*

---

### ❓ Pertanyaan 2: "Mengapa password tidak disimpan dalam bentuk teks biasa atau MD5?"
> **Jawaban Kamu:**  
> *"Menyimpan password dalam bentuk teks biasa sangat berbahaya karena jika database bocor, password pengguna langsung terbaca. Algoritma MD5 juga sudah usang dan rentan dibongkar menggunakan rainbow table. Oleh karena itu, saya menggunakan fungsi standar industri PHP yaitu `password_hash()` dengan algoritma Bcrypt (`PASSWORD_DEFAULT`), yang secara otomatis menghasilkan salt unik dan proses hashing lambat yang tahan terhadap serangan brute force."*

---

### ❓ Pertanyaan 3: "Apa fungsi dari `session_start()` dan apa bedanya Session dengan Cookie?"
> **Jawaban Kamu:**  
> *"Fungsi `session_start()` digunakan untuk memulai atau melanjutkan sesi pengguna.  
> - **Session** menyimpan data di sisi **server** (sehingga lebih aman untuk menyimpan status login dan ID pengguna).  
> - **Cookie** menyimpan data di sisi **browser/klien** (biasanya untuk preferensi tampilan atau token ingat saya)."*

---

### ❓ Pertanyaan 4: "Bagaimana cara aplikasi memastikan bahwa pengguna tidak bisa melihat atau menghapus to-do list orang lain?"
> **Jawaban Kamu:**  
> *"Saya menerapkan pengecekan hak akses data (*Authorization*) pada setiap query SQL. Di `index.php`, query SELECT dipagari dengan `WHERE tasks.user_id = :user_id`. Begitu juga pada proses UPDATE, DELETE, dan checklist centang, selalu disematkan klausa `AND user_id = :user_id`. Jadi, meskipun pengguna mencoba memanipulasi ID tugas lewat URL, sistem tetap menolak jika tugas tersebut bukan milik akunnya."*

---

### ❓ Pertanyaan 5: "Bagaimana kamu menguji halaman jika ada pengguna yang belum login mencoba masuk?"
> **Jawaban Kamu:**  
> *"Saya membuat fungsi penjaga `requireLogin()` di file `config/auth.php`. Jika session `user_id` belum terdaftar, fungsi tersebut langsung memblokir akses dan me-redirect pengguna ke `login.php` dengan pesan peringatan."*

---

### ❓ Pertanyaan 6: "Bagaimana cara kerja fitur Drag and Drop di aplikasi kamu dan bagaimana urutannya bisa tersimpan?"
> **Jawaban Kamu:**  
> *"Saya menggunakan **HTML5 Drag and Drop API murni** di JavaScript tanpa library tambahan. Kartu tugas diberi atribut `draggable='true'`. Saat kartu digeser, JavaScript menangkap event `dragover` dan menyusun ulang posisi kartu di antarmuka (DOM). Ketika kartu dilepas (`dragend`), JavaScript mengambil susunan urutan ID yang baru, lalu mengirimkannya via AJAX (Fetch API) ke backend `actions/reorder_tasks.php`. Di sisi server, nilai posisi tersebut disimpan ke kolom `order_index` di database MySQL. Dengan demikian, ketika halaman di-refresh, urutan langkah skincare tetap rapi tersimpan sesuai susunan pengguna."*

---

🌸 **Tips Sukses Presentasi UKK:**  
Buka browser dalam mode **Incognito/Private**, lalu tunjukkan kepada penguji saat kamu mendaftar akun baru dengan jenis kulitmu, login, menambahkan rutinitas skincare, dan mencentang selesai dengan animasi progress bar. Penguji pasti akan memberikan nilai maksimal! Semangat yaa! ✨💪💖
