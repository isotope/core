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
 * @copyright  Isotope eCommerce Workgroup 2009-2011
 * @author     Andreas Schempp <andreas@schempp.ch>
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */

class CybersourceClient extends SoapClient
{
   protected $merchantId;

   protected $transactionKey;

   public function __construct($wsdl, $options = null, $strMerchantId, $strTransactionKey)
   {
     parent::__construct($wsdl, $options);

   	 $this->merchantId = $strMerchantId;
	 $this->transactionKey = $strTransactionKey;
   }

// This section inserts the UsernameToken information in the outgoing SOAP message.
   public function __doRequest($objRequest, $strLocation, $strAction, $strVersion)
   {
     $user = $this->merchantId;
     $password = $this->transactionKey;

     $strSoapHeader = "<SOAP-ENV:Header xmlns:SOAP-ENV=\"http://schemas.xmlsoap.org/soap/envelope/\" xmlns:wsse=\"http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd\"><wsse:Security SOAP-ENV:mustUnderstand=\"1\"><wsse:UsernameToken><wsse:Username>$user</wsse:Username><wsse:Password Type=\"http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-username-token-profile-1.0#PasswordText\">$password</wsse:Password></wsse:UsernameToken></wsse:Security></SOAP-ENV:Header>";

     $objRequestDOM = new DOMDocument('1.0');
     $objSoapHeaderDOM = new DOMDocument('1.0');

     try
	 {
        $objRequestDOM->loadXML($objRequest);
		$objSoapHeaderDOM->loadXML($strSoapHeader);

		$node = $objRequestDOM->importNode($objSoapHeaderDOM->firstChild, true);
		$objRequestDOM->firstChild->insertBefore($node, $objRequestDOM->firstChild->firstChild);

        $objSOAPRequest = $objRequestDOM->saveXML();

     }
	 catch (DOMException $e)
	 {
     	die( 'Error adding UsernameToken: ' . $e->code);
     }

     return parent::__doRequest($objSOAPRequest, $strLocation, $strAction, $strVersion);
   }
}


?>