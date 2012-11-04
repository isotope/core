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
 * Table tl_iso_tax_class
 */
$GLOBALS['TL_DCA']['tl_iso_tax_class'] = array
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
			array('tl_iso_tax_class', 'checkPermission'),
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
			'panelLayout'             => 'filter;search,limit'
		),
		'label' => array
		(
			'fields'                  => array('name', 'fallback'),
			'format'                  => '%s <span style="color:#b3b3b3; padding-left:3px;">[%s]</span>'
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
				'label'					=> &$GLOBALS['TL_LANG']['tl_iso_tax_class']['new'],
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
				'label'               => &$GLOBALS['TL_LANG']['tl_iso_tax_class']['edit'],
				'href'                => 'act=edit',
				'icon'                => 'edit.gif'
			),
			'copy' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_iso_tax_class']['copy'],
				'href'                => 'act=copy',
				'icon'                => 'copy.gif',
				'button_callback'     => array('tl_iso_tax_class', 'copyTaxClass'),
			),
			'delete' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_iso_tax_class']['delete'],
				'href'                => 'act=delete',
				'icon'                => 'delete.gif',
				'attributes'          => 'onclick="if (!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\')) return false; Backend.getScrollOffset();"',
				'button_callback'     => array('tl_iso_tax_class', 'deleteTaxClass'),
			),
			'show' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_iso_tax_class']['show'],
				'href'                => 'act=show',
				'icon'                => 'show.gif'
			)
		)
	),

	// Palettes
	'palettes' => array
	(
		'default'                     => '{name_legend},name,fallback;{rate_legend},includes,label,rates,applyRoundingIncrement',
	),

	// Fields
	'fields' => array
	(
		'name' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_tax_class']['name'],
			'exclude'                 => true,
			'search'                  => true,
			'inputType'               => 'text',
			'eval'                    => array('maxlength'=>255, 'mandatory'=>true, 'tl_class'=>'w50'),
		),
		'fallback' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_iso_tax_class']['fallback'],
			'exclude'				=> true,
			'inputType'				=> 'checkbox',
			'eval'					=> array('fallback'=>true, 'tl_class'=>'w50 m12'),
		),
		'includes' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_tax_class']['includes'],
			'exclude'                 => true,
			'inputType'               => 'select',
			'options_callback'		  => array('tl_iso_tax_class', 'getTaxRates'),
			'eval'                    => array('includeBlankOption'=>true, 'tl_class'=>'w50'),
		),
		'label' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_tax_class']['label'],
			'exclude'                 => true,
			'search'                  => true,
			'inputType'               => 'text',
			'eval'                    => array('maxlength'=>255, 'tl_class'=>'w50'),
		),
		'rates' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_tax_class']['rates'],
			'exclude'                 => true,
			'inputType'               => 'checkboxWizard',
			'options_callback'		  => array('tl_iso_tax_class', 'getTaxRates'),
			'eval'                    => array('multiple'=>true, 'tl_class'=>'clr w50 w50h'),
		),
		'applyRoundingIncrement' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_iso_tax_class']['applyRoundingIncrement'],
			'exclude'				=> true,
			'inputType'				=> 'checkbox',
			'eval'					=> array('tl_class'=>'w50'),
		),
	)
);


/**
 * Class tl_iso_tax_class
 * Provide miscellaneous methods that are used by the data configuration array.
 */
class tl_iso_tax_class extends Backend
{

	/**
	 * Check permissions to edit table tl_iso_tax_class
	 * @return void
	 */
	public function checkPermission()
	{
		// Do not run the permission check on other Isotope modules
		if ($this->Input->get('mod') != 'tax_class')
		{
			return;
		}

		$this->import('BackendUser', 'User');

		if ($this->User->isAdmin)
		{
			return;
		}

		// Set root IDs
		if (!is_array($this->User->iso_tax_classes) || count($this->User->iso_tax_classes) < 1) // Can't use empty() because its an object property (using __get)
		{
			$root = array(0);
		}
		else
		{
			$root = $this->User->iso_tax_classes;
		}

		$GLOBALS['TL_DCA']['tl_iso_tax_class']['list']['sorting']['root'] = $root;

		// Check permissions to add tax classes
		if (!$this->User->hasAccess('create', 'iso_tax_classp'))
		{
			$GLOBALS['TL_DCA']['tl_iso_tax_class']['config']['closed'] = true;
			unset($GLOBALS['TL_DCA']['tl_iso_tax_class']['list']['global_operations']['new']);
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

					if (is_array($arrNew['tl_iso_tax_class']) && in_array($this->Input->get('id'), $arrNew['tl_iso_tax_class']))
					{
						// Add permissions on user level
						if ($this->User->inherit == 'custom' || !$this->User->groups[0])
						{
							$objUser = $this->Database->prepare("SELECT iso_tax_classes, iso_tax_classp FROM tl_user WHERE id=?")
													   ->limit(1)
													   ->execute($this->User->id);

							$arrPermissions = deserialize($objUser->iso_tax_classp);

							if (is_array($arrPermissions) && in_array('create', $arrPermissions))
							{
								$arrAccess = deserialize($objUser->iso_tax_classes);
								$arrAccess[] = $this->Input->get('id');

								$this->Database->prepare("UPDATE tl_user SET iso_tax_classes=? WHERE id=?")
											   ->execute(serialize($arrAccess), $this->User->id);
							}
						}

						// Add permissions on group level
						elseif ($this->User->groups[0] > 0)
						{
							$objGroup = $this->Database->prepare("SELECT iso_tax_classes, iso_tax_classp FROM tl_user_group WHERE id=?")
													   ->limit(1)
													   ->execute($this->User->groups[0]);

							$arrPermissions = deserialize($objGroup->iso_tax_classp);

							if (is_array($arrPermissions) && in_array('create', $arrPermissions))
							{
								$arrAccess = deserialize($objGroup->iso_tax_classes);
								$arrAccess[] = $this->Input->get('id');

								$this->Database->prepare("UPDATE tl_user_group SET iso_tax_classes=? WHERE id=?")
											   ->execute(serialize($arrAccess), $this->User->groups[0]);
							}
						}

						// Add new element to the user object
						$root[] = $this->Input->get('id');
						$this->User->iso_tax_classes = $root;
					}
				}
				// No break;

			case 'copy':
			case 'delete':
			case 'show':
				if (!in_array($this->Input->get('id'), $root) || ($this->Input->get('act') == 'delete' && !$this->User->hasAccess('delete', 'iso_tax_classp')))
				{
					$this->log('Not enough permissions to '.$this->Input->get('act').' tax class ID "'.$this->Input->get('id').'"', __METHOD__, TL_ERROR);
					$this->redirect('contao/main.php?act=error');
				}
				break;

			case 'editAll':
			case 'deleteAll':
			case 'overrideAll':
				$session = $this->Session->getData();
				if ($this->Input->get('act') == 'deleteAll' && !$this->User->hasAccess('delete', 'iso_tax_classp'))
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
					$this->log('Not enough permissions to '.$this->Input->get('act').' tax classes', __METHOD__, TL_ERROR);
					$this->redirect('contao/main.php?act=error');
				}
				break;
		}
	}


	/**
	 * Get all tax rates sorted by country and name
	 * @return array
	 */
	public function getTaxRates()
	{
		$arrCountries = $this->getCountries();
		$arrRates = array();
		$objRates = $this->Database->execute("SELECT * FROM tl_iso_tax_rate ORDER BY country, name");

		while ($objRates->next())
		{
			$arrRates[$objRates->id] = $arrCountries[$objRates->country] . ' - ' . $objRates->name;
		}

		return $arrRates;
	}


	/**
	 * Return the copy tax class button
	 * @param array
	 * @param string
	 * @param string
	 * @param string
	 * @param string
	 * @param string
	 * @return string
	 */
	public function copyTaxClass($row, $href, $label, $title, $icon, $attributes)
	{
		return ($this->User->isAdmin || $this->User->hasAccess('create', 'iso_tax_classp')) ? '<a href="'.$this->addToUrl($href.'&amp;id='.$row['id']).'" title="'.specialchars($title).'"'.$attributes.'>'.$this->generateImage($icon, $label).'</a> ' : $this->generateImage(preg_replace('/\.gif$/i', '_.gif', $icon)).' ';
	}


	/**
	 * Return the delete tax class button
	 * @param array
	 * @param string
	 * @param string
	 * @param string
	 * @param string
	 * @param string
	 * @return string
	 */
	public function deleteTaxClass($row, $href, $label, $title, $icon, $attributes)
	{
		return ($this->User->isAdmin || $this->User->hasAccess('delete', 'iso_tax_classp')) ? '<a href="'.$this->addToUrl($href.'&amp;id='.$row['id']).'" title="'.specialchars($title).'"'.$attributes.'>'.$this->generateImage($icon, $label).'</a> ' : $this->generateImage(preg_replace('/\.gif$/i', '_.gif', $icon)).' ';
	}
}

