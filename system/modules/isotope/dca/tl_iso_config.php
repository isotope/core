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


/**
 * Table tl_iso_config
 */
$GLOBALS['TL_DCA']['tl_iso_config'] = array
(

	// Config
	'config' => array
	(
		'dataContainer'               => 'Table',
		'enableVersioning'            => true,
		'closed'					  => true,
		'onload_callback' => array
		(
			array('IsotopeBackend', 'initializeSetupModule'),
			array('tl_iso_config', 'checkPermission'),
		)
	),

	// List
	'list' => array
	(
		'sorting' => array
		(
			'mode'                    => 1,
			'fields'                  => array('name'),
			'flag'					  => 1,
		),
		'label' => array
		(
			'fields'                  => array('name', 'fallback'),
			'format'                  => '%s <span style="color:#b3b3b3; padding-left:3px;">[%s]</span>',
			'label_callback'		  => array('tl_iso_config', 'addIcon')
		),
		'global_operations' => array
		(
			'back' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['MSC']['backBT'],
				'href'                => 'mod=&table=',
				'class'               => 'header_back',
				'attributes'          => 'onclick="Backend.getScrollOffset();"',
			),
			'new' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_iso_config']['new'],
				'href'                => 'act=create',
				'class'               => 'header_new',
				'attributes'          => 'onclick="Backend.getScrollOffset();"',
			),
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
				'label'               => &$GLOBALS['TL_LANG']['tl_iso_config']['edit'],
				'href'                => 'act=edit',
				'icon'                => 'edit.gif',
			),
			'copy' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_iso_config']['copy'],
				'href'                => 'act=copy',
				'icon'                => 'copy.gif',
				'button_callback'     => array('tl_iso_config', 'copyConfig'),
			),
			'delete' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_iso_config']['delete'],
				'href'                => 'act=delete',
				'icon'                => 'delete.gif',
				'attributes'          => 'onclick="if (!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\')) return false; Backend.getScrollOffset();"',
				'button_callback'     => array('tl_iso_config', 'deleteConfig'),
			),
			'show' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_iso_config']['show'],
				'href'                => 'act=show',
				'icon'                => 'show.gif',
			)
		)
	),

	// Palettes
	'palettes' => array
	(
		'__selector__'				  => array('currencySymbol', 'currencyAutomator'),
		'default'                     => '
			{name_legend},name,label,fallback,store_id;
			{address_legend:hide},firstname,lastname,company,vat_no,street_1,street_2,street_3,postal,city,country,subdivision,email,phone;
			{config_legend},orderPrefix,orderDigits,templateGroup;
			{checkout_legend},billing_countries,shipping_countries,billing_fields,shipping_fields,billing_country,shipping_country,limitMemberCountries;
			{price_legend},priceRoundPrecision,priceRoundIncrement,cartMinSubtotal;
			{currency_legend},currency,currencyFormat,currencyPosition,currencySymbol;
			{converter_legend:hide},priceCalculateFactor,priceCalculateMode,currencyAutomator;
			{order_legend:hide},orderstatus_new,orderstatus_error,invoiceLogo;
			{images_legend},gallery,missing_image_placeholder,imageSizes',
	),

	// Subpalettes
	'subpalettes' => array
	(
		'currencySymbol'				=> 'currencySpace',
		'currencyAutomator'				=> 'currencyOrigin,currencyProvider',
	),

	// Fields
	'fields' => array
	(
		'name' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_config']['name'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => array('mandatory'=>true, 'unique'=>true, 'maxlength'=>255, 'tl_class'=>'w50'),
		),
		'label' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_config']['label'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => array('maxlength'=>255, 'tl_class'=>'w50'),
		),
		'fallback' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_config']['fallback'],
			'exclude'                 => true,
			'inputType'               => 'checkbox',
			'eval'						=> array('doNotCopy'=>true, 'fallback'=>true, 'tl_class'=>'w50 m12'),
		),
		'store_id' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_config']['store_id'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => array('mandatory'=>true, 'rgxp'=>'digit', 'nospace'=>true, 'maxlength'=>2, 'tl_class'=>'w50'),
		),
		'firstname' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_config']['firstname'],
			'exclude'                 => true,
			'search'                  => true,
			'sorting'                 => true,
			'flag'                    => 1,
			'inputType'               => 'text',
			'eval'                    => array('maxlength'=>255, 'tl_class'=>'w50'),
		),
		'lastname' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_config']['lastname'],
			'exclude'                 => true,
			'search'                  => true,
			'sorting'                 => true,
			'flag'                    => 1,
			'inputType'               => 'text',
			'eval'                    => array('maxlength'=>255, 'tl_class'=>'w50'),
		),
		'company' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_config']['company'],
			'exclude'                 => true,
			'search'                  => true,
			'sorting'                 => true,
			'flag'                    => 1,
			'inputType'               => 'text',
			'eval'                    => array('maxlength'=>255, 'tl_class'=>'w50'),
		),
		'vat_no' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_config']['vat_no'],
			'exclude'                 => true,
			'search'                  => true,
			'inputType'               => 'text',
			'eval'                    => array('maxlength'=>255, 'tl_class'=>'w50'),
		),
		'street_1' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_config']['street_1'],
			'exclude'                 => true,
			'search'                  => true,
			'inputType'               => 'text',
			'eval'                    => array('maxlength'=>255, 'tl_class'=>'w50'),
		),
		'street_2' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_config']['street_2'],
			'exclude'                 => true,
			'search'                  => true,
			'inputType'               => 'text',
			'eval'                    => array('maxlength'=>255, 'tl_class'=>'w50'),
		),
		'street_3' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_config']['street_3'],
			'exclude'                 => true,
			'search'                  => true,
			'inputType'               => 'text',
			'eval'                    => array('maxlength'=>255, 'tl_class'=>'w50'),
		),
		'postal' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_config']['postal'],
			'exclude'                 => true,
			'search'                  => true,
			'inputType'               => 'text',
			'eval'                    => array('maxlength'=>32, 'tl_class'=>'clr w50'),
		),
		'city' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_config']['city'],
			'exclude'                 => true,
			'filter'                  => true,
			'search'                  => true,
			'sorting'                 => true,
			'inputType'               => 'text',
			'eval'                    => array('maxlength'=>255, 'tl_class'=>'w50'),
		),
		'subdivision' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_config']['subdivision'],
			'exclude'                 => true,
			'sorting'                 => true,
			'inputType'               => 'conditionalselect',
			'options_callback'		  => array('IsotopeBackend', 'getSubdivisions'),
			'eval'                    => array('conditionField'=>'country', 'includeBlankOption'=>true, 'tl_class'=>'w50'),
		),
		'country' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_config']['country'],
			'exclude'                 => true,
			'filter'                  => true,
			'sorting'                 => true,
			'inputType'               => 'select',
			'default'				  => $this->User->country,
			'options'                 => $this->getCountries(),
			'eval'                    => array('mandatory'=>true, 'includeBlankOption'=>true, 'tl_class'=>'w50', 'chosen'=>true),
		),
		'phone' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_config']['phone'],
			'exclude'                 => true,
			'search'                  => true,
			'inputType'               => 'text',
			'eval'                    => array('maxlength'=>64, 'rgxp'=>'phone', 'tl_class'=>'w50'),
		),
		'email' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_config']['email'],
			'exclude'                 => true,
			'search'                  => true,
			'inputType'               => 'text',
			'eval'                    => array('maxlength'=>64, 'rgxp'=>'email', 'tl_class'=>'w50')
		),
		'shipping_countries' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_config']['shipping_countries'],
			'exclude'                 => true,
			'inputType'               => 'select',
			'options'                 => $this->getCountries(),
			'eval'                    => array('multiple'=>true, 'size'=>8, 'tl_class'=>'w50 w50h', 'chosen'=>true)
		),
		'shipping_fields' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_config']['shipping_fields'],
			'exclude'                 => true,
			'inputType'               => 'fieldWizard',
			'eval'                    => array('mandatory'=>true, 'multiple'=>true, 'tl_class'=>'w50 w50h', 'table'=>'tl_iso_addresses')
		),
		'shipping_country' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_config']['shipping_country'],
			'exclude'                 => true,
			'inputType'               => 'select',
			'options'                 => $this->getCountries(),
			'eval'                    => array('includeBlankOption'=>true, 'tl_class'=>'w50', 'chosen'=>true)
		),
		'billing_countries' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_config']['billing_countries'],
			'exclude'                 => true,
			'inputType'               => 'select',
			'options'                 => $this->getCountries(),
			'eval'                    => array('multiple'=>true, 'size'=>8, 'tl_class'=>'w50 w50h', 'chosen'=>true)
		),
		'billing_fields' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_config']['billing_fields'],
			'exclude'                 => true,
			'inputType'               => 'fieldWizard',
			'eval'                    => array('mandatory'=>true, 'multiple'=>true, 'table'=>'tl_iso_addresses', 'tl_class'=>'clr w50 w50h'),
		),
		'billing_country' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_config']['billing_country'],
			'exclude'                 => true,
			'inputType'               => 'select',
			'options'                 => $this->getCountries(),
			'eval'                    => array('includeBlankOption'=>true, 'tl_class'=>'w50', 'chosen'=>true)
		),
		'orderPrefix' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_config']['orderPrefix'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => array('maxlength'=>255, 'decodeEntities'=>true, 'tl_class'=>'w50'),
		),
		'orderDigits' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_config']['orderDigits'],
			'exclude'                 => true,
			'default'				  => 4,
			'inputType'               => 'select',
			'options'				  => range(1, 9),
			'eval'                    => array('tl_class'=>'w50'),
		),
		'templateGroup' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_config']['templateGroup'],
			'exclude'                 => true,
			'inputType'               => 'fileTree',
			'eval'                    => array('fieldType'=>'radio', 'path'=>'templates', 'tl_class'=>'clr')
		),
		'limitMemberCountries' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_config']['limitMemberCountries'],
			'exclude'                 => true,
			'inputType'               => 'checkbox',
			'eval'					  => array('tl_class'=>'w50'),
		),
		'orderstatus_new' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_config']['orderstatus_new'],
			'exclude'                 => true,
			'filter'                  => true,
			'inputType'               => 'select',
			'options'                 => IsotopeBackend::getOrderStatus(),
			'eval'                    => array('mandatory'=>true, 'includeBlankOption'=>true, 'tl_class'=>'w50'),
		),
		'orderstatus_error' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_config']['orderstatus_error'],
			'exclude'                 => true,
			'filter'                  => true,
			'inputType'               => 'select',
			'options'                 => IsotopeBackend::getOrderStatus(),
			'eval'                    => array('mandatory'=>true, 'includeBlankOption'=>true, 'tl_class'=>'w50'),
		),
		'invoiceLogo' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_config']['invoiceLogo'],
			'exclude'                 => true,
			'inputType'               => 'fileTree',
			'eval'                    => array('fieldType'=>'radio', 'files'=>true, 'filesOnly'=>true, 'extensions'=>'jpg,jpeg,gif,png,tif,tiff', 'tl_class'=>'clr'),
		),
		'gallery' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_config']['gallery'],
			'exclude'                 => true,
			'inputType'               => 'select',
			'default'				  => 'default',
			'options'				  => array_keys($GLOBALS['ISO_GAL']),
			'reference'				  => &$GLOBALS['ISO_LANG']['GAL'],
			'eval'                    => array('mandatory'=>true, 'tl_class'=>'clr', 'helpwizard'=>true),
		),
		'missing_image_placeholder' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_config']['missing_image_placeholder'],
			'exclude'                 => true,
			'inputType'               => 'fileTree',
			'eval'                    => array('fieldType'=>'radio', 'files'=>true, 'filesOnly'=>true, 'extensions' => 'jpg,jpeg,gif,png,tif,tiff', 'tl_class'=>'clr'),
		),
		'imageSizes' => array
		(
			'label'					  => &$GLOBALS['TL_LANG']['tl_iso_config']['imageSizes'],
			'exclude'                 => true,
			'inputType'				  => 'multiColumnWizard',
			'default'                 => array
			(
				array('name'=>'gallery'),
				array('name'=>'thumbnail'),
				array('name'=>'medium'),
				array('name'=>'large'),
			),
			'eval'                    => array
			(
				'mandatory'           => true,
				'tl_class'            => 'clr',
				'disableSorting'      => true,
				'columnFields' => array
				(
					'name' => array
					(
						'label'       => $GLOBALS['TL_LANG']['tl_iso_config']['iwName'],
						'inputType'   => 'text',
						'eval'        => array('mandatory'=>true, 'rgxp'=>'alpha', 'spaceToUnderscore'=>true, 'class'=>'tl_text_4'),
					),
					'width' => array
					(
						'label'       => $GLOBALS['TL_LANG']['tl_iso_config']['iwWidth'],
						'inputType'   => 'text',
						'eval'        => array('rgxp'=>'digit', 'class'=>'tl_text_4'),
					),
					'height' => array
					(
						'label'       => $GLOBALS['TL_LANG']['tl_iso_config']['iwHeight'],
						'inputType'   => 'text',
						'eval'        => array('rgxp'=>'digit', 'class'=>'tl_text_4'),
					),
					'mode' => array
					(
						'label'       => $GLOBALS['TL_LANG']['tl_iso_config']['iwMode'],
						'inputType'   => 'select',
						'options'     => $GLOBALS['TL_CROP'],
						'reference'   => &$GLOBALS['TL_LANG']['MSC'],
						'eval'        => array('style'=>'width:150px'),
					),
					'watermark' => array
					(
						'label'       => $GLOBALS['TL_LANG']['tl_iso_config']['iwWatermark'],
						'inputType'   => 'text',
						'eval'        => array('class'=>'tl_text_2'),
						'wizard'      => array(array('tl_iso_config', 'filePicker')),
					),
					'position' => array
					(
						'label'       => $GLOBALS['TL_LANG']['tl_iso_config']['iwPosition'],
						'inputType'   => 'select',
						'options'     => array('tl', 'tc', 'tr', 'bl', 'bc', 'br', 'cc'),
						'reference'   => $GLOBALS['TL_LANG']['tl_iso_config'],
						'eval'        => array('style'=>'width:60px'),
					),
				),
			),
		),
		'priceCalculateFactor' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_config']['priceCalculateFactor'],
			'exclude'                 => true,
			'default'				  => 1,
			'inputType'               => 'text',
			'eval'                    => array('mandatory'=>true, 'maxlength'=>10, 'rgxp'=>'digit', 'tl_class'=>'w50'),
		),
		'priceCalculateMode' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_config']['priceCalculateMode'],
			'exclude'                 => true,
			'default'				  => 'mul',
			'inputType'               => 'radio',
			'options'				  => array('mul', 'div'),
			'reference'				  => &$GLOBALS['TL_LANG']['tl_iso_config'],
			'eval'                    => array('mandatory'=>true, 'tl_class'=>'w50'),
		),
		'priceRoundPrecision' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_config']['priceRoundPrecision'],
			'exclude'                 => true,
			'default'				  => '2',
			'inputType'               => 'text',
			'eval'                    => array('mandatory'=>true, 'maxlength'=>1, 'rgpx'=>'digit', 'tl_class'=>'w50'),
		),
		'priceRoundIncrement' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_config']['priceRoundIncrement'],
			'exclude'                 => true,
			'inputType'               => 'select',
			'options'				  => array('0.01', '0.05'),
			'eval'                    => array('mandatory'=>true, 'tl_class'=>'w50'),
		),
		'cartMinSubtotal' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_config']['cartMinSubtotal'],
			'exclude'                 => true,
			'default'				  => '',
			'inputType'               => 'text',
			'eval'                    => array('mandatory'=>true, 'maxlength'=>13, 'rgpx'=>'price', 'tl_class'=>'w50'),
		),
		'currency' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_config']['currency'],
			'exclude'                 => true,
			'inputType'               => 'select',
			'options'				  => &$GLOBALS['ISO_LANG']['CUR'],
			'eval'                    => array('includeBlankOption'=>true, 'mandatory'=>true, 'tl_class'=>'w50'),
		),
		'currencySymbol' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_config']['currencySymbol'],
			'exclude'                 => true,
			'inputType'               => 'checkbox',
			'eval'					  => array('submitOnChange'=>true, 'tl_class'=>'w50'),
		),
		'currencySpace' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_config']['currencySpace'],
			'exclude'                 => true,
			'inputType'               => 'checkbox',
			'eval'					  => array('tl_class'=>'w50'),
		),
		'currencyPosition' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_config']['currencyPosition'],
			'exclude'                 => true,
			'inputType'               => 'radio',
			'default'				  => 'left',
			'options'				  => array('left', 'right'),
			'reference'				  => &$GLOBALS['TL_LANG']['tl_iso_config'],
			'eval'					  => array('tl_class'=>'w50'),
		),
		'currencyFormat' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_config']['currencyFormat'],
			'exclude'                 => true,
			'inputType'               => 'select',
			'options'				  => array_keys($GLOBALS['ISO_NUM']),
			'eval'                    => array('includeBlankOption'=>true, 'mandatory'=>true, 'tl_class'=>'w50'),
		),
		'currencyAutomator' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_config']['currencyAutomator'],
			'exclude'                 => true,
			'inputType'               => 'checkbox',
			'eval'					  => array('submitOnChange'=>true, 'tl_class'=>'clr', 'helpwizard'=>true),
		),
		'currencyOrigin' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_config']['currencyOrigin'],
			'exclude'                 => true,
			'inputType'               => 'select',
			'options'				  => &$GLOBALS['ISO_LANG']['CUR'],
			'eval'                    => array('includeBlankOption'=>true, 'mandatory'=>true, 'tl_class'=>'w50'),
		),
		'currencyProvider' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_config']['currencyProvider'],
			'exclude'                 => true,
			'inputType'               => 'select',
			'options'				  => array('ecb.int', 'admin.ch'),
			'reference'				  => &$GLOBALS['TL_LANG']['tl_iso_config'],
			'eval'                    => array('mandatory'=>true, 'tl_class'=>'w50'),
		),
	)
);


/**
 * Class tl_iso_config
 * Provide miscellaneous methods that are used by the data configuration array.
 */
class tl_iso_config extends Backend
{

	/**
	 * Check permissions to edit table tl_iso_config
	 * @return void
	 */
	public function checkPermission()
	{
		// Do not run the permission check on other Isotope modules
		if ($this->Input->get('mod') != 'configs')
		{
			return;
		}

		// Set fallback if no fallback is available
		$objConfig = $this->Database->query("SELECT COUNT(*) AS total FROM tl_iso_config WHERE fallback='1'");

		if ($objConfig->total == 0)
		{
			$GLOBALS['TL_DCA']['tl_iso_config']['fields']['fallback']['default'] = '1';
		}

		$this->import('BackendUser', 'User');

		// Return if user is admin
		if ($this->User->isAdmin)
		{
			return;
		}

		// Set root IDs
		if (!is_array($this->User->iso_configs) || count($this->User->iso_configs) < 1) // Can't use empty() because its an object property (using __get)
		{
			$root = array(0);
		}
		else
		{
			$root = $this->User->iso_configs;
		}

		$GLOBALS['TL_DCA']['tl_iso_config']['list']['sorting']['root'] = $root;

		// Check permissions to add configs
		if (!$this->User->hasAccess('create', 'iso_configp'))
		{
			$GLOBALS['TL_DCA']['tl_iso_config']['config']['closed'] = true;
			unset($GLOBALS['TL_DCA']['tl_iso_config']['list']['global_operations']['new']);
		}

		// Check current action
		switch ($this->Input->get('act'))
		{
			case 'create':
			case 'select':
				// Allow
				break;

			case 'edit':
				// Dynamically add the record to the user profile
				if (!in_array($this->Input->get('id'), $root))
				{
					$arrNew = $this->Session->get('new_records');

					if (is_array($arrNew['tl_iso_config']) && in_array($this->Input->get('id'), $arrNew['tl_iso_config']))
					{
						// Add permissions on user level
						if ($this->User->inherit == 'custom' || !$this->User->groups[0])
						{
							$objUser = $this->Database->prepare("SELECT iso_configs, iso_configp FROM tl_user WHERE id=?")
													   ->limit(1)
													   ->execute($this->User->id);

							$arrPermissions = deserialize($objUser->iso_configp);

							if (is_array($arrPermissions) && in_array('create', $arrPermissions))
							{
								$arrAccess = deserialize($objUser->iso_configs);
								$arrAccess[] = $this->Input->get('id');

								$this->Database->prepare("UPDATE tl_user SET iso_configs=? WHERE id=?")
											   ->execute(serialize($arrAccess), $this->User->id);
							}
						}

						// Add permissions on group level
						elseif ($this->User->groups[0] > 0)
						{
							$objGroup = $this->Database->prepare("SELECT iso_configs, iso_configp FROM tl_user_group WHERE id=?")
													   ->limit(1)
													   ->execute($this->User->groups[0]);

							$arrPermissions = deserialize($objGroup->iso_configp);

							if (is_array($arrPermissions) && in_array('create', $arrPermissions))
							{
								$arrAccess = deserialize($objGroup->iso_configs);
								$arrAccess[] = $this->Input->get('id');

								$this->Database->prepare("UPDATE tl_user_group SET iso_configs=? WHERE id=?")
											   ->execute(serialize($arrAccess), $this->User->groups[0]);
							}
						}

						// Add new element to the user object
						$root[] = $this->Input->get('id');
						$this->User->iso_configs = $root;
					}
				}
				// No break;

			case 'copy':
			case 'delete':
			case 'show':
				if (!in_array($this->Input->get('id'), $root) || ($this->Input->get('act') == 'delete' && !$this->User->hasAccess('delete', 'iso_configp')))
				{
					$this->log('Not enough permissions to '.$this->Input->get('act').' store configuration ID "'.$this->Input->get('id').'"', __METHOD__, TL_ERROR);
					$this->redirect('contao/main.php?act=error');
				}
				break;

			case 'editAll':
			case 'deleteAll':
			case 'overrideAll':
				$session = $this->Session->getData();
				if ($this->Input->get('act') == 'deleteAll' && !$this->User->hasAccess('delete', 'iso_configp'))
				{
					$session['CURRENT']['IDS'] = array();
				}
				else
				{
					$session['CURRENT']['IDS'] = array_intersect($session['CURRENT']['IDS'], $root);
				}
				$this->Session->setData($session);
				break;

			default:
				if (strlen($this->Input->get('act')))
				{
					$this->log('Not enough permissions to '.$this->Input->get('act').' store configurations', __METHOD__, TL_ERROR);
					$this->redirect('contao/main.php?act=error');
				}
				break;
		}
	}


	/**
	 * Add an image to each record
	 * @param array
	 * @param string
	 * @return string
	 */
	public function addIcon($row, $label)
	{
		switch ($row['currency'])
		{
			case 'AUD':
				$image = 'currency-dollar-aud';
				break;

			case 'CAD':
				$image = 'currency-dollar-cad';
				break;

			case 'NZD':
				$image = 'currency-dollar-nzd';
				break;

			case 'USD':
				$image = 'currency-dollar-usd';
				break;

			case 'BBD':
			case 'BMD':
			case 'BND':
			case 'BSD':
			case 'BZD':
			case 'FJD':
			case 'GYD':
			case 'HKD':
			case 'JMD':
			case 'KYD':
			case 'LRD':
			case 'MYR':
			case 'NAD':
			case 'SBD':
			case 'SGD':
			case 'SRD':
			case 'TTD':
			case 'TWD':
			case 'ZWL':
				$image = 'currency';
				break;

			case 'EUR':
				$image = 'currency-euro';
				break;

			case 'EGP':
			case 'FKP':
			case 'GBP':
			case 'GIP':
			case 'LBP':
			case 'SDG':
			case 'SHP':
			case 'SYP':
				$image = 'currency-pound';
				break;

			case 'BYR':
			case 'RUB':
				$image = 'currency-ruble';
				break;

			case 'JPY':
				$image = 'currency-yen';
				break;

			default:
				$image = 'money';
		}

		return sprintf('<div class="list_icon" style="background-image:url(\'system/modules/isotope/html/%s.png\');line-height:16px" title="%s">%s</div>', $image, $GLOBALS['ISO_LANG']['CUR'][$row['currency']], $label);
	}


	/**
	 * Return the copy config button
	 * @param array
	 * @param string
	 * @param string
	 * @param string
	 * @param string
	 * @param string
	 * @return string
	 */
	public function copyConfig($row, $href, $label, $title, $icon, $attributes)
	{
		return ($this->User->isAdmin || $this->User->hasAccess('create', 'iso_configp')) ? '<a href="'.$this->addToUrl($href.'&amp;id='.$row['id']).'" title="'.specialchars($title).'"'.$attributes.'>'.$this->generateImage($icon, $label).'</a> ' : $this->generateImage(preg_replace('/\.gif$/i', '_.gif', $icon)).' ';
	}


	/**
	 * Return the delete config button
	 * @param array
	 * @param string
	 * @param string
	 * @param string
	 * @param string
	 * @param string
	 * @return string
	 */
	public function deleteConfig($row, $href, $label, $title, $icon, $attributes)
	{
		return ($this->User->isAdmin || $this->User->hasAccess('delete', 'iso_configp')) ? '<a href="'.$this->addToUrl($href.'&amp;id='.$row['id']).'" title="'.specialchars($title).'"'.$attributes.'>'.$this->generateImage($icon, $label).'</a> ' : $this->generateImage(preg_replace('/\.gif$/i', '_.gif', $icon)).' ';
	}


	/**
	 * Return the file picker wizard
	 * @param DataContainer
	 * @return string
	 */
	public function filePicker(DataContainer $dc)
	{
		$strField = 'ctrl_' . $dc->field . (($this->Input->get('act') == 'editAll') ? '_' . $dc->id : '');
		return ' ' . $this->generateImage('pickfile.gif', $GLOBALS['TL_LANG']['MSC']['filepicker'], 'style="vertical-align:top;cursor:pointer" onclick="Backend.pickFile(\'' . $strField . '\')"');
	}
}

