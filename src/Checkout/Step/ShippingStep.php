<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Checkout\Step;

use Contao\CoreBundle\Exception\RedirectResponseException;
use Isotope\CoreBundle\Checkout\Shipping\ShippingInterface;
use Isotope\CoreBundle\DependencyInjection\Attribute\AsCheckoutStep;
use Isotope\CoreBundle\Model\ShippingModel;
use Isotope\CoreBundle\ModelType\ShippingRegistry;
use Isotope\CoreBundle\Product\Extension\ShippingExtension;
use Isotope\CoreBundle\ProductCollection\Cart\Cart;
use Isotope\CoreBundle\Store\Context\StoreContext;
use Isotope\CoreBundle\Twig\TemplateMarkupFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @internal
 */
#[AsCheckoutStep(priority: 60)]
class ShippingStep extends AbstractCheckoutStep
{
    public function __construct(
        private readonly ShippingRegistry $shippingRegistry,
        private readonly TranslatorInterface $translator,
        TemplateMarkupFactory $markupFactory,
        EventDispatcherInterface $eventDispatcher,
    ) {
        parent::__construct($markupFactory, $eventDispatcher);
    }

    public function getSummary(Request $request, StoreContext $context, array $config): CheckoutStepSummary|null
    {
        $cart = $context->getCart();

        if (!$cart || !$this->requiresShipping($cart)) {
            return null;
        }

        $event = $this->dispatchEvent($context, $request, $config);

        $shippings = $this->getShippingMethods($context->getShippingIds(), $context);
        $current = $this->getCurrent($cart, $context, $config);

        if (
            $current
            && !$this->isEditing($request)
            && !$event->hasError()
            && !$current->hasError()
            && null !== array_find($shippings, static fn (ShippingInterface $s) => $s->getId() === $current->getId())
        ) {
            $cart->setShippingMethod($current);
            $cart->setModified();

            return $this->createSummary(
                $event,
                true,
                'shipping',
                [
                    'label' => $current->getLabel(),
                    'review' => $current->review(),
                    'editable' => \count($shippings) > 1,
                ],
            );
        }

        if (
            !$event->hasError()
            && $this->isSubmitted($request)
            && $newId = $request->request->getInt('shipping_id')
        ) {
            if (
                $cart->getShippingMethod()?->getId() !== $newId
                && null !== ($shipping = array_find($shippings, static fn (ShippingInterface $s) => $s->getId() === $newId))
            ) {
                $cart->setShippingMethod($shipping);
                $cart->setModified();
            }

            throw new RedirectResponseException($request->getRequestUri());
        }

        return $this->createSummary(
            $event,
            false,
            'shipping',
            [
                'current' => $current,
                'options' => $shippings,
            ],
        );
    }

    protected function getType(): string
    {
        return 'shipping';
    }

    protected function getTitle(): string|null
    {
        return $this->translator->trans('MSC.checkout_shipping', [], 'contao_default');
    }

    private function getCurrent(Cart $cart, StoreContext $context, array $config): ShippingInterface|null
    {
        if ($shipping = $cart->getShippingMethod()) {
            return $shipping;
        }

        $skippable = explode(',', $config['iso_checkout_skippable'] ?? '');

        if (\in_array('shipping_method', $skippable, true) && $id = ($context->getShippingIds()[0] ?? null)) {
            return $this->shippingRegistry->get(ShippingModel::findById($id), $context);
        }

        return null;
    }

    /**
     * @param array<int> $ids
     *
     * @return array<ShippingInterface>
     */
    private function getShippingMethods(array $ids, StoreContext $context): array
    {
        /** @var list<ShippingInterface> $shippings */
        $shippings = $this->shippingRegistry->get(ShippingModel::findMultipleByIds($ids), $context, true);

        if (!$shippings) {
            return [];
        }

        return array_filter(
            $shippings,
            static fn (ShippingInterface $shipping) => $shipping->isAvailable(),
        );
    }

    private function requiresShipping(Cart $cart): bool
    {
        foreach ($cart->getLineItems()->products() as $item) {
            if (!$item->getProduct()?->getExtension(ShippingExtension::class)?->isExemptFromShipping()) {
                return true;
            }
        }

        return false;
    }
}
