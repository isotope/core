-- **********************************************************
-- *                                                        *
-- * IMPORTANT NOTE                                         *
-- *                                                        *
-- * Do not import this file manually but use the TYPOlight *
-- * install tool to create and maintain database tables!   *
-- *                                                        *
-- **********************************************************


-- 
-- Table `tl_content`
-- 

CREATE TABLE `tl_content` (
  `productsAlias` blob NULL,
  `iso_reader_jumpTo` int(10) unsigned NOT NULL default '0',
  `iso_list_layout` varchar(64) NOT NULL default ''
) ENGINE=MyISAM DEFAULT CHARSET=utf8;