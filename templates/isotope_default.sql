-- ISOTOPE eCOMMERCE
-- v0.1 Default Install
-- www.isotopeecommerce.com

-- 
-- Table `tl_store` - Installs default store config
--
INSERT INTO `tl_store` (`id`, `pid`, `sorting`, `tstamp`, `store_configuration_name`, `label`, `missing_image_placeholder`, `thumbnail_image_width`, `thumbnail_image_height`, `medium_image_width`, `medium_image_height`, `large_image_width`, `large_image_height`, `gallery_image_width`, `gallery_image_height`, `cookie_duration`, `country`, `priceField`, `priceOverrideField`, `priceCalculateFactor`, `priceCalculateMode`, `priceRoundPrecision`, `priceRoundIncrement`, `currency`, `currencySymbol`, `currencyPosition`, `currencyFormat`, `shipping_countries`, `shipping_fields`, `orderPrefix`, `invoiceLogo`, `isDefaultStore`, `firstname`, `lastname`, `street`, `street_2`, `street_3`, `postal`, `city`, `state`, `company`, `phone`, `emailShipping`, `weightUnit`, `enableGoogleAnalytics`, `billing_countries`, `billing_fields`) VALUES (1, 0, 0, 1263270562, 'Default Store', 'Default Store', '', 100, 0, 200, 0, 400, 0, 50, 0, 30, 'us', 'price', 'price_override', '1', 'mul', 2, '0.01', 'USD', '1', 'left', '10,000.00', 0x613a313a7b693a303b733a323a227573223b7d, 0x613a31323a7b693a303b733a393a2266697273746e616d65223b693a313b733a383a226c6173746e616d65223b693a323b733a373a22636f6d70616e79223b693a333b733a363a22737472656574223b693a343b733a343a2263697479223b693a353b733a353a227374617465223b693a363b733a363a22706f7374616c223b693a373b733a373a22636f756e747279223b693a383b733a353a2270686f6e65223b693a393b733a353a22656d61696c223b693a31303b733a31363a22697344656661756c7442696c6c696e67223b693a31313b733a31373a22697344656661756c745368697070696e67223b7d, '', '', '1', '', '', '', '', '', '', '', '', '', '', '', 'LBS', '', 0x613a313a7b693a303b733a323a227573223b7d, 0x613a31323a7b693a303b733a393a2266697273746e616d65223b693a313b733a383a226c6173746e616d65223b693a323b733a373a22636f6d70616e79223b693a333b733a363a22737472656574223b693a343b733a343a2263697479223b693a353b733a353a227374617465223b693a363b733a363a22706f7374616c223b693a373b733a373a22636f756e747279223b693a383b733a353a2270686f6e65223b693a393b733a353a22656d61696c223b693a31303b733a31363a22697344656661756c7442696c6c696e67223b693a31313b733a31373a22697344656661756c745368697070696e67223b7d);



--
-- Table `tl_product_types` - Installs default Basic Product Type
--

INSERT INTO `tl_product_types` (`pid`, `sorting`, `tstamp`, `name`, `description`, `attributes`) VALUES (0, 0, 1252961303, 'Basic Product', 'Basic Default Data', 0x613a31363a7b693a303b733a323a223135223b693a313b733a323a223136223b693a323b733a313a2235223b693a333b733a313a2238223b693a343b733a323a223132223b693a353b733a313a2231223b693a363b733a313a2239223b693a373b733a323a223130223b693a383b733a313a2234223b693a393b733a313a2232223b693a31303b733a313a2237223b693a31313b733a313a2236223b693a31323b733a313a2233223b693a31333b733a323a223134223b693a31343b733a323a223133223b693a31353b733a323a223131223b7d);


--
-- Table `tl_iso_mail` - Installs basic emails needed for module setup
--

INSERT INTO `tl_iso_mail` (`tstamp`, `name`, `senderName`, `sender`, `cc`, `bcc`, `template`) VALUES (1252958014, 'Admin Notification Email', 'Store', 'info@mystore.com', '', '', 'mail_default'), (1252958021, 'Customer Notification Email', 'Store', 'info@mystore.com', '', '', 'mail_default');



