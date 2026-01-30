<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\RuleBuilder\Condition;

use Contao\CoreBundle\Intl\Countries;
use Isotope\CoreBundle\DependencyInjection\Attribute\AsRuleCondition;
use Isotope\CoreBundle\Exception\LogicException;
use Isotope\CoreBundle\Intl\Subdivisions;
use Isotope\CoreBundle\Store\Context\StoreContext;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsRuleCondition(category: 'cart', factory: CartConditionFactory::class)]
class CartAddressCondition implements RuleConditionInterface
{
    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly Countries $intlCountries,
        private readonly Subdivisions $intlSubdivisions,
        private readonly string $type,
        private readonly array|null $countries,
        private readonly array|null $subdivisions,
        private readonly array|null $postalcodes,
        private readonly string $postalcodesLabel,
    ) {
    }

    public function isValid(): bool
    {
        return true;
    }

    public function isApplicable(StoreContext $context): bool
    {
        $address = match ($this->type) {
            'billing' => $context->getCart()->getBillingAddress(),
            'shipping' => $context->getCart()->getShippingMethod(),
            default => throw new LogicException('Unknown address type "'.$this->type.'"'),
        };

        if (!$address) {
            return false;
        }

        if (null !== $this->countries && !\in_array($address->country, $this->countries, true)) {
            return false;
        }

        if (null !== $this->subdivisions && \in_array($address->subdivision, $this->subdivisions, true)) {
            return false;
        }

        if (null !== $this->postalcodes && !\in_array($address->postal, $this->postalcodes, false)) {
            return false;
        }

        return true;
    }

    public function getBackendLabel(): string
    {
        $label = [];

        if (null !== $this->countries) {
            $names = $this->intlCountries->getCountries();

            $label['countries'] = implode(', ', array_intersect_key($names, array_flip($this->countries)));
        }

        if (null !== $this->subdivisions) {
            $names = $this->intlSubdivisions->getNames();

            $label['subdivisions'] = implode(', ', array_intersect_key($names, array_flip($this->subdivisions)));
        }

        if (null !== $this->postalcodes) {
            $label['postalcodes'] = $this->postalcodesLabel;
        }

        array_walk($label, fn (&$v, $k) => $v = $this->translator->trans("tl_iso_rule_condition.$k.0", [], 'contao_tl_iso_rule_condition').':'.$v);

        return '<div>'.implode('<br>', $label).'</div>';
    }

    public function getOperators(): array
    {
        return [];
    }
}
