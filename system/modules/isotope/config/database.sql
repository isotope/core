-- **********************************************************
-- *                                                        *
-- * IMPORTANT NOTE                                         *
-- *                                                        *
-- * Do not import this file manually but use the TYPOlight *
-- * install tool to create and maintain database tables!   *
-- *                                                        *
-- **********************************************************


-- 
-- Table `tl_module`
-- 

CREATE TABLE `tl_module` (
  `iso_list_layout` varchar(64) NOT NULL default '',
  `iso_reader_layout` varchar(64) NOT NULL default '',
  `iso_reader_jumpTo` int(10) unsigned NOT NULL default '0',
  `iso_cart_layout` varchar(64) NOT NULL default '',
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
  `iso_addToAddressbook` char(1) NOT NULL default '',
  `iso_category_scope` varchar(64) NOT NULL default '',
  `iso_use_quantity` char(1) NOT NULL default '',
  `iso_filter_layout` varchar(64) NOT NULL default '',
  `iso_filterFields` blob NULL,
  `iso_orderByFields` blob NULL,
  `iso_searchFields` blob NULL,
  `iso_enableLimit` char(1) NOT NULL default '',
  `iso_listingModule` int(10) NOT NULL default '0',
  `iso_enableSearch` char(1) NOT NULL default '',
  `iso_cart_jumpTo` int(10) unsigned NOT NULL default '0',
  `iso_checkout_jumpTo` int(10) unsigned NOT NULL default '0',
  `orderCompleteJumpTo` int(10) unsigned NOT NULL default '0',
  `iso_addProductJumpTo` int(10) unsigned NOT NULL default '0',
  `iso_listingSortField` varchar(255) NOT NULL default '',
  `iso_listingSortDirection` varchar(8) NOT NULL default '',
  `iso_buttons` blob NULL,
  `iso_related_categories` blob NULL,
  `iso_noProducts` varchar(255) NOT NULL default '',
  `iso_forceNoProducts` char(1) NOT NULL default '',
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


-- --------------------------------------------------------

-- 
-- Table `tl_iso_products`
--

CREATE TABLE `tl_iso_products` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `pid` int(10) unsigned NOT NULL default '0',
  `tstamp` int(10) unsigned NOT NULL default '0',
  `type` int(10) unsigned NOT NULL default '0',
  `language` varchar(2) NOT NULL default '',
  `pages` blob NULL,
  `inherit` blob NULL,
  `alias` varchar(128) NOT NULL default '',
  `sku` varchar(128) NOT NULL default '',
  `name` varchar(255) NOT NULL default '',
  `images` blob NULL,
  `teaser` text NULL,
  `description` text NULL,
  `keywords_meta` text NULL,
  `description_meta` text NULL,
  `price` decimal(12,2) NOT NULL default '0.00',
  `shipping_weight` varchar(255) NOT NULL default '',
  `shipping_exempt` char(1) NOT NULL default '',
  `tax_class` int(10) unsigned NOT NULL default '0',
  `archive` int(1) unsigned NOT NULL default '0',
  `published` char(1) NOT NULL default '',
  `start` varchar(10) NOT NULL default '',
  `stop` varchar(10) NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `pid` (`pid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


-- --------------------------------------------------------

-- 
-- Table `tl_iso_downloads`
--

CREATE TABLE `tl_iso_downloads` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `pid` int(10) unsigned NOT NULL default '0',
  `tstamp` int(10) unsigned NOT NULL default '0',
  `singleSRC` varchar(255) NOT NULL default '',
  `title` varchar(255) NOT NULL default '',
  `description` text NULL,
  `downloads_allowed` int(5) unsigned NOT NULL default '0',
  `archive` int(1) unsigned NOT NULL default '0',
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
  `class` varchar(32) NOT NULL default 'regular',
  `fallback` char(1) NOT NULL default '',
  `list_template` varchar(255) NOT NULL default '',
  `reader_template` varchar(255) NOT NULL default '',
  `description` text NULL,
  `attributes` blob NULL,
  `variants` char(1) NOT NULL default '',
  `variant_attributes` blob NULL,
  `downloads` char(1) NOT NULL default '',
  `languages` blob NULL,
  `archive` int(1) unsigned NOT NULL default '0',
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
  `is_customer_defined` char(1) NOT NULL default '',
  `mandatory` char(1) NOT NULL default '',
  `is_filterable` char(1) NOT NULL default '',
  `is_searchable` char(1) NOT NULL default '',
  `is_order_by_enabled` char(1) NOT NULL default '',
  `multiple` char(1) NOT NULL default '',
  `size` smallint(5) unsigned NOT NULL default '0',
  `extensions` varchar(255) NOT NULL default '',
  `is_be_filterable` char(1) NOT NULL default '',
  `is_be_searchable` char(1) NOT NULL default '',
  `multilingual` char(1) NOT NULL default '',
  `variant_option` char(1) NOT NULL default '',
  `invisible` char(1) NOT NULL default '',
  `foreignKey` varchar(64) NOT NULL default '',
  `rte` varchar(255) NOT NULL default '',
  `maxlength` int(10) unsigned NOT NULL default '0',
  `rgxp` varchar(255) NOT NULL default '',
  `conditionField` varchar(30) NOT NULL default '',
  `gallery` varchar(64) NOT NULL default '',
  PRIMARY KEY  (`id`)
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
  `priceCalculateFactor` varchar(10) NOT NULL default '',
  `priceCalculateMode` varchar(3) NOT NULL default '',
  `priceRoundPrecision` int(1) unsigned NOT NULL default '2',
  `priceRoundIncrement` varchar(4) NOT NULL default '',
  `currency` varchar(3) NOT NULL default '',
  `currencySymbol` char(1) NOT NULL default '',
  `currencyPosition` varchar(5) NOT NULL default '',
  `currencyFormat` varchar(20) NOT NULL default '',
  `orderPrefix` varchar(4) NOT NULL default '',
  `store_id` int(2) unsigned NOT NULL default '0',
  `invoiceLogo` text NULL,
  `fallback` char(1) NOT NULL default '',
  `company` varchar(255) NOT NULL default '',
  `firstname` varchar(255) NOT NULL default '',
  `lastname` varchar(255) NOT NULL default '',
  `street_1` varchar(255) NOT NULL default '',
  `street_2` varchar(255) NOT NULL default '',
  `street_3` varchar(255) NOT NULL default '',
  `postal` varchar(32) NOT NULL default '',
  `city` varchar(255) NOT NULL default '',
  `subdivision` varchar(10) NOT NULL default '',
  `country` varchar(32) NOT NULL default '',
  `shipping_countries` blob NULL,
  `shipping_fields` blob NULL,
  `billing_countries` blob NULL,
  `billing_fields` blob NULL,
  `phone` varchar(64) NOT NULL default '',
  `emailShipping` varchar(255) NOT NULL default '',
  `enableGoogleAnalytics` char(1) NOT NULL default '',
  `gallery` varchar(64) NOT NULL default '',
  `missing_image_placeholder` varchar(255) NOT NULL default '',
  `imageSizes` blob NULL,
  `archive` int(1) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`)
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
-- Table `tl_filter_values_to_categories` pid is page id.
-- 
CREATE TABLE `tl_filter_values_to_categories` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `pid` int(10) unsigned NOT NULL default '0',
  `attribute_id` int(10) unsigned NOT NULL default '0',
  `sorting` int(10) unsigned NOT NULL default '0',
  `tstamp` int(10) unsigned NOT NULL default '0',
  `value_collection` text NULL,
  PRIMARY KEY  (`id`),
  KEY `pid` (`pid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


-- --------------------------------------------------------

-- 
-- Table `tl_page`
-- 

CREATE TABLE `tl_page` (
  `iso_config` int(10) unsigned NOT NULL default '0',
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


-- --------------------------------------------------------

-- 
-- Table `tl_content`
-- 

CREATE TABLE `tl_content` (
  `iso_reader_jumpTo` int(10) unsigned NOT NULL default '0',
  `iso_list_layout` varchar(64) NOT NULL default '',
  `iso_attribute_set` int(10) unsigned NOT NULL default '0',
  `iso_filters` varchar(255) NOT NULL default '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


-- --------------------------------------------------------

--
-- Table `tl_iso_cart`
--

CREATE TABLE `tl_iso_cart` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `pid` int(10) unsigned NOT NULL default '0',
  `tstamp` int(10) unsigned NOT NULL default '0',
  `session` varchar(64) NOT NULL default '',
  `store_id` int(2) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `pid` (`pid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


-- --------------------------------------------------------

--
-- Table `tl_iso_cart_items`
--

CREATE TABLE `tl_iso_cart_items` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `pid` int(10) unsigned NOT NULL default '0',
  `tstamp` int(10) unsigned NOT NULL default '0',
  `product_id` int(10) unsigned NOT NULL default '0',
  `product_sku` varchar(128) NOT NULL default '',
  `product_name` varchar(255) NOT NULL default '',
  `product_options` blob NULL,
  `product_quantity` int(10) unsigned NOT NULL default '0',
  `price` decimal(12,2) NOT NULL default '0.00',
  `href_reader` varchar(255) NOT NULL default '',
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
  `tstamp` int(10) unsigned NOT NULL default '0',
  `company` varchar(255) NOT NULL default '',
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
  KEY `pid` (`pid`)
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
  `tax_class` int(10) unsigned NOT NULL default '0',
  `countries` blob NULL,
  `shipping_modules` blob NULL,
  `product_types` blob NULL,
  `allowed_cc_types` text NULL,  
  `minimum_total` decimal(12,2) NOT NULL default '0.00',
  `maximum_total` decimal(12,2) NOT NULL default '0.00',
  `new_order_status` varchar(255) NOT NULL default '',
  `postsale_mail` int(10) unsigned NOT NULL default '0',
  `trans_type` varchar(8) NOT NULL default '',
  `paypal_account` varchar(255) NOT NULL default '',
  `payflowpro_user` varchar(255) NOT NULL default '',
  `payflowpro_vendor` varchar(255) NOT NULL default '',
  `payflowpro_password` varchar(255) NOT NULL default '',
  `payflowpro_transType` varchar(255) NOT NULL default '',
  `payflowpro_partner` varchar(255) NOT NULL default '',
  `postfinance_pspid` varchar(255) NOT NULL default '',
  `postfinance_secret` varchar(255) NOT NULL default '',
  `postfinance_method` varchar(4) NOT NULL default '',
  `authorize_login` varchar(255) NOT NULL default '',
  `authorize_trans_key` varchar(255) NOT NULL default '',
  `authorize_delimiter` varchar(4) NOT NULL default '',
  `authorize_trans_type` varchar(32) NOT NULL default '',
  `authorize_relay_response` char(1) NOT NULL default '',
  `authorize_email_customer` char(1) NOT NULL default '',
  `cybersource_merchant_id` varchar(255) NOT NULL default '',
  `cybersource_trans_key` text NULL,
  `cybersource_trans_type` varchar(32) NOT NULL default '',
  `epay_merchantnumber` varchar(7) NOT NULL default '',
  `epay_secretkey` varchar(255) NOT NULL default '',
  `requireCCV` char(1) NOT NULL default '',
  `button` varchar(255) NOT NULL default '',
  `guests` char(1) NOT NULL default '',
  `protected` char(1) NOT NULL default '',
  `groups` blob NULL,
  `debug` char(1) NOT NULL default '',
  `enabled` char(1) NOT NULL default '',
  `archive` int(1) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


-- --------------------------------------------------------

-- 
-- Table `tl_payment_options`
-- 

CREATE TABLE `tl_payment_options` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `pid` int(10) unsigned NOT NULL default '0',
  `tstamp` int(10) unsigned NOT NULL default '0',
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
  `tax_class` int(10) unsigned NOT NULL default '0',
  `countries` blob NULL,
  `subdivisions` longblob NULL,
  `minimum_total` decimal(12,2) NOT NULL default '0.00',
  `maximum_total` decimal(12,2) NOT NULL default '0.00',
  `product_types` blob NULL,
  `price` varchar(16) NOT NULL default '',
  `surcharge_field` varchar(255) NOT NULL default '',
  `flatCalculation` varchar(10) NOT NULL default '',
  `weight_unit` varchar(5) NOT NULL default '',
  `guests` char(1) NOT NULL default '',
  `protected` char(1) NOT NULL default '',
  `groups` blob NULL,
  `enabled` char(1) NOT NULL default '',
  `ups_accessKey` varchar(255) NOT NULL default '',
  `ups_developersKey` varchar(255) NOT NULL default '',
  `ups_userName` varchar(255) NOT NULL default '',
  `ups_password` varchar(255) NOT NULL default '',
  `ups_enabledService` varchar(255) NOT NULL default '',
  `usps_userName` varchar(255) NOT NULL default '',
  `usps_enabledService` varchar(255) NOT NULL default '',
  `archive` int(1) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


-- --------------------------------------------------------

-- 
-- Table `tl_iso_shipping_options`
-- 

CREATE TABLE `tl_iso_shipping_options` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `pid` int(10) unsigned NOT NULL default '0',
  `tstamp` int(10) unsigned NOT NULL default '0',
  `name` varchar(255) NOT NULL default '',
  `description` text NULL,
  `rate` decimal(12,2) NOT NULL default '0.00',
  `minimum_total` decimal(12,2) NOT NULL default '0.00',
  `maximum_total` decimal(12,2) NOT NULL default '0.00',
  `weight_from` varchar(32) NOT NULL default '0',
  `weight_to` varchar(32) NOT NULL default '0',
  `enabled` char(1) NOT NULL default '',
  PRIMARY KEY  (`id`),
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


-- --------------------------------------------------------

-- 
-- Table `tl_iso_orders`
-- pid == member ID
--

CREATE TABLE `tl_iso_orders` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `pid` int(10) unsigned NOT NULL default '0',
  `tstamp` int(10) unsigned NOT NULL default '0',
  `date` int(10) unsigned NOT NULL default '0',
  `date_payed` varchar(10) NOT NULL default '',
  `date_shipped` varchar(10) NOT NULL default '',
  `status` varchar(32) NOT NULL default '',
  
  `order_id` varchar(14) NOT NULL default '',
  `uniqid` varchar(27) NOT NULL default '',
  
  `config_id` int(10) unsigned NOT NULL default '0',
  `cart_id` int(10) unsigned NOT NULL default '0',
  `payment_id` int(10) unsigned NOT NULL default '0',
  `shipping_id` int(10) unsigned NOT NULL default '0',
  `language` varchar(2) NOT NULL default '',
  `shipping_address` blob NULL,
  `billing_address` blob NULL,
  `checkout_info` blob NULL,
  `surcharges` blob NULL,  
  `coupons` blob NULL,
  `payment_data` blob NULL,
  `shipping_data` blob NULL,
  `subTotal` decimal(12,2) NOT NULL default '0.00',
  `taxTotal` decimal(12,2) NOT NULL default '0.00',
  `shippingTotal` decimal(12,2) NOT NULL default '0.00',
  `grandTotal` decimal(12,2) NOT NULL default '0.00',
  `cc_num` varchar(64) NOT NULL default '',
  `cc_type` varchar(32) NOT NULL default '',
  `cc_exp` varchar(16) NOT NULL default '',
  `cc_cvv` varchar(8) NOT NULL default '',
  `transaction_response` varchar(255) NOT NULL default '',
  `transaction_response_code` varchar(255) NOT NULL default '',
  `order_comments` text NULL, 
  `gift_message` text NULL,
  `gift_wrap` char(1) NOT NULL default '', 
  `currency` varchar(4) NOT NULL default '',
  `notes` text NULL,
  PRIMARY KEY  (`id`),
  KEY `pid` (`pid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


-- --------------------------------------------------------

--
-- Table `tl_iso_order_items`
--

CREATE TABLE `tl_iso_order_items` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `pid` int(10) unsigned NOT NULL default '0',
  `tstamp` int(10) unsigned NOT NULL default '0',
  `product_id` int(10) unsigned NOT NULL default '0',
  `product_sku` varchar(128) NOT NULL default '',
  `product_name` varchar(255) NOT NULL default '',
  `product_options` blob NULL,
  `product_quantity` int(10) unsigned NOT NULL default '0',
  `price` decimal(12,2) NOT NULL default '0.00',
  PRIMARY KEY  (`id`),
  KEY `pid` (`pid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


-- --------------------------------------------------------

--
-- Table `tl_iso_order_downloads`
--

CREATE TABLE `tl_iso_order_downloads` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `pid` int(10) unsigned NOT NULL default '0',
  `tstamp` int(10) unsigned NOT NULL default '0',
  `download_id` int(10) unsigned NOT NULL default '0',
  `downloads_remaining` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `pid` (`pid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


-- --------------------------------------------------------

-- 
-- Table `tl_iso_tax_class`
--

CREATE TABLE `tl_iso_tax_class` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `tstamp` int(10) unsigned NOT NULL default '0',
  `name` varchar(255) NOT NULL default '',
  `fallback` char(1) NOT NULL default '',
  `label` varchar(255) NOT NULL default '',
  `includes` int(10) unsigned NOT NULL default '0',
  `rates` blob NULL,
  `archive` int(1) unsigned NOT NULL default '0',
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
  `country` varchar(2) NOT NULL default '',
  `subdivision` varchar(10) NOT NULL default '',
  `postal` varchar(255) NOT NULL default '',
  `config` int(10) unsigned NOT NULL default '0',
  `rate` varchar(255) NOT NULL default '',
  `address` blob NULL,
  `amount` varchar(255) NOT NULL default '',
  `compound` char(1) NOT NULL default '',
  `stop` char(1) NOT NULL default '',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


-- --------------------------------------------------------

-- 
-- Table `tl_user`
--

CREATE TABLE `tl_user` (
 `iso_product_types` blob NULL,
 `iso_configs` blob NULL,
 `iso_modules` blob NULL,
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


-- --------------------------------------------------------

-- 
-- Table `tl_user_group`
--

CREATE TABLE `tl_user_group` (
 `iso_product_types` blob NULL,
 `iso_configs` blob NULL,
 `iso_modules` blob NULL,
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
  `originateFromCustomerEmail` char(1) NOT NULL default '',
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
  `language` varchar(255) NOT NULL default '',
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


-- PRODUCT ATTRIBUTES START --
-- PRODUCT ATTRIBUTES STOP --

