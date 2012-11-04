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

namespace Isotope;


/**
 * Table tl_iso_related_products
 */
$GLOBALS['TL_DCA']['tl_iso_related_products'] = array
(

	// Config
	'config' => array
	(
		'dataContainer'					=> 'Table',
		'enableVersioning'				=> true,
		'ptable'						=> 'tl_iso_products',
		'onload_callback' => array
		(
			array('tl_iso_related_products', 'initDCA')
		),
	),

	// List
	'list' => array
	(
		'sorting' => array
		(
			'mode'						=> 4,
			'fields'					=> array('category'),
			'flag'						=> 1,
			'panelLayout'				=> 'filter,limit',
			'headerFields'				=> array('type', 'name', 'alias', 'sku'),
			'child_record_callback'		=> array('tl_iso_related_products', 'listRows')
		),
		'global_operations' => array
		(
			'all' => array
			(
				'label'					=> &$GLOBALS['TL_LANG']['MSC']['all'],
				'href'					=> 'act=select',
				'class'					=> 'header_edit_all',
				'attributes'			=> 'onclick="Backend.getScrollOffset();"',
			)
		),
		'operations' => array
		(
			'edit' => array
			(
				'label'					=> &$GLOBALS['TL_LANG']['tl_iso_related_products']['edit'],
				'href'					=> 'act=edit',
				'icon'					=> 'edit.gif'
			),
			'copy' => array
			(
				'label'					=> &$GLOBALS['TL_LANG']['tl_iso_related_products']['copy'],
				'href'					=> 'act=copy',
				'icon'					=> 'copy.gif'
			),
			'delete' => array
			(
				'label'					=> &$GLOBALS['TL_LANG']['tl_iso_related_products']['delete'],
				'href'					=> 'act=delete',
				'icon'					=> 'delete.gif',
				'attributes'			=> 'onclick="if (!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\')) return false; Backend.getScrollOffset();"'
			),
			'show' => array
			(
				'label'					=> &$GLOBALS['TL_LANG']['tl_iso_related_products']['show'],
				'href'					=> 'act=show',
				'icon'					=> 'show.gif'
			),
		)
	),

	// Palettes
	'palettes' => array
	(
		'default'						=> '{category_legend},category;{products_legend},products',
	),

	// Fields
	'fields' => array
	(
		'category' => array
		(
			'label'						=> &$GLOBALS['TL_LANG']['tl_iso_related_products']['category'],
			'exclude'					=> true,
			'filter'					=> true,
			'inputType'					=> 'select',
			'foreignKey'				=> 'tl_iso_related_categories.name',
			'eval'						=> array('mandatory'=>true, 'includeBlankOption'=>true, 'chosen'=>true)
		),
		'products' => array
		(
			'label'						=> &$GLOBALS['TL_LANG']['tl_iso_related_products']['products'],
			'exclude'					=> true,
			'inputType'					=> 'productTree',
			'eval'						=> array('mandatory'=>true, 'fieldType'=>'checkbox', 'variants'=>true, 'tl_class'=>'clr'),
		),
	)
);


/**
 * Class tl_iso_related_products
 * Provide miscellaneous methods that are used by the data configuration array.
 */
class tl_iso_related_products extends Backend
{

	/**
	 * Add an image to each record
	 * @param array
	 * @param string
	 * @return string
	 */
	public function listRows($row)
	{
		$strCategory = $this->Database->prepare("SELECT * FROM tl_iso_related_categories WHERE id=?")->execute($row['category'])->name;

		$strBuffer = '
<div class="cte_type" style="color:#666966"><strong>' . $GLOBALS['TL_LANG']['tl_iso_related_products']['category'][0] . ':</strong> ' . $strCategory . '</div>';

		$arrProducts = deserialize($row['products']);

		if (is_array($arrProducts) && !empty($arrProducts))
		{
			$strBuffer .= '<div class="limit_height' . (!$GLOBALS['TL_CONFIG']['doNotCollapse'] ? ' h0' : '') . ' block"><ul>';
			$objProducts = $this->Database->execute("SELECT * FROM tl_iso_products WHERE id IN (" . implode(',', $arrProducts) . ") ORDER BY name");

			while ($objProducts->next())
			{
				$strBuffer .= '<li>' . $objProducts->name . '</li>';
			}

			$strBuffer .= '</ul></div>' . "\n";
		}

		return $strBuffer;
	}


	/**
	 * Initialize the data container
	 * @param object
	 * @return string
	 */
	public function initDCA($dc)
	{
		$arrCategories = array();
		$objCategories = $this->Database->prepare("SELECT * FROM tl_iso_related_categories WHERE id NOT IN (SELECT category FROM tl_iso_related_products WHERE pid=" . (strlen($this->Input->get('act')) ? "(SELECT pid FROM tl_iso_related_products WHERE id=?) AND id!=?" : '?') . ")")
										->execute($dc->id, $dc->id);

		while ($objCategories->next())
		{
			$arrCategories[$objCategories->id] = $objCategories->name;
		}

		if (empty($arrCategories))
		{
			$GLOBALS['TL_DCA']['tl_iso_related_products']['config']['closed'] = true;
		}

		if ($this->Input->get('act') == 'edit')
		{
			unset($GLOBALS['TL_DCA']['tl_iso_related_products']['fields']['category']['foreignKey']);
			$GLOBALS['TL_DCA']['tl_iso_related_products']['fields']['category']['options'] = $arrCategories;
			$GLOBALS['TL_DCA']['tl_iso_related_products']['fields']['products']['eval']['allowedIds'] = $this->Database->prepare("SELECT id FROM tl_iso_products WHERE pid=0 AND id!=(SELECT pid FROM tl_iso_related_products WHERE id=?)")->execute($dc->id)->fetchEach('id');
		}
	}
}

