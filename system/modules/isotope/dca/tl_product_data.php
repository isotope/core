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
 * @author     Fred Bliss <fred@winanscreative.com>
 * @author     Andreas Schempp <andreas@schempp.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */


/**
 * Table tl_product_data
 */
$GLOBALS['TL_DCA']['tl_product_data'] = array
(
	// Config
	'config' => array
	(
		'dataContainer'               => 'Table',
		'enableVersioning'            => false,
		'ctables'					  => array('tl_product_downloads', 'tl_product_categories'),
		/*
		'onload_callback'			  => array
		(
			array('tl_product_data', 'checkPermission'),
		),*/
		'onsubmit_callback'			  => array
		(
			array('ProductCatalog', 'saveProduct')
		),
	),
	
	// List
	'list' => array
	(
		'sorting' => array
		(
			'mode'                    => 1,
			'fields'                  => array('type', 'name'),
			'flag'                    => 1,
			'panelLayout'             => 'filter;search,limit',
		),
		'label' => array
		(
			'fields'                  => array('name'),
			'format'                  => '%s',
			'label_callback'		  => array('tl_product_data','getRowLabel'),
		),
		'global_operations' => array
		(
			'import' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_product_data']['import'],
				'href'                => 'key=import',
				'class'               => 'header_import_assets',
				'attributes'          => 'onclick="Backend.getScrollOffset();"'
			),
			'all' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['MSC']['all'],
				'href'                => 'act=select',
				'class'               => 'header_edit_all',
				'attributes'          => 'onclick="Backend.getScrollOffset();"'
			),
		),
		'operations' => array
		(
			'edit' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_product_data']['edit'],
				'href'                => 'act=edit',
				'icon'                => 'edit.gif'
			),
			'copy' => array
			(
				'label'					=> &$GLOBALS['TL_LANG']['tl_product_data']['copy'],
				'href'					=> 'act=copy',
				'icon'					=> 'copy.gif'
			),
			'delete' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_product_data']['delete'],
				'href'                => 'act=delete',
				'icon'                => 'delete.gif',
				'attributes'          => 'onclick="if (!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\')) return false; Backend.getScrollOffset();"'
			),
			'show' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_product_data']['show'],
				'href'                => 'act=show',
				'icon'                => 'show.gif'
			),
			'downloads' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_product_data']['downloads'],
				'href'                => 'table=tl_product_downloads',
				'icon'                => 'system/modules/isotope/html/attach.png',
				'button_callback'	  => array('tl_product_data', 'downloadsButton'),
			),
		),
	),
	
	// Palettes
	'palettes' => array
	(
		'__selector__'				=> array('type', 'enable_stocks'),
		'default'					=> '{general_legend},type,alias',
	),
	
	// Subpalettes
	'subpalettes' => array
	(
		'enable_stocks'				=> 'stocked',
	),
	
	// Fields
	'fields' => array
	(
		'type' => array
		(
			'label'					=>  &$GLOBALS['TL_LANG']['tl_product_data']['type'],
			'filter'				=> true,
			'inputType'				=> 'select',
			'options_callback'		=> array('tl_product_data', 'getProductTypes'),
			'eval'					=> array('mandatory'=>true, 'includeBlankOption'=>true, 'submitOnChange'=>true),
			'attributes'			=> array('legend'=>'general_legend', 'fixed'=>true),
		),
		'pages' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_product_data']['pages'],
			'filter'				=> true,
			'inputType'				=> 'pageTree',
			'foreignKey'			=> 'tl_page.title',
			'eval'					=> array('mandatory'=>false, 'multiple'=>true, 'fieldType'=>'checkbox'),
			'attributes'			=> array('legend'=>'general_legend', 'fixed'=>true),
			'save_callback'			=> array
			(
				array('ProductCatalog','saveProductCategories'),
			),
		),
		'alias' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_product_data']['alias'],
			'inputType'				=> 'text',
			'eval'					=> array('rgxp'=>'alnum', 'doNotCopy'=>true, 'spaceToUnderscore'=>true, 'maxlength'=>128, 'tl_class'=>'w50'),
			'attributes'			=> array('legend'=>'general_legend', 'fixed'=>true),
			'save_callback' => array
			(
				array('tl_product_data', 'generateAlias'),
			)
		),
		'sku' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_product_data']['sku'],
			'inputType'				=> 'text',
			'eval'					=> array('maxlength'=>128, 'tl_class'=>'w50'),
			'attributes'			=> array('mandatory'=>true, 'legend'=>'general_legend'),
		),
		'name' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_product_data']['name'],
			'search'				=> true,
			'inputType'				=> 'text',
			'eval'					=> array('mandatory'=>true, 'tl_class'=>'long'),
			'attributes'			=> array('legend'=>'general_legend', 'fixed'=>true),
		),
		'teaser' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_product_data']['teaser'],
			'search'				=> true,
			'inputType'				=> 'textarea',
			'eval'					=> array('style'=>'height:80px'),
			'attributes'			=> array('legend'=>'general_legend'),
		),
		'description' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_product_data']['description'],
			'search'				=> true,
			'inputType'				=> 'textarea',
			'eval'					=> array('mandatory'=>true, 'rte'=>'tinyMCE'),
			'attributes'			=> array('legend'=>'general_legend'),
		),
		'max_order_quantity' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_product_data']['max_order_quantity'],
			'inputType'				=> 'text',
			'eval'					=> array('rgxp'=>'digits', 'disabled'=>'disabled'),
			'attributes'			=> array('legend'=>'inventory_legend'),
		),
		'stock_enabled' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_product_data']['stock_enabled'],
			'filter'				=> true,
			'inputType'				=> 'checkbox',
			'eval'					=> array('submitOnChange'=>true, 'disabled'=>'disabled'),
			'attributes'			=> array('legend'=>'inventory_legend'),
		),
		'stock_quantity' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_product_data']['stock_quantity'],
			'inputType'				=> 'text',
			'eval'					=> array('mandatory'=>true, 'rgxp'=>'digits', 'disabled'=>'disabled'),
		),
		'tax_class' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_product_data']['tax_class'],
			'filter'				=> true,
			'inputType'				=> 'select',
			'foreignKey'			=> 'tl_tax_class.name',
			'attributes'			=> array('legend'=>'tax_legend'),
		),
		'images' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_product_data']['images'],
			'inputType'				=> 'mediaManager',
			'attributes'			=> array('legend'=>'media_legend', 'fixed'=>true),
		),
		'published' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_product_data']['published'],
			'filter'				=> true,
			'inputType'				=> 'checkbox',
			'eval'					=> array('doNotCopy'=>true),
			'attributes'			=> array('legend'=>'publish_legend', 'fixed'=>true),
		),
		'start' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_product_data']['start'],
			'inputType'				=> 'text',
			'eval'					=> array('rgxp'=>'date', 'datepicker'=>$this->getDatePickerString(), 'tl_class'=>'w50 wizard', 'disabled'=>'disabled'),
			'attributes'			=> array('legend'=>'publish_legend'),
		),
		'stop' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_product_data']['stop'],
			'inputType'				=> 'text',
			'eval'					=> array('rgxp'=>'date', 'datepicker'=>$this->getDatePickerString(), 'tl_class'=>'w50 wizard', 'disabled'=>'disabled'),
			'attributes'			=> array('legend'=>'publish_legend'),
		),
		'source' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_product_data']['source'],
			'eval'                    => array('mandatory'=>true, 'required'=>true, 'fieldType'=>'radio'),
		),
		
		
		
		
		
		
		
		
		
		
		
		/*
		'create_variations' => array
		(
			'label'					  => &$GLOBALS['TL_LANG']['tl_product_data']['create_variations'],
			'inputType'				  => 'checkbox',
			'eval'					  => array('submitOnChange'=>true)		
		),*/
		'option_set_source' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_product_data']['option_set_source'],
			'default'                 => 'new_option_set',
			'inputType'               => 'radio',
			//'options'                 => array('existing_option_set', 'new_option_set'),
			'reference'               => &$GLOBALS['TL_LANG']['tl_product_data'],
			'eval'                    => array('submitOnChange'=>true),	//, 'helpwizard'=>true)
			'attributes'			  => array('legend'=>'options_legend'),
			'options_callback'		  => array('ProductCatalog','getOptionSets')
		),
		'option_sets' => array
		(
			'label'					  =>  &$GLOBALS['TL_LANG']['tl_product_data']['option_sets'],
			'inputType'				  => 'select',
			'eval'					  => array('includeBlankOption'=>true, 'submitOnChange'=>true),
			'attributes'			  => array('legend'=>'options_legend'),
			'options_callback'		  => array('ProductCatalog','getProductOptionSets')
		),
		'option_set_title' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_product_data']['option_set_title'],
			'search'                  => true,
			'inputType'               => 'text',
			'eval'                    => array('rgxp'=>'extnd', 'maxlength'=>255),
			'attributes'			  => array('legend'=>'options_legend')
		),
		'variants_wizard' => array
		(
			'label'					  => &$GLOBALS['TL_LANG']['tl_product_data']['variants_wizard'],
			'inputType' 			  => 'variantsWizard',
			'eval'					  => array('mandatory'=>false, 'enableDelete'=>false),
			'attributes'			  => array('legend'=>'options_legend'),
			'explanation'			  => 'variantsWizard'
		),/*
		'add_audio_file' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_product_data']['add_audio_file'],
			'default'				  => 'internal',
			'filter'                  => false,
			'inputType'               => 'checkbox',
			'eval'                    => array('submitOnChange'=>true)
		),
		'add_video_file' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_product_data']['add_video_file'],
			'default'				  => 'internal',
			'filter'                  => false,
			'inputType'               => 'checkbox',
			'eval'                    => array('submitOnChange'=>true)
		),
		'audio_source' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_product_data']['audio_source'],
			'default'                 => 'internal',
			'filter'                  => false,
			'inputType'               => 'radio',
			'options'                 => array('internal', 'external'),
			'reference'               => &$GLOBALS['TL_LANG']['tl_product_data'],
			'eval'                    => array('helpwizard'=>true)
		),
		'audio_jumpTo' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_product_data']['audio_jumpTo'],
			'inputType'               => 'fileTree',
			'eval'                    => array('fieldType'=>'radio', 'files'=>true, 'helpwizard'=>true)
		),
		'audio_url' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_product_data']['audio_url'],
			'search'                  => true,
			'inputType'               => 'text',
			'eval'                    => array('decodeEntities'=>true, 'maxlength'=>255)
		),
		'video_source' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_product_data']['video_source'],
			'default'                 => 'internal',
			'filter'                  => false,
			'inputType'               => 'radio',
			'options'                 => array('internal', 'external'),
			'reference'               => &$GLOBALS['TL_LANG']['tl_product_data'],
			'eval'                    => array('helpwizard'=>true)
		),
		'video_jumpTo' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_product_data']['video_jumpTo'],
			'inputType'               => 'fileTree',
			'eval'                    => array('fieldType'=>'radio', 'files'=>true, 'helpwizard'=>true)
		),
		'video_url' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_product_data']['video_url'],
			'search'                  => true,
			'inputType'               => 'text',
			'eval'                    => array('decodeEntities'=>true, 'maxlength'=>255)
		),
		'option_collection' => array
		(
			'label'					  => &$GLOBALS['TL_LANG']['tl_product_data']['option_collection'],
			'inputType'				  => 'productOptionWizard',
			'load_callback'			  => array
			(
				array('ProductCatalog','loadProductOptions')
			),
			'save_callback'			  => array
			(
				array('ProductCatalog','saveProductOptions')
			)
		),*/
	),
);


$objPC = new ProductCatalog();
$objPC->loadProductCatalogDCA();


class tl_product_data extends Backend
{

	public function __construct()
	{
		parent::__construct();
		
		$this->import('Isotope');
	}
	
	
	/**
	 * Show/hide the downloads button
	 */
	public function downloadsButton($row, $href, $label, $title, $icon, $attributes)
	{
		$objType = $this->Database->prepare("SELECT * FROM tl_product_types WHERE id=?")
								  ->limit(1)
								  ->execute($row['type']);

		if (!$objType->downloads)
			return '';
			
		$objDownloads = $this->Database->prepare("SELECT COUNT(*) AS total FROM tl_product_downloads WHERE pid=?")->execute($row['id']);
			
		return '<p style="padding-top:8px"><a href="'.$this->addToUrl($href.'&amp;id='.$row['id']).'" title="'.specialchars($title).'"'.$attributes.'>'.$this->generateImage($icon, $label).' '.sprintf($GLOBALS['TL_LANG']['MSC']['downloadCount'], $objDownloads->total).'</a></p>';
	}
	
	
	/**
	 * Only list product types a user is allowed to see.
	 */
	public function checkPermission($dc)
	{
		$this->import('BackendUser', 'User');
		
		if ($this->User->isAdmin)
			return;
		
		$arrTypes = is_array($this->User->iso_product_types) ? $this->User->iso_product_types : array(0);
		
		$arrProducts = $this->Database->execute("SELECT id FROM tl_product_data WHERE type IN ('','" . implode("','", $arrTypes) . "')")->fetchEach('id');
		
		if (!is_array($arrProducts) || !count($arrProducts))
		{
			$arrProducts = array(0);
		}
		
		$GLOBALS['TL_DCA']['tl_product_data']['list']['sorting']['root'] = $arrProducts;
		
		if (strlen($this->Input->get('id')) && !in_array($this->Input->get('id'), $arrProducts))
		{
			$this->redirect('typolight/main.php?act=error');
		}
	}
	
	
	/**
	 * List products in backend.
	 */
	public function getRowLabel($row, $label = '')
	{
		$key = $row['published'] ? 'published' : 'unpublished';
		
		$arrImages = deserialize($row['images']);
		$thumbnail = '';
		
		if (is_array($arrImages) && count($arrImages))
		{
			foreach( $arrImages as $image )
			{
				$strImage = 'isotope/' . substr($image['src'], 0, 1) . '/' . $image['src'];
				
				if (!is_file(TL_ROOT . '/' . $strImage))
					continue;
					
				$thumbnail = sprintf('<img src="%s" alt="%s" align="left" style="padding-right: 8px;" />', $this->getImage($strImage, 50, 50), $image['alt']);
				break;
			}
		}
		
		$output = '<div style="margin-top:5px!important;margin-bottom:0px!important;" class="cte_type ' . $key . '"><div><span>' . $thumbnail . '<strong>' . $row['name'] . '</strong></span><div><span style="color:#b3b3b3;"><strong>' . $this->Isotope->formatPriceWithCurrency($row['price']) . '</strong></span></div><br /><br /><div><em>' . $GLOBALS['TL_LANG']['tl_product_data']['pages'][0] . ': ' . $this->getCategoryList(deserialize($row['pages'])) . '</em></div></div></div> ';
		
		$fields = array();
		
		return $output;
	}
	
	
	/**
	 * Returns all allowed product types as array.
	 *
	 * @access public
	 * @param object DataContainer $dc
	 * @return array
	 */
	public function getProductTypes(DataContainer $dc)
	{
		$this->import('BackendUser', 'User');
		
		$arrTypes = $this->User->iso_product_types;
		if (!is_array($arrTypes) || !count($arrTypes))
		{
			$arrTypes = array(0);
		}
		
		$arrProductTypes = array();

		$objProductTypes = $this->Database->execute("SELECT id,name FROM tl_product_types" . ($this->User->isAdmin ? '' : (" WHERE id IN (".implode(',', $arrTypes).")")));

		while($objProductTypes->next())
		{
			$arrProductTypes[$objProductTypes->id] = $objProductTypes->name;
		}

		return $arrProductTypes;
	}
	
	
	/**
	 * Produce a list of categories for the backend listing
	 *
	 * @param mixed
	 * @return string
	 */
	private function getCategoryList($varValue)
	{
		if(!is_array($varValue) || !count($varValue))
		{
			return $GLOBALS['TL_LANG']['MSC']['noCategoriesAssociated'];
		}
		
		$objCategories = $this->Database->execute("SELECT title FROM tl_page WHERE id IN (" . implode(',', $varValue) . ")");
		
		if(!$objCategories->numRows)
		{
			return $GLOBALS['TL_LANG']['MSC']['noCategoriesAssociated'];
		}
		
		return implode(', ', $objCategories->fetchEach('title'));
	}
	
	
	/**
	 * Autogenerate a page alias if it has not been set yet
	 * @param mixed
	 * @param object
	 * @return string
	 */
	public function generateAlias($varValue, DataContainer $dc)
	{
		$autoAlias = false;

		// Generate alias if there is none
		if (!strlen($varValue))
		{
			$objProduct = $this->Database->prepare("SELECT sku, name FROM tl_product_data WHERE id=?")
										 ->limit(1)
										 ->execute($dc->id);

			$autoAlias = true;
			$varValue = strlen($objProduct->sku) ? standardize($objProduct->sku) : standardize($objProduct->name);
		}

		$objAlias = $this->Database->prepare("SELECT id FROM tl_product_data WHERE id=? OR alias=?")
								   ->execute($dc->id, $varValue);

		// Check whether the page alias exists
		if ($objAlias->numRows > 1)
		{
			if (!$autoAlias)
			{
				throw new Exception(sprintf($GLOBALS['TL_LANG']['ERR']['aliasExists'], $varValue));
			}

			$varValue .= '.' . $dc->id;
		}

		return $varValue;
	}
	
	
	/**
	 * Import images and other media file for products
	 */
	public function importAssets($dc)
	{
		$objTree = new FileTree($this->prepareForWidget($GLOBALS['TL_DCA']['tl_product_data']['fields']['source'], 'source', null, 'source', 'tl_product_data'));
		
		// Import assets
		if ($this->Input->post('FORM_SUBMIT') == 'tl_product_data_import' && strlen($this->Input->post('source')))
		{
			$this->import('Files');
			
			$strPath = $this->Input->post('source');
			$arrFiles = scan(TL_ROOT . '/' . $strPath);
			
			if (!count($arrFiles))
			{
				$_SESSION['TL_ERROR'][] = 'No files in this folder';
				$this->reload();
			}
			
			$arrDelete = array();
			$objProducts = $this->Database->execute("SELECT * FROM tl_product_data");
			
			
			while( $objProducts->next() )
			{
				$arrImages = deserialize($objProducts->images);
				if (!is_array($arrImages))
					$arrImages = array();
				
				$strPattern = '@^(' . $objProducts->alias . '|' . standardize($objProducts->alias) . '|' . $objProducts->sku . '|' . standardize($objProducts->sku) . ')@i';
				$arrMatches = preg_grep($strPattern, $arrFiles);
				
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
						
						$this->Database->prepare("UPDATE tl_product_data SET images=? WHERE id=?")->execute(serialize($arrImages), $objProducts->id);
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
			
			$this->reload();
		}

		// Return form
		return '
<div id="tl_buttons">
<a href="'.ampersand(str_replace('&key=import', '', $this->Environment->request)).'" class="header_back" title="'.specialchars($GLOBALS['TL_LANG']['MSC']['backBT']).'">'.$GLOBALS['TL_LANG']['MSC']['backBT'].'</a>
</div>

<h2 class="sub_headline">'.$GLOBALS['TL_LANG']['tl_product_data']['import'][1].'</h2>'.$this->getMessages().'

<form action="'.ampersand($this->Environment->request, true).'" id="tl_product_data_import" class="tl_form" method="post">
<div class="tl_formbody_edit">
<input type="hidden" name="FORM_SUBMIT" value="tl_product_data_import" />

<div class="tl_tbox block">
  <h3><label for="source">'.$GLOBALS['TL_LANG']['tl_product_data']['source'][0].'</label> <a href="typolight/files.php" title="' . specialchars($GLOBALS['TL_LANG']['MSC']['fileManager']) . '" onclick="Backend.getScrollOffset(); this.blur(); Backend.openWindow(this, 750, 500); return false;">' . $this->generateImage('filemanager.gif', $GLOBALS['TL_LANG']['MSC']['fileManager'], 'style="vertical-align:text-bottom;"') . '</a></h3>'.$objTree->generate().(strlen($GLOBALS['TL_LANG']['tl_product_data']['source'][1]) ? '
  <p class="tl_help">'.$GLOBALS['TL_LANG']['tl_product_data']['source'][1].'</p>' : '').'
</div>

</div>

<div class="tl_formbody_submit">

<div class="tl_submit_container">
<input type="submit" name="save" id="save" class="tl_submit" alt="import product assets" accesskey="s" value="'.specialchars($GLOBALS['TL_LANG']['tl_product_data']['import'][0]).'" />
</div>

</div>
</form>';
	}
}

