<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\EventListener\DataContainer\ProductCollection;

use Contao\CoreBundle\Event\DataContainerRecordLabelEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsEventListener]
class DataContainerRecordLabelListener
{
    public function __construct(private readonly TranslatorInterface $translator)
    {
    }

    public function __invoke(DataContainerRecordLabelEvent $event): void
    {
        if (!str_starts_with($event->getIdentifier(), 'contao.db.tl_iso_product_collection.')) {
            return;
        }

        $event->setLabel($this->translator->trans(
            'tl_iso_product_collection.recordLabel',
            [$event->getData()['document_number']],
            'contao_tl_iso_product_collection'
        ));
    }
}
