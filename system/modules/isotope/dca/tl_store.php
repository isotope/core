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
 * @copyright  Winans Creative / Fred Bliss 2009
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
		'enableVersioning'            => true
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
		//'default'                     => 'store_configuration_name;country,orderPrefix;currency,currencySymbol,currencyPosition,currencyFormat;cookie_duration;root_asset_import_path;enabled_modules;countries,address_fields,checkout_login_module;productReaderJumpTo,cartJumpTo,checkoutJumpTo;missing_image_placeholder;gallery_thumbnail_image_width,gallery_thumbnail_image_height;thumbnail_image_width,thumbnail_image_height;medium_image_width,medium_image_height;large_image_width,large_image_height'
		'default'                     => 'store_configuration_name;country;currency,currencySymbol,currencyPosition,currencyFormat;cookie_duration;root_asset_import_path;enabled_modules;countries,address_fields,checkout_login_module;productReaderJumpTo,cartJumpTo,checkoutJumpTo;missing_image_placeholder;gallery_thumbnail_image_width,gallery_thumbnail_image_height;thumbnail_image_width,thumbnail_image_height;medium_image_width,medium_image_height;large_image_width,large_image_height'
	),

	// Fields
	'fields' => array
	(
		'store_configuration_name' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_store']['store_configuration_name'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => array('rgxp'=>'alnum', 'mandatory'=>true, 'maxlength'=>255)
		),
		'cookie_duration' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_store']['cookie_duration'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => array('rgxp'=>'digit', 'maxlength'=>4)
		),
		'root_asset_import_path' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_store']['root_asset_import_path'],
			'exclude'                 => true,
			'inputType'               => 'fileTree',
			'eval'                    => array('fieldType'=>'radio', 'mandatory'=>false)
		),
		'enabled_modules' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_store']['enabled_modules'],
			'exclude'                 => true,
			'inputType'               => 'checkbox',
			'options_callback'        => array('tl_store', 'getIsotopeFEModuleList'),
			'eval'                    => array('multiple'=>true)
		),
		'checkout_login_module' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_store']['checkout_login_module'],
			'exclude'                 => true,
			'inputType'               => 'radio',
			'options_callback'        => array('tl_store', 'getLoginModuleList'),
			'eval'                    => array('mandatory'=>true,'moduleTypes'=>array('login'))
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
			'eval'                    => array('fieldType'=>'radio', 'files'=>true, 'filesOnly'=>true, 'mandatory'=>true, 'extensions' => 'jpg,jpeg,gif,png,tif,tiff')
		),
		'gallery_thumbnail_image_width' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_store']['gallery_thumbnail_image_width'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => array('rgxp'=>'digit', 'mandatory'=>true, 'maxlength'=>10)
		),
		'gallery_thumbnail_image_height' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_store']['gallery_thumbnail_image_height'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => array('rgxp'=>'digit', 'maxlength'=>10)
		), 
		'thumbnail_image_width' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_store']['thumbnail_image_width'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => array('rgxp'=>'digit', 'mandatory'=>true, 'maxlength'=>10)
		),
		'thumbnail_image_height' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_store']['thumbnail_image_height'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => array('rgxp'=>'digit', 'maxlength'=>10)
		),
		'medium_image_width' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_store']['medium_image_width'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => array('rgxp'=>'digit', 'mandatory'=>true, 'maxlength'=>10)
		),
		'medium_image_height' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_store']['medium_image_height'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => array('rgxp'=>'digit', 'maxlength'=>10)
		),
		'large_image_width' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_store']['large_image_width'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => array('rgxp'=>'digit', 'mandatory'=>true, 'maxlength'=>10)
		)
		,
		'large_image_height' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_store']['large_image_height'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => array('rgxp'=>'digit', 'maxlength'=>10)
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
		'currency' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_store']['currency'],
			'exclude'                 => true,
			'inputType'               => 'select',
			'options'				  => &$GLOBALS['TL_LANG']['CUR'],
			'eval'                    => array('includeBlankOption'=>true, 'mandatory'=>true),
		),
		'currencySymbol' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_store']['currencySymbol'],
			'exclude'                 => true,
			'inputType'               => 'checkbox',
		),
		'currencyPosition' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_store']['currencyPosition'],
			'exclude'                 => true,
			'inputType'               => 'radio',
			'default'				  => 'left',
			'options'				  => array('left', 'right'),
			'reference'				  => &$GLOBALS['TL_LANG']['tl_store'],
		),
		'currencyFormat' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_store']['currencyFormat'],
			'exclude'                 => true,
			'inputType'               => 'select',
			'options'				  => array_keys($GLOBALS['ISO_NUM']),
			'eval'                    => array('includeBlankOption'=>true, 'mandatory'=>true),
		),
		'countries' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_store']['countries'],
			'exclude'                 => true,
			'inputType'               => 'select',
			'default'                 => array_keys($this->getCountries()),
			'options'                 => $this->getCountries(),
			'eval'                    => array('mandatory'=>true, 'multiple'=>true, 'size'=>8),
		),
		'address_fields' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_store']['address_fields'],
			'exclude'                 => true,
			'inputType'               => 'checkboxWizard',
			'options_callback'		  => array('tl_store', 'getAddressFields'),
			'eval'                    => array('mandatory'=>true, 'multiple'=>true),
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

	/**
	 * Return all editable fields of table tl_member.
	 * 
	 * @access public
	 * @return array
	 */
	public function getIsotopeFEModuleList()
	{
		$return = array();

		foreach ($GLOBALS['ISOTOPE_FE_MODULES'] as $moduleKey)
		{
			$return[$moduleKey] = $GLOBALS['TL_LANG']['FMD'][$moduleKey][0];
		}

		return $return;
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

