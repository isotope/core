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

namespace Isotope;


/**
 * Class IsotopeTranslation
 *
 * @copyright  Isotope eCommerce Workgroup 2009-2012
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 */
class IsotopeTranslation extends \Controller
{

	public function loadLocalLanguageFiles($strName, $strLanguage)
	{
		// Parse all active modules
		foreach ($this->Config->getActiveModules() as $strModule)
		{
			$strFile = sprintf('%s/system/modules/%s/languages/%s/local/%s.php', TL_ROOT, $strModule, $strLanguage, $strName);

			if (is_file($strFile))
			{
				@include($strFile);
			}
		}
	}
}

