<?php

/*
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009 - 2019 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Module;

use Isotope\CompatibilityHelper;
use Contao\Controller;
use Contao\Environment;
use Haste\Input\Input;
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
        if (CompatibilityHelper::isBackend()) {
            return $this->generateWildcard();
        }

        if (CompatibilityHelper::isFrontend() && 0 === \count($this->iso_rangeFields)) {
            return '';
        }

        // Hide product list in reader mode if the respective setting is enabled
        if ($this->iso_hide_list && Input::getAutoItem('product', false, true) != '') {
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

        if ('iso_filter_' . $this->id === Input::post('FORM_SUBMIT')) {
            $cache = Isotope::getRequestCache();

            foreach ($fields as $config) {
                $values = Input::post($config['id']);

                switch ($config['mode']) {
                    case 'min':
                        if (null === $values) {
                            $cache->removeFilterForModule($config['id'], $this->id);
                        } else {
                            $cache->setFilterForModule(
                                $config['id'],
                                Filter::attribute($config['attribute'])->isGreaterOrEqualTo((int) $values),
                                $this->id
                            );
                        }
                        break;

                    case 'max':
                        if (null === $values) {
                            $cache->removeFilterForModule($config['id'], $this->id);
                        } else {
                            $cache->setFilterForModule(
                                $config['id'],
                                Filter::attribute($config['attribute'])->isSmallerOrEqualTo((int) $values),
                                $this->id
                            );
                        }
                        break;

                    case 'fields':
                        if (null === $values) {
                            $cache->removeFilterForModule($config['id'].'_min', $this->id);
                            $cache->removeFilterForModule($config['id'].'_max', $this->id);
                        } else {
                            $cache->setFilterForModule(
                                $config['id'].'_min',
                                Filter::attribute($config['attribute'])->isSmallerOrEqualTo((int) $values),
                                $this->id
                            );
                            $cache->setFilterForModule(
                                $config['id'].'_max',
                                Filter::attribute($config['attribute_max'])->isGreaterOrEqualTo((int) $values),
                                $this->id
                            );
                        }
                        break;

                    case 'range':
                    default:
                        if (null === $values) {
                            $cache->removeFilterForModule($config['attribute'].'_min', $this->id);
                            $cache->removeFilterForModule($config['attribute'].'_max', $this->id);
                        } else {
                            $cache->setFilterForModule(
                                $config['attribute'].'_min',
                                Filter::attribute($config['attribute'])->isGreaterOrEqualTo((int) $values['min']),
                                $this->id
                            );
                            $cache->setFilterForModule(
                                $config['attribute'].'_max',
                                Filter::attribute($config['attribute'])->isSmallerOrEqualTo((int) $values['max']),
                                $this->id
                            );
                        }
                        break;
                }
            }

            $new = $cache->saveNewConfiguration();

            if ($new->id !== $cache->id) {
                Controller::redirect(
                    Environment::get('base') .
                    Url::addQueryString(
                        'isorc='.$new->id,
                        Url::removeQueryStringCallback(
                            static function ($value, $key) {
                                return !str_starts_with($key, 'page_iso');
                            },
                            ($this->jumpTo ?: null)
                        )
                    )
                );
            }
        }

        $this->Template->fields = $fields;
        $this->Template->jsonFields = json_encode($fields);
        $this->Template->formId = 'iso_filter_'.$this->id;
        $this->Template->slabel = $GLOBALS['TL_LANG']['MSC']['submitLabel'];
    }

    private function getRangeConfig(): array
    {
        $cache = Isotope::getRequestCache();
        $configs = [];

        foreach ($this->iso_rangeFields as $i => $config) {
            $new = [
                'id' => "row$i",
                'mode' => $config['mode'] ?? '',
                'attribute' => $config['attribute'] ?? '',
                'attribute_max' => $config['attribute_max'] ?? '',
                'min' => (int) $config['min'],
                'max' => (int) $config['max'],
                'step' => (int) $config['step'],
            ];

            switch ($config['mode']) {
                case 'min':
                case 'max':
                case 'fields':
                    $new['value'] = 'max' === $config['mode'] ? (int) $config['max'] : (int) $config['min'];

                    if (null !== ($filter = $cache->getFilterForModule("row{$i}_min", $this->id))) {
                        $new['value'] = (int) $filter['value'];
                    }

                    $new['inputs'] = [[
                        'id' => "ctrl_row{$i}_".$this->id,
                        'name' => "row$i",
                        'value' => $new['value'],
                    ]];
                    break;

                case 'range':
                default:
                    $new['id'] = $config['attribute'];
                    $new['value'] = [(int) $config['min'], (int) $config['max']];
                    $new['inputs'] = [
                        [
                            'id' => "ctrl_{$config['attribute']}_min_".$this->id,
                            'name' => $config['attribute'].'[min]',
                            'value' => $config['min'],
                        ],
                        [
                            'id' => "ctrl_{$config['attribute']}_max_".$this->id,
                            'name' => $config['attribute'].'[max]',
                            'value' => $config['max'],
                        ],
                    ];

                    if (null !== ($filter = $cache->getFilterForModule($config['attribute'].'_min', $this->id))) {
                        $new['value'][0] = (int) $filter['value'];
                    }

                    if (null !== ($filter = $cache->getFilterForModule($config['attribute'].'_max', $this->id))) {
                        $new['value'][1] = (int) $filter['value'];
                    }
                    break;
            }

            $configs[] = $new;
        }

        return $configs;
    }
}
