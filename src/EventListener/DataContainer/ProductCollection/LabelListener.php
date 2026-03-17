<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\EventListener\DataContainer\ProductCollection;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\DataContainer;
use Isotope\CoreBundle\Model\Address;
use Isotope\CoreBundle\Model\Config;
use Isotope\CoreBundle\Store\Address\AddressConfig;
use Isotope\CoreBundle\Store\Address\AddressConfigFactory;
use Isotope\CoreBundle\Store\Address\AddressFormatter;
use Isotope\CoreBundle\Store\Order\WorkflowManager;
use Isotope\CoreBundle\Util\ColorUtil;
use Isotope\Isotope;

#[AsCallback('tl_iso_product_collection', 'list.label.label')]
class LabelListener
{
    public function __construct(
        private readonly AddressFormatter $addressFormatter,
        private readonly AddressConfigFactory $addressConfigFactory,
        private readonly WorkflowManager $workflowManager,
    ) {
    }

    public function __invoke(array $row, string|null $label, DataContainer $dc, array $args): array
    {
        foreach ($GLOBALS['TL_DCA']['tl_iso_product_collection']['list']['label']['fields'] as $i => $field) {
            switch ($field) {
                case 'billing_address_id':
                case 'shipping_address_id':
                    $args[$i] = $this->formatAddress($row, $field);
                    break;

                case 'total':
                    // TODO: format the price
                    $args[$i] = Isotope::formatPriceWithCurrency($row['total']);
                    break;

                case 'order_status':
                    if ($color = $this->workflowManager->getOrderWorkflow()->getMetadataStore()->getPlaceMetadata($row['order_status'])['color'] ?? null) {
                        $args[$i] = '<span style="' . ColorUtil::getCssBackgroundStyles($color) . '">' . $args[$i] . '</span>';
                    }
                    break;
            }
        }

        return $args;
    }

    private function formatAddress(array $row, string $field): string
    {
        [$type] = explode('_', $field);
        $addressConfig = $this->addressConfigFactory->createFromConfig(Config::findById($row['config_id']), $type);

        // Remove phone number and email from backend view
        $fields = array_diff($addressConfig->getFields(), ['email', 'phone']);
        $addressConfig = new AddressConfig($fields, $addressConfig->getMandatory(), $addressConfig->getCountryCodes(), $addressConfig->getDefaultCountryCode());

        return (string) $this->addressFormatter->toHtml(Address::findById($row[$field]), $addressConfig);
    }
}
