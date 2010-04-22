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
		if (!$this->Database->tableExists('tl_store'))
			return;
			
		$this->renameFields();
		$this->updateAttributes();
		$this->updateProductCategories();
		$this->updateStoreConfigurations();
		$this->updateProductOptions();
		$this->updateImageSizes();
		$this->updateFrontendModules();
		$this->updateFrontendTemplates();
		$this->refreshDatabaseFile();
		
		if($this->Database->tableExists('tl_product_attribute_types'))
			$this->Database->execute("DROP TABLE tl_product_attribute_types");
			
		// Checkout method has been renamed from "login" to "member" to prevent a problem with palette of the login module
		$this->Database->execute("UPDATE tl_module SET iso_checkout_method='member' WHERE iso_checkout_method='login'");
		
		// Renamed attribute types
		$this->Database->execute("UPDATE tl_product_attributes SET type='textarea' WHERE type='longtext'");
		
		// Drop fields that are now part of the default DCA
		$this->Database->execute("DELETE FROM tl_product_attributes WHERE field_name='alias'");
		$this->Database->execute("DELETE FROM tl_product_attributes WHERE field_name='visibility'");
		$this->Database->execute("DELETE FROM tl_product_attributes WHERE field_name='name'");
		$this->Database->execute("DELETE FROM tl_product_attributes WHERE field_name='teaser'");
		$this->Database->execute("DELETE FROM tl_product_attributes WHERE field_name='description'");
		$this->Database->execute("DELETE FROM tl_product_attributes WHERE field_name='tax_class'");
		$this->Database->execute("DELETE FROM tl_product_attributes WHERE field_name='main_image'");
		$this->Database->execute("DELETE FROM tl_product_attributes WHERE field_name='sku'");
		$this->Database->execute("DELETE FROM tl_product_attributes WHERE field_name='quantity'");
		$this->Database->execute("DELETE FROM tl_product_attributes WHERE field_name='shipping_exempt'");
		$this->Database->execute("DELETE FROM tl_product_attributes WHERE field_name='price'");
		$this->Database->execute("DELETE FROM tl_product_attributes WHERE field_name='price_override'");
		$this->Database->execute("DELETE FROM tl_product_attributes WHERE field_name='use_price_override'");
		$this->Database->execute("DELETE FROM tl_product_attributes WHERE field_name='weight'");
		
		// Because configuration has been changed to objects, we cannot use the existing cart data
		$this->Database->prepare("TRUNCATE TABLE tl_cart_items");
		$this->Database->prepare("TRUNCATE TABLE tl_cart");
	}
	
	
	private function renameFields()
	{
		// tl_module.iso_listingModules has been renamed to tl_module.iso_listingModule
		if ($this->Database->fieldExists('iso_listingModules', 'tl_module'))
		{
			$this->Database->execute("ALTER TABLE tl_module CHANGE COLUMN iso_listingModules iso_listingModule varchar(32) NOT NULL default ''");
		}
		
		// tl_store.store_configuration_name has been renamed to tl_store.name
		if ($this->Database->fieldExists('store_configuration_name', 'tl_store'))
		{
			$this->Database->execute("ALTER TABLE tl_store CHANGE COLUMN store_configuration_name name varchar(255) NOT NULL default ''");
		}
		
		// tl_store.gallery_thumbnail_image_width has been renamed to tl_store.gallery_image_width
		if ($this->Database->fieldExists('gallery_thumbnail_image_width', 'tl_store'))
		{
			$this->Database->execute("ALTER TABLE tl_store CHANGE COLUMN gallery_thumbnail_image_width gallery_image_width int(10) unsigned NOT NULL default '0'");
		}
		
		// tl_store.gallery_thumbnail_image_height has been renamed to tl_store.gallery_image_height
		if ($this->Database->fieldExists('gallery_thumbnail_image_height', 'tl_store'))
		{
			$this->Database->execute("ALTER TABLE tl_store CHANGE COLUMN gallery_thumbnail_image_height gallery_image_height int(10) unsigned NOT NULL default '0'");
		}
		
		// tl_product_data.visiblity has been renamed to tl_product_data.published
		if ($this->Database->fieldExists('visibility', 'tl_product_data'))
		{
			$this->Database->execute("ALTER TABLE tl_product_data CHANGE COLUMN visibility published char(1) NOT NULL default ''");
		}
		
		// tl_product_attributes.fieldGroup has been renamed to tl_product_attributes.legend
		if ($this->Database->fieldExists('fieldGroup', 'tl_product_attributes'))
		{
			$this->Database->execute("ALTER TABLE tl_product_attributes CHANGE COLUMN fieldGroup legend varchar(255) NOT NULL default ''");
		}
		
		// tl_product_date.main_image has been renamed to tl_product_data.images
		if ($this->Database->fieldExists('main_image', 'tl_product_data'))
		{
			$this->Database->execute("ALTER TABLE tl_product_data CHANGE COLUMN main_image images blob NULL");
		}
		
		// tl_address_book.state has been renamed to tl_address_book.subdivision
		if ($this->Database->fieldExists('state', 'tl_address_book') && !$this->Database->fieldExists('subdivision', 'tl_address_book'))
		{
			$this->Database->execute("ALTER TABLE tl_address_book CHANGE COLUMN state subdivision varchar(10) NOT NULL default ''");
		}
		
		// tl_store.state has been renamed to tl_store.subdivision
		if ($this->Database->fieldExists('state', 'tl_store') && !$this->Database->fieldExists('subdivision', 'tl_store'))
		{
			$this->Database->execute("ALTER TABLE tl_store CHANGE COLUMN state subdivision varchar(10) NOT NULL default ''");
		}
		
		// tl_store.street has been renamed to tl_store.street_1
		if ($this->Database->fieldExists('street', 'tl_store') && !$this->Database->fieldExists('street_1', 'tl_store'))
		{
			$this->Database->execute("ALTER TABLE tl_store CHANGE COLUMN street street_1 varchar(255) NOT NULL default ''");
		}
		
		// tl_address_book.street has been renamed to tl_address_book.street_1
		if ($this->Database->fieldExists('street', 'tl_address_book') && !$this->Database->fieldExists('street_1','tl_address_book'))
		{
			$this->Database->execute("ALTER TABLE tl_address_book CHANGE COLUMN street street_1 varchar(255) NOT NULL default ''");
			$this->Database->execute("ALTER TABLE tl_store CHANGE COLUMN street street_1 varchar(255) NOT NULL default ''");
			$objStores = $this->Database->execute("SELECT * FROM tl_store");
			
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
				
				$this->Database->prepare("UPDATE tl_store SET shipping_fields=?, billing_fields=? WHERE id=?")->execute(serialize($arrShipping), serialize($arrBilling), $objStores->id);
			}
		}
	}
	
	
	/**
	 * Checkboxes should be '' not '0'
	 */
	private function updateAttributes()
	{
		$arrFields = $this->Database->listFields('tl_product_attributes');
		foreach( $arrFields as $field )
		{
			$this->Database->execute("UPDATE tl_product_attributes SET " . $field['name'] . "='' WHERE " . $field['name'] . "='0'");
		}
	}
	
	private function updateProductCategories()
	{
		if ($this->Database->tableExists('tl_product_to_category'))
		{
			$this->Database->execute("CREATE TABLE IF NOT EXISTS `tl_product_categories` (`id` int(10) unsigned NOT NULL auto_increment,`pid` int(10) unsigned NOT NULL default '0',`tstamp` int(10) unsigned NOT NULL default '0',`page_id` int(10) unsigned NOT NULL default '0',PRIMARY KEY  (`id`),KEY `pid` (`pid`)) ENGINE=MyISAM DEFAULT CHARSET=utf8;");
			
			$this->Database->execute("INSERT INTO tl_product_categories (pid,tstamp,page_id) (SELECT product_id AS pid, tstamp, pid AS page_id FROM tl_product_to_category)");
			
			$this->Database->execute("DROP TABLE tl_product_to_category");
		}
	}
	
	private function updateStoreConfigurations()
	{
		if ($this->Database->fieldExists('countries', 'tl_store') && !$this->Database->fieldExists('shipping_countries','tl_store'))
		{
			$this->Database->execute("ALTER TABLE tl_store CHANGE COLUMN countries shipping_countries blob NULL");
			$this->Database->execute("ALTER TABLE tl_store ADD COLUMN billing_countries blob NULL");
			$this->Database->prepare("UPDATE tl_store SET billing_countries=shipping_countries");
		}
		
		if ($this->Database->fieldExists('address_fields', 'tl_store') && !$this->Database->fieldExists('shipping_fields','tl_store'))
		{
			$this->Database->execute("ALTER TABLE tl_store CHANGE COLUMN address_fields shipping_fields blob NULL");
			$this->Database->execute("ALTER TABLE tl_store ADD COLUMN billing_fields blob NULL");
			$this->Database->prepare("UPDATE tl_store SET billing_fields=shipping_fields");
		}
	}
	
	
	private function updateProductOptions()
	{
		if ($this->Database->fieldExists('product_options', 'tl_iso_order_items'))
		{
			$objItems = $this->Database->execute("SELECT * FROM tl_iso_order_items");
			
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
	}
	
	
	private function updateImageSizes()
	{
		$arrUpdate = array();
		
		if ($this->Database->fieldExists('gallery_image_width', 'tl_store') && $this->Database->fieldExists('gallery_image_height', 'tl_store'))
		{
			if (!$this->Database->fieldExists('gallery_size', 'tl_store'))
			{
				$this->Database->execute("ALTER TABLE tl_store ADD COLUMN gallery_size varchar(64) NOT NULL default ''");
			}
			
			$arrUpdate[] = 'gallery';
		}
		
		if ($this->Database->fieldExists('thumbnail_image_width', 'tl_store') && $this->Database->fieldExists('thumbnail_image_height', 'tl_store'))
		{
			if (!$this->Database->fieldExists('thumbnail_size', 'tl_store'))
			{
				$this->Database->execute("ALTER TABLE tl_store ADD COLUMN thumbnail_size varchar(64) NOT NULL default ''");
			}
			
			$arrUpdate[] = 'thumbnail';
		}
		
		if ($this->Database->fieldExists('medium_image_width', 'tl_store') && $this->Database->fieldExists('medium_image_height', 'tl_store'))
		{
			if (!$this->Database->fieldExists('medium_size', 'tl_store'))
			{
				$this->Database->execute("ALTER TABLE tl_store ADD COLUMN medium_size varchar(64) NOT NULL default ''");
			}
			
			$arrUpdate[] = 'medium';
		}
		
		if ($this->Database->fieldExists('large_image_width', 'tl_store') && $this->Database->fieldExists('large_image_height', 'tl_store'))
		{
			if (!$this->Database->fieldExists('large_size', 'tl_store'))
			{
				$this->Database->execute("ALTER TABLE tl_store ADD COLUMN large_size varchar(64) NOT NULL default ''");
			}
			
			$arrUpdate[] = 'large';
		}
		
		
		if (count($arrUpdate))
		{
			$objStores = $this->Database->execute("SELECT * FROM tl_store");
			
			while( $objStores->next() )
			{
				$arrSet = array();
				
				foreach( $arrUpdate as $size )
				{
					$arrSet[$size.'_size'] = serialize(array($objStores->{$size.'_image_width'}, $objStores->{$size.'_image_height'}, 'crop'));
				}
				
				$this->Database->prepare("UPDATE tl_store %s WHERE id=?")->set($arrSet)->execute($objStores->id);
			}
			
			foreach( $arrUpdate as $size )
			{
				// Do not use multiple DROP COLUMN in one ALTER TABLE. It is supported by MySQL, but not standard SQL92
				$this->Database->execute("ALTER TABLE tl_store DROP COLUMN ".$size."_image_width");
				$this->Database->execute("ALTER TABLE tl_store DROP COLUMN ".$size."_image_height");
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
		
		$objAttributes = $this->Database->execute("SELECT * FROM tl_product_attributes");
		
		while( $objAttributes->next() )
		{
			$this->IsotopeDatabase->add($objAttributes->field_name, $GLOBALS['ISO_ATTR'][$objAttributes->type]['sql']);
		}
	}
}


/**
 * Instantiate controller
 */
$objIsotope = new IsotopeRunonce();
$objIsotope->run();

