<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2008-2012 Isotope eCommerce Workgroup
 *
 * @package    Isotope
 * @link       http://www.isotopeecommerce.com
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 */


/**
 * Class InlineGallery
 *
 * Provide methods to handle inline gallery.
 * @copyright  Isotope eCommerce Workgroup 2009-2012
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
 */
class InlineGallery extends IsotopeGallery
{

	/**
	 * Template
	 * @var string
	 */
	protected $strTemplate = 'iso_gallery_inline';


	/**
	 * Generate gallery and return it as HTML string
	 * @param string
	 * @param integer
	 * @param boolean
	 * @return string
	 */
	public function generateGallery($strType='gallery', $intSkip=0, $blnForce=false)
	{
		// Do not render gallery if there are no additional image
		$total = count($this->arrFiles);

		if (($total == 1 || $total <= $intSkip) && !$blnForce)
		{
			return $this->generateAttribute($this->name . '_gallery', ' ', $strType);
		}

		$strGallery = '';

		foreach ($this->arrFiles as $i => $arrFile)
		{
			if ($i < $intSkip)
			{
				continue;
			}

			$objTemplate = new \Isotope\Template($this->strTemplate);

			$objTemplate->setData($arrFile);
			$objTemplate->id = $i;
			$objTemplate->mode = 'gallery';
			$objTemplate->type = $strType;
			$objTemplate->name = $this->name;
			$objTemplate->product_id = $this->product_id;
			$objTemplate->href_reader = $this->href_reader;

			list($objTemplate->link, $objTemplate->rel) = explode('|', $arrFile['link']);

			if ($i == 0)
			{
				$objTemplate->class = 'active';
			}

			$strGallery .= $objTemplate->parse();
		}

		return $this->generateAttribute($this->name . '_gallery', $strGallery, $strType);
	}


	/**
	 * Inject AJAX script
	 */
	protected function injectAjax()
	{
	}
}

