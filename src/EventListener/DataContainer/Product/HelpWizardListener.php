<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\EventListener\DataContainer\Product;

use Contao\CoreBundle\DependencyInjection\Attribute\AsHook;
use Doctrine\DBAL\Connection;

#[AsHook('loadLanguageFile')]
class HelpWizardListener
{
    public function __construct(private readonly Connection $connection)
    {
    }

    public function __invoke(string $name): void
    {
        if ('explain' !== $name) {
            return;
        }

        $types = $this->connection->fetchAllNumeric('SELECT id, name, description FROM tl_iso_producttype');

        foreach ($types as $type) {
            $GLOBALS['TL_LANG']['XPL']['tl_iso_product.type'][$type[0]] = [$type[1], $type[2]];
        }
    }
}
