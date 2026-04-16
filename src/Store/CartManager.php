<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Store;

use Isotope\CoreBundle\Model\ProductCollectionModel;
use Isotope\CoreBundle\ProductCollection\Cart\Cart;
use Isotope\CoreBundle\ProductCollection\Cart\CartCalculator;
use Isotope\CoreBundle\ProductCollection\Cart\CartFactory;
use Isotope\CoreBundle\ProductCollection\LineItem\LineItem;
use Isotope\CoreBundle\ProductCollection\LineItem\LineItemCollection;
use Isotope\CoreBundle\ProductCollection\ProductCollectionStorage;
use Isotope\CoreBundle\Store\Context\StoreContext;
use Isotope\CoreBundle\Store\Exception\EmptyCartException;
use Isotope\CoreBundle\Store\Order\CreateOrderResult;
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
    ) {
    }

    public function setCart(Cart $cart, StoreContext $context): void
    {
        $this->cart[$context->getIdentifier()] = $cart;
    }

    public function getCart(StoreContext $context): Cart
    {
        $cart = $this->cartFactory->load($context);

        if (!$cart) {
            $cart = $this->cartFactory->create($context);
        }

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
        $cart = $this->calculator->calculate($original, $context);

        if ($original->isModified()) {
            $cart->setModified();
        }

        if ($cart->isModified()) {
            $this->storage->save($cart, $context);
        }

        return $cart;
    }

    public function deleteCart(StoreContext $context): void
    {
        $cart = ProductCollectionModel::findByIdentifier($context->getIdentifier(), Cart::getType(), $context->getStoreId());

        if ($cart) {
            $this->storage->delete($cart);
        }
    }

    public function reset(): void
    {
        $this->cart = [];
    }

    public function order(Cart $cart, StoreContext $context): CreateOrderResult
    {
        if ($cart->isEmpty()) {
            throw new EmptyCartException();
        }

        $cart = $this->calculator->calculate($cart, $context);

        $data = $this->orderConverter->convertToOrder($cart, $context);
        $result = $this->orderManager->createOrder($data, $context);

        if ($payment = $cart->getPaymentMethod()) {
            if ($response = $payment->processPayment($result, $context)) {
                $result->setResponse($response);

                return $result;
            }
        }

        if (!$this->orderManager->lockOrder($result->getCollectionId(), $context)) {
            throw new \RuntimeException();
        }

        // TODO: maybe send email?

        if (isset($this->cart[$context->getIdentifier()])) {
            unset($this->cart[$context->getIdentifier()]);
        }

        // TODO: duplicate the same way we duplicate on recalculate
        $newCart = $this->cartFactory->create($context);
        $newCart->setBillingAddress($cart->getBillingAddress());
        $newCart->setShippingAddress($cart->getShippingAddress());
        $newCart->setShippingMethod($cart->getShippingMethod());
        $newCart->setPaymentMethod($cart->getPaymentMethod());

        if ($model = ProductCollectionModel::findByIdentifier($cart->getIdentifier(), Cart::getType())) {
            $this->storage->delete($model);
        }

        return $result;
    }
}
