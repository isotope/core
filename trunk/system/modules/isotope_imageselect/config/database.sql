-- **********************************************************
-- *                                                        *
-- * IMPORTANT NOTE                                         *
-- *                                                        *
-- * Do not import this file manually but use the TYPOlight *
-- * install tool to create and maintain database tables!   *
-- *                                                        *
-- **********************************************************

-- 
-- Table `tl_iso_attributes`
-- 

CREATE TABLE `tl_iso_attributes` (
  `imageSource` varchar(255) NOT NULL default '',
  `size` varchar(64) NOT NULL default '',
  `sortBy` varchar(32) NOT NULL default '',
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

