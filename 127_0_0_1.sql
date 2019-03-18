-- phpMyAdmin SQL Dump
-- version 4.8.3
-- https://www.phpmyadmin.net/
--
-- 主機: 127.0.0.1:3306
-- 產生時間： 2019-03-14 13:09:21
-- 伺服器版本: 10.3.9-MariaDB
-- PHP 版本： 5.6.38

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- 資料庫： `teamwork`
--
-- DROP DATABASE IF EXISTS `teamwork`;
-- CREATE DATABASE IF NOT EXISTS `teamwork` DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;
-- USE `teamwork`;

-- --------------------------------------------------------

--
-- 資料表結構 `t_audit_log`
--

DROP TABLE IF EXISTS `t_audit_log`;
CREATE TABLE IF NOT EXISTS `t_audit_log` (
  `uid` int(10) NOT NULL AUTO_INCREMENT,
  `datetime` datetime DEFAULT NULL,
  `type` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `event` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- 資料表結構 `t_customers`
--

DROP TABLE IF EXISTS `t_customers`;
CREATE TABLE IF NOT EXISTS `t_customers` (
  `uid` int(10) NOT NULL AUTO_INCREMENT,
  `cust_code` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `mail_addr` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `shop_addr` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `delivery_addr` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `attn_1` varchar(15) COLLATE utf8_unicode_ci NOT NULL,
  `phone_1` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `fax_1` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `email_1` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `attn_2` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `phone_2` varchar(15) COLLATE utf8_unicode_ci NOT NULL,
  `fax_2` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `email_2` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `statement_remark` text COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `group_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `pm_code` varchar(10) COLLATE utf8_unicode_ci NOT NULL COMMENT 'payment method code',
  `pt_code` varchar(10) COLLATE utf8_unicode_ci NOT NULL COMMENT 'payment term',
  `remark` text COLLATE utf8_unicode_ci NOT NULL,
  `create_date` datetime DEFAULT NULL,
  `modify_date` datetime DEFAULT NULL,
  PRIMARY KEY (`uid`),
  UNIQUE KEY `custcode_unique` (`cust_code`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- 資料表的匯出資料 `t_customers`
--

INSERT INTO `t_customers` (`uid`, `cust_code`, `mail_addr`, `shop_addr`, `delivery_addr`, `attn_1`, `phone_1`, `fax_1`, `email_1`, `attn_2`, `phone_2`, `fax_2`, `email_2`, `statement_remark`, `name`, `group_name`, `pm_code`, `pt_code`, `remark`, `create_date`, `modify_date`) VALUES
(1, 'C150301', '九龍旺角花園街217號地下', '九龍旺角花園街217號地下', '九龍旺角花園街217號地下', '豪哥', '9714 8829', '0', '', '', '', '', '', '不用寄月結單', '峰飲食 MI-NE Restaurant', '', 'PM001', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(2, 'C150302', '新界葵涌葵豐街18-26號永康工業大廈3樓 H室', '新界葵涌葵豐街18-26號永康工業大廈3樓 H室', '新界葵涌葵豐街18-26號永康工業大廈3樓 H室', '戴先生', '6797 8829', '0', '', '', '', '', '', '', '孖寶車仔麵', '', 'PM001', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(3, 'C150401', '香港灣仔謝斐道69號馬來西亞大廈地下G4號舖', '香港灣仔謝斐道69號馬來西亞大廈地下G4號舖', '香港灣仔謝斐道69號馬來西亞大廈地下G4號舖', 'Mo哥', '9125 8829', '2345 1234', '', '', '', '', '', '', '金蒂餐廳 Cinta-J', '', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(4, 'C150402', '香港柴灣利眾街24號東貿廣場6/F', '香港循理會-社會服務部香港北角百福道21號香港青年協會大廈1901室', '香港柴灣利眾街24號東貿廣場6/F', '蘇小姐(店長)', '9634 8829', '3634 8829', '', '', '', '', '', '附上回郵信封', 'Fantastic Cafe (柴灣)', '香港循理會', 'PM001', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(5, 'C150403', '新界元朗泰衡街4號東堤街14號1樓', '新界元朗泰衡街4號東堤街14號1樓', '新界元朗泰衡街4號東堤街14號1樓', '李院長', '2474 8829', '0', '', '', '', '', '', '', '基德(泰衡)護老院有限公司', '', 'PM001', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(6, 'C150404', '九龍佐敦德興銜18號地下', '九龍佐敦德興銜18號地下', '九龍佐敦德興銜18號地下', '波仔', '9813 8829', '0', '', '', '', '', '', '', '好德來小籠包店', '', 'PM002', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(7, 'C150405', '九龍尖沙咀廣東道33號中港城第3座平台2-3號舖', '九龍尖沙咀廣東道33號中港城第3座平台2-3號舖', '九龍尖沙咀廣東道33號中港城第3座平台2-3號舖', 'Angel', '9132 8829', '0', '', '', '', '', '', '附上回郵信封', '福村日本料理', '', 'PM002', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(8, 'C150406', '九龍旺角登打士街2A寶亨大廈地下 10號鋪', '九龍旺角登打士街2A寶亨大廈地下 10號鋪', '九龍旺角登打士街2A寶亨大廈地下 10號鋪', '朱經理', '6236 8829', '0', '', '', '', '', '', '', '粵來順 (旺角)', '', 'PM001', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(9, 'C150407', '香港太古城太古城道26號漢宮閣地下411號舖', '九龍灣常悅道1號 恩浩國際中心5樓D室', '香港太古城太古城道26號漢宮閣地下411號舖', '黃經理', '9870 8829', '0', '', '', '', '', '', '附上回郵信封', '華昌粥麵', '', 'PM001', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(10, 'C150408', '香港西環皇后大道西576-584號新景樓地下A', '香港西環皇后大道西576-584號新景樓地下A', '香港西環皇后大道西576-584號新景樓地下A', '蘭姐', '9707 8829', '0', '', '', '', '', '', '附上回郵信封', '讚記廚房 (西環店) A Plus Kitchen', '', 'PM001', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(11, 'C150409', '九龍尖沙咀寶勒巷6-8號 盈豐商業大廈地下B鋪', '九龍尖沙咀寶勒巷6-8號 盈豐商業大廈地下B鋪', '九龍尖沙咀寶勒巷6-8號 盈豐商業大廈地下B鋪', '蘭姐', '9707 8829', '0', '', '', '', '', '', '附上回郵信封', '讚記 (尖沙咀店)   A Plus Kitchen', '', 'PM001', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(12, 'C150410', '九龍尖沙咀厚福街8號H8 17樓', '九龍尖沙咀厚福街8號H8 17樓', '九龍尖沙咀厚福街8號H8 17樓', '', '2234 8829', '0', '', '', '', '', '', '', 'OUT DART (尖沙咀)', '', 'PM001', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

-- --------------------------------------------------------

--
-- 資料表結構 `t_delivery`
--

DROP TABLE IF EXISTS `t_delivery`;
CREATE TABLE IF NOT EXISTS `t_delivery` (
  `uid` int(10) NOT NULL AUTO_INCREMENT,
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
  `remark` text COLLATE utf8_unicode_ci NOT NULL,
  `post_time` datetime DEFAULT NULL,
  `update_time` datetime DEFAULT NULL,
  PRIMARY KEY (`uid`),
  UNIQUE KEY `deliverycode_unique` (`delivery_code`) USING BTREE,
  KEY `delivery_code_index` (`delivery_code`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- 資料表結構 `t_employee`
--

DROP TABLE IF EXISTS `t_employee`;
CREATE TABLE IF NOT EXISTS `t_employee` (
  `uid` int(10) NOT NULL AUTO_INCREMENT,
  `employee_code` int(10) NOT NULL,
  `username` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `default_shopcode` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `access_level` int(3) NOT NULL,
  `role` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `last_login` datetime NOT NULL,
  `last_token` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `status` tinyint(4) NOT NULL,
  `create_date` datetime DEFAULT NULL,
  `modify_date` datetime DEFAULT NULL,
  PRIMARY KEY (`uid`),
  UNIQUE KEY `unique_employee_code` (`employee_code`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- 資料表的匯出資料 `t_employee`
--

INSERT INTO `t_employee` (`uid`, `employee_code`, `username`, `password`, `default_shopcode`, `access_level`, `role`, `last_login`, `last_token`, `status`, `create_date`, `modify_date`) VALUES
(1, 123456, 'iamadmin', 'pa4.HHSXL55NA', 'HQ01', 5, 'sales', '2019-03-14 12:04:28', '2fbd8062876fc6c5e0b6624711986b6f', 1, '2019-03-13 19:33:53', '2019-03-13 19:33:53');

-- --------------------------------------------------------

--
-- 資料表結構 `t_items`
--

DROP TABLE IF EXISTS `t_items`;
CREATE TABLE IF NOT EXISTS `t_items` (
  `uid` int(10) NOT NULL AUTO_INCREMENT,
  `item_code` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `eng_name` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
  `chi_name` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
  `desc` text COLLATE utf8_unicode_ci NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `price_special` decimal(10,2) NOT NULL,
  `cate_code` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `unit` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
  `create_date` datetime DEFAULT NULL,
  `modify_date` datetime DEFAULT NULL,
  PRIMARY KEY (`uid`),
  UNIQUE KEY `uid_unique` (`item_code`) USING BTREE,
  KEY `uid_index` (`item_code`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=140 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- 資料表的匯出資料 `t_items`
--

INSERT INTO `t_items` (`uid`, `item_code`, `eng_name`, `chi_name`, `desc`, `price`, `price_special`, `cate_code`, `unit`, `create_date`, `modify_date`) VALUES
(1, 'GIFT', 'GIFT', 'GIFT123', '', '10.00', '0.00', 'PDT1', '20PCS/PACK', '2019-03-13 19:34:38', '2019-03-13 11:34:38'),
(2, 'GUS0120', 'Gel Urinal Screen1', '香味尿格1', '', '110.00', '1.00', 'SVR', '20PCS/PACK1', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
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
(135, 'scjsdck', 'nskcn', 'jknksncknskc', '', '32.00', '32.00', 'PDT', '', '2018-09-16 18:20:00', '0000-00-00 00:00:00'),
(136, 'NAS0200', 'NAS ', 'NAS', 'Network A Server', '230.00', '0.00', 'Can', 'Box set', '2018-10-15 16:55:51', '0000-00-00 00:00:00'),
(139, 'GF703', 'eng', 'chinese', 'testing', '703.00', '0.00', 'MAT', '2dasd', '2018-12-06 08:38:34', '0000-00-00 00:00:00');

-- --------------------------------------------------------

--
-- 資料表結構 `t_items_category`
--

DROP TABLE IF EXISTS `t_items_category`;
CREATE TABLE IF NOT EXISTS `t_items_category` (
  `uid` int(10) NOT NULL AUTO_INCREMENT,
  `cate_code` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `desc` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
  `create_date` datetime DEFAULT NULL,
  `modify_date` datetime DEFAULT NULL,
  PRIMARY KEY (`uid`),
  UNIQUE KEY `uid_unique` (`uid`) USING BTREE,
  UNIQUE KEY `cate_code_unique` (`cate_code`) USING BTREE,
  KEY `uid_index` (`uid`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- 資料表的匯出資料 `t_items_category`
--

INSERT INTO `t_items_category` (`uid`, `cate_code`, `desc`, `create_date`, `modify_date`) VALUES
(1, 'PDT1', 'wrwewsd555', '2019-01-17 07:40:40', '2019-01-17 15:40:40'),
(2, 'SVR', 'Service ', '2018-12-06 07:56:45', '2018-04-25 00:00:00'),
(3, 'MAT', 'Maintenance', '2018-04-25 00:00:00', '2018-04-25 00:00:00'),
(15, 'Can', 'a box of can gategory', '2018-10-03 17:29:33', '0000-00-00 00:00:00'),
(16, 'TRI', 'test code ', '2018-12-06 07:49:25', '0000-00-00 00:00:00');

-- --------------------------------------------------------

--
-- 資料表結構 `t_items_price`
--

DROP TABLE IF EXISTS `t_items_price`;
CREATE TABLE IF NOT EXISTS `t_items_price` (
  `uid` int(10) NOT NULL AUTO_INCREMENT,
  `item_code` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `create_date` datetime DEFAULT NULL,
  `modify_date` datetime DEFAULT NULL,
  PRIMARY KEY (`uid`),
  UNIQUE KEY `uid_unique` (`uid`) USING BTREE,
  KEY `uid_index` (`uid`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- 資料表結構 `t_login`
--

DROP TABLE IF EXISTS `t_login`;
CREATE TABLE IF NOT EXISTS `t_login` (
  `uid` int(10) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `shop_code` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `status` enum('in','out') COLLATE utf8_unicode_ci NOT NULL,
  `create_date` datetime DEFAULT NULL,
  `modify_date` datetime DEFAULT NULL,
  UNIQUE KEY `uid` (`uid`)
) ENGINE=InnoDB AUTO_INCREMENT=38 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- 資料表的匯出資料 `t_login`
--

INSERT INTO `t_login` (`uid`, `username`, `shop_code`, `token`, `status`, `create_date`, `modify_date`) VALUES
(26, 'iamadmin', '', 'a3b22bdf3684acfeb105804d15c271cd', 'out', '2019-01-14 22:48:34', '2019-01-14 22:48:34'),
(27, 'iamadmin', '', '73d82d94e19c2d27215cd41cca2eb71b', 'out', '2019-01-16 00:47:00', '2019-01-16 00:47:00'),
(28, 'iamadmin', '', 'f9a5d315c5429cf6a779f1480a926cb7', 'out', '2019-02-23 02:11:58', '2019-02-22 18:11:58'),
(29, 'iamadmin', 'HQ1', '51caf5567a8dd4d72690eb9bfcfe46e7', 'out', '2019-03-03 09:13:26', '2019-03-03 01:12:06'),
(33, 'iamadmin', 'HQ01', 'c09179b64fc920fb548e22eef4234c45', 'out', '2019-03-04 19:41:36', '2019-03-04 11:41:36'),
(34, 'iamadmin', 'HQ01', '87dab5550f4a02816056f1f853cdafbf', 'out', '2019-03-11 20:52:21', '2019-03-11 12:52:21'),
(35, 'iamadmin', 'HQ01', 'a47a42f44a0dbd99eac78063fad3e876', 'out', '2019-03-13 19:33:53', '2019-03-13 11:33:53'),
(36, 'iamadmin', 'HQ01', '3f6aba122f69b1cf25f7460041412969', 'out', '2019-03-13 20:14:17', '2019-03-14 12:04:28'),
(37, 'iamadmin', 'HQ01', '2fbd8062876fc6c5e0b6624711986b6f', 'in', '2019-03-14 12:04:28', '2019-03-14 12:26:52');

-- --------------------------------------------------------

--
-- 資料表結構 `t_payment_method`
--

DROP TABLE IF EXISTS `t_payment_method`;
CREATE TABLE IF NOT EXISTS `t_payment_method` (
  `uid` int(10) NOT NULL AUTO_INCREMENT,
  `pm_code` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `payment_method` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `create_date` datetime DEFAULT NULL,
  `modify_date` datetime DEFAULT NULL,
  PRIMARY KEY (`uid`),
  UNIQUE KEY `pmcode_unique` (`pm_code`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- 資料表的匯出資料 `t_payment_method`
--

INSERT INTO `t_payment_method` (`uid`, `pm_code`, `payment_method`, `create_date`, `modify_date`) VALUES
(1, 'PM001', 'Cash', '2018-05-19 00:00:00', '2018-05-19 18:18:24'),
(4, 'PM002', 'VISA', '0000-00-00 00:00:00', '2018-12-10 22:59:55');

-- --------------------------------------------------------

--
-- 資料表結構 `t_payment_term`
--

DROP TABLE IF EXISTS `t_payment_term`;
CREATE TABLE IF NOT EXISTS `t_payment_term` (
  `uid` int(11) NOT NULL AUTO_INCREMENT,
  `pt_code` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `terms` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `create_date` datetime DEFAULT NULL,
  `modify_date` datetime DEFAULT NULL,
  PRIMARY KEY (`uid`),
  UNIQUE KEY `ptcode_unique` (`pt_code`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- 資料表的匯出資料 `t_payment_term`
--

INSERT INTO `t_payment_term` (`uid`, `pt_code`, `terms`, `create_date`, `modify_date`) VALUES
(1, 'PT001', 'C.O.D', '2018-12-10 00:00:00', '0000-00-00 00:00:00'),
(3, 'PT002', 'Monthly Payment', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

-- --------------------------------------------------------

--
-- 資料表結構 `t_prefix`
--

DROP TABLE IF EXISTS `t_prefix`;
CREATE TABLE IF NOT EXISTS `t_prefix` (
  `uid` int(10) NOT NULL AUTO_INCREMENT,
  `prefix` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `desc` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `status` tinyint(4) NOT NULL,
  PRIMARY KEY (`uid`),
  UNIQUE KEY `prefix_unique` (`prefix`) USING BTREE,
  KEY `uid_index` (`uid`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- 資料表的匯出資料 `t_prefix`
--

INSERT INTO `t_prefix` (`uid`, `prefix`, `desc`, `status`) VALUES
(1, 'INV', 'Invoices ', 1),
(2, 'PO', 'Purchase Order', 1),
(3, 'QU', 'Quotation', 1);

-- --------------------------------------------------------

--
-- 資料表結構 `t_shop`
--

DROP TABLE IF EXISTS `t_shop`;
CREATE TABLE IF NOT EXISTS `t_shop` (
  `uid` int(10) NOT NULL AUTO_INCREMENT,
  `shop_code` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
  `phone` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `address1` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `address2` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `create_date` datetime DEFAULT NULL,
  `modify_date` datetime DEFAULT NULL,
  PRIMARY KEY (`uid`),
  UNIQUE KEY `shopcode_unique` (`shop_code`) USING BTREE,
  KEY `shopcode_index` (`shop_code`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- 資料表的匯出資料 `t_shop`
--

INSERT INTO `t_shop` (`uid`, `shop_code`, `name`, `phone`, `address1`, `address2`, `create_date`, `modify_date`) VALUES
(1, 'HQ01', 'AABB LTD (HONG KONG)', '23992122', 'Kowloon City, Hung To Rd, 19號10樓1006室', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(2, 'HQ02', 'DD moving Co, (HK)', '22021010', 'Kwun Tong, 九龍觀塘鴻圖道37號鴻泰工業大廈', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

-- --------------------------------------------------------

--
-- 資料表結構 `t_stock`
--

DROP TABLE IF EXISTS `t_stock`;
CREATE TABLE IF NOT EXISTS `t_stock` (
  `uid` int(10) NOT NULL AUTO_INCREMENT,
  `inv_code` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `item_code` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `qty` double NOT NULL,
  `type` enum('in','out','hold','') COLLATE utf8_unicode_ci NOT NULL,
  `create_date` datetime DEFAULT NULL,
  `modify_date` datetime DEFAULT NULL,
  PRIMARY KEY (`uid`),
  UNIQUE KEY `uid_unique` (`inv_code`) USING BTREE,
  KEY `uid_index` (`inv_code`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- 資料表結構 `t_transaction_d`
--

DROP TABLE IF EXISTS `t_transaction_d`;
CREATE TABLE IF NOT EXISTS `t_transaction_d` (
  `uid` int(10) NOT NULL AUTO_INCREMENT,
  `trans_code` varchar(25) COLLATE utf8_unicode_ci NOT NULL,
  `item_code` varchar(25) COLLATE utf8_unicode_ci NOT NULL,
  `eng_name` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
  `chi_name` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
  `qty` decimal(5,1) NOT NULL,
  `unit` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `discount` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `create_date` datetime DEFAULT NULL,
  `modify_date` datetime DEFAULT NULL,
  PRIMARY KEY (`uid`),
  KEY `uid_index` (`uid`) USING BTREE,
  KEY `transcode_unique` (`trans_code`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- 資料表的匯出資料 `t_transaction_d`
--

INSERT INTO `t_transaction_d` (`uid`, `trans_code`, `item_code`, `eng_name`, `chi_name`, `qty`, `unit`, `price`, `discount`, `create_date`, `modify_date`) VALUES
(26, 'QTA2019038341', 'DA0110', 'Graese Trap Cleaner', 'Drain Away 隔油池化油劑', '1.0', '10 Ltr / Pail', '328.00', '', '2019-03-13 12:00:45', NULL),
(27, 'INV2019031440', 'Pentax', '', 'Pentax 水泵', '3.0', '', '1100.00', '', '2019-03-14 20:12:40', NULL),
(28, 'INV2019031440', 'DA0110', 'Graese Trap Cleaner', 'Drain Away 隔油池化油劑', '3.0', '10 Ltr / Pail', '328.00', '', '2019-03-14 20:12:40', NULL),
(29, 'INV2019031413', 'DA0110', 'Graese Trap Cleaner', 'Drain Away 隔油池化油劑', '1.0', '10 Ltr / Pail', '328.00', '', '2019-03-14 20:14:14', NULL),
(30, 'INV2019031406', 'DF0105', 'Concentrated Dishwasher Drying Agent', 'Dry Flash 高濃縮洗碗碟機快乾劑', '1.0', '5 Ltr / Bottle', '45.00', '', '2019-03-14 12:27:06', NULL),
(31, 'QTA2019031020', 'DD0405', 'Dishmachine Detergent', 'DD-100 洗碗碟機鹼液', '1.0', '4X5L', '120.00', '', '2019-03-14 12:27:52', NULL);

-- --------------------------------------------------------

--
-- 資料表結構 `t_transaction_h`
--

DROP TABLE IF EXISTS `t_transaction_h`;
CREATE TABLE IF NOT EXISTS `t_transaction_h` (
  `uid` int(10) NOT NULL AUTO_INCREMENT,
  `trans_code` varchar(25) COLLATE utf8_unicode_ci NOT NULL,
  `cust_code` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `quotation_code` varchar(25) COLLATE utf8_unicode_ci NOT NULL,
  `prefix` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `employee_code` int(10) NOT NULL,
  `shop_code` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `remark` text COLLATE utf8_unicode_ci NOT NULL,
  `is_void` tinyint(1) NOT NULL,
  `is_convert` tinyint(1) NOT NULL,
  `create_date` datetime DEFAULT NULL,
  `modify_date` datetime DEFAULT NULL,
  PRIMARY KEY (`uid`) USING BTREE,
  UNIQUE KEY `transcode_unique` (`trans_code`) USING BTREE,
  UNIQUE KEY `uid_index` (`uid`) USING BTREE,
  KEY `transcode_index` (`trans_code`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- 資料表的匯出資料 `t_transaction_h`
--

INSERT INTO `t_transaction_h` (`uid`, `trans_code`, `cust_code`, `quotation_code`, `prefix`, `total`, `employee_code`, `shop_code`, `remark`, `is_void`, `is_convert`, `create_date`, `modify_date`) VALUES
(24, 'QTA2019038341', 'C150302', '', 'QTA', '328.00', 110022, 'HQ01', '', 0, 1, '2019-03-14 09:51:31', '2019-03-14 20:14:14'),
(25, 'INV2019031440', 'C150302', '', 'INV', '4284.00', 110022, 'HQ01', 'test', 0, 0, '2019-03-14 20:12:40', '0000-00-00 00:00:00'),
(26, 'INV2019031413', 'C150302', 'QTA2019038341', 'INV', '328.00', 110022, 'HQ01', '', 0, 0, '2019-03-14 20:14:14', '0000-00-00 00:00:00'),
(27, 'INV2019031406', 'C150403', '', 'INV', '45.00', 110022, 'HQ01', '', 0, 0, '2019-03-14 12:27:06', '0000-00-00 00:00:00'),
(28, 'QTA2019031020', 'C150403', '', 'QTA', '120.00', 110022, 'HQ01', '', 0, 0, '2019-03-14 12:27:52', NULL);

-- --------------------------------------------------------

--
-- 資料表結構 `t_transaction_t`
--

DROP TABLE IF EXISTS `t_transaction_t`;
CREATE TABLE IF NOT EXISTS `t_transaction_t` (
  `uid` int(10) NOT NULL AUTO_INCREMENT,
  `trans_code` varchar(25) COLLATE utf8_unicode_ci NOT NULL,
  `pm_code` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `create_date` datetime DEFAULT NULL,
  `modify_date` datetime DEFAULT NULL,
  PRIMARY KEY (`uid`) USING BTREE,
  UNIQUE KEY `transcode_unique` (`trans_code`) USING BTREE,
  KEY `transcode_index` (`trans_code`) USING BTREE,
  KEY `uid_index` (`uid`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- 資料表的匯出資料 `t_transaction_t`
--

INSERT INTO `t_transaction_t` (`uid`, `trans_code`, `pm_code`, `total`, `create_date`, `modify_date`) VALUES
(8, 'QTA2019038341', 'PM001', '328.00', '2019-03-13 12:00:45', NULL),
(9, 'INV2019031440', 'PM001', '4284.00', '2019-03-14 20:12:40', NULL),
(10, 'INV2019031413', 'PM001', '328.00', '2019-03-14 20:14:14', NULL),
(11, 'INV2019031406', 'PM001', '45.00', '2019-03-14 12:27:06', NULL),
(12, 'QTA2019031020', 'PM001', '120.00', '2019-03-14 12:27:52', NULL);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
