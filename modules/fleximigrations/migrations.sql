CREATE TABLE `migration` (
  `migrationid` int(11) NOT NULL AUTO_INCREMENT,
  `hash` varchar(30) NOT NULL,
  `name` varchar(100) NOT NULL,
  `updated_at` datetime default NULL,
  `created_at` datetime default NULL,
  PRIMARY KEY  (`id`)
);