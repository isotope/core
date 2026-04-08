<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Store\Order;

use Doctrine\DBAL\Connection;
use Symfony\Component\Workflow\DefinitionBuilder;
use Symfony\Component\Workflow\MarkingStore\MethodMarkingStore;
use Symfony\Component\Workflow\Metadata\InMemoryMetadataStore;
use Symfony\Component\Workflow\Transition;
use Symfony\Component\Workflow\Workflow;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Service\ResetInterface;

/**
 * @internal
 */
class WorkflowManager implements ResetInterface
{
    private Workflow|null $order = null;
    private Workflow|null $transaction = null;
    private Workflow|null $delivery = null;

    public function __construct(
        private readonly Connection $connection,
        private readonly EventDispatcherInterface $eventDispatcher,
    ) {
    }

    public function reset(): void
    {
        $this->order = null;
        $this->transaction = null;
        $this->delivery = null;
    }

    public function getOrderWorkflow(): Workflow
    {
        if (!$this->order) {
            $this->load();
        }

        return $this->order;
    }

    public function getTransactionWorkflow(): Workflow
    {
        if (!$this->transaction) {
            $this->load();
        }

        return $this->transaction;
    }

    public function getDeliveryWorkflow(): Workflow
    {
        if (!$this->delivery) {
            $this->load();
        }

        return $this->delivery;
    }

    private function load(): void
    {
        $metadata = [];
        $definitions = [
            'order' => new DefinitionBuilder(),
            'transaction' => new DefinitionBuilder(),
            'delivery' => new DefinitionBuilder(),
        ];

        $records = $this->connection->fetchAllAssociative('SELECT * FROM tl_iso_orderstatus');

        foreach ($records as $row) {
            if (!isset($definitions[$row['type']])) {
                continue;
            }

            $definition = $definitions[$row['type']];

            foreach (explode(',', $row['workflow']) as $to) {
                $definition->addTransition(new Transition($to, $row['alias'], $to));
                $metadata[$row['type']][$row['alias']] = $row;
            }
        }

        $this->order = new Workflow(
            $definitions['order']
                ->setMetadataStore(new InMemoryMetadataStore(placesMetadata: $metadata['order'] ?? []))
                ->build(),
            new MethodMarkingStore(true, 'orderStatus'),
            $this->eventDispatcher,
            'tl_iso_product_collection.order_status',
        );

        $this->transaction = new Workflow(
            $definitions['transaction']
                ->setMetadataStore(new InMemoryMetadataStore(placesMetadata: $metadata['transaction'] ?? []))
                ->build(),
            new MethodMarkingStore(true, 'transactionStatus'),
            $this->eventDispatcher,
            'tl_iso_product_collection.transaction_status',
        );

        $this->delivery = new Workflow(
            $definitions['delivery']
                ->setMetadataStore(new InMemoryMetadataStore(placesMetadata: $metadata['delivery'] ?? []))
                ->build(),
            new MethodMarkingStore(true, 'deliveryStatus'),
            $this->eventDispatcher,
            'tl_iso_product_collection.delivery_status',
        );
    }
}
