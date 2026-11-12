<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Test\Attribute\Option;

use Contao\CoreBundle\Routing\ScopeMatcher;
use Doctrine\DBAL\Connection;
use Isotope\CoreBundle\Attribute\Option\OptionFactory;
use Isotope\CoreBundle\Attribute\Option\OptionsSource;
use Isotope\CoreBundle\Attribute\OptionsAttributeInterface;
use Isotope\CoreBundle\Model\AttributeOptionModel;
use Isotope\CoreBundle\Product\CoreProduct;
use Isotope\CoreBundle\Store\Context\ProductContext;
use Isotope\CoreBundle\Store\Price\PriceCalculator;
use Isotope\CoreBundle\Test\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

final class OptionFactoryTest extends TestCase
{
    #[DataProvider('emptyForeignKeyProvider')]
    public function testForeignKeyWithoutActiveLinesReturnsNoOptions(string $foreignKey): void
    {
        $attribute = $this->createStub(OptionsAttributeInterface::class);
        $attribute
            ->method('getOptionsSource')
            ->willReturn(OptionsSource::ForeignKey)
        ;

        $connection = $this->createMock(Connection::class);
        $connection
            ->expects($this->never())
            ->method('iterateAssociative')
        ;

        $factory = new OptionFactory(
            $this->createContaoFrameworkStub([AttributeOptionModel::class => $this->createAdapterStub([])]),
            $connection,
            $this->createStub(TranslatorInterface::class),
            $this->createStub(PriceCalculator::class),
            $this->createStub(Environment::class),
            $this->createStub(ScopeMatcher::class),
        );

        $context = new ProductContext($this->createStub(CoreProduct::class), $this->createStoreContext());

        $this->assertTrue($factory->create($attribute, $context, null, $foreignKey)->isEmpty());
    }

    public static function emptyForeignKeyProvider(): iterable
    {
        yield 'empty' => [''];
        yield 'whitespace only' => [" \n\t\r\n"];
        yield 'commented out source' => ['# en=tl_table.label'];
        yield 'comments and blank lines' => ["# en=tl_table.label\n\n # de=tl_table.label_de\n"];
    }
}
