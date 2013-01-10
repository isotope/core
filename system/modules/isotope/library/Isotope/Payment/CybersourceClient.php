<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2012 Isotope eCommerce Workgroup
 *
 * @package    Isotope
 * @link       http://www.isotopeecommerce.com
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 */

namespace Isotope\Payment;


/**
 * Class CybersourceClient
 *
 * @copyright  Isotope eCommerce Workgroup 2009-2012
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
 */
class CybersourceClient extends \SoapClient
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


