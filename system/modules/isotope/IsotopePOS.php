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


/**
 * Class IsotopePOS
 *
 * Point-of-sale related resources class
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
	
	protected $strTemplate = "iso_invoice";
	
	public function __construct()
	{
		parent::__construct();
		
		$this->import('Isotope');
	
	}
	
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
		$this->intOrderId = $objDc->id;
		
		$objOrderInfo = $this->Database->prepare("SELECT * FROM tl_iso_orders WHERE id=?")
										   ->limit(1)
										   ->execute($objDc->id);
				
		$arrOrderInfo = $objOrderInfo->fetchAssoc();
		
		
		$this->Input->setGet('uid', $arrOrderInfo['uniqid']);
		$objModule = new ModuleIsotopeOrderDetails($this->Database->execute("SELECT * FROM tl_module WHERE type='iso_orderdetails'"));
		
		$strOrderDetails = $objModule->generate(true);
		
							
		$arrPaymentInfo = deserialize($arrOrderInfo['payment_data']);
		
		$this->fltOrderTotal = $arrOrderInfo['grandTotal'];
		
		
		//Get the authorize.net configuration data			
		$objAIMConfig = $this->Database->prepare("SELECT * FROM tl_iso_payment_modules WHERE type=?")
														->execute('authorizedotnet');
		if($objAIMConfig->numRows < 1)
		{
			return '<i>' . $GLOBALS['TL_LANG']['MSC']['noPaymentModules'] . '</i>';
		}
			
		//Code specific to Authorize.net!
		$objTemplate = new BackendTemplate('mod_pos_terminal');
									
		if($objAIMConfig->numRows > 0)
		{
			
			$delimResponse = "TRUE";
			$delimChar = $objAIMConfig->authorize_delimiter;
			$loginID = $objAIMConfig->authorize_login;
			$transKey = $objAIMConfig->authorize_trans_key;
			$transType = 'PRIOR_AUTH_CAPTURE'; //$objAIMConfig->authorize_trans_type;
			$status = ($objAIMConfig->debug ? "TRUE" : "FALSE");
			$strMode = ($objAIMConfig->debug ? "test" : "secure");
		}


		if ($this->Input->post('FORM_SUBMIT') == 'mod_pos_terminal' && $arrPaymentInfo['x_trans_id']!=="0")
		{
			
				
			$authnet_values = array
			(
				"x_version"							=> '3.1',
				"x_login"							=> $loginID,
				"x_tran_key"						=> $transKey,
				"x_type"							=> $transType,
				"x_trans_id"						=> $arrPaymentInfo['x_trans_id'],
				"x_amount"							=> number_format($this->fltOrderTotal, 2),
				"x_delim_data"						=> 'TRUE',
				"x_delim_char"						=> ',',
				"x_encap_char"						=> '"',
				"x_relay_response"					=> 'FALSE'
			
			);
			
						
			foreach( $authnet_values as $key => $value ) $fields .= "$key=" . urlencode( $value ) . "&";

			$fieldsFinal = rtrim($fields, '&');
						
			$objRequest = new Request();
			
			$objRequest->send('https://secure.authorize.net/gateway/transact.dll', $fieldsFinal, 'post');
		
			$arrResponses = $this->handleResponse($objRequest->response);
								
			foreach(array_keys($arrResponses) as $key)
			{
				$arrReponseLabels[strtolower(standardize($key))] = $key;
			}
						
			$objTemplate->fields = $this->generateResponseString($arrResponses, $arrReponseLabels);
			
			$objTemplate->headline = $this->generateModuleHeadline($arrResponses['transaction-status']) . ' - ' . $this->strReason;
			
			$arrPaymentInfo['authorize_response'] = $arrResponses['transaction-status'];
			
			switch($arrResponses['transaction-status'])
			{
				case 'Approved':		
					$arrPaymentInfo['authorization_code'] = $arrResponses['authorization-code'];			
					$strPaymentInfo = serialize($arrPaymentInfo);
					
					$this->Database->prepare("UPDATE tl_iso_orders SET status='processing', payment_data=? WHERE id=?")
								   ->execute($strPaymentInfo, $this->intOrderId);
					break;
				default:
					$arrPaymentInfo['authorize_reason'] = $arrResponses['reason'];
					$strPaymentInfo = serialize($arrPaymentInfo);
					
					$this->Database->prepare("UPDATE tl_iso_orders SET status='on_hold', payment_data=? WHERE id=?")
								   ->execute($strPaymentInfo, $this->intOrderId);					
					break;
			
			}
			
			$objTemplate->isConfirmation = true;
			
			//$objTemplate->showPrintLink = true;
		}
		
			
		$action = ampersand($this->Environment->request, ENCODE_AMPERSANDS);
		
		//$objTemplate->x_cust_id;
		
		$objTemplate->formId = 'mod_pos_terminal';
	
		$objTemplate->slabel = specialchars($GLOBALS['TL_LANG']['MSC']['confirmOrder']);
		$return = '<input type="hidden" name="FORM_SUBMIT" value="' . $objTemplate->formId . '" />';
		$return .= '<div id="tl_buttons">

<a href="'.$this->getReferer(ENCODE_AMPERSANDS).'" class="header_back" title="'.specialchars($GLOBALS['TL_LANG']['MSC']['backBT']).'">'.$GLOBALS['TL_LANG']['MSC']['backBT'].'</a>
</div>
';
		$return .= '<h2 class="sub_headline">' . $GLOBALS['TL_LANG']['PAY']['authorizedotnet'][0] . (!$arrPaymentInfo['x_trans_id'] || $arrPaymentInfo['x_trans_id']=="0" ? ' - ' . 'Test Transaction' : '') . '</h2>';
		$return .= '<div style="padding:10px;">';
		$return .= $strOrderDetails;
		$return .= '</div>';
 
		//<h2>Cart Contents:</h2><div style="border: solid 1px #cccccc; margin: 10px; padding: 10px;">' . $strProductList . '</div></div></div>';
		if($arrOrderInfo['status']=='pending'){
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
		
		$objOrder = $this->Database->prepare("SELECT * FROM tl_iso_orders WHERE id=?")
										   ->limit(1)
										   ->execute($objDc->id);
		
		$strInvoiceTitle = $GLOBALS['TL_LANG']['MSC']['iso_invoice_title'] . '_' . $objDc->id . '_' . time();

		
		// Replace relative links
		$arrLinks = array();
		
		// Remove form elements
		$strArticle = preg_replace('/<form.*<\/form>/Us', '', $strArticle);
		$strArticle = preg_replace('/\?pdf=[0-9]*/i', '', $strArticle);

		$arrChunks = array();
		
		$strArticle .= $this->generateContent($objOrder);

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
		
		$this->Isotope->resetStore(true); 	//Set store back to default.
		
		ob_end_clean();
		exit;	
	}
	
	protected function generateContent($objOrder)
	{				
		$objOrderData = $this->Database->prepare("SELECT * FROM tl_iso_orders WHERE uniqid=?")->limit(1)->execute($objOrder->uniqid);
		
		if (!$objOrderData->numRows)
		{
			$objTemplate = new FrontendTemplate('mod_message');
			$objTemplate->type = 'error';
			$objTemplate->message = $GLOBALS['TL_LANG']['ERR']['orderNotFound'];
			return;
		}
		
		$objTemplate = new BackendTemplate($this->strTemplate);
				
		$objTemplate->setData($objOrderData->row());
		
		$this->import('Isotope');
		$this->Isotope->overrideStore($objOrderData->store_id);
		
		// Invoice Logo
		$objInvoiceLogo = $this->Database->prepare("SELECT invoiceLogo FROM tl_store WHERE id=?")
										 ->limit(1)
										 ->execute($objOrderData->store_id);
		
		if($objInvoiceLogo->numRows < 1)
		{
			$strInvoiceLogo = null;
		}else{
			$strInvoiceLogo = $objInvoiceLogo->invoiceLogo;
		}

		$objTemplate->logoImage = strlen($strInvoiceLogo) && file_exists(TL_ROOT . '/' . $strInvoiceLogo) ? str_replace('src="', 'src="/', $this->generateImage($strInvoiceLogo)) : false;
				
		$objTemplate->invoiceTitle = $GLOBALS['TL_LANG']['MSC']['iso_invoice_title'] . ' ' . $objOrderData->id . ' - ' . date('m-d-Y g:i', $objOrderData->tstamp);
		
		// Article reader
		$arrPage = $this->Database->prepare("SELECT * FROM tl_page WHERE id=?")->limit(1)->execute($this->jumpTo)->fetchAssoc();
		
		$arrAllDownloads = array();
		$arrItems = array();
		$objItems = $this->Database->prepare("SELECT p.*, o.*, t.downloads AS downloads_allowed, (SELECT COUNT(*) FROM tl_iso_order_downloads d WHERE d.pid=o.id) AS has_downloads FROM tl_iso_order_items o LEFT OUTER JOIN tl_iso_products p ON o.product_id=p.id LEFT OUTER JOIN tl_product_types t ON p.type=t.id WHERE o.pid=?")->execute($objOrderData->id);
		
		
		while( $objItems->next() )
		{
			// Do not use the TYPOlight function deserialize() cause it handles arrays not objects
			$objProduct = unserialize($objItems->product_data);
			
			if (!is_object($objProduct))
				continue;
			
			if ($objItems->downloads_allowed/* && $objItems->has_downlaods > 0*/)
			{
				$arrDownloads = array();
				$objDownloads = $this->Database->prepare("SELECT p.*, o.* FROM tl_iso_order_downloads o LEFT OUTER JOIN tl_iso_downloads p ON o.download_id=p.id WHERE o.pid=?")->execute($objItems->id);
				
				while( $objDownloads->next() )
				{
					// Send file to the browser
					if (strlen($this->Input->get('file')) && $this->Input->get('file') == $objDownloads->id && ($objDownloads->downloads_allowed == 0 || $objDownloads->downloads_remaining > 0))
					{
						if ($objDownloads->downloads_remaining > 0)
						{
							$this->Database->prepare("UPDATE tl_iso_order_downloads SET downloads_remaining=? WHERE id=?")->execute(($objDownloads->downloads_remaining-1), $objDownloads->id);
						}
						
						$this->sendFileToBrowser($objDownloads->singleSRC);
					}
					
					$arrDownload = array
					(
						'raw'			=> $objDownloads->row(),
						'title'			=> $objDownloads->title,
						'href'			=> ($this->generateFrontendUrl($objPage->row()) . '?uid=' . $this->Input->get('uid') . '&amp;file=' . $objDownloads->id),
						'remaining'		=> ($objDownloads->downloads_allowed > 0 ? sprintf('<br />%s Downloads verbleibend', intval($objDownloads->downloads_remaining)) : ''),
						'downloadable'	=> (($objDownloads->downloads_allowed == 0 || $objDownloads->downloads_remaining > 0) ? true : false),
					);
					
					$arrDownloads[] = $arrDownload;
					$arrAllDownloads[] = $arrDownload;
				}
			}
			
			$arrItems[] = array
			(
				'raw'				=> $objItems->row(),
				'product_options' 	=> $objProduct->getOptions(),
				'downloads'			=> (is_array($arrDownloads) ? $arrDownloads : array()),
				'name'				=> $objProduct->name,
				'quantity'			=> $objItems->quantity_sold,
				'price'				=> $this->Isotope->formatPriceWithCurrency($objItems->price),
				'total'				=> $this->Isotope->formatPriceWithCurrency(($objItems->price * $objItems->quantity_sold)),
				'href'				=> ($this->jumpTo ? $this->generateFrontendUrl($arrPage, '/product/'.$objItems->alias) : ''),
				'tax_id'			=> $objProduct->tax_id,
			);
		}
		
		
		$objTemplate->info = deserialize($objOrderData->checkout_info);
		$objTemplate->items = $arrItems;
		$objTemplate->downloads = $arrAllDownloads;
		$objTemplate->downloadsLabel = $GLOBALS['TL_LANG']['MSC']['downloadsLabel'];
		
		$objTemplate->raw = $objOrderData->row();
		
		$objTemplate->date = $this->parseDate($GLOBALS['TL_CONFIG']['dateFormat'], $objOrderData->date);
		$objTemplate->time = $this->parseDate($GLOBALS['TL_CONFIG']['timeFormat'], $objOrderData->date);
		$objTemplate->datim = $this->parseDate($GLOBALS['TL_CONFIG']['datimFormat'], $objOrderData->date);
		$objTemplate->datimLabel = $GLOBALS['TL_LANG']['MSC']['datimLabel'];
		
		$objTemplate->subTotalPrice = $this->Isotope->formatPriceWithCurrency($objOrderData->subTotal);
		$objTemplate->grandTotal = $this->Isotope->formatPriceWithCurrency($objOrderData->grandTotal);
		$objTemplate->subTotalLabel = $GLOBALS['TL_LANG']['MSC']['subTotalLabel'];
		$objTemplate->grandTotalLabel = $GLOBALS['TL_LANG']['MSC']['grandTotalLabel'];
		
		$arrSurcharges = array();
		foreach( deserialize($objOrderData->surcharges) as $arrSurcharge )
		{
			$arrSurcharges[] = array
			(
				'label'			=> $arrSurcharge['label'],
				'price'			=> $this->Isotope->formatPriceWithCurrency($arrSurcharge['price']),
				'total_price'	=> $this->Isotope->formatPriceWithCurrency($arrSurcharge['total_price']),
				'tax_id'		=> $arrSurcharge['tax_id'],
			);
		}
		
		$objTemplate->surcharges = $arrSurcharges;
		
		$objTemplate->billing_label = $GLOBALS['TL_LANG']['ISO']['billing_address'];
		$objTemplate->billing_address = $this->Isotope->generateAddressString(deserialize($objOrderData->billing_address), $this->Isotope->Store->billing_fields);
		if (strlen($objOrderData->shipping_method))
		{
			$arrShippingAddress = deserialize($objOrderData->shipping_address);
			if (!is_array($arrShippingAddress) || $arrShippingAddress['id'] == -1)
			{
				$objTemplate->has_shipping = false;
				$objTemplate->billing_label = $GLOBALS['TL_LANG']['ISO']['billing_shipping_address'];
			}
			else
			{
				$objTemplate->has_shipping = true;
				$objTemplate->shipping_label = $GLOBALS['TL_LANG']['ISO']['shipping_address'];
				$objTemplate->shipping_address = $this->Isotope->generateAddressString($arrShippingAddress, $this->Isotope->Store->shipping_fields);
			}
		}
		
		return $objTemplate->parse();
	}

	protected function getItems($intOrderId)
	{
		$arrItems = array();
		$objItems = $this->Database->prepare("SELECT p.*, o.*, t.downloads AS downloads_allowed, (SELECT COUNT(*) FROM tl_iso_order_downloads d WHERE d.pid=o.id) AS has_downloads FROM tl_iso_order_items o LEFT OUTER JOIN tl_iso_products p ON o.product_id=p.id LEFT OUTER JOIN tl_product_types t ON p.type=t.id WHERE o.pid=?")->execute($intOrderId);
		
		
		while( $objItems->next() )
		{
			// Do not use the TYPOlight function deserialize() cause it handles arrays not objects
			$objProduct = unserialize($objItems->product_data);
			
			if (!is_object($objProduct))
				continue;
			
			if ($objItems->downloads_allowed/* && $objItems->has_downlaods > 0*/)
			{
				$arrDownloads = array();
				$objDownloads = $this->Database->prepare("SELECT p.*, o.* FROM tl_iso_order_downloads o LEFT OUTER JOIN tl_iso_downloads p ON o.download_id=p.id WHERE o.pid=?")->execute($objItems->id);
				
				while( $objDownloads->next() )
				{
					// Send file to the browser
					if (strlen($this->Input->get('file')) && $this->Input->get('file') == $objDownloads->id && ($objDownloads->downloads_allowed == 0 || $objDownloads->downloads_remaining > 0))
					{
						if ($objDownloads->downloads_remaining > 0)
						{
							$this->Database->prepare("UPDATE tl_iso_order_downloads SET downloads_remaining=? WHERE id=?")->execute(($objDownloads->downloads_remaining-1), $objDownloads->id);
						}
						
						$this->sendFileToBrowser($objDownloads->singleSRC);
					}
					
					$arrDownload = array
					(
						'raw'			=> $objDownloads->row(),
						'title'			=> $objDownloads->title,
						'href'			=> ($this->generateFrontendUrl($objPage->row()) . '?uid=' . $this->Input->get('uid') . '&amp;file=' . $objDownloads->id),
						'remaining'		=> ($objDownloads->downloads_allowed > 0 ? sprintf('<br />%s Downloads verbleibend', intval($objDownloads->downloads_remaining)) : ''),
						'downloadable'	=> (($objDownloads->downloads_allowed == 0 || $objDownloads->downloads_remaining > 0) ? true : false),
					);
					
					$arrDownloads[] = $arrDownload;
					$arrAllDownloads[] = $arrDownload;
				}
			}
			
			$arrItems[] = array
			(
				'raw'			=> $objItems->row(),
				'downloads'		=> (is_array($arrDownloads) ? $arrDownloads : array()),
				'name'			=> $objProduct->name,
				'quantity'		=> $objItems->quantity_sold,
				'price'			=> $this->Isotope->formatPriceWithCurrency($objItems->price),
				'total'			=> $this->Isotope->formatPriceWithCurrency(($objItems->price * $objItems->quantity_sold)),
				'href'			=> ($this->jumpTo ? $this->generateFrontendUrl($arrPage, '/product/'.$objItems->alias) : ''),
				'tax_id'		=> $objProduct->tax_id,
			);
		}
		
		return $arrItems;
	}

	protected function createAddressString($arrOrderInfo, $strAddressType)
	{
		$strAddress = $arrOrderInfo[$strAddressType . '_information_firstname'];
		$strAddress .= ' ' . $arrOrderInfo[$strAddressType . '_information_lastname'];
		$strAddress = '<br />' . $arrOrderInfo[$strStep . '_information_company'];

		$strStreetAddress = $arrOrderInfo[$strAddressType . '_information_street_1'];
		$strStreetAddress .= $arrOrderInfo[$strAddressType . '_information_street_2'] ? '<br /> ' . $arrOrderInfo[$strAddressType . '_information_street_2'] : '';
		$strStreetAddress .= $arrOrderInfo[$strAddressType . '_information_street_3'] ? '<br /> ' . $arrOrderInfo[$strAddressType . '_information_street_3'] : '';				
		
		$strAddress = '<br />' . $strStreetAddress;

		$strAddress = '<br />' . $arrOrderInfo[$strAddressType . '_information_city'];
		
		$strAddress = $arrOrderInfo[$strAddressType . '_information_subdivision'] ? '<br /> ' . $arrOrderInfo[$strAddressType . '_information_subdivision'] : '';
		
		$strAddress = '<br />' . $arrOrderInfo[$strAddressType . '_information_postal'];
		$strAddress = '<br />' . $arrOrderInfo[$strAddressType . '_information_country'];
	
		return $strAddress;
	}
	
	
	protected function getOptionsHTML($arrOptionsData)
	{
        $strProductData .= '<p><strong>' . $GLOBALS['TL_LANG']['MSC']['productOptionsLabel'] . '</strong></p>';
	
		foreach($arrOptionsData as $option)
		{
			//$arrOptions = deserialize($row['options']);
        	
        	//if(sizeof($arrOptions))
        	//{
        		//foreach($arrOptions as $option)
        		//{
	        		$arrValues = $option['values'];
	        		
				    $strProductData .= '<ul>';
				   	$strProductData .= '	<li>' . $option['name'] . ': ';
				    $strProductData .= implode(', ', $arrValues);
					$strProductData .= '    </li>';     						
					$strProductData .= '</ul>'; 
				//}
			//}
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
		$strAddress .= $objAddress->street_1 . "<br />";
		$strAddress .= $objAddress->city . ', ' . $objAddress->subdivision . '  ' . $objAddress->postal . "<br />";
		$strAddress .= $objAddress->country;

		return $strAddress;
	}
	
	protected function generatePaymentInfoString($arrOrderInfo)
	{		
		$arrBillingInfoLines = split("\n",$arrOrderInfo['billing_address']);
			
		$strPaymentInfo = $GLOBALS['TL_LANG']['MSC']['iso_card_name_title'] . ': ' . $arrBillingInfoLines[0] . '<br />';
		//$strPaymentInfo .= in_array($arrOrderInfo['cc_type'], $GLOBALS['TL_LANG']['tl_iso_orders']['credit_card_types']) ? $GLOBALS['TL_LANG']['tl_iso_orders']['cc_type'][0] . ': ' . $GLOBALS['TL_LANG']['tl_iso_orders']['credit_card_types'][$arrOrderInfo['cc_type']] . '<br />' : NULL;
		$strPaymentInfo .= strlen($arrOrderInfo['cc_type']) ? $GLOBALS['ISO_PAY']['cc_types'][$arrOrderInfo['cc_type']] : NULL;
		$strPaymentInfo .= $GLOBALS['TL_LANG']['tl_iso_orders']['cc_num'][0] . ': XXXX-XXXX-XXXX-' . substr($arrOrderInfo['cc_num'], 12, 4) . '<br />';
		$strPaymentInfo .= $GLOBALS['TL_LANG']['tl_iso_orders']['cc_exp'][0] . ': ' . $arrOrderInfo['cc_exp'];
	
		return $strPaymentInfo;
	}
	
	protected function generateShippingInfoString($intShippingRateId)
	{
		$objShippingMethod = $this->Database->prepare("SELECT s.name, sr.description FROM tl_iso_shipping_modules s INNER JOIN tl_iso_shipping_options sr ON s.id=sr.pid  WHERE sr.id=?")
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
		
		$arrFieldsToDisplay = array(1, 4, 5, 7, 9, 10, 11, 14, 15, 16, 17, 18, 19, 20, 22, 23, 24);	//Dynamic Later
		
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
						case 5:
							$ftitle = "Authorization Code";
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

