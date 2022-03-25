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
use Contao\StringUtil;
use Contao\System;
use Isotope\Interfaces\IsotopeProductCollection;
use Isotope\Interfaces\IsotopePurchasableCollection;
use Isotope\Isotope;
use Isotope\Model\ProductCollection\Order;
use Isotope\Template;

/**
 * Sofortueberweisung payment method
 *
 * @property string $sofortueberweisung_user_id
 * @property string $sofortueberweisung_project_id
 * @property string $sofortueberweisung_project_password
 */
class Sofortueberweisung extends Postsale
{
    /**
     * sofortueberweisung.de only supports these currencies
     *
     * @inheritdoc
     */
    public function isAvailable()
    {
        if (!\in_array(Isotope::getConfig()->currency, array('EUR', 'CHF', 'GBP'), true)) {
            return false;
        }

        try {
            return parent::isAvailable();
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * @inheritdoc
     */
    public function processPostsale(IsotopeProductCollection $objOrder)
    {
        if (!$objOrder instanceof IsotopePurchasableCollection) {
            System::log('Product collection ID "' . $objOrder->getId() . '" is not purchasable', __METHOD__, TL_ERROR);
            return;
        }

        $arrHash = array(
            'transaction'               => Input::post('transaction', true),
            'user_id'                   => Input::post('user_id', true),
            'project_id'                => Input::post('project_id', true),
            'sender_holder'             => Input::post('sender_holder', true),
            'sender_account_number'     => Input::post('sender_account_number', true),
            'sender_bank_code'          => Input::post('sender_bank_code', true),
            'sender_bank_name'          => Input::post('sender_bank_name', true),
            'sender_bank_bic'           => Input::post('sender_bank_bic', true),
            'sender_iban'               => Input::post('sender_iban', true),
            'sender_country_id'         => Input::post('sender_country_id', true),
            'recipient_holder'          => Input::post('recipient_holder', true),
            'recipient_account_number'  => Input::post('recipient_account_number', true),
            'recipient_bank_code'       => Input::post('recipient_bank_code', true),
            'recipient_bank_name'       => Input::post('recipient_bank_name', true),
            'recipient_bank_bic'        => Input::post('recipient_bank_bic', true),
            'recipient_iban'            => Input::post('recipient_iban', true),
            'recipient_country_id'      => Input::post('recipient_country_id', true),
            'international_transaction' => Input::post('international_transaction', true),
            'amount'                    => Input::post('amount', true),
            'currency_id'               => Input::post('currency_id', true),
            'reason_1'                  => Input::post('reason_1', true),
            'reason_2'                  => Input::post('reason_2', true),
            'security_criteria'         => Input::post('security_criteria', true),
            'user_variable_0'           => Input::post('user_variable_0', true),
            'user_variable_1'           => Input::post('user_variable_1', true),
            'user_variable_2'           => Input::post('user_variable_2', true),
            'user_variable_3'           => Input::post('user_variable_3', true),
            'user_variable_4'           => Input::post('user_variable_4', true),
            'user_variable_5'           => Input::post('user_variable_5', true),
            'created'                   => Input::post('created', true),
            'notification_password'     => $this->sofortueberweisung_project_password,
        );

        // check if both hashes math
        if (Input::post('hash', true) != sha1(implode('|', $arrHash))) {
            System::log('The given hash does not match. (sofortüberweisung.de)', __METHOD__, TL_ERROR);

            return;
        }

        if ($objOrder->isCheckoutComplete()) {
            System::log('Postsale checkout for Order ID "' . $objOrder->getId() . '" already completed', __METHOD__, TL_ERROR);
            return;
        }

        if (!$objOrder->checkout()) {
            System::log('Postsale checkout for Order ID "' . Input::post('user_variable_0') . '" failed', __METHOD__, TL_ERROR);

            return;
        }

        $objOrder->setDatePaid(time());
        $objOrder->updateOrderStatus($this->new_order_status);

        $objOrder->save();
    }

    /**
     * @inheritdoc
     */
    public function getPostsaleOrder()
    {
        return Order::findByPk((int) Input::post('user_variable_0'));
    }

    /**
     * @inheritdoc
     */
    public function checkoutForm(IsotopeProductCollection $objOrder, Module $objModule)
    {
        if (!$objOrder instanceof IsotopePurchasableCollection) {
            System::log('Product collection ID "' . $objOrder->getId() . '" is not purchasable', __METHOD__, TL_ERROR);
            return false;
        }

        $strCountry = \in_array($objOrder->getBillingAddress()->country, ['de', 'ch', 'at'], true) ? $objOrder->getBillingAddress()->country : 'de';
        $strUrl     = 'https://www.sofortueberweisung.' . $strCountry . '/payment/start';

        $arrParams = array
        (
            'user_id'               => $this->sofortueberweisung_user_id,
            'project_id'            => $this->sofortueberweisung_project_id,
            'sender_holder'         => '',
            'sender_account_number' => '',
            'sender_bank_code'      => '',
            'sender_country_id'     => strtoupper($objOrder->getBillingAddress()->country),
            'amount'                => number_format($objOrder->getTotal(), 2, '.', ''),
            'currency_id'           => $objOrder->getCurrency(),
            'reason_1'              => Environment::get('host'),
            'reason_2'              => '',
            'user_variable_0'       => $objOrder->getId(),
            'user_variable_1'       => $this->id,
            'user_variable_2'       => $objOrder->getUniqueId(),
            'user_variable_3'       => '',
            'user_variable_4'       => '',
            'user_variable_5'       => '',
            'project_password'      => $this->sofortueberweisung_project_password,
        );

        $arrParams['hash']        = sha1(implode('|', $arrParams));
        $arrParams['language_id'] = $GLOBALS['TL_LANGUAGE'];

        /** @var Template|\stdClass $objTemplate */
        $objTemplate = new Template('iso_payment_sofortueberweisung');
        $objTemplate->setData($this->arrData);
        $objTemplate->action   = $strUrl;
        $objTemplate->params   = array_filter(array_diff_key($arrParams, array('project_password' => '')));
        $objTemplate->headline = StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['pay_with_redirect'][0]);
        $objTemplate->message  = StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['pay_with_redirect'][1]);
        $objTemplate->slabel   = StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['pay_with_redirect'][2]);
        $objTemplate->noscript = StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['pay_with_redirect'][3]);

        return $objTemplate->parse();
    }
}
