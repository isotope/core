<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Attribute\Option;

use Ramsey\Collection\AbstractCollection;

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

    // TODO: maybe remove this?
    public function asWidgetArray(): array
    {
        $options = [];

        /** @var Option $option */
        foreach ($this as $option) {
            $options[] = [
                'value' => $option->getValue(),
                'label' => $option->getLabel(),
                'group' => $option->isGroup(),
                'default' => $option->isDefault(),
            ];
        }

        return $options;
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
