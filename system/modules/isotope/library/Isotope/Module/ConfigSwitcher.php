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

use Contao\Controller;
use Contao\Environment;
use Contao\Input;
use Haste\Generator\RowClass;
use Isotope\CompatibilityHelper;
use Isotope\Isotope;
use Isotope\Model\Config;


/**
 * @property array $iso_config_ids
 */
class ConfigSwitcher extends Module
{

    /**
     * Module template
     * @var string
     */
    protected $strTemplate = 'mod_iso_configswitcher';

    /**
     * @inheritDoc
     */
    protected function getSerializedProperties()
    {
        $props = parent::getSerializedProperties();

        $props[] = 'iso_config_ids';

        return $props;
    }

    /**
     * Display a wildcard in the back end
     * @return string
     */
    public function generate()
    {
        if (CompatibilityHelper::isBackend()) {
            return $this->generateWildcard();
        }

        if (($configId = (int) Input::get('config')) > 0) {
            $this->overrideConfig($configId, true);
        }

        $optionsCount = \count($this->iso_config_ids);

        // If the module config has only one config, always override the current cart
        if (1 === $optionsCount) {
            $this->overrideConfig((int) reset($this->iso_config_ids));
        }

        if ($optionsCount < 2) {
            return '';
        }

        return parent::generate();
    }


    /**
     * Compile the module
     * @return void
     */
    protected function compile()
    {
        $arrConfigs = array();

        /** @var Config[] $objConfigs */
        $objConfigs = Config::findMultipleByIds($this->iso_config_ids);

        if (null !== $objConfigs) {
            foreach ($objConfigs as $objConfig) {
                $arrConfigs[] = array (
                    'config'    => $objConfig,
                    'label'     => $objConfig->getLabel(),
                    'active'    => Isotope::getConfig()->id == $objConfig->id ? true : false,
                    'href'      => Environment::get('request') . ((strpos(Environment::get('request'), '?') === false) ? '?' : '&amp;') . 'config=' . $objConfig->id,
                );
            }
        }

        RowClass::withKey('class')->addFirstLast()->applyTo($arrConfigs);

        $this->Template->configs = $arrConfigs;
    }

    private function overrideConfig(int $configId, bool $forceReload = false): void
    {
        if ((int) Isotope::getCart()->config_id === $configId && !$forceReload) {
            return;
        }

        if (\in_array($configId, $this->iso_config_ids)) {
            Isotope::getCart()->config_id = $configId;
            Isotope::getCart()->save();
        }

        Controller::redirect(
            preg_replace(
                '@[?|&]config=' . Input::get('config') . '@',
                '',
                Environment::get('request')
            )
        );
    }
}
