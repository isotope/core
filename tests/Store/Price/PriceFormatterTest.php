<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Test\Store\Price;

use Isotope\CoreBundle\Store\Price\BasePrice;
use Isotope\CoreBundle\Store\Price\CalculatedPrice;
use Isotope\CoreBundle\Store\Price\PriceFormatter;
use Isotope\CoreBundle\Test\TestCase;
use Isotope\CoreBundle\Twig\TemplateMarkup;
use Isotope\CoreBundle\Twig\TemplateMarkupFactory;
use PHPUnit\Framework\Attributes\DataProvider;
use Twig\Environment;

final class PriceFormatterTest extends TestCase
{
    #[DataProvider('rangeProvider')]
    public function testBasePricePreservesTheProductPriceRange(bool $range): void
    {
        $price = (new CalculatedPrice(6, 6))->setRange($range)->setBasePrice(new BasePrice(500, 100, '%s per 100g'));

        $formatter = new PriceFormatter(new TemplateMarkupFactory($this->createStub(Environment::class)));
        $markup = $formatter->getBasePrice($price, $this->createStoreContext());

        $this->assertInstanceOf(TemplateMarkup::class, $markup);
        $this->assertSame($range, $markup['is_range']);
        $this->assertEqualsWithDelta(1.2, $markup['unit_price'], PHP_FLOAT_EPSILON);
    }

    public static function rangeProvider(): iterable
    {
        yield 'exact price' => [false];
        yield 'price range' => [true];
    }
}
