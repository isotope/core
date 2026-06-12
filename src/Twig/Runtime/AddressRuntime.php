<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Twig\Runtime;

use Isotope\CoreBundle\Model\AddressModel;
use Isotope\CoreBundle\Store\Address\AddressConfig;
use Isotope\CoreBundle\Store\Address\AddressFormatter;
use Isotope\CoreBundle\Twig\TemplateMarkup;
use Isotope\CoreBundle\Twig\TemplateMarkupFactory;
use Twig\Extension\RuntimeExtensionInterface;

/**
 * @internal
 */
class AddressRuntime implements RuntimeExtensionInterface
{
    public function __construct(
        private readonly AddressFormatter $addressFormatter,
        private readonly TemplateMarkupFactory $markupFactory,
    ) {
    }

    public function get(AddressModel $address, AddressConfig $config): TemplateMarkup|null
    {
        return $this->markupFactory->create(
            '@Contao/component/_isotope_address.html.twig',
            $this->addressFormatter->getData($address, $config),
        );
    }
}
