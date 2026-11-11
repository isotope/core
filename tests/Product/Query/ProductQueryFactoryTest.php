<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Test\Product\Query;

use Isotope\CoreBundle\Attribute\AttributeRegistry;
use Isotope\CoreBundle\Product\Query\ProductQuery;
use Isotope\CoreBundle\Product\Query\ProductQueryFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

final class ProductQueryFactoryTest extends TestCase
{
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
