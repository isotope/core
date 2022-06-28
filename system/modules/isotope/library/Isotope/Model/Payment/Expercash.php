<?php

/*
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009 - 2019 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Model\Payment;

use Contao\Environment;
use Contao\Input;
use Contao\Module;
use Contao\System;
use Isotope\Interfaces\IsotopePostsale;
use Isotope\Interfaces\IsotopeProductCollection;
use Isotope\Interfaces\IsotopePurchasableCollection;
use Isotope\Model\Payment;
use Isotope\Model\ProductCollection\Order;
use Isotope\Module\Checkout;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Expercash payment method.
 *
 * @property string $expercash_popupId
 * @property string $expercash_profile
 * @property string $expercash_popupKey
 * @property string $expercash_paymentMethod
 * @property string $expercash_css
 */
class Expercash extends Payment implements IsotopePostsale
{
    /**
     * @inheritdoc
     */
    public function processPayment(IsotopeProductCollection $objOrder, \Module $objModule)
    {
        if (!$objOrder instanceof IsotopePurchasableCollection) {
            System::log('Product collection ID "' . $objOrder->getId() . '" is not purchasable', __METHOD__, TL_ERROR);
            return false;
        }

        // @todo this can't be the only validation
        if ($this->validateUrlParams($objOrder)) {
            $objOrder->checkout();
            $objOrder->updateOrderStatus($this->new_order_status);

            return true;
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function processPostsale(IsotopeProductCollection $objOrder)
    {
        if (!$objOrder instanceof IsotopePurchasableCollection) {
            System::log('Product collection ID "' . $objOrder->getId() . '" is not purchasable', __METHOD__, TL_ERROR);
            return new Response('', Response::HTTP_BAD_REQUEST);
        }

        if (!$this->validateUrlParams($objOrder)) {
            System::log('ExperCash: data rejected' . print_r($_POST, true), __METHOD__, TL_GENERAL);
            return new Response('', Response::HTTP_BAD_REQUEST);
        }

        if ($objOrder->isCheckoutComplete()) {
            System::log('Postsale checkout for Order ID "' . $objOrder->getId() . '" already completed', __METHOD__, TL_ERROR);
            return new Response();
        }

        if (!$objOrder->checkout()) {
            System::log('Postsale checkout for Order ID "' . $objOrder->getId() . '" failed', __METHOD__, TL_ERROR);
            return new Response('', Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $objOrder->setDatePaid(time());
        $objOrder->updateOrderStatus($this->new_order_status);

        $objOrder->save();

        return new Response();
    }

    /**
     * @inheritdoc
     */
    public function getPostsaleOrder()
    {
        return Order::findByPk(Input::get('transactionId'));
    }

    /**
     * @inheritdoc
     */
    public function checkoutForm(IsotopeProductCollection $objOrder, Module $objModule)
    {
        $arrData = array
        (
            'popupId'       => $this->expercash_popupId,
            'jobId'         => microtime(),
            'functionId'    => null !== $objOrder->getMember() ? $objOrder->getMember()->id : $objOrder->getUniqueId(),
            'transactionId' => $objOrder->getId(),
            'amount'        => round($objOrder->getTotal(), 2) * 100,
            'currency'      => $objOrder->getCurrency(),
            'paymentMethod' => $this->expercash_paymentMethod,
            'returnUrl'     => Checkout::generateUrlForStep(Checkout::STEP_COMPLETE, $objOrder, null, true),
            'errorUrl'      => Checkout::generateUrlForStep(Checkout::STEP_FAILED, null, null, true),
            'notifyUrl'     => System::getContainer()->get('router')->generate('isotope_postsale', ['mod' => 'pay', 'id' => $this->id], UrlGeneratorInterface::ABSOLUTE_URL),
            'profile'       => $this->expercash_profile,
        );

        $strKey = '';
        $strUrl = 'https://epi.expercash.net/epi_popup2.php?';

        foreach ($arrData as $k => $v) {
            $strKey .= $v;
            $strUrl .= $k . '=' . urlencode($v) . '&amp;';
        }

        if (is_file(TL_ROOT . '/' . $this->expercash_css)) {
            $strUrl .= 'cssUrl=' . urlencode(Environment::get('base') . $this->expercash_css) . '&amp;';
        }

        $strUrl .= 'language=' . strtoupper($GLOBALS['TL_LANGUAGE']) . '&amp;popupKey=' . md5($strKey . $this->expercash_popupKey);

        $strBuffer = '
<h2>' . $GLOBALS['TL_LANG']['MSC']['pay_with_redirect'][0] . '</h2>
<p class="message">' . $GLOBALS['TL_LANG']['MSC']['pay_with_redirect'][1] . '</p>

<iframe src="' . $strUrl . '" width="100%" height="500">
  <p>Ihr Browser kann leider keine eingebetteten Frames anzeigen:
  Sie können die eingebettete Seite über den folgenden Verweis
  aufrufen: <a href="' . $strUrl . '">ExperCash</a></p>
</iframe>';

        return $strBuffer;
    }

    /**
     * Validate URL parameters against payment hacking.
     *
     * @param IsotopeProductCollection $objOrder
     *
     * @return bool
     */
    private function validateUrlParams(IsotopeProductCollection $objOrder)
    {
        if ($objOrder === null) {
            return false;
        }

        $strKey = md5(
            Input::get('amount')
            . Input::get('currency')
            . Input::get('paymentMethod')
            . Input::get('transactionId')
            . Input::get('GuTID')
            . $this->expercash_popupKey
        );

        if (Input::get('exportKey') != $strKey) {
            System::log('ExperCash: exportKey was incorrect. Possible data manipulation!', __METHOD__, TL_ERROR);

            return false;
        }

        if (Input::get('amount') != (round($objOrder->getTotal(), 2) * 100)) {
            System::log('ExperCash: amount is incorrect. Possible data manipulation!', __METHOD__, TL_ERROR);

            return false;
        }

        if (Input::get('currency') != $objOrder->getCurrency()) {
            System::log('ExperCash: currency is incorrect. Possible data manipulation!', __METHOD__, TL_ERROR);

            return false;
        }

        if (Input::get('transactionId') != $objOrder->getId()) {
            System::log('ExperCash: transactionId is incorrect. Possible data manipulation!', __METHOD__, TL_ERROR);

            return false;
        }

        return true;
    }
}
