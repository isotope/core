<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Test\Store\Address;

use Contao\CoreBundle\DataContainer\ValueFormatter;
use Contao\CoreBundle\Intl\Countries;
use Isotope\CoreBundle\Model\AddressModel;
use Isotope\CoreBundle\Store\Address\AddressConfig;
use Isotope\CoreBundle\Store\Address\AddressFormatter;
use Isotope\CoreBundle\Store\Address\AddressValidator;
use Isotope\CoreBundle\Test\TestCase;
use Isotope\CoreBundle\Twig\TemplateMarkupFactory;
use PHPUnit\Framework\Attributes\DataProvider;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

final class AddressFormatterTest extends TestCase
{
    #[DataProvider('addressProvider')]
    public function testAddress(array $data, string $expectedHTML, string $expectedText): void
    {
        $loader = new FilesystemLoader();
        $loader->addPath(\dirname(__DIR__, 3).'/contao/templates', 'Contao');

        $markupFactory = new TemplateMarkupFactory(new Environment($loader));

        $formatter = $this->createStub(ValueFormatter::class);
        $formatter
            ->method('getLabel')
            ->willReturnArgument(2)
        ;

        $addressFormatter = new AddressFormatter($formatter, $markupFactory, $this->createStub(AddressValidator::class), $this->createStub(Countries::class));

        $address = $this->createClassWithPropertiesStub(AddressModel::class, $data);

        $config = new AddressConfig(
            ['gender', 'company', 'salutation', 'firstname', 'lastname', 'street_1', 'street_2', 'street_3', 'postal', 'city', 'subdivision', 'country', 'email', 'phone'],
            [],
            [$data['country']],
            $data['country'],
        );

        $this->assertSame($expectedHTML, $addressFormatter->toHtml($address, $config));
        $this->assertSame($expectedText, $addressFormatter->toText($address, $config));
    }

    public static function addressProvider(): iterable
    {
        yield 'HTML entities in address fields' => [
            [
                'company' => 'A & B <Research>',
                'firstname' => 'Zoë',
                'lastname' => "O'Brien",
                'street_1' => 'R&D Road <1>',
                'postal' => '12345',
                'city' => 'Paris',
                'country' => 'FR',
            ],
            <<<'HTML'
                A &amp; B &lt;Research&gt;<br>
                Zoë O&#039;Brien<br>
                R&amp;D Road &lt;1&gt;<br>
                12345 Paris<br>
                FR
                HTML,
            <<<'TEXT'
                A & B <Research>
                Zoë O'Brien
                R&D Road <1>
                12345 Paris
                FR
                TEXT,
        ];

        yield [
            [
                'company' => 'Musterfirma',
                'salutation' => 'Herr',
                'firstname' => 'Max',
                'lastname' => 'Muster',
                'street_1' => 'Musterstrasse 1',
                'street_2' => 'Postfach',
                'postal' => '10000',
                'city' => 'Berlin',
                'subdivision' => 'DE-BE',
                'country' => 'DE',
            ],
            <<<'HTML'
                Musterfirma<br>
                Herr Max Muster<br>
                Musterstrasse 1<br>
                Postfach<br>
                10000 Berlin<br>
                DE
                HTML,
            <<<'TEXT'
                Musterfirma
                Herr Max Muster
                Musterstrasse 1
                Postfach
                10000 Berlin
                DE
                TEXT,
        ];

        yield [
            [
                'company' => 'Azienda modello',
                'salutation' => 'Signore',
                'firstname' => 'Max',
                'lastname' => 'Muster',
                'street_1' => 'Via Modello 42',
                'postal' => '20011',
                'city' => 'Milano',
                'subdivision' => 'IT-MI',
                'country' => 'IT',
            ],
            <<<'HTML'
                Azienda modello<br>
                Signore Max Muster<br>
                Via Modello 42<br>
                20011 Milano (IT-MI)<br>
                IT
                HTML,
            <<<'TEXT'
                Azienda modello
                Signore Max Muster
                Via Modello 42
                20011 Milano (IT-MI)
                IT
                TEXT,
        ];

        yield [
            [
                'company' => 'Example company',
                'salutation' => 'Mr.',
                'firstname' => 'John',
                'lastname' => 'Doe',
                'street_1' => '9169 Chester Road',
                'postal' => 'SE1 3AE',
                'city' => 'East Central London',
                'country' => 'GB',
            ],
            <<<'HTML'
                Example company<br>
                Mr. John Doe<br>
                9169 Chester Road<br>
                East Central London<br>
                SE1 3AE<br>
                GB
                HTML,
            <<<'TEXT'
                Example company
                Mr. John Doe
                9169 Chester Road
                East Central London
                SE1 3AE
                GB
                TEXT,
        ];

        yield [
            [
                'company' => 'Example company',
                'salutation' => 'Mr.',
                'firstname' => 'John',
                'lastname' => 'Doe',
                'street_1' => '417 Holly Drive',
                'postal' => '11203',
                'city' => 'Brooklyn',
                'subdivision' => 'US-NY',
                'country' => 'US',
            ],
            <<<'HTML'
                Example company<br>
                Mr. John Doe<br>
                417 Holly Drive<br>
                Brooklyn, US-NY 11203<br>
                US
                HTML,
            <<<'TEXT'
                Example company
                Mr. John Doe
                417 Holly Drive
                Brooklyn, US-NY 11203
                US
                TEXT,
        ];

        yield [
            [
                'company' => 'Example company',
                'salutation' => 'Mr.',
                'firstname' => 'John',
                'lastname' => 'Doe',
                'street_1' => '12 Al Tahrir Street, Apartment 4A',
                'postal' => '11511',
                'city' => 'Cairo',
                'subdivision' => 'EG-BA',
                'country' => 'EG',
            ],
            <<<'HTML'
                Example company<br>
                Mr. John Doe<br>
                12 Al Tahrir Street, Apartment 4A<br>
                11511 Cairo<br>
                EG-BA<br>
                EG
                HTML,
            <<<'TEXT'
                Example company
                Mr. John Doe
                12 Al Tahrir Street, Apartment 4A
                11511 Cairo
                EG-BA
                EG
                TEXT,
        ];

        yield [
            [
                'company' => 'DSA CO. LTD',
                'firstname' => 'Somchai',
                'lastname' => 'Subhani',
                'street_1' => 'Human Resource Department',
                'street_2' => '431 Nayok Road, Prachathipat',
                'postal' => '13241',
                'city' => 'Klongtoey District, Bangkok',
                'country' => 'TH',
            ],
            <<<'HTML'
                DSA CO. LTD<br>
                Somchai Subhani<br>
                Human Resource Department<br>
                431 Nayok Road, Prachathipat<br>
                Klongtoey District, Bangkok<br>
                13241<br>
                TH
                HTML,
            <<<'TEXT'
                DSA CO. LTD
                Somchai Subhani
                Human Resource Department
                431 Nayok Road, Prachathipat
                Klongtoey District, Bangkok
                13241
                TH
                TEXT,
        ];
    }
}
