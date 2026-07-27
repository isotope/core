<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Store\Context;

use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\InsertTag\InsertTagParser;
use Contao\MemberModel;
use Contao\StringUtil;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use Isotope\CoreBundle\Model\AddressModel;
use Isotope\CoreBundle\Model\ConfigModel;
use Isotope\CoreBundle\Store\Address\AddressConfigFactory;
use Isotope\CoreBundle\Store\Event\ContextCreatedEvent;
use Isotope\CoreBundle\Store\Price\Currency;
use Isotope\CoreBundle\Store\Price\MoneyFormat;
use Isotope\CoreBundle\Store\Price\PriceDisplay;
use Isotope\CoreBundle\Store\Price\TaxCalculation;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Intl\Currencies;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @internal
 */
#[Autoconfigure(public: true)]
class StoreContextFactory
{
    public function __construct(
        private readonly ContaoFramework $framework,
        private readonly Connection $connection,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly AddressConfigFactory $addressConfigFactory,
        private readonly InsertTagParser $insertTagParser,
        #[Autowire(param: 'isotope.currency_formats')] private readonly array $currencyFormats,
    ) {
    }

    public function create(int $storeId, ConfigModel $config, string $locale, string $identifier, int|null $memberId): StoreContext
    {
        $this->framework->initialize();

        // TODO: load applicable taxes, etc.

        $billingAddress = new AddressModel();
        $billingAddress->country = $config->billing_country;

        $shippingAddress = new AddressModel();
        $shippingAddress->country = $config->shipping_country;

        $context = new StoreContext(
            $identifier,
            $storeId,
            (int) $config->id,
            $memberId,
            memberGroups: $this->getMemberGroups($memberId),
            locale: $locale,
            moneyFormat: fn () => $this->getMoneyFormat($config),
            priceDisplay: fn () => $this->getPriceDisplay($config, $memberId),
            taxCalculation: static fn () => TaxCalculation::from($config->taxCalculation),
            currency: fn () => $this->getCurrency($config),
            currencyFactor: (float) $config->priceCalculateFactor,
            billingAddressConfig: fn () => $this->addressConfigFactory->createFromConfig($config, 'billing'),
            shippingAddressConfig: fn () => $this->addressConfigFactory->createFromConfig($config, 'shipping'),
            ownerAddress: fn () => $this->getOwnerAddress($config),
        );

        $context->setDefaultBillingAddress($billingAddress);
        $context->setDefaultShippingAddress($shippingAddress);
        $context->setShippingIds(array_map(intval(...), array_filter(explode(',', (string) $config->shipping_ids))));
        $context->setPaymentIds(array_map(intval(...), array_filter(explode(',', (string) $config->payment_ids))));
        $context->setDocumentNumberPrefix($config->orderPrefix ? $this->insertTagParser->replaceInline($config->orderPrefix) : '');
        $context->setDocumentNumberDigits((int) $config->orderDigits);

        $this->eventDispatcher->dispatch(new ContextCreatedEvent($context, $config));

        return $context;
    }

    public function createBackend(): StoreContext
    {
        $config = new ConfigModel();
        $config->currency = '';
        $config->priceDisplay = PriceDisplay::Gross->value;
        $config->taxCalculation = TaxCalculation::Vertical->value;

        return $this->create(0, $config, $GLOBALS['TL_LANGUAGE'], '__backend__', null);
    }

    private function getCurrency(ConfigModel $config): Currency
    {
        return new Currency(
            $config->currency,
            (bool) $config->currencySymbol,
            (bool) $config->currencySpace,
            'right' === $config->currencyPosition,
            Currencies::getCashFractionDigits($config->currency),
        );
    }

    private function getMoneyFormat(ConfigModel $config): MoneyFormat
    {
        if (!isset($this->currencyFormats[$config->currencyFormat])) {
            return new MoneyFormat();
        }

        $format = $this->currencyFormats[$config->currencyFormat];

        return new MoneyFormat(
            (int) $format['decimals'],
            $format['decimal_separator'],
            $format['thousands_separator'],
            (int) $config->priceRoundPrecision,
            (float) $config->priceRoundIncrement,
        );
    }

    private function getPriceDisplay(ConfigModel $config, int|null $memberId): PriceDisplay
    {
        $priceDisplay = PriceDisplay::from($config->priceDisplay);

        if (null === $memberId) {
            return $priceDisplay;
        }

        $groups = $this->connection->fetchOne('SELECT `groups` FROM tl_member WHERE id=?', [$memberId]);

        if (
            false === $groups
            || [] === ($groups = StringUtil::deserialize($groups, true))
        ) {
            return $priceDisplay;
        }

        $value = $this->connection->fetchOne(
            "SELECT iso_priceDisplay FROM tl_member_group WHERE iso_priceDisplay!='' AND id IN (?) ORDER BY FIELD(id, ?)",
            [$groups, $groups],
            [ArrayParameterType::INTEGER, ArrayParameterType::INTEGER],
        );

        if (false === $value) {
            return $priceDisplay;
        }

        return PriceDisplay::from($value);
    }

    /**
     * Returns an address model for the shop configuration address data.
     */
    private function getOwnerAddress(ConfigModel $config): AddressModel
    {
        $address = new AddressModel();

        $address->company = $config->company;
        $address->firstname = $config->firstname;
        $address->lastname = $config->lastname;
        $address->street_1 = $config->street_1;
        $address->street_2 = $config->street_2;
        $address->street_3 = $config->street_3;
        $address->postal = $config->postal;
        $address->city = $config->city;
        $address->subdivision = $config->subdivision;
        $address->country = $config->country;
        $address->email = $config->email;
        $address->phone = $config->phone;
        $address->vat_no = $config->vat_no;

        $address->preventSaving(false);

        return $address;
    }

    private function getMemberGroups(int|null $memberId): \Closure|array
    {
        if (!$memberId) {
            return [];
        }

        return static fn () => array_map(
            intval(...),
            StringUtil::deserialize(MemberModel::findById($memberId)->groups ?? [], true),
        );
    }
}
