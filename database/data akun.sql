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
-- Dumping data for table `daftar_akun`
--

LOCK TABLES `daftar_akun` WRITE;
/*!40000 ALTER TABLE `daftar_akun` DISABLE KEYS */;
INSERT INTO `daftar_akun` VALUES ('1011','Kas Kecil','harta','Harta Lancar','debit'),('1012','Kas Bank','harta','Harta Lancar','debit'),('1111','Piutang Pinjaman Gadai','harta','Harta Lancar','debit'),('1112','Piutang Bunga Gadai','harta','Harta Lancar','debit'),('1121','Perlengkapan Kantor','harta','Harta Lancar','debit'),('1122','Persediaan Barang Lelang','harta','Harta Lancar','debit'),('1131','Uang Muka Sewa','harta','Harta Lancar','debit'),('1132','Uang Muka Iklan','harta','Harta Lancar','debit'),('1201','Tanah','harta','Harta Tetap','debit'),('1202','Bangunan','harta','Harta Tetap','debit'),('1203','Kendaraan','harta','Harta Tetap','debit'),('1204','Peralatan Kantor','harta','Harta Tetap','debit'),('1211','Akumulasi Penyusutan Bangunan','harta','Harta Tetap','kredit'),('1212','Akumulasi Penyusutan Kendaraan','harta','Harta Tetap','kredit'),('1213','Akumulasi Penyusutan Peralatan','harta','Harta Tetap','kredit'),('2101','Utang Usaha','utang','Utang Jangka Pendek','kredit'),('2102','Utang Bunga','utang','Utang Jangka Pendek','kredit'),('2103','Utang Pajak','utang','Utang Jangka Pendek','kredit'),('2111','Uang Kelebihan Lelang','utang','Utang Jangka Pendek','kredit'),('2201','Utang Bank','utang','Utang Jangka Panjang','kredit'),('2211','Pinjaman Obligasi','utang','Utang Jangka Panjang','kredit'),('3001','Modal Disetor','modal','Modal Pemilik','kredit'),('3002','Laba Ditahan','modal','Modal Operasional','kredit'),('3003','Cadangan Risiko Kredit','modal','Modal Lainnya','kredit'),('4001','Pendapatan Bunga Gadai','pendapatan','Pendapatan Operasional','kredit'),('4002','Pendapatan Admin','pendapatan','Pendapatan Operasional','kredit'),('4003','Pendapatan Lelang','pendapatan','Pendapatan Operasional','kredit'),('4004','Pendapatan Denda Keterlambatan','pendapatan','Pendapatan Operasional','kredit'),('5001','Beban Gaji','beban','Beban Operasional','debit'),('5002','Beban Listrik & Air','beban','Beban Operasional','debit'),('5003','Beban Operasional Lainnya','beban','Beban Operasional','debit'),('5111','Beban Kerugian Kredit Macet','beban','Beban Kerugian','debit'),('5211','Beban Penyusutan Bangunan','beban','Beban Penyusutan','debit'),('5212','Beban Penyusutan Kendaraan','beban','Beban Penyusutan','debit'),('5213','Beban Penyusutan Peralatan Kantor','beban','Beban Penyusutan','debit');
/*!40000 ALTER TABLE `daftar_akun` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-04-14 12:56:12
