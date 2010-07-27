-- **********************************************************
-- *                                                        *
-- * IMPORTANT NOTE                                         *
-- *                                                        *
-- * Do not import this file manually but use the TYPOlight *
-- * install tool to create and maintain database tables!   *
-- *                                                        *
-- **********************************************************


---
--- Table `tl_iso_rule`
---

CREATE TABLE `tl_iso_rule` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `pid` int(10) unsigned NOT NULL default '0',
  `tstamp` int(10) unsigned NOT NULL default '0',
  `type` varchar(255) NOT NULL default '',
  `title` varchar(255) NOT NULL default '',
  `description` text NULL,
  `discount` varchar(255) NOT NULL default '',
  `enableCode` char(1) NOT NULL default '',
  `code` varchar(255) NOT NULL default '',
  `numUses` blob NULL,
  `minSubTotal` decimal(12,2) NOT NULL default '0.00',
  `minCartQuantity` int(10) unsigned NOT NULL default '0',
  `maxCartQuantity` int(10) unsigned NOT NULL default '0',
  `minItemQuantity` int(10) unsigned NOT NULL default '0',
  `maxItemQuantity` int(10) unsigned NOT NULL default '0',
  `collectionType` blob NULL,
  `startDate` int(10) unsigned NOT NULL default '0',
  `endDate` int(10) unsigned NOT NULL default '0',
  `startTime` int(10) unsigned NOT NULL default '0',
  `endTime` int(10) unsigned NOT NULL default '0',
  `collectionTypeRestrictions` char(1) NOT NULL default '',
  `dateRestrictions` varchar(255) NOT NULL default '',  
  `timeRestrictions` varchar(255) NOT NULL default '',
  `memberRestrictions` varchar(255) NOT NULL default '',
  `productRestrictions` varchar(255) NOT NULL default '',
  `ruleRestrictions` varchar(255) NOT NULL default '',
  `members` blob NULL,
  `groups` blob NULL,
  `rules` blob NULL,
  `countries` blob NULL,
  `subdivisions` blob NULL,
  `pages` blob NULL,
  `productTypes` blob NULL,
  `products` blob NULL,
  `paymentModules` blob NULL,
  `shippingModules` blob NULL,
  `enabled` char(1) NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `pid` (`pid`),  
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
 
 
--- --------------------------------------------------------
 
--- 
--- Table `tl_iso_rule_usage`
--- 
 
CREATE TABLE `tl_iso_rule_usage` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `pid` int(10) unsigned NOT NULL default '0',
  `tstamp` int(10) unsigned NOT NULL default '0',
  `object_type` varchar(255) NOT NULL default '',
  `object_id` int(10) unsigned NOT NULL default '0',
  `member_id` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `pid` (`pid`),  
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

-- 
-- Table `tl_iso_rule_codes`
-- 

CREATE TABLE `tl_iso_rule_codes` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `pid` int(10) unsigned NOT NULL default '0',
  `tstamp` int(10) unsigned NOT NULL default '0',
  `member_id` int(10) unsigned NOT NULL default '0',
  `code` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `pid` (`pid`),  
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

-- 
-- Table `tl_module`
-- 

CREATE TABLE `tl_module` (
  `iso_enableCoupons` char(1) NOT NULL default '',
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
