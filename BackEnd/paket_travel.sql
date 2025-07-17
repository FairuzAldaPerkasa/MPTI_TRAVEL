-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 17, 2025 at 10:20 AM
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
-- Database: `paket_travel`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `role` varchar(50) DEFAULT 'admin',
  `avatar` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `name`, `email`, `email_verified_at`, `password`, `role`, `avatar`, `is_active`, `remember_token`, `created_at`, `updated_at`) VALUES
(3, 'Admin', 'admin@vacationland.com', NULL, '$2y$10$Dl2cRWFD1qVn6C8i/ETqr.CAmqIqzz3e38ed0TgZnRQCmyrYf6tAa', 'admin', NULL, 1, NULL, '2025-05-30 12:56:24', '2025-07-17 08:14:31'),
(4, 'Administrator', 'admin@mptitravel.com', NULL, '$2y$10$2V5KSBDUMtg.9u0RW56Yy.x3aUk6DPSkFWcuwUmGuH0wPBIBKoADC', 'admin', NULL, 1, NULL, '2025-07-17 06:15:12', '2025-07-17 08:19:17');

-- --------------------------------------------------------

--
-- Table structure for table `booking_history`
--

CREATE TABLE `booking_history` (
  `id` int(11) NOT NULL,
  `customer_name` varchar(200) NOT NULL,
  `customer_phone` varchar(20) NOT NULL,
  `customer_email` varchar(200) DEFAULT NULL,
  `package_id` int(11) DEFAULT NULL,
  `package_name` varchar(300) NOT NULL,
  `booking_date` date NOT NULL,
  `travel_date` date DEFAULT NULL,
  `participants` int(11) DEFAULT 1,
  `total_price` decimal(15,2) NOT NULL,
  `payment_method` varchar(100) DEFAULT NULL,
  `payment_status` enum('pending','paid','cancelled') DEFAULT 'pending',
  `booking_status` enum('confirmed','cancelled','completed') DEFAULT 'confirmed',
  `notes` text DEFAULT NULL,
  `whatsapp_number` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `booking_history`
--

INSERT INTO `booking_history` (`id`, `customer_name`, `customer_phone`, `customer_email`, `package_id`, `package_name`, `booking_date`, `travel_date`, `participants`, `total_price`, `payment_method`, `payment_status`, `booking_status`, `notes`, `whatsapp_number`, `created_at`, `updated_at`) VALUES
(2, 'John Doe', '081234567890', 'john@email.com', 29, '2D1N Wisata Yogyakarta', '2025-06-20', '0000-00-00', 2, 3000000.00, 'BCA', 'paid', 'confirmed', 'Booking melalui WhatsApp', '6281234567890', '2025-06-22 14:34:54', '2025-06-22 14:34:54'),
(3, 'Jane Smith', '081987654321', 'jane@email.com', NULL, '3D2N Adventure Jogja', '2025-06-21', '0000-00-00', 4, 6000000.00, 'GoPay', 'pending', 'confirmed', 'Menunggu konfirmasi pembayaran', '6281987654321', '2025-06-22 14:34:54', '2025-06-22 14:34:54'),
(4, 'Budi Santoso', '082111222333', 'budi@email.com', 29, '2D1N Wisata Yogyakarta', '2025-06-22', '0000-00-00', 1, 1500000.00, 'OVO', 'paid', 'completed', 'Trip sudah selesai', '6282111222333', '2025-06-22 14:34:54', '2025-06-22 14:34:54');

-- --------------------------------------------------------

--
-- Table structure for table `email_campaigns`
--

CREATE TABLE `email_campaigns` (
  `id` int(11) NOT NULL,
  `subject` varchar(500) NOT NULL,
  `message` text NOT NULL,
  `sent_to_count` int(11) DEFAULT 0,
  `sent_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `sent_by` varchar(100) DEFAULT 'admin',
  `campaign_type` enum('promo','newsletter','announcement') DEFAULT 'promo',
  `status` enum('draft','sent','scheduled') DEFAULT 'sent',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `email_campaigns`
--

INSERT INTO `email_campaigns` (`id`, `subject`, `message`, `sent_to_count`, `sent_at`, `sent_by`, `campaign_type`, `status`, `created_at`, `updated_at`) VALUES
(1, '🎉 Promo Spesial Liburan Sekolah!', 'Dapatkan diskon 25% untuk semua paket wisata Yogyakarta. Buruan booking sebelum kehabisan!', 4, '2025-06-22 14:46:20', 'admin', 'promo', 'sent', '2025-06-22 14:46:20', '2025-06-22 14:46:20'),
(2, '📰 Newsletter Bulanan - Juni 2025', 'Informasi destinasi wisata terbaru dan tips traveling yang menarik untuk Anda.', 4, '2025-06-22 14:46:20', 'admin', 'newsletter', 'sent', '2025-06-22 14:46:20', '2025-06-22 14:46:20'),
(3, '📢 Pembukaan Paket Baru: Bromo Adventure', 'Kami dengan bangga memperkenalkan paket wisata Bromo yang menakjubkan!', 3, '2025-06-22 14:46:20', 'admin', 'announcement', 'sent', '2025-06-22 14:46:20', '2025-06-22 14:46:20'),
(4, 'Test Promo Newsletter', 'Halo Sahabat Traveler!\n\nIni adalah test newsletter promo dari admin panel.\n\nKami menawarkan:\n- Diskon 20% untuk paket Yogyakarta\n- Transport AC nyaman\n- Guide berpengalaman\n\nHubungi kami di WhatsApp untuk booking!\n\nSalam,\nMPTI Travel Team', 5, '2025-06-22 14:55:17', 'admin', 'promo', 'sent', '2025-06-22 14:55:17', '2025-06-22 14:55:17');

-- --------------------------------------------------------

--
-- Table structure for table `newsletter_subscribers`
--

CREATE TABLE `newsletter_subscribers` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `name` varchar(200) DEFAULT NULL,
  `status` enum('active','unsubscribed') DEFAULT 'active',
  `subscription_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_email_sent` timestamp NULL DEFAULT NULL,
  `source` varchar(100) DEFAULT 'website_footer',
  `user_agent` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `verification_token` varchar(100) DEFAULT NULL,
  `is_verified` tinyint(1) DEFAULT 1,
  `unsubscribe_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `newsletter_subscribers`
--

INSERT INTO `newsletter_subscribers` (`id`, `email`, `name`, `status`, `subscription_date`, `last_email_sent`, `source`, `user_agent`, `ip_address`, `verification_token`, `is_verified`, `unsubscribe_token`, `created_at`, `updated_at`) VALUES
(9, '2200018138@webmail.uad.ac.id', '', 'active', '2025-07-01 15:00:46', NULL, 'website_footer', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '::1', NULL, 1, 'ae3fe831efb297d51c097fe2f4f4a163', '2025-07-01 15:00:46', '2025-07-01 15:00:46'),
(10, 'fairuzaldaperkasa@gmail.com', '', 'active', '2025-07-01 15:02:09', NULL, 'website_footer', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '::1', NULL, 1, '90e67ee997939c66713dc6887c5bd3ec', '2025-07-01 15:02:09', '2025-07-01 15:02:09');

-- --------------------------------------------------------

--
-- Table structure for table `package_gallery`
--

CREATE TABLE `package_gallery` (
  `id` int(11) NOT NULL,
  `package_id` int(11) NOT NULL,
  `photo_filename` varchar(255) NOT NULL,
  `caption` text DEFAULT NULL,
  `photo_order` int(11) DEFAULT 0,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `package_gallery`
--

INSERT INTO `package_gallery` (`id`, `package_id`, `photo_filename`, `caption`, `photo_order`, `uploaded_at`) VALUES
(1, 15, 'gallery_15_1748667617_683a8ce10f8c8.png', 'fyfyy', 1, '2025-05-31 05:00:17'),
(3, 23, 'gallery_23_1748854189_683d65ada6036.png', '', 1, '2025-06-02 08:49:49'),
(13, 28, 'gallery_28_1749742136_684af23882646.jpg', '', 1, '2025-06-12 15:28:56'),
(14, 28, 'gallery_28_1749742136_684af238828aa.png', '', 2, '2025-06-12 15:28:56'),
(15, 28, 'gallery_28_1749742153_684af249c06f2.jpg', '', 3, '2025-06-12 15:29:13'),
(16, 28, 'gallery_28_1749742165_684af2553a9e2.png', '', 4, '2025-06-12 15:29:25');

-- --------------------------------------------------------

--
-- Table structure for table `paket`
--

CREATE TABLE `paket` (
  `id` int(11) NOT NULL,
  `nama` varchar(255) NOT NULL,
  `deskripsi` text NOT NULL,
  `fotos` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `itinerary` text DEFAULT NULL,
  `highlights` text DEFAULT NULL,
  `inclusions` text DEFAULT NULL,
  `exclusions` text DEFAULT NULL,
  `price` decimal(12,2) NOT NULL DEFAULT 0.00,
  `duration` varchar(50) DEFAULT '2D1N'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `paket`
--

INSERT INTO `paket` (`id`, `nama`, `deskripsi`, `fotos`, `created_at`, `updated_at`, `itinerary`, `highlights`, `inclusions`, `exclusions`, `price`, `duration`) VALUES
(29, 'Jogja Heritage & Culture Tour', 'Ini paket yang menarik harusnya', '[\"1750864322_e299d4b52481fe4d_1.png\",\"1750864322_ea1a22f2f1b7f66e_2.png\",\"1750864322_ff2fd4e3eb854672_3.png\"]', '2025-06-17 09:11:15', '2025-06-25 15:12:02', '[{\"day\":1,\"title\":\"mantap\",\"activities\":[]},{\"day\":2,\"title\":\"Kedatangan\",\"activities\":[{\"time\":\"14:00\",\"activity\":\"wkledknwd\"}]}]', '[\"Pemandangan Mantepg\",\"Keren\",\"Cool\"]', '[{\"icon\":\"fas fa-ticket-alt\",\"text\":\"r,smdnjkbkjds\"}]', '[{\"icon\":\"fas fa-shopping-bag\",\"text\":\"rsdjnsdn\"}]', 2000000.00, '2D1N');

-- --------------------------------------------------------

--
-- Table structure for table `paket_backup`
--

CREATE TABLE `paket_backup` (
  `id` int(11) NOT NULL DEFAULT 0,
  `nama` varchar(255) NOT NULL,
  `deskripsi` text NOT NULL,
  `fotos` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `paket_backup`
--

INSERT INTO `paket_backup` (`id`, `nama`, `deskripsi`, `fotos`, `created_at`, `updated_at`) VALUES
(7, 'JALAN JALAN KE BANDUNG', 'dsdds', NULL, '2025-05-30 13:24:36', '2025-05-30 13:24:36'),
(8, 'JALAN JALAN KE BANDUNG', 'hjsvhjdvjsbd', '[\"1748612156_6839b43ce5c52_1.png\",\"1748612156_6839b43ce5d7d_2.png\",\"1748612156_6839b43ce5ea3_3.jpg\"]', '2025-05-30 13:35:56', '2025-05-30 13:35:56');

-- --------------------------------------------------------

--
-- Table structure for table `payment_methods`
--

CREATE TABLE `payment_methods` (
  `id` int(11) NOT NULL,
  `method_name` varchar(100) NOT NULL,
  `method_type` enum('bank','ewallet','card','other') DEFAULT 'other',
  `icon_class` varchar(100) DEFAULT 'fas fa-credit-card',
  `is_active` tinyint(1) DEFAULT 1,
  `display_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payment_methods`
--

INSERT INTO `payment_methods` (`id`, `method_name`, `method_type`, `icon_class`, `is_active`, `display_order`, `created_at`, `updated_at`) VALUES
(1, 'BCA', 'bank', 'fas fa-university', 1, 1, '2025-06-22 14:41:38', '2025-06-22 14:41:38'),
(2, 'Mandiri', 'bank', 'fas fa-university', 1, 2, '2025-06-22 14:41:38', '2025-06-22 14:41:38'),
(3, 'BNI', 'bank', 'fas fa-university', 1, 3, '2025-06-22 14:41:38', '2025-06-22 14:41:38'),
(4, 'BRI', 'bank', 'fas fa-university', 1, 4, '2025-06-22 14:41:38', '2025-06-22 14:41:38'),
(5, 'VISA', 'card', 'fab fa-cc-visa', 1, 5, '2025-06-22 14:41:38', '2025-06-22 14:41:38'),
(6, 'Mastercard', 'card', 'fab fa-cc-mastercard', 1, 6, '2025-06-22 14:41:38', '2025-06-22 14:41:38'),
(7, 'GoPay', 'ewallet', 'fas fa-mobile-alt', 1, 7, '2025-06-22 14:41:38', '2025-06-22 14:41:38'),
(8, 'OVO', 'ewallet', 'fas fa-wallet', 1, 8, '2025-06-22 14:41:38', '2025-06-22 14:41:38'),
(9, 'DANA', 'ewallet', 'fas fa-mobile-alt', 1, 9, '2025-06-22 14:41:38', '2025-06-22 14:41:38'),
(10, 'ShopeePay', 'ewallet', 'fas fa-shopping-bag', 1, 10, '2025-06-22 14:41:38', '2025-06-22 14:41:38'),
(11, 'TEST', 'bank', 'fa-credit-card', 1, 2, '2025-07-17 06:40:11', '2025-07-17 06:40:11');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `website_settings`
--

CREATE TABLE `website_settings` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text NOT NULL,
  `setting_type` enum('text','number','email','url','textarea') DEFAULT 'text',
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `website_settings`
--

INSERT INTO `website_settings` (`id`, `setting_key`, `setting_value`, `setting_type`, `description`, `created_at`, `updated_at`) VALUES
(1, 'whatsapp_number', '628122702748', 'text', 'Nomor WhatsApp untuk booking (format: 62xxx tanpa +)', '2025-06-22 14:17:06', '2025-06-22 14:32:39'),
(2, 'phone_number', '+62 8122702748', 'text', 'Nomor telepon untuk kontak', '2025-06-22 14:17:06', '2025-06-22 14:22:55'),
(3, 'company_email', 'info@mptitravel.com', 'email', 'Email perusahaan', '2025-06-22 14:17:06', '2025-06-22 14:17:36'),
(4, 'company_address', 'Yogyakarta, Indonesia', 'textarea', 'Alamat perusahaan', '2025-06-22 14:17:06', '2025-06-22 14:17:36'),
(5, 'instagram_handle', '@mptitravel', 'text', 'Handle Instagram', '2025-06-22 14:17:06', '2025-06-22 14:17:36'),
(6, 'facebook_page', 'Vacationland', 'text', 'Nama halaman Facebook', '2025-06-22 14:17:06', '2025-06-22 14:17:06'),
(7, 'website_url', 'https://mptitravel.com', 'url', 'URL website', '2025-06-22 14:17:06', '2025-06-22 14:17:36'),
(8, 'email', 'info@mptitravel.com', 'text', NULL, '2025-06-22 14:17:36', '2025-06-22 14:17:36'),
(9, 'website_name', 'MPTI Travel', 'text', NULL, '2025-06-22 14:17:36', '2025-06-22 14:17:36'),
(10, 'whatsapp_message', 'Halo, saya tertarik dengan paket wisata dari Vacationland. Bisakah Anda memberikan informasi lebih lanjut?', 'text', NULL, '2025-06-22 14:17:36', '2025-06-22 14:26:46'),
(11, 'address', 'Yogyakarta, Indonesia', 'text', NULL, '2025-06-22 14:17:36', '2025-06-22 14:17:36'),
(12, 'city', 'Yogyakarta', 'text', NULL, '2025-06-22 14:17:36', '2025-06-22 14:17:36'),
(13, 'province', 'DIY', 'text', NULL, '2025-06-22 14:17:36', '2025-06-22 14:17:36'),
(14, 'description', 'Vacationland - Solusi perjalanan wisata terbaik untuk liburan Anda', 'text', NULL, '2025-06-22 14:17:36', '2025-06-22 14:26:46'),
(15, 'instagram', '@mptitravel', 'text', NULL, '2025-06-22 14:17:36', '2025-06-22 14:17:36');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `admins_email_unique` (`email`);

--
-- Indexes for table `booking_history`
--
ALTER TABLE `booking_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `package_id` (`package_id`);

--
-- Indexes for table `email_campaigns`
--
ALTER TABLE `email_campaigns`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `newsletter_subscribers`
--
ALTER TABLE `newsletter_subscribers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_subscription_date` (`subscription_date`);

--
-- Indexes for table `package_gallery`
--
ALTER TABLE `package_gallery`
  ADD PRIMARY KEY (`id`),
  ADD KEY `package_id` (`package_id`),
  ADD KEY `photo_order` (`photo_order`);

--
-- Indexes for table `paket`
--
ALTER TABLE `paket`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_id` (`id`);

--
-- Indexes for table `payment_methods`
--
ALTER TABLE `payment_methods`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- Indexes for table `website_settings`
--
ALTER TABLE `website_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `booking_history`
--
ALTER TABLE `booking_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `email_campaigns`
--
ALTER TABLE `email_campaigns`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `newsletter_subscribers`
--
ALTER TABLE `newsletter_subscribers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `package_gallery`
--
ALTER TABLE `package_gallery`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `paket`
--
ALTER TABLE `paket`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `payment_methods`
--
ALTER TABLE `payment_methods`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `website_settings`
--
ALTER TABLE `website_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `booking_history`
--
ALTER TABLE `booking_history`
  ADD CONSTRAINT `booking_history_ibfk_1` FOREIGN KEY (`package_id`) REFERENCES `paket` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
