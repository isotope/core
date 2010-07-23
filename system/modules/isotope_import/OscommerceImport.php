<?php

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
 * @copyright  Winans Creative 2010
 * @author     Blair Winans <http://www.winanscreative.com>
 * @package    Backend
 * @license    LGPL
 * @filesource
 */



/**
 * Class OscommerceImport
 *
 */
class OscommerceImport extends BackendModule
{
	
	/**
	 * Template
	 * @var string
	 */
	protected $strTemplate = 'be_import';
	
	/**
	 * Default Product Type
	 * Map the old products to a new default product type
	 * @todo - Make this a widget that allows you to map them via select dropdowns
	 */
	protected $defaultType = '6';	

	/**
	 * Generate module
	 */
	public function compile()
	{
		$this->loadLanguageFile('tl_import');

		$this->Template->importMessage = '';
		$this->Template->setOldDb = true;
		
		if ($this->Input->post('FORM_SUBMIT') == 'tl_import_setdb')
		{
			$_SESSION['TL_IMPORT']['olddb'] = $this->Input->post('import_db');
			$this->Template->oldDb = $this->Input->post('import_db');
			$this->Template->setOldDb = false;
			$this->Template->setCats = true;
			
		} 
		if ($this->Input->post('FORM_SUBMIT') == 'tl_import_setcats')
		{
			 $this->Template->setOldDb = false;
			 $this->Template->setCats = false;
			$this->Template->setConfirm = true;
			 $arrOptions = $this->Input->post('options');
			 $strCategories = '';
			 foreach($arrOptions as $option)
			 {
			 	$arrCategoryMap[$option['value']] = $option['label'];
			 	$strCategories .= '<tr><td>' . $option['value'] . '</td><td class="arrow">' . $option['label'] . '</td></tr>';
			 }
			 $_SESSION['TL_IMPORT']['arrCategoryMap'] = $arrCategoryMap;
			 $this->Template->oldDb = $_SESSION['TL_IMPORT']['olddb'];
			  $this->Template->categories = $strCategories;
				
		}
		if ($this->Input->post('FORM_SUBMIT') == 'tl_import_confirm')
		{
			$this->Template->readyForImport = true;
		}
		
		
		$this->Template->newoptions = str_replace('}}','', str_replace('{{link_url::','',$this->createPageList()));
		$this->Template->oldoptions = $this->createOldPageList();
				
		$this->Template->importHeadline = $GLOBALS['TL_LANG']['tl_import']['oscimport'];
		$this->Template->importSubmit = $GLOBALS['TL_LANG']['tl_import']['importSubmit'];
		$this->Template->href = $this->getReferer(true);
		$this->Template->title = specialchars($GLOBALS['TL_LANG']['MSC']['backBT']);
		$this->Template->action = ampersand($this->Environment->request, true);
		$this->Template->selectAll = $GLOBALS['TL_LANG']['MSC']['selectAll'];
		$this->Template->button = $GLOBALS['TL_LANG']['MSC']['backBT'];
	}
	
	public function startImport($strAction, $dc)
	{
		
		if ($strAction == 'startImport')
		{
			$method[0] = $this->Input->post('method');
			if($method[0]=='start' || $method[0]=='')
			{
				$method[0] = 'truncateTables';
			}
			$arrJson = array();
			$arrJson = $this->$method[0]();
			echo json_encode($arrJson);
		}
	}


	private function truncateTables()
	{		
		if ($this->Database->tableExists('tl_iso_tax_class')) $this->Database->executeUncached("TRUNCATE tl_iso_tax_class");
		if ($this->Database->tableExists('tl_iso_tax_rate')) $this->Database->executeUncached("TRUNCATE tl_iso_tax_rate");
		if ($this->Database->tableExists('tl_iso_product_categories')) $this->Database->executeUncached("TRUNCATE tl_iso_product_categories");
		if ($this->Database->tableExists('tl_iso_orders')) $this->Database->executeUncached("TRUNCATE tl_iso_orders");
		if ($this->Database->tableExists('tl_iso_order_items')) $this->Database->executeUncached("TRUNCATE tl_iso_order_items");
		
		$this->Database->executeUncached("DELETE FROM tl_member_group WHERE name='Customers'");
		if ($this->Database->fieldExists('old_memberid','tl_member')) $this->Database->executeUncached("DELETE FROM tl_member WHERE old_memberid !='0'");
		if ($this->Database->fieldExists('old_memberid','tl_iso_addresses')) $this->Database->executeUncached("DELETE FROM tl_iso_addresses WHERE old_memberid !='0'");

		if ($this->Database->fieldExists('successful_import','tl_iso_products'))
		{
			
			$objSuccess = $this->Database->prepare("SELECT successful_import FROM tl_iso_products")->execute()->first();
			if($objSuccess->successful_import == 1)
			{
				$arrReturn = array('message' => 'Previous import found. Skipping Products truncation. Successfully truncated other Isotope tables.', 'method' => 'addFields');
			} else
			{
				if ($this->Database->tableExists('tl_iso_products')) $this->Database->executeUncached("TRUNCATE tl_iso_products");
				$arrReturn = array('message' => 'Successfully truncated Isotope tables.', 'method' => 'addFields');
			}
			
		}
		
		return $arrReturn;
	}
	
	private function addFields()
	{
		if (!$this->Database->fieldExists('old_productid','tl_iso_products'))
		{
			$this->Database->executeUncached("ALTER TABLE " . $GLOBALS['TL_CONFIG']['dbDatabase'] . ".tl_iso_products ADD COLUMN old_productid int(10) unsigned NOT NULL default '0'");
		}
		if (!$this->Database->fieldExists('old_productimage','tl_iso_products'))
		{
			$this->Database->executeUncached("ALTER TABLE " . $GLOBALS['TL_CONFIG']['dbDatabase'] . ".tl_iso_products ADD COLUMN old_productimage varchar(255) NOT NULL default ''");
		}
		if (!$this->Database->fieldExists('successful_import','tl_iso_products'))
		{
			$this->Database->executeUncached("ALTER TABLE " . $GLOBALS['TL_CONFIG']['dbDatabase'] . ".tl_iso_products ADD COLUMN successful_import char(1) NOT NULL default ''");
		}
		if (!$this->Database->fieldExists('old_memberid','tl_member'))
		{
			$this->Database->executeUncached("ALTER TABLE " . $GLOBALS['TL_CONFIG']['dbDatabase'] . ".tl_member ADD COLUMN old_memberid int(10) unsigned NOT NULL default '0'");
		}
		if (!$this->Database->fieldExists('old_addressid','tl_iso_addresses'))
		{
			$this->Database->executeUncached("ALTER TABLE " . $GLOBALS['TL_CONFIG']['dbDatabase'] . ".tl_iso_addresses ADD COLUMN old_addressid int(10) unsigned NOT NULL default '0'");
		}
		if (!$this->Database->fieldExists('old_memberid','tl_iso_addresses'))
		{
			$this->Database->executeUncached("ALTER TABLE " . $GLOBALS['TL_CONFIG']['dbDatabase'] . ".tl_iso_addresses ADD COLUMN old_memberid int(10) unsigned NOT NULL default '0'");
		}
		if (!$this->Database->fieldExists('old_memberid','tl_iso_orders'))
		{
			$this->Database->executeUncached("ALTER TABLE " . $GLOBALS['TL_CONFIG']['dbDatabase'] . ".tl_iso_orders ADD COLUMN old_memberid int(10) unsigned NOT NULL default '0'");
		}
		if (!$this->Database->fieldExists('old_orderid','tl_iso_orders'))
		{
			$this->Database->executeUncached("ALTER TABLE " . $GLOBALS['TL_CONFIG']['dbDatabase'] . ".tl_iso_orders ADD COLUMN old_orderid int(10) unsigned NOT NULL default '0'");
		}
		return array('message' => 'Added temporary fields to tl_iso_products.', 'method' => 'addProducts');
	}
	
	private function addProducts()
	{
		$arrNew = array();
		$arrOld = array();
		
		//Map fields from old database to new one
		$arrDataMap = array
		(
			'old_productimage'	=>	'products_image',
			'old_productid'		=>	'products_id',
			'sku'				=>	'products_model',
			'price'				=>	'products_price',
			'shipping_weight'	=>	'products_weight',
			'published'			=>	'products_status',
			'tax_class'			=>	'products_tax_class_id',
			'stock_quantity'	=>	'products_quantity'
		);
		
		foreach($arrDataMap as $k => $v)
		{
			$arrQuery[] = $GLOBALS['TL_CONFIG']['dbDatabase'] . ".tl_iso_products." . $k . " = " . $_SESSION['TL_IMPORT']['olddb'] . ".products." . $v;
			$arrNew[] = $k;
			$arrOld[] = $v;
		}
		
		if ($this->Database->fieldExists('successful_import','tl_iso_products'))
		{
			$objSuccess = $this->Database->prepare("SELECT successful_import FROM tl_iso_products")->execute()->first();
			if($objSuccess->successful_import == 1)
			{
				$this->Database->executeUncached("UPDATE " . $GLOBALS['TL_CONFIG']['dbDatabase'] . ".tl_iso_products, " . $_SESSION['TL_IMPORT']['olddb'] . ".products SET ". implode(',',$arrQuery) . " WHERE " . $GLOBALS['TL_CONFIG']['dbDatabase'] . ".tl_iso_products.old_productid = " . $_SESSION['TL_IMPORT']['olddb'] . ".products.products_id");
				
				return array('message' => 'Previous import found. Updating base products.', 'method' => 'updateDescriptions');
			}
		}
		
		if ($this->Database->fieldExists('old_productid','tl_iso_products'))
		{
			$this->Database->executeUncached("INSERT INTO tl_iso_products (". implode(',',$arrNew) .") SELECT ". implode(',',$arrOld) ." FROM " . $_SESSION['TL_IMPORT']['olddb'] . ".products");
		}
		
		return array('message' => 'Moved base products from old database to new database.', 'method' => 'updateDescriptions');
	}
	
	
	private function updateDescriptions()
	{

	$this->Database->executeUncached("UPDATE " . $GLOBALS['TL_CONFIG']['dbDatabase'] . ".tl_iso_products, " . $_SESSION['TL_IMPORT']['olddb'] . ".products_description SET
	" . $GLOBALS['TL_CONFIG']['dbDatabase'] . ".tl_iso_products.name = " . $_SESSION['TL_IMPORT']['olddb'] . ".products_description.products_name,
	" . $GLOBALS['TL_CONFIG']['dbDatabase'] . ".tl_iso_products.description = " . $_SESSION['TL_IMPORT']['olddb'] . ".products_description.products_description
	WHERE " . $GLOBALS['TL_CONFIG']['dbDatabase'] . ".tl_iso_products.old_productid = " . $_SESSION['TL_IMPORT']['olddb'] . ".products_description.products_id");
				
		return array('message' => 'Updated all product names and descriptions.', 'method' => 'updateProductTypes');
	}
	
	private function updateProductTypes()
	{

	$this->Database->executeUncached("UPDATE tl_iso_products SET type = " . $this->defaultType . " WHERE type=0");
				
		return array('message' => 'Set default product type.', 'method' => 'setAliases');
	}
	
	private function setAliases()
	{
		$objName = $this->Database->prepare("SELECT id,name FROM tl_iso_products")->execute();
		
		while ($objName->next())
		{
			$this->Database->prepare("UPDATE tl_iso_products SET alias = ? WHERE id = ?")->execute(standardize($objName->name), $objName->id);
		}
		
		return array('message' => 'Set product aliases from product names.', 'method' => 'updateP2C');

	}
	
	private function updateP2C()
	{
		
		$objCat = $this->Database->prepare("SELECT products_id, GROUP_CONCAT(DISTINCT categories_id) as categories FROM " . $_SESSION['TL_IMPORT']['olddb'] . ".products_to_categories GROUP BY products_id")->execute();
		
			while ($objCat->next())
			{
				$arrNewCats = array();
				$arrOldCats = explode(',',$objCat->categories);
				foreach($arrOldCats as $cat)
				{
					$arrNewCats[] = $_SESSION['TL_IMPORT']['arrCategoryMap'][$cat];
				}
				$this->Database->executeUncached("UPDATE " . $GLOBALS['TL_CONFIG']['dbDatabase'] . ".tl_iso_products SET " . $GLOBALS['TL_CONFIG']['dbDatabase'] . ".tl_iso_products.pages = '" . serialize($arrNewCats) . "' WHERE " . $GLOBALS['TL_CONFIG']['dbDatabase'] . ".tl_iso_products.old_productid = '" . $objCat->products_id . "'");
				
			}
		
				
		return array('message' => 'Updated all product categories. ', 'method' => 'syncP2C');
	}
	
	private function syncP2C()
	{
		$objProducts = $this->Database->prepare("SELECT id, pages FROM tl_iso_products")
									  ->execute();
		
		if(!$objProducts->numRows)
		{
			return array('message' => 'No products found. P2C syncing skipped.', 'method' => 'importImages');
		}
		
		while($objProducts->next())
		{
			$arrProducts[$objProducts->id] = deserialize($objProducts->pages);
		}
		
		foreach($arrProducts as $k=>$v)
		{
			
			if (is_array($v) && count($v))
			{
				
				$time = time();
				$this->Database->prepare("DELETE FROM tl_iso_product_categories WHERE pid=? AND page_id NOT IN (" . implode(',', $v) . ")")->execute($k);
				$objPages = $this->Database->prepare("SELECT page_id FROM tl_iso_product_categories WHERE pid=?")->execute($k);
				$arrIds = array_diff($v, $objPages->fetchEach('page_id'));
				
				foreach( $arrIds as $id )
				{
					$intSorting = $this->Database->prepare("SELECT sorting FROM tl_iso_product_categories WHERE page_id=? ORDER BY sorting DESC")->limit(1)->execute($id)->sorting;
					$intSorting += 128;
					$this->Database->prepare("INSERT INTO tl_iso_product_categories (pid,tstamp,page_id,sorting) VALUES (?,?,?,?)")->execute($k, $time, $id, $intSorting);
				}
			}
			else
			{
				$this->Database->prepare("DELETE FROM tl_iso_product_categories WHERE pid=?")->execute($k);
			}
		}
		
		return array('message' => 'Synced products to categories. ', 'method' => 'importImages');
		
	}


	private function importImages()
	{
		$this->import('Files');
		$strPath = 'isotope';
		$arrFiles = scan(TL_ROOT . '/' . $strPath);
		
		if (!count($arrFiles))
		{
			$_SESSION['TL_ERROR'][] = 'No files in this folder';
			$this->reload();
		}
		
		$arrDelete = array();
		$objProducts = $this->Database->prepare("SELECT * FROM tl_iso_products WHERE pid=0")										 
									  ->execute();
		
		while( $objProducts->next() )
		{			
			$arrImageNames  = array();
			$arrImages = deserialize($objProducts->images);

			if (!is_array($arrImages))
			{
				$arrImages = array();
			}
			else
			{
				foreach($arrImages as $row)
				{
					if($row['src'])
					{
						$arrImageNames[] = $row['src'];
					}
				}	
			}

			$strPattern = '@^(' . ($objProducts->old_productimage ?  $objProducts->old_productimage : '') . (count($arrImageNames) ? '|' . implode('|', $arrImageNames) : '') .')@i';
			$arrMatches = array_unique(preg_grep($strPattern, $arrFiles));

			if (count($arrMatches))
			{
				$arrNewImages = array();
				
				foreach( $arrMatches as $file )
				{					
					if (is_dir(TL_ROOT . '/' . $strPath . '/' . $file))
					{
						$arrSubfiles = scan(TL_ROOT . '/' . $strPath . '/' . $file);
						if (count($arrSubfiles))
						{
							foreach( $arrSubfiles as $subfile )
							{
								if (is_file($strPath . '/' . $file . '/' . $subfile))
								{
									$objFile = new File($strPath . '/' . $file . '/' . $subfile);
								
									if ($objFile->isGdImage)
									{
										$arrNewImages[] = $strPath . '/' . $file . '/' . $subfile;
									}
								}
							}
						}
					}
					elseif (is_file(TL_ROOT . '/' . $strPath . '/' . $file))
					{
						$objFile = new File($strPath . '/' . $file);
						
						if ($objFile->isGdImage)
						{
							$arrNewImages[] = $strPath . '/' . $file;
						}
					}
				}
									
				if (count($arrNewImages))
				{
					foreach( $arrNewImages as $strFile )
					{
						$pathinfo = pathinfo(TL_ROOT . '/' . $strFile);
						
						// Make sure directory exists
						$this->Files->mkdir('isotope/' . substr($pathinfo['filename'], 0, 1) . '/');
						
						$strCacheName = $pathinfo['filename'] . '-' . substr(md5_file(TL_ROOT . '/' . $strFile), 0, 8) . '.' . $pathinfo['extension'];
						
						$this->Files->copy($strFile, 'isotope/' . substr($pathinfo['filename'], 0, 1) . '/' . $strCacheName);
						$arrImages[] = array('src'=>$strCacheName);
						$arrDelete[] = $strFile;
						
						$_SESSION['TL_CONFIRM'][] = sprintf('Imported file %s for product "%s"', $pathinfo['filename'] . '.' . $pathinfo['extension'], $objProducts->name);
						
					}
					
					$this->Database->prepare("UPDATE tl_iso_products SET images=? WHERE id=?")->execute(serialize($arrImages), $objProducts->id);

				}
			}
		}
					
		if (count($arrDelete))
		{
			$arrDelete = array_unique($arrDelete);
			
			foreach( $arrDelete as $file )
			{
				$this->Files->delete($file);
			}
		}
		
		return array('message' => 'Moved and thumbnailed all images in isotope folder.', 'method' => 'setTaxes');
			
	}
	
	private function setTaxes()
	{
		
		$objTaxClass = $this->Database->prepare("SELECT * FROM " . $_SESSION['TL_IMPORT']['olddb'] . ".tax_class")->execute();
		
		while ($objTaxClass->next())
		{
			$arrRates = array();
			$objTaxRates = $this->Database->prepare("SELECT * FROM " . $_SESSION['TL_IMPORT']['olddb'] . ".tax_rates WHERE " . $_SESSION['TL_IMPORT']['olddb'] . ".tax_rates.tax_class_id = ?")->execute($objTaxClass->tax_class_id);
			
			while ($objTaxRates->next())
			{
				//Add rate to array of rates
				$arrRates[] = $objTaxRates->tax_rates_id;
				
				//Have to get the zone and country here
				$arrZones = $this->getSubdivisionAndCountry($objTaxRates->tax_zone_id, true);
						
				//Map tax rate fields from old database to new one
				$arrRatesMap = array
				(
					'id'			=>	$objTaxRates->tax_rates_id,
					'name'			=>	$objTaxRates->tax_description,
					'label'			=>	$objTaxRates->tax_description,
					'rate'			=>	serialize(array('unit'=>'%','value'=>$objTaxRates->tax_rate)),
					'country' 		=>	$arrZones['country'],
					'subdivision' 	=>	$arrZones['subdivision']
				);
				
				$arrMap = $this->mapFields($arrRatesMap);
				
				$this->Database->executeUncached("INSERT INTO tl_iso_tax_rate (". implode(',',$arrMap['new']) .") VALUES ('". implode("','",$arrMap['old']) ."')");
	
			}
			
			//Map tax class fields from old database to new one
			$arrClassMap = array
			(
				'id'			=>	$objTaxClass->tax_class_id,
				'name'			=>	$objTaxClass->tax_class_title,
				'label'			=>	$objTaxClass->tax_class_title,
				'rates'			=>	serialize($arrRates)
			);
			
			$arrMap = $this->mapFields($arrClassMap);
			
			$this->Database->executeUncached("INSERT INTO tl_iso_tax_class (". implode(',',$arrMap['new']) .") VALUES ('". implode("','",$arrMap['old']) ."')");

		}
		
		return array('message' => 'Moved tax classes and rates.', 'method' => 'addMembers');
	}
	
	private function addMembers()
	{		
		//Map customer fields from old database to new one
		$arrMemberMap = array
		(
			'old_memberid'		=>	'customers_id',
			'firstname'			=>	'customers_firstname',
			'lastname'			=>	'customers_lastname',
			'email'				=>	'customers_email_address',
			'phone'				=>	'customers_telephone',
			'password'			=>	'customers_password',
		);
		$arrMap = $this->mapFields($arrMemberMap);
		
		if ($this->Database->fieldExists('old_memberid','tl_member'))
		{
			$this->Database->executeUncached("INSERT INTO tl_member (". implode(',',$arrMap['new']) .") SELECT ". implode(',',$arrMap['old']) ." FROM " . $_SESSION['TL_IMPORT']['olddb'] . ".customers");
		}
		
		return array('message' => 'Moved customers from old database to new database.', 'method' => 'syncGroups');
	}
	
	private function syncGroups()
	{
		//Create Member Group "Customers" and retrieve its id
		$this->Database->executeUncached("INSERT INTO tl_member_group (name) VALUES ('Customers')");
		$objGroup = $this->Database->prepare("SELECT id FROM tl_member_group")->execute()->last();
		
		$this->Database->executeUncached("UPDATE tl_member SET groups = '" . serialize(array($objGroup->id)) . "' WHERE old_memberid !='0'");
		
		return array('message' => 'Synced new "Customer" member group.', 'method' => 'addAddresses');
	}
	
	private function addAddresses()
	{
		//Map address fields from old database to new one
		$arrAddressMap = array
		(
			'old_memberid'		=>	'customers_id',
			'old_addressid'		=>	'address_book_id',
			'company'			=>	'entry_company',
			'firstname'			=>	'entry_firstname',
			'lastname'			=>	'entry_lastname',
			'street_1'			=>	'entry_street_address',
			'postal'			=>	'entry_postcode',
			'city'				=>	'entry_city'
		);
		
		$arrMap = $this->mapFields($arrAddressMap);
		
		if ($this->Database->fieldExists('old_addressid','tl_iso_addresses') && $this->Database->fieldExists('old_memberid','tl_iso_addresses'))
		{
			$this->Database->executeUncached("INSERT INTO tl_iso_addresses (". implode(',',$arrMap['new']) .") SELECT ". implode(',',$arrMap['old']) ." FROM " . $_SESSION['TL_IMPORT']['olddb'] . ".address_book");
		}
		
		return array('message' => 'Added addresses to address book. Starting address to member sync. (Could take a while - be patient).', 'method' => 'syncAddresses');

	}
	
	private function syncAddresses()
	{
		//Have to break this into chunks because the update will time out otherwise
		$arrData = array();
		
		$objMember = $this->Database->prepare("SELECT id, old_memberid FROM tl_member")->execute();
		while($objMember->next())
		{
			$arrData[] = array('old_memberid' => $objMember->old_memberid, 'pid'=>$objMember->id);
		}
		
		//Step 2: Chunk the array into 1000 smaller ones if it is huge
		if(count($arrData)>1000)
		{
			$arrChunks = array_chunk($arrData, 1000);
		} else
		{
			$arrChunks = array_chunk($arrData, 50);
		}
		//Step 3: Loop and Set the Update 
		for($i=0; $i<count($arrChunks); $i++)
		{
			$arrUpdate = $arrChunks[$i];
			foreach($arrUpdate as $update)
			{
				$arrStr[] = "WHEN '". $update['old_memberid'] ."' THEN '". $update['pid'] . "' ";
			}
			
			$this->Database->executeUncached("UPDATE tl_iso_addresses SET pid = CASE old_memberid ". implode('',$arrStr) ."ELSE pid END");
			
		}
		
		return array('message' => 'Synced addresses to members. Adding address details (be patient).', 'method' => 'addAddressDetails');
	}
	
	private function addAddressDetails()
	{
		//Have to break this into chunks because the update will time out otherwise
		$arrData = array();
		
		$objAddress = $this->Database->prepare("SELECT address_book_id, entry_zone_id FROM " . $_SESSION['TL_IMPORT']['olddb'] . ".address_book")->execute();
		while($objAddress->next())
		{
			$arrZones = $this->getSubdivisionAndCountry($objAddress->entry_zone_id);
			$arrData[] = array('old_addressid' => $objAddress->address_book_id, 'subdivision'=>$arrZones['subdivision'], 'country'=>$arrZones['country']);
		}
		
		//Step 2: Chunk the array into 100 smaller ones if it is huge
		if(count($arrData)>1000)
		{
			$arrChunks = array_chunk($arrData, 1000);
		} else
		{
			$arrChunks = array_chunk($arrData, 50);
		}
		
		//Step 3: Loop and Set the Update
		for($i=0; $i<count($arrChunks); $i++)
		{
			$arrUpdate = $arrChunks[$i];
			foreach($arrUpdate as $update)
			{
				$arrSubdivisions[] = "WHEN '". $update['old_addressid'] ."' THEN '". $update['subdivision'] . "' ";
				$arrCountries[] = "WHEN '". $update['old_addressid'] ."' THEN '". $update['country'] . "' ";
			}
			
			$this->Database->executeUncached("UPDATE tl_iso_addresses SET subdivision = CASE old_addressid ". implode('',$arrSubdivisions) ."ELSE subdivision END");
			$this->Database->executeUncached("UPDATE tl_iso_addresses SET country = CASE old_addressid ". implode('',$arrCountries) ."ELSE country END");
		}	
		
		return array('message' => 'Added subdivision and country details to addresses.', 'method' => 'addOrders');
		
	}
	
	
	private function addOrders()
	{
		$this->loadLanguageFile('subdivisions');
		$arrSubdivisions = $GLOBALS['TL_LANG']['DIV'];
		$arrCountries = $this->getCountries();
		$arrEmpty = array();
	
		$objOrder = $this->Database->prepare("SELECT * FROM " . $_SESSION['TL_IMPORT']['olddb'] . ".orders")->execute();
		while($objOrder->next())
		{
			$objMember = $this->Database->prepare("SELECT id FROM tl_member WHERE old_memberid = ?")->execute($objOrder->customers_id);
			
			$arrTotals = $this->Database->prepare("SELECT value,class FROM " . $_SESSION['TL_IMPORT']['olddb'] . ".orders_total WHERE orders_id=?")->execute($objOrder->orders_id)->fetchAllAssoc();
			foreach($arrTotals as $total)
			{
				$arrFinalTotals[$total['class']] = $total['value'];
			}
			
			$arrShipName = explode(' ',$objOrder->delivery_name);
			$arrBillName = explode(' ',$objOrder->billing_name);
			$strShipCountry = (array_search($objOrder->delivery_country, $arrCountries)) ? array_search($objOrder->delivery_country, $arrCountries) : '';
			$strBillCountry = (array_search($objOrder->billing_country, $arrCountries)) ? array_search($objOrder->billing_country, $arrCountries) : '';
			if($strShipCountry)
			{
				$strShipSubdivision = (array_search($objOrder->delivery_state,$GLOBALS['TL_LANG']['DIV'][$strShipCountry])) ? array_search($objOrder->delivery_country,$GLOBALS['TL_LANG']['DIV'][$strShipCountry]) : '';
			}
			if($strBillCountry)
			{
				$strBillSubdivision = (array_search($objOrder->billing_state,$GLOBALS['TL_LANG']['DIV'][$strShipCountry])) ? array_search($objOrder->delivery_country,$GLOBALS['TL_LANG']['DIV'][$strBillCountry]) : '';
			}
			
			$arrShipping = array(
				'firstname'			=>	array_shift($arrShipName),
				'lastname'			=>	implode(' ',$arrShipName),
				'company'			=>	$objOrder->delivery_company,
				'street_1'			=>	$objOrder->delivery_street_address,
				'city'				=>	$objOrder->delivery_city,
				'subdivision'		=>	$strShipSubdivision,
				'postal'			=>	$objOrder->delivery_postcode,
				'country'			=>	$strShipCountry,
				'phone'				=>	$objOrder->customers_telephone,
				'email'				=>	$objOrder->customers_email_address,
				'isDefaultBilling'	=>	'',
				'id'				=>	'-1'
			);
			
			$arrBilling = array(
				'firstname'			=>	array_shift($arrBillName),
				'lastname'			=>	implode(' ',$arrBillName),
				'company'			=>	$objOrder->billing_company,
				'street_1'			=>	$objOrder->billing_street_address,
				'city'				=>	$objOrder->billing_city,
				'subdivision'		=>	$strBillSubdivision,
				'postal'			=>	$objOrder->billing_postcode,
				'country'			=>	$strBillCountry,
				'phone'				=>	$objOrder->customers_telephone,
				'email'				=>	$objOrder->customers_email_address,
				'isDefaultBilling'	=>	'',
				'id'				=>	''
			);
			
			$strShipAddress = $arrShipping['firstname'] . ' ' . $arrShipping['lastname'] . '<br />' . $arrShipping['street_1'] . '<br />' . $arrShipping['city'] . ', ' . substr($arrShipping['subdivision'],-2) . ' ' . $arrShipping['postal'] . '<br />' . $arrCountries[$arrShipping['country']] . '<br />' . $arrShipping['phone'];
			
			$strBillAddress = $arrBilling['firstname'] . ' ' . $arrBilling['lastname'] . '<br />' . $arrBilling['street_1'] . '<br />' . $arrBilling['city'] . ', ' . substr($arrBilling['subdivision'],-2) . ' ' . $arrBilling['postal'] . '<br />' . $arrCountries[$arrBilling['country']] . '<br />' . $arrBilling['phone'];
			
			$arrCheckout = array(
				'billing_address'	=> array('headline'=>'Billing Address', 'info'=>$strBillAddress),
				'shipping_address'	=> array('headline'=>'Shipping Address', 'info'=>$strShipAddress),
				'shipping_method'	=> array('headline'=>'Shipping Method', 'info'=>$objOrder->shipping_module),
				'payment_method'	=> array('headline'=>'Payment Method', 'info'=>$objOrder->payment_method . '<br />' . $objOrder->cc_type . ': ' . $objOrder->cc_number)
			);
					
			$arrSet = array(
				'pid'				=>	($objMember->id) ? $objMember->id : '0',
				'old_orderid'		=>	$objOrder->orders_id,
				'tstamp'			=>	strtotime($objOrder->date_purchased),
				'date'				=>	strtotime($objOrder->date_purchased),
				'status'			=>	'complete',
				'order_id'			=>	$objOrder->orders_id,
				'uniqid'			=>	uniqid($objOrder->orders_id, true),
				'config_id'			=>	'1',
				'language'			=>	'en',
				'shipping_address'	=>	serialize($arrShipping),
				'billing_address'	=>	serialize($arrBilling),
				'checkout_info'		=>	serialize($arrCheckout),
				'surcharges'		=>	serialize($arrEmpty),
				'payment_data'		=>	serialize($arrEmpty),
				'shipping_data'		=>	serialize($arrEmpty),
				'subTotal'			=>	($arrFinalTotals['ot_subtotal']) ? $arrFinalTotals['ot_subtotal'] : '0.00',
				'taxTotal'			=>	($arrFinalTotals['ot_tax']) ? $arrFinalTotals['ot_tax'] : '0.00',
				'shippingTotal'		=>	($arrFinalTotals['ot_shipping']) ? $arrFinalTotals['ot_shipping'] : '0.00',
				'grandTotal'		=>	($arrFinalTotals['ot_total']) ? $arrFinalTotals['ot_total'] : '0.00',
				'currency'			=>	($objMember->currency) ? $objMember->currency : 'USD'
			);
			
			$this->Database->prepare("INSERT INTO tl_iso_orders %s")->set($arrSet)->executeUncached();
			
		}
		
		return array('message' => 'Added all previous orders. Starting to move orders to items. (Could take a while - be patient).', 'method' => 'addOrdersToItems');

	}
	
	private function addOrdersToItems()
	{
		$objItem = $this->Database->prepare("SELECT * FROM " . $_SESSION['TL_IMPORT']['olddb'] . ".orders_products")->execute();
		while($objItem->next())
		{
			$objProduct = $this->Database->prepare("SELECT id FROM tl_iso_products WHERE old_productid=?")->execute($objItem->products_id);
			$objOrder = $this->Database->prepare("SELECT id FROM tl_iso_orders WHERE old_orderid=?")->execute($objItem->orders_id);
		
			$arrSet = array(
				'pid'				=>	($objOrder->id) ? $objOrder->id : '0',
				'tstamp'			=>	time(),
				'product_id'		=>	($objProduct->id) ? $objProduct->id : '0',
				'product_quantity'	=>	$objItem->products_quantity ? $objItem->products_quantity : '0',
				'price'				=>	$objItem->products_price ? $objItem->products_price : '0',
				'product_sku'		=>	$objItem->products_model,
				'product_name'		=>	$objItem->products_name
			);
			
			$this->Database->prepare("INSERT INTO tl_iso_order_items %s")->set($arrSet)->executeUncached();
		
		}
		
		return array('message' => 'Added all items to orders.', 'method' => 'flagForSuccess');
	}
	
		
	private function flagForSuccess()
	{
		if ($this->Database->fieldExists('successful_import','tl_iso_products'))
		{
			$this->Database->executeUncached("UPDATE tl_iso_products SET successful_import ='1'");

		}
		return array('message' => 'Flagged products as successfully imported.', 'method' => 'finished');
	}
	
	/**
	 * Helper functions
	 *
	 */
	 
	private function createOldPageList()
	{
		$olddb = $_SESSION['TL_IMPORT']['olddb'];
		$strOptions = '';
		
		if($olddb)
		{
			$objCategory = $this->Database->prepare("SELECT * FROM " . $olddb . ".categories_description WHERE language_id = ?")->execute(1);
			
			while($objCategory->next())
			{
				$strOptions .= '<option value ="' . $objCategory->categories_id . '">' . $objCategory->categories_name . '</option>';
			}
			
		}
		
		return $strOptions;
	}
	 
	private function mapFields($arrMap, $blnFlip=false)
	{
		if(!count($arrMap))
		{
			return array();
		}
		
		foreach($arrMap as $k => $v)
		{
			$arrOld[] = ($blnFlip) ? $k : $v;
			$arrNew[] = ($blnFlip) ? $v : $k;
		}
		$arrReturn = array('old' => $arrOld, 'new'=> $arrNew);
		
		return $arrReturn;
	}
	
	private function getSubdivisionAndCountry($zoneid, $blnUsegeozone=false)
	{
		if(!strlen($zoneid))
		{
			return '';
		}
		if($blnUsegeozone)
		{
			$objGeoZone =  $this->Database->prepare("SELECT zone_id FROM " . $_SESSION['TL_IMPORT']['olddb'] . ".zones_to_geo_zones WHERE " . $_SESSION['TL_IMPORT']['olddb'] . ".zones_to_geo_zones.geo_zone_id = ?")->limit(1)->execute($zoneid);
			$zoneid = $objGeoZone->zone_id;
		}
		$objZone =  $this->Database->prepare("SELECT zone_code, zone_country_id FROM " . $_SESSION['TL_IMPORT']['olddb'] . ".zones WHERE " . $_SESSION['TL_IMPORT']['olddb'] . ".zones.zone_id = ?")->limit(1)->execute($zoneid);
		$objCountry = $this->Database->prepare("SELECT countries_iso_code_2 FROM " . $_SESSION['TL_IMPORT']['olddb'] . ".countries WHERE " . $_SESSION['TL_IMPORT']['olddb'] . ".countries.countries_id = ?")->limit(1)->execute($objZone->zone_country_id);
		
		$arrReturn = array('country'=>strtolower($objCountry->countries_iso_code_2), 'subdivision' => $objCountry->countries_iso_code_2 . '-' . $objZone->zone_code);
		
		return $arrReturn;
	}
	
}

?>