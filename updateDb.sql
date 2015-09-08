-- Adminer 4.2.2-fix MySQL dump

SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

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

-- 2015-09-08 15:15:34
