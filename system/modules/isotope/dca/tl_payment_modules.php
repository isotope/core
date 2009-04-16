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
 * @copyright  Winans Creative/Fred Bliss 2009 
 * @author     Fred Bliss 
 * @package    Isotope 
 * @license    Commercial 
 * @filesource
 */


/**
 * Table tl_payment_modules 
 */
$GLOBALS['TL_DCA']['tl_payment_modules'] = array
(

	// Config
	'config' => array
	(
		'dataContainer'               => 'Table',
		'ctable'                      => array('tl_payment_options'),
		'switchToEdit'                => true,
		'enableVersioning'            => true
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
			'fields'                  => array('name'),
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
				'label'               => &$GLOBALS['TL_LANG']['tl_payment_modules']['edit'],
				'href'                => 'act=edit',
				'icon'                => 'edit.gif'
			),
			'copy' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_payment_modules']['copy'],
				'href'                => 'act=paste&amp;mode=copy',
				'icon'                => 'copy.gif'
			),
			'delete' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_payment_modules']['delete'],
				'href'                => 'act=delete',
				'icon'                => 'delete.gif',
				'attributes'          => 'onclick="if (!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\')) return false; Backend.getScrollOffset();"'
			),
			'show' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_payment_modules']['show'],
				'href'                => 'act=show',
				'icon'                => 'show.gif'
			),
			'buttons' => array
			(
				'button_callback'     => array('tl_payment_modules', 'moduleOperations'),
			)
		)
	),

	// Palettes
	'palettes' => array
	(
		'__selector__'                => array('type'),
		'default'                     => 'name,type;enabled',
		'payflow'                     => 'name,type;url,verbosity,payment_action,tender,fraud_status,new_status;creditcards,cc_verification;partner,merchant,user,password;debug,enabled',
	),

	// Fields
	'fields' => array
	(
		'name' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_payment_modules']['name'],
			'exclude'                 => true,
			'search'                  => true,
			'inputType'               => 'text',
			'eval'                    => array('mandatory'=>true, 'maxlength'=>255)
		),
		'type' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_payment_modules']['type'],
			'default'                 => 'cc',
			'exclude'                 => true,
			'filter'                  => true,
			'inputType'               => 'select',
			'options_callback'        => array('tl_payment_modules', 'getModules'),
			'reference'               => &$GLOBALS['TL_LANG']['PAY'],
			'eval'                    => array('helpwizard'=>true, 'submitOnChange'=>true, 'tl_class'=>'w50')
		),
		'url' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_payment_modules']['url'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => array('mandatory'=>true, 'maxlength'=>255, 'rgxp'=>'url')
		),
		'verbosity' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_payment_modules']['verbosity'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => array('maxlength'=>255)
		),
		'payment_action' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_payment_modules']['payment_action'],
			'exclude'                 => true,
			'inputType'               => 'select',
			'options'                 => array('authorize'),
			'reference'				  => &$GLOBALS['TL_LANG']['ISO'],
			'eval'                    => array('mandatory'=>true),
		),
		'tender' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_payment_modules']['tender'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => array('mandatory'=>true, 'maxlength'=>255),
		),
		'fraud_status' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_payment_modules']['fraud_status'],
			'exclude'                 => true,
			'inputType'               => 'select',
			'default'                 => 'onhold',
			'options'                 => array('new', 'processing', 'onhold', 'complete'),
			'reference'				  => &$GLOBALS['TL_LANG']['ISO'],
			'eval'                    => array('mandatory'=>true),
		),
		'new_status' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_payment_modules']['new_status'],
			'exclude'                 => true,
			'inputType'               => 'select',
			'default'                 => 'processing',
			'options'                 => array('new', 'processing', 'onhold', 'complete'),
			'reference'				  => &$GLOBALS['TL_LANG']['ISO'],
			'eval'                    => array('mandatory'=>true),
		),
		'creditcards' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_payment_modules']['creditcards'],
			'exclude'                 => true,
			'inputType'               => 'checkbox',
			'options'                 => array('mastercard', 'visa', 'amex', 'discover', 'other'),
			'reference'				  => &$GLOBALS['TL_LANG']['ISO'],
			'eval'                    => array('mandatory'=>true, 'multiple'=>true),
		),
		'cc_verification' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_payment_modules']['cc_verification'],
			'exclude'                 => true,
			'inputType'               => 'checkbox',
		),
		'partner' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_payment_modules']['partner'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => array('mandatory'=>true, 'maxlength'=>255),
		),
		'merchant' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_payment_modules']['merchant'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => array('mandatory'=>true, 'maxlength'=>255),
		),
		'user' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_payment_modules']['user'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => array('mandatory'=>true, 'maxlength'=>255),
		),
		'password' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_payment_modules']['password'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => array('mandatory'=>true, 'maxlength'=>255),
		),
		'debug' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_payment_modules']['debug'],
			'exclude'                 => true,
			'inputType'               => 'checkbox',
		),		
		'enabled' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_payment_modules']['enabled'],
			'exclude'                 => true,
			'inputType'               => 'checkbox',
		),
	)
);


/**
 * tl_payment_modules class.
 * 
 * @extends Backend
 */
class tl_payment_modules extends Backend
{

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
		
		$this->import($strClass);
		
		return $this->$strClass->moduleOperations($arrRow);
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
}

