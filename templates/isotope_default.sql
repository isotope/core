-- ISOTOPE eCOMMERCE
-- v0.1 Default Install
-- www.isotopeecommerce.com

-- 
-- Table `tl_product_attribute_types` - Installs default attribute types and options
--

INSERT INTO `tl_product_attribute_types` (`pid`, `sorting`, `tstamp`, `type`, `attr_datatype`, `inputType`, `eval`, `name`) VALUES (0, 128, 1218221789, 'text', 'varchar', 'text', '', ''),(0, 256, 1218221789, 'integer', 'int', 'text', '', ''), (0, 384, 1218221789, 'decimal', 'decimal', 'text', '', ''),(0, 512, 1218221789, 'longtext', 'text', 'textarea', '', ''),(0, 640, 1218221789, 'datetime', 'datetime', 'text', '', ''),(0, 768, 1218221789, 'select', 'options', 'select', '', ''),(0, 896, 1218221789, 'checkbox', 'options', 'checkbox', '', ''),(0, 1024, 1218221789, 'options', 'options', 'radio', '', ''),(0, 1152, 1218221789, 'file', 'varchar', 'fileTree', '', ''),(0, 1280, 1218221789, 'media', 'varchar', 'imageManager', '', ''),(0, 150, 1218221789, 'shorttext', 'varchar', 'text', '', '');

-- 
-- Table `tl_store` - Installs default store config
--
INSERT INTO `tl_store` (`sorting`, `tstamp`, `store_configuration_name`, `productReaderJumpTo`, `cartJumpTo`, `checkoutJumpTo`, `missing_image_placeholder`, `thumbnail_image_width`, `thumbnail_image_height`, `medium_image_width`, `medium_image_height`, `large_image_width`, `large_image_height`, `gallery_thumbnail_image_width`, `gallery_thumbnail_image_height`, `cookie_duration`, `root_asset_import_path`, `checkout_login_module`, `currency`, `currencySymbol`, `currencyPosition`, `currencyFormat`, `countries`, `address_fields`, `country`, `orderPrefix`, `invoiceLogo`, `priceField`, `isDefaultStore`) VALUES (0, 1252093742, 'Default', 0, 0, 0, '', 100, 100, 200, 250, 1000, 1000, 50, 50, 30, '', 0, 'USD', '1', 'left', '10,000.00', null, null, 'us', '', '', 'price', '1');


--
-- Table `tl_product_types` - Installs default Basic Product Type
--

INSERT INTO `tl_product_types` (`pid`, `sorting`, `tstamp`, `name`, `description`, `attributes`) VALUES (0, 0, 1252961303, 'Basic Product', 'Basic Default Data', 0x613a31363a7b693a303b733a323a223135223b693a313b733a323a223136223b693a323b733a313a2235223b693a333b733a313a2238223b693a343b733a323a223132223b693a353b733a313a2231223b693a363b733a313a2239223b693a373b733a323a223130223b693a383b733a313a2234223b693a393b733a313a2232223b693a31303b733a313a2237223b693a31313b733a313a2236223b693a31323b733a313a2233223b693a31333b733a323a223134223b693a31343b733a323a223133223b693a31353b733a323a223131223b7d);


--
-- Table `tl_iso_mail` - Installs basic emails needed for module setup
--

INSERT INTO `tl_iso_mail` (`tstamp`, `name`, `senderName`, `sender`, `cc`, `bcc`, `template`) VALUES (1252958014, 'Admin Notification Email', 'Store', 'info@mystore.com', '', '', 'mail_default'), (1252958021, 'Customer Notification Email', 'Store', 'info@mystore.com', '', '', 'mail_default');



