<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Test\EventListener\DataContainer;

use Contao\Widget;
use Isotope\CoreBundle\EventListener\DataContainer\RegexpListener;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class RegexpListenerTest extends TestCase
{
    #[DataProvider('optionValueProvider')]
    public function testOptionValueValidation(string $value, bool $valid): void
    {
        $this->assertRegexpValidation('optionvalue', $value, $valid, 'Use ASCII letters, digits and underscores only.');
    }

    public static function optionValueProvider(): iterable
    {
        yield ['red_blue', true];
        yield ['0', true];
        yield ['ABC_123', true];
        yield ['', false];
        yield ['red,blue', false];
        yield ['red blue', false];
        yield ['red|blue', false];
        yield ['red.blue', false];
        yield ['red-blue', false];
        yield ['ä', false];
        yield ["red\n", false];
    }

    #[DataProvider('discountProvider')]
    public function testDiscountValidation(string $value, bool $valid): void
    {
        $this->assertRegexpValidation('discount', $value, $valid, 'Please enter whole numbers or decimals signed with + or - and optionally with a percentage.');
    }

    public static function discountProvider(): iterable
    {
        yield ['+10%', true];
        yield ['-100%', true];
        yield ['-100.01%', false];
        yield ['+150%', true];
        yield ['-150', true];
        yield ['10%', false];
        yield ['+12,50', false];
        yield ['+10%invalid', false];
        yield ["+10%\n", false];
    }

    private function assertRegexpValidation(string $regexp, string $value, bool $valid, string $message): void
    {
        $previousLanguage = $GLOBALS['TL_LANG'] ?? [];
        $GLOBALS['TL_LANG']['ERR'][$regexp] = $message;
        $widget = $this->createMock(Widget::class);
        $widget
            ->expects($valid ? $this->never() : $this->once())
            ->method('addError')
            ->with($message)
        ;

        try {
            $this->assertTrue((new RegexpListener())($regexp, $value, $widget));
        } finally {
            $GLOBALS['TL_LANG'] = $previousLanguage;
        }
    }
}
