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
		'label'                       => &$GLOBALS['TL_LANG']['MSC']['productsTitle'],
		'dataContainer'               => 'Table',
		'enableVersioning'            => false,
		'ctable'					  => array('tl_product_downloads', 'tl_product_categories'),
		'onload_callback'			  => array
		(
			array('tl_product_data', 'checkPermission'),
		),
	),
	
	// List
	'list' => array
	(
		'sorting' => array
		(
			'mode'                    => 5,
			//'fields'                  => array('type', 'name'),
			'icon'                    => 'pagemounts.gif',
			'paste_button_callback'   => array('tl_product_data', 'pasteProduct'),
//			'flag'                    => 1,
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
			'toggleNodes' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['MSC']['toggleNodes'],
				'href'                => 'ptg=all',
				'class'               => 'header_toggle'
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
				'icon'                => 'edit.gif',
				'button_callback'     => array('tl_product_data', 'editProduct')
			),
			'copy' => array
			(
				'label'				  => &$GLOBALS['TL_LANG']['tl_product_data']['copy'],
				'href'                => 'act=paste&amp;mode=copy',
				'icon'                => 'copy.gif',
				'attributes'          => 'onclick="Backend.getScrollOffset();"',
				'button_callback'     => array('tl_product_data', 'copyProduct')
			),
			'copyChilds' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_product_data']['copyChilds'],
				'href'                => 'act=paste&amp;mode=copy&amp;childs=1',
				'icon'                => 'copychilds.gif',
				'attributes'          => 'onclick="Backend.getScrollOffset();"',
				'button_callback'     => array('tl_product_data', 'copyProductWithSubproducts')
			)/*,
			'cut' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_product_data']['cut'],
				'href'                => 'act=paste&amp;mode=cut',
				'icon'                => 'cut.gif',
				'attributes'          => 'onclick="Backend.getScrollOffset();"',
				'button_callback'     => array('tl_product_data', 'cutProduct')
			),
			'toggle' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_product_data']['toggle'],
				'icon'                => 'visible.gif',
				'attributes'          => 'onclick="Backend.getScrollOffset(); return AjaxRequest.toggleVisibility(this, %s);"',
				'button_callback'     => array('tl_product_data', 'toggleIcon')
			)*/,
			'delete' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_product_data']['delete'],
				'href'                => 'act=delete',
				'icon'                => 'delete.gif',
				'attributes'          => 'onclick="if (!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\')) return false; Backend.getScrollOffset();"',
				'button_callback'     => array('tl_product_data', 'deleteProduct')
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
		'__selector__'				=> array('type', 'stock_enabled'),
		'default'					=> '{general_legend},type,alias',
	),
	
	// Subpalettes
	'subpalettes' => array
	(
		'stock_enabled'				=> 'stock_quantity,stock_oversell',
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
				array('tl_product_data','saveProductCategories'),
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
			'eval'					=> array('mandatory'=>true, 'tl_class'=>'clr long'),
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
		'price' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_product_data']['price'],
			'inputType'				=> 'text',
			'eval'					=> array('mandatory'=>true, 'maxlength'=>255, 'rgxp'=>'digits', 'tl_class'=>'w50'),
			'attributes'			=> array('legend'=>'pricing_legend'),
		),
		'price_override' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_product_data']['price_override'],
			'inputType'				=> 'text',
			'eval'					=> array('maxlength'=>255, 'tl_class'=>'w50'),
			'attributes'			=> array('legend'=>'pricing_legend'),
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
			'eval'					=> array('submitOnChange'=>true, 'tl_class'=>'clr'),
			'attributes'			=> array('legend'=>'inventory_legend'),
		),
		'stock_quantity' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_product_data']['stock_quantity'],
			'inputType'				=> 'text',
			'eval'					=> array('mandatory'=>true, 'rgxp'=>'digits', 'disabled'=>'disabled', 'tl_class'=>'w50'),
		),
		'stock_oversell' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_product_data']['stock_oversell'],
			'inputType'				=> 'checkbox',
			'eval'					=> array('tl_class'=>'w50 m12'),
		),
		'weight' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_product_data']['weight'],
			'inputType'				=> 'text',
			'eval'					=> array('rgxp'=>'digits', 'disabled'=>'disabled', 'tl_class'=>'w50'),
			'attributes'			=> array('legend'=>'shipping_legend'),
		),
		'shipping_exempt' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_product_data']['shipping_exempt'],
			'filter'				=> true,
			'inputType'				=> 'checkbox',
			'eval'					=> array('tl_class'=>'w50'),
			'attributes'			=> array('legend'=>'shipping_legend'),
		),
		'tax_class' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_product_data']['tax_class'],
			'filter'				=> true,
			'inputType'				=> 'select',
			'foreignKey'			=> 'tl_tax_class.name',
			'attributes'			=> array('legend'=>'tax_legend'),
			'eval'					=> array('includeBlankOption'=>true),
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
			'eval'					=> array('rgxp'=>'date', 'datepicker'=>$this->getDatePickerString(), 'tl_class'=>'w50 wizard'),
			'attributes'			=> array('legend'=>'publish_legend'),
		),
		'stop' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_product_data']['stop'],
			'inputType'				=> 'text',
			'eval'					=> array('rgxp'=>'date', 'datepicker'=>$this->getDatePickerString(), 'tl_class'=>'w50 wizard'),
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
		/*'option_set_source' => array
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
		),*/
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

		$this->import('BackendUser', 'User');
		$this->import('Isotope');
	}


	/**
	 * Add an image to each product in the tree
	 * @param array
	 * @param string
	 * @param string
	 * @param object
	 * @param boolean
	 * @return string
	 */
	public function addIcon($row, $label, $imageAttribute, DataContainer $dc=null, $blnReturnImage=false)
	{

		// Get image name
		$arrImage = deserialize($row['images']);
		
		if(count($arrImage))
		{
			$image = $arrImage['src'];
		}

		// Return the image only
		//if ($blnReturnImage)
		//{
			return $this->generateImage($image, '', $imageAttribute);
		//}

		// Add breadcrumb link
		//$label = '<a href="' . $this->addToUrl('node='.$row['id']) . '">' . $label . '</a>';

		// Return image
		//return '<a href="'.$this->generateFrontendUrl($row).'" title="'.specialchars($GLOBALS['TL_LANG']['MSC']['view']).'"' . (($dc->table != 'tl_page') ? ' class="tl_gray"' : '') . LINK_NEW_WINDOW . '>'.$this->generateImage($image, '', $imageAttribute).'</a> '.$label;
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
		return;		
		/*if ($this->User->isAdmin)
		{			
		
			$objPid = $this->Database->prepare("SELECT pid FROM tl_product_data WHERE id=?")
									 ->limit(1)
									 ->execute($dc->id);
			
			if(!$objPid->numRows)
			{
				$intPid = 0;
			}
			else
			{
				$intPid = $objPid->pid;
			}
			
			$arrProducts = $this->Database->execute("SELECT id FROM tl_product_data WHERE pid=" . $intPid)->fetchEach('id');
		
			if (!is_array($arrProducts) || !count($arrProducts))
			{
				$arrProducts = array(0);
			}
			
			$GLOBALS['TL_DCA']['tl_product_data']['list']['sorting']['root'] = $arrProducts;
			
			if (strlen($this->Input->get('id')) && !in_array($this->Input->get('id'), $arrProducts))
			{
				$this->redirect('typolight/main.php?act=error');
			}
			
			// Add access rights to new pages
			if ($this->Input->get('act') == 'create')
			{
				$GLOBALS['TL_DCA']['tl_page']['fields']['includeChmod']['default'] = 1;
			}
		}
		else
		{		
			$arrTypes = is_array($this->User->iso_product_types) ? $this->User->iso_product_types : array(0);
			
			$arrProducts = $this->Database->execute("SELECT id FROM tl_product_data WHERE pid=0 AND type IN ('','" . implode("','", $arrTypes) . "')")->fetchEach('id');
			
			if (!is_array($arrProducts) || !count($arrProducts))
			{
				$arrProducts = array(0);
			}
			
			$GLOBALS['TL_DCA']['tl_product_data']['list']['sorting']['root'] = $arrProducts;
			
			if (strlen($this->Input->get('id')) && !in_array($this->Input->get('id'), $arrProducts))
			{
				$this->redirect('typolight/main.php?act=error');
			}
		}*/
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
		
		$output = '<div style="margin-top:5px!important;margin-bottom:0px!important;" class="cte_type ' . $key . '"><div><span>' . $thumbnail . '<strong>' . $row['name'] . '</strong></span><div><span style="color:#b3b3b3;"><strong>' . ($row['pid']!=0 ? $this->getVariantValues($row) : '') . $this->Isotope->formatPriceWithCurrency($row['price']) . '</strong></span></div><br /><br /><div>' . ($row['pid']==0 ? '<em>' . $GLOBALS['TL_LANG']['tl_product_data']['pages'][0] .': ' . $this->getCategoryList(deserialize($row['pages'])) . '</em>' : '') . '</div></div></div> ';
		
		$fields = array();
		
		return $output;
	}
	
	public function getVariantValues($row)
	{	
			$objVariantAttributes = $this->Database->prepare("SELECT name, field_name FROM tl_product_attributes WHERE add_to_product_variants=?")
									  				->execute(1);
			if(!$objVariantAttributes->numRows)
			{
				return '';
			}
			
			while($objVariantAttributes->next())
			{
				$strField = $objVariantAttributes->field_name;
				
				if(array_key_exists($strField, $row))
				{
					$arrVariantValues[] = array
					(
						'label'		=> $objVariantAttributes->name,
						'value'		=> $row[$strField]
					);
				}
			}
	
			if(count($arrVariantValues))
			{
				$strReturn = '<ul>';
				
					foreach($arrVariantValues as $record)
					{
						$strReturn .= '<li>' . $record['label'] . ': ' . $record['value'] . '</li>';
					}		
				
				$strReturn .= '</ul>';			
			}
			
			return $strReturn;
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
		$arrPages = array();
		
		if(!is_array($varValue) || !count($varValue))
		{
			return $GLOBALS['TL_LANG']['MSC']['noCategoriesAssociated'];
		}
				
		$arrCategories = array();
		
		foreach( $varValue as $intPage )
		{
			if (!$arrPages[$intPage])
			{
				$objPage = $this->getPageDetails($intPage);
				if(count($objPage->trail))
				{
					$arrPages[$intPage]['title'] = $objPage->title;
								
					$objPages = $this->Database->execute("SELECT * FROM tl_page WHERE id IN (" . implode(',', $objPage->trail) . ") ORDER BY id=" . implode(' DESC, id=', $objPage->trail) . " DESC");
				
								
					$arrHelp = array();
					while( $objPages->next() )
					{
						$arrHelp[] = $objPages->title;
					}
					
					$arrPages[$intPage]['help'] = implode(' , ', $arrHelp);
				}
			}
			
			
			$arrCategories[] = '<a class="tl_tip" longdesc="' . $arrPages[$intPage]['help'] . '" href="' . $this->addToUrl('table=tl_product_categories&id='.$intPage) . '">' . $arrPages[$intPage]['title'] . '</a>';
		}
		
		return implode(', ', $arrCategories);
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
			$varValue = strlen($objProduct->name) ? standardize($objProduct->name) : standardize($objProduct->sku);
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
	 * Save page ids to tl_product_categories table. This allows to retrieve all products associated to a page.
	 */
	public function saveProductCategories($varValue, DataContainer $dc)
	{
		$arrIds = deserialize($varValue);
		
		if (is_array($arrIds) && count($arrIds))
		{
			$time = time();
			$this->Database->prepare("DELETE FROM tl_product_categories WHERE pid=? AND page_id NOT IN (" . implode(',', $arrIds) . ")")->execute($dc->id);
			$objPages = $this->Database->prepare("SELECT page_id FROM tl_product_categories WHERE pid=?")->execute($dc->id);
			$arrIds = array_diff($arrIds, $objPages->fetchEach('page_id'));
			
			foreach( $arrIds as $id )
			{
				$intSorting = $this->Database->prepare("SELECT sorting FROM tl_product_categories WHERE page_id=? ORDER BY sorting DESC")->limit(1)->execute($id)->sorting;
				$intSorting += 128;
				$this->Database->prepare("INSERT INTO tl_product_categories (pid,tstamp,page_id,sorting) VALUES (?,?,?,?)")->execute($dc->id, $time, $id, $intSorting);
			}
		}
		else
		{
			$this->Database->prepare("DELETE FROM tl_product_categories WHERE pid=?")->execute($dc->id);
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

		/**
	 * Return the edit page button
	 * @param array
	 * @param string
	 * @param string
	 * @param string
	 * @param string
	 * @param string
	 * @return string
	 */
	public function editProduct($row, $href, $label, $title, $icon, $attributes)
	{
		$objParentType = $this->Database->prepare("SELECT type FROM tl_product_data WHERE pid=? OR id=?")
										->limit(1)
										->execute($row['id'], $row['id']);
		
		if(!$objParentType->numRows)
		{
			return '';
		}
		
		return ($this->User->isAdmin || (in_array($objParentType->type, $this->User->iso_product_types) && $this->User->isAllowed(1, $row))) ? '<a href="'.$this->addToUrl($href.'&amp;id='.$row['id']).'" title="'.specialchars($title).'"'.$attributes.'>'.$this->generateImage($icon, $label).'</a> ' : $this->generateImage(preg_replace('/\.gif$/i', '_.gif', $icon)).' ';
	
	}


	/**
	 * Return the copy page button
	 * @param array
	 * @param string
	 * @param string
	 * @param string
	 * @param string
	 * @param string
	 * @param string
	 * @return string
	 */
	public function copyProduct($row, $href, $label, $title, $icon, $attributes, $table)
	{
		if ($GLOBALS['TL_DCA'][$table]['config']['closed'])
		{
			return '';
		}

		return ($this->User->isAdmin || (in_array($row['type'], $this->User->iso_product_types) && $this->User->isAllowed(2, $row))) ? '<a href="'.$this->addToUrl($href.'&amp;id='.$row['id']).'" title="'.specialchars($title).'"'.$attributes.'>'.$this->generateImage($icon, $label).'</a> ' : $this->generateImage(preg_replace('/\.gif$/i', '_.gif', $icon)).' ';
	}


	/**
	 * Return the copy page with subpages button
	 * @param array
	 * @param string
	 * @param string
	 * @param string
	 * @param string
	 * @param string
	 * @param string
	 * @return string
	 */
	public function copyProductWithSubproducts($row, $href, $label, $title, $icon, $attributes, $table)
	{
		if ($GLOBALS['TL_DCA'][$table]['config']['closed'] || $row['pid']>0)
		{
			return '';
		}

		$objSubpages = $this->Database->prepare("SELECT * FROM tl_product_data WHERE pid=?")
									  ->limit(1)
									  ->execute($row['id']);

		return ($objSubpages->numRows && ($this->User->isAdmin || (in_array($row['type'], $this->User->iso_product_types) && $this->User->isAllowed(2, $row)))) ? '<a href="'.$this->addToUrl($href.'&amp;id='.$row['id']).'" title="'.specialchars($title).'"'.$attributes.'>'.$this->generateImage($icon, $label).'</a> ' : $this->generateImage(preg_replace('/\.gif$/i', '_.gif', $icon)).' ';
	}


	/**
	 * Return the cut page button
	 * @param array
	 * @param string
	 * @param string
	 * @param string
	 * @param string
	 * @param string
	 * @return string
	 */
	public function cutProduct($row, $href, $label, $title, $icon, $attributes)
	{
		return ($this->User->isAdmin || (in_array($row['type'], $this->User->iso_product_types) && $this->User->isAllowed(2, $row))) ? '<a href="'.$this->addToUrl($href.'&amp;id='.$row['id']).'" title="'.specialchars($title).'"'.$attributes.'>'.$this->generateImage($icon, $label).'</a> ' : $this->generateImage(preg_replace('/\.gif$/i', '_.gif', $icon)).' ';
	}


	/**
	 * Return the paste page button
	 * @param object
	 * @param array
	 * @param string
	 * @param boolean
	 * @param array
	 * @return string
	 */
	public function pasteProduct(DataContainer $dc, $row, $table, $cr, $arrClipboard=false)
	{
		$disablePA = false;
		$disablePI = false;

		// Disable all buttons if there is a circular reference
		if ($arrClipboard !== false && ($arrClipboard['mode'] == 'cut' && ($cr == 1 || $arrClipboard['id'] == $row['id']) || $arrClipboard['mode'] == 'cutAll' && ($cr == 1 || in_array($row['id'], $arrClipboard['id']))))
		{
			$disablePA = true;
			$disablePI = true;
		}
echo $disablePI;
		// Check permissions if the user is not an administrator
		if (!$this->User->isAdmin)
		{
			// Disable "paste into" button if there is no permission 2 for the current page
			if (!$disablePI && !$this->User->isAllowed(2, $row) || $row['pid']>0)
			{
				$disablePI = true;
			}

			$objProduct = $this->Database->prepare("SELECT * FROM " . $table . " WHERE id=?")
									  ->limit(1)
									  ->execute($row['pid']);

			// Disable "paste after" button if there is no permission 2 for the parent page
			if (!$disablePA && $objProduct->numRows)
			{
				if (!$this->User->isAllowed(2, $objProduct->fetchAssoc()))
				{
					$disablePA = true;
				}
			}

			// Disable "paste after" button if the parent page is a root page and the user is not an administrator
			if (!$disablePA && ($row['pid'] < 1 || in_array($row['id'], $dc->rootIds)))
			{
				$disablePA = true;
			}
		}
		
		// Disable "paste into" button if there is no permission 2 for the current page
		if ($row['pid']>0)
		{
			$disablePI = true;
		}

		// Return the buttons
		$imagePasteAfter = $this->generateImage('pasteafter.gif', sprintf($GLOBALS['TL_LANG'][$table]['pasteafter'][1], $row['id']), 'class="blink"');
		$imagePasteInto = $this->generateImage('pasteinto.gif', sprintf($GLOBALS['TL_LANG'][$table]['pasteinto'][1], $row['id']), 'class="blink"');

		if ($row['id'] > 0)
		{
			$return = $disablePA ? $this->generateImage('pasteafter_.gif', '', 'class="blink"').' ' : '<a href="'.$this->addToUrl('act='.$arrClipboard['mode'].'&amp;mode=1&amp;pid='.$row['id'].(!is_array($arrClipboard['id']) ? '&amp;id='.$arrClipboard['id'] : '')).'" title="'.specialchars(sprintf($GLOBALS['TL_LANG'][$table]['pasteafter'][1], $row['id'])).'" onclick="Backend.getScrollOffset();">'.$imagePasteAfter.'</a> ';
		}

		return $return.($disablePI ? $this->generateImage('pasteinto_.gif', '', 'class="blink"').' ' : '<a href="'.$this->addToUrl('act='.$arrClipboard['mode'].'&amp;mode=2&amp;pid='.$row['id'].(!is_array($arrClipboard['id']) ? '&amp;id='.$arrClipboard['id'] : '')).'" title="'.specialchars(sprintf($GLOBALS['TL_LANG'][$table]['pasteinto'][1], $row['id'])).'" onclick="Backend.getScrollOffset();">'.$imagePasteInto.'</a> ');
	}


	/**
	 * Return the delete page button
	 * @param array
	 * @param string
	 * @param string
	 * @param string
	 * @param string
	 * @param string
	 * @return string
	 */
	public function deleteProduct($row, $href, $label, $title, $icon, $attributes)
	{
		$root = func_get_arg(7);
		return ($this->User->isAdmin || (in_array($row['type'], $this->User->iso_product_types) && $this->User->isAllowed(3, $row) && !in_array($row['id'], $root))) ? '<a href="'.$this->addToUrl($href.'&amp;id='.$row['id']).'" title="'.specialchars($title).'"'.$attributes.'>'.$this->generateImage($icon, $label).'</a> ' : $this->generateImage(preg_replace('/\.gif$/i', '_.gif', $icon)).' ';
	}

}

