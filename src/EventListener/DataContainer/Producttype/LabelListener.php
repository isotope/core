<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\EventListener\DataContainer\Producttype;

use Codefog\HasteBundle\Formatter;
use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\DataContainer;

#[AsCallback('tl_iso_producttype', 'list.label.label')]
class LabelListener
{
    public function __construct(private readonly Formatter $formatter)
    {
    }

    public function __invoke(array $row, string $label, DataContainer $dc, array $args): array
    {
        if (!($row['fallback'] ?? null)) {
            return $args;
        }

        $args[0] = \sprintf(
            '%s <span style="color:#b3b3b3; padding-left:3px;">[%s]</span>',
            $args[0],
            $this->formatter->dcaLabel($dc->table, 'fallback'),
        );

        return $args;
    }
}
