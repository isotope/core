<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\EventListener\DataContainer\OrderDocument;

use Contao\CoreBundle\DataContainer\DataContainerOperation;
use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\CoreBundle\Security\ContaoCorePermissions;
use Isotope\CoreBundle\Controller\Backend\DocumentCreateController;
use Isotope\CoreBundle\Controller\Backend\DocumentPopupController;
use Isotope\CoreBundle\Controller\DocumentController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\UriSigner;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class OperationsListener
{
    public function __construct(
        private readonly Security $security,
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    #[AsCallback('tl_iso_order_document', 'list.global_operations.create.button')]
    public function create(DataContainerOperation $operation): void
    {
        // Cannot vote on CreateAction because DCA is "closed" and "notEditable"
        if (!$this->security->isGranted(ContaoCorePermissions::USER_CAN_OPERATE_ON_TABLE, 'tl_iso_order_document::create')) {
            $operation->hide();

            return;
        }

        $operation->setUrl($this->urlGenerator->generate(
            DocumentCreateController::class,
            ['orderId' => $operation->getDataContainer()->currentPid],
        ));
    }

    #[AsCallback('tl_iso_order_document', 'list.operations.download.button')]
    public function download(DataContainerOperation $operation): void
    {
        if (!($operation->getRecord()['uniqid'] ?? null)) {
            $operation->hide();

            return;
        }

        $url = $this->urlGenerator->generate(
            DocumentController::class,
            ['uuid' => $operation->getRecord()['uniqid']],
        );

        $operation->setUrl($url);
        $operation['attributes']->set('target', '_blank');
    }

    #[AsCallback('tl_iso_order_document', 'list.operations.show.button')]
    public function show(DataContainerOperation $operation): void
    {
        $operation->setUrl($this->urlGenerator->generate(
            DocumentPopupController::class,
            ['id' => $operation->getRecord()['id']],
        ));
    }
}
