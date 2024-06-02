<?php

/*
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009 - 2019 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\ContentElement;

use Contao\ContentElement as Contao_ContentElement;
use Isotope\CompatibilityHelper;
use Isotope\Isotope;

abstract class ContentElement extends Contao_ContentElement
{

    /**
     * Initialize the content element
     * @param object
     */
    public function __construct($objElement)
    {
        parent::__construct($objElement);

        // Load Isotope JavaScript and style sheet
        if (CompatibilityHelper::isFrontend()) {
            $GLOBALS['TL_JAVASCRIPT'][] = 'system/modules/isotope/assets/js/isotope.min.js|static|'.Isotope::VERSION;
            $GLOBALS['TL_CSS'][] = 'system/modules/isotope/assets/css/isotope.min.css|screen|static|'.Isotope::VERSION;
        }
    }
}
