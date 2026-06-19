<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\InsertTag;

use Contao\CoreBundle\DependencyInjection\Attribute\AsInsertTag;
use Contao\CoreBundle\InsertTag\InsertTagResult;
use Contao\CoreBundle\InsertTag\OutputType;
use Contao\CoreBundle\InsertTag\ResolvedInsertTag;
use Contao\CoreBundle\InsertTag\Resolver\InsertTagResolverNestedResolvedInterface;
use Contao\CoreBundle\Routing\ContentUrlGenerator;
use Isotope\CoreBundle\Model\ProductModel;
use Isotope\CoreBundle\Product\CoreProduct;
use Isotope\CoreBundle\Product\DataCollector\ProductDto;
use Isotope\CoreBundle\Product\ProductBuilder;
use Isotope\CoreBundle\Store\Price\PriceFormatter;
use Isotope\CoreBundle\Store\StoreManager;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\PropertyAccess\Exception\NoSuchPropertyException;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Routing\Exception\ExceptionInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[AsInsertTag('product')]
#[AsInsertTag('product_open')]
#[AsInsertTag('product_url')]
#[AsInsertTag('product_price')]
class ProductInsertTag implements InsertTagResolverNestedResolvedInterface
{
    public function __construct(
        private readonly ContentUrlGenerator $urlGenerator,
        private readonly RequestStack $requestStack,
        private readonly ProductBuilder $productBuilder,
        private readonly StoreManager $storeManager,
        private readonly PropertyAccessorInterface $propertyAccessor,
        private readonly PriceFormatter $priceFormatter,
    ) {
    }

    public function __invoke(ResolvedInsertTag $insertTag): InsertTagResult
    {
        $first = $insertTag->getParameters()->get(0);

        if (
            is_numeric($first)
            || 'product_open' === $insertTag->getName()
            || 'product_url' === $insertTag->getName()
        ) {
            return $this->resolveLink($insertTag, (int) $first);
        }

        $id = $insertTag->getParameters()->getScalar(1);
        $id = null === $id ? null : (int) $id;

        if ('product_price' === $insertTag->getName()) {
            return $this->resolvePrice((string) $first, $id);
        }

        return $this->resolveAttribute((string) $first, $id);
    }

    private function resolveLink(ResolvedInsertTag $insertTag, int $id): InsertTagResult
    {
        $product = ProductModel::findById($id);

        if (!$product) {
            return new InsertTagResult('');
        }

        $absolute = \in_array('absolute', \array_slice($insertTag->getParameters()->all(), 1), true);
        $target = \in_array('blank', \array_slice($insertTag->getParameters()->all(), 1), true) ? ' target="_blank" rel="noreferrer noopener"' : '';
        $url = '#';

        try {
            $url = $this->urlGenerator->generate($product, [], $absolute ? UrlGeneratorInterface::ABSOLUTE_URL : UrlGeneratorInterface::ABSOLUTE_PATH);
        } catch (ExceptionInterface) {
            // Ignore routing exception
        }

        return match ($insertTag->getName()) {
            'product_open' => new InsertTagResult(\sprintf('<a href="%s"%s>', $url, $target), OutputType::html),
            'product_url' => new InsertTagResult($url, OutputType::url),
            default => new InsertTagResult(\sprintf('<a href="%s"%s>%s</a>', $url, $target, $product->name), OutputType::html),
        };
    }

    private function resolvePrice(string $attribute, int|null $id): InsertTagResult
    {
        $product = $this->findProduct($id);
        $price = $product?->getPrice();

        if (!$product || !$price) {
            return new InsertTagResult('');
        }

        $key = match ($attribute) {
            'net_amount' => 'net_unit_price',
            'gross_amount' => 'gross_unit_price',
            'amount',
            'original_amount',
            'html',
            '' => null,
            default => $attribute,
        };

        return new InsertTagResult($this->priceFormatter->toHtml($price, $this->storeManager->getCurrentContext(), $key));
    }

    private function resolveAttribute(string $attribute, int|null $id): InsertTagResult
    {
        $product = $this->findProduct($id);

        if (!$product) {
            return new InsertTagResult('');
        }

        try {
            $value = (string) $this->propertyAccessor->getValue($product, $attribute);
        } catch (NoSuchPropertyException) {
            $value = '';
        }

        return new InsertTagResult($value);
    }

    private function findProduct(int|null $id): CoreProduct|null
    {
        if (null === $id) {
            $request = $this->requestStack->getCurrentRequest();
            $product = $request?->attributes->get('_content');
        } else {
            $product = $this->productBuilder->buildOne(
                new ProductDto('tl_iso_product.'.$id),
                $this->storeManager->getCurrentContext(),
            );
        }

        return $product instanceof CoreProduct && $product->isAvailable() ? $product : null;
    }
}
