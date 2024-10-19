<?php

/*
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009 - 2019 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Model\Gallery;

use Isotope\Template;

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
        $this->arrFiles[0]['class'] = trim(($this->arrFiles[0]['class'] ?? '') . ' active');

        return parent::generateGallery($intSkip);
    }

    /**
     * Add CSS ID to main image so we can replace it
     *
     * @param Template|object $objTemplate
     * @param string          $strType
     * @param bool            $blnWatermark
     */
    protected function addImageToTemplate(Template $objTemplate, $strType, array $arrFile, $blnWatermark = true)
    {
        parent::addImageToTemplate($objTemplate, $strType, $arrFile, $blnWatermark);

        // Backwards compatibility
        $objTemplate->uid = $this->getName();

        if ('gallery' === $strType) {
            $objTemplate->link = $this->getImageForType('main', $arrFile, $blnWatermark)['main'];

            // Generate the lightbox image
            if ($this->anchor === 'lightbox') {
                $objTemplate->lightboxUrl = $this->getImageForType('lightbox', $arrFile, $blnWatermark)['lightbox'];
            }
        }
    }
}
