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
use Isotope\CoreBundle\Store\CartManager;
use Isotope\CoreBundle\Store\Context\StoreContext;
use Isotope\CoreBundle\Twig\TemplateMarkupFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsCheckoutStep(priority: 60)]
class ShippingStep extends AbstractCheckoutStep
{
    public function __construct(
        private readonly ShippingRegistry $shippingRegistry,
        private readonly CartManager $cartManager,
        private readonly TranslatorInterface $translator,
        TemplateMarkupFactory $markupFactory,
        EventDispatcherInterface $eventDispatcher,
    ) {
        parent::__construct($markupFactory, $eventDispatcher);
    }

    public function getSummary(Request $request, StoreContext $context, array $config): CheckoutStepSummary|null
    {
        if (!$this->requiresShipping($context, $config)) {
            return null;
        }

        $cart = $context->getCart();
        $shippings = $this->getShippingMethods($context->getShippingIds(), $context);
        $current = $this->getCurrent($cart, $context, $config);

        if (
            $current
            && !$this->isEditing($request)
            && !$current->hasError()
            && null !== array_find($shippings, fn (ShippingInterface $s) => $s->getId() === $current->getId())
        ) {
            $cart->setShippingMethod($current);
            $cart->setModified();

            return $this->createSummary(
                $this->dispatchEvent($request, true, $config),
                'shipping',
                [
                    'label' => $current->getLabel(),
                    'review' => $current->review(),
                    'editable' => \count($shippings) > 1,
                ],
            );
        }

        if (
            $this->isSubmitted($request)
            && $newId = $request->request->getInt('shipping_id')
        ) {
            if (
                $cart->getShippingMethod()?->getId() !== $newId
                && null !== ($shipping = array_find($shippings, fn (ShippingInterface $s) => $s->getId() === $newId))
            ) {
                $cart->setShippingMethod($shipping);
                $cart->setModified();
                $this->cartManager->recalculate($cart, $context);
            }

            throw new RedirectResponseException($request->getRequestUri());
        }

        return $this->createSummary(
            $this->dispatchEvent($request, false, $config),
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

    private function requiresShipping(StoreContext $context, array $config): bool
    {
        foreach ($context->getCart()->getLineItems()->products() as $item) {
            if (!$item->getProduct()?->getExtension(ShippingExtension::class)?->isExemptFromShipping()) {
                return true;
            }
        }

        return false;
    }
}
