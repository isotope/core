<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Intl;

use Contao\System;
use Symfony\Contracts\Service\ResetInterface;

class Subdivisions implements ResetInterface
{
    private array|null $options = null;

    private array|null $names = null;

    public function all(): array
    {
        $this->loadOptions();

        return $this->options;
    }

    public function getForCountry(string $country): array
    {
        $this->loadOptions();

        return $this->options[$country];
    }

    public function getNames(): array
    {
        if (null !== $this->names) {
            return $this->names;
        }

        $this->names = [];

        System::loadLanguageFile('subdivisions');

        foreach ($GLOBALS['TL_LANG']['DIV'] as $subdivisions) {
            foreach ($subdivisions as $code => $name) {
                if (\is_array($name)) {
                    $group = $name[''];
                    unset($name['']);

                    $this->names[$code] = $group;
                    $this->names += $name;

                    continue;
                }

                $this->names[$code] = $name;
            }
        }

        return $this->names;
    }

    public function reset(): void
    {
        $this->options = null;
    }

    private function loadOptions(): void
    {
        if (null !== $this->options) {
            return;
        }

        $this->options = [];

        System::loadLanguageFile('subdivisions');

        foreach ($GLOBALS['TL_LANG']['DIV'] as $country => $subdivisions) {
            $country = strtoupper((string) $country);

            foreach ($subdivisions as $code => $name) {
                if (\is_array($name)) {
                    $group = $name[''];
                    unset($name['']);

                    $this->options[$country][$code][$group] = $name;

                    continue;
                }

                $this->options[$country][$code] = $name;
            }
        }
    }
}
