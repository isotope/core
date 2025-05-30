<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\EventListener\DataContainer\Attribute;

use Contao\Controller;
use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;

#[AsCallback('tl_iso_attribute', 'fields.rte.options')]
class RteOptionsListener
{
    public function __invoke(): array
    {
        $options = [];

        foreach (preg_grep('/^be_tiny/', array_keys(Controller::getTemplateGroup('be_'))) as $template) {
            $options[] = substr((string) $template, 3);
        }

        return $options;
    }
}
