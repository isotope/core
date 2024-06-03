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
    public function processPayment(IsotopeProductCollection $objOrder, Module $objModule)
    {
        if (!$objOrder instanceof IsotopePurchasableCollection) {
            \Contao\System::getContainer()->get('monolog.logger.contao')->log(\Psr\Log\LogLevel::ERROR, 'Product collection ID "' . $objOrder->getId() . '" is not purchasable', ['contao' => new \Contao\CoreBundle\Monolog\ContaoContext(__METHOD__, \Contao\CoreBundle\Monolog\ContaoContext::ERROR)]);
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
            \Contao\System::getContainer()->get('monolog.logger.contao')->log(\Psr\Log\LogLevel::ERROR, 'Product collection ID "' . $objOrder->getId() . '" is not purchasable', ['contao' => new \Contao\CoreBundle\Monolog\ContaoContext(__METHOD__, \Contao\CoreBundle\Monolog\ContaoContext::ERROR)]);
            return new Response('', Response::HTTP_BAD_REQUEST);
        }

        if (!$this->validateUrlParams($objOrder)) {
            \Contao\System::getContainer()->get('monolog.logger.contao')->log(\Psr\Log\LogLevel::INFO, 'ExperCash: data rejected' . print_r($_POST, true), ['contao' => new \Contao\CoreBundle\Monolog\ContaoContext(__METHOD__, \Contao\CoreBundle\Monolog\ContaoContext::GENERAL)]);
            return new Response('', Response::HTTP_BAD_REQUEST);
        }

        if ($objOrder->isCheckoutComplete()) {
            \Contao\System::getContainer()->get('monolog.logger.contao')->log(\Psr\Log\LogLevel::ERROR, 'Postsale checkout for Order ID "' . $objOrder->getId() . '" already completed', ['contao' => new \Contao\CoreBundle\Monolog\ContaoContext(__METHOD__, \Contao\CoreBundle\Monolog\ContaoContext::ERROR)]);
            return new Response();
        }

        if (!$objOrder->checkout()) {
            \Contao\System::getContainer()->get('monolog.logger.contao')->log(\Psr\Log\LogLevel::ERROR, 'Postsale checkout for Order ID "' . $objOrder->getId() . '" failed', ['contao' => new \Contao\CoreBundle\Monolog\ContaoContext(__METHOD__, \Contao\CoreBundle\Monolog\ContaoContext::ERROR)]);
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

        if (is_file(\Contao\System::getContainer()->getParameter('kernel.project_dir') . '/' . $this->expercash_css)) {
            $strUrl .= 'cssUrl=' . urlencode(Environment::get('base') . $this->expercash_css) . '&amp;';
        }

        $strUrl .= 'language=' . strtoupper($GLOBALS['TL_LANGUAGE']) . '&amp;popupKey=' . md5($strKey . $this->expercash_popupKey);

        return '
<h2>' . $GLOBALS['TL_LANG']['MSC']['pay_with_redirect'][0] . '</h2>
<p class="message">' . $GLOBALS['TL_LANG']['MSC']['pay_with_redirect'][1] . '</p>

<iframe src="' . $strUrl . '" width="100%" height="500">
  <p>Ihr Browser kann leider keine eingebetteten Frames anzeigen:
  Sie können die eingebettete Seite über den folgenden Verweis
  aufrufen: <a href="' . $strUrl . '">ExperCash</a></p>
</iframe>';
    }

    /**
     * Validate URL parameters against payment hacking.
     *
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
            \Contao\System::getContainer()->get('monolog.logger.contao')->log(\Psr\Log\LogLevel::ERROR, 'ExperCash: exportKey was incorrect. Possible data manipulation!', ['contao' => new \Contao\CoreBundle\Monolog\ContaoContext(__METHOD__, \Contao\CoreBundle\Monolog\ContaoContext::ERROR)]);

            return false;
        }

        if (Input::get('amount') != (round($objOrder->getTotal(), 2) * 100)) {
            \Contao\System::getContainer()->get('monolog.logger.contao')->log(\Psr\Log\LogLevel::ERROR, 'ExperCash: amount is incorrect. Possible data manipulation!', ['contao' => new \Contao\CoreBundle\Monolog\ContaoContext(__METHOD__, \Contao\CoreBundle\Monolog\ContaoContext::ERROR)]);

            return false;
        }

        if (Input::get('currency') != $objOrder->getCurrency()) {
            \Contao\System::getContainer()->get('monolog.logger.contao')->log(\Psr\Log\LogLevel::ERROR, 'ExperCash: currency is incorrect. Possible data manipulation!', ['contao' => new \Contao\CoreBundle\Monolog\ContaoContext(__METHOD__, \Contao\CoreBundle\Monolog\ContaoContext::ERROR)]);

            return false;
        }

        if (Input::get('transactionId') != $objOrder->getId()) {
            \Contao\System::getContainer()->get('monolog.logger.contao')->log(\Psr\Log\LogLevel::ERROR, 'ExperCash: transactionId is incorrect. Possible data manipulation!', ['contao' => new \Contao\CoreBundle\Monolog\ContaoContext(__METHOD__, \Contao\CoreBundle\Monolog\ContaoContext::ERROR)]);

            return false;
        }

        return true;
    }
}
