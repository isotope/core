<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\ProductCollection\Action;

use Isotope\CoreBundle\DependencyInjection\Attribute\AsProductCollectionAction;
use Isotope\CoreBundle\HttpFoundation\ActionResult;
use Isotope\CoreBundle\ProductCollection\AbstractProductCollection;
use Isotope\CoreBundle\ProductCollection\Order\Order;
use Isotope\CoreBundle\Store\OrderManager;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

#[AsProductCollectionAction]
class CancelPaymentAction extends AbstractProductCollectionAction
{
    public function __construct(
        private readonly string $type,
        private readonly OrderManager $orderManager,
        private readonly TranslatorInterface $translator,
        Environment $twig,
    ) {
        parent::__construct($twig);
    }

    public function supports(AbstractProductCollection $collection, array $context, array $options = []): bool
    {
        $status = $options['status'] ?? $context['data']['iso_transaction_cancelled'] ?? null;

        return $status && $collection instanceof Order && $this->orderManager->canCancelPayment($collection, $status);
    }

    public function render(AbstractProductCollection $collection, array $context, array $options = []): string
    {
        return $this->renderView($this->type, [
            'label' => $this->translator->trans('MSC.payment_cancel', [], 'contao_default'),
            ...$options,
        ]);
    }

    /**
     * @param Order $collection
     */
    public function handle(AbstractProductCollection $collection, array $context, array $options = []): ActionResult
    {
        $status = $options['status'] ?? $context['data']['iso_transaction_cancelled'] ?? null;
        $success = $status && $this->orderManager->cancelPayment($collection, $status);

        return new ActionResult(success: $success);
    }
}
