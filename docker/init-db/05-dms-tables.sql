-- Document Management System permission tables (v2 DMS) — safe to re-run
USE `sahamid`;

CREATE TABLE IF NOT EXISTS `addPerm` (
  `userid` varchar(50) NOT NULL,
  `permission` tinyint(4) NOT NULL DEFAULT 0,
  PRIMARY KEY (`userid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `editPerm` (
  `userid` varchar(50) NOT NULL,
  `permission` tinyint(4) NOT NULL DEFAULT 0,
  PRIMARY KEY (`userid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `category` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cat_name` varchar(255) NOT NULL,
  `cat_code` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `category_perm` (
  `user_id` varchar(50) NOT NULL,
  `cat_id` varchar(255) NOT NULL,
  PRIMARY KEY (`user_id`,`cat_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
