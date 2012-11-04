<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2012 Isotope eCommerce Workgroup
 *
 * @package    Isotope
 * @link       http://www.isotopeecommerce.com
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 *
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
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
				'button_callback'     => array('tl_iso_tax_rate', 'copyTaxRate'),
			),
			'delete' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_iso_tax_rate']['delete'],
				'href'                => 'act=delete',
				'icon'                => 'delete.gif',
				'attributes'          => 'onclick="if (!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\')) return false; Backend.getScrollOffset();"',
				'button_callback'     => array('tl_iso_tax_rate', 'deleteTaxRate'),
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
		'__selector__'                => array('protected'),
		'default'                     => '{name_legend},name,label;{rate_legend},rate;{location_legend},address,country,subdivision,postalCodes;{condition_legend},amount;{config_legend:hide},config,stop,guests,protected',
	),

	// Subpalettes
	'subpalettes' => array
	(
		'protected'                   => 'groups',
	),

	// Fields
	'fields' => array
	(
		'name' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_tax_rate']['name'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => array('mandatory'=>true, 'maxlength'=>255, 'tl_class'=>'w50'),
		),
		'label' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_tax_rate']['label'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => array('maxlength'=>255, 'tl_class'=>'w50'),
		),
		'address' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_tax_rate']['address'],
			'exclude'                 => true,
			'inputType'               => 'checkbox',
			'options'                 => array('billing', 'shipping'),
			'reference'               => &$GLOBALS['TL_LANG']['tl_iso_tax_rate'],
			'eval'                    => array('mandatory'=>true, 'multiple'=>true)
		),
		'country' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_tax_rate']['country'],
			'exclude'                 => true,
			'inputType'               => 'select',
			'options'                 => $this->getCountries(),
			'eval'                    => array('includeBlankOption'=>true, 'tl_class'=>'w50', 'chosen'=>true)
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
		'rate' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_tax_rate']['rate'],
			'exclude'                 => true,
			'inputType'               => 'inputUnit',
			'options'                 => array('%'=>'%'),
			'eval'                    => array('mandatory'=>true, 'maxlength'=>255, 'rgxp'=>'price'),
		),
		'amount' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_tax_rate']['amount'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => array('multiple'=>true, 'size'=>2, 'maxlength'=>10, 'rgxp'=>'digit', 'tl_class'=>'w50'),
		),
		'config' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_tax_rate']['config'],
			'exclude'                 => true,
			'inputType'               => 'select',
			'foreignKey'              => 'tl_iso_config.name',
			'eval'                    => array('includeBlankOption'=>true, 'submitOnChange'=>true, 'tl_class'=>'w50'),
		),
		'stop' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_tax_rate']['stop'],
			'exclude'                 => true,
			'inputType'               => 'checkbox',
			'eval'                    => array('tl_class'=>'w50 m12'),
		),
		'guests' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_tax_rate']['guests'],
			'exclude'                 => true,
			'inputType'               => 'checkbox',
			'eval'                    => array('tl_class'=>'clr'),
		),
		'protected' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_tax_rate']['protected'],
			'exclude'                 => true,
			'inputType'               => 'checkbox',
			'eval'                    => array('submitOnChange'=>true, 'tl_class'=>'clr'),
		),
		'groups' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_tax_rate']['groups'],
			'exclude'                 => true,
			'inputType'               => 'checkbox',
			'foreignKey'              => 'tl_member_group.name',
			'eval'                    => array('multiple'=>true)
		),
	)
);


/**
 * Class tl_iso_tax_rate
 * Provide miscellaneous methods that are used by the data configuration array.
 */
class tl_iso_tax_rate extends \Backend
{

	/**
	 * Check permissions to edit table tl_iso_tax_rate
	 * @return void
	 */
	public function checkPermission()
	{
		// Do not run the permission check on other Isotope modules
		if (\Input::get('mod') != 'tax_rate')
		{
			return;
		}

		$this->import('BackendUser', 'User');

		if ($this->User->isAdmin)
		{
			return;
		}

		// Set root IDs
		if (!is_array($this->User->iso_tax_rates) || count($this->User->iso_tax_rates) < 1) // Can't use empty() because its an object property (using __get)
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
		switch (\Input::get('act'))
		{
			case 'create':
			case 'select':
				// Allow
				break;

			case 'edit':
				// Dynamically add the record to the user profile
				if (!in_array(\Input::get('id'), $root))
				{
					$arrNew = $this->Session->get('new_records');

					if (is_array($arrNew['tl_iso_tax_rate']) && in_array(\Input::get('id'), $arrNew['tl_iso_tax_rate']))
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
								$arrAccess[] = \Input::get('id');

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
								$arrAccess[] = \Input::get('id');

								$this->Database->prepare("UPDATE tl_user_group SET iso_tax_rates=? WHERE id=?")
											   ->execute(serialize($arrAccess), $this->User->groups[0]);
							}
						}

						// Add new element to the user object
						$root[] = \Input::get('id');
						$this->User->iso_tax_rates = $root;
					}
				}
				// No break;

			case 'copy':
			case 'delete':
			case 'show':
				if (!in_array(\Input::get('id'), $root) || (\Input::get('act') == 'delete' && !$this->User->hasAccess('delete', 'iso_tax_ratep')))
				{
					$this->log('Not enough permissions to '.\Input::get('act').' tax rate ID "'.\Input::get('id').'"', __METHOD__, TL_ERROR);
					$this->redirect('contao/main.php?act=error');
				}
				break;

			case 'editAll':
			case 'deleteAll':
			case 'overrideAll':
				$session = $this->Session->getData();
				if (\Input::get('act') == 'deleteAll' && !$this->User->hasAccess('delete', 'iso_tax_ratep'))
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
				if (strlen(\Input::get('act')))
				{
					$this->log('Not enough permissions to '.\Input::get('act').' tax rates', __METHOD__, TL_ERROR);
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
	 * @param object
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