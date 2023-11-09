<?php

/*
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009 - 2019 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\EventListener;

use Contao\StringUtil;
use Haste\Units\Mass\Unit;
use Isotope\Interfaces\IsotopePurchasableCollection;
use Isotope\Model\Address;
use Isotope\Model\Config;
use Isotope\Model\ProductCollection\Order;
use Isotope\Model\Shipping\DHLBusiness;
use Petschko\DHL\BusinessShipment;
use Petschko\DHL\Credentials;
use Petschko\DHL\Receiver;
use Petschko\DHL\Sender;
use Petschko\DHL\SendPerson;
use Petschko\DHL\ShipmentDetails;

class DHLBusinessCheckoutListener
{
    public function onPostCheckout(IsotopePurchasableCollection $order)
    {
        $shippingAddress = $order->getShippingAddress();
        $shipping = $order->getShippingMethod();
        $config = $order->getConfig();

        if (!$order instanceof Order
            || !$shipping instanceof DHLBusiness
            || !$shippingAddress instanceof Address
            || !$config instanceof Config
        ) {
            return;
        }

        $dhl = new BusinessShipment($this->getCredentials($shipping), (bool) $shipping->debug);
        $dhl->setShipmentDetails($this->getShipmentDetails($shipping, $order));
        $dhl->setSender($this->getSender($config->getOwnerAddress()));
        $dhl->setReceiver($this->getReceiver($shippingAddress));
        $dhl->setReceiverEmail($shippingAddress->email);

        $response = $dhl->createShipment();

        $this->debugLog($dhl->getLastXML(), $shipping);
        $this->debugLog($response, $shipping);

        if (false === $response) {
            $this->debugLog($dhl->getErrors(), $shipping);

            return;
        }

        $data = StringUtil::deserialize($order->shipping_data, true);
        $data['dhl_shipment_number'] = $response->getShipmentNumber();
        $order->shipping_data = $data;
        $order->save();

        $this->debugLog('Shipment Number: ' . $response->getShipmentNumber(), $shipping);
    }

    private function getCredentials(DHLBusiness $shipping)
    {
        $credentials = new Credentials((bool) $shipping->debug);

        $credentials->setUser($shipping->dhl_user);
        $credentials->setSignature($shipping->dhl_signature);
        $credentials->setEpk($shipping->dhl_epk);
        $credentials->setApiUser($shipping->dhl_app);
        $credentials->setApiPassword($shipping->dhl_token);

        $this->debugLog($credentials, $shipping);

        return $credentials;
    }

    private function getSender(Address $address)
    {
        $person = new Sender();

        $this->createPerson($person, $address);

        return $person;
    }

    private function getReceiver(Address $address)
    {
        $person = new Receiver();

        $this->createPerson($person, $address);

        return $person;
    }

    private function createPerson(SendPerson $person, Address $address)
    {
        if ($address->company) {
            $person->setName($address->company);

            if ($address->firstname && $address->lastname) {
                $person->setContactPerson($address->firstname . ' ' . $address->lastname);
            }
        } else {
            $person->setName($address->firstname . ' ' . $address->lastname);
        }

        $person->setFullStreet($address->street_1);
        $person->setAddressAddition($address->street_2);

        $person->setZip($address->postal);
        $person->setCity($address->city);
//        $person->setCountry((string) 'Germany');
        $person->setCountryISOCode($address->country);

        $person->setEmail($address->email);
        $person->setPhone($address->phone);

        return $person;
    }

    private function getShipmentDetails(
        DHLBusiness $shippingMethod,
        IsotopePurchasableCollection $order
    ) {
        $scale = $order->addToScale();

        if (($shippingWeight = $shippingMethod->getWeight()) !== null) {
            $scale->add($shippingWeight);
        }

        $details = new ShipmentDetails($shippingMethod->dhl_epk);

        $details->setProduct($shippingMethod->dhl_product);
        $details->setCustomerReference($order->getDocumentNumber());
        $details->setReturnReference($order->getDocumentNumber());
        $details->setWeight($scale->amountIn("kg"));

        $shippingDate = StringUtil::deserialize($shippingMethod->dhl_shipping, true);
        if (isset($shippingDate['value']) && $shippingDate['value'] && $shippingDate['unit']) {
            $shippingDate = strtotime(sprintf('+%s %s', $shippingDate['value'], $shippingDate['unit']));

            $details->setShipmentDate(
                date('Y-m-d', date('w', $shippingDate) === 0 ? strtotime('+1 day', $shippingDate) : $shippingDate)
            );
        }

        return $details;
    }

    private function debugLog($value, DHLBusiness $shipping): void
    {
        if (!$shipping->logging) {
            return;
        }

        $logFile = 'isotope_dhl_business-' . date('Y-m-d') . '.log';

        log_message(print_r($value, true), $logFile);
    }
}
