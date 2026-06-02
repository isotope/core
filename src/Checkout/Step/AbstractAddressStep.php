<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Checkout\Step;

use Contao\StringUtil;
use Isotope\CoreBundle\Store\Address\AddressFormatter;
use Isotope\CoreBundle\Store\Address\AddressValidator;
use Isotope\CoreBundle\Store\Context\StoreContext;
use Isotope\CoreBundle\Twig\TemplateMarkupFactory;
use Isotope\CoreBundle\Util\CartUtil;
use Symfony\Contracts\Translation\TranslatorInterface;

abstract class AbstractAddressStep implements CheckoutStepInterface
{
    public function __construct(
        protected readonly AddressValidator $addressValidator,
        protected readonly AddressFormatter $addressFormatter,
        protected readonly TemplateMarkupFactory $markupFactory,
        protected readonly TranslatorInterface $translator,
    ) {
    }

    protected function useMemberAddress(array $config): bool
    {
        $skippable = explode(',', $config['iso_checkout_skippable'] ?? '');

        return \in_array('billing_address', $skippable, true);
    }

    protected function skipShippingAddress(array $config): bool
    {
        $skippable = explode(',', $config['iso_checkout_skippable'] ?? '');

        return \in_array('shipping_address', $skippable, true);
    }

    protected function hasError(StoreContext $context): bool
    {
        $billingAddress = $context->getCart()->getBillingAddress();

        if (
            !$billingAddress
            || ($context->getMemberId() > 0 ^ $billingAddress->isMemberAddress())
            || !$this->addressValidator->isValid($billingAddress, $context->getBillingAddressConfig())
        ) {
            return true;
        }

        $shippingAddress = $context->getCart()->getShippingAddress();

        if (
            $shippingAddress
            && (
                ($context->getMemberId() > 0 ^ $shippingAddress->isMemberAddress())
                || !$this->addressValidator->isValid($shippingAddress, $context->getShippingAddressConfig())
            )
        ) {
            return true;
        }

        return false;
    }

    protected function generateSummary(StoreContext $context, array $config): CheckoutStepSummary
    {
        $cart = $context->getCart();

        $requiresShipping = CartUtil::requiresShipping($cart);
        $skipShipping = $this->skipShippingAddress($config);

        $billingAddress = $cart->getBillingAddress();
        $billingAddressConfig = $context->getBillingAddressConfig();
        $shippingAddress = $cart->getShippingAddress();
        $shippingAddressConfig = $context->getShippingAddressConfig();

        if ($requiresShipping && $skipShipping && !$billingAddressConfig->isSameAs($shippingAddressConfig)) {
            $shippingAddress = $cart->getBillingAddress();
        }

        return new CheckoutStepSummary(
            $this->translator->trans('MSC.checkout_address', [], 'contao_default'),
            'address',
            true,
            $this->markupFactory->create(
                '@Contao/isotope/checkout_step/summary/address.html.twig',
                [
                    'billing_address' => $this->addressFormatter->toHtml($billingAddress, $billingAddressConfig),
                    'shipping_address' => $shippingAddress ? $this->addressFormatter->toHtml($shippingAddress, $shippingAddressConfig) : null,
                    'requires_payment' => CartUtil::requiresPayment($cart),
                    'requires_shipping' => $requiresShipping,
                    'edit_step' => 'edit_address_step',
                ],
            ),
        );
    }
}
