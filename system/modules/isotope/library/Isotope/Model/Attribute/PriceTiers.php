<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2012 Isotope eCommerce Workgroup
 *
 * @package    Isotope
 * @link       http://www.isotopeecommerce.com
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
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
class PriceTiers extends Attribute implements IsotopeAttribute
{

    public function __construct(\Database\Result $objResult=null)
    {
        // This class should not be registered
    	// Set type or ModelType would throw an exception
    	$this->arrData['type'] = 'pricetiers';

    	parent::__construct($objResult);
    }

	public function generate(IsotopeProduct $objProduct, array $arrOptions=array())
	{
	    $objPrice = $objProduct->getPrice();

        if (null === $objPrice || !$objPrice->hasTiers()) {
            return '';
        }

        $arrTiers = array();

        foreach ($objPrice->getTiers() as $min => $price) {
            $arrTiers[] = array(
                'min'       => $min,
                'price'     => $price,
                'tax_class' => $objPrice->tax_class,
            );
        }

        $order = $arrOptions['order'];
        if ($order != '' && in_array($order, array_keys($arrTiers[0]))) {

            usort($arrTiers, function($a, $b) use ($order) {
                return strcmp($a[$order], $b[$order]);
            });
        }

        return $this->generateTable($arrTiers);
	}
}
