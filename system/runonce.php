<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');

/**
 * TYPOlight Open Source CMS
 * Copyright (C) 2005-2010 Leo Feyer
 *
 * This program is free software: you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation, either
 * version 3 of the License, or (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 * 
 * You should have received a copy of the GNU Lesser General Public
 * License along with this program. If not, please visit the Free
 * Software Foundation website at <http://www.gnu.org/licenses/>.
 *
 * PHP version 5
 * @copyright  Winans Creative 2009, Intelligent Spark 2010, iserv.ch GmbH 2010
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
 * @author     Andreas Schempp <andreas@schempp.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */


class IsotopeRunonce extends Frontend
{

	/**
	 * Initialize the object
	 */
	public function __construct()
	{
		parent::__construct();
		
		$this->import('Database');
	}


	/**
	 * Run the controller
	 */
	public function run()
	{
		// Cancel if shop has not yet been installed
		if (!$this->Database->tableExists('tl_store') && !$this->Database->tableExists('tl_iso_config'))
			return;
			
		$this->renameTables();
		$this->renameFields();
		$this->updateAttributes();
		$this->updateProductCategories();
		$this->updateStoreConfigurations();
		$this->updateOrders();
		$this->updateImageSizes();
		$this->updateFrontendModules();
		$this->updateFrontendTemplates();
		$this->refreshDatabaseFile();
		
		if($this->Database->tableExists('tl_product_attribute_types'))
			$this->Database->executeUncached("DROP TABLE tl_product_attribute_types");
			
		// Checkout method has been renamed from "login" to "member" to prevent a problem with palette of the login module
		$this->Database->executeUncached("UPDATE tl_module SET iso_checkout_method='member' WHERE iso_checkout_method='login'");
		
		// Renamed attribute types
		$this->Database->executeUncached("UPDATE tl_iso_attributes SET type='textarea' WHERE type='longtext'");
		
		// Drop fields that are now part of the default DCA
		$this->Database->executeUncached("DELETE FROM tl_iso_attributes WHERE field_name='alias'");
		$this->Database->executeUncached("DELETE FROM tl_iso_attributes WHERE field_name='visibility'");
		$this->Database->executeUncached("DELETE FROM tl_iso_attributes WHERE field_name='name'");
		$this->Database->executeUncached("DELETE FROM tl_iso_attributes WHERE field_name='teaser'");
		$this->Database->executeUncached("DELETE FROM tl_iso_attributes WHERE field_name='description'");
		$this->Database->executeUncached("DELETE FROM tl_iso_attributes WHERE field_name='tax_class'");
		$this->Database->executeUncached("DELETE FROM tl_iso_attributes WHERE field_name='main_image'");
		$this->Database->executeUncached("DELETE FROM tl_iso_attributes WHERE field_name='sku'");
		$this->Database->executeUncached("DELETE FROM tl_iso_attributes WHERE field_name='quantity'");
		$this->Database->executeUncached("DELETE FROM tl_iso_attributes WHERE field_name='shipping_exempt'");
		$this->Database->executeUncached("DELETE FROM tl_iso_attributes WHERE field_name='price'");
		$this->Database->executeUncached("DELETE FROM tl_iso_attributes WHERE field_name='price_override'");
		$this->Database->executeUncached("DELETE FROM tl_iso_attributes WHERE field_name='use_price_override'");
		$this->Database->executeUncached("DELETE FROM tl_iso_attributes WHERE field_name='weight'");
		
		// Because configuration has been changed, we cannot use the existing cart data
		$this->Database->executeUncached("DELETE FROM tl_iso_cart_items");
		$this->Database->executeUncached("DELETE FROM tl_iso_cart");
	}
	
	
	private function renameTables()
	{
		if ($this->Database->tableExists('tl_product_data')) $this->Database->executeUncached("ALTER TABLE tl_product_data RENAME tl_iso_products");
		if ($this->Database->tableExists('tl_product_types')) $this->Database->executeUncached("ALTER TABLE tl_product_types RENAME tl_iso_producttypes");
		if ($this->Database->tableExists('tl_product_attributes')) $this->Database->executeUncached("ALTER TABLE tl_product_attributes RENAME tl_iso_attributes");
		if ($this->Database->tableExists('tl_product_downloads')) $this->Database->executeUncached("ALTER TABLE tl_product_downloads RENAME tl_iso_downloads");
		if ($this->Database->tableExists('tl_product_categories')) $this->Database->executeUncached("ALTER TABLE tl_product_categories RENAME tl_iso_product_categories");
		if ($this->Database->tableExists('tl_tax_class')) $this->Database->executeUncached("ALTER TABLE tl_tax_class RENAME tl_iso_tax_class");
		if ($this->Database->tableExists('tl_tax_rate')) $this->Database->executeUncached("ALTER TABLE tl_tax_rate RENAME tl_iso_tax_rate");
		if ($this->Database->tableExists('tl_payment_modules')) $this->Database->executeUncached("ALTER TABLE tl_payment_modules RENAME tl_iso_payment_modules");
		if ($this->Database->tableExists('tl_shipping_modules')) $this->Database->executeUncached("ALTER TABLE tl_shipping_modules RENAME tl_iso_shipping_modules");
		if ($this->Database->tableExists('tl_shipping_options')) $this->Database->executeUncached("ALTER TABLE tl_shipping_options RENAME tl_iso_shipping_options");
		if ($this->Database->tableExists('tl_related_categories')) $this->Database->executeUncached("ALTER TABLE tl_related_categories RENAME tl_iso_related_categories");
		if ($this->Database->tableExists('tl_related_products')) $this->Database->executeUncached("ALTER TABLE tl_related_products RENAME tl_iso_related_products");
		if ($this->Database->tableExists('tl_address_book')) $this->Database->executeUncached("ALTER TABLE tl_address_book RENAME tl_iso_addresses");
		if ($this->Database->tableExists('tl_store')) $this->Database->executeUncached("ALTER TABLE tl_store RENAME tl_iso_config");
		if ($this->Database->tableExists('tl_cart')) $this->Database->executeUncached("ALTER TABLE tl_cart RENAME tl_iso_cart");
		if ($this->Database->tableExists('tl_cart_items')) $this->Database->executeUncached("ALTER TABLE tl_cart_items RENAME tl_iso_cart_items");
	}
	
	
	private function renameFields()
	{
		// tl_iso_config.store_configuration_name has been renamed to tl_iso_config.name
		if ($this->Database->fieldExists('store_configuration_name', 'tl_iso_config'))
		{
			$this->Database->executeUncached("ALTER TABLE tl_iso_config CHANGE COLUMN store_configuration_name name varchar(255) NOT NULL default ''");
		}
		
		// tl_iso_config.gallery_thumbnail_image_width has been renamed to tl_iso_config.gallery_image_width
		if ($this->Database->fieldExists('gallery_thumbnail_image_width', 'tl_iso_config'))
		{
			$this->Database->executeUncached("ALTER TABLE tl_iso_config CHANGE COLUMN gallery_thumbnail_image_width gallery_image_width int(10) unsigned NOT NULL default '0'");
		}
		
		// tl_iso_config.gallery_thumbnail_image_height has been renamed to tl_iso_config.gallery_image_height
		if ($this->Database->fieldExists('gallery_thumbnail_image_height', 'tl_iso_config'))
		{
			$this->Database->executeUncached("ALTER TABLE tl_iso_config CHANGE COLUMN gallery_thumbnail_image_height gallery_image_height int(10) unsigned NOT NULL default '0'");
		}
		
		// tl_iso_products.visiblity has been renamed to tl_iso_products.published
		if ($this->Database->fieldExists('visibility', 'tl_iso_products'))
		{
			$this->Database->executeUncached("ALTER TABLE tl_iso_products CHANGE COLUMN visibility published char(1) NOT NULL default ''");
		}
		
		// tl_product_data.main_image has been renamed to tl_iso_products.images
		if ($this->Database->fieldExists('main_image', 'tl_iso_products'))
		{
			$this->Database->executeUncached("ALTER TABLE tl_iso_products CHANGE COLUMN main_image images blob NULL");
		}
		
		// tl_iso_attributes.fieldGroup has been renamed to tl_iso_attributes.legend
		if ($this->Database->fieldExists('fieldGroup', 'tl_iso_attributes'))
		{
			$this->Database->executeUncached("ALTER TABLE tl_iso_attributes CHANGE COLUMN fieldGroup legend varchar(255) NOT NULL default ''");
		}
		
		// tl_iso_addresses.state has been renamed to tl_iso_addresses.subdivision
		if ($this->Database->fieldExists('state', 'tl_iso_addresses') && !$this->Database->fieldExists('subdivision', 'tl_iso_addresses'))
		{
			$this->Database->executeUncached("ALTER TABLE tl_iso_addresses CHANGE COLUMN state subdivision varchar(10) NOT NULL default ''");
		}
		
		// tl_iso_config.state has been renamed to tl_iso_config.subdivision
		if ($this->Database->fieldExists('state', 'tl_iso_config') && !$this->Database->fieldExists('subdivision', 'tl_iso_config'))
		{
			$this->Database->executeUncached("ALTER TABLE tl_iso_config CHANGE COLUMN state subdivision varchar(10) NOT NULL default ''");
		}
		
		// tl_iso_config.street has been renamed to tl_iso_config.street_1
		if ($this->Database->fieldExists('street', 'tl_iso_config') && !$this->Database->fieldExists('street_1', 'tl_iso_config'))
		{
			$this->Database->executeUncached("ALTER TABLE tl_iso_config CHANGE COLUMN street street_1 varchar(255) NOT NULL default ''");
		}
		
		// tl_module.store_id has been renamed to tl_module.iso_config_id
		if ($this->Database->fieldExists('store_id', 'tl_module') && !$this->Database->fieldExists('iso_config_id', 'tl_module'))
		{
			$this->Database->executeUncached("ALTER TABLE tl_module CHANGE COLUMN store_id iso_config_id int(10) unsigned NOT NULL default '0'");
		}
		
		// tl_module.store_ids has been renamed to tl_module.iso_config_ids
		if ($this->Database->fieldExists('store_ids', 'tl_module') && !$this->Database->fieldExists('iso_config_ids', 'tl_module'))
		{
			$this->Database->executeUncached("ALTER TABLE tl_module CHANGE COLUMN store_ids iso_config_ids blob NULL");
		}
		
		// tl_iso_cart.store_id has been renamed to tl_iso_cart.config_id
		if ($this->Database->fieldExists('store_id', 'tl_iso_cart') && !$this->Database->fieldExists('config_id', 'tl_iso_cart'))
		{
			$this->Database->executeUncached("ALTER TABLE tl_iso_cart CHANGE COLUMN store_id config_id int(10) unsigned NOT NULL default '0'");
		}
		
		// tl_iso_orders.store_id has been renamed to tl_iso_orders.config_id
		if ($this->Database->fieldExists('store_id', 'tl_iso_orders') && !$this->Database->fieldExists('config_id', 'tl_iso_orders'))
		{
			$this->Database->executeUncached("ALTER TABLE tl_iso_orders CHANGE COLUMN store_id config_id int(10) unsigned NOT NULL default '0'");
		}
		
		// tl_iso_config.isDefaultStore has been renamed to tl_iso_config.fallback
		if ($this->Database->fieldExists('isDefaultStore', 'tl_iso_config') && !$this->Database->fieldExists('fallback', 'tl_iso_config'))
		{
			$this->Database->executeUncached("ALTER TABLE tl_iso_config CHANGE COLUMN isDefaultStore fallback char(1) NOT NULL default ''");
		}
		
		// tl_user.iso_stores has been renamed to tl_user.iso_configs
		if ($this->Database->fieldExists('iso_stores', 'tl_user') && !$this->Database->fieldExists('iso_configs', 'tl_user'))
		{
			$this->Database->executeUncached("ALTER TABLE tl_user CHANGE COLUMN iso_stores iso_configs blob NULL");
		}
		
		// tl_user_group.iso_stores has been renamed to tl_user_group.iso_configs
		if ($this->Database->fieldExists('iso_stores', 'tl_user_group') && !$this->Database->fieldExists('iso_configs', 'tl_user_group'))
		{
			$this->Database->executeUncached("ALTER TABLE tl_user_group CHANGE COLUMN iso_stores iso_configs blob NULL");
		}
		
		// tl_iso_tax_rate.store has been renamed to tl_iso_tax_rate.config
		if ($this->Database->fieldExists('store', 'tl_iso_tax_rate') && !$this->Database->fieldExists('config', 'tl_iso_tax_rate'))
		{
			$this->Database->executeUncached("ALTER TABLE tl_iso_tax_rate CHANGE COLUMN store config int(10) unsigned NOT NULL default '0'");
		}
		
		// tl_page.isotopeStoreConfig has been renamed to tl_page.iso_config
		if ($this->Database->fieldExists('isotopeStoreConfig', 'tl_page') && !$this->Database->fieldExists('iso_config', 'tl_page'))
		{
			$this->Database->executeUncached("ALTER TABLE tl_page CHANGE COLUMN isotopeStoreConfig iso_config int(10) unsigned NOT NULL default '0'");
		}
		
		// tl_iso_cart_items.quantity_requested has been renamed to tl_iso_cart_items.product_quantity
		if ($this->Database->fieldExists('quantity_requested', 'tl_iso_cart_items') && !$this->Database->fieldExists('product_quantity', 'tl_iso_cart_items'))
		{
			$this->Database->executeUncached("ALTER TABLE tl_iso_cart_items CHANGE COLUMN quantity_requested product_quantity int(10) unsigned NOT NULL default '0'");
		}
		
		// tl_iso_order_items.quantity_sold has been renamed to tl_iso_order_items.product_quantity
		if ($this->Database->fieldExists('quantity_sold', 'tl_iso_order_items') && !$this->Database->fieldExists('product_quantity', 'tl_iso_order_items'))
		{
			$this->Database->executeUncached("ALTER TABLE tl_iso_order_items CHANGE COLUMN quantity_sold product_quantity int(10) unsigned NOT NULL default '0'");
		}
		
		// tl_iso_addresses.street has been renamed to tl_iso_addresses.street_1
		if ($this->Database->fieldExists('street', 'tl_iso_addresses') && !$this->Database->fieldExists('street_1','tl_iso_addresses'))
		{
			$this->Database->executeUncached("ALTER TABLE tl_iso_addresses CHANGE COLUMN street street_1 varchar(255) NOT NULL default ''");
			$this->Database->executeUncached("ALTER TABLE tl_iso_config CHANGE COLUMN street street_1 varchar(255) NOT NULL default ''");
			$objStores = $this->Database->executeUncached("SELECT * FROM tl_iso_config");
			
			while( $objStores->next() )
			{
				$arrBilling = deserialize($objStores->billing_fields, true);
				$arrShipping = deserialize($objStores->shipping_fields, true);
				
				if (($k = array_search('street', $arrBilling)) !== false)
				{
					$arrBilling[$k] = 'street_1';
				}
				
				if (($k = array_search('street', $arrShipping)) !== false)
				{
					$arrShipping[$k] = 'street_1';
				}
				
				$this->Database->prepare("UPDATE tl_iso_config SET shipping_fields=?, billing_fields=? WHERE id=?")->execute(serialize($arrShipping), serialize($arrBilling), $objStores->id);
			}
		}
	}
	
	
	/**
	 * Checkboxes should be '' not '0'
	 */
	private function updateAttributes()
	{
		$arrFields = $this->Database->listFields('tl_iso_attributes');
		foreach( $arrFields as $field )
		{
			$this->Database->executeUncached("UPDATE tl_iso_attributes SET " . $field['name'] . "='' WHERE " . $field['name'] . "='0'");
		}
	}
	
	private function updateProductCategories()
	{
		if ($this->Database->tableExists('tl_product_to_category'))
		{
			$this->Database->executeUncached("CREATE TABLE IF NOT EXISTS `tl_product_categories` (`id` int(10) unsigned NOT NULL auto_increment,`pid` int(10) unsigned NOT NULL default '0',`tstamp` int(10) unsigned NOT NULL default '0',`page_id` int(10) unsigned NOT NULL default '0',PRIMARY KEY  (`id`),KEY `pid` (`pid`)) ENGINE=MyISAM DEFAULT CHARSET=utf8;");
			
			$this->Database->executeUncached("INSERT INTO tl_product_categories (pid,tstamp,page_id) (SELECT product_id AS pid, tstamp, pid AS page_id FROM tl_product_to_category)");
			
			$this->Database->executeUncached("DROP TABLE tl_product_to_category");
		}
	}
	
	private function updateStoreConfigurations()
	{
		if ($this->Database->fieldExists('countries', 'tl_iso_config') && !$this->Database->fieldExists('shipping_countries','tl_iso_config'))
		{
			$this->Database->executeUncached("ALTER TABLE tl_iso_config CHANGE COLUMN countries shipping_countries blob NULL");
			$this->Database->executeUncached("ALTER TABLE tl_iso_config ADD COLUMN billing_countries blob NULL");
			$this->Database->prepare("UPDATE tl_iso_config SET billing_countries=shipping_countries");
		}
		
		if ($this->Database->fieldExists('address_fields', 'tl_iso_config') && !$this->Database->fieldExists('shipping_fields','tl_iso_config'))
		{
			$this->Database->executeUncached("ALTER TABLE tl_iso_config CHANGE COLUMN address_fields shipping_fields blob NULL");
			$this->Database->executeUncached("ALTER TABLE tl_iso_config ADD COLUMN billing_fields blob NULL");
			$this->Database->prepare("UPDATE tl_iso_config SET billing_fields=shipping_fields");
		}
	}
	
	
	private function updateOrders()
	{
/*
		if ($this->Database->fieldExists('product_options', 'tl_iso_order_items'))
		{
			$objItems = $this->Database->executeUncached("SELECT * FROM tl_iso_order_items");
			
			while( $objItems->next() )
			{
				$arrOld = deserialize($objItems->product_options);
				
				if (is_array($arrOld) && count($arrOld))
				{
					$arrOptions = array();
					$objProduct = unserialize($objItems->product_data);
					
					foreach( $arrOld as $name => $value )
					{
						$arrOptions[$name] = $value['values'][0];
					}
					
					$objProduct->setOptions($arrOptions);
					
					$this->Database->prepare("UPDATE tl_iso_order_items SET product_data=?, product_options='' WHERE id=?")->execute(serialize($objProduct), $objItems->id);
				}
			}
		}
*/
		
		if (!$this->Database->fieldExists('date_shipped', 'tl_iso_orders'))
		{
			$this->Database->executeUncached("ALTER TABLE tl_iso_orders ADD COLUMN date_shipped varchar(10) NOT NULL default ''");
		}
		
		$this->Database->executeUncached("UPDATE tl_iso_orders SET date_shipped=date, status='processing' WHERE status='shipped'");
	}
	
	
	private function updateImageSizes()
	{
		$arrUpdate = array();
		
		if ($this->Database->fieldExists('gallery_image_width', 'tl_iso_config') && $this->Database->fieldExists('gallery_image_height', 'tl_iso_config'))
		{
			if (!$this->Database->fieldExists('gallery_size', 'tl_iso_config'))
			{
				$this->Database->executeUncached("ALTER TABLE tl_iso_config ADD COLUMN gallery_size varchar(64) NOT NULL default ''");
			}
			
			$arrUpdate[] = 'gallery';
		}
		
		if ($this->Database->fieldExists('thumbnail_image_width', 'tl_iso_config') && $this->Database->fieldExists('thumbnail_image_height', 'tl_iso_config'))
		{
			if (!$this->Database->fieldExists('thumbnail_size', 'tl_iso_config'))
			{
				$this->Database->executeUncached("ALTER TABLE tl_iso_config ADD COLUMN thumbnail_size varchar(64) NOT NULL default ''");
			}
			
			$arrUpdate[] = 'thumbnail';
		}
		
		if ($this->Database->fieldExists('medium_image_width', 'tl_iso_config') && $this->Database->fieldExists('medium_image_height', 'tl_iso_config'))
		{
			if (!$this->Database->fieldExists('medium_size', 'tl_iso_config'))
			{
				$this->Database->executeUncached("ALTER TABLE tl_iso_config ADD COLUMN medium_size varchar(64) NOT NULL default ''");
			}
			
			$arrUpdate[] = 'medium';
		}
		
		if ($this->Database->fieldExists('large_image_width', 'tl_iso_config') && $this->Database->fieldExists('large_image_height', 'tl_iso_config'))
		{
			if (!$this->Database->fieldExists('large_size', 'tl_iso_config'))
			{
				$this->Database->executeUncached("ALTER TABLE tl_iso_config ADD COLUMN large_size varchar(64) NOT NULL default ''");
			}
			
			$arrUpdate[] = 'large';
		}
		
		
		if (count($arrUpdate))
		{
			$objStores = $this->Database->executeUncached("SELECT * FROM tl_iso_config");
			
			while( $objStores->next() )
			{
				$arrSet = array();
				
				foreach( $arrUpdate as $size )
				{
					$arrSet[$size.'_size'] = serialize(array($objStores->{$size.'_image_width'}, $objStores->{$size.'_image_height'}, 'crop'));
				}
				
				$this->Database->prepare("UPDATE tl_iso_config %s WHERE id=?")->set($arrSet)->execute($objStores->id);
			}
			
			foreach( $arrUpdate as $size )
			{
				// Do not use multiple DROP COLUMN in one ALTER TABLE. It is supported by MySQL, but not standard SQL92
				$this->Database->executeUncached("ALTER TABLE tl_iso_config DROP COLUMN ".$size."_image_width");
				$this->Database->executeUncached("ALTER TABLE tl_iso_config DROP COLUMN ".$size."_image_height");
			}
		}
	}
	
	
	private function updateFrontendModules()
	{
		$arrUpdate = array
		(
			'isoProductLister'			=> 'iso_productlist',
			'isoProductReader'			=> 'iso_productreader',
			'isoShoppingCart'			=> 'iso_cart',
			'isoCheckout'				=> 'iso_checkout',
			'isoFilterModule'			=> 'iso_productfilter',
			'isoOrderHistory'			=> 'iso_orderhistory',
			'isoOrderDetails'			=> 'iso_orderdetails',
			'isoStoreSwitcher'			=> 'iso_storeswitcher',
			'isoAddressBook'			=> 'iso_addressbook',
			'iso_storeswitcher'			=> 'iso_configswitcher',
		);
		
		foreach( $arrUpdate as $old => $new )
		{
			$objModules = $this->Database->prepare("SELECT * FROM tl_module WHERE type=?")->execute($old);
			
			while( $objModules->next() )
			{
				$cssID = deserialize($objModules->cssID, true);
				$cssID[1] = trim($cssID[1] . ' mod_' . $old);
				
				$this->Database->prepare("UPDATE tl_module SET type=?, cssID=? WHERE id=?")->execute($new, serialize($cssID), $objModules->id);
				
				$objContents = $this->Database->prepare("SELECT * FROM tl_content WHERE type='module' AND module=?")->execute($objModules->id);
			
				while( $objContents->next() )
				{
					$cssID = deserialize($objContents->cssID, true);
					$cssID[1] = trim($cssID[1] . ' mod_' . $old);
					
					$this->Database->prepare("UPDATE tl_content SET cssID=? WHERE id=?")->execute(serialize($cssID), $objContents->id);
				}
			}
		}
	}
	
	
	private function updateFrontendTemplates()
	{
		$arrUpdate = array
		(
			'mod_shopping_cart'			=> 'mod_iso_cart',
			'mod_filters'				=> 'mod_iso_productfilter',
			'mod_orderdetails'			=> 'mod_iso_orderdetails',
			'mod_orderhistory'			=> 'mod_iso_orderhistory',
			'mod_productlist'			=> 'mod_iso_productlist',
			'mod_productreader'			=> 'mod_iso_productreader',
			'mod_storeswitcher'			=> 'mod_iso_storeswitcher',
			'iso_address_book_list'		=> 'mod_iso_addressbook',
			'mod_iso_storeswitcher'		=> 'mod_iso_configswitcher',
		);
		
		$this->import('Files');
		
		foreach( $arrUpdate as $old => $new )
		{
			if (file_exists(TL_ROOT . '/templates/' . $old . '.tpl') && !file_exists(TL_ROOT . '/templates/' . $new . '.tpl'))
			{
				$this->Files->rename('templates/' . $old . '.tpl', 'templates/' . $new . '.tpl');
			}
		}
		
		
		// Move old templates to root folder, they might be in use
		$arrUpdate = array
		(
			'iso_list_featured_product',
			'iso_reader_product_single',
		);
		
		foreach( $arrUpdate as $file )
		{
			if (file_exists(TL_ROOT . '/system/modules/isotope/templates/' . $file . '.tpl') && !file_exists(TL_ROOT . '/templates/' . $file . '.tpl'))
			{
				$this->Files->rename('system/modules/isotope/templates/' . $file . '.tpl', 'templates/' . $file . '.tpl');
			}
		}
	}
	
	
	/**
	 * Regenerate the database.sql to include custom attributes.
	 * This info might have been lost when updating the file via FTP.
	 */
	private function refreshDatabaseFile()
	{
		$this->import('IsotopeDatabase');
		
		$objAttributes = $this->Database->execute("SELECT * FROM tl_iso_attributes");
		
		while( $objAttributes->next() )
		{
			// Skip empty lines
			if (!strlen($objAttributes->field_name) || !strlen($GLOBALS['ISO_ATTR'][$objAttributes->type]['sql']))
				continue;
				
			$this->IsotopeDatabase->add($objAttributes->field_name, $GLOBALS['ISO_ATTR'][$objAttributes->type]['sql']);
		}
	}
}


/**
 * Instantiate controller
 */
$objIsotope = new IsotopeRunonce();
$objIsotope->run();

