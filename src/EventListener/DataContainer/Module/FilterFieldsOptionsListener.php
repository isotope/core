<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\EventListener\DataContainer\Module;

use Contao\Controller;
use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\System;

#[AsCallback('tl_module', 'fields.iso_filterFields.options')]
class FilterFieldsOptionsListener
{
    use DcaLabelTrait;

    public function __invoke(): array
    {
        Controller::loadDataContainer('tl_iso_product');
        System::loadLanguageFile('tl_iso_product');

        $options = array();

        foreach ($GLOBALS['TL_DCA']['tl_iso_product']['fields'] as $name => $config) {
            if ($config['attributes']['fe_filter'] ?? false) {
                $options[$name] = $this->getDcaLabel($name, $config);
            }
        }

        natcasesort($options);

        return $options;
    }
}
