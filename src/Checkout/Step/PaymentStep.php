<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Checkout\Step;

use Contao\CoreBundle\Exception\RedirectResponseException;
use Isotope\CoreBundle\Checkout\Payment\PaymentInterface;
use Isotope\CoreBundle\DependencyInjection\Attribute\AsCheckoutStep;
use Isotope\CoreBundle\Model\PaymentModel;
use Isotope\CoreBundle\ModelType\PaymentRegistry;
use Isotope\CoreBundle\ProductCollection\Cart\Cart;
use Isotope\CoreBundle\Store\CartManager;
use Isotope\CoreBundle\Store\Context\StoreContext;
use Isotope\CoreBundle\Twig\TemplateMarkupFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsCheckoutStep(priority: 40)]
class PaymentStep extends AbstractCheckoutStep
{
    public function __construct(
        private readonly PaymentRegistry $paymentRegistry,
        private readonly CartManager $cartManager,
        private readonly TranslatorInterface $translator,
        TemplateMarkupFactory $markupFactory,
        EventDispatcherInterface $eventDispatcher,
    ) {
        parent::__construct($markupFactory, $eventDispatcher);
    }

    public function getSummary(Request $request, StoreContext $context, array $config): CheckoutStepSummary|null
    {
        if (!$this->requiresPayment($context, $config)) {
            return null;
        }

        $cart = $context->getCart();
        $payments = $this->getPaymentMethods($context->getPaymentIds(), $context);
        $current = $this->getCurrent($cart, $context, $config);

        if (
            $current
            && !$this->isEditing($request)
            && !$current->hasError()
            && null !== array_find($payments, fn (PaymentInterface $p) => $p->getId() === $current->getId())
        ) {
            $cart->setPaymentMethod($current);
            $cart->setModified();

            return $this->createSummary(
                $this->dispatchEvent($request, true, $config),
                'payment',
                [
                    'label' => $current->getLabel(),
                    'review' => $current->review(),
                    'editable' => \count($payments) > 1,
                ],
            );
        }

        if (
            $this->isSubmitted($request)
            && $newId = $request->request->getInt('payment_id')
        ) {
            if (
                $cart->getPaymentMethod()?->getId() !== $newId
                && null !== ($payment = array_find($payments, fn (PaymentInterface $p) => $p->getId() === $newId))
            ) {
                $cart->setPaymentMethod($payment);
                $cart->setModified();
                $this->cartManager->recalculate($cart, $context);
            }

            throw new RedirectResponseException($request->getRequestUri());
        }

        return $this->createSummary(
            $this->dispatchEvent($request, false, $config),
            'payment',
            [
                'current' => $current,
                'options' => $payments,
            ],
        );
    }

    protected function getType(): string
    {
        return 'payment';
    }

    protected function getTitle(): string|null
    {
        return $this->translator->trans('MSC.checkout_payment', [], 'contao_default');
    }

    private function getCurrent(Cart $cart, StoreContext $context, array $config): PaymentInterface|null
    {
        if ($payment = $cart->getPaymentMethod()) {
            return $payment;
        }

        $skippable = explode(',', $config['iso_checkout_skippable'] ?? '');

        if (\in_array('payment_method', $skippable, true) && $id = ($context->getPaymentIds()[0] ?? null)) {
            return $this->paymentRegistry->get(PaymentModel::findById($id), $context);
        }

        return null;
    }

    /**
     * @param array<int> $ids
     * @return array<PaymentInterface>
     */
    private function getPaymentMethods(array $ids, StoreContext $context): array
    {
        $payments = $this->paymentRegistry->get(PaymentModel::findMultipleByIds($ids), $context, true);

        if (!$payments) {
            return [];
        }

        return array_filter(
            $payments,
            static fn (PaymentInterface $payment) => $payment->isAvailable($context),
        );
    }

    private function requiresPayment(StoreContext $context, array $config): bool
    {
        return $context->getCart()->getTotal()?->getTotalPrice() > 0;
    }
}
