-- **********************************************************
-- *                                                        *
-- * IMPORTANT NOTE                                         *
-- *                                                        *
-- * Do not import this file manually but use the Contao    *
-- * install tool to create and maintain database tables!   *
-- *                                                        *
-- **********************************************************


--
-- Table `tl_iso_config`
--

CREATE TABLE `tl_iso_config` (
  `ga_enable` char(1) NOT NULL default '',
  `ga_account` varchar(64) NOT NULL default ''
  `ga_member` varchar(255) NOT NULL default ''
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
