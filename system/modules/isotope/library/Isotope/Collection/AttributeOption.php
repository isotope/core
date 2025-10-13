<?php

/*
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009 - 2019 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Collection;

use Contao\Model\Collection;
use Contao\System;
use Isotope\CoreBundle\Product\ProductInterface;
use Isotope\CoreBundle\Store\Context\StoreContext;
use Isotope\CoreBundle\Store\StoreManager;


/**
 * Class AttributeOption
 *
 * @method \Isotope\Model\AttributeOption[] getModels()
 * @method \Isotope\Model\AttributeOption current()
 */
class AttributeOption extends Collection
{

    /**
     * Get array structure suitable for a frontend widget
     *
     * @param bool           $blnPriceInLabel
     * @return array
     */
    public function getArrayForFrontendWidget(ProductInterface $objProduct = null, $blnPriceInLabel = true)
    {
        /** @var StoreManager $storeManager */
        $storeManager = System::getContainer()->get(StoreManager::class);
        $context = $storeManager->getCurrentContext();

        $arrOptions = array();

        foreach ($this->getModels() as $objModel) {
            $arrOptions[] = $objModel->getAsArray($objProduct, $blnPriceInLabel, $context);
        }

        return $arrOptions;
    }

    /**
     * Get array structure suitable for a backend widget
     *
     * @return array
     */
    public function getArrayForBackendWidget()
    {
        $group = null;
        $arrOptions = array();

        foreach ($this->getModels() as $objModel) {
            $option = $objModel->getAsArray();

            if ($option['group']) {
                $group = $option['label'];
                continue;
            }

            if (null !== $group) {
                $arrOptions[$group][] = $option;
            } else {
                $arrOptions[] = $option;
            }
        }

        return $arrOptions;
    }
}
