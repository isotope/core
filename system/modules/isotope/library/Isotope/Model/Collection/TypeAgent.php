<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2008-2012 Isotope eCommerce Workgroup
 *
 * @package    Isotope
 * @link       http://www.isotopeecommerce.com
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 */

namespace Isotope\Model\Collection;


class TypeAgent extends \Model\Collection
{

    /**
     * Fetch the next result row and create the model
     *
     * @return boolean True if there was another row
     */
    protected function fetchNext()
    {
        if ($this->objResult->next() == false) {
            return false;
        }

        $this->arrModels[$this->intIndex + 1] = call_user_func(array($this->strTable, 'buildModelType'), $this->objResult);

        return true;
    }
}
