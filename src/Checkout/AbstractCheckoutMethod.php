<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Checkout;

use Contao\CoreBundle\Security\Authentication\Token\TokenChecker;
use Contao\CoreBundle\Security\ContaoCorePermissions;
use Isotope\Interfaces\IsotopeOrderableCollection;
use Isotope\Interfaces\IsotopeProductCollection;
use Isotope\Isotope;
use Symfony\Bundle\SecurityBundle\Security;

abstract class AbstractCheckoutMethod
{
    protected string $label;

    protected string $note = '';

    protected float|null $price = null;

    protected int $taxClass = 0;

    protected bool $percentage = false;

    protected bool $protected = false;

    protected array $groups = [];

    protected int $minQuantity = 0;

    protected int $maxQuantity = 0;

    protected QuantityMode $quantityMode = QuantityMode::cartItems;

    protected float $minTotal = 0;

    protected float $maxTotal = 0;

    /**
     * @var array<int>|null
     */
    protected array|null $allowedConfigs = null;

    /**
     * @var array<int>|null
     */
    protected array|null $allowedProductTypes = null;

    protected ProductTypeCondition $productTypesCondition = ProductTypeCondition::onlyAvailable;

    protected bool $debug = false;

    protected bool $logging = false;

    protected bool $enabled = true;

    public function __construct(
        private readonly int $id,
        private readonly Security $security,
        private readonly TokenChecker $tokenChecker,
    ) {
        $this->label = static::class;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getLabel(IsotopeOrderableCollection $collection): string
    {
        return $this->label;
    }

    public function setLabel(string $label): static
    {
        $this->label = $label;

        return $this;
    }

    public function getNote(IsotopeOrderableCollection $collection): string
    {
        return $this->note;
    }

    public function setNote(string $note): static
    {
        $this->note = $note;

        return $this;
    }

    public function setProtected(bool $protected, array $groups): static
    {
        $this->protected = $protected;
        $this->groups = $groups;

        return $this;
    }

    public function setMinMaxQuantity(int $min, int $max, QuantityMode $mode): static
    {
        $this->minQuantity = $min;
        $this->maxQuantity = $max;
        $this->quantityMode = $mode;

        return $this;
    }

    public function setMinMaxTotal(float $min, float $max): static
    {
        $this->minTotal = $min;
        $this->maxTotal = $max;

        return $this;
    }

    public function setAllowedConfigs(array|null $ids): static
    {
        $this->allowedConfigs = $ids;

        return $this;
    }

    public function setAllowedProductTypes(array|null $ids, ProductTypeCondition $condition): static
    {
        $this->allowedProductTypes = $ids;
        $this->productTypesCondition = $condition;

        return $this;
    }

    public function isDebug(): bool
    {
        return $this->debug;
    }

    public function setDebug(bool $debug): static
    {
        $this->debug = $debug;

        return $this;
    }

    public function isLogging(): bool
    {
        return $this->logging;
    }

    public function setLogging(bool $logging): static
    {
        $this->logging = $logging;

        return $this;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): static
    {
        $this->enabled = $enabled;

        return $this;
    }

    public function isPercentage(): bool
    {
        return $this->percentage;
    }

    public function getPercentage(): float
    {
        if (!$this->percentage) {
            throw new \UnexpectedValueException('Not have a percentage amount.');
        }

        return $this->price;
    }

    public function getPercentageLabel(): string
    {
        return $this->isPercentage() ? $this->price.'%' : '';
    }

    public function getPrice(IsotopeOrderableCollection $collection): float|null
    {
        $price = $this->calculatePrice($collection);

        if (null === $price) {
            return null;
        }

        return Isotope::calculatePrice($price, $this, 'price', $this->taxClass);
    }

    public function setPrice(float|null $price, bool $isPercentage): static
    {
        $this->price = $price;
        $this->percentage = $isPercentage;

        return $this;
    }

    public function getTaxClass(IsotopeOrderableCollection $collection): int
    {
        return $this->taxClass;
    }

    public function setTaxClass(int $taxClass): static
    {
        $this->taxClass = $taxClass;

        return $this;
    }

    public function isAvailable(IsotopeOrderableCollection $collection): bool
    {
        if (!$this->enabled && !$this->tokenChecker->isPreviewMode()) {
            return false;
        }

        if ($this->protected && !$this->security->isGranted(ContaoCorePermissions::MEMBER_IN_GROUPS, $this->groups)) {
            return false;
        }

        if (
            ($this->minTotal > 0 && $this->minTotal > $collection->getSubtotal())
            || ($this->maxTotal > 0 && $this->maxTotal < $collection->getSubtotal())
        ) {
            return false;
        }

        if ($this->minQuantity > 0 || $this->maxQuantity > 0) {
            $quantity = match ($this->quantityMode) {
                QuantityMode::cartItems => $collection->sumItemsQuantity(),
                QuantityMode::cartProducts => $collection->countItems(),
            };

            if (
                ($this->minQuantity > 0 && $this->minQuantity > $quantity)
                || ($this->maxQuantity > 0 && $this->maxQuantity < $quantity)
            ) {
                return false;
            }
        }

        if (
            null !== $this->allowedConfigs
            && !\in_array($collection->getConfig()->id, $this->allowedConfigs, true)
        ) {
            return false;
        }

        if (null !== $this->allowedProductTypes) {
            $items = $collection->getItems();
            $types = [];

            foreach ($items as $item) {
                if ($item->hasProduct()) {
                    $types[] = (int) $item->getProduct()?->getProductType()?->id;
                } elseif (ProductTypeCondition::onlyAvailable === $this->productTypesCondition) {
                    // If one product in collection is not of given type, shipping method is not available
                    return false;
                }
            }

            $types = array_unique($types);
            $available = match ($this->productTypesCondition) {
                ProductTypeCondition::onlyAvailable => [] === array_diff($types, $this->allowedProductTypes),
                ProductTypeCondition::notAvailable => [] === array_intersect($this->allowedProductTypes, $types),
                ProductTypeCondition::oneAvailable => [] !== array_intersect($this->allowedProductTypes, $types),
                ProductTypeCondition::allAvailable => array_intersect($this->allowedProductTypes, $types) === $this->allowedProductTypes,
            };

            if (!$available) {
                return false;
            }
        }

        return true;
    }

    public function checkoutReview(IsotopeOrderableCollection $collection): string
    {
        return $this->getLabel($collection);
    }

    protected function calculatePrice(IsotopeProductCollection $collection): float|null
    {
        if (null === $this->price) {
            return null;
        }

        if ($this->percentage) {
            return $collection->getSubtotal() / 100 * $this->price;
        }

        return $this->price;
    }
}
