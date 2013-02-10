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
 * @copyright  Isotope eCommerce Workgroup 2009-2012
 * @author     Andreas Schempp <andreas@schempp.ch>
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
 * @author     Christian de la Haye <service@delahaye.de>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */


/**
 * Load tl_iso_products data container and language files
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
		'ptable'					  => ($_GET['act'] == 'delete' ? 'tl_member' : ''), // See #70
		'ctable'					  => array('tl_iso_order_items'),
		'closed'            		  => true,
		'onload_callback' 			  => array
		(
			array('tl_iso_orders', 'checkPermission'),
		),
		'onsubmit_callback' => array
		(
			array('tl_iso_orders', 'executeSaveHook'),
		),
	),

	// List
	'list' => array
	(
		'sorting' => array
		(
			'mode'					=> 2,
			'fields'				=> array('date DESC'),
			'panelLayout'			=> 'filter;sort,search,limit',
			'filter'				=> array(array('status>?', '0')),
		),
		'label' => array
		(
			'fields'				=> array('order_id', 'date', 'billing_address', 'grandTotal', 'status'),
			'showColumns'			=> true,
			'label_callback'		=> array('tl_iso_orders', 'getOrderLabel')
		),
		'global_operations' => array
		(
			'all' => array
			(
				'label'				=> &$GLOBALS['TL_LANG']['MSC']['all'],
				'href'				=> 'act=select',
				'class'				=> 'header_edit_all',
				'attributes'		=> 'onclick="Backend.getScrollOffset();"'
			),
			'tools' => array
			(
				'label'				=> &$GLOBALS['TL_LANG']['tl_iso_orders']['tools'],
				'href'				=> '',
				'class'				=> 'header_isotope_tools',
				'attributes'		=> 'onclick="Backend.getScrollOffset();" style="display:none"',
			),
			'export_emails' => array
			(
				'label'				=> &$GLOBALS['TL_LANG']['tl_iso_orders']['export_emails'],
				'href'				=> 'key=export_emails',
				'class'				=> 'header_iso_export_csv isotope-tools',
				'attributes'		=> 'onclick="Backend.getScrollOffset();"'
			),
			'print_invoices' => array
			(
				'label'				=> &$GLOBALS['TL_LANG']['tl_iso_orders']['print_invoices'],
				'href'				=> 'key=print_invoices',
				'class'				=> 'header_print_invoices isotope-tools',
				'attributes'		=> 'onclick="Backend.getScrollOffset();"'
			)
		),
		'operations' => array
		(
			'edit' => array
			(
				'label'				=> &$GLOBALS['TL_LANG']['tl_iso_orders']['edit'],
				'href'				=> 'act=edit',
				'icon'				=> 'edit.gif'
			),
			'delete' => array
			(
				'label'				=> &$GLOBALS['TL_LANG']['tl_iso_orders']['delete'],
				'href'				=> 'act=delete',
				'icon'				=> 'delete.gif',
				'attributes'		=> 'onclick="if (!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\')) return false; Backend.getScrollOffset();"'
			),
			'info' => array
			(
				'label'				=> &$GLOBALS['TL_LANG']['tl_iso_orders']['info'],
				'icon'				=> 'show.gif',
				'attributes'		=> 'class="invisible isotope-contextmenu"',
			),
			'show' => array
			(
				'label'				=> &$GLOBALS['TL_LANG']['tl_iso_orders']['show'],
				'href'				=> 'act=show',
				'icon'				=> 'show.gif',
				'attributes'		=> 'class="isotope-tools"',
			),
			'payment' => array
			(
				'label'				=> &$GLOBALS['TL_LANG']['tl_iso_orders']['payment'],
				'href'				=> 'key=payment',
				'icon'				=> 'system/modules/isotope/html/money-coin.png',
				'attributes'		=> 'class="isotope-tools"',
			),
			'shipping' => array
			(
				'label'				=> &$GLOBALS['TL_LANG']['tl_iso_orders']['shipping'],
				'href'				=> 'key=shipping',
				'icon'				=> 'system/modules/isotope/html/box-label.png',
				'attributes'		=> 'class="isotope-tools"',
			),
			'print_order' => array
			(
				'label'				=> &$GLOBALS['TL_LANG']['tl_iso_orders']['print_order'],
				'href'				=> 'key=print_order',
				'icon'				=> 'system/modules/isotope/html/document-pdf-text.png'
			),
		)
	),

	// Palettes
	'palettes' => array
	(
		'default'					=> '{status_legend},status,date_paid,date_shipped;{details_legend},details,notes;{email_legend:hide},email_data;{billing_address_legend:hide},billing_address_data;{shipping_address_legend:hide},shipping_address_data',
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
			'exclude'               => true,
			'filter'                => true,
			'sorting'				=> true,
			'inputType'             => 'select',
			'options'         		=> IsotopeBackend::getOrderStatus(),
			'save_callback'			=> array
			(
				array('tl_iso_orders', 'updateStatus'),
			),
		),
		'date' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_iso_orders']['date'],
			'flag'					=> 8,
			'filter'				=> true,
			'sorting'				=> true,
			'eval'					=> array('rgxp'=>'date', 'tl_class'=>'clr'),
		),
		'date_paid' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_iso_orders']['date_paid'],
			'exclude'               => true,
			'inputType'				=> 'text',
			'eval'					=> array('rgxp'=>'date', 'datepicker'=>(method_exists($this,'getDatePickerString') ? $this->getDatePickerString() : true), 'tl_class'=>'w50 wizard'),
		),
		'date_shipped' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_iso_orders']['date_shipped'],
			'exclude'               => true,
			'inputType'				=> 'text',
			'eval'					=> array('rgxp'=>'date', 'datepicker'=>(method_exists($this,'getDatePickerString') ? $this->getDatePickerString() : true), 'tl_class'=>'w50 wizard'),
		),
		'config_id' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_iso_orders']['config_id'],
			'foreignKey'			=> 'tl_iso_config.name',
		),
		'payment_id' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_iso_orders']['payment_id'],
			'filter'				=> true,
			'foreignKey'			=> 'tl_iso_payment_modules.name',
		),
		'shipping_id' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_iso_orders']['shipping_id'],
			'filter'				=> true,
			'foreignKey'			=> 'tl_iso_shipping_modules.name',
		),
		'billing_address' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_iso_orders']['billing_address'],
			'search'				=> true,
		),
		'details' => array
		(
			'input_field_callback'	=> array('tl_iso_orders', 'generateOrderDetails'),
			'eval'					=> array('doNotShow'=>true),
		),
		'grandTotal' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['MSC']['grandTotalLabel'],
		),
		'notes' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_iso_orders']['notes'],
			'exclude'               => true,
			'inputType'				=> 'textarea',
			'eval'					=> array('style'=>'height:80px;')
		),
		'email_data' => array
		(
			'input_field_callback'	=> array('tl_iso_orders', 'generateEmailData'),
			'eval'					=> array('doNotShow'=>true),
		),
		'billing_address_data' => array
		(
			'input_field_callback'	=> array('tl_iso_orders', 'generateBillingAddressData'),
			'eval'					=> array('doNotShow'=>true),
		),
		'shipping_address_data' => array
		(
			'input_field_callback'	=> array('tl_iso_orders', 'generateShippingAddressData'),
			'eval'					=> array('doNotShow'=>true),
		),
	)
);


/**
 * Class tl_iso_orders
 * Provide miscellaneous methods that are used by the data configuration array.
 */
class tl_iso_orders extends Backend
{

	/**
	 * Import an Isotope object
	 */
	public function __construct()
	{
		parent::__construct();
		$this->import('Isotope');
	}


	/**
	 * Generate the order label and return it as string
	 * @param array
	 * @param string
	 * @return string
	 */
	public function getOrderLabel($row, $label, DataContainer $dc, $args)
	{
		$this->Isotope->overrideConfig($row['config_id']);

		$objAddress = new IsotopeAddressModel();
		$objAddress->setData(deserialize($row['billing_address'], true));
		$arrTokens = $objAddress->getTokens($this->Isotope->Config->billing_fields);

		$args[2] = $arrTokens['hcard_fn'];
		$args[3] = $this->Isotope->formatPriceWithCurrency($row['grandTotal']);

		return $args;
	}


	/**
	 * Generate the order details view when editing an order
	 * @param object
	 * @param string
	 * @return string
	 */
	public function generateOrderDetails($dc, $xlabel)
	{
		$objOrder = $this->Database->execute("SELECT * FROM tl_iso_orders WHERE id=".$dc->id);

		if (!$objOrder->numRows)
		{
			$this->redirect('contao/main.php?act=error');
		}

		$GLOBALS['TL_CSS'][] = 'system/modules/isotope/html/print.css|print';

		// Generate a regular order details module
		$this->Input->setGet('uid', $objOrder->uniqid);
		$objModule = new ModuleIsotopeOrderDetails($this->Database->execute("SELECT * FROM tl_module WHERE type='iso_orderdetails'"));
		return $objModule->generate(true);
	}


	/**
	 * Generate the order details view when editing an order
	 * @param object
	 * @param string
	 * @return string
	 */
	public function generateEmailData($dc, $xlabel)
	{
		$objOrder = $this->Database->execute("SELECT * FROM tl_iso_orders WHERE id=" . $dc->id);

		if (!$objOrder->numRows)
		{
			$this->redirect('contao/main.php?act=error');
		}

		$arrSettings = deserialize($objOrder->settings, true);

		if (!is_array($arrSettings['email_data']))
		{
			return '<div class="tl_gerror">No email data available.</div>';
		}

		$strBuffer = '
<div>
<table cellpadding="0" cellspacing="0" class="tl_show" summary="Table lists all details of an entry" style="width:650px">
  <tbody>';

		$i=0;

		foreach ($arrSettings['email_data'] as $k => $v)
		{
			$strClass = ++$i%2 ? '' : ' class="tl_bg"';

			if (is_array($v))
			{
				$strValue = implode(', ', $v);
			}
			else
			{
				$strValue = ((strip_tags($v) == $v) ? nl2br($v) : $v);
			}

			$strBuffer .= '
  <tr>
    <td' . $strClass . ' style="vertical-align:top"><span class="tl_label">'.$k.': </span></td>
    <td' . $strClass . '>'.$strValue.'</td>
  </tr>';
		}

		$strBuffer .= '
</tbody></table>
</div>';

		return $strBuffer;
	}


	/**
	 * Generate the billing address details
	 * @param object
	 * @param string
	 * @return string
	 */
	public function generateBillingAddressData($dc, $xlabel)
	{
		return $this->generateAddressData($dc->id, 'billing_address');
	}


	/**
	 * Generate the shipping address details
	 * @param object
	 * @param string
	 * @return string
	 */
	public function generateShippingAddressData($dc, $xlabel)
	{
		return $this->generateAddressData($dc->id, 'shipping_address');
	}


	/**
	 * Generate address details amd return it as string
	 * @param integer
	 * @param string
	 * @return string
	 */
	protected function generateAddressData($intId, $strField)
	{
		$objOrder = $this->Database->execute("SELECT * FROM tl_iso_orders WHERE id=".$intId);

		if (!$objOrder->numRows)
		{
			$this->redirect('contao/main.php?act=error');
		}

		$arrAddress = deserialize($objOrder->$strField, true);

		if (!is_array($arrAddress))
		{
			return '<div class="tl_gerror">No address data available.</div>';
		}

		$this->loadLanguageFile('tl_iso_addresses');
		$this->loadDataContainer('tl_iso_addresses');

		$strBuffer = '
<div>
<table cellpadding="0" cellspacing="0" class="tl_show" summary="Table lists all details of an entry" style="width:650px">
  <tbody>';

		$i=0;

		foreach ($GLOBALS['TL_DCA']['tl_iso_addresses']['fields'] as $k => $v)
		{
			if (!isset($arrAddress[$k]))
			{
				continue;
			}

			$v = $arrAddress[$k];
			$strClass = (++$i % 2) ? '' : ' class="tl_bg"';

			$strBuffer .= '
  <tr>
    <td' . $strClass . ' style="vertical-align:top"><span class="tl_label">'.$this->Isotope->formatLabel('tl_iso_addresses', $k).': </span></td>
    <td' . $strClass . '>'.$this->Isotope->formatValue('tl_iso_addresses', $k, $v).'</td>
  </tr>';
		}

		$strBuffer .= '
</tbody></table>
</div>';

		return $strBuffer;
	}


	/**
	* Review order page stores temporary information in this table to know it when user is redirected to a payment provider. We do not show this data in backend.
	* @param object
	* @return void
	*/
	public function checkPermission($dc)
	{
		$this->import('BackendUser', 'User');

		if ($this->User->isAdmin)
		{
			return;
		}

		// Only admins can delete orders. Others should set the status to cancelled.
		unset($GLOBALS['TL_DCA']['tl_iso_orders']['list']['operations']['delete']);
		if ($this->Input->get('act') == 'delete' || $this->Input->get('act') == 'deleteAll')
		{
			$this->log('Only admin can delete orders!', __METHOD__, TL_ERROR);
			$this->redirect('contao/main.php?act=error');
		}

		$arrIds = array(0);
		$arrConfigs = $this->User->iso_configs;

		if (is_array($arrConfigs) && !empty($arrConfigs))
		{
			$objOrders = $this->Database->query("SELECT id FROM tl_iso_orders WHERE config_id IN (" . implode(',', $arrConfigs) . ")");

			if ($objOrders->numRows)
			{
				$arrIds = $objOrders->fetchEach('id');
			}
		}

		$GLOBALS['TL_DCA']['tl_iso_orders']['list']['sorting']['root'] = $arrIds;

		if ($this->Input->get('id') != '' && !in_array($this->Input->get('id'), $arrIds))
		{
			$this->log('Trying to access disallowed order ID '.$this->Input->get('id'), __METHOD__, TL_ERROR);
			$this->redirect($this->Environment->script.'?act=error');
		}
	}


	/**
	 * Export order e-mails and send them to browser as file
	 * @param DataContainer
	 * @return string
	 * @todo orders should be sorted, but by ID or date? also might want to respect user filter/search
	 */
	public function exportOrderEmails(DataContainer $dc)
	{
		if ($this->Input->get('key') != 'export_emails')
		{
			return '';
		}

		$arrExport = array();
		$objOrders = $this->Database->execute("SELECT billing_address FROM tl_iso_orders");

		while ($objOrders->next())
		{
			$arrAddress = deserialize($objOrders->billing_address);

			if ($arrAddress['email'])
			{
				$arrExport[] = $arrAddress['firstname'] . ' ' . $arrAddress['lastname'] . ' <' . $arrAddress['email'] . '>';
			}
		}

		if (empty($arrExport))
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


	/**
	 * Generate a payment interface and return it as HTML string
	 * @param object
	 * @return string
	 */
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


	/**
	 * Generate a shipping interface and return it as HTML string
	 * @param object
	 * @return string
	 */
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
<input type="hidden" name="FORM_SUBMIT" value="tl_print_invoices">
<input type="hidden" name="REQUEST_TOKEN" value="'.REQUEST_TOKEN.'">
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
<input type="submit" name="print_invoices" id="ctrl_print_invoices" value="'.$GLOBALS['TL_LANG']['MSC']['labelSubmit'].'">
</div>
</div>
</form>
</div>';
	}


	/**
	 * Print one order as PDF
	 * @param DataContainer
	 * @return void
	 */
	public function printInvoice(DataContainer $dc)
	{
		$this->generateInvoices(array($dc->id));
	}


	/**
	 * Generate one or multiple PDFs by order ID
	 * @param array
	 * @return void
	 */
	public function generateInvoices(array $arrIds)
	{
		$this->import('Isotope');

		if (empty($arrIds))
		{
			$this->log('No order IDs passed to method.', __METHOD__, TL_ERROR);
			$this->redirect('contao/main.php?act=error');
		}

		$pdf = null;

		foreach ($arrIds as $intId)
		{
			$objOrder = new IsotopeOrder();

			if ($objOrder->findBy('id', $intId))
			{
				$pdf = $objOrder->generatePDF(null, $pdf, false);
			}
		}

		if (!$pdf)
		{
			$this->log('No order IDs passed to method.', __METHOD__, TL_ERROR);
			$this->redirect('contao/main.php?act=error');
		}

		// Close and output PDF document
		$pdf->lastPage();

		// @todo make things like this configurable in a further version of Isotope
		$strInvoiceTitle = 'invoice_' . $objOrder->order_id;
		$pdf->Output(standardize(ampersand($strInvoiceTitle, false), true) . '.pdf', 'D');

		// Set config back to default
		// @todo do we need that? The PHP session is ended anyway...
		$this->Isotope->resetConfig(true);

		// Stop script execution
		exit;
	}


	/**
	 * Trigger order status update when changing the status in the backend
	 * @param string
	 * @param DataContainer
	 * @return string
	 * @link http://www.contao.org/callbacks.html#save_callback
	 */
	public function updateStatus($varValue, $dc)
	{
		if ($dc->activeRecord && $dc->activeRecord->status != $varValue)
		{
			$objOrder = new IsotopeOrder();

			if ($objOrder->findBy('id', $dc->id))
			{
				// Status update has been cancelled, do not update
				if (!$objOrder->updateOrderStatus($varValue))
				{
					return $dc->activeRecord->status;
				}
			}
		}

		return $varValue;
	}


	/**
	 * Execute the saveCollection hook when an order is saved
	 * @param object
	 * @return void
	 */
	public function executeSaveHook($dc)
	{
		$objOrder = new IsotopeOrder();

		if ($objOrder->findBy('id', $dc->id))
		{
			// !HOOK: add additional functionality when saving collection
			if (isset($GLOBALS['ISO_HOOKS']['saveCollection']) && is_array($GLOBALS['ISO_HOOKS']['saveCollection']))
			{
				foreach ($GLOBALS['ISO_HOOKS']['saveCollection'] as $callback)
				{
					$this->import($callback[0]);
					$this->$callback[0]->$callback[1]($objOrder);
				}
			}
		}
	}
}

