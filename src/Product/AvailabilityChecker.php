<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Product;

use Contao\CoreBundle\Security\Authentication\Token\TokenChecker;
use Contao\MemberModel;
use Contao\StringUtil;
use Isotope\CoreBundle\Store\Context\StoreContext;
use Isotope\CoreBundle\Util\ProductCategoryUtil;

class AvailabilityChecker
{
    public function __construct(
        private readonly TokenChecker $tokenChecker,
        private readonly ProductCategoryUtil $categoryUtil,
    ) {
    }

    public function isAvailable(ProductInterface $product, StoreContext $context): bool
    {
        return $this->isPublished($product)
            && $this->hasAccess($product, $context)
            && $this->hasVariantsAvailable($product)
            && $this->hasValidPrice($product, $context)
            && $this->hasCategoriesInCurrentRoot($product);
    }

    private function isPublished(ProductInterface $product): bool
    {
        $time = time();

        return ($product['published']
                && ('' === $product['start'] || $product['start'] > $time)
                && ('' === $product['stop'] || $product['stop'] < $time)
            ) || $this->tokenChecker->isPreviewMode();
    }

    private function hasAccess(ProductInterface $product, StoreContext $context): bool
    {
        $attributes = $product->getProductType()->getAttributes() ?? [];

        if (
            !\in_array('protected', $attributes, true)
            || !\in_array('groups', $attributes, true)
            || !($product['protected'] ?? false)
        ) {
            return true;
        }

        $member = $context->getMemberId() ? MemberModel::findById($context->getMemberId()) : null;
        $groups = StringUtil::deserialize($product['groups'], true);

        if (!$member) {
            return \in_array(-1, array_map(\intval(...), $groups), true);
        }

        return [] !== array_intersect($groups, StringUtil::deserialize($member->groups, true));
    }

    private function hasVariantsAvailable(ProductInterface $product): bool
    {
        return !$product->hasVariants() || [] !== $product->getVariantIds();
    }

    private function hasValidPrice(ProductInterface $product, StoreContext $context): bool
    {
        if (
            !$product->getProductType()->hasAdvancedPrices()
            || (!\in_array('price', $product->getProductType()->getAttributes(), true) && !$product->hasVariantPrices())
        ) {
            return true;
        }

        return null !== $product->getPrice($context);
    }

    private function hasCategoriesInCurrentRoot(ProductInterface $product): bool
    {
        return [] !== $this->categoryUtil->getPublishedForProductInCurrentRoot($product);
    }
}
