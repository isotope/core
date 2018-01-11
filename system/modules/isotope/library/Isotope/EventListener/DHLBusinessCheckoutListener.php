<?php

namespace Isotope\EventListener;

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

        $credentials = $this->getCredentials($shipping);

        $dhl = new BusinessShipment($credentials, (bool) $shipping->debug);
        $dhl->setShipmentDetails($this->getShipmentDetails($credentials, $shipping, $order));
        $dhl->setSender($this->getSender($config->getOwnerAddress()));
        $dhl->setReceiver($this->getReceiver($shippingAddress));

        $response = $dhl->createShipment();

        if ($shipping->logging) {
            log_message(print_r($response, true), 'dhl_business.log');
        }

        if (false === $response) {
            if ($shipping->logging) {
                log_message(print_r($dhl->getErrors(), true), 'dhl_business.log');
            }

            return;
        }

        $data = deserialize($order->shipping_data, true);
        $data['dhl_shipment_number'] = $response->getShipmentNumber();
        $order->shipping_data = $data;
        $order->save();

        if ($shipping->logging) {
            log_message('Shipment Number: ' . $response->getShipmentNumber(), 'dhl_business.log');
        }
    }

    private function getCredentials(DHLBusiness $shipping)
    {
        $credentials = new Credentials((bool) $shipping->debug);

        $credentials->setUser($shipping->dhl_user);
        $credentials->setSignature($shipping->dhl_signature);
        $credentials->setEpk($shipping->dhl_epk);
        $credentials->setApiUser($shipping->dhl_app);
        $credentials->setApiPassword($shipping->dhl_token);

        if ($shipping->logging) {
            log_message(print_r($credentials, true), 'dhl_business.log');
        }

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

    private function getShipmentDetails(Credentials $credentials, DHLBusiness $shippingMethod, IsotopePurchasableCollection $order)
    {
        $accountNumber = sprintf(
            '%s%s01',
            $credentials->getEpk(10),
            substr($shippingMethod->dhl_product, 1, 2)
        );

        $details = new ShipmentDetails($accountNumber);

        $details->setProduct($shippingMethod->dhl_product);
        $details->setCustomerReference($order->getDocumentNumber());
        $details->setReturnReference($order->getDocumentNumber());

        return $details;
    }
}
