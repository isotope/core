<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\EventListener\DataContainer\Payment;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\DataContainer;
use Isotope\CoreBundle\Model\ProductCollectionModel;
use Isotope\CoreBundle\Store\Order\WorkflowManager;

class StatusOptionsListener
{
    public function __construct(private readonly WorkflowManager $workflowManager)
    {
    }

    #[AsCallback('tl_iso_payment', 'fields.processing_status.options')]
    #[AsCallback('tl_iso_payment', 'fields.paid_status.options')]
    #[AsCallback('tl_iso_payment', 'fields.failed_status.options')]
    public function __invoke(DataContainer $dc): array
    {
        $workflow = $this->workflowManager->getTransactionWorkflow();
        $collection = new ProductCollectionModel();
        $collection->transaction_status = array_key_first($workflow->getMarking($collection)->getPlaces());

        $options = [];

        foreach ($workflow->getEnabledTransitions($collection) as $transition) {
            $status = $transition->getTos()[0];
            $options[$transition->getName()] = $workflow->getMetadataStore()->getPlaceMetadata($status)['name'] ?? $status;
        }

        return $options;
    }
}
