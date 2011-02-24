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

	public function cleanCreditCardData($varCCNum, $intOrderId)
	{

		$strCCNum = str_replace(substr($varCCNum, 0, 12), 'XXXXXXXXXXXX', $varCCNum);

		$this->Database->prepare("UPDATE tl_iso_orders SET cc_num=? WHERE id=?")
					   ->execute($strCCNum, $intOrderId);

	}

	public function printInvoicesInterface()
	{
		$strMessage = '';

		$strReturn = '
<div id="tl_buttons">
<a href="'.ampersand(str_replace('&key=print_invoices', '', $this->Environment->request)).'" class="header_back" title="'.specialchars($GLOBALS['TL_LANG']['MSC']['backBT']).'">'.$GLOBALS['TL_LANG']['MSC']['backBT'].'</a>
</div>

<h2 class="sub_headline">'.$GLOBALS['TL_LANG']['tl_iso_orders']['print_invoices'][0].'</h2>
<form action="'.$this->Environment->request.'"  id="tl_print_invoices" class="tl_form" method="post">
<input type="hidden" name="FORM_SUBMIT" value="tl_print_invoices" />
<div class="tl_formbody_edit">
<div class="tl_tbox block">';

		$objWidget = new SelectMenu($this->prepareForWidget($GLOBALS['TL_DCA']['tl_iso_orders']['fields']['status'], 'status'));

		if($this->Input->post('FORM_SUBMIT')=='tl_print_invoices')
		{
			$varValue = $this->Input->post('status');

			$objOrders = $this->Database->query("SELECT id FROM tl_iso_orders WHERE status='$varValue'");

			if($objOrders->numRows)
			{
				$this->printInvoices($objOrders->fetchEach('id'));
			}
			else
			{
				$strMessage = '<div class="tl_error">'.$GLOBALS['TL_LANG']['MSC']['noOrders'].'</div>';
			}
		}

		return $strReturn .$objWidget->parse().$strMessage.'</div>
</div>
<div class="tl_formbody_submit">
<div class="tl_submit_container">
<input type="submit" name="print_invoices" id="ctrl_print_invoices" value="'.$GLOBALS['TL_LANG']['MSC']['labelSubmit'].'" />
</div>
</div>
</form>
</div>';
	}

	public function printInvoices($arrIds = array())
	{
		if(!count($arrIds))
			return;

		// Include library
		require_once(TL_ROOT . '/system/config/tcpdf.php');
		require_once(TL_ROOT . '/plugins/tcpdf/tcpdf.php');

		//Initial PDF setup
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
		//$pdf->AddPage();

		// TCPDF configuration
		$l['a_meta_dir'] = 'ltr';
		$l['a_meta_charset'] = $GLOBALS['TL_CONFIG']['characterSet'];
		$l['a_meta_language'] = $GLOBALS['TL_LANGUAGE'];
		$l['w_page'] = "page";

		// Set font
		$pdf->SetFont(PDF_FONT_NAME_MAIN, "", PDF_FONT_SIZE_MAIN);

		$strIds = implode(',', $arrIds);

		$objOrders = $this->Database->query("SELECT * FROM tl_iso_orders WHERE id IN($strIds)");

		while($objOrders->next())
		{
			$pdf->AddPage();
			$strArticle = '';

			$arrLinks = array();

			$arrChunks = array();

			$strArticle .= $this->generateContent($objOrders->uniqid);

			// Remove form elements
			$strArticle = preg_replace('/<form.*<\/form>/Us', '', $strArticle);
			$strArticle = preg_replace('/\?pdf=[0-9]*/i', '', $strArticle);

			preg_match_all('/<pre.*<\/pre>/Us', $strArticle, $arrChunks);

			foreach ($arrChunks[0] as $strChunk)
			{
				$strArticle = str_replace($strChunk, str_replace("\n", '<br />', $strChunk), $strArticle);
			}

			// Remove linebreaks and tabs
			$strArticle = str_replace(array("\n", "\t"), '', $strArticle);
			$strArticle = preg_replace('/<span style="text-decoration: ?underline;?">(.*)<\/span>/Us', '<u>$1</u>', $strArticle);

			// Write the HTML content
			$pdf->writeHTML($strArticle, true, 0, true, 0);

		}

		// Close and output PDF document
		$pdf->lastPage();
		$pdf->Output(standardize(ampersand($strInvoiceTitle, false), true) . '.pdf', 'D');
		$this->Isotope->resetConfig(true); 	//Set store back to default.

		ob_end_clean();
		exit;
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

		$strArticle .= $this->generateContent($objOrder->uniqid);

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
		$pdf->Output(standardize(ampersand($strInvoiceTitle, false), true) . '.pdf', 'D');

		$this->Isotope->resetConfig(true); 	//Set store back to default.

		ob_end_clean();
		exit;
	}

	protected function generateContent($varId)
	{
		$objOrderData = $this->Database->prepare("SELECT * FROM tl_iso_orders WHERE uniqid=?")->limit(1)->execute($varId);

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
		$this->Isotope->overrideConfig($objOrderData->config_id);

		// Invoice Logo
		$objInvoiceLogo = $this->Database->prepare("SELECT invoiceLogo FROM tl_iso_config WHERE id=?")
										 ->limit(1)
										 ->execute($objOrderData->config_id);

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
		$objItems = $this->Database->prepare("SELECT p.*, o.*, t.downloads AS downloads_allowed, t.class AS product_class, (SELECT COUNT(*) FROM tl_iso_order_downloads d WHERE d.pid=o.id) AS has_downloads FROM tl_iso_order_items o LEFT OUTER JOIN tl_iso_products p ON o.product_id=p.id LEFT OUTER JOIN tl_iso_producttypes t ON p.type=t.id WHERE o.pid=?")->execute($objOrderData->id);


		while( $objItems->next() )
		{
			$strClass = $GLOBALS['ISO_PRODUCT'][$objItems->product_class]['class'];

			if (!$this->classFileExists($strClass))
			{
				$strClass = 'IsotopeProduct';
			}

			$arrProduct = $objItems->row();
			$arrProduct['id'] = $objItems->product_id;
			unset($arrProduct['pid']);

			$objProduct = new $strClass($arrProduct, deserialize($objItems->product_options), true);

			$objProduct->quantity_requested = $objItems->product_quantity;
			$objProduct->cart_id = $objItems->id;

			//$objProduct->reader_jumpTo_Override = $objProducts->href_reader;

			if($objProduct->price==0)
				$objProduct->price = $objItems->price;

			$arrOptions = deserialize($objItems->product_options, true);

			$objProduct->setOptions($arrOptions);

			if (!is_object($objProduct))
				continue;

			$arrItems[] = array
			(
				'raw'				=> $objItems->row(),
				'product_options' 	=> $objProduct->getOptions(),
				'downloads'			=> (is_array($arrDownloads) ? $arrDownloads : array()),
				'name'				=> $objProduct->name,
				'quantity'			=> $objItems->product_quantity,
				'price'				=> $this->Isotope->formatPriceWithCurrency($objItems->price),
				'total'				=> $this->Isotope->formatPriceWithCurrency(($objItems->price * $objItems->product_quantity)),
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
		foreach( deserialize($objOrderData->surcharges, true) as $arrSurcharge )
		{
			if (!is_array($arrSurcharge))
				continue;

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
		$objTemplate->billing_address = $this->Isotope->generateAddressString(deserialize($objOrderData->billing_address), $this->Isotope->Config->billing_fields);
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
				$objTemplate->shipping_address = $this->Isotope->generateAddressString($arrShippingAddress, $this->Isotope->Config->shipping_fields);
			}
		}

		return $objTemplate->parse();
	}

	protected function getItems($intOrderId)
	{
		$arrItems = array();
		$objItems = $this->Database->prepare("SELECT p.*, o.*, t.downloads AS downloads_allowed, t.class AS product_class, (SELECT COUNT(*) FROM tl_iso_order_downloads d WHERE d.pid=o.id) AS has_downloads FROM tl_iso_order_items o LEFT OUTER JOIN tl_iso_products p ON o.product_id=p.id LEFT OUTER JOIN tl_iso_producttypes t ON p.type=t.id WHERE o.pid=?")->execute($intOrderId);


		while( $objItems->next() )
		{
			$strClass = $GLOBALS['ISO_PRODUCT'][$objItems->product_class]['class'];

			if (!$this->classFileExists($strClass))
			{
				$strClass = 'IsotopeProduct';
			}

			$objProduct = new $strClass($objItems->row());

			$objProduct->quantity_requested = $objItems->product_quantity;
			$objProduct->cart_id = $objItems->id;
			//$objProduct->reader_jumpTo_Override = $objProducts->href_reader;

			if($objProduct->price==0)
				$objProduct->price = $objItems->price;
			$objProduct->options = deserialize($objItems->product_options, true);

			if (!is_object($objProduct))
				continue;

			$arrItems[] = array
			(
				'raw'			=> $objItems->row(),
				'downloads'		=> (is_array($arrDownloads) ? $arrDownloads : array()),
				'name'			=> $objProduct->name,
				'quantity'		=> $objItems->product_quantity,
				'price'			=> $this->Isotope->formatPriceWithCurrency($objItems->price),
				'total'			=> $this->Isotope->formatPriceWithCurrency(($objItems->price * $objItems->product_quantity)),
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


	protected function loadAddress($varValue, $intId, $blnSaveAsBillingInfo = false)
	{
		$intPid = $this->getPid($intId, 'tl_iso_orders');

		$objAddress = $this->Database->prepare("SELECT * FROM tl_iso_addresses WHERE id=? and pid=?")
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

					$arrResponse[strtolower(standardize($ftitle, true))] = $fval;
				}

			$i++;
		}

		return $arrResponse;
	}

}

