-- **********************************************************
-- *                                                        *
-- * IMPORTANT NOTE                                         *
-- *                                                        *
-- * Do not import this file manually but use the TYPOlight *
-- * install tool to create and maintain database tables!   *
-- *                                                        *
-- **********************************************************


---
--- Table `tl_iso_rules`
---

CREATE TABLE `tl_iso_rules` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `pid` int(10) unsigned NOT NULL default '0',
  `sorting` int(10) unsigned NOT NULL default '0',
  `tstamp` int(10) unsigned NOT NULL default '0',
  `type` varchar(32) NOT NULL default '',
  `name` varchar(255) NOT NULL default '',
  `label` varchar(255) NOT NULL default '',
  `applyTo` varchar(8) NOT NULL default '',
  `discount` varchar(16) NOT NULL default '',
  `tax_class` int(10) NOT NULL default '0',
  `enableCode` char(1) NOT NULL default '',
  `code` varchar(255) NOT NULL default '',
  `minSubtotal` int(10) unsigned NOT NULL default '0',
  `maxSubtotal` int(10) unsigned NOT NULL default '0',
  `minItemQuantity` int(10) unsigned NOT NULL default '0',
  `maxItemQuantity` int(10) unsigned NOT NULL default '0',
  `quantityMode` varchar(32) NOT NULL default '',
  `limitPerMember` int(10) unsigned NOT NULL default '0',
  `limitPerConfig` int(10) unsigned NOT NULL default '0',
  `startDate` varchar(10) NOT NULL default '',
  `endDate` varchar(10) NOT NULL default '',
  `startTime` varchar(10) NOT NULL default '',
  `endTime` varchar(10) NOT NULL default '',
  `configRestrictions` char(1) NOT NULL default '',
  `configCondition` char(1) NOT NULL default '',
  `memberRestrictions` varchar(32) NOT NULL default '',
  `memberCondition` char(1) NOT NULL default '',
  `productRestrictions` varchar(32) NOT NULL default '',
  `productCondition` char(1) NOT NULL default '',
  `attributeName` varchar(32) NOT NULL default '',
  `attributeCondition` varchar(8) NOT NULL default '',
  `attributeValue` varchar(255) NOT NULL default '',
  `enabled` char(1) NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `pid` (`pid`),
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--- --------------------------------------------------------

--
-- Table `tl_iso_rule_restrictions`
--

CREATE TABLE `tl_iso_rule_restrictions` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `pid` int(10) unsigned NOT NULL default '0',
  `tstamp` int(10) unsigned NOT NULL default '0',
  `type` varchar(32) NOT NULL default '',
  `object_id` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `pid` (`pid`, `type`),
  KEY `type` (`type`, `object_id`, `pid`),
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

---
--- Table `tl_iso_rule_usage`
---

CREATE TABLE `tl_iso_rule_usage` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `pid` int(10) unsigned NOT NULL default '0',
  `tstamp` int(10) unsigned NOT NULL default '0',
  `order_id` int(10) unsigned NOT NULL default '0',
  `config_id` int(10) unsigned NOT NULL default '0',
  `member_id` int(10) unsigned NOT NULL default '0',
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

-- --------------------------------------------------------

--
-- Table `tl_iso_cart`
--

CREATE TABLE `tl_iso_cart` (
  `coupons` blob NULL,
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

