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
 * @author     Christian de la Haye <service@delahaye.de>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */


/**
 * Table tl_iso_prices
 */
$GLOBALS['TL_DCA']['tl_iso_prices'] = array
(

	// Config
	'config' => array
	(
		'dataContainer'					=> 'Table',
		'enableVersioning'				=> true,
		'ptable'						=> 'tl_iso_products',
		'ctable'						=> array('tl_iso_price_tiers'),
	),

	// List
	'list' => array
	(
		'sorting' => array
		(
			'mode'						=> 4,
			'fields'					=> array('id'),
			'flag'						=> 1,
			'panelLayout'				=> 'filter;search,limit',
			'headerFields'				=> array('id', 'name', 'alias', 'sku'),
			'disableGrouping'			=> true,
			'child_record_callback'		=> array('tl_iso_prices', 'listRows')
		),
		'global_operations' => array
		(
			'all' => array
			(
				'label'					=> &$GLOBALS['TL_LANG']['MSC']['all'],
				'href'					=> 'act=select',
				'class'					=> 'header_edit_all',
				'attributes'			=> 'onclick="Backend.getScrollOffset();"'
			),
		),
		'operations' => array
		(
			'edit' => array
			(
				'label'					=> &$GLOBALS['TL_LANG']['tl_iso_prices']['edit'],
				'href'					=> 'act=edit',
				'icon'					=> 'edit.gif'
			),
			'copy' => array
			(
				'label'					=> &$GLOBALS['TL_LANG']['tl_iso_prices']['copy'],
				'href'					=> 'act=copy',
				'icon'					=> 'copy.gif'
			),
			'delete' => array
			(
				'label'					=> &$GLOBALS['TL_LANG']['tl_iso_prices']['delete'],
				'href'					=> 'act=delete',
				'icon'					=> 'delete.gif',
				'attributes'			=> 'onclick="if (!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\')) return false; Backend.getScrollOffset();"'
			),
			'show' => array
			(
				'label'					=> &$GLOBALS['TL_LANG']['tl_iso_prices']['show'],
				'href'					=> 'act=show',
				'icon'					=> 'show.gif'
			),
		)
	),

	// Palettes
	'palettes' => array
	(
		'default'						=> '{price_legend},price_tiers,tax_class;{limit_legend},config_id,member_group,start,stop',
		'dcawizard'						=> 'price_tiers,tax_class,config_id,member_group,start,stop',
	),

	// Fields
	'fields' => array
	(
		'price_tiers' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_iso_prices']['price_tiers'],
			'inputType'				=> 'multitextWizard',
			'eval'					=> array
			(
				'doNotSaveEmpty'	=> true,
				'tl_class'			=> 'clr',
				'columns'			=> array
				(
					'min' => array
					(
						'label'		=> &$GLOBALS['TL_LANG']['tl_iso_prices']['price_tier_columns']['min'],
						'mandatory'	=> true,
						'rgxp'		=> 'digit',
					),
					'price' => array
					(
						'label'		=> &$GLOBALS['TL_LANG']['tl_iso_prices']['price_tier_columns']['price'],
						'mandatory'	=> true,
						'rgxp'		=> 'price',
					),
				),
			),
			'load_callback' => array
			(
				array('tl_iso_prices', 'loadTiers'),
			),
			'save_callback' => array
			(
				array('tl_iso_prices', 'saveTiers'),
			),
		),
		'tax_class' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_iso_prices']['tax_class'],
			'inputType'				=> 'select',
			'default'				=> &$GLOBALS['TL_DCA']['tl_iso_products']['fields']['tax_class']['default'],
			'foreignKey'			=> 'tl_iso_tax_class.name',
			'eval'					=> array('includeBlankOption'=>true, 'tl_class'=>'clr', 'chosen'=>true),
		),
		'config_id' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_prices']['config_id'],
			'inputType'               => 'select',
			'foreignKey'			  => 'tl_iso_config.name',
			'eval'					  => array('includeBlankOption'=>true, 'tl_class'=>'w50'),
		),
		'member_group' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_iso_prices']['member_group'],
			'inputType'				=> 'select',
			'foreignKey'			=> 'tl_member_group.name',
			'eval'					=> array('includeBlankOption'=>true, 'tl_class'=>'w50', 'chosen'=>true)
		),
		'start' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_iso_prices']['start'],
			'inputType'				=> 'text',
			'eval'					=> array('rgxp'=>'date', 'datepicker'=>(method_exists($this,'getDatePickerString') ? $this->getDatePickerString() : true), 'tl_class'=>'w50 wizard'),
		),
		'stop' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_iso_prices']['stop'],
			'inputType'				=> 'text',
			'eval'					=> array('rgxp'=>'date', 'datepicker'=>(method_exists($this,'getDatePickerString') ? $this->getDatePickerString() : true), 'tl_class'=>'w50 wizard'),
		),
	)
);


/**
 * Class tl_iso_prices
 * Provide miscellaneous methods that are used by the data configuration array.
 */
class tl_iso_prices extends Backend
{

	/**
	 * List all price rows
	 * @param array
	 * @return string
	 */
	public function listRows($row)
	{
		if (!$row['id'])
		{
			return '';
		}

		$this->import('Isotope');

		$arrTiers = array();
		$objTiers = $this->Database->execute("SELECT * FROM tl_iso_price_tiers WHERE pid={$row['id']} ORDER BY min");

		while ($objTiers->next())
		{
			$arrTiers[] = "{$objTiers->min}={$objTiers->price}";
		}

		$arrInfo = array('<strong>'.$GLOBALS['TL_LANG']['tl_iso_prices']['price_tiers'][0].':</strong> ' . implode(', ', $arrTiers));

		foreach ($row as $name => $value)
		{
			switch ($name)
			{
				case 'id':
				case 'pid':
				case 'tstamp':
					break;

				default:
					if ($value != '' && $value > 0)
					{
						$arrInfo[] = '<strong>' . $this->Isotope->formatLabel('tl_iso_prices', $name) . '</strong>: ' . $this->Isotope->formatValue('tl_iso_prices', $name, $value);
					}
					break;
			}
		}

		return '<ul style="margin:0"><li>' . implode('</li><li>', $arrInfo) . '</li></ul>';
	}


	/**
	 * Get tiers and return them as array
	 * @param mixed
	 * @param object
	 * @return array
	 */
	public function loadTiers($varValue, $dc)
	{
		if (!$dc->id)
		{
			return array();
		}

		$arrTiers = array();
		$objTiers = $this->Database->execute("SELECT * FROM tl_iso_price_tiers WHERE pid={$dc->id} ORDER BY min");

		while ($objTiers->next())
		{
			$arrTiers[] = array($objTiers->min, $objTiers->price);
		}

		if (!count($arrTiers))
		{
			return array(array(1, ''));
		}

		return $arrTiers;
	}


	/**
	 * Save the price tiers
	 * @param mixed
	 * @param object
	 * @return string
	 */
	public function saveTiers($varValue, $dc)
	{
		$arrNew = deserialize($varValue);

		if (!is_array($arrNew) || !count($arrNew))
		{
			$this->Database->query("DELETE FROM tl_iso_price_tiers WHERE pid={$dc->id}");
		}
		else
		{
			$time = time();
			$arrInsert = array();
			$arrUpdate = array();
			$arrDelete = $this->Database->execute("SELECT min FROM tl_iso_price_tiers WHERE pid={$dc->id}")->fetchEach('min');

			foreach ($arrNew as $new)
			{
				$pos = array_search($new[0], $arrDelete);

				if ($pos === false)
				{
					$arrInsert[$new[0]] = $new[1];
				}
				else
				{
					$arrUpdate[$new[0]] = $new[1];
					unset($arrDelete[$pos]);
				}
			}

			if (count($arrDelete))
			{
				$this->Database->query("DELETE FROM tl_iso_price_tiers WHERE pid={$dc->id} AND min IN (" . implode(',', $arrDelete) . ")");
			}

			if (count($arrUpdate))
			{
				foreach ($arrUpdate as $min => $price)
				{
					$this->Database->prepare("UPDATE tl_iso_price_tiers SET tstamp=$time, price=? WHERE pid={$dc->id} AND min=?")->executeUncached($price, $min);
				}
			}

			if (count($arrInsert))
			{
				foreach ($arrInsert as $min => $price)
				{
					$this->Database->prepare("INSERT INTO tl_iso_price_tiers (pid,tstamp,min,price) VALUES ({$dc->id}, $time, ?, ?)")->executeUncached($min, $price);
				}
			}
		}

		return '';
	}
}