-- Adminer 4.2.2fx MySQL dump

SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

DROP TABLE IF EXISTS `address`;
CREATE TABLE `address` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `street` varchar(30) NOT NULL,
  `street_no` int(5) NOT NULL,
  `city` varchar(30) NOT NULL,
  `country` varchar(30) NOT NULL,
  `zip_code` smallint(7) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `address` (`id`, `street`, `street_no`, `city`, `country`, `zip_code`) VALUES
(1,	'fsadfasd',	12,	'Bratislava',	'Slovensko',	484);

DROP TABLE IF EXISTS `brand`;
CREATE TABLE `brand` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(30) NOT NULL,
  `logo_path` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `brand` (`id`, `name`, `logo_path`) VALUES
(1,	'B&C',	'images/brands/B&C_BLACK_ON_WHITE.jpg'),
(2,	'Fruit of the Loom',	'images/brands/1280px-Fruit_logo.svg.png');

DROP TABLE IF EXISTS `category`;
CREATE TABLE `category` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL DEFAULT '0',
  `icon` varchar(100) NOT NULL,
  `depth` smallint(6) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `category` (`id`, `parent_id`, `icon`, `depth`) VALUES
(68,	0,	'oblecenie',	0),
(76,	68,	'bvmbm',	1),
(77,	68,	'sdasjhadsfh',	1);

DROP TABLE IF EXISTS `category_lang`;
CREATE TABLE `category_lang` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_id` int(11) NOT NULL,
  `lang_id` int(11) NOT NULL,
  `name` varchar(30) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `category_id` (`category_id`),
  KEY `lang_id` (`lang_id`),
  CONSTRAINT `category_lang_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `category` (`id`),
  CONSTRAINT `category_lang_ibfk_2` FOREIGN KEY (`lang_id`) REFERENCES `lang` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `category_lang` (`id`, `category_id`, `lang_id`, `name`) VALUES
(20,	68,	1,	'Oblečenie'),
(21,	68,	2,	'Oblečení'),
(28,	76,	1,	'afsd'),
(29,	76,	2,	'vjkb'),
(30,	77,	1,	'spodky'),
(31,	77,	2,	'spodare');

DROP TABLE IF EXISTS `currency`;
CREATE TABLE `currency` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(30) NOT NULL,
  `sign` varchar(100) NOT NULL,
  `rate` double NOT NULL,
  `active` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `currency` (`id`, `name`, `sign`, `rate`, `active`) VALUES
(1,	'Euro',	'€',	1,	1),
(2,	'Česká koruna',	'CZK',	27,	0);

DROP TABLE IF EXISTS `customer`;
CREATE TABLE `customer` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `address_id` int(11) NOT NULL,
  `first_name` varchar(30) NOT NULL,
  `second_name` varchar(30) NOT NULL,
  `company` varchar(50) NOT NULL,
  `VAT_registered` tinyint(4) NOT NULL DEFAULT '0',
  `ICO` varchar(30) DEFAULT NULL,
  `DIC` varchar(30) DEFAULT NULL,
  `ICO_DPH` varchar(30) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `address_id` (`address_id`),
  CONSTRAINT `customer_ibfk_1` FOREIGN KEY (`address_id`) REFERENCES `address` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `feature`;
CREATE TABLE `feature` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` tinyint(4) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `feature` (`id`, `type`) VALUES
(1,	NULL),
(20,	NULL);

DROP TABLE IF EXISTS `feature_lang`;
CREATE TABLE `feature_lang` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `feature_id` int(11) NOT NULL,
  `lang_id` int(11) NOT NULL,
  `name` varchar(30) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `feature_id` (`feature_id`),
  KEY `lang_id` (`lang_id`),
  CONSTRAINT `feature_lang_ibfk_1` FOREIGN KEY (`feature_id`) REFERENCES `feature` (`id`),
  CONSTRAINT `feature_lang_ibfk_2` FOREIGN KEY (`lang_id`) REFERENCES `lang` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `feature_lang` (`id`, `feature_id`, `lang_id`, `name`) VALUES
(1,	1,	1,	'farba'),
(2,	1,	2,	'farba'),
(39,	20,	1,	'veľkosť'),
(40,	20,	2,	'veľkosť');

DROP TABLE IF EXISTS `feature_value`;
CREATE TABLE `feature_value` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `feature_id` int(11) NOT NULL,
  `value` varchar(30) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `feature_id` (`feature_id`),
  CONSTRAINT `feature_value_ibfk_1` FOREIGN KEY (`feature_id`) REFERENCES `feature` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `feature_value` (`id`, `feature_id`, `value`) VALUES
(7,	1,	''),
(8,	1,	''),
(9,	1,	''),
(13,	20,	''),
(14,	20,	''),
(15,	20,	''),
(16,	20,	''),
(17,	20,	'');

DROP TABLE IF EXISTS `feature_value_lang`;
CREATE TABLE `feature_value_lang` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `feature_value_id` int(11) NOT NULL,
  `lang_id` int(11) NOT NULL,
  `value` varchar(30) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `feature_value_id` (`feature_value_id`),
  KEY `lang_id` (`lang_id`),
  CONSTRAINT `feature_value_lang_ibfk_1` FOREIGN KEY (`feature_value_id`) REFERENCES `feature_value` (`id`),
  CONSTRAINT `feature_value_lang_ibfk_2` FOREIGN KEY (`lang_id`) REFERENCES `lang` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `feature_value_lang` (`id`, `feature_value_id`, `lang_id`, `value`) VALUES
(7,	7,	1,	'biela'),
(8,	7,	2,	'biela'),
(9,	8,	1,	'čierna'),
(10,	8,	2,	'čierna'),
(11,	9,	1,	'modrá'),
(12,	9,	2,	'modrá'),
(19,	13,	1,	'XS'),
(20,	13,	2,	'XS'),
(21,	14,	1,	'S'),
(22,	14,	2,	'S'),
(23,	15,	1,	'M'),
(24,	15,	2,	'M'),
(25,	16,	1,	'L'),
(26,	16,	2,	'L'),
(27,	17,	1,	'XL'),
(28,	17,	2,	'XL');

DROP TABLE IF EXISTS `invoice`;
CREATE TABLE `invoice` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `address_dest_id` int(11) NOT NULL,
  `currency_id` int(11) NOT NULL,
  `number` int(11) NOT NULL,
  `date_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_payable` date NOT NULL,
  `currency` varchar(30) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  KEY `address_dest_id` (`address_dest_id`),
  KEY `currency_id` (`currency_id`),
  CONSTRAINT `invoice_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `order` (`id`),
  CONSTRAINT `invoice_ibfk_2` FOREIGN KEY (`address_dest_id`) REFERENCES `address` (`id`),
  CONSTRAINT `invoice_ibfk_3` FOREIGN KEY (`currency_id`) REFERENCES `currency` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `lang`;
CREATE TABLE `lang` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(30) NOT NULL,
  `img` varchar(100) DEFAULT NULL,
  `active` tinyint(4) NOT NULL DEFAULT '0',
  `iso_code` varchar(5) NOT NULL,
  `language_code` varchar(10) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `lang` (`id`, `name`, `img`, `active`, `iso_code`, `language_code`) VALUES
(1,	'slovak',	NULL,	1,	'sk',	'sk_SVK'),
(2,	'czech',	NULL,	1,	'cs',	'cs_CZ');

DROP TABLE IF EXISTS `order`;
CREATE TABLE `order` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `tax_id` int(11) NOT NULL,
  `subject` varchar(100) NOT NULL,
  `desc` text,
  `state` tinyint(4) NOT NULL DEFAULT '0',
  `email` varchar(50) NOT NULL,
  `code` int(11) NOT NULL,
  `date_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `discount_percent` int(11) DEFAULT NULL,
  `discount_money` double DEFAULT NULL,
  `total_price_tax_incl` double NOT NULL DEFAULT '0',
  `total_price_tax_ecl` double NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `customer_id` (`customer_id`),
  KEY `tax_id` (`tax_id`),
  CONSTRAINT `order_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`),
  CONSTRAINT `order_ibfk_2` FOREIGN KEY (`customer_id`) REFERENCES `customer` (`id`),
  CONSTRAINT `order_ibfk_3` FOREIGN KEY (`tax_id`) REFERENCES `tax` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `product`;
CREATE TABLE `product` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `brand_id` int(11) DEFAULT NULL,
  `code` varchar(12) DEFAULT NULL,
  `status` tinyint(4) NOT NULL DEFAULT '0',
  `order` int(11) NOT NULL DEFAULT '0',
  `add_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `price_sell` double NOT NULL,
  PRIMARY KEY (`id`),
  KEY `brand_id` (`brand_id`),
  CONSTRAINT `product_ibfk_1` FOREIGN KEY (`brand_id`) REFERENCES `brand` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `product` (`id`, `brand_id`, `code`, `status`, `order`, `add_date`, `price_sell`) VALUES
(41,	1,	NULL,	0,	0,	'2015-09-30 21:09:13',	12),
(42,	1,	NULL,	0,	0,	'2015-10-02 13:48:50',	12),
(43,	2,	NULL,	1,	0,	'2016-01-13 09:46:09',	14),
(55,	1,	'AB124',	0,	0,	'2016-01-16 10:44:03',	14);

DROP TABLE IF EXISTS `product_category`;
CREATE TABLE `product_category` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `category_id` (`category_id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `product_category_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `category` (`id`),
  CONSTRAINT `product_category_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `product` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `product_category` (`id`, `category_id`, `product_id`) VALUES
(28,	68,	42),
(35,	68,	41),
(36,	76,	41),
(41,	68,	43),
(44,	68,	55);

DROP TABLE IF EXISTS `product_feature`;
CREATE TABLE `product_feature` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `feature_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`),
  KEY `feature_id` (`feature_id`),
  CONSTRAINT `product_feature_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `product` (`id`),
  CONSTRAINT `product_feature_ibfk_2` FOREIGN KEY (`feature_id`) REFERENCES `feature` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `product_feature` (`id`, `product_id`, `feature_id`) VALUES
(25,	43,	1);

DROP TABLE IF EXISTS `product_feature_value`;
CREATE TABLE `product_feature_value` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_feature_id` int(11) NOT NULL,
  `feature_value_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `feature_value_id` (`feature_value_id`),
  KEY `product_feature_id` (`product_feature_id`),
  CONSTRAINT `product_feature_value_ibfk_2` FOREIGN KEY (`feature_value_id`) REFERENCES `feature_value` (`id`),
  CONSTRAINT `product_feature_value_ibfk_3` FOREIGN KEY (`product_feature_id`) REFERENCES `product_feature` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `product_feature_value` (`id`, `product_feature_id`, `feature_value_id`) VALUES
(3,	25,	8),
(6,	25,	9),
(11,	25,	7);

DROP TABLE IF EXISTS `product_image`;
CREATE TABLE `product_image` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `path` varchar(100) NOT NULL,
  `order` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `product_image_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `product` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `product_image` (`id`, `product_id`, `path`, `order`) VALUES
(3,	41,	'images/products/logo_web.png',	1),
(4,	41,	'images/products/logo.png',	2);

DROP TABLE IF EXISTS `product_image_lang`;
CREATE TABLE `product_image_lang` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_image_id` int(11) NOT NULL,
  `lang_id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `product_image_id` (`product_image_id`),
  KEY `lang_id` (`lang_id`),
  CONSTRAINT `product_image_lang_ibfk_1` FOREIGN KEY (`product_image_id`) REFERENCES `product_image` (`id`),
  CONSTRAINT `product_image_lang_ibfk_2` FOREIGN KEY (`lang_id`) REFERENCES `lang` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `product_image_lang` (`id`, `product_image_id`, `lang_id`, `name`) VALUES
(5,	3,	1,	'logo_web'),
(6,	3,	2,	'logo_web'),
(7,	4,	1,	'logo'),
(8,	4,	2,	'logo');

DROP TABLE IF EXISTS `product_lang`;
CREATE TABLE `product_lang` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `lang_id` int(11) NOT NULL,
  `name` varchar(30) NOT NULL,
  `short_desc` varchar(150) DEFAULT NULL,
  `desc` text,
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`),
  KEY `lang_id` (`lang_id`),
  CONSTRAINT `product_lang_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `product` (`id`),
  CONSTRAINT `product_lang_ibfk_2` FOREIGN KEY (`lang_id`) REFERENCES `lang` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `product_lang` (`id`, `product_id`, `lang_id`, `name`, `short_desc`, `desc`) VALUES
(32,	41,	1,	'fsdasad',	'sfs',	''),
(33,	41,	2,	'fsdasad',	'sfs',	''),
(34,	42,	1,	'fds',	'dfs',	''),
(35,	42,	2,	'fds',	'dfs',	''),
(36,	43,	1,	'vsdz',	'vszd',	'<p>vsdz</p>\n'),
(37,	43,	2,	'vsdz',	'vszd',	'<p>vsdz</p>\n'),
(40,	55,	1,	'fsdasad',	'fds',	''),
(41,	55,	2,	'fsdasad',	'fds',	'');

DROP TABLE IF EXISTS `product_order`;
CREATE TABLE `product_order` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `count` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `product_service`;
CREATE TABLE `product_service` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `service_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`),
  KEY `service_id` (`service_id`),
  CONSTRAINT `product_service_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `product` (`id`),
  CONSTRAINT `product_service_ibfk_2` FOREIGN KEY (`service_id`) REFERENCES `service` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `product_sticker`;
CREATE TABLE `product_sticker` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `sticker_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`),
  KEY `sticker_id` (`sticker_id`),
  CONSTRAINT `product_sticker_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `product` (`id`),
  CONSTRAINT `product_sticker_ibfk_2` FOREIGN KEY (`sticker_id`) REFERENCES `sticker` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `product_stock`;
CREATE TABLE `product_stock` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `stock_id` int(11) NOT NULL,
  `product_stock_feature_id` int(11) DEFAULT NULL,
  `count` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`),
  KEY `stock_id` (`stock_id`),
  KEY `product_stock_feature_id` (`product_stock_feature_id`),
  CONSTRAINT `product_stock_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `product` (`id`),
  CONSTRAINT `product_stock_ibfk_2` FOREIGN KEY (`stock_id`) REFERENCES `stock` (`id`),
  CONSTRAINT `product_stock_ibfk_3` FOREIGN KEY (`product_stock_feature_id`) REFERENCES `product_stock_feature` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `product_stock_feature`;
CREATE TABLE `product_stock_feature` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_stock` int(11) NOT NULL,
  `product_feature_value` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `product_stock` (`product_stock`),
  KEY `product_feature_value` (`product_feature_value`),
  CONSTRAINT `product_stock_feature_ibfk_1` FOREIGN KEY (`product_stock`) REFERENCES `product_stock` (`id`),
  CONSTRAINT `product_stock_feature_ibfk_2` FOREIGN KEY (`product_feature_value`) REFERENCES `product_feature_value` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `product_supplier`;
CREATE TABLE `product_supplier` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `supplier_id` int(11) NOT NULL,
  `price_buy` double DEFAULT NULL,
  `status` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`),
  KEY `supplier_id` (`supplier_id`),
  CONSTRAINT `product_supplier_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `product` (`id`),
  CONSTRAINT `product_supplier_ibfk_2` FOREIGN KEY (`supplier_id`) REFERENCES `supplier` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `product_supplier` (`id`, `product_id`, `supplier_id`, `price_buy`, `status`) VALUES
(7,	41,	1,	10,	1),
(8,	42,	1,	10,	1),
(9,	43,	1,	12,	1),
(11,	55,	1,	12,	1);

DROP TABLE IF EXISTS `product_tag`;
CREATE TABLE `product_tag` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `tag_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`),
  KEY `tag_id` (`tag_id`),
  CONSTRAINT `product_tag_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `product` (`id`),
  CONSTRAINT `product_tag_ibfk_2` FOREIGN KEY (`tag_id`) REFERENCES `tag` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `reference`;
CREATE TABLE `reference` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `logo_path` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `reference_lang`;
CREATE TABLE `reference_lang` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `reference_id` int(11) NOT NULL,
  `lang_id` int(11) NOT NULL,
  `name` varchar(30) NOT NULL,
  `desc` text,
  PRIMARY KEY (`id`),
  KEY `reference_id` (`reference_id`),
  KEY `lang_id` (`lang_id`),
  CONSTRAINT `reference_lang_ibfk_1` FOREIGN KEY (`reference_id`) REFERENCES `reference` (`id`),
  CONSTRAINT `reference_lang_ibfk_2` FOREIGN KEY (`lang_id`) REFERENCES `lang` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `rights`;
CREATE TABLE `rights` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `right` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `rights_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `service`;
CREATE TABLE `service` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `img_path` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `service_feature`;
CREATE TABLE `service_feature` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `service_id` int(11) NOT NULL,
  `feature_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `service_id` (`service_id`),
  KEY `feature_id` (`feature_id`),
  CONSTRAINT `service_feature_ibfk_1` FOREIGN KEY (`service_id`) REFERENCES `service` (`id`),
  CONSTRAINT `service_feature_ibfk_2` FOREIGN KEY (`feature_id`) REFERENCES `feature` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `service_feature_2`;
CREATE TABLE `service_feature_2` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `service_feature1_id` int(11) NOT NULL,
  `service_feature2_id` int(11) NOT NULL,
  `tax_id` int(11) NOT NULL,
  `price_buy` double DEFAULT NULL,
  `price_sell` double NOT NULL,
  PRIMARY KEY (`id`),
  KEY `service_feature1_id` (`service_feature1_id`),
  KEY `service_feature2_id` (`service_feature2_id`),
  CONSTRAINT `service_feature_2_ibfk_1` FOREIGN KEY (`service_feature1_id`) REFERENCES `service_feature` (`id`),
  CONSTRAINT `service_feature_2_ibfk_2` FOREIGN KEY (`service_feature2_id`) REFERENCES `service_feature` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `service_lang`;
CREATE TABLE `service_lang` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `service_id` int(11) NOT NULL,
  `lang_id` int(11) NOT NULL,
  `name` varchar(30) NOT NULL,
  `desc` text,
  PRIMARY KEY (`id`),
  KEY `service_id` (`service_id`),
  KEY `lang_id` (`lang_id`),
  CONSTRAINT `service_lang_ibfk_1` FOREIGN KEY (`service_id`) REFERENCES `service` (`id`),
  CONSTRAINT `service_lang_ibfk_2` FOREIGN KEY (`lang_id`) REFERENCES `lang` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `sticker`;
CREATE TABLE `sticker` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(30) NOT NULL,
  `path` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `stock`;
CREATE TABLE `stock` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `address_id` int(11) NOT NULL,
  `name` char(30) NOT NULL,
  `count_products` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `address_id` (`address_id`),
  CONSTRAINT `stock_ibfk_1` FOREIGN KEY (`address_id`) REFERENCES `address` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `supplier`;
CREATE TABLE `supplier` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `address_id` int(11) NOT NULL,
  `name` varchar(30) NOT NULL,
  `date_from` date NOT NULL,
  PRIMARY KEY (`id`),
  KEY `address_id` (`address_id`),
  CONSTRAINT `supplier_ibfk_1` FOREIGN KEY (`address_id`) REFERENCES `address` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `supplier` (`id`, `address_id`, `name`, `date_from`) VALUES
(1,	1,	'Dodávateľ 1',	'2015-09-01');

DROP TABLE IF EXISTS `tag`;
CREATE TABLE `tag` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(30) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `tax`;
CREATE TABLE `tax` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(30) NOT NULL,
  `value` int(11) NOT NULL,
  `active` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `tax` (`id`, `name`, `value`, `active`) VALUES
(1,	'SVK daň',	20,	1),
(2,	'ČR daň',	21,	0);

DROP TABLE IF EXISTS `user`;
CREATE TABLE `user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(30) NOT NULL,
  `password` varchar(70) NOT NULL,
  `email` varchar(50) NOT NULL,
  `reg_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `role` varchar(30) NOT NULL DEFAULT 'user',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `user` (`id`, `name`, `password`, `email`, `reg_date`, `role`) VALUES
(1,	'admin',	'$2y$10$LT4tHNVCez2lDkNmS7SUTOYLrn3ySbokG6b5/LjyFakKXKZKqbq5y',	'',	'2015-08-28 21:37:13',	'admin'),
(2,	'user',	'$2y$10$nIUnGVrnZBvbcoCcnucOYOIEO9hHhCUqtT3nla00dhk1Z5Blfj4bG',	'',	'2015-08-31 13:41:32',	'user');

-- 2016-01-16 10:49:48
