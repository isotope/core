<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Checkout\Step;

use Contao\CoreBundle\Exception\RedirectResponseException;
use Isotope\CoreBundle\DependencyInjection\Attribute\AsCheckoutStep;
use Isotope\CoreBundle\Model\AddressModel;
use Isotope\CoreBundle\Store\Address\AddressFormatter;
use Isotope\CoreBundle\Store\Address\AddressValidator;
use Isotope\CoreBundle\Store\Address\FormBuilder;
use Isotope\CoreBundle\Store\CartManager;
use Isotope\CoreBundle\Store\Context\StoreContext;
use Isotope\CoreBundle\Twig\TemplateMarkupFactory;
use Isotope\CoreBundle\Util\CartUtil;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsCheckoutStep(priority: 80)]
class GuestAddressStep extends AbstractAddressStep
{
    public function __construct(
        AddressValidator $addressValidator,
        AddressFormatter $addressFormatter,
        TemplateMarkupFactory $markupFactory,
        TranslatorInterface $translator,
        private readonly FormBuilder $formBuilder,
        private readonly CartManager $cartManager,
    ) {
        parent::__construct($addressValidator, $addressFormatter, $markupFactory, $translator);
    }

    public function getSummary(Request $request, StoreContext $context, array $config): CheckoutStepSummary|null
    {
        if ($context->getMemberId()) {
            return null;
        }

        if (
            (!$request->isMethod(Request::METHOD_POST) || !$request->request->has('edit_address_step'))
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
}
