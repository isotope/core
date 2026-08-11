<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\EventListener\DataContainer\Content;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Isotope\CoreBundle\Store\Order\WorkflowManager;

#[AsCallback('tl_content', 'fields.iso_transaction_cancelled.options')]
class TransactionStatusOptionsListener
{
    public function __construct(private readonly WorkflowManager $workflowManager)
    {
    }

    public function __invoke(): array
    {
        $options = [];
        $workflow = $this->workflowManager->getTransactionWorkflow();

        foreach ($workflow->getDefinition()->getPlaces() as $place) {
            $options[$place] = $workflow->getMetadataStore()->getPlaceMetadata($place)['name'] ?? $place;
        }

        return $options;
    }
}
