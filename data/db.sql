-- phpMyAdmin SQL Dump
-- version 4.8.3
-- https://www.phpmyadmin.net/
--
-- Хост: 127.0.0.1:3306
-- Время создания: Июн 16 2019 г., 02:42
-- Версия сервера: 10.3.9-MariaDB
-- Версия PHP: 7.2.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `pridestudio_true`
--

-- --------------------------------------------------------

--
-- Структура таблицы `client`
--

CREATE TABLE IF NOT EXISTS `client` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sex` tinyint(4) DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `comment` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Клиент';

-- --------------------------------------------------------

--
-- Структура таблицы `hall`
--

CREATE TABLE IF NOT EXISTS `hall` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` varchar(1024) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `base_price` decimal(10,2) NOT NULL,
  `preview_image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `detail_image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sort` int(11) NOT NULL DEFAULT 1,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` int(10) UNSIGNED NOT NULL,
  `updated_at` int(10) UNSIGNED NOT NULL,
  `created_by` int(10) UNSIGNED DEFAULT NULL,
  `updated_by` int(10) UNSIGNED DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `created_by` (`created_by`),
  KEY `updated_by` (`updated_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Зал';

-- --------------------------------------------------------

--
-- Структура таблицы `payment`
--

CREATE TABLE IF NOT EXISTS `payment` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `record_id` int(10) UNSIGNED DEFAULT NULL,
  `client_id` int(10) UNSIGNED DEFAULT NULL,
  `method_id` int(10) UNSIGNED DEFAULT NULL,
  `type_id` int(10) UNSIGNED DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `status` tinyint(4) NOT NULL DEFAULT 1,
  `paid_at` int(10) UNSIGNED DEFAULT NULL,
  `created_at` int(10) UNSIGNED NOT NULL,
  `updated_at` int(10) UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  KEY `client_id` (`client_id`),
  KEY `type_id` (`type_id`),
  KEY `method_id` (`method_id`),
  KEY `record_id` (`record_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Оплата';

-- --------------------------------------------------------

--
-- Структура таблицы `record`
--

CREATE TABLE IF NOT EXISTS `record` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `client_id` int(10) UNSIGNED DEFAULT NULL,
  `hall_id` int(10) UNSIGNED DEFAULT NULL,
  `payment_id` int(10) UNSIGNED DEFAULT NULL,
  `promo_id` int(10) UNSIGNED DEFAULT NULL,
  `total` decimal(10,2) NOT NULL DEFAULT 0.00,
  `comment` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` tinyint(4) NOT NULL DEFAULT 1,
  `created_at` int(10) UNSIGNED NOT NULL,
  `updated_at` int(10) UNSIGNED NOT NULL,
  `created_by` int(10) UNSIGNED DEFAULT NULL,
  `updated_by` int(10) UNSIGNED DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `client_id` (`client_id`),
  KEY `hall_id` (`hall_id`),
  KEY `payment_id` (`payment_id`),
  KEY `promo_id` (`promo_id`),
  KEY `created_by` (`created_by`),
  KEY `updated_by` (`updated_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Запись';

--
-- Ограничения внешнего ключа сохраненных таблиц
--

--
-- Ограничения внешнего ключа таблицы `payment`
--
ALTER TABLE `payment`
  ADD CONSTRAINT `payment_ibfk_1` FOREIGN KEY (`client_id`) REFERENCES `client` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `payment_ibfk_2` FOREIGN KEY (`record_id`) REFERENCES `record` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `record`
--
ALTER TABLE `record`
  ADD CONSTRAINT `record_ibfk_1` FOREIGN KEY (`client_id`) REFERENCES `client` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `record_ibfk_2` FOREIGN KEY (`hall_id`) REFERENCES `hall` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `record_ibfk_3` FOREIGN KEY (`payment_id`) REFERENCES `payment` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
