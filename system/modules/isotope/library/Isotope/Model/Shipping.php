<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2016 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Model;

use Haste\Units\Mass\Weight;
use Isotope\Frontend;
use Isotope\Interfaces\IsotopeProductCollection;
use Isotope\Interfaces\IsotopeShipping;
use Isotope\Isotope;
use Isotope\Translation;


/**
 * Class Shipping
 *
 * @property int    $id
 * @property int    $tstamp
 * @property string $name
 * @property string $label
 * @property string $type
 * @property string $note
 * @property array  $countries
 * @property array  $subdivisions
 * @property string $postalCodes
 * @property float  $minimum_total
 * @property float  $maximum_total
 * @property float  $quantity_mode
 * @property float  $minimum_quantity
 * @property float  $maximum_quantity
 * @property float  $minimum_weight
 * @property float  $maximum_weight
 * @property array  $product_types
 * @property string $product_types_condition
 * @property array  $config_ids
 * @property string $price
 * @property int    $tax_class
 * @property bool   $guests
 * @property bool   $protected
 * @property array  $groups
 * @property bool   $enabled
 */
abstract class Shipping extends TypeAgent implements IsotopeShipping
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
        if (!$this->enabled && BE_USER_LOGGED_IN !== true) {
            return false;
        }

        if (($this->guests && FE_USER_LOGGED_IN === true) || ($this->protected && FE_USER_LOGGED_IN !== true)) {
            return false;
        }

        if ($this->protected) {
            $arrGroups = deserialize($this->groups);

            if (!is_array($arrGroups)
                || empty($arrGroups)
                || !count(array_intersect($arrGroups, \FrontendUser::getInstance()->groups))
            ) {
                return false;
            }
        }

        if (($this->minimum_total > 0 && $this->minimum_total > Isotope::getCart()->getSubtotal())
            || ($this->maximum_total > 0 && $this->maximum_total < Isotope::getCart()->getSubtotal())
        ) {
            return false;
        }

        $objScale = Isotope::getCart()->addToScale(null, true);

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

        $arrConfigs = deserialize($this->config_ids);
        if (is_array($arrConfigs) && !empty($arrConfigs) && !in_array(Isotope::getConfig()->id, $arrConfigs)) {
            return false;
        }

        $objAddress = Isotope::getCart()->getShippingAddress();

        $arrCountries = deserialize($this->countries);
        if (is_array($arrCountries) && !empty($arrCountries)) {
            if (!in_array($objAddress->country, $arrCountries, true)) {
                return false;
            }

            $arrSubdivisions = deserialize($this->subdivisions);
            if (is_array($arrSubdivisions)
                && !empty($arrSubdivisions)
                && !in_array($objAddress->subdivision, $arrSubdivisions, true)
            ) {
                return false;
            }
        }

        // Check if address has a valid postal code
        if ($this->postalCodes != '') {
            $arrCodes = Frontend::parsePostalCodes($this->postalCodes);

            if (!in_array($objAddress->postal, $arrCodes)) {
                return false;
            }
        }

        if ('calculation' !== $this->product_types_condition) {
            $arrConfigTypes = deserialize($this->product_types);

            if (is_array($arrConfigTypes) && count($arrConfigTypes) > 0) {
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
                        return 0 === count(array_diff($arrItemTypes, $arrConfigTypes));

                    case 'oneAvailable':
                        return count(array_intersect($arrConfigTypes, $arrItemTypes)) > 0;

                    case 'allAvailable':
                        return count(array_intersect($arrConfigTypes, $arrItemTypes)) === count($arrConfigTypes);

                    default:
                        throw new \UnexpectedValueException(
                            'Unknown product type condition "' . $this->product_types_condition . '"'
                        );
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
        if (null === $objCollection) {
            $objCollection = Isotope::getCart();
        }

        if ($this->isPercentage()) {
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
    public function backendInterface($orderId)
    {
        return '
<div id="tl_buttons">
<a href="' . ampersand(str_replace('&key=shipping', '', \Environment::get('request'))) . '" class="header_back" title="' . specialchars($GLOBALS['TL_LANG']['MSC']['backBT']) . '">' . $GLOBALS['TL_LANG']['MSC']['backBT'] . '</a>
</div>

<h2 class="sub_headline">' . $this->name . ' (' . $GLOBALS['TL_LANG']['MODEL']['tl_iso_shipping'][$this->type][0] . ')' . '</h2>

<div class="tl_formbody_edit">
<div class="tl_tbox block">
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
        if ($this->getPrice() == 0) {
            return null;
        }

        return ProductCollectionSurcharge::createForShippingInCollection($this, $objCollection);
    }
}
