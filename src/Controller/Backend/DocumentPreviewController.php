<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Controller\Backend;

use Contao\CoreBundle\Controller\AbstractController;
use Contao\CoreBundle\Exception\PageNotFoundException;
use Contao\CoreBundle\Security\ContaoCorePermissions;
use Contao\CoreBundle\Security\DataContainer\ReadAction;
use Contao\System;
use Isotope\CoreBundle\Model\DocumentModel;
use Isotope\CoreBundle\Model\ProductCollectionModel;
use Isotope\CoreBundle\ModelType\DocumentRegistry;
use Isotope\CoreBundle\ProductCollection\Order\OrderFactory;
use Isotope\CoreBundle\Store\DocumentManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[Route(
    '%contao.backend.route_prefix%/isotope-document/preview/{orderId}/{documentId}.pdf',
    requirements: ['orderId' => '\d+', 'documentId' => '\d+'],
    defaults: ['_scope' => 'backend'],
)]
class DocumentPreviewController extends AbstractController
{
    public function __construct(
        private readonly OrderFactory $orderFactory,
        private readonly DocumentRegistry $documentRegistry,
        private readonly DocumentManager $documentManager,
    ) {
    }

    public function __invoke(Request $request, int $orderId, int $documentId): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $this->initializeContaoFramework();
        System::loadLanguageFile('default');

        $documentModel = DocumentModel::findById($documentId);

        if (
            !($order = $this->orderFactory->loadFromId($orderId))
            || !($document = $this->documentRegistry->get($documentModel, $order->getContext()))
        ) {
            throw new PageNotFoundException();
        }

        $this->denyAccessUnlessGranted(
            ContaoCorePermissions::DC_PREFIX.'tl_iso_product_collection',
            new ReadAction('tl_iso_product_collection', ProductCollectionModel::findById($orderId)->row()),
        );

        return $this->documentManager->preview($order, $document);
    }
}
