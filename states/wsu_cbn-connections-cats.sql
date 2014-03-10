
-- Adminer 3.6.2-dev MySQL dump

SET NAMES utf8;
SET foreign_key_checks = 0;
SET time_zone = 'SYSTEM';
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

USE `wsu_cbn`;

DROP TABLE IF EXISTS `wp_connections_term_taxonomy`;
CREATE TABLE `wp_connections_term_taxonomy` (
  `term_taxonomy_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `term_id` bigint(20) NOT NULL,
  `taxonomy` varchar(32) NOT NULL,
  `description` longtext NOT NULL,
  `parent` bigint(20) NOT NULL,
  `count` bigint(20) NOT NULL,
  PRIMARY KEY (`term_taxonomy_id`),
  UNIQUE KEY `term_id_taxonomy` (`term_id`,`taxonomy`),
  KEY `taxonomy` (`taxonomy`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `wp_connections_term_taxonomy` (`term_taxonomy_id`, `term_id`, `taxonomy`, `description`, `parent`, `count`) VALUES
(1,	1,	'category',	'Entries not assigned to a category will automatically be assigned to this category and deleting a category which has been assigned to an entry will reassign that entry to this category. This category can not be edited or deleted.',	0,	0),
(4,	4,	'category',	'',	0,	0),
(5,	5,	'category',	'',	0,	0),
(6,	6,	'category',	'',	0,	0),
(7,	7,	'category',	'',	0,	0),
(8,	8,	'category',	'',	0,	0),
(9,	9,	'category',	'',	0,	0),
(10,	10,	'category',	'',	0,	0),
(11,	11,	'category',	'',	0,	0),
(12,	12,	'category',	'',	0,	0),
(13,	13,	'category',	'',	0,	0),
(14,	14,	'category',	'',	0,	0),
(15,	15,	'category',	'',	0,	0),
(16,	16,	'category',	'',	0,	0),
(17,	17,	'category',	'',	0,	0),
(18,	18,	'category',	'',	0,	0),
(19,	19,	'category',	'',	0,	0),
(20,	20,	'category',	'',	0,	0),
(21,	21,	'category',	'',	0,	0),
(22,	22,	'category',	'',	0,	0),
(23,	23,	'category',	'',	0,	0),
(24,	24,	'category',	'',	0,	0),
(25,	25,	'category',	'',	0,	0),
(26,	26,	'category',	'',	0,	0),
(27,	27,	'category',	'',	0,	0),
(28,	28,	'category',	'',	0,	0),
(29,	29,	'category',	'',	0,	0),
(30,	30,	'category',	'',	0,	0),
(31,	31,	'category',	'',	0,	0),
(32,	32,	'category',	'',	0,	0),
(33,	33,	'category',	'',	0,	0),
(34,	34,	'category',	'',	0,	0),
(35,	35,	'category',	'',	0,	0),
(36,	36,	'category',	'',	0,	0),
(37,	37,	'category',	'',	0,	0),
(38,	38,	'category',	'',	0,	0),
(39,	39,	'category',	'',	0,	0),
(40,	40,	'category',	'',	0,	0),
(41,	41,	'category',	'',	0,	0),
(42,	42,	'category',	'',	0,	0),
(43,	43,	'category',	'',	0,	0),
(44,	44,	'category',	'',	0,	0),
(45,	45,	'category',	'',	0,	0),
(46,	46,	'category',	'',	0,	0),
(47,	47,	'category',	'',	0,	0),
(48,	48,	'category',	'',	0,	0);

DROP TABLE IF EXISTS `wp_connections_terms`;
CREATE TABLE `wp_connections_terms` (
  `term_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) NOT NULL,
  `slug` varchar(200) NOT NULL,
  `term_group` bigint(10) NOT NULL,
  PRIMARY KEY (`term_id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `wp_connections_terms` (`term_id`, `name`, `slug`, `term_group`) VALUES
(1,	'Uncategorized',	'uncategorized',	0),
(4,	'Accounting',	'accounting',	0),
(5,	'Advertising and Public Relations',	'advertising-and-public-relations',	0),
(6,	'Agriculture',	'agriculture',	0),
(7,	'Architecture',	'architecture',	0),
(8,	'Automotive Sales and Service',	'automotive-sales-and-service',	0),
(9,	'Aviation',	'aviation',	0),
(10,	'Broadcasting',	'broadcasting',	0),
(11,	'Business Development and Consulting',	'business-development-and-consulting',	0),
(12,	'Computer Sales and Service',	'computer-sales-and-service',	0),
(13,	'Construction',	'construction',	0),
(14,	'Consumer Goods and Services',	'consumer-goods-and-services',	0),
(15,	'Counseling Services',	'counseling-services',	0),
(16,	'Dining and Restaurants',	'dining-and-restaurants',	0),
(17,	'Education',	'education',	0),
(18,	'Engineering',	'engineering',	0),
(19,	'Entertainment and Music',	'entertainment-and-music',	0),
(20,	'Event Planning and Promotions',	'event-planning-and-promotions',	0),
(21,	'Financial Services and Planning',	'financial-services-and-planning',	0),
(22,	'Food Supply/Retail Sales',	'food-supplyretail-sales',	0),
(23,	'Health Care',	'health-care',	0),
(24,	'Hospitality and Catering',	'hospitality-and-catering',	0),
(25,	'Hotel and Resort',	'hotel-and-resort',	0),
(26,	'Information Technology',	'information-technology',	0),
(27,	'Insurance Services',	'insurance-services',	0),
(28,	'Landscaping and Yard Maintenance',	'landscaping-and-yard-maintenance',	0),
(29,	'Law and Legal Services',	'law-and-legal-services',	0),
(30,	'Management',	'management',	0),
(31,	'Marketing',	'marketing',	0),
(32,	'Miscellaneous/Other',	'miscellaneous-other',	0),
(33,	'Nonprofit',	'nonprofit',	0),
(34,	'Nursing and Therapy',	'nursing-and-therapy',	0),
(35,	'Nutrition',	'nutrition',	0),
(36,	'Pharmacy and Rx',	'pharmacy-and-rx',	0),
(37,	'Pool, Patio, and Outdoor Living',	'pool-patio-and-outdoor-living',	0),
(38,	'Publishing',	'publishing',	0),
(39,	'Real Estate and Mortgage Lending',	'real-estate-and-mortgage-lending',	0),
(40,	'Retail Sales',	'retail-sales',	0),
(41,	'Sciences',	'sciences',	0),
(42,	'Sports and Fitness',	'sports-and-fitness',	0),
(43,	'Transportation',	'transportation',	0),
(44,	'Travel',	'travel',	0),
(45,	'Veterinary Services',	'veterinary-services',	0),
(46,	'Web Development and Design',	'web-development-and-design',	0),
(47,	'Wineries',	'wineries',	0),
(48,	'Writing and Editing',	'writing-and-editing',	0);

-- 2014-01-09 19:21:32
