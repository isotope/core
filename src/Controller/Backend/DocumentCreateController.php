<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Controller\Backend;

use Contao\Controller;
use Contao\CoreBundle\Controller\Backend\AbstractBackendController;
use Contao\CoreBundle\DataContainer\DataContainerGlobalOperationsBuilder;
use Contao\CoreBundle\Exception\PageNotFoundException;
use Contao\CoreBundle\Security\ContaoCorePermissions;
use Contao\CoreBundle\Security\DataContainer\ReadAction;
use Contao\SelectMenu;
use Contao\System;
use Isotope\CoreBundle\Document\DocumentViewerInterface;
use Isotope\CoreBundle\Model\DocumentModel;
use Isotope\CoreBundle\Model\ProductCollectionModel;
use Isotope\CoreBundle\ModelType\DocumentRegistry;
use Isotope\CoreBundle\ProductCollection\Order\OrderFactory;
use Isotope\CoreBundle\Store\DocumentManager;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsController]
#[Route(
    '%contao.backend.route_prefix%/isotope-document/create/{orderId}',
    requirements: ['orderId' => '\d+'],
    defaults: ['_scope' => 'backend'],
)]
class DocumentCreateController extends AbstractBackendController
{
    public function __construct(
        private readonly OrderFactory $orderFactory,
        private readonly DocumentRegistry $documentRegistry,
        private readonly DocumentManager $documentManager,
        #[Autowire(service: 'contao.data_container.global_operations_builder')] private readonly DataContainerGlobalOperationsBuilder $globalOperationsBuilder,
        private readonly TranslatorInterface $translator,
    ) {
    }

    public function __invoke(Request $request, int $orderId): Response
    {
        // Cannot vote on CreateAction because DCA is "closed" and "notEditable"
        $this->denyAccessUnlessGranted(ContaoCorePermissions::USER_CAN_OPERATE_ON_TABLE, 'tl_iso_order_document::create');

        $this->initializeContaoFramework();
        System::loadLanguageFile('default');

        $order = $this->orderFactory->loadFromId($orderId);

        if (!$order) {
            throw new PageNotFoundException();
        }

        $this->denyAccessUnlessGranted(
            ContaoCorePermissions::DC_PREFIX.'tl_iso_product_collection',
            new ReadAction('tl_iso_product_collection', ProductCollectionModel::findById($orderId)->row()),
        );

        Controller::loadDataContainer('tl_iso_order_document');
        System::loadLanguageFile('tl_iso_order_document');

        $widget = new SelectMenu(SelectMenu::getAttributesFromDca(
            [
                'label' => &$GLOBALS['TL_LANG']['tl_iso_order_document']['type'],
                'foreignKey' => 'tl_iso_document.name',
                'eval' => [
                    'mandatory' => true,
                    'includeBlankOption' => true,
                    'submitOnChange' => true,
                ],
            ],
            'type',
            null,
            'type',
            'tl_iso_order_document',
        ));

        $documentId = null;
        $document = null;

        if ($request->isMethod(Request::METHOD_POST)) {
            $widget->validate();

            if (!$widget->hasErrors()) {
                $documentId = (int) $widget->value;
                $document = $this->documentRegistry->get(DocumentModel::findById($documentId), $order->getContext());

                if ($request->request->has('save')) {
                    $this->documentManager->create($order, $document);

                    return new RedirectResponse($this->generateUrl('contao_backend', [
                        'do' => 'iso_orders',
                        'table' => 'tl_iso_order_document',
                        'id' => $orderId,
                    ]));
                }
            }
        }

        $operations = $this->globalOperationsBuilder
            ->initialize('tl_iso_order_document')
            ->addBackButton("do=iso_orders&table=tl_iso_order_document&id=$orderId")
        ;

        $previewUrl = $documentId ? $this->generateUrl(DocumentPreviewController::class, ['orderId' => $orderId, 'documentId' => $documentId]) : null;

        return $this->render('@Contao/backend/isotope/document-create.html.twig', [
            'title' => $this->translator->trans('tl_iso_order_document.create.0', [], 'contao_tl_iso_order_document'),
            'headline' => $this->translator->trans('tl_iso_order_document.create.1', [$orderId], 'contao_tl_iso_order_document'),
            'operations' => $operations,
            'widget' => $widget->parse(),
            'has_errors' => $widget->hasErrors(),
            'viewer_markup' => $document instanceof DocumentViewerInterface ? $document->getViewerMarkup($previewUrl) : null,
            'preview_url' => $previewUrl,
        ]);
    }
}
