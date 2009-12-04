<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');

/**
 * TYPOlight webCMS
 * Copyright (C) 2005 Leo Feyer
 *
 * This program is free software: you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation, either
 * version 2.1 of the License, or (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 * 
 * You should have received a copy of the GNU Lesser General Public
 * License along with this program. If not, please visit the Free
 * Software Foundation website at http://www.gnu.org/licenses/.
 *
 * PHP version 5
 * @copyright  Winans Creative 2009
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
			
		$this->insertDefaultAttributeTypes();
		$this->renameFields();
		$this->updateAttributes();
		$this->updateProductCategories();
		
		// Checkout method has been renamed from "login" to "member" to prevent a problem with palette of the login module
		$this->Database->execute("UPDATE tl_module SET iso_checkout_method='member' WHERE iso_checkout_method='login'");
		
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
	
	
	private function insertDefaultAttributeTypes()
	{
		$objAttributeTypes = $this->Database->execute("SELECT type FROM tl_product_attribute_types");
		
		if($objAttributeTypes->numRows < 1)
		{
			$this->Database->execute("INSERT INTO `tl_product_attribute_types` (`id`, `pid`, `sorting`, `tstamp`, `type`, `attr_datatype`, `inputType`, `eval`, `name`) VALUES
	(1, 0, 128, 1218221789, 'text', 'varchar', 'text', '', ''),(2, 0, 256, 1218221789, 'integer', 'int', 'text', '', ''),(3, 0, 384, 1218221789, 'decimal', 'decimal', 'text', '', ''),(4, 0, 512, 1218221789, 'longtext', 'text', 'textarea', '', ''),(5, 0, 640, 1218221789, 'datetime', 'datetime', 'text', '', ''),(6, 0, 768, 1218221789, 'select', 'options', 'select', '', ''),(7, 0, 896, 1218221789, 'checkbox', 'options', 'checkbox', '', ''),(8, 0, 1024, 1218221789, 'options', 'options', 'radio', '', ''),(9, 0, 1152, 1218221789, 'file', 'varchar', 'fileTree', '', ''),(10, 0, 1280, 1218221789, 'media', 'varchar', 'imageManager', '', ''),(11, 0, 150, 1218221789, 'shorttext', 'varchar', 'text', '', '')");	
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
}


/**
 * Instantiate controller
 */
$objIsotope = new IsotopeRunonce();
$objIsotope->run();

