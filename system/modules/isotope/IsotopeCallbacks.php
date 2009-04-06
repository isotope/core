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
 * @copyright  Winans Creative/Fred Bliss 2009 
 * @author     Fred Bliss 
 * @package    IsotopeBase 
 * @license    Commercial 
 * @filesource
 */


/**
 * Class ModuleGiftRegistryResults
 *
 * @copyright  Winans Creative/Fred Bliss 2009 
 * @author     Fred Bliss 
 * @package    Controller
 */

class IsotopeCallbacks extends Backend
{

	public function copyAddressBookEntry($intId, $arrData)
	{
		
		if(strlen($arrData['street'])<1 || strlen($arrData['city'])<1 || strlen($arrData['state'])<1 || strlen($arrData['postal']))
		{
			return '';
		}	
							  
		//copy the address as it exists from the tl_member table.
		$arrSet = array
		(
			'pid'			=> $intId,
			'tstamp'		=> $arrData['tstamp'],
			'firstname'		=> $arrData['firstname'],
			'lastname'		=> $arrData['lastname'],
			'company'		=> $arrData['company'],
			'street'		=> $arrData['street'],
			'postal'		=> $arrData['postal'],
			'city'			=> $arrData['city'],
			'state'			=> $arrData['state'],
			'country'		=> $arrData['country'],
			'phone'			=> $arrData['phone'],
			'isDefaultBilling'	=> '1',
			'isDefaultShipping' => '1'
		
		);
	
		
		$this->Database->prepare('INSERT INTO tl_address_book %s')
					   ->set($arrSet)
					   ->execute();
			
	}
	
	public function autoActivateNewMember($intId, $arrData)
	{
		
		//Auto-activate the user.
		$this->Database->prepare("UPDATE tl_member SET disable=0 WHERE id=?")->execute($intId);
		
	}
}
	
?>