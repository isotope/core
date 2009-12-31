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
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */


/**
 * Configuration
 */
$GLOBALS['TL_DCA']['tl_member']['config']['ctable'][] = 'tl_address_book';
$GLOBALS['TL_DCA']['tl_member']['config']['onsubmit_callback'][] = array('tl_member_isotope_extended','copyInitialAddress');
	
$GLOBALS['TL_DCA']['tl_member']['fields']['country']['eval']['mandatory'] = true;		
$GLOBALS['TL_DCA']['tl_member']['fields']['phone']['eval']['rgxp'] = null;

/**
 * Operations
 */
$GLOBALS['TL_DCA']['tl_member']['list']['operations']['address_book'] = array
(
	'label'               => &$GLOBALS['TL_LANG']['tl_member']['address_book'],
	'href'                => 'table=tl_address_book',
	'icon'                => 'system/modules/isotope/html/icon-addressbook.gif',
);


/**
 * Field settings
 */
$GLOBALS['TL_DCA']['tl_member']['fields']['firstname']['eval']['mandatory'] = true;
$GLOBALS['TL_DCA']['tl_member']['fields']['lastname']['eval']['mandatory'] = true;
$GLOBALS['TL_DCA']['tl_member']['fields']['street']['eval']['mandatory'] = true;
$GLOBALS['TL_DCA']['tl_member']['fields']['postal']['eval']['mandatory'] = true;
$GLOBALS['TL_DCA']['tl_member']['fields']['city']['eval']['mandatory'] = true;
$GLOBALS['TL_DCA']['tl_member']['fields']['state']['eval']['mandatory'] = true;


/**
 * tl_member_isotope_extended class.
 * 
 * @extends Backend
 */
class tl_member_isotope_extended extends Backend
{
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
										 ->execute($dc->id);
										 
		if($objAddressInfo->count < 1)
		{
			$objAddress = $this->Database->prepare("SELECT * FROM tl_member WHERE id=?")
													  ->limit(1)
													  ->execute($dc->id);
			
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
				'isDefaultShipping' => '1'
			
			);

			$this->Database->prepare('INSERT INTO tl_address_book %s')
						   ->set($arrSet)
						   ->execute();
		}
	}
}

