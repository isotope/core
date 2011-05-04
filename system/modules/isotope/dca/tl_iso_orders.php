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


$this->loadDataContainer('tl_iso_products');
$this->loadLanguageFile('tl_iso_products');


/**
 * Table tl_iso_orders
 */
$GLOBALS['TL_DCA']['tl_iso_orders'] = array
(
	
	// Config
	'config' => array
	(
		'dataContainer'               => 'Table',
		'enableVersioning'            => false,
		'ctable'					  => array('tl_iso_order_items'),
		'closed'            		  => true,
		'onload_callback' 			  => array
		(
			array('tl_iso_orders', 'checkPermission'),
		),
	),
	
	// List
	'list' => array
	(
		'sorting' => array
		(
			'mode'                    => 2,
			'fields'                  => array('date DESC'),
			'flag'                    => 1,
			'panelLayout'             => 'filter;sort,search,limit'
		),
		'label' => array
		(
			'fields'                  => array('order_id'),
			'label'                   => '%s',
			'label_callback'          => array('tl_iso_orders', 'getOrderLabel')
		),
		'global_operations' => array
		(
			'tools' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_iso_orders']['tools'],
				'href'                => '',
				'class'               => 'header_isotope_tools',
				'attributes'          => 'onclick="Backend.getScrollOffset();" style="display:none"',
			),
			'export_emails' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_iso_orders']['export_emails'],
				'href'                => 'key=export_emails',
				'class'               => 'header_export_emails isotope-tools',
				'attributes'          => 'onclick="Backend.getScrollOffset();"'
			),
			'print_invoices' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_iso_orders']['print_invoices'],
				'href'                => 'key=print_invoices',
				'class'               => 'header_print_invoices isotope-tools',
				'attributes'          => 'onclick="Backend.getScrollOffset();"'
			)
		),
		'operations' => array
		(
			'edit' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_iso_orders']['edit'],
				'href'                => 'act=edit',
				'icon'                => 'edit.gif'
			),
			'delete' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_iso_orders']['delete'],
				'href'                => 'act=delete',
				'icon'                => 'delete.gif',
				'attributes'          => 'onclick="if (!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\')) return false; Backend.getScrollOffset();"'
			),
			'show' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_iso_orders']['show'],
				'href'                => 'act=show',
				'icon'                => 'show.gif'
			),
			'payment' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_iso_orders']['payment'],
				'href'                => 'key=payment',
				'icon'                => 'system/modules/isotope/html/icon-payment.png',
			),
			'shipping' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_iso_orders']['shipping'],
				'href'                => 'key=shipping',
				'icon'                => 'system/modules/isotope/html/icon-shipping.gif',
			),
			'print_order' => array
			(
				'label'			=> &$GLOBALS['TL_LANG']['tl_iso_orders']['print_order'],
				'href'			=> 'key=print_order',
				'icon'			=> 'system/modules/isotope/html/printer.png'
			),
			'buttons' => array
			(
				'button_callback'     => array('tl_iso_orders', 'moduleOperations'),
			)
		)
	),
	
	// Palettes
	'palettes' => array
	(
		'default'                     => '{status_legend},status,date_payed,date_shipped;{details_legend},details,notes',
	),
	
	// Fields
	'fields' => array
	(
		'id' => array
		(
			'eval'					=> array('doNotShow'=>true),
		),
		'pid' => array
		(
			'eval'					=> array('doNotShow'=>true),
		),

		'order_id' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_iso_orders']['order_id'],
			'search'				=> true,
			'sorting'				=> true,
		),
		'uniqid' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_iso_orders']['uniqid'],
			'search'				=> true,
		),
		'status' => array
		(
			'label'                 => &$GLOBALS['TL_LANG']['tl_iso_orders']['status'],
			'filter'                => true,
			'sorting'				=> true,
			'inputType'             => 'select',
			'options'         		=> $GLOBALS['ISO_ORDER'],
			'reference'         	=> &$GLOBALS['TL_LANG']['ORDER'],
		),
		'date' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_iso_orders']['date'],
			'flag'					=> 8,
			'filter'				=> true,
			'sorting'				=> true,
			'eval'					=> array('rgxp'=>'date', 'tl_class'=>'clr'),
		),
		'date_payed' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_iso_orders']['date_payed'],
			'inputType'				=> 'text',
			'eval'					=> array('rgxp'=>'date', 'datepicker'=>$this->getDatePickerString(), 'tl_class'=>'w50 wizard'),
		),
		'date_shipped' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_iso_orders']['date_shipped'],
			'inputType'				=> 'text',
			'eval'					=> array('rgxp'=>'date', 'datepicker'=>$this->getDatePickerString(), 'tl_class'=>'w50 wizard'),
		),
		'billing_address' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_iso_orders']['billing_address'],
			'search'				=> true,
		),
		'surcharges' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_iso_orders']['surcharges'],
			'inputType'				=> 'surchargeWizard',
			'save_callback'			=> array
			(
				array('tl_iso_orders','saveSurcharges')
			)
		),
		'details' => array
		(
			'input_field_callback'	=> array('tl_iso_orders', 'showDetails'),
		),
		'notes' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_iso_orders']['notes'],
			'inputType'				=> 'textarea',
			'eval'					=> array('style'=>'height:80px;')
		)
	)
);


/**
 * tl_iso_orders class.
 * 
 * @extends Backend
 */
class tl_iso_orders extends Backend
{

	public function __construct()
	{
		parent::__construct();
		
		$this->import('Isotope');
	}
	
	
	public function saveSurcharges($varValue, DataContainer $dc)
	{	
		
		$fltTaxTotal = 0.00;
		
		$arrTaxables = array();
	
		$arrSurcharges = deserialize($varValue);
				
		if(!is_array($arrSurcharges) || !count($arrSurcharges))
			return $varValue;
			
		$arrAddresses['shipping'] = deserialize($dc->activeRecord->shipping_address);
		$arrAddresses['billing'] = deserialize($dc->activeRecord->billing_address);
		
		foreach($arrSurcharges as $surcharge)
		{			
			if($surcharge['tax_class']>0)
			{
				$surcharge['before_tax'] = 1;
				$arrTaxables[] = $surcharge;
			}
		}
		
		foreach( $arrTaxables as $arrSurcharge )
		{
			$arrTax = array();
			
			//skip taxes.
			if(strpos($arrSurcharge['price'], '%')!==0)
			{
				$arrTax = $this->Isotope->calculateTax($arrSurcharge['tax_class'], $arrSurcharge['total_price'], $arrSurcharge['before_tax'], $arrAddresses);
			}	
			
			foreach($arrTax as $tax)
			{				
				$fltTaxTotal += $tax['total_price'];
			} 
		}	
		
		foreach($arrSurcharges as $row)
		{
			$arrSurchargePrices[] = array
			(
				'label' 		=> $row['label'],
				'total_price' 	=> $row['total_price'],
				'tax_class' 	=> $row['tax_class']
			);
			
			$arrTotalPrices[] = $row['total_price'];
		}
		
		//step 2: adjust order totals
		$fltGrandTotal = $dc->activeRecord->subTotal + array_sum($arrTotalPrices) + $fltTaxTotal;
		
		$this->Database->prepare("UPDATE tl_iso_orders SET grandTotal=? WHERE id=?")->execute($fltGrandTotal, $dc->id);

		return serialize($arrSurchargePrices);
	}
	
		
	/**
	 * Return a string of more buttons for the orders module.
	 * 
	 * @access public
	 * @param array $arrRow
	 * @return string
	 */
	//!@todo I don't think we need that...
	public function moduleOperations($arrRow)
	{
		if(!count($GLOBALS['ISO_ORDERS']['operations']))
		{
			return;
		}
		
		foreach($GLOBALS['ISO_ORDERS']['operations'] as $k=>$v)
		{
			
			
			$objPaymentType = $this->Database->prepare("SELECT type FROM tl_iso_payment_modules WHERE id=?")
											 ->limit(1)
											 ->execute($arrRow['payment_id']);
		
			if($objPaymentType->numRows && $objPaymentType->type==$k)
			{				
					$strClass = $v;
	
					if (!strlen($strClass) || !$this->classFileExists($strClass))
						return '';
						
					try 
					{
						$objModule = new $strClass($arrRow);
						$strButtons .= $objModule->moduleOperations($arrRow['id']);
					}
					catch (Exception $e) {}

			}
		}
		
		return $strButtons;
	}

		
	/**
	* getOrderLabel function.
	* 
	* @access public
	* @param array $row
	* @param string $label
	* @return string
	*/
	public function getOrderLabel($row, $label)
	{
		$this->Isotope->overrideConfig($row['config_id']);
		$strBillingAddress = $this->Isotope->generateAddressString(deserialize($row['billing_address']), $this->Isotope->Config->billing_fields);
		
		return '
<div style="float:left; width:90px">' . $row['order_id'] . '</div>
<div style="float:left; width:130px;">' . $this->parseDate($GLOBALS['TL_CONFIG']['datimFormat'], $row['date']) . '</div>
<div style="float:left; width:140px">' . substr($strBillingAddress, 0, strpos($strBillingAddress, '<br />')) . '</div>
<div style="float:left; width:80px; text-align:right; padding-right:20px">' . $this->Isotope->formatPriceWithCurrency($row['grandTotal']) . '</div>
<div style="float: left; width:80px">' . $GLOBALS['TL_LANG']['ORDER'][$row['status']] . '</div>';
	}
	
	
	public function showDetails($dc, $xlabel)
	{
		$objOrder = $this->Database->prepare("SELECT * FROM tl_iso_orders WHERE id=?")->limit(1)->execute($dc->id);
		
		if ($objOrder->numRows)
		{
			$GLOBALS['TL_HOOKS']['outputBackendTemplate'][] = array('tl_iso_orders', 'injectPrintCSS');
			
			return $this->getOrderDescription($objOrder->row());
		}
	
		return '';
	}
	
	
	protected function getOrderDescription($row)
	{
		$this->Input->setGet('uid', $row['uniqid']);
		$objModule = new ModuleIsotopeOrderDetails($this->Database->execute("SELECT * FROM tl_module WHERE type='iso_orderdetails'"));
		return $objModule->generate(true);
	}
	

	/**
	* Review order page stores temporary information in this table to know it when user is redirected to a payment provider. We do not show this data in backend.
	* 
	* @access public
	* @param object $dc
	* @return void
	*/
	public function checkPermission()
	{
		$this->import('BackendUser', 'User');
		
		$arrConfigs = $this->User->iso_configs;
		
		if ($this->User->isAdmin || (is_array($arrConfigs) && count($arrConfigs)))
		{
			$arrIds = $this->Database->execute("SELECT id FROM tl_iso_orders WHERE status!=''" . ($this->User->isAdmin ? '' : " AND config_id IN (".implode(',', $arrConfigs).")"))->fetchEach('id');
		}
		
		if (!count($arrIds))
			$arrIds = array(0);
		
		$GLOBALS['TL_DCA']['tl_iso_orders']['list']['sorting']['root'] = $arrIds;
		
		if (!$this->User->isAdmin)
		{
			unset($GLOBALS['TL_DCA']['tl_iso_orders']['list']['operations']['delete']);
			
			if ($this->Input->get('act') == 'delete' || (strlen($this->Input->get('id')) && !in_array($this->Input->get('id'), $arrIds)))
				$this->redirect($this->Environment->script.'?act=error');
		}
	}


	public function injectPrintCSS($strBuffer)
	{
		return str_replace('</head>', '<link rel="stylesheet" type="text/css" href="system/modules/isotope/html/print.css" media="print" />' . "\n</head>", $strBuffer);
	}
	
	
	//!@todo orders should be sorted, but by ID or date? also might want to respect user filter/search
	public function exportOrderEmails(DataContainer $dc)
	{
		if ($this->Input->get('key') != 'export_emails')
		{
			return '';
		}
		
		$arrExport = array();
		$objOrders = $this->Database->execute("SELECT billing_address FROM tl_iso_orders");

		while( $objOrders->next() )
		{
			$arrAddress = deserialize($objOrders->billing_address);
			
			if ($arrAddress['email'])
			{
				$arrExport[] = $arrAddress['firstname'] . ' ' . $arrAddress['lastname'] . ' <' . $arrAddress['email'] . '>';
			}
		}
		
		if (!count($arrExport))
		{
			return '
<div id="tl_buttons">
<a href="'.ampersand(str_replace('&key=export_emails', '', $this->Environment->request)).'" class="header_back" title="'.specialchars($GLOBALS['TL_LANG']['MSC']['backBT']).'">'.$GLOBALS['TL_LANG']['MSC']['backBT'].'</a>
</div>
<p class="tl_gerror">'. $GLOBALS['TL_LANG']['MSC']['noOrderEmails'] .'</p>';
		}

		header('Content-Type: application/csv');
		header('Content-Transfer-Encoding: binary');
		header('Content-Disposition: attachment; filename="isotope_order_emails_export_' . time() .'.csv"');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Pragma: public');
		header('Expires: 0');

		$output = '';
		
		foreach ($arrExport as $export) 
		{
			$output .= '"' . $export . '"' . "\n";
		}

		echo $output;
		exit;
	}
	
	
	public function paymentInterface($dc)
	{
		$objPayment = $this->Database->execute("SELECT p.* FROM tl_iso_payment_modules p, tl_iso_orders o WHERE p.id=o.payment_id AND o.id=".$dc->id);
		
		$strClass = $GLOBALS['ISO_PAY'][$objPayment->type];
		
		if (!$objPayment->numRows || !strlen($strClass) || !$this->classFileExists($strClass))
		{
			return '<p class="tl_gerror">'.$GLOBALS['TL_LANG']['ISO']['backendPaymentNotFound'].'</p>';
		}
		
		$objModule = new $strClass($objPayment->row());
		
		return $objModule->backendInterface($dc->id);
	}
	
	
	public function shippingInterface($dc)
	{
		$objShipping = $this->Database->execute("SELECT p.* FROM tl_iso_shipping_modules p, tl_iso_orders o WHERE p.id=o.shipping_id AND o.id=".$dc->id);
		
		$strClass = $GLOBALS['ISO_SHIP'][$objShipping->type];
		
		if (!$objShipping->numRows || !strlen($strClass) || !$this->classFileExists($strClass))
		{
			return '<p class="tl_gerror">'.$GLOBALS['TL_LANG']['ISO']['backendShippingNotFound'].'</p>';
		}
		
		$objModule = new $strClass($objShipping->row());
		
		return $objModule->backendInterface($dc->id);
	}

	
	/**
	 * Provide a select menu to choose orders by status and print PDF
	 *
	 * @param  object
	 * @return string
	 */
	public function printInvoices()
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
	
		if ($this->Input->post('FORM_SUBMIT') == 'tl_print_invoices')
		{
			$objOrders = $this->Database->prepare("SELECT id FROM tl_iso_orders WHERE status=?")->execute($this->Input->post('status'));
				
			if ($objOrders->numRows)
			{
				$this->generateInvoices($objOrders->fetchEach('id'));
			}
			else
			{
				$strMessage = '<p class="tl_gerror">'.$GLOBALS['TL_LANG']['MSC']['noOrders'].'</p>';
			}
		}	
	
		return $strReturn . $strMessage . $objWidget->parse() . '
</div>
</div>
<div class="tl_formbody_submit">
<div class="tl_submit_container">
<input type="submit" name="print_invoices" id="ctrl_print_invoices" value="'.$GLOBALS['TL_LANG']['MSC']['labelSubmit'].'" />
</div>
</div>
</form>
</div>';
	}
	
	
	/**
	 * Print one order as PDF
	 *
	 * @param  object
	 * @return void
	 */
	public function printInvoice(DataContainer $dc)
	{
		$this->generateInvoices(array($dc->id));
	}


	/**
	 * Generate one or multiple PDFs by order ID
	 *
	 * @param  array
	 * @return void
	 */
	public function generateInvoices(array $arrIds)
	{
		$this->import('Isotope');

		if(!count($arrIds))
		{
			$this->log('No order IDs passed to method.', __METHOD__, TL_ERROR);
			$this->redirect($this->Environment->script . '?act=error');
		}
		
		// TCPDF configuration
		$l['a_meta_dir'] = 'ltr';
		$l['a_meta_charset'] = $GLOBALS['TL_CONFIG']['characterSet'];
		$l['a_meta_language'] = $GLOBALS['TL_LANGUAGE'];
		$l['w_page'] = 'page';

		// Include library
		require_once(TL_ROOT . '/system/config/tcpdf.php');
		require_once(TL_ROOT . '/plugins/tcpdf/tcpdf.php');

		// Create new PDF document
		$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true);

		// Set document information
		$pdf->SetCreator(PDF_CREATOR);
		$pdf->SetAuthor(PDF_AUTHOR);

// @todo $objInvoice is not defined
//		$pdf->SetTitle($objInvoice->title);
//		$pdf->SetSubject($objInvoice->title);
//		$pdf->SetKeywords($objInvoice->keywords);

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

		// Set font
		$pdf->SetFont(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN);
		
		foreach( $arrIds as $intId )
		{
			$pdf->AddPage();
			
			$strArticle = $this->generateInvoiceContent($intId);
	
			// Write the HTML content
			$pdf->writeHTML($strArticle, true, 0, true, 0);
		}	
		
		// Close and output PDF document
		// @todo $strInvoiceTitle is not defined
		$pdf->lastPage();
		$pdf->Output(standardize(ampersand($strInvoiceTitle, false), true) . '.pdf', 'D');
		
		// Set store back to default
		// @todo do we need that? The PHP session is ended anyway...
		$this->Isotope->resetConfig(true);
		
		// Stop script execution
		exit;
	}


	/**
	 * Generate content for an order based on iso_invoice.tpl
	 *
	 * @param  int
	 * @return string
	 */
	public function generateInvoiceContent($intId)
	{
		$objOrder = new IsotopeOrder();
		
		if (!$objOrder->findBy('id', $intId))
		{
			$this->log('Could not find order with ID ' . $intId, __METHOD__, TL_ERROR);
			$this->redirect($this->Environment->script . '?act=error');
		}
		
		// Set global config to this order
		$this->Isotope->overrideConfig($objOrder->config_id);
		
		$objTemplate = new BackendTemplate('iso_invoice');
		$objTemplate->setData($objOrder->getData());
		$objTemplate->logoImage = '';
		
		if ($this->Isotope->Config->invoiceLogo != '' && is_file(TL_ROOT . '/' . $this->Isotope->Config->invoiceLogo))
		{
			$objTemplate->logoImage = str_replace('src="', 'src="' . TL_PATH . '/', $this->generateImage($this->Isotope->Config->invoiceLogo));
		}
		
		$objTemplate->invoiceTitle = $GLOBALS['TL_LANG']['MSC']['iso_invoice_title'] . ' ' . $objOrder->order_id . ' – ' . date($GLOBALS['TL_CONFIG']['datimFormat'], $objOrder->date);
		
		$arrItems = array();
		$arrProducts = $objOrder->getProducts();
		
		foreach( $arrProducts as $objProduct )
		{
			$arrItems[] = array
			(
				'raw'				=> $objProduct->getData(),
				'product_options' 	=> $objProduct->getOptions(),
				'name'				=> $objProduct->name,
				'quantity'			=> $objProduct->quantity_requested,
				'price'				=> $objProduct->formatted_price,
				'total'				=> $objProduct->formatted_total_price,
				'tax_id'			=> $objProduct->tax_id,
			);
		}
		
	
		$objTemplate->info = deserialize($objOrder->checkout_info);
		$objTemplate->items = $arrItems;
		
		$objTemplate->raw = $objOrder->getData();
		
		$objTemplate->date = $this->parseDate($GLOBALS['TL_CONFIG']['dateFormat'], $objOrder->date);
		$objTemplate->time = $this->parseDate($GLOBALS['TL_CONFIG']['timeFormat'], $objOrder->date);
		$objTemplate->datim = $this->parseDate($GLOBALS['TL_CONFIG']['datimFormat'], $objOrder->date);
		$objTemplate->datimLabel = $GLOBALS['TL_LANG']['MSC']['datimLabel'];
		
		$objTemplate->subTotalPrice = $this->Isotope->formatPriceWithCurrency($objOrder->subTotal);
		$objTemplate->grandTotal = $this->Isotope->formatPriceWithCurrency($objOrder->grandTotal);
		$objTemplate->subTotalLabel = $GLOBALS['TL_LANG']['MSC']['subTotalLabel'];
		$objTemplate->grandTotalLabel = $GLOBALS['TL_LANG']['MSC']['grandTotalLabel'];
		
		$arrSurcharges = array();
		foreach( deserialize($objOrder->surcharges, true) as $arrSurcharge )
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
		$objTemplate->billing_address = $this->Isotope->generateAddressString(deserialize($objOrder->billing_address), $this->Isotope->Config->billing_fields);
		if (strlen($objOrder->shipping_method))
		{
			$arrShippingAddress = deserialize($objOrder->shipping_address);
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
		
		
		$strArticle = $this->replaceInsertTags($objTemplate->parse());
		$strArticle = html_entity_decode($strArticle, ENT_QUOTES, $GLOBALS['TL_CONFIG']['characterSet']);
		$strArticle = $this->convertRelativeUrls($strArticle, '', true);

		// Remove form elements and JavaScript links
		$arrSearch = array
		(
			'@<form.*</form>@Us',
			'@<a [^>]*href="[^"]*javascript:[^>]+>.*</a>@Us'
		);

		$strArticle = preg_replace($arrSearch, '', $strArticle);
		
		// Handle line breaks in preformatted text
		$strArticle = preg_replace_callback('@(<pre.*</pre>)@Us', 'nl2br_callback', $strArticle);

		// Default PDF export using TCPDF
		$arrSearch = array
		(
			'@<span style="text-decoration: ?underline;?">(.*)</span>@Us',
			'@(<img[^>]+>)@',
			'@(<div[^>]+block[^>]+>)@',
			'@[\n\r\t]+@',
			'@<br /><div class="mod_article@',
			'@href="([^"]+)(pdf=[0-9]*(&|&amp;)?)([^"]*)"@'
		);

		$arrReplace = array
		(
			'<u>$1</u>',
			'<br />$1',
			'<br />$1',
			' ',
			'<div class="mod_article',
			'href="$1$4"'
		);

		$strArticle = preg_replace($arrSearch, $arrReplace, $strArticle);
		
		return $strArticle;
	}
}

