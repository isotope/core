<?php

namespace Isotope\IntegrityCheck;

use Isotope\Interfaces\IsotopeIntegrityCheck;

abstract class AbstractIntegrityCheck implements IsotopeIntegrityCheck
{

    /**
     * Generate an ID for this integrity check
     *
     * @return string
     */
    public function getId()
    {
        $className = get_called_class();

        if (($pos = strrpos($className, '\\')) !== false) {
            $className = substr($className, $pos+1);
        }

        return standardize($className);
    }

    public function getName()
    {
        return $GLOBALS['TL_LANG']['ISO_INTEGRITY'][$this->getId()][0];
    }

    public function getDescription()
    {
        if ($this->hasError()) {
            return $GLOBALS['TL_LANG']['ISO_INTEGRITY'][$this->getId()][1];
        } else {
            return $GLOBALS['TL_LANG']['ISO_INTEGRITY'][$this->getId()][2];
        }
    }
}