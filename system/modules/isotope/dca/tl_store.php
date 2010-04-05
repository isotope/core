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


/**
 * Table tl_store 
 */
$GLOBALS['TL_DCA']['tl_store'] = array
(

	// Config
	'config' => array
	(
		'dataContainer'               => 'Table',
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
			'fields'                  => array('store_configuration_name'),
			'flag'					  => 1,
		),
		'label' => array
		(
			'fields'                  => array('store_configuration_name'),
			'format'                  => '%s',
		),
		'global_operations' => array
		(
			'all' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['MSC']['all'],
				'href'                => 'act=select',
				'class'               => 'header_edit_all',
				'attributes'          => 'onclick="Backend.getScrollOffset();"',
			)
		),
		'operations' => array
		(
			'edit' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_store']['edit'],
				'href'                => 'act=edit',
				'icon'                => 'edit.gif',
			),
			'copy' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_store']['copy'],
				'href'                => 'act=copy',
				'icon'                => 'copy.gif',
			),
			'delete' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_store']['delete'],
				'href'                => 'act=delete',
				'icon'                => 'delete.gif',
				'attributes'          => 'onclick="if (!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\')) return false; Backend.getScrollOffset();"',
			),
			'show' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_store']['show'],
				'href'                => 'act=show',
				'icon'                => 'show.gif',
			)
		)
	),

	// Palettes
	'palettes' => array
	(
		'default'                     => '{name_legend},store_configuration_name,label;{address_legend:hide},firstname,lastname,company,street_1,street_2,street_3,postal,city,subdivision,country,emailShipping,phone,shipping_countries,billing_countries,shipping_fields,billing_fields;{config_legend},weightUnit,cookie_duration,isDefaultStore,enableGoogleAnalytics;{price_legend},priceField,priceOverrideField,priceCalculateFactor,priceCalculateMode,priceRoundPrecision,priceRoundIncrement;{currency_legend},currency,currencySymbol,currencyFormat,currencyPosition;{redirect_legend},cartJumpTo,checkoutJumpTo;{invoice_legend},invoiceLogo;{images_legend},missing_image_placeholder,gallery_size,thumbnail_size,medium_size,large_size',
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
		'firstname' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_store']['firstname'],
			'exclude'                 => true,
			'search'                  => true,
			'sorting'                 => true,
			'flag'                    => 1,
			'inputType'               => 'text',
			'eval'                    => array('maxlength'=>255, 'tl_class'=>'w50'),
		),
		'lastname' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_store']['lastname'],
			'exclude'                 => true,
			'search'                  => true,
			'sorting'                 => true,
			'flag'                    => 1,
			'inputType'               => 'text',
			'eval'                    => array('maxlength'=>255, 'tl_class'=>'w50'),
		),
		'company' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_store']['company'],
			'exclude'                 => true,
			'search'                  => true,
			'sorting'                 => true,
			'flag'                    => 1,
			'inputType'               => 'text',
			'eval'                    => array('maxlength'=>255, 'tl_class'=>'w50'),
		),
		'street_1' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_store']['street_1'],
			'exclude'                 => true,
			'search'                  => true,
			'inputType'               => 'text',
			'eval'                    => array('maxlength'=>255, 'tl_class'=>'w50'),
		),
		'street_2' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_store']['street_2'],
			'exclude'                 => true,
			'search'                  => true,
			'inputType'               => 'text',
			'eval'                    => array('maxlength'=>255, 'tl_class'=>'w50'),
		),
		'street_3' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_store']['street_3'],
			'exclude'                 => true,
			'search'                  => true,
			'inputType'               => 'text',
			'eval'                    => array('maxlength'=>255, 'tl_class'=>'w50'),
		),
		'postal' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_store']['postal'],
			'exclude'                 => true,
			'search'                  => true,
			'inputType'               => 'text',
			'eval'                    => array('maxlength'=>32, 'tl_class'=>'w50'),
		),
		'city' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_store']['city'],
			'exclude'                 => true,
			'filter'                  => true,
			'search'                  => true,
			'sorting'                 => true,
			'inputType'               => 'text',
			'eval'                    => array('maxlength'=>255, 'tl_class'=>'w50'),
		),
		'subdivision' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_store']['subdivision'],
			'exclude'                 => true,
			'sorting'                 => true,
			'inputType'               => 'text',
			'eval'                    => array('maxlength'=>64, 'tl_class'=>'w50'),
		),
		'country' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_store']['country'],
			'exclude'                 => true,
			'filter'                  => true,
			'sorting'                 => true,
			'inputType'               => 'select',
			'default'				  => $this->User->country,
			'options'                 => $this->getCountries(),
			'eval'                    => array('mandatory'=>true, 'includeBlankOption'=>true, 'tl_class'=>'w50'),
		),
		'phone' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_store']['phone'],
			'exclude'                 => true,
			'search'                  => true,
			'inputType'               => 'text',
			'eval'                    => array('maxlength'=>64, 'rgxp'=>'phone', 'tl_class'=>'w50'),
		),
		'emailShipping' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_store']['emailShipping'],
			'exclude'                 => true,
			'search'                  => true,
			'inputType'               => 'text',
			'eval'                    => array('maxlength'=>64, 'rgxp'=>'email', 'tl_class'=>'w50'),
		),
		'shipping_countries' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_store']['shipping_countries'],
			'exclude'                 => true,
			'inputType'               => 'select',
//			'default'                 => array_keys($this->getCountries()),
			'options'                 => $this->getCountries(),
			'eval'                    => array('mandatory'=>true, 'multiple'=>true, 'size'=>8, 'tl_class'=>'w50'),
		),
		'shipping_fields' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_store']['shipping_fields'],
			'exclude'                 => true,
			'inputType'               => 'checkboxWizard',
			'options_callback'		  => array('tl_store', 'getAddressFields'),
			'eval'                    => array('mandatory'=>true, 'multiple'=>true, 'tl_class'=>'w50 w50h'),
		),
		'billing_countries' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_store']['billing_countries'],
			'exclude'                 => true,
			'inputType'               => 'select',
//			'default'                 => array_keys($this->getCountries()),
			'options'                 => $this->getCountries(),
			'eval'                    => array('mandatory'=>true, 'multiple'=>true, 'size'=>8, 'tl_class'=>'w50 w50h'),
		),
		'billing_fields' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_store']['billing_fields'],
			'exclude'                 => true,
			'inputType'               => 'checkboxWizard',
			'options_callback'		  => array('tl_store', 'getAddressFields'),
			'eval'                    => array('mandatory'=>true, 'multiple'=>true, 'tl_class'=>'w50 w50h'),
		),
		'enableGoogleAnalytics' => array
		(
			'label'                   => array($GLOBALS['TL_LANG']['tl_store']['enableGoogleAnalytics'][0], (!file_exists(TL_ROOT . '/system/modules/googleanalytics/GoogleAnalytics.php') ? $GLOBALS['TL_LANG']['MSC']['missingGoogleAnalyticsExtension'] : $GLOBALS['TL_LANG']['tl_store']['enableGoogleAnalytics'][0])),
			'exclude'                 => true,
			'inputType'               => 'checkbox',
		),
		'cookie_duration' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_store']['cookie_duration'],
			'exclude'                 => true,
			'default'				  => 30,
			'inputType'               => 'text',
			'eval'                    => array('rgxp'=>'digit', 'maxlength'=>4, 'tl_class'=>'w50 w50h'),
		),
		'isDefaultStore' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_store']['isDefaultStore'],
			'exclude'                 => true,
			'inputType'               => 'checkbox',
			'eval'						=> array('doNotCopy'=>true, 'fallback'=>true, 'tl_class'=>'w50 m12'),
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
			'eval'                    => array('fieldType'=>'radio', 'files'=>true, 'filesOnly'=>true, 'extensions' => 'jpg,jpeg,gif,png,tif,tiff'),
		),
		'gallery_size'				  => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_store']['gallery_size'],
			'exclude'                 => true,
			'inputType'               => 'imageSize',
			'options'                 => array('crop', 'proportional', 'box'),
			'reference'               => &$GLOBALS['TL_LANG']['MSC'],
			'eval'                    => array('rgxp'=>'digit', 'nospace'=>true, 'tl_class'=>'w50'),
		),
		'thumbnail_size'				  => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_store']['thumbnail_size'],
			'exclude'                 => true,
			'inputType'               => 'imageSize',
			'options'                 => array('crop', 'proportional', 'box'),
			'reference'               => &$GLOBALS['TL_LANG']['MSC'],
			'eval'                    => array('rgxp'=>'digit', 'nospace'=>true, 'tl_class'=>'w50'),
		),
		'medium_size'				  => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_store']['medium_size'],
			'exclude'                 => true,
			'inputType'               => 'imageSize',
			'options'                 => array('crop', 'proportional', 'box'),
			'reference'               => &$GLOBALS['TL_LANG']['MSC'],
			'eval'                    => array('rgxp'=>'digit', 'nospace'=>true, 'tl_class'=>'w50'),
		),
		'large_size'				  => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_store']['large_size'],
			'exclude'                 => true,
			'inputType'               => 'imageSize',
			'options'                 => array('crop', 'proportional', 'box'),
			'reference'               => &$GLOBALS['TL_LANG']['MSC'],
			'eval'                    => array('rgxp'=>'digit', 'nospace'=>true, 'tl_class'=>'w50'),
		),
		'priceField' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_store']['priceField'],
			'exclude'                 => true,
			'default'				  => 'price',
			'inputType'               => 'select',
			'eval'                    => array('mandatory'=>true, 'tl_class'=>'w50'),
			'options_callback'		  => array('tl_store', 'getPriceFields'),
		),
		'priceOverrideField' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_store']['priceOverrideField'],
			'exclude'                 => true,
			'default'				  => 'price_override',
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
		'orderPrefix' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_store']['orderPrefix'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => array('maxlength'=>4),
		),
		'weightUnit' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_store']['weightUnit'],
			'exclude'                 => true,
			'filter'                  => true,
			'sorting'                 => true,
			'inputType'               => 'select',
			'options'                 => $GLOBALS['TL_LANG']['tl_store']['weightUnits'],
			'eval'                    => array('includeBlankOption'=>true, 'tl_class'=>'w50'),
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
		// Make sure field data is available
		if (!is_array($GLOBALS['TL_DCA']['tl_product_data']['fields']))
		{
			$this->loadDataContainer('tl_product_data');
			$this->loadLanguageFile('tl_product_data');
		}
		
		$arrPricingFields = array();
		
		foreach( $GLOBALS['TL_DCA']['tl_product_data']['fields'] as $field => $arrData )
		{
			if (is_array($arrData['attributes']) && $arrData['attributes']['legend'] == 'pricing_legend')
			{
				$arrPricingFields[$field] = strlen($arrData['label'][0]) ? $arrData['label'][0] : $field;
			}
		}
		
		return $arrPricingFields;
	}
	
	
	/**
	 * Get all checkout fields in tl_store.
	 * 
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
			if ($arrData['eval']['feEditable'])
			{
				$arrFields[$strField] = strlen($arrData['label'][0]) ? $arrData['label'][0] : $strField;
			}
		}
		
		return $arrFields;
	}
}

