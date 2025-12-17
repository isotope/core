<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\EventListener\DataContainer\AttributeOption;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\Input;
use Isotope\CoreBundle\Model\AttributeOption;

class LabelListener
{
    #[AsCallback('tl_iso_attribute_option', 'config.onload')]
    public function initWrappers(): void
    {
        $act = Input::get('act');

        if (!$act || 'select' === $act) {
            $GLOBALS['TL_WRAPPERS'] = [
                'start' => ['group'],
                'separator' => [],
                'stop' => [],
                'single' => [],
            ];
        }
    }

    #[AsCallback('tl_iso_attribute_option', 'list.label.label')]
    public function label(array $row, string $label): string
    {
        if ('group' === $row['type']) {
            $GLOBALS['TL_WRAPPERS']['stop'][] = 'group';
        }

        if ($row['isDefault']) {
            $label = '<strong>'.$label.'</strong>';
        }

        if ('' !== $row['price'] && isset($GLOBALS['TL_DCA'][AttributeOption::getTable()]['fields']['price'])) {
            $label .= ' <span style="color:#b3b3b3; padding-left:3px;">('.$row['price'].')</span>';
        }

        return $label;
    }
}
