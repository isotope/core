<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2012 Isotope eCommerce Workgroup
 *
 * @package    Isotope
 * @link       http://www.isotopeecommerce.com
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 *
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
 * @author     Christian de la Haye <service@delahaye.de>
 */

namespace Isotope;


/**
 * Class tl_iso_mail_content
 * Provide miscellaneous methods that are used by the data configuration array.
 */
class tl_iso_mail_content extends \Backend
{

	/**
	 * Available languages
	 * @var array
	 */
	protected $arrLanguages;


	/**
	 * List contents of the e-mail
	 * @param array
	 * @return string
	 */
	public function listRows($arrRow)
	{
		if (!is_array($this->arrLanguages))
		{
			$arrLanguages = $this->getLanguages();
		}

		return '
<div class="cte_type published"><strong>' . $arrRow['subject'] . '</strong> - ' . $arrLanguages[$arrRow['language']] . ($arrRow['fallback'] ? (' (' . $GLOBALS['TL_LANG']['tl_iso_mail_content']['fallback'][0] . ')') : '') . '</div>
<div class="limit_height' . (!$GLOBALS['TL_CONFIG']['doNotCollapse'] ? ' h64' : '') . ' block">
' . $arrRow['html'] . '
<hr>
' . nl2br($arrRow['text']) . '
</div>' . "\n";
	}
}