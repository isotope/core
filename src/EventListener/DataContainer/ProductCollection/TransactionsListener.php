<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\EventListener\DataContainer\ProductCollection;

use Contao\Config;
use Contao\CoreBundle\DataContainer\DataContainerOperationsBuilder;
use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\CoreBundle\Exception\PageNotFoundException;
use Contao\CoreBundle\String\HtmlAttributes;
use Contao\DataContainer;
use Contao\Date;
use Doctrine\DBAL\Connection;
use Isotope\CoreBundle\Controller\Backend\TransactionEventsController;
use Isotope\CoreBundle\ProductCollection\Order\Order;
use Isotope\CoreBundle\ProductCollection\Order\OrderFactory;
use Isotope\CoreBundle\ProductCollection\OrderTransaction\TransactionStatus;
use Isotope\CoreBundle\Store\Price\CalculatedPrice;
use Isotope\CoreBundle\Store\Price\PriceFormatter;
use Symfony\Component\Asset\Packages;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

#[AsCallback('tl_iso_product_collection', 'fields.transaction_data.input_field')]
class TransactionsListener
{
    public function __construct(
        private readonly OrderFactory $loader,
        private readonly Environment $twig,
        private readonly TranslatorInterface $translator,
        private readonly Connection $connection,
        private readonly PriceFormatter $priceFormatter,
        #[Autowire(service: 'contao.data_container.operations_builder')] private readonly DataContainerOperationsBuilder $operationsBuilder,
        private readonly Packages $packages,
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function __invoke(DataContainer $dc): string
    {
        $order = $this->getOrder($dc);

        $records = [];
        $transactions = $this->connection->fetchAllAssociative(
            <<<'SQL'
                SELECT t.*, COUNT(e.id) AS event_count
                FROM tl_iso_order_transaction t
                    LEFT JOIN tl_iso_order_transaction_event e ON e.pid=t.id
                WHERE t.pid=?
                GROUP BY t.id
                ORDER BY t.tstamp
                SQL,
            [$order->getId()],
        );

        foreach ($transactions as $transaction) {
            $operations = $this->operationsBuilder->initialize('tl_iso_order_transaction');
            if ($transaction['event_count'] > 0) {
                $operations->append([
                    'href' => $this->urlGenerator->generate(TransactionEventsController::class, ['transactionId' => $transaction['id']]),
                    'icon' => $this->packages->getUrl('images/webhook.svg', 'isotope'),
                    'label' => 'Webhooks',
                    'attributes' => (new HtmlAttributes())->set('onclick', "Backend.openModalIframe({title:'Webhooks', url:this.href+'?popup=1'});return false"),
                ]);
            } else {
                $operations->append([
                    'icon' => $this->packages->getUrl('images/webhook--disabled.svg', 'isotope'),
                    'label' => 'Webhooks',
                ]);
            }

            if ($status = $transaction['status']) {
                $status = TransactionStatus::format($transaction['status'], $this->translator);
            }

            $records[] = [
                'id' => $transaction['id'],
                'is_draft' => false,
                'columns_colspan' => null,
                'operations' => $operations,
                'columns' => [
                    'uniqid' => $transaction['uniqid'],
                    'created' => Date::parse(Config::get('datimFormat'), $transaction['created']),
                    'completed' => $transaction['completed'] ? Date::parse(Config::get('datimFormat'), $transaction['completed']) : '-',
                    'label' => $transaction['label'],
                    'status' => $status,
                    'amount' => $this->priceFormatter->toHtml(new CalculatedPrice((float) $transaction['amount'], (float) $transaction['amount']), $order->getContext()),
                ],
            ];
        }

        return $this->twig->render('@Contao/backend/data_container/table/view/list.html.twig', [
            'table_name' => 'tl_iso_order_transaction',
            'table_headers' => [
                'uniqid' => $this->translator->trans('tl_iso_order_transaction.uniqid.0', [], 'contao_tl_iso_order_transaction'),
                'created' => $this->translator->trans('tl_iso_order_transaction.created.0', [], 'contao_tl_iso_order_transaction'),
                'completed' => $this->translator->trans('tl_iso_order_transaction.completed.0', [], 'contao_tl_iso_order_transaction'),
                'label' => $this->translator->trans('tl_iso_order_transaction.label.0', [], 'contao_tl_iso_order_transaction'),
                'status' => $this->translator->trans('tl_iso_order_transaction.status.0', [], 'contao_tl_iso_order_transaction'),
                'amount' => $this->translator->trans('tl_iso_order_transaction.amount.0', [], 'contao_tl_iso_order_transaction'),
            ],
            'records' => $records,
            'show_columns' => true,
            'as_picker' => false,
            'limit_height' => false,
            'message' => '<h3>'.$this->translator->trans('tl_iso_order_transaction.payments', [], 'contao_tl_iso_order_transaction').'</h3>',
            'global_operations' => '',
            'as_select' => false,
            'panel_active' => '',
            'order_by' => '',
            'primary_only' => false,
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
