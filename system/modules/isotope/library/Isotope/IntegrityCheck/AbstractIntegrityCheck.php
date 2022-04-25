<?php

/*
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009 - 2019 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\IntegrityCheck;

use Contao\StringUtil;
use Isotope\Interfaces\IsotopeIntegrityCheck;

abstract class AbstractIntegrityCheck implements IsotopeIntegrityCheck
{
    /**
     * @inheritdoc
     */
    public function getId()
    {
        $className = \get_called_class();

        if (($pos = strrpos($className, '\\')) !== false) {
            $className = substr($className, $pos+1);
        }

        return StringUtil::standardize($className);
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return $GLOBALS['TL_LANG']['tl_iso_integrity'][$this->getId()][0];
    }

    /**
     * @inheritdoc
     */
    public function getDescription()
    {
        $key = $this->hasError() ? 1 : 2;

        return $GLOBALS['TL_LANG']['tl_iso_integrity'][$this->getId()][$key];
    }
}
