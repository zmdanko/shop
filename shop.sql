CREATE DATABASE IF NOT EXISTS `shop`;

USE `shop`;

CREATE TABLE IF NOT EXISTS `customers`(
	`userid` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`username` VARCHAR(10) NOT NULL ,
	`password` VARCHAR(60) NOT NULL ,
	`email` VARCHAR(30) NOT NULL ,
	`token` VARCHAR(40),
	`token_exptime` VARCHAR(40),
	`confirm` TINYINT(1),
	PRIMARY KEY(`userid`)
)ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `address`(
	`address_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`customer_id` INT(10) UNSIGNED NOT NULL,
	`name` VARCHAR(10) NOT NULL,
	`address` VARCHAR(40) NOT NULL ,
	`phone` VARCHAR(20) NOT NULL ,
	PRIMARY KEY(`address_id`,`customer_id`)
)ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `orders`(
	`order_id` INT(8) UNSIGNED NOT NULL AUTO_INCREMENT DEFAUT,
	`address_id` INT(10) NOT NULL,
	`total_price` FLOAT(10,2) NOT NULL,
	`condition` VARCHAR(40) NOT NULL,
	PRIMARY KEY(`order_id`)
)ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `order_items`(
	`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`order_id` INT(10) UNSIGNED NOT NULL,
	`item` VARCHAR(30) NOT NULL,
	`quantity` TINYINT(10) NOT NULL,
	`total_price` FLOAT(10,2) NOT NULL,
	PRIMARY KEY(`id`,`order_id`)
)ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `items`(
	`item_id` INT(10) UNSIGNED AUTO_INCREMENT NOT NULL,
	`item` VARCHAR(30) NOT NULL,
	`price` FLOAT(10,2) NOT NULL,
	`category` VARCHAR(30) NOT NULL,
	`description` VARCHAR(255),
	`sell` INT(10) UNSIGNED,
	`quantity` INT(10) UNSIGNED,
	PRIMARY KEY(`item`)
)ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `admin`(
	`admin_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`admin_name` VARCHAR(10) NOT NULL,
	`password` VARCHAR(60) NOT NULL,
	PRIMARY KEY(`admin_id`)
)ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `categories`(
	`category_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`category` VARCHAR(10) NOT NULL,
	PRIMARY KEY(`category_id`,`category`)
)ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `comment`(
	`comment_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`item_id` INT(10) UNSIGNED NOT NULL,
	`username` VARCHAR(10) NOT NULL,
	`comment` VARCHAR(150) NOT NULL,
	PRIMARY KEY(`comment_id`,`item_id`)
)ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_unicode_ci;
