<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Attribute\Filter;

use Isotope\CoreBundle\Attribute\Option\OptionCollection;
use Symfony\Component\DependencyInjection\Attribute\Exclude;

#[Exclude]
final readonly class FilterAttribute
{
    public function __construct(
        private string $name,
        private string $label,
        private OptionCollection $options,
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function getOptions(): OptionCollection
    {
        return $this->options;
    }
}
