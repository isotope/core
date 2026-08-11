<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\ProductCollection\Action;

use Contao\CoreBundle\Routing\PageFinder;
use Isotope\CoreBundle\DependencyInjection\Attribute\AsProductCollectionAction;
use Isotope\CoreBundle\HttpFoundation\ActionResult;
use Isotope\CoreBundle\ProductCollection\AbstractProductCollection;
use Isotope\CoreBundle\ProductCollection\Order\Order;
use Isotope\CoreBundle\Store\Order\TransactionState;
use Isotope\CoreBundle\Store\OrderManager;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

#[AsProductCollectionAction]
class InitPaymentAction extends AbstractProductCollectionAction
{
    public function __construct(
        private readonly string $type,
        private readonly PageFinder $pageFinder,
        private readonly TranslatorInterface $translator,
        private readonly OrderManager $orderManager,
        Environment $twig,
    ) {
        parent::__construct($twig);
    }

    public function supports(AbstractProductCollection $collection, array $context, array $options = []): bool
    {
        return $collection instanceof Order
            && null !== $collection->getPaymentMethod()
            && TransactionState::Pending === $collection->getTransactionStatus()?->getTransactionState();
    }

    /**
     * @param Order $collection
     */
    public function render(AbstractProductCollection $collection, array $context, array $options = []): string
    {
        return $this->renderView($this->type, [
            'label' => $this->translator->trans('MSC.payment_complete', [], 'contao_default'),
            ...$options,
        ]);
    }

    /**
     * @param Order $collection
     */
    public function handle(AbstractProductCollection $collection, array $context, array $options = []): ActionResult
    {
        return $this->orderManager->initPayment($collection, $collection->getPaymentMethod(), $this->pageFinder->getCurrentPage());
    }
}
