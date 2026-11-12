<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Test\Store\EventListener;

use Codefog\HasteBundle\Formatter;
use Contao\MemberModel;
use Isotope\CoreBundle\Model\AddressModel;
use Isotope\CoreBundle\Model\ConfigModel;
use Isotope\CoreBundle\Model\PaymentModel;
use Isotope\CoreBundle\Model\ShippingModel;
use Isotope\CoreBundle\ProductCollection\LineItem\LineItem;
use Isotope\CoreBundle\ProductCollection\Order\Order;
use Isotope\CoreBundle\Store\Address\AddressConfig;
use Isotope\CoreBundle\Store\Address\AddressFormatter;
use Isotope\CoreBundle\Store\Context\StoreContext;
use Isotope\CoreBundle\Store\Event\CollectOrderNotificationTokensEvent;
use Isotope\CoreBundle\Store\EventListener\DefaultOrderNotificationTokensListener;
use Isotope\CoreBundle\Store\Price\Currency;
use Isotope\CoreBundle\Store\Price\MoneyFormat;
use Isotope\CoreBundle\Store\Price\PriceDisplay;
use Isotope\CoreBundle\Store\Price\PriceFormatter;
use Isotope\CoreBundle\Store\Price\TaxCalculation;
use Isotope\CoreBundle\Test\TestCase;
use Symfony\Contracts\Translation\TranslatorInterface;
use Terminal42\NotificationCenterBundle\Config\NotificationConfig;
use Twig\Environment;

final class DefaultOrderNotificationTokensListenerTest extends TestCase
{
    public function testProductCountsAndAddressFormats(): void
    {
        $billingAddress = $this->createClassWithPropertiesStub(AddressModel::class, [
            'firstname' => 'Billing', 'company' => 'Company', 'street_1' => 'Billing street',
        ]);
        $shippingAddress = $this->createClassWithPropertiesStub(AddressModel::class, [
            'firstname' => 'Shipping', 'company' => 'Company', 'street_1' => 'Delivery street',
        ]);
        $context = new StoreContext(
            'test', 0, 0, null, [], 'en', new MoneyFormat(), PriceDisplay::Gross,
            TaxCalculation::Vertical, new Currency('CHF', false, true, false, 2), 1.0,
            new AddressConfig(['firstname', 'company'], [], [], 'ch'),
            new AddressConfig(['firstname', 'street_1'], [], [], 'ch'),
            $billingAddress,
        );
        $config = $this->createClassWithPropertiesStub(ConfigModel::class, ['id' => 0, 'bankName' => 'Test bank']);
        $configAdapter = $this->createAdapterStub(['findById', 'getTable']);
        $configAdapter
            ->method('findById')
            ->willReturn($config)
        ;

        $configAdapter
            ->method('getTable')
            ->willReturn('tl_iso_config')
        ;

        $order = new Order('test', $context);
        $order->setBillingAddress($billingAddress);
        $order->setShippingAddress($shippingAddress);
        $order->add(new LineItem(LineItem::TYPE_PRODUCT, 'product_1', 2));
        $order->add(new LineItem(LineItem::TYPE_PRODUCT, 'product_2', 1));

        foreach ([LineItem::TYPE_SHIPPING, LineItem::TYPE_PAYMENT, LineItem::TYPE_SUBTOTAL, LineItem::TYPE_TOTAL] as $type) {
            $order->add(new LineItem($type, $type));
        }

        $addressFormatter = $this->createStub(AddressFormatter::class);
        $formatAddress = static fn (AddressModel $address, AddressConfig $config): string => implode('|', array_intersect_key($address->row(), array_flip($config->getFields())));
        $addressFormatter
            ->method('toHtml')
            ->willReturnCallback($formatAddress)
        ;

        $addressFormatter
            ->method('toText')
            ->willReturnCallback($formatAddress)
        ;

        $listener = new DefaultOrderNotificationTokensListener(
            $this->createContaoFrameworkStub([
                ConfigModel::class => $configAdapter,
                MemberModel::class => $this->createAdapterStub([]),
                PaymentModel::class => $this->createAdapterStub([]),
                ShippingModel::class => $this->createAdapterStub([]),
            ]),
            $this->createStub(Formatter::class),
            $this->createStub(PriceFormatter::class),
            $addressFormatter,
            $this->createStub(TranslatorInterface::class),
            $this->createStub(Environment::class),
        );

        $event = new CollectOrderNotificationTokensEvent($order, NotificationConfig::fromArray([]));

        $listener($event);

        $this->assertSame(3, $event->getTokens()['order_items']);
        $this->assertSame(2, $event->getTokens()['order_products']);
        $this->assertSame('Test bank', $event->getTokens()['bank_name']);
        $this->assertSame('Billing|Company', $event->getTokens()['billing_address']);
        $this->assertSame('Billing|Company', $event->getTokens()['billing_address_text']);
        $this->assertSame('Shipping|Delivery street', $event->getTokens()['shipping_address']);
        $this->assertSame('Shipping|Delivery street', $event->getTokens()['shipping_address_text']);
    }
}
