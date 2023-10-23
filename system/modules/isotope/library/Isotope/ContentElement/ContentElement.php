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

use ContentElement as Contao_ContentElement;
use Haste\Util\Debug;
use Haste\Util\RepositoryVersion;
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
            $version = RepositoryVersion::encode(Isotope::VERSION);

            $GLOBALS['TL_JAVASCRIPT'][] = Debug::uncompressedFile(
                'system/modules/isotope/assets/js/isotope.min.js|static|'.$version
            );

            $GLOBALS['TL_CSS'][] = Debug::uncompressedFile(
                'system/modules/isotope/assets/css/isotope.min.css|screen|static|'.$version
            );
        }
    }
}
