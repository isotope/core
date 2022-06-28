<?php

/*
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009 - 2019 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Module;

use Contao\Environment;
use Contao\Input;
use Isotope\Model\Product;
use Isotope\Model\ProductCollection\Order;

class Trustedshops extends Module
{
    /**
     * @var string
     */
    protected $strTemplate = 'mod_iso_trustedshops';

    /**
     * Compile the current element
     */
    protected function compile()
    {
        $config = $this->generateConfig();

        $this->Template->customElementId = $config['customElementId'];
        $this->Template->checkoutData = $this->getCheckoutData();

        $jsonConfig = json_encode($config);

        $GLOBALS['TL_MOOTOOLS']['iso_trustedshops'] = <<<HTML
<script type="text/javascript">
    (function () {
        _tsConfig = $jsonConfig;
        var _ts = document.createElement('script');
        _ts.type = 'text/javascript';
        _ts.charset = 'utf-8';
        _ts.async = true;
        _ts.src = 'https://widgets.trustedshops.com/js/{$this->iso_tsid}.js';
        var __ts = document.getElementsByTagName('script')[0];
        __ts.parentNode.insertBefore(_ts, __ts);
    })();
</script>
HTML;
    }

    /**
     * @return array
     */
    private function generateConfig()
    {
        $config = [
            'disableTrustbadge' => false,
            'disableResponsive' => false,
            'yOffset' => (string) $this->iso_tsyoffset,
        ];

//        if ($this->iso_tswidth) {
//            $config['customBadgeWidth'] = $this->iso_tswidth;
//        }
//
//        if ($this->iso_tsheight) {
//            $config['customBadgeHeight'] = $this->iso_tsheight;
//        }

        switch ($this->iso_tsdisplay) {
            case 'standard':
                $config['variant'] = $this->iso_tsreviews ? 'reviews' : 'default';
                break;

            case 'custom':
                $config['variant'] = $this->iso_tsreviews ? 'custom_reviews' : 'custom';
                $config['customElementId'] = 'customTrustbadge'.$this->id;
                $config['trustcardDirection'] = $this->iso_tsdirection;
                break;
        }

        return $config;
    }

    private function getCheckoutData()
    {
        if (!$this->iso_tscheckout || !($uid = Input::get('uid'))) {
            return [];
        }

        $order = Order::findBy('uniqid', $uid);

        if (!$order instanceof Order) {
            return [];
        }

        $data = [
            'order' => [
                'tsCheckoutOrderNr' => $order->getDocumentNumber(),
                'tsCheckoutBuyerEmail' => $order->getBillingAddress()->email,
                'tsCheckoutOrderAmount' => $order->getTotal(),
                'tsCheckoutOrderCurrency' => $order->getCurrency(),
                'tsCheckoutOrderPaymentType' => $order->getPaymentMethod()->getLabel(),
                'tsCheckoutOrderEstDeliveryDate' => '',
            ],
            'items' => [],
        ];

        if ($this->iso_tsproducts) {
            $data['items'] = [];

            foreach ($order->getItems() as $item) {
                $product = $item->getProduct();

                if (!$product instanceof Product) {
                    continue;
                }

                $gtin = '';
                $type = $product->getType();

                if (null !== $type && (\in_array('gtin', $type->getAttributes(), true) || \in_array('gtin', $type->getVariantAttributes(), true))) {
                    $gtin = $product->gtin;
                }

                $data['items'][] = [
                    'tsCheckoutProductUrl' => $product->generateUrl($item->getRelated('jumpTo'), true),
//                    'tsCheckoutProductImageUrl' => '',
                    'tsCheckoutProductName' => $item->getName(),
                    'tsCheckoutProductSKU' => $item->getSku(),
                    'tsCheckoutProductGTIN' => $gtin,
//                    'tsCheckoutProductMPN' => '',
//                    'tsCheckoutProductBrand' => '',
                ];
            }
        }

        return $data;
    }
}
