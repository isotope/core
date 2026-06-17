<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\EventListener\DataContainer\Content;

use Contao\Controller;
use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\System;

#[AsCallback('tl_content', 'fields.iso_sortingFields.options')]
#[AsCallback('tl_content', 'fields.iso_listingSortField.options')]
class SortingFieldsOptionsListener
{
    use DcaLabelTrait;

    public function __invoke(): array
    {
        Controller::loadDataContainer('tl_iso_product');
        System::loadLanguageFile('tl_iso_product');

        $options = [];

        foreach ($GLOBALS['TL_DCA']['tl_iso_product']['fields'] as $name => $config) {
            if ($config['attributes']['fe_sorting'] ?? false) {
                $options[$name] = $this->getDcaLabel($name, $config);
            }
        }

        natcasesort($options);

        return $options;
    }
}
