<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Test\Attribute\Filter;

use Contao\CoreBundle\Security\Authentication\Token\TokenChecker;
use Contao\DcaLoader;
use Contao\System;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Isotope\CoreBundle\Attribute\AttributeRegistry;
use Isotope\CoreBundle\Attribute\Filter\FilterAttribute;
use Isotope\CoreBundle\Attribute\Filter\FilterOptionsProvider;
use Isotope\CoreBundle\Attribute\Option\Option;
use Isotope\CoreBundle\Attribute\Option\OptionCollection;
use Isotope\CoreBundle\Product\Finder\ProductFinderFactory;
use Isotope\CoreBundle\Product\Query\Filter\EqualFilter;
use Isotope\CoreBundle\Test\TestCase;
use Symfony\Component\Config\FileLocatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class FilterOptionsProviderTest extends TestCase
{
    public function testOptionsRespectQueryScopeAndProductTypes(): void
    {
        $previousDca = $GLOBALS['TL_DCA'] ?? [];
        $container = $this->getContainerWithContaoConfiguration();
        $locator = $this->createStub(FileLocatorInterface::class);
        $locator
            ->method('locate')
            ->willReturn([])
        ;
        $container->set('contao.resource_locator', $locator);
        System::setContainer($container);
        DcaLoader::switchToCurrentRequest();
        $GLOBALS['TL_DCA']['tl_iso_product']['fields'] = [
            'color' => [],
            'tags' => ['eval' => ['multiple' => true, 'csv' => ',']],
            'sizes' => ['eval' => ['multiple' => true]],
        ];
        $connection = $this->createConnection();
        $connection->executeStatement('ALTER TABLE tl_iso_product ADD COLUMN type INTEGER DEFAULT 1');
        $connection->executeStatement("ALTER TABLE tl_iso_product ADD COLUMN tags TEXT DEFAULT ''");
        $connection->executeStatement("ALTER TABLE tl_iso_product ADD COLUMN sizes TEXT DEFAULT ''");
        $connection->executeStatement('CREATE TABLE tl_iso_producttype (id INTEGER, attributes TEXT, variants INTEGER, variant_attributes TEXT)');
        $connection->insert('tl_iso_producttype', [
            'id' => 1,
            'attributes' => serialize(['color' => ['enabled' => true], 'tags' => ['enabled' => true], 'sizes' => ['enabled' => true]]),
            'variants' => 1,
            'variant_attributes' => serialize(['color' => ['enabled' => true]]),
        ]);
        $connection->update('tl_iso_product', ['type' => 2], ['id' => 2]);
        $connection->update('tl_iso_product', ['color' => 'disabled', 'type' => 2], ['id' => 4]);
        $connection->update('tl_iso_product', ['color' => 'unpublished'], ['id' => 5]);
        $connection->update('tl_iso_product', ['color' => 'hidden-parent'], ['id' => 6]);
        $connection->update('tl_iso_product', ['color' => 'future'], ['id' => 7]);
        $connection->update('tl_iso_product', ['color' => 'expired'], ['id' => 8]);
        $connection->update('tl_iso_product', ['color' => 'other-category'], ['id' => 9]);
        $connection->update('tl_iso_product', ['tags' => 'red,blue,0', 'sizes' => serialize(['small', '0'])], ['id' => 1]);
        $connection->update('tl_iso_product', ['tags' => 'blue,green', 'sizes' => serialize(['large', 'small'])], ['id' => 3]);
        $connection->insert('tl_iso_product', ['id' => 10, 'pid' => 1, 'language' => 'de', 'published' => 1, 'start' => '', 'stop' => '', 'color' => 'translation']);
        $finder = new ProductFinderFactory($connection, $this->createStub(TokenChecker::class));
        $selection = $finder->create([10, 11], 'de');
        $baseQuery = $selection->getQueryBuilder();
        $provider = new FilterOptionsProvider(
            $this->createStub(AttributeRegistry::class),
            $connection,
            $this->createStub(TranslatorInterface::class),
        );
        $sql = $baseQuery->getSQL();
        $parameters = $baseQuery->getParameters();

        try {
            $options = [];

            foreach ($provider->getAttributes(['color', 'tags', 'sizes', 'disabled'], $selection) as $attribute) {
                $options[$attribute->getName()] = $attribute;
            }

            $this->assertSame('color', $options['color']->getName());
            $this->assertSame('color', $options['color']->getLabel());
            $this->assertEqualsCanonicalizing(
                [
                    new Option('red', 'red'),
                    new Option('blue', 'blue'),
                ],
                $options['color']->getOptions()->toArray(),
            );
            $this->assertEqualsCanonicalizing(
                [
                    new Option('red', 'red'),
                    new Option('blue', 'blue'),
                    new Option('green', 'green'),
                    new Option('0', '0'),
                ],
                $options['tags']->getOptions()->toArray(),
            );
            $this->assertArrayNotHasKey('sizes', $options, 'Serialized multiple-choice fields cannot be used as filter options.');
            $this->assertArrayNotHasKey('disabled', $options);
            $this->assertSame($sql, $selection->getQueryBuilder()->getSQL());
            $this->assertSame($parameters, $selection->getQueryBuilder()->getParameters());
            $this->assertSame([], $provider->getAttributes(['color'], $finder->create([], 'en')));
            $filtered = $selection->withFilters([new EqualFilter('id', '2')]);
            $this->assertEquals(
                [
                    new FilterAttribute('color', 'color', new OptionCollection([new Option('blue', 'blue')])),
                ],
                $provider->getAttributes(['color'], $filtered),
                'Variant attributes use the parent product type and configured query.',
            );
        } finally {
            $connection->close();
            DcaLoader::reset();
            $GLOBALS['TL_DCA'] = $previousDca;
        }
    }

    private function createConnection(): Connection
    {
        $connection = DriverManager::getConnection(['driver' => 'pdo_sqlite', 'memory' => true]);
        $connection->executeStatement('CREATE TABLE tl_iso_product (id INTEGER PRIMARY KEY, pid INTEGER, language TEXT, published INTEGER, start TEXT, stop TEXT, color TEXT)');
        $connection->executeStatement('CREATE TABLE tl_iso_product_category (pid INTEGER, product_id INTEGER)');

        foreach ([
            [1, 0, '', 1, '', '', 'red'],
            [2, 1, '', 1, '', '', 'blue'],
            [3, 1, '', 1, '', '', 'blue'],
            [4, 0, '', 1, '', '', 'red'],
            [5, 0, '', 0, '', '', 'red'],
            [6, 5, '', 1, '', '', 'red'],
            [7, 0, '', 1, (string) (time() + 3600), '', 'red'],
            [8, 0, '', 1, '', (string) (time() - 3600), 'red'],
            [9, 0, '', 1, '', '', 'blue'],
        ] as $row) {
            $connection->insert('tl_iso_product', array_combine(['id', 'pid', 'language', 'published', 'start', 'stop', 'color'], $row));
        }

        foreach ([1, 4, 5, 7, 8] as $id) {
            foreach ([10, 11] as $category) {
                $connection->insert('tl_iso_product_category', ['pid' => $category, 'product_id' => $id]);
            }
        }

        $connection->insert('tl_iso_product_category', ['pid' => 20, 'product_id' => 9]);

        return $connection;
    }
}
