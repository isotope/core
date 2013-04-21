<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2012 Isotope eCommerce Workgroup
 *
 * @package    Isotope
 * @link       http://www.isotopeecommerce.com
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 */

namespace Isotope\Model;

use Isotope\Isotope;


/**
 * Class Address
 *
 * @copyright  Isotope eCommerce Workgroup 2009-2012
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
 * @author     Yanick Witschi <yanick.witschi@terminal42.ch>
 * @author     Christoph Wiechert <christoph.wiechert@4wardmedia.de>
 */
class Address extends \Model
{

    /**
     * Table
     * @var string
     */
    protected static $strTable = 'tl_iso_addresses';


    public function __construct()
    {
        parent::__construct();

        if (!is_array($GLOBALS['ISO_ADR']))
        {
            Isotope::getInstance()->call('loadDataContainer', 'tl_iso_addresses');
            \System::loadLanguageFile('addresses');
        }
    }


    /**
     * Return this address formatted as text
     * @param array
     * @return string
     */
    public function generateText($arrFields=null)
    {
        return strip_tags($this->generateHtml($arrFields));
    }


    /**
     * Return an address formatted with HTML (hCard)
     * @param array
     * @return string
     */
    public function generateHtml($arrFields=null)
    {
        // We need a country to format the address, use default country if none is available
        $strCountry = $this->country != '' ? $this->country :  Isotope::getInstance()->Config->country;

        // Use generic format if no country specific format is available
        $strFormat = $GLOBALS['ISO_ADR'][$strCountry] != '' ? $GLOBALS['ISO_ADR'][$strCountry] : $GLOBALS['ISO_ADR']['generic'];

        $arrTokens = $this->getTokens($arrFields);
        $strAddress = \String::parseSimpleTokens($strFormat, $arrTokens);

        return $strAddress;
    }


    /**
     * Compile the list of hCard tokens for this address
     * @param array
     * @return array
     */
    public function getTokens($arrFields=null)
    {
        global $objPage;

        if (!is_array($arrFields)) {
            $arrFields = Isotope::getInstance()->getConfig()->billing_fields;
        }

        $arrTokens = array('outputFormat'=>$objPage->outputFormat);

        foreach ($arrFields as $arrField)
        {
            $strField = $arrField['value'];

            // Set an empty value for disabled fields, otherwise the token would not be replaced
            if (!$arrField['enabled'])
            {
                $arrTokens[$strField] = '';
                continue;
            }

            if ($strField == 'subdivision' && $this->subdivision != '')
            {
                if (!is_array($GLOBALS['TL_LANG']['DIV']))
                {
                    \System::loadLanguageFile('subdivisions');
                }

                list($country, $subdivion) = explode('-', $this->subdivision);

                $arrTokens['subdivision'] = $GLOBALS['TL_LANG']['DIV'][strtolower($country)][$this->subdivision];
                $arrTokens['subdivision_abbr'] = $subdivion;

                continue;
            }

            $arrTokens[$strField] = Isotope::formatValue('tl_iso_addresses', $strField, $this->$strField);
        }


        /**
         * Generate hCard fields
         * See http://microformats.org/wiki/hcard
         */

        // Set "fn" (full name) to company if no first- and lastname is given
        if ($arrTokens['company'] != '')
        {
            $fn = $arrTokens['company'];
            $fnCompany = ' fn';
        }
        else
        {
            $fn = trim($arrTokens['firstname'] . ' ' . $arrTokens['lastname']);
            $fnCompany = '';
        }

        $street = implode(($objPage->outputFormat == 'html' ? '<br>' : '<br />'), array_filter(array($this->street_1, $this->street_2, $this->street_3)));

        $arrTokens += array
        (
            'hcard_fn'                    => ($fn ? '<span class="fn">'.$fn.'</span>' : ''),
            'hcard_n'                    => (($arrTokens['firstname'] || $arrTokens['lastname']) ? '1' : ''),
            'hcard_honorific_prefix'    => ($arrTokens['salutation'] ? '<span class="honorific-prefix">'.$arrTokens['salutation'].'</span>' : ''),
            'hcard_given_name'            => ($arrTokens['firstname'] ? '<span class="given-name">'.$arrTokens['firstname'].'</span>' : ''),
            'hcard_family_name'            => ($arrTokens['lastname'] ? '<span class="family-name">'.$arrTokens['lastname'].'</span>' : ''),
            'hcard_org'                    => ($arrTokens['company'] ? '<div class="org'.$fnCompany.'">'.$arrTokens['company'].'</div>' : ''),
            'hcard_email'                => ($arrTokens['email'] ? '<a href="mailto:'.$arrTokens['email'].'">'.$arrTokens['email'].'</a>' : ''),
            'hcard_tel'                    => ($arrTokens['phone'] ? '<div class="tel">'.$arrTokens['phone'].'</div>' : ''),
            'hcard_adr'                    => (($street | $arrTokens['city'] || $arrTokens['postal'] || $arrTokens['subdivision'] || $arrTokens['country']) ? '1' : ''),
            'hcard_street_address'        => ($street ? '<div class="street-address">'.$street.'</div>' : ''),
            'hcard_locality'            => ($arrTokens['city'] ? '<span class="locality">'.$arrTokens['city'].'</span>' : ''),
            'hcard_region'                => ($arrTokens['subdivision'] ? '<span class="region">'.$arrTokens['subdivision'].'</span>' : ''),
            'hcard_region_abbr'            => ($arrTokens['subdivision_abbr'] ? '<abbr class="region" title="'.$arrTokens['subdivision'].'">'.$arrTokens['subdivision_abbr'].'</abbr>' : ''),
            'hcard_postal_code'            => ($arrTokens['postal'] ? '<span class="postal-code">'.$arrTokens['postal'].'</span>' : ''),
            'hcard_country_name'        => ($arrTokens['country'] ? '<div class="country-name">'.$arrTokens['country'].'</div>' : ''),
        );

        return $arrTokens;
    }

    /**
     * Find address for member, automatically checking the current store ID and tl_member parent table
     * @param   int
     * @param   array
     * @return  Collection|null
     */
    public static function findForMember($intMember, array $arrOptions=array())
    {
        return static::findBy(array('pid=?', 'ptable=?', 'store_id=?'), array($intMember, 'tl_member', Isotope::getConfig()->store_id), $arrOptions);
    }

    /**
     * Find address by ID and member, automatically checking the current store ID and tl_member parent table
     * @param   int
     * @param   int
     * @param   array
     * @return  Address|null
     */
    public static function findOneForMember($intId, $intMember, array $arrOptions=array())
    {
        return static::findBy(array('id=?', 'pid=?', 'ptable=?', 'store_id=?'), array($intId, $intMember, 'tl_member', Isotope::getConfig()->store_id), $arrOptions);
    }

    /**
     * Find default billing adddress for a member, automatically checking the current store ID and tl_member parent table
     * @param   int
     * @param   array
     * @return  Address|null
     */
    public static function findDefaultBillingForMember($intMember, array $arrOptions=array())
    {
        return static::findOneBy(array('pid=?', 'ptable=?', 'store_id=?', 'isDefaultBilling=?'), array($intMember, 'tl_member', Isotope::getConfig()->store_id, '1'), $arrOptions);
    }

    /**
     * Find default shipping adddress for a member, automatically checking the current store ID and tl_member parent table
     * @param   int
     * @param   array
     * @return  Address|null
     */
    public static function findDefaultShippingForMember($intMember, array $arrOptions=array())
    {
        return static::findOneBy(array('pid=?', 'ptable=?', 'store_id=?', 'isDefaultShipping=?'), array($intMember, 'tl_member', Isotope::getConfig()->store_id, '1'), $arrOptions);
    }

    /**
     * Create a new address for a member and automatically set default properties
     * @param   int
     * @param   array|null
     * @return  Address
     */
    public static function createForMember($intMember, $arrFill=null)
    {
        $objAddress = new Address();

        $arrData = array(
            'pid'       => $intMember,
            'ptable'    => 'tl_member',
            'tstamp'    => time(),
            'store_id'  => Isotope::getConfig()->store_id,
        );

        if (!empty($arrFill) && is_array($arrFill) && ($objMember = \MemberModel::findByPk($intMember)) !== null) {

            $arrData = array_intersect_key(
                array_merge(
                    $objMember->row(),
                    $arrData,
                    array(
                        'street_1'      => $objMember->street,

                        // Trying to guess subdivision by country and state
                        'subdivision'   => strtoupper($objMember->country . '-' . $objMember->state)
                    )
                ),
                array_flip($arrFill)
            );
        }

        $objAddress->setRow($arrData);

        return $objAddress;
    }
}
