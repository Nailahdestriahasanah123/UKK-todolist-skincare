# 🌸 GlowTask — Skincare & Self-Care Daily Routine Tracker
> **Proyek Uji Kompetensi Keahlian (UKK) 2026**  
> **Skema Sertifikasi: Asisten Pemrograman Junior (Junior Web Developer)**  
> **Teknologi:** PHP (Native PDO), MySQL, HTML5, CSS3 (Soft Pink Aesthetic), JavaScript (DOM & Fetch API)  
> **Fitur Keamanan:** Autentikasi Pengguna Multi-User (Login, Register, Logout), Session Guard, Bcrypt Password Hashing (`password_hash`), Prepared Statements (Anti SQL Injection), Sanitasi XSS (`htmlspecialchars`).

---

## 📖 1. Tentang Aplikasi
Aplikasi ini merupakan modifikasi kreatif dari aplikasi *To-Do List* konvensional. Alih-alih membuat daftar tugas sekolah biasa, aplikasi ini berfokus pada **Daftar Rutinitas Perawatan Diri & Skincare Harian Pribadi (Multi-User)**. Rutinitas dibagi ke dalam sesi:
- ☀️ **Skincare Pagi (Morning Routine)** — Contoh: *Gentle Facial Wash*, *Hydrating Toner*, *Sunscreen SPF 50+*.
- 🌙 **Skincare Malam (Night Routine)** — Contoh: *Double Cleansing*, *Serum Niacinamide*, *Moisturizer Ceramide*.
- 💖 **Body & Health Care** — Contoh: *Minum Air 2 Liter*, *Sheet Mask*, *Olahraga Ringan*.

---

## ✨ 2. Fitur Utama (Sesuai Standar UKK)
1. **Sistem Autentikasi Multi-User:**
   - **Login Pengguna:** Masuk dengan username dan kata sandi yang telah di-hash dengan aman.
   - **Registrasi Akun Baru:** Mendaftar akun dengan pilihan jenis kulit (*Normal, Dry, Oily, Combination, Sensitive*), otomatis mendapatkan starter routine.
   - **Logout:** Menghancurkan session dengan aman.
   - **Data Privacy (Multi-User):** Setiap pengguna hanya bisa melihat, menambah, mengubah, dan menghapus daftar rutinitas miliknya sendiri.
2. **Relasi Database (One-to-Many):**
   - Tabel `users` berelasi dengan tabel `tasks` (`users.id -> tasks.user_id`).
   - Tabel `categories` berelasi dengan tabel `tasks` (`categories.id -> tasks.category_id`).
3. **Create (Tambah Data):** Menambahkan langkah perawatan kulit dengan pilihan kategori, target jam, prioritas, dan tips pemakaian.
4. **Read & Filter (Tampil & Saring Data):** 
   - Filter instan berdasarkan Kategori (Pagi, Malam, Health, Semua).
   - Filter status (Belum Selesai vs Sudah Selesai).
   - Pencarian realtime (*Live Search* sisi client).
5. **Update Status (Checklist Selesai Tanpa Reload):** 
   - Tombol centang bulat aesthetic menggunakan AJAX Fetch API.
   - Teks otomatis dicoret (`<del>`) saat langkah selesai dilakukan.
6. **Update Data (Edit Rutinitas):** Modal popup edit interaktif untuk memperbarui data rutinitas.
7. **Delete (Hapus Rutinitas):** Menghapus rutinitas dengan konfirmasi dialog modal yang cute.
8. **Progress Bar Otomatis:** Menghitung persentase penyelesaian rutinitas harian secara dinamis.
9. **Desain Cute Pink Soft Aesthetic:** Tampilan modern, responsif untuk HP dan Laptop.

---

## 🔑 3. Akun Demo untuk Penguji UKK
Untuk mempermudah penguji atau siapa pun yang ingin mencoba aplikasi tanpa perlu mendaftar terlebih dahulu:
- **Username:** `alya`
- **Password:** `password123`
*(Atau kamu bisa klik tombol **Daftar Akun Baru** untuk membuat akunmu sendiri!)*

---

## 🚀 4. Cara Menjalankan Proyek di Laptop (XAMPP / Laragon)

### Langkah 1: Pindahkan Folder Proyek
1. Buka folder instalasi XAMPP kamu (biasanya di `C:\xampp\htdocs\`).
2. Salin seluruh folder `ToDoList-skincare` ini ke dalam folder `htdocs`, misal menjadi:  
   `C:\xampp\htdocs\skincare-todolist`

### Langkah 2: Nyalakan Web Server & Database
1. Buka **XAMPP Control Panel**.
2. Klik tombol **Start** pada modul **Apache** dan **MySQL** hingga keduanya berwarna hijau.

### Langkah 3: Import Database
1. Buka browser, akses alamat: `http://localhost/phpmyadmin/`.
2. Klik tab **Databases** (atau **Basis Data**), buat database baru bernama:
   ```text
   db_skincare_todolist
   ```
3. Klik nama database `db_skincare_todolist` di panel sebelah kiri.
4. Klik tab **Import** di bagian atas menu.
5. Klik **Choose File** (Pilih Berkas), lalu pilih file `database.sql` yang ada di dalam folder proyek ini.
6. Scroll ke bawah dan klik tombol **Import** (atau **Kirim**).
7. Database, tabel `users`, `categories`, `tasks`, dan data demo siap digunakan!

### Langkah 4: Buka Aplikasi di Browser
Buka browser dan ketik alamat:
```text
http://localhost/skincare-todolist/
```
Aplikasi akan secara otomatis membawamu ke halaman **Login**. Masukkan akun demo atau buat akun baru!

---

## 📁 5. Struktur Folder Proyek
```text
ToDoList-skincare/
│
├── actions/                  # File pemroses aksi CRUD (Backend Controller)
│   ├── tambah_task.php       # CREATE: Menyimpan data rutinitas baru
│   ├── toggle_status.php     # UPDATE: Checklist selesai/belum (AJAX)
│   ├── edit_task.php         # UPDATE: Memperbarui detail rutinitas
│   └── hapus_task.php        # DELETE: Menghapus rutinitas
│
├── assets/                   # Aset Antarmuka Web
│   ├── css/
│   │   └── style.css         # Styling Soft Pink Aesthetic & Responsif
│   └── js/
│       └── script.js         # Interaksi DOM, AJAX Fetch, Live Search, Modal
│
├── config/
│   ├── auth.php              # Middleware session dan fungsi requireLogin()
│   └── koneksi.php           # File koneksi database MySQL menggunakan PDO
│
├── database.sql              # Skrip SQL untuk membuat database, tabel, & relasi
├── index.php                 # Halaman utama aplikasi (Daftar, Form, Filter)
├── login.php                 # Halaman login pengguna
├── register.php              # Halaman registrasi akun baru
├── logout.php                # Script logout & destroy session
├── PANDUAN_UJIAN_UKK.md      # Panduan Belajar & Bocoran Tanya Jawab Penguji UKK
└── README.md                 # Petunjuk instalasi dan dokumentasi
```
