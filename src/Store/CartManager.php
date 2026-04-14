<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Store;

use Isotope\CoreBundle\Model\ProductCollection;
use Isotope\CoreBundle\ProductCollection\Cart\Cart;
use Isotope\CoreBundle\ProductCollection\Cart\CartCalculator;
use Isotope\CoreBundle\ProductCollection\Cart\CartFactory;
use Isotope\CoreBundle\ProductCollection\LineItem\LineItem;
use Isotope\CoreBundle\ProductCollection\LineItem\LineItemCollection;
use Isotope\CoreBundle\ProductCollection\ProductCollectionStorage;
use Isotope\CoreBundle\Store\Context\StoreContext;
use Isotope\CoreBundle\Store\Order\OrderConverter;
use Symfony\Contracts\Service\ResetInterface;

class CartManager implements ResetInterface
{
    /**
     * @var array<Cart>
     */
    private array $cart = [];

    /**
     * @internal
     */
    public function __construct(
        private readonly CartFactory $cartFactory,
        private readonly ProductCollectionStorage $storage,
        private readonly CartCalculator $calculator,
        private readonly OrderManager $orderManager,
        private readonly OrderConverter $orderConverter,
        private readonly ProductCollectionStorage $productCollectionStorage,
    ) {
    }

    public function setCart(Cart $cart, StoreContext $context): void
    {
        $this->cart[$context->getIdentifier()] = $cart;
    }

    public function hasCart(string $token): bool
    {
        return isset($this->cart[$token]);
    }

    public function createNew(StoreContext $context): Cart
    {
        $cart = $this->cartFactory->create($context);

        return $this->cart[$context->getIdentifier()] = $cart;
    }

    public function getCart(StoreContext $context, bool $caching = true): Cart
    {
        if ($caching && $this->hasCart($context->getIdentifier())) {
            return $this->cart[$context->getIdentifier()];
        }

        $cart = $this->cartFactory->load($context);

        if (!$cart) {
            $cart = $this->cartFactory->create($context);
        }

        $this->cart[$context->getIdentifier()] = $cart;

        return $this->recalculate($cart, $context);
    }

    /**
     * @param LineItem|array<LineItem> $items
     */
    public function add(Cart $cart, LineItem|array $items, StoreContext $context): Cart
    {
        if ($items instanceof LineItem) {
            $items = [$items];
        }

        foreach ($items as $item) {
            $cart->add($item);
            $cart->setModified();
        }

        return $this->recalculate($cart, $context);
    }

    public function changeQuantity(Cart $cart, int $itemId, int $quantity, StoreContext $context): Cart
    {
        $item = $cart->getLineItems()->where('getId', $itemId)->first();
        $item->setQuantity($quantity);
        $cart->setModified();

        return $this->recalculate($cart, $context);
    }

    public function remove(Cart $cart, int $id, StoreContext $context): Cart
    {
        return $this->removeItems($cart, [$id], $context);
    }

    /**
     * @param array<int> $ids
     */
    public function removeItems(Cart $cart, array $ids, StoreContext $context): Cart
    {
        /** @var LineItemCollection $lineItems */
        $lineItems = $cart->getLineItems()->filter(static fn (LineItem $item) => !\in_array($item->getId(), $ids, true));
        $cart->setLineItems($lineItems);
        $cart->setModified();

        return $this->recalculate($cart, $context);
    }

    public function recalculate(Cart $original, StoreContext $context): Cart
    {
        $cart = $this->calculator->calculate($original, $context, $this->setCart(...));

        if ($original->isModified()) {
            $cart->setModified();
        }

        if ($cart->isModified()) {
            $this->storage->save($cart, $context);
        }

        return $this->cart[$context->getIdentifier()] = $cart;
    }

    public function deleteCart(StoreContext $context): void
    {
        $cart = ProductCollection::findByIdentifier($context->getIdentifier(), Cart::getType(), $context->getStoreId());

        if ($cart) {
            $this->storage->delete($cart);
        }
    }

    public function reset(): void
    {
        $this->cart = [];
    }

    public function order(Cart $cart, StoreContext $context): string
    {
        $cart = $this->calculator->calculate($cart, $context);

        $data = $this->orderConverter->convertToOrder($cart, $context);

        $data += [
            'order_status' => 1,
            //'date_paid' => null,
            //'date_shipped' => null,
        ];

        $this->orderManager->createOrder($data, $context);

        if (isset($this->cart[$context->getIdentifier()])) {
            unset($this->cart[$context->getIdentifier()]);
        }

        // TODO: duplicate the same way we duplicate on recalculate
        $newCart = $this->createNew($context);
        $newCart->setBillingAddress($cart->getBillingAddress());
        $newCart->setShippingAddress($cart->getShippingAddress());
        $newCart->setShippingMethod($cart->getShippingMethod());
        $newCart->setPaymentMethod($cart->getPaymentMethod());

        if ($model = ProductCollection::findByIdentifier($cart->getIdentifier(), Cart::getType())) {
            $this->productCollectionStorage->delete($model);
        }

        return 'FOO'; //$orderId;
    }
}
