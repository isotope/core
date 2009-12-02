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


class IsotopeProduct extends Model
{
	
	/**
	 * Name of the current table
	 * @var string
	 */
	protected $strTable = 'tl_product_data';
	
	/**
	 * Attributes assigned to this product
	 * @var array
	 */
	protected $arrAttributes = array();
	
	/**
	 * Downloads for this product
	 */
	protected $arrDownloads = array();
	
	/**
	 * Cache properties, cache is dropped when serializing
	 */
	protected $arrCache = array();
	
	
	/**
	 * Construct the object
	 */
	public function __construct()
	{
		parent::__construct();
		$this->import('Isotope');
	}
	
	
	/**
	 * Get a property
	 * @return mixed
	 */
	public function __get($strKey)
	{
		switch( $strKey)
		{
			case 'id':
			case 'pid':
			case 'href_reader':
				return $this->arrData[$strKey];
				
			case 'hasDownloads':
				return count($this->arrDownloads) ? true : false;
				
			default:
				// Initialize attribute
				if (!isset($this->arrCache[$strKey]))
				{
					if (isset($GLOBALS['TL_DCA']['tl_product_data']['fields'][$strKey]))
					{
						switch( $GLOBALS['TL_DCA']['tl_product_data']['fields'][$strKey]['inputType'] )
						{
							case 'mediaManager':
								$varValue = array();
								$arrImages = deserialize($this->arrData[$strKey]);
								
								if(is_array($arrImages) && count($arrImages))
								{
									foreach( $arrImages as $k => $file )
									{
										$strFile = 'isotope/' . substr($file['src'], 0, 1) . '/' . $file['src'];
										
										if (is_file(TL_ROOT . '/' . $strFile))
										{
											$objFile = new File($strFile);
											
											if ($objFile->isGdImage)
											{
												$file['is_image'] = true;
												
												foreach( array('large', 'medium', 'thumbnail', 'gallery') as $size )
												{
													$strImage = $this->Isotope->getImage($strFile, $this->Isotope->Store->{$size . '_image_width'}, $this->Isotope->Store->{$size . '_image_height'});
													$arrSize = @getimagesize(TL_ROOT . '/' . $strImage);
													
													$file[$size] = $strImage;
													
													if (is_array($arrSize) && strlen($arrSize[3]))
													{
														$file[$size . '_size'] = $arrSize[3];
													}
												}
												
												$varValue[] = $file;
											}
										}
									}
								}
								break;
						}
					}
						
					switch( $strKey )
					{
						case 'price':
							return $this->Isotope->calculatePrice((strlen($this->arrData[$this->Isotope->Store->priceOverrideField]) ? $this->arrData[$this->Isotope->Store->priceOverrideField] : $this->arrData[$this->Isotope->Store->priceField]), $this->arrData['tax_class']);
							break;
							
						case 'total_price':
							return ($this->quantity_requested ? $this->quantity_requested : 1) * $this->price;
							break;
							
						case 'formatted_price':
							$varValue = $this->Isotope->formatPriceWithCurrency($this->price);
							break;
							
						case 'formatted_total_price':
							$varValue = $this->Isotope->formatPriceWithCurrency($this->total_price);
							break;
							
						case 'images':
							// No image available, add default image
							if (!count($varValue) && is_file(TL_ROOT . '/' . $this->Isotope->Store->missing_image_placeholder))
							{
								foreach( array('large', 'medium', 'thumbnail', 'gallery') as $size )
								{
									$strImage = $this->Isotope->getImage($this->Isotope->Store->missing_image_placeholder, $this->Isotope->Store->{$size . '_image_width'}, $this->Isotope->Store->{$size . '_image_height'});
									$arrSize = @getimagesize(TL_ROOT . '/' . $strImage);
									
									$file[$size] = $strImage;
									
									if (is_array($arrSize) && strlen($arrSize[3]))
									{
										$file[$size . '_size'] = $arrSize[3];
									}
								}
								
								$varValue[] = $file;
							}
							break;
					}
		
					$this->arrCache[$strKey] = $varValue ? $varValue : deserialize($this->arrData[$strKey]);;
				}
				
				return $this->arrCache[$strKey];
		}
	}
	
	
	/**
	 * Set a property
	 */
	public function __set($strKey, $varValue)
	{
		switch( $strKey )
		{
			case 'reader_jumpTo':
				$this->arrData['href_reader'] = $this->Isotope->generateFrontendUrl($this->Database->prepare("SELECT * FROM tl_page WHERE id=?")->execute($varValue)->fetchAssoc(), '/product/' . $this->arrData['alias']);
				
			default:
				$this->arrCache[$strKey] = $varValue;
		}
	}
	
	
	/**
	 * Destroy unnessessary data when serializing
	 */
	public function __sleep()
	{
		return array('arrAttributes', 'arrDownloads', 'arrData');
	}
	
	
	/**
	 * Make sure required data is available
	 */
	public function __wakeup()
	{
		$this->import('Config');
		$this->import('Input');
		$this->import('Environment');
		$this->import('Session');
		$this->import('Database');
		$this->import('Isotope');
	}
	
	
	/**
	 * Find a record by its reference field and return true if it has been found
	 * @param  int
	 * @return boolean
	 */
	public function findBy($strRefField, $varRefId)
	{
		if (!parent::findBy($strRefField, $varRefId))
			return false;
			
		if (!$this->arrData['published'] || (strlen($this->arrData['start']) && $this->arrData['start'] > time()) || (strlen($this->arrData['stop']) && $this->arrData['stop'] < time()))
			return false;
		
		$objType = $this->Database->prepare("SELECT * FROM tl_product_types WHERE id=?")->execute($this->arrData['type']);
		
		if (!$objType->numRows)
			return false;
			
		$this->arrAttributes = deserialize($objType->attributes);
		
		if (!is_array($this->arrAttributes) || !count($this->arrAttributes))
			return false;
		
		// Cache downloads for this product
		if ($objType->downloads)
		{
			$this->arrDownloads = $this->Database->prepare("SELECT * FROM tl_product_downloads WHERE pid=?")->execute($this->arrData['id'])->fetchAllAssoc();
		}
		
		return true;
	}
	
	
	/**
	 * Return all downloads for this product
	 *
	 * @todo: Confirm that files are available, possibly on __wakeup() ?
	 */
	public function getDownloads()
	{
		return $this->arrDownloads;
	}
	
	
	/**
	 * Return all attributes for this product
	 */
	public function getAttributes()
	{
		$arrData = array();
		
		foreach( $this->arrAttributes as $attribute )
		{
			$arrData[$attribute] = $this->$attribute;
		}
		
		return $arrData;
	}
}

