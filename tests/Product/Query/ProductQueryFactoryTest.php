<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Test\Product\Query;

use Isotope\CoreBundle\Attribute\AttributeRegistry;
use Isotope\CoreBundle\Product\Query\Filter\EqualFilter;
use Isotope\CoreBundle\Product\Query\Filter\NotEqualFilter;
use Isotope\CoreBundle\Product\Query\ProductQuery;
use Isotope\CoreBundle\Product\Query\ProductQueryFactory;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

final class ProductQueryFactoryTest extends TestCase
{
    /**
     * @param class-string<EqualFilter|NotEqualFilter> $filterClass
     */
    #[DataProvider('filterValueProvider')]
    public function testFilterValuesSurviveQueryRoundTrip(string $filterClass, string $value): void
    {
        $attributes = $this->createStub(AttributeRegistry::class);
        $attributes
            ->method('getNames')
            ->willReturn(['color'])
        ;

        $factory = new ProductQueryFactory([':' => EqualFilter::class, '!' => NotEqualFilter::class], $attributes, new RequestStack());
        $filter = new $filterClass('color', $value);
        $query = $factory->create()->setFilters('shop', [$filter]);
        $restored = $factory->createFromRequest(Request::create('/products?'.$query));

        $this->assertInstanceOf(ProductQuery::class, $restored);

        $filters = $restored->getFilters(['shop']);

        $this->assertCount(1, $filters);
        $this->assertInstanceOf($filterClass, $filters[0]);
        $this->assertSame((string) $filter, (string) $filters[0]);
        $this->assertSame((string) $query, (string) $restored);
    }

    public static function filterValueProvider(): iterable
    {
        yield 'empty equality' => [EqualFilter::class, ''];
        yield 'empty inequality' => [NotEqualFilter::class, ''];
        yield 'zero value' => [EqualFilter::class, '0'];
        yield 'non-empty value' => [EqualFilter::class, 'red'];
    }

    public function testEmptyAndNonEmptyFiltersAreBothParsed(): void
    {
        $factory = new ProductQueryFactory([':' => EqualFilter::class, '!' => NotEqualFilter::class], $this->createStub(AttributeRegistry::class), new RequestStack());
        $filters = $factory->parseFilters(';color:;color!red;', ['color']);

        $this->assertCount(2, $filters);
        $this->assertSame('color:', (string) $filters[0]);
        $this->assertSame('color!red', (string) $filters[1]);
    }

    public function testCurrentQueryWithoutRequest(): void
    {
        $factory = new ProductQueryFactory([], $this->createStub(AttributeRegistry::class), new RequestStack());

        $this->assertNull($factory->getCurrent());
    }

    public function testCurrentQueryFromExplicitRequest(): void
    {
        $factory = new ProductQueryFactory([], $this->createStub(AttributeRegistry::class), new RequestStack());
        $request = Request::create('/products?_q=shoes');

        $query = $factory->getCurrent($request);

        $this->assertInstanceOf(ProductQuery::class, $query);
        $this->assertSame(['' => 'shoes'], $query->getSearches(['']));
        $this->assertSame($query, $factory->getCurrent($request));
    }

    public function testCurrentQueryFromRequestStack(): void
    {
        $stack = new RequestStack();
        $stack->push(Request::create('/products?_q=shoes'));
        $factory = new ProductQueryFactory([], $this->createStub(AttributeRegistry::class), $stack);

        $this->assertSame(['' => 'shoes'], $factory->getCurrent()?->getSearches(['']));
    }
}
