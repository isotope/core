<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\EventListener\DataContainer\ProductCollection;

use Codefog\HasteBundle\Formatter;
use Contao\Config;
use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\CoreBundle\Exception\PageNotFoundException;
use Contao\DataContainer;
use Isotope\CoreBundle\ProductCollection\Order\Order;
use Isotope\CoreBundle\ProductCollection\Order\OrderFactory;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

#[AsCallback('tl_iso_product_collection', 'fields.details.input_field')]
#[AsCallback('tl_iso_product_collection', 'fields.billing_address_data.input_field')]
#[AsCallback('tl_iso_product_collection', 'fields.shipping_address_data.input_field')]
class OrderDetailsListener
{
    public function __construct(
        private readonly OrderFactory $loader,
        private readonly Environment $twig,
        private readonly Formatter $formatter,
        private readonly TranslatorInterface $translator,
    ) {
    }

    public function __invoke(DataContainer $dc): string
    {
        $order = $this->getOrder($dc);

        return match ($dc->field) {
            'details' => $this->twig->render('@Contao/backend/isotope/order-details.html.twig', [
                'order' => $order,
                'datimFormat' => Config::get('datimFormat'),
            ]),
            'billing_address_data' => $this->show('tl_iso_address', $order->getBillingAddress()->row(), 'billing_address_empty'),
            'shipping_address_data' => $this->show('tl_iso_address', $order->getShippingAddress()?->row(), 'shipping_address_empty'),
        };
    }

    private function getOrder(DataContainer $dc): Order
    {
        $order = $this->loader->loadFromid((int) $dc->id);

        if (!$order) {
            throw new PageNotFoundException();
        }

        return $order;
    }

    private function show(string $table, array|null $data, string $empty): string
    {
        if (!$data) {
            return \sprintf(
                '<div class="tl_gerror">%s</div>',
                $this->translator->trans('tl_iso_product_collection.'.$empty, [], 'contao_tl_iso_product_collection'),
            );
        }

        $entries = [];

        foreach ($data as $field => $value) {
            $entries[$this->formatter->dcaLabel($table, $field)] = $this->formatter->dcaValue($table, $field, $value);
        }

        return $this->twig->render(
            '@Contao/backend/data_container/table/show.html.twig',
            ['data' => [$table => [$entries]]],
        );
    }
}
