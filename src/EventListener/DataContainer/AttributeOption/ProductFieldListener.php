<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\EventListener\DataContainer\AttributeOption;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\DataContainer;
use Symfony\Component\HttpFoundation\RequestStack;
use Terminal42\DcawizardBundle\UrlConfig;

class ProductFieldListener
{
    public function __construct(private readonly RequestStack $requestStack)
    {
    }

    #[AsCallback('tl_iso_attribute_option', 'config.onload')]
    public function filterProductOptions()
    {
        $request = $this->requestStack->getCurrentRequest();

        if (
            !$request
            || 'iso_products' !== $request->query->get('do')
            || !$request->query->has('picker')
            || !($field = UrlConfig::urlDecode($request->query->getString('picker'))->getField())
        ) {
            return;
        }

        $GLOBALS['TL_DCA']['tl_iso_attribute_option']['list']['sorting']['filter'] = [['field_name=?', $field]];
        $GLOBALS['TL_DCA']['tl_iso_attribute_option']['list']['sorting']['headerFields'] = ['name', 'type', 'alias', 'published'];
    }

    /**
     * Store the attribute field name for product options.
     */
    #[AsCallback('tl_iso_attribute_option', 'config.onbeforesubmit')]
    public function storeFieldName(array $values, DataContainer $dc): array
    {
        $request = $this->requestStack->getCurrentRequest();

        if (!$request || !$request->query->has('picker')) {
            return $values;
        }

        if ('iso_products' === $request->query->get('do')) {
            $config = UrlConfig::urlDecode($request->query->getString('picker'));

            $values['field_name'] = $config->getField();
        }

        return $values;
    }
}
