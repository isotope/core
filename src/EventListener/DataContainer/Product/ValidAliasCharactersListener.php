<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\EventListener\DataContainer\Product;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Doctrine\DBAL\Connection;
use Terminal42\DcMultilingualBundle\Driver;

class ValidAliasCharactersListener
{
    public function __construct(private readonly Connection $connection)
    {
    }

    #[AsCallback('tl_iso_product', 'fields.alias.eval.slugOptions', exact: true, singleton: true, closure: true)]
    public function __invoke(Driver $dc): array
    {
        $currentRecord = $dc->getCurrentRecord();

        $slugOptions = [];
        $validChars = $this->connection->fetchOne(
            'SELECT validAliasCharacters FROM tl_iso_producttype WHERE id=?',
            [$currentRecord['type']],
        );

        if ($validChars) {
            $slugOptions['validChars'] = $validChars;
        }

        return $slugOptions;
    }
}
