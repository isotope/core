<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\EventListener\DataContainer\Product;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\CoreBundle\Picker\PickerBuilderInterface;
use Contao\DataContainer;
use Contao\DC_Table;
use Contao\Image;
use Contao\StringUtil;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Types;
use Twig\Environment;

class CategoryListener extends AbstractSubtableListener
{
    public function __construct(
        Connection $connection,
        private readonly Environment $twig,
        private readonly PickerBuilderInterface $pickerBuilder,
    ) {
        parent::__construct($connection);
    }

    #[AsCallback('tl_iso_product', 'config.oncopy')]
    public function copy(int|string $productId, DataContainer $dc): void
    {
        $categories = $this->connection->fetchAllAssociative(
            <<<'SQL'
                    SELECT
                        p.pid,
                        p.product_sorting,
                        (SELECT MAX(c.sorting) FROM tl_iso_product_category c WHERE c.pid=p.pid) AS sorting
                    FROM tl_iso_product_category p
                    WHERE p.product_id=?
                SQL,
            [$dc->id],
        );

        foreach ($categories as $category) {
            $this->connection->insert('tl_iso_product_category', [
                'pid' => $category['pid'],
                'tstamp' => time(),
                'sorting' => $category['sorting'] + 128,
                'product_id' => $productId,
                'product_sorting' => $category['product_sorting'],
            ]);
        }
    }

    #[AsCallback('tl_iso_product', 'config.ondelete')]
    public function delete(DataContainer $dc, int|string $undoId): void
    {
        $data = StringUtil::deserialize($this->connection->fetchOne('SELECT data FROM tl_undo WHERE id=?', [$undoId]));

        if (!\is_array($data)) {
            return;
        }

        $data['tl_iso_product_category'] = $this->connection->fetchAllAssociative('SELECT * FROM tl_iso_product_category WHERE product_id=?', [$dc->id]);

        $this->connection->executeStatement('DELETE FROM tl_iso_product_category WHERE product_id=?', [$dc->id]);
        $this->connection->executeStatement('UPDATE tl_undo SET data=? WHERE id=?', [serialize($data), $undoId]);
    }

    #[AsCallback('tl_iso_product', 'fields.pages.load')]
    public function load(mixed $value, DC_Table $dc): array
    {
        return $this->getValueForProduct((int) $dc->id);
    }

    #[AsCallback('tl_iso_product', 'fields.pages.save')]
    public function save(mixed $value, DC_Table $dc): void
    {
        $pageIds = StringUtil::deserialize($value);

        if (\is_array($pageIds) && $this->saveValueForProduct($pageIds, (int) $dc->id)) {
            $dc->createNewVersion = true;
        }
    }

    #[AsCallback('tl_iso_product', 'list.sorting.panel_callback.iso_sorting')]
    public function categorySortingPanel(DataContainer $dc): string
    {
        if ($dc->currentPid) {
            return '';
        }

        return $this->twig->render('@Contao/backend/isotope/product-category-sorting-panel.html.twig', [
            'pickerUrl' => $this->pickerBuilder->getUrl('dc.tl_page'),
            // TODO: replace with Twig {{ backend_icon }} in Contao 5.5+
            'icon' => Image::getHtml('pagemounts.svg', $GLOBALS['TL_LANG']['tl_iso_product']['sorting']),
        ]);
    }

    protected function getField(): string
    {
        return 'pages';
    }

    protected function getValueForProduct(int $productId): array
    {
        return $this->connection->fetchFirstColumn(
            'SELECT pid FROM tl_iso_product_category WHERE product_id=? ORDER BY product_sorting, id',
            [$productId],
        );
    }

    protected function saveValueForProduct(array $value, int $productId): bool
    {
        if (empty($value)) {
            $affected = $this->connection->executeStatement(
                'DELETE FROM tl_iso_product_category WHERE product_id=?',
                [$productId],
            );

            return $affected > 0;
        }

        $createNewVersion = false;

        $affected = $this->connection->executeStatement(
            'DELETE FROM tl_iso_product_category WHERE product_id=? AND pid NOT IN (?)',
            [$productId, $value],
            [Types::INTEGER, ArrayParameterType::INTEGER],
        );

        if ($affected > 0) {
            $createNewVersion = true;
        }

        $current = $this->connection->fetchAllKeyValue(
            'SELECT pid, product_sorting FROM tl_iso_product_category WHERE product_id=? ORDER BY sorting, id',
            [$productId],
        );

        foreach (array_diff($value, array_keys($current)) as $sorting => $pageId) {
            $this->connection->executeStatement(
                <<<'SQL'
                    INSERT INTO tl_iso_product_category
                        (pid,tstamp,sorting,product_id,product_sorting)
                    VALUES (?, ?, (SELECT IFNULL(MAX(sorting), 0)+128 FROM (SELECT * FROM tl_iso_product_category WHERE pid=?) c), ?, ?)
                SQL,
                [$pageId, time(), $pageId, $productId, $sorting],
            );

            $createNewVersion = true;
        }

        foreach ($value as $sorting => $pid) {
            if (isset($current[$pid]) && $current[$pid] !== $sorting) {
                $this->connection->executeStatement(
                    'UPDATE tl_iso_product_category SET product_sorting=? WHERE pid=? AND product_id=?',
                    [$sorting, $pid, $productId],
                );
            }
        }

        return $createNewVersion;
    }
}
