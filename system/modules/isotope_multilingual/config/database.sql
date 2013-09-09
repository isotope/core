-- **********************************************************
-- *                                                        *
-- * IMPORTANT NOTE                                         *
-- *                                                        *
-- * Do not import this file manually but use the TYPOlight *
-- * install tool to create and maintain database tables!   *
-- *                                                        *
-- **********************************************************

--
-- Table `tl_user`
--

CREATE TABLE `tl_user` (
  `translation` varchar(2) NOT NULL default '',
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


-- --------------------------------------------------------

--
-- Table `tl_iso_labels`
--

CREATE TABLE `tl_iso_labels` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `tstamp` int(10) unsigned NOT NULL default '0',
  `language` varchar(2) NOT NULL default '',
  `label` varchar(255) NOT NULL default '',
  `replacement` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `language` (`language`),
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

