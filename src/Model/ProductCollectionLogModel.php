<?php

namespace Isotope\CoreBundle\Model;

use Contao\Model;

/**
 * @property int    $id
 * @property int    $pid
 * @property int    $tstamp
 * @property int    $author
 * @property int    $order_status
 * @property int    $date_paid
 * @property int    $date_shipped
 * @property string $notes
 * @property int    $notification
*/
class ProductCollectionLogModel extends Model
{
    /**
     * @var string
     */
    protected static $strTable = 'tl_iso_product_collection_log';

    public function getData(): array
    {
        return $this->data ? json_decode($this->data, true, 512, JSON_THROW_ON_ERROR) : [];
    }

    public function setData(array $data): self
    {
        $this->data = json_encode($data, JSON_THROW_ON_ERROR);

        return $this;
    }
}
