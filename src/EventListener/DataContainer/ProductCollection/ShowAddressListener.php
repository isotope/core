<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\EventListener\DataContainer\ProductCollection;

use Contao\CoreBundle\DataContainer\DataContainerOperation;
use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Isotope\CoreBundle\Exception\RuntimeException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsCallback('tl_iso_product_collection', 'list.operations.show_billing.button')]
#[AsCallback('tl_iso_product_collection', 'list.operations.show_shipping.button')]
class ShowAddressListener
{
    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly TranslatorInterface $translator,
    ) {
    }

    public function __invoke(DataContainerOperation $operation): void
    {
        $addressId = match ($operation->getName()) {
            'show_billing' => $operation->getRecord()['billing_address_id'],
            'show_shipping' => $operation->getRecord()['shipping_address_id'],
            default => throw new RuntimeException('Invalid operation in '.__METHOD__),
        };

        if (!$addressId) {
            $operation->disable();

            return;
        }

        $operation->setUrl($this->urlGenerator->generate('contao_backend', ['do' => 'iso_orders', 'table' => 'tl_iso_address', 'act' => 'show', 'id' => $addressId]));

        $operation['attributes']->set(
            'onclick',
            \sprintf(
                "Backend.openModalIframe({title:'%s', url:`\${this.href}&popup=1`});return false",
                $this->translator->trans("tl_iso_product_collection.{$operation->getName()}.1", [$operation->getRecord()['id']], 'contao_tl_iso_product_collection'),
            ),
        );
    }
}
