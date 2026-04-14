CREATE DATABASE  IF NOT EXISTS `sobatgadai` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `sobatgadai`;
-- MySQL dump 10.13  Distrib 8.0.34, for Win64 (x86_64)
--
-- Host: 127.0.0.1    Database: sobatgadai
-- ------------------------------------------------------
-- Server version	8.0.34

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `aktivitas_kerja`
--

DROP TABLE IF EXISTS `aktivitas_kerja`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `aktivitas_kerja` (
  `no` int NOT NULL AUTO_INCREMENT,
  `aktivitas` varchar(100) DEFAULT NULL,
  `tanggal` datetime DEFAULT NULL,
  `operated` varchar(30) DEFAULT NULL,
  `cabang` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`no`)
) ENGINE=InnoDB AUTO_INCREMENT=85 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `bayar`
--

DROP TABLE IF EXISTS `bayar`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `bayar` (
  `id_bayar` varchar(20) NOT NULL,
  `no_gadai` varchar(20) DEFAULT NULL,
  `cabang` varchar(50) DEFAULT NULL,
  `tanggal_bayar` date NOT NULL,
  `jumlah_bayar` decimal(15,2) NOT NULL,
  `metode_bayar` enum('cash','transfer') NOT NULL,
  `bukti_bayar` varchar(255) NOT NULL,
  `keterangan` varchar(10) DEFAULT NULL,
  `status_bayar` varchar(2) DEFAULT NULL,
  PRIMARY KEY (`id_bayar`),
  KEY `no_gadai` (`no_gadai`),
  CONSTRAINT `bayar_ibfk_1` FOREIGN KEY (`no_gadai`) REFERENCES `gadai` (`no_gadai`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dfbunga`
--

DROP TABLE IF EXISTS `dfbunga`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `dfbunga` (
  `no` varchar(2) NOT NULL,
  `bunga` varchar(30) NOT NULL,
  `tarif` float DEFAULT NULL,
  PRIMARY KEY (`no`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `gadai`
--

DROP TABLE IF EXISTS `gadai`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `gadai` (
  `no_gadai` varchar(100) NOT NULL,
  `nomor_nasabah` int DEFAULT NULL,
  `nama_nasabah` varchar(100) DEFAULT NULL,
  `cabang` varchar(50) DEFAULT NULL,
  `jenis` varchar(40) DEFAULT NULL,
  `jaminan` varchar(60) DEFAULT NULL,
  `tanggal_masuk` datetime DEFAULT NULL,
  `tanggal_keluar` datetime DEFAULT NULL,
  `nilai` float DEFAULT NULL,
  `nilai_taksir` float DEFAULT NULL,
  `bunga` float DEFAULT NULL,
  `biaya_adm` float DEFAULT NULL,
  `denda` float DEFAULT NULL,
  `denda_total` float DEFAULT '0',
  `status` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`no_gadai`),
  KEY `nomor_nasabah` (`nomor_nasabah`),
  CONSTRAINT `gadai_ibfk_1` FOREIGN KEY (`nomor_nasabah`) REFERENCES `nasabah_gadai` (`nomor_nasabah`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `gadai_confirmed`
--

DROP TABLE IF EXISTS `gadai_confirmed`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `gadai_confirmed` (
  `id` int NOT NULL AUTO_INCREMENT,
  `no_gadai` varchar(100) NOT NULL,
  `user_id` varchar(20) NOT NULL,
  `status_verifikasi` enum('diterima','pending','ditolak','lunas') NOT NULL,
  `catatan` text,
  `tanggal_verifikasi` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `no_gadai` (`no_gadai`),
  CONSTRAINT `gadai_confirmed_ibfk_1` FOREIGN KEY (`no_gadai`) REFERENCES `gadai` (`no_gadai`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `karyawan`
--

DROP TABLE IF EXISTS `karyawan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `karyawan` (
  `user_id` varchar(20) NOT NULL,
  `nama` varchar(70) DEFAULT NULL,
  `nohp` varchar(20) NOT NULL,
  `alamat` varchar(100) DEFAULT NULL,
  `jkel` varchar(30) DEFAULT NULL,
  `agama` varchar(20) DEFAULT NULL,
  `rl` varchar(30) DEFAULT NULL,
  `cabang` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`user_id`),
  CONSTRAINT `karyawan_chk_1` CHECK ((`agama` in (_utf8mb4'1',_utf8mb4'2',_utf8mb4'3',_utf8mb4'4',_utf8mb4'5',_utf8mb4'6')))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`sobatgadai`@`%`*/ /*!50003 TRIGGER `trig_log_daftar_karyawan` AFTER INSERT ON `karyawan` FOR EACH ROW BEGIN
    INSERT INTO logmasuk (user_id, activity, waktu)
    VALUES (NEW.user_id, 'Daftar karyawan', NOW());
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`sobatgadai`@`%`*/ /*!50003 TRIGGER `trg_after_insert_karyawan` AFTER INSERT ON `karyawan` FOR EACH ROW BEGIN
    UPDATE user_account
    SET created_at = NOW()
    WHERE user_id = NEW.user_id;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`sobatgadai`@`%`*/ /*!50003 TRIGGER `trig_log_hapus_karyawan` AFTER DELETE ON `karyawan` FOR EACH ROW BEGIN
    INSERT INTO logmasuk (user_id, activity, waktu)
    VALUES (OLD.user_id, 'Karyawan dihapus', NOW());
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `log_bayar`
--

DROP TABLE IF EXISTS `log_bayar`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `log_bayar` (
  `no` int NOT NULL AUTO_INCREMENT,
  `id_bayar` varchar(20) DEFAULT NULL,
  `aktivitas` text NOT NULL,
  `tanggal` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `operated` varchar(50) NOT NULL,
  PRIMARY KEY (`no`),
  KEY `id_bayar` (`id_bayar`),
  CONSTRAINT `log_bayar_ibfk_1` FOREIGN KEY (`id_bayar`) REFERENCES `bayar` (`id_bayar`)
) ENGINE=InnoDB AUTO_INCREMENT=91 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `logmasuk`
--

DROP TABLE IF EXISTS `logmasuk`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `logmasuk` (
  `user_id` varchar(20) DEFAULT NULL,
  `activity` varchar(60) DEFAULT NULL,
  `waktu` datetime DEFAULT CURRENT_TIMESTAMP,
  `operated` varchar(20) DEFAULT NULL,
  `cabang` varchar(20) DEFAULT NULL,
  KEY `log_masuk` (`user_id`),
  KEY `idx_waktu` (`waktu`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `nasabah_gadai`
--

DROP TABLE IF EXISTS `nasabah_gadai`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `nasabah_gadai` (
  `nomor_nasabah` int NOT NULL AUTO_INCREMENT,
  `nama_nasabah` varchar(100) NOT NULL,
  `ibu_kandung` varchar(100) DEFAULT NULL,
  `alamat` varchar(100) NOT NULL,
  `nohp` varchar(20) DEFAULT NULL,
  `cabang` varchar(50) DEFAULT NULL,
  `jenis_id` varchar(100) NOT NULL,
  `nomor_id` varchar(100) NOT NULL,
  `jml_transaksi` int DEFAULT NULL,
  PRIMARY KEY (`nomor_nasabah`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `produk`
--

DROP TABLE IF EXISTS `produk`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `produk` (
  `kd_produk` varchar(10) NOT NULL,
  `jenis` varchar(40) DEFAULT NULL,
  `deskripsi` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`kd_produk`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user_account`
--

DROP TABLE IF EXISTS `user_account`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_account` (
  `user_id` varchar(20) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `rl` varchar(30) DEFAULT NULL,
  `status` enum('verified','pending') NOT NULL DEFAULT 'pending',
  `cabang` varchar(50) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  KEY `fk_user_karyawan` (`user_id`),
  CONSTRAINT `fk_user_karyawan` FOREIGN KEY (`user_id`) REFERENCES `karyawan` (`user_id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`sobatgadai`@`%`*/ /*!50003 TRIGGER `trig_log_verifikasi_akun` AFTER UPDATE ON `user_account` FOR EACH ROW BEGIN
    IF NEW.status = 'verified' AND OLD.status <> 'verified' THEN
        INSERT INTO logmasuk (user_id, activity, waktu)
        VALUES (NEW.user_id, 'Diverifikasi', NOW());
    END IF;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`sobatgadai`@`%`*/ /*!50003 TRIGGER `after_password_update_log` AFTER UPDATE ON `user_account` FOR EACH ROW BEGIN
    IF OLD.password_hash <> NEW.password_hash THEN
        INSERT INTO logmasuk (user_id, activity, waktu, operated)
        VALUES (OLD.user_id, 'Ubah Password', NOW(), OLD.user_id);
    END IF;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`sobatgadai`@`%`*/ /*!50003 TRIGGER `trig_hapus_karyawan` AFTER DELETE ON `user_account` FOR EACH ROW BEGIN
    DELETE FROM karyawan
    WHERE user_id = OLD.user_id;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Dumping events for database 'sobatgadai'
--
/*!50106 SET @save_time_zone= @@TIME_ZONE */ ;
/*!50106 DROP EVENT IF EXISTS `ev_purge_logmasuk` */;
DELIMITER ;;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;;
/*!50003 SET character_set_client  = utf8mb4 */ ;;
/*!50003 SET character_set_results = utf8mb4 */ ;;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;;
/*!50003 SET @saved_time_zone      = @@time_zone */ ;;
/*!50003 SET time_zone             = 'SYSTEM' */ ;;
/*!50106 CREATE*/ /*!50117 DEFINER=`sobatgadai`@`%`*/ /*!50106 EVENT `ev_purge_logmasuk` ON SCHEDULE EVERY 1 DAY STARTS '2025-08-04 16:19:12' ON COMPLETION NOT PRESERVE ENABLE DO DELETE FROM logmasuk
  WHERE waktu < NOW() - INTERVAL 30 DAY */ ;;
/*!50003 SET time_zone             = @saved_time_zone */ ;;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;;
/*!50003 SET character_set_client  = @saved_cs_client */ ;;
/*!50003 SET character_set_results = @saved_cs_results */ ;;
/*!50003 SET collation_connection  = @saved_col_connection */ ;;
/*!50106 DROP EVENT IF EXISTS `hapus_log_bayar_lama` */;;
DELIMITER ;;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;;
/*!50003 SET character_set_client  = utf8mb4 */ ;;
/*!50003 SET character_set_results = utf8mb4 */ ;;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;;
/*!50003 SET @saved_time_zone      = @@time_zone */ ;;
/*!50003 SET time_zone             = 'SYSTEM' */ ;;
/*!50106 CREATE*/ /*!50117 DEFINER=`sobatgadai`@`%`*/ /*!50106 EVENT `hapus_log_bayar_lama` ON SCHEDULE EVERY 1 DAY STARTS '2025-08-14 14:45:40' ON COMPLETION NOT PRESERVE ENABLE DO DELETE FROM log_bayar
    WHERE tanggal < NOW() - INTERVAL 60 DAY */ ;;
/*!50003 SET time_zone             = @saved_time_zone */ ;;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;;
/*!50003 SET character_set_client  = @saved_cs_client */ ;;
/*!50003 SET character_set_results = @saved_cs_results */ ;;
/*!50003 SET collation_connection  = @saved_col_connection */ ;;
DELIMITER ;
/*!50106 SET TIME_ZONE= @save_time_zone */ ;

--
-- Dumping routines for database 'sobatgadai'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-09-08 12:24:53
