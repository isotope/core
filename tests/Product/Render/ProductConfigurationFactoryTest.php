<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Test\Product\Render;

use Contao\DcaLoader;
use Contao\SelectMenu;
use Contao\System;
use Contao\Widget;
use Isotope\CoreBundle\Attribute\AttributeRegistry;
use Isotope\CoreBundle\Attribute\OptionsAttributeInterface;
use Isotope\CoreBundle\Attribute\VariantAttributeInterface;
use Isotope\CoreBundle\Model\ProductTypeModel;
use Isotope\CoreBundle\Product\CoreProduct;
use Isotope\CoreBundle\Product\ProductCollection;
use Isotope\CoreBundle\Product\Render\ProductConfigurationFactory;
use Isotope\CoreBundle\Product\Render\ProductRendererInterface;
use Isotope\CoreBundle\Product\Render\ViewInterface;
use Isotope\CoreBundle\Store\Context\ProductContext;
use Isotope\CoreBundle\Test\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\Config\FileLocatorInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request;

final class ProductConfigurationFactoryTest extends TestCase
{
    #[DataProvider('variantValueProvider')]
    public function testVariantOptionIsIncludedInConfiguration(int|string $value, bool $priced = true): void
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

        $GLOBALS['TL_DCA']['tl_iso_product']['fields']['size'] = [];

        $type = $this->createClassWithPropertiesStub(ProductTypeModel::class);
        $type
            ->method('getAttributes')
            ->willReturn(['wrapping', 'size'])
        ;

        $renderer = $this->createStub(ProductRendererInterface::class);
        $variant = new CoreProduct(['id' => 2, 'size' => $value], $type, $renderer);

        $product = new CoreProduct(['id' => 1], $type, $renderer);
        $product->setVariants(new ProductCollection([$variant]));

        $GLOBALS['TL_DCA']['tl_iso_product']['fields']['wrapping'] = [
            'inputType' => 'select',
            'options_callback' => function (ProductContext $context) use ($variant): array {
                $this->assertSame($variant, $context->getProduct());

                return [42 => 'Gift wrapping'];
            },
        ];

        $wrapping = $this->createStub(OptionsAttributeInterface::class);
        $wrapping
            ->method('isCustomerDefined')
            ->willReturn(true)
        ;
        $wrapping
            ->method('getFrontendWidget')
            ->willReturn(SelectMenu::class)
        ;

        $attribute = $this->createStub(VariantAttributeInterface::class);
        $attribute
            ->method('isVariantOption')
            ->willReturn(true)
        ;
        $attribute
            ->method('getFrontendWidget')
            ->willReturn(Widget::class)
        ;

        $registry = $this->createStub(AttributeRegistry::class);
        $registry
            ->method('getPricedFields')
            ->willReturn($priced ? ['wrapping'] : [])
        ;
        $registry
            ->method('get')
            ->willReturnMap([['size', $attribute], ['wrapping', $wrapping]])
        ;

        $factory = new ProductConfigurationFactory($this->createContaoFrameworkStub(), $registry, new EventDispatcher());

        try {
            $configuration = $factory->create(new ProductContext($product, $this->createStoreContext()), $this->createStub(ViewInterface::class), Request::create('/product'));

            $this->assertSame(['size' => $value], $configuration->getData());
            $this->assertSame($variant, $configuration->getProduct());
            $this->assertArrayHasKey('wrapping', $configuration->getWidgets());
        } finally {
            DcaLoader::reset();

            $GLOBALS['TL_DCA'] = $previousDca;
        }
    }

    public static function variantValueProvider(): iterable
    {
        yield 'string zero' => ['0'];
        yield 'integer zero' => [0];
        yield 'nonzero value' => ['small'];
        yield 'unpriced option callback uses the selected variant' => ['small', false];
    }
}
