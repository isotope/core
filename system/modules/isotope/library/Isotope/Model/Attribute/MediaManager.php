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
use Isotope\Model\Gallery;


/**
 * Attribute to impelement additional image galleries
 *
 * @copyright  Isotope eCommerce Workgroup 2009-2012
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 */
class MediaManager extends Attribute implements IsotopeAttribute
{

	public function saveToDCA(array &$arrData)
	{
		parent::saveToDCA($arrData);

		$arrData['fields'][$this->field_name]['sql'] = "blob NULL";

		// Media Manager must fetch fallback
        $arrData['fields'][$this->field_name]['attributes']['fetch_fallback'] = true;
	}

	public function generate(IsotopeProduct $objProduct, array $arrOptions=array())
	{
        throw new \BadMethodCallException('MediaManager attribute cannot be generated');
	}
}
