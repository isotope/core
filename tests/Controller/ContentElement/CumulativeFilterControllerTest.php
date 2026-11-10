<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Test\Controller\ContentElement;

use Codefog\HasteBundle\UrlParser;
use Contao\ContentModel;
use Contao\CoreBundle\Security\Authentication\Token\TokenChecker;
use Contao\CoreBundle\Twig\FragmentTemplate;
use Doctrine\DBAL\DriverManager;
use Isotope\CoreBundle\Attribute\AttributeRegistry;
use Isotope\CoreBundle\Attribute\Filter\FilterAttribute;
use Isotope\CoreBundle\Attribute\Filter\FilterOptionsProvider;
use Isotope\CoreBundle\Attribute\Option\Option;
use Isotope\CoreBundle\Attribute\Option\OptionCollection;
use Isotope\CoreBundle\Controller\ContentElement\CumulativeFilterController;
use Isotope\CoreBundle\Product\Finder\ProductFinderFactory;
use Isotope\CoreBundle\Product\Query\Filter\EqualFilter;
use Isotope\CoreBundle\Product\Query\Filter\NotEqualFilter;
use Isotope\CoreBundle\Product\Query\ProductQuery;
use Isotope\CoreBundle\Product\Query\ProductQueryFactory;
use Isotope\CoreBundle\Test\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;

final class CumulativeFilterControllerTest extends TestCase
{
    private array $previousDca;

    protected function setUp(): void
    {
        $this->previousDca = $GLOBALS['TL_DCA'] ?? [];
    }

    protected function tearDown(): void
    {
        $GLOBALS['TL_DCA'] = $this->previousDca;

        parent::tearDown();
    }

    #[DataProvider('countProvider')]
    public function testCountsAndLinks(string $queryType, string $countType, bool $selected, int|null $expectedCount, string $expectedFilter, string $filterQuery = '', bool $multiple = false): void
    {
        $GLOBALS['TL_DCA']['tl_iso_product']['fields']['color']['eval']['multiple'] = $multiple;
        $GLOBALS['TL_DCA']['tl_iso_product']['fields']['color']['eval']['csv'] = ',';
        $connection = DriverManager::getConnection(['driver' => 'pdo_sqlite', 'memory' => true]);
        $pdo = $connection->getNativeConnection();
        $this->assertInstanceOf(\PDO::class, $pdo);
        $pdo->sqliteCreateFunction(
            'FIND_IN_SET',
            static function (string $value, string $list): int {
                $index = array_search($value, explode(',', $list), true);

                return false === $index ? 0 : $index + 1;
            },
            2,
        );
        $connection->executeStatement('CREATE TABLE tl_iso_product (id INTEGER PRIMARY KEY, pid INTEGER, language TEXT, published INTEGER, start TEXT, stop TEXT, color TEXT)');
        $connection->executeStatement('CREATE TABLE tl_iso_product_category (pid INTEGER, product_id INTEGER)');

        foreach ($multiple ? [['red'], ['blue'], ['red', 'blue']] : ['red', 'blue', 'blue'] as $i => $color) {
            $color = $multiple ? implode(',', $color) : $color;
            $connection->insert('tl_iso_product', ['id' => $i + 1, 'pid' => 0, 'language' => '', 'published' => 1, 'start' => '', 'stop' => '', 'color' => $color]);
            $connection->insert('tl_iso_product_category', ['pid' => 10, 'product_id' => $i + 1]);
        }

        $query = new ProductQuery();
        $query->setFilters('shop', $selected ? [new EqualFilter('color', 'red')] : []);
        $query->setFilters('other', [new EqualFilter('color', 'green')]);
        $request = Request::create('https://example.com/shop?'.$query.'&_p12=3&campaign=test');
        $stack = new RequestStack();
        $stack->push($request);
        $provider = $this->getMockBuilder(FilterOptionsProvider::class)->disableOriginalConstructor()->onlyMethods(['getAttributes'])->getMock();
        $provider
            ->expects($this->once())
            ->method('getAttributes')
            ->willReturn([
                new FilterAttribute('color', 'Color', new OptionCollection([
                    new Option('red', 'Red'),
                    new Option('blue', 'Blue'),
                ])),
            ])
        ;

        $attributes = $this->createStub(AttributeRegistry::class);
        $attributes
            ->method('getNames')
            ->willReturn(['id', 'color'])
        ;
        $queryFactory = new ProductQueryFactory([':' => EqualFilter::class, '!' => NotEqualFilter::class], $attributes, $stack);

        $controller = $this->getMockBuilder(CumulativeFilterController::class)
            ->setConstructorArgs([
                $provider,
                new ProductFinderFactory($connection, $this->createStub(TokenChecker::class)),
                $queryFactory,
                $attributes,
                new UrlParser($stack),
            ])
            ->onlyMethods(['getCategories', 'getProductQuery'])
            ->getMock()
        ;

        $controller
            ->expects($this->once())
            ->method('getCategories')
            ->willReturn([10])
        ;

        $controller
            ->method('getProductQuery')
            ->willReturn($query)
        ;
        $model = $this->createStub(ContentModel::class);
        $model
            ->method('__get')
            ->willReturnMap([
                ['iso_cumulativeFields', serialize([['attribute' => 'color', 'queryType' => $queryType, 'matchCount' => $countType]])],
                ['iso_filterKey', 'shop'],
                ['iso_filterQuery', $filterQuery],
            ])
        ;
        $template = new FragmentTemplate('content_element/iso_cumulativefilter', static fn () => new Response());
        (new \ReflectionMethod($controller, 'getResponse'))->invoke($controller, $template, $model, $request);
        $data = $template->getData();
        $items = $data['filters']['color']['items'];
        $this->assertSame($expectedCount, $items[1]['count']);
        $this->assertSame($selected, $items[0]['is_active']);
        $this->assertSame($selected, $data['show_clear']);
        $this->assertNull($selected ? $items[0]['count'] : null, 'Selected options have no result count.');
        parse_str(parse_url($items[1]['href'], PHP_URL_QUERY), $params);
        $this->assertSame($expectedFilter, $params['_fshop']);
        $this->assertSame('color:green', $params['_fother']);
        $this->assertSame('test', $params['campaign']);
        $this->assertArrayNotHasKey('_p12', $params);
        parse_str(parse_url($data['link_clear_all'], PHP_URL_QUERY), $clearParams);
        $this->assertArrayNotHasKey('_fshop', $clearParams);
        $this->assertSame('color:green', $clearParams['_fother']);
        $this->assertSame($selected ? 'color:red' : '', implode(';', array_map(strval(...), $query->getFilters(['shop']))), 'Generating links must not change the current query.');
        $connection->close();
    }

    public static function countProvider(): iterable
    {
        yield 'total with no selection' => ['or', 'all', false, 2, 'color:blue'];
        yield 'difference with no selection' => ['or', 'new', false, 2, 'color:blue'];
        yield 'OR total' => ['or', 'all', true, 3, 'color:red|blue'];
        yield 'OR difference' => ['or', 'new', true, 2, 'color:red|blue'];
        yield 'AND single replaces total' => ['and', 'all', true, 2, 'color:blue'];
        yield 'AND single replaces difference' => ['and', 'new', true, 1, 'color:blue'];
        yield 'configured query restricts total' => ['or', 'all', false, 1, 'color:blue', 'id:1|2'];
        yield 'configured query restricts difference' => ['or', 'new', true, 1, 'color:red|blue', 'id:1|2'];
        yield 'counts disabled' => ['or', 'none', true, null, 'color:red|blue'];
        yield 'multiple AND requires both' => ['and', 'all', true, 1, 'color:red,blue', '', true];
        yield 'multiple OR matches either' => ['or', 'all', true, 3, 'color:red|blue', '', true];
        yield 'multiple OR difference' => ['or', 'new', true, 1, 'color:red|blue', '', true];
        yield 'multiple configured query' => ['or', 'all', false, 1, 'color:blue', 'color!red', true];
    }

    #[DataProvider('toggleProvider')]
    public function testToggle(string $initial, string $queryType, bool $multiple, string $expected): void
    {
        $filters = '' === $initial ? [] : [new EqualFilter('color', $initial)];
        $otherFilter = new EqualFilter('size', 'large');
        $exclusion = new NotEqualFilter('color', 'green');
        $filters = [$otherFilter, $exclusion, ...$filters];
        $controller = new CumulativeFilterController(
            $this->createStub(FilterOptionsProvider::class),
            $this->createStub(ProductFinderFactory::class),
            $this->createStub(ProductQueryFactory::class),
            $this->createStub(AttributeRegistry::class),
            new UrlParser(new RequestStack()),
        );
        $result = (new \ReflectionMethod($controller, 'toggleFilter'))->invoke($controller, $filters, 'color', 'blue', $queryType, $multiple);
        $isActive = new \ReflectionMethod($controller, 'isFilterActive');

        $this->assertSame($otherFilter, $result[0]);
        $this->assertSame($exclusion, $result[1]);
        $this->assertSame($expected, implode(';', array_map(strval(...), \array_slice($result, 2))));
        $this->assertSame('size:large', (string) $otherFilter);
        $this->assertSame('' !== $expected && str_contains($expected, 'blue'), $isActive->invoke($controller, $result, 'color', 'blue'));
        $this->assertTrue($isActive->invoke($controller, $result, 'size', 'large'));
        $this->assertFalse($isActive->invoke($controller, $result, 'size', 'blue'));
        $this->assertFalse($isActive->invoke($controller, $result, 'color', 'green'));
    }

    public static function toggleProvider(): iterable
    {
        yield 'single AND replaces' => ['red', 'and', false, 'color:blue'];
        yield 'single OR adds' => ['red', 'or', false, 'color:red|blue'];
        yield 'single deselects' => ['blue', 'and', false, ''];
        yield 'OR removes one value' => ['red|blue', 'or', false, 'color:red'];
        yield 'multiple AND adds' => ['red', 'and', true, 'color:red,blue'];
        yield 'multiple OR adds' => ['red', 'or', true, 'color:red|blue'];
        yield 'multiple AND removes one value' => ['red,blue', 'and', true, 'color:red'];
        yield 'zero is a value' => ['0', 'or', false, 'color:0|blue'];
    }
}
