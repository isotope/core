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

use Isotope\Product\Collection\Order;


/**
 * Class PaymentCybersource
 *
 * Handle Cybersource payments
 * @copyright  Isotope eCommerce Workgroup 2009-2012
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
 * @author     Christian de la Haye <service@delahaye.de>
 */
class Cybersource extends Payment
{
	private $arrCardTypes = array('visa'=>'001','mc'=>'002','amex'=>'003','discover'=>'004','diners'=>'005','carte_blanche'=>'006','jcb'=>'007','enroute'=>'014','jal'=>'021','maestro'=>'024','delta'=>'031','solo'=>'032','visa_electron'=>'033','dankort'=>'034','laser'=>'035','carte_bleue'=>'036','carta_si'=>'037','enc_acct_num'=>'039','uatp'=>'040','maestro_intl'=>'042','ge_money_uk'=>'043');

	public function __get($strKey)
	{
		switch( $strKey )
		{
			// Make sure at least one credit card is available
			case 'available':
				if (parent::__get($strKey) && is_array($this->allowed_cc_types) && count($this->allowed_cc_types)) // Can't use empty() because its an object property (using __get)
				{
					return true;
				}
				return false;
				break;

			default:
				return parent::__get($strKey);
		}
	}


	/**
	 * Process payment on confirmation page.
	 *
	 * @access public
	 * @return void
	 */
	public function processPayment()
	{
		return true;
	}

	public function checkoutForm()
	{
		$fields = '';

		$doNotSubmit = false;
		$strBuffer = '';

		$arrPayment = \Input::post('payment');
		$arrCCTypes = deserialize($this->allowed_cc_types);	//standard keys

		foreach($arrCCTypes as $type)
		{
			// numeric keys specific to Cybersource
			// @todo merchant bank makes a difference!
			$arrAllowedCCTypes[] = $this->arrCardTypes[$type];
		}

		$intStartYear = (integer)date('Y', time()); //4-digit year

		for($i=0;$i<=7;$i++)
			$arrYears[] = (string)$intStartYear+$i;
		//card_accountNumber,card_cardType,card_expirationMonth,card_expirationYear,card_cvNumber
		$arrFields = array
		(
			'card_accountNumber' 			=> array
			(
				'label'			=> &$GLOBALS['TL_LANG']['ISO']['cc_num'],
				'inputType'		=> 'text',
				'eval'			=> array('mandatory'=>true, 'rgxp'=>'digit', 'tableless'=>true),
			),
			'card_cardType' 			=> array
			(
				'label'			=> &$GLOBALS['TL_LANG']['ISO']['cc_type'],
				'inputType'		=> 'select',
				'options'		=> $arrAllowedCCTypes,
				'eval'			=> array('mandatory'=>true, 'rgxp'=>'digit', 'tableless'=>true),
				'reference'		=> &$GLOBALS['ISO_LANG']['CCT'],
			),
			'card_expirationMonth' => array
			(
				'label'			=> &$GLOBALS['TL_LANG']['ISO']['cc_exp_month'],
				'inputType'		=> 'select',
				'options'		=> array('01','02','03','04','05','06','07','08','09','10','11','12'),
				'eval'			=> array('mandatory'=>true, 'tableless'=>true, 'includeBlankOption'=>true)
			),
			'card_expirationYear'  => array
			(
				'label'			=> &$GLOBALS['TL_LANG']['ISO']['cc_exp_year'],
				'inputType'		=> 'select',
				'options'		=> $arrYears,
				'eval'			=> array('mandatory'=>true, 'tableless'=>true, 'includeBlankOption'=>true)
			),
			'card_cvNumber' => array
			(
				'label'			=> &$GLOBALS['TL_LANG']['ISO']['cc_ccv'],
				'inputType'		=> 'text',
				'eval'			=> array('mandatory'=>true, 'tableless'=>true)
			),
		);

		foreach( $arrFields as $field => $arrData )
		{
			$strClass = $GLOBALS['TL_FFL'][$arrData['inputType']];

			// Continue if the class is not defined
			if (!$this->classFileExists($strClass))
			{
				continue;
			}

			$objWidget = new $strClass($this->prepareForWidget($arrData, 'payment['.$field.']'));

			// Validate input
			if (\Input::post('FORM_SUBMIT') == 'iso_mod_checkout_payment' && $arrPayment['module'] == $this->id)
			{

				$objWidget->validate();

				if ($objWidget->hasErrors())
				{
					$doNotSubmit = true;
				}
			}
			elseif ($objWidget->mandatory && !strlen($arrPayment[$field]))
			{

				$doNotSubmit = true;
			}

			$strBuffer .= $objWidget->parse();
		}

		// Get the order from cart id
		$objOrder = Order::findOneBy('cart_id', $this->Isotope->Cart->id);

		$objAddress = $this->Isotope->Cart->billingAddress;
		$intTotal = round($this->Isotope->Cart->grandTotal, 2);

		$arrSubdivision = explode('-', $objAddress->subdivision);

		if(!$doNotSubmit && \Input::post('FORM_SUBMIT') == 'payment_form')
		{

			try
			{
				$objSoapClient = new CybersourceClient('https://ics2ws'.($this->debug ? 'test' : '').'.ic3.com/commerce/1.x/transactionProcessor/CyberSourceTransaction_1.26.wsdl', array(), $this->cybersource_merchant_id, $this->cybersource_trans_key);

				$objRequest = new stdClass();

				$objRequest->merchantID = $this->cybersource_merchant_id;

				// Before using this example, replace the generic value with your own.
				$objRequest->merchantReferenceCode = $objOrder->id;

				// To help us troubleshoot any problems that you may encounter,
				// please include the following information about your PHP application.
				$objRequest->clientLibrary = "PHP";
				$objRequest->clientLibraryVersion = phpversion();
				$objRequest->clientEnvironment = php_uname();

				// This section builds the transaction information
				// service with complete billing, payment card, and purchase (two items) information.
				$objCCAuthService = new stdClass();
				$objCCAuthService->run = "true";
				$objRequest->ccAuthService = $objCCAuthService;

				$objBillTo = new stdClass();
				$objBillTo->firstName = $objAddress->firstname;
				$objBillTo->lastName = $objAddress->lastname;
				$objBillTo->street1 = $objAddress->street_1;
				$objBillTo->city = $objAddress->city;
				$objBillTo->state = $arrSubdivision[1];
				$objBillTo->postalCode = $objAddress->postal;
				$objBillTo->country = $objAddress->country;
				$objBillTo->email = $objAddress->email;
				$objBillTo->ipAddress = $this->Environment->ip;
				$objRequest->billTo = $objBillTo;

				$objCard = new stdClass();
				$objCard->accountNumber = $arrPayment['card_accountNumber'];
				$objCard->expirationMonth = $arrPayment['card_expirationMonth'];
				$objCard->expirationYear = $arrPayment['card_expirationYear'];

				//if($this->requireCardType)
				$objCard->cardType = $arrPayment['card_cardType'];

				if($this->requireCCV)
					$objCard->cvNumber = $arrPayment['card_cvNumber'];

				$objRequest->card = $objCard;

				$objPurchaseTotals = new stdClass();
				$objPurchaseTotals->currency = $this->Isotope->Config->currency;
				$objPurchaseTotals->grandTotalAmount = round($this->Isotope->Cart->grandTotal, 2);
				$objRequest->purchaseTotals = $objPurchaseTotals;

				/*$arrProducts = $this->Isotope->Cart->getProducts();

				foreach($arrProducts as $i=>$objProduct)
				{
					$objItem = new stdClass();

					$objItem->unitPrice = $objProduct->price;
					$objItem->quantity = $objProduct->quantity;
					$objItem->id = $objProduct->id;

					$arrItems[] = $objItem;
				}

				$objRequest->item = $arrItems;*/

				//, $strLocation, $strAction, $strVersion, $strMerchantId, $strTransactionKey
				$objReply = $objSoapClient->runTransaction($objRequest);

				$arrPaymentData['transaction_response'] = $objReply->decision;
				$arrPaymentData['transaction_response_code'] = $objReply->reasonCode;
				$arrPaymentData['request_id'] = $objReply->requestID;
				$arrPaymentData['request_token'] = $objReply->requestToken;
				$objOrder->payment_data = serialize($arrPaymentData);

				switch($objReply->decision)
				{
					case 'ACCEPT':
						$arrPaymentData['cc_last_four'] = substr($strCCNum, strlen($strCCNum) - 4, 4);
						break;

					default:
						$blnFail = true;
						break;
				}

				$objOrder->save();

				if ($blnFail)
				{
					$this->log('Invalid payment data received.', 'PaymentCybersource checkoutForm()', TL_ERROR);
					$this->redirect(\Isotope\Frontend::addQueryStringToUrl('error=' . $objReply->reasonCode));
				}

				$this->redirect($this->addToUrl('step=complete', true));
			}
			catch (SoapFault $exception)
			{
				var_dump(get_class($exception));
				var_dump($exception);
			}
		}

		list($endTag) = \Isotope\Frontend::getElementAndScriptTags();

		return '
<h2>' . $this->label . '</h2>'.
(\Input::get('error') == '' ? '' : '<p class="error message">'.$GLOBALS['TL_LANG']['CYB'][\Input::get('error')].'</p>').
'<form id="payment_form" action="'.$this->Environment->request.'" method="post">
<input type="hidden" name="FORM_SUBMIT" value="payment_form"' . $endTag . '
<input type="hidden" name="REQUEST_TOKEN" value="'.REQUEST_TOKEN.'"' . $endTag
.$strBuffer.'
<input type="submit" value="' . specialchars($GLOBALS['TL_LANG']['MSC']['confirmOrder']) . '"' . $endTag . '
</form>';


	}

	public function backendInterface($intOrderId)
	{
		$objOrder = Order::findByPk($intOrderId);

		\Input::setGet('uid', $objOrder->uniqid);
		$objModule = new \Isotope\Module\OrderDetails($this->Database->execute("SELECT * FROM tl_module WHERE type='iso_orderdetails'"));

		$arrPaymentInfo = $objOrder->payment_data;

		//Get the authorize.net configuration data
		$objAIMConfig = $this->Database->prepare("SELECT * FROM tl_iso_payment_modules WHERE type=?")
														->execute('authorizedotnet');
		if($objAIMConfig->numRows < 1)
		{
			return '<i>' . $GLOBALS['TL_LANG']['MSC']['noPaymentModules'] . '</i>';
		}

		//Code specific to Authorize.net!
		$objTemplate = new \BackendTemplate('be_pos_terminal');

		if($objAIMConfig->numRows > 0)
		{

			$delimResponse = "TRUE";
			$delimChar = $objAIMConfig->authorize_delimiter;
			$loginID = $objAIMConfig->authorize_login;
			$transKey = $objAIMConfig->authorize_trans_key;
			$transType = 'PRIOR_AUTH_CAPTURE';
			$status = ($objAIMConfig->debug ? "TRUE" : "FALSE");
			$strMode = ($objAIMConfig->debug ? "test" : "secure");
		}

		if (\Input::post('FORM_SUBMIT') == 'be_pos_terminal' && $arrPaymentInfo['x_trans_id'] !== '0')
		{
			$cybersource_values = array
			(
				"x_version"							=> '3.1',
				"x_login"							=> $loginID,
				"x_tran_key"						=> $transKey,
				"x_type"							=> $transType,
				"x_trans_id"						=> $arrPaymentInfo['x_trans_id'],
				"x_amount"							=> number_format($objOrder->grandTotal, 2),
				"x_delim_data"						=> 'TRUE',
				"x_delim_char"						=> ',',
				"x_encap_char"						=> '"',
				"x_relay_response"					=> 'FALSE'
			);

			foreach( $cybersource_values as $key => $value )
			{
				$fields .= "$key=" . urlencode( $value ) . "&";
			}

			$fieldsFinal = rtrim($fields, '&');

			$objRequest = new \Request();

			$objRequest->send('https://secure.authorize.net/gateway/transact.dll', $fieldsFinal, 'post');

			$arrResponses = $this->handleResponse($objRequest->response);

			foreach(array_keys($arrResponses) as $key)
			{
				$arrReponseLabels[standardize($key)] = $key;
			}

			$objTemplate->fields = $this->generateResponseString($arrResponses, $arrReponseLabels);

			//$objTemplate->headline = $arrResponses['transaction-status'] . ' - ' . $this->strReason;

			$strResponse = '<p class="tl_info">' . $arrPaymentInfo['authorize_response'] . ' - ' . $arrResponses['transaction-status'] . '</p>';

			switch ($arrResponses['transaction-status'])
			{
				case 'Approved':
					$arrPaymentInfo['authorization_code'] = $arrResponses['authorization-code'];
					break;

				default:
					$arrPaymentInfo['authorize_reason'] = $arrResponses['reason'];
					break;
			}

			$objOrder->payment_info = serialize($arrPaymentInfo);
			$objOrder->save();

			$objTemplate->isConfirmation = true;
		}

		$objTemplate->formId = 'be_pos_terminal';
		$objTemplate->slabel = specialchars($GLOBALS['TL_LANG']['MSC']['confirmOrder']);

		$return = '<div id="tl_buttons">
<input type="hidden" name="FORM_SUBMIT" value="' . $objTemplate->formId . '" />
<input type="hidden" name="REQUEST_TOKEN" value="'.REQUEST_TOKEN.'">
<a href="'.ampersand(str_replace('&key=payment', '', $this->Environment->request)).'" class="header_back" title="'.specialchars($GLOBALS['TL_LANG']['MSC']['backBT']).'">'.$GLOBALS['TL_LANG']['MSC']['backBT'].'</a>
</div>
<h2 class="sub_headline">' . $GLOBALS['ISO_LANG']['PAY']['authorizedotnet'][0] . (!$arrPaymentInfo['x_trans_id'] || $arrPaymentInfo['x_trans_id'] == '0' ? ' - ' . 'Test Transaction' : '') . '</h2>
<div class="tl_formbody_edit">
<div class="tl_tbox block">';
$return .= ($strResponse ? $strResponse : '');
$return .= $objModule->generate(true);
$return .= '</div></div>';

		if($objOrder->status == 'pending')
		{
			$return .= '<div class="tl_formbody_submit"><div class="tl_submit_container">';
			$return .= '<input type="submit" class="submit" value="' . $objTemplate->slabel . '" /></div></div>';
		}

		$objTemplate->orderReview = $return;
		$objTemplate->action = ampersand($this->Environment->request, ENCODE_AMPERSANDS);
		$objTemplate->rowLast = 'row_' . (count($this->editable) + 1) . ((($i % 2) == 0) ? ' odd' : ' even');

		return $objTemplate->parse();
	}


	public function getAllowedCCTypes()
	{
		return array('mc', 'visa', 'amex', 'discover', 'jcb', 'diners', 'enroute', 'carte_blanche', 'jal', 'maestro', 'delta', 'solo', 'visa_electron', 'dankort', 'laser', 'carte_bleue', 'carta_si', 'enc_acct_num', 'uatp', 'maestro_intl', 'ge_money_uk');
	}
}

