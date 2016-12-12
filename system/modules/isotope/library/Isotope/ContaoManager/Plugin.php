<?php

namespace Isotope\ContaoManager;

use Contao\ManagerPlugin\Bundle\BundlePluginInterface;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;

/**
 * Plugin for the Contao Manager.
 *
 * @author Andreas Schempp <https://github.com/aschempp>
 */
class Plugin implements BundlePluginInterface
{
    /**
     * @inheritdoc
     */
    public function getBundles(ParserInterface $parser)
    {
        return array_merge(
            $parser->parse('isotope', 'ini'),
            $parser->parse('isotope_reports', 'ini'),
            $parser->parse('isotope_rules', 'ini')
        );
    }
}
