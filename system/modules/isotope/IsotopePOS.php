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
 * @package    Isotope 
 * @license    Commercial 
 * @filesource
 */


/**
 * Class IsotopePOS
 *
 * Point-of-sale related resources class
 * @copyright  Winans Creative/Fred Bliss 2009 
 * @author     Fred Bliss 
 * @package    Controller
 */

class IsotopePOS extends Backend
{
	
	
	protected $fltOrderTotal;
	
	protected $fltOrderSubtotal;
	
	protected $fltOrderTaxTotal;
	
	protected $fltOrderShippingTotal;
	
	protected $arrBillingInfo;
	
	protected $intOrderId;
	
	protected $strReason;
	
	public function moduleOperations($intId)
	{
		
		$this->import('BackendUser', 'User');
	
		if ($this->User->isAdmin)
		{
			$strOperations = '&nbsp;<a href="'.$this->Environment->request.'&amp;key=authorize_process_payment&amp;id=' . $intId . '" title="'.specialchars($GLOBALS['TL_LANG']['tl_iso_orders']['authorize_process_payment'][0]).'"'.$attributes.'><img src="system/modules/isotope/html/money.png" border="0" alt="' . specialchars($GLOBALS['TL_LANG']['tl_iso_orders']['authorize_process_payment'][0]) . '" /></a>';
		} 
			
		$strOperations .= '&nbsp;<a href="'.$this->Environment->request.'&amp;key=print_order&amp;id=' . $intId . '" title="'.specialchars($GLOBALS['TL_LANG']['tl_iso_orders']['print_order'][0]).'"'.$attributes.'><img src="system/modules/isotope/html/printer.png" border="0" alt="'.specialchars($GLOBALS['TL_LANG']['tl_iso_orders']['print_order'][0]).'" /></a>';
		
		return $strOperations;

	}
	
	public function getPOSInterface(DataContainer $objDc)
	{	
		
		/*
		$ch = curl_init();

		curl_setopt($ch, CURLOPT_TIMEOUT, 30);
		curl_setopt($ch, CURLOPT_HEADER, 1);
		curl_setopt($ch, CURLOPT_VERBOSE, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		
		//Uncomment this for Windows.
		//curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		
		curl_setopt($ch, CURLOPT_URL, "https://www.stanford.edu/group/idg/leland/samples/secure/test.html");
		
		$result = curl_exec($ch);
		
		echo '<pre>';
		print_r(curl_getinfo($ch));
		echo '</pre>';
		
		echo 'Errors: ' . curl_errno($ch) . ' ' . curl_error($ch) . '<br><br>';
		
		curl_close ($ch);
		
		echo $result . 'EOF';
		*/
	
		//$objDc->id = $this->Input->get('id');
		$this->intOrderId = $objDc->id;
		
		//setlocale(LC_MONETARY, $GLOBALS['TL_LANG']['MSC']['isotopeLocale'][$GLOBALS['TL_LANG']['MSC']['defaultCurrency']]);		
		
		$objOrderInfo = $this->Database->prepare("SELECT * FROM tl_iso_orders WHERE id=?")
										   ->limit(1)
										   ->execute($objDc->id);
		
		
		
		$arrOrderInfo = $objOrderInfo->fetchAssoc();
							
		$arrPaymentInfo = deserialize($arrOrderInfo['payment_data']);
	
		$arrShippingInfo = deserialize($arrOrderInfo['shipping_data']);
		
				
		$this->fltOrderTotal = $arrPaymentInfo['totals']['grandTotal'];
		
		$strBillingAddress = nl2br($arrOrderInfo['billing_address']);
		$strShippingAddress = nl2br($arrOrderInfo['shipping_address']);

		$arrProductList = $this->getProducts($arrOrderInfo['cart_id']);
		
		//$strProductList = $this->generateProductList($arrProductList);
			
		
		//Get the authorize.net configuration data			
		$objAIMConfig = $this->Database->prepare("SELECT * FROM tl_payment_modules WHERE type=?")
														->execute('authorizedotnet');
		if($objAIMConfig->numRows < 1)
		{
			return '<i>' . $GLOBALS['TL_LANG']['MSC']['noPaymentModules'] . '</i>';
		}
		
		//$arrParams[$module] = $objPaymentModuleConfiguration->fetchAllAssoc();			
	
		//Code specific to Authorize.net!
		$objTemplate = new BackendTemplate('mod_pos_terminal');
									
		if($objAIMConfig->numRows > 0)
		{
			
			$delimResponse = "TRUE";
			$delimChar = $objAIMConfig->authorize_delimiter;
			$loginID = $objAIMConfig->authorize_login;
			$transKey = $objAIMConfig->authorize_trans_key;
			$transType = $objAIMConfig->authorize_trans_type;
			$status = ($objAIMConfig->debug ? "TRUE" : "FALSE");
			//var_dump($status);
		}

		if ($this->Input->post('FORM_SUBMIT') == 'mod_pos_terminal')
		{
			
			$authnet_values = array(
				"x_login"							=> $loginID,
				"x_version"							=> '3.1',
				"x_test_request"					=> $status,
				"x_delim_char"						=> ",",
				"x_delim_data"						=> $delimResponse,
				"x_url"								=> "FALSE",
				"x_type"							=> $transType,
				"x_method"							=> "CC",
				"x_tran_key"						=> $transKey,
				"x_relay_response"					=> "FALSE",
				"x_card_num"						=> $arrOrderInfo['cc_num'],
				"x_exp_date"						=> $arrOrderInfo['cc_exp'],
				"x_cardholder_authentication_value"	=> $arrOrderInfo['cc_cvv'],
				"x_description"						=> "Order Number " . $objDc->id,
				"x_amount"							=> number_format($this->fltOrderTotal, 2),
				"x_first_name"						=> $arrPaymentInfo['address']['firstname'],
				"x_last_name"						=> $arrPaymentInfo['address']['lastname'],
				"x_address"							=> $arrPaymentInfo['address']['street'],
				"x_city"							=> $arrPaymentInfo['address']['city'],
				"x_state"							=> $arrPaymentInfo['address']['state'],
				"x_zip"								=> $arrPaymentInfo['address']['postal'],
				"x_company"							=> $arrPaymentInfo['address']['company'],
				//"x_email_customer"				=> "TRUE",
				//"x_email"							=> $this->arrBillingInfo['email']
			);
			
			//$arrPaymentInfo = array('cc_num' => $this->Input->post('x_card_num'), 'cc_exp' => $this->Input->post('x_exp_date'), 'cc_cvv' => $this->Input->post('x_cardholder_authentication_value'));
			
			/*if($this->writeOrder($arrPaymentInfo))
			{		
				$objNextPage = $this->Database->prepare("SELECT id, alias FROM tl_page WHERE id=?")
										  ->limit(1)
										  ->execute($this->orderCompleteJumpTo);

				if ($objNextPage->numRows)
				{
					$postToURL = $this->generateFrontendUrl($objNextPage->fetchAssoc());
				}else{
					$postToURL = ampersand($this->Environment->request, ENCODE_AMPERSANDS);
				}			
				
				$this->redirect($postToURL);
			}*/
			
			foreach( $authnet_values as $key => $value ) $fields .= "$key=" . urlencode( $value ) . "&";

			$ch = curl_init(); 

			###  Uncomment the line ABOVE for test accounts or BELOW for live merchant accounts
			### $ch = curl_init("https://secure.authorize.net/gateway/transact.dll"); 
			
			curl_setopt($ch, CURLOPT_URL, 'https://test.authorize.net/gateway/transact.dll'); 
			curl_setopt($ch, CURLOPT_HEADER, 0); // set to 0 to eliminate header info from response
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // Returns response data instead of TRUE(1)
			curl_setopt($ch, CURLOPT_POSTFIELDS, rtrim( $fields, "& " )); // use HTTP POST to send form data

			#curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); // uncomment this line if you get no gateway response. ###
			$resp = curl_exec($ch); //execute post and get results
			curl_close ($ch);
			
							
			$arrResponses = $this->handleResponse($resp);

			foreach(array_keys($arrResponses) as $key)
			{
				$arrReponseLabels[strtolower(standardize($key))] = $key;
			}
						
			$objTemplate->fields = $this->generateResponseString($arrResponses, $arrReponseLabels);
			
			$objTemplate->headline = $this->generateModuleHeadline($arrResponses['transaction-status']) . ' - ' . $this->strReason;
			
			$objTemplate->isConfirmation = true;
			
			//$objTemplate->showPrintLink = true;
		}else{
			$objTemplate->x_version = '3.1';
			$objTemplate->x_delim_data = $delimResponse;
			$objTemplate->x_delim_char = $delimChar;
			$objTemplate->x_relay_response = "false";	//Must be false for AIM processing.
			$objTemplate->x_login = $loginID;
			$objTemplate->x_tran_key = $transKey;
			$objTemplate->x_method = "CC";
			$objTemplate->x_type = $transType;
			$objTemplate->x_test_request = $status;
			
			$objTemplate->x_first_name = $arrPaymentInfo['address']['firstname'];
			$objTemplate->x_last_name = $arrPaymentInfo['address']['lastname'];
			$objTemplate->x_company = $arrPaymentInfo['address']['company'];
			$objTemplate->x_address = $arrPaymentInfo['address']['street'];
			$objTemplate->x_city = $arrPaymentInfo['address']['city'];
			$objTemplate->x_state = $arrPaymentInfo['address']['state'];
			$objTemplate->x_zip = $arrPaymentInfo['address']['postal'];
			//$objTemplate->x_phone = $this->arrBillingInfo['phone'];
			//$objTemplate->x_fax = $this->arrBillingInfo['fax'];
			//$objTemplate->x_email = $this->arrSession['FORM_DATA']['billing_information_email'];
			//$objTemplate->x_email_customer = "TRUE";
			$objTemplate->x_amount = number_format($this->fltOrderTotal, 2);
			$objTemplate->subtotal = number_format($this->fltOrderSubtotal, 2);
			$objTemplate->shippingTotal = number_format($this->fltOrderShippingTotal, 2);
			$objTemplate->taxTotal = number_format($this->fltOrderTaxTotal, 2);
			$objTemplate->x_card_num = $arrOrderInfo['cc_num'];
			$objTemplate->x_exp_date = $arrOrderInfo['cc_exp'];
			$objTemplate->x_cardholder_authentication_value = $arrOrderInfo['cc_cvv'];
		
		}	
		
	
		$action = ampersand($this->Environment->request, ENCODE_AMPERSANDS);
		
		//$this->Template->x_cust_id;
		
		$objTemplate->formId = 'mod_pos_terminal';
	
		$objTemplate->slabel = specialchars($GLOBALS['TL_LANG']['MSC']['confirmOrder']);
		$return = '<input type="hidden" name="FORM_SUBMIT" value="' . $objTemplate->formId . '" />';
		$return .= '<div id="tl_buttons"><h1>' . $objTemplate->headline . '</h1>

<a href="'.$this->getReferer(ENCODE_AMPERSANDS).'" class="header_back" title="'.specialchars($GLOBALS['TL_LANG']['MSC']['backBT']).'">'.$GLOBALS['TL_LANG']['MSC']['backBT'].'</a>
</div>
';
		$return .= '<div class="block" style="padding:20px;"><div><h2>Order #' . $arrOrderInfo['id'] . '</h2>' . $objUserName->firstname . ' ' . $objUserName->lastname . '<br />Status: <strong>' . $GLOBALS['TL_LANG']['MSC']['order_status_labels'][$arrOrderInfo['status']] . '</strong><br />Shipping Method: ' . $GLOBALS['TL_LANG']['tl_iso_orders']['shipping_method_labels'][$arrOrderInfo['shipping_method']]  . '<br />Order Total: ' . money_format('$%n', $this->fltOrderTotal) . '</div><br /><div style="display: inline;"><div style="width: 50%; float: left"><h2>Billing Address:</h2>' . $strBillingAddress . '</div><div style="width: 50%; float: left"><h2>Shipping Address:</h2>' . $strShippingAddress . '</div></div><div style="clear: both;"></div><h2>Cart Contents:</h2><div style="border: solid 1px #cccccc; margin: 10px; padding: 10px;">' . $strProductList . '</div></div></div>';
		if(strlen($objTemplate->fields) && $arrResponses['transaction-status']=='Approved')
		{
			$this->cleanCreditCardData($arrOrderInfo['cc_num'], $objDc->id);
			//$return .= $objTemplate->fields;
		}elseif($arrOrderInfo['status']=='pending'){
			//$return .= $objTemplate->fields;
			$return .= '<div class="tl_formbody_submit"><div class="tl_submit_container">';
			$return .= '<input type="submit" class="submit" value="' . $objTemplate->slabel . '" /></div></td>';
			$return .= '</div></div>';
		}
					
		$objTemplate->orderReview = $return;
		$objTemplate->action = $action;
		$objTemplate->rowLast = 'row_' . (count($this->editable) + 1) . ((($i % 2) == 0) ? ' odd' : ' even');
					
		return $objTemplate->parse();
	
	}
	
	public function cleanCreditCardData($varCCNum, $intOrderId)
	{
		
		$strCCNum = str_replace(substr($varCCNum, 0, 12), 'XXXXXXXXXXXX', $varCCNum);
		
		$this->Database->prepare("UPDATE tl_iso_orders SET cc_num=? WHERE id=?")
					   ->execute($strCCNum, $intOrderId);
	
	}
	
	
	public function printInvoice(DataContainer $objDc)
	{
		//$objDc->id = $this->Input->get('id');
		$this->intOrderId = $objDc->id;
		
		//setlocale(LC_MONETARY, $GLOBALS['TL_LANG']['MSC']['isotopeLocale'][$GLOBALS['TL_LANG']['MSC']['defaultCurrency']]);		
		
		$objOrderInfo = $this->Database->prepare("SELECT * FROM tl_iso_orders WHERE id=?")
										   ->limit(1)
										   ->execute($objDc->id);
		
		
		
		$arrOrderInfo = $objOrderInfo->fetchAssoc();
		
		/*
		$objUserName = $this->Database->prepare("SELECT firstname, lastname FROM tl_address_book WHERE id=?")
									  ->limit(1)
									  ->execute($arrOrderInfo['billing_address_id']);
				
		if($objUserName->numRows < 1)
		{
			return '<no user name specified>';		
		}	
		*/
		
		
		//Store ID MUST be set prior to importing the Isotope or IsotopeStore libraries!
				
		//$this->fltOrderTotal = (float)$arrOrderInfo['subTotal'] + (float)$arrOrderInfo['taxTotal'] + (float)$arrOrderInfo['shippingTotal'];
		
		$strBillingAddress = nl2br($arrOrderInfo['billing_address']); //$this->createAddressString($arrOrderInfo, 'billing');
		$strShippingAddress = nl2br($arrOrderInfo['shipping_address']); //$this->createAddressString($arrOrderInfo, 'shipping');

		$strPaymentInfo = $this->generatePaymentInfoString($arrOrderInfo);
		//$strShippingInfo = $this->generateShippingInfoString($arrOrderInfo['shipping_rate_id']);
		
		$arrProductData = $this->getProducts($arrOrderInfo['cart_id']);
		
		$objTemplate = new BackendTemplate('iso_invoice');
		
		$objTemplate->invoiceTitle = $GLOBALS['TL_LANG']['MSC']['iso_invoice_title'] . ' #' . $this->intOrderId . '-' . date('mjY', $arrOrderInfo['tstamp']);		
		$objTemplate->orderBillingAddressHeader = $GLOBALS['TL_LANG']['MSC']['iso_billing_address_header'];
		$objTemplate->orderBillingAddressString = $strBillingAddress;
		$objTemplate->orderShippingAddressHeader = $GLOBALS['TL_LANG']['MSC']['iso_shipping_address_header'];
		$objTemplate->orderShippingAddressString = $strShippingAddress;
		$objTemplate->paymentInfoHeader = $GLOBALS['TL_LANG']['MSC']['iso_payment_info_header'];
		$objTemplate->paymentInfoString = $strPaymentInfo;
		$objTemplate->shippingInfoHeader = $GLOBALS['TL_LANG']['MSC']['iso_shipping_info_header'];
		$objTemplate->shippingInfoString = $arrOrderInfo['shipping_method']; //$strShippingInfo;
		$objTemplate->orderTrackingInfoString = $strOrderTrackingInfo;
		$objTemplate->productNameHeader = $GLOBALS['TL_LANG']['MSC']['iso_product_name_header'];
		$objTemplate->productSkuHeader = $GLOBALS['TL_LANG']['MSC']['iso_sku_header'];
		$objTemplate->productPriceHeader = $GLOBALS['TL_LANG']['MSC']['iso_price_header'];
		$objTemplate->productQuantityHeader = $GLOBALS['TL_LANG']['MSC']['iso_quantity_header'];
		$objTemplate->productTaxHeader = $GLOBALS['TL_LANG']['MSC']['iso_tax_header'];	
		$objTemplate->productSubtotalHeader = $GLOBALS['TL_LANG']['MSC']['iso_subtotal_header'];
		$objTemplate->products = $arrProductData;	//name, sku, price, quantity, tax, subtotal, options = array('name', 'value')
		$objTemplate->orderSubtotalHeader = $GLOBALS['TL_LANG']['MSC']['iso_subtotal_header'];
		$objTemplate->orderTaxHeader = $GLOBALS['TL_LANG']['MSC']['iso_tax_header'];
		$objTemplate->orderShippingHeader = $GLOBALS['TL_LANG']['MSC']['iso_order_shipping_header'];
		$objTemplate->orderGrandTotalHeader = $GLOBALS['TL_LANG']['MSC']['iso_order_grand_total_header'];
		$objTemplate->orderSubtotal = $arrOrderInfo['subTotal']; 
		$objTemplate->orderTaxTotal = $arrOrderInfo['taxTotal']; 
		$objTemplate->orderShippingTotal = $arrOrderInfo['shippingTotal']; 
		$objTemplate->orderGrandTotal = $arrOrderInfo['grandTotal'];
		$objTemplate->orderFooterString = '';	
		$objTemplate->logoImage = $this->Environment->base . 'tl_files/cdss/images/globalLayout/headerLogo.gif';
		$strInvoiceTitle = $GLOBALS['TL_LANG']['MSC']['iso_invoice_title'] . '_' . $objDc->id . '_' . time();
		
		//$strArticle = html_entity_decode($strArticle, ENT_QUOTES, $GLOBALS['TL_CONFIG']['characterSet']);
		
		// Replace relative links
		$arrLinks = array();
		
		// Remove form elements
		$strArticle = preg_replace('/<form.*<\/form>/Us', '', $strArticle);
		$strArticle = preg_replace('/\?pdf=[0-9]*/i', '', $strArticle);

		$arrChunks = array();
		$strArticle .= $objTemplate->parse();
		
		//echo $strArticle;
		//exit;
		
		/*
		if(1==1)
		{
			// Add head section
			$strHtml = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">' . "\n";
			$strHtml .= '<html xmlns="http://www.w3.org/1999/xhtml">' . "\n";
			$strHtml .= '<head>' . "\n";
			$strHtml .= '<title>' . $strInvoiceTitle . '</title>' . "\n";
			$strHtml .= '<meta http-equiv="Content-Type" content="text/html; charset=' . $GLOBALS['TL_CONFIG']['characterSet'] . '" />' . "\n";
			$strHtml .= '<base href="' . $this->Environment->base . '" />' . "\n";

			$objStylesheet = $this->Database->execute("SELECT * FROM tl_style_sheet");

			
			// Add stylesheets
			while ($objStylesheet->next())
			{
				$arrMedia = deserialize($objStylesheet->media, true);
				
				if (in_array('print', $arrMedia) || in_array('all', $arrMedia))
				{
					$strHtml .= '<link rel="stylesheet" type="text/css" href="' . $objStylesheet->name . '.css" />' . "\n";
				}
			}

			// Convert the Euro symbol
			$strArticle = str_replace('€', '&#0128;', $strArticle);

			// Make sure there is no background
			$strHtml .= '<style type="text/css">' . "\n";
			$strHtml .= 'body { background:none; background-color:#ffffff; }' . "\n";
			$strHtml .= '</style>' . "\n";
			$strHtml .= '</head>' . "\n";
			$strHtml .= '<body>' . "\n";
			$strHtml .= $strArticle . "\n";
			$strHtml .= '</body>' . "\n";
			$strHtml .= '</html>';

			// Generate DOMPDF object
			require_once(TL_ROOT . '/plugins/dompdf/dompdf_config.inc.php');
			$dompdf = new DOMPDF();

			$dompdf->set_paper('a4');
			$dompdf->set_base_path(TL_ROOT);
			$dompdf->load_html($strArticle);
			$dompdf->render();

			$dompdf->stream(standardize(ampersand($strInvoiceTitle, false)) . '.pdf');

		}else{*/
			preg_match_all('/<pre.*<\/pre>/Us', $strArticle, $arrChunks);
	
			// Replace linebreaks within PRE tags
			foreach ($arrChunks[0] as $strChunk)
			{
				$strArticle = str_replace($strChunk, str_replace("\n", '<br />', $strChunk), $strArticle);
			}
				
			// Remove linebreaks and tabs
			$strArticle = str_replace(array("\n", "\t"), '', $strArticle);
			$strArticle = preg_replace('/<span style="text-decoration: ?underline;?">(.*)<\/span>/Us', '<u>$1</u>', $strArticle);
	
			// TCPDF configuration
			$l['a_meta_dir'] = 'ltr';
			$l['a_meta_charset'] = $GLOBALS['TL_CONFIG']['characterSet'];
			$l['a_meta_language'] = $GLOBALS['TL_LANGUAGE'];
			$l['w_page'] = "page";
	
			// Include library
			require_once(TL_ROOT . '/system/config/tcpdf.php');
			require_once(TL_ROOT . '/plugins/tcpdf/tcpdf.php'); 
	
			// Create new PDF document
			$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true); 
	
			// Set document information
			$pdf->SetCreator(PDF_CREATOR);
			$pdf->SetAuthor(PDF_AUTHOR);
			$pdf->SetTitle($objInvoice->title);
			$pdf->SetSubject($objInvoice->title);
			$pdf->SetKeywords($objInvoice->keywords);
	
			// Remove default header/footer
			$pdf->setPrintHeader(false);
			$pdf->setPrintFooter(false);
	
			// Set margins
			$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
	
			// Set auto page breaks
			$pdf->SetAutoPageBreak(true, PDF_MARGIN_BOTTOM);
	
			// Set image scale factor
			$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO); 
	
			// Set some language-dependent strings
			$pdf->setLanguageArray($l); 
	
			// Initialize document and add a page
			$pdf->AliasNbPages();
			$pdf->AddPage();
	
			// Set font
			$pdf->SetFont(PDF_FONT_NAME_MAIN, "", PDF_FONT_SIZE_MAIN);
	
			// Write the HTML content
			$pdf->writeHTML($strArticle, true, 0, true, 0);
	
			// Close and output PDF document
			$pdf->lastPage();
			$pdf->Output(standardize(ampersand($strInvoiceTitle, false)) . '.pdf', 'D');
		//}
		// Stop script execution
		ob_end_clean();
		exit;	
	}

	protected function createAddressString($arrOrderInfo, $strAddressType)
	{
		$strAddress = $arrOrderInfo[$strAddressType . '_information_firstname'];
		$strAddress .= ' ' . $arrOrderInfo[$strAddressType . '_information_lastname'];
		$strAddress = '<br />' . $arrOrderInfo[$strStep . '_information_company'];

		$strStreetAddress = $arrOrderInfo[$strAddressType . '_information_street'];
		$strStreetAddress .= $arrOrderInfo[$strAddressType . '_information_street_2'] ? '<br /> ' . $arrOrderInfo[$strAddressType . '_information_street_2'] : '';
		$strStreetAddress .= $arrOrderInfo[$strAddressType . '_information_street_3'] ? '<br /> ' . $arrOrderInfo[$strAddressType . '_information_street_3'] : '';				
		
		$strAddress = '<br />' . $strStreetAddress;

		$strAddress = '<br />' . $arrOrderInfo[$strAddressType . '_information_city'];
		
		$strAddress = $arrOrderInfo[$strAddressType . '_information_state'] ? '<br /> ' . $arrOrderInfo[$strAddressType . '_information_state'] : '';
		
		$strAddress = '<br />' . $arrOrderInfo[$strAddressType . '_information_postal'];
		$strAddress = '<br />' . $arrOrderInfo[$strAddressType . '_information_country'];
	
		return $strAddress;
	}

	protected function getProducts($intSourceCartId)
	{
		$arrProductListsByTable = array();
		$arrProductData = array();
		
		$objProductData = $this->Database->prepare("SELECT ci.product_id, ci.quantity_requested, ci.price, p.storeTable FROM tl_cart_items ci, tl_product_attribute_sets p WHERE p.id = ci.attribute_set_id AND ci.pid =?")
										 ->execute($intSourceCartId);
		
		if($objProductData->numRows < 1)
		{
			return '';
		}
		
		$arrProductData = $objProductData->fetchAllAssoc();
		
		
		foreach($arrProductData as $productData)
		{
			$arrProductLists[$productData['storeTable']][$productData['product_id']] = array
			(
					'table'				=> $productData['storeTable'],
					'id'				=> $productData['product_id'], 
					'quantity'			=> $productData['quantity_requested'],
					'price'				=> $productData['price']
			);
		}

			
		foreach(array_keys($arrProductLists) as $storeTable)
		{					
			
			$fltProductTotal = 0.00;
			
			//Build list of product ids to work with
			foreach($arrProductLists[$storeTable] as $row)
			{
				$arrProductIds[] = $row['id'];
			}
			
			$strProductList = implode(',', $arrProductIds);
									
			$objProductExtendedData = $this->Database->prepare("SELECT id, name, sku FROM " . $storeTable . " WHERE id IN(" . $strProductList . ")")
													 ->execute();
									
			if($objProductExtendedData->numRows < 1)
			{
				continue;
			}		
			
			$arrProductExtendedData = $objProductExtendedData->fetchAllAssoc();
			
			foreach($arrProductExtendedData as $row)
			{
				//$fltProductTax = ((int)$arrProductLists[$storeTable][$row['id']]['quantity'] * (float)$arrProductLists[$storeTable][$row['id']]['price']) * 0.05;
					
				$fltSubtotal = ((int)$arrProductLists[$storeTable][$row['id']]['quantity'] * (float)$arrProductLists[$storeTable][$row['id']]['price']) + round($fltProductTax, 2);
				//	echo $fltSubtotal;
				
				$arrAllProducts[] = array
				(
					'name'			=> $row['name'],
					'sku'			=> $row['sku'],
					'price'			=> number_format((float)$arrProductLists[$storeTable][$row['id']]['price'], 2),
					'quantity'		=> $arrProductLists[$storeTable][$row['id']]['quantity'],
					//'tax'			=> number_format($fltProductTax, 2),
					'subtotal'		=> number_format($fltSubtotal, 2)
					
				); 	
			}	
		}
		
		return $arrAllProducts;
	}
	
	protected function generateProductList($arrProducts)
	{
		
		foreach($arrProducts as $row)
		{			
			$strProductData .= $row['name'] . ' - ' . money_format('$%n', (float)$arrProductLists[$storeTable][$row['id']]['price']) . ' x ' . $row['quantity'] . ' = ' . money_format('$%n', ((float)$arrProductLists[$storeTable][$row['id']]['price'] * $row['quantity'])) . '<br />';
		
		}
	
		return $strProductData;
	}
	
	protected function loadAddress($varValue, $intId, $blnSaveAsBillingInfo = false)
	{
		$intPid = $this->getPid($intId, 'tl_iso_orders');
	
		$objAddress = $this->Database->prepare("SELECT * FROM tl_address_book WHERE id=? and pid=?")
									 ->limit(1)
									 ->execute($varValue, $intPid);
		
		if($objAddress->numRows < 1)
		{
			return 'no address specified';
		}
		
		if($blnSaveAsBillingInfo)
		{
			$this->arrBillingInfo = $objAddress->fetchAssoc();
		}
		
		
		$strAddress = $objAddress->firstname . ' ' . $objAddress->lastname . "<br />";
		$strAddress .= $objAddress->street . "<br />";
		$strAddress .= $objAddress->city . ', ' . $objAddress->state . '  ' . $objAddress->postal . "<br />";
		$strAddress .= $objAddress->country;

		return $strAddress;
	}
	
	protected function generatePaymentInfoString($arrOrderInfo)
	{
		$arrBillingInfoLines = split("\n",$arrOrderInfo['billing_address']);
		
		$strPaymentInfo = $GLOBALS['TL_LANG']['MSC']['iso_card_name_title'] . ': ' . $arrBillingInfoLines[0] . '<br />';
		$strPaymentInfo .= in_array($arrOrderInfo['cc_type'], $GLOBALS['TL_LANG']['tl_iso_orders']['credit_card_types']) ? $GLOBALS['TL_LANG']['tl_iso_orders']['cc_type'][0] . ': ' . $GLOBALS['TL_LANG']['tl_iso_orders']['credit_card_types'][$arrOrderInfo['cc_type']] . '<br />' : NULL;
		$strPaymentInfo .= $GLOBALS['TL_LANG']['tl_iso_orders']['cc_num'][0] . ': XXXX-XXXX-XXXX-' . substr($arrOrderInfo['cc_num'], 12, 4) . '<br />';
		$strPaymentInfo .= $GLOBALS['TL_LANG']['tl_iso_orders']['cc_exp'][0] . ': ' . $arrOrderInfo['cc_exp'];
	
		return $strPaymentInfo;
	}
	
	protected function generateShippingInfoString($intShippingRateId)
	{
		$objShippingMethod = $this->Database->prepare("SELECT s.name, sr.description FROM tl_shipping_modules s INNER JOIN tl_shipping_options sr ON s.id=sr.pid  WHERE sr.id=?")
											->limit(1)
											->execute($intShippingRateId);
		
		if($objShippingMethod->numRows < 1)
		{
			return sprintf($GLOBALS['TL_LANG']['ERR']['noShippingMethodAvailable'], $intShippingRateId);
		}						
	
		$strShippingInfo = $objShippingMethod->name . ' ' . $objShippingMethod->description;
		
		return $strShippingInfo;
	}

	protected function getPid($intId, $strTable)
	{
		if(!$this->Database->fieldExists('pid',$strTable))
		{
			return 0;
		}
		
		
		$objPid = $this->Database->prepare("SELECT pid FROM " . $strTable . " WHERE id=?")
								 ->limit(1)
								 ->execute($intId);
		
		if($objPid->numRows < 1)
		{
			return 0;
		}
		
		return $objPid->pid;
		
	}
	
		//*** AUTHORIZE.NET Processing code - move to authorize class module and call that as the standard approach for handling and rendering out data?
	
	private function addAlert($alertText)
	{
		return "<span style=\"color:#ff0000;\">" . $alertText . "</span>";
	}
	
	private function generateModuleHeadline($strOrderStatus)
	{
		switch($strOrderStatus)
		{
			case "Approved":
				$this->setOrderStatus('processing');
				
				return "Your Order Is Complete!";
				break;
				
			case "Declined":
				return "Your payment method has been declined.";
				break;
			
			case "Error":
				return "There was an error with your payment method.";
				break;
			default:
				return;			
		}
	}
	
	private function setOrderStatus($strStatus)
	{
		$this->Database->prepare("UPDATE tl_iso_orders SET status=? WHERE id=?")
					   ->execute($strStatus, $this->intOrderId);
					   
		return;
	
	}
	
	private function generateResponseString($arrResponses, $arrResponseLabels)
	{
		$responseString .= '<tr><td align="right" colspan="2">&nbsp;</td></tr>';
			
			$showReason = true;
						
			foreach($arrResponses as $k=>$v)
			{
				$value = $v;
				
				switch($k)
				{
					case 'transaction-status':
						switch($v)
						{
							case "Declined":
							case "Error":
								$value = $this->addAlert($v); 
								$showReason = true;
								break;
							default:
								$value = "<strong>" . $v . "</strong>";
								break;
						}
						break;
					case 'reason':
						if(!$showReason)
						{
							continue;
						}
						
						$value = $this->addAlert($v); //. "<br /><a href=\"" . $this->session['infoPage'] . "\"><strong>Click here to review and correct your order</strong></a>";
						$this->strReason = $value;
					case 'grand-total':
						$value = $v;
						break;
				}	
				
				$responseString .= '<tr><td align="right" width="150">' . $arrResponseLabels[$k] . ':&nbsp;&nbsp;</td><td>' . $value . '</td></tr>';
				
			}
			
			return $responseString;
	}
	
	private function handleResponse($resp)
	{
		
		$resp = str_replace('"', '', $resp);
		
		$arrResponseString = explode(",",$resp);
		
		$i=1;
		
		$arrFieldsToDisplay = array(1, 4, 7, 9, 10, 11, 14, 15, 16, 17, 18, 19, 20, 22, 23, 24);	//Dynamic Later
		
		foreach($arrResponseString as $currResponseString)
		{
				if(empty($currResponseString)){
					$i++;
					continue; //$pstr_trimmed="NO VALUE RETURNED";
				}
				
				if(in_array($i, $arrFieldsToDisplay))
				{
					$pstr_trimmed = $currResponseString;
					
					switch($i)
					{
						
						case 1:
							$ftitle = "Transaction Status";
									
							$fval="";
							if($pstr_trimmed=="1"){
								$fval="Approved";
							}elseif($pstr_trimmed=="2"){
								$fval="Declined";
							}elseif($pstr_trimmed=="3"){
								$fval="Error";
							}
							break;
						
						case 4:
							$ftitle = "Reason";
							$fval = $pstr_trimmed;
							break;
							
						case 7:
							$ftitle = "Transaction ID";
							$fval = $pstr_trimmed;
							break;
							
						case 9:
							$ftitle = "Service";
							$fval = $pstr_trimmed;
							break;
							
						case 10:
							$ftitle = "Grand Total";
							$fval = $pstr_trimmed;
							break;
							
						case 11:
							$ftitle = "Payment Method";
							$fval = ($pstr_trimmed=="CC" ? "Credit Card" : "Other");
							break;
						
						case 14:	
							$ftitle = "First Name";
							$fval = $pstr_trimmed;
							break;
						
						case 15:	
							$ftitle = "Last Name";
							$fval = $pstr_trimmed;
							break;
							
						case 16:	
							$ftitle = "Company Name";
							$fval = $pstr_trimmed;
							break;
							
						case 17:	
							$ftitle = "Billing Address";
							$fval = $pstr_trimmed;
							break;
							
						case 18:	
							$ftitle = "City";
							$fval = $pstr_trimmed;
							break;
							
						case 19:	
							$ftitle = "State";
							$fval = $pstr_trimmed;
							break;
							
						case 20:	
							$ftitle = "Zip";
							$fval = $pstr_trimmed;
							break;
							
						case 22:	
							$ftitle = "Phone";
							$fval = $pstr_trimmed;
							break;
							
						case 23:	
							$ftitle = "Fax";
							$fval = $pstr_trimmed;
							break;
							
						case 24:	
							$ftitle = "Email";
							$fval = $pstr_trimmed;
							break;
							
						default:
							break;
					}
			
					$arrResponse[strtolower(standardize($ftitle))] = $fval;
				}
	
			$i++;
		}
	
		return $arrResponse;
	}

}


?>