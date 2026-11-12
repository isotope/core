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
    #[DataProvider('foreignKeyFallbackProvider')]
    public function testForeignKeyFallbackSelection(string $foreignKey, string $expectedField, string $locale = 'en'): void
    {
        $attribute = $this->createStub(OptionsAttributeInterface::class);
        $attribute
            ->method('getOptionsSource')
            ->willReturn(OptionsSource::ForeignKey)
        ;

        $connection = $this->createMock(Connection::class);
        $connection
            ->expects($this->once())
            ->method('iterateAssociative')
            ->with("SELECT id AS value, $expectedField AS label FROM tl_options ORDER BY label")
            ->willReturn(new \ArrayIterator([['value' => 42, 'label' => 'Option label']]))
        ;

        $factory = new OptionFactory(
            $this->createContaoFrameworkStub([AttributeOptionModel::class => $this->createAdapterStub([])]),
            $connection,
            $this->createStub(TranslatorInterface::class),
            $this->createStub(PriceCalculator::class),
            $this->createStub(Environment::class),
            $this->createStub(ScopeMatcher::class),
        );

        $context = new ProductContext($this->createStub(CoreProduct::class), $this->createStoreContext(locale: $locale));

        $options = $factory->create($attribute, $context, null, $foreignKey);

        $this->assertCount(1, $options);
        $this->assertSame('42', $options->first()->getValue());
        $this->assertSame('Option label', $options->first()->getLabel());
    }

    public static function foreignKeyFallbackProvider(): iterable
    {
        yield 'default after unmatched language' => ["de=tl_options.german_label\ntl_options.default_label", 'default_label'];
        yield 'default before unmatched language' => ["tl_options.default_label\nde=tl_options.german_label", 'default_label'];
        yield 'matching language overrides default' => ["tl_options.default_label\nen=tl_options.english_label", 'english_label'];
        yield 'first language remains fallback without default' => ["de=tl_options.german_label\nfr=tl_options.french_label", 'german_label'];
        yield 'underscore regional locale' => ["de=tl_options.german_label\nen-GB=tl_options.british_label", 'british_label', 'en_GB'];
        yield 'hyphenated regional locale' => ["de=tl_options.german_label\nen-GB=tl_options.british_label", 'british_label', 'en-GB'];
        yield 'different region uses default' => ["tl_options.default_label\nen-GB=tl_options.british_label", 'default_label', 'en_US'];
    }

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
