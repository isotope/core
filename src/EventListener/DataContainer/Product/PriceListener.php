<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\EventListener\DataContainer\Product;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\DC_Table;
use Contao\StringUtil;
use Doctrine\DBAL\Connection;
use Isotope\CoreBundle\Model\ProductType;
use Isotope\Model\TaxClass;

/**
 * Stores prices from and to the subtables also used for advanced pricing and ensures versioning.
 */
class PriceListener
{
    public function __construct(private readonly Connection $connection)
    {
    }

    #[AsCallback('tl_iso_product', 'config.oncreate_version')]
    public function createNewVersion(string $table, int $id, int|string $version, array &$data): void
    {
        // Ignore version and restore for advanced prices
        if (ProductType::findById($data['type'])?->hasAdvancedPrices()) {
            return;
        }

        $current = $this->getPrice($id);

        if (null === $current) {
            return;
        }

        $data['price'] = serialize(['value' => $current['price'], 'unit' => $current['tax_class']]);

        $this->connection->executeStatement(
            'UPDATE tl_version SET data=? WHERE fromTable=? AND pid=? AND version=?',
            [serialize($data), $table, $id, $version],
        );
    }

    #[AsCallback('tl_iso_product', 'config.onrestore_version')]
    public function restoreVersion(string $table, int $pid, int|string $version, array $data): void
    {
        // Ignore version and restore for advanced prices
        if (ProductType::findById($data['type'])?->hasAdvancedPrices()) {
            return;
        }

        $vData = $this->connection->fetchOne(
            'SELECT data FROM tl_version WHERE fromTable=? AND pid=? AND version=?',
            [$table, $pid, $version],
        );
        $vData = StringUtil::deserialize($vData);

        if (!\is_array($vData) || !isset($vData['price'])) {
            return;
        }

        $this->savePrice(StringUtil::deserialize($vData['price'], true), $pid);
    }

    #[AsCallback('tl_iso_product', 'fields.price.load')]
    public function load(mixed $value, DC_Table $dc): array
    {
        $price = $this->getPrice((int) $dc->id);

        if (null === $price) {
            return [
                'value' => '0.00',
                'unit'  => TaxClass::findFallback()?->id ?? 0,
            ];
        }

        return array('value' => $price['price'], 'unit' => $price['tax_class']);
    }

    #[AsCallback('tl_iso_product', 'fields.price.save')]
    public function save(mixed $value, DC_Table $dc): void
    {
        if ($this->savePrice(StringUtil::deserialize($value, true), (int) $dc->id)) {
            $dc->createNewVersion = true;
        }
    }

    /**
     * @return array{
     *     priceId: int,
     *     tierId: int,
     *     tax_class: int,
     *     price: string
     * }|null
     */
    private function getPrice(int $id): array|null
    {
        $price = $this->connection->fetchAssociative("
            SELECT t.id, p.id AS pid, p.tax_class, t.price
            FROM tl_iso_product_price p
            LEFT JOIN tl_iso_product_pricetier t ON p.id=t.pid AND t.min=1
            WHERE p.pid=? AND p.config_id=0 AND p.member_group=0 AND p.start='' AND p.stop=''
        ", [$id]);

        if (false === $price) {
            return null;
        }

        return [
            'tierId' => (int) $price['id'],
            'priceId' => (int) $price['pid'],
            'tax_class' => (int) $price['tax_class'],
            'price' => number_format((float) $price['price'], 2, '.', ''),
        ];
    }

    private function savePrice(array $value, int $productId): bool
    {
        $price = $value['value'] ? number_format((float) $value['value'], 2, '.', '') : '';
        $taxId = (int) $value['unit'];

        $current = $this->getPrice($productId);

        if (null === $current) {
            $priceId = $this->insertPrice($productId, $taxId);
            $this->insertPriceTier($priceId, $price);

            return true;
        }

        $createNewVersion = false;

        if (0 === $current['tierId']) {
            $current['tierId'] = $this->insertPriceTier($current['priceId'], $price);
            $current['price'] = $price;

            $createNewVersion = true;
        }

        if ($current['price'] !== $price) {
            $this->connection->executeStatement(
                'UPDATE tl_iso_product_pricetier SET tstamp=?, price=? WHERE id=?',
                [time(), $price, $current['tierId']]
            );

            $createNewVersion = true;
        }

        if ($current['tax_class'] !== $taxId) {
            $this->connection->executeStatement(
                'UPDATE tl_iso_product_price SET tstamp=?, tax_class=? WHERE id=?',
                [time(), $taxId, $current['priceId']]
            );

            $createNewVersion = true;
        }

        return $createNewVersion;
    }

    private function insertPrice(int $productId, int $taxId): int
    {
        $this->connection->executeStatement(
            'INSERT INTO tl_iso_product_price (pid,tstamp,tax_class) VALUES (?,?,?)',
            [$productId, time(), $taxId],
        );

        return $this->connection->lastInsertId();
    }

    private function insertPriceTier(int $priceId, string $price): int
    {
        $this->connection->executeStatement(
            'INSERT INTO tl_iso_product_pricetier (pid,tstamp,min,price) VALUES (?,?,1,?)',
            [$priceId, time(), $price]
        );

        return $this->connection->lastInsertId();
    }
}
