<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Checkout\Step;

use Contao\CoreBundle\Exception\RedirectResponseException;
use Isotope\CoreBundle\Checkout\Payment\PaymentInterface;
use Isotope\CoreBundle\DependencyInjection\Attribute\AsCheckoutStep;
use Isotope\CoreBundle\Model\PaymentModel;
use Isotope\CoreBundle\ModelType\PaymentRegistry;
use Isotope\CoreBundle\ProductCollection\Cart\Cart;
use Isotope\CoreBundle\Store\Context\StoreContext;
use Isotope\CoreBundle\Twig\TemplateMarkupFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @internal
 */
#[AsCheckoutStep(priority: 40)]
class PaymentStep extends AbstractCheckoutStep
{
    public function __construct(
        private readonly PaymentRegistry $paymentRegistry,
        private readonly TranslatorInterface $translator,
        TemplateMarkupFactory $markupFactory,
        EventDispatcherInterface $eventDispatcher,
    ) {
        parent::__construct($markupFactory, $eventDispatcher);
    }

    public function getSummary(Request $request, StoreContext $context, array $config): CheckoutStepSummary|null
    {
        $cart = $context->getCart();

        if (!$cart || !$this->requiresPayment($cart)) {
            return null;
        }

        $event = $this->dispatchEvent($context, $request, $config);

        $payments = $this->getPaymentMethods($context);
        $current = $this->getCurrent($cart, $context, $config);

        if (
            $current
            && !$this->isEditing($request)
            && !$event->hasError()
            && !$current->hasError()
            && null !== array_find($payments, static fn (PaymentInterface $p) => $p->getId() === $current->getId())
        ) {
            $cart->setPaymentMethod($current);
            $cart->setModified();

            // TODO: this sets the payment method on every run, should only be done if necessary

            return $this->createSummary(
                $event,
                true,
                'payment',
                [
                    'label' => $current->getLabel(),
                    'review' => $current->review(),
                    'editable' => \count($payments) > 1,
                ],
            );
        }

        if (
            !$event->hasError()
            && $this->isSubmitted($request)
            && $newId = $request->request->getInt('payment_id')
        ) {
            if (
                $cart->getPaymentMethod()?->getId() !== $newId
                && null !== ($payment = array_find($payments, static fn (PaymentInterface $p) => $p->getId() === $newId))
            ) {
                $cart->setPaymentMethod($payment);
                $cart->setModified();
            }

            throw new RedirectResponseException($request->getRequestUri());
        }

        return $this->createSummary(
            $event,
            false,
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

        if (\in_array('payment_method', explode(',', $config['iso_checkout_skippable'] ?? ''), true)) {
            return array_first($this->getPaymentMethods($context));
        }

        return null;
    }

    /**
     * @return array<PaymentInterface>
     */
    private function getPaymentMethods(StoreContext $context): array
    {
        $payments = $this->paymentRegistry->get(PaymentModel::findMultipleByIds($context->getPaymentIds()), $context, true);

        if (!$payments) {
            return [];
        }

        return array_filter(
            $payments,
            static fn (PaymentInterface $payment) => $payment->isAvailable(),
        );
    }

    private function requiresPayment(Cart $cart): bool
    {
        return $cart->getTotal()?->getTotalPrice() > 0;
    }
}
