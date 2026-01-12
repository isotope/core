<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Migration;

use Contao\CoreBundle\Migration\MigrationResult;
use Contao\CoreBundle\Migration\Version507\AbstractFieldPermissionMigration;
use Doctrine\DBAL\Connection;

class FieldPermissionMigration extends AbstractFieldPermissionMigration
{
    private const array RENAME_FIELDS = [
        'iso_product_types' => 'iso_producttypes',
        'iso_payment_modules' => 'iso_payments',
        'iso_shipping_modules' => 'iso_shippings',
    ];

    public function __construct(private readonly Connection $connection)
    {
        parent::__construct($this->connection);
    }

    public function run(): MigrationResult
    {
        $schemaManager = $this->connection->createSchemaManager();

        foreach (['tl_user', 'tl_user_group'] as $table) {
            if (!$schemaManager->tablesExist([$table])) {
                continue;
            }

            $columns = $schemaManager->listTableColumns($table);

            foreach (self::RENAME_FIELDS as $old => $new) {
                if (\array_key_exists($old, $columns) && !\array_key_exists($new, $columns)) {
                    $this->connection->executeStatement("ALTER TABLE $table CHANGE COLUMN $old $new blob NULL");
                }
            }
        }

        return parent::run();
    }

    protected function getMapping(): array
    {
        return [
            'iso_product_typep' => [
                'create' => 'tl_iso_producttype::create',
                'delete' => 'tl_iso_producttype::delete',
            ],
            'iso_payment_modulep' => [
                'create' => 'tl_iso_payment::create',
                'delete' => 'tl_iso_payment::delete',
            ],
            'iso_shipping_modulep' => [
                'create' => 'tl_iso_shipping::create',
                'delete' => 'tl_iso_shipping::delete',
            ],
            'iso_tax_classp' => [
                'create' => 'tl_iso_tax_class::create',
                'delete' => 'tl_iso_tax_class::delete',
            ],
            'iso_tax_ratep' => [
                'create' => 'tl_iso_tax_rate::create',
                'delete' => 'tl_iso_tax_rate::delete',
            ],
            'iso_configp' => [
                'create' => 'tl_iso_config::create',
                'delete' => 'tl_iso_config::delete',
            ],
        ];
    }
}
