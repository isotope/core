<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\ProductCollection\Cart;

use Isotope\CoreBundle\Model\ProductCollection;
use Isotope\CoreBundle\ProductCollection\Event\CartCreatedEvent;
use Isotope\CoreBundle\ProductCollection\Event\MergeGuestCartEvent;
use Isotope\CoreBundle\ProductCollection\ProductCollectionStorage;
use Isotope\CoreBundle\Store\Context\StoreContext;
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
        $cartModel = $context->getMemberId() ? ProductCollection::findByMember($context->getMemberId(), Cart::COLLECTION_TYPE, $context->getStoreId()) : null;
        $guestCartModel = ProductCollection::findByIdentifier($context->getIdentifier(), Cart::COLLECTION_TYPE, $context->getStoreId());

        if (!$cartModel && $guestCartModel) {
            $cartModel = $guestCartModel;
            $guestCartModel = null;
        }

        if (!$cartModel) {
            return null;
        }

        $cart = $this->storage->load($this->create($context), $cartModel, $context);

        if ($guestCartModel) {
            $guestCart = $this->storage->load($this->create($context), $guestCartModel, $context);

            $cart->getLineItems()->merge($guestCart->getLineItems());
            $cart->setModified();

            $this->eventDispatcher->dispatch(new MergeGuestCartEvent($cart, $guestCart, $context));
        }

        return $cart;
    }

    public function create(StoreContext $context): Cart
    {
        $cart = new Cart($context->getIdentifier());

        $this->eventDispatcher->dispatch(new CartCreatedEvent($cart, $context));

        return $cart;
    }
}
