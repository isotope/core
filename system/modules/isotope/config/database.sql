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
  `iso_show_teaser` char(1) NOT NULL default '',
  `new_products_time_window` int(10) unsigned NOT NULL default '0',
  `filter_module` text NULL,
  `columns` int(10) unsigned NOT NULL default '0',
  `store_id` int(10) unsigned NOT NULL default '0',
  `store_ids` blob NULL,
  `iso_jump_first` char(1) NOT NULL default '',
  `iso_forward_cart` char(1) NOT NULL default '',
  `iso_forward_review` char(1) NOT NULL default '',
  `iso_mail_customer` int(10) unsigned NOT NULL default '0',
  `iso_mail_admin` int(10) unsigned NOT NULL default '0',
  `iso_sales_email` varchar(255) NOT NULL default '',
  `iso_order_conditions` int(10) unsigned NOT NULL default '0',
  `iso_list_format` varchar(64) NOT NULL default '',
  `iso_category_scope` varchar(64) NOT NULL default '',
  `iso_use_quantity` char(1) NOT NULL default '',
  `iso_filter_layout` varchar(64) NOT NULL default '',
  `iso_filterFields` blob NULL,
  `iso_orderByFields` blob NULL,
  `iso_searchFields` blob NULL,
  `iso_enableLimit` char(1) NOT NULL default '',
  `iso_listingModule` int(10) NOT NULL default '0',
  `iso_enableSearch` char(1) NOT NULL default '',
  `iso_disableFilterAjax` char(1) NOT NULL default '',
  `iso_cart_jumpTo` int(10) unsigned NOT NULL default '0',
  `iso_checkout_jumpTo` int(10) unsigned NOT NULL default '0',
  `orderCompleteJumpTo` int(10) unsigned NOT NULL default '0',
  `iso_listingSortField` varchar(255) NOT NULL default '',
  `iso_listingSortDirection` varchar(8) NOT NULL default '',
  `iso_buttons` blob NULL,
  `iso_related_categories` blob NULL,
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


-- --------------------------------------------------------

-- 
-- Table `tl_product_data`
--

CREATE TABLE `tl_product_data` (
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
  `price` decimal(9,3) unsigned NOT NULL default '0.000',
  `price_override` varchar(255) NOT NULL default '',
  `max_order_quantity` int(10) unsigned NOT NULL default '0',
  `stock_enabled` char(1) NOT NULL default '',
  `stock_quantity` int(10) unsigned NOT NULL default '0',
  `stock_oversell` char(1) NOT NULL default '',
  `weight` decimal(9,3) unsigned NOT NULL default '0.000',
  `shipping_exempt` char(1) NOT NULL default '',
  `tax_class` int(10) unsigned NOT NULL default '0',

  `new_import` char(1) NOT NULL default '',
  `option_set_source` varchar(64) NOT NULL default '',
  `option_sets` int(10) unsigned NOT NULL default '0',
  `option_set_title` varchar(255) NOT NULL default '',

  `published` char(1) NOT NULL default '',
  `start` varchar(10) NOT NULL default '',
  `stop` varchar(10) NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `pid` (`pid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


-- --------------------------------------------------------

-- 
-- Table `tl_product_downloads`
--

CREATE TABLE `tl_product_downloads` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `pid` int(10) unsigned NOT NULL default '0',
  `tstamp` int(10) unsigned NOT NULL default '0',
  `singleSRC` varchar(255) NOT NULL default '',
  `title` varchar(255) NOT NULL default '',
  `description` text NULL,
  `downloads_allowed` int(5) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `pid` (`pid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


-- --------------------------------------------------------

-- 
-- Table `tl_product_types`
--

CREATE TABLE `tl_product_types` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `tstamp` int(10) unsigned NOT NULL default '0',
  `name` varchar(255) NOT NULL default '',
  `class` varchar(32) NOT NULL default 'regular',
  `list_template` varchar(255) NOT NULL default '',
  `reader_template` varchar(255) NOT NULL default '',
  `description` text NULL,
  `attributes` blob NULL,
  `variants` char(1) NOT NULL default '',
  `variant_attributes` blob NULL,
  `downloads` char(1) NOT NULL default '',
  `languages` blob NULL,
  PRIMARY KEY  (`id`),
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


-- --------------------------------------------------------

--
-- Table `tl_product_attributes`
--

CREATE TABLE `tl_product_attributes` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `pid` int(10) unsigned NOT NULL default '0',
  `tstamp` int(10) unsigned NOT NULL default '0',
  `name` varchar(255) NOT NULL default '',
  `description` varchar(255) NOT NULL default '',
  `type` varchar(64) NOT NULL default '',
  `legend` varchar(255) NOT NULL default '',
  `option_list` blob NULL,
  `show_files` int(1) NOT NULL default '0',
  `attr_use_mode` varchar(10) NOT NULL default 'fixed',
  `attr_default_value` varchar(255) NOT NULL default '',
  `is_customer_defined` char(1) NOT NULL default '',
  `is_visible_on_front` char(1) NOT NULL default '',
  `is_required` char(1) NOT NULL default '',
  `is_filterable` char(1) NOT NULL default '',
  `is_searchable` char(1) NOT NULL default '',
  `is_order_by_enabled` char(1) NOT NULL default '',
  `is_used_for_price_rules` char(1) NOT NULL default '',
  `is_multiple_select` char(1) NOT NULL default '',
  `multilingual` char(1) NOT NULL default '',
  `add_to_product_variants` char(1) NOT NULL default '',
  `invisible` char(1) NOT NULL default '',
  `inputType` varchar(64) NOT NULL default '',
  `use_rich_text_editor` char(1) NOT NULL default '',
  `attr_datatype` varchar(64) NOT NULL default '',
  `is_user_defined` char(1) NOT NULL default '',
  `is_listing_field` char(1) NOT NULL default '',
  `use_alternate_source` char(1) NOT NULL default '',
  `list_source_table` varchar(255) NOT NULL default '',
  `list_source_field` varchar(255) NOT NULL default '',
  `text_collection_rows` varchar(255) NOT NULL default '',
  `delete_locked` char(1) NOT NULL default '',
  `rgxp` varchar(255) NOT NULL default '',
  `field_name` varchar(30) NOT NULL default '',
  `conditionField` varchar(30) NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `pid` (`pid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

-- 
-- Table `tl_store`
-- 

CREATE TABLE `tl_store` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `pid` int(10) unsigned NOT NULL default '0',
  `sorting` int(10) unsigned NOT NULL default '0',
  `tstamp` int(10) unsigned NOT NULL default '0',
  `name` varchar(255) NOT NULL default '',
  `label` varchar(255) NOT NULL default '',
  `missing_image_placeholder` varchar(255) NOT NULL default '',
  `cookie_duration` int(10) unsigned NOT NULL default '0',
  `priceCalculateFactor` varchar(10) NOT NULL default '',
  `priceCalculateMode` varchar(3) NOT NULL default '',
  `priceRoundPrecision` int(1) unsigned NOT NULL default '2',
  `priceRoundIncrement` varchar(4) NOT NULL default '',
  `currency` varchar(3) NOT NULL default '',
  `currencySymbol` char(1) NOT NULL default '',
  `currencyPosition` varchar(5) NOT NULL default '',
  `currencyFormat` varchar(20) NOT NULL default '',
  `orderPrefix` varchar(4) NOT NULL default '',
  `invoiceLogo` text NULL,
  `isDefaultStore` char(1) NOT NULL default '',
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
  `weightUnit` varchar(10) NOT NULL default '',
  `enableGoogleAnalytics` char(1) NOT NULL default '',
  `thumbnail_size` varchar(64) NOT NULL default '',
  `gallery_size` varchar(64) NOT NULL default '',
  `medium_size` varchar(64) NOT NULL default '',
  `large_size` varchar(64) NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `pid` (`pid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


-- --------------------------------------------------------

--
-- Table `tl_product_categories`
--	

CREATE TABLE `tl_product_categories` (
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
  `isotopeStoreConfig` int(10) unsigned NOT NULL default '0',
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


-- --------------------------------------------------------

-- 
-- Table `tl_content`
-- 

CREATE TABLE `tl_content` (
  `iso_attribute_set` int(10) unsigned NOT NULL default '0',
  `iso_filters` varchar(255) NOT NULL default '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


-- --------------------------------------------------------

--
-- Table structure for table `tl_cart`
-- pid = user_id which is 0 if not tied to registered user
--

CREATE TABLE `tl_cart` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `pid` int(10) unsigned NOT NULL default '0',
  `sorting` int(10) unsigned NOT NULL default '0',
  `tstamp` int(10) unsigned NOT NULL default '0',
  `cart_type_id` int(10) unsigned NOT NULL default '0',
  `session` blob NULL,
  `last_visit` int(10) unsigned NOT NULL default '0',
  `source_cart_id` int(10) unsigned NOT NULL default '0',
  `store_id` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `pid` (`pid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


-- --------------------------------------------------------

--
-- Table structure for table `tl_cart_items`
-- pid = cart id.
--

CREATE TABLE `tl_cart_items` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `pid` int(10) unsigned NOT NULL default '0',
  `sorting` int(10) unsigned NOT NULL default '0',
  `tstamp` int(10) unsigned NOT NULL default '0',
  `product_id` int(10) unsigned NOT NULL default '0',
  `quantity_requested` int(10) unsigned NOT NULL default '0',
  `quantity_sold` int(10) unsigned NOT NULL default '0',
  `price` double NULL default NULL,
  `price_override` varchar(255) NOT NULL default '',
  `product_attribute_collection` text NULL,
  `product_data` blob NULL,
  `href_reader` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `pid` (`pid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


-- tl_cart_rules: (we will do this later, but here's what I had) id, moduleID, customerGroup, storeID

-- --------------------------------------------------------

-- 
-- Table `tl_address_book`
-- 

CREATE TABLE `tl_address_book` (
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
  `subdivision` varchar(64) NOT NULL default '',
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
-- Table `tl_payment_modules`
-- 

CREATE TABLE `tl_payment_modules` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `tstamp` int(10) unsigned NOT NULL default '0',
  `name` varchar(255) NOT NULL default '',
  `type` varchar(64) NOT NULL default '',
  `label` varchar(255) NOT NULL default '',
  `note` text NULL,
  `price` float NOT NULL default '0',
  `tax_class` int(10) unsigned NOT NULL default '0',
  `countries` blob NULL,
  `shipping_modules` blob NULL,
  `product_types` blob NULL,
  `allowed_cc_types` text NULL,  
  `minimum_total` varchar(255) NOT NULL default '',
  `maximum_total` varchar(255) NOT NULL default '',
  `new_order_status` varchar(255) NOT NULL default '',
  `postsale_mail` int(10) unsigned NOT NULL default '0',
  `paypal_account` varchar(255) NOT NULL default '',
  `paypal_business` varchar(255) NOT NULL default '',
  `paypalpro_apiUserName` varchar(255) NOT NULL default '',
  `paypalpro_apiPassword` varchar(255) NOT NULL default '',
  `paypalpro_apiSignature` varchar(255) NOT NULL default '',
  `paypalpro_transType` varchar(64) NOT NULL default '',
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
  `requireCCV` char(1) NOT NULL default '',
  `groups` blob NULL,
  `button` varchar(255) NOT NULL default '',
  `debug` char(1) NOT NULL default '',
  `enabled` char(1) NOT NULL default '',
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
-- Table `tl_shipping_modules`
-- 

CREATE TABLE `tl_shipping_modules` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `tstamp` int(10) unsigned NOT NULL default '0',
  `name` varchar(255) NOT NULL default '',
  `type` varchar(64) NOT NULL default '',
  `label` varchar(255) NOT NULL default '',
  `note` text NULL,
  `tax_class` int(10) unsigned NOT NULL default '0',
  `countries` blob NULL,
  `subdivisions` blob NULL,
  `minimum_total` int(10) NOT NULL default '0',
  `maximum_total` int(10) NOT NULL default '0',
  `price` float NOT NULL default '0',
  `surcharge_field` varchar(255) NOT NULL default '',
  `flatCalculation` varchar(10) NOT NULL default '',
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
  PRIMARY KEY  (`id`),
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


-- --------------------------------------------------------

-- 
-- Table `tl_shipping_options`
-- 

CREATE TABLE `tl_shipping_options` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `pid` int(10) unsigned NOT NULL default '0',
  `tstamp` int(10) unsigned NOT NULL default '0',
  `name` varchar(255) NOT NULL default '',
  `description` text NULL,
  `option_type` varchar(255) NOT NULL default '',
  `override` char(1) NOT NULL default '',
  `override_rule` int(10) unsigned NOT NULL default '0',
  `override_message` varchar(255) NOT NULL default '',
  `limit_type` varchar(255) NOT NULL default '',
  `limit_value` double NULL default NULL,
  `rate` double NULL default NULL,
  `groups` blob NULL,
  `mandatory` char(1) NOT NULL default '',
  `dest_postalcodes` text NULL,
  `dest_countries` blob NULL,
  `dest_regions` blob NULL,
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
  `sorting` int(10) unsigned NOT NULL default '0',
  `tstamp` int(10) unsigned NOT NULL default '0',
  `date` int(10) unsigned NOT NULL default '0',  
  `order_id` varchar(14) NOT NULL default '',
  `uniqid` varchar(27) NOT NULL default '',
  
  `store_id` int(10) unsigned NOT NULL default '0',
  `cart_id` int(10) unsigned NOT NULL default '0',
  `source_cart_id` int(10) unsigned NOT NULL default '0',
  `language` varchar(2) NOT NULL default '',
  `status` varchar(32) NOT NULL default '',
  `shipping_address` blob NULL,
  `billing_address` blob NULL,
  `checkout_info` blob NULL,
  `surcharges` blob NULL,  
  `payment_data` blob NULL,
  `shipping_data` blob NULL,
  `subTotal` double NULL default NULL,
  `taxTotal` double NULL default NULL,
  `shippingTotal` double NULL default NULL,
  `grandTotal` double NULL default NULL,
  
  `cc_num` varchar(64) NOT NULL default '',
  `cc_type` varchar(32) NOT NULL default '',
  `cc_exp` varchar(16) NOT NULL default '',
  `cc_cvv` varchar(8) NOT NULL default '',
  `authnet_response` varchar(255) NOT NULL default '',
  `authnet_reason` text NULL,
  `order_comments` text NULL, 
  `gift_message` text NULL,
  `gift_wrap` char(1) NOT NULL default '', 
  `currency` varchar(4) NOT NULL default '',
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
  `quantity_sold` int(10) unsigned NOT NULL default '0',
  `price` double NULL default NULL,
  `product_data` blob NULL,
  `product_status` varchar(255) NOT NULL default '',
  `status` varchar(255) NOT NULL default '',
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
-- Table `tl_tax_class`
--

CREATE TABLE `tl_tax_class` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `tstamp` int(10) unsigned NOT NULL default '0',
  `name` varchar(255) NOT NULL default '',
  `label` varchar(255) NOT NULL default '',
  `includes` int(10) unsigned NOT NULL default '0',
  `rates` blob NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


-- --------------------------------------------------------

-- 
-- Table `tl_tax_rate`
--

CREATE TABLE `tl_tax_rate` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `tstamp` int(10) unsigned NOT NULL default '0',
  `name` varchar(255) NOT NULL default '',
  `label` varchar(255) NOT NULL default '',
  `country` varchar(2) NOT NULL default '',
  `subdivision` varchar(10) NOT NULL default '',
  `postal` varchar(255) NOT NULL default '',
  `store` int(10) unsigned NOT NULL default '0',
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
 `iso_stores` blob NULL,
 `iso_modules` blob NULL,
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


-- --------------------------------------------------------

-- 
-- Table `tl_user_group`
--

CREATE TABLE `tl_user_group` (
 `iso_product_types` blob NULL,
 `iso_stores` blob NULL,
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
  `fallback` varchar(1) NOT NULL default '',
  `subject` varchar(255) NOT NULL default '',
  `text` text NULL,
  `textOnly` varchar(1) NOT NULL default '',
  `html` text NULL,
  `attachments` blob NULL,
  PRIMARY KEY  (`id`),
  KEY `pid` (`pid`),
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


-- --------------------------------------------------------

-- 
-- Table `tl_related_categories`
-- 

CREATE TABLE `tl_related_categories` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `tstamp` int(10) unsigned NOT NULL default '0',
  `name` varchar(255) NOT NULL default '',
  `jumpTo` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


-- --------------------------------------------------------

-- 
-- Table `tl_related_products`
-- 

CREATE TABLE `tl_related_products` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `pid` int(10) unsigned NOT NULL default '0',
  `sorting` int(10) unsigned NOT NULL default '0',
  `tstamp` int(10) unsigned NOT NULL default '0',
  `category` int(10) unsigned NOT NULL default '0',
  `products` blob NULL,
  PRIMARY KEY  (`id`),
  KEY `pid` (`pid`),
) ENGINE=MyISAM DEFAULT CHARSET=utf8;



-- PRODUCT ATTRIBUTES START --
-- PRODUCT ATTRIBUTES STOP --

