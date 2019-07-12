-- phpMyAdmin SQL Dump
-- version 4.8.3
-- https://www.phpmyadmin.net/
--
-- 主機: 127.0.0.1:3306
-- 產生時間： 2019-07-12 05:25:25
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
DROP DATABASE IF EXISTS `teamwork`;
CREATE DATABASE IF NOT EXISTS `teamwork` DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;
USE `teamwork`;

-- --------------------------------------------------------

--
-- 資料表結構 `t_accounts_info`
--

DROP TABLE IF EXISTS `t_accounts_info`;
CREATE TABLE IF NOT EXISTS `t_accounts_info` (
  `uid` int(10) NOT NULL AUTO_INCREMENT,
  `cust_code` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `company_BR` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `company_sign` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `group_name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `attn` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `tel` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `fax` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `create_date` datetime NOT NULL,
  `modify_date` datetime NOT NULL,
  PRIMARY KEY (`uid`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- 資料表的匯出資料 `t_accounts_info`
--

INSERT INTO `t_accounts_info` (`uid`, `cust_code`, `company_BR`, `company_sign`, `group_name`, `attn`, `tel`, `fax`, `email`, `create_date`, `modify_date`) VALUES
(1, 'C150404', '123456789012345679', 'Ocean Park Company', 'Group of OC Company', 'Mrs Chan Chan', '90092234', '23223323', 'acc@oc.com', '0000-00-00 00:00:00', '2019-06-04 00:22:44'),
(2, 'C150413', '123456789012345679', 'Ocean Park Company', 'Group of OC Company1', 'Mrs Chan Chan', '12321312', '12312323', 'adasd@jdj,com', '0000-00-00 00:00:00', '2019-06-07 22:58:53'),
(3, 'C150414', '129912', 'New Company Ltd', 'New world Company', 'Chan', '48832112', '21102110', 'acc@newcom.com', '0000-00-00 00:00:00', '2019-06-13 23:50:57'),
(4, 'C150410', '123456789012345679', 'Ocean Park Company', '', 'Mrs Chan Chan', '12321312', '12312323', 'du@oc.com', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

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
  `employee_code` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `attn_1` varchar(15) COLLATE utf8_unicode_ci NOT NULL,
  `phone_1` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `fax_1` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `email_1` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `attn_2` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `phone_2` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `fax_2` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `email_2` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `statement_remark` text COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `pm_code` varchar(10) COLLATE utf8_unicode_ci NOT NULL COMMENT 'payment method code',
  `pt_code` varchar(10) COLLATE utf8_unicode_ci NOT NULL COMMENT 'payment term',
  `remark` text COLLATE utf8_unicode_ci NOT NULL,
  `district_code` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `delivery_addr` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `from_time` time NOT NULL,
  `to_time` time NOT NULL,
  `delivery_remark` text COLLATE utf8_unicode_ci NOT NULL,
  `status` enum('Active','Closed') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Active',
  `create_date` datetime DEFAULT NULL,
  `modify_date` datetime DEFAULT NULL,
  PRIMARY KEY (`uid`),
  UNIQUE KEY `custcode_unique` (`cust_code`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- 資料表的匯出資料 `t_customers`
--

INSERT INTO `t_customers` (`uid`, `cust_code`, `mail_addr`, `shop_addr`, `employee_code`, `attn_1`, `phone_1`, `fax_1`, `email_1`, `attn_2`, `phone_2`, `fax_2`, `email_2`, `statement_remark`, `name`, `pm_code`, `pt_code`, `remark`, `district_code`, `delivery_addr`, `from_time`, `to_time`, `delivery_remark`, `status`, `create_date`, `modify_date`) VALUES
(1, 'C150301', '九龍旺角花園街217號地下', '九龍旺角花園街217號地下', '', '豪哥', '97148829', '0', '', '', '', '', '', '不用寄月結單', '峰飲食 MI-NE Restaurant', 'PM001', 'PT001', '', 'NT13', '九龍旺角花園街217號地下', '00:00:00', '00:00:00', '', 'Active', '0000-00-00 00:00:00', '2019-05-19 11:32:57'),
(2, 'C150302', '新界葵涌葵豐街18-26號永康工業大廈3樓 H室', '新界葵涌葵豐街18-26號永康工業大廈3樓 H室', '', '戴先生', '6797 8829', '0', '', '', '', '', '', '', '孖寶車仔麵12', 'PM001', 'PT002', '', 'NT13', '新界葵涌葵豐街18-26號永康工業大廈3樓 H室', '00:00:00', '00:00:00', '', 'Active', '0000-00-00 00:00:00', '2019-04-22 00:05:00'),
(3, 'C150401', '九龍佐敦德興銜18號地下1', '九龍佐敦德興銜18號地下1', '', '波仔1', '98138829', '33339999', 'email2@mm.com1', 'Mr Li1', '21210022', '23033302', 'email2@mm.com1', '附上回郵信封 use letter1', '好德來小籠包店1', 'PM001', 'PT002', 'Remember to save my Remark1', 'NT12', '九龍佐敦德興銜18號地下1', '12:00:00', '01:00:00', 'This field is delivery Remark1', 'Closed', '0000-00-00 00:00:00', '2019-06-04 00:11:32'),
(4, 'C150402', '香港柴灣利眾街24號東貿廣場6/F', '香港循理會-社會服務部香港北角百福道21號香港青年協會大廈1901室', '', '蘇小姐(店長)', '9634 8829', '3634 8829', '', '', '', '', '', '附上回郵信封', 'Fantastic Cafe (柴灣)', 'PM001', 'PT001', '', 'NT14', '香港柴灣利眾街24號東貿廣場6/F', '00:00:00', '00:00:00', '', 'Active', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(5, 'C150403', '新界元朗泰衡街4號東堤街14號1樓', '新界元朗泰衡街4號東堤街14號1樓', '', '李院長', '2474 8829', '0', '', '', '', '', '', '', '基德(泰衡)護老院有限公司', 'PM001', 'PT002', '', 'NT14', '新界元朗泰衡街4號東堤街14號1樓', '00:00:00', '00:00:00', '', 'Active', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(6, 'C150404', '九龍佐敦德興銜18號地下', '九龍佐敦德興銜18號地下', '', '波仔', '98138829', '33339999', 'email2@mm.com', 'Mr Li', '21210022', '23033302', 'email2@mm.com', '附上回郵信封 use letter', '好德來小籠包店', 'PM002', 'PT002', 'Remember to save my Remark', 'NT12', '九龍佐敦德興銜18號地下', '12:00:00', '02:00:00', 'This field is delivery Remark', 'Closed', '0000-00-00 00:00:00', '2019-06-04 00:22:44'),
(7, 'C150405', '九龍尖沙咀廣東道33號中港城第3座平台2-3號舖', '九龍尖沙咀廣東道33號中港城第3座平台2-3號舖', '', 'Angel', '9132 8829', '0', '', '', '', '', '', '附上回郵信封', '福村日本料理', 'PM002', 'PT001', '', 'KL07', '九龍尖沙咀廣東道33號中港城第3座平台2-3號舖', '00:00:00', '00:00:00', '', 'Active', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(8, 'C150406', '九龍旺角登打士街2A寶亨大廈地下 10號鋪', '九龍旺角登打士街2A寶亨大廈地下 10號鋪', '', '朱經理', '6236 8829', '0', '', '', '', '', '', '', '粵來順 (旺角)', 'PM001', 'PT001', '', 'KL06', '九龍旺角登打士街2A寶亨大廈地下 10號鋪', '00:00:00', '00:00:00', '', 'Active', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(9, 'C150407', '香港太古城太古城道26號漢宮閣地下411號舖', '九龍灣常悅道1號 恩浩國際中心5樓D室', '', '黃經理', '9870 8829', '0', '', '', '', '', '', '附上回郵信封', '華昌粥麵', 'PM001', 'PT002', '', 'NT14', '香港太古城太古城道26號漢宮閣地下411號舖', '00:00:00', '00:00:00', '', 'Active', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(10, 'C150408', '香港西環皇后大道西576-584號新景樓地下A', '香港西環皇后大道西576-584號新景樓地下A', '', '蘭姐', '9707 8829', '0', '', '', '', '', '', '附上回郵信封', '讚記廚房 (西環店) A Plus Kitchen', 'PM001', 'PT001', '', 'NT13', '香港西環皇后大道西576-584號新景樓地下A', '00:00:00', '00:00:00', '', 'Active', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(11, 'C150409', '九龍尖沙咀寶勒巷6-8號 盈豐商業大廈地下B鋪', '九龍尖沙咀寶勒巷6-8號 盈豐商業大廈地下B鋪', '', '蘭姐', '9707 8829', '0', '', '', '', '', '', '附上回郵信封', '讚記 (尖沙咀店)   A Plus Kitchen', 'PM001', 'PT001', '', 'HK02', '九龍尖沙咀寶勒巷6-8號 盈豐商業大廈地下B鋪', '00:00:00', '00:00:00', '', 'Active', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(13, 'C150410', 'address1', '九龍佐敦德興銜18號地下', '', '波仔', '98138829', '39939221', 'email2@mm.com', 'Mr Li', '12312332', '', '', '', '孖寶', 'PM001', 'PT001', '', 'NT11', '九龍佐敦德興銜18號地下', '12:00:00', '23:00:00', '', 'Active', '2019-06-17 22:50:56', NULL);

-- --------------------------------------------------------

--
-- 資料表結構 `t_delivery_note`
--

DROP TABLE IF EXISTS `t_delivery_note`;
CREATE TABLE IF NOT EXISTS `t_delivery_note` (
  `uid` int(10) NOT NULL AUTO_INCREMENT,
  `delivery_code` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `cust_code` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `trans_code` varchar(25) COLLATE utf8_unicode_ci NOT NULL,
  `district_code` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `remark` text COLLATE utf8_unicode_ci NOT NULL,
  `post_time` datetime DEFAULT NULL,
  `update_time` datetime DEFAULT NULL,
  PRIMARY KEY (`uid`),
  UNIQUE KEY `deliverycode_unique` (`delivery_code`) USING BTREE,
  KEY `delivery_code_index` (`delivery_code`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- 資料表結構 `t_district`
--

DROP TABLE IF EXISTS `t_district`;
CREATE TABLE IF NOT EXISTS `t_district` (
  `uid` int(10) NOT NULL AUTO_INCREMENT,
  `district_code` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `district_chi` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `district_eng` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `region` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`uid`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- 資料表的匯出資料 `t_district`
--

INSERT INTO `t_district` (`uid`, `district_code`, `district_chi`, `district_eng`, `region`) VALUES
(3, 'HK01', '中西區', 'Central and Western', 'HONG KONG'),
(5, 'HK02', '東區', 'Eastern', 'HONG KONG'),
(6, 'HK03', '南區', 'Southern', 'HONG KONG'),
(7, 'HK04', '灣仔區', 'Wan Chai', 'HONG KONG'),
(8, 'KL05', '深水埗區', 'Sham Shui Po', 'Kowloon'),
(9, 'KL06', '九龍城區', 'Kowloon City', 'Kowloon'),
(10, 'KL07', '觀塘區', 'Kwun Tong', 'Kowloon'),
(11, 'KL08', '黃大仙區', 'Wong Tai Sin', 'Kowloon'),
(12, 'KL09', '油尖旺區', 'Yau Tsim Mong', 'Kowloon'),
(13, 'NT10', '離島區', 'Islands', 'New Territories'),
(14, 'NT11', '葵青區', 'Kwai Tsing', 'New Territories'),
(15, 'NT12', '北區', 'North', 'New Territories'),
(16, 'NT13', '西貢區', 'Sai Kung', 'New Territories'),
(17, 'NT14', '沙田區', 'Sha Tin', 'New Territories'),
(18, 'NT15', '大埔區', 'Tai Po', 'New Territories'),
(19, 'NT16', '荃灣區', 'Tsuen Wan', 'New Territories'),
(20, 'NT17', '屯門區', 'Tuen Mun', 'New Territories'),
(21, 'NT18', '元朗區', 'Yuen Long', 'New Territories');

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
  `role_code` int(10) NOT NULL,
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

INSERT INTO `t_employee` (`uid`, `employee_code`, `username`, `password`, `default_shopcode`, `role_code`, `last_login`, `last_token`, `status`, `create_date`, `modify_date`) VALUES
(1, 123456, 'iamadmin', 'pa4.HHSXL55NA', 'HQ01', 1232, '2019-07-12 04:48:59', 'cb96e48b48432f5a7ba071aa7d3ea261', 1, '2019-03-13 19:33:53', '2019-03-13 19:33:53');

-- --------------------------------------------------------

--
-- 資料表結構 `t_employee_access`
--

DROP TABLE IF EXISTS `t_employee_access`;
CREATE TABLE IF NOT EXISTS `t_employee_access` (
  `uid` int(10) NOT NULL,
  `employee_code` int(10) NOT NULL,
  `type` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `sub_type` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `a_create` tinyint(1) NOT NULL,
  `a_edit` tinyint(1) NOT NULL,
  `a_delete` tinyint(1) NOT NULL,
  `a_show` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- 資料表結構 `t_employee_role`
--

DROP TABLE IF EXISTS `t_employee_role`;
CREATE TABLE IF NOT EXISTS `t_employee_role` (
  `uid` int(10) NOT NULL AUTO_INCREMENT,
  `role_code` int(10) NOT NULL,
  `name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `access_level` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `create_date` datetime NOT NULL,
  `modify_date` datetime NOT NULL,
  PRIMARY KEY (`uid`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- 資料表的匯出資料 `t_employee_role`
--

INSERT INTO `t_employee_role` (`uid`, `role_code`, `name`, `access_level`, `create_date`, `modify_date`) VALUES
(1, 1232, 'Sales', '5', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

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
) ENGINE=InnoDB AUTO_INCREMENT=142 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- 資料表的匯出資料 `t_items`
--

INSERT INTO `t_items` (`uid`, `item_code`, `eng_name`, `chi_name`, `desc`, `price`, `price_special`, `cate_code`, `unit`, `create_date`, `modify_date`) VALUES
(1, 'GIFT', 'GIFT12345', 'GIFT1234', '', '10.00', '0.00', 'PDT1', '20PCS/PACK', '2019-03-13 19:34:38', '2019-03-17 04:58:00'),
(2, 'GUS0120', 'Gel Urinal Screen1', '香味尿格', '', '110.00', '1.00', 'SVR', '20PCS/PACK1', '0000-00-00 00:00:00', '2019-06-10 23:13:50'),
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
(139, 'GF703', 'eng', 'chinese', 'testing', '703.00', '0.00', 'MAT', '2dasd', '2018-12-06 08:38:34', '0000-00-00 00:00:00'),
(140, 'IAM2033', 'eng testing item', 'Testing item', 'items testing', '9999.00', '9999.00', 'SVR', 'testing', '2019-03-19 05:00:22', NULL),
(141, 'IC12345', 'ICeng', 'IC', 'laskmdkdmald\r\n', '122.00', '23.00', 'SVR', '4x3pack', '2019-04-26 22:37:34', NULL);

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
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- 資料表的匯出資料 `t_items_category`
--

INSERT INTO `t_items_category` (`uid`, `cate_code`, `desc`, `create_date`, `modify_date`) VALUES
(1, 'PDT1', 'wrwewsd5551', '2019-01-17 07:40:40', '2019-03-17 03:30:25'),
(2, 'SVR', 'Service ', '2018-12-06 07:56:45', '2018-04-25 00:00:00'),
(3, 'MAT', 'Maintenance', '2018-04-25 00:00:00', '2018-04-25 00:00:00'),
(15, 'Can', 'a box of can gategory', '2018-10-03 17:29:33', '0000-00-00 00:00:00'),
(22, 'testing', 'testing1', '2019-03-19 04:59:03', '2019-03-19 05:22:34');

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
) ENGINE=InnoDB AUTO_INCREMENT=105 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- 資料表的匯出資料 `t_login`
--

INSERT INTO `t_login` (`uid`, `username`, `shop_code`, `token`, `status`, `create_date`, `modify_date`) VALUES
(54, 'iamadmin', 'HQ01', 'da106e647c9e7f6cc974d95d7cd68bf0', 'out', '2019-04-19 22:19:55', '2019-04-20 22:42:14'),
(55, 'iamadmin', 'HQ01', '62bb4e6f80c6f46d24eaa4f3bddb4611', 'out', '2019-04-20 22:42:14', '2019-04-21 23:33:08'),
(56, 'iamadmin', 'HQ01', '67b08a3a7bddb0bda536dcd423cf9576', 'out', '2019-04-21 23:33:08', '2019-04-23 00:29:23'),
(57, 'iamadmin', 'HQ01', '71ac6f2de52157ed64b550ad2d4a52bb', 'out', '2019-04-23 00:29:23', '2019-04-25 22:38:03'),
(58, 'iamadmin', 'HQ01', 'd9c42a5a43441d5ea8b10d906e76a1e8', 'out', '2019-04-25 22:38:03', '2019-04-26 22:50:52'),
(59, 'iamadmin', 'HQ01', 'd717848d2792294500d41937071375be', 'out', '2019-04-26 22:50:59', '2019-04-28 11:06:10'),
(60, 'iamadmin', 'HQ01', 'aab38934265d21757c56f4bfb42e7b83', 'out', '2019-04-28 11:06:10', '2019-04-29 21:45:47'),
(61, 'iamadmin', 'HQ01', 'b508404593a69c87a6eddedb41d6de3d', 'out', '2019-04-29 21:45:47', '2019-04-30 23:20:25'),
(62, 'iamadmin', 'HQ01', 'cb9e527082c4b92fad5cc288418d3f28', 'out', '2019-04-30 23:20:25', '2019-05-01 23:45:08'),
(63, 'iamadmin', 'HQ01', '2912e0f97d97ee275c73cb7e3c238658', 'out', '2019-05-01 23:45:14', '2019-05-03 00:01:55'),
(64, 'iamadmin', 'HQ01', '2a26115af4f8fe2e42d400fa0f038221', 'out', '2019-05-03 00:02:00', '2019-05-04 11:22:29'),
(65, 'iamadmin', 'HQ01', 'aa07dde43d3e33ec07d2b42347990fe4', 'out', '2019-05-04 11:22:29', '2019-05-06 22:39:11'),
(66, 'iamadmin', 'HQ01', 'e76123a991e9a1d7dcf08ab602c5b787', 'out', '2019-05-06 22:39:11', '2019-05-07 23:21:24'),
(67, 'iamadmin', 'HQ01', '4b093d6dbb408fead4f9f08fde944388', 'out', '2019-05-07 23:21:24', '2019-05-09 11:15:37'),
(68, 'iamadmin', 'HQ01', 'ff5b0c22dc8bc0089c8bbc13f09d0154', 'out', '2019-05-09 11:15:37', '2019-05-10 11:16:04'),
(69, 'iamadmin', 'HQ01', '968ce71f964dc44cd254f4e3ba5eabab', 'out', '2019-05-10 11:16:18', '2019-05-11 13:56:35'),
(70, 'iamadmin', 'HQ01', '5817ee76215e78a1065ec892bed4e78c', 'out', '2019-05-11 13:56:35', '2019-05-12 23:10:34'),
(71, 'iamadmin', 'HQ01', '92408176e68c7db12a62e841071cca27', 'out', '2019-05-12 23:10:34', '2019-05-16 08:38:26'),
(72, 'iamadmin', 'HQ01', '408e295e46c5d6d0fbdd4fa9c6df8c15', 'out', '2019-05-16 08:38:26', '2019-05-17 22:21:17'),
(73, 'iamadmin', 'HQ01', '48f617b0e48282661e1e67034b68bf84', 'out', '2019-05-17 22:21:17', '2019-05-19 11:31:23'),
(74, 'iamadmin', 'HQ01', '3ea0f72e2cbb27960d201d0792061112', 'out', '2019-05-19 11:31:23', '2019-05-20 23:40:57'),
(75, 'iamadmin', 'HQ01', '3e274db66139e1226c23a13f227aa550', 'out', '2019-05-20 23:40:57', '2019-05-21 23:45:16'),
(76, 'iamadmin', 'HQ01', '37b403b0381c85a0b5bc7bdf06f9c2ed', 'out', '2019-05-21 23:45:21', '2019-05-23 21:53:49'),
(77, 'iamadmin', 'HQ01', 'd52ab2b6e998ae9f68a77266404f3ede', 'out', '2019-05-23 21:53:49', '2019-05-27 21:54:46'),
(78, 'iamadmin', 'HQ01', '53ee5a45c05862821f695aea70f265b9', 'out', '2019-05-27 21:54:46', '2019-05-28 22:27:46'),
(79, 'iamadmin', 'HQ01', '8db36b34278eade666dcd58fae3f6eed', 'out', '2019-05-28 22:27:46', '2019-05-29 22:49:00'),
(80, 'iamadmin', 'HQ01', '78041c4bf44eaca672586a5024670088', 'out', '2019-05-29 22:49:00', '2019-05-30 22:49:55'),
(81, 'iamadmin', 'HQ01', '9e3470078096d8c92f2d87eb503cf18a', 'out', '2019-05-30 22:50:10', '2019-05-31 23:09:45'),
(82, 'iamadmin', 'HQ01', '572d93c20001654afe715d09ccb28127', 'out', '2019-05-31 23:09:45', '2019-06-02 10:32:07'),
(83, 'iamadmin', 'HQ01', '46da2151a73224d25f9f39370a773b0f', 'out', '2019-06-02 10:32:07', '2019-06-03 22:42:10'),
(84, 'iamadmin', 'HQ01', '908e6ee53b3d8b0038e3ddaf93545c3b', 'out', '2019-06-03 22:42:10', '2019-06-04 23:21:36'),
(85, 'iamadmin', 'HQ01', 'b09b0a28ba176da519fe7a46c160a241', 'out', '2019-06-04 23:21:36', '2019-06-05 23:26:12'),
(86, 'iamadmin', 'HQ01', 'f1b9a90f4abf5844057849143a1f463f', 'out', '2019-06-05 23:26:17', '2019-06-07 22:15:41'),
(87, 'iamadmin', 'HQ01', '1050ea110154fc881958776a529210c0', 'out', '2019-06-07 22:15:41', '2019-06-09 11:23:24'),
(88, 'iamadmin', 'HQ01', '507001756c89fb788eb31e3f2e9b92e2', 'out', '2019-06-09 11:23:24', '2019-06-10 21:57:34'),
(89, 'iamadmin', 'HQ01', '3dbf360fb05c96519cead6321146a73e', 'out', '2019-06-10 21:57:34', '2019-06-11 23:25:33'),
(90, 'iamadmin', 'HQ01', 'a5d65570e3a0faa7f7fa585500d80243', 'out', '2019-06-11 23:25:33', '2019-06-13 23:46:17'),
(91, 'iamadmin', 'HQ01', '4f3818c638f325cd0a6cd9660f1a3c6b', 'out', '2019-06-13 23:46:17', '2019-06-16 21:21:48'),
(92, 'iamadmin', 'HQ01', 'c8bc15279f7d29eee98e4eb3411c34dc', 'out', '2019-06-16 21:21:48', '2019-06-17 22:27:40'),
(93, 'iamadmin', 'HQ01', 'bf0f2ada66e14fe34b286f370d4b3fb1', 'out', '2019-06-17 22:27:40', '2019-06-18 23:12:13'),
(94, 'iamadmin', 'HQ01', '29e4fcafc29073968254f6202e5873d3', 'out', '2019-06-18 23:12:13', '2019-06-19 23:27:23'),
(95, 'iamadmin', 'HQ01', '166b703ce3e6a8edb60ca71eb0a0be80', 'out', '2019-06-19 23:27:23', '2019-06-23 10:20:33'),
(96, 'iamadmin', 'HQ01', '4ca54b28b24af32e928fe60e769a7ff4', 'out', '2019-06-23 10:20:33', '2019-06-24 21:48:12'),
(97, 'iamadmin', 'HQ01', '21e05c93751c3ee4e6c40ea13a9d83db', 'out', '2019-06-24 21:48:12', '2019-06-25 22:51:07'),
(98, 'iamadmin', 'HQ01', 'f00bd2e1ebac954bf5d1426f21a8a109', 'out', '2019-06-25 22:51:07', '2019-06-26 23:08:10'),
(99, 'iamadmin', 'HQ01', 'ecaafcda6f8adfc88990ba55ab484759', 'out', '2019-06-26 23:08:15', '2019-06-28 20:52:47'),
(100, 'iamadmin', 'HQ01', '5d6e464fa5d41f79e767efab66c313af', 'out', '2019-06-28 20:52:47', '2019-07-02 23:36:12'),
(101, 'iamadmin', 'HQ01', '5c704e990c9cde85d63ec79a6b2ea246', 'out', '2019-07-02 23:36:12', '2019-07-04 01:25:41'),
(102, 'iamadmin', 'HQ01', '8e1055b45065aa6e20766525ac22c0dc', 'out', '2019-07-04 01:25:41', '2019-07-06 01:54:44'),
(103, 'iamadmin', 'HQ01', '369f17714194c36dadacfb19a5b5e827', 'out', '2019-07-06 01:54:44', '2019-07-12 04:48:59'),
(104, 'iamadmin', 'HQ01', 'cb96e48b48432f5a7ba071aa7d3ea261', 'in', '2019-07-12 04:48:59', '2019-07-12 05:24:37');

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
(1, 'HQ01', 'Homeland Worker', '22222222', 'dfsf', '', '0000-00-00 00:00:00', '2019-05-10 14:56:47'),
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
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- 資料表的匯出資料 `t_transaction_d`
--

INSERT INTO `t_transaction_d` (`uid`, `trans_code`, `item_code`, `eng_name`, `chi_name`, `qty`, `unit`, `price`, `discount`, `create_date`, `modify_date`) VALUES
(1, 'INV2019032629', 'DD0405', 'Dishmachine Detergent', 'DD-100 洗碗碟機鹼液', '1.0', '4X5L', '120.00', '', '2019-03-26 00:29:29', '2019-03-26 00:30:07'),
(2, 'INV2019032629', 'DF0120', 'Concentrated Dishwasher Drying Agent', 'Dry F lash 高濃縮洗碗碟機快乾劑', '2.0', '20 Ltr / Pail', '180.00', '', '2019-03-26 00:29:29', '2019-03-26 00:30:07'),
(3, 'QTA2019032728', 'DA0110', 'Graese Trap Cleaner', 'Drain Away 隔油池化油劑', '2.0', '10 Ltr / Pail', '328.00', '', '2019-03-27 23:11:28', NULL),
(4, 'QTA2019032728', 'DF0120', 'Concentrated Dishwasher Drying Agent', 'Dry F lash 高濃縮洗碗碟機快乾劑', '3.0', '20 Ltr / Pail', '180.00', '', '2019-03-27 23:11:28', NULL),
(5, 'INV2019050643', 'DD0120', 'Dishmachine Detergent', 'DD-100 洗碗碟機鹼液', '2.0', '20L', '80.00', '', '2019-05-06 23:01:43', NULL),
(6, 'QTA2019050648', 'DA0110', 'Graese Trap Cleaner', 'Drain Away 隔油池化油劑', '1.0', '10 Ltr / Pail', '328.00', '', '2019-05-06 23:03:48', NULL),
(7, 'QTA2019050648', 'Pentax', '', 'Pentax 水泵', '1.0', '', '1100.00', '', '2019-05-06 23:03:48', NULL),
(8, 'INV2019050622', 'DD0405', 'Dishmachine Detergent', 'DD-100 洗碗碟機鹼液', '1.0', '4X5L', '120.00', '', '2019-05-06 23:04:22', NULL),
(9, 'INV2019050616', 'DD0405', 'Dishmachine Detergent', 'DD-100 洗碗碟機鹼液', '1.0', '4X5L', '120.00', '', '2019-05-06 23:05:16', NULL),
(10, 'QTA2019051113', 'DA0110', 'Graese Trap Cleaner', 'Drain Away 隔油池化油劑', '1.0', '10 Ltr / Pail', '328.00', '', '2019-05-11 14:23:13', NULL),
(11, 'QTA2019051114', 'DA0110', 'Graese Trap Cleaner', 'Drain Away 隔油池化油劑', '3.0', '10 Ltr / Pail', '328.00', '', '2019-05-11 14:34:14', NULL),
(12, 'INV2019051254', 'DA0110', 'Graese Trap Cleaner', 'Drain Away 隔油池化油劑', '1.0', '10 Ltr / Pail', '328.00', '', '2019-05-12 10:00:36', NULL),
(13, 'INV2019051940', 'DD0405', 'Dishmachine Detergent', 'DD-100 洗碗碟機鹼液', '3.0', '4X5L', '120.00', '', '2019-05-19 22:04:40', NULL),
(14, 'INV2019062301', 'DD0120', 'Dishmachine Detergent', 'DD-100 洗碗碟機鹼液', '1.0', '20L', '80.00', '', '2019-06-23 22:47:01', NULL),
(15, 'QTA2019062302', 'DD0405', 'Dishmachine Detergent', 'DD-100 洗碗碟機鹼液', '5.0', '4X5L', '120.00', '', '2019-06-23 22:52:02', NULL),
(16, 'QTA2019062302', 'DF0120', 'Concentrated Dishwasher Drying Agent', 'Dry F lash 高濃縮洗碗碟機快乾劑', '1.0', '20 Ltr / Pail', '180.00', '', '2019-06-23 22:52:02', NULL);

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
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- 資料表的匯出資料 `t_transaction_h`
--

INSERT INTO `t_transaction_h` (`uid`, `trans_code`, `cust_code`, `quotation_code`, `prefix`, `total`, `employee_code`, `shop_code`, `remark`, `is_void`, `is_convert`, `create_date`, `modify_date`) VALUES
(1, 'INV2019032629', 'C150405', '', 'INV', '480.00', 123456, 'HQ01', '', 0, 0, '2019-03-26 00:29:29', '2019-03-26 00:30:07'),
(2, 'QTA2019032728', 'C150403', '', 'QTA', '1196.00', 123456, 'HQ02', '', 0, 1, '2019-03-27 23:11:28', '2019-05-06 23:01:43'),
(3, 'INV2019050643', 'C150402', 'QTA2019032728', 'INV', '160.00', 123456, 'HQ02', '', 0, 0, '2019-05-06 23:01:43', NULL),
(4, 'QTA2019050648', 'C150405', '', 'QTA', '1428.00', 123456, 'HQ02', '', 0, 0, '2019-05-06 23:03:48', NULL),
(5, 'INV2019050622', 'C150401', '', 'INV', '120.00', 123456, 'HQ02', '', 0, 0, '2019-05-06 23:04:22', NULL),
(6, 'INV2019050616', 'C150402', '', 'INV', '120.00', 123456, 'HQ02', '', 0, 0, '2019-05-06 23:05:16', NULL),
(7, 'QTA2019051113', 'C150301', '', 'QTA', '328.00', 123456, 'HQ01', '', 0, 0, '2019-05-11 14:23:13', NULL),
(9, 'QTA2019051114', 'C150301', '', 'QTA', '984.00', 123456, 'HQ02', '', 0, 0, '2019-05-11 14:34:14', NULL),
(10, 'INV2019051254', 'C150402', '', 'INV', '328.00', 123456, 'HQ01', '', 0, 0, '2019-05-12 10:00:36', NULL),
(11, 'INV2019051940', 'C150402', '', 'INV', '360.00', 123456, 'HQ02', '', 0, 0, '2019-05-19 22:04:40', NULL),
(12, 'INV2019062301', 'C150403', '', 'INV', '80.00', 123456, 'HQ01', '', 0, 0, '2019-06-23 22:47:01', NULL),
(13, 'QTA2019062302', 'C150406', '', 'QTA', '780.00', 123456, 'HQ02', '', 0, 0, '2019-06-23 22:52:02', NULL);

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
(1, 'INV2019032629', 'PM002', '480.00', '2019-03-26 00:29:29', '2019-03-26 00:30:07'),
(2, 'QTA2019032728', 'PM001', '1196.00', '2019-03-27 23:11:28', NULL),
(3, 'INV2019050643', 'PM001', '160.00', '2019-05-06 23:01:43', NULL),
(4, 'QTA2019050648', 'PM002', '1428.00', '2019-05-06 23:03:48', NULL),
(5, 'INV2019050622', 'PM002', '120.00', '2019-05-06 23:04:22', NULL),
(6, 'INV2019050616', 'PM001', '120.00', '2019-05-06 23:05:16', NULL),
(7, 'QTA2019051113', 'PM001', '328.00', '2019-05-11 14:23:13', NULL),
(8, 'QTA2019051114', 'PM002', '984.00', '2019-05-11 14:34:14', NULL),
(9, 'INV2019051254', 'PM001', '328.00', '2019-05-12 10:00:36', NULL),
(10, 'INV2019051940', 'PM001', '360.00', '2019-05-19 22:04:40', NULL),
(11, 'INV2019062301', 'PM001', '80.00', '2019-06-23 22:47:01', NULL),
(12, 'QTA2019062302', 'PM001', '780.00', '2019-06-23 22:52:02', NULL);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
