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
  `iso_cart_layout` varchar(64) NOT NULL default '',
  `iso_registry_layout` varchar(64) NOT NULL default '',
  `iso_registry_results` varchar(64) NOT NULL default '',
  `iso_registry_reader` varchar(64) NOT NULL default '',
  `iso_checkout_layout` varchar(64) NOT NULL default '',
  `iso_checkout_method` varchar(10) NOT NULL default '',
  `iso_payment_modules` text NULL,
  `iso_shipping_modules` text NULL,
  `new_products_time_window` int(10) unsigned NOT NULL default '0',
  `listing_filters` text NULL,
  `columns` int(10) unsigned NOT NULL default '0',
  `store_id` int(10) unsigned NOT NULL default '0',
  `orderCompleteJumpTo` int(10) unsigned NOT NULL default '0',
  `addressBookTemplate` varchar(64) NOT NULL default '',
  `iso_jump_first` char(1) NOT NULL default '',
  `iso_forward_cart` char(1) NOT NULL default '',
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


-- --------------------------------------------------------

-- 
-- Table `tl_product_attribute_types
--

CREATE TABLE `tl_product_attribute_types` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `pid` int(10) unsigned NOT NULL default '0',
  `sorting` int(10) unsigned NOT NULL default '0',
  `tstamp` int(10) unsigned NOT NULL default '0',
  `type` varchar(255) NOT NULL default '',
  `attr_datatype` varchar(255) NOT NULL default '',
  `inputType` varchar(64) NOT NULL default '',
  `eval` text NULL,
  `name` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `pid` (`pid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


-- INSERT INTO `tl_product_attribute_types` (`id`, `pid`, `sorting`, `tstamp`, `type`, `attr_datatype`, `inputType`, `eval`, `name`) VALUES
-- (1, 0, 128, 1218221789, 'text', 'varchar', 'text', '', ''),
-- (2, 0, 256, 1218221789, 'integer', 'int', 'text', '', ''),
-- (3, 0, 384, 1218221789, 'decimal', 'decimal', 'text', '', ''),
-- (4, 0, 512, 1218221789, 'longtext', 'text', 'textarea', '', ''),
-- (5, 0, 640, 1218221789, 'datetime', 'datetime', 'text', '', ''),
-- (6, 0, 768, 1218221789, 'select', 'options', 'select', '', ''),
-- (7, 0, 896, 1218221789, 'checkbox', 'options', 'checkbox', '', ''),
-- (8, 0, 1024, 1218221789, 'options', 'options', 'radio', '', ''),
-- (9, 0, 1152, 1218221789, 'file', 'varchar', 'fileTree', '', ''),
-- (10, 0, 1280, 1218221789, 'media', 'varchar', 'imageManager', '', ''),
-- (11, 0, 150, 1218221789, 'shorttext', 'varchar', 'text', '', '');


-- --------------------------------------------------------

-- 
-- Table `tl_product_attribute_sets`
-- 

CREATE TABLE `tl_product_attribute_sets` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `tstamp` int(10) unsigned NOT NULL default '0',
  `name` varchar(255) NOT NULL default '',
  `storeTable` varchar(64) NOT NULL default '',
  `store_id` int(10) unsigned NOT NULL default '0',
  `noTable` char(1) NOT NULL default '',
  `dca` text NULL,
  `format` text NULL,
  `addImage` char(1) NOT NULL default '',
  `singleSRC` varchar(255) NOT NULL default '',
  `size` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


-- --------------------------------------------------------

--
-- Table `tl_product_attributes`
--

CREATE TABLE `tl_product_attributes` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `pid` int(10) unsigned NOT NULL default '0',
  `sorting` int(10) unsigned NOT NULL default '0',
  `tstamp` int(10) unsigned NOT NULL default '0',
  `attr_type_id` int(10) unsigned NOT NULL default '0',
  `name` varchar(255) NOT NULL default '',
  `description` varchar(255) NOT NULL default '',
  `type` varchar(64) NOT NULL default '',
  `option_list` text NULL,
  `show_files` int(1) NOT NULL default '0',
  `attr_use_mode` varchar(10) NOT NULL default 'fixed',
  `attr_default_value` varchar(255) NOT NULL default '',
  `is_customer_defined` int(1) NOT NULL default '0',
  `is_visible_on_front` int(1) NOT NULL default '0',
  `is_hidden_on_backend` int(1) NOT NULL default '0',
  `is_required` int(1) NOT NULL default '0',
  `is_filterable` int(1) NOT NULL default '0',
  `is_searchable` int(1) NOT NULL default '0',
  `is_order_by_enabled` int(1) NOT NULL default '0',
  `is_used_for_price_rules` int(1) NOT NULL default '0',
  `is_multiple_select` int(1) NOT NULL default '0',
  `invisible` char(1) NOT NULL default '',
  `inputType` varchar(64) NOT NULL default '',
  `use_rich_text_editor` char(1) NOT NULL default '',
  `attr_datatype` varchar(64) NOT NULL default '',
  `is_user_defined` int(1) NOT NULL default '0',
  `is_listing_field` char(1) NOT NULL default '0',
  `use_alternate_source` char(1) NOT NULL default '0',
  `list_source_table` varchar(255) NOT NULL default '',
  `list_source_field` varchar(255) NOT NULL default '',
  `delete_locked` char(1) NOT NULL default '',
  `rgxp` varchar(255) NOT NULL default '',
  `load_callback` text NULL,
  `save_callback` text NULL,
  `field_name` varchar(255) NOT NULL default '',
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
  `store_configuration_name` varchar(255) NOT NULL default '',
  `productReaderJumpTo` int(10) unsigned NOT NULL default '0',
  `cartJumpTo` int(10) unsigned NOT NULL default '0',
  `checkoutJumpTo` int(10) unsigned NOT NULL default '0',
  `missing_image_placeholder` varchar(255) NOT NULL default '',
  `thumbnail_image_width` int(10) unsigned NOT NULL default '0',
  `thumbnail_image_height` int(10) unsigned NOT NULL default '0',
  `medium_image_width` int(10) unsigned NOT NULL default '0',
  `medium_image_height` int(10) unsigned NOT NULL default '0',
  `large_image_width` int(10) unsigned NOT NULL default '0',
  `large_image_height` int(10) unsigned NOT NULL default '0',
  `gallery_thumbnail_image_width` int(10) unsigned NOT NULL default '0',
  `gallery_thumbnail_image_height` int(10) unsigned NOT NULL default '0', 
  `cookie_duration` int(10) unsigned NOT NULL default '0',
  `enabled_modules` blob NULL,
  `root_asset_import_path` varchar(255) NOT NULL default '',
  `checkout_login_module` int(10) unsigned NOT NULL default '0',
  `currency` varchar(3) NOT NULL default '',
  `currencySymbol` char(1) NOT NULL default '',
  `currencyPosition` varchar(5) NOT NULL default '',
  `currencyFormat` varchar(20) NOT NULL default '',
  `countries` blob NULL,
  `address_fields` blob NULL,
   PRIMARY KEY  (`id`),
  KEY `pid` (`pid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


-- --------------------------------------------------------

--
-- Table `tl_cap_aggregate`
--

CREATE TABLE `tl_cap_aggregate` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `pid` int(10) unsigned NOT NULL default '0',
  `sorting` int(10) unsigned NOT NULL default '0',
  `tstamp` int(10) unsigned NOT NULL default '0',
  `storeTable` varchar(64) NOT NULL default '',
  `product_id` int(10) unsigned NOT NULL default '0',
  `attribute_set_id` int(10) NOT NULL default '0',
  `store_id` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `pid` (`pid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


-- --------------------------------------------------------

--
-- Table `tl_pfc_aggregate`
--

CREATE TABLE `tl_pfc_aggregate` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `pid` int(10) unsigned NOT NULL default '0',
  `attribute_id` int(10) unsigned NOT NULL default '0',
  `sorting` int(10) unsigned NOT NULL default '0',
  `tstamp` int(10) unsigned NOT NULL default '0',
  `value_collection` text NULL,
  `attribute_set_id` int(10) NOT NULL default '0',
  `store_id` int(10) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `pid` (`pid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


-- --------------------------------------------------------

-- 
-- Table `tl_page`
-- 

CREATE TABLE `tl_page` (
  `show_child_category_products` char(1) NOT NULL default '0'
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
  `attribute_set_id` int(10) unsigned NOT NULL default '0',
  `quantity_requested` int(10) unsigned NOT NULL default '0',
  `quantity_sold` int(10) unsigned NOT NULL default '0',
  `product_attribute_collection` blob NULL,
  PRIMARY KEY  (`id`),
  KEY `pid` (`pid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


-- --------------------------------------------------------

--
-- Table `tl_cart_types`
--

CREATE TABLE `tl_cart_types` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `pid` int(10) unsigned NOT NULL default '0',
  `sorting` int(10) unsigned NOT NULL default '0',
  `tstamp` int(10) unsigned NOT NULL default '0',
  `name` varchar(64) NOT NULL default '',
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
  `sorting` int(10) unsigned NOT NULL default '0',
  `firstname` varchar(255) NOT NULL default '',
  `lastname` varchar(255) NOT NULL default '',
  `street` varchar(255) NOT NULL default '',
  `street_2` varchar(255) NOT NULL default '',
  `street_3` varchar(255) NOT NULL default '',
  `postal` varchar(32) NOT NULL default '',
  `city` varchar(255) NOT NULL default '',
  `state` varchar(64) NOT NULL default '',
  `company` varchar(255) NOT NULL default '',
  `country` varchar(32) NOT NULL default '',
  `phone` varchar(64) NOT NULL default '',
  `isDefaultShipping` char(1) NOT NULL default '',
  `isDefaultBilling` char(1) NOT NULL default '',
  `email` varchar(255) NOT NULL default '',
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
  `countries` blob NULL,
  `shipping_modules` blob NULL,
  `minimum_total` varchar(255) NOT NULL default '',
  `maximum_total` varchar(255) NOT NULL default '',
  `paypal_account` varchar(255) NOT NULL default '',
  `paypal_business` varchar(255) NOT NULL default '',
  `postfinance_pspid` varchar(255) NOT NULL default '',
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
  `countries` blob NULL,
  `minimum_total` int(10) NOT NULL default '0',
  `maximum_total` int(10) NOT NULL default '0',
  `price` float NOT NULL default '0',
  `flatCalculation` varchar(10) NOT NULL default '',
  `enabled` char(1) NOT NULL default '',
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
  `upper_limit` double NULL default NULL,
  `rate` double NULL default NULL,
  `dest_zip` varchar(32) NOT NULL default '',
  `dest_country` varchar(64) NOT NULL default '',
  `dest_region` varchar(64) NOT NULL default '',
  `enabled` char(1) NOT NULL default '',
  PRIMARY KEY  (`id`),
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


-- --------------------------------------------------------

-- 
-- Table `tl_registry`
-- pid == cartID
--

CREATE TABLE `tl_registry` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `pid` int(10) unsigned NOT NULL default '0',
  `tstamp` int(10) unsigned NOT NULL default '0',
  `name` varchar(255) NOT NULL default '',
  `description` varchar(255) NOT NULL default '',
  `date` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `pid` (`pid`)
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
  `store_id` int(10) unsigned NOT NULL default '0',
  `source_cart_id` int(10) unsigned NOT NULL default '0',
  
  `status` varchar(32) NOT NULL default '',
  `shipping_address` text NULL,
  `billing_address` text NULL,
  
  `subTotal` varchar(20) NOT NULL default '',
  `taxTotal` varchar(20) NOT NULL default '',
  `shippingTotal` varchar(20) NOT NULL default '',
  `grandTotal` varchar(20) NOT NULL default '',
  
  `shipping_method` varchar(64) NOT NULL default '',
  
  `order_comments` text NULL, 
  `gift_message` text NULL,
  `gift_wrap` char(1) NOT NULL default '',
  
  `completed` char(1) NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `pid` (`pid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


-- --------------------------------------------------------

-- 
-- Table `tl_tax_class`
--

CREATE TABLE `tl_tax_class` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `pid` int(10) unsigned NOT NULL default '0',
  `tstamp` int(10) unsigned NOT NULL default '0',
  `name` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `pid` (`pid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


-- --------------------------------------------------------

-- 
-- Table `tl_tax_rate`
--

CREATE TABLE `tl_tax_rate` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `pid` int(10) unsigned NOT NULL default '0',
  `sorting` int(10) unsigned NOT NULL default '0',
  `tstamp` int(10) unsigned NOT NULL default '0',
  `country_id` varchar(255) NOT NULL default '',
  `region_id` varchar(255) NOT NULL default '',
  `postcode` varchar(255) NOT NULL default '',
  `code` varchar(255) NOT NULL default '',
  `rate` double NULL default NULL,
  PRIMARY KEY  (`id`),
  KEY `pid` (`pid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


-- --------------------------------------------------------

-- 
-- Table `tl_user`
--

CREATE TABLE `tl_user` (
 `mediamounts` blob NULL,
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


-- --------------------------------------------------------

-- 
-- Table `tl_user_group`
--

CREATE TABLE `tl_user_group` (
 `mediamounts` blob NULL,
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

