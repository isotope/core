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
 * Load tl_iso_products data container and language files
 */
$this->loadDataContainer('tl_iso_products');
$this->loadLanguageFile('tl_iso_products');


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
			array('IsotopeBackend', 'initializeSetupModule'),
			array('tl_iso_shipping_modules', 'checkPermission'),
		)
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
			'label_callback'		  => array('IsotopeBackend', 'addPublishIcon'),
		),
		'global_operations' => array
		(
			'back' => array
			(
				'label'					=> &$GLOBALS['TL_LANG']['MSC']['backBT'],
				'href'					=> 'mod=&table=',
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
				'icon'                => 'copy.gif',
				'button_callback'     => array('tl_iso_shipping_modules', 'copyShippingModule'),
			),
			'delete' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['delete'],
				'href'                => 'act=delete',
				'icon'                => 'delete.gif',
				'attributes'          => 'onclick="if (!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\')) return false; Backend.getScrollOffset();"',
				'button_callback'     => array('tl_iso_shipping_modules', 'deleteShippingModule'),
			),
			'show' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['show'],
				'href'                => 'act=show',
				'icon'                => 'show.gif'
			),
			'options' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['options'],
				'href'                => 'table=tl_iso_shipping_options',
				'icon'                => 'tablewizard.gif',
				'button_callback'     => array('tl_iso_shipping_modules', 'optionsButton'),
			),
		)
	),

	// Palettes
	'palettes' => array
	(
		'__selector__'					=> array('type', 'protected'),
		'default'						=> '{title_legend},type,name',
		'flat'							=> '{title_legend},type,name,label;{note_legend:hide},note;{price_legend},price,tax_class,flatCalculation,surcharge_field;{config_legend},countries,subdivisions,postalCodes,minimum_total,maximum_total,product_types;{expert_legend:hide},guests,protected;{enabled_legend},enabled',
		'order_total'					=> '{title_legend},type,name,label;{note_legend:hide},note;{price_legend},price,tax_class;{config_legend},countries,subdivisions,minimum_total,maximum_total,product_types;{expert_legend:hide},guests,protected;{enabled_legend},enabled',
		'weight_total'					=> '{title_legend},type,name,label;{note_legend:hide},note;{price_legend},tax_class;{config_legend},weight_unit,countries,subdivisions,minimum_total,maximum_total,product_types;{expert_legend:hide},guests,protected;{enabled_legend},enabled',
		'ups'							=> '{title_legend},type,name,label;{note_legend:hide},note;{price_legend},tax_class;{ups_legend},ups_enabledService,ups_accessKey,ups_userName,ups_password;{config_legend},weight_unit,countries,subdivisions,minimum_total,maximum_total,product_types;{expert_legend:hide},guests,protected;{enabled_legend},enabled',
		'usps'							=> '{title_legend},type,name,label;{note_legend:hide},note;{price_legend},tax_class;{usps_legend},usps_enabledService,usps_userName;{config_legend},countries,subdivisions,minimum_total,maximum_total,product_types;{expert_legend:hide},guests,protected;{enabled_legend},enabled'
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
			'reference'               => &$GLOBALS['ISO_LANG']['SHIP'],
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
			'eval'                    => array('maxlength'=>255, 'tl_class'=>'w50'),
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
			'options'                 => $this->getCountries(),
			'eval'                    => array('multiple'=>true, 'size'=>8, 'tl_class'=>'w50 w50h'),
		),
		'subdivisions' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['subdivisions'],
			'exclude'                 => true,
			'sorting'                 => true,
			'inputType'               => 'conditionalselect',
			'options_callback'		  => array('IsotopeBackend', 'getSubdivisions'),
			'eval'                    => array('multiple'=>true, 'size'=>8, 'conditionField'=>'countries', 'tl_class'=>'w50 w50h'),
		),
		'postalCodes' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['postalCodes'],
			'exclude'                 => true,
			'inputType'               => 'textarea',
			'eval'                    => array('style'=>'height:40px', 'tl_class'=>'clr'),
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
		'product_types' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['product_types'],
			'exclude'                 => true,
			'inputType'               => 'select',
			'foreignKey'			  => 'tl_iso_producttypes.name',
			'eval'                    => array('multiple'=>true, 'size'=>8, 'tl_class'=>'clr'),
		),
		'price' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['price'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => array('maxlength'=>16, 'rgxp'=>'surcharge', 'tl_class'=>'w50'),
		),
		'tax_class' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['tax_class'],
			'exclude'                 => true,
			'filter'				=> true,
			'inputType'				=> 'select',
			'options_callback'		=> array('IsotopeBackend', 'getTaxClassesWithSplit'),
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
			'reference'				  => &$GLOBALS['ISO_LANG']['WGT'],
			'eval'                    => array('tl_class'=>'clr', 'helpwizard'=>&$GLOBALS['ISO_LANG']['WGT']),
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
 * Class tl_iso_shipping_modules
 * Provide miscellaneous methods that are used by the data configuration array.
 */
class tl_iso_shipping_modules extends Backend
{

	/**
	 * Check permissions to edit table tl_iso_shipping_modules
	 * @return void
	 */
	public function checkPermission()
	{
		// Do not run the permission check on other Isotope modules
		if ($this->Input->get('mod') != 'shipping')
		{
			return;
		}
		
		$this->import('BackendUser', 'User');
		
		if ($this->User->isAdmin)
		{
			return;
		}

		// Set root IDs
		if (!is_array($this->User->iso_shipping_modules) || count($this->User->iso_shipping_modules) < 1)
		{
			$root = array(0);
		}
		else
		{
			$root = $this->User->iso_shipping_modules;
		}

		$GLOBALS['TL_DCA']['tl_iso_shipping_modules']['list']['sorting']['root'] = $root;

		// Check permissions to add shipping modules
		if (!$this->User->hasAccess('create', 'iso_shipping_modulep'))
		{
			$GLOBALS['TL_DCA']['tl_iso_shipping_modules']['config']['closed'] = true;
			unset($GLOBALS['TL_DCA']['tl_iso_shipping_modules']['list']['global_operations']['new']);
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

					if (is_array($arrNew['tl_iso_shipping_modules']) && in_array($this->Input->get('id'), $arrNew['tl_iso_shipping_modules']))
					{
						// Add permissions on user level
						if ($this->User->inherit == 'custom' || !$this->User->groups[0])
						{
							$objUser = $this->Database->prepare("SELECT iso_shipping_modules, iso_shipping_modulep FROM tl_user WHERE id=?")
													   ->limit(1)
													   ->execute($this->User->id);

							$arrPermissions = deserialize($objUser->iso_shipping_modulep);

							if (is_array($arrPermissions) && in_array('create', $arrPermissions))
							{
								$arrAccess = deserialize($objUser->iso_shipping_modules);
								$arrAccess[] = $this->Input->get('id');

								$this->Database->prepare("UPDATE tl_user SET iso_shipping_modules=? WHERE id=?")
											   ->execute(serialize($arrAccess), $this->User->id);
							}
						}

						// Add permissions on group level
						elseif ($this->User->groups[0] > 0)
						{
							$objGroup = $this->Database->prepare("SELECT iso_shipping_modules, iso_shipping_modulep FROM tl_user_group WHERE id=?")
													   ->limit(1)
													   ->execute($this->User->groups[0]);

							$arrPermissions = deserialize($objGroup->iso_shipping_modulep);

							if (is_array($arrPermissions) && in_array('create', $arrPermissions))
							{
								$arrAccess = deserialize($objGroup->iso_shipping_modules);
								$arrAccess[] = $this->Input->get('id');

								$this->Database->prepare("UPDATE tl_user_group SET iso_shipping_modules=? WHERE id=?")
											   ->execute(serialize($arrAccess), $this->User->groups[0]);
							}
						}

						// Add new element to the user object
						$root[] = $this->Input->get('id');
						$this->User->iso_shipping_modules = $root;
					}
				}
				// No break;

			case 'copy':
			case 'delete':
			case 'show':
				if (!in_array($this->Input->get('id'), $root) || ($this->Input->get('act') == 'delete' && !$this->User->hasAccess('delete', 'iso_shipping_modulep')))
				{
					$this->log('Not enough permissions to '.$this->Input->get('act').' shipping module ID "'.$this->Input->get('id').'"', __METHOD__, TL_ERROR);
					$this->redirect('contao/main.php?act=error');
				}
				break;

			case 'editAll':
			case 'deleteAll':
			case 'overrideAll':
				$session = $this->Session->getData();
				if ($this->Input->get('act') == 'deleteAll' && !$this->User->hasAccess('delete', 'iso_shipping_modulep'))
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
					$this->log('Not enough permissions to '.$this->Input->get('act').' shipping modules', __METHOD__, TL_ERROR);
					$this->redirect('contao/main.php?act=error');
				}
				break;
		}
	}


	/**
	 * Get a list of all shipping modules available
	 * @return array
	 */
	public function getModules()
	{
		$arrModules = array();

		if (is_array($GLOBALS['ISO_SHIP']) && count($GLOBALS['ISO_SHIP']))
		{
			foreach ($GLOBALS['ISO_SHIP'] as $module => $class)
			{
				$arrModules[$module] = (strlen($GLOBALS['ISO_LANG']['SHIP'][$module][0]) ? $GLOBALS['ISO_LANG']['SHIP'][$module][0] : $module);
			}
		}

		return $arrModules;
	}


	/**
	 * Callback for options button
	 * @param array
	 * @param string
	 * @param string
	 * @param string
	 * @param string
	 * @param string
	 * @return string
	 */
	public function optionsButton($row, $href, $label, $title, $icon, $attributes)
	{
		switch ($row['type'])
		{
			case 'order_total':
			case 'weight_total':
				return '<a href="'.$this->addToUrl($href.'&amp;id='.$row['id']).'" title="'.specialchars($title).'"'.$attributes.'>'.$this->generateImage($icon, $label).'</a> ';

			default:
				return '';
		}
	}
	
	
	/**
	 * Return the copy shipping module button
	 * @param array
	 * @param string
	 * @param string
	 * @param string
	 * @param string
	 * @param string
	 * @return string
	 */
	public function copyShippingModule($row, $href, $label, $title, $icon, $attributes)
	{
		return ($this->User->isAdmin || $this->User->hasAccess('create', 'iso_shipping_modulep')) ? '<a href="'.$this->addToUrl($href.'&amp;id='.$row['id']).'" title="'.specialchars($title).'"'.$attributes.'>'.$this->generateImage($icon, $label).'</a> ' : $this->generateImage(preg_replace('/\.gif$/i', '_.gif', $icon)).' ';
	}


	/**
	 * Return the delete shipping module button
	 * @param array
	 * @param string
	 * @param string
	 * @param string
	 * @param string
	 * @param string
	 * @return string
	 */
	public function deleteShippingModule($row, $href, $label, $title, $icon, $attributes)
	{
		return ($this->User->isAdmin || $this->User->hasAccess('delete', 'iso_shipping_modulep')) ? '<a href="'.$this->addToUrl($href.'&amp;id='.$row['id']).'" title="'.specialchars($title).'"'.$attributes.'>'.$this->generateImage($icon, $label).'</a> ' : $this->generateImage(preg_replace('/\.gif$/i', '_.gif', $icon)).' ';
	}
}

