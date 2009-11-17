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
			array('MediaManagement', 'createMediaDirectoryStructure')
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
			'fields'                  => array('product_name'),
			'format'                  => '%s',
			'label_callback'		  => array('tl_product_data','getRowLabel'),
		),
		'global_operations' => array
		(
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
		'__selector__'				  => array('type'),
		'default'					  => '{general_legend},type,alias',
	),
	
	// Fields
	'fields' => array
	(
		'type' => array
		(
			'label'					  =>  &$GLOBALS['TL_LANG']['tl_product_data']['type'],
			'inputType'				  => 'select',
			'filter'				  => true,
			'eval'					  => array('mandatory'=>true, 'includeBlankOption'=>true, 'submitOnChange'=>true),
			'options_callback'		  => array('tl_product_data', 'getProductTypes'),
		),
		'pages' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_product_data']['pages'],
			'filter'				  => true,
			'inputType'				  => 'pageTree',
			'foreignKey'			  => 'tl_page.title',
			'eval'                    => array('mandatory'=>false, 'multiple'=>true, 'fieldType'=>'checkbox'),
			'save_callback'			  => array
			(
				array('ProductCatalog','saveProductCategories'),
			),
			//'explanation'             => 'pageCategories'
		),
		'alias' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_product_data']['alias'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => array('rgxp'=>'alnum', 'doNotCopy'=>true, 'spaceToUnderscore'=>true, 'maxlength'=>128, 'tl_class'=>'clr'),
			'save_callback' => array
			(
				array('tl_product_data', 'generateAlias')
			)

		),/*
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
			'options_callback'		  => array('ProductCatalog','getOptionSets')
		),
		'option_sets' => array
		(
			'label'					  =>  &$GLOBALS['TL_LANG']['tl_product_data']['option_sets'],
			'inputType'				  => 'select',
			'eval'					  => array('includeBlankOption'=>true, 'submitOnChange'=>true),
			'options_callback'		  => array('ProductCatalog','getProductOptionSets')
		),
		'option_set_title' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_product_data']['option_set_title'],
			'search'                  => true,
			'inputType'               => 'text',
			'eval'                    => array('rgxp'=>'extnd', 'maxlength'=>255)
		),
		'variants_wizard' => array
		(
			'label'					  => &$GLOBALS['TL_LANG']['tl_product_data']['variants_wizard'],
			'inputType' 			  => 'variantsWizard',
			'eval'					  => array('mandatory'=>false, 'enableDelete'=>false, 'helpwizard'=>false),
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
		'published' => array
		(
			'label'					  => &$GLOBALS['TL_LANG']['tl_product_data']['published'],
			'inputType'				  => 'checkbox',
			'filter'				  => true,
			'eval'					  => array('doNotCopy'=>true),
		),
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
		
		$arrImages = deserialize($row['main_image']);
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
}

