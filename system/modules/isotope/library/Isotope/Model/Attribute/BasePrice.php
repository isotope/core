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

use Isotope\Isotope;
use Isotope\Interfaces\IsotopeAttribute;
use Isotope\Interfaces\IsotopeProduct;
use Isotope\Model\Attribute;


/**
 * Attribute to impelement base price calculation
 *
 * @copyright  Isotope eCommerce Workgroup 2009-2012
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 */
class BasePrice extends Attribute implements IsotopeAttribute
{

    public function __construct(\Database\Result $objResult=null)
    {
        // This class should not be registered
    	// Set type or ModelType would throw an exception
    	$this->arrData['type'] = 'baseprice';

    	parent::__construct($objResult);
    }

	public function saveToDCA(array &$arrData)
	{
		parent::saveToDCA($arrData);

		$arrData['fields'][$this->field_name]['sql'] = "varchar(255) NOT NULL default ''";
	}

	public function generate(IsotopeProduct $objProduct, array $arrOptions=array())
	{
	    $arrData = deserialize($objProduct->{$this->field_name});

        if (is_array($arrData) && $arrData['unit'] > 0 && $arrData['value'] != '')
        {
            $objBasePrice = \Isotope\Model\BasePrice::findByPk((int) $arrData['unit']);

            if (null !== $objBasePrice && null !== $objProduct->getPrice())
            {
                return sprintf($objBasePrice->getLabel(), Isotope::formatPriceWithCurrency($objProduct->getPrice()->getAmount() / $arrData['value'] * $objBasePrice->amount), $arrData['value']);
            }
        }

        return '';
	}
}
