<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Checkout\Step;

use Contao\CoreBundle\Exception\RedirectResponseException;
use Contao\StringUtil;
use Isotope\CoreBundle\DependencyInjection\Attribute\AsCheckoutStep;
use Isotope\CoreBundle\Model\AddressModel;
use Isotope\CoreBundle\Store\Address\AddressFormatter;
use Isotope\CoreBundle\Store\Address\AddressValidator;
use Isotope\CoreBundle\Store\AddressManager;
use Isotope\CoreBundle\Store\CartManager;
use Isotope\CoreBundle\Store\Context\StoreContext;
use Isotope\CoreBundle\Twig\TemplateMarkupFactory;
use Isotope\CoreBundle\Util\CartUtil;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsCheckoutStep(priority: 80)]
class MemberAddressStep extends AbstractAddressStep
{
    public function __construct(
        AddressValidator $addressValidator,
        AddressFormatter $addressFormatter,
        TemplateMarkupFactory $markupFactory,
        TranslatorInterface $translator,
        private readonly AddressManager $addressManager,
        private readonly CartManager $cartManager,
    ) {
        parent::__construct($addressValidator, $addressFormatter, $markupFactory, $translator);
    }

    public function getSummary(Request $request, StoreContext $context, array $config): CheckoutStepSummary|null
    {
        if (!$context->getMemberId()) {
            return null;
        }

        if (
            (
                !$request->isMethod(Request::METHOD_POST)
                || (
                    !$request->request->has('edit_address_step')
                    && !$request->request->has('create_address_billing')
                    && !$request->request->has('edit_address_billing')
                    && !$request->request->has('delete_address_billing')
                    && !$request->request->has('create_address_shipping')
                    && !$request->request->has('edit_address_shipping')
                    && !$request->request->has('delete_address_shipping')
                )
            )
            && !($hasError = $this->hasError($context))
        ) {
            return $this->generateSummary($context, $config);
        }

        $cart = $context->getCart();
        $requiresShipping = CartUtil::requiresShipping($cart);
        $billingAddressConfig = $context->getBillingAddressConfig();

        if ($skipShipping = $this->skipShippingAddress($config)) {
            $billingAddressConfig = $billingAddressConfig->mergeWith($context->getShippingAddressConfig());
        }

        $billingAddressManager = $this->addressManager->generate($request, $context->getMemberId(), $context->getStoreId(), $billingAddressConfig, 'billing');
        $shippingAddressManager = $requiresShipping && !$skipShipping ? $this->addressManager->generate($request, $context->getMemberId(), $context->getStoreId(), $context->getShippingAddressConfig(), 'shipping') : null;

        $billingAddress = $cart->getBillingAddress();
        $shippingAddress = $cart->getShippingAddress();
        $addShipping = null !== $shippingAddress;

        // TODO: validate that the existing address matches the requested config

        if ($request->isMethod(Request::METHOD_POST) && $request->request->has('submit_address_step')) {
            if ($billingAddress = $this->getAddress($request->request->getInt('billing_address_id'), $context)) {
                $cart->setBillingAddress($billingAddress);
            }

            $addShipping = $request->request->getBoolean('add_shipping_address');

            if (!$addShipping) {
                $cart->setShippingAddress(null);
            } elseif ($shippingAddress = $this->getAddress($request->request->getInt('shipping_address_id'), $context)) {
                $cart->setShippingAddress($shippingAddress);
            }

            $cart->setModified();
            $this->cartManager->recalculate($cart, $context);

            throw new RedirectResponseException($request->getRequestUri());
        }

        if ($request->request->has('create_address_billing') || $request->request->has('edit_address_billing')) {
            return $this->getResponse(
                [
                    'edit_address' => $billingAddressManager,
                ],
            );
        }

        if ($requiresShipping && !$skipShipping && ($request->request->has('create_address_shipping') || $request->request->has('edit_address_shipping'))) {
            return $this->getResponse(
                [
                    'edit_address' => $shippingAddressManager,
                ],
            );
        }

        return $this->getResponse(
            [
                'billing_address_manager' => $billingAddressManager,
                'shipping_address_manager' => $shippingAddressManager,

                'billing_address' => $billingAddress,
                'shipping_address' => $shippingAddress,
                'add_shipping_address' => $addShipping,
                'requires_payment' => CartUtil::requiresPayment($cart),
                'requires_shipping' => $requiresShipping,
            ],
        );
    }

    private function getResponse(array $context): CheckoutStepSummary
    {
        return new CheckoutStepSummary(
            $this->translator->trans('MSC.checkout_address', [], 'contao_default'),
            'address member',
            false,
            $this->markupFactory->create(
                '@Contao/isotope/checkout_step/member_address.html.twig',
                $context,
            ),
        );
    }

    private function getAddress(int $id, StoreContext $context): AddressModel|null
    {
        $address = AddressModel::findById($id);

        if (
            !$address
            || 'tl_member' !== $address->ptable
            || (int) $address->pid !== $context->getMemberId()
            || (int) $address->store_id !== $context->getStoreId()
        ) {
            return null;
        }

        return $address;
    }
}
