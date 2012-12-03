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
  `expercash_popupId` varchar(10) NOT NULL default '',
  `expercash_profile` int(3) NOT NULL default '0',
  `expercash_popupKey` varchar(32) NOT NULL default '',
  `expercash_paymentMethod` varchar(32) NOT NULL default '',
  `expercash_css` varchar(255) NOT NULL default '',
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

