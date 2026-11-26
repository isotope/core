<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Test\Attribute\Option;

use Contao\CoreBundle\Routing\ScopeMatcher;
use Contao\Model\Collection;
use Doctrine\DBAL\Connection;
use Isotope\CoreBundle\Attribute\AttributeRegistry;
use Isotope\CoreBundle\Attribute\Option\OptionFactory;
use Isotope\CoreBundle\Attribute\Option\OptionPriceEvent;
use Isotope\CoreBundle\Attribute\Option\OptionsSource;
use Isotope\CoreBundle\Attribute\OptionsAttributeInterface;
use Isotope\CoreBundle\Model\AttributeOptionModel;
use Isotope\CoreBundle\Product\CoreProduct;
use Isotope\CoreBundle\Product\Event\ProductPriceEvent;
use Isotope\CoreBundle\Product\EventListener\ConfigurationPriceListener;
use Isotope\CoreBundle\RuleBuilder\RuleMatcher;
use Isotope\CoreBundle\Store\Context\ProductContext;
use Isotope\CoreBundle\Store\Price\CalculatedPrice;
use Isotope\CoreBundle\Store\Price\PriceCalculator;
use Isotope\CoreBundle\Store\Tax\TaxCalculator;
use Isotope\CoreBundle\Test\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;
use Twig\Loader\ArrayLoader;

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
            new EventDispatcher(),
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
            new EventDispatcher(),
        );

        $context = new ProductContext($this->createStub(CoreProduct::class), $this->createStoreContext());

        $this->assertTrue($factory->create($attribute, $context, null, $foreignKey)->isEmpty());
    }

    public function testPricedOptionUsesTheSurchargeFromTheEvent(): void
    {
        $attribute = $this->createStub(OptionsAttributeInterface::class);
        $attribute
            ->method('getOptionsSource')
            ->willReturn(OptionsSource::Table)
        ;

        $model = $this->createClassWithPropertiesStub(AttributeOptionModel::class, [
            'label' => 'Gift wrapping',
            'type' => 'option',
            'isDefault' => false,
            'cssClass' => '',
            'price' => '15%',
        ]);
        $model
            ->method('getLanguageId')
            ->willReturn(42)
        ;

        $models = $this->createStub(Collection::class);
        $models
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([$model]))
        ;

        $adapter = $this->createAdapterStub(['findByAttribute']);
        $adapter
            ->method('findByAttribute')
            ->willReturn($models)
        ;

        $product = $this->createStub(CoreProduct::class);
        $basePrice = (new CalculatedPrice(40, 40))->setListPrice(100);

        $product
            ->method('getPrice')
            ->willReturn($basePrice)
        ;

        $context = new ProductContext($product, $this->createStoreContext());

        $translator = $this->createStub(TranslatorInterface::class);
        $translator
            ->method('trans')
            ->willReturnArgument(0)
        ;

        $taxCalculator = new TaxCalculator($this->createContaoFrameworkStub(), $this->createStub(RuleMatcher::class), $translator);
        $calculator = new PriceCalculator($taxCalculator);

        $twig = new Environment(new ArrayLoader([
            '@Contao/component/_isotope_option_price.html.twig' => '{{ label }} (+ {{ money.format(amount) }})',
        ]));

        $adjusted = (new CalculatedPrice(3, 3))->setListPrice(15);

        $dispatcher = new EventDispatcher();
        $dispatcher->addListener(
            OptionPriceEvent::class,
            function (OptionPriceEvent $event) use ($attribute, $context, $basePrice, $adjusted): void {
                $this->assertSame($attribute, $event->getAttribute());
                $this->assertSame('42', $event->getOption()->getValue());
                $this->assertSame($context, $event->getProductContext());
                $this->assertSame($basePrice, $event->getProductPrice());
                $this->assertTrue($event->getPriceDefinition()->isPercentage());
                $this->assertEqualsWithDelta(6.0, $event->getPrice()->getUnitPrice(), PHP_FLOAT_EPSILON);
                $this->assertEqualsWithDelta(15.0, $event->getPrice()->getListPrice(), PHP_FLOAT_EPSILON);

                $event->setPrice($adjusted);
            },
        );

        $factory = new OptionFactory(
            $this->createContaoFrameworkStub([AttributeOptionModel::class => $adapter]),
            $this->createStub(Connection::class),
            $translator,
            $calculator,
            $twig,
            $this->createStub(ScopeMatcher::class),
            $dispatcher,
        );

        $options = $factory->create($attribute, $context, null, null);
        $option = $options->first();

        $this->assertSame($adjusted, $option->getPrice());
        $this->assertSame('Gift wrapping (+ 3.00)', $option->getLabel());

        $attribute
            ->method('getOptions')
            ->willReturn($options)
        ;

        $registry = $this->createStub(AttributeRegistry::class);
        $registry
            ->method('getPricedFields')
            ->willReturn(['gift_wrapping'])
        ;
        $registry
            ->method('get')
            ->willReturn($attribute)
        ;

        $listener = new ConfigurationPriceListener($registry, $calculator);
        $event = new ProductPriceEvent($product, $context->getContext(), ['gift_wrapping' => '42'], $basePrice);
        $listener($event);

        $this->assertEqualsWithDelta(43.0, $event->getPrice()->getUnitPrice(), PHP_FLOAT_EPSILON);
        $this->assertEqualsWithDelta(115.0, $event->getPrice()->getListPrice(), PHP_FLOAT_EPSILON);
    }

    public static function emptyForeignKeyProvider(): iterable
    {
        yield 'empty' => [''];
        yield 'whitespace only' => [" \n\t\r\n"];
        yield 'commented out source' => ['# en=tl_table.label'];
        yield 'comments and blank lines' => ["# en=tl_table.label\n\n # de=tl_table.label_de\n"];
    }
}
