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

use Contao\Environment;
use Contao\FrontendUser;
use Contao\StringUtil;
use Contao\System;
use Isotope\CompatibilityHelper;
use Isotope\Frontend;
use Isotope\Interfaces\IsotopeProductCollection;
use Isotope\Interfaces\IsotopeShipping;
use Isotope\Isotope;
use Isotope\Interfaces\IsotopeWeightAggregate;
use Isotope\Translation;
use Isotope\Weight;


/**
 * Class Shipping
 *
 * @property int    $id
 * @property int    $tstamp
 * @property string $name
 * @property string $label
 * @property string $type
 * @property bool   $inherit
 * @property string $note
 * @property array  $countries
 * @property array  $subdivisions
 * @property string $postalCodes
 * @property float  $minimum_total
 * @property float  $maximum_total
 * @property float  $quantity_mode
 * @property float  $minimum_quantity
 * @property float  $maximum_quantity
 * @property array  $minimum_weight
 * @property array  $maximum_weight
 * @property array  $product_types
 * @property string $product_types_condition
 * @property array  $config_ids
 * @property string $address_type
 * @property string $price
 * @property int    $tax_class
 * @property array  $shipping_weight
 * @property bool   $guests
 * @property bool   $protected
 * @property array  $groups
 * @property bool   $debug
 * @property bool   $logging
 * @property bool   $enabled
 */
abstract class Shipping extends TypeAgent implements IsotopeShipping, IsotopeWeightAggregate
{
    const QUANTITY_MODE_ITEMS = 'cart_items';
    const QUANTITY_MODE_PRODUCTS = 'cart_products';

    /**
     * Table name
     * @var string
     */
    protected static $strTable = 'tl_iso_shipping';

    /**
     * Interface to validate shipping method
     * @var string
     */
    protected static $strInterface = '\Isotope\Interfaces\IsotopeShipping';

    /**
     * List of types (classes) for this model
     * @var array
     */
    protected static $arrModelTypes = array();

    /**
     * Template
     * @var string
     */
    protected $strTemplate;


    /**
     * @inheritdoc
     */
    public function getId()
    {
        return (int) $this->id;
    }

    /**
     * @inheritdoc
     *
     * @throws \InvalidArgumentException on unknown quantity mode
     * @throws \UnexpectedValueException on unknown product type condition
     */
    public function isAvailable()
    {
        if (CompatibilityHelper::isBackend() ) {
            return true;
        }

        if (!$this->enabled && BE_USER_LOGGED_IN !== true) {
            return false;
        }

        if ($this->address_type) {
            $billingAddress = Isotope::getCart()->getBillingAddress();
            $shippingAddress = Isotope::getCart()->getShippingAddress();

            if ($this->address_type === 'custom' && $billingAddress->id === $shippingAddress->id) {
                return false;
            }

            if ($this->address_type === 'billing' && $billingAddress->id !== $shippingAddress->id) {
                return false;
            }
        }

        if (($this->guests && FE_USER_LOGGED_IN === true) || ($this->protected && FE_USER_LOGGED_IN !== true)) {
            return false;
        }

        if ($this->protected) {
            $arrGroups = StringUtil::deserialize($this->groups);

            if (!\is_array($arrGroups)
                || empty($arrGroups)
                || !\count(array_intersect($arrGroups, FrontendUser::getInstance()->groups))
            ) {
                return false;
            }
        }

        if (($this->minimum_total > 0 && $this->minimum_total > Isotope::getCart()->getSubtotal())
            || ($this->maximum_total > 0 && $this->maximum_total < Isotope::getCart()->getSubtotal())
        ) {
            return false;
        }

        $objScale = Isotope::getCart()->addToScale();

        if (($minWeight = Weight::createFromTimePeriod($this->minimum_weight)) !== null
            && $objScale->isLessThan($minWeight)
        ) {
            return false;
        }

        if (($maxWeight = Weight::createFromTimePeriod($this->maximum_weight)) !== null
            && $objScale->isMoreThan($maxWeight)
        ) {
            return false;
        }

        if ($this->minimum_quantity > 0 || $this->maximum_quantity > 0) {
            switch ($this->quantity_mode) {
                case static::QUANTITY_MODE_ITEMS:
                    $quantity =  Isotope::getCart()->sumItemsQuantity();
                    break;

                case static::QUANTITY_MODE_PRODUCTS:
                    $quantity =  Isotope::getCart()->countItems();
                    break;

                default:
                    throw new \InvalidArgumentException(sprintf('Unknown quantity mode "%s"', $this->quantity_mode));
            }

            if (($this->minimum_quantity > 0 && $this->minimum_quantity > $quantity)
                || ($this->maximum_quantity > 0 && $this->maximum_quantity < $quantity)
            ) {
                return false;
            }
        }

        $arrConfigs = StringUtil::deserialize($this->config_ids);
        if (\is_array($arrConfigs) && !empty($arrConfigs) && !\in_array(Isotope::getConfig()->id, $arrConfigs)) {
            return false;
        }

        $objAddress = Isotope::getCart()->getShippingAddress();

        $arrCountries = StringUtil::deserialize($this->countries);
        if (\is_array($arrCountries) && !empty($arrCountries)) {
            if (!\in_array($objAddress->country, $arrCountries, true)) {
                return false;
            }

            $arrSubdivisions = StringUtil::deserialize($this->subdivisions);
            if (\is_array($arrSubdivisions)
                && !empty($arrSubdivisions)
                && !\in_array($objAddress->subdivision, $arrSubdivisions, true)
            ) {
                return false;
            }
        }

        // Check if address has a valid postal code
        if ($this->postalCodes != '') {
            $arrCodes = Frontend::parsePostalCodes($this->postalCodes);

            if (!\in_array($objAddress->postal, $arrCodes)) {
                return false;
            }
        }

        if ('calculation' !== $this->product_types_condition) {
            $arrConfigTypes = StringUtil::deserialize($this->product_types);

            if (\is_array($arrConfigTypes) && \count($arrConfigTypes) > 0) {
                $arrItems = Isotope::getCart()->getItems();
                $arrItemTypes = array();

                foreach ($arrItems as $objItem) {
                    if ($objItem->hasProduct()) {
                        $productType = $objItem->getProduct()->getType();
                        $arrItemTypes[] = null === $productType ? 0 : (int) $productType->id;

                    } elseif ('onlyAvailable' === $this->product_types_condition) {
                        // If one product in cart is not of given type, shipping method is not available
                        return false;
                    }
                }

                $arrItemTypes = array_unique($arrItemTypes);

                switch ($this->product_types_condition) {
                    case 'onlyAvailable':
                        return 0 === \count(array_diff($arrItemTypes, $arrConfigTypes));

                    case 'oneAvailable':
                        return \count(array_intersect($arrConfigTypes, $arrItemTypes)) > 0;

                    case 'allAvailable':
                        return \count(array_intersect($arrConfigTypes, $arrItemTypes)) === \count($arrConfigTypes);

                    default:
                        throw new \UnexpectedValueException(
                            'Unknown product type condition "' . $this->product_types_condition . '"'
                        );
                }
            }
        }

        // !HOOK: modify if shipping method is available
        if (isset($GLOBALS['ISO_HOOKS']['shippingAvailable']) && \is_array($GLOBALS['ISO_HOOKS']['shippingAvailable'])) {
            foreach ($GLOBALS['ISO_HOOKS']['shippingAvailable'] as $callback) {
                if (!System::importStatic($callback[0])->{$callback[1]}($this)) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function isPercentage()
    {
        return '%' === substr($this->arrData['price'], -1);
    }

    /**
     * @inheritdoc
     *
     * @throws \UnexpectedValueException if the shipping methods does not have a percentage amount.
     */
    public function getPercentage()
    {
        if (!$this->isPercentage()) {
            throw new \UnexpectedValueException('Shipping method does not have a percentage amount.');
        }

        return (float) substr($this->arrData['price'], 0, -1);
    }

    /**
     * @inheritdoc
     */
    public function getPercentageLabel()
    {
        return $this->isPercentage() ? $this->arrData['price'] : '';
    }

    /**
     * @inheritdoc
     */
    public function getPrice(IsotopeProductCollection $objCollection = null)
    {
        if ('' === (string) $this->arrData['price']) {
            return null;
        }

        if ($this->isPercentage()) {
            if (null === $objCollection) {
                $objCollection = Isotope::getCart();
            }

            $fltPrice = $objCollection->getSubtotal() / 100 * $this->getPercentage();
        } else {
            $fltPrice = (float) $this->arrData['price'];
        }

        return Isotope::calculatePrice($fltPrice, $this, 'price', $this->arrData['tax_class']);
    }

    /**
     * @inheritdoc
     */
    public function getLabel()
    {
        return Translation::get($this->label ? : $this->name);
    }

    /**
     * @inheritdoc
     */
    public function getNote()
    {
        return $this->note;
    }

    /**
     * @inheritdoc
     */
    protected function backendInterface($orderId)
    {
        return '
<div id="tl_buttons">
<a href="' . ampersand(str_replace('&key=shipping', '', Environment::get('request'))) . '" class="header_back" title="' . StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['backBT']) . '">' . $GLOBALS['TL_LANG']['MSC']['backBT'] . '</a>
</div>

<h2 class="sub_headline">' . $this->name . ' (' . $GLOBALS['TL_LANG']['MODEL']['tl_iso_shipping'][$this->type][0] . ')' . '</h2>

<div id="tl_soverview">
<div id="tl_messages">
<p class="tl_info">' . $GLOBALS['TL_LANG']['MSC']['backendShippingNoInfo'] . '</p>
</div>
</div>';
    }

    /**
     * @inheritdoc
     */
    public function checkoutReview()
    {
        return $this->getLabel();
    }

    /**
     * @inheritdoc
     */
    public function getSurcharge(IsotopeProductCollection $objCollection)
    {
        if (null === $this->getPrice()) {
            return null;
        }

        return ProductCollectionSurcharge::createForShippingInCollection($this, $objCollection);
    }

    /**
     * {@inheritdoc}
     */
    public function getWeight()
    {
        return Weight::createFromTimePeriod($this->shipping_weight);
    }

    /**
     * Logs information for this shipping method if enabled.
     *
     * @param mixed $value
     */
    protected function debugLog($value)
    {
        if (!$this->logging) {
            return;
        }

        $pos = strrpos(\get_called_class(), '\\') ?: -1;
        $className = substr(\get_called_class(), $pos+1);

        $logFile = sprintf(
            'isotope_%s-%s.log',
            strtolower(preg_replace(array('/([A-Z]+)([A-Z][a-z])/', '/([a-z\d])([A-Z])/'), array('\\1_\\2', '\\1_\\2'), str_replace('_', '.', $className))),
            date('Y-m-d')
        );

        log_message(print_r($value, true), $logFile);
    }
}
