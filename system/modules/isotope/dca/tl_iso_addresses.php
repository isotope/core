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
 

// Load country sub-divisions
$this->loadLanguageFile('subdivisions');


/**
 * Table tl_iso_addresses
 */
$GLOBALS['TL_DCA']['tl_iso_addresses'] = array
(

	// Config
	'config' => array
	(
		'ptable'					  => 'tl_member',
		'dataContainer'               => 'Table',
		'onsubmit_callback' => array
		(
			array('tl_iso_addresses', 'updateDefaultAddress'),
		)
	),

	// List
	'list' => array
	(
		'sorting' => array
		(
			'mode'                    => 4,
			'headerFields'			  => array('firstname','lastname', 'username'),
			'disableGrouping'		  => true,
			'flag'                    => 1,
			'panelLayout'             => 'filter;sort,search,limit',
			'child_record_callback'   => array('tl_iso_addresses','renderLabel')
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
				'label'               => &$GLOBALS['TL_LANG']['tl_iso_addresses']['edit'],
				'href'                => 'act=edit',
				'icon'                => 'edit.gif'
			),
			'copy' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_iso_addresses']['copy'],
				'href'                => 'act=copy',
				'icon'                => 'copy.gif'
			),
			'delete' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_iso_addresses']['delete'],
				'href'                => 'act=delete',
				'icon'                => 'delete.gif',
				'attributes'          => 'onclick="if (!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\')) return false; Backend.getScrollOffset();"'
			),
			'show' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_iso_addresses']['show'],
				'href'                => 'act=show',
				'icon'                => 'show.gif'
			)
		)
	),

	// Palettes
	'palettes' => array
	(
		'default'					  => '{personal_legend},firstname,lastname;{address_legend:hide},company,street_1,street_2,street_3,postal,city,subdivision,country;{contact_legend},email,phone;{default_legend},isDefaultBilling,isDefaultShipping',
	),

	// Fields
	'fields' => array
	(
		'firstname' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_addresses']['firstname'],
			'exclude'                 => true,
			'search'                  => true,
			'sorting'                 => true,
			'flag'                    => 1,
			'inputType'               => 'text',
			'eval'                    => array('mandatory'=>true, 'maxlength'=>255, 'feEditable'=>true, 'feGroup'=>'personal', 'tl_class'=>'w50'),
		),
		'lastname' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_addresses']['lastname'],
			'exclude'                 => true,
			'search'                  => true,
			'sorting'                 => true,
			'flag'                    => 1,
			'inputType'               => 'text',
			'eval'                    => array('mandatory'=>true, 'maxlength'=>255, 'feEditable'=>true, 'feGroup'=>'personal', 'tl_class'=>'w50'),
		),
		'company' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_addresses']['company'],
			'exclude'                 => true,
			'search'                  => true,
			'sorting'                 => true,
			'flag'                    => 1,
			'inputType'               => 'text',
			'eval'                    => array('maxlength'=>255, 'feEditable'=>true, 'feGroup'=>'address', 'tl_class'=>'w50'),
		),
		'street_1' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_addresses']['street_1'],
			'exclude'                 => true,
			'search'                  => true,
			'inputType'               => 'text',
			'eval'                    => array('mandatory'=>true, 'maxlength'=>255, 'feEditable'=>true, 'feGroup'=>'address', 'tl_class'=>'w50'),
		),
		'street_2' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_addresses']['street_2'],
			'exclude'                 => true,
			'search'                  => true,
			'inputType'               => 'text',
			'eval'                    => array('maxlength'=>255, 'feEditable'=>true, 'feGroup'=>'address', 'tl_class'=>'w50'),
		),
		'street_3' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_addresses']['street_3'],
			'exclude'                 => true,
			'search'                  => true,
			'inputType'               => 'text',
			'eval'                    => array('maxlength'=>255, 'feEditable'=>true, 'feGroup'=>'address', 'tl_class'=>'w50'),
		),
		'postal' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_addresses']['postal'],
			'exclude'                 => true,
			'search'                  => true,
			'inputType'               => 'text',
			'eval'                    => array('mandatory'=>true, 'maxlength'=>32, 'feEditable'=>true, 'feGroup'=>'address', 'tl_class'=>'w50'),
		),
		'city' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_addresses']['city'],
			'exclude'                 => true,
			'filter'                  => true,
			'search'                  => true,
			'sorting'                 => true,
			'inputType'               => 'text',
			'eval'                    => array('mandatory'=>true, 'maxlength'=>255, 'feEditable'=>true, 'feGroup'=>'address', 'tl_class'=>'w50'),
		),
		'subdivision' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_addresses']['subdivision'],
			'exclude'                 => true,
			'sorting'                 => true,
			'inputType'               => 'conditionalselect',
			'options'				  => $GLOBALS['TL_LANG']['DIV'],
			'eval'                    => array('feEditable'=>true, 'feGroup'=>'address', 'conditionField'=>'country', 'includeBlankOption'=>true, 'tl_class'=>'w50'),
		),
		'country' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_addresses']['country'],
			'exclude'                 => true,
			'filter'                  => true,
			'sorting'                 => true,
			'inputType'               => 'select',
			'options'                 => array_keys($this->getCountries()),
			'reference'               => $this->getCountries(),
			'eval'                    => array('mandatory'=>true, 'includeBlankOption'=>true, 'feEditable'=>true, 'feGroup'=>'address', 'tl_class'=>'w50'),
		),
		'phone' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_addresses']['phone'],
			'exclude'                 => true,
			'search'                  => true,
			'inputType'               => 'text',
			'eval'                    => array('maxlength'=>64, 'rgxp'=>'phone', 'feEditable'=>true, 'feGroup'=>'contact', 'tl_class'=>'w50'),
		),
		'email' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_addresses']['email'],
			'exclude'                 => true,
			'search'                  => true,
			'inputType'               => 'text',
			'eval'                    => array('maxlength'=>64, 'rgxp'=>'email', 'feEditable'=>true, 'feGroup'=>'contact', 'tl_class'=>'w50'),
		),
		'isDefaultBilling' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_addresses']['isDefaultBilling'],
			'exclude'                 => true,
			'inputType'               => 'checkbox',
			'eval'					  => array('feEditable'=>true, 'feGroup'=>'login', 'membersOnly'=>true, 'tl_class'=>'w50')
		),
		'isDefaultShipping' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_addresses']['isDefaultShipping'],
			'exclude'                 => true,
			'inputType'               => 'checkbox',
			'eval'					  => array('feEditable'=>true, 'feGroup'=>'login', 'membersOnly'=>true, 'tl_class'=>'w50')
		),
	)
);


/**
 * tl_iso_addresses class.
 * 
 * @extends Backend
 */
class tl_iso_addresses extends Backend
{

	public function renderLabel($arrAddress)
	{
		$this->import('Isotope');
		
		return $this->Isotope->generateAddressString($arrAddress);
	}
	
	
	/**
	 * Make sure only one address is marked as default
	 */
	public function updateDefaultAddress($dc=null)
	{
		$intId = TL_MODE == 'FE' ? $this->Input->get('id') : $dc->id;
		
		$objAddress = $this->Database->prepare("SELECT * FROM tl_iso_addresses WHERE id=?")->limit(1)->execute($intId);
		
		if (!$objAddress->numRows)
			return;
		
		if ($this->Input->post('isDefaultBilling'))
		{
			$this->Database->execute("UPDATE tl_iso_addresses SET isDefaultBilling='' WHERE pid={$objAddress->pid}");
			$this->Database->execute("UPDATE tl_iso_addresses SET isDefaultBilling='1' WHERE id={$objAddress->id}");
		}
		
		if ($this->Input->post('isDefaultShipping'))
		{
			$this->Database->execute("UPDATE tl_iso_addresses SET isDefaultShipping='' WHERE pid={$objAddress->pid}");
			$this->Database->execute("UPDATE tl_iso_addresses SET isDefaultShipping='1' WHERE id={$objAddress->id}");
		}
	}
}

