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
            'price' => $this->validate('/^[\d .-]*$/', (string) $value, 'digit', $widget),
            'discount' => $this->validate('/^[-+]\d+(\.\d+)?%?$/', (string) $value, 'discount', $widget),
            'surcharge' => $this->validate('/^-?\d+(\.\d+)?%?$/', (string) $value, 'surcharge', $widget),
            default => false,
        };
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
