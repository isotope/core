<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\EventListener\DataContainer\ProductCollection;

use Contao\Config;
use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\CoreBundle\Exception\PageNotFoundException;
use Contao\DataContainer;
use Isotope\CoreBundle\ProductCollection\Order\Order;
use Isotope\CoreBundle\ProductCollection\Order\OrderFactory;
use Twig\Environment;

#[AsCallback('tl_iso_product_collection', 'fields.details.input_field')]
class OrderDetailsListener
{
    public function __construct(
        private readonly OrderFactory $loader,
        private readonly Environment $twig,
    ) {
    }

    public function __invoke(DataContainer $dc): string
    {
        $order = $this->getOrder($dc);

        return $this->twig->render('@Contao/backend/isotope/order-details.html.twig', [
            'order' => $order,
            'datimFormat' => Config::get('datimFormat'),
        ]);
    }

    private function getOrder(DataContainer $dc): Order
    {
        $order = $this->loader->loadFromid((int) $dc->id);

        if (!$order) {
            throw new PageNotFoundException();
        }

        return $order;
    }
}
