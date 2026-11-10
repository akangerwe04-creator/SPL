-- ==================================================================
-- SUSI PARSEL LEBARAN (SPL) - DATABASE DUMP
-- Database: spl2026 / MySQL & MariaDB compatible
-- Generated: 2026-11-04 13:55:18
-- ==================================================================

SET FOREIGN_KEY_CHECKS=0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+07:00";

CREATE TABLE IF NOT EXISTS `users` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `username` varchar(255) DEFAULT NULL UNIQUE,
  `email` varchar(255) NOT NULL UNIQUE,
  `phone` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `role` varchar(50) NOT NULL DEFAULT 'agent',
  `avatar` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `users` (`id`, `name`, `username`, `email`, `phone`, `address`, `role`, `avatar`, `is_active`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`) VALUES (1, 'Ibu Hj. Susi Susilawati', 'susi', 'susi@spl.com', '0812-3456-7890', 'Komplek Pertokoan Parsel Blok A1, Paseh', 'owner', NULL, 1, NULL, '$2y$12$EWUYgZs7nUOT8f1Tk0TgD.MW3g4yfpHrMKplKYlLl/rf53/iPNyRO', NULL, '2026-11-04 13:31:37', '2026-11-04 13:31:37');
INSERT INTO `users` (`id`, `name`, `username`, `email`, `phone`, `address`, `role`, `avatar`, `is_active`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`) VALUES (4, 'Budi Santoso', 'budi', 'budi@spl.com', '0813-9876-5432', 'Kp. Babakan RT 02/RW 04, Paseh', 'agent', NULL, 1, NULL, '$2y$04$tc5xQUXA8.pbYRfuc1jYXexhCUVkCpGLDyF2YQ172tYAV3N/amRj.', NULL, '2026-11-04 13:33:21', '2026-11-04 13:33:21');
INSERT INTO `users` (`id`, `name`, `username`, `email`, `phone`, `address`, `role`, `avatar`, `is_active`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`) VALUES (5, 'Siti Aminah', 'siti', 'siti@spl.com', '0815-5555-8888', 'Kp. Cijambe No. 12, Majalaya', 'agent', NULL, 1, NULL, '$2y$04$VO5esyMVS5PhVCmlNq4em.29Ji9BVJl5BUGHfAHbq82r9hRtPsikq', NULL, '2026-11-04 13:33:21', '2026-11-04 13:33:21');
INSERT INTO `users` (`id`, `name`, `username`, `email`, `phone`, `address`, `role`, `avatar`, `is_active`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`) VALUES (6, 'Agen Sukses Terverifikasi', 'agen_sukses', 'agen_sukses@spl.local', '0899-9999-8888', 'Jl. Raya Cipaku No. 45, Paseh', 'agent', NULL, 1, NULL, '$2y$04$Fc6M.rmJQ2Uym74Tq3jZdOJ1MEbJISBuTohdxUltt/izLSw24QoFW', NULL, '2026-11-04 13:33:54', '2026-11-04 13:33:54');

CREATE TABLE IF NOT EXISTS `items` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `code` varchar(50) DEFAULT NULL UNIQUE,
  `name` varchar(255) NOT NULL,
  `unit_price` decimal(14,2) NOT NULL DEFAULT 0.00,
  `base_unit` varchar(50) NOT NULL DEFAULT 'pcs',
  `wholesale_unit` varchar(50) NOT NULL DEFAULT 'dus',
  `conversion_rate` decimal(10,2) NOT NULL DEFAULT 24.00,
  `cost_price` decimal(14,2) NOT NULL DEFAULT 0.00,
  `category` varchar(100) DEFAULT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `items` (`id`, `code`, `name`, `unit_price`, `base_unit`, `wholesale_unit`, `conversion_rate`, `cost_price`, `category`, `image_path`, `is_active`, `created_at`, `updated_at`) VALUES (51, 'BRG-001', 'Khong Guan Classic Red 1600g', 115000, 'kaleng', 'dus', 6, 102000, 'Biskuit & Kue', NULL, 1, '2026-11-04 13:33:21', '2026-11-04 13:33:21');
INSERT INTO `items` (`id`, `code`, `name`, `unit_price`, `base_unit`, `wholesale_unit`, `conversion_rate`, `cost_price`, `category`, `image_path`, `is_active`, `created_at`, `updated_at`) VALUES (52, 'BRG-002', 'Monde Butter Cookies 454g', 78000, 'kaleng', 'dus', 6, 69000, 'Biskuit & Kue', NULL, 1, '2026-11-04 13:33:21', '2026-11-04 13:33:21');
INSERT INTO `items` (`id`, `code`, `name`, `unit_price`, `base_unit`, `wholesale_unit`, `conversion_rate`, `cost_price`, `category`, `image_path`, `is_active`, `created_at`, `updated_at`) VALUES (53, 'BRG-003', 'Wafer Tango Vanilla Kaleng 300g', 38000, 'kaleng', 'dus', 12, 33000, 'Biskuit & Kue', NULL, 1, '2026-11-04 13:33:21', '2026-11-04 13:33:21');
INSERT INTO `items` (`id`, `code`, `name`, `unit_price`, `base_unit`, `wholesale_unit`, `conversion_rate`, `cost_price`, `category`, `image_path`, `is_active`, `created_at`, `updated_at`) VALUES (54, 'BRG-004', 'Wafer Tango Cokelat Kaleng 300g', 38000, 'kaleng', 'dus', 12, 33000, 'Biskuit & Kue', NULL, 1, '2026-11-04 13:33:22', '2026-11-04 13:33:22');
INSERT INTO `items` (`id`, `code`, `name`, `unit_price`, `base_unit`, `wholesale_unit`, `conversion_rate`, `cost_price`, `category`, `image_path`, `is_active`, `created_at`, `updated_at`) VALUES (55, 'BRG-005', 'Wafer Roma Kelapa Kaleng 350g', 36000, 'kaleng', 'dus', 12, 31000, 'Biskuit & Kue', NULL, 1, '2026-11-04 13:33:22', '2026-11-04 13:33:22');
INSERT INTO `items` (`id`, `code`, `name`, `unit_price`, `base_unit`, `wholesale_unit`, `conversion_rate`, `cost_price`, `category`, `image_path`, `is_active`, `created_at`, `updated_at`) VALUES (56, 'BRG-006', 'Astor Wafer Stick Cokelat 330g', 42000, 'kaleng', 'dus', 12, 36000, 'Biskuit & Kue', NULL, 1, '2026-11-04 13:33:22', '2026-11-04 13:33:22');
INSERT INTO `items` (`id`, `code`, `name`, `unit_price`, `base_unit`, `wholesale_unit`, `conversion_rate`, `cost_price`, `category`, `image_path`, `is_active`, `created_at`, `updated_at`) VALUES (57, 'BRG-007', 'Nissin Wafer Chocolate 570g', 55000, 'kaleng', 'dus', 6, 48000, 'Biskuit & Kue', NULL, 1, '2026-11-04 13:33:22', '2026-11-04 13:33:22');
INSERT INTO `items` (`id`, `code`, `name`, `unit_price`, `base_unit`, `wholesale_unit`, `conversion_rate`, `cost_price`, `category`, `image_path`, `is_active`, `created_at`, `updated_at`) VALUES (58, 'BRG-008', 'Good Time Chocochip Cookies 149g', 24000, 'kaleng', 'dus', 12, 20000, 'Biskuit & Kue', NULL, 1, '2026-11-04 13:33:22', '2026-11-04 13:33:22');
INSERT INTO `items` (`id`, `code`, `name`, `unit_price`, `base_unit`, `wholesale_unit`, `conversion_rate`, `cost_price`, `category`, `image_path`, `is_active`, `created_at`, `updated_at`) VALUES (59, 'BRG-009', 'Oreo Biscuit Selection Box 286g', 46000, 'box', 'dus', 12, 40000, 'Biskuit & Kue', NULL, 1, '2026-11-04 13:33:22', '2026-11-04 13:33:22');
INSERT INTO `items` (`id`, `code`, `name`, `unit_price`, `base_unit`, `wholesale_unit`, `conversion_rate`, `cost_price`, `category`, `image_path`, `is_active`, `created_at`, `updated_at`) VALUES (60, 'BRG-010', 'Serena Cheese Cookies 454g', 68000, 'kaleng', 'dus', 6, 60000, 'Biskuit & Kue', NULL, 1, '2026-11-04 13:33:22', '2026-11-04 13:33:22');
INSERT INTO `items` (`id`, `code`, `name`, `unit_price`, `base_unit`, `wholesale_unit`, `conversion_rate`, `cost_price`, `category`, `image_path`, `is_active`, `created_at`, `updated_at`) VALUES (61, 'BRG-011', 'Sirup Marjan Boudoin Cocopandan 460ml', 24000, 'botol', 'dus', 12, 20500, 'Sirup & Minuman', NULL, 1, '2026-11-04 13:33:22', '2026-11-04 13:33:22');
INSERT INTO `items` (`id`, `code`, `name`, `unit_price`, `base_unit`, `wholesale_unit`, `conversion_rate`, `cost_price`, `category`, `image_path`, `is_active`, `created_at`, `updated_at`) VALUES (62, 'BRG-012', 'Sirup Marjan Boudoin Melon 460ml', 24000, 'botol', 'dus', 12, 20500, 'Sirup & Minuman', NULL, 1, '2026-11-04 13:33:23', '2026-11-04 13:33:23');
INSERT INTO `items` (`id`, `code`, `name`, `unit_price`, `base_unit`, `wholesale_unit`, `conversion_rate`, `cost_price`, `category`, `image_path`, `is_active`, `created_at`, `updated_at`) VALUES (63, 'BRG-013', 'Sirup ABC Squash Delight Jeruk 450ml', 16000, 'botol', 'dus', 12, 13500, 'Sirup & Minuman', NULL, 1, '2026-11-04 13:33:23', '2026-11-04 13:33:23');
INSERT INTO `items` (`id`, `code`, `name`, `unit_price`, `base_unit`, `wholesale_unit`, `conversion_rate`, `cost_price`, `category`, `image_path`, `is_active`, `created_at`, `updated_at`) VALUES (64, 'BRG-014', 'Sirup ABC Squash Delight Mangga 450ml', 16000, 'botol', 'dus', 12, 13500, 'Sirup & Minuman', NULL, 1, '2026-11-04 13:33:23', '2026-11-04 13:33:23');
INSERT INTO `items` (`id`, `code`, `name`, `unit_price`, `base_unit`, `wholesale_unit`, `conversion_rate`, `cost_price`, `category`, `image_path`, `is_active`, `created_at`, `updated_at`) VALUES (65, 'BRG-015', 'Susu Kental Manis Frisian Flag Gold 370g', 18000, 'kaleng', 'karton', 48, 15500, 'Sirup & Minuman', NULL, 1, '2026-11-04 13:33:23', '2026-11-04 13:33:23');
INSERT INTO `items` (`id`, `code`, `name`, `unit_price`, `base_unit`, `wholesale_unit`, `conversion_rate`, `cost_price`, `category`, `image_path`, `is_active`, `created_at`, `updated_at`) VALUES (66, 'BRG-016', 'Susu Kental Manis Indomilk Cokelat 370g', 15000, 'kaleng', 'karton', 48, 13000, 'Sirup & Minuman', NULL, 1, '2026-11-04 13:33:23', '2026-11-04 13:33:23');
INSERT INTO `items` (`id`, `code`, `name`, `unit_price`, `base_unit`, `wholesale_unit`, `conversion_rate`, `cost_price`, `category`, `image_path`, `is_active`, `created_at`, `updated_at`) VALUES (67, 'BRG-017', 'Nata De Coco Kara Crispy 1000g', 16500, 'pouch', 'dus', 12, 14000, 'Sirup & Minuman', NULL, 1, '2026-11-04 13:33:23', '2026-11-04 13:33:23');
INSERT INTO `items` (`id`, `code`, `name`, `unit_price`, `base_unit`, `wholesale_unit`, `conversion_rate`, `cost_price`, `category`, `image_path`, `is_active`, `created_at`, `updated_at`) VALUES (68, 'BRG-018', 'Nata De Coco Wong Coco 1000g', 18500, 'pouch', 'dus', 12, 15500, 'Sirup & Minuman', NULL, 1, '2026-11-04 13:33:23', '2026-11-04 13:33:23');
INSERT INTO `items` (`id`, `code`, `name`, `unit_price`, `base_unit`, `wholesale_unit`, `conversion_rate`, `cost_price`, `category`, `image_path`, `is_active`, `created_at`, `updated_at`) VALUES (69, 'BRG-019', 'Teh Botol Sosro Kotak 250ml', 4000, 'kotak', 'karton', 24, 3300, 'Sirup & Minuman', NULL, 1, '2026-11-04 13:33:23', '2026-11-04 13:33:23');
INSERT INTO `items` (`id`, `code`, `name`, `unit_price`, `base_unit`, `wholesale_unit`, `conversion_rate`, `cost_price`, `category`, `image_path`, `is_active`, `created_at`, `updated_at`) VALUES (70, 'BRG-020', 'Buavita Jus Jeruk 1000ml', 28000, 'kotak', 'dus', 12, 24000, 'Sirup & Minuman', NULL, 1, '2026-11-04 13:33:24', '2026-11-04 13:33:24');
INSERT INTO `items` (`id`, `code`, `name`, `unit_price`, `base_unit`, `wholesale_unit`, `conversion_rate`, `cost_price`, `category`, `image_path`, `is_active`, `created_at`, `updated_at`) VALUES (71, 'BRG-021', 'Minyak Goreng Sania 2 Liter', 36000, 'pouch', 'dus', 6, 32000, 'Sembako', NULL, 1, '2026-11-04 13:33:24', '2026-11-04 13:33:24');
INSERT INTO `items` (`id`, `code`, `name`, `unit_price`, `base_unit`, `wholesale_unit`, `conversion_rate`, `cost_price`, `category`, `image_path`, `is_active`, `created_at`, `updated_at`) VALUES (72, 'BRG-022', 'Minyak Goreng Tropical 2 Liter', 37000, 'botol', 'dus', 6, 33000, 'Sembako', NULL, 1, '2026-11-04 13:33:24', '2026-11-04 13:33:24');
INSERT INTO `items` (`id`, `code`, `name`, `unit_price`, `base_unit`, `wholesale_unit`, `conversion_rate`, `cost_price`, `category`, `image_path`, `is_active`, `created_at`, `updated_at`) VALUES (73, 'BRG-023', 'Beras Setra Ramos Super 5 Kg', 75000, 'karung', 'bal', 5, 68000, 'Sembako', NULL, 1, '2026-11-04 13:33:24', '2026-11-04 13:33:24');
INSERT INTO `items` (`id`, `code`, `name`, `unit_price`, `base_unit`, `wholesale_unit`, `conversion_rate`, `cost_price`, `category`, `image_path`, `is_active`, `created_at`, `updated_at`) VALUES (74, 'BRG-024', 'Gula Pasir Kristal Putih Gulaku 1 Kg', 18500, 'pcs', 'dus', 24, 16500, 'Sembako', NULL, 1, '2026-11-04 13:33:24', '2026-11-04 13:33:24');
INSERT INTO `items` (`id`, `code`, `name`, `unit_price`, `base_unit`, `wholesale_unit`, `conversion_rate`, `cost_price`, `category`, `image_path`, `is_active`, `created_at`, `updated_at`) VALUES (75, 'BRG-025', 'Gula Pasir Rose Brand 1 Kg', 18000, 'pcs', 'dus', 24, 16000, 'Sembako', NULL, 1, '2026-11-04 13:33:24', '2026-11-04 13:33:24');
INSERT INTO `items` (`id`, `code`, `name`, `unit_price`, `base_unit`, `wholesale_unit`, `conversion_rate`, `cost_price`, `category`, `image_path`, `is_active`, `created_at`, `updated_at`) VALUES (76, 'BRG-026', 'Tepung Terigu Segitiga Biru 1 Kg', 14000, 'pcs', 'dus', 12, 12000, 'Sembako', NULL, 1, '2026-11-04 13:33:24', '2026-11-04 13:33:24');
INSERT INTO `items` (`id`, `code`, `name`, `unit_price`, `base_unit`, `wholesale_unit`, `conversion_rate`, `cost_price`, `category`, `image_path`, `is_active`, `created_at`, `updated_at`) VALUES (77, 'BRG-027', 'Margarin Blue Band Serbaguna 200g', 11000, 'sachet', 'dus', 60, 9200, 'Sembako', NULL, 1, '2026-11-04 13:33:25', '2026-11-04 13:33:25');
INSERT INTO `items` (`id`, `code`, `name`, `unit_price`, `base_unit`, `wholesale_unit`, `conversion_rate`, `cost_price`, `category`, `image_path`, `is_active`, `created_at`, `updated_at`) VALUES (78, 'BRG-028', 'Kecap Manis Bango Refill 520ml', 24500, 'pouch', 'dus', 12, 21500, 'Sembako', NULL, 1, '2026-11-04 13:33:25', '2026-11-04 13:33:25');
INSERT INTO `items` (`id`, `code`, `name`, `unit_price`, `base_unit`, `wholesale_unit`, `conversion_rate`, `cost_price`, `category`, `image_path`, `is_active`, `created_at`, `updated_at`) VALUES (79, 'BRG-029', 'Saus Sambal ABC Extra Pedas 335ml', 17000, 'botol', 'dus', 24, 14500, 'Sembako', NULL, 1, '2026-11-04 13:33:25', '2026-11-04 13:33:25');
INSERT INTO `items` (`id`, `code`, `name`, `unit_price`, `base_unit`, `wholesale_unit`, `conversion_rate`, `cost_price`, `category`, `image_path`, `is_active`, `created_at`, `updated_at`) VALUES (80, 'BRG-030', 'Garam Dapur Beriodium Cap Kapal 250g', 3500, 'pcs', 'bal', 40, 2800, 'Sembako', NULL, 1, '2026-11-04 13:33:25', '2026-11-04 13:33:25');
INSERT INTO `items` (`id`, `code`, `name`, `unit_price`, `base_unit`, `wholesale_unit`, `conversion_rate`, `cost_price`, `category`, `image_path`, `is_active`, `created_at`, `updated_at`) VALUES (81, 'BRG-031', 'Kopi Kapal Api Spesial Mix 20s', 26000, 'bag', 'dus', 10, 22000, 'Kopi & Teh', NULL, 1, '2026-11-04 13:33:25', '2026-11-04 13:33:25');
INSERT INTO `items` (`id`, `code`, `name`, `unit_price`, `base_unit`, `wholesale_unit`, `conversion_rate`, `cost_price`, `category`, `image_path`, `is_active`, `created_at`, `updated_at`) VALUES (82, 'BRG-032', 'Kopi Luwak White Koffie 20s', 25000, 'bag', 'dus', 10, 21000, 'Kopi & Teh', NULL, 1, '2026-11-04 13:33:25', '2026-11-04 13:33:25');
INSERT INTO `items` (`id`, `code`, `name`, `unit_price`, `base_unit`, `wholesale_unit`, `conversion_rate`, `cost_price`, `category`, `image_path`, `is_active`, `created_at`, `updated_at`) VALUES (83, 'BRG-033', 'Teh Celup Sariwangi Kotak 50s', 14000, 'box', 'karton', 24, 11500, 'Kopi & Teh', NULL, 1, '2026-11-04 13:33:25', '2026-11-04 13:33:25');
INSERT INTO `items` (`id`, `code`, `name`, `unit_price`, `base_unit`, `wholesale_unit`, `conversion_rate`, `cost_price`, `category`, `image_path`, `is_active`, `created_at`, `updated_at`) VALUES (84, 'BRG-034', 'Teh Celup Sosro Kotak 50s', 12500, 'box', 'karton', 24, 10500, 'Kopi & Teh', NULL, 1, '2026-11-04 13:33:25', '2026-11-04 13:33:25');
INSERT INTO `items` (`id`, `code`, `name`, `unit_price`, `base_unit`, `wholesale_unit`, `conversion_rate`, `cost_price`, `category`, `image_path`, `is_active`, `created_at`, `updated_at`) VALUES (85, 'BRG-035', 'Cokelat Bubuk Van Houten 100g', 28000, 'box', 'dus', 12, 24000, 'Kopi & Teh', NULL, 1, '2026-11-04 13:33:25', '2026-11-04 13:33:25');
INSERT INTO `items` (`id`, `code`, `name`, `unit_price`, `base_unit`, `wholesale_unit`, `conversion_rate`, `cost_price`, `category`, `image_path`, `is_active`, `created_at`, `updated_at`) VALUES (86, 'BRG-036', 'Kurma Khalas Barari 500g', 42000, 'box', 'dus', 12, 35000, 'Khas Lebaran', NULL, 1, '2026-11-04 13:33:26', '2026-11-04 13:33:26');
INSERT INTO `items` (`id`, `code`, `name`, `unit_price`, `base_unit`, `wholesale_unit`, `conversion_rate`, `cost_price`, `category`, `image_path`, `is_active`, `created_at`, `updated_at`) VALUES (87, 'BRG-037', 'Kurma Tunis Madu Barari 500g', 52000, 'box', 'dus', 12, 44000, 'Khas Lebaran', NULL, 1, '2026-11-04 13:33:26', '2026-11-04 13:33:26');
INSERT INTO `items` (`id`, `code`, `name`, `unit_price`, `base_unit`, `wholesale_unit`, `conversion_rate`, `cost_price`, `category`, `image_path`, `is_active`, `created_at`, `updated_at`) VALUES (88, 'BRG-038', 'Permen Foxs Crystal Clear 180g', 12000, 'kaleng', 'dus', 24, 9800, 'Khas Lebaran', NULL, 1, '2026-11-04 13:33:26', '2026-11-04 13:33:26');
INSERT INTO `items` (`id`, `code`, `name`, `unit_price`, `base_unit`, `wholesale_unit`, `conversion_rate`, `cost_price`, `category`, `image_path`, `is_active`, `created_at`, `updated_at`) VALUES (89, 'BRG-039', 'Kacang Mede Oven Asin Gurih 250g', 48000, 'toples', 'dus', 12, 41000, 'Khas Lebaran', NULL, 1, '2026-11-04 13:33:26', '2026-11-04 13:33:26');
INSERT INTO `items` (`id`, `code`, `name`, `unit_price`, `base_unit`, `wholesale_unit`, `conversion_rate`, `cost_price`, `category`, `image_path`, `is_active`, `created_at`, `updated_at`) VALUES (90, 'BRG-040', 'Kacang Telur Garuda 220g', 16000, 'bungkus', 'dus', 20, 13500, 'Khas Lebaran', NULL, 1, '2026-11-04 13:33:26', '2026-11-04 13:33:26');
INSERT INTO `items` (`id`, `code`, `name`, `unit_price`, `base_unit`, `wholesale_unit`, `conversion_rate`, `cost_price`, `category`, `image_path`, `is_active`, `created_at`, `updated_at`) VALUES (91, 'BRG-041', 'Chocolatos Wafer Roll Kaleng 350g', 35000, 'kaleng', 'dus', 6, 30000, 'Camilan', NULL, 1, '2026-11-04 13:33:26', '2026-11-04 13:33:26');
INSERT INTO `items` (`id`, `code`, `name`, `unit_price`, `base_unit`, `wholesale_unit`, `conversion_rate`, `cost_price`, `category`, `image_path`, `is_active`, `created_at`, `updated_at`) VALUES (92, 'BRG-042', 'Twister Wafer Roll Vanila 300g', 32000, 'kaleng', 'dus', 6, 27500, 'Camilan', NULL, 1, '2026-11-04 13:33:27', '2026-11-04 13:33:27');
INSERT INTO `items` (`id`, `code`, `name`, `unit_price`, `base_unit`, `wholesale_unit`, `conversion_rate`, `cost_price`, `category`, `image_path`, `is_active`, `created_at`, `updated_at`) VALUES (93, 'BRG-043', 'Selamat Sandwich Wafer Chocolate 340g', 38000, 'kaleng', 'dus', 6, 32500, 'Camilan', NULL, 1, '2026-11-04 13:33:27', '2026-11-04 13:33:27');
INSERT INTO `items` (`id`, `code`, `name`, `unit_price`, `base_unit`, `wholesale_unit`, `conversion_rate`, `cost_price`, `category`, `image_path`, `is_active`, `created_at`, `updated_at`) VALUES (94, 'BRG-044', 'Mie Telur Cap Atom 200g', 6500, 'bungkus', 'dus', 20, 5200, 'Sembako', NULL, 1, '2026-11-04 13:33:27', '2026-11-04 13:33:27');
INSERT INTO `items` (`id`, `code`, `name`, `unit_price`, `base_unit`, `wholesale_unit`, `conversion_rate`, `cost_price`, `category`, `image_path`, `is_active`, `created_at`, `updated_at`) VALUES (95, 'BRG-045', 'Sarden ABC Saus Tomat 425g', 26000, 'kaleng', 'karton', 24, 22500, 'Sembako', NULL, 1, '2026-11-04 13:33:27', '2026-11-04 13:33:27');
INSERT INTO `items` (`id`, `code`, `name`, `unit_price`, `base_unit`, `wholesale_unit`, `conversion_rate`, `cost_price`, `category`, `image_path`, `is_active`, `created_at`, `updated_at`) VALUES (96, 'BRG-046', 'Kornet Sapi Pronas 340g', 34000, 'kaleng', 'dus', 24, 29000, 'Sembako', NULL, 1, '2026-11-04 13:33:27', '2026-11-04 13:33:27');
INSERT INTO `items` (`id`, `code`, `name`, `unit_price`, `base_unit`, `wholesale_unit`, `conversion_rate`, `cost_price`, `category`, `image_path`, `is_active`, `created_at`, `updated_at`) VALUES (97, 'BRG-047', 'Keju Kraft All In One 165g', 16500, 'box', 'dus', 24, 14000, 'Biskuit & Kue', NULL, 1, '2026-11-04 13:33:27', '2026-11-04 13:33:27');
INSERT INTO `items` (`id`, `code`, `name`, `unit_price`, `base_unit`, `wholesale_unit`, `conversion_rate`, `cost_price`, `category`, `image_path`, `is_active`, `created_at`, `updated_at`) VALUES (98, 'BRG-048', 'Meses Meises Ceres Classic 225g', 23000, 'box', 'dus', 12, 19500, 'Biskuit & Kue', NULL, 1, '2026-11-04 13:33:27', '2026-11-04 13:33:27');
INSERT INTO `items` (`id`, `code`, `name`, `unit_price`, `base_unit`, `wholesale_unit`, `conversion_rate`, `cost_price`, `category`, `image_path`, `is_active`, `created_at`, `updated_at`) VALUES (99, 'BRG-049', 'Agar-agar Swallow Globe Merah', 5000, 'sachet', 'pak', 12, 4100, 'Khas Lebaran', NULL, 1, '2026-11-04 13:33:28', '2026-11-04 13:33:28');
INSERT INTO `items` (`id`, `code`, `name`, `unit_price`, `base_unit`, `wholesale_unit`, `conversion_rate`, `cost_price`, `category`, `image_path`, `is_active`, `created_at`, `updated_at`) VALUES (100, 'BRG-050', 'Puding Nutrijell Cokelat 145g', 8500, 'box', 'dus', 12, 7000, 'Khas Lebaran', NULL, 1, '2026-11-04 13:33:28', '2026-11-04 13:33:28');

CREATE TABLE IF NOT EXISTS `packages` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `code` varchar(50) DEFAULT NULL UNIQUE,
  `name` varchar(255) NOT NULL,
  `type` enum('parsel','tabungan','ecer') NOT NULL DEFAULT 'parsel',
  `target_nominal` decimal(14,2) NOT NULL DEFAULT 0.00,
  `installment_count` int(11) NOT NULL DEFAULT 30,
  `installment_amount` decimal(14,2) NOT NULL DEFAULT 0.00,
  `admin_fee` decimal(14,2) NOT NULL DEFAULT 0.00,
  `period_type` varchar(50) NOT NULL DEFAULT 'mingguan',
  `description` text DEFAULT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `packages` (`id`, `code`, `name`, `type`, `target_nominal`, `installment_count`, `installment_amount`, `admin_fee`, `period_type`, `description`, `image_path`, `is_active`, `created_at`, `updated_at`) VALUES (9, 'PAR-001', 'Paket Parsel Mewah Sultan (Paket A)', 'parsel', 750000, 30, 25000, 0, 'mingguan', 'Paket parsel eksklusif lengkap biskuit kaleng premium, sirup marjan, kurma tunis, dan sembako berkualitas.', 'uploads/packages/sample_parsel_a.jpg', 1, '2026-11-04 13:33:28', '2026-11-04 13:33:28');
INSERT INTO `packages` (`id`, `code`, `name`, `type`, `target_nominal`, `installment_count`, `installment_amount`, `admin_fee`, `period_type`, `description`, `image_path`, `is_active`, `created_at`, `updated_at`) VALUES (10, 'PAR-002', 'Paket Parsel Berkah Ramadhan (Paket B)', 'parsel', 450000, 30, 15000, 0, 'mingguan', 'Paket favorit keluarga berisi biskuit kaleng, sirup, kurma, dan sembako harian.', 'uploads/packages/sample_parsel_b.jpg', 1, '2026-11-04 13:33:29', '2026-11-04 13:33:29');
INSERT INTO `packages` (`id`, `code`, `name`, `type`, `target_nominal`, `installment_count`, `installment_amount`, `admin_fee`, `period_type`, `description`, `image_path`, `is_active`, `created_at`, `updated_at`) VALUES (11, 'PAR-003', 'Paket Parsel Sembako Manis (Paket C)', 'parsel', 300000, 30, 10000, 0, 'mingguan', 'Parsel hemat fokus kebutuhan pokok sembako Lebaran keluarga praktis.', 'uploads/packages/sample_parsel_c.jpg', 1, '2026-11-04 13:33:31', '2026-11-04 13:33:31');
INSERT INTO `packages` (`id`, `code`, `name`, `type`, `target_nominal`, `installment_count`, `installment_amount`, `admin_fee`, `period_type`, `description`, `image_path`, `is_active`, `created_at`, `updated_at`) VALUES (12, 'PAR-004', 'Paket Parsel Mini Ceria (Paket D)', 'parsel', 150000, 30, 5000, 0, 'mingguan', 'Parsel mini ramah kantong cocok untuk kerabat dan hadiah tetangga.', NULL, 1, '2026-11-04 13:33:32', '2026-11-04 13:33:32');
INSERT INTO `packages` (`id`, `code`, `name`, `type`, `target_nominal`, `installment_count`, `installment_amount`, `admin_fee`, `period_type`, `description`, `image_path`, `is_active`, `created_at`, `updated_at`) VALUES (13, 'TAB-001', 'Paket Tabungan Lebaran Emas (Rp 1.5 Juta)', 'tabungan', 1500000, 30, 50000, 0, 'mingguan', 'Nabung Rp 50.000/minggu. Saat Lebaran cair uang tunai Rp 1.500.000 + GRATIS BONUS BARANG (Khong Guan & Marjan).', 'uploads/packages/sample_tabungan_1.jpg', 1, '2026-11-04 13:33:33', '2026-11-04 13:33:33');
INSERT INTO `packages` (`id`, `code`, `name`, `type`, `target_nominal`, `installment_count`, `installment_amount`, `admin_fee`, `period_type`, `description`, `image_path`, `is_active`, `created_at`, `updated_at`) VALUES (14, 'TAB-002', 'Paket Tabungan Lebaran Perak (Rp 900 Ribu)', 'tabungan', 900000, 30, 30000, 0, 'mingguan', 'Nabung Rp 30.000/minggu. Cair Rp 900.000 utuh + BONUS BARANG Wafer Tango & Sirup Marjan.', NULL, 1, '2026-11-04 13:33:33', '2026-11-04 13:33:33');
INSERT INTO `packages` (`id`, `code`, `name`, `type`, `target_nominal`, `installment_count`, `installment_amount`, `admin_fee`, `period_type`, `description`, `image_path`, `is_active`, `created_at`, `updated_at`) VALUES (15, 'ECR-001', 'Paket Eceran Rakitan Mandiri (Custom Parsel)', 'ecer', 0, 30, 0, 0, 'mingguan', 'Konsumen bebas merakit parselnya sendiri dari 50 daftar barang pilihan. Total nominal dibagi 30 minggu tagihan.', NULL, 1, '2026-11-04 13:33:34', '2026-11-04 13:33:34');

CREATE TABLE IF NOT EXISTS `package_items` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `package_id` bigint(20) UNSIGNED NOT NULL,
  `item_id` bigint(20) UNSIGNED NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `is_bonus` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `package_items` (`id`, `package_id`, `item_id`, `quantity`, `is_bonus`, `created_at`, `updated_at`) VALUES (40, 9, 51, 1, 0, '2026-11-04 13:33:28', '2026-11-04 13:33:28');
INSERT INTO `package_items` (`id`, `package_id`, `item_id`, `quantity`, `is_bonus`, `created_at`, `updated_at`) VALUES (41, 9, 52, 1, 0, '2026-11-04 13:33:28', '2026-11-04 13:33:28');
INSERT INTO `package_items` (`id`, `package_id`, `item_id`, `quantity`, `is_bonus`, `created_at`, `updated_at`) VALUES (42, 9, 61, 2, 0, '2026-11-04 13:33:28', '2026-11-04 13:33:28');
INSERT INTO `package_items` (`id`, `package_id`, `item_id`, `quantity`, `is_bonus`, `created_at`, `updated_at`) VALUES (43, 9, 71, 2, 0, '2026-11-04 13:33:28', '2026-11-04 13:33:28');
INSERT INTO `package_items` (`id`, `package_id`, `item_id`, `quantity`, `is_bonus`, `created_at`, `updated_at`) VALUES (44, 9, 73, 1, 0, '2026-11-04 13:33:28', '2026-11-04 13:33:28');
INSERT INTO `package_items` (`id`, `package_id`, `item_id`, `quantity`, `is_bonus`, `created_at`, `updated_at`) VALUES (45, 9, 74, 2, 0, '2026-11-04 13:33:29', '2026-11-04 13:33:29');
INSERT INTO `package_items` (`id`, `package_id`, `item_id`, `quantity`, `is_bonus`, `created_at`, `updated_at`) VALUES (46, 9, 87, 1, 0, '2026-11-04 13:33:29', '2026-11-04 13:33:29');
INSERT INTO `package_items` (`id`, `package_id`, `item_id`, `quantity`, `is_bonus`, `created_at`, `updated_at`) VALUES (47, 9, 81, 1, 0, '2026-11-04 13:33:29', '2026-11-04 13:33:29');
INSERT INTO `package_items` (`id`, `package_id`, `item_id`, `quantity`, `is_bonus`, `created_at`, `updated_at`) VALUES (48, 9, 83, 2, 0, '2026-11-04 13:33:29', '2026-11-04 13:33:29');
INSERT INTO `package_items` (`id`, `package_id`, `item_id`, `quantity`, `is_bonus`, `created_at`, `updated_at`) VALUES (49, 9, 56, 1, 0, '2026-11-04 13:33:29', '2026-11-04 13:33:29');
INSERT INTO `package_items` (`id`, `package_id`, `item_id`, `quantity`, `is_bonus`, `created_at`, `updated_at`) VALUES (50, 9, 68, 2, 0, '2026-11-04 13:33:29', '2026-11-04 13:33:29');
INSERT INTO `package_items` (`id`, `package_id`, `item_id`, `quantity`, `is_bonus`, `created_at`, `updated_at`) VALUES (51, 9, 89, 1, 0, '2026-11-04 13:33:29', '2026-11-04 13:33:29');
INSERT INTO `package_items` (`id`, `package_id`, `item_id`, `quantity`, `is_bonus`, `created_at`, `updated_at`) VALUES (52, 10, 53, 1, 0, '2026-11-04 13:33:30', '2026-11-04 13:33:30');
INSERT INTO `package_items` (`id`, `package_id`, `item_id`, `quantity`, `is_bonus`, `created_at`, `updated_at`) VALUES (53, 10, 55, 1, 0, '2026-11-04 13:33:30', '2026-11-04 13:33:30');
INSERT INTO `package_items` (`id`, `package_id`, `item_id`, `quantity`, `is_bonus`, `created_at`, `updated_at`) VALUES (54, 10, 62, 2, 0, '2026-11-04 13:33:30', '2026-11-04 13:33:30');
INSERT INTO `package_items` (`id`, `package_id`, `item_id`, `quantity`, `is_bonus`, `created_at`, `updated_at`) VALUES (55, 10, 72, 1, 0, '2026-11-04 13:33:30', '2026-11-04 13:33:30');
INSERT INTO `package_items` (`id`, `package_id`, `item_id`, `quantity`, `is_bonus`, `created_at`, `updated_at`) VALUES (56, 10, 75, 2, 0, '2026-11-04 13:33:30', '2026-11-04 13:33:30');
INSERT INTO `package_items` (`id`, `package_id`, `item_id`, `quantity`, `is_bonus`, `created_at`, `updated_at`) VALUES (57, 10, 86, 1, 0, '2026-11-04 13:33:30', '2026-11-04 13:33:30');
INSERT INTO `package_items` (`id`, `package_id`, `item_id`, `quantity`, `is_bonus`, `created_at`, `updated_at`) VALUES (58, 10, 84, 2, 0, '2026-11-04 13:33:30', '2026-11-04 13:33:30');
INSERT INTO `package_items` (`id`, `package_id`, `item_id`, `quantity`, `is_bonus`, `created_at`, `updated_at`) VALUES (59, 10, 67, 1, 0, '2026-11-04 13:33:30', '2026-11-04 13:33:30');
INSERT INTO `package_items` (`id`, `package_id`, `item_id`, `quantity`, `is_bonus`, `created_at`, `updated_at`) VALUES (60, 10, 78, 1, 0, '2026-11-04 13:33:31', '2026-11-04 13:33:31');
INSERT INTO `package_items` (`id`, `package_id`, `item_id`, `quantity`, `is_bonus`, `created_at`, `updated_at`) VALUES (61, 10, 88, 1, 0, '2026-11-04 13:33:31', '2026-11-04 13:33:31');
INSERT INTO `package_items` (`id`, `package_id`, `item_id`, `quantity`, `is_bonus`, `created_at`, `updated_at`) VALUES (62, 11, 71, 1, 0, '2026-11-04 13:33:31', '2026-11-04 13:33:31');
INSERT INTO `package_items` (`id`, `package_id`, `item_id`, `quantity`, `is_bonus`, `created_at`, `updated_at`) VALUES (63, 11, 75, 2, 0, '2026-11-04 13:33:31', '2026-11-04 13:33:31');
INSERT INTO `package_items` (`id`, `package_id`, `item_id`, `quantity`, `is_bonus`, `created_at`, `updated_at`) VALUES (64, 11, 63, 2, 0, '2026-11-04 13:33:31', '2026-11-04 13:33:31');
INSERT INTO `package_items` (`id`, `package_id`, `item_id`, `quantity`, `is_bonus`, `created_at`, `updated_at`) VALUES (65, 11, 83, 1, 0, '2026-11-04 13:33:31', '2026-11-04 13:33:31');
INSERT INTO `package_items` (`id`, `package_id`, `item_id`, `quantity`, `is_bonus`, `created_at`, `updated_at`) VALUES (66, 11, 76, 2, 0, '2026-11-04 13:33:32', '2026-11-04 13:33:32');
INSERT INTO `package_items` (`id`, `package_id`, `item_id`, `quantity`, `is_bonus`, `created_at`, `updated_at`) VALUES (67, 11, 77, 2, 0, '2026-11-04 13:33:32', '2026-11-04 13:33:32');
INSERT INTO `package_items` (`id`, `package_id`, `item_id`, `quantity`, `is_bonus`, `created_at`, `updated_at`) VALUES (68, 11, 65, 2, 0, '2026-11-04 13:33:32', '2026-11-04 13:33:32');
INSERT INTO `package_items` (`id`, `package_id`, `item_id`, `quantity`, `is_bonus`, `created_at`, `updated_at`) VALUES (69, 11, 91, 1, 0, '2026-11-04 13:33:32', '2026-11-04 13:33:32');
INSERT INTO `package_items` (`id`, `package_id`, `item_id`, `quantity`, `is_bonus`, `created_at`, `updated_at`) VALUES (70, 12, 63, 1, 0, '2026-11-04 13:33:32', '2026-11-04 13:33:32');
INSERT INTO `package_items` (`id`, `package_id`, `item_id`, `quantity`, `is_bonus`, `created_at`, `updated_at`) VALUES (71, 12, 91, 1, 0, '2026-11-04 13:33:32', '2026-11-04 13:33:32');
INSERT INTO `package_items` (`id`, `package_id`, `item_id`, `quantity`, `is_bonus`, `created_at`, `updated_at`) VALUES (72, 12, 75, 1, 0, '2026-11-04 13:33:33', '2026-11-04 13:33:33');
INSERT INTO `package_items` (`id`, `package_id`, `item_id`, `quantity`, `is_bonus`, `created_at`, `updated_at`) VALUES (73, 12, 71, 1, 0, '2026-11-04 13:33:33', '2026-11-04 13:33:33');
INSERT INTO `package_items` (`id`, `package_id`, `item_id`, `quantity`, `is_bonus`, `created_at`, `updated_at`) VALUES (74, 12, 84, 1, 0, '2026-11-04 13:33:33', '2026-11-04 13:33:33');
INSERT INTO `package_items` (`id`, `package_id`, `item_id`, `quantity`, `is_bonus`, `created_at`, `updated_at`) VALUES (75, 13, 51, 1, 1, '2026-11-04 13:33:33', '2026-11-04 13:33:33');
INSERT INTO `package_items` (`id`, `package_id`, `item_id`, `quantity`, `is_bonus`, `created_at`, `updated_at`) VALUES (76, 13, 61, 2, 1, '2026-11-04 13:33:33', '2026-11-04 13:33:33');
INSERT INTO `package_items` (`id`, `package_id`, `item_id`, `quantity`, `is_bonus`, `created_at`, `updated_at`) VALUES (77, 14, 53, 1, 1, '2026-11-04 13:33:33', '2026-11-04 13:33:33');
INSERT INTO `package_items` (`id`, `package_id`, `item_id`, `quantity`, `is_bonus`, `created_at`, `updated_at`) VALUES (78, 14, 62, 1, 1, '2026-11-04 13:33:34', '2026-11-04 13:33:34');

CREATE TABLE IF NOT EXISTS `customers` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `agent_id` bigint(20) UNSIGNED NOT NULL,
  `code` varchar(50) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `customers` (`id`, `agent_id`, `code`, `name`, `phone`, `address`, `notes`, `is_active`, `created_at`, `updated_at`) VALUES (6, 4, 'KNS-2610-001', 'Ibu Anisa Rahmawati', '0812-1111-2222', 'Jl. Mawar No. 12 RT 02/04, Paseh', 'Langganan setia tiap tahun', 1, '2026-11-04 13:33:34', '2026-11-04 13:33:34');
INSERT INTO `customers` (`id`, `agent_id`, `code`, `name`, `phone`, `address`, `notes`, `is_active`, `created_at`, `updated_at`) VALUES (7, 4, 'KNS-2610-002', 'Bapak Hendra Wijaya', '0857-3333-4444', 'Gg. Kenanga No. 5 RT 01/02', 'Sering dinas luar kota', 1, '2026-11-04 13:33:39', '2026-11-04 13:33:39');
INSERT INTO `customers` (`id`, `agent_id`, `code`, `name`, `phone`, `address`, `notes`, `is_active`, `created_at`, `updated_at`) VALUES (8, 5, 'KNS-2610-003', 'Ibu Dewi Sartika', '0878-7777-9999', 'Komplek Griya Indah Blok B2 No. 8', 'Mengajukan penyesuaian parsel sesuai saldo setorannya', 1, '2026-11-04 13:33:43', '2026-11-04 13:33:43');
INSERT INTO `customers` (`id`, `agent_id`, `code`, `name`, `phone`, `address`, `notes`, `is_active`, `created_at`, `updated_at`) VALUES (9, 5, 'KNS-2610-004', 'Bapak Rahmat Hidayat', '0896-1234-5678', 'Kp. Babakan Desa Sukamaju', 'Memilih sendiri barang parsel sesuai selera keluarga', 1, '2026-11-04 13:33:48', '2026-11-04 13:33:48');

CREATE TABLE IF NOT EXISTS `customer_packages` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `customer_id` bigint(20) UNSIGNED NOT NULL,
  `package_id` bigint(20) UNSIGNED NOT NULL,
  `agent_id` bigint(20) UNSIGNED NOT NULL,
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
  `fulfillment_notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `customer_packages` (`id`, `customer_id`, `package_id`, `agent_id`, `start_date`, `total_target`, `total_paid`, `status`, `is_bonus_forfeited`, `forfeit_reason`, `consecutive_unpaid_weeks`, `cancellation_fee`, `refund_amount`, `fulfillment_status`, `fulfillment_notes`, `created_at`, `updated_at`) VALUES (7, 6, 9, 4, '2026-08-26 13:33:34', 750000, 250000, 'active', 0, NULL, 0, 0, 0, 'pending', NULL, '2026-11-04 13:33:34', '2026-11-04 13:33:34');
INSERT INTO `customer_packages` (`id`, `customer_id`, `package_id`, `agent_id`, `start_date`, `total_target`, `total_paid`, `status`, `is_bonus_forfeited`, `forfeit_reason`, `consecutive_unpaid_weeks`, `cancellation_fee`, `refund_amount`, `fulfillment_status`, `fulfillment_notes`, `created_at`, `updated_at`) VALUES (8, 7, 13, 4, '2026-08-26 13:33:39', 1500000, 300000, 'active', 1, 'Tidak menyetor 4 minggu berturut-turut (Minggu 7 s.d 10 bolong). Sesuai aturan SPL, bonus barang hangus.', 4, 0, 0, 'pending', NULL, '2026-11-04 13:33:39', '2026-11-04 13:33:39');
INSERT INTO `customer_packages` (`id`, `customer_id`, `package_id`, `agent_id`, `start_date`, `total_target`, `total_paid`, `status`, `is_bonus_forfeited`, `forfeit_reason`, `consecutive_unpaid_weeks`, `cancellation_fee`, `refund_amount`, `fulfillment_status`, `fulfillment_notes`, `created_at`, `updated_at`) VALUES (9, 8, 10, 5, '2026-08-26 13:33:43', 450000, 150000, 'active', 0, NULL, 0, 0, 0, 'pending', NULL, '2026-11-04 13:33:43', '2026-11-04 13:33:43');
INSERT INTO `customer_packages` (`id`, `customer_id`, `package_id`, `agent_id`, `start_date`, `total_target`, `total_paid`, `status`, `is_bonus_forfeited`, `forfeit_reason`, `consecutive_unpaid_weeks`, `cancellation_fee`, `refund_amount`, `fulfillment_status`, `fulfillment_notes`, `created_at`, `updated_at`) VALUES (10, 9, 15, 5, '2026-08-26 13:33:48', 235000, 78340, 'active', 0, NULL, 0, 0, 0, 'pending', NULL, '2026-11-04 13:33:48', '2026-11-04 13:33:48');

CREATE TABLE IF NOT EXISTS `customer_custom_items` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `customer_package_id` bigint(20) UNSIGNED NOT NULL,
  `item_id` bigint(20) UNSIGNED NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `unit_price` decimal(14,2) NOT NULL DEFAULT 0.00,
  `subtotal` decimal(14,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `customer_custom_items` (`id`, `customer_package_id`, `item_id`, `quantity`, `unit_price`, `subtotal`, `created_at`, `updated_at`) VALUES (5, 10, 71, 2, 36000, 72000, '2026-11-04 13:33:48', '2026-11-04 13:33:48');
INSERT INTO `customer_custom_items` (`id`, `customer_package_id`, `item_id`, `quantity`, `unit_price`, `subtotal`, `created_at`, `updated_at`) VALUES (6, 10, 52, 1, 78000, 78000, '2026-11-04 13:33:48', '2026-11-04 13:33:48');
INSERT INTO `customer_custom_items` (`id`, `customer_package_id`, `item_id`, `quantity`, `unit_price`, `subtotal`, `created_at`, `updated_at`) VALUES (7, 10, 61, 2, 24000, 48000, '2026-11-04 13:33:48', '2026-11-04 13:33:48');
INSERT INTO `customer_custom_items` (`id`, `customer_package_id`, `item_id`, `quantity`, `unit_price`, `subtotal`, `created_at`, `updated_at`) VALUES (8, 10, 74, 2, 18500, 37000, '2026-11-04 13:33:48', '2026-11-04 13:33:48');

CREATE TABLE IF NOT EXISTS `agent_deposits` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `deposit_number` varchar(50) NOT NULL UNIQUE,
  `agent_id` bigint(20) UNSIGNED NOT NULL,
  `total_amount` decimal(14,2) NOT NULL DEFAULT 0.00,
  `status` enum('pending','submitted','approved','rejected') NOT NULL DEFAULT 'pending',
  `agent_notes` text DEFAULT NULL,
  `owner_notes` text DEFAULT NULL,
  `submitted_at` timestamp NULL DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `approved_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `agent_deposits` (`id`, `deposit_number`, `agent_id`, `total_amount`, `status`, `agent_notes`, `owner_notes`, `submitted_at`, `approved_at`, `approved_by`, `created_at`, `updated_at`) VALUES (4, 'DEP-202610-0001', 4, 550000, 'approved', 'Setoran awal binaan Budi (Bu Anisa M1-10 & Pak Hendra M1-6)', 'Uang tunai Rp 550.000 sudah diterima di kantor oleh Bu Susi.', '2026-10-21 13:33:34', '2026-10-22 13:33:34', 1, '2026-11-04 13:33:34', '2026-11-04 13:33:34');
INSERT INTO `agent_deposits` (`id`, `deposit_number`, `agent_id`, `total_amount`, `status`, `agent_notes`, `owner_notes`, `submitted_at`, `approved_at`, `approved_by`, `created_at`, `updated_at`) VALUES (5, 'DEP-202611-0002', 5, 228340, 'submitted', 'Setoran tagihan minggu berjalan konsumen binaan Siti (Bu Dewi M1-10 & Pak Rahmat M1-10).', NULL, '2026-11-04 10:33:34', NULL, NULL, '2026-11-04 13:33:34', '2026-11-04 13:33:53');

CREATE TABLE IF NOT EXISTS `payments` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `customer_package_id` bigint(20) UNSIGNED NOT NULL,
  `agent_deposit_id` bigint(20) UNSIGNED DEFAULT NULL,
  `agent_id` bigint(20) UNSIGNED NOT NULL,
  `period_number` int(11) NOT NULL,
  `amount` decimal(14,2) NOT NULL DEFAULT 0.00,
  `is_paid` tinyint(1) NOT NULL DEFAULT 0,
  `paid_at` timestamp NULL DEFAULT NULL,
  `is_locked` tinyint(1) NOT NULL DEFAULT 0,
  `status` enum('unpaid','pending','verified') NOT NULL DEFAULT 'unpaid',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `payments` (`id`, `customer_package_id`, `agent_deposit_id`, `agent_id`, `period_number`, `amount`, `is_paid`, `paid_at`, `is_locked`, `status`, `created_at`, `updated_at`) VALUES (131, 7, 4, 4, 1, 25000, 1, '2026-08-26 13:33:34', 1, 'verified', '2026-11-04 13:33:34', '2026-11-04 13:33:34');
INSERT INTO `payments` (`id`, `customer_package_id`, `agent_deposit_id`, `agent_id`, `period_number`, `amount`, `is_paid`, `paid_at`, `is_locked`, `status`, `created_at`, `updated_at`) VALUES (132, 7, 4, 4, 2, 25000, 1, '2026-09-02 13:33:35', 1, 'verified', '2026-11-04 13:33:35', '2026-11-04 13:33:35');
INSERT INTO `payments` (`id`, `customer_package_id`, `agent_deposit_id`, `agent_id`, `period_number`, `amount`, `is_paid`, `paid_at`, `is_locked`, `status`, `created_at`, `updated_at`) VALUES (133, 7, 4, 4, 3, 25000, 1, '2026-09-09 13:33:35', 1, 'verified', '2026-11-04 13:33:35', '2026-11-04 13:33:35');
INSERT INTO `payments` (`id`, `customer_package_id`, `agent_deposit_id`, `agent_id`, `period_number`, `amount`, `is_paid`, `paid_at`, `is_locked`, `status`, `created_at`, `updated_at`) VALUES (134, 7, 4, 4, 4, 25000, 1, '2026-09-16 13:33:35', 1, 'verified', '2026-11-04 13:33:35', '2026-11-04 13:33:35');
INSERT INTO `payments` (`id`, `customer_package_id`, `agent_deposit_id`, `agent_id`, `period_number`, `amount`, `is_paid`, `paid_at`, `is_locked`, `status`, `created_at`, `updated_at`) VALUES (135, 7, 4, 4, 5, 25000, 1, '2026-09-23 13:33:35', 1, 'verified', '2026-11-04 13:33:35', '2026-11-04 13:33:35');
INSERT INTO `payments` (`id`, `customer_package_id`, `agent_deposit_id`, `agent_id`, `period_number`, `amount`, `is_paid`, `paid_at`, `is_locked`, `status`, `created_at`, `updated_at`) VALUES (136, 7, 4, 4, 6, 25000, 1, '2026-09-30 13:33:35', 1, 'verified', '2026-11-04 13:33:35', '2026-11-04 13:33:35');
INSERT INTO `payments` (`id`, `customer_package_id`, `agent_deposit_id`, `agent_id`, `period_number`, `amount`, `is_paid`, `paid_at`, `is_locked`, `status`, `created_at`, `updated_at`) VALUES (137, 7, 4, 4, 7, 25000, 1, '2026-10-07 13:33:35', 1, 'verified', '2026-11-04 13:33:35', '2026-11-04 13:33:35');
INSERT INTO `payments` (`id`, `customer_package_id`, `agent_deposit_id`, `agent_id`, `period_number`, `amount`, `is_paid`, `paid_at`, `is_locked`, `status`, `created_at`, `updated_at`) VALUES (138, 7, 4, 4, 8, 25000, 1, '2026-10-14 13:33:35', 1, 'verified', '2026-11-04 13:33:35', '2026-11-04 13:33:35');
INSERT INTO `payments` (`id`, `customer_package_id`, `agent_deposit_id`, `agent_id`, `period_number`, `amount`, `is_paid`, `paid_at`, `is_locked`, `status`, `created_at`, `updated_at`) VALUES (139, 7, 4, 4, 9, 25000, 1, '2026-10-21 13:33:35', 1, 'verified', '2026-11-04 13:33:35', '2026-11-04 13:33:35');
INSERT INTO `payments` (`id`, `customer_package_id`, `agent_deposit_id`, `agent_id`, `period_number`, `amount`, `is_paid`, `paid_at`, `is_locked`, `status`, `created_at`, `updated_at`) VALUES (140, 7, 4, 4, 10, 25000, 1, '2026-10-28 13:33:36', 1, 'verified', '2026-11-04 13:33:36', '2026-11-04 13:33:36');
INSERT INTO `payments` (`id`, `customer_package_id`, `agent_deposit_id`, `agent_id`, `period_number`, `amount`, `is_paid`, `paid_at`, `is_locked`, `status`, `created_at`, `updated_at`) VALUES (141, 7, NULL, 4, 11, 25000, 0, NULL, 0, 'unpaid', '2026-11-04 13:33:36', '2026-11-04 13:33:36');
INSERT INTO `payments` (`id`, `customer_package_id`, `agent_deposit_id`, `agent_id`, `period_number`, `amount`, `is_paid`, `paid_at`, `is_locked`, `status`, `created_at`, `updated_at`) VALUES (142, 7, NULL, 4, 12, 25000, 0, NULL, 0, 'unpaid', '2026-11-04 13:33:36', '2026-11-04 13:33:36');
INSERT INTO `payments` (`id`, `customer_package_id`, `agent_deposit_id`, `agent_id`, `period_number`, `amount`, `is_paid`, `paid_at`, `is_locked`, `status`, `created_at`, `updated_at`) VALUES (143, 7, NULL, 4, 13, 25000, 0, NULL, 0, 'unpaid', '2026-11-04 13:33:36', '2026-11-04 13:33:36');
INSERT INTO `payments` (`id`, `customer_package_id`, `agent_deposit_id`, `agent_id`, `period_number`, `amount`, `is_paid`, `paid_at`, `is_locked`, `status`, `created_at`, `updated_at`) VALUES (144, 7, NULL, 4, 14, 25000, 0, NULL, 0, 'unpaid', '2026-11-04 13:33:36', '2026-11-04 13:33:36');
INSERT INTO `payments` (`id`, `customer_package_id`, `agent_deposit_id`, `agent_id`, `period_number`, `amount`, `is_paid`, `paid_at`, `is_locked`, `status`, `created_at`, `updated_at`) VALUES (145, 7, NULL, 4, 15, 25000, 0, NULL, 0, 'unpaid', '2026-11-04 13:33:36', '2026-11-04 13:33:36');
INSERT INTO `payments` (`id`, `customer_package_id`, `agent_deposit_id`, `agent_id`, `period_number`, `amount`, `is_paid`, `paid_at`, `is_locked`, `status`, `created_at`, `updated_at`) VALUES (146, 7, NULL, 4, 16, 25000, 0, NULL, 0, 'unpaid', '2026-11-04 13:33:37', '2026-11-04 13:33:37');
INSERT INTO `payments` (`id`, `customer_package_id`, `agent_deposit_id`, `agent_id`, `period_number`, `amount`, `is_paid`, `paid_at`, `is_locked`, `status`, `created_at`, `updated_at`) VALUES (147, 7, NULL, 4, 17, 25000, 0, NULL, 0, 'unpaid', '2026-11-04 13:33:37', '2026-11-04 13:33:37');
INSERT INTO `payments` (`id`, `customer_package_id`, `agent_deposit_id`, `agent_id`, `period_number`, `amount`, `is_paid`, `paid_at`, `is_locked`, `status`, `created_at`, `updated_at`) VALUES (148, 7, NULL, 4, 18, 25000, 0, NULL, 0, 'unpaid', '2026-11-04 13:33:37', '2026-11-04 13:33:37');
INSERT INTO `payments` (`id`, `customer_package_id`, `agent_deposit_id`, `agent_id`, `period_number`, `amount`, `is_paid`, `paid_at`, `is_locked`, `status`, `created_at`, `updated_at`) VALUES (149, 7, NULL, 4, 19, 25000, 0, NULL, 0, 'unpaid', '2026-11-04 13:33:37', '2026-11-04 13:33:37');
INSERT INTO `payments` (`id`, `customer_package_id`, `agent_deposit_id`, `agent_id`, `period_number`, `amount`, `is_paid`, `paid_at`, `is_locked`, `status`, `created_at`, `updated_at`) VALUES (150, 7, NULL, 4, 20, 25000, 0, NULL, 0, 'unpaid', '2026-11-04 13:33:37', '2026-11-04 13:33:37');
INSERT INTO `payments` (`id`, `customer_package_id`, `agent_deposit_id`, `agent_id`, `period_number`, `amount`, `is_paid`, `paid_at`, `is_locked`, `status`, `created_at`, `updated_at`) VALUES (151, 7, NULL, 4, 21, 25000, 0, NULL, 0, 'unpaid', '2026-11-04 13:33:37', '2026-11-04 13:33:37');
INSERT INTO `payments` (`id`, `customer_package_id`, `agent_deposit_id`, `agent_id`, `period_number`, `amount`, `is_paid`, `paid_at`, `is_locked`, `status`, `created_at`, `updated_at`) VALUES (152, 7, NULL, 4, 22, 25000, 0, NULL, 0, 'unpaid', '2026-11-04 13:33:37', '2026-11-04 13:33:37');
INSERT INTO `payments` (`id`, `customer_package_id`, `agent_deposit_id`, `agent_id`, `period_number`, `amount`, `is_paid`, `paid_at`, `is_locked`, `status`, `created_at`, `updated_at`) VALUES (153, 7, NULL, 4, 23, 25000, 0, NULL, 0, 'unpaid', '2026-11-04 13:33:38', '2026-11-04 13:33:38');
INSERT INTO `payments` (`id`, `customer_package_id`, `agent_deposit_id`, `agent_id`, `period_number`, `amount`, `is_paid`, `paid_at`, `is_locked`, `status`, `created_at`, `updated_at`) VALUES (154, 7, NULL, 4, 24, 25000, 0, NULL, 0, 'unpaid', '2026-11-04 13:33:38', '2026-11-04 13:33:38');
INSERT INTO `payments` (`id`, `customer_package_id`, `agent_deposit_id`, `agent_id`, `period_number`, `amount`, `is_paid`, `paid_at`, `is_locked`, `status`, `created_at`, `updated_at`) VALUES (155, 7, NULL, 4, 25, 25000, 0, NULL, 0, 'unpaid', '2026-11-04 13:33:38', '2026-11-04 13:33:38');
INSERT INTO `payments` (`id`, `customer_package_id`, `agent_deposit_id`, `agent_id`, `period_number`, `amount`, `is_paid`, `paid_at`, `is_locked`, `status`, `created_at`, `updated_at`) VALUES (156, 7, NULL, 4, 26, 25000, 0, NULL, 0, 'unpaid', '2026-11-04 13:33:38', '2026-11-04 13:33:38');
INSERT INTO `payments` (`id`, `customer_package_id`, `agent_deposit_id`, `agent_id`, `period_number`, `amount`, `is_paid`, `paid_at`, `is_locked`, `status`, `created_at`, `updated_at`) VALUES (157, 7, NULL, 4, 27, 25000, 0, NULL, 0, 'unpaid', '2026-11-04 13:33:38', '2026-11-04 13:33:38');
INSERT INTO `payments` (`id`, `customer_package_id`, `agent_deposit_id`, `agent_id`, `period_number`, `amount`, `is_paid`, `paid_at`, `is_locked`, `status`, `created_at`, `updated_at`) VALUES (158, 7, NULL, 4, 28, 25000, 0, NULL, 0, 'unpaid', '2026-11-04 13:33:38', '2026-11-04 13:33:38');
INSERT INTO `payments` (`id`, `customer_package_id`, `agent_deposit_id`, `agent_id`, `period_number`, `amount`, `is_paid`, `paid_at`, `is_locked`, `status`, `created_at`, `updated_at`) VALUES (159, 7, NULL, 4, 29, 25000, 0, NULL, 0, 'unpaid', '2026-11-04 13:33:38', '2026-11-04 13:33:38');
INSERT INTO `payments` (`id`, `customer_package_id`, `agent_deposit_id`, `agent_id`, `period_number`, `amount`, `is_paid`, `paid_at`, `is_locked`, `status`, `created_at`, `updated_at`) VALUES (160, 7, NULL, 4, 30, 25000, 0, NULL, 0, 'unpaid', '2026-11-04 13:33:39', '2026-11-04 13:33:39');
INSERT INTO `payments` (`id`, `customer_package_id`, `agent_deposit_id`, `agent_id`, `period_number`, `amount`, `is_paid`, `paid_at`, `is_locked`, `status`, `created_at`, `updated_at`) VALUES (161, 8, 4, 4, 1, 50000, 1, '2026-08-26 13:33:39', 1, 'verified', '2026-11-04 13:33:39', '2026-11-04 13:33:39');
INSERT INTO `payments` (`id`, `customer_package_id`, `agent_deposit_id`, `agent_id`, `period_number`, `amount`, `is_paid`, `paid_at`, `is_locked`, `status`, `created_at`, `updated_at`) VALUES (162, 8, 4, 4, 2, 50000, 1, '2026-09-02 13:33:39', 1, 'verified', '2026-11-04 13:33:39', '2026-11-04 13:33:39');
INSERT INTO `payments` (`id`, `customer_package_id`, `agent_deposit_id`, `agent_id`, `period_number`, `amount`, `is_paid`, `paid_at`, `is_locked`, `status`, `created_at`, `updated_at`) VALUES (163, 8, 4, 4, 3, 50000, 1, '2026-09-09 13:33:39', 1, 'verified', '2026-11-04 13:33:39', '2026-11-04 13:33:39');
INSERT INTO `payments` (`id`, `customer_package_id`, `agent_deposit_id`, `agent_id`, `period_number`, `amount`, `is_paid`, `paid_at`, `is_locked`, `status`, `created_at`, `updated_at`) VALUES (164, 8, 4, 4, 4, 50000, 1, '2026-09-16 13:33:39', 1, 'verified', '2026-11-04 13:33:39', '2026-11-04 13:33:39');
INSERT INTO `payments` (`id`, `customer_package_id`, `agent_deposit_id`, `agent_id`, `period_number`, `amount`, `is_paid`, `paid_at`, `is_locked`, `status`, `created_at`, `updated_at`) VALUES (165, 8, 4, 4, 5, 50000, 1, '2026-09-23 13:33:40', 1, 'verified', '2026-11-04 13:33:40', '2026-11-04 13:33:40');
INSERT INTO `payments` (`id`, `customer_package_id`, `agent_deposit_id`, `agent_id`, `period_number`, `amount`, `is_paid`, `paid_at`, `is_locked`, `status`, `created_at`, `updated_at`) VALUES (166, 8, 4, 4, 6, 50000, 1, '2026-09-30 13:33:40', 1, 'verified', '2026-11-04 13:33:40', '2026-11-04 13:33:40');
INSERT INTO `payments` (`id`, `customer_package_id`, `agent_deposit_id`, `agent_id`, `period_number`, `amount`, `is_paid`, `paid_at`, `is_locked`, `status`, `created_at`, `updated_at`) VALUES (167, 8, NULL, 4, 7, 50000, 0, NULL, 0, 'unpaid', '2026-11-04 13:33:40', '2026-11-04 13:33:40');
INSERT INTO `payments` (`id`, `customer_package_id`, `agent_deposit_id`, `agent_id`, `period_number`, `amount`, `is_paid`, `paid_at`, `is_locked`, `status`, `created_at`, `updated_at`) VALUES (168, 8, NULL, 4, 8, 50000, 0, NULL, 0, 'unpaid', '2026-11-04 13:33:40', '2026-11-04 13:33:40');
INSERT INTO `payments` (`id`, `customer_package_id`, `agent_deposit_id`, `agent_id`, `period_number`, `amount`, `is_paid`, `paid_at`, `is_locked`, `status`, `created_at`, `updated_at`) VALUES (169, 8, NULL, 4, 9, 50000, 0, NULL, 0, 'unpaid', '2026-11-04 13:33:40', '2026-11-04 13:33:40');
INSERT INTO `payments` (`id`, `customer_package_id`, `agent_deposit_id`, `agent_id`, `period_number`, `amount`, `is_paid`, `paid_at`, `is_locked`, `status`, `created_at`, `updated_at`) VALUES (170, 8, NULL, 4, 10, 50000, 0, NULL, 0, 'unpaid', '2026-11-04 13:33:40', '2026-11-04 13:33:40');
INSERT INTO `payments` (`id`, `customer_package_id`, `agent_deposit_id`, `agent_id`, `period_number`, `amount`, `is_paid`, `paid_at`, `is_locked`, `status`, `created_at`, `updated_at`) VALUES (171, 8, NULL, 4, 11, 50000, 0, NULL, 0, 'unpaid', '2026-11-04 13:33:40', '2026-11-04 13:33:40');
INSERT INTO `payments` (`id`, `customer_package_id`, `agent_deposit_id`, `agent_id`, `period_number`, `amount`, `is_paid`, `paid_at`, `is_locked`, `status`, `created_at`, `updated_at`) VALUES (172, 8, NULL, 4, 12, 50000, 0, NULL, 0, 'unpaid', '2026-11-04 13:33:40', '2026-11-04 13:33:40');
INSERT INTO `payments` (`id`, `customer_package_id`, `agent_deposit_id`, `agent_id`, `period_number`, `amount`, `is_paid`, `paid_at`, `is_locked`, `status`, `created_at`, `updated_at`) VALUES (173, 8, NULL, 4, 13, 50000, 0, NULL, 0, 'unpaid', '2026-11-04 13:33:41', '2026-11-04 13:33:41');
INSERT INTO `payments` (`id`, `customer_package_id`, `agent_deposit_id`, `agent_id`, `period_number`, `amount`, `is_paid`, `paid_at`, `is_locked`, `status`, `created_at`, `updated_at`) VALUES (174, 8, NULL, 4, 14, 50000, 0, NULL, 0, 'unpaid', '2026-11-04 13:33:41', '2026-11-04 13:33:41');
INSERT INTO `payments` (`id`, `customer_package_id`, `agent_deposit_id`, `agent_id`, `period_number`, `amount`, `is_paid`, `paid_at`, `is_locked`, `status`, `created_at`, `updated_at`) VALUES (175, 8, NULL, 4, 15, 50000, 0, NULL, 0, 'unpaid', '2026-11-04 13:33:41', '2026-11-04 13:33:41');
INSERT INTO `payments` (`id`, `customer_package_id`, `agent_deposit_id`, `agent_id`, `period_number`, `amount`, `is_paid`, `paid_at`, `is_locked`, `status`, `created_at`, `updated_at`) VALUES (176, 8, NULL, 4, 16, 50000, 0, NULL, 0, 'unpaid', '2026-11-04 13:33:41', '2026-11-04 13:33:41');
INSERT INTO `payments` (`id`, `customer_package_id`, `agent_deposit_id`, `agent_id`, `period_number`, `amount`, `is_paid`, `paid_at`, `is_locked`, `status`, `created_at`, `updated_at`) VALUES (177, 8, NULL, 4, 17, 50000, 0, NULL, 0, 'unpaid', '2026-11-04 13:33:41', '2026-11-04 13:33:41');
INSERT INTO `payments` (`id`, `customer_package_id`, `agent_deposit_id`, `agent_id`, `period_number`, `amount`, `is_paid`, `paid_at`, `is_locked`, `status`, `created_at`, `updated_at`) VALUES (178, 8, NULL, 4, 18, 50000, 0, NULL, 0, 'unpaid', '2026-11-04 13:33:41', '2026-11-04 13:33:41');
INSERT INTO `payments` (`id`, `customer_package_id`, `agent_deposit_id`, `agent_id`, `period_number`, `amount`, `is_paid`, `paid_at`, `is_locked`, `status`, `created_at`, `updated_at`) VALUES (179, 8, NULL, 4, 19, 50000, 0, NULL, 0, 'unpaid', '2026-11-04 13:33:41', '2026-11-04 13:33:41');
INSERT INTO `payments` (`id`, `customer_package_id`, `agent_deposit_id`, `agent_id`, `period_number`, `amount`, `is_paid`, `paid_at`, `is_locked`, `status`, `created_at`, `updated_at`) VALUES (180, 8, NULL, 4, 20, 50000, 0, NULL, 0, 'unpaid', '2026-11-04 13:33:41', '2026-11-04 13:33:41');
INSERT INTO `payments` (`id`, `customer_package_id`, `agent_deposit_id`, `agent_id`, `period_number`, `amount`, `is_paid`, `paid_at`, `is_locked`, `status`, `created_at`, `updated_at`) VALUES (181, 8, NULL, 4, 21, 50000, 0, NULL, 0, 'unpaid', '2026-11-04 13:33:42', '2026-11-04 13:33:42');
INSERT INTO `payments` (`id`, `customer_package_id`, `agent_deposit_id`, `agent_id`, `period_number`, `amount`, `is_paid`, `paid_at`, `is_locked`, `status`, `created_at`, `updated_at`) VALUES (182, 8, NULL, 4, 22, 50000, 0, NULL, 0, 'unpaid', '2026-11-04 13:33:42', '2026-11-04 13:33:42');
INSERT INTO `payments` (`id`, `customer_package_id`, `agent_deposit_id`, `agent_id`, `period_number`, `amount`, `is_paid`, `paid_at`, `is_locked`, `status`, `created_at`, `updated_at`) VALUES (183, 8, NULL, 4, 23, 50000, 0, NULL, 0, 'unpaid', '2026-11-04 13:33:42', '2026-11-04 13:33:42');
INSERT INTO `payments` (`id`, `customer_package_id`, `agent_deposit_id`, `agent_id`, `period_number`, `amount`, `is_paid`, `paid_at`, `is_locked`, `status`, `created_at`, `updated_at`) VALUES (184, 8, NULL, 4, 24, 50000, 0, NULL, 0, 'unpaid', '2026-11-04 13:33:42', '2026-11-04 13:33:42');
INSERT INTO `payments` (`id`, `customer_package_id`, `agent_deposit_id`, `agent_id`, `period_number`, `amount`, `is_paid`, `paid_at`, `is_locked`, `status`, `created_at`, `updated_at`) VALUES (185, 8, NULL, 4, 25, 50000, 0, NULL, 0, 'unpaid', '2026-11-04 13:33:42', '2026-11-04 13:33:42');
INSERT INTO `payments` (`id`, `customer_package_id`, `agent_deposit_id`, `agent_id`, `period_number`, `amount`, `is_paid`, `paid_at`, `is_locked`, `status`, `created_at`, `updated_at`) VALUES (186, 8, NULL, 4, 26, 50000, 0, NULL, 0, 'unpaid', '2026-11-04 13:33:42', '2026-11-04 13:33:42');
INSERT INTO `payments` (`id`, `customer_package_id`, `agent_deposit_id`, `agent_id`, `period_number`, `amount`, `is_paid`, `paid_at`, `is_locked`, `status`, `created_at`, `updated_at`) VALUES (187, 8, NULL, 4, 27, 50000, 0, NULL, 0, 'unpaid', '2026-11-04 13:33:43', '2026-11-04 13:33:43');
INSERT INTO `payments` (`id`, `customer_package_id`, `agent_deposit_id`, `agent_id`, `period_number`, `amount`, `is_paid`, `paid_at`, `is_locked`, `status`, `created_at`, `updated_at`) VALUES (188, 8, NULL, 4, 28, 50000, 0, NULL, 0, 'unpaid', '2026-11-04 13:33:43', '2026-11-04 13:33:43');
INSERT INTO `payments` (`id`, `customer_package_id`, `agent_deposit_id`, `agent_id`, `period_number`, `amount`, `is_paid`, `paid_at`, `is_locked`, `status`, `created_at`, `updated_at`) VALUES (189, 8, NULL, 4, 29, 50000, 0, NULL, 0, 'unpaid', '2026-11-04 13:33:43', '2026-11-04 13:33:43');
INSERT INTO `payments` (`id`, `customer_package_id`, `agent_deposit_id`, `agent_id`, `period_number`, `amount`, `is_paid`, `paid_at`, `is_locked`, `status`, `created_at`, `updated_at`) VALUES (190, 8, NULL, 4, 30, 50000, 0, NULL, 0, 'unpaid', '2026-11-04 13:33:43', '2026-11-04 13:33:43');
INSERT INTO `payments` (`id`, `customer_package_id`, `agent_deposit_id`, `agent_id`, `period_number`, `amount`, `is_paid`, `paid_at`, `is_locked`, `status`, `created_at`, `updated_at`) VALUES (191, 9, 5, 5, 1, 15000, 1, '2026-08-26 13:33:43', 0, 'pending', '2026-11-04 13:33:43', '2026-11-04 13:33:43');
INSERT INTO `payments` (`id`, `customer_package_id`, `agent_deposit_id`, `agent_id`, `period_number`, `amount`, `is_paid`, `paid_at`, `is_locked`, `status`, `created_at`, `updated_at`) VALUES (192, 9, 5, 5, 2, 15000, 1, '2026-09-02 13:33:44', 0, 'pending', '2026-11-04 13:33:44', '2026-11-04 13:33:44');
INSERT INTO `payments` (`id`, `customer_package_id`, `agent_deposit_id`, `agent_id`, `period_number`, `amount`, `is_paid`, `paid_at`, `is_locked`, `status`, `created_at`, `updated_at`) VALUES (193, 9, 5, 5, 3, 15000, 1, '2026-09-09 13:33:44', 0, 'pending', '2026-11-04 13:33:44', '2026-11-04 13:33:44');
INSERT INTO `payments` (`id`, `customer_package_id`, `agent_deposit_id`, `agent_id`, `period_number`, `amount`, `is_paid`, `paid_at`, `is_locked`, `status`, `created_at`, `updated_at`) VALUES (194, 9, 5, 5, 4, 15000, 1, '2026-09-16 13:33:44', 0, 'pending', '2026-11-04 13:33:44', '2026-11-04 13:33:44');
INSERT INTO `payments` (`id`, `customer_package_id`, `agent_deposit_id`, `agent_id`, `period_number`, `amount`, `is_paid`, `paid_at`, `is_locked`, `status`, `created_at`, `updated_at`) VALUES (195, 9, 5, 5, 5, 15000, 1, '2026-09-23 13:33:44', 0, 'pending', '2026-11-04 13:33:44', '2026-11-04 13:33:44');
INSERT INTO `payments` (`id`, `customer_package_id`, `agent_deposit_id`, `agent_id`, `period_number`, `amount`, `is_paid`, `paid_at`, `is_locked`, `status`, `created_at`, `updated_at`) VALUES (196, 9, 5, 5, 6, 15000, 1, '2026-09-30 13:33:44', 0, 'pending', '2026-11-04 13:33:44', '2026-11-04 13:33:44');
INSERT INTO `payments` (`id`, `customer_package_id`, `agent_deposit_id`, `agent_id`, `period_number`, `amount`, `is_paid`, `paid_at`, `is_locked`, `status`, `created_at`, `updated_at`) VALUES (197, 9, 5, 5, 7, 15000, 1, '2026-10-07 13:33:44', 0, 'pending', '2026-11-04 13:33:44', '2026-11-04 13:33:44');
INSERT INTO `payments` (`id`, `customer_package_id`, `agent_deposit_id`, `agent_id`, `period_number`, `amount`, `is_paid`, `paid_at`, `is_locked`, `status`, `created_at`, `updated_at`) VALUES (198, 9, 5, 5, 8, 15000, 1, '2026-10-14 13:33:44', 0, 'pending', '2026-11-04 13:33:44', '2026-11-04 13:33:44');
INSERT INTO `payments` (`id`, `customer_package_id`, `agent_deposit_id`, `agent_id`, `period_number`, `amount`, `is_paid`, `paid_at`, `is_locked`, `status`, `created_at`, `updated_at`) VALUES (199, 9, 5, 5, 9, 15000, 1, '2026-10-21 13:33:45', 0, 'pending', '2026-11-04 13:33:45', '2026-11-04 13:33:45');
INSERT INTO `payments` (`id`, `customer_package_id`, `agent_deposit_id`, `agent_id`, `period_number`, `amount`, `is_paid`, `paid_at`, `is_locked`, `status`, `created_at`, `updated_at`) VALUES (200, 9, 5, 5, 10, 15000, 1, '2026-10-28 13:33:45', 0, 'pending', '2026-11-04 13:33:45', '2026-11-04 13:33:45');
INSERT INTO `payments` (`id`, `customer_package_id`, `agent_deposit_id`, `agent_id`, `period_number`, `amount`, `is_paid`, `paid_at`, `is_locked`, `status`, `created_at`, `updated_at`) VALUES (201, 9, NULL, 5, 11, 15000, 0, NULL, 0, 'unpaid', '2026-11-04 13:33:45', '2026-11-04 13:33:45');
INSERT INTO `payments` (`id`, `customer_package_id`, `agent_deposit_id`, `agent_id`, `period_number`, `amount`, `is_paid`, `paid_at`, `is_locked`, `status`, `created_at`, `updated_at`) VALUES (202, 9, NULL, 5, 12, 15000, 0, NULL, 0, 'unpaid', '2026-11-04 13:33:45', '2026-11-04 13:33:45');
INSERT INTO `payments` (`id`, `customer_package_id`, `agent_deposit_id`, `agent_id`, `period_number`, `amount`, `is_paid`, `paid_at`, `is_locked`, `status`, `created_at`, `updated_at`) VALUES (203, 9, NULL, 5, 13, 15000, 0, NULL, 0, 'unpaid', '2026-11-04 13:33:45', '2026-11-04 13:33:45');
INSERT INTO `payments` (`id`, `customer_package_id`, `agent_deposit_id`, `agent_id`, `period_number`, `amount`, `is_paid`, `paid_at`, `is_locked`, `status`, `created_at`, `updated_at`) VALUES (204, 9, NULL, 5, 14, 15000, 0, NULL, 0, 'unpaid', '2026-11-04 13:33:45', '2026-11-04 13:33:45');
INSERT INTO `payments` (`id`, `customer_package_id`, `agent_deposit_id`, `agent_id`, `period_number`, `amount`, `is_paid`, `paid_at`, `is_locked`, `status`, `created_at`, `updated_at`) VALUES (205, 9, NULL, 5, 15, 15000, 0, NULL, 0, 'unpaid', '2026-11-04 13:33:45', '2026-11-04 13:33:45');
INSERT INTO `payments` (`id`, `customer_package_id`, `agent_deposit_id`, `agent_id`, `period_number`, `amount`, `is_paid`, `paid_at`, `is_locked`, `status`, `created_at`, `updated_at`) VALUES (206, 9, NULL, 5, 16, 15000, 0, NULL, 0, 'unpaid', '2026-11-04 13:33:45', '2026-11-04 13:33:45');
INSERT INTO `payments` (`id`, `customer_package_id`, `agent_deposit_id`, `agent_id`, `period_number`, `amount`, `is_paid`, `paid_at`, `is_locked`, `status`, `created_at`, `updated_at`) VALUES (207, 9, NULL, 5, 17, 15000, 0, NULL, 0, 'unpaid', '2026-11-04 13:33:46', '2026-11-04 13:33:46');
INSERT INTO `payments` (`id`, `customer_package_id`, `agent_deposit_id`, `agent_id`, `period_number`, `amount`, `is_paid`, `paid_at`, `is_locked`, `status`, `created_at`, `updated_at`) VALUES (208, 9, NULL, 5, 18, 15000, 0, NULL, 0, 'unpaid', '2026-11-04 13:33:46', '2026-11-04 13:33:46');
INSERT INTO `payments` (`id`, `customer_package_id`, `agent_deposit_id`, `agent_id`, `period_number`, `amount`, `is_paid`, `paid_at`, `is_locked`, `status`, `created_at`, `updated_at`) VALUES (209, 9, NULL, 5, 19, 15000, 0, NULL, 0, 'unpaid', '2026-11-04 13:33:46', '2026-11-04 13:33:46');
INSERT INTO `payments` (`id`, `customer_package_id`, `agent_deposit_id`, `agent_id`, `period_number`, `amount`, `is_paid`, `paid_at`, `is_locked`, `status`, `created_at`, `updated_at`) VALUES (210, 9, NULL, 5, 20, 15000, 0, NULL, 0, 'unpaid', '2026-11-04 13:33:46', '2026-11-04 13:33:46');
INSERT INTO `payments` (`id`, `customer_package_id`, `agent_deposit_id`, `agent_id`, `period_number`, `amount`, `is_paid`, `paid_at`, `is_locked`, `status`, `created_at`, `updated_at`) VALUES (211, 9, NULL, 5, 21, 15000, 0, NULL, 0, 'unpaid', '2026-11-04 13:33:46', '2026-11-04 13:33:46');
INSERT INTO `payments` (`id`, `customer_package_id`, `agent_deposit_id`, `agent_id`, `period_number`, `amount`, `is_paid`, `paid_at`, `is_locked`, `status`, `created_at`, `updated_at`) VALUES (212, 9, NULL, 5, 22, 15000, 0, NULL, 0, 'unpaid', '2026-11-04 13:33:46', '2026-11-04 13:33:46');
INSERT INTO `payments` (`id`, `customer_package_id`, `agent_deposit_id`, `agent_id`, `period_number`, `amount`, `is_paid`, `paid_at`, `is_locked`, `status`, `created_at`, `updated_at`) VALUES (213, 9, NULL, 5, 23, 15000, 0, NULL, 0, 'unpaid', '2026-11-04 13:33:47', '2026-11-04 13:33:47');
INSERT INTO `payments` (`id`, `customer_package_id`, `agent_deposit_id`, `agent_id`, `period_number`, `amount`, `is_paid`, `paid_at`, `is_locked`, `status`, `created_at`, `updated_at`) VALUES (214, 9, NULL, 5, 24, 15000, 0, NULL, 0, 'unpaid', '2026-11-04 13:33:47', '2026-11-04 13:33:47');
INSERT INTO `payments` (`id`, `customer_package_id`, `agent_deposit_id`, `agent_id`, `period_number`, `amount`, `is_paid`, `paid_at`, `is_locked`, `status`, `created_at`, `updated_at`) VALUES (215, 9, NULL, 5, 25, 15000, 0, NULL, 0, 'unpaid', '2026-11-04 13:33:47', '2026-11-04 13:33:47');
INSERT INTO `payments` (`id`, `customer_package_id`, `agent_deposit_id`, `agent_id`, `period_number`, `amount`, `is_paid`, `paid_at`, `is_locked`, `status`, `created_at`, `updated_at`) VALUES (216, 9, NULL, 5, 26, 15000, 0, NULL, 0, 'unpaid', '2026-11-04 13:33:47', '2026-11-04 13:33:47');
INSERT INTO `payments` (`id`, `customer_package_id`, `agent_deposit_id`, `agent_id`, `period_number`, `amount`, `is_paid`, `paid_at`, `is_locked`, `status`, `created_at`, `updated_at`) VALUES (217, 9, NULL, 5, 27, 15000, 0, NULL, 0, 'unpaid', '2026-11-04 13:33:47', '2026-11-04 13:33:47');
INSERT INTO `payments` (`id`, `customer_package_id`, `agent_deposit_id`, `agent_id`, `period_number`, `amount`, `is_paid`, `paid_at`, `is_locked`, `status`, `created_at`, `updated_at`) VALUES (218, 9, NULL, 5, 28, 15000, 0, NULL, 0, 'unpaid', '2026-11-04 13:33:47', '2026-11-04 13:33:47');
INSERT INTO `payments` (`id`, `customer_package_id`, `agent_deposit_id`, `agent_id`, `period_number`, `amount`, `is_paid`, `paid_at`, `is_locked`, `status`, `created_at`, `updated_at`) VALUES (219, 9, NULL, 5, 29, 15000, 0, NULL, 0, 'unpaid', '2026-11-04 13:33:47', '2026-11-04 13:33:47');
INSERT INTO `payments` (`id`, `customer_package_id`, `agent_deposit_id`, `agent_id`, `period_number`, `amount`, `is_paid`, `paid_at`, `is_locked`, `status`, `created_at`, `updated_at`) VALUES (220, 9, NULL, 5, 30, 15000, 0, NULL, 0, 'unpaid', '2026-11-04 13:33:48', '2026-11-04 13:33:48');
INSERT INTO `payments` (`id`, `customer_package_id`, `agent_deposit_id`, `agent_id`, `period_number`, `amount`, `is_paid`, `paid_at`, `is_locked`, `status`, `created_at`, `updated_at`) VALUES (221, 10, 5, 5, 1, 7834, 1, '2026-08-26 13:33:49', 0, 'pending', '2026-11-04 13:33:49', '2026-11-04 13:33:49');
INSERT INTO `payments` (`id`, `customer_package_id`, `agent_deposit_id`, `agent_id`, `period_number`, `amount`, `is_paid`, `paid_at`, `is_locked`, `status`, `created_at`, `updated_at`) VALUES (222, 10, 5, 5, 2, 7834, 1, '2026-09-02 13:33:49', 0, 'pending', '2026-11-04 13:33:49', '2026-11-04 13:33:49');
INSERT INTO `payments` (`id`, `customer_package_id`, `agent_deposit_id`, `agent_id`, `period_number`, `amount`, `is_paid`, `paid_at`, `is_locked`, `status`, `created_at`, `updated_at`) VALUES (223, 10, 5, 5, 3, 7834, 1, '2026-09-09 13:33:49', 0, 'pending', '2026-11-04 13:33:49', '2026-11-04 13:33:49');
INSERT INTO `payments` (`id`, `customer_package_id`, `agent_deposit_id`, `agent_id`, `period_number`, `amount`, `is_paid`, `paid_at`, `is_locked`, `status`, `created_at`, `updated_at`) VALUES (224, 10, 5, 5, 4, 7834, 1, '2026-09-16 13:33:49', 0, 'pending', '2026-11-04 13:33:49', '2026-11-04 13:33:49');
INSERT INTO `payments` (`id`, `customer_package_id`, `agent_deposit_id`, `agent_id`, `period_number`, `amount`, `is_paid`, `paid_at`, `is_locked`, `status`, `created_at`, `updated_at`) VALUES (225, 10, 5, 5, 5, 7834, 1, '2026-09-23 13:33:49', 0, 'pending', '2026-11-04 13:33:49', '2026-11-04 13:33:49');
INSERT INTO `payments` (`id`, `customer_package_id`, `agent_deposit_id`, `agent_id`, `period_number`, `amount`, `is_paid`, `paid_at`, `is_locked`, `status`, `created_at`, `updated_at`) VALUES (226, 10, 5, 5, 6, 7834, 1, '2026-09-30 13:33:49', 0, 'pending', '2026-11-04 13:33:49', '2026-11-04 13:33:49');
INSERT INTO `payments` (`id`, `customer_package_id`, `agent_deposit_id`, `agent_id`, `period_number`, `amount`, `is_paid`, `paid_at`, `is_locked`, `status`, `created_at`, `updated_at`) VALUES (227, 10, 5, 5, 7, 7834, 1, '2026-10-07 13:33:49', 0, 'pending', '2026-11-04 13:33:49', '2026-11-04 13:33:49');
INSERT INTO `payments` (`id`, `customer_package_id`, `agent_deposit_id`, `agent_id`, `period_number`, `amount`, `is_paid`, `paid_at`, `is_locked`, `status`, `created_at`, `updated_at`) VALUES (228, 10, 5, 5, 8, 7834, 1, '2026-10-14 13:33:50', 0, 'pending', '2026-11-04 13:33:50', '2026-11-04 13:33:50');
INSERT INTO `payments` (`id`, `customer_package_id`, `agent_deposit_id`, `agent_id`, `period_number`, `amount`, `is_paid`, `paid_at`, `is_locked`, `status`, `created_at`, `updated_at`) VALUES (229, 10, 5, 5, 9, 7834, 1, '2026-10-21 13:33:50', 0, 'pending', '2026-11-04 13:33:50', '2026-11-04 13:33:50');
INSERT INTO `payments` (`id`, `customer_package_id`, `agent_deposit_id`, `agent_id`, `period_number`, `amount`, `is_paid`, `paid_at`, `is_locked`, `status`, `created_at`, `updated_at`) VALUES (230, 10, 5, 5, 10, 7834, 1, '2026-10-28 13:33:50', 0, 'pending', '2026-11-04 13:33:50', '2026-11-04 13:33:50');
INSERT INTO `payments` (`id`, `customer_package_id`, `agent_deposit_id`, `agent_id`, `period_number`, `amount`, `is_paid`, `paid_at`, `is_locked`, `status`, `created_at`, `updated_at`) VALUES (231, 10, NULL, 5, 11, 7834, 0, NULL, 0, 'unpaid', '2026-11-04 13:33:50', '2026-11-04 13:33:50');
INSERT INTO `payments` (`id`, `customer_package_id`, `agent_deposit_id`, `agent_id`, `period_number`, `amount`, `is_paid`, `paid_at`, `is_locked`, `status`, `created_at`, `updated_at`) VALUES (232, 10, NULL, 5, 12, 7834, 0, NULL, 0, 'unpaid', '2026-11-04 13:33:50', '2026-11-04 13:33:50');
INSERT INTO `payments` (`id`, `customer_package_id`, `agent_deposit_id`, `agent_id`, `period_number`, `amount`, `is_paid`, `paid_at`, `is_locked`, `status`, `created_at`, `updated_at`) VALUES (233, 10, NULL, 5, 13, 7834, 0, NULL, 0, 'unpaid', '2026-11-04 13:33:50', '2026-11-04 13:33:50');
INSERT INTO `payments` (`id`, `customer_package_id`, `agent_deposit_id`, `agent_id`, `period_number`, `amount`, `is_paid`, `paid_at`, `is_locked`, `status`, `created_at`, `updated_at`) VALUES (234, 10, NULL, 5, 14, 7834, 0, NULL, 0, 'unpaid', '2026-11-04 13:33:50', '2026-11-04 13:33:50');
INSERT INTO `payments` (`id`, `customer_package_id`, `agent_deposit_id`, `agent_id`, `period_number`, `amount`, `is_paid`, `paid_at`, `is_locked`, `status`, `created_at`, `updated_at`) VALUES (235, 10, NULL, 5, 15, 7834, 0, NULL, 0, 'unpaid', '2026-11-04 13:33:51', '2026-11-04 13:33:51');
INSERT INTO `payments` (`id`, `customer_package_id`, `agent_deposit_id`, `agent_id`, `period_number`, `amount`, `is_paid`, `paid_at`, `is_locked`, `status`, `created_at`, `updated_at`) VALUES (236, 10, NULL, 5, 16, 7834, 0, NULL, 0, 'unpaid', '2026-11-04 13:33:51', '2026-11-04 13:33:51');
INSERT INTO `payments` (`id`, `customer_package_id`, `agent_deposit_id`, `agent_id`, `period_number`, `amount`, `is_paid`, `paid_at`, `is_locked`, `status`, `created_at`, `updated_at`) VALUES (237, 10, NULL, 5, 17, 7834, 0, NULL, 0, 'unpaid', '2026-11-04 13:33:51', '2026-11-04 13:33:51');
INSERT INTO `payments` (`id`, `customer_package_id`, `agent_deposit_id`, `agent_id`, `period_number`, `amount`, `is_paid`, `paid_at`, `is_locked`, `status`, `created_at`, `updated_at`) VALUES (238, 10, NULL, 5, 18, 7834, 0, NULL, 0, 'unpaid', '2026-11-04 13:33:51', '2026-11-04 13:33:51');
INSERT INTO `payments` (`id`, `customer_package_id`, `agent_deposit_id`, `agent_id`, `period_number`, `amount`, `is_paid`, `paid_at`, `is_locked`, `status`, `created_at`, `updated_at`) VALUES (239, 10, NULL, 5, 19, 7834, 0, NULL, 0, 'unpaid', '2026-11-04 13:33:51', '2026-11-04 13:33:51');
INSERT INTO `payments` (`id`, `customer_package_id`, `agent_deposit_id`, `agent_id`, `period_number`, `amount`, `is_paid`, `paid_at`, `is_locked`, `status`, `created_at`, `updated_at`) VALUES (240, 10, NULL, 5, 20, 7834, 0, NULL, 0, 'unpaid', '2026-11-04 13:33:51', '2026-11-04 13:33:51');
INSERT INTO `payments` (`id`, `customer_package_id`, `agent_deposit_id`, `agent_id`, `period_number`, `amount`, `is_paid`, `paid_at`, `is_locked`, `status`, `created_at`, `updated_at`) VALUES (241, 10, NULL, 5, 21, 7834, 0, NULL, 0, 'unpaid', '2026-11-04 13:33:51', '2026-11-04 13:33:51');
INSERT INTO `payments` (`id`, `customer_package_id`, `agent_deposit_id`, `agent_id`, `period_number`, `amount`, `is_paid`, `paid_at`, `is_locked`, `status`, `created_at`, `updated_at`) VALUES (242, 10, NULL, 5, 22, 7834, 0, NULL, 0, 'unpaid', '2026-11-04 13:33:52', '2026-11-04 13:33:52');
INSERT INTO `payments` (`id`, `customer_package_id`, `agent_deposit_id`, `agent_id`, `period_number`, `amount`, `is_paid`, `paid_at`, `is_locked`, `status`, `created_at`, `updated_at`) VALUES (243, 10, NULL, 5, 23, 7834, 0, NULL, 0, 'unpaid', '2026-11-04 13:33:52', '2026-11-04 13:33:52');
INSERT INTO `payments` (`id`, `customer_package_id`, `agent_deposit_id`, `agent_id`, `period_number`, `amount`, `is_paid`, `paid_at`, `is_locked`, `status`, `created_at`, `updated_at`) VALUES (244, 10, NULL, 5, 24, 7834, 0, NULL, 0, 'unpaid', '2026-11-04 13:33:52', '2026-11-04 13:33:52');
INSERT INTO `payments` (`id`, `customer_package_id`, `agent_deposit_id`, `agent_id`, `period_number`, `amount`, `is_paid`, `paid_at`, `is_locked`, `status`, `created_at`, `updated_at`) VALUES (245, 10, NULL, 5, 25, 7834, 0, NULL, 0, 'unpaid', '2026-11-04 13:33:52', '2026-11-04 13:33:52');
INSERT INTO `payments` (`id`, `customer_package_id`, `agent_deposit_id`, `agent_id`, `period_number`, `amount`, `is_paid`, `paid_at`, `is_locked`, `status`, `created_at`, `updated_at`) VALUES (246, 10, NULL, 5, 26, 7834, 0, NULL, 0, 'unpaid', '2026-11-04 13:33:52', '2026-11-04 13:33:52');
INSERT INTO `payments` (`id`, `customer_package_id`, `agent_deposit_id`, `agent_id`, `period_number`, `amount`, `is_paid`, `paid_at`, `is_locked`, `status`, `created_at`, `updated_at`) VALUES (247, 10, NULL, 5, 27, 7834, 0, NULL, 0, 'unpaid', '2026-11-04 13:33:52', '2026-11-04 13:33:52');
INSERT INTO `payments` (`id`, `customer_package_id`, `agent_deposit_id`, `agent_id`, `period_number`, `amount`, `is_paid`, `paid_at`, `is_locked`, `status`, `created_at`, `updated_at`) VALUES (248, 10, NULL, 5, 28, 7834, 0, NULL, 0, 'unpaid', '2026-11-04 13:33:52', '2026-11-04 13:33:52');
INSERT INTO `payments` (`id`, `customer_package_id`, `agent_deposit_id`, `agent_id`, `period_number`, `amount`, `is_paid`, `paid_at`, `is_locked`, `status`, `created_at`, `updated_at`) VALUES (249, 10, NULL, 5, 29, 7834, 0, NULL, 0, 'unpaid', '2026-11-04 13:33:53', '2026-11-04 13:33:53');
INSERT INTO `payments` (`id`, `customer_package_id`, `agent_deposit_id`, `agent_id`, `period_number`, `amount`, `is_paid`, `paid_at`, `is_locked`, `status`, `created_at`, `updated_at`) VALUES (250, 10, NULL, 5, 30, 7834, 0, NULL, 0, 'unpaid', '2026-11-04 13:33:53', '2026-11-04 13:33:53');

CREATE TABLE IF NOT EXISTS `partial_fulfillments` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `customer_package_id` bigint(20) UNSIGNED NOT NULL,
  `total_paid` decimal(14,2) NOT NULL DEFAULT 0.00,
  `admin_fee` decimal(14,2) NOT NULL DEFAULT 0.00,
  `settlement_type` varchar(50) NOT NULL DEFAULT 'goods',
  `cash_payout` decimal(14,2) NOT NULL DEFAULT 0.00,
  `total_items_value` decimal(14,2) NOT NULL DEFAULT 0.00,
  `remaining_refund` decimal(14,2) NOT NULL DEFAULT 0.00,
  `adjusted_by` bigint(20) UNSIGNED NOT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `partial_fulfillment_items` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `partial_fulfillment_id` bigint(20) UNSIGNED NOT NULL,
  `item_id` bigint(20) UNSIGNED NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `unit_price` decimal(14,2) NOT NULL DEFAULT 0.00,
  `subtotal` decimal(14,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `posts` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `caption` text DEFAULT NULL,
  `image_path` varchar(255) NOT NULL,
  `compressed_size_kb` int(11) NOT NULL DEFAULT 0,
  `likes_count` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `posts` (`id`, `user_id`, `caption`, `image_path`, `compressed_size_kb`, `likes_count`, `created_at`, `updated_at`) VALUES (4, 1, 'Alhamdulillah, persediaan stok Biskuit Khong Guan dan Sirup Marjan untuk musim Lebaran 2026 / 1447 H sudah tiba di gudang pusat! Semangat teman-teman agen di lapangan! 📦✨', 'uploads/posts/sample_parsel_1.jpg', 142, 12, '2026-11-02 13:33:53', '2026-11-04 13:33:53');
INSERT INTO `posts` (`id`, `user_id`, `caption`, `image_path`, `compressed_size_kb`, `likes_count`, `created_at`, `updated_at`) VALUES (5, 4, 'Dokumentasi penagihan setoran mingguan di RT 03 RW 04 Paseh. Semua konsumen tertib dan ceklis berjalan lancar. 💪🚀', 'uploads/posts/sample_parsel_2.jpg', 156, 8, '2026-11-03 13:33:54', '2026-11-04 13:33:54');

CREATE TABLE IF NOT EXISTS `comments` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `post_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `content` text NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `comments` (`id`, `post_id`, `user_id`, `content`, `created_at`, `updated_at`) VALUES (4, 4, 4, 'Siap Bu Susi! Konsumen blok barat sangat antusias dengan Paket Parsel Mewah A tahun ini 👍', '2026-11-02 14:33:53', '2026-11-04 13:33:53');
INSERT INTO `comments` (`id`, `post_id`, `user_id`, `content`, `created_at`, `updated_at`) VALUES (5, 4, 5, 'Katalog barunya sudah saya sebar Bu, respon ibu-ibu pengajian sangat positif!', '2026-11-03 13:33:53', '2026-11-04 13:33:53');
INSERT INTO `comments` (`id`, `post_id`, `user_id`, `content`, `created_at`, `updated_at`) VALUES (6, 5, 1, 'Mantap Mas Budi, jangan lupa setoran fisiknya segera diserahkan agar keranjangnya bisa saya sahkan ya.', '2026-11-04 05:33:54', '2026-11-04 13:33:54');

CREATE TABLE IF NOT EXISTS `catalog_settings` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `app_name` varchar(255) NOT NULL DEFAULT 'Susi Parsel Lebaran',
  `logo_path` varchar(255) DEFAULT NULL,
  `owner_name` varchar(255) NOT NULL DEFAULT 'Ibu Hj. Susi Susilawati',
  `owner_address` text DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `program_period` varchar(255) NOT NULL,
  `terms_and_conditions` text DEFAULT NULL,
  `contact_person` varchar(255) NOT NULL,
  `bank_account_info` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `catalog_settings` (`id`, `app_name`, `logo_path`, `owner_name`, `owner_address`, `title`, `program_period`, `terms_and_conditions`, `contact_person`, `bank_account_info`, `created_at`, `updated_at`) VALUES (1, 'SPL Nusantara 2026', 'uploads/logos/spl_1793774034_7b275a78.jpg', 'Hj. Susi Susilawati, S.E.', 'Komplek Pertokoan Parsel Blok A1, Jawa Barat', 'KATALOG PARSEL NUSANTARA 2026', 'Musim Lebaran 1447 H / 2026', 'Syarat resmi berlaku.', '0812-3456-7890 (Ibu Susi)', 'BCA 1234567890 an Susi', '2026-11-04 13:32:10', '2026-11-04 13:33:54');

SET FOREIGN_KEY_CHECKS=1;
