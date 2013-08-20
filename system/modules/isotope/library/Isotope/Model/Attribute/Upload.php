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
 * Attribute to implement frontend uploads
 *
 * @copyright  Isotope eCommerce Workgroup 2009-2012
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 */
class Upload extends Attribute implements IsotopeAttribute
{

	/**
	 * Upload widget is always customer defined
	 * @return	bool
	 */
	public function isCustomerDefined()
	{
		return true;
	}

	public function getBackendWidget()
	{
		return false;
	}

	public function saveToDCA(array &$arrData)
	{
		parent::saveToDCA($arrData);

		$arrData['fields'][$this->field_name]['sql'] = "varchar(255) NOT NULL default ''";

		// Install save_callback for upload widgets
        $arrData['fields'][$this->field_name]['save_callback'][] = array('Isotope\Frontend', 'saveUpload');
	}
}
