<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\EventListener\DataContainer\ProductCollection;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\DataContainer;
use Isotope\CoreBundle\Model\AddressModel;
use Isotope\CoreBundle\Model\ConfigModel;
use Isotope\CoreBundle\Store\Address\AddressConfig;
use Isotope\CoreBundle\Store\Address\AddressConfigFactory;
use Isotope\CoreBundle\Store\Address\AddressFormatter;
use Isotope\CoreBundle\Store\Context\StoreContextFactory;
use Isotope\CoreBundle\Store\Order\WorkflowManager;
use Isotope\CoreBundle\Store\Price\CalculatedPrice;
use Isotope\CoreBundle\Store\Price\PriceFormatter;
use Isotope\CoreBundle\Util\ColorUtil;
use Symfony\Component\Workflow\Workflow;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

#[AsCallback('tl_iso_product_collection', 'list.label.label')]
class LabelListener
{
    public function __construct(
        private readonly StoreContextFactory $storeContextFactory,
        private readonly PriceFormatter $priceFormatter,
        private readonly AddressFormatter $addressFormatter,
        private readonly AddressConfigFactory $addressConfigFactory,
        private readonly WorkflowManager $workflowManager,
        private readonly TranslatorInterface $translator,
        private readonly Environment $twig,
    ) {
    }

    public function __invoke(array $row, string|null $label, DataContainer $dc, array $args): array
    {
        foreach ($GLOBALS['TL_DCA']['tl_iso_product_collection']['list']['label']['fields'] as $i => $field) {
            switch ($field) {
                case 'document_number':
                    $args[$i] = $label.'<br><small class="tl_gray">ID '.$row['id'].'</small>';
                    break;

                case 'billing_address_id':
                case 'shipping_address_id':
                    $args[$i] = $this->formatAddress($row, $field);
                    break;

                case 'total':
                    $context = $this->storeContextFactory->create($row['store_id'], ConfigModel::findById($row['config_id']), $row['language'], $row['uniqid'], $row['member']);

                    $args[$i] = $this->priceFormatter->toHtml(new CalculatedPrice((float) $row['total'], (float) $row['total']), $context);
                    break;

                case 'order_status':
                    $lines = ['<div>'.$this->formatStatus(
                        $this->workflowManager->getOrderWorkflow(),
                        $row['order_status'],
                    ).'</div>'];

                    if ($row['transaction_status']) {
                        $lines[] = sprintf('<div>%s: %s</div>',
                            $this->translator->trans('tl_iso_product_collection.transaction_status.2', [], 'contao_tl_iso_product_collection'),
                            $this->formatStatus(
                                $this->workflowManager->getTransactionWorkflow(),
                                $row['transaction_status'],
                            ),
                        );
                    }

                    if ($row['delivery_status']) {
                        $lines[] = sprintf('<div>%s: %s</div>',
                            $this->translator->trans('tl_iso_product_collection.delivery_status.2', [], 'contao_tl_iso_product_collection'),
                            $this->formatStatus(
                                $this->workflowManager->getDeliveryWorkflow(),
                                $row['delivery_status'],
                            ),
                        );
                    }

                    $args[$i] = implode('', $lines);
                    break;
            }
        }

        return $args;
    }

    private function formatAddress(array $row, string $field): string
    {
        [$type] = explode('_', $field);
        $addressConfig = $this->addressConfigFactory->createFromConfig(ConfigModel::findById($row['config_id']), $type);

        // Remove phone number and email from backend view
        $fields = array_diff($addressConfig->getFields(), ['email', 'phone']);
        $addressConfig = new AddressConfig($fields, $addressConfig->getMandatory(), $addressConfig->getCountryCodes(), $addressConfig->getDefaultCountryCode());

        return $this->twig->render(
            '@Contao/backend/isotope/address.html.twig',
            $this->addressFormatter->getData(AddressModel::findById($row[$field]), $addressConfig),
        );
    }

    private function formatStatus(Workflow $workflow, string|null $value, string $element = 'span'): string
    {
        if (!$value) {
            return '';
        }

        $metadata = $workflow->getMetadataStore()->getPlaceMetadata($value);

        if (empty($metadata['color'])) {
            return $metadata['name'] ?? $value;
        }

        return sprintf(
            '<span style="%s">%s</span>',
            ColorUtil::getCssBackgroundStyles($metadata['color']),
            $metadata['name'],
        );
    }
}
