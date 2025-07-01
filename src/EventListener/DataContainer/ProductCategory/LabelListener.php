<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\EventListener\DataContainer\ProductCategory;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Doctrine\DBAL\Connection;
use Isotope\CoreBundle\EventListener\DataContainer\Product\LabelListener as ProductLabelListener;
use Symfony\Contracts\Service\ResetInterface;

#[AsCallback('tl_iso_product_category', 'list.sorting.child_record_callback')]
class LabelListener implements ResetInterface
{
    private int|null $pageId = null;
    private array|null $cache = null;

    public function __construct(
        private readonly Connection $connection,
        private readonly ProductLabelListener $productLabelListener,
    ) {
    }

    public function __invoke(array $row): string
    {
        if ($this->pageId !== $row['pid']) {
            $this->pageId = $row['pid'];
            $this->cache = $this->connection->fetchAllAssociativeIndexed(
                'SELECT id, name, images FROM tl_iso_product WHERE id IN (SELECT product_id FROM tl_iso_product_category WHERE pid=?)',
                [$row['pid']],
            );
        }

        $product = $this->cache[$row['product_id']];

        return \sprintf(
            '<div style="display:flex"><div style="width:50px;">%s</div><div style="margin: 0 0 0 10px;padding: 18px 0;">%s</div></div>',
            $this->productLabelListener->generateImage($product),
            $product['name'],
        );
    }

    public function reset(): void
    {
        $this->pageId = null;
        $this->cache = null;
    }
}
