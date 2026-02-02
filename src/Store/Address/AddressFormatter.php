<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Store\Address;

use Contao\CoreBundle\DataContainer\ValueFormatter;
use Isotope\CoreBundle\Model\Address;
use Isotope\CoreBundle\Twig\TemplateContext;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Twig\Environment;

#[Autoconfigure(public: true)] // TODO: remove public:true once Address model does not longer need it
class AddressFormatter
{
    public function __construct(
        private readonly Environment $twig,
        #[Autowire(service: 'contao.data_container.value_formatter')] private readonly ValueFormatter $formatter,
    ) {
    }

    public function get(Address $address, AddressConfig $config): TemplateContext
    {
        $data = array_intersect_key($address->row(), array_flip($config->getFields()));

        foreach ($data as $field => $value) {
            $data[$field] = $this->formatter->getLabel('tl_iso_address', $field, $value, null);
        }

        $data['data'] = $address->row();

        // Use the default country if there is no country to format the address
        if (empty($data['data']['country'])) {
            $data['data']['country'] = $config->getDefaultCountryCode();
        }

        return new TemplateContext($this->twig, '@Contao/isotope/address.html.twig', $data);
    }

    public function getText(Address $address, AddressConfig $config): string
    {
        $output = (string) $this->get($address, $config);

        // Remove newlines in template and keep HTML <br> for newlines
        $output = str_replace(array("\n", '<br>'), array('', "\n"), $output);

        return trim(strip_tags($output));
    }
}
