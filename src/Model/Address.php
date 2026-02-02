<?php

namespace Isotope\CoreBundle\Model;

use Contao\Controller;
use Contao\MemberModel;
use Contao\Model;
use Contao\Model\Collection;
use Isotope\Backend;
use Isotope\Interfaces\IsotopeProductCollection;
use Isotope\Isotope;

/**
 * @property int    $id
 * @property int    $pid
 * @property string $ptable
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
 */
class Address extends Model
{
    /**
     * @var string
     */
    protected static $strTable = 'tl_iso_address';

    public function __construct($objResult = null)
    {
        parent::__construct($objResult);

        Controller::loadDataContainer(static::$strTable);
    }

    /**
     * Find addresses for member and store ID.
     *
     * @return Collection<Address>|null
     */
    public static function findByMemberAndStoreId(int $member, int $storeId, array $options = array()): Collection|null
    {
        return static::findBy(
            array('pid=?', 'ptable=?', 'store_id=?'),
            array($member, 'tl_member', $storeId),
            $options
        );
    }

    /**
     * Find address by ID and member, automatically checking the current store ID and tl_member parent table
     *
     * @param int   $intId
     * @param int   $intMember
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
     * Find default billing address for a product collection
     *
     * @param int   $intCollection
     *
     * @return static|null
     */
    public static function findDefaultBillingForProductCollection($intCollection, array $arrOptions = array())
    {
        return static::findOneBy(
            array('pid=?', 'ptable=?'),
            array($intCollection, 'tl_iso_product_collection'),
            $arrOptions
        );
    }

    /**
     * Find default shipping address for a product collection
     *
     * @param int   $intCollection
     *
     * @return static|null
     */
    public static function findDefaultShippingForProductCollection($intCollection, array $arrOptions = array())
    {
        return static::findOneBy(
            array('pid=?', 'ptable=?'),
            array($intCollection, 'tl_iso_product_collection'),
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
     * @param array|null               $arrFill an array of member fields to inherit
     * @param bool                     $blnDefaultBilling
     * @param bool                     $blnDefaultShipping
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
        $subdivision = strtoupper($member->country.'-'.$member->state);

        if ('' === Backend::getLabelForSubdivision($member->country, $subdivision)) {
            $subdivision = '';
        }

        return array_intersect_key(
            array_merge(
                $member->row(),
                array(
                    'street_1'    => $member->street,

                    // Trying to guess subdivision by country and state
                    'subdivision' => $subdivision,
                )
            ),
            array_flip($fields)
        );
    }
}
