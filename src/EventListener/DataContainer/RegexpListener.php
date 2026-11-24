<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\EventListener\DataContainer;

use Contao\CoreBundle\DependencyInjection\Attribute\AsHook;
use Contao\Widget;

#[AsHook('addCustomRegexp')]
class RegexpListener
{
    public function __invoke(string $regexp, mixed $value, Widget $widget): bool
    {
        return match ($regexp) {
            'optionvalue' => $this->validate('/^[A-Za-z0-9_]+$/D', (string) $value, 'optionvalue', $widget),
            'price' => $this->validate('/^[\d .-]*$/', (string) $value, 'digit', $widget),
            'discount' => $this->validateDiscount((string) $value, $widget),
            'surcharge' => $this->validate('/^-?\d+(\.\d+)?%?$/', (string) $value, 'surcharge', $widget),
            default => false,
        };
    }

    private function validateDiscount(string $value, Widget $widget): bool
    {
        if (
            !preg_match('/^[-+]\d+(\.\d+)?%?$/D', $value)
            || (str_ends_with($value, '%') && (float) $value < -100)
        ) {
            $widget->addError(\sprintf($GLOBALS['TL_LANG']['ERR']['discount'], $widget->label));
        }

        // Always return true because we handled the validation
        return true;
    }

    private function validate(string $pattern, string $value, string $errorKey, Widget $widget): bool
    {
        if (!preg_match($pattern, $value)) {
            $widget->addError(\sprintf($GLOBALS['TL_LANG']['ERR'][$errorKey], $widget->label));
        }

        // Always return true because we handled the validation
        return true;
    }
}
