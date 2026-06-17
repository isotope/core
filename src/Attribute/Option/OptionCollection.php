<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Attribute\Option;

use Ramsey\Collection\AbstractCollection;

/**
 * @extends AbstractCollection<Option>
 */
class OptionCollection extends AbstractCollection
{
    public function getType(): string
    {
        return Option::class;
    }

    public function getLabel(string $value): string|null
    {
        try {
            return $this->where('getValue', $value)->first()->getLabel();
        } catch (\Exception) {
            return null;
        }
    }

    public function asDcaArray(): array
    {
        $options = [];
        $group = null;

        /** @var Option $option */
        foreach ($this as $option) {
            if ($option->isGroup()) {
                $group = $option->getLabel();
                continue;
            }

            if (null !== $group) {
                $options[$group][$option->getValue()] = $option->getLabel();
            } else {
                $options[$option->getValue()] = $option->getLabel();
            }
        }

        return $options;
    }
}
