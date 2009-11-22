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
 * Table tl_shipping_modules
 */
$GLOBALS['TL_DCA']['tl_shipping_modules'] = array
(

	// Config
	'config' => array
	(
		'dataContainer'               => 'Table',
		'ctable'                      => array('tl_shipping_options'),
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
			'fields'                  => array('name', 'type'),
			'format'                  => '%s <span style="color:#b3b3b3; padding-left:3px;">[%s]</span>',
			'label_callback'		  => array('tl_shipping_modules', 'addIcon'),
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
				'label'               => &$GLOBALS['TL_LANG']['tl_shipping_modules']['edit'],
				'href'                => 'act=edit',
				'icon'                => 'edit.gif'
			),
			'copy' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_shipping_modules']['copy'],
				'href'                => 'act=copy',
				'icon'                => 'copy.gif'
			),
			'delete' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_shipping_modules']['delete'],
				'href'                => 'act=delete',
				'icon'                => 'delete.gif',
				'attributes'          => 'onclick="if (!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\')) return false; Backend.getScrollOffset();"'
			),
			'show' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_shipping_modules']['show'],
				'href'                => 'act=show',
				'icon'                => 'show.gif'
			),
			'buttons' => array
			(
				'button_callback'     => array('tl_shipping_modules', 'moduleOperations'),
			)
		)
	),

	// Palettes
	'palettes' => array
	(
		'__selector__'					=> array('type', 'protected'),
		'default'						=> 'type,name,label,note;countries,minimum_total,maximum_total;guests,protected;enabled',
		'order_total'					=> 'type,name,label,note;price;countries,minimum_total,maximum_total;guests,protected;enabled',
		'flat'							=> 'type,name,label,note;price,flatCalculation,surcharge_field;countries,minimum_total,maximum_total;guests,protected;enabled',
	),
	
	// Subpalettes
	'subpalettes' => array
	(
		'protected'						=> 'groups',
	),

	// Fields
	'fields' => array
	(
		'name' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_shipping_modules']['name'],
			'exclude'                 => true,
			'search'                  => true,
			'inputType'               => 'text',
			'eval'                    => array('mandatory'=>true, 'maxlength'=>255)
		),
		'type' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_shipping_modules']['type'],
			'default'                 => 'cc',
			'exclude'                 => true,
			'filter'                  => true,
			'inputType'               => 'select',
			'default'				  => 'order_total',
			'options_callback'        => array('tl_shipping_modules', 'getModules'),
			'reference'               => &$GLOBALS['TL_LANG']['SHIP'],
			'eval'                    => array('helpwizard'=>true, 'submitOnChange'=>true)
		),
		'label' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_shipping_modules']['label'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => array('maxlength'=>255, 'mandatory'=>true),
		),
		'note' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_shipping_modules']['note'],
			'exclude'                 => true,
			'inputType'               => 'textarea',
			'eval'                    => array('rte'=>'tinyMCE', 'decodeEntities'=>true),
		),
		'countries' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_shipping_modules']['countries'],
			'exclude'                 => true,
			'inputType'               => 'select',
			'default'                 => array_keys($this->getCountries()),
			'options'                 => $this->getCountries(),
			'eval'                    => array('mandatory'=>true, 'multiple'=>true, 'size'=>8),
		),
		'minimum_total' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_shipping_modules']['minimum_total'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => array('maxlength'=>255, 'rgxp'=>'digit'),
		),
		'maximum_total' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_shipping_modules']['maximum_total'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => array('maxlength'=>255, 'rgxp'=>'digit'),
		),
		'price' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_shipping_modules']['price'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => array('maxlength'=>10, 'rgxp'=>'digit'),
		),
		'flatCalculation' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_shipping_modules']['flatCalculation'],
			'exclude'                 => true,
			'inputType'               => 'select',
			'options'                 => array('flat', 'perProduct', 'perItem'),
			'reference'               => &$GLOBALS['TL_LANG']['tl_shipping_modules'],
		),
		'surcharge_field' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_shipping_modules']['surcharge_field'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => array('maxlength'=>255),
		),
		'guests' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_shipping_modules']['guests'],
			'exclude'                 => true,
			'inputType'               => 'checkbox',
		),
		'protected' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_shipping_modules']['protected'],
			'exclude'                 => true,
			'inputType'               => 'checkbox',
			'eval'                    => array('submitOnChange'=>true)
		),
		'groups' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_shipping_modules']['groups'],
			'exclude'                 => true,
			'inputType'               => 'checkbox',
			'foreignKey'              => 'tl_member_group.name',
			'eval'                    => array('multiple'=>true)
		),
		'enabled' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_shipping_modules']['enabled'],
			'exclude'                 => true,
			'default'				  => 0,
			'inputType'               => 'checkbox'
		)
	)
);



/**
 * tl_shipping_modules class.
 * 
 * @extends Backend
 */
class tl_shipping_modules extends Backend
{

	/**
	 * Return a string of more buttons for the current shipping module.
	 * 
	 * @todo Collect additional buttons from shipping modules.
	 * @access public
	 * @param array $arrRow
	 * @return string
	 */
	public function moduleOperations($arrRow)
	{
		$strClass = $GLOBALS['ISO_SHIP'][$arrRow['type']];

		if (!strlen($strClass) || !$this->classFileExists($strClass))
			return '';
			
		try 
		{
			$objModule = new $strClass($arrRow);
			return $objModule->moduleOperations($arrRow['id']);
		}
		catch (Exception $e) {}
		
		return '';
	}
	
	
	/**
	 * Get a list of all shipping modules available.
	 * 
	 * @access public
	 * @return array
	 */
	public function getModules()
	{
		$arrModules = array();
		
		if (is_array($GLOBALS['ISO_SHIP']) && count($GLOBALS['ISO_SHIP']))
		{
			foreach( $GLOBALS['ISO_SHIP'] as $module => $class )
			{
				$arrModules[$module] = (strlen($GLOBALS['TL_LANG']['SHIP'][$module][0]) ? $GLOBALS['TL_LANG']['SHIP'][$module][0] : $module);
			}
		}
		
		return $arrModules;
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

