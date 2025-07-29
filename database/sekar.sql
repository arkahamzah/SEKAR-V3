-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 26, 2025 at 07:28 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `sekar`
--

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cache`
--

INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES
('laravel-cache-dashboard_statistics', 'a:6:{s:12:\"anggotaAktif\";i:8;s:13:\"totalPengurus\";i:7;s:13:\"anggotaKeluar\";i:1;s:10:\"nonAnggota\";i:2;s:16:\"mappingWithStats\";O:29:\"Illuminate\\Support\\Collection\":2:{s:8:\"\0*\0items\";a:3:{i:0;O:8:\"stdClass\":6:{s:3:\"dpw\";s:9:\"DPW Jabar\";s:3:\"dpd\";s:11:\"DPD Bandung\";s:13:\"anggota_aktif\";i:4;s:8:\"pengurus\";i:3;s:14:\"anggota_keluar\";i:1;s:11:\"non_anggota\";i:1;}i:1;O:8:\"stdClass\":6:{s:3:\"dpw\";s:11:\"DPW Jakarta\";s:3:\"dpd\";s:17:\"DPD Jakarta Pusat\";s:13:\"anggota_aktif\";i:2;s:8:\"pengurus\";i:2;s:14:\"anggota_keluar\";i:0;s:11:\"non_anggota\";i:1;}i:2;O:8:\"stdClass\":6:{s:3:\"dpw\";s:9:\"DPW Jatim\";s:3:\"dpd\";s:12:\"DPD Surabaya\";s:13:\"anggota_aktif\";i:2;s:8:\"pengurus\";i:2;s:14:\"anggota_keluar\";i:0;s:11:\"non_anggota\";i:0;}}s:28:\"\0*\0escapeWhenCastingToString\";b:0;}s:10:\"growthData\";a:4:{s:20:\"anggota_aktif_growth\";s:2:\"+1\";s:15:\"pengurus_growth\";s:1:\"0\";s:21:\"anggota_keluar_growth\";s:2:\"+1\";s:18:\"non_anggota_growth\";s:1:\"0\";}}', 1753430849);

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `m_jajaran`
--

CREATE TABLE `m_jajaran` (
  `ID` int(11) NOT NULL,
  `NAMA_JAJARAN` varchar(255) DEFAULT NULL,
  `IS_AKTIF` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

--
-- Dumping data for table `m_jajaran`
--

INSERT INTO `m_jajaran` (`ID`, `NAMA_JAJARAN`, `IS_AKTIF`) VALUES
(1, 'KETUA UMUM', '1'),
(2, 'WAKIL SEKRETARIS JENDRAL', '1'),
(3, 'SEKRETARIS JENDRAL', '1');

-- --------------------------------------------------------

--
-- Table structure for table `p_params`
--

CREATE TABLE `p_params` (
  `ID` int(11) NOT NULL,
  `NOMINAL_IURAN_WAJIB` varchar(50) DEFAULT NULL,
  `NOMINAL_BANPERS` varchar(50) DEFAULT NULL,
  `CREATED_BY` varchar(255) DEFAULT NULL,
  `CREATED_AT` datetime DEFAULT NULL,
  `TAHUN` varchar(4) DEFAULT NULL,
  `IS_AKTIF` char(2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

--
-- Dumping data for table `p_params`
--

INSERT INTO `p_params` (`ID`, `NOMINAL_IURAN_WAJIB`, `NOMINAL_BANPERS`, `CREATED_BY`, `CREATED_AT`, `TAHUN`, `IS_AKTIF`) VALUES
(1, '25000', '20000', '401031', '2025-07-22 09:20:34', '2025', '1');

-- --------------------------------------------------------

--
-- Table structure for table `t_ex_anggota`
--

CREATE TABLE `t_ex_anggota` (
  `ID` int(11) NOT NULL,
  `N_NIK` varchar(60) DEFAULT NULL,
  `V_NAMA_KARYAWAN` varchar(100) DEFAULT NULL,
  `V_SHORT_POSISI` varchar(150) DEFAULT NULL,
  `V_SHORT_DIVISI` varchar(150) DEFAULT NULL,
  `TGL_KELUAR` datetime DEFAULT NULL,
  `DPP` varchar(50) DEFAULT NULL,
  `DPW` varchar(50) DEFAULT NULL,
  `DPD` varchar(50) DEFAULT NULL,
  `V_KOTA_GEDUNG` varchar(100) DEFAULT NULL,
  `NO_TELP` varchar(20) DEFAULT NULL,
  `CREATED_BY` varchar(20) DEFAULT NULL,
  `CREATED_AT` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

--
-- Dumping data for table `t_ex_anggota`
--

INSERT INTO `t_ex_anggota` (`ID`, `N_NIK`, `V_NAMA_KARYAWAN`, `V_SHORT_POSISI`, `V_SHORT_DIVISI`, `TGL_KELUAR`, `DPP`, `DPW`, `DPD`, `V_KOTA_GEDUNG`, `NO_TELP`, `CREATED_BY`, `CREATED_AT`) VALUES
(1, '401032', 'DEBORAH', 'OFF 3 SYSTEM INFORMATION', 'DIVISI INFORMATION TECHNOLOGY', '2025-07-22 09:16:30', NULL, NULL, 'BD00', 'BANDUNG', '0828-8901-2345', '401031', '2025-07-22 09:16:54');

-- --------------------------------------------------------

--
-- Table structure for table `t_iuran`
--

CREATE TABLE `t_iuran` (
  `ID` int(11) NOT NULL,
  `N_NIK` varchar(30) DEFAULT NULL,
  `IURAN_WAJIB` varchar(20) DEFAULT NULL,
  `IURAN_SUKARELA` varchar(20) DEFAULT NULL,
  `CREATED_BY` varchar(30) DEFAULT NULL,
  `CREATED_AT` datetime DEFAULT NULL,
  `UPDATE_BY` varchar(30) DEFAULT NULL,
  `UPDATED_AT` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

--
-- Dumping data for table `t_iuran`
--

INSERT INTO `t_iuran` (`ID`, `N_NIK`, `IURAN_WAJIB`, `IURAN_SUKARELA`, `CREATED_BY`, `CREATED_AT`, `UPDATE_BY`, `UPDATED_AT`) VALUES
(1, '401031', '25000', '15000', '401031', '2025-07-25 02:42:29', '401031', '2025-07-25 02:42:49'),
(2, '401032', '25000', '20000', '401032', '2025-07-25 02:52:46', NULL, NULL),
(3, '401033', '25000', '10000', '401033', '2025-07-25 10:42:16', NULL, NULL),
(4, '601031', '25000', '15000', '601031', '2025-07-25 10:42:16', NULL, NULL),
(5, '501031', '25000', '30000', '501031', '2025-07-25 10:42:16', '501031', '2025-07-25 06:43:49'),
(6, '601032', '25000', '12000', '601032', '2025-07-25 10:42:16', NULL, NULL),
(7, '401034', '25000', '8000', '401034', '2025-07-25 10:42:16', NULL, NULL),
(8, '501032', '25000', '18000', '501032', '2025-07-25 10:42:16', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `t_iuran_history`
--

CREATE TABLE `t_iuran_history` (
  `ID` bigint(20) UNSIGNED NOT NULL,
  `N_NIK` varchar(30) NOT NULL,
  `JENIS` varchar(20) NOT NULL,
  `NOMINAL_LAMA` varchar(20) DEFAULT NULL,
  `NOMINAL_BARU` varchar(20) NOT NULL,
  `STATUS_PROSES` varchar(20) NOT NULL DEFAULT 'PENDING',
  `TGL_PERUBAHAN` datetime NOT NULL,
  `TGL_PROSES` datetime DEFAULT NULL,
  `TGL_IMPLEMENTASI` datetime DEFAULT NULL,
  `KETERANGAN` varchar(255) DEFAULT NULL,
  `CREATED_BY` varchar(30) NOT NULL,
  `CREATED_AT` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `t_iuran_history`
--

INSERT INTO `t_iuran_history` (`ID`, `N_NIK`, `JENIS`, `NOMINAL_LAMA`, `NOMINAL_BARU`, `STATUS_PROSES`, `TGL_PERUBAHAN`, `TGL_PROSES`, `TGL_IMPLEMENTASI`, `KETERANGAN`, `CREATED_BY`, `CREATED_AT`) VALUES
(1, '401031', 'SUKARELA', '10000', '15000', 'PENDING', '2025-07-25 02:42:49', '2025-08-20 02:42:49', '2025-09-01 02:42:49', 'Perubahan iuran sukarela oleh anggota', '401031', '2025-07-25 02:42:49'),
(2, '501031', 'SUKARELA', '20000', '30000', 'PENDING', '2025-07-25 06:43:49', '2025-08-20 06:43:49', '2025-09-01 06:43:49', 'Perubahan iuran sukarela oleh anggota', '501031', '2025-07-25 06:43:49');

-- --------------------------------------------------------

--
-- Table structure for table `t_karyawan`
--

CREATE TABLE `t_karyawan` (
  `ID` int(11) NOT NULL,
  `N_NIK` varchar(30) DEFAULT NULL,
  `V_NAMA_KARYAWAN` varchar(150) DEFAULT NULL,
  `V_SHORT_UNIT` varchar(150) DEFAULT NULL,
  `V_SHORT_POSISI` varchar(150) DEFAULT NULL,
  `C_KODE_POSISI` varchar(60) DEFAULT NULL,
  `C_KODE_UNIT` varchar(60) DEFAULT NULL,
  `V_SHORT_DIVISI` varchar(150) DEFAULT NULL,
  `V_BAND_POSISI` varchar(4) DEFAULT NULL,
  `C_KODE_DIVISI` varchar(50) DEFAULT NULL,
  `C_PERSONNEL_AREA` varchar(100) DEFAULT NULL,
  `C_PERSONNEL_SUB_AREA` varchar(100) DEFAULT NULL,
  `V_KOTA_GEDUNG` varchar(100) DEFAULT NULL,
  `NO_TELP` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

--
-- Dumping data for table `t_karyawan`
--

INSERT INTO `t_karyawan` (`ID`, `N_NIK`, `V_NAMA_KARYAWAN`, `V_SHORT_UNIT`, `V_SHORT_POSISI`, `C_KODE_POSISI`, `C_KODE_UNIT`, `V_SHORT_DIVISI`, `V_BAND_POSISI`, `C_KODE_DIVISI`, `C_PERSONNEL_AREA`, `C_PERSONNEL_SUB_AREA`, `V_KOTA_GEDUNG`, `NO_TELP`) VALUES
(1, '401031', 'ADHIE PRANATHA', 'HCM RISK OPERATION', 'OFF 2 HCM & RISK OPERATION', 'COP0200001', 'DIT-1020202', 'DIVISI INFORMATION SYSTEM', 'V', 'DIV-DIT', 'HCP2', 'BN00', 'BANDUNG', '0812-3456-7890'),
(2, '401032', 'SARI DEWI LESTARI', 'NETWORK OPERATION', 'SPV NETWORK MONITORING', 'COP0300001', 'DIT-1030101', 'DIVISI NETWORK & IT SERVICE', 'IV', 'DIV-NIT', 'HCP2', 'BN00', 'BANDUNG', '0813-4567-8901'),
(3, '401033', 'BUDI SANTOSO', 'SOFTWARE DEVELOPMENT', 'PROGRAMMER ANALYST', 'COP0400001', 'DIT-1040201', 'DIVISI DIGITAL BUSINESS', 'III', 'DIV-DB', 'HCP2', 'BN00', 'BANDUNG', '0814-5678-9012'),
(4, '401034', 'RINA SARI INDAH', 'HUMAN CAPITAL', 'OFF 1 RECRUITMENT', 'COP0500001', 'DIT-1050301', 'DIVISI HUMAN CAPITAL', 'IV', 'DIV-HC', 'HCP2', 'BN00', 'BANDUNG', '0815-6789-0123'),
(5, '401035', 'AHMAD FAUZI', 'ENTERPRISE SALES', 'ACCOUNT MANAGER', 'COP0600001', 'DIT-1060101', 'DIVISI ENTERPRISE & BUSINESS', 'IV', 'DIV-EB', 'HCP2', 'BN00', 'BANDUNG', '0816-7890-1234'),
(6, '501031', 'DIANA SARTIKA', 'CORPORATE STRATEGY', 'MGR STRATEGIC PLANNING', 'COP0700001', 'DIT-2010101', 'DIVISI CORPORATE PLANNING', 'V', 'DIV-CP', 'HCP1', 'JK01', 'JAKARTA', '0817-8901-2345'),
(7, '501032', 'RUDI HERMAWAN', 'FINANCE & ACCOUNTING', 'SPV FINANCIAL REPORTING', 'COP0800001', 'DIT-2020201', 'DIVISI FINANCE', 'IV', 'DIV-FIN', 'HCP1', 'JK01', 'JAKARTA', '0818-9012-3456'),
(8, '501033', 'MAYA KUSUMA', 'LEGAL & COMPLIANCE', 'LEGAL OFFICER', 'COP0900001', 'DIT-2030101', 'DIVISI LEGAL & COMPLIANCE', 'III', 'DIV-LC', 'HCP1', 'JK01', 'JAKARTA', '0819-0123-4567'),
(9, '601031', 'AGUS SETIAWAN', 'REGIONAL SALES EAST', 'MGR REGIONAL SALES', 'COP1200001', 'DIT-3010101', 'DIVISI CONSUMER', 'V', 'DIV-CONS', 'HCP3', 'SB00', 'SURABAYA', '0823-3456-7890'),
(10, '601032', 'FITRI HANDAYANI', 'CUSTOMER SERVICE', 'SPV CUSTOMER CARE', 'COP1300001', 'DIT-3020101', 'DIVISI CUSTOMER EXPERIENCE', 'IV', 'DIV-CX', 'HCP3', 'SB00', 'SURABAYA', '0824-4567-8901'),
(11, '301001', 'ANDI GPTP BARU', 'TRAINING PROGRAM', 'GPTP PROGRAM 2025', 'GPTP2025001', 'TRAIN-001', 'DIVISI HUMAN CAPITAL', 'GPTP', 'DIV-HC', 'HCP2', 'BN00', 'BANDUNG', '0829-9012-3456'),
(12, '301002', 'SITI GPTP PROGRAM', 'TRAINING PROGRAM', 'GPTP DEVELOPMENT', 'GPTP2025002', 'TRAIN-002', 'DIVISI INFORMATION TECHNOLOGY', 'GPTP', 'DIV-IT', 'HCP2', 'BN00', 'BANDUNG', '0831-0123-4567'),
(13, '301003', 'BUDI GPTP TRAINEE', 'TRAINING PROGRAM', 'GPTP NETWORK', 'GPTP2025003', 'TRAIN-003', 'DIVISI NETWORK & IT SERVICE', 'GPTP', 'DIV-NIT', 'HCP1', 'JK01', 'JAKARTA', '0832-1234-5678');

-- --------------------------------------------------------

--
-- Table structure for table `t_konsultasi`
--

CREATE TABLE `t_konsultasi` (
  `ID` bigint(20) UNSIGNED NOT NULL,
  `N_NIK` varchar(30) NOT NULL,
  `JENIS` enum('ADVOKASI','ASPIRASI') NOT NULL,
  `KATEGORI_ADVOKASI` varchar(100) DEFAULT NULL,
  `TUJUAN` enum('DPP','DPW','DPD','GENERAL') NOT NULL,
  `TUJUAN_SPESIFIK` varchar(100) DEFAULT NULL,
  `JUDUL` varchar(200) NOT NULL,
  `DESKRIPSI` text NOT NULL,
  `STATUS` enum('OPEN','IN_PROGRESS','CLOSED') NOT NULL DEFAULT 'OPEN',
  `CREATED_BY` varchar(30) NOT NULL,
  `CREATED_AT` datetime NOT NULL,
  `UPDATED_BY` varchar(30) DEFAULT NULL,
  `UPDATED_AT` datetime DEFAULT NULL,
  `CLOSED_BY` varchar(30) DEFAULT NULL,
  `CLOSED_AT` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `t_konsultasi`
--

INSERT INTO `t_konsultasi` (`ID`, `N_NIK`, `JENIS`, `KATEGORI_ADVOKASI`, `TUJUAN`, `TUJUAN_SPESIFIK`, `JUDUL`, `DESKRIPSI`, `STATUS`, `CREATED_BY`, `CREATED_AT`, `UPDATED_BY`, `UPDATED_AT`, `CLOSED_BY`, `CLOSED_AT`) VALUES
(1, '401032', 'ADVOKASI', 'Pelecehan di Tempat Kerja', 'GENERAL', NULL, 'love bombing', 'force relationship', 'OPEN', '401032', '2025-07-26 05:27:40', NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `t_konsultasi_komentar`
--

CREATE TABLE `t_konsultasi_komentar` (
  `ID` bigint(20) UNSIGNED NOT NULL,
  `ID_KONSULTASI` bigint(20) UNSIGNED NOT NULL,
  `N_NIK` varchar(30) NOT NULL,
  `KOMENTAR` text NOT NULL,
  `PENGIRIM_ROLE` enum('USER','ADMIN') NOT NULL DEFAULT 'USER',
  `CREATED_AT` datetime NOT NULL,
  `CREATED_BY` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `t_sekar_jajaran`
--

CREATE TABLE `t_sekar_jajaran` (
  `ID` int(11) NOT NULL,
  `N_NIK` varchar(50) DEFAULT NULL,
  `V_NAMA_KARYAWAN` varchar(150) DEFAULT NULL,
  `ID_JAJARAN` varchar(255) DEFAULT NULL,
  `START_DATE` datetime DEFAULT NULL,
  `END_DATE` datetime DEFAULT NULL,
  `CREATED_BY` char(30) DEFAULT NULL,
  `CREATED_AT` datetime DEFAULT NULL,
  `IS_AKTIF` varchar(2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

--
-- Dumping data for table `t_sekar_jajaran`
--

INSERT INTO `t_sekar_jajaran` (`ID`, `N_NIK`, `V_NAMA_KARYAWAN`, `ID_JAJARAN`, `START_DATE`, `END_DATE`, `CREATED_BY`, `CREATED_AT`, `IS_AKTIF`) VALUES
(1, '990009', 'JHON CRUYF', '1', '2025-07-22 09:11:57', '2026-02-22 09:12:01', '401031', '2025-07-22 09:12:12', '1'),
(2, '980303', 'EMIL DARDAK', '2', '2025-07-22 09:12:49', '2026-02-22 09:12:01', '401031', '2025-07-22 09:12:12', '1');

-- --------------------------------------------------------

--
-- Table structure for table `t_sekar_pengurus`
--

CREATE TABLE `t_sekar_pengurus` (
  `ID` int(11) NOT NULL,
  `N_NIK` varchar(30) DEFAULT NULL,
  `V_SHORT_POSISI` varchar(100) DEFAULT NULL,
  `V_SHORT_UNIT` varchar(100) DEFAULT NULL,
  `CREATED_BY` varchar(100) DEFAULT NULL,
  `CREATED_AT` datetime DEFAULT NULL,
  `UPDATED_BY` varchar(50) DEFAULT NULL,
  `UPDATED_AT` datetime DEFAULT NULL,
  `DPP` varchar(100) DEFAULT NULL,
  `DPW` varchar(100) DEFAULT NULL,
  `DPD` varchar(100) DEFAULT NULL,
  `ID_ROLES` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

--
-- Dumping data for table `t_sekar_pengurus`
--

INSERT INTO `t_sekar_pengurus` (`ID`, `N_NIK`, `V_SHORT_POSISI`, `V_SHORT_UNIT`, `CREATED_BY`, `CREATED_AT`, `UPDATED_BY`, `UPDATED_AT`, `DPP`, `DPW`, `DPD`, `ID_ROLES`) VALUES
(1, '401031', 'OFF 2 HCM MANAGEMENT', 'HCM & RISK OPERATION', '401031', '2025-07-22 09:10:02', NULL, NULL, NULL, 'DPW Jabar', 'DPD Bandung', 1),
(2, '980269', 'MGR RISK OPERATION', 'RISK OPERATION', '980269', '2025-07-22 09:10:58', NULL, NULL, 'BN00', 'DPW Jabar', 'DPD Jakarta', 2),
(3, '401033', 'PROGRAMMER ANALYST', 'SOFTWARE DEVELOPMENT', '401031', '2025-07-25 10:42:16', NULL, NULL, NULL, 'DPW Jabar', 'DPD Bandung', 3),
(4, '501031', 'MGR STRATEGIC PLANNING', 'CORPORATE STRATEGY', '401031', '2025-07-25 10:42:16', NULL, NULL, 'DPP Jakarta', 'DPW Jakarta', 'DPD Jakarta Pusat', 2),
(5, '401034', 'OFF 1 RECRUITMENT', 'HUMAN CAPITAL', '401031', '2025-07-25 10:42:16', NULL, NULL, NULL, 'DPW Jabar', 'DPD Bandung', 4),
(6, '501032', 'SPV FINANCIAL REPORTING', 'FINANCE & ACCOUNTING', '401031', '2025-07-25 10:42:16', NULL, NULL, NULL, 'DPW Jakarta', 'DPD Jakarta Pusat', 3),
(7, '601031', 'MGR REGIONAL SALES', 'REGIONAL SALES EAST', '401031', '2025-07-25 10:42:16', NULL, NULL, NULL, 'DPW Jatim', 'DPD Surabaya', 2),
(8, '601032', 'SPV CUSTOMER CARE', 'CUSTOMER SERVICE', '401031', '2025-07-25 10:42:16', NULL, NULL, NULL, 'DPW Jatim', 'DPD Surabaya', 4);

-- --------------------------------------------------------

--
-- Table structure for table `t_sekar_roles`
--

CREATE TABLE `t_sekar_roles` (
  `ID` int(11) NOT NULL,
  `NAME` varchar(150) DEFAULT NULL,
  `DESC` varchar(255) DEFAULT NULL,
  `IS_AKTIF` char(2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

--
-- Dumping data for table `t_sekar_roles`
--

INSERT INTO `t_sekar_roles` (`ID`, `NAME`, `DESC`, `IS_AKTIF`) VALUES
(1, 'ADM', 'Administrator', '1'),
(2, 'ADMIN_DPP', 'Admin DPP', '1'),
(3, 'ADMIN_DPW', 'Admin DPW', '1'),
(4, 'ADMIN_DPD', 'Admin DPD', '1');

-- --------------------------------------------------------

--
-- Table structure for table `t_setting`
--

CREATE TABLE `t_setting` (
  `ID` int(11) NOT NULL,
  `SETTING_KEY` varchar(100) NOT NULL,
  `SETTING_VALUE` text DEFAULT NULL,
  `SETTING_TYPE` varchar(50) DEFAULT 'text',
  `DESCRIPTION` varchar(255) DEFAULT NULL,
  `CREATED_BY` varchar(30) DEFAULT NULL,
  `CREATED_AT` datetime DEFAULT NULL,
  `UPDATED_BY` varchar(30) DEFAULT NULL,
  `UPDATED_AT` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `t_setting`
--

INSERT INTO `t_setting` (`ID`, `SETTING_KEY`, `SETTING_VALUE`, `SETTING_TYPE`, `DESCRIPTION`, `CREATED_BY`, `CREATED_AT`, `UPDATED_BY`, `UPDATED_AT`) VALUES
(1, 'sekjen_signature', '', 'file', 'Tanda tangan Sekretaris Jenderal', 'system', '2025-07-25 14:31:39', NULL, NULL),
(2, 'waketum_signature', '', 'file', 'Tanda tangan Wakil Ketua Umum', 'system', '2025-07-25 14:31:39', NULL, NULL),
(3, 'signature_periode_start', '', 'date', 'Tanggal mulai periode tanda tangan', 'system', '2025-07-25 14:31:39', NULL, NULL),
(4, 'signature_periode_end', '', 'date', 'Tanggal akhir periode tanda tangan', 'system', '2025-07-25 14:31:39', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `nik` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `nik`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'ADHIE PRANATHA', '401031@sekar.local', '401031', NULL, '$2y$12$N2.RNwKRnnE4R/VDc5JMXu5W3Ko6xL7fdRXCNWPOIJ4FpKOJpvtw6', NULL, '2025-07-24 19:42:29', '2025-07-24 19:42:29'),
(2, 'SARI DEWI LESTARI', '401032@sekar.local', '401032', NULL, '$2y$12$2W9sX2db/DuRnpKe.7R52uXCHqUItLeNursm3WXIZA.g.3.4VK5d.', NULL, '2025-07-24 19:52:46', '2025-07-24 19:52:46'),
(3, 'BUDI SANTOSO', '401033@sekar.local', '401033', NULL, '$2y$12$C3JLMGZ.Iv0CsJOvwkNzmOpltR7EgL2PmxWrhnoiwupoWz9GdHXKq', NULL, '2025-07-25 03:39:17', '2025-07-25 03:39:17'),
(4, 'DIANA SARTIKA', '501031@sekar.local', '501031', NULL, '$2y$12$C3JLMGZ.Iv0CsJOvwkNzmOpltR7EgL2PmxWrhnoiwupoWz9GdHXKq', NULL, '2025-07-25 03:39:17', '2025-07-25 03:39:17'),
(5, 'RINA SARI INDAH', '401034@sekar.local', '401034', NULL, '$2y$12$C3JLMGZ.Iv0CsJOvwkNzmOpltR7EgL2PmxWrhnoiwupoWz9GdHXKq', NULL, '2025-07-25 03:39:17', '2025-07-25 03:39:17'),
(6, 'RUDI HERMAWAN', '501032@sekar.local', '501032', NULL, '$2y$12$C3JLMGZ.Iv0CsJOvwkNzmOpltR7EgL2PmxWrhnoiwupoWz9GdHXKq', NULL, '2025-07-25 03:39:17', '2025-07-25 03:39:17'),
(7, 'AGUS SETIAWAN', '601031@sekar.local', '601031', NULL, '$2y$12$C3JLMGZ.Iv0CsJOvwkNzmOpltR7EgL2PmxWrhnoiwupoWz9GdHXKq', NULL, '2025-07-25 03:39:17', '2025-07-25 03:39:17'),
(8, 'FITRI HANDAYANI', '601032@sekar.local', '601032', NULL, '$2y$12$C3JLMGZ.Iv0CsJOvwkNzmOpltR7EgL2PmxWrhnoiwupoWz9GdHXKq', NULL, '2025-07-25 03:39:17', '2025-07-25 03:39:17');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `m_jajaran`
--
ALTER TABLE `m_jajaran`
  ADD PRIMARY KEY (`ID`) USING BTREE;

--
-- Indexes for table `p_params`
--
ALTER TABLE `p_params`
  ADD PRIMARY KEY (`ID`) USING BTREE;

--
-- Indexes for table `t_ex_anggota`
--
ALTER TABLE `t_ex_anggota`
  ADD PRIMARY KEY (`ID`) USING BTREE;

--
-- Indexes for table `t_iuran`
--
ALTER TABLE `t_iuran`
  ADD PRIMARY KEY (`ID`) USING BTREE;

--
-- Indexes for table `t_iuran_history`
--
ALTER TABLE `t_iuran_history`
  ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `t_karyawan`
--
ALTER TABLE `t_karyawan`
  ADD PRIMARY KEY (`ID`) USING BTREE;

--
-- Indexes for table `t_konsultasi`
--
ALTER TABLE `t_konsultasi`
  ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `t_konsultasi_komentar`
--
ALTER TABLE `t_konsultasi_komentar`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `t_konsultasi_komentar_id_konsultasi_foreign` (`ID_KONSULTASI`);

--
-- Indexes for table `t_sekar_jajaran`
--
ALTER TABLE `t_sekar_jajaran`
  ADD PRIMARY KEY (`ID`) USING BTREE;

--
-- Indexes for table `t_sekar_pengurus`
--
ALTER TABLE `t_sekar_pengurus`
  ADD PRIMARY KEY (`ID`) USING BTREE;

--
-- Indexes for table `t_sekar_roles`
--
ALTER TABLE `t_sekar_roles`
  ADD PRIMARY KEY (`ID`) USING BTREE;

--
-- Indexes for table `t_setting`
--
ALTER TABLE `t_setting`
  ADD PRIMARY KEY (`ID`),
  ADD UNIQUE KEY `SETTING_KEY` (`SETTING_KEY`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD UNIQUE KEY `users_nik_unique` (`nik`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `m_jajaran`
--
ALTER TABLE `m_jajaran`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `p_params`
--
ALTER TABLE `p_params`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `t_ex_anggota`
--
ALTER TABLE `t_ex_anggota`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `t_iuran`
--
ALTER TABLE `t_iuran`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `t_iuran_history`
--
ALTER TABLE `t_iuran_history`
  MODIFY `ID` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `t_karyawan`
--
ALTER TABLE `t_karyawan`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `t_konsultasi`
--
ALTER TABLE `t_konsultasi`
  MODIFY `ID` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `t_konsultasi_komentar`
--
ALTER TABLE `t_konsultasi_komentar`
  MODIFY `ID` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `t_sekar_jajaran`
--
ALTER TABLE `t_sekar_jajaran`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `t_sekar_pengurus`
--
ALTER TABLE `t_sekar_pengurus`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `t_sekar_roles`
--
ALTER TABLE `t_sekar_roles`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `t_setting`
--
ALTER TABLE `t_setting`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `t_konsultasi_komentar`
--
ALTER TABLE `t_konsultasi_komentar`
  ADD CONSTRAINT `t_konsultasi_komentar_id_konsultasi_foreign` FOREIGN KEY (`ID_KONSULTASI`) REFERENCES `t_konsultasi` (`ID`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
