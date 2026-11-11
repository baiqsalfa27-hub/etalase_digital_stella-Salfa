CREATE DATABASE IF NOT EXISTS `db_sekolah_digital` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `db_sekolah_digital`;

CREATE TABLE `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `nama` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `role` ENUM('super_admin', 'admin', 'siswa') NOT NULL DEFAULT 'siswa'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Buat tabel projects (dengan link_github dan link_demo)
CREATE TABLE `projects` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `nama_project` VARCHAR(150) NOT NULL,
  `deskripsi` TEXT NOT NULL,
  `gambar` VARCHAR(255) NOT NULL,
  `link_github` VARCHAR(255) NOT NULL,
  `link_demo` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Masukkan akun default (password menggunakan teks biasa agar langsung cocok dengan controller)
INSERT INTO `users` (`id`, `nama`, `email`, `password`, `role`) VALUES
(1, 'Super Admin Stella', 'superadmin@stella.sch.id', 'superadmin123', 'super_admin'),
(2, 'Admin Stella', 'admin@stella.sch.id', 'admin123', 'admin'),
(3, 'Siswa Stella', 'siswa@stella.sch.id', 'siswa123', 'siswa');