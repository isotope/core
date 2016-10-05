<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2014 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @package    Isotope
 * @link       http://isotopeecommerce.org
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Model\Payment;

use Isotope\Interfaces\IsotopePayment;
use Isotope\Interfaces\IsotopePostsale;
use Isotope\Interfaces\IsotopeProductCollection;
use Isotope\Model\Payment;
use Isotope\Model\ProductCollection\Order;


/**
 * Class PSP
 *
 * Handle PSP payments
 *
 * @property string psp_pspid
 * @property string psp_http_method
 * @property string psp_hash_method
 * @property string psp_hash_in
 * @property string psp_hash_out
 * @property string psp_dynamic_template
 */
abstract class PSP extends Payment implements IsotopePayment, IsotopePostsale
{

    /**
     * Process payment on checkout page.
     *
     * @param IsotopeProductCollection|Order $objOrder  The order being places
     * @param \Module                        $objModule The checkout module instance
     *
     * @return bool
     */
    public function processPayment(IsotopeProductCollection $objOrder, \Module $objModule)
    {
        // If the order has already been placed through postsale
        if ($objOrder->isCheckoutComplete()) {
            return true;
        }

        // In processPayment, the parameters are always in GET
        $this->psp_http_method = 'GET';

        return $this->processPostsale($objOrder);
    }


    /**
     * Process post-sale request from the PSP payment server.
     *
     * @param IsotopeProductCollection $objOrder
     *
     * @return  boolean Not needed when called by postsale.php but when called internally by processPayment
     * @return bool
     */
    public function processPostsale(IsotopeProductCollection $objOrder)
    {
        /** @type Order $objOrder */

        if (!$this->validateSHASign()) {
            \System::log('Received invalid postsale data for order ID "' . $objOrder->id . '"', __METHOD__, TL_ERROR);
            return false;
        }

        // Validate payment data
        if ($objOrder->currency != $this->getRequestData('currency') || $objOrder->getTotal() != $this->getRequestData('amount')) {
            \System::log('Postsale checkout manipulation in payment for Order ID ' . $objOrder->id . '!', __METHOD__, TL_ERROR);
            return false;
        }

        // Validate payment status
        switch ($this->getRequestData('STATUS')) {

            /** @noinspection PhpMissingBreakStatementInspection */
            case 9:  // Zahlung beantragt (Authorize & Capture)
                $objOrder->date_paid = time();
                // no break

            case 5:  // Genehmigt (Authorize ohne Capture)
                $intStatus = $this->new_order_status;
                break;

            case 41: // Unbekannter Wartezustand
            case 51: // Genehmigung im Wartezustand
            case 91: // Zahlung im Wartezustand
            case 52: // Genehmigung nicht bekannt
            case 92: // Zahlung unsicher

                /** @type \Isotope\Model\Config $objConfig */
                if (($objConfig = $objOrder->getRelated('config_id')) === null) {
                    \System::log('Config for Order ID ' . $objOrder->id . ' not found', __METHOD__, TL_ERROR);
                    return false;
                }

                $intStatus = $objConfig->orderstatus_error;
                break;

            case 0:  // Ungültig / Unvollständig
            case 1:  // Zahlungsvorgang abgebrochen
            case 2:  // Genehmigung verweigert
            case 4:  // Gespeichert
            case 93: // Bezahlung verweigert
            default:
                return false;
        }

        if (!$objOrder->checkout()) {
            \System::log('Post-Sale checkout for Order ID "' . $objOrder->id . '" failed', __METHOD__, TL_ERROR);
            return false;
        }
 
        // HOOK: to be able to check other PSP Feedback Parameters
        if (isset($GLOBALS['ISO_HOOKS']['processPostsale']) && is_array($GLOBALS['ISO_HOOKS']['processPostsale']))
        {
            foreach ($GLOBALS['ISO_HOOKS']['processPostsale'] as $callback)
            {
                $objCallback = \System::importStatic($callback[0]);
                $return = $objCallback->{$callback[1]}($objOrder,$this->getRawRequestData());
                
                // If return false  exit function
                if (false === $return) {
                    return false;
                }
            }
        }

        $objOrder->payment_data = json_encode($this->getRawRequestData());

        $objOrder->updateOrderStatus($intStatus);
        $objOrder->save();

        return true;
    }


    /**
     * Get the order object in a postsale request
     * @return  IsotopeProductCollection
     */
    public function getPostsaleOrder()
    {
        if (!$this->getRequestData('orderID')) {
            return null;
        }

        return Order::findByPk((int) $this->getRequestData('orderID'));
    }

    /**
     * Return the payment form
     *
     * @param IsotopeProductCollection $objOrder  The order being places
     * @param \Module                  $objModule The checkout module instance
     *
     * @return string
     */
    public function checkoutForm(IsotopeProductCollection $objOrder, \Module $objModule)
    {
        $arrParams = $this->preparePSPParams($objOrder, $objModule);

        // SHA-1 must be generated on alphabetically sorted keys.
        // Use the natural order algorithm so ITEM10 gets listed after ITEM2
        // We can only use ksort($arrParams, SORT_NATURAL) as of PHP 5.4
        uksort($arrParams, 'strnatcasecmp');

        $strSHASign = '';
        foreach ($arrParams as $k => $v) {
            if ($v == '')
                continue;

            $strSHASign .= $k . '=' . htmlspecialchars_decode($v) . $this->psp_hash_in;
        }

        $arrParams['SHASIGN'] = strtoupper(hash($this->psp_hash_method, $strSHASign));

        $objTemplate = new \Isotope\Template($this->strTemplate);
        $objTemplate->setData($this->arrData);

        $objTemplate->params   = $arrParams;
        $objTemplate->headline = specialchars($GLOBALS['TL_LANG']['MSC']['pay_with_redirect'][0]);
        $objTemplate->message  = specialchars($GLOBALS['TL_LANG']['MSC']['pay_with_redirect'][1]);
        $objTemplate->slabel   = specialchars($GLOBALS['TL_LANG']['MSC']['pay_with_redirect'][2]);
        $objTemplate->noscript = specialchars($GLOBALS['TL_LANG']['MSC']['pay_with_redirect'][3]);

        return $objTemplate->parse();
    }

    /**
     * Gets the available payment methods
     *
     * @param Order  $objOrder
     * @param \Isotope\Module\Checkout $objModule
     *
     * @return array
     */
    public function getPaymentMethods()
    {
        return array();
    }

    /**
     * Prepare PSP params
     * @param   Order
     * @param   Module
     * @return  array
     */
    protected function preparePSPParams($objOrder, $objModule)
    {
        $objBillingAddress = $objOrder->getBillingAddress();

        $arrParams = array(
            'PSPID'         => $this->psp_pspid,
            'ORDERID'       => $objOrder->id,
            'AMOUNT'        => round(($objOrder->getTotal() * 100)),
            'CURRENCY'      => $objOrder->currency,
            'LANGUAGE'      => $GLOBALS['TL_LANGUAGE'] . '_' . strtoupper($GLOBALS['TL_LANGUAGE']),
            'CN'            => $objBillingAddress->firstname . ' ' . $objBillingAddress->lastname,
            'EMAIL'         => $objBillingAddress->email,
            'OWNERZIP'      => $objBillingAddress->postal,
            'OWNERADDRESS'  => substr($objBillingAddress->street_1, 0, 35),
            'OWNERADDRESS2' => substr($objBillingAddress->street_2, 0, 35),
            'OWNERCTY'      => strtoupper($objBillingAddress->country),
            'OWNERTOWN'     => substr($objBillingAddress->city, 0, 35),
            'OWNERTELNO'    => $objBillingAddress->phone,
            'ACCEPTURL'     => \Environment::get('base') . $objModule->generateUrlForStep('complete', $objOrder),
            'DECLINEURL'    => \Environment::get('base') . $objModule->generateUrlForStep('failed'),
            'BACKURL'       => \Environment::get('base') . $objModule->generateUrlForStep('review'),
            'PARAMPLUS'     => 'mod=pay&amp;id=' . $this->id,
            'TP'            => $this->psp_dynamic_template ? : ''
        );
        
        // HOOK: check other PSP Parameters, like AlIAS
        if (isset($GLOBALS['ISO_HOOKS']['preparePSPParams']) && is_array($GLOBALS['ISO_HOOKS']['preparePSPParams']))
        {
            foreach ($GLOBALS['ISO_HOOKS']['preparePSPParams'] as $callback)
            {
                $objCallback	= \System::importStatic($callback[0]);
                $arrParams		= $objCallback->{$callback[1]}($objOrder,$objModule, $arrParams);
            }
        }

        return $arrParams;
    }

    /**
     * Gets the request data based on the chosen HTTP method
     *
     * @param string $strKey
     *
     * @return  mixed
     */
    private function getRequestData($strKey)
    {
        if ($this->psp_http_method == 'GET') {
            return $_GET[$strKey];
        }

        return $_POST[$strKey];
    }

    /**
     * Gets the raw request data based on the chosen HTTP method
     *
     * @return  array
     */
    private function getRawRequestData()
    {
        if ($this->psp_http_method == 'GET') {
            return $_GET;
        }

        return $_POST;
    }


    /**
     * Validate SHA-OUT signature
     *
     * @return bool
     */
    private function validateSHASign()
    {
        $strSHASign = '';
        $arrParams  = array();

        foreach ($this->getRawRequestData() as $key => $value) {
            if (in_array(strtoupper($key), static::$arrShaOut)) {
                $arrParams[$key] = $value;
            }
        }

        // SHA-1 must be generated on alphabetically sorted keys.
        // Use the natural order algorithm so ITEM10 gets listed after ITEM2
        // We can only use ksort($arrParams, SORT_NATURAL) as of PHP 5.4
        uksort($arrParams, 'strnatcasecmp');

        foreach ($arrParams as $k => $v) {
            if ($v == '') {
                continue;
            }

            $strSHASign .= strtoupper($k) . '=' . $v . $this->psp_hash_out;
        }

        if ($this->getRequestData('SHASIGN') == strtoupper(hash($this->psp_hash_method, $strSHASign))) {
            return true;
        }

        log_message(
            sprintf(
                "Received invalid postsale data.\nInput hash: %s\nCalculated hash: %s\nParameters: %s\n",
                $this->getRequestData('SHASIGN'),
                strtoupper(hash($this->psp_hash_method, $strSHASign)),
                print_r($arrParams, true)
            ),
            'isotope_psp.log'
        );

        return false;
    }
}
