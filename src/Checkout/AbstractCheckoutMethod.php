<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Checkout;

use Contao\CoreBundle\Security\Authentication\Token\TokenChecker;
use Isotope\CoreBundle\Exception\UnexpectedValueException;
use Isotope\CoreBundle\ModelType\AbstractModelType;
use Isotope\CoreBundle\ModelType\ContainerAwareInterface;
use Isotope\CoreBundle\Product\CoreProduct;
use Isotope\CoreBundle\RuleBuilder\RuleMatcher;
use Isotope\CoreBundle\Store\Context\StoreContext;
use Isotope\CoreBundle\Store\Price\PriceDefinition;
use Isotope\Interfaces\IsotopeOrderableCollection;
use Isotope\Interfaces\IsotopeProductCollection;

abstract class AbstractCheckoutMethod extends AbstractModelType implements ContainerAwareInterface
{
    protected string $label;

    protected string $note = '';

    protected PriceDefinition|null $priceDefinition;

    protected float|null $price = null;

    protected int $taxClass = 0;

    protected bool $percentage = false;

    protected int $rule = 0;

    /**
     * @var array<int>|null
     */
    protected array|null $allowedProductTypes = null;

    protected ProductTypeCondition $productTypesCondition = ProductTypeCondition::onlyAvailable;

    protected bool $debug = false;

    protected bool $logging = false;

    protected bool $enabled = true;

    public function __construct(
        protected readonly int $id,
        protected readonly StoreContext $context,
    ) {
        $this->label = static::class;
    }

    public static function getSubscribedServices(): array
    {
        return [
            TokenChecker::class,
            RuleMatcher::class,
        ];
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getStoreContext(): StoreContext
    {
        return $this->context;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function setLabel(string $label): static
    {
        $this->label = $label;

        return $this;
    }

    public function getNote(): string
    {
        return $this->note;
    }

    public function setNote(string $note): static
    {
        $this->note = $note;

        return $this;
    }

    public function getRule(): int
    {
        return $this->rule;
    }

    public function setRule(int $rule): static
    {
        $this->rule = $rule;

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

    /**
     * TODO: remove this
     * @deprecated
     */
    public function isPercentage(): bool
    {
        return $this->percentage;
    }

    /**
     * TODO: remove this
     * @deprecated
     */
    public function getPercentage(): float
    {
        if (!$this->percentage) {
            throw new UnexpectedValueException('Not have a percentage amount.');
        }

        return $this->price;
    }

    /**
     * TODO: remove this
     * @deprecated
     */
    public function getPercentageLabel(): string
    {
        return $this->isPercentage() ? $this->price.'%' : '';
    }

    public function getLevel(): int
    {
        // TODO: allow to configure the level
        return 1;
    }

    public function setPriceDefinition(PriceDefinition|null $priceDefinition): self
    {
        $this->priceDefinition = $priceDefinition;

        return $this;
    }

    public function getPriceDefinition(): PriceDefinition|null
    {
        return $this->priceDefinition;
    }

    /**
     * TODO: remove this
     * @deprecated
     */
    public function getPrice(IsotopeOrderableCollection $collection): float|null
    {
        $price = $this->calculatePrice($collection);

        if (null === $price) {
            return null;
        }

        return Isotope::calculatePrice($price, $this, 'price', $this->taxClass);
    }

    /**
     * TODO: remove this
     * @deprecated
     */
    public function setPrice(float|null $price, bool $isPercentage): static
    {
        $this->price = $price;
        $this->percentage = $isPercentage;

        return $this;
    }

    /**
     * TODO: remove this
     * @deprecated
     */
    public function getTaxClass(IsotopeOrderableCollection $collection): int
    {
        return $this->taxClass;
    }

    /**
     * TODO: remove this
     * @deprecated
     */
    public function setTaxClass(int $taxClass): static
    {
        $this->taxClass = $taxClass;

        return $this;
    }

    public function isAvailable(StoreContext $context): bool
    {
        if (!$this->enabled && !$this->get(TokenChecker::class)->isPreviewMode()) {
            return false;
        }

        if ($this->rule) {
            return $this->get(RuleMatcher::class)->isApplicable($this->rule, $this->context);
        }

        $cart = $this->context->getCart();

        if (null !== $this->allowedProductTypes) {
            $items = $cart->getLineItems();
            $types = [];

            foreach ($items as $item) {
                $product = $item->getProduct();

                if ($product instanceof CoreProduct) {
                    $types[] = (int) $product->getProductType()?->id;
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
