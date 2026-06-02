<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Checkout\Step;

use Contao\CoreBundle\Exception\RedirectResponseException;
use Contao\StringUtil;
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
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsCheckoutStep(priority: 60)]
class ShippingStep implements CheckoutStepInterface
{
    public function __construct(
        private readonly ShippingRegistry $shippingRegistry,
        private readonly TemplateMarkupFactory $markupFactory,
        private readonly CartManager $cartManager,
        private readonly TranslatorInterface $translator,
    ) {
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
            && (!$request->isMethod(Request::METHOD_POST) || !$request->request->has('edit_shipping_step'))
            && null !== array_find($shippings, fn (ShippingInterface $s) => $s->getId() === $current->getId())
            && !$current->hasError()
        ) {
            $cart->setShippingMethod($current);
            $cart->setModified();

            return new CheckoutStepSummary(
                $this->translator->trans('MSC.checkout_shipping', [], 'contao_default'),
                'shipping',
                true,
                $this->markupFactory->create(
                    '@Contao/isotope/checkout_step/summary/shipping.html.twig',
                    [
                        'label' => $current->getLabel(),
                        'review' => $current->review(),
                        'edit_step' => \count($shippings) > 1 ? 'edit_shipping_step' : null,
                    ],
                ),
            );
        }

        if (
            $request->isMethod(Request::METHOD_POST)
            && $request->request->has('submit_shipping_step')
            && $newId = $request->request->getInt('shipping_id')
        ) {
            if (null !== ($shipping = array_find($shippings, fn (ShippingInterface $s) => $s->getId() === $newId))) {
                $cart->setShippingMethod($shipping);
                $cart->setModified();
                $this->cartManager->recalculate($cart, $context);

                throw new RedirectResponseException($request->getRequestUri());
            }
        }

        $options = [];

        foreach ($shippings as $shipping) {
            $options[] = [
                'shipping' => $shipping,
                'value' => $shipping->getId(),
                'label' => $shipping->getLabel(),
                'note' => $shipping->getNote(),
                'configurable' => false, //$shipping instanceof ConfigurableShippingInterface,
            ];
        }

        return new CheckoutStepSummary(
            $this->translator->trans('MSC.checkout_shipping', [], 'contao_default'),
            'shipping',
            false,
            $this->markupFactory->create(
                '@Contao/isotope/checkout_step/shipping.html.twig',
                [
                    'current' => $current,
                    'options' => $options,
                ],
            ),
        );
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
