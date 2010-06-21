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
 * Load required DCA and language files
 */
$this->loadDataContainer('tl_iso_products');
$this->loadLanguageFile('tl_iso_products');
$this->loadLanguageFile('subdivisions');

		
/**
 * Table tl_iso_shipping_modules
 */
$GLOBALS['TL_DCA']['tl_iso_shipping_modules'] = array
(

	// Config
	'config' => array
	(
		'dataContainer'               => 'Table',
		'ctable'                      => array('tl_iso_shipping_options'),
		'switchToEdit'                => true,
		'enableVersioning'            => true,
		'closed'					  => true,
		'onload_callback' => array
		(
			array('tl_iso_shipping_modules', 'checkPermission'),
		),
		'ondelete_callback'			  => array
		(
			array('tl_iso_shipping_modules', 'archiveRecord'),
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
			'label_callback'		  => array('tl_iso_shipping_modules', 'addIcon'),
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
				'label'					=> &$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['new'],
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
				'label'               => &$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['edit'],
				'href'                => 'act=edit',
				'icon'                => 'edit.gif'
			),
			'copy' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['copy'],
				'href'                => 'act=copy',
				'icon'                => 'copy.gif'
			),
			'delete' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['delete'],
				'href'                => 'act=delete',
				'icon'                => 'delete.gif',
				'attributes'          => 'onclick="if (!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\')) return false; Backend.getScrollOffset();"'
			),
			'show' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['show'],
				'href'                => 'act=show',
				'icon'                => 'show.gif'
			),
			'buttons' => array
			(
				'button_callback'     => array('tl_iso_shipping_modules', 'moduleOperations'),
			)
		)
	),

	// Palettes
	'palettes' => array
	(
		'__selector__'					=> array('type', 'protected'),
		'default'						=> '{title_legend},type,name,label;{note_legend:hide},note;{price_legend},price,tax_class;{config_legend},countries,subdivisions,minimum_total,maximum_total;{expert_legend:hide},guests,protected;{enabled_legend},enabled',
		'flat'							=> '{title_legend},type,name,label;{note_legend:hide},note;{price_legend},price,tax_class,flatCalculation,surcharge_field;{config_legend},countries,subdivisions,minimum_total,maximum_total;{expert_legend:hide},guests,protected;{enabled_legend},enabled',
		'order_total'					=> '{title_legend},type,name,label;{note_legend:hide},note;{price_legend},price,tax_class;{config_legend},countries,subdivisions,minimum_total,maximum_total;{expert_legend:hide},guests,protected;{enabled_legend},enabled',
		'weight_total'					=> '{title_legend},type,name,label;{note_legend:hide},note;{config_legend},weight_unit,countries,subdivisions,minimum_total,maximum_total;{expert_legend:hide},guests,protected;{enabled_legend},enabled',
		'ups'							=> '{title_legend},type,name,label;{note_legend:hide},note;{price_legend},tax_class;{ups_legend},ups_enabledService,ups_accessKey,ups_developersKey,ups_userName,ups_password;{config_legend},countries,subdivisions,minimum_total,maximum_total;{expert_legend:hide},guests,protected;{enabled_legend},enabled',
		'usps'							=> '{title_legend},type,name,label;{note_legend:hide},note;{price_legend},tax_class;{usps_legend},usps_enabledService,usps_userName;{config_legend},countries,subdivisions,minimum_total,maximum_total;{expert_legend:hide},guests,protected;{enabled_legend},enabled'
	),
	
	// Subpalettes
	'subpalettes' => array
	(
		'protected'						=> 'groups',
	),

	// Fields
	'fields' => array
	(
		'type' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['type'],
			'default'                 => 'cc',
			'exclude'                 => true,
			'filter'                  => true,
			'inputType'               => 'select',
			'default'				  => 'flat',
			'options_callback'        => array('tl_iso_shipping_modules', 'getModules'),
			'reference'               => &$GLOBALS['TL_LANG']['SHIP'],
			'eval'                    => array('helpwizard'=>true, 'submitOnChange'=>true, 'tl_class'=>'clr')
		),
		'name' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['name'],
			'exclude'                 => true,
			'search'                  => true,
			'inputType'               => 'text',
			'eval'                    => array('mandatory'=>true, 'maxlength'=>255, 'tl_class'=>'w50')
		),
		'label' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['label'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => array('mandatory'=>true, 'maxlength'=>255, 'tl_class'=>'w50'),
		),
		'note' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['note'],
			'exclude'                 => true,
			'inputType'               => 'textarea',
			'eval'                    => array('rte'=>'tinyMCE', 'decodeEntities'=>true),
		),
		'ups_enabledService' => array
		(
			'label'					  => &$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['ups_enabledService'],
			'exclude'				  => true,
			'inputType'				  => 'select',
			'options'				  => $GLOBALS['TL_LANG']['tl_iso_shipping_modules']['ups_service'],
			'eval'					  => array('mandatory'=>true)
		),
		'ups_accessKey' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['ups_accessKey'],
			'exclude'                 => true,
			'search'                  => true,
			'inputType'               => 'text',
			'eval'                    => array('mandatory'=>true, 'maxlength'=>255)
		),
		'ups_developersKey' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['ups_developersKey'],
			'exclude'                 => true,
			'search'                  => true,
			'inputType'               => 'text',
			'eval'                    => array('mandatory'=>true, 'maxlength'=>255)
		),
		'ups_userName' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['ups_userName'],
			'exclude'                 => true,
			'search'                  => true,
			'inputType'               => 'text',
			'eval'                    => array('mandatory'=>true, 'maxlength'=>255)
		),
		'ups_password' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['ups_password'],
			'exclude'                 => true,
			'search'                  => true,
			'inputType'               => 'text',
			'eval'                    => array('mandatory'=>true, 'maxlength'=>255)
		),
		'usps_enabledService' => array
		(
			'label'					  => &$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['usps_enabledService'],
			'exclude'				  => true,
			'inputType'				  => 'select',
			'options'				  => $GLOBALS['TL_LANG']['tl_iso_shipping_modules']['usps_service'],
			'eval'					  => array('mandatory'=>true)
		),
		'usps_userName' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['usps_userName'],
			'exclude'                 => true,
			'search'                  => true,
			'inputType'               => 'text',
			'eval'                    => array('mandatory'=>true, 'maxlength'=>255)
		),
		'countries' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['countries'],
			'exclude'                 => true,
			'inputType'               => 'select',
			'default'                 => array_keys($this->getCountries()),
			'options'                 => $this->getCountries(),
			'eval'                    => array('multiple'=>true, 'size'=>8, 'tl_class'=>'w50 w50h'),
		),
		'subdivisions' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['subdivisions'],
			'exclude'                 => true,
			'sorting'                 => true,
			'inputType'               => 'conditionalselect',
			'options'				  => &$GLOBALS['TL_LANG']['DIV'],
			'eval'                    => array('multiple'=>true, 'size'=>8, 'conditionField'=>'countries', 'tl_class'=>'w50 w50h'),
		),
		'minimum_total' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['minimum_total'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => array('maxlength'=>255, 'rgxp'=>'price', 'tl_class'=>'w50'),
		),
		'maximum_total' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['maximum_total'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => array('maxlength'=>255, 'rgxp'=>'price', 'tl_class'=>'w50'),
		),
		'price' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['price'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => array('maxlength'=>10, 'rgxp'=>'price', 'tl_class'=>'w50'),
		),
		'tax_class' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['tax_class'],
			'filter'				=> true,
			'inputType'				=> 'select',
			'foreignKey'			=> 'tl_iso_tax_class.name',
			'attributes'			=> array('legend'=>'tax_legend'),
			'eval'					=> array('includeBlankOption'=>true, 'tl_class'=>'w50'),
		),
		'flatCalculation' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['flatCalculation'],
			'exclude'                 => true,
			'inputType'               => 'select',
			'options'                 => array('flat', 'perProduct', 'perItem'),
			'reference'               => &$GLOBALS['TL_LANG']['tl_iso_shipping_modules'],
			'eval'                    => array('tl_class'=>'w50'),
		),
		'surcharge_field' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['surcharge_field'],
			'exclude'                 => true,
			'inputType'               => 'select',
			'options'				  => array_keys($GLOBALS['TL_DCA']['tl_iso_products']['fields']),
			'reference'				  => &$GLOBALS['TL_LANG']['tl_iso_products'],
			'eval'                    => array('includeBlankOption'=>true, 'tl_class'=>'w50'),
		),
		'weight_unit' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['weight_unit'],
			'exclude'                 => true,
			'default'				  => 'kg',
			'inputType'               => 'select',
			'options'				  => array('mg', 'g', 'kg', 't', 'ct', 'oz', 'lb', 'st', 'grain'),
			'reference'				  => &$GLOBALS['TL_LANG']['WGT'],
			'eval'                    => array('tl_class'=>'clr', 'helpwizard'=>&$GLOBALS['TL_LANG']['WGT']),
		),
		'guests' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['guests'],
			'exclude'                 => true,
			'inputType'               => 'checkbox',
		),
		'protected' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['protected'],
			'exclude'                 => true,
			'inputType'               => 'checkbox',
			'eval'                    => array('submitOnChange'=>true)
		),
		'groups' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['groups'],
			'exclude'                 => true,
			'inputType'               => 'checkbox',
			'foreignKey'              => 'tl_member_group.name',
			'eval'                    => array('multiple'=>true)
		),
		'enabled' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['enabled'],
			'exclude'                 => true,
			'inputType'               => 'checkbox',
		),
	)
);



/**
 * tl_iso_shipping_modules class.
 * 
 * @extends Backend
 */
class tl_iso_shipping_modules extends Backend
{

	public function checkPermission($dc)
	{
		if (strlen($this->Input->get('act')))
		{
			$GLOBALS['TL_DCA']['tl_iso_shipping_modules']['config']['closed'] = false;
		}
		
		// Hide archived (used and deleted) modules
		$arrModules = $this->Database->execute("SELECT id FROM tl_iso_shipping_modules WHERE archive<2")->fetchEach('id');
		
		if (!count($arrModules))
		{
			$arrModules = array(0);
		}

		$GLOBALS['TL_DCA']['tl_iso_shipping_modules']['list']['sorting']['root'] = $arrModules;

		// Check current action
		switch ($this->Input->get('act'))
		{
			case 'edit':
			case 'copy':
			case 'delete':
			case 'show':
				if (!in_array($this->Input->get('id'), $arrModules))
				{
					$this->log('Not enough permissions to '.$this->Input->get('act').' shipping module ID "'.$this->Input->get('id').'"', 'tl_iso_shipping_modules checkPermission()', TL_ACCESS);
					$this->redirect('typolight/main.php?act=error');
				}
				break;

			case 'editAll':
			case 'copyAll':
			case 'deleteAll':
				$session = $this->Session->getData();
				$session['CURRENT']['IDS'] = array_intersect($session['CURRENT']['IDS'], $arrModules);
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
			return $objModule->moduleOperations();
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

