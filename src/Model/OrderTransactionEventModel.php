<?php

namespace Isotope\CoreBundle\Model;

use Contao\Model;

/**
 * @property int    $pid
 * @property int    $tstamp
 * @property string $payload
 */
class OrderTransactionEventModel extends Model
{
    /**
     * @var string
     */
    protected static $strTable = 'tl_iso_order_transaction_event';
}
