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
-- Table structure for table `daftar_akun`
--
USE sobatgadai;
DROP TABLE IF EXISTS `daftar_akun`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `daftar_akun` (
  `kode_akun` varchar(5) NOT NULL,
  `nama_akun` varchar(100) NOT NULL,
  `jenis` varchar(10) DEFAULT NULL,
  `spesifik` varchar(60) DEFAULT NULL,
  `saldo_normal` varchar(6) DEFAULT NULL,
  PRIMARY KEY (`kode_akun`),
  CONSTRAINT `daftar_akun_chk_1` CHECK (((`saldo_normal` = _utf8mb4'debit') or (`saldo_normal` = _utf8mb4'kredit')))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `log_jurnal`
--

DROP TABLE IF EXISTS `log_jurnal`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `log_jurnal` (
  `no_ref` varchar(5) NOT NULL,
  `tanggal` datetime NOT NULL,
  `keterangan` varchar(100) DEFAULT NULL,
  `kode_akun` varchar(5) DEFAULT NULL,
  `nama_akun` varchar(100) DEFAULT NULL,
  `debit` int DEFAULT NULL,
  `kredit` int DEFAULT NULL
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
/*!50003 CREATE*/ /*!50017 DEFINER=`a122206757`@`%`*/ /*!50003 TRIGGER `after_insert_log_jurnal` AFTER INSERT ON `log_jurnal` FOR EACH ROW BEGIN
    DECLARE saldo_norm VARCHAR(6);

    -- Ambil jenis saldo normal untuk kode_akun dari daftar_akun
    SELECT saldo_normal INTO saldo_norm
    FROM daftar_akun
    WHERE kode_akun = NEW.kode_akun;

    -- Periksa jenis saldo normal dan update saldo yang sesuai
    IF saldo_norm = 'debit' THEN
        -- Jika saldo normal adalah 'debit', tambahkan debit dan set saldo kredit ke 0 jika ada kredit
        UPDATE saldo_akun
        SET debit = debit + COALESCE(NEW.debit, 0) - COALESCE(NEW.kredit, 0),
            kredit = 0
        WHERE kode_akun = NEW.kode_akun;
    ELSEIF saldo_norm = 'kredit' THEN
        -- Jika saldo normal adalah 'kredit', tambahkan kredit dan set saldo debit ke 0 jika ada debit
        UPDATE saldo_akun
        SET kredit = kredit + COALESCE(NEW.kredit, 0) - COALESCE(NEW.debit, 0),
            debit = 0
        WHERE kode_akun = NEW.kode_akun;
    END IF;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `saldo_akun`
--

DROP TABLE IF EXISTS `saldo_akun`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `saldo_akun` (
  `no_sd` int NOT NULL AUTO_INCREMENT,
  `kode_akun` varchar(5) DEFAULT NULL,
  `debit` int DEFAULT '0',
  `kredit` int DEFAULT '0',
  PRIMARY KEY (`no_sd`),
  KEY `fk_kode_akun` (`kode_akun`),
  CONSTRAINT `fk_kode_akun` FOREIGN KEY (`kode_akun`) REFERENCES `daftar_akun` (`kode_akun`)
) ENGINE=InnoDB AUTO_INCREMENT=41 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping events for database 'sobatgadai'
--

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

-- Dump completed on 2026-03-30 12:03:15
