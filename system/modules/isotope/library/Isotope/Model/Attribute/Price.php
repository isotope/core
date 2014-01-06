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

namespace Isotope\Model\Attribute;

use Isotope\Interfaces\IsotopeAttribute;
use Isotope\Interfaces\IsotopeProduct;
use Isotope\Model\Attribute;


/**
 * Attribute to impelement base price calculation
 *
 * @copyright  Isotope eCommerce Workgroup 2009-2012
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 */
class Price extends Attribute implements IsotopeAttribute
{

    public function __construct(\Database\Result $objResult = null)
    {
        // This class should not be registered
        // Set type or ModelType would throw an exception
        $this->arrData['type'] = 'price';

        parent::__construct($objResult);
    }

    public function getBackendWidget()
    {
        return $GLOBALS['BE_FFL']['timePeriod'];
    }

    public function getFrontendWidget()
    {
        return false;
    }

    public function generate(IsotopeProduct $objProduct, array $arrOptions = array())
    {
        $objPrice = $objProduct->getPrice();

        if (null === $objPrice) {
            return '';
        }

        return $objPrice->generate($objProduct->getRelated('type')->showPriceTiers());
    }
}
