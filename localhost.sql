-- phpMyAdmin SQL Dump
-- version 4.7.7
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Oct 10, 2018 at 05:55 PM
-- Server version: 10.1.30-MariaDB
-- PHP Version: 7.2.1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `teamwork`
--
DROP DATABASE IF EXISTS `teamwork`;
CREATE DATABASE IF NOT EXISTS `teamwork` DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;
USE `teamwork`;

-- --------------------------------------------------------

--
-- Table structure for table `t_customers`
--

DROP TABLE IF EXISTS `t_customers`;
CREATE TABLE `t_customers` (
  `uid` int(10) NOT NULL,
  `cust_code` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `mail_addr` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `shop_addr` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `delivery_addr` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `phone` int(10) NOT NULL,
  `fax` int(10) NOT NULL,
  `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `statement_remark` text COLLATE utf8_unicode_ci NOT NULL,
  `attn` varchar(15) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `group_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `pm_code` varchar(10) COLLATE utf8_unicode_ci NOT NULL COMMENT 'payment method code',
  `pt_code` varchar(10) COLLATE utf8_unicode_ci NOT NULL COMMENT 'payment term',
  `remark` text COLLATE utf8_unicode_ci NOT NULL,
  `create_date` datetime NOT NULL,
  `modify_date` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `t_customers`
--

INSERT INTO `t_customers` (`uid`, `cust_code`, `mail_addr`, `shop_addr`, `delivery_addr`, `phone`, `fax`, `email`, `statement_remark`, `attn`, `name`, `group_name`, `pm_code`, `pt_code`, `remark`, `create_date`, `modify_date`) VALUES
(1, 'C150301', '九龍旺角花園街217號地下', '九龍旺角花園街217號地下', '九龍旺角花園街217號地下', 9714, 0, '', '不用寄月結單', '豪哥', '峰飲食 MI-NE Restaurant', '', 'PM001', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(2, 'C150302', '新界葵涌葵豐街18-26號永康工業大廈3樓 H室', '新界葵涌葵豐街18-26號永康工業大廈3樓 H室', '新界葵涌葵豐街18-26號永康工業大廈3樓 H室', 6797, 0, '', '', '戴先生', '孖寶車仔麵', '', 'PM001', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(3, 'C150401', '香港灣仔謝斐道69號馬來西亞大廈地下G4號舖', '香港灣仔謝斐道69號馬來西亞大廈地下G4號舖', '香港灣仔謝斐道69號馬來西亞大廈地下G4號舖', 9125, 2527, '', '', 'Mo哥', '金蒂餐廳 Cinta-J', '', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(4, 'C150402', '香港柴灣利眾街24號東貿廣場6/F', '香港循理會-社會服務部香港北角百福道21號香港青年協會大廈1901室', '香港柴灣利眾街24號東貿廣場6/F', 9634, 0, '', '附上回郵信封', '蘇小姐(店長)', 'Fantastic Cafe (柴灣)', '香港循理會', 'PM003', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(5, 'C150403', '新界元朗泰衡街4號東堤街14號1樓', '新界元朗泰衡街4號東堤街14號1樓', '新界元朗泰衡街4號東堤街14號1樓', 2474, 0, '', '', '李院長', '基德(泰衡)護老院有限公司', '', 'PM001', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(6, 'C150404', '九龍佐敦德興銜18號地下', '九龍佐敦德興銜18號地下', '九龍佐敦德興銜18號地下', 9813, 0, '', '', '波仔', '好德來小籠包店', '', 'PM002', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(7, 'C150405', '九龍尖沙咀廣東道33號中港城第3座平台2-3號舖', '九龍尖沙咀廣東道33號中港城第3座平台2-3號舖', '九龍尖沙咀廣東道33號中港城第3座平台2-3號舖', 9132, 0, '', '附上回郵信封', 'Angel', '福村日本料理', '', 'PM003', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(8, 'C150406', '九龍旺角登打士街2A寶亨大廈地下 10號鋪', '九龍旺角登打士街2A寶亨大廈地下 10號鋪', '九龍旺角登打士街2A寶亨大廈地下 10號鋪', 6236, 0, '', '', '朱經理', '粵來順 (旺角)', '', 'PM001', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(9, 'C150407', '香港太古城太古城道26號漢宮閣地下411號舖', '九龍灣常悅道1號 恩浩國際中心5樓D室', '香港太古城太古城道26號漢宮閣地下411號舖', 9870, 0, '', '附上回郵信封', '黃經理', '華昌粥麵', '', 'PM001', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(10, 'C150408', '香港西環皇后大道西576-584號新景樓地下A', '香港西環皇后大道西576-584號新景樓地下A', '香港西環皇后大道西576-584號新景樓地下A', 9707, 0, '', '附上回郵信封', '蘭姐', '讚記廚房 (西環店) A Plus Kitchen', '', 'PM001', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(11, 'C150409', '九龍尖沙咀寶勒巷6-8號 盈豐商業大廈地下B鋪', '九龍尖沙咀寶勒巷6-8號 盈豐商業大廈地下B鋪', '九龍尖沙咀寶勒巷6-8號 盈豐商業大廈地下B鋪', 9707, 0, '', '附上回郵信封', '蘭姐', '讚記 (尖沙咀店)   A Plus Kitchen', '', 'PM001', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(12, 'C150410', '九龍尖沙咀厚福街8號H8 17樓', '九龍尖沙咀厚福街8號H8 17樓', '九龍尖沙咀厚福街8號H8 17樓', 0, 0, '', '', '', 'OUT DART (尖沙咀)', '', 'PM001', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `t_delivery`
--

DROP TABLE IF EXISTS `t_delivery`;
CREATE TABLE `t_delivery` (
  `uid` int(10) NOT NULL,
  `delivery_code` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `cust_code` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `delivery_addr` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `from_time` time NOT NULL,
  `to_time` time NOT NULL,
  `phone1` int(10) NOT NULL,
  `attn1` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `fax1` int(10) NOT NULL,
  `phone2` int(10) NOT NULL,
  `attn2` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `fax2` int(10) NOT NULL,
  `location` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `remark` text COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `t_employee`
--

DROP TABLE IF EXISTS `t_employee`;
CREATE TABLE `t_employee` (
  `uid` int(10) NOT NULL,
  `employee_code` int(10) NOT NULL,
  `username` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `default_shopcode` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `access_level` int(3) NOT NULL,
  `role` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `last_login` datetime NOT NULL,
  `status` tinyint(4) NOT NULL,
  `crrate_date` datetime NOT NULL,
  `modify_date` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `t_employee`
--

INSERT INTO `t_employee` (`uid`, `employee_code`, `username`, `password`, `default_shopcode`, `access_level`, `role`, `last_login`, `status`, `crrate_date`, `modify_date`) VALUES
(1, 123456, 'iamadmin', '', 'HQ01', 5, 'sales', '2018-05-22 00:00:00', 1, '2018-05-22 00:00:00', '2018-05-22 00:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `t_inventory`
--

DROP TABLE IF EXISTS `t_inventory`;
CREATE TABLE `t_inventory` (
  `uid` int(10) NOT NULL,
  `inv_code` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `item_code` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `qty` double NOT NULL,
  `type` enum('in','out','hold','') COLLATE utf8_unicode_ci NOT NULL,
  `create_date` datetime NOT NULL,
  `modify_date` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `t_items`
--

DROP TABLE IF EXISTS `t_items`;
CREATE TABLE `t_items` (
  `uid` int(10) NOT NULL,
  `item_code` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `eng_name` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
  `chi_name` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
  `desc` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `price_special` decimal(10,2) NOT NULL,
  `cate_code` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `unit` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
  `create_date` datetime NOT NULL,
  `modify_date` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `t_items`
--

INSERT INTO `t_items` (`uid`, `item_code`, `eng_name`, `chi_name`, `desc`, `price`, `price_special`, `cate_code`, `unit`, `create_date`, `modify_date`) VALUES
(1, 'GIFT', 'GIFT', 'GIFT', '                                 ', '10.00', '0.00', 'PDT', '20PCS/PACK', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(2, 'GUS0120', 'Gel Urinal Screen1', '香味尿格1', '                                                                ', '110.00', '1.00', 'SVR', '20PCS/PACK1', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(3, 'Pentax', '', 'Pentax 水泵', '', '1100.00', '0.00', 'PDT', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(4, 'DA0110', 'Graese Trap Cleaner', 'Drain Away 隔油池化油劑', '', '328.00', '0.00', 'PDT', '10 Ltr / Pail', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(6, 'DA0405', 'Graese Trap Cleaner', 'Drain Away 隔油池化油劑', '', '0.00', '0.00', 'PDT', '4X5L', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(7, 'DD0120', 'Dishmachine Detergent', 'DD-100 洗碗碟機鹼液', '', '80.00', '0.00', 'PDT', '20L', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(8, 'DD0405', 'Dishmachine Detergent', 'DD-100 洗碗碟機鹼液', '', '120.00', '0.00', 'PDT', '4X5L', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(9, 'DF0105', 'Concentrated Dishwasher Drying Agent', 'Dry Flash 高濃縮洗碗碟機快乾劑', '', '45.00', '0.00', 'PDT', '5 Ltr / Bottle', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(10, 'DF0120', 'Concentrated Dishwasher Drying Agent', 'Dry F lash 高濃縮洗碗碟機快乾劑', '', '180.00', '0.00', 'PDT', '20 Ltr / Pail', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(11, 'DF0205', 'Concentrated Dishwasher Drying Agent', 'Dry Flash 高濃縮洗碗碟機快乾劑', '', '90.00', '0.00', 'PDT', '2x5 Ltr', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(12, 'DF0405', 'Concentrated Dishwasher Drying Agent', 'Dry Flash 高濃縮洗碗碟機快乾劑', '', '180.00', '0.00', 'PDT', '4x5 Ltr / C', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(13, 'DM0104', 'Concentrated Oxygenic Stain Remover', 'Dip Master 高濃縮活氧浸漬粉', '', '80.00', '0.00', 'PDT', '4 KGS / Pail', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(14, 'DM0105', 'Concentrated Oxygenic Stain Remover', 'Dip Master 高濃縮活氧浸漬粉', '', '80.00', '0.00', 'PDT', '5 KGS / Pail', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(15, 'DM0205', 'Concentrated Oxygenic Stain Remover', 'Dip Master 高濃縮活氧浸漬粉', '', '150.00', '0.00', 'PDT', '2x5 KGS', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(16, 'DM0404', 'Concentrated Oxygenic Stain Remover', 'Dip Master 高濃縮活氧浸漬粉', '', '310.00', '0.00', 'PDT', '4x4 KGS / C', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(17, 'DM0405', 'Concentrated Oxygenic Stain Remover', 'Dip Master 高濃縮活氧浸漬粉', '', '310.00', '0.00', 'PDT', '4x5 KGS / C', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(18, 'DR0120', 'Dishmachine Rinse Agent', 'DR-100 洗碗碟機催乾劑', '', '130.00', '0.00', 'PDT', '20L', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(19, 'DR0405', 'Dishmachine Rinse Agent', 'DR-100 洗碗碟機催乾劑', '', '160.00', '0.00', 'PDT', '4X5L', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(20, 'EC0112', 'Easy Clean', '易潔', '', '200.00', '0.00', 'PDT', '12x500ml', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(21, 'EP0405', 'Electrostatic Precipitation System Degreaser', 'EP Cleaner 靜電油煙系統除油劑', '', '110.00', '0.00', 'PDT', '4x5 Ltr / C', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(22, 'EWP0204', 'High Concentrated Multi-Purpose Cleaner', 'Easy Wash Plus 超濃縮全能清潔劑', '', '65.00', '0.00', 'PDT', '2x4 Ltr', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(23, 'EWP0404', 'High Concentrated Multi-Purpose Cleaner', 'Easy Wash Plus 超濃縮全能清潔劑', '', '130.00', '0.00', 'PDT', '4x4 Ltr / C', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(24, 'F160205', 'Chlorinated Floor Cleaner', 'F-16 高濃縮氯性地面清潔劑', '', '67.00', '0.00', 'PDT', '2x5 Ltr', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(25, 'F160405', 'Chlorinated Floor Cleaner', 'F-16 高濃縮氯性地面清潔劑', '', '138.00', '0.00', 'PDT', '4x5 Ltr / C', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(26, 'FC0405', 'Kitchen Floor Cleaner', 'FC-100 廚房地板清潔劑', '', '140.00', '0.00', 'PDT', '4x5 Ltr / C', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(27, 'GD0405', 'Grill Degreaser', '爐具清潔劑', '', '340.00', '0.00', 'PDT', '4X5L', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(28, 'GD-P', 'High Concentrated Grill Degreaser', 'GD-Plus 超濃縮扒爐水', '', '170.00', '0.00', 'PDT', '4x1Gal', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(29, 'GDP0401', 'High Concentrated Grill Degreaser', 'GD-Plus 超濃縮扒爐水', '', '300.00', '0.00', 'PDT', '4x1Gal', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(30, 'GDP0405', 'High Concentrated Grill Degreaser', 'GD-Plus 超濃縮爐具清潔劑', '', '300.00', '0.00', 'PDT', '4x5 Ltr / C', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(31, 'GR0205', 'Heavy Duty Degreaser', 'Grease Remover 強力爐灶除油劑 ', '', '123.00', '0.00', 'PDT', '2x5 Ltr', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(32, 'GR0401', 'Heavy Duty Degreaser', 'Grease Remover 強力爐灶除油劑 ', '', '169.00', '0.00', 'PDT', '4x1 Gal / C', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(33, 'GR0405', 'Heavy Duty Degreaser', 'Grease Remover 強力爐灶除油劑 ', '', '251.00', '0.00', 'PDT', '4x5 Ltr / C', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(34, 'GT0110', 'Grease Trap Degreaser', 'GT-100 隔油池化油劑', '', '330.00', '0.00', 'PDT', '10L', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(35, 'GT0205', 'Grease Trap Degreaser', 'GT-100 隔油池化油劑', '', '340.00', '0.00', 'PDT', '2x5L', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(36, 'GT0405', 'Grease Trap Degreaser', 'GT-100 隔油池化油劑', '', '680.00', '0.00', 'PDT', '4X5L', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(37, 'H10201', 'Antimicrobial Hand Soap', 'H-1 消毒洗手皂液', '', '60.00', '0.00', 'PDT', '2X1G', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(38, 'H10401', 'Antimicrobial Hand Soap', 'H-1 消毒洗手皂液 ', '', '120.00', '0.00', 'PDT', '4x1 Gal / C', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(39, 'HF0105', 'Foam Type Hand Soap', 'H 1 消毒泡沬洗手皂液 ', '', '100.00', '0.00', 'PDT', '5 KGS / Pail', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(40, 'HS0401', 'Hand Soap', '洗手液', '', '120.00', '0.00', 'PDT', '4X1G', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(41, 'HW0405', 'Hand Washing Cleaner', 'HW-100 手洗清潔劑', '', '190.00', '0.00', 'PDT', '4X5L', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(42, 'LR0120', 'Concentrated Lime Scale Remover', 'Lime Remover 超高濃縮水垢去除劑', '', '200.00', '0.00', 'PDT', '20 Ltr / Pail', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(43, 'LR0205', 'Concentrated Lime Scale Remover', 'Lime Remover 超高濃縮水垢去除劑', '', '120.00', '0.00', 'PDT', '2x5 Ltr', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(44, 'LR0401', 'Concentrated Lime Scale Remover', 'Lime Remover 超高濃縮水垢去除劑', '', '245.00', '0.00', 'PDT', '4x1 Gal / C', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(45, 'LR0405', 'Concentrated Lime Scale Remover', 'Lime Remover 超高濃縮水垢去除劑', '', '245.00', '0.00', 'PDT', '4x5 Ltr / C', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(46, 'MFOLD', 'M-Fold Paper', '方型抹手紙 ', '', '150.48', '0.00', 'PDT', '250 Pcs x 16 Pack / C', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(47, 'MFTB', 'Micro Fiber  Cloth (Blue)', 'M - Cloth 微纖布 (藍色)', '', '35.00', '0.00', 'PDT', '10 Pcs / Pack', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(48, 'MFTG', 'Micro Fiber  Cloth (Green)', 'M - Cloth 微纖布(綠色)', '', '35.00', '0.00', 'PDT', '10 Pcs / Pack', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(49, 'MFTN', 'Micro Fiber  Cloth (Brown)', 'M - Cloth 微纖布 (啡色)', '', '35.00', '0.00', 'PDT', '10 Pcs / Pack', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(50, 'MFTY', 'Micro Fiber  Cloth (Yellow)', 'M - Cloth 微纖布 (黃色)', '', '35.00', '0.00', 'PDT', '10 Pcs / Pack', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(51, 'MP0401', 'Multi Purposes Cleaner', 'MP-100 多用途清潔劑', '', '110.00', '0.00', 'PDT', '4X1G', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(52, 'NC0105', 'Concentrated Neutral Multi-Purposes Cleaner', 'NC-360 中性多用途清潔劑', '', '0.00', '0.00', 'PDT', '5L', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(53, 'NC0401', 'Concentrated Neutral Multi-Purposes Cleaner', 'NC-360 中性多用途清潔劑', '', '109.00', '0.00', 'PDT', '4x1 Gal / C', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(54, 'OC0401', 'Heavy Duty Degreaser', 'Oven Cleaner 爐灶除油劑', '', '169.00', '0.00', 'PDT', '4x1 Gal / C', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(55, 'OYP0105', 'Heavy Duty Alkaline Degreaser Powder', 'OYP 強力鹼性除油粉', '', '67.50', '0.00', 'PDT', '5KG', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(56, 'OYP0120', 'Heavy Duty Alkaline Degreaser Powder', 'OYP 強力鹼性除油粉', '', '230.00', '0.00', 'PDT', '20 KGS / Pail', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(57, 'OYP0205', 'Heavy Duty Alkaline Degreaser Powder', 'OYP 強力鹼性除油粉', '', '140.00', '0.00', 'PDT', '2x5KGS', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(58, 'OYP0405', 'Heavy Duty Alkaline Degreaser Powder', 'OYP 強力鹼性除油粉', '', '270.00', '0.00', 'PDT', '4x5KGS', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(59, 'PD0105', 'Concentrated  Heavy Duty Degreaser', 'Power Degreaser 高濃縮強力爐灶除油劑', '', '85.00', '0.00', 'PDT', '5 Ltr / Bottle', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(60, 'PD0110', 'Concentrated  Heavy Duty Degreaser', 'Power Degreaser 高濃縮強力爐灶除油劑 高濃縮強力爐灶除油劑', '', '0.00', '0.00', 'PDT', '10L', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(61, 'PD0205', 'Concentrated  Heavy Duty Degreaser', 'Power Degreaser 高濃縮強力爐灶除油劑', '', '165.00', '0.00', 'PDT', '2x5 Ltr', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(62, 'PD0405', 'Concentrated  Heavy Duty Degreaser', 'Power Degreaser 高濃縮強力爐灶除油劑', '', '335.00', '0.00', 'PDT', '4x5 Ltr / C', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(63, 'SC0101', 'Liquid Stainless Steel Cleaner & Polish', 'Steel Cleaner 不銹鋼潔亮劑', '', '60.00', '0.00', 'PDT', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(64, 'SC0401', 'Liquid Stainless Steel Cleaner & Polish', 'Steel Cleaner 不銹鋼潔亮劑', '', '240.00', '0.00', 'PDT', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(65, 'SG0101', 'Sanitizer Gel', 'Sanitizer Gel', '', '220.00', '0.00', 'PDT', '1GAL', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(66, 'SQ0101', 'Sanitizer', 'Sani-Q 餐具殺菌消毒劑', '', '240.00', '0.00', 'PDT', '1 Gal / Bottle', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(67, 'SR0105', 'Stain Remover', 'SR-100 浸漬粉', '', '80.00', '0.00', 'PDT', '5KG', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(68, 'SS0104', 'Enzyme Activated Presoak & Silver Detarnisher', 'Silver Shine 浸銀粉', '', '120.00', '0.00', 'PDT', '4 KGS / Pail', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(69, 'SS0105', 'Enzyme Activated Presoak & Silver Detarnisher', 'Silver Shine 浸銀粉', '', '120.00', '0.00', 'PDT', '5 KGS / Pail', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(70, 'T100205', 'Concentrated Pot and Pan Cleaner', 'T-10 高濃縮餐具清潔劑', '', '100.00', '0.00', 'PDT', '2x5 Ltr', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(71, 'T100405', 'Concentrated Pot and Pan Cleaner', 'T-10 高濃縮餐具清潔劑', '', '190.00', '0.00', 'PDT', '4x5 Ltr / C', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(72, 'T50205', 'Pot and Pan Cleaner', 'T-5 餐具清潔劑', '', '70.00', '0.00', 'PDT', '2x5 Ltr', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(73, 'T50405', 'Pot and Pan Cleaner', 'T-5 餐具清潔劑', '', '150.00', '0.00', 'PDT', '4x5 Ltr / C', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(74, 'TD0105', 'Dishwasher Detergent', 'TD-150 洗碗碟機清潔劑', '', '30.00', '0.00', 'PDT', '5 Ltr / Bottle', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(75, 'TD0120', 'Dishwasher Detergent', 'TD-150 洗碗碟機清潔劑', '', '90.00', '0.00', 'PDT', '20 Ltr / Pail', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(76, 'TD0205', 'Dishwasher Detergent', 'TD-150 洗碗碟機清潔劑', '', '58.00', '0.00', 'PDT', '2x5 Ltr', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(77, 'TD0405', 'Dishwasher Detergent', 'TD-150 洗碗碟機清潔劑', '', '120.01', '0.00', 'PDT', '4x5 Ltr / C', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(78, 'TR0105', 'Drying Agent', 'TR-250 洗碗碟機快乾劑', '', '35.00', '0.00', 'PDT', '5 Ltr / Bottle', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(79, 'TR0120', 'Drying Agent', 'TR-250 洗碗碟機快乾劑', '', '130.00', '0.00', 'PDT', '20 Ltr / Pail', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(80, 'TR0205', 'Drying Agent', 'TR-250 洗碗碟機快乾劑', '', '80.00', '0.00', 'PDT', '2x5 Ltr', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(81, 'TR0405', 'Drying Agent', 'TR-250 洗碗碟機快乾劑', '', '160.00', '0.00', 'PDT', '4x5 Ltr / C', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(82, 'TS0105', 'Concentrated Dishwasher Detergent', 'Top Shine 高濃縮洗碗碟機清潔劑', '', '40.00', '0.00', 'PDT', '5 Ltr / Bottle', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(83, 'TS0120', 'Concentrated Dishwasher Detergent', 'Top Shine 高濃縮洗碗碟機清潔劑', '', '120.00', '0.00', 'PDT', '20 Ltr / Pail', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(84, 'TS0205', 'Concentrated Dishwasher Detergent', 'Top Shine 高濃縮洗碗碟機清潔劑', '', '80.00', '0.00', 'PDT', '2x5 Ltr', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(85, 'TS0405', 'Concentrated Dishwasher Detergent', 'Top Shine 高濃縮洗碗碟機清潔劑', '', '150.00', '0.00', 'PDT', '4x5 Ltr / C', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(86, 'TW0120', 'Chlorinated Sanitizer and Stain Remover', 'TW-11 氯性消毒除漬劑', '', '52.00', '0.00', 'PDT', '20 Ltr / Pail', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(87, 'TW0205', 'Chlorinated Sanitizer and Stain Remover', 'TW-11 氯性消毒除漬劑', '', '45.00', '0.00', 'PDT', '2x5L', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(88, 'TW0405', 'Chlorinated Sanitizer and Stain Remover', 'TW-11 氯性消毒除漬劑', '', '86.88', '0.00', 'PDT', '4x5 Ltr / C', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(89, 'U20104', 'Urinal Drain Cleaner', 'U-2  生物廁盆尿石去除劑', '', '270.00', '0.00', 'PDT', '4 Ltr / Bottle', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(90, 'VC0120', 'Hydro Ventilator Hood Detergent', 'VC 運水煙罩化油劑', '', '63.00', '0.00', 'PDT', '20 Ltr / Pail', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(91, 'VC0401', 'Hydro Ventilator Hood Detergent', 'VC 運水煙罩化油劑', '', '90.00', '0.00', 'PDT', '4x1 Gal / C', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(92, 'VC0405', 'Hydro Ventilator Hood Detergent', 'VC 運水煙罩化油劑', '', '96.00', '0.00', 'PDT', '4x5 Ltr / C', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(93, 'VD0405', 'Hydro Vent Degreaser', '運水煙罩化油劑', '', '100.00', '0.00', 'PDT', '4X5L', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(94, 'CN-1', 'CANAAN CN-1 Door Type Dishwasher', 'CANAAN CN-1 揭門式洗碗碟機', '', '13000.00', '0.00', 'PDT', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(95, 'CN-1E', 'CANAAN CN-1E Type Dishwasher', 'CANAAN CN-1E 揭門式洗碗碟機', '', '13000.00', '0.00', 'PDT', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(96, 'CN-F', 'CANAAN Channel Type Dishwasher', 'CANAAN CN-F 通道式洗碗碟機', '', '35000.00', '0.00', 'PDT', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(97, 'DR01', 'Plate Rack', '洗碗碟機用針篩', '', '60.00', '0.00', 'PDT', '1 Pcs', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(98, 'DR02', 'Open Rack', '洗碗碟機用平篩 ', '', '60.00', '0.00', 'PDT', '1 Pcs', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(99, 'DR03', 'Glass Rack', '洗碗碟機用16格杯篩 ', '', '0.00', '0.00', 'PDT', '1 Pcs', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(100, 'DR04', '', '8格刀叉籃', '', '50.00', '0.00', 'PDT', '1 Pcs', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(101, 'DR05', '', '有柄杯篩車', '', '150.00', '0.00', 'PDT', '1 Pcs', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(102, 'DR06', '', '洗碗碟機用25格杯篩 Glass Rack', '', '0.00', '0.00', 'PDT', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(103, 'DR07', '', '杯機專用平篩 400x400x150mm', '', '50.00', '0.00', 'PDT', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(104, 'DR08', '', '洗碗碟機用36格杯篩 Glass Rack', '', '0.00', '0.00', 'PDT', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(105, 'DR09', '', '圓桶形刀叉籃', '', '10.00', '0.00', 'PDT', '3 PCs', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(106, 'GT-CR1', 'G-Tek GT-CR1 Conveyor Type Dishwasher', 'G-Tek GT-CR1 運輸帶式洗碗碟機 ', '', '47600.00', '0.00', 'PDT', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(107, 'GT-D1', 'G-Tek GT-D1 Door Type Dishwasher', 'G-Tek GT-D1 揭門式單缸洗碗碟機Door type dishwasher', '', '16500.00', '0.00', 'PDT', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(108, 'HSFD', 'Foam Type Hand Soap Dispenser', '泡沬洗手皂液機 ', '', '30.00', '0.00', 'PDT', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(109, 'SS-TABLE', 'Stainless Steel Table', '不銹鋼檯', '', '1000.00', '0.00', 'PDT', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(110, 'TS-603', 'Teikos TS-603 Under-Counter Dishwasher', 'Teikos TS-603  檯下式洗碗碟機', '', '10000.00', '0.00', 'PDT', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(111, 'TS-830', 'Teikos TS-830 Under-Counter Glasswasher', 'Teikos TS-830  檯下式洗杯機', '', '8000.00', '0.00', 'PDT', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(112, 'VCPUMP', '', '運水煙罩儀器', '', '400.00', '0.00', 'PDT', '1 PCS', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(113, 'Deposit', 'Deposit', '洗碗碟機按金', '', '0.00', '0.00', 'PDT', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(114, 'FL', 'Fly Light Rental Fee', '捕蠅燈機租用月費', '', '30.00', '0.00', 'PDT', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(115, 'R001', 'Diswasher Chemicals Monthly Fee', '洗碗碟機清潔劑服務月費', '', '0.00', '0.00', 'PDT', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(116, 'R002', 'Dishwasher Full Service Plan', '洗碗碟機連清潔劑(全包)月費', '', '0.00', '0.00', 'PDT', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(117, 'Rental', 'Diswasher Monthly Rental', '洗碗碟機租用服務月費', '', '0.00', '0.00', 'PDT', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(118, 'GWDP', '', 'Glass Washer Drain Pipe', '', '100.00', '0.00', 'PDT', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(119, 'Parts', 'Diswasher Parts', '洗碗碟機零件', '', '0.00', '0.00', 'PDT', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(120, 'CHVS', '', '油煙罩系統清潔服務', '', '0.00', '0.00', 'PDT', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(121, 'CSS', '', '清洗蒸櫃服務連除垢劑', '', '0.00', '0.00', 'PDT', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(122, 'JOB', '', '洗碗機及杯機裝拆費用', '', '0.00', '0.00', 'PDT', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(123, 'P00001', 'Inspection Fee', '洗碗碟機檢查費', '', '0.00', '0.00', 'PDT', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(125, 'test1', 'test1', 'test1', '', '212.00', '334.00', 'PDT', '4X3', '2018-09-13 18:41:34', '0000-00-00 00:00:00'),
(126, 'icinddkmad', 'ksndej', 'kadksd', '', '23.00', '33.00', 'PDT', '4X3 L', '2018-09-15 16:54:17', '0000-00-00 00:00:00'),
(127, 'uteoi', 'asdin', 'isnf', '', '213.00', '3213.00', 'SVR', '4X5', '2018-09-16 10:48:49', '0000-00-00 00:00:00'),
(130, 'sadad', 'asda', 'dasd', '', '123.00', '123.00', 'SVR', '4X3', '2018-09-16 12:52:16', '0000-00-00 00:00:00'),
(135, 'scjsdck', 'nskcn', 'jknksncknskc', '', '32.00', '32.00', 'PDT', '', '2018-09-16 18:20:00', '0000-00-00 00:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `t_items_category`
--

DROP TABLE IF EXISTS `t_items_category`;
CREATE TABLE `t_items_category` (
  `uid` int(10) NOT NULL,
  `cate_code` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `desc` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
  `create_date` datetime NOT NULL,
  `modify_date` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `t_items_category`
--

INSERT INTO `t_items_category` (`uid`, `cate_code`, `desc`, `create_date`, `modify_date`) VALUES
(1, 'PDT', 'Product', '2018-04-25 00:00:00', '2018-04-25 00:00:00'),
(2, 'SVR', 'Service ', '2018-04-25 00:00:00', '2018-04-25 00:00:00'),
(3, 'MAT', 'Maintenance', '2018-04-25 00:00:00', '2018-04-25 00:00:00'),
(15, 'Can', 'a box of can gategory', '2018-10-03 17:29:33', '0000-00-00 00:00:00'),
(16, 'try', 'trt', '2018-10-04 18:07:24', '0000-00-00 00:00:00'),
(17, 'category1', 'testing item', '2018-10-08 18:46:35', '0000-00-00 00:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `t_items_price`
--

DROP TABLE IF EXISTS `t_items_price`;
CREATE TABLE `t_items_price` (
  `uid` int(10) NOT NULL,
  `item_code` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `create_date` datetime NOT NULL,
  `modify_date` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `t_payment_method`
--

DROP TABLE IF EXISTS `t_payment_method`;
CREATE TABLE `t_payment_method` (
  `uid` int(10) NOT NULL,
  `pm_code` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `payment_method` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `create_date` datetime NOT NULL,
  `modify_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `t_payment_method`
--

INSERT INTO `t_payment_method` (`uid`, `pm_code`, `payment_method`, `create_date`, `modify_date`) VALUES
(1, 'PM001', 'Cash', '2018-05-19 00:00:00', '2018-05-19 10:18:24'),
(2, 'PM002', 'C.O.D', '0000-00-00 00:00:00', '2018-05-28 16:13:26'),
(3, 'PM003', 'Monthly', '0000-00-00 00:00:00', '2018-05-28 16:14:10');

-- --------------------------------------------------------

--
-- Table structure for table `t_payment_term`
--

DROP TABLE IF EXISTS `t_payment_term`;
CREATE TABLE `t_payment_term` (
  `uid` int(11) NOT NULL,
  `pt_code` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `terms` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `create_date` datetime NOT NULL,
  `modify_date` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `t_prefix`
--

DROP TABLE IF EXISTS `t_prefix`;
CREATE TABLE `t_prefix` (
  `uid` int(10) NOT NULL,
  `prefix` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `desc` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `status` tinyint(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `t_prefix`
--

INSERT INTO `t_prefix` (`uid`, `prefix`, `desc`, `status`) VALUES
(1, 'INV', 'Invoices ', 1),
(2, 'PO', 'Purchase Order', 1),
(3, 'QU', 'Quotation', 1);

-- --------------------------------------------------------

--
-- Table structure for table `t_shop`
--

DROP TABLE IF EXISTS `t_shop`;
CREATE TABLE `t_shop` (
  `uid` int(10) NOT NULL,
  `shop_code` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
  `phone` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `address1` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `address2` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `create_date` datetime NOT NULL,
  `modify_date` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `t_shop`
--

INSERT INTO `t_shop` (`uid`, `shop_code`, `name`, `phone`, `address1`, `address2`, `create_date`, `modify_date`) VALUES
(1, 'HQ01', 'AABB LTD (HONG KONG)', '23992122', 'Kowloon City, Hung To Rd, 19號10樓1006室', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(2, 'HQ02', 'DD moving Co, (HK)', '22021010', 'Kwun Tong, 九龍觀塘鴻圖道37號鴻泰工業大廈', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `t_transaction_d`
--

DROP TABLE IF EXISTS `t_transaction_d`;
CREATE TABLE `t_transaction_d` (
  `uid` int(10) NOT NULL,
  `trans_code` varchar(25) COLLATE utf8_unicode_ci NOT NULL,
  `item_code` varchar(25) COLLATE utf8_unicode_ci NOT NULL,
  `eng_name` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
  `chi_name` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
  `qty` decimal(5,1) NOT NULL,
  `unit` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `discount` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `create_date` datetime NOT NULL,
  `modify_date` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `t_transaction_d`
--

INSERT INTO `t_transaction_d` (`uid`, `trans_code`, `item_code`, `eng_name`, `chi_name`, `qty`, `unit`, `price`, `discount`, `create_date`, `modify_date`) VALUES
(6, 'INV2018080506', 'DR0120', 'Dishmachine Rinse Agent', 'DR-100 洗碗碟機催乾劑', '1.0', '20L', '130.00', '', '2018-08-05 08:37:07', '2018-09-24 19:36:58'),
(11, 'INV2018080506', 'DD0120', 'Dishmachine Detergent', 'DD-100 洗碗碟機鹼液', '2.0', '20L', '80.00', '', '2018-08-05 08:37:07', '2018-09-24 19:36:58'),
(13, 'INV2018080506', 'EC0112', 'Easy Clean', '易潔', '1.0', '12x500ml', '200.00', '', '2018-08-05 08:37:07', '2018-09-24 19:36:58'),
(14, 'INV2018080506', 'EWP0404', 'High Concentrated Multi-Purpose Cleaner', 'Easy Wash Plus 超濃縮全能清潔劑', '1.0', '4x4 Ltr / C', '130.00', '', '2018-08-05 08:37:07', '2018-09-24 19:36:58'),
(15, 'INV2018081451', 'DA0110', 'Graese Trap Cleaner', 'Drain Away 隔油池化油劑', '3.0', '10 Ltr / Pail', '328.00', '', '2018-08-14 15:48:12', '2018-10-06 02:12:35'),
(16, 'INV2018081451', 'DD0120', 'Dishmachine Detergent', 'DD-100 洗碗碟機鹼液', '3.0', '20L', '80.00', '', '2018-08-14 15:48:12', '2018-10-06 02:12:35'),
(17, 'INV2018081442', 'DA0205', 'Graese Trap Cleaner', 'Drain Away 隔油池化油劑', '1.0', '2x5 Ltr', '340.00', '', '2018-08-14 17:05:43', '2018-09-25 07:42:14'),
(18, 'INV2018081442', 'MFOLD', 'M-Fold Paper', '方型抹手紙 ', '2.0', '250 Pcs x 16 Pack / C', '150.48', '', '2018-08-14 17:05:43', '2018-09-25 07:42:14'),
(19, 'INV2018081442', 'SR0105', 'Stain Remover', 'SR-100 浸漬粉', '1.0', '5KG', '80.00', '', '2018-08-14 17:05:43', '2018-09-25 07:42:14'),
(20, 'INV2018081425', 'MFTN', 'Micro Fiber  Cloth (Brown)', 'M - Cloth 微纖布 (啡色)', '1.0', '10 Pcs / Pack', '35.00', '', '2018-08-14 17:05:25', '2018-09-16 12:17:08'),
(21, 'INV2018081425', 'DR01', 'Plate Rack', '洗碗碟機用針篩', '1.0', '1 Pcs', '60.00', '', '2018-08-14 17:05:25', '2018-09-16 12:17:08'),
(22, 'INV2018081425', 'GT-CR1', 'G-Tek GT-CR1 Conveyor Type Dishwasher', 'G-Tek GT-CR1 運輸帶式洗碗碟機 ', '1.0', '', '47600.00', '', '2018-08-14 17:05:25', '2018-09-16 12:17:08'),
(23, 'INV2018090305', 'DA0205', 'Graese Trap Cleaner', 'Drain Away 隔油池化油劑', '1.0', '2x5 Ltr', '340.00', '', '2018-09-03 15:54:05', '0000-00-00 00:00:00'),
(24, 'INV2018090305', 'F160205', 'Chlorinated Floor Cleaner', 'F-16 高濃縮氯性地面清潔劑', '1.0', '2x5 Ltr', '67.00', '', '2018-09-03 15:54:05', '0000-00-00 00:00:00'),
(25, 'INV2018090305', 'GD-P', 'High Concentrated Grill Degreaser', 'GD-Plus 超濃縮扒爐水', '1.0', '4x1Gal', '170.00', '', '2018-09-03 15:54:05', '0000-00-00 00:00:00'),
(26, 'INV2018090305', 'MP0401', 'Multi Purposes Cleaner', 'MP-100 多用途清潔劑', '1.0', '4X1G', '110.00', '', '2018-09-03 15:54:05', '0000-00-00 00:00:00'),
(27, 'INV2018091852', 'DD0120', 'Dishmachine Detergent', 'DD-100 洗碗碟機鹼液', '2.0', '20L', '80.00', '', '2018-09-18 17:56:53', '0000-00-00 00:00:00'),
(28, 'INV2018091852', 'GD0405', 'Grill Degreaser', '爐具清潔劑', '1.0', '4X5L', '340.00', '', '2018-09-18 17:56:53', '0000-00-00 00:00:00'),
(29, 'INV2018091852', 'DM0105', 'Concentrated Oxygenic Stain Remover', 'Dip Master 高濃縮活氧浸漬粉', '2.0', '5 KGS / Pail', '80.00', '', '2018-09-18 17:56:53', '0000-00-00 00:00:00'),
(30, 'INV2018091852', 'DM0404', 'Concentrated Oxygenic Stain Remover', 'Dip Master 高濃縮活氧浸漬粉', '4.0', '4x4 KGS / C', '310.00', '', '2018-09-18 17:56:53', '0000-00-00 00:00:00'),
(31, 'INV2018100639', 'DD0120', 'Dishmachine Detergent', 'DD-100 洗碗碟機鹼液', '6.0', '20L', '80.00', '', '2018-10-06 02:01:39', '0000-00-00 00:00:00'),
(32, 'INV2018100621', 'DD0120', 'Dishmachine Detergent', 'DD-100 洗碗碟機鹼液', '1.0', '20L', '80.00', '', '2018-10-06 04:01:27', '0000-00-00 00:00:00'),
(33, 'INV2018100619', 'DD0120', 'Dishmachine Detergent', 'DD-100 洗碗碟機鹼液', '34.0', '20L', '80.00', '', '2018-10-06 04:35:49', '0000-00-00 00:00:00'),
(34, 'INV2018100619', 'DA0110', 'Graese Trap Cleaner', 'Drain Away 隔油池化油劑', '6.0', '10 Ltr / Pail', '328.00', '', '2018-10-06 04:35:49', '0000-00-00 00:00:00'),
(35, 'INV2018100845', 'DA0110', 'Graese Trap Cleaner', 'Drain Away 隔油池化油劑', '7.0', '10 Ltr / Pail', '328.00', '', '2018-10-08 18:44:45', '0000-00-00 00:00:00'),
(36, 'INV2018100845', 'DF0105', 'Concentrated Dishwasher Drying Agent', 'Dry Flash 高濃縮洗碗碟機快乾劑', '2.0', '5 Ltr / Bottle', '45.00', '', '2018-10-08 18:44:45', '0000-00-00 00:00:00'),
(37, 'INV2018100845', 'DF0120', 'Concentrated Dishwasher Drying Agent', 'Dry F lash 高濃縮洗碗碟機快乾劑', '2.0', '20 Ltr / Pail', '180.00', '', '2018-10-08 18:44:45', '0000-00-00 00:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `t_transaction_h`
--

DROP TABLE IF EXISTS `t_transaction_h`;
CREATE TABLE `t_transaction_h` (
  `uid` int(10) NOT NULL,
  `trans_code` varchar(25) COLLATE utf8_unicode_ci NOT NULL,
  `cust_code` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `quotation_code` varchar(25) COLLATE utf8_unicode_ci NOT NULL,
  `prefix` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `employee_code` int(10) NOT NULL,
  `shop_code` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `remark` text COLLATE utf8_unicode_ci NOT NULL,
  `is_void` tinyint(1) NOT NULL,
  `create_date` datetime NOT NULL,
  `modify_date` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `t_transaction_h`
--

INSERT INTO `t_transaction_h` (`uid`, `trans_code`, `cust_code`, `quotation_code`, `prefix`, `total`, `employee_code`, `shop_code`, `remark`, `is_void`, `create_date`, `modify_date`) VALUES
(1, 'INV2018080506', 'C150301', '', 'INV', '620.00', 110022, 'HQ01', 'test\ntesthttp://[::1]/webapp/invoices/edit/INV2018080506\n#20 	Dishmachine Rinse Agent 	DR-100 洗碗碟機催乾劑 	1.0 	20L 	130.00 	130.00 	\n2 	DD0120 	D', 0, '2018-08-05 08:37:07', '2018-09-24 19:36:58'),
(2, 'INV2018081451', 'C150301', '', 'INV', '408.00', 110022, 'HQ01', 'test\n\ntest\n', 0, '2018-08-14 15:48:12', '2018-10-06 02:12:35'),
(3, 'INV2018081442', 'C150403', '', 'INV', '720.96', 110022, 'HQ01', 'terrrwewe\ndefw\nfef\n\nfew', 0, '2018-08-14 17:05:43', '2018-09-25 07:42:14'),
(4, 'INV2018081425', 'C150302', '', 'INV', '47695.00', 110022, 'HQ01', 'yeah\nunr\nad\n\nasd\nlarge price', 0, '2018-08-14 17:05:25', '2018-09-16 12:17:08'),
(5, 'INV2018090305', 'C150404', '', 'INV', '687.00', 110022, 'HQ01', 'new test', 0, '2018-09-03 15:54:05', '0000-00-00 00:00:00'),
(6, 'INV2018091852', 'C150408', '', 'INV', '1900.00', 110022, 'HQ01', 'test test', 0, '2018-09-18 17:56:53', '0000-00-00 00:00:00'),
(7, 'INV2018100639', 'C150404', '', 'INV', '80.00', 110022, 'HQ02', 'test', 0, '2018-10-06 02:01:39', '0000-00-00 00:00:00'),
(8, 'INV2018100621', 'C150403', '', 'INV', '80.00', 110022, 'HQ01', '', 0, '2018-10-06 04:01:27', '0000-00-00 00:00:00'),
(9, 'INV2018100619', 'C150403', '', 'INV', '4688.00', 110022, 'HQ01', '', 0, '2018-10-06 04:35:49', '0000-00-00 00:00:00'),
(10, 'INV2018100845', 'C150404', '', 'INV', '2746.00', 110022, 'HQ02', '', 0, '2018-10-08 18:44:45', '0000-00-00 00:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `t_transaction_t`
--

DROP TABLE IF EXISTS `t_transaction_t`;
CREATE TABLE `t_transaction_t` (
  `uid` int(10) NOT NULL,
  `trans_code` varchar(25) COLLATE utf8_unicode_ci NOT NULL,
  `pm_code` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `create_date` datetime NOT NULL,
  `modify_date` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `t_transaction_t`
--

INSERT INTO `t_transaction_t` (`uid`, `trans_code`, `pm_code`, `total`, `create_date`, `modify_date`) VALUES
(1, 'INV2018080506', 'PM001', '620.00', '2018-08-05 08:37:07', '2018-09-24 19:36:58'),
(2, 'INV2018081451', 'PM001', '408.00', '2018-08-14 15:48:12', '2018-10-06 02:12:35'),
(3, 'INV2018081442', 'PM001', '720.96', '2018-08-14 17:05:43', '2018-09-25 07:42:14'),
(4, 'INV2018081425', 'PM002', '47695.00', '2018-08-14 17:05:25', '2018-09-16 12:17:08'),
(5, 'INV2018090305', 'PM002', '687.00', '2018-09-03 15:54:05', '0000-00-00 00:00:00'),
(6, 'INV2018091852', 'PM001', '1900.00', '2018-09-18 17:56:53', '0000-00-00 00:00:00'),
(7, 'INV2018100639', 'PM002', '80.00', '2018-10-06 02:01:39', '0000-00-00 00:00:00'),
(8, 'INV2018100621', 'PM001', '80.00', '2018-10-06 04:01:27', '0000-00-00 00:00:00'),
(9, 'INV2018100619', 'PM001', '4688.00', '2018-10-06 04:35:49', '0000-00-00 00:00:00'),
(10, 'INV2018100845', 'PM002', '2746.00', '2018-10-08 18:44:45', '0000-00-00 00:00:00');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `t_customers`
--
ALTER TABLE `t_customers`
  ADD PRIMARY KEY (`uid`),
  ADD UNIQUE KEY `custcode_unique` (`cust_code`);

--
-- Indexes for table `t_delivery`
--
ALTER TABLE `t_delivery`
  ADD PRIMARY KEY (`uid`),
  ADD UNIQUE KEY `deliverycode_unique` (`delivery_code`) USING BTREE,
  ADD KEY `delivery_code_index` (`delivery_code`) USING BTREE;

--
-- Indexes for table `t_employee`
--
ALTER TABLE `t_employee`
  ADD PRIMARY KEY (`uid`),
  ADD UNIQUE KEY `unique_employee_code` (`employee_code`);

--
-- Indexes for table `t_inventory`
--
ALTER TABLE `t_inventory`
  ADD PRIMARY KEY (`uid`),
  ADD UNIQUE KEY `uid_unique` (`inv_code`) USING BTREE,
  ADD KEY `uid_index` (`inv_code`) USING BTREE;

--
-- Indexes for table `t_items`
--
ALTER TABLE `t_items`
  ADD PRIMARY KEY (`uid`),
  ADD UNIQUE KEY `uid_unique` (`item_code`) USING BTREE,
  ADD KEY `uid_index` (`item_code`) USING BTREE;

--
-- Indexes for table `t_items_category`
--
ALTER TABLE `t_items_category`
  ADD PRIMARY KEY (`uid`),
  ADD UNIQUE KEY `uid_unique` (`uid`) USING BTREE,
  ADD UNIQUE KEY `cate_code_unique` (`cate_code`) USING BTREE,
  ADD KEY `uid_index` (`uid`) USING BTREE;

--
-- Indexes for table `t_items_price`
--
ALTER TABLE `t_items_price`
  ADD PRIMARY KEY (`uid`),
  ADD UNIQUE KEY `uid_unique` (`uid`) USING BTREE,
  ADD KEY `uid_index` (`uid`) USING BTREE;

--
-- Indexes for table `t_payment_method`
--
ALTER TABLE `t_payment_method`
  ADD PRIMARY KEY (`uid`),
  ADD UNIQUE KEY `pm_unique` (`pm_code`);

--
-- Indexes for table `t_prefix`
--
ALTER TABLE `t_prefix`
  ADD PRIMARY KEY (`uid`),
  ADD UNIQUE KEY `prefix_unique` (`prefix`) USING BTREE,
  ADD KEY `uid_index` (`uid`) USING BTREE;

--
-- Indexes for table `t_shop`
--
ALTER TABLE `t_shop`
  ADD PRIMARY KEY (`uid`),
  ADD UNIQUE KEY `shopcode_unique` (`shop_code`) USING BTREE,
  ADD KEY `shopcode_index` (`shop_code`) USING BTREE;

--
-- Indexes for table `t_transaction_d`
--
ALTER TABLE `t_transaction_d`
  ADD PRIMARY KEY (`uid`),
  ADD KEY `uid_index` (`uid`) USING BTREE,
  ADD KEY `transcode_unique` (`trans_code`) USING BTREE;

--
-- Indexes for table `t_transaction_h`
--
ALTER TABLE `t_transaction_h`
  ADD PRIMARY KEY (`uid`) USING BTREE,
  ADD UNIQUE KEY `transcode_unique` (`trans_code`) USING BTREE,
  ADD UNIQUE KEY `uid_index` (`uid`) USING BTREE,
  ADD KEY `transcode_index` (`trans_code`) USING BTREE;

--
-- Indexes for table `t_transaction_t`
--
ALTER TABLE `t_transaction_t`
  ADD PRIMARY KEY (`uid`) USING BTREE,
  ADD UNIQUE KEY `transcode_unique` (`trans_code`) USING BTREE,
  ADD KEY `transcode_index` (`trans_code`) USING BTREE,
  ADD KEY `uid_index` (`uid`) USING BTREE;

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `t_customers`
--
ALTER TABLE `t_customers`
  MODIFY `uid` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `t_delivery`
--
ALTER TABLE `t_delivery`
  MODIFY `uid` int(10) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `t_employee`
--
ALTER TABLE `t_employee`
  MODIFY `uid` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `t_inventory`
--
ALTER TABLE `t_inventory`
  MODIFY `uid` int(10) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `t_items`
--
ALTER TABLE `t_items`
  MODIFY `uid` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=136;

--
-- AUTO_INCREMENT for table `t_items_category`
--
ALTER TABLE `t_items_category`
  MODIFY `uid` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `t_items_price`
--
ALTER TABLE `t_items_price`
  MODIFY `uid` int(10) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `t_payment_method`
--
ALTER TABLE `t_payment_method`
  MODIFY `uid` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `t_prefix`
--
ALTER TABLE `t_prefix`
  MODIFY `uid` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `t_shop`
--
ALTER TABLE `t_shop`
  MODIFY `uid` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `t_transaction_d`
--
ALTER TABLE `t_transaction_d`
  MODIFY `uid` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT for table `t_transaction_h`
--
ALTER TABLE `t_transaction_h`
  MODIFY `uid` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `t_transaction_t`
--
ALTER TABLE `t_transaction_t`
  MODIFY `uid` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
