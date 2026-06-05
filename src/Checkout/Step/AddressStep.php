<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Checkout\Step;

use Contao\CoreBundle\Exception\RedirectResponseException;
use Isotope\CoreBundle\DependencyInjection\Attribute\AsCheckoutStep;
use Isotope\CoreBundle\Model\AddressModel;
use Isotope\CoreBundle\Store\Address\AddressFormatter;
use Isotope\CoreBundle\Store\Address\AddressValidator;
use Isotope\CoreBundle\Store\Address\FormBuilder;
use Isotope\CoreBundle\Store\AddressManager;
use Isotope\CoreBundle\Store\CartManager;
use Isotope\CoreBundle\Store\Context\StoreContext;
use Isotope\CoreBundle\Twig\TemplateMarkupFactory;
use Isotope\CoreBundle\Util\CartUtil;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsCheckoutStep(priority: 80)]
class AddressStep implements CheckoutStepInterface
{
    public function __construct(
        private readonly AddressValidator $addressValidator,
        private readonly AddressFormatter $addressFormatter,
        private readonly TemplateMarkupFactory $markupFactory,
        private readonly TranslatorInterface $translator,
        private readonly AddressManager $addressManager,
        private readonly FormBuilder $formBuilder,
        private readonly CartManager $cartManager,
    ) {
    }

    public function getSummary(Request $request, StoreContext $context, array $config): CheckoutStepSummary|null
    {
        return $context->getMemberId() ? $this->getMemberSummary($request, $context, $config) : $this->getGuestSummary($request, $context, $config);
    }

    private function getGuestSummary(Request $request, StoreContext $context, array $config): CheckoutStepSummary|null
    {
        if (
            (!$request->isMethod(Request::METHOD_POST) || !$request->request->has('edit_address_step'))
            && !$this->hasError($context)
        ) {
            return $this->generateSummary($context, $config);
        }

        $cart = $context->getCart();
        $requiresShipping = CartUtil::requiresShipping($cart);
        $billingAddressConfig = $context->getBillingAddressConfig();

        if ($skipShipping = $this->skipShippingAddress($config)) {
            $billingAddressConfig = $billingAddressConfig->mergeWith($context->getShippingAddressConfig());
        }

        $billingAddress = $cart->getBillingAddress() ?? new AddressModel();
        $billingForm = $this->formBuilder->build($request, $billingAddress, $billingAddressConfig, 'billing_');

        $addShipping = null !== $cart->getShippingAddress();
        $shippingAddress = null;
        $shippingForm = null;

        // TODO: validate that the existing address matches the requested config

        if ($requiresShipping && !$skipShipping) {
            $shippingAddress = $cart->getShippingAddress() ?? new AddressModel();

            // Ensure we have a separate shipping address ID
            if ((int) $shippingAddress->id === (int) $billingAddress->id) {
                $shippingAddress = clone $shippingAddress;
            }

            $shippingForm = $this->formBuilder->build($request, $shippingAddress, $context->getShippingAddressConfig(), 'shipping_');
        }

        if ($request->isMethod(Request::METHOD_POST) && $request->request->has('submit_address_step')) {
            $billingResult = $this->formBuilder->validate($billingForm, $context->getBillingAddressConfig());
            $addShipping = $requiresShipping && !$skipShipping && $request->request->getBoolean('add_shipping_address');
            $shippingResult = $addShipping ? $this->formBuilder->validate($shippingForm, $context->getShippingAddressConfig()) : false;

            if (true === $billingResult) {
                $cart->setBillingAddress($billingAddress);
            } elseif (false === $billingResult) {

            } else {
                $errors = $billingResult;
            }

            if ($addShipping) {
                if (true === $shippingResult) {
                    $cart->setShippingAddress($shippingAddress);
                } elseif (false === $billingResult) {

                } else {
                    $errors = $billingResult;
                }
            } else {
                $cart->setShippingAddress(null);
            }

            $cart->setModified();
            $this->cartManager->recalculate($cart, $context);

            throw new RedirectResponseException($request->getRequestUri());
        }

        return new CheckoutStepSummary(
            $this->translator->trans('MSC.checkout_address', [], 'contao_default'),
            'address',
            false,
            $this->markupFactory->create(
                '@Contao/isotope/checkout_step/guest_address.html.twig',
                [
                    'billing_address_form' => $this->formBuilder->render($billingForm),
                    'shipping_address_form' => $shippingForm ? $this->formBuilder->render($shippingForm) : null,

                    'requires_payment' => CartUtil::requiresPayment($cart),
                    'requires_shipping' => $requiresShipping && !$skipShipping,
                    'add_shipping_address' => $addShipping,
                ],
            ),
        );
    }

    private function getMemberSummary(Request $request, StoreContext $context, array $config): CheckoutStepSummary|null
    {
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
            && !$this->hasError($context)
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
            'address',
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

    private function useMemberAddress(array $config): bool
    {
        $skippable = explode(',', $config['iso_checkout_skippable'] ?? '');

        return \in_array('billing_address', $skippable, true);
    }

    private function skipShippingAddress(array $config): bool
    {
        $skippable = explode(',', $config['iso_checkout_skippable'] ?? '');

        return \in_array('shipping_address', $skippable, true);
    }

    private function hasError(StoreContext $context): bool
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

    private function generateSummary(StoreContext $context, array $config): CheckoutStepSummary
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
