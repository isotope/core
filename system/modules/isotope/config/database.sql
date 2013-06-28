-- **********************************************************
-- *                                                        *
-- * IMPORTANT NOTE                                         *
-- *                                                        *
-- * Do not import this file manually but use the Contao    *
-- * install tool to create and maintain database tables!   *
-- *                                                        *
-- **********************************************************


--
-- Table `tl_iso_groups`
--

CREATE TABLE `tl_iso_groups` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `pid` int(10) unsigned NOT NULL default '0',
  `sorting` int(10) unsigned NOT NULL default '0',
  `tstamp` int(10) unsigned NOT NULL default '0',
  `name` varchar(255) NOT NULL default '',
  `product_type` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `pid` (`pid`),
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table `tl_iso_products`
--

CREATE TABLE `tl_iso_products` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `pid` int(10) unsigned NOT NULL default '0',
  `gid` int(10) unsigned NOT NULL default '0',
  `tstamp` int(10) unsigned NOT NULL default '0',
  `dateAdded` int(10) unsigned NOT NULL default '0',
  `type` int(10) unsigned NOT NULL default '0',
  `language` varchar(5) NOT NULL default '',
  `pages` blob NULL,
  `inherit` blob NULL,
  `alias` varchar(128) NOT NULL default '',
  `sku` varchar(128) NOT NULL default '',
  `name` varchar(255) NOT NULL default '',
  `images` blob NULL,
  `teaser` text NULL,
  `description` text NULL,
  `pagetitle_meta` text NULL,
  `keywords_meta` text NULL,
  `description_meta` text NULL,
  `price` decimal(12,2) NOT NULL default '0.00',
  `shipping_weight` varchar(255) NOT NULL default '',
  `shipping_exempt` char(1) NOT NULL default '',
  `tax_class` int(10) unsigned NOT NULL default '0',
  `baseprice` varchar(255) NOT NULL default '',
  `protected` char(1) NOT NULL default '',
  `groups` blob NULL,
  `guests` char(1) NOT NULL default '',
  `cssID` varchar(255) NOT NULL default '',
  `published` char(1) NOT NULL default '',
  `start` varchar(10) NOT NULL default '',
  `stop` varchar(10) NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `gid` (`gid`),
#  KEY `pid` (`pid`, `language`, `published`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table `tl_iso_product_categories`
--

CREATE TABLE `tl_iso_product_categories` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `pid` int(10) unsigned NOT NULL default '0',
  `sorting` int(10) unsigned NOT NULL default '0',
  `tstamp` int(10) unsigned NOT NULL default '0',
  `page_id` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `pid` (`pid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table `tl_iso_prices`
--

CREATE TABLE `tl_iso_prices` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `pid` int(10) unsigned NOT NULL default '0',
  `tstamp` int(10) unsigned NOT NULL default '0',
  `tax_class` int(10) unsigned NOT NULL default '0',
  `config_id` int(10) unsigned NOT NULL default '0',
  `member_group` int(10) unsigned NOT NULL default '0',
  `start` varchar(10) NOT NULL default '',
  `stop` varchar(10) NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `pid` (`pid`),
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table `tl_iso_price_tiers`
--

CREATE TABLE `tl_iso_price_tiers` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `pid` int(10) unsigned NOT NULL default '0',
  `tstamp` int(10) unsigned NOT NULL default '0',
  `min` int(10) unsigned NOT NULL default '0',
  `price` decimal(12,2) NOT NULL default '0.00',
  PRIMARY KEY  (`id`),
  KEY `pid` (`pid`),
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table `tl_iso_downloads`
--

CREATE TABLE `tl_iso_downloads` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `pid` int(10) unsigned NOT NULL default '0',
  `sorting` int(10) unsigned NOT NULL default '0',
  `tstamp` int(10) unsigned NOT NULL default '0',
  `type` varchar(8) NOT NULL default 'file',
  `singleSRC` varchar(255) NOT NULL default '',
  `title` varchar(255) NOT NULL default '',
  `description` text NULL,
  `downloads_allowed` int(5) unsigned NOT NULL default '0',
  `expires` varchar(64) NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `pid` (`pid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table `tl_iso_producttypes`
--

CREATE TABLE `tl_iso_producttypes` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `tstamp` int(10) unsigned NOT NULL default '0',
  `name` varchar(255) NOT NULL default '',
  `class` varchar(32) NOT NULL default 'standard',
  `fallback` char(1) NOT NULL default '',
  `description` text NULL,
  `prices` char(1) NOT NULL default '',
  `show_price_tiers` char(1) NOT NULL default '',
  `list_template` varchar(255) NOT NULL default '',
  `reader_template` varchar(255) NOT NULL default '',
  `attributes` blob NULL,
  `variants` char(1) NOT NULL default '',
  `variant_attributes` blob NULL,
  `force_variant_options` char(1) NOT NULL default '',
  `shipping_exempt` char(1) NOT NULL default '',
  `downloads` char(1) NOT NULL default '',
  PRIMARY KEY  (`id`),
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table `tl_iso_attributes`
--

CREATE TABLE `tl_iso_attributes` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `tstamp` int(10) unsigned NOT NULL default '0',
  `name` varchar(255) NOT NULL default '',
  `field_name` varchar(30) NOT NULL default '',
  `type` varchar(64) NOT NULL default '',
  `legend` varchar(255) NOT NULL default '',
  `description` varchar(255) NOT NULL default '',
  `options` blob NULL,
  `customer_defined` char(1) NOT NULL default '',
  `mandatory` char(1) NOT NULL default '',
  `fe_filter` char(1) NOT NULL default '',
  `fe_search` char(1) NOT NULL default '',
  `fe_sorting` char(1) NOT NULL default '',
  `multiple` char(1) NOT NULL default '',
  `size` smallint(5) unsigned NOT NULL default '0',
  `extensions` varchar(255) NOT NULL default '',
  `be_filter` char(1) NOT NULL default '',
  `be_search` char(1) NOT NULL default '',
  `multilingual` char(1) NOT NULL default '',
  `variant_option` char(1) NOT NULL default '',
  `invisible` char(1) NOT NULL default '',
  `foreignKey` text NULL,
  `rte` varchar(255) NOT NULL default '',
  `maxlength` int(10) unsigned NOT NULL default '0',
  `rgxp` varchar(255) NOT NULL default '',
  `conditionField` varchar(30) NOT NULL default '',
  `gallery` varchar(64) NOT NULL default '',
  `files` char(1) NOT NULL default '',
  `filesOnly` char(1) NOT NULL default '',
  `fieldType` varchar(8) NOT NULL default '',
  `sortBy` varchar(32) NOT NULL default '',
  `storeFile` char(1) NOT NULL default '',
  `uploadFolder` varchar(255) NOT NULL default '',
  `useHomeDir` char(1) NOT NULL default '',
  `doNotOverwrite` char(1) NOT NULL default '',
  `path` varchar(255) NOT NULL default '',
  `datepicker` char(1) NOT NULL default '',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table `tl_iso_related_categories`
--

CREATE TABLE `tl_iso_related_categories` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `tstamp` int(10) unsigned NOT NULL default '0',
  `name` varchar(255) NOT NULL default '',
  `jumpTo` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table `tl_iso_related_products`
--

CREATE TABLE `tl_iso_related_products` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `pid` int(10) unsigned NOT NULL default '0',
  `tstamp` int(10) unsigned NOT NULL default '0',
  `sorting` int(10) unsigned NOT NULL default '0',
  `category` int(10) unsigned NOT NULL default '0',
  `products` blob NULL,
  PRIMARY KEY  (`id`),
  KEY `pid` (`pid`),
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table `tl_iso_payment_modules`
--

CREATE TABLE `tl_iso_payment_modules` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `tstamp` int(10) unsigned NOT NULL default '0',
  `name` varchar(255) NOT NULL default '',
  `type` varchar(64) NOT NULL default '',
  `label` varchar(255) NOT NULL default '',
  `note` text NULL,
  `price` varchar(16) NOT NULL default '',
  `tax_class` int(10) NOT NULL default '0',
  `countries` blob NULL,
  `shipping_modules` blob NULL,
  `product_types` blob NULL,
  `allowed_cc_types` text NULL,
  `minimum_total` decimal(12,2) NOT NULL default '0.00',
  `maximum_total` decimal(12,2) NOT NULL default '0.00',
  `new_order_status` int(10) unsigned NOT NULL default '0',
  `trans_type` varchar(8) NOT NULL default '',
  `paypal_account` varchar(255) NOT NULL default '',
  `postfinance_pspid` varchar(255) NOT NULL default '',
  `postfinance_secret` varchar(255) NOT NULL default '',
  `postfinance_method` varchar(4) NOT NULL default '',
  `authorize_login` varchar(255) NOT NULL default '',
  `authorize_trans_key` varchar(255) NOT NULL default '',
  `authorize_delimiter` varchar(4) NOT NULL default '',
  `authorize_trans_type` varchar(32) NOT NULL default '',
  `authorize_relay_response` char(1) NOT NULL default '',
  `authorize_email_customer` char(1) NOT NULL default '',
  `datatrans_id` varchar(16) NOT NULL default '',
  `datatrans_sign` varchar(128) NOT NULL default '',
  `sparkasse_paymentmethod` varchar(32) NOT NULL default '',
  `sparkasse_sslmerchant` varchar(16) NOT NULL default '',
  `sparkasse_sslpassword` varchar(255) NOT NULL default '',
  `sparkasse_merchantref` varchar(255) NOT NULL default '',
  `expercash_popupId` varchar(10) NOT NULL default '',
  `expercash_profile` int(3) NOT NULL default '0',
  `expercash_popupKey` varchar(32) NOT NULL default '',
  `expercash_paymentMethod` varchar(32) NOT NULL default '',
  `expercash_css` varchar(255) NOT NULL default '',
  `payone_clearingtype` varchar(3) NOT NULL default '',
  `payone_aid` varchar(6) NOT NULL default '',
  `payone_portalid` varchar(7) NOT NULL default '',
  `payone_key` varchar(255) NOT NULL default '',
  `requireCCV` char(1) NOT NULL default '',
  `guests` char(1) NOT NULL default '',
  `protected` char(1) NOT NULL default '',
  `groups` blob NULL,
  `debug` char(1) NOT NULL default '',
  `enabled` char(1) NOT NULL default '',
  PRIMARY KEY  (`id`),
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table `tl_iso_shipping_modules`
--

CREATE TABLE `tl_iso_shipping_modules` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `tstamp` int(10) unsigned NOT NULL default '0',
  `name` varchar(255) NOT NULL default '',
  `type` varchar(64) NOT NULL default '',
  `label` varchar(255) NOT NULL default '',
  `note` text NULL,
  `tax_class` int(10) NOT NULL default '0',
  `countries` blob NULL,
  `subdivisions` longblob NULL,
  `postalCodes` text NULL,
  `minimum_total` decimal(12,2) NOT NULL default '0.00',
  `maximum_total` decimal(12,2) NOT NULL default '0.00',
  `product_types` blob NULL,
  `price` varchar(16) NOT NULL default '',
  `flatCalculation` varchar(10) NOT NULL default '',
  `guests` char(1) NOT NULL default '',
  `protected` char(1) NOT NULL default '',
  `groups` blob NULL,
  `enabled` char(1) NOT NULL default '',
  PRIMARY KEY  (`id`),
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table `tl_iso_tax_class`
--

CREATE TABLE `tl_iso_tax_class` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `tstamp` int(10) unsigned NOT NULL default '0',
  `name` varchar(255) NOT NULL default '',
  `label` varchar(255) NOT NULL default '',
  `fallback` char(1) NOT NULL default '',
  `applyRoundingIncrement` char(1) NOT NULL default '',
  `notNegative` char(1) NOT NULL default '',
  `includes` int(10) unsigned NOT NULL default '0',
  `rates` blob NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table `tl_iso_tax_rate`
--

CREATE TABLE `tl_iso_tax_rate` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `tstamp` int(10) unsigned NOT NULL default '0',
  `name` varchar(255) NOT NULL default '',
  `label` varchar(255) NOT NULL default '',
  `countries` text NULL,
  `subdivisions` text NULL,
  `postalCodes` text NULL,
  `config` int(10) unsigned NOT NULL default '0',
  `rate` varchar(255) NOT NULL default '',
  `address` blob NULL,
  `amount` varchar(255) NOT NULL default '',
  `stop` char(1) NOT NULL default '',
  `guests` char(1) NOT NULL default '',
  `protected` char(1) NOT NULL default '',
  `groups` blob NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table `tl_iso_base_price`
--

CREATE TABLE `tl_iso_baseprice` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `tstamp` int(10) unsigned NOT NULL default '0',
  `name` varchar(255) NOT NULL default '',
  `amount` varchar(32) NOT NULL default '',
  `label` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`id`),
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table `tl_iso_mail`
--

CREATE TABLE `tl_iso_mail` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `tstamp` int(10) unsigned NOT NULL default '0',
  `name` varchar(255) NOT NULL default '',
  `senderName` varchar(255) NOT NULL default '',
  `sender` varchar(255) NOT NULL default '',
  `cc` varchar(255) NOT NULL default '',
  `bcc` varchar(255) NOT NULL default '',
  `template` varchar(255) NOT NULL default '',
  `attachDocument` char(1) NOT NULL default '',
  `documentTemplate` varchar(255) NOT NULL default '',
  `documentTitle` varchar(255) NOT NULL default '',
  `priority` int(1) unsigned NOT NULL default '3',
  PRIMARY KEY  (`id`),
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table `tl_iso_mail_content`
--

CREATE TABLE `tl_iso_mail_content` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `pid` int(10) unsigned NOT NULL default '0',
  `tstamp` int(10) unsigned NOT NULL default '0',
  `language` varchar(5) NOT NULL default '',
  `fallback` char(1) NOT NULL default '',
  `subject` varchar(255) NOT NULL default '',
  `text` text NULL,
  `textOnly` char(1) NOT NULL default '',
  `html` text NULL,
  `attachments` blob NULL,
  PRIMARY KEY  (`id`),
  KEY `pid` (`pid`),
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table `tl_iso_config`
--

CREATE TABLE `tl_iso_config` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `tstamp` int(10) unsigned NOT NULL default '0',
  `name` varchar(255) NOT NULL default '',
  `label` varchar(255) NOT NULL default '',
  `fallback` char(1) NOT NULL default '',
  `store_id` int(2) unsigned NOT NULL default '0',
  `priceCalculateFactor` varchar(10) NOT NULL default '',
  `priceCalculateMode` varchar(3) NOT NULL default '',
  `priceRoundPrecision` int(1) unsigned NOT NULL default '2',
  `priceRoundIncrement` varchar(4) NOT NULL default '',
  `cartMinSubtotal` decimal(12,2) NOT NULL default '0.00',
  `currency` varchar(3) NOT NULL default '',
  `currencyFormat` varchar(20) NOT NULL default '',
  `currencyPosition` varchar(5) NOT NULL default '',
  `currencySymbol` char(1) NOT NULL default '',
  `currencySpace` char(1) NOT NULL default '',
  `currencyAutomator` char(1) NOT NULL default '',
  `currencyOrigin` varchar(3) NOT NULL default '',
  `currencyProvider` varchar(32) NOT NULL default '',
  `orderPrefix` varchar(255) NOT NULL default '',
  `orderDigits` int(1) unsigned NOT NULL default '4',
  `templateGroup` varchar(255) NOT NULL default '',
  `orderstatus_new` int(10) unsigned NOT NULL default '0',
  `orderstatus_error` int(10) unsigned NOT NULL default '0',
  `invoiceLogo` varchar(255) NOT NULL default '',
  `company` varchar(255) NOT NULL default '',
  `vat_no` varchar(255) NOT NULL default '',
  `firstname` varchar(255) NOT NULL default '',
  `lastname` varchar(255) NOT NULL default '',
  `street_1` varchar(255) NOT NULL default '',
  `street_2` varchar(255) NOT NULL default '',
  `street_3` varchar(255) NOT NULL default '',
  `postal` varchar(32) NOT NULL default '',
  `city` varchar(255) NOT NULL default '',
  `subdivision` varchar(10) NOT NULL default '',
  `country` varchar(2) NOT NULL default '',
  `shipping_countries` blob NULL,
  `shipping_fields` blob NULL,
  `shipping_country` varchar(2) NOT NULL default '',
  `billing_countries` blob NULL,
  `billing_fields` blob NULL,
  `billing_country` varchar(2) NOT NULL default '',
  `phone` varchar(64) NOT NULL default '',
  `email` varchar(255) NOT NULL default '',
  `limitMemberCountries` char(1) NOT NULL default '',
  `gallery` varchar(64) NOT NULL default '',
  `missing_image_placeholder` varchar(255) NOT NULL default '',
  `imageSizes` blob NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table `tl_iso_orderstatus`
--

CREATE TABLE `tl_iso_orderstatus` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `pid` int(10) unsigned NOT NULL default '0',
  `tstamp` int(10) unsigned NOT NULL default '0',
  `sorting` int(10) unsigned NOT NULL default '0',
  `name` varchar(255) NOT NULL default '',
  `paid` char(1) NOT NULL default '',
  `welcomescreen` char(1) NOT NULL default '',
  `mail_customer` int(10) unsigned NOT NULL default '0',
  `mail_admin` int(10) unsigned NOT NULL default '0',
  `sales_email` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `pid` (`pid`),
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table `tl_iso_product_collection`
--

CREATE TABLE `tl_iso_product_collection` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `tstamp` int(10) unsigned NOT NULL default '0',
  `type` varchar(32) NOT NULL default '',
  `member` int(10) unsigned NOT NULL default '0',
  `uniqid` varchar(64) NOT NULL default '',
  `source_collection_id` int(10) unsigned NOT NULL default '0',
  `config_id` int(10) unsigned NOT NULL default '0',
  `store_id` int(2) unsigned NOT NULL default '0',
  `settings` blob NULL,
  `date` int(10) unsigned NOT NULL default '0',
  `date_paid` varchar(10) NOT NULL default '',
  `date_shipped` varchar(10) NOT NULL default '',
  `order_status` int(10) unsigned NOT NULL default '0',
  `order_id` varchar(14) NOT NULL default '',
  `payment_id` int(10) unsigned NOT NULL default '0',
  `shipping_id` int(10) unsigned NOT NULL default '0',
  `address1_id` int(10) unsigned NOT NULL default '0',
  `address2_id` int(10) unsigned NOT NULL default '0',
  `language` varchar(5) NOT NULL default '',
  `checkout_info` blob NULL,
  `payment_data` blob NULL,
  `shipping_data` blob NULL,
  `subTotal` decimal(12,2) NOT NULL default '0.00',
  `taxTotal` decimal(12,2) NOT NULL default '0.00',
  `grandTotal` decimal(12,2) NOT NULL default '0.00',
  `currency` varchar(4) NOT NULL default '',
  `notes` text NULL,
  PRIMARY KEY  (`id`),
#  KEY `member` (`member`, `store_id`, `type`),
#  KEY `uniqid` (`uniqid`, `store_id`, `type`),
#  KEY `source_collection_id` (`source_collection_id`, `type`),
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table `tl_iso_product_collection_item`
--

CREATE TABLE `tl_iso_product_collection_item` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `pid` int(10) unsigned NOT NULL default '0',
  `tstamp` int(10) unsigned NOT NULL default '0',
  `type` varchar(32) NOT NULL default '',
  `product_id` int(10) unsigned NOT NULL default '0',
  `sku` varchar(128) NOT NULL default '',
  `name` varchar(255) NOT NULL default '',
  `options` blob NULL,
  `quantity` int(10) unsigned NOT NULL default '0',
  `price` decimal(12,2) NOT NULL default '0.00',
  `tax_free_price` decimal(12,2) NOT NULL default '0.00',
  `tax_id` varchar(32) NOT NULL default '',
  `href_reader` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `pid` (`pid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table `tl_iso_product_collection_surcharge`
--

CREATE TABLE `tl_iso_product_collection_surcharge` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `pid` int(10) unsigned NOT NULL default '0',
  `sorting` int(10) unsigned NOT NULL default '0',
  `tstamp` int(10) unsigned NOT NULL default '0',
  `type` varchar(32) NOT NULL default '',
  `label` varchar(255) NOT NULL default '',
  `price` varchar(32) NOT NULL default '',
  `total_price` decimal(12,2) NOT NULL default '0.00',
  `tax_free_total_price` decimal(12,2) NOT NULL default '0.00',
  `tax_class` int(10) unsigned NOT NULL default '0',
  `tax_id` varchar(32) NOT NULL default '',
  `before_tax` char(1) NOT NULL default '',
  `add` char(1) NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `pid` (`pid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table `tl_iso_product_collection_download`
--

CREATE TABLE `tl_iso_product_collection_download` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `pid` int(10) unsigned NOT NULL default '0',
  `tstamp` int(10) unsigned NOT NULL default '0',
  `download_id` int(10) unsigned NOT NULL default '0',
  `downloads_remaining` varchar(255) NOT NULL default '',
  `expires` varchar(10) NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `pid` (`pid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table `tl_iso_addresses`
--

CREATE TABLE `tl_iso_addresses` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `pid` int(10) unsigned NOT NULL default '0',
  `ptable` varchar(64) NOT NULL default '',
  `tstamp` int(10) unsigned NOT NULL default '0',
  `store_id` int(2) unsigned NOT NULL default '0',
  `label` varchar(255) NOT NULL default '',
  `salutation` varchar(255) NOT NULL default '',
  `company` varchar(255) NOT NULL default '',
  `vat_no` varchar(255) NOT NULL default '',
  `firstname` varchar(255) NOT NULL default '',
  `lastname` varchar(255) NOT NULL default '',
  `street_1` varchar(255) NOT NULL default '',
  `street_2` varchar(255) NOT NULL default '',
  `street_3` varchar(255) NOT NULL default '',
  `postal` varchar(32) NOT NULL default '',
  `city` varchar(255) NOT NULL default '',
  `subdivision` varchar(10) NOT NULL default '',
  `country` varchar(32) NOT NULL default '',
  `phone` varchar(64) NOT NULL default '',
  `email` varchar(255) NOT NULL default '',
  `isDefaultShipping` char(1) NOT NULL default '',
  `isDefaultBilling` char(1) NOT NULL default '',
  PRIMARY KEY  (`id`),
#  KEY `pid` (`pid`, `store_id`),
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table `tl_iso_requestcache`
--

CREATE TABLE `tl_iso_requestcache` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `tstamp` int(10) unsigned NOT NULL default '0',
  `store_id` int(10) unsigned NOT NULL default '0',
  `filters` blob NULL,
  `sorting` blob NULL,
  `limits` blob NULL,
  PRIMARY KEY  (`id`),
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table `tl_iso_productcache`
--

CREATE TABLE `tl_iso_productcache` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `page_id` int(10) unsigned NOT NULL default '0',
  `module_id` int(10) unsigned NOT NULL default '0',
  `requestcache_id` int(10) unsigned NOT NULL default '0',
  `keywords` varchar(255) NOT NULL default '',
  `products` blob NULL,
  `expires` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `page_id` (`page_id`, `module_id`, `requestcache_id`, `keywords`, `expires`),
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table `tl_page`
--

CREATE TABLE `tl_page` (
  `iso_config` int(10) unsigned NOT NULL default '0',
  `iso_setReaderJumpTo` char(1) NOT NULL default '',
  `iso_readerJumpTo` int(10) unsigned NOT NULL default '0',
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table `tl_module`
--

CREATE TABLE `tl_module` (
  `iso_list_layout` varchar(64) NOT NULL default '',
  `iso_reader_layout` varchar(64) NOT NULL default '',
  `iso_reader_jumpTo` int(10) unsigned NOT NULL default '0',
  `iso_checkout_method` varchar(10) NOT NULL default '',
  `iso_login_jumpTo` int(10) unsigned NOT NULL default '0',
  `iso_payment_modules` blob NULL,
  `iso_shipping_modules` blob NULL,
  `filter_module` text NULL,
  `iso_cols` int(1) unsigned NOT NULL default '1',
  `iso_config_id` int(10) unsigned NOT NULL default '0',
  `iso_config_ids` blob NULL,
  `iso_jump_first` char(1) NOT NULL default '',
  `iso_forward_review` char(1) NOT NULL default '',
  `iso_mail_customer` int(10) unsigned NOT NULL default '0',
  `iso_mail_admin` int(10) unsigned NOT NULL default '0',
  `iso_sales_email` varchar(255) NOT NULL default '',
  `iso_order_conditions` int(10) unsigned NOT NULL default '0',
  `iso_order_conditions_position` varchar(6) NOT NULL default 'after',
  `iso_addToAddressbook` char(1) NOT NULL default '',
  `iso_category_scope` varchar(64) NOT NULL default '',
  `iso_list_where` varchar(255) NOT NULL default '',
  `iso_filterModules` blob NULL,
  `iso_use_quantity` char(1) NOT NULL default '',
  `iso_hide_list` char(1) NOT NULL default '',
  `iso_perPage` varchar(64) NOT NULL default '',
  `iso_filterTpl` varchar(64) NOT NULL default '',
  `iso_filterFields` blob NULL,
  `iso_filterHideSingle` char(1) NOT NULL default '',
  `iso_sortingFields` blob NULL,
  `iso_searchFields` blob NULL,
  `iso_searchAutocomplete` varchar(255) NOT NULL default '',
  `iso_enableLimit` char(1) NOT NULL default '',
  `iso_cart_jumpTo` int(10) unsigned NOT NULL default '0',
  `iso_checkout_jumpTo` int(10) unsigned NOT NULL default '0',
  `orderCompleteJumpTo` int(10) unsigned NOT NULL default '0',
  `iso_addProductJumpTo` int(10) unsigned NOT NULL default '0',
  `iso_listingSortField` varchar(255) NOT NULL default '',
  `iso_listingSortDirection` varchar(8) NOT NULL default '',
  `iso_buttons` blob NULL,
  `iso_related_categories` blob NULL,
  `iso_emptyMessage` char(1) NOT NULL default '',
  `iso_noProducts` varchar(255) NOT NULL default '',
  `iso_emptyFilter` char(1) NOT NULL default '',
  `iso_noFilter` varchar(255) NOT NULL default '',
  `iso_includeMessages` char(1) NOT NULL default '',
  `iso_productcache` blob NULL,
  `iso_continueShopping` char(1) NOT NULL default '',
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table `tl_user`
--

CREATE TABLE `tl_user` (
 `iso_modules` blob NULL,
 `iso_product_types` blob NULL,
 `iso_product_typep` blob NULL,
 `iso_payment_modules` blob NULL,
 `iso_payment_modulep` blob NULL,
 `iso_shipping_modules` blob NULL,
 `iso_shipping_modulep` blob NULL,
 `iso_tax_classes` blob NULL,
 `iso_tax_classp` blob NULL,
 `iso_tax_rates` blob NULL,
 `iso_tax_ratep` blob NULL,
 `iso_mails` blob NULL,
 `iso_mailp` blob NULL,
 `iso_configs` blob NULL,
 `iso_configp` blob NULL,
 `iso_groups` blob NULL
 `iso_groupp` blob NULL,
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table `tl_user_group`
--

CREATE TABLE `tl_user_group` (
 `iso_modules` blob NULL,
 `iso_product_types` blob NULL,
 `iso_product_typep` blob NULL,
 `iso_payment_modules` blob NULL,
 `iso_payment_modulep` blob NULL,
 `iso_shipping_modules` blob NULL,
 `iso_shipping_modulep` blob NULL,
 `iso_tax_classes` blob NULL,
 `iso_tax_classp` blob NULL,
 `iso_tax_rates` blob NULL,
 `iso_tax_ratep` blob NULL,
 `iso_mails` blob NULL,
 `iso_mailp` blob NULL,
 `iso_configs` blob NULL,
 `iso_configp` blob NULL,
 `iso_groups` blob NULL
 `iso_groupp` blob NULL,
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
