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

use Contao\Controller;
use Contao\MemberModel;
use Contao\Model;
use Contao\Model\Collection;
use Contao\StringUtil;
use Contao\System;
use Database\Result;
use Haste\Util\Format;
use Isotope\Backend;
use Isotope\Interfaces\IsotopeProductCollection;
use Isotope\Interfaces\IsotopeVatNoValidator;
use Isotope\Isotope;


/**
 * Class Address
 *
 * @property int    $id
 * @property int    $pid
 * @property string $ptable
 * @property string $label
 * @property int    $store_id
 * @property string $gender
 * @property string $salutation
 * @property string $firstname
 * @property string $lastname
 * @property int    $dateOfBirth
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
 * @property bool   $isDefaultShipping
 * @property bool   $isDefaultBilling
 */
class Address extends Model
{

    /**
     * Table
     * @var string
     */
    protected static $strTable = 'tl_iso_address';

    /**
     * Construct the model
     *
     * @param Result $objResult
     */
    public function __construct($objResult = null)
    {
        parent::__construct($objResult);

        Controller::loadDataContainer(static::$strTable);
        System::loadLanguageFile('addresses');
    }

    /**
     * @return string
     */
    public function __toString()
    {
        try {
            return $this->generate();
        } catch (\Exception $e) {
            return '';
        }
    }

    /**
     * Check if the address has a valid VAT number
     *
     * @param Config $config
     *
     * @return bool
     *
     * @throws \LogicException if a validator does not implement the correct interface
     * @throws \RuntimeException if a validators reports an error about the VAT number
     */
    public function hasValidVatNo(Config $config = null)
    {
        if (null === $config) {
            $config = Isotope::getConfig();
        }

        $validators = StringUtil::deserialize($config->vatNoValidators);

        // if no validators are enabled, the VAT No is always valid
        if (!\is_array($validators) || 0 === \count($validators)) {
            return true;
        }

        foreach ($validators as $class) {
            $service = new $class();

            if (!($service instanceof IsotopeVatNoValidator)) {
                throw new \LogicException($class . ' does not implement IsotopeVatNoValidator interface');
            }

            $result = $service->validate($this);

            if (true === $result) {
                return true;
            }
        }

        return false;
    }

    /**
     * Return formatted address (hCard)
     *
     * @param array $arrFields
     *
     * @return string
     *
     * @throws \Exception on error parsing simple tokens
     */
    public function generate($arrFields = null)
    {
        // We need a country to format the address, use default country if none is available
        $strCountry = $this->country ?: Isotope::getConfig()->country;

        // Use generic format if no country specific format is available
        $strFormat = $GLOBALS['ISO_ADR'][$strCountry] ?? $GLOBALS['ISO_ADR']['generic'];

        $arrTokens  = $this->getTokens($arrFields);

        return StringUtil::parseSimpleTokens($strFormat, $arrTokens);
    }

    /**
     * Return this address formatted as text
     *
     * @param array $arrFields
     *
     * @return string
     *
     * @deprecated use Address::generate() and strip_tags
     * @throws \Exception on invalid simple tokens
     */
    public function generateText($arrFields = null)
    {
        return strip_tags($this->generate($arrFields));
    }

    /**
     * Return an address formatted with HTML (hCard)
     *
     * @param array $arrFields
     *
     * @return string
     *
     * @deprecated use Address::generate()
     * @throws \Exception on invalid simple tokens
     */
    public function generateHtml($arrFields = null)
    {
        return $this->generate($arrFields);
    }

    /**
     * Compile the list of hCard tokens for this address
     *
     * @param array $arrFields
     *
     * @return array
     */
    public function getTokens($arrFields = null)
    {
        if (!\is_array($arrFields)) {
            $arrFields = Isotope::getConfig()->getBillingFieldsConfig();
        }

        $arrTokens = array('outputFormat' => 'html');

        foreach ($arrFields as $arrField) {
            $strField = $arrField['value'];

            // Set an empty value for disabled fields, otherwise the token would not be replaced
            if (!$arrField['enabled']) {
                $arrTokens[$strField] = '';
                continue;
            }

            if ('subdivision' === $strField && $this->subdivision != '') {
                [$country, $subdivision] = explode('-', $this->subdivision);

                $arrTokens['subdivision_abbr'] = $subdivision;
                $arrTokens['subdivision']      = Backend::getLabelForSubdivision($country, $this->subdivision);

                continue;
            }

            $arrTokens[$strField] = Format::dcaValue(static::$strTable, $strField, $this->$strField);
        }


        /**
         * Generate hCard fields
         * See http://microformats.org/wiki/hcard
         */

        // Set "fn" (full name) to company if no first- and lastname is given
        if ($arrTokens['company'] != '') {
            $fn        = $arrTokens['company'];
            $fnCompany = ' fn';
        } else {
            $fn        = trim($arrTokens['firstname'] . ' ' . $arrTokens['lastname']);
            $fnCompany = '';
        }

        $street = implode('<br>', array_filter([$this->street_1, $this->street_2, $this->street_3]));

        $arrTokens += [
            'hcard_fn'               => $fn ? '<span class="fn">' . $fn . '</span>' : '',
            'hcard_n'                => ($arrTokens['firstname'] || $arrTokens['lastname']) ? '1' : '',
            'hcard_honorific_prefix' => $arrTokens['salutation'] ? '<span class="honorific-prefix">' . $arrTokens['salutation'] . '</span>' : '',
            'hcard_given_name'       => $arrTokens['firstname'] ? '<span class="given-name">' . $arrTokens['firstname'] . '</span>' : '',
            'hcard_family_name'      => $arrTokens['lastname'] ? '<span class="family-name">' . $arrTokens['lastname'] . '</span>' : '',
            'hcard_org'              => $arrTokens['company'] ? '<div class="org' . $fnCompany . '">' . $arrTokens['company'] . '</div>' : '',
            'hcard_email'            => $arrTokens['email'] ? '<a href="mailto:' . $arrTokens['email'] . '">' . $arrTokens['email'] . '</a>' : '',
            'hcard_tel'              => $arrTokens['phone'] ? '<div class="tel">' . $arrTokens['phone'] . '</div>' : '',
            'hcard_adr'              => ($street || $arrTokens['city'] || $arrTokens['postal'] || $arrTokens['subdivision'] || $arrTokens['country']) ? '1' : '',
            'hcard_street_address'   => $street ? '<div class="street-address">' . $street . '</div>' : '',
            'hcard_locality'         => $arrTokens['city'] ? '<span class="locality">' . $arrTokens['city'] . '</span>' : '',
            'hcard_region'           => $arrTokens['subdivision'] ? '<span class="region">' . $arrTokens['subdivision'] . '</span>' : '',
            'hcard_region_abbr'      => !empty($arrTokens['subdivision_abbr']) ? '<abbr class="region" title="' . $arrTokens['subdivision'] . '">' . $arrTokens['subdivision_abbr'] . '</abbr>' : '',
            'hcard_postal_code'      => $arrTokens['postal'] ? '<span class="postal-code">' . $arrTokens['postal'] . '</span>' : '',
            'hcard_country_name'     => $arrTokens['country'] ? '<div class="country-name">' . $arrTokens['country'] . '</div>' : '',
        ];

        return $arrTokens;
    }

    /**
     * Find address for member, automatically checking the current store ID and tl_member parent table
     *
     * @param int   $intMember
     * @param array $arrOptions
     *
     * @return Collection|null
     */
    public static function findForMember($intMember, array $arrOptions = array())
    {
        return static::findBy(
            array('pid=?', 'ptable=?', 'store_id=?'),
            array($intMember, 'tl_member', Isotope::getCart()->store_id),
            $arrOptions
        );
    }

    /**
     * Find address by ID and member, automatically checking the current store ID and tl_member parent table
     *
     * @param int   $intId
     * @param int   $intMember
     * @param array $arrOptions
     *
     * @return Address|null
     */
    public static function findOneForMember($intId, $intMember, array $arrOptions = array())
    {
        return static::findOneBy(
            array('id=?', 'pid=?', 'ptable=?', 'store_id=?'),
            array($intId, $intMember, 'tl_member', Isotope::getCart()->store_id),
            $arrOptions
        );
    }

    /**
     * Find default billing adddress for a member, automatically checking the current store ID and tl_member parent table
     * @param   int
     * @param   array
     * @return  static|null
     */
    public static function findDefaultBillingForMember($intMember, array $arrOptions = array())
    {
        return static::findOneBy(
            array('pid=?', 'ptable=?', 'store_id=?', 'isDefaultBilling=?'),
            array($intMember, 'tl_member', Isotope::getCart()->store_id, '1'),
            $arrOptions
        );
    }

    /**
     * Find default shipping adddress for a member, automatically checking the current store ID and tl_member parent table
     * @param   int
     * @param   array
     * @return  static|null
     */
    public static function findDefaultShippingForMember($intMember, array $arrOptions = array())
    {
        return static::findOneBy(array('pid=?', 'ptable=?', 'store_id=?', 'isDefaultShipping=?'), array($intMember, 'tl_member', Isotope::getCart()->store_id, '1'), $arrOptions);
    }

    /**
     * Find default billing address for a product collection
     *
     * @param int   $intCollection
     * @param array $arrOptions
     *
     * @return static|null
     */
    public static function findDefaultBillingForProductCollection($intCollection, array $arrOptions = array())
    {
        return static::findOneBy(
            array('pid=?', 'ptable=?', 'isDefaultBilling=?'),
            array($intCollection, 'tl_iso_product_collection', '1'),
            $arrOptions
        );
    }

    /**
     * Find default shipping address for a product collection
     *
     * @param int   $intCollection
     * @param array $arrOptions
     *
     * @return static|null
     */
    public static function findDefaultShippingForProductCollection($intCollection, array $arrOptions = array())
    {
        return static::findOneBy(
            array('pid=?', 'ptable=?', 'isDefaultShipping=?'),
            array($intCollection, 'tl_iso_product_collection', '1'),
            $arrOptions
        );
    }

    /**
     * Create a new address for a member and automatically set default properties
     *
     * @param int        $intMember
     * @param array|null $arrFill
     *
     * @return static
     */
    public static function createForMember($intMember, $arrFill = null)
    {
        $objAddress = new static();

        $arrData = array(
            'pid'      => $intMember,
            'ptable'   => 'tl_member',
            'tstamp'   => time(),
            'store_id' => (int) Isotope::getCart()->store_id,
        );

        if (!empty($arrFill) && \is_array($arrFill) && ($objMember = MemberModel::findByPk($intMember)) !== null) {
            $arrData = array_merge(static::getAddressDataForMember($objMember, $arrFill), $arrData);
        }

        $objAddress->setRow($arrData);

        return $objAddress;
    }

    /**
     * Create a new address for a product collection
     *
     * @param IsotopeProductCollection $objCollection
     * @param array|null               $arrFill an array of member fields to inherit
     * @param bool                     $blnDefaultBilling
     * @param bool                     $blnDefaultShipping
     *
     * @return static
     */
    public static function createForProductCollection(
        IsotopeProductCollection $objCollection,
        $arrFill = null,
        $blnDefaultBilling = false,
        $blnDefaultShipping = false
    ) {
        $objAddress = new static();

        $arrData = array(
            'pid'               => $objCollection->getId(),
            'ptable'            => 'tl_iso_product_collection',
            'tstamp'            => time(),
            'store_id'          => $objCollection->getStoreId(),
            'isDefaultBilling'  => $blnDefaultBilling ? '1' : '',
            'isDefaultShipping' => $blnDefaultShipping ? '1' : '',
        );

        if (!empty($arrFill) && \is_array($arrFill) && ($objMember = $objCollection->getMember()) !== null) {
            $arrData = array_merge(static::getAddressDataForMember($objMember, $arrFill), $arrData);
        }

        if (empty($arrData['country']) && null !== ($objConfig = $objCollection->getConfig())) {
            if ($blnDefaultBilling) {
                $arrData['country'] = $objConfig->billing_country ?: $objConfig->country;
            } elseif ($blnDefaultShipping) {
                $arrData['country'] = $objConfig->shipping_country ?: $objConfig->country;
            }
        }

        $objAddress->setRow($arrData);

        return $objAddress;
    }

    /**
     * Generate address data from tl_member, limit to fields enabled in the shop configuration
     */
    public static function getAddressDataForMember(MemberModel $member, array $fields)
    {
        return array_intersect_key(
            array_merge(
                $member->row(),
                array(
                    'street_1'    => $member->street,

                    // Trying to guess subdivision by country and state
                    'subdivision' => strtoupper($member->country . '-' . $member->state)
                )
            ),
            array_flip($fields)
        );
    }
}
