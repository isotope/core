<?php

namespace Isotope\CoreBundle\Test\Store\Address;

use Contao\CoreBundle\DataContainer\ValueFormatter;
use Contao\System;
use Isotope\CoreBundle\Model\AddressModel;
use Isotope\CoreBundle\Store\Address\AddressConfig;
use Isotope\CoreBundle\Store\Address\AddressFormatter;
use Isotope\CoreBundle\Store\Address\AddressValidator;
use Isotope\CoreBundle\Twig\TemplateMarkupFactory;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Twig\Environment;

class AddressFormatterTest extends KernelTestCase
{
    protected function tearDown(): void
    {
        parent::tearDown();

        \restore_exception_handler();
    }

    #[DataProvider('addressProvider')]
    public function testAddress(array $data, string $expectedHTML, string $expectedText): void
    {
        self::bootKernel();

        System::setContainer($this->getContainer());

        $twig = $this->getContainer()->get(Environment::class);

        $formatter = $this->createStub(ValueFormatter::class);
        $formatter
            ->method('getLabel')
            ->willReturnArgument(2)
        ;

        $addressFormatter = new AddressFormatter($formatter, new TemplateMarkupFactory($twig), $this->createStub(AddressValidator::class));

        $address = new AddressModel();
        (new \ReflectionProperty(AddressModel::class, 'arrData'))->setValue($address, $data);

        $config = new AddressConfig(
            ['gender', 'company', 'salutation', 'firstname', 'lastname', 'street_1', 'street_2', 'street_3', 'postal', 'city', 'subdivision', 'country', 'email', 'phone'],
            [],
            [$data['country']],
            $data['country'],
        );

        $this->assertSame($expectedHTML, (string) $addressFormatter->toHtml($address, $config));
        $this->assertSame($expectedText, $addressFormatter->toText($address, $config));
    }

    public static function addressProvider(): iterable
    {
        yield [
            [
                'company' => 'Musterfirma',
                'salutation' => 'Herr',
                'firstname' => 'Max',
                'lastname' => 'Muster',
                'street_1' => 'Musterstrasse 1',
                //'street_2' => 'Postfach',
                'postal' => '10000',
                'city' => 'Berlin',
                'subdivision' => 'DE-BE',
                'country' => 'DE',
            ],
            'Musterfirma<br>Herr Max Muster<br>Musterstrasse 1<br>10000 Berlin<br>DE',
            <<<'TEXT'
Musterfirma
Herr Max Muster
Musterstrasse 1
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
                //'street_2' => 'Postfach',
                'postal' => '20011',
                'city' => 'Milano',
                'subdivision' => 'IT-MI',
                'country' => 'IT',
            ],
            'Azienda modello<br>Signore Max Muster<br>Via Modello 42<br>20011 Milano (IT-MI)<br>IT',
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
                //'street_2' => 'Postfach',
                'postal' => 'SE1 3AE',
                'city' => 'East Central London',
                //'subdivision' => 'GB-LND',
                'country' => 'GB',
            ],
            'Example company<br>Mr. John Doe<br>9169 Chester Road<br>East Central London<br>SE1 3AE<br>GB',
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
                //'street_2' => 'Postfach',
                'postal' => '11203',
                'city' => 'Brooklyn',
                'subdivision' => 'US-NY',
                'country' => 'US',
            ],
            'Example company<br>Mr. John Doe<br>417 Holly Drive<br>Brooklyn, US-NY 11203<br>US',
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
            'Example company<br>Mr. John Doe<br>12 Al Tahrir Street, Apartment 4A<br>11511 Cairo<br>EG-BA<br>EG',
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
            'DSA CO. LTD<br>Somchai Subhani<br>Human Resource Department<br>431 Nayok Road, Prachathipat<br>Klongtoey District, Bangkok<br>13241<br>TH',
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
