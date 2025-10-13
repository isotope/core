<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Store\Context;

use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\Intl\Countries;
use Contao\StringUtil;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use Isotope\CoreBundle\Store\Price\Currency;
use Isotope\CoreBundle\Store\Price\MoneyFormat;
use Isotope\CoreBundle\Store\Price\PriceDisplay;
use Isotope\Model\Address;
use Isotope\Model\Config;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;

/**
 * @internal
 */
#[Autoconfigure(public: true)]
class StoreContextFactory
{
    public function __construct(
        private readonly ContaoFramework $framework,
        private readonly Connection $connection,
        private readonly Countries $countries,
    ) {
    }

    public function create(int $storeId, Config $config, string $locale, string $identifier, int|null $memberId): StoreContext
    {
        $this->framework->initialize();

        // TODO: load applicable taxes, etc.

        // TODO: add event to modify the store config on init

        return new StoreContext(
            $identifier,
            $storeId,
            (int) $config->id,
            $memberId,
            $locale,
            fn () => $this->getMoneyFormat($config),
            fn () => $this->getPriceDisplay($config, $memberId),
            fn () => $this->getCurrency($config),
            (float) $config->priceCalculateFactor,
            fn () => $this->getOwnerAddress($config),
            fn () => $this->getCountries($config->billing_countries),
            fn () => $this->getCountries($config->shipping_countries),
        );
    }

    private function getCurrency(Config $config): Currency
    {
        $isoCode = $config->currency;
        $currencies = include(__DIR__.'/../../../contao/config/currencies.php');

        return new Currency(
            $isoCode,
            $config->currencySymbol ?: null,
            (bool) $config->currencySpace,
            $config->currencyPosition,
            $currencies[$isoCode]['number'] ?? null,
            $currencies[$isoCode]['units'] ?? 2,
        );
    }

    private function getMoneyFormat(Config $config): MoneyFormat
    {
        if (!isset($GLOBALS['ISO_NUM'][$config->currencyFormat])) {
            throw new \RuntimeException(sprintf('Unknown currency format "%s" in shop config ID %s', $config->currencyFormat, $config->id));
        }

        $format = $GLOBALS['ISO_NUM'][$config->currencyFormat];

        return new MoneyFormat(
            (int) $format[0],
            $format[1],
            $format[2],
            (int) $config->priceRoundPrecision,
            (float) $config->priceRoundIncrement,
        );
    }

    private function getPriceDisplay(Config $config, int|null $memberId): PriceDisplay
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
    private function getOwnerAddress(Config $config): Address
    {
        $address = new Address();

        $address->company     = $config->company;
        $address->firstname   = $config->firstname;
        $address->lastname    = $config->lastname;
        $address->street_1    = $config->street_1;
        $address->street_2    = $config->street_2;
        $address->street_3    = $config->street_3;
        $address->postal      = $config->postal;
        $address->city        = $config->city;
        $address->subdivision = $config->subdivision;
        $address->country     = $config->country;
        $address->email       = $config->email;
        $address->phone       = $config->phone;
        $address->vat_no      = $config->vat_no;

        $address->preventSaving(false);

        return $address;
    }

    private function getCountries(string|array $value): array
    {
        $countries = StringUtil::deserialize($value);

        if (empty($countries) || !\is_array($countries)) {
            $countries = $this->countries->getCountries();

            // Convert country keys to lowercase
            // TODO: should switch to uppercase countries
            return array_combine(array_map('strtolower', array_keys($countries)), $countries);
        }

        return $countries;
    }
}
