<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\EventListener\DataContainer\OrderStatus;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\DataContainer;
use Isotope\CoreBundle\Util\ColorUtil;

#[AsCallback('tl_iso_orderstatus', 'list.label.label')]
class LabelListener
{
    public function __invoke(array $row, string|null $label, DataContainer $dc, array $args): array
    {
        foreach ($GLOBALS['TL_DCA']['tl_iso_orderstatus']['list']['label']['fields'] as $k => $field) {
            if ('color' === $field) {
                $args[$k] = \sprintf(
                    '<span style="display:inline-block; padding: 2px 5px 3px;border-radius: 2px;vertical-align: middle;%s">%s</span>',
                    ColorUtil::getCssBackgroundStyles($row['color']),
                    $args[$k],
                );
            }
        }

        return $args;
    }
}
