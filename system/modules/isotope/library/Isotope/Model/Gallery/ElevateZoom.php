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

use Contao\StringUtil;
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
                $GLOBALS['TL_JAVASCRIPT'][] = 'system/modules/isotope/assets/plugins/elevatezoom/jquery.elevateZoom-3.0.8.min.js';
            }

            $objTemplate->zoom              = $this->getImageForType('zoom', $arrFile, $blnWatermark);
            $objTemplate->zoom_windowSize   = StringUtil::deserialize($this->zoom_windowSize);
            $objTemplate->zoom_position     = StringUtil::deserialize($this->zoom_position);
            $objTemplate->zoom_windowFade   = StringUtil::deserialize($this->zoom_windowFade);
            $objTemplate->zoom_border       = StringUtil::deserialize($this->zoom_border);
        }
    }
}
