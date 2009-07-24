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
 * @copyright  Winans Creative / Fred Bliss 2009
 * @author     Fred Bliss <fred@winanscreative.com>
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
			array('tl_address_book','copyInitialAddress')
		)		
	),

	// List
	'list' => array
	(
		'sorting' => array
		(
			'mode'                    => 2,
			'fields'                  => array('lastname'),
			'flag'                    => 1,
			'panelLayout'             => 'filter;sort,search,limit'
		),
		'label' => array
		(
			'fields'                  => array('firstname', 'lastname'),
			'format'                  => '%s %s <span style="color:#b3b3b3; padding-left:3px;">[%s]</span>',
			'label_callback'          => array('tl_address_book', 'addIcon')
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
		'default'                     => 'firstname,lastname;email;street,postal,city,state,country;phone;isDefaultBilling,isDefaultShipping',
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
			'eval'                    => array('mandatory'=>true, 'maxlength'=>255, 'insertTag'=>true, 'feEditable'=>true, 'feViewable'=>true, 'feGroup'=>'personal', 'isoEditable'=>true, 'isoCheckoutGroups'=>array('billing_information','shipping_information'))
		),
		'lastname' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_address_book']['lastname'],
			'exclude'                 => true,
			'search'                  => true,
			'sorting'                 => true,
			'flag'                    => 1,
			'inputType'               => 'text',
			'eval'                    => array('mandatory'=>true, 'maxlength'=>255, 'insertTag'=>true, 'feEditable'=>true, 'feViewable'=>true, 'feGroup'=>'personal', 'isoEditable'=>true, 'isoCheckoutGroups'=>array('billing_information','shipping_information'))
		),
		'company' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_address_book']['company'],
			'exclude'                 => true,
			'search'                  => true,
			'sorting'                 => true,
			'flag'                    => 1,
			'inputType'               => 'text',
			'eval'                    => array('maxlength'=>255, 'insertTag'=>true, 'feEditable'=>true, 'feViewable'=>true, 'feGroup'=>'address', 'isoEditable'=>true, 'isoCheckoutGroups'=>array('billing_information','shipping_information'))
		),
		'street' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_address_book']['street'],
			'exclude'                 => true,
			'search'                  => true,
			'inputType'               => 'text',
			'eval'                    => array('mandatory'=>true, 'maxlength'=>255, 'insertTag'=>true, 'feEditable'=>true, 'feViewable'=>true, 'feGroup'=>'address', 'isoEditable'=>true, 'isoCheckoutGroups'=>array('billing_information','shipping_information'))
		),
		'street_2' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_address_book']['street'],
			'exclude'                 => true,
			'search'                  => true,
			'inputType'               => 'text',
			'eval'                    => array('maxlength'=>255, 'insertTag'=>true, 'feEditable'=>true, 'feViewable'=>true, 'feGroup'=>'address', 'isoEditable'=>true, 'isoCheckoutGroups'=>array('billing_information','shipping_information'))
		),
		'street_3' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_address_book']['street'],
			'exclude'                 => true,
			'search'                  => true,
			'inputType'               => 'text',
			'eval'                    => array('maxlength'=>255, 'insertTag'=>true, 'feEditable'=>true, 'feViewable'=>true, 'feGroup'=>'address', 'isoEditable'=>true, 'isoCheckoutGroups'=>array('billing_information','shipping_information'))
		),
		'postal' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_address_book']['postal'],
			'exclude'                 => true,
			'search'                  => true,
			'inputType'               => 'text',
			'eval'                    => array('mandatory'=>true, 'maxlength'=>32, 'insertTag'=>true, 'feEditable'=>true, 'feViewable'=>true, 'feGroup'=>'address', 'isoEditable'=>true, 'isoCheckoutGroups'=>array('billing_information','shipping_information'))
		),
		'city' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_address_book']['city'],
			'exclude'                 => true,
			'filter'                  => true,
			'search'                  => true,
			'sorting'                 => true,
			'inputType'               => 'text',
			'eval'                    => array('mandatory'=>true, 'maxlength'=>255, 'insertTag'=>true, 'feEditable'=>true, 'feViewable'=>true, 'feGroup'=>'address', 'isoEditable'=>true, 'isoCheckoutGroups'=>array('billing_information','shipping_information'))
		),
		'state' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_address_book']['state'],
			'exclude'                 => true,
			'sorting'                 => true,
			'inputType'               => 'text',
			'eval'                    => array('maxlength'=>64, 'insertTag'=>true, 'feEditable'=>true, 'feViewable'=>true, 'feGroup'=>'address', 'isoEditable'=>true, 'isoCheckoutGroups'=>array('billing_information','shipping_information'))
		),
		'country' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_address_book']['country'],
			'exclude'                 => true,
			'filter'                  => true,
			'sorting'                 => true,
			'inputType'               => 'select',
			'options'                 => $this->getCountries(),
			'eval'                    => array('mandatory'=>true, 'includeBlankOption'=>true, 'feEditable'=>true, 'feViewable'=>true, 'feGroup'=>'address', 'isoEditable'=>true, 'isoCheckoutGroups'=>array('billing_information','shipping_information'))
		),
		'phone' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_address_book']['phone'],
			'exclude'                 => true,
			'search'                  => true,
			'inputType'               => 'text',
			'eval'                    => array('mandatory'=>true, 'maxlength'=>64, 'insertTag'=>true, 'feEditable'=>true, 'feViewable'=>true, 'feGroup'=>'contact', 'isoEditable'=>true, 'isoCheckoutGroups'=>array('billing_information'))
		),
		'isDefaultBilling' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_address_book']['isDefaultBilling'],
			'exclude'                 => true,
			'inputType'               => 'checkbox',
			'eval'					  => array('isoEditable'=>true, 'feEditable'=>true)
		),
		'isDefaultShipping' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_address_book']['isDefaultShipping'],
			'exclude'                 => true,
			'inputType'               => 'checkbox',
			'eval'					  => array('isoEditable'=>true, 'feEditable'=>true)
		),
		'email' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_address_book']['email'],
			'exclude'                 => true,
			'search'                  => true,
			'inputType'               => 'text',
			'eval'                    => array('mandatory'=>true, 'mandatory'=>true, 'maxlength'=>255, 'rgxp'=>'email', 'insertTag'=>true, 'feEditable'=>true, 'feViewable'=>true, 'feGroup'=>'contact', 'isoEditable'=>true, 'isoCheckoutGroups'=>array('billing_information'))
		)
	)
);


/**
 * tl_address_book class.
 * 
 * @extends Backend
 */
class tl_address_book extends Backend
{

	/**
	 * Add an image to each record.
	 * 
	 * @access public
	 * @param array $row
	 * @param string $label
	 * @return string
	 */
	public function addIcon($row, $label)
	{
		$image = 'member';

		if ($row['disable'] || strlen($row['start']) && $row['start'] > time() || strlen($row['stop']) && $row['stop'] < time())
		{
			$image .= '_';
		}

		return sprintf('<div class="list_icon" style="background-image:url(\'system/themes/%s/images/%s.gif\');">%s</div>', $this->getTheme(), $image, $label);
	}
	
	
	/**
	 * copyInitialAddress function.
	 * 
	 * @access public
	 * @param object DataContainer $dc
	 * @return void
	 */
	public function copyInitialAddress(DataContainer $dc)
	{
	
		$objAddressInfo = $this->Database->prepare("SELECT COUNT(*) as count FROM tl_address_book WHERE pid=?")
										 ->execute($this->Input->get('id'));
												 
		if($objAddressInfo->numRows < 1)
		{
			$objAddress = $this->Database->prepare("SELECT * FROM tl_member WHERE id=?")
													  ->limit(1)
													  ->execute($this->Input->get('id'));
			
			if($objAddress->numRows < 1)
			{
				return;
			}			
										  
			//copy the address as it exists from the tl_member table.
			$arrSet = array
			(
				'pid'			=> $this->Input->get('id'),
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
				'email'			=> $objAddress->email
			
			);
			
			$this->Database->prepare('INSERT INTO tl_address_book %s')
						   ->set($arrSet)
						   ->execute();
		}
	}
}

