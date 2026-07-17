<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\ProductCollection\Cart;

use Isotope\CoreBundle\ProductCollection\AbstractCollectionCalculator;
use Isotope\CoreBundle\ProductCollection\AbstractProductCollection;
use Isotope\CoreBundle\ProductCollection\ItemCollector\ItemCollectorInterface;
use Isotope\CoreBundle\ProductCollection\ProductCollectionHasher;
use Isotope\CoreBundle\Store\Context\StoreContext;
use Isotope\CoreBundle\Store\Price\PriceCalculator;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

class CartCalculator extends AbstractCollectionCalculator
{
    /**
     * @param iterable<ItemCollectorInterface> $collectors
     */
    public function __construct(
        private readonly CartFactory $cartFactory,
        #[AutowireIterator('isotope.item_collector')] iterable $collectors,
        PriceCalculator $priceCalculator,
        ProductCollectionHasher $collectionHasher,
    ) {
        parent::__construct($collectors, $priceCalculator, $collectionHasher);
    }

    protected function createNewInstance(AbstractProductCollection $collection, StoreContext $context): AbstractProductCollection
    {
        $toCalculate = $this->cartFactory->create($context, $collection->getIdentifier());

        $toCalculate->setId($collection->getId());
        $toCalculate->setBillingAddress($collection->getBillingAddress());
        $toCalculate->setShippingAddress($collection->getShippingAddress());
        $toCalculate->setShippingMethod($collection->getShippingMethod());
        $toCalculate->setPaymentMethod($collection->getPaymentMethod());
        $toCalculate->setExtensions($collection->getExtensions());

        return $toCalculate;
    }
}
