<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Model;

use Contao\Model;
use Contao\System;
use Isotope\CoreBundle\Store\Order\StatusType;

/**
 * @property int    $id
 * @property int    $pid
 * @property int    $tstamp
 * @property string $type
 * @property string $alias
 * @property string $name
 * @property string $label
 * @property string $color
 * @property bool   $paid
 * @property bool   $welcomescreen
 */
class OrderStatus extends Model
{
    /**
     * @var string
     */
    protected static $strTable = 'tl_iso_orderstatus';

    /**
     * Return if the order status means a collection has been paid (configuration flag).
     */
    public function isPaid(): bool
    {
        return (bool) $this->paid;
    }

    /**
     * Return the localized order status name.
     */
    public function getName(): string
    {
        return System::getContainer()->get('translator')->trans($this->name, [], 'isotope');
    }

    public static function findOneByAlias(string $alias, StatusType $type, array $options = []): self|null
    {
        return self::findOneBy(
            ['tl_iso_orderstatus.alias=?', 'tl_iso_orderstatus.type=?'],
            [$alias, $type->value],
            $options,
        );
    }
}
