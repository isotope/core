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
 * Attribute to impelement TextArea widget
 *
 * @copyright  Isotope eCommerce Workgroup 2009-2012
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 */
class TextArea extends Attribute implements IsotopeAttribute
{

	public function addToDCA(array &$arrData)
	{
		parent::addToDCA($arrData);

		$arrData['fields'][$this->field_name]['sql'] = "text NULL";

		// Textarea cannot be w50
        if ($this->rte != '')
        {
            $arrData['fields'][$this->field_name]['eval']['tl_class'] = 'clr';
        }
	}
}
