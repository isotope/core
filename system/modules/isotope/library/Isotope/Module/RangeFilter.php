<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2016 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Module;

use Haste\Util\Url;
use Isotope\Interfaces\IsotopeFilterModule;
use Isotope\Isotope;
use Isotope\RequestCache\Filter;

/**
 * RangeFilter allows to filter a product list by value from-to.
 *
 * @property array $iso_rangeFields
 */
class RangeFilter extends AbstractProductFilter implements IsotopeFilterModule
{

    /**
     * Template
     * @var string
     */
    protected $strTemplate = 'mod_iso_rangefilter';

    /**
     * @inheritDoc
     */
    protected function getSerializedProperties()
    {
        $props = parent::getSerializedProperties();

        $props['iso_rangeFields'] = true;

        return $props;
    }

    /**
     * @inheritDoc
     */
    public function generate()
    {
        if ('FE' === TL_MODE && 0 === count($this->iso_rangeFields)) {
            return '';
        }

        return parent::generate();
    }

    /**
     * Compile the current element
     */
    protected function compile()
    {
        $fields = $this->getRangeConfig();

        if ('iso_filter_' . $this->id === \Input::post('FORM_SUBMIT')) {
            $cache = Isotope::getRequestCache();

            foreach ($fields as $config) {
                $values = \Input::post($config['attribute']);

                if (!is_array($values)) {
                    continue;
                }

                $cache->setFilterForModule(
                    $config['attribute'].'_min',
                    Filter::attribute($config['attribute'])->isGreaterOrEqualTo((int) $values['min']),
                    $this->id
                );

                $cache->setFilterForModule(
                    $config['attribute'].'_max',
                    Filter::attribute($config['attribute'])->isSmallerOrEqualTo((int) $values['max']),
                    $this->id);
            }

            $new = $cache->saveNewConfiguration();

            if ($new->id !== $cache->id) {
                \Controller::redirect(
                    \Environment::get('base') .
                    Url::addQueryString('isorc='.$new->id, ($this->jumpTo ?: null))
                );
            }
        }

        $this->Template->fields = $fields;
        $this->Template->jsonFields = json_encode($fields);
        $this->Template->formId      = 'iso_filter_' . $this->id;
        $this->Template->action      = ampersand(\Environment::get('request'));
        $this->Template->slabel      = $GLOBALS['TL_LANG']['MSC']['submitLabel'];
    }

    private function getRangeConfig()
    {
        $cache = Isotope::getRequestCache();
        $configs = [];

        foreach ($this->iso_rangeFields as $config) {
            $new = [
                'attribute' => $config['attribute'],
                'value'     => [(int) $config['min'], (int) $config['max']],
                'min'       => (int) $config['min'],
                'max'       => (int) $config['max'],
                'step'      => (int) $config['step'],
            ];

            if (null !== ($filter = $cache->getFilterForModule($config['attribute'].'_min', $this->id))) {
                $new['value'][0] = (int) $filter['value'];
            }

            if (null !== ($filter = $cache->getFilterForModule($config['attribute'].'_max', $this->id))) {
                $new['value'][1] = (int) $filter['value'];
            }

            $configs[] = $new;
        }

        return $configs;
    }
}
