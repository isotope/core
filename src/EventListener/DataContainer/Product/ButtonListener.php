<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\EventListener\DataContainer\Product;

use Contao\CoreBundle\DataContainer\DataContainerOperation;
use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Doctrine\DBAL\Connection;
use Isotope\CoreBundle\Model\ProductTypeModel;

class ButtonListener
{
    public function __construct(
        private readonly Connection $connection,
    ) {
    }

    #[AsCallback('tl_iso_product', 'list.operations.children.button')]
    public function childrenButton(DataContainerOperation $operation): void
    {
        if (!$this->getProductType($operation->getRecord())?->hasVariants()) {
            $operation->setHtml('');
        }
    }

    #[AsCallback('tl_iso_product', 'list.operations.downloads.button')]
    public function downloadsButton(DataContainerOperation $operation): void
    {
        if (!$this->getProductType($operation->getRecord())?->hasDownloads()) {
            $operation->setHtml('');
        }
    }

    private function getProductType(array $product): ProductTypeModel|null
    {
        $id = $product['type'];

        if ($product['pid'] > 0) {
            $id = $this->connection->fetchOne('SELECT type FROM tl_iso_product WHERE id=?', [$product['pid']]);
        }

        return ProductTypeModel::findById($id);
    }
}
