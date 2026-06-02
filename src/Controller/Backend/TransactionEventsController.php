<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Controller\Backend;

use Contao\Config;
use Contao\Controller;
use Contao\CoreBundle\Controller\Backend\AbstractBackendController;
use Contao\CoreBundle\DataContainer\ValueFormatter;
use Contao\CoreBundle\Security\ContaoCorePermissions;
use Contao\CoreBundle\Security\DataContainer\ReadAction;
use Contao\Date;
use Isotope\CoreBundle\Model\OrderTransactionEventModel;
use Isotope\CoreBundle\Model\ProductCollectionModel;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[Route('%contao.backend.route_prefix%/isotope-transaction-events/{transactionId}', requirements: ['transactionId' => '\d+'], defaults: ['_scope' => 'backend'])]
class TransactionEventsController extends AbstractBackendController
{
    public function __construct(
        #[Autowire(service: 'contao.data_container.value_formatter')] private readonly ValueFormatter $valueFormatter,
    ) {
    }

    public function __invoke(int $transactionId): Response
    {
        $this->initializeContaoFramework();
        Controller::loadDataContainer('tl_iso_order_transaction_event');

        if (!$order = ProductCollectionModel::findOneBy(["id=(SELECT pid FROM tl_iso_order_transaction WHERE id=$transactionId)"], [])) {
            throw new NotFoundHttpException();
        }

        $this->denyAccessUnlessGranted(ContaoCorePermissions::DC_PREFIX.'tl_iso_product_collection', new ReadAction('tl_iso_product_collection', $order->row()));

        $events = [];
        $models = OrderTransactionEventModel::findBy('pid', $transactionId);

        foreach (($models ?? []) as $model) {
            $row = [
                'id' => $model->id,
                'tstamp' => Date::parse(Config::get('datimFormat'), $model->tstamp),
                'payload' => json_encode(json_decode($model->payload, true, flags: JSON_THROW_ON_ERROR), flags: JSON_PRETTY_PRINT),
                'values' => [],
            ];

            foreach ($GLOBALS['TL_DCA']['tl_iso_order_transaction_event']['fields'] as $field => $config) {
                if ($config['eval']['doNotShow'] ?? false) {
                    continue;
                }

                if (empty($model->$field)) {
                    continue;
                }

                $row['values'][$field] = $this->valueFormatter->format('tl_iso_order_transaction_event', $field, $model->$field, null);
            }

            $events[] = $row;
        }

        return $this->render('@Contao/backend/isotope/transaction-events.html.twig', [
            'headline' => 'Webhook Events',
            'events' => $events,
        ]);
    }
}
