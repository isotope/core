<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Model;

use Contao\Model;
use Contao\Model\Collection;

/**
 * @property int    $id
 * @property int    $pid
 * @property string $ptable
 * @property int    $tstamp
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
class AddressModel extends Model
{
    /**
     * @var string
     */
    protected static $strTable = 'tl_iso_address';

    /**
     * Find addresses for member and store ID.
     *
     * @return Collection<AddressModel>|null
     */
    public static function findByMemberAndStoreId(int $member, int $storeId, array $options = []): Collection|null
    {
        return static::findBy(
            ['pid=?', 'ptable=?', 'store_id=?'],
            [$member, 'tl_member', $storeId],
            $options,
        );
    }

    public function isMemberAddress(): bool
    {
        return 'tl_member' === $this->ptable;
    }
}
