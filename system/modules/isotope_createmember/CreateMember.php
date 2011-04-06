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


class CreateMember extends Frontend
{

	public function addMember($orderId, $blnCheckout=false, $objModule)
	{
		if($blnCheckout)
		{
			$arrData = array();
			$objOrder = $this->Database->prepare("SELECT * FROM tl_iso_orders WHERE id=?")->execute($orderId);
			
			//Get order info and email address
			$arrBilling = deserialize($objOrder->billing_address);
						
			if($arrBilling['saveUserInfo'])
			{
			
				//First check for existing user email. Don't want to duplicate.
				$objUser = $this->Database->prepare("SELECT * FROM tl_member WHERE email=?")->execute($arrBilling['email']);
				if(!$objUser->numRows)
				{		
					$strSalt = substr(md5(uniqid('', true)), 0, 23);
					$clearPass = $this->createRandomPassword();
					$hashPass = sha1($strSalt . $clearPass) . ':' . $strSalt;
					
					//Only small difference here. Perhaps work to 
					$arrData = array
					(
						'firstname' 	=> ($arrBilling['firstname'] ? $arrBilling['firstname'] : 'newuser'.$objOrder->id),
						'lastname'		=> ($arrBilling['lastname'] ? $arrBilling['lastname'] : 'newuser'.$objOrder->id),
						'street'		=> $arrBilling['street'],
						'city'			=> $arrBilling['city'],
						'state'			=> $arrBilling['state'],
						'postal'		=> $arrBilling['postal'],
						'country'		=> $arrBilling['country'],
						'email'			=> ($arrBilling['email'] ? $arrBilling['email'] : $objOrder->id.'@ibuycoffee.com'),
						'phone'			=> $arrBilling['phone'],
						'username'		=> $arrBilling['email'],
						'password'		=> $hashPass
					);
					
										
					if($objModule->arrOrderData['form_email_newsletter']>0)
					{
						$arrData['signup'] = true;
					}
					
					$this->createNewMember($arrData,$orderId,$clearPass);
				}
			}
			
			return true;
		}
		return $blnCheckout;
	}
	
	/**
	 * Create a new member
	 * @param array
	 */
	protected function createNewMember($arrData, $orderId, $clearPass)
	{
		$arrData['tstamp'] = time();
		$arrData['login'] = 1;
		$arrData['activation'] = md5(uniqid(mt_rand(), true));
		$arrData['dateAdded'] = $arrData['tstamp'];
		
		//Find Customers Group... if doesn't exist, create one.
		$arrGroup = array();
		$objGroup = $this->Database->execute("SELECT * FROM tl_member_group WHERE name='Customers'");
		if(!$objGroup->numRows)
		{
			try
			{
				$arrGroup[] = $this->Database->execute("INSERT INTO tl_member_group (name) VALUES ('Customers')")->insertID;
			}
			catch (Exception $e)
			{
				return;
			}
			
		}
		else
		{
			$arrGroup[] = $objGroup->id;
		}
		
		// Set default group
		$arrData['groups'] = serialize($arrGroup);

		// Auto-activate account
		$arrData['disable'] = 0;
				
		try
		{
			// Create user
			$objNewUser = $this->Database->prepare("INSERT INTO tl_member %s")->set($arrData)->execute();
			$insertId = $objNewUser->insertId;
		}
		catch (Exception $e)
		{
			$this->log('Create User Failed On Order ' . $orderId . '.', 'ModuleIsotopeCheckout writeOrder()', TL_ERROR);
			return;
		}
		
		$this->Database->prepare("UPDATE tl_iso_orders SET pid=? WHERE id=?")->execute($insertId, $orderId);

		// HOOK: send insert ID and user data
		if (isset($GLOBALS['TL_HOOKS']['createNewUser']) && is_array($GLOBALS['TL_HOOKS']['createNewUser']))
		{
			foreach ($GLOBALS['TL_HOOKS']['createNewUser'] as $callback)
			{
				$this->import($callback[0]);
				$this->$callback[0]->$callback[1]($insertId, $arrData);
			}
		}
		
		
		$strConfirmation = 'Thank you for your registration on '.$this->Environment->host.'.'. "\n\n";
		$strConfirmation .= 'Your username is: '.$arrData['username']." \n";
		$strConfirmation .= 'and your password is: '.$clearPass."\n\n";
		$strConfirmation .= 'Please keep this info in a safe place and use it to re-login to your account. '. "\n";
				
		$objEmail = new Email();

		$objEmail->from = 'sales@ibuycoffee.com';
		$objEmail->fromName = $GLOBALS['TL_ADMIN_NAME'];
		$objEmail->subject = sprintf($GLOBALS['TL_LANG']['MSC']['emailSubject'], $this->Environment->host);
		$objEmail->text = $strConfirmation;
		$objEmail->sendTo($arrData['email']);

	}
	
	/** 
	 * The letter l (lowercase L) and the number 1 
	 * have been removed, as they can be mistaken 
	 * for each other. 
	 * From http://www.totallyphp.co.uk/code/create_a_random_password.htm
	 */ 
	
	protected function createRandomPassword() { 
	
	    $chars = "abcdefghijkmnopqrstuvwxyz023456789"; 
	    srand((double)microtime()*1000000); 
	    $i = 0; 
	    $pass = '' ; 
	
	    while ($i <= 7) { 
	        $num = rand() % 33; 
	        $tmp = substr($chars, $num, 1); 
	        $pass = $pass . $tmp; 
	        $i++; 
	    }
	
	    return $pass; 
	
	} 


}


?>