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
use Contao\System;
use Isotope\CompatibilityHelper;
use Isotope\CoreBundle\Store\CartManager;
use Isotope\CoreBundle\Store\Context\StoreContextFactory;
use Isotope\CoreBundle\Store\StoreManager;
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

        /** @var StoreManager $storeManager */
        $storeManager = System::getContainer()->get(StoreManager::class);

        if (null !== $objConfigs) {
            foreach ($objConfigs as $objConfig) {
                $arrConfigs[] = array (
                    'config'    => $objConfig,
                    'label'     => $objConfig->getLabel(),
                    'active'    => $storeManager->getCurrentContext()->getConfigId() === (int) $objConfig->id,
                    'href'      => Environment::get('request') . ((strpos(Environment::get('request'), '?') === false) ? '?' : '&amp;') . 'config=' . $objConfig->id,
                );
            }
        }

        $this->Template->configs = $arrConfigs;
    }

    private function overrideConfig(int $configId, bool $forceReload = false): void
    {
        /** @var StoreManager $storeManager */
        $storeManager = System::getContainer()->get(StoreManager::class);
        $context = $storeManager->getCurrentContext();

        if (!$forceReload && $context->getConfigId() === $configId) {
            return;
        }

        if (\in_array($configId, $this->iso_config_ids) && ($config = Config::findById($configId))) {
            /** @var StoreContextFactory $storeContextFactory */
            $storeContextFactory = System::getContainer()->get(StoreContextFactory::class);
            $newContext = $storeContextFactory->create($context->getStoreId(), $config, $context->getLocale(), $context->getIdentifier(), $context->getMemberId());

            /** @var CartManager $cartManager */
            $cartManager = System::getContainer()->get(CartManager::class);
            $cart = $context->getCart();
            $cartManager->recalculate($cart, $newContext);
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
