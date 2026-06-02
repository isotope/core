<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Store\Address;

use Contao\CoreBundle\DataContainer\ValueFormatter;
use Isotope\CoreBundle\Model\AddressModel;
use Isotope\CoreBundle\Twig\TemplateMarkup;
use Isotope\CoreBundle\Twig\TemplateMarkupFactory;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class AddressFormatter
{
    public function __construct(
        #[Autowire(service: 'contao.data_container.value_formatter')] private readonly ValueFormatter $formatter,
        private readonly TemplateMarkupFactory $markupFactory,
        private readonly AddressValidator $addressValidator,
    ) {
    }

    /**
     * Format an address for an HTML template.
     */
    public function toHtml(AddressModel $address, AddressConfig $config): TemplateMarkup
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
        if (empty($data['data']['country'])) {
            $data['data']['country'] = $config->getDefaultCountryCode();
        }

        return $this->markupFactory->create('@Contao/isotope/address.html.twig', $data);
    }

    /**
     * Format an address as HTML string, so that applying `strip_tags` results in the correct text representation.
     */
    public function toHtmlString(AddressModel $address, AddressConfig $config): string
    {
        $output = (string) $this->toHtml($address, $config);

        $output = str_replace(array("\n", '<br>'), array('', "<br>\n"), $output);

        return trim($output);
    }

    /**
     * Format an address as plain text.
     */
    public function toText(AddressModel $address, AddressConfig $config): string
    {
        return strip_tags($this->toHtmlString($address, $config));
    }
}
