<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\EventListener\DataContainer\RelatedProduct;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\DataContainer;
use Contao\Input;
use Doctrine\DBAL\Connection;

#[AsCallback('tl_iso_related_product', 'config.onload')]
class CategoryOptionsListener
{
    public function __construct(
        private readonly Connection $connection,
    ) {
    }

    public function __invoke(DataContainer $dc): void
    {
        $currentRecord = $dc->getCurrentRecord();
        $options = $this->connection->fetchAllKeyValue(
            '
                SELECT id, name FROM tl_iso_related_category
                WHERE id NOT IN (
                    SELECT category FROM tl_iso_related_product
                    WHERE pid=? AND id!=?
                )
            ',
            [$dc->currentPid, $currentRecord['id'] ?? 0],
        );

        if (empty($options)) {
            $GLOBALS['TL_DCA']['tl_iso_related_product']['config']['closed'] = true;
        }

        if ('edit' === Input::get('act')) {
            unset($GLOBALS['TL_DCA']['tl_iso_related_product']['fields']['category']['foreignKey']);
            $GLOBALS['TL_DCA']['tl_iso_related_product']['fields']['category']['options'] = $options;
        }
    }
}
