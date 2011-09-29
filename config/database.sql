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
  `datatrans_id` varchar(100) NOT NULL default '',
  `datatrans_sign` char(1) NOT NULL default '0',
  `datatrans_sign_value` varchar(100) NOT NULL default '',
) ENGINE=MyISAM DEFAULT CHARSET=utf8;