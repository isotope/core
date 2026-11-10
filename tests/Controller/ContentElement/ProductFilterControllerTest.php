<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Test\Controller\ContentElement;

use Codefog\HasteBundle\Formatter;
use Contao\ContentModel;
use Contao\CoreBundle\Twig\FragmentTemplate;
use Contao\System;
use Doctrine\DBAL\DriverManager;
use Isotope\CoreBundle\Attribute\AttributeRegistry;
use Isotope\CoreBundle\Attribute\Filter\FilterAttribute;
use Isotope\CoreBundle\Attribute\Filter\FilterOptionsProvider;
use Isotope\CoreBundle\Attribute\Option\Option;
use Isotope\CoreBundle\Attribute\Option\OptionCollection;
use Isotope\CoreBundle\Controller\ContentElement\ProductFilterController;
use Isotope\CoreBundle\Product\Finder\ProductFinder;
use Isotope\CoreBundle\Product\Finder\ProductFinderFactory;
use Isotope\CoreBundle\Product\Query\Filter\EqualFilter;
use Isotope\CoreBundle\Product\Query\ProductQueryFactory;
use Isotope\CoreBundle\Test\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;

final class ProductFilterControllerTest extends TestCase
{
    #[DataProvider('hideSingleProvider')]
    public function testWidgetPreparation(bool $hideSingle): void
    {
        $container = $this->getContainerWithContaoConfiguration();
        System::setContainer($container);
        $previousDca = $GLOBALS['TL_DCA'] ?? [];
        $GLOBALS['TL_DCA']['tl_iso_product']['fields']['color'] = [
            'label' => ['Color', 'Choose a color'],
            'inputType' => 'select',
            'options_callback' => static fn () => throw new \LogicException('Options should only be resolved once.'),
            'eval' => ['includeBlankOption' => true, 'blankOptionLabel' => 'Any color'],
        ];
        $GLOBALS['TL_DCA']['tl_iso_product']['fields']['size'] = ['label' => ['Size'], 'inputType' => 'select'];
        $finder = $this->createStub(ProductFinder::class);
        $helper = $this->createMock(FilterOptionsProvider::class);
        $helper
            ->expects($this->once())
            ->method('getAttributes')
            ->with(['color', 'size'], $finder)
            ->willReturn([
                new FilterAttribute('color', 'Color', new OptionCollection([
                    new Option('', 'All colors'),
                    new Option('0', 'Zero'),
                    new Option('blue', 'Blue'),
                ])),
                new FilterAttribute('size', 'Size', new OptionCollection([new Option('large', 'Large')])),
            ])
        ;
        $controller = new ProductFilterController(
            $this->createStub(AttributeRegistry::class),
            $this->createStub(Formatter::class),
            $this->createStub(ProductFinderFactory::class),
            $this->createStub(ProductQueryFactory::class),
            $helper,
            $this->createStub(TranslatorInterface::class),
        );

        try {
            $widgets = (new \ReflectionMethod($controller, 'getFilterWidgets'))->invoke($controller, ['color', 'size'], ['color' => '0'], $finder, $hideSingle);

            $this->assertCount($hideSingle ? 1 : 2, $widgets);
            $this->assertSame('color', $widgets[0]['name']);
            $this->assertSame('Color', $widgets[0]['label']);
            $this->assertSame('Choose a color', $widgets[0]['description']);
            $this->assertSame('All colors', $widgets[0]['blankOptionLabel']);
            $this->assertSame(
                [
                    ['value' => '0', 'label' => 'Zero', 'cssClass' => '', 'default' => true],
                    ['value' => 'blue', 'label' => 'Blue', 'cssClass' => '', 'default' => false],
                ],
                $widgets[0]['options'],
            );
        } finally {
            $GLOBALS['TL_DCA'] = $previousDca;
        }
    }

    public function testOptionsUseConfiguredQueryAndCategoryScope(): void
    {
        $connection = DriverManager::getConnection(['driver' => 'pdo_sqlite', 'memory' => true]);
        $connection->executeStatement('CREATE TABLE tl_iso_product (id INTEGER, color TEXT)');
        $connection->insert('tl_iso_product', ['id' => 1, 'color' => 'red']);
        $connection->insert('tl_iso_product', ['id' => 2, 'color' => 'blue']);
        $baseQuery = $connection->createQueryBuilder()->select('id')->from('tl_iso_product');
        $finder = $this->createMock(ProductFinderFactory::class);
        $finder
            ->expects($this->once())
            ->method('create')
            ->with([10], 'de', [new EqualFilter('color', 'blue')])
            ->willReturnCallback(
                static function (array $categories, string $locale, array $filters) use ($connection, $baseQuery): ProductFinder {
                    foreach ($filters as $filter) {
                        $filter->apply($baseQuery);
                    }

                    return new ProductFinder($connection, $baseQuery);
                },
            )
        ;
        $helper = $this->createMock(FilterOptionsProvider::class);
        $helper
            ->expects($this->once())
            ->method('getAttributes')
            ->willReturnCallback(
                function (array $fields, ProductFinder $finder): array {
                    $this->assertSame([2], $finder->getQueryBuilder()->fetchFirstColumn());

                    return [];
                },
            )
        ;
        $attributes = $this->createStub(AttributeRegistry::class);
        $attributes
            ->method('getNames')
            ->willReturn(['color'])
        ;
        $factory = new ProductQueryFactory([':' => EqualFilter::class], $attributes, new RequestStack());
        $controller = $this->getMockBuilder(ProductFilterController::class)
            ->setConstructorArgs([$attributes, $this->createStub(Formatter::class), $finder, $factory, $helper, $this->createStub(TranslatorInterface::class)])
            ->onlyMethods(['getCategories', 'getProductQuery'])
            ->getMock()
        ;

        $controller
            ->expects($this->once())
            ->method('getCategories')
            ->willReturn([10])
        ;

        // Current selections must not narrow the options available for other selections.
        $controller
            ->method('getProductQuery')
            ->willReturn($factory->create()->setFilters('', [new EqualFilter('color', 'red')]))
        ;
        $model = $this->createStub(ContentModel::class);
        $model
            ->method('__get')
            ->willReturnMap([
                ['iso_filterFields', 'color'],
                ['iso_filterQuery', 'color:blue'],
                ['iso_sortingFields', ''],
            ])
        ;
        $request = Request::create('/shop');
        $request->setLocale('de');
        $template = new FragmentTemplate('content_element/iso_productfilter', static fn () => new Response());

        try {
            (new \ReflectionMethod($controller, 'getResponse'))->invoke($controller, $template, $model, $request);
        } finally {
            $connection->close();
        }
    }

    public static function hideSingleProvider(): iterable
    {
        yield [false];
        yield [true];
    }
}
