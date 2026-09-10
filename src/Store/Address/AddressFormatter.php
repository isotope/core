<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Store\Address;

use Contao\CoreBundle\DataContainer\ValueFormatter;
use Contao\CoreBundle\Intl\Countries;
use Isotope\CoreBundle\Model\AddressModel;
use Isotope\CoreBundle\Twig\TemplateMarkupFactory;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\Service\ResetInterface;

class AddressFormatter implements ResetInterface
{
    private array|null $countryLabels = null;

    public function __construct(
        #[Autowire(service: 'contao.data_container.value_formatter')] private readonly ValueFormatter $formatter,
        private readonly TemplateMarkupFactory $markupFactory,
        private readonly AddressValidator $addressValidator,
        private readonly Countries $countries,
    ) {
    }

    public function getData(AddressModel $address, AddressConfig $config): array
    {
        $data = array_intersect_key($address->row(), array_flip($config->getFields()));

        foreach ($data as $field => $value) {
            $data[$field] = $this->formatter->getLabel('tl_iso_address', $field, $value, null);
        }

        $data['data'] = $address->row();

        $validation = $this->addressValidator->validate($address, $config);
        $data['isValid'] = !$validation->count();
        $data['error'] = (string) $validation;

        // Use the default country if there is no country to format the address
        if (empty($address->country)) {
            $data['data']['country'] = $config->getDefaultCountryCode();
        }

        $data['country'] = $this->getCountryLabel($data['data']['country']);

        return $data;
    }

    /**
     * Format an address as HTML string, so that applying `strip_tags` results in the correct text representation.
     */
    public function toHtml(AddressModel $address, AddressConfig $config): string
    {
        $output = (string) $this->markupFactory->create(
            '@Contao/component/_isotope_address.html.twig',
            $this->getData($address, $config),
        );

        $output = str_replace(["\n", '<br>'], ['', "<br>\n"], $output);

        return trim($output);
    }

    /**
     * Format an address as plain text.
     */
    public function toText(AddressModel $address, AddressConfig $config): string
    {
        return strip_tags($this->toHtml($address, $config));
    }

    public function reset(): void
    {
        $this->countryLabels = null;
    }

    private function getCountryLabel(string $code): string
    {
        $this->countryLabels ??= $this->countries->getCountries();

        return $this->countryLabels[$code] ?? $code;
    }
}
