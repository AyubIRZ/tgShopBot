-- phpMyAdmin SQL Dump
-- version 4.7.4
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: May 28, 2018 at 08:52 PM
-- Server version: 5.7.19
-- PHP Version: 5.6.31

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `7learn_shop`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

DROP TABLE IF EXISTS `admin`;
CREATE TABLE IF NOT EXISTS `admin` (
  `user_id` int(63) NOT NULL,
  `step` varchar(127) DEFAULT NULL,
  `cat_name` varchar(255) CHARACTER SET utf8mb4 DEFAULT NULL,
  `tmp_cat_id` int(31) DEFAULT NULL,
  `tmp_name` varchar(512) CHARACTER SET utf8mb4 DEFAULT NULL,
  `tmp_desc` text CHARACTER SET utf8mb4,
  `tmp_price` float DEFAULT NULL,
  `tmp_photo_link` varchar(512) CHARACTER SET utf8mb4 DEFAULT NULL,
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`user_id`, `step`, `cat_name`, `tmp_cat_id`, `tmp_name`, `tmp_desc`, `tmp_price`, `tmp_photo_link`) VALUES
(252519699, 'admin_home', '6', 2, 'نرم افزار عکس برداری', 'نرم افزار عکس بردارینرم افزار عکس بردارینرم افزار عکس بردارینرم افزار عکس بردارینرم افزار عکس بردارینرم افزار عکس بردارینرم افزار عکس بردارینرم افزار عکس بردارینرم افزار عکس بردارینرم افزار عکس برداری', 6000, 'http://fbf55e78.ngrok.io/telegram-bot-course/project3/images/1522969636file_1.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

DROP TABLE IF EXISTS `cart`;
CREATE TABLE IF NOT EXISTS `cart` (
  `id` int(255) NOT NULL AUTO_INCREMENT,
  `user_id` int(63) NOT NULL,
  `product_id` varchar(1024) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `category`
--

DROP TABLE IF EXISTS `category`;
CREATE TABLE IF NOT EXISTS `category` (
  `id` int(127) NOT NULL AUTO_INCREMENT,
  `cat_name` varchar(127) CHARACTER SET utf8mb4 NOT NULL,
  `cat_description` varchar(1024) CHARACTER SET utf8mb4 DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `category`
--

INSERT INTO `category` (`id`, `cat_name`, `cat_description`) VALUES
(1, 'محصولات آموزشی', 'توضیحات تست برای دسته ی محصولات آموزشی'),
(2, 'نرم افزار های موبایل', 'توضیحات تست برای دسته ی نرم افزار های موبایل'),
(3, 'ویدیوهای کلاسی', 'توضیحات تست برای دسته ی ویدیو های کلاسی'),
(4, 'کتاب های الکترونیکی', 'توضیحات تست برای دسته ی کتاب های الکترونیکی'),
(5, 'بازی های ویدیویی', 'توضیحات تست برای بازیهای ویدیویی'),
(6, 'کتاب های علمی', 'توضیحات جدید');

-- --------------------------------------------------------

--
-- Table structure for table `payed_cart`
--

DROP TABLE IF EXISTS `payed_cart`;
CREATE TABLE IF NOT EXISTS `payed_cart` (
  `id` int(127) NOT NULL AUTO_INCREMENT,
  `user_id` int(63) NOT NULL,
  `product_id` varchar(1024) NOT NULL,
  `pay_time` varchar(63) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `payed_cart`
--

INSERT INTO `payed_cart` (`id`, `user_id`, `product_id`, `pay_time`) VALUES
(1, 252519699, '[\"1\",\"2\"]', '1522954444');

-- --------------------------------------------------------

--
-- Table structure for table `product`
--

DROP TABLE IF EXISTS `product`;
CREATE TABLE IF NOT EXISTS `product` (
  `id` int(127) NOT NULL AUTO_INCREMENT,
  `cat_id` int(127) NOT NULL,
  `name` varchar(512) CHARACTER SET utf8mb4 NOT NULL,
  `description` text CHARACTER SET utf8mb4 NOT NULL,
  `price` float NOT NULL,
  `photo_link` varchar(255) CHARACTER SET utf8mb4 DEFAULT NULL,
  `download_link` varchar(512) CHARACTER SET utf8mb4 NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `product`
--

INSERT INTO `product` (`id`, `cat_id`, `name`, `description`, `price`, `photo_link`, `download_link`) VALUES
(1, 2, 'نرم افزار ماشین حساب مهندسی', 'نرم افزار های موبایلنرم افزار های موبایلنرم افزار های موبایلنرم افزار های موبایلنرم افزار های موبایلنرم افزار های موبایلنرم افزار های موبایلنرم افزار های موبایلنرم افزار های موبایلنرم افزار های موبایلنرم افزار های موبایلنرم افزار های موبایلنرم افزار های موبایلنرم افزار های موبایلنرم افزار های موبایلنرم افزار های موبایلنرم افزار های موبایلنرم افزار های موبایلنرم افزار های موبایلنرم افزار های موبایلنرم افزار های موبایلنرم افزار های موبایل', 5500, 'http://fbf55e78.ngrok.io/telegram-bot-course/project3/images/1.jpg', 'http://google.com/some_directory'),
(2, 3, 'کتاب آموزش برنامه نویسی', 'کتاب آموزش برنامه نویسیکتاب آموزش برنامه نویسیکتاب آموزش برنامه نویسیکتاب آموزش برنامه نویسیکتاب آموزش برنامه نویسیکتاب آموزش برنامه نویسیکتاب آموزش برنامه نویسیکتاب آموزش برنامه نویسیکتاب آموزش برنامه نویسیکتاب آموزش برنامه نویسیکتاب آموزش برنامه نویسیکتاب آموزش برنامه نویسیکتاب آموزش برنامه نویسیکتاب آموزش برنامه نویسیکتاب آموزش برنامه نویسیکتاب آموزش برنامه نویسیکتاب آموزش برنامه نویسیکتاب آموزش برنامه نویسیکتاب آموزش برنامه نویسیکتاب آموزش برنامه نویسیکتاب آموزش برنامه نویسیکتاب آموزش برنامه نویسیکتاب آموزش برنامه نویسیکتاب آموزش برنامه نویسیکتاب آموزش برنامه نویسیکتاب آموزش برنامه نویسیکتاب آموزش برنامه نویسیکتاب آموزش برنامه نویسیکتاب آموزش برنامه نویسیکتاب آموزش برنامه نویسیکتاب آموزش برنامه نویسیکتاب آموزش برنامه نویسیکتاب آموزش برنامه نویسیکتاب آموزش برنامه نویسیکتاب آموزش برنامه نویسی', 4000, NULL, 'https://core.telegram.org/bots/api'),
(3, 2, 'نرم افزار عکس برداری', 'نرم افزار عکس بردارینرم افزار عکس بردارینرم افزار عکس بردارینرم افزار عکس بردارینرم افزار عکس بردارینرم افزار عکس بردارینرم افزار عکس بردارینرم افزار عکس بردارینرم افزار عکس بردارینرم افزار عکس برداری', 6000, 'http://fbf55e78.ngrok.io/telegram-bot-course/project3/images/1522969636file_1.jpg', 'https://www.urldecoder.org/privacy/');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `user_id` int(63) NOT NULL,
  `first_name` varchar(1023) CHARACTER SET utf8mb4 NOT NULL,
  `last_name` varchar(1023) CHARACTER SET utf8mb4 DEFAULT NULL,
  `username` varchar(1023) DEFAULT NULL,
  `step` varchar(16) DEFAULT NULL,
  `hash_id` varchar(512) DEFAULT NULL,
  `last_search` varchar(512) CHARACTER SET utf8mb4 DEFAULT NULL,
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `first_name`, `last_name`, `username`, `step`, `hash_id`, `last_search`) VALUES
(252519699, '7learn account', '', 'sevenlearn_test_account', 'admin', '950d3ea089dbb72d84f24df5450c18c7', '3');

--
-- Constraints for dumped tables
--

--
-- Constraints for table `admin`
--
ALTER TABLE `admin`
  ADD CONSTRAINT `admin_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE NO ACTION ON UPDATE NO ACTION;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
