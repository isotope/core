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
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */


/**
 * Table tl_store 
 */
$GLOBALS['TL_DCA']['tl_store'] = array
(

	// Config
	'config' => array
	(
		'dataContainer'               => 'Table',
		'switchToEdit'				  => true,
		'enableVersioning'            => true,
		'onload_callback' => array
		(
			array('tl_store', 'checkPermission'),
		),
	),

	// List
	'list' => array
	(
		'sorting' => array
		(
			'mode'                    => 1,
			'fields'                  => array('store_configuration_name')
		),
		'label' => array
		(
			'fields'                  => array('store_configuration_name'),
			'format'                  => '%s'
		),
		'global_operations' => array
		(
			'all' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['MSC']['all'],
				'href'                => 'act=select',
				'class'               => 'header_edit_all',
				'attributes'          => 'onclick="Backend.getScrollOffset();"'
			)
		),
		'operations' => array
		(
			'edit' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_store']['edit'],
				'href'                => 'act=edit',
				'icon'                => 'edit.gif'
			),
			'copy' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_store']['copy'],
				'href'                => 'act=copy',
				'icon'                => 'copy.gif'
			),
			'delete' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_store']['delete'],
				'href'                => 'act=delete',
				'icon'                => 'delete.gif',
				'attributes'          => 'onclick="if (!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\')) return false; Backend.getScrollOffset();"'
			),
			'show' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_store']['show'],
				'href'                => 'act=show',
				'icon'                => 'show.gif'
			)
		)
	),

	// Palettes
	'palettes' => array
	(
		'default'                     => '{name_legend},store_configuration_name,label;{config_legend},cookie_duration,isDefaultStore;{module_legend},checkout_login_module;{price_legend},priceField,priceOverrideField,priceCalculateFactor,priceCalculateMode,priceRoundPrecision,priceRoundIncrement;{currency_legend},currency,currencySymbol,currencyFormat,currencyPosition;{address_legend},country,countries,address_fields;{redirect_legend},productReaderJumpTo,cartJumpTo,checkoutJumpTo;{invoice_legend},invoiceLogo;{images_legend},root_asset_import_path,missing_image_placeholder,gallery_thumbnail_image_width,gallery_thumbnail_image_height,thumbnail_image_width,thumbnail_image_height,medium_image_width,medium_image_height,large_image_width,large_image_height'
	),

	// Fields
	'fields' => array
	(
		'store_configuration_name' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_store']['store_configuration_name'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => array('rgxp'=>'alnum', 'mandatory'=>true, 'maxlength'=>255, 'tl_class'=>'w50'),
		),
		'label' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_store']['label'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => array('maxlength'=>255, 'tl_class'=>'w50'),
		),
		'cookie_duration' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_store']['cookie_duration'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => array('rgxp'=>'digit', 'maxlength'=>4, 'tl_class'=>'w50')
		),
		'isDefaultStore' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_store']['isDefaultStore'],
			'exclude'                 => true,
			'inputType'               => 'checkbox',
			'eval'						=> array('doNotCopy'=>true, 'fallback'=>true, 'tl_class'=>'w50 m12'),
		),
		'root_asset_import_path' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_store']['root_asset_import_path'],
			'exclude'                 => true,
			'inputType'               => 'fileTree',
			'eval'                    => array('fieldType'=>'radio', 'mandatory'=>false)
		),
		'checkout_login_module' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_store']['checkout_login_module'],
			'exclude'                 => true,
			'inputType'               => 'radio',
			'options_callback'        => array('tl_store', 'getLoginModuleList'),
			'eval'                    => array('moduleTypes'=>array('login'))
		),
		'productReaderJumpTo' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_module']['productReaderJumpTo'],
			'exclude'                 => true,
			'inputType'               => 'pageTree',
			'explanation'             => 'jumpTo',
			'eval'                    => array('fieldType'=>'radio', 'helpwizard'=>true)
		),
		'cartJumpTo' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_module']['cartJumpTo'],
			'exclude'                 => true,
			'inputType'               => 'pageTree',
			'explanation'             => 'jumpTo',
			'eval'                    => array('fieldType'=>'radio', 'helpwizard'=>true)
		),
		'checkoutJumpTo' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_module']['checkoutJumpTo'],
			'exclude'                 => true,
			'inputType'               => 'pageTree',
			'explanation'             => 'jumpTo',
			'eval'                    => array('fieldType'=>'radio', 'helpwizard'=>true)
		),
		'missing_image_placeholder' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_store']['missing_image_placeholder'],
			'exclude'                 => true,
			'inputType'               => 'fileTree',
			'eval'                    => array('fieldType'=>'radio', 'files'=>true, 'filesOnly'=>true, 'extensions' => 'jpg,jpeg,gif,png,tif,tiff', 'tl_class'=>'clr'),
		),
		'invoiceLogo' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_store']['invoiceLogo'],
			'exclude'                 => true,
			'inputType'               => 'fileTree',
			'eval'                    => array('fieldType'=>'radio', 'files'=>true, 'filesOnly'=>true, 'extensions' => 'jpg,jpeg,gif,png,tif,tiff')
		),
		'gallery_thumbnail_image_width' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_store']['gallery_thumbnail_image_width'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => array('rgxp'=>'digit', 'mandatory'=>true, 'maxlength'=>10, 'tl_class'=>'w50')
		),
		'gallery_thumbnail_image_height' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_store']['gallery_thumbnail_image_height'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => array('rgxp'=>'digit', 'maxlength'=>10, 'tl_class'=>'w50')
		), 
		'thumbnail_image_width' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_store']['thumbnail_image_width'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => array('rgxp'=>'digit', 'mandatory'=>true, 'maxlength'=>10, 'tl_class'=>'w50')
		),
		'thumbnail_image_height' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_store']['thumbnail_image_height'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => array('rgxp'=>'digit', 'maxlength'=>10, 'tl_class'=>'w50')
		),
		'medium_image_width' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_store']['medium_image_width'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => array('rgxp'=>'digit', 'mandatory'=>true, 'maxlength'=>10, 'tl_class'=>'w50')
		),
		'medium_image_height' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_store']['medium_image_height'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => array('rgxp'=>'digit', 'maxlength'=>10, 'tl_class'=>'w50')
		),
		'large_image_width' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_store']['large_image_width'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => array('rgxp'=>'digit', 'mandatory'=>true, 'maxlength'=>10, 'tl_class'=>'w50')
		),
		'large_image_height' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_store']['large_image_height'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => array('rgxp'=>'digit', 'maxlength'=>10, 'tl_class'=>'w50'),
		),		
		'country' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_store']['country'],
			'exclude'                 => true,
			'inputType'               => 'select',
			'default'				  => $this->User->country,
			'options'				  => $this->getCountries(),
			'eval'                    => array('includeBlankOption'=>true, 'mandatory'=>true),
		),
		'priceField' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_store']['priceField'],
			'exclude'                 => true,
			'inputType'               => 'select',
			'eval'                    => array('mandatory'=>true, 'tl_class'=>'w50'),
			'options_callback'		  => array('tl_store', 'getPriceFields'),
		),
		'priceOverrideField' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_store']['priceOverrideField'],
			'exclude'                 => true,
			'inputType'               => 'select',
			'eval'                    => array('mandatory'=>true, 'tl_class'=>'w50'),
			'options_callback'		  => array('tl_store', 'getPriceFields'),
		),
		'priceCalculateFactor' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_store']['priceCalculateFactor'],
			'exclude'                 => true,
			'default'				  => 1,
			'inputType'               => 'text',
			'eval'                    => array('mandatory'=>true, 'maxlength'=>10, 'rgxp'=>'digits', 'tl_class'=>'w50'),
		),
		'priceCalculateMode' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_store']['priceCalculateMode'],
			'exclude'                 => true,
			'default'				  => 'mul',
			'inputType'               => 'radio',
			'options'				  => array('mul', 'div'),
			'reference'				  => &$GLOBALS['TL_LANG']['tl_store'],
			'eval'                    => array('mandatory'=>true, 'tl_class'=>'w50'),
		),
		'priceRoundPrecision' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_store']['priceRoundPrecision'],
			'exclude'                 => true,
			'default'				  => '2',
			'inputType'               => 'text',
			'eval'                    => array('mandatory'=>true, 'maxlength'=>1, 'rgpx'=>'digits', 'tl_class'=>'w50'),
		),
		'priceRoundIncrement' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_store']['priceRoundIncrement'],
			'exclude'                 => true,
			'inputType'               => 'select',
			'options'				  => array('0.01', '0.05'),
			'eval'                    => array('mandatory'=>true, 'tl_class'=>'w50'),
		),
		'currency' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_store']['currency'],
			'exclude'                 => true,
			'inputType'               => 'select',
			'options'				  => &$GLOBALS['TL_LANG']['CUR'],
			'eval'                    => array('includeBlankOption'=>true, 'mandatory'=>true, 'tl_class'=>'w50'),
		),
		'currencySymbol' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_store']['currencySymbol'],
			'exclude'                 => true,
			'inputType'               => 'checkbox',
			'eval'					  => array('tl_class'=>'w50 m12'),
		),
		'currencyPosition' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_store']['currencyPosition'],
			'exclude'                 => true,
			'inputType'               => 'radio',
			'default'				  => 'left',
			'options'				  => array('left', 'right'),
			'reference'				  => &$GLOBALS['TL_LANG']['tl_store'],
			'eval'					  => array('tl_class'=>'w50'),
		),
		'currencyFormat' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_store']['currencyFormat'],
			'exclude'                 => true,
			'inputType'               => 'select',
			'options'				  => array_keys($GLOBALS['ISO_NUM']),
			'eval'                    => array('includeBlankOption'=>true, 'mandatory'=>true, 'tl_class'=>'w50'),
		),
		'countries' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_store']['countries'],
			'exclude'                 => true,
			'inputType'               => 'select',
			'default'                 => array_keys($this->getCountries()),
			'options'                 => $this->getCountries(),
			'eval'                    => array('mandatory'=>true, 'multiple'=>true, 'size'=>8, 'tl_class'=>'clr'),
		),
		'address_fields' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_store']['address_fields'],
			'exclude'                 => true,
			'inputType'               => 'checkboxWizard',
			'options_callback'		  => array('tl_store', 'getAddressFields'),
			'eval'                    => array('mandatory'=>true, 'multiple'=>true, 'tl_class'=>'clr'),
		),
		'orderPrefix' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_store']['orderPrefix'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => array('maxlength'=>4),
		),
	)
);


/**
 * tl_store class.
 * 
 * @extends Backend
 */
class tl_store extends Backend
{

	public function checkPermission($dc)
	{
		$this->import('BackendUser', 'User');
		
		if ($this->User->isAdmin)
			return;
			
		$arrStores = $this->User->iso_stores;
		
		if (!is_array($arrStores) || !count($arrStores))
		{
			$arrStores = array(0);
		}
		
		$GLOBALS['TL_DCA']['tl_store']['list']['sorting']['root'] = $arrStores;
		
		if (strlen($this->Input->get('id')) && !in_array($this->Input->get('id'), $arrStores))
		{
			$this->redirect('typolight/main.php?act=error');
		}
	}
	

	/**
	 * Return all fields that are price fields.
	 */
	public function getPriceFields()
	{
		$objPricingFields = $this->Database->execute("SELECT field_name, name FROM tl_product_attributes WHERE fieldGroup='pricing_legend' AND (type='integer' OR type='decimal')");
		
		if($objPricingFields->numRows < 1)
		{
			return array();			
		}
		
		while($objPricingFields->next())
		{
			$arrPricingData[$objPricingFields->field_name] = $objPricingFields->name;
		}
		
		return $arrPricingData;
		
	}
	
	
	/**
	 * getLoginModuleList function.
	 * 
	 * @access public
	 * @return array
	 */
	public function getLoginModuleList()
	{
		$return = array();

		$this->loadDataContainer('tl_store');

		$strModuleTypes = join("','", $GLOBALS['TL_DCA']['tl_store']['fields']['checkout_login_module']['eval']['moduleTypes']);
		
		$strModuleTypes = "'" . $strModuleTypes . "'";
		
		$objLoginModules = $this->Database->prepare("SELECT id, name FROM tl_module WHERE type IN(" . $strModuleTypes . ")")
										  ->execute();
										  
		if($objLoginModules->numRows < 1)
		{
			return '<em>' . $GLOBALS['TL_LANG']['MSC']['noLoginModulesDefined'] . '</em>';
		}
		
		$arrLoginModules = $objLoginModules->fetchAllAssoc();

		foreach ($arrLoginModules as $moduleKey)
		{
			$return[$moduleKey['id']] = $moduleKey['name'];
		}

		return $return;
	}
	
	
	/**
	 * Get all checkout fields in tl_address_book.
	 * 
	 * @todo check if we need to use param "isoEditable"
	 * @access public
	 * @param object $dc
	 * @return array
	 */
	public function getAddressFields($dc)
	{
		$arrFields = array();
		
		$this->loadLanguageFile('tl_address_book');
		$this->loadDataContainer('tl_address_book');
		
		foreach( $GLOBALS['TL_DCA']['tl_address_book']['fields'] as $strField => $arrData )
		{
			if ($arrData['eval']['isoEditable'])
			{
				$arrFields[$strField] = strlen($arrData['label'][0]) ? $arrData['label'][0] : $strField;
			}
		}
		
		return $arrFields;
	}
}

