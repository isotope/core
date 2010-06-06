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
 * Table tl_iso_payment_modules 
 */
$GLOBALS['TL_DCA']['tl_iso_payment_modules'] = array
(

	// Config
	'config' => array
	(
		'dataContainer'               => 'Table',
		'ctable'                      => array('tl_payment_options'),
		'enableVersioning'            => true,
		'closed'						=> true,
		'onload_callback'			  => array
		(
			array('tl_iso_payment_modules', 'checkPermission'),
			array('tl_iso_payment_modules', 'loadShippingModules'),
		),
	),

	// List
	'list' => array
	(
		'sorting' => array
		(
			'mode'                    => 1,
			'fields'                  => array('name'),
			'flag'                    => 1,
			'panelLayout'             => 'sort,filter;search,limit'
		),
		'label' => array
		(
			'fields'                  => array('name', 'type'),
			'format'                  => '%s <span style="color:#b3b3b3; padding-left:3px;">[%s]</span>',
			'label_callback'		  => array('tl_iso_payment_modules', 'addIcon'),

		),
		'global_operations' => array
		(
			'back' => array
			(
				'label'					=> &$GLOBALS['TL_LANG']['MSC']['backBT'],
				'href'					=> 'table=',
				'class'					=> 'header_back',
				'attributes'			=> 'onclick="Backend.getScrollOffset();"',
			),
			'new' => array
			(
				'label'					=> &$GLOBALS['TL_LANG']['tl_iso_payment_modules']['new'],
				'href'					=> 'act=create',
				'class'					=> 'header_new',
				'attributes'			=> 'onclick="Backend.getScrollOffset();"',
			),
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
				'label'               => &$GLOBALS['TL_LANG']['tl_iso_payment_modules']['edit'],
				'href'                => 'act=edit',
				'icon'                => 'edit.gif'
			),
			'copy' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_iso_payment_modules']['copy'],
				'href'                => 'act=copy',
				'icon'                => 'copy.gif'
			),
			'delete' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_iso_payment_modules']['delete'],
				'href'                => 'act=delete',
				'icon'                => 'delete.gif',
				'attributes'          => 'onclick="if (!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\')) return false; Backend.getScrollOffset();"'
			),
			'show' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_iso_payment_modules']['show'],
				'href'                => 'act=show',
				'icon'                => 'show.gif'
			),
			'buttons' => array
			(
				'button_callback'     => array('tl_iso_payment_modules', 'moduleOperations'),
			)
		)
	),

	// Palettes
	'palettes' => array
	(
		'__selector__'			=> array('type'),
		'default'				=> '{type_legend},type,name',
		'cash'					=> '{type_legend},type,name,label;{note_legend:hide},note;{config_legend},new_order_status,minimum_total,maximum_total,countries,shipping_modules,product_types;{price_legend:hide},price,tax_class;{enabled_legend},enabled',
		'paypal'				=> '{type_legend},type,name,label;{note_legend:hide},note;{config_legend},new_order_status,postsale_mail,minimum_total,maximum_total,countries,shipping_modules,product_types;{gateway_legend},paypal_account,paypal_business;{price_legend:hide},price,tax_class;{template_legend},button;{enabled_legend},debug,enabled',
		'paypalpayflowpro'		=> '{type_legend},type,name,label;{note_legend:hide},note;{config_legend},new_order_status,allowed_cc_types,requireCCV,postsale_mail,minimum_total,maximum_total,countries,shipping_modules,product_types;{gateway_legend},payflowpro_user,payflowpro_vendor,payflowpro_partner,payflowpro_password,payflowpro_transType;{price_legend:hide},price,tax_class;{template_legend},button;{enabled_legend},debug,enabled',
		'postfinance'			=> '{type_legend},type,name,label;{note_legend:hide},note;{config_legend},new_order_status,postsale_mail,minimum_total,maximum_total,countries,shipping_modules,product_types;{gateway_legend},postfinance_pspid,postfinance_secret,postfinance_method;{price_legend:hide},price,tax_class;{enabled_legend},debug,enabled',
		'authorizedotnet'		=> '{type_legend},type,name,label;{note_legend:hide},note;{config_legend},new_order_status,allowed_cc_types,requireCCV,minimum_total,maximum_total,countries,shipping_modules,product_types;{gateway_legend},authorize_login,authorize_trans_key,authorize_trans_type,authorize_delimiter;{price_legend:hide},price,tax_class;{enabled_legend},debug,enabled',
		'epay_standard'			=> '{type_legend},type,name,label;{note_legend:hide},note;{config_legend},new_order_status,postsale_mail,minimum_total,maximum_total,countries,shipping_modules,product_types;{gateway_legend},epay_merchantnumber,epay_secretkey;{price_legend:hide},price,tax_class;{template_legend},button;{enabled_legend},debug,enabled',
	),

	// Fields
	'fields' => array
	(
		'type' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_payment_modules']['type'],
			'default'                 => 'cc',
			'exclude'                 => true,
			'filter'                  => true,
			'inputType'               => 'select',
			'default'				  => 'cash',
			'options_callback'        => array('tl_iso_payment_modules', 'getModules'),
			'reference'               => &$GLOBALS['TL_LANG']['PAY'],
			'eval'                    => array('helpwizard'=>true, 'submitOnChange'=>true)
		),
		'name' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_payment_modules']['name'],
			'exclude'                 => true,
			'search'                  => true,
			'inputType'               => 'text',
			'eval'                    => array('mandatory'=>true, 'maxlength'=>255, 'tl_class'=>'w50')
		),
		'label' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_payment_modules']['label'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => array('maxlength'=>255, 'mandatory'=>true, 'tl_class'=>'w50'),
		),
		'note' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_payment_modules']['note'],
			'exclude'                 => true,
			'inputType'               => 'textarea',
			'eval'                    => array('rte'=>'tinyMCE'),
		),
		'new_order_status' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_payment_modules']['new_order_status'],
			'exclude'                 => true,
			'inputType'               => 'select',
			'default'                 => 'pending',
			'options_callback'        => array('tl_iso_payment_modules', 'getOrderStatus'),
			'reference'               => &$GLOBALS['TL_LANG']['MSC']['order_status_labels'],
			'eval'                    => array('includeBlankOption'=>true, 'mandatory'=>true, 'tl_class'=>'clr'),
		),
		'price' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_payment_modules']['price'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => array('maxlength'=>10, 'rgxp'=>'digit', 'tl_class'=>'w50'),
		),
		'tax_class' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_iso_payment_modules']['tax_class'],
			'filter'				=> true,
			'inputType'				=> 'select',
			'foreignKey'			=> 'tl_iso_tax_class.name',
			'attributes'			=> array('legend'=>'tax_legend'),
			'eval'					=> array('includeBlankOption'=>true, 'tl_class'=>'w50'),
		),
		'allowed_cc_types' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_payment_modules']['allowed_cc_types'],
			'exclude'                 => true,
			'filter'                  => true,
			'inputType'               => 'checkbox',
			'eval'					  => array('multiple'=>true, 'tl_class'=>'clr'),
			'options_callback'		  => array('tl_iso_payment_modules', 'getAllowedCCTypes') 
		),
		'postsale_mail' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_payment_modules']['postsale_mail'],
			'exclude'                 => true,
			'inputType'               => 'select',
			'foreignKey'              => 'tl_iso_mail.name',
			'eval'					  => array('includeBlankOption'=>true, 'tl_class'=>'w50'),
		),
		'minimum_total' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_payment_modules']['minimum_total'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'default'                 => 0,
			'eval'                    => array('maxlength'=>255, 'rgxp'=>'digit', 'tl_class'=>'w50'),
		),
		'maximum_total' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_payment_modules']['maximum_total'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'default'                 => 0,
			'eval'                    => array('maxlength'=>255, 'rgxp'=>'digit', 'tl_class'=>'w50'),
		),
		'countries' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_payment_modules']['countries'],
			'exclude'                 => true,
			'inputType'               => 'select',
			'options'                 => $this->getCountries(),
			'eval'                    => array('multiple'=>true, 'size'=>8, 'tl_class'=>'clr'),
		),
		'shipping_modules' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_payment_modules']['shipping_modules'],
			'exclude'                 => true,
			'inputType'               => 'select',
			'eval'                    => array('multiple'=>true, 'size'=>8, 'tl_class'=>'clr'),
		),
		'product_types' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_payment_modules']['product_types'],
			'exclude'                 => true,
			'inputType'               => 'select',
			'foreignKey'			  => 'tl_iso_producttypes.name',
			'eval'                    => array('multiple'=>true, 'size'=>8, 'tl_class'=>'clr'),
		),
		'paypal_account' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_payment_modules']['paypal_account'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => array('mandatory'=>true, 'maxlength'=>255, 'rgxp'=>'email', 'tl_class'=>'w50'),
		),
		'paypal_business' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_payment_modules']['paypal_business'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => array('mandatory'=>true, 'maxlength'=>255, 'tl_class'=>'w50'),
		),
		'payflowpro_user' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_payment_modules']['payflowpro_user'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => array('mandatory'=>true, 'maxlength'=>255),
		),
		'payflowpro_vendor' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_payment_modules']['payflowpro_vendor'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => array('mandatory'=>true, 'maxlength'=>255),
		),
		'payflowpro_partner' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_payment_modules']['payflowpro_partner'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => array('mandatory'=>true, 'maxlength'=>255),
		),
		'payflowpro_password' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_payment_modules']['payflowpro_password'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => array('mandatory'=>true, 'maxlength'=>255, 'hideInput'=>true),
		),
		'payflowpro_transType' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_payment_modules']['payflowpro_transType'],
			'exclude'                 => true,
			'default'				  => 'Sale',
			'inputType'               => 'select',
			'options'				  => array('Sale', 'Authorization'),
			'eval'                    => array('mandatory'=>true, 'maxlength'=>255),
			'reference'				  => &$GLOBALS['TL_LANG']['tl_payment_module']['payflowpro_transTypes']
		),
		'postfinance_pspid' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_payment_modules']['postfinance_pspid'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => array('mandatory'=>true, 'maxlength'=>255),
		),
		'postfinance_secret' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_payment_modules']['postfinance_secret'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => array('maxlength'=>255, 'tl_class'=>'w50'),
		),
		'postfinance_method' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_payment_modules']['postfinance_method'],
			'exclude'                 => true,
			'inputType'               => 'select',
			'default'                 => 'POST',
			'options'                 => array('POST', 'GET'),
			'eval'                    => array('mandatory'=>true, 'tl_class'=>'w50', 'tl_class'=>'w50'),
		),
		'authorize_login' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_payment_modules']['authorize_login'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => array('mandatory'=>true, 'maxlength'=>255)
		),
		'authorize_trans_key' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_payment_modules']['authorize_trans_key'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => array('mandatory'=>true, 'maxlength'=>255)
		),
		'authorize_trans_type' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_payment_modules']['authorize_trans_type'],
			'exclude'                 => true,
			'default'				  => 'AUTH_CAPTURE',
			'inputType'               => 'select',
			'options'				  => array('AUTH_CAPTURE', 'AUTH_ONLY'),
			'eval'                    => array('mandatory'=>true, 'maxlength'=>255),
			'reference'				  => array('AUTH_CAPTURE'=>'Authorize and Capture', 'AUTH_ONLY'=>'Authorize Only')
		),
		'authorize_delimiter' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_payment_modules']['authorize_delimiter'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => array('mandatory'=>true, 'maxlength'=>1)
		),
		'epay_merchantnumber' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_payment_modules']['epay_merchantnumber'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => array('mandatory'=>true, 'maxlength'=>7, 'tl_class'=>'w50'),
		),
		'epay_secretkey' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_payment_modules']['epay_secretkey'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => array('mandatory'=>true, 'maxlength'=>255, 'tl_class'=>'w50'),
		),
		'requireCCV' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_payment_modules']['requireCCV'],
			'exclude'                 => true,
			'inputType'               => 'checkbox',
		),
		'button' => array
		(
			'label'					  => &$GLOBALS['TL_LANG']['tl_iso_payment_modules']['button'],
			'exclude'				  => true,
			'inputType'				  => 'fileTree',
			'eval'					  => array('fieldType'=>'radio', 'files'=>true, 'filesOnly'=>true, 'extensions'=>'jpg,jpeg,png,gif'),
		),
		'debug' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_payment_modules']['debug'],
			'exclude'                 => true,
			'inputType'               => 'checkbox',
		),		
		'enabled' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_payment_modules']['enabled'],
			'exclude'                 => true,
			'inputType'               => 'checkbox',
		),
	)
);
  

/**
 * tl_iso_payment_modules class.
 * 
 * @extends Backend
 */
class tl_iso_payment_modules extends Backend
{
	
	public function checkPermission($dc)
	{
		if (strlen($this->Input->get('act')))
		{
			$GLOBALS['TL_DCA']['tl_iso_payment_modules']['config']['closed'] = false;
		}
	}


	/**
	 * Return a string of more buttons for the current payment module.
	 * 
	 * @todo Collect additional buttons from payment modules.
	 * @access public
	 * @param array $arrRow
	 * @return string
	 */
	public function moduleOperations($arrRow)
	{
		$strClass = $GLOBALS['ISO_PAY'][$arrRow['type']];

		if (!strlen($strClass) || !$this->classFileExists($strClass))
			return '';
			
		try 
		{
			$objModule = new $strClass($arrRow);
			return $objModule->moduleOperations();
		}
		catch (Exception $e) {}
		
		return '';
	}
	
	public function getAllowedCCTypes(DataContainer $dc)
	{
		$objModuleType = $this->Database->prepare("SELECT * FROM tl_iso_payment_modules WHERE id=?")->limit(1)->execute($dc->id);
		
		if(!$objModuleType->numRows)
			return array();
		
		$strClass = $GLOBALS['ISO_PAY'][$objModuleType->type];
		
		if(!strlen($strClass) || !$this->classFileExists($strClass))
			return array();
		
		$arrCCTypes = array();
		$objModule = new $strClass($objModuleType->fetchAssoc());
			
		foreach($objModule->getAllowedCCTypes() as $type)
		{
			$arrCCTypes[$type] = $GLOBALS['TL_LANG']['CCT'][$type];
		}
			
		return $arrCCTypes;
	}
	
	public function getOrderStatus($dc)
	{
		$objModule = $this->Database->prepare("SELECT * FROM tl_iso_payment_modules WHERE id=?")->limit(1)->execute($dc->id);
		
		$strClass = $GLOBALS['ISO_PAY'][$objModule->type];

		if (!strlen($strClass) || !$this->classFileExists($strClass))
			return array();
			
		try 
		{
			$objModule = new $strClass($arrRow);
			return $objModule->statusOptions();
		}
		catch (Exception $e) {}
		
		return array();
	}
	
	
	/**
	 * Get a list of all payment modules available.
	 * 
	 * @access public
	 * @return array
	 */
	public function getModules()
	{
		$arrModules = array();
		
		if (is_array($GLOBALS['ISO_PAY']) && count($GLOBALS['ISO_PAY']))
		{
			foreach( $GLOBALS['ISO_PAY'] as $module => $class )
			{
				$arrModules[$module] = (strlen($GLOBALS['TL_LANG']['PAY'][$module][0]) ? $GLOBALS['TL_LANG']['PAY'][$module][0] : $module);
			}
		}
		
		return $arrModules;
	}
	
	
	/**
	 * Load shipping modules into the DCA. options_callback would not work due to numeric array keys.
	 * 
	 * @access public
	 * @param object $dc
	 * @return void
	 */
	public function loadShippingModules($dc)
	{
		$arrModules = array(0=>$GLOBALS['TL_LANG']['tl_iso_payment_modules']['no_shipping']);
		
		$objShippings = $this->Database->execute("SELECT * FROM tl_iso_shipping_modules ORDER BY name");
		
		while( $objShippings->next() )
		{
			$arrModules[$objShippings->id] = $objShippings->name;
		}
		
		$GLOBALS['TL_DCA']['tl_iso_payment_modules']['fields']['shipping_modules']['options'] = array_keys($arrModules);
		$GLOBALS['TL_DCA']['tl_iso_payment_modules']['fields']['shipping_modules']['reference'] = $arrModules;
	}
	
	
	/**
	 * Add an image to each record
	 * @param array
	 * @param string
	 * @return string
	 */
	public function addIcon($row, $label)
	{
		$image = 'published';

		if (!$row['enabled'])
		{
			$image = 'un'.$image;
		}

		return sprintf('<div class="list_icon" style="background-image:url(\'system/themes/%s/images/%s.gif\');">%s</div>', $this->getTheme(), $image, $label);
	}
}

