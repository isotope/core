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
use Isotope\Model\Attribute;


/**
 * Attribute to impelement SelectMenu widget
 *
 * @copyright  Isotope eCommerce Workgroup 2009-2012
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 */
class SelectMenu extends Attribute implements IsotopeAttribute
{

	public function addToDCA(&$arrData)
	{
		parent::addToDCA($arrData);

		$arrData['fields'][$this->field_name]['sql'] = "blob NULL";

        // Varian select menu cannot have multiple option
        if ($this->isVariantOption()) {
            $arrData['fields'][$this->field_name]['eval']['multiple'] = false;
            $arrData['fields'][$this->field_name]['eval']['size'] = 1;
        }
	}
}
