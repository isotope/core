<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Test\Store\Order;

use Doctrine\DBAL\Connection;
use Isotope\CoreBundle\Store\Order\WorkflowManager;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;

final class WorkflowManagerTest extends TestCase
{
    public function testTerminalStatusHasNoOutgoingTransitions(): void
    {
        $connection = $this->createStub(Connection::class);
        $connection
            ->method('fetchAllAssociative')
            ->willReturn([
                ['type' => 'order', 'alias' => 'new', 'workflow' => 'completed', 'name' => 'New'],
                ['type' => 'order', 'alias' => 'completed', 'workflow' => '', 'name' => 'Completed'],
            ])
        ;

        $manager = new WorkflowManager($connection, new EventDispatcher());
        $definition = $manager->getOrderWorkflow()->getDefinition();

        $this->assertSame(['new' => 'new', 'completed' => 'completed'], $definition->getPlaces());

        $transitions = $definition->getTransitions();
        $this->assertCount(1, $transitions);
        $this->assertSame('completed', $transitions[0]->getName());
        $this->assertSame(['new'], $transitions[0]->getFroms());
        $this->assertSame(['completed'], $transitions[0]->getTos());
        $this->assertSame('Completed', $definition->getMetadataStore()->getPlaceMetadata('completed')['name']);
    }
}
