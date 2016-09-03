<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2016 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Model\Gallery;

use Isotope\Template;


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
    public function generateGallery($intSkip = 0, $blnForce = false)
    {
        // Do not render gallery if there are no additional image
        $total = $this->size();

        if (($total == 1 || $total <= $intSkip) && !$blnForce) {
            return '';
        }

        // Add class "active" to the first file
        $this->arrFiles[0]['class'] = trim($this->arrFiles[0]['class'] . ' active');

        return parent::generateGallery($intSkip);
    }

    /**
     * Add CSS ID to main image so we can replace it
     *
     * @param Template|object $objTemplate
     * @param string          $strType
     * @param array           $arrFile
     * @param bool            $blnWatermark
     *
     * @return string
     */
    protected function addImageToTemplate(Template $objTemplate, $strType, array $arrFile, $blnWatermark = true)
    {
        parent::addImageToTemplate($objTemplate, $strType, $arrFile, $blnWatermark);

        $objTemplate->uid = uniqid($this->id . $this->product_id);

        if ($strType == 'gallery') {
            $image = $this->getImageForType('main', $arrFile, $blnWatermark);

            $objTemplate->link = $image['main'];
        }
    }
}
