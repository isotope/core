<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Controller\Backend;

use Contao\CoreBundle\Controller\Backend\AbstractBackendController;
use Contao\CoreBundle\Exception\PageNotFoundException;
use Contao\CoreBundle\Security\ContaoCorePermissions;
use Contao\CoreBundle\Security\DataContainer\ReadAction;
use Contao\System;
use Isotope\CoreBundle\Controller\DocumentController;
use Isotope\CoreBundle\Document\DocumentViewerInterface;
use Isotope\CoreBundle\Model\DocumentModel;
use Isotope\CoreBundle\Model\OrderDocumentModel;
use Isotope\CoreBundle\ModelType\DocumentRegistry;
use Isotope\CoreBundle\ProductCollection\Order\OrderFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\UriSigner;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[AsController]
#[Route(
    '%contao.backend.route_prefix%/isotope-document/show/{id}',
    requirements: ['id' => '\d+'],
    defaults: ['_scope' => 'backend'],
)]
class DocumentPopupController extends AbstractBackendController
{
    public function __construct(
        private readonly OrderFactory $orderFactory,
        private readonly DocumentRegistry $documentRegistry,
        private readonly UriSigner $uriSigner,
    ) {
    }

    public function __invoke(Request $request, int $id): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $this->initializeContaoFramework();
        System::loadLanguageFile('default');

        $documentModel = OrderDocumentModel::findById($id);

        if (!$documentModel) {
            throw new PageNotFoundException();
        }

        $this->denyAccessUnlessGranted(
            ContaoCorePermissions::DC_PREFIX.'tl_iso_order_document',
            new ReadAction('tl_iso_order_document', $documentModel->row()),
        );

        if (
            !($order = $this->orderFactory->loadFromId($documentModel->pid))
            || !($document = $this->documentRegistry->get(DocumentModel::findById($documentModel->type), $order->getContext()))
        ) {
            throw new PageNotFoundException();
        }

        $url = $this->generateUrl(DocumentController::class, ['id' => $documentModel->id], UrlGeneratorInterface::ABSOLUTE_URL);
        $url = $this->uriSigner->sign($url);

        return $this->render('@Contao/backend/isotope/document-popup.html.twig', [
            'tstamp' => $documentModel->tstamp,
            'viewer_markup' => $document instanceof DocumentViewerInterface ? $document->getViewerMarkup($url) : null,
            'document_url' => $url,
        ]);
    }
}
