-- **********************************************************
-- *                                                        *
-- * IMPORTANT NOTE                                         *
-- *                                                        *
-- * Do not import this file manually but use the TYPOlight *
-- * install tool to create and maintain database tables!   *
-- *                                                        *
-- **********************************************************


--
-- Table `tl_iso_payment_modules`
--

CREATE TABLE `tl_iso_payment_modules` (
  `datatrans_id` varchar(16) NOT NULL default '',
  `datatrans_sign` varchar(128) NOT NULL default '',
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

