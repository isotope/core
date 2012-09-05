<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');

/**
 * Contao Open Source CMS
 * Copyright (C) 2005-2010 Leo Feyer
 *
 * Formerly known as TYPOlight Open Source CMS.
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
 * @copyright  Isotope eCommerce Workgroup 2009-2012
 * @author     Andreas Schempp <andreas@schempp.ch>
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */


class IsotopeRunonce extends Controller
{

	/**
	 * Initialize the object
	 */
	public function __construct()
	{
		parent::__construct();

		// Fix potential Exception on line 0 because of __destruct method (see http://dev.contao.org/issues/2236)
		$this->import((TL_MODE=='BE' ? 'BackendUser' : 'FrontendUser'), 'User');
		$this->import('Database');
	}


	/**
	 * Run the controller
	 */
	public function run()
	{
		// Cancel if shop has not yet been installed
		if (!$this->Database->tableExists('tl_iso_config') && !$this->Database->tableExists('tl_store'))
			return;

		$this->exec('createIsotopeFolder');
		$this->exec('renameTables');
		$this->exec('renameFields');
		$this->exec('updateStoreConfigurations');
		$this->exec('updateOrders');
		$this->exec('initializeOrderStatus');
		$this->exec('updateImageSizes');
		$this->exec('updateAttributes');
		$this->exec('updateFrontendModules');
		$this->exec('updateFrontendTemplates');
		$this->exec('updateProductTypes');
		$this->exec('updateRules');
		$this->exec('generateCategoryGroups');
		$this->exec('createGroupForAllNonAssignedProducts');

		// Make sure file extension .imt (Isotope Mail Template) is allowed for up- and download
		if (!in_array('imt', trimsplit(',', $GLOBALS['TL_CONFIG']['uploadTypes'])))
		{
			$this->Config->update('$GLOBALS[\'TL_CONFIG\'][\'uploadTypes\']', $GLOBALS['TL_CONFIG']['uploadTypes'].',imt');
		}

		// Just make sure no variant or translation has any categories assigned
		$this->Database->query("DELETE FROM tl_iso_product_categories WHERE pid IN (SELECT id FROM tl_iso_products WHERE pid>0)");

		// Delete caches
		$this->Database->query("TRUNCATE TABLE tl_iso_productcache");
		$this->Database->query("TRUNCATE TABLE tl_iso_requestcache");
	}


	private function exec($function)
	{
		try
		{
			$this->$function();
		}
		catch (Exception $e)
		{
echo '
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Contao Open Source CMS</title>
<style media="screen">
div { width:520px; margin:64px auto 18px; padding:24px; background:#ffc; border:1px solid #fc0; font-family:Verdana,sans-serif; font-size:13px; }
h1 { font-size:18px; font-weight:normal; margin:0 0 18px; }
</style>
</head>
<body>

<div>

<h1>Isotope eCommerce Update was not run successfully!</h1>

<pre style="white-space:normal">' . $e->getMessage() . '</pre>

</div>

</body>
</html>
';
			exit;
		}
	}


	/**
	 * Creates the isotope media folder if it doesn't exist yet
	 */
	private function createIsotopeFolder()
	{
		// delete the "isotope" folder from ER2 database table so they don't get deleted while updating or uninstalling Isotope.
		// IMPORTANT: don't remove the TL_ROOT/isotope directory from the ER package
		$this->Database->query("DELETE FROM tl_repository_instfiles WHERE filename='isotope' OR filename='isotope/index.html'");
	}


	private function renameTables()
	{
		if ($this->Database->tableExists('tl_product_data')) $this->Database->query("ALTER TABLE tl_product_data RENAME tl_iso_products");
		if ($this->Database->tableExists('tl_product_types')) $this->Database->query("ALTER TABLE tl_product_types RENAME tl_iso_producttypes");
		if ($this->Database->tableExists('tl_product_attributes')) $this->Database->query("ALTER TABLE tl_product_attributes RENAME tl_iso_attributes");
		if ($this->Database->tableExists('tl_product_downloads')) $this->Database->query("ALTER TABLE tl_product_downloads RENAME tl_iso_downloads");
		if ($this->Database->tableExists('tl_product_categories')) $this->Database->query("ALTER TABLE tl_product_categories RENAME tl_iso_product_categories");
		if ($this->Database->tableExists('tl_tax_class')) $this->Database->query("ALTER TABLE tl_tax_class RENAME tl_iso_tax_class");
		if ($this->Database->tableExists('tl_tax_rate')) $this->Database->query("ALTER TABLE tl_tax_rate RENAME tl_iso_tax_rate");
		if ($this->Database->tableExists('tl_payment_modules')) $this->Database->query("ALTER TABLE tl_payment_modules RENAME tl_iso_payment_modules");
		if ($this->Database->tableExists('tl_shipping_modules')) $this->Database->query("ALTER TABLE tl_shipping_modules RENAME tl_iso_shipping_modules");
		if ($this->Database->tableExists('tl_shipping_options')) $this->Database->query("ALTER TABLE tl_shipping_options RENAME tl_iso_shipping_options");
		if ($this->Database->tableExists('tl_related_categories')) $this->Database->query("ALTER TABLE tl_related_categories RENAME tl_iso_related_categories");
		if ($this->Database->tableExists('tl_related_products')) $this->Database->query("ALTER TABLE tl_related_products RENAME tl_iso_related_products");
		if ($this->Database->tableExists('tl_address_book')) $this->Database->query("ALTER TABLE tl_address_book RENAME tl_iso_addresses");
		if ($this->Database->tableExists('tl_store')) $this->Database->query("ALTER TABLE tl_store RENAME tl_iso_config");
		if ($this->Database->tableExists('tl_cart')) $this->Database->query("ALTER TABLE tl_cart RENAME tl_iso_cart");
		if ($this->Database->tableExists('tl_cart_items')) $this->Database->query("ALTER TABLE tl_cart_items RENAME tl_iso_cart_items");
	}


	private function renameFields()
	{
		// tl_iso_config.store_configuration_name has been renamed to tl_iso_config.name
		if ($this->Database->fieldExists('store_configuration_name', 'tl_iso_config'))
		{
			$this->Database->query("ALTER TABLE tl_iso_config CHANGE COLUMN store_configuration_name name varchar(255) NOT NULL default ''");
		}

		// tl_iso_config.gallery_thumbnail_image_width has been renamed to tl_iso_config.gallery_image_width
		if ($this->Database->fieldExists('gallery_thumbnail_image_width', 'tl_iso_config'))
		{
			$this->Database->query("ALTER TABLE tl_iso_config CHANGE COLUMN gallery_thumbnail_image_width gallery_image_width int(10) unsigned NOT NULL default '0'");
		}

		// tl_iso_config.gallery_thumbnail_image_height has been renamed to tl_iso_config.gallery_image_height
		if ($this->Database->fieldExists('gallery_thumbnail_image_height', 'tl_iso_config'))
		{
			$this->Database->query("ALTER TABLE tl_iso_config CHANGE COLUMN gallery_thumbnail_image_height gallery_image_height int(10) unsigned NOT NULL default '0'");
		}

		// tl_iso_products.visiblity has been renamed to tl_iso_products.published
		if ($this->Database->fieldExists('visibility', 'tl_iso_products'))
		{
			$this->Database->query("ALTER TABLE tl_iso_products CHANGE COLUMN visibility published char(1) NOT NULL default ''");
		}

		// tl_product_data.main_image has been renamed to tl_iso_products.images
		if ($this->Database->fieldExists('main_image', 'tl_iso_products'))
		{
			$this->Database->query("ALTER TABLE tl_iso_products CHANGE COLUMN main_image images blob NULL");
		}

		// tl_iso_attributes.fieldGroup has been renamed to tl_iso_attributes.legend
		if ($this->Database->fieldExists('fieldGroup', 'tl_iso_attributes'))
		{
			$this->Database->query("ALTER TABLE tl_iso_attributes CHANGE COLUMN fieldGroup legend varchar(255) NOT NULL default ''");
		}

		// tl_iso_attributes.is_required has been renamed to tl_iso_attributes.mandatory
		if ($this->Database->fieldExists('is_required', 'tl_iso_attributes'))
		{
			$this->Database->query("ALTER TABLE tl_iso_attributes CHANGE COLUMN is_required mandatory char(1) NOT NULL default ''");
		}

		// tl_iso_attributes.is_required has been renamed to tl_iso_attributes.mandatory
		if (in_array('isotope_imageselect', $this->Config->getActiveModules()) && $this->Database->fieldExists('size', 'tl_iso_attributes') && !$this->Database->fieldExists('imgSize', 'tl_iso_attributes'))
		{
			$this->Database->query("ALTER TABLE tl_iso_attributes CHANGE COLUMN size imgSize varchar(64) NOT NULL default ''");
		}

		// tl_iso_attributes.is_multiple_select has been renamed to tl_iso_attributes.multiple
		if ($this->Database->fieldExists('is_multiple_select', 'tl_iso_attributes'))
		{
			$this->Database->query("ALTER TABLE tl_iso_attributes CHANGE COLUMN is_multiple_select multiple char(1) NOT NULL default ''");
		}

		// tl_iso_attributes.add_to_product_variants has been renamed to tl_iso_attributes.variant_option
		if ($this->Database->fieldExists('add_to_product_variants', 'tl_iso_attributes'))
		{
			$this->Database->query("ALTER TABLE tl_iso_attributes CHANGE COLUMN add_to_product_variants variant_option char(1) NOT NULL default ''");
		}

		// tl_iso_attributes.use_rich_text_editor has been renamed to tl_iso_attributes.rte
		if ($this->Database->fieldExists('use_rich_text_editor', 'tl_iso_attributes'))
		{
			$this->Database->query("ALTER TABLE tl_iso_attributes CHANGE COLUMN use_rich_text_editor rte varchar(255) NOT NULL default ''");
			$this->Database->query("UPDATE tl_iso_attributes SET rte='tinyMCE' WHERE rte='1'");
		}

		// tl_iso_attributes.option_list has been renamed to tl_iso_attributes.options
		if ($this->Database->fieldExists('option_list', 'tl_iso_attributes'))
		{
			$this->Database->query("ALTER TABLE tl_iso_attributes CHANGE COLUMN option_list options blob NULL");
		}

		// tl_iso_attributes.is_be_filterable has been renamed to tl_iso_attributes.be_filter
		if ($this->Database->fieldExists('is_be_filterable', 'tl_iso_attributes') && !$this->Database->fieldExists('be_filter', 'tl_iso_attributes'))
		{
			$this->Database->query("ALTER TABLE tl_iso_attributes CHANGE COLUMN is_be_filterable be_filter char(1) NOT NULL default ''");
		}

		// tl_iso_attributes.is_filterable has been renamed to tl_iso_attributes.fe_filter
		if ($this->Database->fieldExists('is_filterable', 'tl_iso_attributes') && !$this->Database->fieldExists('fe_filter', 'tl_iso_attributes'))
		{
			$this->Database->query("ALTER TABLE tl_iso_attributes CHANGE COLUMN is_filterable fe_filter char(1) NOT NULL default ''");
		}

		// tl_iso_attributes.is_searchable has been renamed to tl_iso_attributes.fe_search
		if ($this->Database->fieldExists('is_searchable', 'tl_iso_attributes') && !$this->Database->fieldExists('fe_search', 'tl_iso_attributes'))
		{
			$this->Database->query("ALTER TABLE tl_iso_attributes CHANGE COLUMN is_searchable fe_search char(1) NOT NULL default ''");
		}

		// tl_iso_attributes.is_order_by_enabled has been renamed to tl_iso_attributes.fe_sorting
		if ($this->Database->fieldExists('is_order_by_enabled', 'tl_iso_attributes') && !$this->Database->fieldExists('fe_sorting', 'tl_iso_attributes'))
		{
			$this->Database->query("ALTER TABLE tl_iso_attributes CHANGE COLUMN is_order_by_enabled fe_sorting char(1) NOT NULL default ''");
		}

		// tl_iso_attributes.is_customer_defined has been renamed to tl_iso_attributes.customer_defined
		if ($this->Database->fieldExists('is_customer_defined', 'tl_iso_attributes') && !$this->Database->fieldExists('customer_defined', 'tl_iso_attributes'))
		{
			$this->Database->query("ALTER TABLE tl_iso_attributes CHANGE COLUMN is_customer_defined customer_defined char(1) NOT NULL default ''");
		}

		// tl_iso_attributes.is_be_searchable has been renamed to tl_iso_attributes.be_search
		if ($this->Database->fieldExists('is_be_searchable', 'tl_iso_attributes') && !$this->Database->fieldExists('be_search', 'tl_iso_attributes'))
		{
			$this->Database->query("ALTER TABLE tl_iso_attributes CHANGE COLUMN is_be_searchable be_search char(1) NOT NULL default ''");
		}

		// tl_iso_addresses.state has been renamed to tl_iso_addresses.subdivision
		if ($this->Database->fieldExists('state', 'tl_iso_addresses') && !$this->Database->fieldExists('subdivision', 'tl_iso_addresses'))
		{
			$this->Database->query("ALTER TABLE tl_iso_addresses CHANGE COLUMN state subdivision varchar(10) NOT NULL default ''");
		}

		// tl_iso_config.state has been renamed to tl_iso_config.subdivision
		if ($this->Database->fieldExists('state', 'tl_iso_config') && !$this->Database->fieldExists('subdivision', 'tl_iso_config'))
		{
			$this->Database->query("ALTER TABLE tl_iso_config CHANGE COLUMN state subdivision varchar(10) NOT NULL default ''");
		}

		// tl_iso_config.street has been renamed to tl_iso_config.street_1
		if ($this->Database->fieldExists('street', 'tl_iso_config') && !$this->Database->fieldExists('street_1', 'tl_iso_config'))
		{
			$this->Database->query("ALTER TABLE tl_iso_config CHANGE COLUMN street street_1 varchar(255) NOT NULL default ''");
		}

		// tl_module.store_id has been renamed to tl_module.iso_config_id
		if ($this->Database->fieldExists('store_id', 'tl_module') && !$this->Database->fieldExists('iso_config_id', 'tl_module'))
		{
			$this->Database->query("ALTER TABLE tl_module CHANGE COLUMN store_id iso_config_id int(10) unsigned NOT NULL default '0'");
		}

		// tl_module.store_ids has been renamed to tl_module.iso_config_ids
		if ($this->Database->fieldExists('store_ids', 'tl_module') && !$this->Database->fieldExists('iso_config_ids', 'tl_module'))
		{
			$this->Database->query("ALTER TABLE tl_module CHANGE COLUMN store_ids iso_config_ids blob NULL");
		}

		// tl_module.columns has been renamed to tl_module.iso_cols
		if ($this->Database->fieldExists('columns', 'tl_module') && !$this->Database->fieldExists('iso_cols', 'tl_module'))
		{
			$this->Database->query("ALTER TABLE tl_module CHANGE COLUMN columns iso_cols int(1) unsigned NOT NULL default '1'");
		}

		// tl_module.iso_orderByFields has been renamed to tl_module.iso_sortingFields
		if ($this->Database->fieldExists('iso_orderByFields', 'tl_module') && !$this->Database->fieldExists('iso_sortingFields', 'tl_module'))
		{
			$this->Database->query("ALTER TABLE tl_module CHANGE COLUMN iso_orderByFields iso_sortingFields int(1) unsigned NOT NULL default '1'");
		}

		// tl_module.iso_perPage has been added
		if (!$this->Database->fieldExists('iso_perPage', 'tl_module'))
		{
			$this->Database->query("ALTER TABLE tl_module ADD COLUMN iso_perPage varchar(64) NOT NULL default ''");
			$this->Database->query("UPDATE tl_module SET iso_perPage='8,12,32,64'");
		}

		// tl_module.iso_forceNoProducts has renamed to tl_module.iso_emptyMessage
		if ($this->Database->fieldExists('iso_forceNoProducts', 'tl_module') && !$this->Database->fieldExists('iso_emptyMessage', 'tl_module'))
		{
			$this->Database->query("ALTER TABLE tl_module CHANGE COLUMN iso_forceNoProducts iso_emptyMessage char(1) NOT NULL default ''");
			$this->Database->query("UPDATE tl_module SET iso_emptyMessage='1' WHERE iso_noProducts!=''");
		}

		// tl_module.iso_listingModule has been removed
		if ($this->Database->fieldExists('iso_listingModule', 'tl_module'))
		{
			if (!$this->Database->fieldExists('iso_filterModules', 'tl_module'))
			{
				$this->Database->query("ALTER TABLE tl_module ADD COLUMN iso_filterModules blob NULL");
			}

			if ($this->Database->fieldExists('iso_category_scope', 'tl_module'))
			{
				$this->Database->query("UPDATE tl_module m1 SET iso_category_scope=(SELECT iso_category_scope FROM (SELECT * FROM tl_module) m2 WHERE m2.id=m1.iso_listingModule) WHERE m1.type='iso_productfilter'");
			}

			$objModules = $this->Database->query("SELECT iso_listingModule, GROUP_CONCAT(id) AS ids FROM tl_module WHERE iso_listingModule>0 GROUP BY iso_listingModule");

			while( $objModules->next() )
			{
				$this->Database->query("UPDATE tl_module SET iso_filterModules='" . serialize(explode(',', $objModules->ids)) . "' WHERE id=" . $objModules->iso_listingModule);
			}

			$this->Database->query("ALTER TABLE tl_module DROP COLUMN iso_listingModule");
		}

		// tl_iso_orders.store_id has been renamed to tl_iso_orders.config_id
		if ($this->Database->fieldExists('store_id', 'tl_iso_orders') && !$this->Database->fieldExists('config_id', 'tl_iso_orders'))
		{
			$this->Database->query("ALTER TABLE tl_iso_orders CHANGE COLUMN store_id config_id int(10) unsigned NOT NULL default '0'");
		}

		// tl_iso_orders.date_payed has been renamed to tl_iso_orders.date_paid
		if ($this->Database->fieldExists('date_payed', 'tl_iso_orders') && !$this->Database->fieldExists('date_paid', 'tl_iso_orders'))
		{
			$this->Database->query("ALTER TABLE tl_iso_orders CHANGE COLUMN date_payed date_paid varchar(10) NOT NULL default ''");
		}

		// tl_iso_config.isDefaultStore has been renamed to tl_iso_config.fallback
		if ($this->Database->fieldExists('isDefaultStore', 'tl_iso_config') && !$this->Database->fieldExists('fallback', 'tl_iso_config'))
		{
			$this->Database->query("ALTER TABLE tl_iso_config CHANGE COLUMN isDefaultStore fallback char(1) NOT NULL default ''");
		}

		// tl_iso_config.emailShipping has been renamed to tl_iso_config.email
		if ($this->Database->fieldExists('emailShipping', 'tl_iso_config') && !$this->Database->fieldExists('email', 'tl_iso_config'))
		{
			$this->Database->query("ALTER TABLE tl_iso_config CHANGE COLUMN emailShipping email varchar(255) NOT NULL default ''");
		}

		// tl_user.iso_stores has been renamed to tl_user.iso_configs
		if ($this->Database->fieldExists('iso_stores', 'tl_user') && !$this->Database->fieldExists('iso_configs', 'tl_user'))
		{
			$this->Database->query("ALTER TABLE tl_user CHANGE COLUMN iso_stores iso_configs blob NULL");
		}

		// tl_user_group.iso_stores has been renamed to tl_user_group.iso_configs
		if ($this->Database->fieldExists('iso_stores', 'tl_user_group') && !$this->Database->fieldExists('iso_configs', 'tl_user_group'))
		{
			$this->Database->query("ALTER TABLE tl_user_group CHANGE COLUMN iso_stores iso_configs blob NULL");
		}

		// tl_iso_tax_rate.store has been renamed to tl_iso_tax_rate.config
		if ($this->Database->fieldExists('store', 'tl_iso_tax_rate') && !$this->Database->fieldExists('config', 'tl_iso_tax_rate'))
		{
			$this->Database->query("ALTER TABLE tl_iso_tax_rate CHANGE COLUMN store config int(10) unsigned NOT NULL default '0'");
		}

		// tl_page.isotopeStoreConfig has been renamed to tl_page.iso_config
		if ($this->Database->fieldExists('isotopeStoreConfig', 'tl_page') && !$this->Database->fieldExists('iso_config', 'tl_page'))
		{
			$this->Database->query("ALTER TABLE tl_page CHANGE COLUMN isotopeStoreConfig iso_config int(10) unsigned NOT NULL default '0'");
		}

		// tl_iso_cart_items.quantity_requested has been renamed to tl_iso_cart_items.product_quantity
		if ($this->Database->fieldExists('quantity_requested', 'tl_iso_cart_items') && !$this->Database->fieldExists('product_quantity', 'tl_iso_cart_items'))
		{
			$this->Database->query("ALTER TABLE tl_iso_cart_items CHANGE COLUMN quantity_requested product_quantity int(10) unsigned NOT NULL default '0'");
		}

		// tl_iso_order_items.quantity_sold has been renamed to tl_iso_order_items.product_quantity
		if ($this->Database->fieldExists('quantity_sold', 'tl_iso_order_items') && !$this->Database->fieldExists('product_quantity', 'tl_iso_order_items'))
		{
			$this->Database->query("ALTER TABLE tl_iso_order_items CHANGE COLUMN quantity_sold product_quantity int(10) unsigned NOT NULL default '0'");
		}

		// tl_iso_addresses.street has been renamed to tl_iso_addresses.street_1
		if ($this->Database->fieldExists('street', 'tl_iso_addresses') && !$this->Database->fieldExists('street_1','tl_iso_addresses'))
		{
			$this->Database->query("ALTER TABLE tl_iso_addresses CHANGE COLUMN street street_1 varchar(255) NOT NULL default ''");
			$this->Database->query("ALTER TABLE tl_iso_config CHANGE COLUMN street street_1 varchar(255) NOT NULL default ''");
			$objStores = $this->Database->query("SELECT * FROM tl_iso_config");

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

		// "Orders without shipping" has been changed from value "0" to "-1"
		$objPayments = $this->Database->execute("SELECT * FROM tl_iso_payment_modules WHERE shipping_modules!=''");
		while( $objPayments->next() )
		{
			$arrShipping = deserialize($objPayments->shipping_modules);

			if (is_array($arrShipping) && in_array(0, $arrShipping))
			{
				unset($arrShipping[array_search(0, $arrShipping)]);
				$arrShipping[] = -1;
				$this->Database->query("UPDATE tl_iso_payment_modules SET shipping_modules='" . serialize($arrShipping) . "' WHERE id={$objPayments->id}");
			}
		}

		// tax rates dont have a range but freetext field for postal codes
		if ($this->Database->fieldExists('postal', 'tl_iso_tax_rate') && !$this->Database->fieldExists('postalCodes', 'tl_iso_tax_rate'))
		{
			$this->Database->query("ALTER TABLE tl_iso_tax_rate ADD COLUMN postalCodes text NULL");

			$objTaxRates = $this->Database->execute("SELECT * FROM tl_iso_tax_rate WHERE postal!=''");

			while( $objTaxRates->next() )
			{
				$arrCodes = deserialize($objTaxRates->postal);

				if (is_array($arrCodes) && $arrCodes[0] != '' && $arrCodes[1] != '')
				{
					$this->Database->query("UPDATE tl_iso_tax_rate SET postalCodes='{$arrCodes[0]}-{$arrCodes[1]}' WHERE id=" . $objTaxRates->id);
				}
			}
		}
	}


	private function updateStoreConfigurations()
	{
		if ($this->Database->fieldExists('countries', 'tl_iso_config') && !$this->Database->fieldExists('shipping_countries','tl_iso_config'))
		{
			$this->Database->query("ALTER TABLE tl_iso_config CHANGE COLUMN countries shipping_countries blob NULL");
			$this->Database->query("ALTER TABLE tl_iso_config ADD COLUMN billing_countries blob NULL");
			$this->Database->query("UPDATE tl_iso_config SET billing_countries=shipping_countries");
		}

		if ($this->Database->fieldExists('address_fields', 'tl_iso_config') && !$this->Database->fieldExists('shipping_fields','tl_iso_config'))
		{
			$this->Database->query("ALTER TABLE tl_iso_config CHANGE COLUMN address_fields shipping_fields blob NULL");
			$this->Database->query("ALTER TABLE tl_iso_config ADD COLUMN billing_fields blob NULL");
			$this->Database->query("UPDATE tl_iso_config SET billing_fields=shipping_fields");
		}

		foreach( array('billing_country', 'shipping_country') as $field )
		{
			if (!$this->Database->fieldExists($field, 'tl_iso_config'))
			{
				$this->Database->query("ALTER TABLE tl_iso_config ADD COLUMN `" . $field . "` varchar(2) NOT NULL default ''");
				$this->Database->query("UPDATE tl_iso_config SET `" . $field . "`=country");
			}
		}

		$this->loadDataContainer('tl_iso_addresses');
		$objConfigs = $this->Database->execute("SELECT * FROM tl_iso_config");

		while( $objConfigs->next() )
		{
			$arrBilling = deserialize($objConfigs->billing_fields);
			$arrShipping = deserialize($objConfigs->shipping_fields);

			if (is_array($arrBilling) && count($arrBilling) && !is_array($arrBilling[0]))
			{
				$arrNew = array();

				foreach( $arrBilling as $field )
				{
					$arrNew[] = array('value'=>$field, 'enabled'=>1, 'mandatory'=>$GLOBALS['TL_DCA']['tl_iso_addresses']['fields'][$field]['eval']['mandatory']);
				}

				$this->Database->prepare("UPDATE tl_iso_config SET billing_fields=? WHERE id=?")->execute(serialize($arrNew), $objConfigs->id);
			}

			if (is_array($arrShipping) && count($arrShipping) && !is_array($arrShipping[0]))
			{
				$arrNew = array();

				foreach( $arrShipping as $field )
				{
					$arrNew[] = array('value'=>$field, 'enabled'=>1, 'mandatory'=>$GLOBALS['TL_DCA']['tl_iso_addresses']['fields'][$field]['eval']['mandatory']);
				}

				$this->Database->prepare("UPDATE tl_iso_config SET shipping_fields=? WHERE id=?")->execute(serialize($arrNew), $objConfigs->id);
			}
		}
	}


	private function updateOrders()
	{
		if (!$this->Database->fieldExists('date_shipped', 'tl_iso_orders'))
		{
			$this->Database->query("ALTER TABLE tl_iso_orders ADD COLUMN date_shipped varchar(10) NOT NULL default ''");
		}

		$this->Database->query("UPDATE tl_iso_orders SET date_shipped=date, status='processing' WHERE status='shipped'");

		// Fix for Ticket #383
		$this->Database->query("UPDATE tl_iso_order_downloads SET downloads_remaining='' WHERE downloads_remaining='-1'");
	}


	private function initializeOrderStatus()
	{
		if (!$this->Database->tableExists('tl_iso_orderstatus'))
		{
			$this->Database->query("
CREATE TABLE `tl_iso_orderstatus` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `pid` int(10) unsigned NOT NULL default '0',
  `tstamp` int(10) unsigned NOT NULL default '0',
  `sorting` int(10) unsigned NOT NULL default '0',
  `name` varchar(255) NOT NULL default '',
  `paid` char(1) NOT NULL default '',
  `welcomescreen` char(1) NOT NULL default '',
  `mail_customer` int(10) unsigned NOT NULL default '0',
  `mail_admin` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `pid` (`pid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;");

			$blnUpdate = true;
		}
		else
		{
			$objRecords = $this->Database->query("SELECT COUNT(id) AS total FROM tl_iso_orderstatus");
			$blnUpdate = $objRecords->total > 0 ? false : true;
		}

		if ($blnUpdate)
		{
			// Make sure the required fields are added in the store configuration
			if (!$this->Database->fieldExists('orderstatus_new', 'tl_iso_config'))
			{
				$this->Database->query("ALTER TABLE tl_iso_config ADD COLUMN orderstatus_new int(10) unsigned NOT NULL default '0'");
			}
			if (!$this->Database->fieldExists('orderstatus_error', 'tl_iso_config'))
			{
				$this->Database->query("ALTER TABLE tl_iso_config ADD COLUMN orderstatus_error int(10) unsigned NOT NULL default '0'");
			}

			$GLOBALS['TL_LANG']['ORDER']['pending']		= 'Pending';
			$GLOBALS['TL_LANG']['ORDER']['processing']	= 'Processing';
			$GLOBALS['TL_LANG']['ORDER']['complete']	= 'Complete';
			$GLOBALS['TL_LANG']['ORDER']['on_hold']		= 'On Hold';
			$GLOBALS['TL_LANG']['ORDER']['cancelled']	= 'Cancelled';

			$time = time();
			$arrStatus = array_unique(array_merge
			(
				array('pending', 'processing', 'complete', 'on_hold', 'cancelled'),
				$this->Database->execute("SELECT DISTINCT status FROM tl_iso_orders WHERE status!=''")->fetchEach('status'),
				$this->Database->execute("SELECT DISTINCT new_order_status FROM tl_iso_payment_modules WHERE new_order_status!=''")->fetchEach('new_order_status')
			));

			foreach( $arrStatus as $i => $status )
			{
				$strLabel = $GLOBALS['TL_LANG']['ORDER'][$status] == '' ? $status : $GLOBALS['TL_LANG']['ORDER'][$status];
				$intId = $this->Database->prepare("INSERT INTO tl_iso_orderstatus (tstamp,sorting,name,paid,welcomescreen) VALUES ($time,?,?,?,?)")->executeUncached(($i*128), $strLabel, ($status == 'complete' ? '1' : ''), ($status == 'pending' ? '1' : ''))->insertId;

				// Update existing store configurations
				if ($status == 'pending')
				{
					$this->Database->prepare("UPDATE tl_iso_config SET orderstatus_new=$intId WHERE orderstatus_new=0");
				}
				elseif ($status == 'on_hold')
				{
					$this->Database->prepare("UPDATE tl_iso_config SET orderstatus_error=$intId WHERE orderstatus_error=0");
				}

				$this->Database->prepare("UPDATE tl_iso_orders SET status=? WHERE status=?")->executeUncached($intId, $status);
				$this->Database->prepare("UPDATE tl_iso_payment_modules SET new_order_status=? WHERE new_order_status=?")->executeUncached($intId, $status);
			}
		}
	}


	private function updateAttributes()
	{
		// Renamed attribute types
		$this->Database->query("UPDATE tl_iso_attributes SET type='text', rgxp='date' WHERE type='datetime' AND rgxp=''");
		$this->Database->query("UPDATE tl_iso_attributes SET type='text', rgxp='digit' WHERE (type='integer' OR type='decimal') AND rgxp=''");
		$this->Database->query("UPDATE tl_iso_attributes SET type='text' WHERE type='integer' OR type='decimal' OR type='datetime'");
		$this->Database->query("UPDATE tl_iso_attributes SET type='textarea' WHERE type='longtext'");
		$this->Database->query("UPDATE tl_iso_attributes SET type='radio' WHERE type='options'");
		$this->Database->query("UPDATE tl_iso_attributes SET type='mediaManager' WHERE type='media'");

		if (!$this->Database->fieldExists('foreignKey', 'tl_iso_attributes'))
		{
			$this->Database->query("ALTER TABLE tl_iso_attributes ADD COLUMN foreignKey varchar(64) NOT NULL default ''");
			$this->Database->query("UPDATE tl_iso_attributes SET foreignKey=CONCAT(list_source_table, '.', list_source_field) WHERE use_alternate_source='1'");
		}

		// Update dateAdded for products
		if (!$this->Database->fieldExists('dateAdded', 'tl_iso_products'))
		{
			$this->Database->query("ALTER TABLE tl_iso_products ADD COLUMN dateAdded int(10) unsigned NOT NULL default '0'");
		}

		$this->Database->query("UPDATE tl_iso_products SET dateAdded=tstamp WHERE dateAdded=0");


		// Update attribute wizard
		$objTypes = $this->Database->execute("SELECT * FROM tl_iso_producttypes");

		while ($objTypes->next())
		{
			foreach (array('attributes', 'variant_attributes') as $field)
			{
				$arrAttributes = deserialize($objTypes->$field);

				if (is_array($arrAttributes) && !array_is_assoc($arrAttributes))
				{
					$arrNew = array();

					foreach ($arrAttributes as $i => $attribute)
					{
						$arrNew[$attribute]['enabled'] = '1';
						$arrNew[$attribute]['position'] = $i;
					}

					$this->Database->prepare("UPDATE tl_iso_producttypes SET $field=? WHERE id=?")
								   ->execute(serialize($arrNew), $objTypes->id);
				}
			}
		}
	}


	private function updateImageSizes()
	{
		$arrUpdate = array();

		if ($this->Database->fieldExists('gallery_image_width', 'tl_iso_config') && $this->Database->fieldExists('gallery_image_height', 'tl_iso_config'))
		{
			if (!$this->Database->fieldExists('gallery_size', 'tl_iso_config'))
			{
				$this->Database->query("ALTER TABLE tl_iso_config ADD COLUMN gallery_size varchar(64) NOT NULL default ''");
			}

			$arrUpdate[] = 'gallery';
		}

		if ($this->Database->fieldExists('thumbnail_image_width', 'tl_iso_config') && $this->Database->fieldExists('thumbnail_image_height', 'tl_iso_config'))
		{
			if (!$this->Database->fieldExists('thumbnail_size', 'tl_iso_config'))
			{
				$this->Database->query("ALTER TABLE tl_iso_config ADD COLUMN thumbnail_size varchar(64) NOT NULL default ''");
			}

			$arrUpdate[] = 'thumbnail';
		}

		if ($this->Database->fieldExists('medium_image_width', 'tl_iso_config') && $this->Database->fieldExists('medium_image_height', 'tl_iso_config'))
		{
			if (!$this->Database->fieldExists('medium_size', 'tl_iso_config'))
			{
				$this->Database->query("ALTER TABLE tl_iso_config ADD COLUMN medium_size varchar(64) NOT NULL default ''");
			}

			$arrUpdate[] = 'medium';
		}

		if ($this->Database->fieldExists('large_image_width', 'tl_iso_config') && $this->Database->fieldExists('large_image_height', 'tl_iso_config'))
		{
			if (!$this->Database->fieldExists('large_size', 'tl_iso_config'))
			{
				$this->Database->query("ALTER TABLE tl_iso_config ADD COLUMN large_size varchar(64) NOT NULL default ''");
			}

			$arrUpdate[] = 'large';
		}


		if (count($arrUpdate))
		{
			$objStores = $this->Database->query("SELECT * FROM tl_iso_config");

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
				$this->Database->query("ALTER TABLE tl_iso_config DROP COLUMN ".$size."_image_width");
				$this->Database->query("ALTER TABLE tl_iso_config DROP COLUMN ".$size."_image_height");
			}
		}


		if ($this->Database->fieldExists('gallery_size', 'tl_iso_config', true) && !$this->Database->fieldExists('imageSizes','tl_iso_config', true))
		{
			$this->Database->query("ALTER TABLE tl_iso_config ADD COLUMN imageSizes blob NULL");

			$objConfigs = $this->Database->execute("SELECT * FROM tl_iso_config");

			while( $objConfigs->next() )
			{
				$arrGallery = deserialize($objConfigs->gallery_size, true);
				$arrThumbnail = deserialize($objConfigs->thumbnail_size, true);
				$arrMedium = deserialize($objConfigs->medium_size, true);
				$arrLarge = deserialize($objConfigs->large_size, true);

				$arrSizes = array
				(
					array
					(
						'name'		=> 'gallery',
						'width'		=> $arrGallery[0],
						'height'	=> $arrGallery[1],
						'mode'		=> $arrGallery[2],
						'watermark'	=> $objConfigs->gallery_watermark,
						'position'	=> $objConfigs->watermark_position,
					),
					array
					(
						'name'		=> 'thumbnail',
						'width'		=> $arrThumbnail[0],
						'height'	=> $arrThumbnail[1],
						'mode'		=> $arrThumbnail[2],
						'watermark'	=> $objConfigs->thumbnail_watermark,
						'position'	=> $objConfigs->watermark_position,
					),
					array
					(
						'name'		=> 'medium',
						'width'		=> $arrMedium[0],
						'height'	=> $arrMedium[1],
						'mode'		=> $arrMedium[2],
						'watermark'	=> $objConfigs->medium_watermark,
						'position'	=> $objConfigs->watermark_position,
					),
					array
					(
						'name'		=> 'large',
						'width'		=> $arrLarge[0],
						'height'	=> $arrLarge[1],
						'mode'		=> $arrLarge[2],
						'watermark'	=> $objConfigs->large_watermark,
						'position'	=> $objConfigs->watermark_position,
					),
				);

				$this->Database->query("UPDATE tl_iso_config SET imageSizes='" . serialize($arrSizes) . "' WHERE id={$objConfigs->id}");
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

		// Add "name" and "description" to the list of search fields. Previously, they were enabled in the code directly
		if ($this->Database->fieldExists('iso_enableSearch', 'tl_module'))
		{
			$objFilterModules = $this->Database->query("SELECT * FROM tl_module WHERE iso_enableSearch='1'");
			while( $objFilterModules->next() )
			{
				$arrSearch = deserialize($objFilterModules->iso_searchFields);

				if (!is_array($arrSearch))
				{
					$arrSearch = array('name', 'description');
				}
				else
				{
					array_unshift($arrSearch, 'name', 'description');
				}

				$this->Database->prepare("UPDATE tl_module SET iso_enableSearch='', iso_searchFields=? WHERE id=?")->executeUncached(serialize($arrSearch), $objFilterModules->id);
			}
		}

		// Checkout method has been renamed from "login" to "member" to prevent a problem with palette of the login module
		$this->Database->query("UPDATE tl_module SET iso_checkout_method='member' WHERE iso_checkout_method='login'");
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
	}


	/**
	 * Update product types for new attribute wizard and make start&stop date enabled
	 */
	private function updateProductTypes()
	{
		$objTypes = $this->Database->execute("SELECT * FROM tl_iso_producttypes");

		while ($objTypes->next())
		{
			foreach (array('attributes', 'variant_attributes') as $field)
			{
				$arrAttributes = deserialize($objTypes->$field);

				if (is_array($arrAttributes) && !array_is_assoc($arrAttributes))
				{
					$arrNew = array();

					foreach ($arrAttributes as $i => $attribute)
					{
						$arrNew[$attribute]['enabled'] = '1';
						$arrNew[$attribute]['position'] = $i;
					}

					$arrNew['start']['enabled'] = '1';
					$arrNew['stop']['enabled'] = '1';

					$this->Database->prepare("UPDATE tl_iso_producttypes SET $field=? WHERE id=?")
								   ->execute(serialize($arrNew), $objTypes->id);
				}
			}
		}
	}


	/**
	 * tl_iso_rule.applyTo values had to be renamed for palettes to work
	 */
	private function updateRules()
	{
		if ($this->Database->tableExists('tl_iso_rules'))
		{
			$this->Database->query("UPDATE tl_iso_rules SET applyTo='products' WHERE applyTo='product'");
			$this->Database->query("UPDATE tl_iso_rules SET applyTo='items' WHERE applyTo='item'");
			$this->Database->query("UPDATE tl_iso_rules SET applyTo='subtotal' WHERE applyTo='cart'");
		}
	}


	/**
	 * Automatically generate product groups because the limit feature is no longer available.
	 * Taking product categories order by ID will associate with the page with highest ID, probably the deepest in the page tree.
	 */
	private function generateCategoryGroups()
	{
		if (!$this->Database->tableExists('tl_iso_groups') && $this->Database->query("SELECT COUNT(*) AS total FROM tl_iso_product_categories")->total > 0)
		{
			$this->Database->query("
CREATE TABLE `tl_iso_groups` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `pid` int(10) unsigned NOT NULL default '0',
  `sorting` int(10) unsigned NOT NULL default '0',
  `tstamp` int(10) unsigned NOT NULL default '0',
  `name` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `pid` (`pid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;");

			if (!$this->Database->fieldExists('gid', 'tl_iso_products'))
			{
				$this->Database->query("ALTER TABLE tl_iso_products ADD `gid` int(10) unsigned NOT NULL default '0'");
			}

			$arrCategories = array();
			$objCategories = $this->Database->execute("SELECT * FROM tl_iso_product_categories ORDER BY page_id ASC");

			while( $objCategories->next() )
			{
				$arrCategories[$objCategories->pid] = $objCategories->page_id;
			}

			$time = time();
			$intSorting = -128;
			$objPages = $this->Database->execute("SELECT * FROM tl_page WHERE id IN (" . implode(',', array_unique($arrCategories)) . ")");

			while( $objPages->next() )
			{
				$intSorting += 128;
				$intGroup = $this->Database->query("INSERT INTO tl_iso_groups (pid,sorting,tstamp,name) VALUES (0, $intSorting, $time, '{$objPages->title}')")->insertId;

				$arrProducts = array_keys(array_intersect($arrCategories, array($objPages->id)));
				$this->Database->query("UPDATE tl_iso_products SET gid=$intGroup WHERE id IN (" . implode(',', $arrProducts) . ")");
			}
		}
	}


	/**
	 * In Isotope 1.4 every product has to be assigned to a product groups o permissions work well
	 */
	private function createGroupForAllNonAssignedProducts()
	{
		$objNoGroupProducts = $this->Database->execute("SELECT id FROM tl_iso_products WHERE pid=0 AND language='' AND gid=0");

		if (!$objNoGroupProducts->numRows)
		{
			return;
		}

		// generate a group called "--GENERAL--"
		$intGroup = $this->Database->prepare('INSERT INTO tl_iso_groups (pid,sorting,tstamp,name) VALUES (?, ?, ?, ?)')
			->execute(0, 0, time(), '---GENERAL---')
			->insertId;

		// update
		$this->Database->prepare('UPDATE tl_iso_products SET gid=? WHERE id IN (' . implode(',', $objNoGroupProducts->fetchEach('id')) . ')')
			->execute($intGroup);
	}
}


/**
 * Instantiate controller
 */
$objIsotope = new IsotopeRunonce();
$objIsotope->run();

