<?php

/*
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009 - 2019 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Model;

use Contao\Database;
use Contao\FrontendUser;
use Contao\Model;
use Contao\StringUtil;
use Contao\System;

/**
 * Isotope\Model\Config represents an Isotope config model
 *
 * @property int    $id
 * @property int    $tstamp
 * @property string $name
 * @property string $label
 * @property string $firstname
 * @property string $lastname
 * @property string $company
 * @property string $vat_no
 * @property string $street_1
 * @property string $street_2
 * @property string $street_3
 * @property string $postal
 * @property string $city
 * @property string $subdivision
 * @property string $country
 * @property string $phone
 * @property string $email
 * @property array  $address_fields
 * @property string $billing_country
 * @property string $shipping_country
 * @property string|null $billing_countries
 * @property string|null $shipping_countries
 * @property bool   $limitMemberCountries
 * @property int    $default_shipping
 * @property int    $default_payment
 * @property string $bankName
 * @property string $bankAccount
 * @property string $bankCode
 * @property string $taxNumber
 * @property string $priceDisplay
 * @property string $taxCalculation
 * @property string $currencyFormat
 * @property int    $priceRoundPrecision
 * @property string $priceRoundIncrement
 * @property float  $cartMinSubtotal
 * @property string $currency
 * @property bool   $currencySymbol
 * @property bool   $currencySpace
 * @property string $currencyPosition
 * @property string $priceCalculateFactor
 * @property string $orderPrefix
 * @property int    $orderDigits
 * @property int    $orderstatus_new
 * @property int    $orderstatus_error
 * @property string $templateGroup
 * @property string $ga_account
 * @property string $ga_member
 */
class Config extends Model
{
    public const PRICE_DISPLAY_NET = 'net';
    public const PRICE_DISPLAY_GROSS = 'gross';
    public const PRICE_DISPLAY_FIXED = 'fixed';

    /**
     * Name of the current table
     * @var string
     */
    protected static $strTable = 'tl_iso_config';

    /**
     * Cache for additional methods
     * @var array
     */
    protected $arrCache = array();

    private static $priceDisplayGroups;

    /**
     * Get translated label for the config
     * @return  string
     */
    public function getLabel()
    {
        return System::getContainer()->get('translator')->trans($this->label ? : $this->name, [], 'isotope');
    }

    /**
     * Get billing address fields
     *
     * @return  array
     */
    public function getBillingFields()
    {
        if (!isset($this->arrCache['billingFields'])) {
            $this->arrCache['billingFields'] = array_filter(array_map(
                function($field) {
                    return $field['enabled'] ? $field['value'] : null;
                },
                $this->getBillingFieldsConfig()
            ));
        }

        return $this->arrCache['billingFields'];
    }

    /**
     * Return raw billing field data
     *
     * @return array
     */
    public function getBillingFieldsConfig()
    {
        if (!isset($this->arrCache['billingFieldsConfig'])) {
            $this->arrCache['billingFieldsConfig'] = array();
            $arrFields                             = StringUtil::deserialize($this->address_fields);

            if (\is_array($arrFields)) {
                foreach ($arrFields as $arrField) {
                    $this->arrCache['billingFieldsConfig'][] = [
                        'value'     => $arrField['name'],
                        'enabled'   => 'disabled' !== $arrField['billing'],
                        'mandatory' => 'mandatory' === $arrField['billing'],
                    ];
                }
            }
        }

        return $this->arrCache['billingFieldsConfig'];
    }

    /**
     * Get shipping address fields
     *
     * @return array
     */
    public function getShippingFields()
    {
        if (!isset($this->arrCache['shippingFields'])) {
            $this->arrCache['shippingFields'] = array_filter(array_map(
                function($field) {
                    return $field['enabled'] ? $field['value'] : null;
                },
                $this->getShippingFieldsConfig()
            ));
        }

        return $this->arrCache['shippingFields'];
    }

    /**
     * Return raw shipping field data
     *
     * @return array
     */
    public function getShippingFieldsConfig()
    {
        if (!isset($this->arrCache['shippingFieldsConfig'])) {
            $this->arrCache['shippingFieldsConfig'] = array();
            $arrFields                              = StringUtil::deserialize($this->address_fields);

            if (\is_array($arrFields)) {
                foreach ($arrFields as $arrField) {
                    $this->arrCache['shippingFieldsConfig'][] = [
                        'value'     => $arrField['name'],
                        'enabled'   => 'disabled' !== $arrField['shipping'],
                        'mandatory' => 'mandatory' === $arrField['shipping'],
                    ];
                }
            }
        }

        return $this->arrCache['shippingFieldsConfig'];
    }

    /**
     * Get the price display configuration
     *
     * @return string
     */
    public function getPriceDisplay()
    {
        $format = $this->priceDisplay;

        if (\Contao\System::getContainer()->get('security.helper')->isGranted('ROLE_MEMBER')) {
            if (null === self::$priceDisplayGroups) {
                self::$priceDisplayGroups = Database::getInstance()
                    ->execute("SELECT id, iso_priceDisplay FROM tl_member_group WHERE iso_priceDisplay!=''")
                    ->fetchEach('iso_priceDisplay')
                ;
            }

            foreach (FrontendUser::getInstance()->groups as $groupId) {
                if (isset(self::$priceDisplayGroups[$groupId])) {
                    $format = self::$priceDisplayGroups[$groupId];
                    break;
                }
            }
        }

        // !HOOK: calculate price
        if (isset($GLOBALS['ISO_HOOKS']['priceDisplay']) && \is_array($GLOBALS['ISO_HOOKS']['priceDisplay'])) {
            foreach ($GLOBALS['ISO_HOOKS']['priceDisplay'] as $callback) {
                $format = System::importStatic($callback[0])->{$callback[1]}($format, $this);
            }
        }

        return $format;
    }

    public static function findByRootPage(int $rootPageId, array $options = array()): self|null
    {
        $t = static::$strTable;

        $options = array_merge(
            array(
                 'column' => array("$t.id=(SELECT iso_config FROM tl_page WHERE id=?)"),
                 'value'  => $rootPageId,
                 'return' => 'Model'
            ),
            $options
        );

        return static::find($options);
    }

    public static function findByMemberCart(int $memberId, int $storeId, array $options = array()): self|null
    {
        $t = static::$strTable;

        $options = array_merge(
            array(
                'column' => array("$t.id=(SELECT config_id FROM tl_iso_product_collection WHERE type='cart' AND member=? AND store_id=?)"),
                'value'  => [$memberId, $storeId],
                'return' => 'Model'
            ),
            $options
        );

        return static::find($options);
    }

    public static function findByGuestCart(string $uniqid, int $storeId, array $options = array()): self|null
    {
        $t = static::$strTable;

        $options = array_merge(
            array(
                'column' => array("$t.id=(SELECT config_id FROM tl_iso_product_collection WHERE type='cart' AND uniqid=? AND store_id=?)"),
                'value'  => [$uniqid, $storeId],
                'return' => 'Model'
            ),
            $options
        );

        return static::find($options);
    }
}
