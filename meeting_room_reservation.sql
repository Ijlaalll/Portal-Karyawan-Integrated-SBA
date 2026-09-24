-- MySQL dump 10.13  Distrib 8.0.30, for Win64 (x86_64)
--
-- Host: localhost    Database: meeting_room_reservation
-- ------------------------------------------------------
-- Server version	8.0.30

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Current Database: `meeting_room_reservation`
--

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `meeting_room_reservation` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;

USE `meeting_room_reservation`;

--
-- Table structure for table `atk_categories`
--

DROP TABLE IF EXISTS `atk_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `atk_categories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `atk_categories_name_index` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `atk_categories`
--

LOCK TABLES `atk_categories` WRITE;
/*!40000 ALTER TABLE `atk_categories` DISABLE KEYS */;
INSERT INTO `atk_categories` VALUES (1,'Alat tulis','Menulis',1,'2026-08-21 03:52:56','2026-08-21 03:52:56'),(2,'Cleansing','Barang Kebersihan',1,'2026-08-26 03:04:18','2026-08-26 03:04:18');
/*!40000 ALTER TABLE `atk_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `atk_items`
--

DROP TABLE IF EXISTS `atk_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `atk_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `category_id` bigint unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `atk_items_category_id_foreign` (`category_id`),
  KEY `atk_items_name_index` (`name`),
  CONSTRAINT `atk_items_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `atk_categories` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `atk_items`
--

LOCK TABLES `atk_items` WRITE;
/*!40000 ALTER TABLE `atk_items` DISABLE KEYS */;
INSERT INTO `atk_items` VALUES (1,1,'Pulpen','pulpen',1,'2026-08-21 03:53:21','2026-08-21 03:53:21'),(2,2,'Tisu','Tisu',1,'2026-08-26 03:04:18','2026-08-26 03:04:18');
/*!40000 ALTER TABLE `atk_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `atk_request_histories`
--

DROP TABLE IF EXISTS `atk_request_histories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `atk_request_histories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `request_id` bigint unsigned NOT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `actor_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `atk_request_histories_request_id_foreign` (`request_id`),
  KEY `atk_request_histories_actor_id_foreign` (`actor_id`),
  KEY `atk_request_histories_status_index` (`status`),
  CONSTRAINT `atk_request_histories_actor_id_foreign` FOREIGN KEY (`actor_id`) REFERENCES `users` (`id`),
  CONSTRAINT `atk_request_histories_request_id_foreign` FOREIGN KEY (`request_id`) REFERENCES `atk_requests` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `atk_request_histories`
--

LOCK TABLES `atk_request_histories` WRITE;
/*!40000 ALTER TABLE `atk_request_histories` DISABLE KEYS */;
/*!40000 ALTER TABLE `atk_request_histories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `atk_request_items`
--

DROP TABLE IF EXISTS `atk_request_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `atk_request_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `request_id` bigint unsigned NOT NULL,
  `variant_id` bigint unsigned NOT NULL,
  `quantity` int NOT NULL,
  `unit_price` decimal(15,2) NOT NULL DEFAULT '0.00',
  `subtotal` decimal(15,2) NOT NULL DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `atk_request_items_request_id_foreign` (`request_id`),
  KEY `atk_request_items_variant_id_foreign` (`variant_id`),
  CONSTRAINT `atk_request_items_request_id_foreign` FOREIGN KEY (`request_id`) REFERENCES `atk_requests` (`id`) ON DELETE CASCADE,
  CONSTRAINT `atk_request_items_variant_id_foreign` FOREIGN KEY (`variant_id`) REFERENCES `atk_variants` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `atk_request_items`
--

LOCK TABLES `atk_request_items` WRITE;
/*!40000 ALTER TABLE `atk_request_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `atk_request_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `atk_requests`
--

DROP TABLE IF EXISTS `atk_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `atk_requests` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `request_number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `status` enum('diajukan','disetujui','diproses','selesai','ditolak','dibatalkan') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'diajukan',
  `note` text COLLATE utf8mb4_unicode_ci,
  `approved_by` bigint unsigned DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `processed_by` bigint unsigned DEFAULT NULL,
  `processed_at` timestamp NULL DEFAULT NULL,
  `completed_by` bigint unsigned DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `rejected_by` bigint unsigned DEFAULT NULL,
  `rejected_at` timestamp NULL DEFAULT NULL,
  `rejection_reason` text COLLATE utf8mb4_unicode_ci,
  `cancelled_by` bigint unsigned DEFAULT NULL,
  `cancelled_at` timestamp NULL DEFAULT NULL,
  `cancellation_reason` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `atk_requests_request_number_unique` (`request_number`),
  KEY `atk_requests_user_id_foreign` (`user_id`),
  KEY `atk_requests_approved_by_foreign` (`approved_by`),
  KEY `atk_requests_processed_by_foreign` (`processed_by`),
  KEY `atk_requests_completed_by_foreign` (`completed_by`),
  KEY `atk_requests_rejected_by_foreign` (`rejected_by`),
  KEY `atk_requests_cancelled_by_foreign` (`cancelled_by`),
  KEY `atk_requests_status_index` (`status`),
  CONSTRAINT `atk_requests_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `atk_requests_cancelled_by_foreign` FOREIGN KEY (`cancelled_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `atk_requests_completed_by_foreign` FOREIGN KEY (`completed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `atk_requests_processed_by_foreign` FOREIGN KEY (`processed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `atk_requests_rejected_by_foreign` FOREIGN KEY (`rejected_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `atk_requests_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `atk_requests`
--

LOCK TABLES `atk_requests` WRITE;
/*!40000 ALTER TABLE `atk_requests` DISABLE KEYS */;
/*!40000 ALTER TABLE `atk_requests` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `atk_stock_movements`
--

DROP TABLE IF EXISTS `atk_stock_movements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `atk_stock_movements` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `variant_id` bigint unsigned NOT NULL,
  `type` enum('adjustment','request','restock') COLLATE utf8mb4_unicode_ci NOT NULL,
  `reference_id` bigint unsigned DEFAULT NULL,
  `reference_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `previous_stock` int NOT NULL,
  `quantity` int NOT NULL,
  `new_stock` int NOT NULL,
  `reason` text COLLATE utf8mb4_unicode_ci,
  `actor_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `atk_stock_movements_variant_id_foreign` (`variant_id`),
  KEY `atk_stock_movements_actor_id_foreign` (`actor_id`),
  KEY `atk_stock_movements_type_index` (`type`),
  CONSTRAINT `atk_stock_movements_actor_id_foreign` FOREIGN KEY (`actor_id`) REFERENCES `users` (`id`),
  CONSTRAINT `atk_stock_movements_variant_id_foreign` FOREIGN KEY (`variant_id`) REFERENCES `atk_variants` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `atk_stock_movements`
--

LOCK TABLES `atk_stock_movements` WRITE;
/*!40000 ALTER TABLE `atk_stock_movements` DISABLE KEYS */;
/*!40000 ALTER TABLE `atk_stock_movements` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `atk_variants`
--

DROP TABLE IF EXISTS `atk_variants`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `atk_variants` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `item_id` bigint unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sku` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `unit` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `stock` int NOT NULL DEFAULT '0',
  `minimum_stock` int NOT NULL DEFAULT '0',
  `purchase_price` decimal(15,2) NOT NULL DEFAULT '0.00',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `atk_variants_sku_unique` (`sku`),
  KEY `atk_variants_item_id_foreign` (`item_id`),
  KEY `atk_variants_name_index` (`name`),
  CONSTRAINT `atk_variants_item_id_foreign` FOREIGN KEY (`item_id`) REFERENCES `atk_items` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `atk_variants`
--

LOCK TABLES `atk_variants` WRITE;
/*!40000 ALTER TABLE `atk_variants` DISABLE KEYS */;
INSERT INTO `atk_variants` VALUES (1,1,'Pilot, Hitam','ATK-00001','Pcs',13,5,3000.00,1,'2026-08-21 03:54:08','2026-08-29 17:28:44'),(2,2,'Paseo 3 ply','ATK-00002','Pack',4,5,10000.00,1,'2026-08-26 03:04:18','2026-08-26 03:23:49');
/*!40000 ALTER TABLE `atk_variants` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bookings`
--

DROP TABLE IF EXISTS `bookings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `bookings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `room_id` bigint unsigned DEFAULT NULL,
  `approved_by` bigint unsigned DEFAULT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `booking_date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `status` enum('pending','approved','rejected','cancelled','completed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `approved_at` timestamp NULL DEFAULT NULL,
  `rejection_reason` text COLLATE utf8mb4_unicode_ci,
  `cancellation_reason` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cancellation_note` text COLLATE utf8mb4_unicode_ci,
  `cancelled_by` bigint unsigned DEFAULT NULL,
  `cancelled_at` datetime DEFAULT NULL,
  `rating` tinyint unsigned DEFAULT NULL,
  `feedback` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `meeting_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'offline',
  `participant_count` int DEFAULT NULL,
  `additional_requirements` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `online_platform` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `online_meeting_link` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `preparation_status` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ready_popup_acknowledged` tinyint(1) NOT NULL DEFAULT '0',
  `active_popup_acknowledged` tinyint(1) NOT NULL DEFAULT '0',
  `ga_status` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `it_status` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `prepared_by_ga` bigint unsigned DEFAULT NULL,
  `prepared_by_it` bigint unsigned DEFAULT NULL,
  `ga_ready_popup_acknowledged` tinyint(1) NOT NULL DEFAULT '0',
  `it_ready_popup_acknowledged` tinyint(1) NOT NULL DEFAULT '0',
  `cancellation_popup_acknowledged` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `bookings_approved_by_foreign` (`approved_by`),
  KEY `bookings_user_id_room_id_booking_date_status_index` (`user_id`,`room_id`,`booking_date`,`status`),
  KEY `bookings_room_id_booking_date_status_start_time_end_time_index` (`room_id`,`booking_date`,`status`,`start_time`,`end_time`),
  KEY `bookings_status_rating_index` (`status`,`rating`),
  KEY `bookings_prepared_by_ga_foreign` (`prepared_by_ga`),
  KEY `bookings_prepared_by_it_foreign` (`prepared_by_it`),
  KEY `bookings_cancelled_by_foreign` (`cancelled_by`),
  CONSTRAINT `bookings_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `bookings_cancelled_by_foreign` FOREIGN KEY (`cancelled_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `bookings_prepared_by_ga_foreign` FOREIGN KEY (`prepared_by_ga`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `bookings_prepared_by_it_foreign` FOREIGN KEY (`prepared_by_it`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `bookings_room_id_foreign` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`),
  CONSTRAINT `bookings_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  CONSTRAINT `bookings_chk_1` CHECK (json_valid(`additional_requirements`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bookings`
--

LOCK TABLES `bookings` WRITE;
/*!40000 ALTER TABLE `bookings` DISABLE KEYS */;
/*!40000 ALTER TABLE `bookings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache`
--

LOCK TABLES `cache` WRITE;
/*!40000 ALTER TABLE `cache` DISABLE KEYS */;
INSERT INTO `cache` VALUES ('sba_operations_cache_smtp_settings_global','N;',2105578904);
/*!40000 ALTER TABLE `cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
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
-- Table structure for table `checklist_items`
--

DROP TABLE IF EXISTS `checklist_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `checklist_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `checklist_items_name_unique` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `checklist_items`
--

LOCK TABLES `checklist_items` WRITE;
/*!40000 ALTER TABLE `checklist_items` DISABLE KEYS */;
INSERT INTO `checklist_items` VALUES (1,'Bau','Toilet bersih dan bebas bau tidak sedap',1,'2026-08-19 01:46:33','2026-08-19 01:46:33'),(2,'Lantai','Lantai toilet bersih, kering, dan tidak licin',1,'2026-08-19 01:46:33','2026-08-19 01:46:33'),(3,'Dinding','Dinding toilet bersih dan tidak terdapat noda atau kerusakan',1,'2026-08-19 01:46:33','2026-08-19 01:46:33'),(4,'Tempat Sampah','Tempat sampah tersedia dan dalam kondisi bersih',1,'2026-08-19 01:46:33','2026-08-19 01:46:33'),(5,'Sabun','Sabun cuci tangan tersedia',1,'2026-08-19 01:46:33','2026-08-19 01:46:33'),(6,'Tisu','Tisu tersedia dan mencukupi',1,'2026-08-19 01:46:33','2026-08-19 01:46:33'),(7,'Cermin','Cermin bersih dan dalam kondisi baik',1,'2026-08-19 01:46:33','2026-08-19 01:46:33'),(8,'Wastafel','Wastafel bersih dan air mengalir dengan lancar',1,'2026-08-19 01:46:33','2026-08-19 01:46:33'),(9,'Kloset','Kloset bersih dan berfungsi dengan baik',1,'2026-08-19 01:46:33','2026-08-19 01:46:33'),(10,'Saringan Pembuangan Air','Saringan pembuangan air bersih dan tidak tersumbat',1,'2026-08-19 01:46:33','2026-08-19 01:46:33'),(11,'Bak / Ember','Bak atau ember tersedia dan dalam kondisi bersih',1,'2026-08-19 01:46:33','2026-08-19 01:46:33'),(12,'Lampu','Lampu menyala dengan baik',1,'2026-08-19 01:46:33','2026-08-19 01:46:33'),(13,'Exhaust Fan','Exhaust fan berfungsi dengan baik',1,'2026-08-19 01:46:33','2026-08-19 01:46:33'),(14,'Air Bersih','Air bersih tersedia dan dapat digunakan',1,'2026-08-19 01:46:33','2026-08-19 01:46:33');
/*!40000 ALTER TABLE `checklist_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cost_center_transactions`
--

DROP TABLE IF EXISTS `cost_center_transactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cost_center_transactions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `cost_center_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `activity_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `reference_id` bigint unsigned NOT NULL,
  `reference_item_id` bigint unsigned NOT NULL DEFAULT '0',
  `description` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `item_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `quantity` decimal(15,2) NOT NULL,
  `unit_price` decimal(15,2) NOT NULL,
  `total_amount` decimal(15,2) NOT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'posted',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `cc_tx_unique_ref` (`activity_type`,`reference_id`,`reference_item_id`,`status`),
  KEY `cost_center_transactions_cost_center_id_foreign` (`cost_center_id`),
  KEY `cost_center_transactions_user_id_foreign` (`user_id`),
  KEY `cost_center_transactions_activity_type_index` (`activity_type`),
  KEY `cost_center_transactions_reference_id_index` (`reference_id`),
  KEY `cost_center_transactions_reference_item_id_index` (`reference_item_id`),
  KEY `cost_center_transactions_status_index` (`status`),
  CONSTRAINT `cost_center_transactions_cost_center_id_foreign` FOREIGN KEY (`cost_center_id`) REFERENCES `cost_centers` (`id`),
  CONSTRAINT `cost_center_transactions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cost_center_transactions`
--

LOCK TABLES `cost_center_transactions` WRITE;
/*!40000 ALTER TABLE `cost_center_transactions` DISABLE KEYS */;
/*!40000 ALTER TABLE `cost_center_transactions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cost_centers`
--

DROP TABLE IF EXISTS `cost_centers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cost_centers` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `cost_center_code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `description` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `person_responsible` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `company_code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `cost_centers_cost_center_code_unique` (`cost_center_code`),
  KEY `cost_centers_status_index` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cost_centers`
--

LOCK TABLES `cost_centers` WRITE;
/*!40000 ALTER TABLE `cost_centers` DISABLE KEYS */;
/*!40000 ALTER TABLE `cost_centers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `department_approvals`
--

DROP TABLE IF EXISTS `department_approvals`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `department_approvals` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `department_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `hod_user_id` bigint unsigned DEFAULT NULL,
  `hod_approver_user_id` bigint unsigned DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `department_approvals_department_name_unique` (`department_name`),
  KEY `department_approvals_hod_user_id_foreign` (`hod_user_id`),
  KEY `department_approvals_hod_approver_user_id_foreign` (`hod_approver_user_id`),
  CONSTRAINT `department_approvals_hod_approver_user_id_foreign` FOREIGN KEY (`hod_approver_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `department_approvals_hod_user_id_foreign` FOREIGN KEY (`hod_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `department_approvals`
--

LOCK TABLES `department_approvals` WRITE;
/*!40000 ALTER TABLE `department_approvals` DISABLE KEYS */;
/*!40000 ALTER TABLE `department_approvals` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `department_replacement_approvers`
--

DROP TABLE IF EXISTS `department_replacement_approvers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `department_replacement_approvers` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `department_approval_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `dept_replacement_unique` (`department_approval_id`,`user_id`),
  KEY `department_replacement_approvers_user_id_foreign` (`user_id`),
  CONSTRAINT `department_replacement_approvers_department_approval_id_foreign` FOREIGN KEY (`department_approval_id`) REFERENCES `department_approvals` (`id`) ON DELETE CASCADE,
  CONSTRAINT `department_replacement_approvers_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `department_replacement_approvers`
--

LOCK TABLES `department_replacement_approvers` WRITE;
/*!40000 ALTER TABLE `department_replacement_approvers` DISABLE KEYS */;
/*!40000 ALTER TABLE `department_replacement_approvers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `drivers`
--

DROP TABLE IF EXISTS `drivers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `drivers` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned DEFAULT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone_number` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('available','inactive') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'available',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `drivers_user_id_foreign` (`user_id`),
  CONSTRAINT `drivers_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `drivers`
--

LOCK TABLES `drivers` WRITE;
/*!40000 ALTER TABLE `drivers` DISABLE KEYS */;
INSERT INTO `drivers` VALUES (1,3,'Siti Rahma','088888888','available','Testing','2026-09-14 07:53:31','2026-09-14 07:53:31');
/*!40000 ALTER TABLE `drivers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `email_logs`
--

DROP TABLE IF EXISTS `email_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `email_logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `recipient` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `subject` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `error_detail` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `email_logs`
--

LOCK TABLES `email_logs` WRITE;
/*!40000 ALTER TABLE `email_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `email_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `email_notification_settings`
--

DROP TABLE IF EXISTS `email_notification_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `email_notification_settings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `event_key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `event_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `reminder_time` int NOT NULL DEFAULT '15',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email_notification_settings_event_key_unique` (`event_key`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `email_notification_settings`
--

LOCK TABLES `email_notification_settings` WRITE;
/*!40000 ALTER TABLE `email_notification_settings` DISABLE KEYS */;
INSERT INTO `email_notification_settings` VALUES (1,'reservasi_baru','Reservasi Baru',1,15,'2026-08-27 07:26:49','2026-08-27 07:26:49'),(2,'reservasi_disetujui','Reservasi Disetujui',1,15,'2026-08-27 07:26:49','2026-08-27 07:26:49'),(3,'reservasi_ditolak','Reservasi Ditolak',1,15,'2026-08-27 07:26:49','2026-08-27 07:26:49'),(4,'reservasi_dibatalkan','Reservasi Dibatalkan',1,15,'2026-08-27 07:26:49','2026-08-27 07:26:49'),(5,'pengingat_meeting','Pengingat Meeting',1,30,'2026-08-27 07:26:49','2026-08-27 07:26:49');
/*!40000 ALTER TABLE `email_notification_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `email_templates`
--

DROP TABLE IF EXISTS `email_templates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `email_templates` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `template_key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `subject` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `body` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email_templates_template_key_unique` (`template_key`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `email_templates`
--

LOCK TABLES `email_templates` WRITE;
/*!40000 ALTER TABLE `email_templates` DISABLE KEYS */;
INSERT INTO `email_templates` VALUES (1,'reservasi_baru','Reservasi Baru','Reservasi Ruang Meeting Baru #{reservation_id}','Halo {user_name},\n\nReservasi Anda untuk ruangan {room_name} pada tanggal {date} pukul {start_time} - {end_time} telah berhasil dibuat.\n\nStatus saat ini: {status}.\n\nTerima kasih.',1,'2026-08-27 07:26:49','2026-08-27 07:26:49'),(2,'reservasi_disetujui','Reservasi Disetujui','Reservasi Ruang Meeting Disetujui #{reservation_id}','Halo {user_name},\n\nReservasi Anda untuk ruangan {room_name} pada tanggal {date} pukul {start_time} - {end_time} telah DISETUJUI oleh General Affairs (GA).\n\nSilakan gunakan ruangan sesuai waktu tersebut.\n\nTerima kasih.',1,'2026-08-27 07:26:49','2026-08-27 07:26:49'),(3,'reservasi_ditolak','Reservasi Ditolak','Reservasi Ruang Meeting Ditolak #{reservation_id}','Halo {user_name},\n\nKami menginformasikan bahwa reservasi Anda untuk ruangan {room_name} pada tanggal {date} pukul {start_time} - {end_time} telah DITOLAK.\n\nAlasan penolakan: {cancellation_note}.\n\nTerima kasih.',1,'2026-08-27 07:26:49','2026-08-27 07:26:49'),(4,'reservasi_dibatalkan','Reservasi Dibatalkan','Reservasi Ruang Meeting Dibatalkan #{reservation_id}','Halo {user_name},\n\nReservasi Anda untuk ruangan {room_name} pada tanggal {date} pukul {start_time} - {end_time} telah DIBATALKAN.\n\nTerima kasih.',1,'2026-08-27 07:26:49','2026-08-27 07:26:49'),(5,'pengingat_meeting','Pengingat Meeting','Pengingat: Meeting Anda di {room_name} akan segera dimulai','Halo {user_name},\n\nIni adalah pengingat bahwa meeting Anda di ruangan {room_name} akan dimulai dalam waktu {reminder_time} menit lagi.\n\nWaktu: {start_time} - {end_time} WIB.\n\nTerima kasih.',1,'2026-08-27 07:26:49','2026-08-27 07:26:49'),(6,'akun_dibuat','Akun Dibuat','Akun SBA Anda telah berhasil dibuat','Halo {user_name},\n\nAkun Anda untuk sistem SBA telah berhasil dibuat oleh Administrator.\n\nUsername/Email: {user_email}\nPeran: {role}\n\nSilakan login menggunakan kredensial Anda.\n\nTerima kasih.',1,'2026-08-27 07:26:49','2026-08-27 07:26:49'),(7,'reset_password','Reset Password','Permintaan Reset Password Akun SBA','Halo {user_name},\n\nKami menerima permintaan untuk merestart password akun Anda.\n\nSilakan gunakan tautan berikut untuk memperbarui password Anda: {reset_link}\n\nJika Anda tidak meminta ini, abaikan email ini.\n\nTerima kasih.',1,'2026-08-27 07:26:49','2026-08-27 07:26:49');
/*!40000 ALTER TABLE `email_templates` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `exit_permits`
--

DROP TABLE IF EXISTS `exit_permits`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `exit_permits` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `sik_number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `department` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `position` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `employee_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `purpose_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'dinas',
  `purpose` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `purpose_detail` text COLLATE utf8mb4_unicode_ci,
  `permit_date` date NOT NULL,
  `expected_departure_time` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expected_return_time` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `vehicle_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `vehicle_details` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `approver_id` bigint unsigned DEFAULT NULL,
  `is_replacement_approver` tinyint(1) NOT NULL DEFAULT '0',
  `approved_at` timestamp NULL DEFAULT NULL,
  `approval_note` text COLLATE utf8mb4_unicode_ci,
  `rejection_reason` text COLLATE utf8mb4_unicode_ci,
  `qr_token` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `actual_departure_at` timestamp NULL DEFAULT NULL,
  `actual_return_at` timestamp NULL DEFAULT NULL,
  `exit_scanned_by` bigint unsigned DEFAULT NULL,
  `return_scanned_by` bigint unsigned DEFAULT NULL,
  `revoked_by` bigint unsigned DEFAULT NULL,
  `revoked_at` timestamp NULL DEFAULT NULL,
  `revoke_reason` text COLLATE utf8mb4_unicode_ci,
  `closed_by` bigint unsigned DEFAULT NULL,
  `closed_at` timestamp NULL DEFAULT NULL,
  `close_reason` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `exit_permits_sik_number_unique` (`sik_number`),
  UNIQUE KEY `exit_permits_qr_token_unique` (`qr_token`),
  KEY `exit_permits_user_id_foreign` (`user_id`),
  KEY `exit_permits_approver_id_foreign` (`approver_id`),
  KEY `exit_permits_exit_scanned_by_foreign` (`exit_scanned_by`),
  KEY `exit_permits_return_scanned_by_foreign` (`return_scanned_by`),
  KEY `exit_permits_revoked_by_foreign` (`revoked_by`),
  KEY `exit_permits_closed_by_foreign` (`closed_by`),
  KEY `exit_permits_status_index` (`status`),
  CONSTRAINT `exit_permits_approver_id_foreign` FOREIGN KEY (`approver_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `exit_permits_closed_by_foreign` FOREIGN KEY (`closed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `exit_permits_exit_scanned_by_foreign` FOREIGN KEY (`exit_scanned_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `exit_permits_return_scanned_by_foreign` FOREIGN KEY (`return_scanned_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `exit_permits_revoked_by_foreign` FOREIGN KEY (`revoked_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `exit_permits_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `exit_permits`
--

LOCK TABLES `exit_permits` WRITE;
/*!40000 ALTER TABLE `exit_permits` DISABLE KEYS */;
/*!40000 ALTER TABLE `exit_permits` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `facilities`
--

DROP TABLE IF EXISTS `facilities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `facilities` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `facilities_name_unique` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `facilities`
--

LOCK TABLES `facilities` WRITE;
/*!40000 ALTER TABLE `facilities` DISABLE KEYS */;
INSERT INTO `facilities` VALUES (1,'Air Conditioner','AC pendingin ruangan','2026-08-19 01:46:33','2026-08-19 01:46:33'),(2,'Projector','Proyektor HD dengan kabel HDMI','2026-08-19 01:46:33','2026-08-19 01:46:33'),(3,'Whiteboard','Papan tulis spidol & penghapus','2026-08-19 01:46:33','2026-08-19 01:46:33'),(4,'Smart TV 55 Inch','Layar display presentasi','2026-08-19 01:46:33','2026-08-19 01:46:33'),(5,'Video Conference System','Kamera dan mikrofon konferensi','2026-08-19 01:46:33','2026-08-19 01:46:33'),(6,'High-Speed WiFi','Koneksi internet dedicated','2026-08-19 01:46:33','2026-08-19 01:46:33'),(7,'Sound System','Speaker dan mic nirkabel','2026-08-19 01:46:33','2026-08-19 01:46:33');
/*!40000 ALTER TABLE `facilities` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
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
-- Table structure for table `inspection_approvals`
--

DROP TABLE IF EXISTS `inspection_approvals`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `inspection_approvals` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `inspection_id` bigint unsigned NOT NULL,
  `approver_id` bigint unsigned NOT NULL,
  `action` enum('approve','reject') COLLATE utf8mb4_unicode_ci NOT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `actioned_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `inspection_approvals_inspection_id_foreign` (`inspection_id`),
  KEY `inspection_approvals_approver_id_foreign` (`approver_id`),
  CONSTRAINT `inspection_approvals_approver_id_foreign` FOREIGN KEY (`approver_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `inspection_approvals_inspection_id_foreign` FOREIGN KEY (`inspection_id`) REFERENCES `inspections` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `inspection_approvals`
--

LOCK TABLES `inspection_approvals` WRITE;
/*!40000 ALTER TABLE `inspection_approvals` DISABLE KEYS */;
/*!40000 ALTER TABLE `inspection_approvals` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `inspection_results`
--

DROP TABLE IF EXISTS `inspection_results`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `inspection_results` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `inspection_id` bigint unsigned NOT NULL,
  `checklist_item_id` bigint unsigned NOT NULL,
  `is_ok` tinyint(1) NOT NULL,
  `remarks` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `inspection_results_inspection_id_foreign` (`inspection_id`),
  KEY `inspection_results_checklist_item_id_foreign` (`checklist_item_id`),
  CONSTRAINT `inspection_results_checklist_item_id_foreign` FOREIGN KEY (`checklist_item_id`) REFERENCES `checklist_items` (`id`) ON DELETE CASCADE,
  CONSTRAINT `inspection_results_inspection_id_foreign` FOREIGN KEY (`inspection_id`) REFERENCES `inspections` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `inspection_results`
--

LOCK TABLES `inspection_results` WRITE;
/*!40000 ALTER TABLE `inspection_results` DISABLE KEYS */;
/*!40000 ALTER TABLE `inspection_results` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `inspections`
--

DROP TABLE IF EXISTS `inspections`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `inspections` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `location_id` bigint unsigned NOT NULL,
  `inspector_id` bigint unsigned NOT NULL,
  `status` enum('draft','submitted','approved','rejected') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `photo_path` varchar(2048) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `inspected_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `inspections_location_id_foreign` (`location_id`),
  KEY `inspections_inspector_id_foreign` (`inspector_id`),
  KEY `inspections_status_index` (`status`),
  CONSTRAINT `inspections_inspector_id_foreign` FOREIGN KEY (`inspector_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `inspections_location_id_foreign` FOREIGN KEY (`location_id`) REFERENCES `toilet_locations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `inspections`
--

LOCK TABLES `inspections` WRITE;
/*!40000 ALTER TABLE `inspections` DISABLE KEYS */;
/*!40000 ALTER TABLE `inspections` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `job_batches`
--

DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL,
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
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
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
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=42 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'0001_01_01_000000_create_users_table',1),(2,'0001_01_01_000001_create_cache_table',1),(3,'0001_01_01_000002_create_jobs_table',1),(4,'2026_01_01_000002_create_rooms_table',1),(5,'2026_01_01_000003_create_facilities_table',1),(6,'2026_01_01_000004_create_room_facilities_table',1),(7,'2026_01_01_000005_create_bookings_table',1),(8,'2026_08_11_091241_add_spv_role_and_create_user_modules_table',1),(9,'2026_08_11_094625_create_toilet_locations_table',1),(10,'2026_08_11_094626_create_checklist_items_table',1),(11,'2026_08_11_094626_create_inspections_table',1),(12,'2026_08_11_094627_create_inspection_results_table',1),(13,'2026_08_11_094628_create_inspection_approvals_table',1),(14,'2026_08_12_095200_add_status_to_users_table',1),(15,'2026_08_13_095725_add_rating_and_feedback_to_bookings_table',1),(16,'2026_08_19_084300_update_bookings_for_revision',1),(17,'2026_08_19_084400_create_notifications_table',1),(18,'2026_08_19_084500_add_ga_it_roles_to_users_table',1),(19,'2026_08_19_113000_add_popup_acknowledgment_columns_to_bookings_table',2),(20,'2026_08_20_000000_add_ga_it_columns_to_bookings_table',3),(21,'2026_08_20_113000_add_cancellation_fields_to_bookings_table',4),(22,'2026_08_20_121500_add_more_cancellation_fields_to_bookings_table',5),(23,'2026_08_20_134028_add_cancellation_metadata_to_bookings_table',99),(24,'2026_08_21_093300_create_atk_tables',100),(25,'2026_08_26_101500_add_purchase_price_to_atk_tables',101),(26,'2026_08_27_110537_create_cost_centers_table',102),(27,'2026_08_27_110538_create_smtp_settings_table',102),(28,'2026_08_27_142000_create_smtp_enhancements_tables',103),(29,'2026_08_27_170000_add_is_active_to_smtp_settings_table',104),(30,'2026_08_29_230230_update_tables_for_cost_center',105),(31,'2026_09_01_100001_add_position_and_employee_fields_to_users_table',106),(32,'2026_09_01_100002_create_department_approvals_table',106),(33,'2026_09_01_100003_create_exit_permits_table',106),(34,'2026_09_01_100004_create_sik_approval_histories_table',106),(35,'2026_09_01_100005_create_sik_scan_logs_table',106),(36,'2026_09_03_100001_create_department_replacement_approvers_table',107),(37,'2026_09_03_100002_add_replacement_approver_fields_to_sik_tables',107),(38,'2026_09_15_000001_create_vehicles_table',108),(39,'2026_09_15_000002_create_drivers_table',108),(40,'2026_09_15_000003_create_vehicle_requests_table',108),(41,'2026_09_17_000001_create_vehicle_fuel_logs_table',109);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `notifications` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `link` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `notifications_user_id_foreign` (`user_id`),
  CONSTRAINT `notifications_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notifications`
--

LOCK TABLES `notifications` WRITE;
/*!40000 ALTER TABLE `notifications` DISABLE KEYS */;
/*!40000 ALTER TABLE `notifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
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
-- Table structure for table `room_facilities`
--

DROP TABLE IF EXISTS `room_facilities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `room_facilities` (
  `room_id` bigint unsigned NOT NULL,
  `facility_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`room_id`,`facility_id`),
  KEY `room_facilities_facility_id_foreign` (`facility_id`),
  CONSTRAINT `room_facilities_facility_id_foreign` FOREIGN KEY (`facility_id`) REFERENCES `facilities` (`id`) ON DELETE CASCADE,
  CONSTRAINT `room_facilities_room_id_foreign` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `room_facilities`
--

LOCK TABLES `room_facilities` WRITE;
/*!40000 ALTER TABLE `room_facilities` DISABLE KEYS */;
INSERT INTO `room_facilities` VALUES (1,1),(2,1),(3,1),(4,1),(5,1),(6,1),(1,2),(3,2),(1,3),(3,3),(4,3),(2,4),(2,5),(1,6),(2,6),(3,6),(4,6),(6,6),(3,7);
/*!40000 ALTER TABLE `room_facilities` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rooms`
--

DROP TABLE IF EXISTS `rooms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rooms` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `location` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `capacity` int unsigned NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `price_per_hour` decimal(15,2) NOT NULL DEFAULT '0.00',
  `is_pricing_active` tinyint(1) NOT NULL DEFAULT '1',
  `status` enum('active','inactive') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rooms`
--

LOCK TABLES `rooms` WRITE;
/*!40000 ALTER TABLE `rooms` DISABLE KEYS */;
INSERT INTO `rooms` VALUES (1,'Seulanga Meeting Room','Main Office',30,'Ruang meeting skala kecil cocok untuk diskusi tim.',50000.00,1,'active','rooms/room_a.jpg','2026-08-19 01:46:33','2026-08-29 17:03:47'),(2,'Melati Meeting Room','Main Office',10,'Ruang meeting eksekutif dengan fasilitas lengkap.',50000.00,1,'active','rooms/room_b.jpg','2026-08-19 01:46:33','2026-08-29 17:03:33'),(3,'Andalas Hall','Andalas Hall',100,'Ruang presentasi besar untuk seminar internal.',50000.00,1,'active','rooms/room_c.jpg','2026-08-19 01:46:33','2026-08-29 17:03:11'),(4,'Jeumpa Meeting Room','Main Office',10,'Ruang kerja fokus dan sprint rapat singkat.',50000.00,1,'active','rooms/room_b.jpg','2026-08-19 01:46:33','2026-08-29 17:03:19'),(5,'LLC Meeting Room','LLC',20,'Ruangan sedang dalam pemeliharaan.',50000.00,1,'active','rooms/room_a.jpg','2026-08-19 01:46:33','2026-08-29 17:03:26'),(6,'Production Meeting Room','CCR Lantai 2',30,NULL,50000.00,1,'active','rooms/room_c.jpg','2026-08-19 08:04:41','2026-08-29 17:03:39');
/*!40000 ALTER TABLE `rooms` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
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
INSERT INTO `sessions` VALUES ('klKCMq4wms4i8HIy3GhMx9CPMwod9v2o2wSlJU6M',1,'192.168.100.237','Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Mobile Safari/537.36','YTo1OntzOjY6Il90b2tlbiI7czo0MDoieGxmZklpbGdUMDJUOFU5SmNiTGY5WWxlNjdpSFd1NFdTVm1oYlpzYyI7czozOiJ1cmwiO2E6MDp7fXM6OToiX3ByZXZpb3VzIjthOjI6e3M6MzoidXJsIjtzOjQ4OiJodHRwOi8vMTkyLjE2OC4xMDAuMjQwOjgwODAvYWRtaW4vc2V0dGluZ3MvdXNlcnMiO3M6NToicm91dGUiO3M6MTc6ImFkbWluLnVzZXJzLmluZGV4Ijt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MTt9',1790218553),('l9fOPcF5ajtD3sJkUsCyqApwU6agpSpseiOXqJFm',1,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36 Edg/154.0.0.0','YTo0OntzOjY6Il90b2tlbiI7czo0MDoiNmh5WHZqTFplQUZlUWdaMDdYaHM4a3NOTmVrcFQ1OTNKYjVCZTU3TSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NTA6Imh0dHA6Ly8xMjcuMC4wLjE6ODA4MC9hZG1pbi9zZXR0aW5ncy9zbXRwP3RhYj1sb2dzIjtzOjU6InJvdXRlIjtzOjI0OiJhZG1pbi5zZXR0aW5ncy5zbXRwLnNob3ciO31zOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aToxO30=',1790218904);
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sik_approval_histories`
--

DROP TABLE IF EXISTS `sik_approval_histories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sik_approval_histories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `exit_permit_id` bigint unsigned NOT NULL,
  `approver_id` bigint unsigned DEFAULT NULL,
  `approver_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'default',
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `note` text COLLATE utf8mb4_unicode_ci,
  `actioned_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sik_approval_histories_exit_permit_id_foreign` (`exit_permit_id`),
  KEY `sik_approval_histories_approver_id_foreign` (`approver_id`),
  CONSTRAINT `sik_approval_histories_approver_id_foreign` FOREIGN KEY (`approver_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `sik_approval_histories_exit_permit_id_foreign` FOREIGN KEY (`exit_permit_id`) REFERENCES `exit_permits` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sik_approval_histories`
--

LOCK TABLES `sik_approval_histories` WRITE;
/*!40000 ALTER TABLE `sik_approval_histories` DISABLE KEYS */;
/*!40000 ALTER TABLE `sik_approval_histories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sik_scan_logs`
--

DROP TABLE IF EXISTS `sik_scan_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sik_scan_logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `exit_permit_id` bigint unsigned NOT NULL,
  `scanned_by` bigint unsigned DEFAULT NULL,
  `scan_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `scanned_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `gate` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sik_scan_logs_exit_permit_id_foreign` (`exit_permit_id`),
  KEY `sik_scan_logs_scanned_by_foreign` (`scanned_by`),
  CONSTRAINT `sik_scan_logs_exit_permit_id_foreign` FOREIGN KEY (`exit_permit_id`) REFERENCES `exit_permits` (`id`) ON DELETE CASCADE,
  CONSTRAINT `sik_scan_logs_scanned_by_foreign` FOREIGN KEY (`scanned_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sik_scan_logs`
--

LOCK TABLES `sik_scan_logs` WRITE;
/*!40000 ALTER TABLE `sik_scan_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `sik_scan_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `smtp_histories`
--

DROP TABLE IF EXISTS `smtp_histories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `smtp_histories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned DEFAULT NULL,
  `host` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `port` int NOT NULL,
  `encryption` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `username` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `from_address` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `from_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `action` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `smtp_histories_user_id_foreign` (`user_id`),
  CONSTRAINT `smtp_histories_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `smtp_histories`
--

LOCK TABLES `smtp_histories` WRITE;
/*!40000 ALTER TABLE `smtp_histories` DISABLE KEYS */;
/*!40000 ALTER TABLE `smtp_histories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `smtp_settings`
--

DROP TABLE IF EXISTS `smtp_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `smtp_settings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `host` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `port` int NOT NULL,
  `username` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `encryption` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `from_address` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `from_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'not_tested',
  `last_success_at` timestamp NULL DEFAULT NULL,
  `last_failure_at` timestamp NULL DEFAULT NULL,
  `last_error_message` text COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `smtp_settings`
--

LOCK TABLES `smtp_settings` WRITE;
/*!40000 ALTER TABLE `smtp_settings` DISABLE KEYS */;
/*!40000 ALTER TABLE `smtp_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `toilet_locations`
--

DROP TABLE IF EXISTS `toilet_locations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `toilet_locations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `building` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `floor` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `area` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `toilet_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `gender` enum('pria','wanita','unisex') COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `toilet_locations_building_index` (`building`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `toilet_locations`
--

LOCK TABLES `toilet_locations` WRITE;
/*!40000 ALTER TABLE `toilet_locations` DISABLE KEYS */;
INSERT INTO `toilet_locations` VALUES (1,'Gedung A','Lantai 1','Area Office Divisi Finance','Toilet Utama Pria','pria',1,'2026-08-19 01:46:33','2026-08-19 01:46:33'),(2,'Gedung A','Lantai 1','Area Office Divisi Finance','Toilet Utama Wanita','wanita',1,'2026-08-19 01:46:33','2026-08-19 01:46:33');
/*!40000 ALTER TABLE `toilet_locations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_modules`
--

DROP TABLE IF EXISTS `user_modules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_modules` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `module` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_modules_user_id_module_unique` (`user_id`,`module`),
  CONSTRAINT `user_modules_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=62 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_modules`
--

LOCK TABLES `user_modules` WRITE;
/*!40000 ALTER TABLE `user_modules` DISABLE KEYS */;
INSERT INTO `user_modules` VALUES (54,13,'meeting_room','2026-09-22 03:01:39','2026-09-22 03:01:39'),(55,13,'toilet_inspection','2026-09-22 03:01:39','2026-09-22 03:01:39'),(56,13,'atk','2026-09-22 03:01:39','2026-09-22 03:01:39'),(57,13,'sik','2026-09-22 03:01:39','2026-09-22 03:01:39'),(58,13,'vehicle','2026-09-22 03:01:39','2026-09-22 03:01:39'),(59,14,'meeting_room','2026-09-24 02:47:27','2026-09-24 02:47:27'),(60,15,'toilet_inspection','2026-09-24 02:47:27','2026-09-24 02:47:27'),(61,17,'toilet_inspection','2026-09-24 02:51:47','2026-09-24 02:51:47');
/*!40000 ALTER TABLE `user_modules` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('admin','user','spv','ga','it','security') COLLATE utf8mb4_unicode_ci DEFAULT 'user',
  `department` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `position` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `employee_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `joined_at` date DEFAULT NULL,
  `cost_center_id` bigint unsigned DEFAULT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  KEY `users_cost_center_id_foreign` (`cost_center_id`),
  CONSTRAINT `users_cost_center_id_foreign` FOREIGN KEY (`cost_center_id`) REFERENCES `cost_centers` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'System Admin','admin@example.com',NULL,'$2y$12$enYF1/kRakfBrdNHh5Bty.CpuIPIIQGpAwHhp7cC5s7/a.0taXjli','admin',NULL,NULL,NULL,NULL,NULL,'active',NULL,'2026-08-19 01:46:30','2026-08-19 01:46:30'),(13,'General Affairs (GA)','ga@example.com',NULL,'$2y$12$YRLnBAh82usM07vEZJOPDOJ/wMnltGKkM9SBaMJ8eSlW9clUsbBGu','ga',NULL,NULL,NULL,NULL,NULL,'active',NULL,'2026-09-22 03:01:39','2026-09-22 03:01:39'),(14,'IT Support (IT)','it@example.com',NULL,'$2y$12$wHMeFgLZoY9sgMFeTeA/AOGoovSJHUaOREcQJo0MaImyqPY0V/7jq','it','IT & Automation','IT Support Specialist','SBA-IT-001',NULL,NULL,'active',NULL,'2026-09-24 02:47:27','2026-09-24 02:53:34'),(15,'Supervisor','spv@example.com',NULL,'$2y$12$VvsGyI5tvsm/BZR6ALaaHekMFyz/KolmuOgEpym.ek0XbWVSNaWqS','spv','Housekeeping & Facility','Supervisor Facility','SBA-SPV-001',NULL,NULL,'active',NULL,'2026-09-24 02:47:27','2026-09-24 02:52:17'),(16,'Danpos Security','security@example.com',NULL,'$2y$12$O4/BR8FgSxdibYJ6oj4u2e3MkRggW3sCQLp0dXXjO4kgzmAETSPNm','security','Security & Safety','Chief Security Guard','SBA-SEC-001',NULL,NULL,'active',NULL,'2026-09-24 02:47:27','2026-09-24 02:56:07'),(17,'Petugas Toilet','petugas@example.com',NULL,'$2y$12$tbbvQNML6Eg5Is82MFuGPO5qerLYwk5TMUCF/Lc5T0pj3bpQ1FD1.','user',NULL,NULL,NULL,NULL,NULL,'active',NULL,'2026-09-24 02:51:47','2026-09-24 02:51:47');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `vehicle_fuel_logs`
--

DROP TABLE IF EXISTS `vehicle_fuel_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `vehicle_fuel_logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `vehicle_id` bigint unsigned NOT NULL,
  `recorded_at` datetime NOT NULL,
  `odometer` int unsigned NOT NULL,
  `fuel_liters` decimal(8,2) NOT NULL,
  `fuel_cost` decimal(15,2) DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `recorded_by` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `vehicle_fuel_logs_recorded_by_foreign` (`recorded_by`),
  KEY `vehicle_fuel_logs_vehicle_id_recorded_at_id_index` (`vehicle_id`,`recorded_at`,`id`),
  CONSTRAINT `vehicle_fuel_logs_recorded_by_foreign` FOREIGN KEY (`recorded_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `vehicle_fuel_logs_vehicle_id_foreign` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `vehicle_fuel_logs`
--

LOCK TABLES `vehicle_fuel_logs` WRITE;
/*!40000 ALTER TABLE `vehicle_fuel_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `vehicle_fuel_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `vehicle_requests`
--

DROP TABLE IF EXISTS `vehicle_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `vehicle_requests` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `request_number` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `purpose` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `destination` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `start_datetime` datetime NOT NULL,
  `end_datetime` datetime NOT NULL,
  `vehicle_id` bigint unsigned DEFAULT NULL,
  `driver_id` bigint unsigned DEFAULT NULL,
  `status` enum('pending_approval','approved','rejected','completed','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending_approval',
  `approved_by` bigint unsigned DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `rejection_reason` text COLLATE utf8mb4_unicode_ci,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `vehicle_requests_request_number_unique` (`request_number`),
  KEY `vehicle_requests_user_id_foreign` (`user_id`),
  KEY `vehicle_requests_vehicle_id_foreign` (`vehicle_id`),
  KEY `vehicle_requests_driver_id_foreign` (`driver_id`),
  KEY `vehicle_requests_approved_by_foreign` (`approved_by`),
  CONSTRAINT `vehicle_requests_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `vehicle_requests_driver_id_foreign` FOREIGN KEY (`driver_id`) REFERENCES `drivers` (`id`) ON DELETE SET NULL,
  CONSTRAINT `vehicle_requests_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `vehicle_requests_vehicle_id_foreign` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `vehicle_requests`
--

LOCK TABLES `vehicle_requests` WRITE;
/*!40000 ALTER TABLE `vehicle_requests` DISABLE KEYS */;
/*!40000 ALTER TABLE `vehicle_requests` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `vehicles`
--

DROP TABLE IF EXISTS `vehicles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `vehicles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `plate_number` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fuel_type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Bensin',
  `status` enum('available','maintenance','inactive') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'available',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `vehicles_plate_number_unique` (`plate_number`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `vehicles`
--

LOCK TABLES `vehicles` WRITE;
/*!40000 ALTER TABLE `vehicles` DISABLE KEYS */;
INSERT INTO `vehicles` VALUES (1,'BL 123 AB','Range Rover','Bensin','available','Testing','2026-09-14 07:51:38','2026-09-14 07:53:45');
/*!40000 ALTER TABLE `vehicles` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-09-24 10:03:56
