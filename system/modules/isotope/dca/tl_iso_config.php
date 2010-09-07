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


$this->loadLanguageFile('subdivisions');


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
			array('tl_iso_config', 'checkPermission'),
		),
		'ondelete_callback'			  => array
		(
			array('tl_iso_config', 'archiveRecord'),
		),
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
		),
		'global_operations' => array
		(
			'back' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['MSC']['backBT'],
				'href'                => 'table=',
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
			),
			'delete' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_iso_config']['delete'],
				'href'                => 'act=delete',
				'icon'                => 'delete.gif',
				'attributes'          => 'onclick="if (!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\')) return false; Backend.getScrollOffset();"',
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
		'default'                     => '
			{name_legend},name,label,fallback;
			{address_legend:hide},firstname,lastname,company,street_1,street_2,street_3,postal,city,subdivision,country,emailShipping,phone;
			{config_legend},shipping_countries,billing_countries,shipping_fields,billing_fields,orderPrefix,store_id,enableGoogleAnalytics;
			{price_legend},priceCalculateFactor,priceCalculateMode,priceRoundPrecision,priceRoundIncrement;
			{currency_legend},currency,currencySymbol,currencyFormat,currencyPosition;
			{invoice_legend:hide},invoiceLogo;
			{images_legend},gallery,missing_image_placeholder,imageSizes',
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
			'eval'						=> array('doNotCopy'=>true, 'fallback'=>true, 'tl_class'=>'w50'),
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
			'eval'                    => array('maxlength'=>32, 'tl_class'=>'w50'),
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
			'options'				  => &$GLOBALS['TL_LANG']['DIV'],
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
			'eval'                    => array('mandatory'=>true, 'includeBlankOption'=>true, 'tl_class'=>'w50'),
		),
		'phone' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_config']['phone'],
			'exclude'                 => true,
			'search'                  => true,
			'inputType'               => 'text',
			'eval'                    => array('maxlength'=>64, 'rgxp'=>'phone', 'tl_class'=>'w50'),
		),
		'emailShipping' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_config']['emailShipping'],
			'exclude'                 => true,
			'search'                  => true,
			'inputType'               => 'text',
			'eval'                    => array('maxlength'=>64, 'rgxp'=>'email', 'tl_class'=>'w50'),
		),
		'shipping_countries' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_config']['shipping_countries'],
			'exclude'                 => true,
			'inputType'               => 'select',
//			'default'                 => array_keys($this->getCountries()),
			'options'                 => $this->getCountries(),
			'eval'                    => array('mandatory'=>true, 'multiple'=>true, 'size'=>8, 'tl_class'=>'w50'),
		),
		'shipping_fields' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_config']['shipping_fields'],
			'exclude'                 => true,
			'inputType'               => 'checkboxWizard',
			'options_callback'		  => array('tl_iso_config', 'getAddressFields'),
			'eval'                    => array('mandatory'=>true, 'multiple'=>true, 'tl_class'=>'w50 w50h'),
		),
		'billing_countries' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_config']['billing_countries'],
			'exclude'                 => true,
			'inputType'               => 'select',
//			'default'                 => array_keys($this->getCountries()),
			'options'                 => $this->getCountries(),
			'eval'                    => array('mandatory'=>true, 'multiple'=>true, 'size'=>8, 'tl_class'=>'w50 w50h'),
		),
		'billing_fields' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_config']['billing_fields'],
			'exclude'                 => true,
			'inputType'               => 'checkboxWizard',
			'options_callback'		  => array('tl_iso_config', 'getAddressFields'),
			'eval'                    => array('mandatory'=>true, 'multiple'=>true, 'tl_class'=>'w50 w50h'),
		),
		'enableGoogleAnalytics' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_config']['enableGoogleAnalytics'],
			'exclude'                 => true,
			'inputType'               => 'checkbox',
		),
		'invoiceLogo' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_config']['invoiceLogo'],
			'exclude'                 => true,
			'inputType'               => 'fileTree',
			'eval'                    => array('fieldType'=>'radio', 'files'=>true, 'filesOnly'=>true, 'extensions' => 'jpg,jpeg,gif,png,tif,tiff'),
		),
		'gallery' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_config']['gallery'],
			'exclude'                 => true,
			'inputType'               => 'select',
			'default'				  => 'default',
			'options'				  => array_keys($GLOBALS['ISO_GAL']),
			'reference'				  => &$GLOBALS['TL_LANG']['GAL'],
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
			'inputType'				  => 'imageWatermarkWizard',
			'options'                 => array('crop', 'proportional', 'box'),
			'reference'               => &$GLOBALS['TL_LANG']['MSC'],
			'eval'                    => array('tl_class'=>'clr'),			
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
		'currency' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_config']['currency'],
			'exclude'                 => true,
			'inputType'               => 'select',
			'options'				  => &$GLOBALS['TL_LANG']['CUR'],
			'eval'                    => array('includeBlankOption'=>true, 'mandatory'=>true, 'tl_class'=>'w50'),
		),
		'currencySymbol' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_config']['currencySymbol'],
			'exclude'                 => true,
			'inputType'               => 'checkbox',
			'eval'					  => array('tl_class'=>'w50 m12'),
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
		'orderPrefix' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_config']['orderPrefix'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => array('maxlength'=>4, 'tl_class'=>'w50'),
		),
		'store_id' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_config']['store_id'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => array('mandatory'=>true, 'rgxp'=>'digit', 'nospace'=>true, 'maxlength'=>2, 'tl_class'=>'w50'),
		),
	)
);


/**
 * tl_iso_config class.
 * 
 * @extends Backend
 */
class tl_iso_config extends Backend
{
	
	public function checkPermission($dc)
	{
		if (!in_array('googleanalytics', $this->Config->getActiveModules()))
		{
			unset($GLOBALS['TL_DCA']['tl_iso_config']['fields']['enableGoogleAnalytics']);
		}
		
		if (strlen($this->Input->get('act')))
		{
			$GLOBALS['TL_DCA']['tl_iso_config']['config']['closed'] = false;
		}
		
		$this->import('BackendUser', 'User');
		
		// Hide archived (used and deleted) configs
		if ($this->User->isAdmin)
		{
			$arrConfigs = $this->Database->execute("SELECT id FROM tl_iso_config WHERE archive<2")->fetchEach('id');
		}
		else
		{
			if (!is_array($this->User->iso_configs) || !count($this->User->iso_configs))
			{
				$this->User->iso_configs = array(0);
			}
			
			$arrConfigs = $this->Database->execute("SELECT id FROM tl_iso_config WHERE id IN ('','" . implode("','", $this->User->iso_configs) . "') AND archive<2")->fetchEach('id');
		}
		
		if (!count($arrConfigs))
		{
			$arrConfigs = array(0);
		}

		$GLOBALS['TL_DCA']['tl_iso_config']['list']['sorting']['root'] = $arrConfigs;

		// Check current action
		switch ($this->Input->get('act'))
		{
			case 'edit':
			case 'copy':
			case 'delete':
			case 'show':
				if (!in_array($this->Input->get('id'), $arrConfigs))
				{
					$this->log('Not enough permissions to '.$this->Input->get('act').' config ID "'.$this->Input->get('id').'"', 'tl_iso_config checkPermission()', TL_ACCESS);
					$this->redirect('typolight/main.php?act=error');
				}
				break;

			case 'editAll':
			case 'copyAll':
			case 'deleteAll':
				$session = $this->Session->getData();
				$session['CURRENT']['IDS'] = array_intersect($session['CURRENT']['IDS'], $arrConfigs);
				$this->Session->setData($session);
				break;
		}
	}
	
	
	/**
	 * Record is deleted, archive if necessary
	 */
	public function archiveRecord($dc)
	{
	}
	
	
	/**
	 * Get all checkout fields in tl_iso_config.
	 * 
	 * @access public
	 * @param object $dc
	 * @return array
	 */
	public function getAddressFields($dc)
	{
		$arrFields = array();
		
		$this->loadLanguageFile('tl_iso_addresses');
		$this->loadDataContainer('tl_iso_addresses');
		
		foreach( $GLOBALS['TL_DCA']['tl_iso_addresses']['fields'] as $strField => $arrData )
		{
			if ($arrData['eval']['feEditable'])
			{
				$arrFields[$strField] = strlen($arrData['label'][0]) ? $arrData['label'][0] : $strField;
			}
		}
		
		return $arrFields;
	}
}

