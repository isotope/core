<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2014 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @package    Isotope
 * @link       http://isotopeecommerce.org
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Collection;


/**
 * Class AttributeOption
 *
 * @method \Isotope\Model\AttributeOption[] getModels()
 * @method \Isotope\Model\AttributeOption current()
 */
class AttributeOption extends \Model\Collection
{

    /**
     * Get array structure suitable for a frontend widget
     *
     * @return array
     */
    public function getArrayForFrontendWidget()
    {
        $arrOptions = array();

        foreach ($this->getModels() as $objModel) {
            $arrOptions[] = $objModel->getAsArray();
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
