<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\InsertTag;

use Contao\CoreBundle\DependencyInjection\Attribute\AsInsertTag;
use Contao\CoreBundle\InsertTag\InsertTagResult;
use Contao\CoreBundle\InsertTag\ResolvedInsertTag;
use Contao\CoreBundle\InsertTag\ResolvedParameters;
use Contao\CoreBundle\InsertTag\Resolver\InsertTagResolverNestedResolvedInterface;
use Isotope\CoreBundle\Model\AddressModel;
use Isotope\CoreBundle\ProductCollection\AbstractProductCollection;
use Isotope\CoreBundle\ProductCollection\Cart\Cart;
use Isotope\CoreBundle\ProductCollection\LineItem\LineItemCollection;
use Isotope\CoreBundle\Store\Address\AddressFormatter;
use Isotope\CoreBundle\Store\StoreManager;
use Isotope\CoreBundle\Store\Weight\WeightUnit;
use Symfony\Component\PropertyAccess\Exception\NoSuchPropertyException;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsInsertTag('cart')]
#[AsInsertTag('order')]
class ProductCollectionInsertTag implements InsertTagResolverNestedResolvedInterface
{
    public function __construct(
        private readonly StoreManager $storeManager,
        private readonly TranslatorInterface $translator,
        private readonly PropertyAccessorInterface $propertyAccessor,
        private readonly AddressFormatter $addressFormatter,
    ) {
    }

    public function __invoke(ResolvedInsertTag $insertTag): InsertTagResult
    {
        $collection = $this->getCollection($insertTag->getName());

        if (!$collection) {
            return new InsertTagResult('');
        }

        return new InsertTagResult((string) $this->resolve($collection, $insertTag->getParameters()));
    }

    private function getCollection(string $type): AbstractProductCollection|null
    {
        return match ($type) {
            'cart' => $this->storeManager->getCurrentContext()->getCart(),
            'order' => null, // TODO: load order
            default => null,
        };
    }

    private function resolve(AbstractProductCollection $collection, ResolvedParameters $parameters): float|int|string
    {
        switch ($parameters->get(0)) {
            case 'items':
                return $collection->getLineItems()->products()->count();

            case 'quantity':
                return $collection->getLineItems()->products()->sumQuantity();

            case 'items_label':
                return $this->formatCount($collection->getLineItems()->products()->count());

            case 'quantity_label':
                return $this->formatCount($collection->getLineItems()->products()->sumQuantity());

            case 'subtotal':
                return $this->formatPrice($collection->getSubtotal((int) ($parameters->get(1) ?? 0))->getTotalPrice());

            case 'total':
                return $this->formatPrice($collection->getTotal()?->getTotalPrice() ?? 0.0);

            case 'billing_address':
                // TODO: support orders
                if (
                    !$collection instanceof Cart
                    || !($address = $collection->getBillingAddress())
                ) {
                    return '';
                }

                return $this->formatAddress($address, $parameters->get(1));

            case 'shipping_address':
                // TODO: support orders
                if (
                    !$collection instanceof Cart
                    || !($address = $collection->getShippingAddress())
                ) {
                    return '';
                }

                return $this->formatAddress($address, $parameters->get(1));

            case 'weight':
                $unit = WeightUnit::tryFrom($parameters->get(2));

                if (!$unit) {
                    return '';
                }

                return $this->formatWeight($collection->getLineItems(), $unit);

            default:
                try {
                    return $this->propertyAccessor->getValue($collection, $parameters->get(0));
                } catch (NoSuchPropertyException) {
                    return '';
                }
        }
    }

    private function formatCount(int $count): string
    {
        return match ($count) {
            0 => '',
            1 => '('.$this->translator->trans('MSC.productSingle', [], 'contao_default').')',
            default => '('.$this->translator->trans('MSC.productMultiple', [$count], 'contao_default').')',
        };
    }

    private function formatPrice(float $amount): string
    {
        // TODO: format price
        return (string) $amount;
    }

    private function formatAddress(AddressModel $address, string|null $attribute = null): string
    {
        if (null === $attribute) {
            $context = $this->storeManager->getCurrentContext();

            return $this->addressFormatter->toHtml($address, $context->getBillingAddressConfig());
        }

        return (string) ($address->$attribute ?? '');
    }

    private function formatWeight(LineItemCollection $items, WeightUnit $unit): string
    {
        // TODO: format weight
        return (string) $items->calculateWeight($unit)->getAmount();
    }
}
