-- =====================================================================
-- DATABASE: sistem_informasi_sekolah
-- Sistem Informasi Sekolah & Monitoring Tahfiz Digital
-- SMA IT Al-Fadhilah Pekanbaru
-- =====================================================================

CREATE DATABASE IF NOT EXISTS `sistem_informasi_sekolah` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `sistem_informasi_sekolah`;

-- ---------------------------------------------------------------------
-- 1. TABEL PENGGUNA (users)
-- Mendukung 3 Peran: admin, ustadz, siswa
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `nama_lengkap` VARCHAR(100) NOT NULL,
    `role` ENUM('admin', 'ustadz', 'siswa') NOT NULL,
    `nip_nis` VARCHAR(30) NULL,
    `halaqah` VARCHAR(50) NULL DEFAULT 'Al-Furqan',
    `status` ENUM('aktif', 'nonaktif') NOT NULL DEFAULT 'aktif',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------
-- 2. TABEL KELOMPOK HALAQAH (halaqah)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `halaqah` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `kode_halaqah` VARCHAR(20) NOT NULL UNIQUE,
    `nama_halaqah` VARCHAR(50) NOT NULL,
    `ustadz_id` INT NULL,
    `nama_ustadz` VARCHAR(100) NOT NULL,
    `target_juz` VARCHAR(50) DEFAULT 'Juz 29 - 30',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------
-- 3. TABEL SETORAN HAFALAN BARU (setoran_hafalan)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `setoran_hafalan` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `siswa_id` INT NOT NULL,
    `nama_siswa` VARCHAR(100) NOT NULL,
    `ustadz_id` INT NOT NULL,
    `nama_ustadz` VARCHAR(100) NOT NULL,
    `surah` VARCHAR(50) NOT NULL,
    `ayat_awal` INT NOT NULL,
    `ayat_akhir` INT NOT NULL,
    `kualitas` VARCHAR(50) NOT NULL,
    `nilai` INT NOT NULL DEFAULT 85,
    `catatan` TEXT NULL,
    `tanggal_setor` DATE NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------
-- 4. TABEL MURAJAAH (murajaah)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `murajaah` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `siswa_id` INT NOT NULL,
    `nama_siswa` VARCHAR(100) NOT NULL,
    `ustadz_id` INT NOT NULL,
    `nama_ustadz` VARCHAR(100) NOT NULL,
    `surah` VARCHAR(50) NOT NULL,
    `ayat_range` VARCHAR(50) NOT NULL,
    `status_mutqin` VARCHAR(50) NOT NULL,
    `tanggal_murajaah` DATE NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------
-- 5. TABEL KONTEN INFORMASI (konten_informasi)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `konten_informasi` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `judul` VARCHAR(200) NOT NULL,
    `kategori` ENUM('Pengumuman', 'Berita', 'Agenda') NOT NULL DEFAULT 'Pengumuman',
    `isi` TEXT NOT NULL,
    `penulis` VARCHAR(100) DEFAULT 'Administrator',
    `tanggal_terbit` DATE NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------
-- DATA AWAL (SEEDER)
-- Akun Default:
-- Admin   : admin / admin123
-- Ustadz  : ustadz / ustadz123 (NIP: 199205142023031004)
-- Siswa   : siswa / siswa123 (NIS: 102601)
-- ---------------------------------------------------------------------
INSERT INTO `users` (`id`, `username`, `password`, `nama_lengkap`, `role`, `nip_nis`, `halaqah`, `status`) VALUES
(1, 'admin', 'admin123', 'Administrator Sekolah', 'admin', '198700', 'Koordinator Pusat', 'aktif'),
(2, 'ustadz', 'ustadz123', 'Ust. Rival Alvian Melfandy', 'ustadz', '199205142023031004', 'Al-Furqan', 'aktif'),
(3, 'siswa', 'siswa123', 'Muhammad Fadhlan', 'siswa', '102601', 'Al-Furqan', 'aktif'),
(4, '102602', 'siswa123', 'Ahmad Zaid', 'siswa', '102602', 'Al-Furqan', 'aktif'),
(5, '102603', 'siswa123', 'Radit Gilang Ramadhan S', 'siswa', '102603', 'Al-Furqan', 'aktif')
ON DUPLICATE KEY UPDATE `username`=`username`;

INSERT INTO `halaqah` (`kode_halaqah`, `nama_halaqah`, `ustadz_id`, `nama_ustadz`, `target_juz`) VALUES
('HLQ-01', 'Al-Furqan', 2, 'Ust. Rival Alvian Melfandy', 'Juz 29 - 30'),
('HLQ-02', 'Al-Ikhlas', 2, 'Ust. Ahmad Fauzi', 'Juz 29 - 30'),
('HLQ-03', 'Al-Falaq', 2, 'Ust. Muhammad Ridwan', 'Juz 29 - 30')
ON DUPLICATE KEY UPDATE `kode_halaqah`=`kode_halaqah`;

INSERT INTO `setoran_hafalan` (`siswa_id`, `nama_siswa`, `ustadz_id`, `nama_ustadz`, `surah`, `ayat_awal`, `ayat_akhir`, `kualitas`, `nilai`, `catatan`, `tanggal_setor`) VALUES
(3, 'Muhammad Fadhlan', 2, 'Ust. Rival Alvian Melfandy', 'Al-Mulk', 13, 18, 'Sangat Lancar', 90, 'Makhraj huruf lancar, perhatikan mad pada ayat 15.', '2026-11-05'),
(3, 'Muhammad Fadhlan', 2, 'Ust. Rival Alvian Melfandy', 'Al-Mulk', 7, 12, 'Lancar', 88, 'Tingkatkan sambungan antar ayat.', '2026-11-04'),
(3, 'Muhammad Fadhlan', 2, 'Ust. Rival Alvian Melfandy', 'Al-Mulk', 1, 6, 'Sangat Lancar', 92, 'Tajwid sangat tepat.', '2026-11-03');

