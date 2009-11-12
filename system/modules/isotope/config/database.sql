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
  `iso_show_teaser` char(1) NOT NULL default '',
  `new_products_time_window` int(10) unsigned NOT NULL default '0',
  `listing_filters` text NULL,
  `columns` int(10) unsigned NOT NULL default '0',
  `store_id` int(10) unsigned NOT NULL default '0',
  `store_ids` blob NULL,
  `orderCompleteJumpTo` int(10) unsigned NOT NULL default '0',
  `addressBookTemplate` varchar(64) NOT NULL default '',
  `iso_jump_first` char(1) NOT NULL default '',
  `iso_forward_cart` char(1) NOT NULL default '',
  `iso_mail_customer` int(10) unsigned NOT NULL default '0',
  `iso_mail_admin` int(10) unsigned NOT NULL default '0',
  `iso_sales_email` varchar(255) NOT NULL default '',
  `iso_order_conditions` int(10) unsigned NOT NULL default '0',
  `featured_products` char(1) NOT NULL default '',
  `iso_list_format` varchar(64) NOT NULL default '',
  `iso_category_scope` varchar(64) NOT NULL default '',
  `iso_use_quantity` char(1) NOT NULL default '',
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


-- --------------------------------------------------------

-- 
-- Table `tl_product_data` pid is product id.
--
CREATE TABLE `tl_product_data` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `pid` int(10) unsigned NOT NULL default '0',
  `sorting` int(10) unsigned NOT NULL default '0',
  `tstamp` int(10) unsigned NOT NULL default '0',
  `archived` char(1) NOT NULL default '',
  `pages` text NULL,
  `type` varchar(255) NOT NULL default '',
  `new_import` char(1) NOT NULL default '',
  `old_images_list` text NULL,
  `option_set_source` varchar(64) NOT NULL default '',
  `option_sets` int(10) unsigned NOT NULL default '0',
  `option_set_title` varchar(255) NOT NULL default '',
  `variants_wizard` text NULL,
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
-- Table `tl_product_option_sets` pid is product type id.
--
CREATE TABLE `tl_product_option_sets` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `pid` int(10) unsigned NOT NULL default '0',
  `sorting` int(10) unsigned NOT NULL default '0',
  `tstamp` int(10) unsigned NOT NULL default '0',
  `title` varchar(255) NOT NULL default '',
  `attribute_collection` text NULL,
  PRIMARY KEY  (`id`),
  KEY `pid` (`pid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


-- --------------------------------------------------------

-- 
-- Table `tl_product_types
--

CREATE TABLE `tl_product_types` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `pid` int(10) unsigned NOT NULL default '0',
  `sorting` int(10) unsigned NOT NULL default '0',
  `tstamp` int(10) unsigned NOT NULL default '0',
  `name` varchar(255) NOT NULL default '',
  `description` text NULL,
  `attributes` blob NULL,
  `downloads` char(1) NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `pid` (`pid`)
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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;



-- --------------------------------------------------------

--
-- Table `tl_product_attributes`
--

CREATE TABLE `tl_product_attributes` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `pid` int(10) unsigned NOT NULL default '0',
  `sorting` int(10) unsigned NOT NULL default '0',
  `tstamp` int(10) unsigned NOT NULL default '0',
  `name` varchar(255) NOT NULL default '',
  `description` varchar(255) NOT NULL default '',
  `type` varchar(64) NOT NULL default '',
  `fieldGroup` varchar(255) NOT NULL default '',
  `option_list` text NULL,
  `show_files` int(1) NOT NULL default '0',
  `attr_use_mode` varchar(10) NOT NULL default 'fixed',
  `attr_default_value` varchar(255) NOT NULL default '',
  `is_customer_defined` char(1) NOT NULL default '',
  `is_visible_on_front` char(1) NOT NULL default '',
  `is_hidden_on_backend` char(1) NOT NULL default '',
  `is_required` char(1) NOT NULL default '',
  `is_filterable` char(1) NOT NULL default '',
  `is_searchable` char(1) NOT NULL default '',
  `is_order_by_enabled` char(1) NOT NULL default '',
  `is_used_for_price_rules` char(1) NOT NULL default '',
  `is_multiple_select` char(1) NOT NULL default '',
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
  `load_callback` text NULL,
  `save_callback` text NULL,
  `field_name` varchar(255) NOT NULL default '',
  `disabled` char(1) NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `pid` (`pid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

-- 
-- Table `tl_attributes_to_languages` pid is product id.
--
CREATE TABLE `tl_attributes_to_languages` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `pid` int(10) unsigned NOT NULL default '0',
  `sorting` int(10) unsigned NOT NULL default '0',
  `tstamp` int(10) unsigned NOT NULL default '0',
  `language` varchar(16) NOT NULL default '',
  `field_name` varchar(64) NOT NULL default '',
  `value` text NULL,
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
  `label` varchar(255) NOT NULL default '',
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
  `root_asset_import_path` varchar(255) NOT NULL default '',
  `checkout_login_module` int(10) unsigned NOT NULL default '0',
  `country` varchar(2) NOT NULL default '',
  `priceField` varchar(64) NOT NULL default '',
  `priceOverrideField` varchar(64) NOT NULL default '',
  `priceCalculateFactor` varchar(10) NOT NULL default '',
  `priceCalculateMode` varchar(3) NOT NULL default '',
  `priceRoundPrecision` int(1) unsigned NOT NULL default '2',
  `priceRoundIncrement` varchar(4) NOT NULL default '',
  `currency` varchar(3) NOT NULL default '',
  `currencySymbol` char(1) NOT NULL default '',
  `currencyPosition` varchar(5) NOT NULL default '',
  `currencyFormat` varchar(20) NOT NULL default '',
  `countries` blob NULL,
  `address_fields` blob NULL,
  `orderPrefix` varchar(4) NOT NULL default '',
  `invoiceLogo` text NULL,
  `isDefaultStore` char(1) NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `pid` (`pid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


-- --------------------------------------------------------

--
-- Table `tl_product_to_category` pid is page id.
--	

CREATE TABLE `tl_product_to_category` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `pid` int(10) unsigned NOT NULL default '0',
  `sorting` int(10) unsigned NOT NULL default '0',
  `tstamp` int(10) unsigned NOT NULL default '0',
  `product_id` int(10) unsigned NOT NULL default '0',
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
  `product_attribute_collection` text NULL,
  `product_options` text NULL,
  `product_data` blob NULL,
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
  `countries` blob NULL,
  `shipping_modules` blob NULL,
  `allowed_cc_types` text NULL,  
  `minimum_total` varchar(255) NOT NULL default '',
  `maximum_total` varchar(255) NOT NULL default '',
  `new_order_status` varchar(255) NOT NULL default '',
  `postsale_mail` int(10) unsigned NOT NULL default '0',
  `paypal_account` varchar(255) NOT NULL default '',
  `paypal_business` varchar(255) NOT NULL default '',
  `postfinance_pspid` varchar(255) NOT NULL default '',
  `postfinance_secret` varchar(255) NOT NULL default '',
  `postfinance_method` varchar(4) NOT NULL default '',
  `authorize_login` varchar(255) NOT NULL default '',
  `authorize_trans_key` varchar(255) NOT NULL default '',
  `authorize_delimiter` varchar(4) NOT NULL default '',
  `authorize_trans_type` varchar(32) NOT NULL default '',
  `authorize_relay_response` char(1) NOT NULL default '',
  `authorize_email_customer` char(1) NOT NULL default '',
  `authorize_bypass_live_collection` char(1) NOT NULL default '',
  `groups` blob NULL,
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
  `countries` blob NULL,
  `groups` blob NULL,
  `minimum_total` int(10) NOT NULL default '0',
  `maximum_total` int(10) NOT NULL default '0',
  `price` float NOT NULL default '0',
  `surcharge_field` varchar(255) NOT NULL default '',
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
  `date` int(10) unsigned NOT NULL default '0',  
  `order_id` varchar(14) NOT NULL default '',
  `uniqid` varchar(27) NOT NULL default '',
  
  `store_id` int(10) unsigned NOT NULL default '0',
  `cart_id` int(10) unsigned NOT NULL default '0',
  `source_cart_id` int(10) unsigned NOT NULL default '0',
  `language` varchar(2) NOT NULL default '',
  `status` varchar(32) NOT NULL default '',
  `shipping_address` text NULL,
  `billing_address` text NULL,
  
  `subTotal` double NULL default NULL,
  `taxTotal` double NULL default NULL,
  `shippingTotal` double NULL default NULL,
  `grandTotal` double NULL default NULL,
  `payment_method` varchar(255) NOT NULL default '',
  `shipping_method` varchar(255) NOT NULL default '',
  `cc_num` varchar(64) NOT NULL default '',
  `cc_type` varchar(32) NOT NULL default '',
  `cc_exp` varchar(16) NOT NULL default '',
  `cc_cvv` varchar(8) NOT NULL default '',
  `authnet_response` varchar(255) NOT NULL default '',
  `authnet_reason` text NULL,
  `order_comments` text NULL, 
  `gift_message` text NULL,
  `gift_wrap` char(1) NOT NULL default '', 
  `completed` char(1) NOT NULL default '',
  `payment_data` blob NULL,
  `shipping_data` blob NULL,
  `payment_description` text NULL,
  `shipping_description` text NULL,
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
  `sorting` int(10) unsigned NOT NULL default '0',
  `tstamp` int(10) unsigned NOT NULL default '0',
  `product_id` int(10) unsigned NOT NULL default '0',
  `quantity_sold` int(10) unsigned NOT NULL default '0',
  `price` double NULL default NULL,
  `product_options` text NULL,
  `product_data` blob NULL,
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
 `iso_product_types` blob NULL,
 `iso_stores` blob NULL,
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


-- --------------------------------------------------------

-- 
-- Table `tl_user_group`
--

CREATE TABLE `tl_user_group` (
 `iso_product_types` blob NULL,
 `iso_stores` blob NULL,
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
  PRIMARY KEY  (`id`),
  KEY `pid` (`pid`),
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

