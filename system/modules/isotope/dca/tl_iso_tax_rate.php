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
 * @copyright  Isotope eCommerce Workgroup 2009-2011
 * @author     Andreas Schempp <andreas@schempp.ch>
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */


/**
 * Table tl_iso_tax_rate
 */
$GLOBALS['TL_DCA']['tl_iso_tax_rate'] = array
(

	// Config
	'config' => array
	(
		'dataContainer'               => 'Table',
		'enableVersioning'            => true,
		'closed'					  => true,
		'onload_callback'			  => array
		(
			array('IsotopeBackend', 'initializeSetupModule'),
			array('tl_iso_tax_rate', 'checkPermission'),
			array('tl_iso_tax_rate', 'addCurrencyRate'),
		),
	),

	// List
	'list' => array
	(
		'sorting' => array
		(
			'mode'                    => 1,
			'fields'                  => array('country', 'name'),
			'panelLayout'             => 'filter;search,limit',
		),
		'label' => array
		(
			'fields'				  => array('name'),
			'format'				  => '%s',
			'label_callback'		  => array('tl_iso_tax_rate', 'listRow'),
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
				'label'					=> &$GLOBALS['TL_LANG']['tl_iso_tax_rate']['new'],
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
				'label'               => &$GLOBALS['TL_LANG']['tl_iso_tax_rate']['edit'],
				'href'                => 'act=edit',
				'icon'                => 'edit.gif'
			),
			'copy' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_iso_tax_rate']['copy'],
				'href'                => 'act=copy',
				'icon'                => 'copy.gif',
				'button_callback'     => array('tl_iso_tax_rate', 'copyTaxClass'),
			),
			'delete' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_iso_tax_rate']['delete'],
				'href'                => 'act=delete',
				'icon'                => 'delete.gif',
				'attributes'          => 'onclick="if (!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\')) return false; Backend.getScrollOffset();"',
				'button_callback'     => array('tl_iso_tax_rate', 'deleteTaxClass'),
			),
			'show' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_iso_tax_rate']['show'],
				'href'                => 'act=show',
				'icon'                => 'show.gif'
			)
		)
	),

	// Palettes
	'palettes' => array
	(
		'default'                     => '{name_legend},name,label;{rate_legend},rate;{location_legend},address,country,subdivision,postalCodes;{condition_legend},amount;{config_legend},config,stop',
	),


	// Fields
	'fields' => array
	(
		'name' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_tax_rate']['name'],
			'inputType'               => 'text',
			'eval'                    => array('mandatory'=>true, 'maxlength'=>255, 'tl_class'=>'w50'),
		),
		'label' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_tax_rate']['label'],
			'inputType'               => 'text',
			'eval'                    => array('maxlength'=>255, 'tl_class'=>'w50'),
		),
		'address' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_tax_rate']['address'],
			'inputType'               => 'checkbox',
			'options'				  => array('billing', 'shipping'),
			'reference'				  => &$GLOBALS['TL_LANG']['tl_iso_tax_rate'],
			'eval'                    => array('mandatory'=>true, 'multiple'=>true)
		),
		'country' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_tax_rate']['country'],
			'inputType'               => 'select',
			'options'                 => $this->getCountries(),
			'eval'                    => array('includeBlankOption'=>true, 'tl_class'=>'w50')
		),
		'subdivision' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_tax_rate']['subdivision'],
			'exclude'                 => true,
			'sorting'                 => true,
			'inputType'               => 'conditionalselect',
			'options_callback'		  => array('IsotopeBackend', 'getSubdivisions'),
			'eval'                    => array('conditionField'=>'country', 'includeBlankOption'=>true, 'tl_class'=>'w50'),
		),
		'postalCodes' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_tax_rate']['postalCodes'],
			'exclude'                 => true,
			'inputType'               => 'textarea',
			'eval'                    => array('style'=>'height:40px', 'tl_class'=>'clr'),
		),
		'config' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_tax_rate']['config'],
			'inputType'               => 'select',
			'foreignKey'			  => 'tl_iso_config.name',
			'eval'                    => array('includeBlankOption'=>true, 'submitOnChange'=>true),
		),
		'rate' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_tax_rate']['rate'],
			'inputType'               => 'inputUnit',
			'options'				  => array('%'=>'%'),
			'eval'                    => array('mandatory'=>true, 'maxlength'=>255, 'rgxp'=>'price'),
		),
		'amount' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_tax_rate']['amount'],
			'inputType'               => 'text',
			'eval'                    => array('multiple'=>true, 'size'=>2, 'maxlength'=>10, 'rgxp'=>'digit', 'tl_class'=>'w50'),
		),
		'compound' => array
		(
			'label'						=> &$GLOBALS['TL_LANG']['tl_iso_tax_rate']['compound'],
			'inputType'					=> 'checkbox',
		),
		'stop' => array
		(
			'label'						=> &$GLOBALS['TL_LANG']['tl_iso_tax_rate']['stop'],
			'inputType'					=> 'checkbox',
		),
	)
);


class tl_iso_tax_rate extends Backend
{

	/**
	 * Check permissions to edit table tl_iso_tax_rate.
	 */
	public function checkPermission()
	{
		// Do not run the permission check on other Isotope modules
		if ($this->Input->get('mod') != 'tax_rate')
		{
			return;
		}
		
		$this->import('BackendUser', 'User');
		
		if ($this->User->isAdmin)
		{
			return;
		}

		// Set root IDs
		if (!is_array($this->User->iso_tax_rates) || count($this->User->iso_tax_rates) < 1)
		{
			$root = array(0);
		}
		else
		{
			$root = $this->User->iso_tax_rates;
		}

		$GLOBALS['TL_DCA']['tl_iso_tax_rate']['list']['sorting']['root'] = $root;

		// Check permissions to add tax rates
		if (!$this->User->hasAccess('create', 'iso_tax_ratep'))
		{
			$GLOBALS['TL_DCA']['tl_iso_tax_rate']['config']['closed'] = true;
			unset($GLOBALS['TL_DCA']['tl_iso_tax_rate']['list']['global_operations']['new']);
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

					if (is_array($arrNew['tl_iso_tax_rate']) && in_array($this->Input->get('id'), $arrNew['tl_iso_tax_rate']))
					{
						// Add permissions on user level
						if ($this->User->inherit == 'custom' || !$this->User->groups[0])
						{
							$objUser = $this->Database->prepare("SELECT iso_tax_rates, iso_tax_ratep FROM tl_user WHERE id=?")
													   ->limit(1)
													   ->execute($this->User->id);

							$arrPermissions = deserialize($objUser->iso_tax_ratep);

							if (is_array($arrPermissions) && in_array('create', $arrPermissions))
							{
								$arrAccess = deserialize($objUser->iso_tax_rates);
								$arrAccess[] = $this->Input->get('id');

								$this->Database->prepare("UPDATE tl_user SET iso_tax_rates=? WHERE id=?")
											   ->execute(serialize($arrAccess), $this->User->id);
							}
						}

						// Add permissions on group level
						elseif ($this->User->groups[0] > 0)
						{
							$objGroup = $this->Database->prepare("SELECT iso_tax_rates, iso_tax_ratep FROM tl_user_group WHERE id=?")
													   ->limit(1)
													   ->execute($this->User->groups[0]);

							$arrPermissions = deserialize($objGroup->iso_tax_ratep);

							if (is_array($arrPermissions) && in_array('create', $arrPermissions))
							{
								$arrAccess = deserialize($objGroup->iso_tax_rates);
								$arrAccess[] = $this->Input->get('id');

								$this->Database->prepare("UPDATE tl_user_group SET iso_tax_rates=? WHERE id=?")
											   ->execute(serialize($arrAccess), $this->User->groups[0]);
							}
						}

						// Add new element to the user object
						$root[] = $this->Input->get('id');
						$this->User->iso_tax_rates = $root;
					}
				}
				// No break;

			case 'copy':
			case 'delete':
			case 'show':
				if (!in_array($this->Input->get('id'), $root) || ($this->Input->get('act') == 'delete' && !$this->User->hasAccess('delete', 'iso_tax_ratep')))
				{
					$this->log('Not enough permissions to '.$this->Input->get('act').' tax rate ID "'.$this->Input->get('id').'"', __METHOD__, TL_ERROR);
					$this->redirect('contao/main.php?act=error');
				}
				break;

			case 'editAll':
			case 'deleteAll':
			case 'overrideAll':
				$session = $this->Session->getData();
				if ($this->Input->get('act') == 'deleteAll' && !$this->User->hasAccess('delete', 'iso_tax_ratep'))
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
					$this->log('Not enough permissions to '.$this->Input->get('act').' tax rates', __METHOD__, TL_ERROR);
					$this->redirect('contao/main.php?act=error');
				}
				break;
		}
	}
	
	
	/**
	 * List all records with formatted currency
	 * @param array
	 * @return string
	 */
	public function listRow($row)
	{
		$arrRate = deserialize($row['rate']);

		if ($row['config'] && !$arrRate['unit'])
		{
			$this->import('Isotope');
			$this->Isotope->overrideConfig($row['config']);

			$strRate = $this->Isotope->formatPriceWithCurrency($arrRate['value'], false);
		}
		else
		{
			$strRate = $arrRate['value'] . '%';
		}

		return sprintf('%s <span style="color:#b3b3b3; padding-left:3px;">[%s]</span>', $row['name'], $strRate);
	}


	/**
	 * Set the currency rate from selected store config
	 * @param DataContainer
	 */
	public function addCurrencyRate($dc)
	{
		$objConfig = $this->Database->prepare("SELECT tl_iso_config.* FROM tl_iso_tax_rate LEFT OUTER JOIN tl_iso_config ON tl_iso_config.id=tl_iso_tax_rate.config WHERE tl_iso_tax_rate.id=?")->execute($dc->id);

		if ($objConfig->currency)
		{
			$GLOBALS['TL_DCA']['tl_iso_tax_rate']['fields']['rate']['options'][''] = $objConfig->currency;
		}
	}


	/**
	 * Return the copy tax rate button
	 * @param array
	 * @param string
	 * @param string
	 * @param string
	 * @param string
	 * @param string
	 * @return string
	 */
	public function copyTaxRate($row, $href, $label, $title, $icon, $attributes)
	{
		return ($this->User->isAdmin || $this->User->hasAccess('create', 'iso_tax_ratep')) ? '<a href="'.$this->addToUrl($href.'&amp;id='.$row['id']).'" title="'.specialchars($title).'"'.$attributes.'>'.$this->generateImage($icon, $label).'</a> ' : $this->generateImage(preg_replace('/\.gif$/i', '_.gif', $icon)).' ';
	}


	/**
	 * Return the delete tax rate button
	 * @param array
	 * @param string
	 * @param string
	 * @param string
	 * @param string
	 * @param string
	 * @return string
	 */
	public function deleteTaxRate($row, $href, $label, $title, $icon, $attributes)
	{
		return ($this->User->isAdmin || $this->User->hasAccess('delete', 'iso_tax_ratep')) ? '<a href="'.$this->addToUrl($href.'&amp;id='.$row['id']).'" title="'.specialchars($title).'"'.$attributes.'>'.$this->generateImage($icon, $label).'</a> ' : $this->generateImage(preg_replace('/\.gif$/i', '_.gif', $icon)).' ';
	}
}

