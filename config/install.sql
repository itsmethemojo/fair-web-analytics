CREATE TABLE `simpletrac_domains` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `domain` varchar(100) CHARACTER SET latin1 NOT NULL,
  `counter` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;

CREATE TABLE `simpletrac_websites` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `url` varchar(100) CHARACTER SET latin1 NOT NULL,
  `domain_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;

CREATE TABLE `simpletrac_visits` (
  `website_id` int(11) NOT NULL,
  `visitor` varchar(100) CHARACTER SET latin1 NOT NULL,
  `date` varchar(10) CHARACTER SET latin1 NOT NULL,
  PRIMARY KEY (`website_id`,`visitor`,`date`)
);