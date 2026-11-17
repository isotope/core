<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Test\Product\Query;

use Isotope\CoreBundle\Product\Query\Sorting;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class SortingTest extends TestCase
{
    #[DataProvider('sortingProvider')]
    public function testInMemorySortingHonorsDirection(array $values, bool $descending, array $expected): void
    {
        $sorting = new Sorting('price', $descending);
        usort($values, $sorting->sort(...));

        $this->assertSame($expected, $values);
        $this->assertSame(0, $sorting->sort($expected[0], $expected[0]));
    }

    public static function sortingProvider(): iterable
    {
        yield 'ascending prices' => [[10, 2, 5, 2], false, [2, 2, 5, 10]];
        yield 'descending prices' => [[10, 2, 5, 2], true, [10, 5, 2, 2]];
        yield 'ascending labels' => [['blue', 'red', 'green'], false, ['blue', 'green', 'red']];
        yield 'descending labels' => [['blue', 'red', 'green'], true, ['red', 'green', 'blue']];
    }
}
