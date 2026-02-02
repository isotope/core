<?php

namespace Isotope\CoreBundle\EventListener\DataContainer\Address;

use Codefog\HasteBundle\Formatter;
use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Isotope\CoreBundle\Model\Address;
use Isotope\CoreBundle\Store\Address\AddressFormatter;
use Isotope\CoreBundle\Store\StoreManager;

class LabelListener
{
    public function __construct(
        private readonly AddressFormatter $addressFormatter,
        private readonly StoreManager $storeManager,
        private readonly Formatter $formatter,
    ) {
    }

    #[AsCallback('tl_iso_address', 'list.label.label')]
    public function rowLabel(array $row): string
    {
        $address = new Address();
        $address->setRow($row);

        return (string) $this->addressFormatter->get(
            $address,
            $this->storeManager->getCurrentContext()->getBillingAddressConfig(),
        );
    }

    #[AsCallback('tl_iso_address', 'list.label.group')]
    public function groupLabel(string $label, int $mode, string $field, array $row): string
    {
        if ('store_id' !== $field) {
            return $label;
        }

        return $this->formatter->dcaLabel('tl_iso_address', 'store_id').': '.$row['store_id'];
    }
}
