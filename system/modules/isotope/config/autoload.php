<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2012 Isotope eCommerce Workgroup
 *
 * @package    Isotope
 * @link       http://www.isotopeecommerce.com
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 */


/**
 * Register the classes
 */
ClassLoader::addClasses(array
(
	// Classes
	'Isotope\tl_content'                => 'system/modules/isotope/classes/tl_content.php',
	'Isotope\tl_iso_addresses'          => 'system/modules/isotope/classes/tl_iso_addresses.php',
	'Isotope\tl_iso_config'             => 'system/modules/isotope/classes/tl_iso_config.php',
	'Isotope\tl_iso_downloads'          => 'system/modules/isotope/classes/tl_iso_downloads.php',
	'Isotope\tl_iso_groups'             => 'system/modules/isotope/classes/tl_iso_groups.php',
	'Isotope\tl_iso_mail'               => 'system/modules/isotope/classes/tl_iso_mail.php',
	'Isotope\tl_iso_mail_content'       => 'system/modules/isotope/classes/tl_iso_mail_content.php',
	'Isotope\tl_iso_orders'             => 'system/modules/isotope/classes/tl_iso_orders.php',
	'Isotope\tl_iso_orderstatus'        => 'system/modules/isotope/classes/tl_iso_orderstatus.php',
	'Isotope\tl_iso_payment_modules'    => 'system/modules/isotope/classes/tl_iso_payment_modules.php',
	'Isotope\tl_iso_prices'             => 'system/modules/isotope/classes/tl_iso_prices.php',
	'Isotope\tl_iso_product_categories' => 'system/modules/isotope/classes/tl_iso_product_categories.php',
	'Isotope\tl_iso_products'           => 'system/modules/isotope/classes/tl_iso_products.php',
	'Isotope\tl_iso_producttypes'       => 'system/modules/isotope/classes/tl_iso_producttypes.php',
	'Isotope\tl_iso_related_products'   => 'system/modules/isotope/classes/tl_iso_related_products.php',
	'Isotope\tl_iso_shipping_modules'   => 'system/modules/isotope/classes/tl_iso_shipping_modules.php',
	'Isotope\tl_iso_shipping_options'   => 'system/modules/isotope/classes/tl_iso_shipping_options.php',
	'Isotope\tl_iso_tax_class'          => 'system/modules/isotope/classes/tl_iso_tax_class.php',
	'Isotope\tl_iso_tax_rate'           => 'system/modules/isotope/classes/tl_iso_tax_rate.php',
	'Isotope\tl_module'                 => 'system/modules/isotope/classes/tl_module.php',

	// Drivers
	'DC_ProductData'                    => 'system/modules/isotope/drivers/DC_ProductData.php',
	'DC_TablePageId'                    => 'system/modules/isotope/drivers/DC_TablePageId.php',

	// Elements
	'Isotope\ContentIsotope'            => 'system/modules/isotope/elements/ContentAccordion.php',

	// Helpers
	'Isotope\PasteProductButton'        => 'system/modules/isotope/helpers/PasteProductButton.php',
	'Isotope\ProductCallbacks'          => 'system/modules/isotope/helpers/ProductCallbacks.php',
	'Isotope\ProductPriceFinder'        => 'system/modules/isotope/helpers/ProductPriceFinder.php',

	// Library/Isotope
	'Isotope\Isotope'                   => 'system/modules/isotope/library/Isotope/Isotope.php',
	'Isotope\Automator'                 => 'system/modules/isotope/library/Isotope/Automator.php',
	'Isotope\Backend'                   => 'system/modules/isotope/library/Isotope/Backend.php',
	'Isotope\Email'                     => 'system/modules/isotope/library/Isotope/Email.php',
	'Isotope\Frontend'                  => 'system/modules/isotope/library/Isotope/Frontend.php',
	'Isotope\Template'                  => 'system/modules/isotope/library/Isotope/Template.php',

	// Models
	'Isotope\IsotopeProduct'            => 'system/modules/isotope/models/IsotopeProduct.php',
	// Library/Isotope/BackendModule
	'Isotope\Module\Setup'                    => 'system/modules/isotope/library/Isotope/BackendModule/Setup.php',

	// Library/Isotope/Collection
	'Isotope\Collection\Collection'           => 'system/modules/isotope/library/Isotope/Collection/Collection.php',
	'Isotope\Collection\Cart'                 => 'system/modules/isotope/library/Isotope/Collection/Cart.php',
	'Isotope\Collection\Order'                => 'system/modules/isotope/library/Isotope/Collection/Order.php',

	// Library/Isotope/Gallery
	'Isotope\Gallery\Standard'                => 'system/modules/isotope/library/Isotope/Gallery/Standard.php',
	'Isotope\Gallery\Inline'                  => 'system/modules/isotope/library/Isotope/Gallery/Inline.php',
	'Isotope\Gallery\Zoom'                    => 'system/modules/isotope/library/Isotope/Gallery/Zoom.php',

	// Library/Isotope/Module
	'Isotope\Module\Module'                   => 'system/modules/isotope/library/Isotope/Module/Module.php',
	'Isotope\Module\AddressBook'              => 'system/modules/isotope/library/Isotope/Module/AddressBook.php',
	'Isotope\Module\Cart'                     => 'system/modules/isotope/library/Isotope/Module/Cart.php',
	'Isotope\Module\Checkout'                 => 'system/modules/isotope/library/Isotope/Module/Checkout.php',
	'Isotope\Module\ConfigSwitcher'           => 'system/modules/isotope/library/Isotope/Module/ConfigSwitcher.php',
	'Isotope\Module\CumulativeFilter'         => 'system/modules/isotope/library/Isotope/Module/CumulativeFilter.php',
	'Isotope\Module\OrderDetails'             => 'system/modules/isotope/library/Isotope/Module/OrderDetails.php',
	'Isotope\Module\OrderHistory'             => 'system/modules/isotope/library/Isotope/Module/OrderHistory.php',
	'Isotope\Module\ProductFilter'            => 'system/modules/isotope/library/Isotope/Module/ProductFilter.php',
	'Isotope\Module\ProductList'              => 'system/modules/isotope/library/Isotope/Module/ProductList.php',
	'Isotope\Module\ProductReader'            => 'system/modules/isotope/library/Isotope/Module/ProductReader.php',
	'Isotope\Module\ProductVariantList'       => 'system/modules/isotope/library/Isotope/Module/ProductVariantList.php',
	'Isotope\Module\RelatedProducts'          => 'system/modules/isotope/library/Isotope/Module/RelatedProducts.php',

	// Library/Isotope/Payment
	'Isotope\Payment\Payment'                  => 'system/modules/isotope/library/Isotope/Payment/Payment.php',
	'Isotope\Payment\AuthorizeDotNet'          => 'system/modules/isotope/library/Isotope/Payment/AuthorizeDotNet.php',
	'Isotope\Payment\Cash'                     => 'system/modules/isotope/library/Isotope/Payment/Cash.php',
	'Isotope\Payment\Cybersource'              => 'system/modules/isotope/library/Isotope/Payment/Cybersource.php',
	'Isotope\Payment\Paypal'                   => 'system/modules/isotope/library/Isotope/Payment/Paypal.php',
	'Isotope\Payment\PaypalPayflowPro'         => 'system/modules/isotope/library/Isotope/Payment/PaypalPayflowPro.php',
	'Isotope\Payment\Postfinance'              => 'system/modules/isotope/library/Isotope/Payment/Postfinance.php',
	'Isotope\Payment\CybersourceClient'        => 'system/modules/isotope/library/Isotope/Payment/CybersourceClient.php',

	// Library/Isotope/Shipping
	'Isotope\Shipping\Shipping'                => 'system/modules/isotope/library/Isotope/Shipping/Shipping.php',
	'Isotope\Shipping\Flat'                    => 'system/modules/isotope/library/Isotope/Shipping/Flat.php',
	'Isotope\Shipping\OrderTotal'              => 'system/modules/isotope/library/Isotope/Shipping/OrderTotal.php',
	'Isotope\Shipping\UPS'                     => 'system/modules/isotope/library/Isotope/Shipping/UPS.php',
	'Isotope\Shipping\USPS'                    => 'system/modules/isotope/library/Isotope/Shipping/USPS.php',
	'Isotope\Shipping\WeightTotal'             => 'system/modules/isotope/library/Isotope/Shipping/WeightTotal.php',

	// Models
	'Isotope\Model\Address'             => 'system/modules/isotope/library/Isotope/Model/Address.php',
	'Isotope\Model\Config'              => 'system/modules/isotope/library/Isotope/Model/Config.php',
	// Widgets
	'Isotope\AttributeWizard'          => 'system/modules/isotope/widgets/AttributeWizard.php',
	'Isotope\FieldWizard'              => 'system/modules/isotope/widgets/FieldWizard.php',
	'Isotope\InheritCheckBox'          => 'system/modules/isotope/widgets/InheritCheckBox.php',
	'Isotope\MediaManager'             => 'system/modules/isotope/widgets/MediaManager.php',
	'Isotope\ProductTree'              => 'system/modules/isotope/widgets/ProductTree.php',
	'Isotope\VariantWizard'            => 'system/modules/isotope/widgets/VariantWizard.php',
));


/**
 * Register the templates
 */
TemplateLoader::addFiles(array
(
	'be_iso_setup'                  => 'system/modules/isotope/templates',
	'be_pos_terminal'               => 'system/modules/isotope/templates',
	'iso_cart_full'                 => 'system/modules/isotope/templates',
	'iso_cart_mini'                 => 'system/modules/isotope/templates',
	'iso_cart_mini_table'           => 'system/modules/isotope/templates',
	'iso_checkout_billing_address'  => 'system/modules/isotope/templates',
	'iso_checkout_order_conditions' => 'system/modules/isotope/templates',
	'iso_checkout_order_info'       => 'system/modules/isotope/templates',
	'iso_checkout_order_products'   => 'system/modules/isotope/templates',
	'iso_checkout_payment_method'   => 'system/modules/isotope/templates',
	'iso_checkout_shipping_address' => 'system/modules/isotope/templates',
	'iso_checkout_shipping_method'  => 'system/modules/isotope/templates',
	'iso_filter_cumulative'         => 'system/modules/isotope/templates',
	'iso_filter_default'            => 'system/modules/isotope/templates',
	'iso_gallery_standard'          => 'system/modules/isotope/templates',
	'iso_gallery_inline'            => 'system/modules/isotope/templates',
	'iso_gallery_zoom'              => 'system/modules/isotope/templates',
	'iso_invoice'                   => 'system/modules/isotope/templates',
	'iso_list_default'              => 'system/modules/isotope/templates',
	'iso_list_variants'             => 'system/modules/isotope/templates',
	'iso_payment_postfinance'       => 'system/modules/isotope/templates',
	'iso_products_html'             => 'system/modules/isotope/templates',
	'iso_products_text'             => 'system/modules/isotope/templates',
	'iso_reader_default'            => 'system/modules/isotope/templates',
	'mod_iso_addressbook'           => 'system/modules/isotope/templates',
	'mod_iso_cart'                  => 'system/modules/isotope/templates',
	'mod_iso_checkout'              => 'system/modules/isotope/templates',
	'mod_iso_configswitcher'        => 'system/modules/isotope/templates',
	'mod_iso_orderdetails'          => 'system/modules/isotope/templates',
	'mod_iso_orderhistory'          => 'system/modules/isotope/templates',
	'mod_iso_productlist'           => 'system/modules/isotope/templates',
	'mod_iso_productlist_caching'   => 'system/modules/isotope/templates',
	'mod_iso_productreader'         => 'system/modules/isotope/templates',
));
