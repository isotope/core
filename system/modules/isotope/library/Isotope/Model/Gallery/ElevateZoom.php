<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2014 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @package    Isotope
 * @link       http://isotopeecommerce.org
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Model\Gallery;

use Isotope\Template;

class ElevateZoom extends Inline
{

    /**
     * Template
     * @var string
     */
    protected $strTemplate = 'iso_gallery_elevatezoom';

    /**
     * Add CSS ID and zoom image to template
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

        if ($blnWatermark) {
            if ('main' === $strType) {
                $GLOBALS['TL_JAVASCRIPT'][] = 'system/modules/isotope/assets/plugins/elevatezoom/jquery.elevatezoom-3.0.8.min.js';
            }

            $objTemplate->zoom              = $this->getImageForType('zoom', $arrFile, $blnWatermark);
            $objTemplate->zoom_windowSize   = deserialize($this->zoom_windowSize);
            $objTemplate->zoom_windowOffset = deserialize($this->zoom_windowOffset);
            $objTemplate->zoom_windowFade   = deserialize($this->zoom_windowFade);
        }
    }
}
