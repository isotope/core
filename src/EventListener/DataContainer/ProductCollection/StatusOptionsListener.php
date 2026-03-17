<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\EventListener\DataContainer\ProductCollection;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\DataContainer;
use Isotope\CoreBundle\Model\ProductCollection;
use Isotope\CoreBundle\Store\Order\WorkflowManager;
use Symfony\Component\Workflow\Workflow;

class StatusOptionsListener
{
    public function __construct(
        private readonly WorkflowManager $workflowManager,
    ) {
    }

    #[AsCallback('tl_iso_product_collection', 'fields.order_status.options')]
    public function getOrderOptions(DataContainer $dc): array
    {
        $collection = $dc->id ? ProductCollection::findbyId($dc->id) : null;

        return $this->buildOptions(
            $this->workflowManager->getOrderWorkflow(),
            $collection,
            $collection?->getOrderStatus(),
        );
    }

    #[AsCallback('tl_iso_product_collection', 'fields.transaction_status.options')]
    public function getTransactionOptions(DataContainer $dc): array
    {
        $collection = $dc->id ? ProductCollection::findbyId($dc->id) : null;

        return $this->buildOptions(
            $this->workflowManager->getTransactionWorkflow(),
            $collection,
            $collection?->getTransactionStatus(),
        );
    }

    #[AsCallback('tl_iso_product_collection', 'fields.delivery_status.options')]
    public function getDeliveryOptions(DataContainer $dc): array
    {
        $collection = $dc->id ? ProductCollection::findbyId($dc->id) : null;

        return $this->buildOptions(
            $this->workflowManager->getDeliveryWorkflow(),
            $collection,
            $collection?->getDeliveryStatus(),
        );
    }

    /**
     * @return array<string, string>
     */
    private function buildOptions(Workflow $workflow, ProductCollection|null $collection, string|null $current): array
    {
        $options = [];

        if (!$collection) {
            foreach ($this->workflowManager->getOrderWorkflow()->getDefinition()->getPlaces() as $place) {
                $options[$place] = $workflow->getMetadataStore()->getPlaceMetadata($place)['name'] ?? $place;
            }

            return $options;
        }

        if ($current) {
            $options[$current] = $workflow->getMetadataStore()->getPlaceMetadata($current)['name'] ?? $current;
        }

        foreach ($workflow->getEnabledTransitions($collection) as $transition) {
            $status = $transition->getTos()[0];
            $options[$transition->getName()] = $workflow->getMetadataStore()->getPlaceMetadata($status)['name'] ?? $status;
        }

        return $options;
    }
}
