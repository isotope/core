<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Model;

use Contao\Model;
use Contao\StringUtil;
use Contao\System;

/**
 * @property int    $id
 * @property int    $pid
 * @property int    $tstamp
 * @property int    $sorting
 * @property string $name
 * @property string $color
 * @property bool   $paid
 * @property bool   $welcomescreen
 * @property int    $notification
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

    /**
     * Return the localized order status name
     * Do not use $this->getName(), the alias should not be localized.
     */
    public function getAlias(): string
    {
        return StringUtil::standardize($this->name);
    }
}
