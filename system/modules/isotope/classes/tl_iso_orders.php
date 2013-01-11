<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2012 Isotope eCommerce Workgroup
 *
 * @package    Isotope
 * @link       http://www.isotopeecommerce.com
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 *
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
 * @author     Christian de la Haye <service@delahaye.de>
 */

namespace Isotope;

use Isotope\Product\Collection\Order;


/**
 * Class tl_iso_orders
 * Provide miscellaneous methods that are used by the data configuration array.
 */
class tl_iso_orders extends \Backend
{

    /**
     * Import an Isotope object
     */
    public function __construct()
    {
        parent::__construct();
        $this->import('Isotope\Isotope', 'Isotope');
    }


    /**
     * Generate the order label and return it as string
     * @param array
     * @param string
     * @return string
     */
    public function getOrderLabel($row, $label, \DataContainer $dc, $args)
    {
        $this->Isotope->overrideConfig($row['config_id']);

        $objAddress = new \Isotope\Model\Address();
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

        $GLOBALS['TL_CSS'][] = 'system/modules/isotope/assets/print.min.css|print';

        // Generate a regular order details module
        \Input::setGet('uid', $objOrder->uniqid);
        $objModule = new \Isotope\Module\OrderDetails($this->Database->execute("SELECT * FROM tl_module WHERE type='iso_orderdetails'"));

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
        if (\Input::get('act') == 'delete' || \Input::get('act') == 'deleteAll')
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

        if (\Input::get('id') != '' && !in_array(\Input::get('id'), $arrIds))
        {
            $this->log('Trying to access disallowed order ID '.\Input::get('id'), __METHOD__, TL_ERROR);
            $this->redirect($this->Environment->script.'?act=error');
        }
    }


    /**
     * Export order e-mails and send them to browser as file
     * @param DataContainer
     * @return string
     * @todo orders should be sorted, but by ID or date? also might want to respect user filter/search
     */
    public function exportOrderEmails(\DataContainer $dc)
    {
        if (\Input::get('key') != 'export_emails')
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
        $objPayment = $this->Database->execute("SELECT p.* FROM tl_iso_payment_modules m, tl_iso_orders o WHERE m.id=o.payment_id AND m.id=".$dc->id);

        try {
            $objMethod = \Isotope\Factory\Payment::build($objPayment->type, $objPayment->row());

            return $objMethod->backendInterface($dc->id);
        } catch (Exception $e) {
            return '<p class="tl_gerror">'.$GLOBALS['TL_LANG']['ISO']['backendPaymentNotFound'].'</p>';
        }
    }


    /**
     * Generate a shipping interface and return it as HTML string
     * @param object
     * @return string
     */
    public function shippingInterface($dc)
    {
        $objShipping = $this->Database->execute("SELECT p.* FROM tl_iso_shipping_modules m, tl_iso_orders o WHERE m.id=o.shipping_id AND m.id=".$dc->id);

        try {
            $objMethod = \Isotope\Factory\Payment::build($objShipping->type, $objShipping->row());

            return $objMethod->backendInterface($dc->id);
        } catch (Exception $e) {
            return '<p class="tl_gerror">'.$GLOBALS['TL_LANG']['ISO']['backendShippingNotFound'].'</p>';
        }
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

        $objWidget = new \SelectMenu($this->prepareForWidget($GLOBALS['TL_DCA']['tl_iso_orders']['fields']['status'], 'status'));

        if (\Input::post('FORM_SUBMIT') == 'tl_print_invoices')
        {
            $objOrders = $this->Database->prepare("SELECT id FROM tl_iso_orders WHERE status=?")->execute(\Input::post('status'));

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
    public function printInvoice(\DataContainer $dc)
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
        if (empty($arrIds))
        {
            $this->log('No order IDs passed to method.', __METHOD__, TL_ERROR);
            $this->redirect('contao/main.php?act=error');
        }

        $pdf = null;

        foreach ($arrIds as $intId)
        {
            if (($objOrder = Order::findByPk($intId)) !== null)
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
            if (($objOrder = Order::findByPk($dc->id)) !== null)
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
        if (($objOrder = Order::findByPk($dc->id)) !== null)
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
