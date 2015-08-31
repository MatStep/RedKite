-- Adminer 4.2.2-fix MySQL dump

SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

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

-- 2015-08-31 14:02:34

