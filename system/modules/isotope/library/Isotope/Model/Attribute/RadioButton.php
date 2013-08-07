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
 * Attribute to impelement RadioButton widget
 *
 * @copyright  Isotope eCommerce Workgroup 2009-2012
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 */
class RadioButton extends Attribute implements IsotopeAttribute
{

	public function addToDCA(&$arrData)
	{
		parent::addToDCA($arrData);

		$arrData['fields'][$this->field_name]['sql'] = "varchar(255) NOT NULL default ''";

		if ($this->fe_filter) {
			$arrData['config']['sql']['keys'][$this->field_name] = 'index';
		}
	}
}
