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
 * Attribute to implement ConditionalSelectMenu widget
 *
 * @copyright  Isotope eCommerce Workgroup 2009-2012
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 */
class ConditionalSelectMenu extends Attribute implements IsotopeAttribute
{

	public function saveToDCA(array &$arrData)
	{
		parent::saveToDCA($arrData);

		$arrData['fields'][$this->field_name]['sql'] = "blob NULL";
	}

//! @todo 'callback'    => array(array('Isotope', 'mergeConditionalOptionData')),
}
