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

namespace Isotope\Model\Gallery;


/**
 * Class InlineGallery
 *
 * Provide methods to handle inline gallery.
 * @copyright  Isotope eCommerce Workgroup 2009-2012
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
 */
class Inline extends Standard
{

    /**
     * Template
     * @var string
     */
    protected $strTemplate = 'iso_gallery_inline';


    /**
     * Generate gallery and return it as HTML string
     * @param   integer
     * @param   bool
     * @return  string
     */
    public function generateGallery($intSkip=0, $blnForce=false)
    {
        // Do not render gallery if there are no additional image
        $total = count($this->arrFiles);

        if (($total == 1 || $total <= $intSkip) && !$blnForce) {
            return '';
        }

        return parent::generateGallery($intSkip);
    }

    /**
     * Add CSS ID to main image so we can replace it
     * @param   object
     * @param   string
     * @param   array
     * @return  string
     */
    protected function addImageToTemplate(\Isotope\Template $objTemplate, $strType, array $arrFile)
    {
        parent::addImageToTemplate($objTemplate, $strType, $arrFile);

        $objTemplate->uid = spl_object_hash($this);

        if ($strType == 'gallery') {
            $objTemplate->link = $arrFile['main'];
        }
    }
}
