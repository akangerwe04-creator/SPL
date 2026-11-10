-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: spl2026
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Current Database: `spl2026`
--

/*!40000 DROP DATABASE IF EXISTS `spl2026`*/;

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `spl2026` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci */;

USE `spl2026`;

--
-- Table structure for table `agent_deposits`
--

DROP TABLE IF EXISTS `agent_deposits`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `agent_deposits` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `deposit_number` varchar(191) NOT NULL,
  `agent_id` bigint(20) unsigned NOT NULL,
  `total_amount` decimal(14,2) NOT NULL DEFAULT 0.00,
  `status` enum('pending','submitted','approved','rejected') NOT NULL DEFAULT 'pending',
  `agent_notes` text DEFAULT NULL,
  `owner_notes` text DEFAULT NULL,
  `submitted_at` timestamp NULL DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `approved_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `agent_deposits_deposit_number_unique` (`deposit_number`),
  KEY `agent_deposits_approved_by_foreign` (`approved_by`),
  KEY `idx_deposits_agent_status` (`agent_id`,`status`),
  KEY `idx_deposits_status` (`status`),
  CONSTRAINT `agent_deposits_agent_id_foreign` FOREIGN KEY (`agent_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `agent_deposits_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `agent_deposits`
--

LOCK TABLES `agent_deposits` WRITE;
/*!40000 ALTER TABLE `agent_deposits` DISABLE KEYS */;
INSERT INTO `agent_deposits` VALUES (1,'DEP-202611-0001',48,15000.00,'pending',NULL,NULL,NULL,NULL,NULL,'2026-11-10 07:14:29','2026-11-10 08:42:10');
/*!40000 ALTER TABLE `agent_deposits` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache` (
  `key` varchar(191) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache`
--

LOCK TABLES `cache` WRITE;
/*!40000 ALTER TABLE `cache` DISABLE KEYS */;
INSERT INTO `cache` VALUES ('spl_cache_spl_catalog_setting_cached','O:25:\"App\\Models\\CatalogSetting\":30:{s:13:\"\0*\0connection\";s:5:\"mysql\";s:8:\"\0*\0table\";s:16:\"catalog_settings\";s:13:\"\0*\0primaryKey\";s:2:\"id\";s:10:\"\0*\0keyType\";s:3:\"int\";s:12:\"incrementing\";b:1;s:7:\"\0*\0with\";a:0:{}s:12:\"\0*\0withCount\";a:0:{}s:19:\"preventsLazyLoading\";b:0;s:10:\"\0*\0perPage\";i:15;s:6:\"exists\";b:1;s:18:\"wasRecentlyCreated\";b:0;s:28:\"\0*\0escapeWhenCastingToString\";b:0;s:13:\"\0*\0attributes\";a:18:{s:2:\"id\";i:1;s:8:\"app_name\";s:25:\"SPL (Susi Parsel Lebaran)\";s:9:\"logo_path\";s:20:\"images/logo/logo.png\";s:10:\"owner_name\";s:4:\"Susi\";s:13:\"owner_address\";s:10:\"Kp. Cibeet\";s:5:\"title\";s:33:\"KATALOG SPL (SUSI PARSEL LEBARAN)\";s:14:\"program_period\";s:35:\"Periode Musim Lebaran 1447 H / 2026\";s:18:\"program_start_date\";s:10:\"2026-03-25\";s:16:\"program_end_date\";s:10:\"2027-01-24\";s:20:\"terms_and_conditions\";s:538:\"1. Pembayaran iuran setoran wajib dilakukan setiap minggu sesuai nominal yang disepakati.\r\n2. Untuk paket tabungan, bonus barang hangus apabila konsumen tidak setor selama 3 minggu berturut-turut.\r\n3. Apabila konsumen membatalkan pesanan / mundur di tengah jalan, dikenakan biaya administrasi 10% dari total uang yang telah disetor.\r\n4. Penyerahan parsel dan tabungan dilakukan H-7 sebelum Hari Raya Idul Fitri 1447 H.\r\n5. Konsumen yang belum lunas dapat memilih penyesuaian barang (Mini-POS) sesuai dengan saldo setoran yang telah masuk.\";s:14:\"contact_person\";s:33:\"0812-3456-7890 (Susi - Owner SPL)\";s:17:\"bank_account_info\";s:31:\"Bank BCA: 123-456-789 a.n. Susi\";s:14:\"is_book_closed\";i:0;s:14:\"book_closed_at\";N;s:14:\"book_closed_by\";N;s:18:\"book_closing_notes\";N;s:10:\"created_at\";s:19:\"2026-11-07 05:22:42\";s:10:\"updated_at\";s:19:\"2026-11-10 14:42:58\";}s:11:\"\0*\0original\";a:18:{s:2:\"id\";i:1;s:8:\"app_name\";s:25:\"SPL (Susi Parsel Lebaran)\";s:9:\"logo_path\";s:20:\"images/logo/logo.png\";s:10:\"owner_name\";s:4:\"Susi\";s:13:\"owner_address\";s:10:\"Kp. Cibeet\";s:5:\"title\";s:33:\"KATALOG SPL (SUSI PARSEL LEBARAN)\";s:14:\"program_period\";s:35:\"Periode Musim Lebaran 1447 H / 2026\";s:18:\"program_start_date\";s:10:\"2026-03-25\";s:16:\"program_end_date\";s:10:\"2027-01-24\";s:20:\"terms_and_conditions\";s:538:\"1. Pembayaran iuran setoran wajib dilakukan setiap minggu sesuai nominal yang disepakati.\r\n2. Untuk paket tabungan, bonus barang hangus apabila konsumen tidak setor selama 3 minggu berturut-turut.\r\n3. Apabila konsumen membatalkan pesanan / mundur di tengah jalan, dikenakan biaya administrasi 10% dari total uang yang telah disetor.\r\n4. Penyerahan parsel dan tabungan dilakukan H-7 sebelum Hari Raya Idul Fitri 1447 H.\r\n5. Konsumen yang belum lunas dapat memilih penyesuaian barang (Mini-POS) sesuai dengan saldo setoran yang telah masuk.\";s:14:\"contact_person\";s:33:\"0812-3456-7890 (Susi - Owner SPL)\";s:17:\"bank_account_info\";s:31:\"Bank BCA: 123-456-789 a.n. Susi\";s:14:\"is_book_closed\";i:0;s:14:\"book_closed_at\";N;s:14:\"book_closed_by\";N;s:18:\"book_closing_notes\";N;s:10:\"created_at\";s:19:\"2026-11-07 05:22:42\";s:10:\"updated_at\";s:19:\"2026-11-10 14:42:58\";}s:10:\"\0*\0changes\";a:0:{}s:8:\"\0*\0casts\";a:4:{s:18:\"program_start_date\";s:4:\"date\";s:16:\"program_end_date\";s:4:\"date\";s:14:\"is_book_closed\";s:7:\"boolean\";s:14:\"book_closed_at\";s:8:\"datetime\";}s:17:\"\0*\0classCastCache\";a:0:{}s:21:\"\0*\0attributeCastCache\";a:0:{}s:13:\"\0*\0dateFormat\";N;s:10:\"\0*\0appends\";a:0:{}s:19:\"\0*\0dispatchesEvents\";a:0:{}s:14:\"\0*\0observables\";a:0:{}s:12:\"\0*\0relations\";a:0:{}s:10:\"\0*\0touches\";a:0:{}s:10:\"timestamps\";b:1;s:13:\"usesUniqueIds\";b:0;s:9:\"\0*\0hidden\";a:0:{}s:10:\"\0*\0visible\";a:0:{}s:11:\"\0*\0fillable\";a:15:{i:0;s:8:\"app_name\";i:1;s:9:\"logo_path\";i:2;s:10:\"owner_name\";i:3;s:13:\"owner_address\";i:4;s:5:\"title\";i:5;s:14:\"program_period\";i:6;s:18:\"program_start_date\";i:7;s:16:\"program_end_date\";i:8;s:20:\"terms_and_conditions\";i:9;s:14:\"contact_person\";i:10;s:17:\"bank_account_info\";i:11;s:14:\"is_book_closed\";i:12;s:14:\"book_closed_at\";i:13;s:14:\"book_closed_by\";i:14;s:18:\"book_closing_notes\";}s:10:\"\0*\0guarded\";a:1:{i:0;s:1:\"*\";}}',1794330433),('spl_cache_spl_dashboard_closing_stats','a:2:{s:4:\"safe\";i:0;s:7:\"pending\";i:1;}',1794330193),('spl_cache_spl_user_badge_1','a:3:{s:5:\"count\";i:0;s:11:\"feed_unseen\";i:0;s:12:\"has_activity\";b:0;}',1794331496);
/*!40000 ALTER TABLE `cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache_locks` (
  `key` varchar(191) NOT NULL,
  `owner` varchar(191) NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache_locks`
--

LOCK TABLES `cache_locks` WRITE;
/*!40000 ALTER TABLE `cache_locks` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache_locks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `catalog_settings`
--

DROP TABLE IF EXISTS `catalog_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `catalog_settings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `app_name` varchar(191) NOT NULL DEFAULT 'Susi Parsel Lebaran',
  `logo_path` varchar(191) DEFAULT NULL,
  `owner_name` varchar(191) NOT NULL DEFAULT 'Ibu Hj. Susi Susilawati',
  `owner_address` text DEFAULT NULL,
  `title` varchar(191) NOT NULL DEFAULT 'KATALOG SUSI PARSEL & TABUNGAN LEBARAN',
  `program_period` varchar(191) NOT NULL DEFAULT 'Periode Musim Lebaran 2026 / 1447 H (30 Minggu Tagihan)',
  `program_start_date` date DEFAULT NULL,
  `program_end_date` date DEFAULT NULL,
  `terms_and_conditions` text DEFAULT NULL,
  `contact_person` varchar(191) NOT NULL DEFAULT '0812-3456-7890 (Ibu Susi - Owner SPL)',
  `bank_account_info` varchar(191) DEFAULT NULL,
  `is_book_closed` tinyint(1) NOT NULL DEFAULT 0,
  `book_closed_at` timestamp NULL DEFAULT NULL,
  `book_closed_by` bigint(20) unsigned DEFAULT NULL,
  `book_closing_notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `catalog_settings_book_closed_by_foreign` (`book_closed_by`),
  CONSTRAINT `catalog_settings_book_closed_by_foreign` FOREIGN KEY (`book_closed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `catalog_settings`
--

LOCK TABLES `catalog_settings` WRITE;
/*!40000 ALTER TABLE `catalog_settings` DISABLE KEYS */;
INSERT INTO `catalog_settings` VALUES (1,'SPL (Susi Parsel Lebaran)','images/logo/logo.png','Susi','Kp. Cibeet','KATALOG SPL (SUSI PARSEL LEBARAN)','Periode Musim Lebaran 1447 H / 2026','2026-03-25','2027-01-24','1. Pembayaran iuran setoran wajib dilakukan setiap minggu sesuai nominal yang disepakati.\r\n2. Untuk paket tabungan, bonus barang hangus apabila konsumen tidak setor selama 3 minggu berturut-turut.\r\n3. Apabila konsumen membatalkan pesanan / mundur di tengah jalan, dikenakan biaya administrasi 10% dari total uang yang telah disetor.\r\n4. Penyerahan parsel dan tabungan dilakukan H-7 sebelum Hari Raya Idul Fitri 1447 H.\r\n5. Konsumen yang belum lunas dapat memilih penyesuaian barang (Mini-POS) sesuai dengan saldo setoran yang telah masuk.','0812-3456-7890 (Susi - Owner SPL)','Bank BCA: 123-456-789 a.n. Susi',0,NULL,NULL,NULL,'2026-11-06 22:22:42','2026-11-10 07:42:58');
/*!40000 ALTER TABLE `catalog_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `comments`
--

DROP TABLE IF EXISTS `comments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `comments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `post_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `content` text NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `comments_user_id_foreign` (`user_id`),
  KEY `idx_comments_post_created` (`post_id`,`created_at`),
  KEY `idx_comments_created_user` (`created_at`,`user_id`),
  CONSTRAINT `comments_post_id_foreign` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `comments_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `comments`
--

LOCK TABLES `comments` WRITE;
/*!40000 ALTER TABLE `comments` DISABLE KEYS */;
/*!40000 ALTER TABLE `comments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `customer_custom_items`
--

DROP TABLE IF EXISTS `customer_custom_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `customer_custom_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `customer_package_id` bigint(20) unsigned NOT NULL,
  `item_id` bigint(20) unsigned NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `unit` varchar(191) DEFAULT NULL,
  `installment_amount` decimal(14,2) NOT NULL DEFAULT 0.00,
  `unit_price` decimal(14,2) NOT NULL DEFAULT 0.00,
  `subtotal` decimal(14,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `customer_custom_items_customer_package_id_foreign` (`customer_package_id`),
  KEY `customer_custom_items_item_id_foreign` (`item_id`),
  CONSTRAINT `customer_custom_items_customer_package_id_foreign` FOREIGN KEY (`customer_package_id`) REFERENCES `customer_packages` (`id`) ON DELETE CASCADE,
  CONSTRAINT `customer_custom_items_item_id_foreign` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `customer_custom_items`
--

LOCK TABLES `customer_custom_items` WRITE;
/*!40000 ALTER TABLE `customer_custom_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `customer_custom_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `customer_packages`
--

DROP TABLE IF EXISTS `customer_packages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `customer_packages` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `customer_id` bigint(20) unsigned NOT NULL,
  `package_id` bigint(20) unsigned NOT NULL,
  `agent_id` bigint(20) unsigned NOT NULL,
  `start_date` date DEFAULT NULL,
  `total_target` decimal(14,2) NOT NULL DEFAULT 0.00,
  `total_paid` decimal(14,2) NOT NULL DEFAULT 0.00,
  `status` enum('active','completed','canceled') NOT NULL DEFAULT 'active',
  `is_bonus_forfeited` tinyint(1) NOT NULL DEFAULT 0,
  `forfeit_reason` text DEFAULT NULL,
  `consecutive_unpaid_weeks` int(11) NOT NULL DEFAULT 0,
  `cancellation_fee` decimal(14,2) NOT NULL DEFAULT 0.00,
  `refund_amount` decimal(14,2) NOT NULL DEFAULT 0.00,
  `fulfillment_status` enum('pending','full','partial','refund') NOT NULL DEFAULT 'pending',
  `fulfillment_decision` varchar(50) DEFAULT NULL COMMENT 'safe_full, custom_partial, cash_only, pending',
  `decided_at` timestamp NULL DEFAULT NULL,
  `decided_by` bigint(20) unsigned DEFAULT NULL,
  `fulfillment_notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `packaging_id` bigint(20) unsigned DEFAULT NULL,
  `packaging_price` decimal(14,2) NOT NULL DEFAULT 0.00,
  PRIMARY KEY (`id`),
  KEY `customer_packages_packaging_id_foreign` (`packaging_id`),
  KEY `customer_packages_decided_by_foreign` (`decided_by`),
  KEY `idx_cp_agent_status` (`agent_id`,`status`),
  KEY `idx_cp_status_decision` (`status`,`fulfillment_decision`),
  KEY `idx_cp_cust_status` (`customer_id`,`status`),
  KEY `idx_cp_pkg_status` (`package_id`,`status`),
  CONSTRAINT `customer_packages_agent_id_foreign` FOREIGN KEY (`agent_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `customer_packages_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `customer_packages_decided_by_foreign` FOREIGN KEY (`decided_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `customer_packages_package_id_foreign` FOREIGN KEY (`package_id`) REFERENCES `packages` (`id`) ON DELETE CASCADE,
  CONSTRAINT `customer_packages_packaging_id_foreign` FOREIGN KEY (`packaging_id`) REFERENCES `packagings` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `customer_packages`
--

LOCK TABLES `customer_packages` WRITE;
/*!40000 ALTER TABLE `customer_packages` DISABLE KEYS */;
INSERT INTO `customer_packages` VALUES (5,3,5,48,'2025-03-25',305000.00,15000.00,'active',0,NULL,0,0.00,0.00,'pending',NULL,NULL,NULL,NULL,'2026-11-10 07:14:20','2026-11-10 08:42:10',1,0.00);
/*!40000 ALTER TABLE `customer_packages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `customers`
--

DROP TABLE IF EXISTS `customers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `customers` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `agent_id` bigint(20) unsigned NOT NULL,
  `code` varchar(191) DEFAULT NULL,
  `name` varchar(191) NOT NULL,
  `phone` varchar(191) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_cust_agent_active` (`agent_id`,`is_active`),
  KEY `idx_cust_name` (`name`),
  CONSTRAINT `customers_agent_id_foreign` FOREIGN KEY (`agent_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `customers`
--

LOCK TABLES `customers` WRITE;
/*!40000 ALTER TABLE `customers` DISABLE KEYS */;
INSERT INTO `customers` VALUES (3,48,'KNS-2611-001','Aa Nizam',NULL,'Cibeet',NULL,1,'2026-11-10 07:14:20','2026-11-10 07:14:20');
/*!40000 ALTER TABLE `customers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `failed_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(191) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `failed_jobs`
--

LOCK TABLES `failed_jobs` WRITE;
/*!40000 ALTER TABLE `failed_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `failed_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `items`
--

DROP TABLE IF EXISTS `items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(191) DEFAULT NULL,
  `name` varchar(191) NOT NULL,
  `unit_price` decimal(14,2) NOT NULL DEFAULT 0.00,
  `base_unit` varchar(191) NOT NULL DEFAULT 'pcs',
  `wholesale_unit` varchar(191) NOT NULL DEFAULT 'dus',
  `conversion_rate` decimal(10,2) NOT NULL DEFAULT 24.00,
  `cost_price` decimal(14,2) NOT NULL DEFAULT 0.00,
  `category` varchar(191) DEFAULT NULL,
  `image_path` varchar(191) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `items_code_unique` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=147 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `items`
--

LOCK TABLES `items` WRITE;
/*!40000 ALTER TABLE `items` DISABLE KEYS */;
INSERT INTO `items` VALUES (3,'BRG-2611-003','UANG TUNAI Rp. 100.000',100000.00,'amplop','amplop',1.00,100000.00,'Uang',NULL,1,'2026-11-07 01:03:49','2026-11-10 06:30:45'),(4,'BRG-2611-004','POP MIE MINI AYAM',0.00,'pcs','dus',1.00,0.00,'Barang Parsel',NULL,1,'2026-11-07 01:03:49','2026-11-07 01:03:49'),(5,'BRG-2611-005','POP MIE MINI BAKSO',0.00,'pcs','dus',1.00,0.00,'Barang Parsel',NULL,1,'2026-11-07 01:03:49','2026-11-07 01:03:49'),(6,'BRG-2611-006','BENGBENG MAX',0.00,'pcs','dus',1.00,0.00,'Barang Parsel',NULL,1,'2026-11-07 01:03:49','2026-11-07 01:03:49'),(7,'BRG-2611-007','PERMEN KOPIKO',0.00,'pcs','dus',1.00,0.00,'Barang Parsel',NULL,1,'2026-11-07 01:03:49','2026-11-07 01:03:49'),(8,'BRG-2611-008','TEH KOTAK 200ml',0.00,'pcs','dus',1.00,0.00,'Barang Parsel',NULL,1,'2026-11-07 01:03:49','2026-11-07 01:03:49'),(9,'BRG-2611-009','TEH PUCUK',0.00,'pcs','dus',1.00,0.00,'Barang Parsel',NULL,1,'2026-11-07 01:03:49','2026-11-07 01:03:49'),(10,'BRG-2611-010','SUSU UHT 200ml',0.00,'pcs','dus',1.00,0.00,'Barang Parsel',NULL,1,'2026-11-07 01:03:49','2026-11-07 01:03:49'),(11,'BRG-2611-011','CHITATO 24gr',0.00,'pcs','dus',1.00,0.00,'Barang Parsel',NULL,1,'2026-11-07 01:03:49','2026-11-07 01:03:49'),(12,'BRG-2611-012','TARO 32gr',0.00,'pcs','dus',1.00,0.00,'Barang Parsel',NULL,1,'2026-11-07 01:03:49','2026-11-07 01:03:49'),(13,'BRG-2611-013','SMAX RING 40gr',0.00,'pcs','dus',1.00,0.00,'Barang Parsel',NULL,1,'2026-11-07 01:03:49','2026-11-07 01:03:49'),(14,'BRG-2611-014','SERENA GOLD',0.00,'pcs','dus',1.00,0.00,'Barang Parsel',NULL,1,'2026-11-07 01:03:49','2026-11-07 01:03:49'),(15,'BRG-2611-015','TIKTIK 34gr',0.00,'pcs','dus',1.00,0.00,'Barang Parsel',NULL,1,'2026-11-07 01:03:49','2026-11-07 01:03:49'),(16,'BRG-2611-016','NABATI 21gr',0.00,'pcs','dus',1.00,0.00,'Barang Parsel',NULL,1,'2026-11-07 01:03:49','2026-11-07 01:03:49'),(17,'BRG-2611-017','TANGGO',0.00,'pcs','dus',1.00,0.00,'Barang Parsel',NULL,1,'2026-11-07 01:03:49','2026-11-07 01:03:49'),(18,'BRG-2611-018','SLAY OLAY',0.00,'pcs','dus',1.00,0.00,'Barang Parsel',NULL,1,'2026-11-07 01:03:49','2026-11-07 01:03:49'),(19,'BRG-2611-019','JELI MINI',0.00,'pcs','dus',1.00,0.00,'Barang Parsel',NULL,1,'2026-11-07 01:03:49','2026-11-07 01:03:49'),(20,'BRG-2611-020','UHT COKLAT 200ml',0.00,'pcs','dus',1.00,0.00,'Barang Parsel',NULL,1,'2026-11-07 01:03:49','2026-11-07 01:03:49'),(25,'BRG-2611-021','UANG TUNAI Rp. 350.000',350000.00,'amplop','amplop',1.00,350000.00,'Uang',NULL,1,'2026-11-07 01:29:41','2026-11-10 06:30:46'),(26,'BRG-2611-022','POP MIE BESAR AYAM',0.00,'pcs','dus',1.00,0.00,'Barang Parsel',NULL,1,'2026-11-07 01:29:41','2026-11-07 01:29:41'),(27,'BRG-2611-023','POP MIE BESAR BAKSO',0.00,'pcs','dus',1.00,0.00,'Barang Parsel',NULL,1,'2026-11-07 01:29:41','2026-11-07 01:29:41'),(28,'BRG-2611-024','FF 1000ml',0.00,'pcs','dus',1.00,0.00,'Barang Parsel',NULL,1,'2026-11-07 01:29:41','2026-11-07 01:29:41'),(29,'BRG-2611-025','FANTA 390ml',0.00,'pcs','dus',1.00,0.00,'Barang Parsel',NULL,1,'2026-11-07 01:29:41','2026-11-07 01:29:41'),(30,'BRG-2611-026','SILVERQUEEN',0.00,'pcs','dus',1.00,0.00,'Barang Parsel',NULL,1,'2026-11-07 01:29:41','2026-11-07 01:29:41'),(31,'BRG-2611-027','CHITATO 65gr',0.00,'pcs','dus',1.00,0.00,'Barang Parsel',NULL,1,'2026-11-07 01:29:41','2026-11-07 01:29:41'),(32,'BRG-2611-028','Z ZET',0.00,'pcs','dus',1.00,0.00,'Barang Parsel',NULL,1,'2026-11-07 01:29:41','2026-11-07 01:29:41'),(33,'BRG-2611-029','QITELA 55gr',0.00,'pcs','dus',1.00,0.00,'Barang Parsel',NULL,1,'2026-11-07 01:29:41','2026-11-07 01:29:41'),(34,'BRG-2611-030','POXS 125gr',0.00,'pcs','dus',1.00,0.00,'Barang Parsel',NULL,1,'2026-11-07 01:29:41','2026-11-07 01:29:41'),(35,'BRG-2611-031','SERENA 60gr',0.00,'pcs','dus',1.00,0.00,'Barang Parsel',NULL,1,'2026-11-07 01:29:41','2026-11-07 01:29:41'),(36,'BRG-2611-032','TANGGO WAFER',0.00,'pcs','dus',1.00,0.00,'Barang Parsel',NULL,1,'2026-11-07 01:29:41','2026-11-07 01:29:41'),(37,'BRG-2611-033','ROLL ASTOR 65gr',0.00,'pcs','dus',1.00,0.00,'Barang Parsel',NULL,1,'2026-11-07 01:29:41','2026-11-07 01:29:41'),(38,'BRG-2611-034','INACO JELLY',0.00,'pcs','dus',1.00,0.00,'Barang Parsel',NULL,1,'2026-11-07 01:29:41','2026-11-07 01:29:41'),(39,'BRG-2611-035','TH KOTAK 200ml',0.00,'pcs','dus',1.00,0.00,'Barang Parsel',NULL,1,'2026-11-07 01:29:41','2026-11-07 01:29:41'),(40,'BRG-2611-036','UANG  TUNAI Rp. 400.000',400000.00,'amplop','amplop',1.00,400000.00,'Uang',NULL,1,'2026-11-07 12:49:03','2026-11-10 06:30:46'),(41,'BRG-2611-037','FF 1.000',0.00,'pcs','dus',1.00,0.00,'Barang Parsel',NULL,1,'2026-11-07 12:49:03','2026-11-07 12:49:03'),(42,'BRG-2611-038','POP MIE NDOWER',0.00,'pcs','dus',1.00,0.00,'Barang Parsel',NULL,1,'2026-11-07 12:49:03','2026-11-07 12:49:03'),(43,'BRG-2611-039','POP MIE GLEDEK',0.00,'pcs','dus',1.00,0.00,'Barang Parsel',NULL,1,'2026-11-07 12:49:03','2026-11-07 12:49:03'),(44,'BRG-2611-040','SOSRO 1 Ltr',0.00,'pcs','dus',1.00,0.00,'Barang Parsel',NULL,1,'2026-11-07 12:49:03','2026-11-07 12:49:03'),(45,'BRG-2611-041','FANTA 1 Ltr',0.00,'pcs','dus',1.00,0.00,'Barang Parsel',NULL,1,'2026-11-07 12:49:03','2026-11-07 12:49:03'),(46,'BRG-2611-042','SHARE IT ISI 10',0.00,'pcs','dus',1.00,0.00,'Barang Parsel',NULL,1,'2026-11-07 12:49:03','2026-11-07 12:49:03'),(47,'BRG-2611-043','CHITATO 62 gr',0.00,'pcs','dus',1.00,0.00,'Barang Parsel',NULL,1,'2026-11-07 12:49:03','2026-11-07 12:49:03'),(48,'BRG-2611-044','HAPPYTOS',0.00,'pcs','dus',1.00,0.00,'Barang Parsel',NULL,1,'2026-11-07 12:49:03','2026-11-07 12:49:03'),(49,'BRG-2611-045','TARO NET 32 gr',0.00,'pcs','dus',1.00,0.00,'Barang Parsel',NULL,1,'2026-11-07 12:49:03','2026-11-07 12:49:03'),(50,'BRG-2611-046','ROLL TANGGO 5 gr 1 DUS',0.00,'pcs','dus',1.00,0.00,'Barang Parsel',NULL,1,'2026-11-07 12:49:03','2026-11-07 12:49:03'),(51,'BRG-2611-047','YAKULT SATU TOROS',0.00,'pcs','dus',1.00,0.00,'Barang Parsel',NULL,1,'2026-11-07 12:49:03','2026-11-07 12:49:03'),(52,'BRG-2611-048','NADA DE COCO 1 Kg',0.00,'pcs','dus',1.00,0.00,'Barang Parsel',NULL,1,'2026-11-07 12:49:03','2026-11-07 12:49:03'),(53,'BRG-2611-049','MONDE BUTTER COOKIES BESAR 900 gr',0.00,'pcs','dus',1.00,0.00,'Barang Parsel',NULL,1,'2026-11-07 12:54:01','2026-11-07 12:54:01'),(54,'BRG-2611-050','KHONGGUAN PERSEGI 1.500 gr',0.00,'pcs','dus',1.00,0.00,'Barang Parsel',NULL,1,'2026-11-07 12:54:01','2026-11-07 12:54:01'),(55,'BRG-2611-051','SERENA EGG ROLL KECIL 300 gr',0.00,'pcs','dus',1.00,0.00,'Barang Parsel',NULL,1,'2026-11-07 12:54:01','2026-11-07 12:54:01'),(56,'BRG-2611-052','WAFELLO WAFER COKLAT KALENG 234 gr',0.00,'pcs','dus',1.00,0.00,'Barang Parsel',NULL,1,'2026-11-07 12:54:01','2026-11-07 12:54:01'),(57,'BRG-2611-053','ASTOR DOUBLE COKLAT 320 gr',0.00,'pcs','dus',1.00,0.00,'Barang Parsel',NULL,1,'2026-11-07 12:54:01','2026-11-07 12:54:01'),(58,'BRG-2611-054','NEXTAR KALENG',0.00,'pcs','dus',1.00,0.00,'Barang Parsel',NULL,1,'2026-11-07 12:54:01','2026-11-07 12:54:01'),(59,'BRG-2611-055','BENGBENG SHARE IT ISI 25',0.00,'pcs','dus',1.00,0.00,'Barang Parsel',NULL,1,'2026-11-07 12:54:01','2026-11-07 12:54:01'),(60,'BRG-2611-056','POXS KALENG 175 gr',0.00,'pcs','dus',1.00,0.00,'Barang Parsel',NULL,1,'2026-11-07 12:54:01','2026-11-07 12:54:01'),(61,'BRG-2611-057','SIRUP ABC',0.00,'pcs','dus',1.00,0.00,'Barang Parsel',NULL,1,'2026-11-07 12:54:01','2026-11-07 12:54:01'),(62,'BRG-2611-058','TEH PUCUK 1 Ltr',0.00,'pcs','dus',1.00,0.00,'Barang Parsel',NULL,1,'2026-11-07 12:54:01','2026-11-07 12:54:01'),(63,'BRG-2611-059','UANG TUNAI Rp. 500.000',500000.00,'amplop','amplop',1.00,500000.00,'Uang',NULL,1,'2026-11-07 13:08:59','2026-11-10 06:30:46'),(64,'BRG-2611-060','BERAS 10 Kg',0.00,'pcs','dus',1.00,0.00,'Barang Parsel',NULL,1,'2026-11-07 13:08:59','2026-11-07 13:08:59'),(65,'BRG-2611-061','DAGING SAPI 1 Kg',0.00,'pcs','dus',1.00,0.00,'Barang Parsel',NULL,1,'2026-11-07 13:08:59','2026-11-07 13:08:59'),(66,'BRG-2611-062','AYAM 1 Kg',0.00,'pcs','dus',1.00,0.00,'Barang Parsel',NULL,1,'2026-11-07 13:08:59','2026-11-07 13:08:59'),(67,'BRG-2611-063','MINYAK 2 Ltr',0.00,'pcs','dus',1.00,0.00,'Barang Parsel',NULL,1,'2026-11-07 13:08:59','2026-11-07 13:08:59'),(68,'BRG-2611-064','KURMA 1 Ember',0.00,'pcs','dus',1.00,0.00,'Barang Parsel',NULL,1,'2026-11-07 13:08:59','2026-11-07 13:08:59'),(69,'BRG-2611-065','INDOMIE AYAM 1 Dus',0.00,'pcs','dus',1.00,0.00,'Barang Parsel',NULL,1,'2026-11-07 13:08:59','2026-11-07 13:08:59'),(70,'BRG-2611-066','TERIGU 1 Kg',0.00,'pcs','dus',1.00,0.00,'Barang Parsel',NULL,1,'2026-11-07 13:08:59','2026-11-07 13:08:59'),(71,'BRG-2611-067','GULA PUTIH 1 Kg',0.00,'pcs','dus',1.00,0.00,'Barang Parsel',NULL,1,'2026-11-07 13:08:59','2026-11-07 13:08:59'),(72,'BRG-2611-068','KECAP 130 ml',0.00,'pcs','dus',1.00,0.00,'Barang Parsel',NULL,1,'2026-11-07 13:08:59','2026-11-07 13:08:59'),(73,'BRG-2611-069','BUMBU OPOR',0.00,'pcs','dus',1.00,0.00,'Barang Parsel',NULL,1,'2026-11-07 13:08:59','2026-11-07 13:08:59'),(74,'BRG-2611-070','ROYCO 230 gr',0.00,'pcs','dus',1.00,0.00,'Barang Parsel',NULL,1,'2026-11-07 13:08:59','2026-11-07 13:08:59'),(75,'BRG-2611-071','SANTAN CAIR',0.00,'pcs','dus',1.00,0.00,'Barang Parsel',NULL,1,'2026-11-07 13:08:59','2026-11-07 13:08:59'),(76,'BRG-2611-072','SARIWANGI isi 25',0.00,'pcs','dus',1.00,0.00,'Barang Parsel',NULL,1,'2026-11-07 13:08:59','2026-11-07 13:08:59'),(77,'BRG-2611-073','KURUPUK UDANG',0.00,'pcs','dus',1.00,0.00,'Barang Parsel',NULL,1,'2026-11-07 13:08:59','2026-11-07 13:08:59'),(78,'BRG-2611-074','BUMBU RENDANG',0.00,'pcs','dus',1.00,0.00,'Barang Parsel',NULL,1,'2026-11-07 13:08:59','2026-11-07 13:08:59'),(79,'BRG-2611-075','MINYAK 4 Ltr',0.00,'pcs','dus',1.00,0.00,'Barang Parsel',NULL,1,'2026-11-10 07:51:52','2026-11-10 07:51:52'),(80,'BRG-2611-076','MONDE KECIL',0.00,'pcs','dus',1.00,0.00,'Barang Parsel',NULL,1,'2026-11-10 07:51:52','2026-11-10 07:51:52'),(81,'BRG-2611-077','PERMEN FOXS KALENG',0.00,'pcs','dus',1.00,0.00,'Barang Parsel',NULL,1,'2026-11-10 07:51:52','2026-11-10 07:51:52'),(82,'BRG-2611-078','MARJAN',0.00,'pcs','dus',1.00,0.00,'Barang Parsel',NULL,1,'2026-11-10 07:51:52','2026-11-10 07:51:52'),(83,'BRG-2611-079','SUSU KM PUTIH',0.00,'pcs','dus',1.00,0.00,'Barang Parsel',NULL,1,'2026-11-10 07:51:52','2026-11-10 07:51:52'),(84,'BRG-2611-080','SUSU KM COKLAT',0.00,'pcs','dus',1.00,0.00,'Barang Parsel',NULL,1,'2026-11-10 07:51:52','2026-11-10 07:51:52'),(85,'BRG-2611-081','KACANG KUPAS 1 Bks',0.00,'pcs','dus',1.00,0.00,'Barang Parsel',NULL,1,'2026-11-10 07:51:52','2026-11-10 07:51:52'),(86,'UANG-001','UANG TUNAI Rp. 2.000.000',2000000.00,'amplop','amplop',1.00,2000000.00,'Uang',NULL,1,'2026-11-10 07:55:45','2026-11-10 07:55:45'),(87,'BRG-2611-082','MINYAK 5 Ltr',0.00,'pcs','dus',1.00,0.00,'Barang Parsel',NULL,1,'2026-11-10 07:55:45','2026-11-10 07:55:45'),(88,'BRG-2611-083','BERAS 20 Kg',0.00,'pcs','dus',1.00,0.00,'Barang Parsel',NULL,1,'2026-11-10 07:55:45','2026-11-10 07:55:45'),(89,'BRG-2611-084','TERIGU SEGITIGA',0.00,'pcs','dus',1.00,0.00,'Barang Parsel',NULL,1,'2026-11-10 07:55:45','2026-11-10 07:55:45'),(90,'BRG-2611-085','TELUR 10 Butir',0.00,'pcs','dus',1.00,0.00,'Barang Parsel',NULL,1,'2026-11-10 07:58:58','2026-11-10 07:58:58'),(91,'BRG-2611-086','INDOMIE AB 10 Pcs',0.00,'pcs','dus',1.00,0.00,'Barang Parsel',NULL,1,'2026-11-10 07:58:58','2026-11-10 07:58:58'),(92,'BRG-2611-087','INDOMIE GORENG 10 Pcs',0.00,'pcs','dus',1.00,0.00,'Barang Parsel',NULL,1,'2026-11-10 07:58:58','2026-11-10 07:58:58'),(93,'BRG-2611-088','RACIK RENDANG',0.00,'pcs','dus',1.00,0.00,'Barang Parsel',NULL,1,'2026-11-10 07:58:58','2026-11-10 07:58:58'),(94,'BRG-2611-089','RACIK OPOR',0.00,'pcs','dus',1.00,0.00,'Barang Parsel',NULL,1,'2026-11-10 07:58:58','2026-11-10 07:58:58'),(95,'BRG-2611-090','ROYCO 250 Gr',0.00,'pcs','dus',1.00,0.00,'Barang Parsel',NULL,1,'2026-11-10 07:58:58','2026-11-10 07:58:58'),(96,'BRG-2611-091','KERUPUK UDANG 500 Gr',0.00,'pcs','dus',1.00,0.00,'Barang Parsel',NULL,1,'2026-11-10 07:58:58','2026-11-10 07:58:58'),(97,'BRG-2611-092','MINYAK 1 Ltr',0.00,'pcs','dus',1.00,0.00,'Barang Parsel',NULL,1,'2026-11-10 08:01:08','2026-11-10 08:01:08'),(98,'BRG-2611-093','BERAS 2 Kg',0.00,'pcs','dus',1.00,0.00,'Barang Parsel',NULL,1,'2026-11-10 08:01:08','2026-11-10 08:01:08'),(99,'BRG-2611-094','GULA PASIR 1/4',0.00,'pcs','dus',1.00,0.00,'Barang Parsel',NULL,1,'2026-11-10 08:01:08','2026-11-10 08:01:08'),(100,'BRG-2611-095','BERAS 5 Kg',0.00,'pcs','dus',1.00,0.00,'Barang Parsel',NULL,1,'2026-11-10 08:04:15','2026-11-10 08:04:15'),(101,'BRG-2611-096','INDOMIE KUAH 5 Pcs',0.00,'pcs','dus',1.00,0.00,'Barang Parsel',NULL,1,'2026-11-10 08:04:16','2026-11-10 08:04:16'),(102,'BRG-2611-097','INDOMIE GORENG 5 Pcs',0.00,'pcs','dus',1.00,0.00,'Barang Parsel',NULL,1,'2026-11-10 08:04:16','2026-11-10 08:04:16'),(103,'BRG-2611-098','ROYCO AYAM 250 Gr',0.00,'pcs','dus',1.00,0.00,'Barang Parsel',NULL,1,'2026-11-10 08:04:16','2026-11-10 08:04:16'),(104,'BRG-2611-099','INDOMIE KUAH 10 Pcs',0.00,'pcs','dus',1.00,0.00,'Barang Parsel',NULL,1,'2026-11-10 08:06:50','2026-11-10 08:06:50'),(105,'BRG-2611-100','RACIK GULE',0.00,'pcs','dus',1.00,0.00,'Barang Parsel',NULL,1,'2026-11-10 08:06:50','2026-11-10 08:06:50'),(106,'BRG-2611-101','MAGNUM',0.00,'pcs','dus',1.00,0.00,'Barang Parsel',NULL,1,'2026-11-10 08:08:27','2026-11-10 08:08:27'),(107,'BRG-2611-102','SAMPOERNA',0.00,'pcs','dus',1.00,0.00,'Barang Parsel',NULL,1,'2026-11-10 08:08:27','2026-11-10 08:08:27'),(108,'BRG-2611-103','ESSE DOUBLE',0.00,'pcs','dus',1.00,0.00,'Barang Parsel',NULL,1,'2026-11-10 08:08:27','2026-11-10 08:08:27'),(109,'BRG-2611-104','DJARUM SUPER',0.00,'pcs','dus',1.00,0.00,'Barang Parsel',NULL,1,'2026-11-10 08:08:27','2026-11-10 08:08:27'),(110,'BRG-2611-105','BERAS 25 Kg',0.00,'pcs','dus',1.00,0.00,'Barang Parsel',NULL,1,'2026-11-10 08:10:30','2026-11-10 08:10:30'),(111,'BRG-2611-106','MINYAK GORENG 5 Ltr',0.00,'pcs','dus',1.00,0.00,'Barang Parsel',NULL,1,'2026-11-10 08:10:30','2026-11-10 08:10:30'),(112,'BRG-2611-107','MONDE B',0.00,'pcs','dus',1.00,0.00,'Barang Parsel',NULL,1,'2026-11-10 08:10:30','2026-11-10 08:10:30'),(113,'BRG-2611-108','KECAP MANIS',0.00,'pcs','dus',1.00,0.00,'Barang Parsel',NULL,1,'2026-11-10 08:10:30','2026-11-10 08:10:30'),(114,'BRG-2611-109','DAGING SAPI',0.00,'kg','dus',1.00,0.00,'Barang Eceran',NULL,1,'2026-11-10 08:32:50','2026-11-10 08:32:50'),(115,'BRG-2611-110','TULANG IGA',0.00,'kg','dus',1.00,0.00,'Barang Eceran',NULL,1,'2026-11-10 08:32:50','2026-11-10 08:32:50'),(116,'BRG-2611-111','HATI SAPI',0.00,'kg','dus',1.00,0.00,'Barang Eceran',NULL,1,'2026-11-10 08:32:50','2026-11-10 08:32:50'),(117,'BRG-2611-112','KAKI SAPI',0.00,'kg','dus',1.00,0.00,'Barang Eceran',NULL,1,'2026-11-10 08:32:50','2026-11-10 08:32:50'),(118,'BRG-2611-113','PARU',0.00,'kg','dus',1.00,0.00,'Barang Eceran',NULL,1,'2026-11-10 08:32:50','2026-11-10 08:32:50'),(119,'BRG-2611-114','AYAM',0.00,'kg','dus',1.00,0.00,'Barang Eceran',NULL,1,'2026-11-10 08:32:50','2026-11-10 08:32:50'),(120,'BRG-2611-115','TELUR',0.00,'kg','dus',1.00,0.00,'Barang Eceran',NULL,1,'2026-11-10 08:32:50','2026-11-10 08:32:50'),(121,'BRG-2611-116','BERAS',0.00,'kg','dus',1.00,0.00,'Barang Eceran',NULL,1,'2026-11-10 08:32:50','2026-11-10 08:32:50'),(122,'BRG-2611-117','MINYAK GORENG',0.00,'liter','dus',1.00,0.00,'Barang Eceran',NULL,1,'2026-11-10 08:32:50','2026-11-10 08:32:50'),(123,'BRG-2611-118','INDOMIE AB',0.00,'dus','dus',1.00,0.00,'Barang Eceran',NULL,1,'2026-11-10 08:32:50','2026-11-10 08:32:50'),(124,'BRG-2611-119','INDOMIE SOTO',0.00,'dus','dus',1.00,0.00,'Barang Eceran',NULL,1,'2026-11-10 08:32:50','2026-11-10 08:32:50'),(125,'BRG-2611-120','INDOMIE GORENG',0.00,'dus','dus',1.00,0.00,'Barang Eceran',NULL,1,'2026-11-10 08:32:50','2026-11-10 08:32:50'),(126,'BRG-2611-121','POP MIE BESAR',0.00,'dus','dus',1.00,0.00,'Barang Eceran',NULL,1,'2026-11-10 08:32:50','2026-11-10 08:32:50'),(127,'BRG-2611-122','POP MIE KECIL',0.00,'dus','dus',1.00,0.00,'Barang Eceran',NULL,1,'2026-11-10 08:32:50','2026-11-10 08:32:50'),(128,'BRG-2611-123','KHONG GUAN',0.00,'kaleng','dus',1.00,0.00,'Barang Eceran',NULL,1,'2026-11-10 08:32:50','2026-11-10 08:32:50'),(129,'BRG-2611-124','SERENA EGG ROLL BESAR',0.00,'kaleng','dus',1.00,0.00,'Barang Eceran',NULL,1,'2026-11-10 08:32:50','2026-11-10 08:32:50'),(130,'BRG-2611-125','SERENA EGG ROLL KECIL',0.00,'kaleng','dus',1.00,0.00,'Barang Eceran',NULL,1,'2026-11-10 08:32:50','2026-11-10 08:32:50'),(131,'BRG-2611-126','KERUPUK UDANG',0.00,'kg','dus',1.00,0.00,'Barang Eceran',NULL,1,'2026-11-10 08:32:50','2026-11-10 08:32:50'),(132,'BRG-2611-127','EMPING',0.00,'kg','dus',1.00,0.00,'Barang Eceran',NULL,1,'2026-11-10 08:32:50','2026-11-10 08:32:50'),(133,'BRG-2611-128','KECAP BOTOL 135 ml',0.00,'botol','dus',1.00,0.00,'Barang Eceran',NULL,1,'2026-11-10 08:32:50','2026-11-10 08:32:50'),(134,'BRG-2611-129','TERIGU',0.00,'kg','dus',1.00,0.00,'Barang Eceran',NULL,1,'2026-11-10 08:32:50','2026-11-10 08:32:50'),(135,'BRG-2611-130','GULA PUTIH',0.00,'kg','dus',1.00,0.00,'Barang Eceran',NULL,1,'2026-11-10 08:32:50','2026-11-10 08:32:50'),(136,'BRG-2611-131','SYIRUP ABC',0.00,'botol','dus',1.00,0.00,'Barang Eceran',NULL,1,'2026-11-10 08:32:50','2026-11-10 08:32:50'),(137,'BRG-2611-132','WAFELLO',0.00,'kaleng','dus',1.00,0.00,'Barang Eceran',NULL,1,'2026-11-10 08:32:50','2026-11-10 08:32:50'),(138,'BRG-2611-133','FANTA/SPRIT/COLA',0.00,'botol','dus',1.00,0.00,'Barang Eceran',NULL,1,'2026-11-10 08:32:50','2026-11-10 08:32:50'),(139,'BRG-2611-134','BENGBENG SHARE IT',0.00,'dus','dus',1.00,0.00,'Barang Eceran',NULL,1,'2026-11-10 08:32:50','2026-11-10 08:32:50'),(140,'BRG-2611-135','KURMA',0.00,'kg','dus',1.00,0.00,'Barang Eceran',NULL,1,'2026-11-10 08:32:50','2026-11-10 08:32:50'),(141,'BRG-2611-136','DJARUM COKLAT',0.00,'slop','dus',1.00,0.00,'Barang Eceran',NULL,1,'2026-11-10 08:32:50','2026-11-10 08:32:50'),(142,'BRG-2611-137','TEH KOTAK ISI 24',0.00,'dus','dus',1.00,0.00,'Barang Eceran',NULL,1,'2026-11-10 08:32:50','2026-11-10 08:32:50'),(143,'BRG-2611-138','WAFER KACANG',0.00,'blek','dus',1.00,0.00,'Barang Eceran',NULL,1,'2026-11-10 08:32:50','2026-11-10 08:32:50'),(144,'BRG-2611-139','WAFER ALADIN',0.00,'blek','dus',1.00,0.00,'Barang Eceran',NULL,1,'2026-11-10 08:32:50','2026-11-10 08:32:50'),(145,'BRG-2611-140','LUAK KOPI',0.00,'renceng','dus',1.00,0.00,'Barang Eceran',NULL,1,'2026-11-10 08:32:50','2026-11-10 08:32:50'),(146,'BRG-2611-141','KAPAL API',0.00,'renceng','dus',1.00,0.00,'Barang Eceran',NULL,1,'2026-11-10 08:32:50','2026-11-10 08:32:50');
/*!40000 ALTER TABLE `items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `job_batches`
--

DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `job_batches` (
  `id` varchar(191) NOT NULL,
  `name` varchar(191) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `job_batches`
--

LOCK TABLES `job_batches` WRITE;
/*!40000 ALTER TABLE `job_batches` DISABLE KEYS */;
/*!40000 ALTER TABLE `job_batches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jobs`
--

DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(191) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) unsigned NOT NULL,
  `reserved_at` int(10) unsigned DEFAULT NULL,
  `available_at` int(10) unsigned NOT NULL,
  `created_at` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jobs`
--

LOCK TABLES `jobs` WRITE;
/*!40000 ALTER TABLE `jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(191) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'0001_01_01_000000_create_users_table',1),(2,'0001_01_01_000001_create_cache_table',1),(3,'0001_01_01_000002_create_jobs_table',1),(4,'2026_11_04_000001_create_spl_core_tables',1),(5,'2026_11_04_000002_add_profile_fields_to_catalog_settings_table',1),(6,'2026_11_04_000003_add_admin_fee_and_settlement_to_partial_fulfillments',1),(7,'2026_11_05_000001_add_admin_fee_to_packages_table',1),(8,'2026_11_05_000002_add_address_to_users_table',1),(9,'2026_11_05_000003_add_dates_to_packages_and_catalog_settings',1),(10,'2026_11_05_000004_add_base_price_to_packages_table',1),(11,'2026_11_06_000001_create_packagings_and_relations_tables',1),(12,'2026_11_06_000002_create_post_likes_and_feed_activity_tracking',1),(13,'2026_11_06_000003_add_installment_amount_and_unit_to_package_and_custom_items',1),(14,'2026_11_07_000001_add_plain_password_to_users_table',2),(15,'2026_11_07_000002_add_safe_threshold_and_book_closing',3),(16,'2026_11_10_000001_add_performance_indexes_for_high_concurrency',4);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `package_items`
--

DROP TABLE IF EXISTS `package_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `package_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `package_id` bigint(20) unsigned NOT NULL,
  `item_id` bigint(20) unsigned NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `unit` varchar(191) DEFAULT NULL,
  `installment_amount` decimal(14,2) NOT NULL DEFAULT 0.00,
  `is_bonus` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `package_items_package_id_foreign` (`package_id`),
  KEY `package_items_item_id_foreign` (`item_id`),
  CONSTRAINT `package_items_item_id_foreign` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`) ON DELETE CASCADE,
  CONSTRAINT `package_items_package_id_foreign` FOREIGN KEY (`package_id`) REFERENCES `packages` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=233 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `package_items`
--

LOCK TABLES `package_items` WRITE;
/*!40000 ALTER TABLE `package_items` DISABLE KEYS */;
INSERT INTO `package_items` VALUES (5,5,3,1,'pcs',0.00,0,'2026-11-07 01:03:49','2026-11-07 01:03:49'),(6,5,4,1,'pcs',0.00,0,'2026-11-07 01:03:49','2026-11-07 01:03:49'),(7,5,5,1,'pcs',0.00,0,'2026-11-07 01:03:49','2026-11-07 01:03:49'),(8,5,6,1,'pcs',0.00,0,'2026-11-07 01:03:49','2026-11-07 01:03:49'),(9,5,7,1,'pcs',0.00,0,'2026-11-07 01:03:49','2026-11-07 01:03:49'),(10,5,8,1,'pcs',0.00,0,'2026-11-07 01:03:49','2026-11-07 01:03:49'),(11,5,9,1,'pcs',0.00,0,'2026-11-07 01:03:49','2026-11-07 01:03:49'),(12,5,10,1,'pcs',0.00,0,'2026-11-07 01:03:49','2026-11-07 01:03:49'),(13,5,11,1,'pcs',0.00,0,'2026-11-07 01:03:49','2026-11-07 01:03:49'),(14,5,12,1,'pcs',0.00,0,'2026-11-07 01:03:49','2026-11-07 01:03:49'),(15,5,13,1,'pcs',0.00,0,'2026-11-07 01:03:49','2026-11-07 01:03:49'),(16,5,14,1,'pcs',0.00,0,'2026-11-07 01:03:49','2026-11-07 01:03:49'),(17,5,15,1,'pcs',0.00,0,'2026-11-07 01:03:49','2026-11-07 01:03:49'),(18,5,16,1,'pcs',0.00,0,'2026-11-07 01:03:49','2026-11-07 01:03:49'),(19,5,17,1,'pcs',0.00,0,'2026-11-07 01:03:49','2026-11-07 01:03:49'),(20,5,18,1,'pcs',0.00,0,'2026-11-07 01:03:49','2026-11-07 01:03:49'),(21,5,19,1,'pcs',0.00,0,'2026-11-07 01:03:49','2026-11-07 01:03:49'),(22,5,20,1,'pcs',0.00,0,'2026-11-07 01:03:49','2026-11-07 01:03:49'),(23,10,25,1,'pcs',0.00,0,'2026-11-07 01:29:41','2026-11-07 01:29:41'),(24,10,26,1,'pcs',0.00,0,'2026-11-07 01:29:41','2026-11-07 01:29:41'),(25,10,27,1,'pcs',0.00,0,'2026-11-07 01:29:41','2026-11-07 01:29:41'),(26,10,28,1,'pcs',0.00,0,'2026-11-07 01:29:41','2026-11-07 01:29:41'),(27,10,29,1,'pcs',0.00,0,'2026-11-07 01:29:41','2026-11-07 01:29:41'),(28,10,9,1,'pcs',0.00,0,'2026-11-07 01:29:41','2026-11-07 01:29:41'),(29,10,30,1,'pcs',0.00,0,'2026-11-07 01:29:41','2026-11-07 01:29:41'),(30,10,31,1,'pcs',0.00,0,'2026-11-07 01:29:41','2026-11-07 01:29:41'),(31,10,32,1,'pcs',0.00,0,'2026-11-07 01:29:41','2026-11-07 01:29:41'),(32,10,33,1,'pcs',0.00,0,'2026-11-07 01:29:41','2026-11-07 01:29:41'),(33,10,34,1,'pcs',0.00,0,'2026-11-07 01:29:41','2026-11-07 01:29:41'),(34,10,35,1,'pcs',0.00,0,'2026-11-07 01:29:41','2026-11-07 01:29:41'),(35,10,36,1,'pcs',0.00,0,'2026-11-07 01:29:41','2026-11-07 01:29:41'),(36,10,18,1,'pcs',0.00,0,'2026-11-07 01:29:41','2026-11-07 01:29:41'),(37,10,37,1,'pcs',0.00,0,'2026-11-07 01:29:41','2026-11-07 01:29:41'),(38,10,38,1,'pcs',0.00,0,'2026-11-07 01:29:41','2026-11-07 01:29:41'),(39,10,39,1,'pcs',0.00,0,'2026-11-07 01:29:41','2026-11-07 01:29:41'),(57,12,40,1,'pcs',0.00,0,'2026-11-07 12:49:03','2026-11-07 12:49:03'),(58,12,41,1,'pcs',0.00,0,'2026-11-07 12:49:03','2026-11-07 12:49:03'),(59,12,42,1,'pcs',0.00,0,'2026-11-07 12:49:03','2026-11-07 12:49:03'),(60,12,43,1,'pcs',0.00,0,'2026-11-07 12:49:03','2026-11-07 12:49:03'),(61,12,44,1,'pcs',0.00,0,'2026-11-07 12:49:03','2026-11-07 12:49:03'),(62,12,45,1,'pcs',0.00,0,'2026-11-07 12:49:03','2026-11-07 12:49:03'),(63,12,30,1,'pcs',0.00,0,'2026-11-07 12:49:03','2026-11-07 12:49:03'),(64,12,46,1,'pcs',0.00,0,'2026-11-07 12:49:03','2026-11-07 12:49:03'),(65,12,47,1,'pcs',0.00,0,'2026-11-07 12:49:03','2026-11-07 12:49:03'),(66,12,48,1,'pcs',0.00,0,'2026-11-07 12:49:03','2026-11-07 12:49:03'),(67,12,49,1,'pcs',0.00,0,'2026-11-07 12:49:03','2026-11-07 12:49:03'),(68,12,35,1,'pcs',0.00,0,'2026-11-07 12:49:03','2026-11-07 12:49:03'),(69,12,18,1,'pcs',0.00,0,'2026-11-07 12:49:03','2026-11-07 12:49:03'),(70,12,50,1,'pcs',0.00,0,'2026-11-07 12:49:03','2026-11-07 12:49:03'),(71,12,51,1,'pcs',0.00,0,'2026-11-07 12:49:03','2026-11-07 12:49:03'),(72,12,52,1,'pcs',0.00,0,'2026-11-07 12:49:03','2026-11-07 12:49:03'),(73,13,40,1,'pcs',0.00,0,'2026-11-07 12:54:01','2026-11-07 12:54:01'),(74,13,53,1,'pcs',0.00,0,'2026-11-07 12:54:01','2026-11-07 12:54:01'),(75,13,54,1,'pcs',0.00,0,'2026-11-07 12:54:01','2026-11-07 12:54:01'),(76,13,55,1,'pcs',0.00,0,'2026-11-07 12:54:01','2026-11-07 12:54:01'),(77,13,56,1,'pcs',0.00,0,'2026-11-07 12:54:01','2026-11-07 12:54:01'),(78,13,57,1,'pcs',0.00,0,'2026-11-07 12:54:01','2026-11-07 12:54:01'),(79,13,58,1,'pcs',0.00,0,'2026-11-07 12:54:01','2026-11-07 12:54:01'),(80,13,59,1,'pcs',0.00,0,'2026-11-07 12:54:01','2026-11-07 12:54:01'),(81,13,60,1,'pcs',0.00,0,'2026-11-07 12:54:01','2026-11-07 12:54:01'),(82,13,61,1,'pcs',0.00,0,'2026-11-07 12:54:01','2026-11-07 12:54:01'),(83,13,45,1,'pcs',0.00,0,'2026-11-07 12:54:01','2026-11-07 12:54:01'),(84,13,62,1,'pcs',0.00,0,'2026-11-07 12:54:01','2026-11-07 12:54:01'),(85,13,52,1,'pcs',0.00,0,'2026-11-07 12:54:01','2026-11-07 12:54:01'),(86,14,63,1,'pcs',0.00,0,'2026-11-07 13:08:59','2026-11-07 13:08:59'),(87,14,64,1,'pcs',0.00,0,'2026-11-07 13:08:59','2026-11-07 13:08:59'),(88,14,65,1,'pcs',0.00,0,'2026-11-07 13:08:59','2026-11-07 13:08:59'),(89,14,66,1,'pcs',0.00,0,'2026-11-07 13:08:59','2026-11-07 13:08:59'),(90,14,67,1,'pcs',0.00,0,'2026-11-07 13:08:59','2026-11-07 13:08:59'),(91,14,55,1,'pcs',0.00,0,'2026-11-07 13:08:59','2026-11-07 13:08:59'),(92,14,68,1,'pcs',0.00,0,'2026-11-07 13:08:59','2026-11-07 13:08:59'),(93,14,69,1,'pcs',0.00,0,'2026-11-07 13:08:59','2026-11-07 13:08:59'),(94,14,70,1,'pcs',0.00,0,'2026-11-07 13:08:59','2026-11-07 13:08:59'),(95,14,71,1,'pcs',0.00,0,'2026-11-07 13:08:59','2026-11-07 13:08:59'),(96,14,72,1,'pcs',0.00,0,'2026-11-07 13:08:59','2026-11-07 13:08:59'),(97,14,73,1,'pcs',0.00,0,'2026-11-07 13:08:59','2026-11-07 13:08:59'),(98,14,74,1,'pcs',0.00,0,'2026-11-07 13:08:59','2026-11-07 13:08:59'),(99,14,75,1,'pcs',0.00,0,'2026-11-07 13:08:59','2026-11-07 13:08:59'),(100,14,76,1,'pcs',0.00,0,'2026-11-07 13:08:59','2026-11-07 13:08:59'),(101,14,45,1,'pcs',0.00,0,'2026-11-07 13:08:59','2026-11-07 13:08:59'),(102,14,77,1,'pcs',0.00,0,'2026-11-07 13:08:59','2026-11-07 13:08:59'),(103,14,78,1,'pcs',0.00,0,'2026-11-07 13:08:59','2026-11-07 13:08:59'),(104,15,63,1,'amplop',0.00,0,'2026-11-10 07:51:52','2026-11-10 07:51:52'),(105,15,64,1,'pcs',0.00,0,'2026-11-10 07:51:52','2026-11-10 07:51:52'),(106,15,79,1,'pcs',0.00,0,'2026-11-10 07:51:52','2026-11-10 07:51:52'),(107,15,80,1,'pcs',0.00,0,'2026-11-10 07:51:52','2026-11-10 07:51:52'),(108,15,55,1,'pcs',0.00,0,'2026-11-10 07:51:52','2026-11-10 07:51:52'),(109,15,81,1,'pcs',0.00,0,'2026-11-10 07:51:52','2026-11-10 07:51:52'),(110,15,45,1,'pcs',0.00,0,'2026-11-10 07:51:52','2026-11-10 07:51:52'),(111,15,82,1,'pcs',0.00,0,'2026-11-10 07:51:52','2026-11-10 07:51:52'),(112,15,70,1,'pcs',0.00,0,'2026-11-10 07:51:52','2026-11-10 07:51:52'),(113,15,71,1,'pcs',0.00,0,'2026-11-10 07:51:52','2026-11-10 07:51:52'),(114,15,83,1,'pcs',0.00,0,'2026-11-10 07:51:52','2026-11-10 07:51:52'),(115,15,84,1,'pcs',0.00,0,'2026-11-10 07:51:52','2026-11-10 07:51:52'),(116,15,85,1,'pcs',0.00,0,'2026-11-10 07:51:52','2026-11-10 07:51:52'),(117,15,76,1,'pcs',0.00,0,'2026-11-10 07:51:52','2026-11-10 07:51:52'),(118,16,86,1,'amplop',0.00,0,'2026-11-10 07:55:45','2026-11-10 07:55:45'),(119,16,87,1,'pcs',0.00,0,'2026-11-10 07:55:45','2026-11-10 07:55:45'),(120,16,88,1,'pcs',0.00,0,'2026-11-10 07:55:45','2026-11-10 07:55:45'),(121,16,58,1,'pcs',0.00,0,'2026-11-10 07:55:45','2026-11-10 07:55:45'),(122,16,56,1,'pcs',0.00,0,'2026-11-10 07:55:45','2026-11-10 07:55:45'),(123,16,69,1,'pcs',0.00,0,'2026-11-10 07:55:45','2026-11-10 07:55:45'),(124,16,55,1,'pcs',0.00,0,'2026-11-10 07:55:45','2026-11-10 07:55:45'),(125,16,9,1,'pcs',0.00,0,'2026-11-10 07:55:45','2026-11-10 07:55:45'),(126,16,71,1,'pcs',0.00,0,'2026-11-10 07:55:45','2026-11-10 07:55:45'),(127,16,89,1,'pcs',0.00,0,'2026-11-10 07:55:45','2026-11-10 07:55:45'),(128,16,85,1,'pcs',0.00,0,'2026-11-10 07:55:45','2026-11-10 07:55:45'),(129,16,76,1,'pcs',0.00,0,'2026-11-10 07:55:45','2026-11-10 07:55:45'),(130,16,82,1,'pcs',0.00,0,'2026-11-10 07:55:45','2026-11-10 07:55:45'),(131,16,74,1,'pcs',0.00,0,'2026-11-10 07:55:45','2026-11-10 07:55:45'),(132,17,3,1,'amplop',0.00,0,'2026-11-10 07:58:58','2026-11-10 07:58:58'),(133,17,64,1,'pcs',0.00,0,'2026-11-10 07:58:58','2026-11-10 07:58:58'),(134,17,79,1,'pcs',0.00,0,'2026-11-10 07:58:58','2026-11-10 07:58:58'),(135,17,66,1,'pcs',0.00,0,'2026-11-10 07:58:58','2026-11-10 07:58:58'),(136,17,90,1,'pcs',0.00,0,'2026-11-10 07:58:58','2026-11-10 07:58:58'),(137,17,91,1,'pcs',0.00,0,'2026-11-10 07:58:58','2026-11-10 07:58:58'),(138,17,92,1,'pcs',0.00,0,'2026-11-10 07:58:58','2026-11-10 07:58:58'),(139,17,93,1,'pcs',0.00,0,'2026-11-10 07:58:58','2026-11-10 07:58:58'),(140,17,94,1,'pcs',0.00,0,'2026-11-10 07:58:58','2026-11-10 07:58:58'),(141,17,95,1,'pcs',0.00,0,'2026-11-10 07:58:58','2026-11-10 07:58:58'),(142,17,75,1,'pcs',0.00,0,'2026-11-10 07:58:58','2026-11-10 07:58:58'),(143,17,96,1,'pcs',0.00,0,'2026-11-10 07:58:58','2026-11-10 07:58:58'),(144,18,66,1,'pcs',0.00,0,'2026-11-10 08:01:08','2026-11-10 08:01:08'),(145,18,97,1,'pcs',0.00,0,'2026-11-10 08:01:08','2026-11-10 08:01:08'),(146,18,98,1,'pcs',0.00,0,'2026-11-10 08:01:08','2026-11-10 08:01:08'),(147,18,94,2,'pcs',0.00,0,'2026-11-10 08:01:08','2026-11-10 08:01:08'),(148,18,75,1,'pcs',0.00,0,'2026-11-10 08:01:08','2026-11-10 08:01:08'),(149,18,99,1,'pcs',0.00,0,'2026-11-10 08:01:08','2026-11-10 08:01:08'),(150,19,100,1,'pcs',0.00,0,'2026-11-10 08:04:15','2026-11-10 08:04:15'),(151,19,97,1,'pcs',0.00,0,'2026-11-10 08:04:15','2026-11-10 08:04:15'),(152,19,66,1,'pcs',0.00,0,'2026-11-10 08:04:15','2026-11-10 08:04:15'),(153,19,90,1,'pcs',0.00,0,'2026-11-10 08:04:15','2026-11-10 08:04:15'),(154,19,101,1,'pcs',0.00,0,'2026-11-10 08:04:16','2026-11-10 08:04:16'),(155,19,102,1,'pcs',0.00,0,'2026-11-10 08:04:16','2026-11-10 08:04:16'),(156,19,73,2,'pcs',0.00,0,'2026-11-10 08:04:16','2026-11-10 08:04:16'),(157,19,103,1,'pcs',0.00,0,'2026-11-10 08:04:16','2026-11-10 08:04:16'),(158,20,64,1,'pcs',0.00,0,'2026-11-10 08:06:50','2026-11-10 08:06:50'),(159,20,79,1,'pcs',0.00,0,'2026-11-10 08:06:50','2026-11-10 08:06:50'),(160,20,65,1,'pcs',0.00,0,'2026-11-10 08:06:50','2026-11-10 08:06:50'),(161,20,66,1,'pcs',0.00,0,'2026-11-10 08:06:50','2026-11-10 08:06:50'),(162,20,104,1,'pcs',0.00,0,'2026-11-10 08:06:50','2026-11-10 08:06:50'),(163,20,92,1,'pcs',0.00,0,'2026-11-10 08:06:50','2026-11-10 08:06:50'),(164,20,93,1,'pcs',0.00,0,'2026-11-10 08:06:50','2026-11-10 08:06:50'),(165,20,105,1,'pcs',0.00,0,'2026-11-10 08:06:50','2026-11-10 08:06:50'),(166,20,95,1,'pcs',0.00,0,'2026-11-10 08:06:50','2026-11-10 08:06:50'),(167,20,75,2,'pcs',0.00,0,'2026-11-10 08:06:50','2026-11-10 08:06:50'),(168,20,96,1,'pcs',0.00,0,'2026-11-10 08:06:50','2026-11-10 08:06:50'),(169,21,106,10,'pcs',0.00,0,'2026-11-10 08:08:27','2026-11-10 08:08:27'),(170,21,107,5,'pcs',0.00,0,'2026-11-10 08:08:27','2026-11-10 08:08:27'),(171,21,108,5,'pcs',0.00,0,'2026-11-10 08:08:27','2026-11-10 08:08:27'),(172,21,109,5,'pcs',0.00,0,'2026-11-10 08:08:27','2026-11-10 08:08:27'),(173,22,65,1,'pcs',0.00,0,'2026-11-10 08:10:30','2026-11-10 08:10:30'),(174,22,66,1,'pcs',0.00,0,'2026-11-10 08:10:30','2026-11-10 08:10:30'),(175,22,90,1,'pcs',0.00,0,'2026-11-10 08:10:30','2026-11-10 08:10:30'),(176,22,110,1,'pcs',0.00,0,'2026-11-10 08:10:30','2026-11-10 08:10:30'),(177,22,111,1,'pcs',0.00,0,'2026-11-10 08:10:30','2026-11-10 08:10:30'),(178,22,93,1,'pcs',0.00,0,'2026-11-10 08:10:30','2026-11-10 08:10:30'),(179,22,105,1,'pcs',0.00,0,'2026-11-10 08:10:30','2026-11-10 08:10:30'),(180,22,55,1,'pcs',0.00,0,'2026-11-10 08:10:30','2026-11-10 08:10:30'),(181,22,112,1,'pcs',0.00,0,'2026-11-10 08:10:30','2026-11-10 08:10:30'),(182,22,113,1,'pcs',0.00,0,'2026-11-10 08:10:30','2026-11-10 08:10:30'),(183,23,114,1,'kg',4000.00,0,'2026-11-10 08:32:50','2026-11-10 08:32:50'),(184,23,115,1,'kg',2500.00,0,'2026-11-10 08:32:50','2026-11-10 08:32:50'),(185,23,116,1,'kg',2700.00,0,'2026-11-10 08:32:50','2026-11-10 08:32:50'),(186,23,117,1,'kg',2700.00,0,'2026-11-10 08:32:50','2026-11-10 08:32:50'),(187,23,118,1,'kg',2500.00,0,'2026-11-10 08:32:50','2026-11-10 08:32:50'),(188,23,119,1,'kg',1600.00,0,'2026-11-10 08:32:50','2026-11-10 08:32:50'),(189,23,120,1,'kg',1100.00,0,'2026-11-10 08:32:50','2026-11-10 08:32:50'),(190,23,121,5,'kg',2500.00,0,'2026-11-10 08:32:50','2026-11-10 08:32:50'),(191,23,121,10,'kg',5000.00,0,'2026-11-10 08:32:50','2026-11-10 08:32:50'),(192,23,121,20,'kg',10000.00,0,'2026-11-10 08:32:50','2026-11-10 08:32:50'),(193,23,121,25,'kg',11700.00,0,'2026-11-10 08:32:50','2026-11-10 08:32:50'),(194,23,122,12,'liter',6000.00,0,'2026-11-10 08:32:50','2026-11-10 08:32:50'),(195,23,122,5,'liter',3500.00,0,'2026-11-10 08:32:50','2026-11-10 08:32:50'),(196,23,122,2,'liter',1500.00,0,'2026-11-10 08:32:50','2026-11-10 08:32:50'),(197,23,122,4,'liter',2750.00,0,'2026-11-10 08:32:50','2026-11-10 08:32:50'),(198,23,123,1,'dus',3000.00,0,'2026-11-10 08:32:50','2026-11-10 08:32:50'),(199,23,124,1,'dus',3000.00,0,'2026-11-10 08:32:50','2026-11-10 08:32:50'),(200,23,125,1,'dus',3500.00,0,'2026-11-10 08:32:50','2026-11-10 08:32:50'),(201,23,126,1,'dus',3000.00,0,'2026-11-10 08:32:50','2026-11-10 08:32:50'),(202,23,127,1,'dus',1500.00,0,'2026-11-10 08:32:50','2026-11-10 08:32:50'),(203,23,42,1,'dus',2600.00,0,'2026-11-10 08:32:50','2026-11-10 08:32:50'),(204,23,112,1,'kaleng',4000.00,0,'2026-11-10 08:32:50','2026-11-10 08:32:50'),(205,23,128,1,'kaleng',3500.00,0,'2026-11-10 08:32:50','2026-11-10 08:32:50'),(206,23,129,1,'kaleng',3200.00,0,'2026-11-10 08:32:50','2026-11-10 08:32:50'),(207,23,130,1,'kaleng',2000.00,0,'2026-11-10 08:32:50','2026-11-10 08:32:50'),(208,23,131,1,'kg',2000.00,0,'2026-11-10 08:32:50','2026-11-10 08:32:50'),(209,23,132,1,'kg',3200.00,0,'2026-11-10 08:32:50','2026-11-10 08:32:50'),(210,23,93,1,'pcs',250.00,0,'2026-11-10 08:32:50','2026-11-10 08:32:50'),(211,23,94,1,'pcs',250.00,0,'2026-11-10 08:32:50','2026-11-10 08:32:50'),(212,23,133,3,'botol',1000.00,0,'2026-11-10 08:32:50','2026-11-10 08:32:50'),(213,23,134,1,'kg',500.00,0,'2026-11-10 08:32:50','2026-11-10 08:32:50'),(214,23,135,2,'kg',1500.00,0,'2026-11-10 08:32:50','2026-11-10 08:32:50'),(215,23,83,2,'kaleng',1000.00,0,'2026-11-10 08:32:50','2026-11-10 08:32:50'),(216,23,84,2,'kaleng',1000.00,0,'2026-11-10 08:32:50','2026-11-10 08:32:50'),(217,23,9,1,'liter',500.00,0,'2026-11-10 08:32:50','2026-11-10 08:32:50'),(218,23,136,1,'botol',500.00,0,'2026-11-10 08:32:50','2026-11-10 08:32:50'),(219,23,137,1,'kaleng',1000.00,0,'2026-11-10 08:32:50','2026-11-10 08:32:50'),(220,23,138,3,'botol',1200.00,0,'2026-11-10 08:32:50','2026-11-10 08:32:50'),(221,23,82,1,'botol',1000.00,0,'2026-11-10 08:32:50','2026-11-10 08:32:50'),(222,23,139,1,'dus',5000.00,0,'2026-11-10 08:32:50','2026-11-10 08:32:50'),(223,23,140,1,'kg',1000.00,0,'2026-11-10 08:32:50','2026-11-10 08:32:50'),(224,23,108,1,'slop',15000.00,0,'2026-11-10 08:32:50','2026-11-10 08:32:50'),(225,23,106,1,'slop',6800.00,0,'2026-11-10 08:32:50','2026-11-10 08:32:50'),(226,23,107,1,'slop',11000.00,0,'2026-11-10 08:32:50','2026-11-10 08:32:50'),(227,23,141,1,'slop',6500.00,0,'2026-11-10 08:32:50','2026-11-10 08:32:50'),(228,23,142,1,'dus',10000.00,0,'2026-11-10 08:32:50','2026-11-10 08:32:50'),(229,23,143,1,'blek',5000.00,0,'2026-11-10 08:32:50','2026-11-10 08:32:50'),(230,23,144,1,'blek',5500.00,0,'2026-11-10 08:32:50','2026-11-10 08:32:50'),(231,23,145,1,'renceng',500.00,0,'2026-11-10 08:32:50','2026-11-10 08:32:50'),(232,23,146,1,'renceng',500.00,0,'2026-11-10 08:32:50','2026-11-10 08:32:50');
/*!40000 ALTER TABLE `package_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `package_packagings`
--

DROP TABLE IF EXISTS `package_packagings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `package_packagings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `package_id` bigint(20) unsigned NOT NULL,
  `packaging_id` bigint(20) unsigned NOT NULL,
  `is_default` tinyint(1) NOT NULL DEFAULT 0,
  `custom_price` decimal(14,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `package_packagings_package_id_foreign` (`package_id`),
  KEY `package_packagings_packaging_id_foreign` (`packaging_id`),
  CONSTRAINT `package_packagings_package_id_foreign` FOREIGN KEY (`package_id`) REFERENCES `packages` (`id`) ON DELETE CASCADE,
  CONSTRAINT `package_packagings_packaging_id_foreign` FOREIGN KEY (`packaging_id`) REFERENCES `packagings` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `package_packagings`
--

LOCK TABLES `package_packagings` WRITE;
/*!40000 ALTER TABLE `package_packagings` DISABLE KEYS */;
INSERT INTO `package_packagings` VALUES (1,5,1,0,NULL,'2026-11-07 01:03:49','2026-11-07 01:03:49'),(2,10,1,0,NULL,'2026-11-07 01:29:41','2026-11-07 01:29:41'),(3,10,7,0,NULL,'2026-11-07 01:29:41','2026-11-07 01:29:41'),(6,12,5,0,NULL,'2026-11-07 12:49:03','2026-11-07 12:49:03'),(7,12,6,0,NULL,'2026-11-07 12:49:03','2026-11-07 12:49:03'),(8,13,4,0,NULL,'2026-11-07 12:54:01','2026-11-07 12:54:01'),(9,14,2,0,NULL,'2026-11-07 13:08:59','2026-11-07 13:08:59'),(10,15,3,0,NULL,'2026-11-10 07:51:52','2026-11-10 07:51:52'),(11,16,8,0,NULL,'2026-11-10 07:55:45','2026-11-10 07:55:45'),(12,17,9,0,NULL,'2026-11-10 07:58:58','2026-11-10 07:58:58'),(13,18,9,0,NULL,'2026-11-10 08:01:09','2026-11-10 08:01:09'),(14,19,9,0,NULL,'2026-11-10 08:04:16','2026-11-10 08:04:16'),(15,20,9,0,NULL,'2026-11-10 08:06:50','2026-11-10 08:06:50'),(16,22,9,0,NULL,'2026-11-10 08:10:30','2026-11-10 08:10:30');
/*!40000 ALTER TABLE `package_packagings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `packages`
--

DROP TABLE IF EXISTS `packages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `packages` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(191) DEFAULT NULL,
  `name` varchar(191) NOT NULL,
  `type` enum('parsel','tabungan','ecer') NOT NULL DEFAULT 'parsel',
  `base_price` decimal(14,2) DEFAULT 0.00,
  `target_nominal` decimal(14,2) NOT NULL DEFAULT 0.00,
  `safe_threshold` decimal(14,2) DEFAULT NULL COMMENT 'Titik Aman Full Parsel SPL (Nominal minimal setoran konsumen untuk jaminan full barang parsel)',
  `installment_count` int(11) NOT NULL DEFAULT 30,
  `installment_amount` decimal(14,2) NOT NULL DEFAULT 0.00,
  `admin_fee` decimal(14,2) NOT NULL DEFAULT 0.00,
  `period_type` varchar(191) NOT NULL DEFAULT 'mingguan',
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `description` text DEFAULT NULL,
  `image_path` varchar(191) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `packages_code_unique` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `packages`
--

LOCK TABLES `packages` WRITE;
/*!40000 ALTER TABLE `packages` DISABLE KEYS */;
INSERT INTO `packages` VALUES (5,'PAR-001','KIDS 1 Rp. 1.000','parsel',305000.00,305000.00,0.00,305,1000.00,0.00,'harian','2026-03-25','2027-01-24',NULL,NULL,1,'2026-11-07 01:03:49','2026-11-07 01:03:49'),(10,'PAR-002','KIDS 2 Rp. 2.000','parsel',610000.00,610000.00,130.00,305,2000.00,0.00,'harian','2026-03-25','2027-01-24',NULL,NULL,1,'2026-11-07 01:29:41','2026-11-10 06:54:22'),(12,'PAR-003','KIDS 3 Rp. 3.000','parsel',915000.00,915000.00,NULL,305,3000.00,0.00,'harian','2026-03-25','2027-01-24',NULL,NULL,1,'2026-11-07 12:49:03','2026-11-07 12:49:03'),(13,'PAR-004','HARMONIS Rp. 4.000','parsel',1220000.00,1220000.00,0.00,305,4000.00,0.00,'harian','2026-03-25','2027-01-24',NULL,NULL,1,'2026-11-07 12:54:01','2026-11-07 12:54:01'),(14,'PAR-005','FAMILY Rp. 5.000','parsel',1525000.00,1525000.00,0.00,305,5000.00,0.00,'harian','2026-03-25','2027-01-24',NULL,NULL,1,'2026-11-07 13:08:59','2026-11-07 13:08:59'),(15,'PAR-006','EKONOMI Rp. 5.500','parsel',16775000.00,16775000.00,0.00,305,55000.00,0.00,'harian','2026-03-25','2027-01-24',NULL,NULL,1,'2026-11-10 07:51:52','2026-11-10 07:51:52'),(16,'PAR-007','IDUL FITRI Rp. 10.000','parsel',3050000.00,3050000.00,0.00,305,10000.00,0.00,'harian','2026-03-25','2027-01-24',NULL,NULL,1,'2026-11-10 07:55:45','2026-11-10 07:55:45'),(17,'PAR-008','HEMAT Rp. 3.000','parsel',915000.00,915000.00,0.00,305,3000.00,0.00,'harian','2026-03-25','2027-01-24',NULL,NULL,1,'2026-11-10 07:58:58','2026-11-10 07:58:58'),(18,'PAR-009','MINGGUAN HEMAT 1 (Rp. 5.000)','parsel',215000.00,215000.00,0.00,43,5000.00,0.00,'mingguan','2026-03-25','2027-01-24',NULL,NULL,1,'2026-11-10 08:01:08','2026-11-10 08:01:08'),(19,'PAR-010','MINGGUAN HEMAT 2 (Rp. 10.000)','parsel',430000.00,430000.00,0.00,43,10000.00,0.00,'mingguan','2026-03-25','2027-01-24',NULL,NULL,1,'2026-11-10 08:04:15','2026-11-10 08:04:15'),(20,'PAR-011','MINGGUAN HEMAT 3 (Rp. 20.000)','parsel',860000.00,860000.00,0.00,43,20000.00,0.00,'mingguan','2026-03-25','2027-01-24',NULL,NULL,1,'2026-11-10 08:06:50','2026-11-10 08:06:50'),(21,'PAR-012','ROKOK Rp. 20.000','parsel',860000.00,860000.00,0.00,43,20000.00,0.00,'mingguan','2026-03-25','2027-01-24',NULL,NULL,1,'2026-11-10 08:08:27','2026-11-10 08:08:27'),(22,'PAR-013','EKONOMIS IBU Rp. 30.000','parsel',1290000.00,1290000.00,0.00,43,30000.00,0.00,'mingguan','2026-03-25','2027-01-24',NULL,NULL,1,'2026-11-10 08:10:30','2026-11-10 08:10:30'),(23,'ECE-001','PAKET ECERAN MINGGUAN','ecer',6842000.00,6842000.00,0.00,40,171050.00,0.00,'mingguan','2026-03-25','2027-01-24',NULL,NULL,1,'2026-11-10 08:32:50','2026-11-10 08:32:50');
/*!40000 ALTER TABLE `packages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `packagings`
--

DROP TABLE IF EXISTS `packagings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `packagings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(191) DEFAULT NULL,
  `name` varchar(191) NOT NULL,
  `cost_price` decimal(14,2) NOT NULL DEFAULT 0.00,
  `price` decimal(14,2) NOT NULL DEFAULT 0.00,
  `image_path` varchar(191) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `packagings_code_unique` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `packagings`
--

LOCK TABLES `packagings` WRITE;
/*!40000 ALTER TABLE `packagings` DISABLE KEYS */;
INSERT INTO `packagings` VALUES (1,'KMS-001','COUNTENER BOX 18 Ltr',0.00,0.00,NULL,NULL,1,'2026-11-06 22:24:26','2026-11-06 22:24:26'),(2,'KMS-002','COUNTENER BOX 25 Ltr',0.00,0.00,NULL,NULL,1,'2026-11-06 22:24:53','2026-11-06 22:24:53'),(3,'KMS-003','COUNTENER BOX 35 Ltr',0.00,0.00,NULL,NULL,1,'2026-11-06 22:25:02','2026-11-06 22:25:02'),(4,'KMS-004','COUNTENER BOX 45 Ltr',0.00,0.00,NULL,NULL,1,'2026-11-06 22:25:09','2026-11-06 22:25:09'),(5,'KMS-005','KOPER 18 INCH BIRU',0.00,0.00,NULL,NULL,1,'2026-11-06 22:25:23','2026-11-06 22:25:23'),(6,'KMS-006','KOPER 18 INCH BIRU PINK',0.00,0.00,NULL,NULL,1,'2026-11-06 22:25:30','2026-11-06 22:25:30'),(7,'KMS-007','TAS',0.00,0.00,NULL,NULL,1,'2026-11-06 22:25:38','2026-11-06 22:25:38'),(8,'KMS-008','KERANJANG LAUNDRY',0.00,0.00,NULL,NULL,1,'2026-11-06 22:26:27','2026-11-06 22:26:27'),(9,'KMS-009','KERANJANG SERBAGUNA',0.00,0.00,NULL,NULL,1,'2026-11-06 22:26:40','2026-11-06 22:26:40');
/*!40000 ALTER TABLE `packagings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `partial_fulfillment_items`
--

DROP TABLE IF EXISTS `partial_fulfillment_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `partial_fulfillment_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `partial_fulfillment_id` bigint(20) unsigned NOT NULL,
  `item_id` bigint(20) unsigned NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `unit_price` decimal(14,2) NOT NULL DEFAULT 0.00,
  `subtotal` decimal(14,2) NOT NULL DEFAULT 0.00,
  `is_bonus` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `partial_fulfillment_items_partial_fulfillment_id_foreign` (`partial_fulfillment_id`),
  KEY `partial_fulfillment_items_item_id_foreign` (`item_id`),
  CONSTRAINT `partial_fulfillment_items_item_id_foreign` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`) ON DELETE CASCADE,
  CONSTRAINT `partial_fulfillment_items_partial_fulfillment_id_foreign` FOREIGN KEY (`partial_fulfillment_id`) REFERENCES `partial_fulfillments` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `partial_fulfillment_items`
--

LOCK TABLES `partial_fulfillment_items` WRITE;
/*!40000 ALTER TABLE `partial_fulfillment_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `partial_fulfillment_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `partial_fulfillments`
--

DROP TABLE IF EXISTS `partial_fulfillments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `partial_fulfillments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `customer_package_id` bigint(20) unsigned NOT NULL,
  `total_paid` decimal(14,2) NOT NULL DEFAULT 0.00,
  `admin_fee` decimal(14,2) NOT NULL DEFAULT 0.00,
  `settlement_type` varchar(50) NOT NULL DEFAULT 'goods',
  `cash_payout` decimal(14,2) NOT NULL DEFAULT 0.00,
  `total_items_value` decimal(14,2) NOT NULL DEFAULT 0.00,
  `remaining_refund` decimal(14,2) NOT NULL DEFAULT 0.00,
  `adjusted_by` bigint(20) unsigned NOT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `partial_fulfillments_customer_package_id_foreign` (`customer_package_id`),
  KEY `partial_fulfillments_adjusted_by_foreign` (`adjusted_by`),
  CONSTRAINT `partial_fulfillments_adjusted_by_foreign` FOREIGN KEY (`adjusted_by`) REFERENCES `users` (`id`),
  CONSTRAINT `partial_fulfillments_customer_package_id_foreign` FOREIGN KEY (`customer_package_id`) REFERENCES `customer_packages` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `partial_fulfillments`
--

LOCK TABLES `partial_fulfillments` WRITE;
/*!40000 ALTER TABLE `partial_fulfillments` DISABLE KEYS */;
/*!40000 ALTER TABLE `partial_fulfillments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(191) NOT NULL,
  `token` varchar(191) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_reset_tokens`
--

LOCK TABLES `password_reset_tokens` WRITE;
/*!40000 ALTER TABLE `password_reset_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_reset_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payments`
--

DROP TABLE IF EXISTS `payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `payments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `customer_package_id` bigint(20) unsigned NOT NULL,
  `agent_deposit_id` bigint(20) unsigned DEFAULT NULL,
  `agent_id` bigint(20) unsigned NOT NULL,
  `period_number` int(11) NOT NULL,
  `amount` decimal(14,2) NOT NULL DEFAULT 0.00,
  `is_paid` tinyint(1) NOT NULL DEFAULT 0,
  `paid_at` timestamp NULL DEFAULT NULL,
  `is_locked` tinyint(1) NOT NULL DEFAULT 0,
  `status` enum('unpaid','pending','verified') NOT NULL DEFAULT 'unpaid',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_payments_pkg_paid` (`customer_package_id`,`is_paid`),
  KEY `idx_payments_agent_paid_lock` (`agent_id`,`is_paid`,`is_locked`),
  KEY `idx_payments_deposit_paid` (`agent_deposit_id`,`is_paid`),
  KEY `idx_payments_status` (`status`),
  CONSTRAINT `payments_agent_deposit_id_foreign` FOREIGN KEY (`agent_deposit_id`) REFERENCES `agent_deposits` (`id`) ON DELETE SET NULL,
  CONSTRAINT `payments_agent_id_foreign` FOREIGN KEY (`agent_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `payments_customer_package_id_foreign` FOREIGN KEY (`customer_package_id`) REFERENCES `customer_packages` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=306 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payments`
--

LOCK TABLES `payments` WRITE;
/*!40000 ALTER TABLE `payments` DISABLE KEYS */;
INSERT INTO `payments` VALUES (1,5,1,48,1,1000.00,1,'2026-11-10 08:42:05',0,'pending','2026-11-10 07:14:20','2026-11-10 08:42:05'),(2,5,1,48,2,1000.00,1,'2026-11-10 08:42:05',0,'pending','2026-11-10 07:14:20','2026-11-10 08:42:05'),(3,5,1,48,3,1000.00,1,'2026-11-10 08:42:05',0,'pending','2026-11-10 07:14:20','2026-11-10 08:42:05'),(4,5,1,48,4,1000.00,1,'2026-11-10 08:42:05',0,'pending','2026-11-10 07:14:20','2026-11-10 08:42:05'),(5,5,1,48,5,1000.00,1,'2026-11-10 08:42:06',0,'pending','2026-11-10 07:14:20','2026-11-10 08:42:06'),(6,5,1,48,6,1000.00,1,'2026-11-10 08:42:07',0,'pending','2026-11-10 07:14:20','2026-11-10 08:42:07'),(7,5,1,48,7,1000.00,1,'2026-11-10 08:42:07',0,'pending','2026-11-10 07:14:20','2026-11-10 08:42:07'),(8,5,1,48,8,1000.00,1,'2026-11-10 08:42:07',0,'pending','2026-11-10 07:14:20','2026-11-10 08:42:07'),(9,5,1,48,9,1000.00,1,'2026-11-10 08:42:07',0,'pending','2026-11-10 07:14:20','2026-11-10 08:42:07'),(10,5,1,48,10,1000.00,1,'2026-11-10 08:42:08',0,'pending','2026-11-10 07:14:20','2026-11-10 08:42:08'),(11,5,1,48,11,1000.00,1,'2026-11-10 08:42:09',0,'pending','2026-11-10 07:14:20','2026-11-10 08:42:09'),(12,5,1,48,12,1000.00,1,'2026-11-10 08:42:09',0,'pending','2026-11-10 07:14:20','2026-11-10 08:42:09'),(13,5,1,48,13,1000.00,1,'2026-11-10 08:42:09',0,'pending','2026-11-10 07:14:20','2026-11-10 08:42:09'),(14,5,1,48,14,1000.00,1,'2026-11-10 08:42:09',0,'pending','2026-11-10 07:14:20','2026-11-10 08:42:09'),(15,5,1,48,15,1000.00,1,'2026-11-10 08:42:10',0,'pending','2026-11-10 07:14:20','2026-11-10 08:42:10'),(16,5,NULL,48,16,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:20','2026-11-10 07:14:20'),(17,5,NULL,48,17,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:20','2026-11-10 07:14:20'),(18,5,NULL,48,18,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:20','2026-11-10 07:14:20'),(19,5,NULL,48,19,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:20','2026-11-10 07:14:20'),(20,5,NULL,48,20,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:20','2026-11-10 07:14:20'),(21,5,NULL,48,21,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:20','2026-11-10 07:14:20'),(22,5,NULL,48,22,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:20','2026-11-10 07:14:20'),(23,5,NULL,48,23,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:20','2026-11-10 07:14:20'),(24,5,NULL,48,24,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:20','2026-11-10 07:14:20'),(25,5,NULL,48,25,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:20','2026-11-10 07:14:20'),(26,5,NULL,48,26,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:20','2026-11-10 07:14:20'),(27,5,NULL,48,27,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:20','2026-11-10 07:14:20'),(28,5,NULL,48,28,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:20','2026-11-10 07:14:20'),(29,5,NULL,48,29,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:20','2026-11-10 07:14:20'),(30,5,NULL,48,30,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:20','2026-11-10 07:14:20'),(31,5,NULL,48,31,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:20','2026-11-10 07:14:20'),(32,5,NULL,48,32,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:20','2026-11-10 07:14:20'),(33,5,NULL,48,33,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:20','2026-11-10 07:14:20'),(34,5,NULL,48,34,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:20','2026-11-10 07:14:20'),(35,5,NULL,48,35,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:20','2026-11-10 07:14:20'),(36,5,NULL,48,36,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:20','2026-11-10 07:14:20'),(37,5,NULL,48,37,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:20','2026-11-10 07:14:20'),(38,5,NULL,48,38,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:20','2026-11-10 07:14:20'),(39,5,NULL,48,39,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:20','2026-11-10 07:14:20'),(40,5,NULL,48,40,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:20','2026-11-10 07:14:20'),(41,5,NULL,48,41,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:20','2026-11-10 07:14:20'),(42,5,NULL,48,42,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:20','2026-11-10 07:14:20'),(43,5,NULL,48,43,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:20','2026-11-10 07:14:20'),(44,5,NULL,48,44,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:20','2026-11-10 07:14:20'),(45,5,NULL,48,45,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:20','2026-11-10 07:14:20'),(46,5,NULL,48,46,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:20','2026-11-10 07:14:20'),(47,5,NULL,48,47,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:20','2026-11-10 07:14:20'),(48,5,NULL,48,48,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:20','2026-11-10 07:14:20'),(49,5,NULL,48,49,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:20','2026-11-10 07:14:20'),(50,5,NULL,48,50,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:20','2026-11-10 07:14:20'),(51,5,NULL,48,51,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:20','2026-11-10 07:14:20'),(52,5,NULL,48,52,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:20','2026-11-10 07:14:20'),(53,5,NULL,48,53,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:20','2026-11-10 07:14:20'),(54,5,NULL,48,54,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:20','2026-11-10 07:14:20'),(55,5,NULL,48,55,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:20','2026-11-10 07:14:20'),(56,5,NULL,48,56,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:20','2026-11-10 07:14:20'),(57,5,NULL,48,57,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:20','2026-11-10 07:14:20'),(58,5,NULL,48,58,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:20','2026-11-10 07:14:20'),(59,5,NULL,48,59,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:20','2026-11-10 07:14:20'),(60,5,NULL,48,60,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:20','2026-11-10 07:14:20'),(61,5,NULL,48,61,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:20','2026-11-10 07:14:20'),(62,5,NULL,48,62,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:20','2026-11-10 07:14:20'),(63,5,NULL,48,63,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:20','2026-11-10 07:14:20'),(64,5,NULL,48,64,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:20','2026-11-10 07:14:20'),(65,5,NULL,48,65,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:20','2026-11-10 07:14:20'),(66,5,NULL,48,66,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:20','2026-11-10 07:14:20'),(67,5,NULL,48,67,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:20','2026-11-10 07:14:20'),(68,5,NULL,48,68,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:20','2026-11-10 07:14:20'),(69,5,NULL,48,69,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:20','2026-11-10 07:14:20'),(70,5,NULL,48,70,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:20','2026-11-10 07:14:20'),(71,5,NULL,48,71,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:20','2026-11-10 07:14:20'),(72,5,NULL,48,72,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:20','2026-11-10 07:14:20'),(73,5,NULL,48,73,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:20','2026-11-10 07:14:20'),(74,5,NULL,48,74,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:20','2026-11-10 07:14:20'),(75,5,NULL,48,75,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:20','2026-11-10 07:14:20'),(76,5,NULL,48,76,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:20','2026-11-10 07:14:20'),(77,5,NULL,48,77,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:20','2026-11-10 07:14:20'),(78,5,NULL,48,78,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:20','2026-11-10 07:14:20'),(79,5,NULL,48,79,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:20','2026-11-10 07:14:20'),(80,5,NULL,48,80,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:20','2026-11-10 07:14:20'),(81,5,NULL,48,81,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:20','2026-11-10 07:14:20'),(82,5,NULL,48,82,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:20','2026-11-10 07:14:20'),(83,5,NULL,48,83,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:20','2026-11-10 07:14:20'),(84,5,NULL,48,84,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:20','2026-11-10 07:14:20'),(85,5,NULL,48,85,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:20','2026-11-10 07:14:20'),(86,5,NULL,48,86,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:20','2026-11-10 07:14:20'),(87,5,NULL,48,87,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:20','2026-11-10 07:14:20'),(88,5,NULL,48,88,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:20','2026-11-10 07:14:20'),(89,5,NULL,48,89,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:20','2026-11-10 07:14:20'),(90,5,NULL,48,90,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:20','2026-11-10 07:14:20'),(91,5,NULL,48,91,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:20','2026-11-10 07:14:20'),(92,5,NULL,48,92,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:20','2026-11-10 07:14:20'),(93,5,NULL,48,93,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:20','2026-11-10 07:14:20'),(94,5,NULL,48,94,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:20','2026-11-10 07:14:20'),(95,5,NULL,48,95,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:20','2026-11-10 07:14:20'),(96,5,NULL,48,96,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:20','2026-11-10 07:14:20'),(97,5,NULL,48,97,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:20','2026-11-10 07:14:20'),(98,5,NULL,48,98,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:20','2026-11-10 07:14:20'),(99,5,NULL,48,99,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:20','2026-11-10 07:14:20'),(100,5,NULL,48,100,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:20','2026-11-10 07:14:20'),(101,5,NULL,48,101,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:20','2026-11-10 07:14:20'),(102,5,NULL,48,102,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:20','2026-11-10 07:14:20'),(103,5,NULL,48,103,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:20','2026-11-10 07:14:20'),(104,5,NULL,48,104,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:20','2026-11-10 07:14:20'),(105,5,NULL,48,105,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:20','2026-11-10 07:14:20'),(106,5,NULL,48,106,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:20','2026-11-10 07:14:20'),(107,5,NULL,48,107,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:20','2026-11-10 07:14:20'),(108,5,NULL,48,108,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:20','2026-11-10 07:14:20'),(109,5,NULL,48,109,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:20','2026-11-10 07:14:20'),(110,5,NULL,48,110,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:20','2026-11-10 07:14:20'),(111,5,NULL,48,111,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:20','2026-11-10 07:14:20'),(112,5,NULL,48,112,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:20','2026-11-10 07:14:20'),(113,5,NULL,48,113,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:20','2026-11-10 07:14:20'),(114,5,NULL,48,114,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:20','2026-11-10 07:14:20'),(115,5,NULL,48,115,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:20','2026-11-10 07:14:20'),(116,5,NULL,48,116,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:20','2026-11-10 07:14:20'),(117,5,NULL,48,117,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:20','2026-11-10 07:14:20'),(118,5,NULL,48,118,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:20','2026-11-10 07:14:20'),(119,5,NULL,48,119,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:20','2026-11-10 07:14:20'),(120,5,NULL,48,120,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:20','2026-11-10 07:14:20'),(121,5,NULL,48,121,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:20','2026-11-10 07:14:20'),(122,5,NULL,48,122,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:20','2026-11-10 07:14:20'),(123,5,NULL,48,123,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:20','2026-11-10 07:14:20'),(124,5,NULL,48,124,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:20','2026-11-10 07:14:20'),(125,5,NULL,48,125,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:20','2026-11-10 07:14:20'),(126,5,NULL,48,126,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:20','2026-11-10 07:14:20'),(127,5,NULL,48,127,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:20','2026-11-10 07:14:20'),(128,5,NULL,48,128,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:20','2026-11-10 07:14:20'),(129,5,NULL,48,129,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:20','2026-11-10 07:14:20'),(130,5,NULL,48,130,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:20','2026-11-10 07:14:20'),(131,5,NULL,48,131,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:20','2026-11-10 07:14:20'),(132,5,NULL,48,132,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:20','2026-11-10 07:14:20'),(133,5,NULL,48,133,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:20','2026-11-10 07:14:20'),(134,5,NULL,48,134,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:20','2026-11-10 07:14:20'),(135,5,NULL,48,135,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:20','2026-11-10 07:14:20'),(136,5,NULL,48,136,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:21','2026-11-10 07:14:21'),(137,5,NULL,48,137,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:21','2026-11-10 07:14:21'),(138,5,NULL,48,138,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:21','2026-11-10 07:14:21'),(139,5,NULL,48,139,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:21','2026-11-10 07:14:21'),(140,5,NULL,48,140,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:21','2026-11-10 07:14:21'),(141,5,NULL,48,141,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:21','2026-11-10 07:14:21'),(142,5,NULL,48,142,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:21','2026-11-10 07:14:21'),(143,5,NULL,48,143,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:21','2026-11-10 07:14:21'),(144,5,NULL,48,144,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:21','2026-11-10 07:14:21'),(145,5,NULL,48,145,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:21','2026-11-10 07:14:21'),(146,5,NULL,48,146,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:21','2026-11-10 07:14:21'),(147,5,NULL,48,147,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:21','2026-11-10 07:14:21'),(148,5,NULL,48,148,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:21','2026-11-10 07:14:21'),(149,5,NULL,48,149,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:21','2026-11-10 07:14:21'),(150,5,NULL,48,150,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:21','2026-11-10 07:14:21'),(151,5,NULL,48,151,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:21','2026-11-10 07:14:21'),(152,5,NULL,48,152,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:21','2026-11-10 07:14:21'),(153,5,NULL,48,153,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:21','2026-11-10 07:14:21'),(154,5,NULL,48,154,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:21','2026-11-10 07:14:21'),(155,5,NULL,48,155,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:21','2026-11-10 07:14:21'),(156,5,NULL,48,156,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:21','2026-11-10 07:14:21'),(157,5,NULL,48,157,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:21','2026-11-10 07:14:21'),(158,5,NULL,48,158,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:21','2026-11-10 07:14:21'),(159,5,NULL,48,159,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:21','2026-11-10 07:14:21'),(160,5,NULL,48,160,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:21','2026-11-10 07:14:21'),(161,5,NULL,48,161,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:21','2026-11-10 07:14:21'),(162,5,NULL,48,162,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:21','2026-11-10 07:14:21'),(163,5,NULL,48,163,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:21','2026-11-10 07:14:21'),(164,5,NULL,48,164,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:21','2026-11-10 07:14:21'),(165,5,NULL,48,165,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:21','2026-11-10 07:14:21'),(166,5,NULL,48,166,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:21','2026-11-10 07:14:21'),(167,5,NULL,48,167,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:21','2026-11-10 07:14:21'),(168,5,NULL,48,168,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:21','2026-11-10 07:14:21'),(169,5,NULL,48,169,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:21','2026-11-10 07:14:21'),(170,5,NULL,48,170,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:21','2026-11-10 07:14:21'),(171,5,NULL,48,171,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:21','2026-11-10 07:14:21'),(172,5,NULL,48,172,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:21','2026-11-10 07:14:21'),(173,5,NULL,48,173,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:21','2026-11-10 07:14:21'),(174,5,NULL,48,174,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:21','2026-11-10 07:14:21'),(175,5,NULL,48,175,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:21','2026-11-10 07:14:21'),(176,5,NULL,48,176,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:21','2026-11-10 07:14:21'),(177,5,NULL,48,177,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:21','2026-11-10 07:14:21'),(178,5,NULL,48,178,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:21','2026-11-10 07:14:21'),(179,5,NULL,48,179,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:21','2026-11-10 07:14:21'),(180,5,NULL,48,180,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:21','2026-11-10 07:14:21'),(181,5,NULL,48,181,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:21','2026-11-10 07:14:21'),(182,5,NULL,48,182,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:21','2026-11-10 07:14:21'),(183,5,NULL,48,183,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:21','2026-11-10 07:14:21'),(184,5,NULL,48,184,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:21','2026-11-10 07:14:21'),(185,5,NULL,48,185,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:21','2026-11-10 07:14:21'),(186,5,NULL,48,186,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:21','2026-11-10 07:14:21'),(187,5,NULL,48,187,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:21','2026-11-10 07:14:21'),(188,5,NULL,48,188,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:21','2026-11-10 07:14:21'),(189,5,NULL,48,189,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:21','2026-11-10 07:14:21'),(190,5,NULL,48,190,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:21','2026-11-10 07:14:21'),(191,5,NULL,48,191,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:21','2026-11-10 07:14:21'),(192,5,NULL,48,192,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:21','2026-11-10 07:14:21'),(193,5,NULL,48,193,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:21','2026-11-10 07:14:21'),(194,5,NULL,48,194,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:21','2026-11-10 07:14:21'),(195,5,NULL,48,195,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:21','2026-11-10 07:14:21'),(196,5,NULL,48,196,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:21','2026-11-10 07:14:21'),(197,5,NULL,48,197,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:21','2026-11-10 07:14:21'),(198,5,NULL,48,198,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:21','2026-11-10 07:14:21'),(199,5,NULL,48,199,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:21','2026-11-10 07:14:21'),(200,5,NULL,48,200,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:21','2026-11-10 07:14:21'),(201,5,NULL,48,201,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:21','2026-11-10 07:14:21'),(202,5,NULL,48,202,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:21','2026-11-10 07:14:21'),(203,5,NULL,48,203,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:21','2026-11-10 07:14:21'),(204,5,NULL,48,204,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:21','2026-11-10 07:14:21'),(205,5,NULL,48,205,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:21','2026-11-10 07:14:21'),(206,5,NULL,48,206,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:21','2026-11-10 07:14:21'),(207,5,NULL,48,207,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:21','2026-11-10 07:14:21'),(208,5,NULL,48,208,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:21','2026-11-10 07:14:21'),(209,5,NULL,48,209,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:21','2026-11-10 07:14:21'),(210,5,NULL,48,210,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:21','2026-11-10 07:14:21'),(211,5,NULL,48,211,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:21','2026-11-10 07:14:21'),(212,5,NULL,48,212,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:21','2026-11-10 07:14:21'),(213,5,NULL,48,213,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:21','2026-11-10 07:14:21'),(214,5,NULL,48,214,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:21','2026-11-10 07:14:21'),(215,5,NULL,48,215,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:21','2026-11-10 07:14:21'),(216,5,NULL,48,216,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:21','2026-11-10 07:14:21'),(217,5,NULL,48,217,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:21','2026-11-10 07:14:21'),(218,5,NULL,48,218,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:21','2026-11-10 07:14:21'),(219,5,NULL,48,219,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:21','2026-11-10 07:14:21'),(220,5,NULL,48,220,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:21','2026-11-10 07:14:21'),(221,5,NULL,48,221,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:21','2026-11-10 07:14:21'),(222,5,NULL,48,222,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:21','2026-11-10 07:14:21'),(223,5,NULL,48,223,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:21','2026-11-10 07:14:21'),(224,5,NULL,48,224,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:21','2026-11-10 07:14:21'),(225,5,NULL,48,225,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:21','2026-11-10 07:14:21'),(226,5,NULL,48,226,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:21','2026-11-10 07:14:21'),(227,5,NULL,48,227,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:21','2026-11-10 07:14:21'),(228,5,NULL,48,228,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:21','2026-11-10 07:14:21'),(229,5,NULL,48,229,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:21','2026-11-10 07:14:21'),(230,5,NULL,48,230,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:21','2026-11-10 07:14:21'),(231,5,NULL,48,231,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:21','2026-11-10 07:14:21'),(232,5,NULL,48,232,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:21','2026-11-10 07:14:21'),(233,5,NULL,48,233,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:21','2026-11-10 07:14:21'),(234,5,NULL,48,234,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:21','2026-11-10 07:14:21'),(235,5,NULL,48,235,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:21','2026-11-10 07:14:21'),(236,5,NULL,48,236,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:21','2026-11-10 07:14:21'),(237,5,NULL,48,237,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:21','2026-11-10 07:14:21'),(238,5,NULL,48,238,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:21','2026-11-10 07:14:21'),(239,5,NULL,48,239,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:21','2026-11-10 07:14:21'),(240,5,NULL,48,240,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:21','2026-11-10 07:14:21'),(241,5,NULL,48,241,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:21','2026-11-10 07:14:21'),(242,5,NULL,48,242,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:21','2026-11-10 07:14:21'),(243,5,NULL,48,243,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:21','2026-11-10 07:14:21'),(244,5,NULL,48,244,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:21','2026-11-10 07:14:21'),(245,5,NULL,48,245,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:21','2026-11-10 07:14:21'),(246,5,NULL,48,246,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:21','2026-11-10 07:14:21'),(247,5,NULL,48,247,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:21','2026-11-10 07:14:21'),(248,5,NULL,48,248,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:21','2026-11-10 07:14:21'),(249,5,NULL,48,249,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:21','2026-11-10 07:14:21'),(250,5,NULL,48,250,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:21','2026-11-10 07:14:21'),(251,5,NULL,48,251,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:21','2026-11-10 07:14:21'),(252,5,NULL,48,252,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:21','2026-11-10 07:14:21'),(253,5,NULL,48,253,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:21','2026-11-10 07:14:21'),(254,5,NULL,48,254,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:21','2026-11-10 07:14:21'),(255,5,NULL,48,255,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:21','2026-11-10 07:14:21'),(256,5,NULL,48,256,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:21','2026-11-10 07:14:21'),(257,5,NULL,48,257,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:21','2026-11-10 07:14:21'),(258,5,NULL,48,258,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:21','2026-11-10 07:14:21'),(259,5,NULL,48,259,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:21','2026-11-10 07:14:21'),(260,5,NULL,48,260,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:21','2026-11-10 07:14:21'),(261,5,NULL,48,261,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:21','2026-11-10 07:14:21'),(262,5,NULL,48,262,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:21','2026-11-10 07:14:21'),(263,5,NULL,48,263,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:21','2026-11-10 07:14:21'),(264,5,NULL,48,264,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:21','2026-11-10 07:14:21'),(265,5,NULL,48,265,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:21','2026-11-10 07:14:21'),(266,5,NULL,48,266,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:21','2026-11-10 07:14:21'),(267,5,NULL,48,267,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:21','2026-11-10 07:14:21'),(268,5,NULL,48,268,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:21','2026-11-10 07:14:21'),(269,5,NULL,48,269,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:21','2026-11-10 07:14:21'),(270,5,NULL,48,270,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:21','2026-11-10 07:14:21'),(271,5,NULL,48,271,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:21','2026-11-10 07:14:21'),(272,5,NULL,48,272,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:21','2026-11-10 07:14:21'),(273,5,NULL,48,273,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:21','2026-11-10 07:14:21'),(274,5,NULL,48,274,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:21','2026-11-10 07:14:21'),(275,5,NULL,48,275,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:21','2026-11-10 07:14:21'),(276,5,NULL,48,276,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:21','2026-11-10 07:14:21'),(277,5,NULL,48,277,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:21','2026-11-10 07:14:21'),(278,5,NULL,48,278,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:21','2026-11-10 07:14:21'),(279,5,NULL,48,279,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:21','2026-11-10 07:14:21'),(280,5,NULL,48,280,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:21','2026-11-10 07:14:21'),(281,5,NULL,48,281,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:21','2026-11-10 07:14:21'),(282,5,NULL,48,282,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:21','2026-11-10 07:14:21'),(283,5,NULL,48,283,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:21','2026-11-10 07:14:21'),(284,5,NULL,48,284,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:21','2026-11-10 07:14:21'),(285,5,NULL,48,285,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:21','2026-11-10 07:14:21'),(286,5,NULL,48,286,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:21','2026-11-10 07:14:21'),(287,5,NULL,48,287,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:21','2026-11-10 07:14:21'),(288,5,NULL,48,288,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:21','2026-11-10 07:14:21'),(289,5,NULL,48,289,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:21','2026-11-10 07:14:21'),(290,5,NULL,48,290,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:21','2026-11-10 07:14:21'),(291,5,NULL,48,291,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:21','2026-11-10 07:14:21'),(292,5,NULL,48,292,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:21','2026-11-10 07:14:21'),(293,5,NULL,48,293,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:21','2026-11-10 07:14:21'),(294,5,NULL,48,294,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:21','2026-11-10 07:14:21'),(295,5,NULL,48,295,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:21','2026-11-10 07:14:21'),(296,5,NULL,48,296,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:21','2026-11-10 07:14:21'),(297,5,NULL,48,297,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:21','2026-11-10 07:14:21'),(298,5,NULL,48,298,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:21','2026-11-10 07:14:21'),(299,5,NULL,48,299,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:21','2026-11-10 07:14:21'),(300,5,NULL,48,300,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:21','2026-11-10 07:14:21'),(301,5,NULL,48,301,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:21','2026-11-10 07:14:21'),(302,5,NULL,48,302,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:21','2026-11-10 07:14:21'),(303,5,NULL,48,303,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:21','2026-11-10 07:14:21'),(304,5,NULL,48,304,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:21','2026-11-10 07:14:21'),(305,5,NULL,48,305,1000.00,0,NULL,0,'unpaid','2026-11-10 07:14:21','2026-11-10 07:14:21');
/*!40000 ALTER TABLE `payments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `post_likes`
--

DROP TABLE IF EXISTS `post_likes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `post_likes` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `post_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `post_likes_post_id_user_id_unique` (`post_id`,`user_id`),
  KEY `post_likes_user_id_foreign` (`user_id`),
  KEY `idx_likes_post_user` (`post_id`,`user_id`),
  KEY `idx_likes_created_user` (`created_at`,`user_id`),
  CONSTRAINT `post_likes_post_id_foreign` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `post_likes_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `post_likes`
--

LOCK TABLES `post_likes` WRITE;
/*!40000 ALTER TABLE `post_likes` DISABLE KEYS */;
/*!40000 ALTER TABLE `post_likes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `posts`
--

DROP TABLE IF EXISTS `posts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `posts` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `caption` text DEFAULT NULL,
  `image_path` varchar(191) NOT NULL,
  `compressed_size_kb` int(11) NOT NULL DEFAULT 0,
  `likes_count` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `posts_user_id_foreign` (`user_id`),
  KEY `idx_posts_created_user` (`created_at`,`user_id`),
  CONSTRAINT `posts_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `posts`
--

LOCK TABLES `posts` WRITE;
/*!40000 ALTER TABLE `posts` DISABLE KEYS */;
/*!40000 ALTER TABLE `posts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sessions` (
  `id` varchar(191) NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sessions`
--

LOCK TABLES `sessions` WRITE;
/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
INSERT INTO `sessions` VALUES ('uxUqJqItoAC5JAftG6jwsyapsmGPjmlxgMEI0ys5',1,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/157.0.0.0 Safari/537.36','YTo0OntzOjY6Il90b2tlbiI7czo0MDoiSFg3RldvdVpXMXNlSlV1SVF1THhuSEFRUExhMHlZOXJiQTVObTBXNyI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJuZXciO2E6MDp7fXM6Mzoib2xkIjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NTc6Imh0dHA6Ly9sb2NhbGhvc3QvU1BML3B1YmxpYy9hcGkvbm90aWZpY2F0aW9ucy9iYWRnZS1jb3VudCI7fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjE7fQ==',1794331466);
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) NOT NULL,
  `username` varchar(191) DEFAULT NULL,
  `email` varchar(191) NOT NULL,
  `phone` varchar(191) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `role` varchar(191) NOT NULL DEFAULT 'agent',
  `avatar` varchar(191) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(191) NOT NULL,
  `plain_password` varchar(191) DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `last_seen_feed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  UNIQUE KEY `users_username_unique` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=78 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'Owner','owner','owner@spl.com','0812-3456-7890',NULL,'owner',NULL,1,NULL,'$2y$12$iaW0D7/q/hLEbI3Z2u8g8.lhyxL3YCgUD8a/pIL.x7UhguovOkVfe','ownerspl',NULL,'2026-11-09 08:30:27','2026-11-06 22:22:42','2026-11-09 08:30:27'),(2,'Administrator','admin','admin@spl.com','0811-0000-9999',NULL,'admin',NULL,1,NULL,'$2y$12$IJC7REzs21.2zKCIN.n8U.IkAarUySdh2cbm/0UgatsLab4E77Mx.','admin123',NULL,NULL,'2026-11-06 22:22:42','2026-11-06 22:22:42'),(3,'AI KOMARIAH','aikomariah','aikomariah@spl.local','081234567890','Test Alamat','agent',NULL,1,NULL,'$2y$12$mMfZcHf4dLnd.0J2BakQBe1pbG2avsO0wmSU6Q/ZDNeCxGH7wmENG','password',NULL,NULL,'2026-11-06 22:29:46','2026-11-06 22:58:40'),(4,'ELIS MAYA','elismaya','elismaya@spl.local','08123456789',NULL,'agent',NULL,1,NULL,'$2y$12$5VBKyxhC0bnJZVmUooYXZeohhOWD4ykLvYvSSJZP9iAn4GmbAsTs6','password',NULL,NULL,'2026-11-06 22:30:14','2026-11-06 22:30:14'),(5,'N YETI','nyeti','nyeti@spl.local','08123456789',NULL,'agent',NULL,1,NULL,'$2y$12$o5L3gqj6BjyAUlAk/uZuYOzUXyWGGF8rFJ7eq8c9dKUHL51kgazVy','password',NULL,NULL,'2026-11-06 22:30:39','2026-11-06 22:30:39'),(6,'ADEL','adel','adel@spl.local','08123456789',NULL,'agent',NULL,1,NULL,'$2y$12$4.0zUHXm11DSBDBdXo3GOuQr/p1rnvQdmeWm3df8pGp/r7lw9CPP6','password',NULL,NULL,'2026-11-06 22:30:59','2026-11-06 22:30:59'),(7,'DIANA','diana','diana@spl.local','08123456789',NULL,'agent',NULL,1,NULL,'$2y$12$r2wRDki9ynp3RVtz28NM8uCa5KLro7RcAJUvb7I/.iWBTa.BE7FZa','password',NULL,NULL,'2026-11-06 22:31:16','2026-11-06 22:31:16'),(8,'SITI ALFIAH','sitialfiah','sitialfiah@spl.local','08123456789',NULL,'agent',NULL,1,NULL,'$2y$12$pI6m6mwiiPIygBYUr8DkZOQiK9WQesnKxN4dKE7Kt2OXfjh5/6pWS','password',NULL,NULL,'2026-11-06 22:31:53','2026-11-06 22:31:53'),(9,'RISMA','risma','risma@spl.local','08123456789',NULL,'agent',NULL,1,NULL,'$2y$12$tPzNJr83v7mFT6/h2wGGBuV7pvu6sd3.1XP1L1zQvqNujvE8Wc8Te','password',NULL,NULL,'2026-11-06 22:32:12','2026-11-06 22:32:12'),(10,'NISSA','nissa','nissa@spl.local','08123456789',NULL,'agent',NULL,1,NULL,'$2y$12$Phg1UkrRjZSQ1AmNwWv1p.HgTx5LcNwQTdsp6pE2.cNPFY62qR3uG','password',NULL,NULL,'2026-11-06 22:32:35','2026-11-06 22:32:35'),(11,'RESTIA','restia','restia@spl.local','08123456789',NULL,'agent',NULL,1,NULL,'$2y$12$y9/Ukn4dz63mw89rogAC0eJzjrpczZqLgbJxMswJKXEiGuWsacfSy','password',NULL,NULL,'2026-11-06 22:32:53','2026-11-06 22:32:53'),(12,'TEH ASIH','tehasih','tehasih@spl.local','08123456789',NULL,'agent',NULL,1,NULL,'$2y$12$NSKHtf2O0fqm/5V9A3CguetAR7ukhIIKCZLqLyhX7Mj2UwT9IB.XS','password',NULL,NULL,'2026-11-06 22:33:19','2026-11-06 22:33:19'),(13,'WINDA NINGSIH','windaningsih','windaningsih@spl.local','08123456789',NULL,'agent',NULL,1,NULL,'$2y$12$m/Te6Cm.zOuNprRisma2XupUv/0Xr.MhWPPmd58cPx4B1snyxIKm6','password',NULL,NULL,'2026-11-06 22:33:46','2026-11-06 22:33:46'),(14,'RISKA PUSPITA','rikapuspita','rikapuspita@spl.local','08123456789',NULL,'agent',NULL,1,NULL,'$2y$12$8ac60FGkZWrVEFQSx45QDOU/J42OC5OE/z7N0pDd9/81HAIMjAXce','password',NULL,NULL,'2026-11-06 22:34:07','2026-11-06 22:34:07'),(15,'HANI HANDAYANI','hanihandayani','hanihandayani@spl.local','08123456789',NULL,'agent',NULL,1,NULL,'$2y$12$DWx75dAMOkW3KKrxcU4iRuDummYhwAePdkTGmEhJB8BikjozZUV76','password',NULL,NULL,'2026-11-06 22:34:30','2026-11-06 22:34:30'),(16,'NIA SULASTRI','niasulastri','niasulastri@spl.local','08123456789',NULL,'agent',NULL,1,NULL,'$2y$12$RycZ6yNBAsfmRMD4Ovuzs.3iUYO1RxxWKfx6aqpo1mqcoRPXCB9pW','password',NULL,NULL,'2026-11-06 22:34:48','2026-11-06 22:34:48'),(17,'SUSILAWATI','susilawati','susilawati@spl.local','08123456789',NULL,'agent',NULL,1,NULL,'$2y$12$KiLt78FRH31QwPrvbjiCnuSPb51m3jJ5zpemMz6EP1myW7t1rNR0y','password',NULL,NULL,'2026-11-06 22:35:15','2026-11-06 22:35:15'),(18,'ASTRI FEBRIANI','astrifebriani','astrifebriani@spl.local','08123456789',NULL,'agent',NULL,1,NULL,'$2y$12$Gz9oqrEJwUzzgtBEIcvyxumHHD84JKaLIm.DJuXY6GRR.wuf/GZFC','password',NULL,NULL,'2026-11-06 22:35:41','2026-11-06 22:35:41'),(19,'DEWI SUKAMANAH','dewisukamanah','dewisukamanah@spl.local','08123456789',NULL,'agent',NULL,1,NULL,'$2y$12$Mr9ixw0AoyH8kbiLqzD7y.O44n6zYXBGce14VXy89BasJKAMzqM3m','password',NULL,NULL,'2026-11-06 22:36:09','2026-11-06 22:36:09'),(20,'NENG SRI JAIT','nengsri','nengsri@spl.local','08123456789',NULL,'agent',NULL,1,NULL,'$2y$12$EGl2LIz3Ryzo3Y8aIa5ADOuDhmFPlBW5SxsHKqjS/Lnp9Z.AbXBMy','password',NULL,NULL,'2026-11-06 22:36:43','2026-11-06 22:57:33'),(21,'RISKA SRI AULIA','riskasriaulia','riskasriaulia@spl.local','08123456789',NULL,'agent',NULL,1,NULL,'$2y$12$SzYr4WuyjhNVnfDzZD0BNeR9ya31ezqEq4J67Dy5dS1hSQK68/p6y','password',NULL,NULL,'2026-11-06 22:39:54','2026-11-06 22:39:54'),(22,'WULAN BONGKOK','wulanbongkok','wulanbongkok@spl.local','08123456789',NULL,'agent',NULL,1,NULL,'$2y$12$BQWBlly1NEjDwRQTAox5UOZJ61mdyA2kRZXQaSNLsIfTT39JG3Bg2','password',NULL,NULL,'2026-11-06 22:40:37','2026-11-06 22:40:37'),(25,'Siti Nurhaliza','sitinur','sitinur@spl.local','0813-9876-5432','Jl. Raya Paseh No. 45','agent',NULL,1,NULL,'$2y$12$nVxxrMKuKfEIkcB/AnnlJuIAwr4rtjdvmHXSGqqOMAGqeZDukPKfi','password123',NULL,NULL,'2026-11-06 23:58:22','2026-11-06 23:58:22'),(27,'TEH DEVI','tehdevi','tehdevi@spl.local','0812-3456-7890',NULL,'agent',NULL,1,NULL,'$2y$12$qedyTCBrQ8.s3UFtFuOHR.1quhDpIbLSBvJ/bqYFBUnmGkcKMzEku','agenspl',NULL,NULL,'2026-11-07 00:17:13','2026-11-07 00:17:13'),(28,'LISNA','lisna','lisna@spl.local','0812-3456-7890',NULL,'agent',NULL,1,NULL,'$2y$12$Zx/Iz2k94PvD5wIj7W4DOOFSl94I0gUNC7NZSAx5mKHsAQGfJphve','agenspl',NULL,NULL,'2026-11-07 00:17:14','2026-11-07 00:17:14'),(29,'RIFFA','riffa','riffa@spl.local','0812-3456-7890',NULL,'agent',NULL,1,NULL,'$2y$12$ORtx2KT9haY/XHmn2VB.0ujS7OVtAlhCJpi8C6Pcn4hE4jHCC/mBi','agenspl',NULL,NULL,'2026-11-07 00:17:14','2026-11-07 00:17:14'),(30,'YANIF','yanif','yanif@spl.local','0812-3456-7890',NULL,'agent',NULL,1,NULL,'$2y$12$LSwJpF2MEN69JvL5M9pKe.360nLnHJvQh2jYuCBJ0YOgytH62gFYC','agenspl',NULL,NULL,'2026-11-07 00:17:14','2026-11-07 00:17:14'),(31,'AGHNI','aghni','aghni@spl.local','0812-3456-7890',NULL,'agent',NULL,1,NULL,'$2y$12$o9nC9rZj/K4BvDTPT6fVzeYUQe05j.8AKufQeIbX2.Xupqyh14XkS','agenspl',NULL,NULL,'2026-11-07 00:17:15','2026-11-07 00:17:15'),(32,'RIRI MARLINA','ririmarlina','ririmarlina@spl.local','0812-3456-7890',NULL,'agent',NULL,1,NULL,'$2y$12$.y1W0zVpHd612Kcud/cOeO848eXS3n9Bki5MWkiIO2eKX6arBF6Gi','agenspl',NULL,NULL,'2026-11-07 00:17:15','2026-11-07 00:17:15'),(33,'YENI CIKAWAO','yenicikawao','yenicikawao@spl.local','0812-3456-7890',NULL,'agent',NULL,1,NULL,'$2y$12$uuXTXktoQndaF0hZLuCccecLOtK0LSp/UmksviwJN0OFPj1Po8elO','agenspl',NULL,NULL,'2026-11-07 00:17:15','2026-11-07 00:17:15'),(34,'RINA NUR','rinanur','rinanur@spl.local','0812-3456-7890',NULL,'agent',NULL,1,NULL,'$2y$12$Mt8XsZ2vh8O..L6At/8IF.mXkraZw5dss9U3/gNHdkC/xXEdZjAZi','agenspl',NULL,NULL,'2026-11-07 00:17:15','2026-11-07 00:17:15'),(35,'TEH DEDE','tehdede','tehdede@spl.local','0812-3456-7890',NULL,'agent',NULL,1,NULL,'$2y$12$Rk3r/sNnQGSsakzNQlPq8udJ2kwfQK2JYgJ8FDlQqrEj1erBH1OO.','agenspl',NULL,NULL,'2026-11-07 00:17:16','2026-11-07 00:17:16'),(36,'SANTI ALIFIA','santialifia','santialifia@spl.local','0812-3456-7890',NULL,'agent',NULL,1,NULL,'$2y$12$OZ6r.8nd.ICU5PkWyyunc.Me0oT1Kikkbf4Yr6vG1EDumBMOmnFn6','agenspl',NULL,NULL,'2026-11-07 00:17:16','2026-11-07 00:17:16'),(37,'ERNA SETIAWATI','ernasetiawati','ernasetiawati@spl.local','0812-3456-7890',NULL,'agent',NULL,1,NULL,'$2y$12$9RHh4TECW4q5vDGB9jHuK.iJtuwIVBbSbpS3xA4W3tC8Ig1r7qf0G','agenspl',NULL,NULL,'2026-11-07 00:17:16','2026-11-07 00:17:16'),(38,'FITRI BALEENDAH','fitribaleendah','fitribaleendah@spl.local','0812-3456-7890',NULL,'agent',NULL,1,NULL,'$2y$12$hZRg/j.UjY./Ms1FtqZ2TuOum2CExxRIoXtN32l5K1jIKSu1i8nR6','agenspl',NULL,NULL,'2026-11-07 00:17:16','2026-11-07 00:17:16'),(39,'RESTI NOVIANTI','restinovianti','restinovianti@spl.local','0812-3456-7890',NULL,'agent',NULL,1,NULL,'$2y$12$i5A36bRXti5CEgHhjGU8ReLI2tC1fg0ByJGxWleQN1dnRSgTq3eL6','agenspl',NULL,NULL,'2026-11-07 00:17:17','2026-11-07 00:17:17'),(40,'EVA ARJASARI','evaarjasari','evaarjasari@spl.local','0812-3456-7890',NULL,'agent',NULL,1,NULL,'$2y$12$8ypIBNxyv5CtKuKtU18iueuTl7sXvm0/r8NAjWxkcOClVCPKlhkxy','agenspl',NULL,NULL,'2026-11-07 00:17:17','2026-11-07 00:17:17'),(41,'BI IIM','biiim','biiim@spl.local','0812-3456-7890',NULL,'agent',NULL,1,NULL,'$2y$12$l0HxWuY047WCfm7wGX7FauGSSYigdYyVyk5T3a5laa32AKLNbHid6','agenspl',NULL,NULL,'2026-11-07 00:17:17','2026-11-07 00:17:17'),(42,'RITA CIPEDES','ritacipedes','ritacipedes@spl.local','0812-3456-7890',NULL,'agent',NULL,1,NULL,'$2y$12$/b9l2h7JVuTGWCZkfr6HtuQ.0ZMfA2GynKulCz5dylXeSICgnGzJy','agenspl',NULL,NULL,'2026-11-07 00:17:17','2026-11-07 00:17:17'),(43,'MUTIA NOVIANTY','mutianovianty','mutianovianty@spl.local','0812-3456-7890',NULL,'agent',NULL,1,NULL,'$2y$12$lhgbu4pbnyQtCJyrsKr4Rutek6EHCPwWh/Hk.IltoMfLkc5TskyDe','agenspl',NULL,NULL,'2026-11-07 00:17:18','2026-11-07 00:17:18'),(44,'RINIH','rinih','rinih@spl.local','0812-3456-7890',NULL,'agent',NULL,1,NULL,'$2y$12$rZEMMNLmatgYvIPczJsUFO4AVrEpj8j.Ruoov5g6RteQ6DlmvfUDe','agenspl',NULL,NULL,'2026-11-07 00:17:18','2026-11-07 00:17:18'),(45,'ANEU NOVIANTY','aneunovianty','aneunovianty@spl.local','0812-3456-7890',NULL,'agent',NULL,1,NULL,'$2y$12$PMSj/mRUczJJTksWYfTtc.p9arsx35KzLW/sLA2xkQD1DyqPT845m','agenspl',NULL,NULL,'2026-11-07 00:17:18','2026-11-07 00:17:18'),(46,'NOVIANTY','novianty','novianty@spl.local','0812-3456-7890',NULL,'agent',NULL,1,NULL,'$2y$12$nAhQbrDLZEqWwatcZ66EVOGcQlFbgbc5/4Wpde/dazOSPyt0Rwkoy','agenspl',NULL,NULL,'2026-11-07 00:17:19','2026-11-07 00:17:19'),(47,'ELIS ANDINI','elisandini','elisandini@spl.local','0812-3456-7890',NULL,'agent',NULL,1,NULL,'$2y$12$LMf6t0IMIUk4mXF6EaWA0eBFXGHpevLcFXZTEkz/Miw7M/CnoVMcK','agenspl',NULL,NULL,'2026-11-07 00:17:19','2026-11-07 00:17:19'),(48,'IRA SINTA','irasinta','irasinta@spl.local','0812-3456-7890',NULL,'agent',NULL,1,NULL,'$2y$12$gvr8EUpz/B1JBRxIgMXV8.sa/huzVIKKBPzqbufEjUufLHvfrlgSW','agenspl','dZZbi7NS9PstaXq3Qu3LNI5oqtrbic3LTKcZPFIRnkfNKkh9mgZ3Momjx33a','2026-11-10 08:43:16','2026-11-07 00:17:19','2026-11-10 08:43:16'),(49,'TEH NUR','tehnur','tehnur@spl.local','0812-3456-7890',NULL,'agent',NULL,1,NULL,'$2y$12$1PmvlO8t3lwfUXGGNpH/f.gNI1jdvA8I3bLam3NrwPomS/lucsoYW','agenspl',NULL,NULL,'2026-11-07 00:17:19','2026-11-07 00:17:19'),(50,'TEH EVI','tehevi','tehevi@spl.local','0812-3456-7890',NULL,'agent',NULL,1,NULL,'$2y$12$5/kAVFMoq.SaBEumNLe0jeXzpU5LEVEbj8jsvr4ch.rnxzNukNnI2','agenspl',NULL,NULL,'2026-11-07 00:17:20','2026-11-07 00:17:20'),(51,'TEH NINING','tehnining','tehnining@spl.local','0812-3456-7890',NULL,'agent',NULL,1,NULL,'$2y$12$ARR7EDiUSQ.f/0YiawoTMu28RDdgav9M/DMhKpmCDdG1rKZuV2A3.','agenspl',NULL,NULL,'2026-11-07 00:17:20','2026-11-07 00:17:20'),(52,'AGHNIA','aghnia','aghnia@spl.local','0812-3456-7890',NULL,'agent',NULL,1,NULL,'$2y$12$9Sxw4TZ4KmzkkBf0Yw5LZuMt5gcgmlSh4egWZM6rDn58WQqvzD8FW','agenspl',NULL,NULL,'2026-11-07 00:17:20','2026-11-07 00:17:20'),(53,'RAFIKA','rafika','rafika@spl.local','0812-3456-7890',NULL,'agent',NULL,1,NULL,'$2y$12$o6rWC7STog4q70fRU.9i7.76abnUvznkwPBL4ZX8GpiFIgPDImXgC','agenspl',NULL,NULL,'2026-11-07 00:17:21','2026-11-07 00:17:21'),(54,'DESI','desi','desi@spl.local','0812-3456-7890',NULL,'agent',NULL,1,NULL,'$2y$12$J9JPCFkUjJ4jW9GvGa2z3.2cJYReRJ.5cjhkuFxYnGaW.JsvSbv/2','agenspl',NULL,NULL,'2026-11-07 00:17:21','2026-11-07 00:17:21'),(55,'TEH FIFIT','tehfifit','tehfifit@spl.local','0812-3456-7890',NULL,'agent',NULL,1,NULL,'$2y$12$5k9rvHR6l5RQjRNcQnoAfulAPLdMlwXDlZFm.e0exMQDmSk7rjsyC','agenspl',NULL,NULL,'2026-11-07 00:17:21','2026-11-07 00:17:21'),(56,'TEH SRI','tehsri','tehsri@spl.local','0812-3456-7890',NULL,'agent',NULL,1,NULL,'$2y$12$3p4J8nzYFrCGqZuxFSM98OcnIGKMongW3bCNE8jciEUdTkx05ALyK','agenspl',NULL,NULL,'2026-11-07 00:17:21','2026-11-07 00:17:21'),(57,'DANAH','danah','danah@spl.local','0812-3456-7890',NULL,'agent',NULL,1,NULL,'$2y$12$wdxjW0nlbXGexpk3G17IJuXKF1/k7kPOdw9oFqlzMmsNwrhhnGlc.','agenspl',NULL,NULL,'2026-11-07 00:17:22','2026-11-07 00:17:22'),(58,'ANISA','anisa','anisa@spl.local','0812-3456-7890',NULL,'agent',NULL,1,NULL,'$2y$12$VNvjg3dWPnx4r9N.2nCLZuf9elDOER10UOqqPQloEPqKwuw26IHXm','agenspl',NULL,NULL,'2026-11-07 00:17:22','2026-11-07 00:17:22'),(60,'DELIA','delia','delia@spl.local','0812-3456-7890',NULL,'agent',NULL,1,NULL,'$2y$12$TkFPIXxruEVEFKgzn7zxBuo6EWAyabtkTzl6J76DOdVUU9kInqU5e','agenspl',NULL,NULL,'2026-11-07 00:17:22','2026-11-07 00:17:22'),(61,'TEH CICIN','tehcicin','tehcicin@spl.local','0812-3456-7890',NULL,'agent',NULL,1,NULL,'$2y$12$lKeaHo7drHsy/6jSDaG7Fuu4kb.NGZha5ySa1SK0blB7jipVWLk6m','agenspl',NULL,NULL,'2026-11-07 00:17:23','2026-11-07 00:17:23'),(62,'PUPUT','puput','puput@spl.local','0812-3456-7890',NULL,'agent',NULL,1,NULL,'$2y$12$UXSSR8Sbi.2/BsWayK8bvevfx26kl9HdBKG6lScMZhhXN33qNMJ0G','agenspl',NULL,NULL,'2026-11-07 00:17:23','2026-11-07 00:17:23'),(63,'DEDE LILIS','dedelilis','dedelilis@spl.local','0812-3456-7890',NULL,'agent',NULL,1,NULL,'$2y$12$TDTYU7XEYXMoXgLSeNgXq.lB0mAHCcFchZ4QzNESM9//vd7rakaOS','agenspl',NULL,NULL,'2026-11-07 00:17:23','2026-11-07 00:17:23'),(64,'DEA','dea','dea@spl.local','0812-3456-7890',NULL,'agent',NULL,1,NULL,'$2y$12$r1/KMbLIg2WkXcwYqUSjFO4N.7Yb./zjvho0P64Jj4hSTBBsYTDJK','agenspl',NULL,NULL,'2026-11-07 00:17:24','2026-11-07 00:17:24'),(65,'PAPAT','papat','papat@spl.local','0812-3456-7890',NULL,'agent',NULL,1,NULL,'$2y$12$Kh0TOxSd4/i9cBkVfZYwJul34YMC/lRxNpZsSwQ27wXllmtsuGqa2','agenspl',NULL,NULL,'2026-11-07 00:17:24','2026-11-07 00:17:24'),(66,'ASTRI AG','astriag','astriag@spl.local','0812-3456-7890',NULL,'agent',NULL,1,NULL,'$2y$12$/ChC/YlfXo0MCs84Q2bKM.8F3ylzi1xnTS7X/msmNhMOjCT5R3gH2','agenspl',NULL,NULL,'2026-11-07 00:17:24','2026-11-07 00:17:24'),(67,'BI AI','biai','biai@spl.local','0812-3456-7890',NULL,'agent',NULL,1,NULL,'$2y$12$0pr30.qoMIcju2DCUJQ12.aqyiUFaUsHbpdoJd42TUvLqmXx4o8ee','agenspl',NULL,NULL,'2026-11-07 00:17:24','2026-11-07 00:17:24'),(68,'NURI PASIR HUNI','nuripasirhuni','nuripasirhuni@spl.local','0812-3456-7890',NULL,'agent',NULL,1,NULL,'$2y$12$1FShxUELOaLHLcuYEJWqg.rQCM.WwSE87D8o6UEXUb2yS3eWYE3Da','agenspl',NULL,NULL,'2026-11-07 00:17:25','2026-11-07 00:17:25'),(69,'MAMAH LAELI','mamahlaeli','mamahlaeli@spl.local','0812-3456-7890',NULL,'agent',NULL,1,NULL,'$2y$12$xjcwmEOXSqni4LucdtluwusnndXHpaY5.Ud.kITq50BtqWlVoyx/C','agenspl',NULL,NULL,'2026-11-07 00:17:25','2026-11-07 00:17:25'),(70,'LIASM','liasm','liasm@spl.local','0812-3456-7890',NULL,'agent',NULL,1,NULL,'$2y$12$l1/6u2nq6Oq2mLnl6LiqiuZS8HljJCTVCtyrgZTbRaTtRVVjalK2q','agenspl',NULL,NULL,'2026-11-07 00:17:25','2026-11-07 00:17:25'),(71,'TEH YULI','tehyuli','tehyuli@spl.local','0812-3456-7890',NULL,'agent',NULL,1,NULL,'$2y$12$Kuwff1PEOu2/oBk8mBDW6ev0mnILmipKOoYtufXJeOqBLjcnFxYqe','agenspl',NULL,NULL,'2026-11-07 00:17:25','2026-11-07 00:17:25'),(72,'BU DINA','budina','budina@spl.local','0812-3456-7890',NULL,'agent',NULL,1,NULL,'$2y$12$NAObfcbZGPmCSyW5prlmzu1vBot7IkA084emSeYaNp9AsoWHLVfge','agenspl',NULL,NULL,'2026-11-07 00:17:26','2026-11-07 00:17:26'),(73,'JUJU JUARSIH','jujujuarsih','jujujuarsih@spl.local','0812-3456-7890',NULL,'agent',NULL,1,NULL,'$2y$12$I8s1oOmlF8go1o6tp9x2P.nrcUQWDP0Wxwj2E3lOJVOlWlxGn046O','agenspl',NULL,NULL,'2026-11-07 00:17:26','2026-11-07 00:17:26'),(74,'TABUNGAN MUTIA','tabunganmutia','tabunganmutia@spl.local','0812-3456-7890',NULL,'agent',NULL,1,NULL,'$2y$12$oInc.t6qpWW1C6dT0hVE7OMP9qeKbGic482Kio.c67uExnK6OmIKG','agenspl',NULL,NULL,'2026-11-07 00:17:26','2026-11-07 00:17:26'),(75,'ELIS INDAH SARI','elisindahsari','elisindahsari@spl.local','0812-3456-7890',NULL,'agent',NULL,1,NULL,'$2y$12$VeAT4CsCz/TD9wMxSsO0JuFPhmMKtq4FfYbKoXF89Ssp.Nv14rvWG','agenspl',NULL,NULL,'2026-11-07 00:17:27','2026-11-07 00:17:27'),(76,'ICA KAMOJANG','icakamojang','icakamojang@spl.local','0812-3456-7890',NULL,'agent',NULL,1,NULL,'$2y$12$xtX8SvTeOmHLJrQ49rjHru4rd0nM.fLV1vBDMSmLqO0yxKSy3BA8a','agenspl',NULL,NULL,'2026-11-07 00:17:27','2026-11-07 00:17:27'),(77,'EMA OMAH','emaomah','emaomah@spl.local','0812-3456-7890',NULL,'agent',NULL,1,NULL,'$2y$12$Don48V7QIgoMOXHhSS5hBuq1tv9BFuXN8FvCiqd9bagTySsfcvV2a','agenspl',NULL,NULL,'2026-11-07 00:17:27','2026-11-07 00:17:27');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping routines for database 'spl2026'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-11-11  0:25:03
