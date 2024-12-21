(
 `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
 `id_page` int(10) unsigned  NOT NULL DEFAULT 0,
 `slug` varchar(60),
 `mid` int(10) unsigned default 0 COMMENT "banner media id",
 `flags` smallint(3) unsigned DEFAULT '0',
 `lft` smallint(3) unsigned NOT NULL DEFAULT '1',
 `rgt` smallint(10) unsigned NOT NULL,
 `icon` varchar(15) DEFAULT NULL,
 `layout` varchar(40),
 `title` varchar(60) NOT NULL,
 `lead` varchar(100),
 `etc` varchar(250),
 `content` text,
  PRIMARY KEY (`id`),
  UNIQUE INDEX (`slug`),
  KEY `zone_id` (`lft`),
  KEY `rgt` (`rgt`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
