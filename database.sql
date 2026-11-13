-- ==============================================================
-- DATABASE: db_skincare_todolist
-- Project: Skincare & Self-Care To-Do List (UKK Asisten Pemrograman)
-- Fitur: Multi-User Autentikasi + Drag & Drop Reordering
-- ==============================================================

CREATE DATABASE IF NOT EXISTS `db_skincare_todolist` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `db_skincare_todolist`;

-- --------------------------------------------------------
-- 1. Tabel: users (Data Pengguna / Akun Login)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL, -- Disimpan dalam bentuk hash aman (password_hash)
    `skin_type` VARCHAR(50) NULL DEFAULT 'Normal Skin',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- --------------------------------------------------------
-- 2. Tabel: categories (Master Kategori Rutinitas)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `categories` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `slug` VARCHAR(50) NOT NULL UNIQUE,
    `badge_icon` VARCHAR(50) NOT NULL DEFAULT '✨',
    `theme_color` VARCHAR(50) NOT NULL DEFAULT 'pink',
    `description` VARCHAR(255) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- --------------------------------------------------------
-- 3. Tabel: tasks (Daftar Langkah Rutinitas Per-User)
-- Dilengkapi kolom order_index untuk fitur Drag and Drop
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `tasks` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `category_id` INT NOT NULL,
    `task_name` VARCHAR(255) NOT NULL,
    `target_time` VARCHAR(50) NULL,
    `notes` TEXT NULL,
    `priority` ENUM('wajib', 'rutin', 'opsional') DEFAULT 'rutin',
    `is_completed` TINYINT(1) NOT NULL DEFAULT 0,
    `order_index` INT NOT NULL DEFAULT 0, -- Kolom untuk menyimpan urutan Drag & Drop
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_tasks_user` FOREIGN KEY (`user_id`) 
        REFERENCES `users` (`id`) 
        ON DELETE CASCADE 
        ON UPDATE CASCADE,
    CONSTRAINT `fk_tasks_category` FOREIGN KEY (`category_id`) 
        REFERENCES `categories` (`id`) 
        ON DELETE CASCADE 
        ON UPDATE CASCADE
) ENGINE=InnoDB;

-- --------------------------------------------------------
-- Data Awal Pengguna (Seeder User Akun Demo)
-- Username: alya / Password: password123
-- --------------------------------------------------------
INSERT INTO `users` (`id`, `name`, `username`, `password`, `skin_type`) VALUES
(1, 'Alya Putri 🌸', 'alya', '$2y$10$wS2Wb5sF.qQ3y5F3z9oMheqPknkL9N7pQzF36oN9d2g5xK5s3u9aW', 'Combination Skin')
ON DUPLICATE KEY UPDATE name=VALUES(name);

-- --------------------------------------------------------
-- Data Awal Kategori (Seeder)
-- --------------------------------------------------------
INSERT INTO `categories` (`id`, `name`, `slug`, `badge_icon`, `theme_color`, `description`) VALUES
(1, 'Skincare Pagi', 'morning', '☀️', 'rose', 'Perlindungan & hidrasi kulit untuk beraktivitas seharian'),
(2, 'Skincare Malam', 'night', '🌙', 'purple', 'Perbaikan sel kulit & nutrisi mendalam saat istirahat malam'),
(3, 'Body & Health Care', 'health', '💖', 'emerald', 'Kebugaran tubuh, hidrasi air putih, dan relaksasi')
ON DUPLICATE KEY UPDATE name=VALUES(name);

-- --------------------------------------------------------
-- Data Contoh Awal Rutinitas Milik User ID 1 (Alya)
-- --------------------------------------------------------
INSERT INTO `tasks` (`id`, `user_id`, `category_id`, `task_name`, `target_time`, `notes`, `priority`, `is_completed`, `order_index`) VALUES
(1, 1, 1, 'Gentle Facial Wash', '06:30 WIB', 'Pijat wajah lembut dengan air hangat suam-suam kuku', 'wajib', 1, 1),
(2, 1, 1, 'Hydrating Toner & Niacinamide Serum', '06:40 WIB', 'Tepuk-tepuk hingga meresap sempurna sebelum pelembap', 'rutin', 1, 2),
(3, 1, 1, 'Sunscreen UV Filter SPF 50+', '07:00 WIB', 'Gunakan sebanyak 2 ruas jari. Wajib re-apply saat siang!', 'wajib', 0, 3),
(4, 1, 2, 'Micellar Water / Cleansing Balm', '20:00 WIB', 'Double cleansing untuk angkat debu, minyak & sisa sunscreen', 'wajib', 0, 4),
(5, 1, 2, 'Moisturizer Ceramide Barrier Gel', '20:30 WIB', 'Kunci kelembapan dan rawat skin barrier saat tidur', 'rutin', 0, 5),
(6, 1, 3, 'Minum Air Putih 2 Liter', 'Sepanjang hari', 'Minimal 8 gelas sehari untuk kulit glowing terhidrasi', 'wajib', 0, 6),
(7, 1, 3, 'Sheet Mask / Eksfoliasi Halus', '21:00 WIB', 'Maksimal 2 kali seminggu agar tidak over-exfoliate', 'opsional', 0, 7)
ON DUPLICATE KEY UPDATE task_name=VALUES(task_name);
