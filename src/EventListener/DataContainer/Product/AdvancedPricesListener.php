<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\EventListener\DataContainer\Product;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\Database;
use Contao\DataContainer;
use Contao\StringUtil;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Types;
use Isotope\Haste\Util\Format;
use Terminal42\DcawizardBundle\Widget\DcaWizard;
use Twig\Environment;

#[AsCallback('tl_iso_product_price', 'config.oncreate_version', 'createVersion')]
#[AsCallback('tl_iso_product_price', 'config.onrestore_version', 'restoreVersion')]
class AdvancedPricesListener extends AbstractSubtableListener
{
    public function __construct(
        Connection $connection,
        private readonly Environment $twig,
    ) {
        parent::__construct($connection);
    }

    #[AsCallback('tl_iso_product', 'fields.prices.eval.list', singleton: true)]
    public function generateDcawizard(array $rows, int|string $id, DcaWizard $objWidget): string
    {
        $operations = \is_array($objWidget->operations ?? null) ? $objWidget->operations : ['edit'];

        // Fetch all tiers in one query instead of a query per price
        $tiers = [];
        $result = $this->connection->fetchAllAssociative(
            'SELECT pid, CONCAT(min, "=", price) AS value FROM tl_iso_product_pricetier WHERE pid IN (?) ORDER BY min',
            [array_column($rows, 'id')],
            [ArrayParameterType::INTEGER],
        );

        foreach ($result as $tier) {
            $tiers[$tier['pid']][] = $tier['value'];
        }

        foreach ($rows as &$row) {
            $row['tiers'] = $tiers[$row['id']] ?? '';
            $row['operations'] = '';

            foreach ($operations as $operation) {
                $row['operations'] .= $objWidget->generateRowOperation($operation, $row);
            }
        }

        return $this->twig->render('@Contao/backend/isotope/prices-list.html.twig', [
            'prices' => $rows,
        ]);
    }

    #[AsCallback('tl_iso_product_price', 'list.sorting.child_record')]
    public function listRows(array $row): string
    {
        if (!$row['id']) {
            return '';
        }

        $arrTiers = [];
        $objTiers = Database::getInstance()->execute("SELECT * FROM tl_iso_product_pricetier WHERE pid={$row['id']} ORDER BY min");

        while ($objTiers->next()) {
            $arrTiers[] = "{$objTiers->min}={$objTiers->price}";
        }

        $arrInfo = ['<tr><td><span class="tl_label">'.$GLOBALS['TL_LANG']['tl_iso_product_price']['price_tiers'][0].':</span></td><td>'.implode(', ', $arrTiers).'</td></tr>'];

        foreach ($row as $name => $value) {
            switch ($name) {
                case 'id':
                case 'pid':
                case 'tstamp':
                    break;

                default:
                    if ('' !== $value && $value > 0) {
                        $arrInfo[] = '<tr><td><span class="tl_label">'.Format::dcaLabel('tl_iso_product_price', $name).':</span></td><td>'.Format::dcaValue('tl_iso_product_price', $name, $value).'</td></tr>';
                    }
                    break;
            }
        }

        return '<table class="tl_header_table">'.implode('', $arrInfo).'</table>';
    }

    #[AsCallback('tl_iso_product_price', 'fields.price_tiers.load')]
    public function loadPriceTiers(mixed $value, DataContainer $dc): array
    {
        return $this->getValue((int) $dc->id);
    }

    #[AsCallback('tl_iso_product_price', 'fields.price_tiers.save')]
    public function savePriceTiers(mixed $value, DataContainer $dc): void
    {
        $this->saveValue(StringUtil::deserialize($value, true), (int) $dc->id);
    }

    protected function getField(): string
    {
        return 'price_tiers';
    }

    protected function getValue(int $pid): array|null
    {
        $tiers = $this->connection->fetchAllAssociative(
            'SELECT min, price FROM tl_iso_product_pricetier WHERE pid=? ORDER BY min',
            [$pid],
        );

        if (empty($tiers)) {
            return [['min' => 1]];
        }

        return $tiers;
    }

    protected function saveValue(array $value, int $pid): bool
    {
        if (empty($value)) {
            $affected = $this->connection->executeStatement('DELETE FROM tl_iso_product_pricetier WHERE pid=?', [$pid]);

            return $affected > 0;
        }

        $time = time();
        $insert = [];
        $update = [];
        $delete = $this->connection->fetchFirstColumn('SELECT min FROM tl_iso_product_pricetier WHERE pid=?', [$pid]);
        $createNewVersion = false;

        foreach ($value as $new) {
            $min = (int) $new['min'];
            $pos = array_search($min, $delete, true);

            if (false === $pos) {
                $insert[$min] = $new['price'];
            } else {
                $update[$min] = $new['price'];
                unset($delete[$pos]);
            }
        }

        if (!empty($delete)) {
            $this->connection->delete(
                'tl_iso_product_pricetier',
                ['pid' => $pid, 'min' => $delete],
                [Types::INTEGER, ArrayParameterType::INTEGER],
            );

            $createNewVersion = true;
        }

        foreach ($update as $min => $price) {
            $this->connection->update(
                'tl_iso_product_pricetier',
                ['tstamp' => $time, 'price' => $price],
                ['pid' => $pid, 'min' => $min],
            );

            $createNewVersion = true;
        }

        foreach ($insert as $min => $price) {
            $this->connection->insert('tl_iso_product_pricetier', [
                'pid' => $pid,
                'tstamp' => $time,
                'min' => $min,
                'price' => $price,
            ]);

            $createNewVersion = true;
        }

        return $createNewVersion;
    }
}
