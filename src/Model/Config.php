<?php

namespace Isotope\CoreBundle\Model;

use Contao\Model;

/**
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
 * @property string $currency
 * @property bool   $currencySymbol
 * @property bool   $currencySpace
 * @property string $currencyPosition
 * @property string $priceCalculateFactor
 * @property string $orderPrefix
 * @property int    $orderDigits
 * @property int    $orderstatus_new
 * @property int    $orderstatus_error
 */
class Config extends Model
{
    /**
     * @var string
     */
    protected static $strTable = 'tl_iso_config';

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
