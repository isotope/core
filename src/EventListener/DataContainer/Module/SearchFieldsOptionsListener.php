<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\EventListener\DataContainer\Module;

use Contao\Controller;
use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\System;

#[AsCallback('tl_module', 'fields.iso_searchFields.options')]
class SearchFieldsOptionsListener
{
    use DcaLabelTrait;

    public function __invoke(): array
    {
        Controller::loadDataContainer('tl_iso_product');
        System::loadLanguageFile('tl_iso_product');

        $options = [];

        foreach ($GLOBALS['TL_DCA']['tl_iso_product']['fields'] as $name => $config) {
            if (($config['attributes']['dynamic'] ?? false)
                || (($config['eval']['multiple'] ?? false) && !($config['eval']['csv'] ?? false))
            ) {
                // Cannot search for dynamic or serialized attributes
                continue;
            }

            if ($config['attributes']['fe_search'] ?? false) {
                $options[$name] = $this->getDcaLabel($name, $config);
            }
        }

        natcasesort($options);

        return $options;
    }
}
