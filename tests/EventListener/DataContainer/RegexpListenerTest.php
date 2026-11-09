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
        $previousLanguage = $GLOBALS['TL_LANG'] ?? [];
        $GLOBALS['TL_LANG']['ERR']['optionvalue'] = 'Use ASCII letters, digits and underscores only.';
        $widget = $this->createMock(Widget::class);
        $widget
            ->expects($valid ? $this->never() : $this->once())
            ->method('addError')
            ->with('Use ASCII letters, digits and underscores only.')
        ;

        try {
            $this->assertTrue((new RegexpListener())('optionvalue', $value, $widget));
        } finally {
            $GLOBALS['TL_LANG'] = $previousLanguage;
        }
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
}
