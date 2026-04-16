<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Store\Order\Transformer;

use Isotope\CoreBundle\Model\AddressModel;

class AddressTransformer
{
    public static function transform(AddressModel $address): array
    {
        $data = $address->row();

        unset(
            $data['id'],
            $data['pid'],
            $data['ptable'],
            $data['tstamp'],
            $data['store_id'],
        );

        return array_filter($data);
    }
}
