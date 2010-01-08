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
 * @author     Andreas Schempp <andreas@schempp.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */


/**
 * Table tl_address_book
 */
$GLOBALS['TL_DCA']['tl_address_book'] = array
(

	// Config
	'config' => array
	(
		'ptable'					  => 'tl_member',
		'dataContainer'               => 'Table',
		'onload_callback'			  => array
		(
			array('tl_address_book', 'copyInitialAddress')
		),
		'onsubmit_callback' => array
		(
			array('tl_address_book', 'updateDefaultAddress'),
		)
	),

	// List
	'list' => array
	(
		'sorting' => array
		(
			'mode'                    => 4,
			'headerFields'			  => array('firstname','lastname', 'username'),
			'flag'                    => 1,
			'panelLayout'             => 'filter;sort,search,limit',
			'child_record_callback'   => array('tl_address_book','renderLabel')
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
				'label'               => &$GLOBALS['TL_LANG']['tl_address_book']['edit'],
				'href'                => 'act=edit',
				'icon'                => 'edit.gif'
			),
			'copy' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_address_book']['copy'],
				'href'                => 'act=copy',
				'icon'                => 'copy.gif'
			),
			'delete' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_address_book']['delete'],
				'href'                => 'act=delete',
				'icon'                => 'delete.gif',
				'attributes'          => 'onclick="if (!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\')) return false; Backend.getScrollOffset();"'
			),
			'show' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_address_book']['show'],
				'href'                => 'act=show',
				'icon'                => 'show.gif'
			)
		)
	),

	// Palettes
	'palettes' => array
	(
		'default'					  => '{personal_legend},firstname,lastname;{address_legend:hide},company,street,postal,city,state,country;{contact_legend},email,phone;{default_legend},isDefaultBilling,isDefaultShipping',
	),

	// Fields
	'fields' => array
	(
		'firstname' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_address_book']['firstname'],
			'exclude'                 => true,
			'search'                  => true,
			'sorting'                 => true,
			'flag'                    => 1,
			'inputType'               => 'text',
			'eval'                    => array('mandatory'=>true, 'isoEditable'=>true, 'maxlength'=>255, 'tl_class'=>'w50'),
		),
		'lastname' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_address_book']['lastname'],
			'exclude'                 => true,
			'search'                  => true,
			'sorting'                 => true,
			'flag'                    => 1,
			'inputType'               => 'text',
			'eval'                    => array('mandatory'=>true, 'isoEditable'=>true, 'maxlength'=>255, 'tl_class'=>'w50'),
		),
		'company' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_address_book']['company'],
			'exclude'                 => true,
			'search'                  => true,
			'sorting'                 => true,
			'flag'                    => 1,
			'inputType'               => 'text',
			'eval'                    => array('maxlength'=>255, 'isoEditable'=>true, 'tl_class'=>'w50'),
		),
		'street' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_address_book']['street'],
			'exclude'                 => true,
			'search'                  => true,
			'inputType'               => 'text',
			'eval'                    => array('maxlength'=>255, 'isoEditable'=>true, 'tl_class'=>'w50'),
		),
		'postal' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_address_book']['postal'],
			'exclude'                 => true,
			'search'                  => true,
			'inputType'               => 'text',
			'eval'                    => array('maxlength'=>32, 'isoEditable'=>true, 'tl_class'=>'w50'),
		),
		'city' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_address_book']['city'],
			'exclude'                 => true,
			'filter'                  => true,
			'search'                  => true,
			'sorting'                 => true,
			'inputType'               => 'text',
			'eval'                    => array('maxlength'=>255, 'isoEditable'=>true, 'tl_class'=>'w50'),
		),
		'state' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_address_book']['state'],
			'exclude'                 => true,
			'sorting'                 => true,
			'inputType'               => 'text',
			'eval'                    => array('maxlength'=>64, 'isoEditable'=>true, 'tl_class'=>'w50'),
		),
		'country' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_address_book']['country'],
			'exclude'                 => true,
			'filter'                  => true,
			'sorting'                 => true,
			'inputType'               => 'select',
			'options'                 => $this->getCountries(),
			'eval'                    => array('includeBlankOption'=>true, 'isoEditable'=>true, 'tl_class'=>'w50'),
		),
		'phone' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_address_book']['phone'],
			'exclude'                 => true,
			'search'                  => true,
			'inputType'               => 'text',
			'eval'                    => array('maxlength'=>64, 'rgxp'=>'phone', 'isoEditable'=>true, 'tl_class'=>'w50'),
		),
		'email' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_address_book']['email'],
			'exclude'                 => true,
			'search'                  => true,
			'inputType'               => 'text',
			'eval'                    => array('maxlength'=>64, 'rgxp'=>'email', 'isoEditable'=>true, 'tl_class'=>'w50'),
		),
		
	/*
		'street_2' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_address_book']['street'],
			'exclude'                 => true,
			'search'                  => true,
			'inputType'               => 'text',
			'eval'                    => array('maxlength'=>255, 'insertTag'=>true, 'feViewable'=>true, 'feGroup'=>'address', 'isoEditable'=>true, 'isoCheckoutGroups'=>array('billing_information','shipping_information'))
		),
		'street_3' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_address_book']['street'],
			'exclude'                 => true,
			'search'                  => true,
			'inputType'               => 'text',
			'eval'                    => array('maxlength'=>255, 'insertTag'=>true, 'feViewable'=>true, 'feGroup'=>'address', 'isoEditable'=>true, 'isoCheckoutGroups'=>array('billing_information','shipping_information'))
		),
*/
		'isDefaultBilling' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_address_book']['isDefaultBilling'],
			'exclude'                 => true,
			'inputType'               => 'checkbox',
			'eval'					  => array('isoEditable'=>true, 'tl_class'=>'w50')
		),
		'isDefaultShipping' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_address_book']['isDefaultShipping'],
			'exclude'                 => true,
			'inputType'               => 'checkbox',
			'eval'					  => array('isoEditable'=>true, 'tl_class'=>'w50')
		),
	)
);


/**
 * tl_address_book class.
 * 
 * @extends Backend
 */
class tl_address_book extends Backend
{

	public function renderLabel($arrAddress)
	{
		$this->import('Isotope');
		
		return '<div style="margin-top:-15px">' . $this->Isotope->generateAddressString($arrAddress) . '</div>';
	}
	
	/**
	 * copyInitialAddress function.
	 * 
	 * @access public
	 * @param object DataContainer $dc
	 * @return void
	 */
	public function copyInitialAddress($dc = null)
	{
		if (TL_MODE == 'FE')
		{
			if (!FE_USER_LOGGED_IN)
				return;
				
			$this->import('FrontendUser', 'User');
			$intId = $this->User->id;
		}
		else
		{
			$intId = $dc->id;
		}
		
		$objAddressInfo = $this->Database->prepare("SELECT COUNT(*) as count FROM tl_address_book WHERE pid=?")
										 ->execute($intId);
												 
		if($objAddressInfo->numRows < 1)
		{
			$objAddress = $this->Database->prepare("SELECT * FROM tl_member WHERE id=?")->limit(1)->execute($intId);
			
			if($objAddress->numRows < 1)
			{
				return;
			}			
										  
			//copy the address as it exists from the tl_member table.
			$arrSet = array
			(
				'pid'			=> $intId,
				'tstamp'		=> $objAddress->tstamp,
				'firstname'		=> $objAddress->firstname,
				'lastname'		=> $objAddress->lastname,
				'company'		=> $objAddress->company,
				'street'		=> $objAddress->street,
				'postal'		=> $objAddress->postal,
				'city'			=> $objAddress->city,
				'state'			=> $objAddress->state,
				'country'		=> $objAddress->country,
				'phone'			=> $objAddress->phone,
				'isDefaultBilling'	=> '1',
				'isDefaultShipping' => '1',
			
			);
			
			$this->Database->prepare('INSERT INTO tl_address_book %s')
						   ->set($arrSet)
						   ->execute();
		}
	}
	
	
	/**
	 * Make sure only one address is marked as default
	 */
	public function updateDefaultAddress($dc=null)
	{
		$intId = TL_MODE == 'FE' ? $this->Input->get('id') : $dc->id;
		
		$objAddress = $this->Database->prepare("SELECT * FROM tl_address_book WHERE id=?")->limit(1)->execute($intId);
		
		if (!$objAddress->numRows)
			return;
		
		if ($this->Input->post('isDefaultBilling'))
		{
			$this->Database->prepare("UPDATE tl_address_book SET isDefaultBilling='' WHERE pid=?")->execute($objAddress->pid);
			$this->Database->prepare("UPDATE tl_address_book SET isDefaultBilling='1' WHERE id=?")->execute($objAddress->id);
		}
		
		if ($this->Input->post('isDefaultShipping'))
		{
			$this->Database->prepare("UPDATE tl_address_book SET isDefaultShipping='' WHERE pid=?")->execute($objAddress->pid);
			$this->Database->prepare("UPDATE tl_address_book SET isDefaultShipping='1' WHERE id=?")->execute($objAddress->id);
		}
	}
}

