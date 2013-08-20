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

	public function addToDCA(array &$arrData)
	{
		// Varian select menu cannot have multiple option
        if ($this->isVariantOption()) {
        	$this->multiple = false;
        	$this->size = 1;
        }

		parent::addToDCA($arrData);

		if ($this->multiple) {
			$arrData['fields'][$this->field_name]['sql'] = "blob NULL";
		} else {
			$arrData['fields'][$this->field_name]['sql'] = "varchar(255) NOT NULL default ''";

			if ($this->fe_filter) {
				$arrData['config']['sql']['keys'][$this->field_name] = 'index';
			}
		}
	}
}
