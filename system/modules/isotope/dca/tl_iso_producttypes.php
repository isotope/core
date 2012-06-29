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
 * Table tl_iso_producttypes
 */
$GLOBALS['TL_DCA']['tl_iso_producttypes'] = array
(
	// Config
	'config' => array
	(
		'dataContainer'				=> 'Table',
		'enableVersioning'			=> true,
		'closed'					=> true,
		'onload_callback' => array
		(
			array('IsotopeBackend', 'initializeSetupModule'),
			array('tl_iso_producttypes', 'checkPermission'),
		)
	),

	// List
	'list' => array
	(
		'sorting' => array
		(
			'mode'					=> 1,
			'fields'				=> array('name'),
			'flag'					=> 1,
			'panelLayout'			=> 'filter;search,limit',
		),
		'label' => array
		(
			'fields'				=> array('name', 'fallback'),
			'format'				=> '%s <span style="color:#b3b3b3; padding-left:3px;">[%s]</span>',
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
				'label'					=> &$GLOBALS['TL_LANG']['tl_iso_producttypes']['new'],
				'href'					=> 'act=create',
				'class'					=> 'header_new',
				'attributes'			=> 'onclick="Backend.getScrollOffset();"',
			),
			'all' => array
			(
				'label'				=> &$GLOBALS['TL_LANG']['MSC']['all'],
				'href'				=> 'act=select',
				'class'				=> 'header_edit_all',
				'attributes'		=> 'onclick="Backend.getScrollOffset();"'
			),
		),
		'operations' => array
		(
			'edit' => array
			(
				'label'				=> &$GLOBALS['TL_LANG']['tl_iso_producttypes']['edit'],
				'href'				=> 'act=edit',
				'icon'				=> 'edit.gif',
			),
			'copy' => array
			(
				'label'				=> &$GLOBALS['TL_LANG']['tl_iso_producttypes']['copy'],
				'href'				=> 'act=copy',
				'icon'				=> 'copy.gif',
				'button_callback'     => array('tl_iso_producttypes', 'copyProductType')
			),
			'delete' => array
			(
				'label'				=> &$GLOBALS['TL_LANG']['tl_iso_producttypes']['delete'],
				'href'				=> 'act=delete',
				'icon'				=> 'delete.gif',
				'attributes'		=> 'onclick="if (!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\')) return false; Backend.getScrollOffset();"',
				'button_callback'     => array('tl_iso_producttypes', 'deleteProductType')
			),
			'show' => array
			(
				'label'				=> &$GLOBALS['TL_LANG']['tl_iso_producttypes']['show'],
				'href'				=> 'act=show',
				'icon'				=> 'show.gif'
			)
		)
	),

	// Palettes
	'palettes' => array
	(
		'__selector__'				=> array('class', 'prices', 'variants'),
		'default'					=> '{name_legend},name,class',
		'regular'					=> '{name_legend},name,class,fallback;{description_legend:hide},description;{prices_legend:hide},prices;{template_legend},list_template,reader_template;{attributes_legend},attributes;{variants_legend:hide},variants;{download_legend:hide},downloads',
	),

	// Subpalettes
	'subpalettes' => array
	(
		'prices'					=> 'show_price_tiers',
		'variants'					=> 'variant_attributes,force_variant_options',
	),

	// Fields
	'fields' => array
	(
		'name' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_iso_producttypes']['name'],
			'exclude'				=> true,
			'inputType'				=> 'text',
			'eval'					=> array('mandatory'=>true, 'maxlength'=>255, 'tl_class'=>'w50')
		),
		'class' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_iso_producttypes']['class'],
			'exclude'				=> true,
			'inputType'				=> 'select',
			'default'				=> 'regular',
			'options'				=> array_keys($GLOBALS['ISO_PRODUCT']),
			'reference'				=> &$GLOBALS['ISO_LANG']['PRODUCT'],
			'eval'					=> array('mandatory'=>true, 'submitOnChange'=>true, 'helpwizard'=>true, 'tl_class'=>'w50'),
		),
		'fallback' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_iso_producttypes']['fallback'],
			'exclude'				=> true,
			'inputType'				=> 'checkbox',
			'eval'					=> array('fallback'=>true, 'tl_class'=>'w50'),
		),
		'description' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_iso_producttypes']['description'],
			'exclude'				=> true,
			'inputType'				=> 'textarea',
			'eval'					=> array('style'=>'height:80px', 'tl_class'=>'clr'),
		),
		'prices' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_iso_producttypes']['prices'],
			'exclude'				=> true,
			'inputType'				=> 'checkbox',
			'eval'					=> array('submitOnChange'=>true, 'tl_class'=>'clr'),
		),
		'show_price_tiers' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_iso_producttypes']['show_price_tiers'],
			'exclude'				=> true,
			'inputType'				=> 'checkbox',
			'eval'					=> array('tl_class'=>'w50'),
		),
		'list_template' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_iso_producttypes']['list_template'],
			'exclude'                 => true,
			'inputType'				=> 'select',
			'default'				=> 'iso_list_default',
			'options_callback'		=> array('tl_iso_producttypes', 'getListTemplates'),
			'eval'					=> array('mandatory'=>true, 'tl_class'=>'w50', 'chosen'=>true)
		),
		'reader_template' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_iso_producttypes']['reader_template'],
			'exclude'                 => true,
			'inputType'				=> 'select',
			'default'				=> 'iso_reader_default',
			'options_callback'		=> array('tl_iso_producttypes', 'getReaderTemplates'),
			'eval'					=> array('mandatory'=>true, 'tl_class'=>'w50', 'chosen'=>true)
		),
        'attributes' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_iso_producttypes']['attributes'],
			'exclude'				=> true,
			'inputType'				=> 'attributeWizard',
			'default'				=> array
			(
				'type'				=> array('enabled'=>1, 'position'=>1, 'tl_class_select'=>'clr'),
				'pages'				=> array('enabled'=>1, 'position'=>2, 'tl_class_select'=>'clr'),
				'alias'				=> array('enabled'=>1, 'position'=>3, 'tl_class_select'=>'w50'),
				'sku'				=> array('enabled'=>1, 'position'=>4, 'tl_class_select'=>'w50'),
				'name'				=> array('enabled'=>1, 'position'=>5, 'tl_class_select'=>'clr long'),
				'teaser'			=> array('enabled'=>1, 'position'=>6, 'tl_class_select'=>'clr'),
				'description'		=> array('enabled'=>1, 'position'=>7, 'tl_class_select'=>'clr'),
				'price'				=> array('enabled'=>1, 'position'=>8, 'tl_class_select'=>'w50'),
				'tax_class'			=> array('enabled'=>1, 'position'=>9, 'tl_class_select'=>'w50'),
				'images'			=> array('enabled'=>1, 'position'=>10, 'tl_class_select'=>'clr'),
				'published'			=> array('enabled'=>1, 'position'=>11, 'tl_class_select'=>'clr'),
			),
			'eval'					=> array('helpwizard'=>true, 'tl_class'=>'clr', 'tl_classes'=>array('clr', 'clr long', 'long', 'w50', 'w50 m12')),
		),
		'variants' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_iso_producttypes']['variants'],
			'exclude'				=> true,
			'inputType'				=> 'checkbox',
			'eval'					=> array('tl_class'=>'clr', 'submitOnChange'=>true),
		),
        'variant_attributes' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_iso_producttypes']['variant_attributes'],
			'exclude'				=> true,
			'inputType'				=> 'attributeWizard',
			'eval'					=> array('helpwizard'=>true, 'variants'=>true, 'tl_class'=>'clr', 'tl_classes'=>array('clr', 'clr long', 'long', 'w50', 'w50 m12')),
		),
        'force_variant_options' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_iso_producttypes']['force_variant_options'],
			'exclude'				=> true,
			'inputType'				=> 'checkbox',
			'eval'					=> array('tl_class'=>'clr'),
		),
		'downloads' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_iso_producttypes']['downloads'],
			'exclude'				=> true,
			'inputType'				=> 'checkbox',
			'eval'					=> array(),
		),
	)
);


/**
 * Class tl_iso_producttypes
 * Provide miscellaneous methods that are used by the data configuration array.
 */
class tl_iso_producttypes extends Backend
{

	/**
	 * Check permissions to edit table tl_iso_producttypes
	 * @return void
	 */
	public function checkPermission()
	{
		// Do not run the permission check on other Isotope modules
		if ($this->Input->get('mod') != 'producttypes')
		{
			return;
		}
		
		$this->import('BackendUser', 'User');
		
		if ($this->User->isAdmin)
		{
			return;
		}

		// Set root IDs
		if (!is_array($this->User->iso_product_types) || count($this->User->iso_product_types) < 1)
		{
			$root = array(0);
		}
		else
		{
			$root = $this->User->iso_product_types;
		}

		$GLOBALS['TL_DCA']['tl_iso_producttypes']['list']['sorting']['root'] = $root;

		// Check permissions to add product types
		if (!$this->User->hasAccess('create', 'iso_product_typep'))
		{
			$GLOBALS['TL_DCA']['tl_iso_producttypes']['config']['closed'] = true;
			unset($GLOBALS['TL_DCA']['tl_iso_producttypes']['list']['global_operations']['new']);
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

					if (is_array($arrNew['tl_iso_producttypes']) && in_array($this->Input->get('id'), $arrNew['tl_iso_producttypes']))
					{
						// Add permissions on user level
						if ($this->User->inherit == 'custom' || !$this->User->groups[0])
						{
							$objUser = $this->Database->prepare("SELECT iso_product_types, iso_product_typep FROM tl_user WHERE id=?")
													   ->limit(1)
													   ->execute($this->User->id);

							$arrPermissions = deserialize($objUser->tl_iso_producttypep);

							if (is_array($arrPermissions) && in_array('create', $arrPermissions))
							{
								$arrAccess = deserialize($objUser->iso_product_types);
								$arrAccess[] = $this->Input->get('id');

								$this->Database->prepare("UPDATE tl_user SET iso_product_types=? WHERE id=?")
											   ->execute(serialize($arrAccess), $this->User->id);
							}
						}

						// Add permissions on group level
						elseif ($this->User->groups[0] > 0)
						{
							$objGroup = $this->Database->prepare("SELECT iso_product_types, iso_product_typep FROM tl_user_group WHERE id=?")
													   ->limit(1)
													   ->execute($this->User->groups[0]);

							$arrPermissions = deserialize($objGroup->iso_product_typep);

							if (is_array($arrPermissions) && in_array('create', $arrPermissions))
							{
								$arrAccess = deserialize($objGroup->iso_product_types);
								$arrAccess[] = $this->Input->get('id');

								$this->Database->prepare("UPDATE tl_user_group SET iso_product_types=? WHERE id=?")
											   ->execute(serialize($arrAccess), $this->User->groups[0]);
							}
						}

						// Add new element to the user object
						$root[] = $this->Input->get('id');
						$this->User->iso_product_types = $root;
					}
				}
				// No break;

			case 'copy':
			case 'delete':
			case 'show':
				if (!in_array($this->Input->get('id'), $root) || ($this->Input->get('act') == 'delete' && !$this->User->hasAccess('delete', 'iso_product_typep')))
				{
					$this->log('Not enough permissions to '.$this->Input->get('act').' product type ID "'.$this->Input->get('id').'"', __METHOD__, TL_ERROR);
					$this->redirect('contao/main.php?act=error');
				}
				break;

			case 'editAll':
			case 'deleteAll':
			case 'overrideAll':
				$session = $this->Session->getData();
				if ($this->Input->get('act') == 'deleteAll' && !$this->User->hasAccess('delete', 'iso_product_typep'))
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
					$this->log('Not enough permissions to '.$this->Input->get('act').' product types', __METHOD__, TL_ERROR);
					$this->redirect('contao/main.php?act=error');
				}
				break;
		}
	}


	/**
	 * Return list templates as array
	 * @param DataContainer
	 * @return array
	 */
	public function getListTemplates(DataContainer $dc)
	{
		return IsotopeBackend::getTemplates('iso_list_');
	}


	/**
	 * Return reader templates as array
	 * @param DataContainer
	 * @return array
	 */
	public function getReaderTemplates(DataContainer $dc)
	{
		return IsotopeBackend::getTemplates('iso_reader_');
	}


	/**
	 * Return the copy product type button
	 * @param array
	 * @param string
	 * @param string
	 * @param string
	 * @param string
	 * @param string
	 * @return string
	 */
	public function copyProductType($row, $href, $label, $title, $icon, $attributes)
	{
		return ($this->User->isAdmin || $this->User->hasAccess('create', 'iso_product_typep')) ? '<a href="'.$this->addToUrl($href.'&amp;id='.$row['id']).'" title="'.specialchars($title).'"'.$attributes.'>'.$this->generateImage($icon, $label).'</a> ' : $this->generateImage(preg_replace('/\.gif$/i', '_.gif', $icon)).' ';
	}


	/**
	 * Return the delete product type button
	 * @param array
	 * @param string
	 * @param string
	 * @param string
	 * @param string
	 * @param string
	 * @return string
	 */
	public function deleteProductType($row, $href, $label, $title, $icon, $attributes)
	{
		return ($this->User->isAdmin || $this->User->hasAccess('delete', 'iso_product_typep')) ? '<a href="'.$this->addToUrl($href.'&amp;id='.$row['id']).'" title="'.specialchars($title).'"'.$attributes.'>'.$this->generateImage($icon, $label).'</a> ' : $this->generateImage(preg_replace('/\.gif$/i', '_.gif', $icon)).' ';
	}
}

