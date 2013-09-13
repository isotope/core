-- **********************************************************
-- *                                                        *
-- * IMPORTANT NOTE                                         *
-- *                                                        *
-- * Do not import this file manually but use the Contao    *
-- * install tool to create and maintain database tables!   *
-- *                                                        *
-- **********************************************************

--
-- Table `tl_iso_payment_modules`
--

CREATE TABLE `tl_iso_payment_modules` (
  `worldpay_instId` int(6) NOT NULL default '0',
  `worldpay_callbackPW` varchar(64) NOT NULL default '',
  `worldpay_md5secret` varchar(255) NOT NULL default '',
  `worldpay_signatureFields` varchar(255) NOT NULL default '',
  `worldpay_description` varchar(255) NOT NULL default '',
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
