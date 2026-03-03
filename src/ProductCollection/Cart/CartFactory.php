<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\ProductCollection\Cart;

use Isotope\CoreBundle\Model\ProductCollection;
use Isotope\CoreBundle\ProductCollection\Event\CartCreatedEvent;
use Isotope\CoreBundle\ProductCollection\Event\MergeGuestCartEvent;
use Isotope\CoreBundle\ProductCollection\ProductCollectionStorage;
use Isotope\CoreBundle\Store\Context\StoreContext;
use Isotope\CoreBundle\Util\StringUtil;
use Psr\EventDispatcher\EventDispatcherInterface;

class CartFactory
{
    public function __construct(
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly ProductCollectionStorage $storage,
    ) {
    }

    public function load(StoreContext $context): Cart|null
    {
        $cartModel = $context->getMemberId() ? ProductCollection::findOneByMember($context->getMemberId(), Cart::COLLECTION_TYPE, $context->getStoreId()) : null;
        $guestCartModel = ProductCollection::findByIdentifier($context->getIdentifier(), Cart::COLLECTION_TYPE, $context->getStoreId());

        if (!$cartModel && $guestCartModel) {
            $cartModel = $guestCartModel;
            $guestCartModel = null;
        }

        if (!$cartModel) {
            return null;
        }

        $cart = $this->storage->load($this->create($context, $cartModel->uniqid), $cartModel, $context);

        if ($guestCartModel) {
            $guestCart = $this->storage->load($this->create($context, $guestCartModel->uniqid), $guestCartModel, $context);
            $cart->setLineItems($cart->getLineItems()->merge($guestCart->getLineItems()));
            $cart->setModified();

            $this->eventDispatcher->dispatch(new MergeGuestCartEvent($cart, $guestCart, $context));
        }

        if ($cart->isModified() || $cartModel->member !== (int) $context->getMemberId()) {
            $this->storage->save($cart, $context);
        }

        // Must be after $cart is saved because line items might have been moved!
        if ($guestCartModel) {
            $this->storage->delete($guestCartModel);
        }

        return $cart;
    }

    public function create(StoreContext $context, string|null $identifier = null): Cart
    {
        $cart = new Cart($identifier ?? $context->getIdentifier());

        $this->eventDispatcher->dispatch(new CartCreatedEvent($cart, $context));

        return $cart;
    }
}
