<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2013 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @package    Isotope
 * @link       http://isotopeecommerce.org
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\ContentElement;

use \ContentElement as Contao_ContentElement;


/**
 * Class ContentIsotope
 *
 * Provide methods to handle Isotope content elements.
 * @copyright  Isotope eCommerce Workgroup 2009-2012
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
 */
abstract class ContentElement extends Contao_ContentElement
{

    /**
     * Initialize the content element
     * @param object
     */
    public function __construct($objElement)
    {
        parent::__construct($objElement);

        if (TL_MODE == 'FE')
        {
            // Load Isotope javascript and css
            $GLOBALS['TL_JAVASCRIPT'][] = \Haste\Util\Debug::uncompressedFile('system/modules/isotope/assets/js/isotope.min.js');
            $GLOBALS['TL_CSS'][] = \Haste\Util\Debug::uncompressedFile('system/modules/isotope/assets/css/isotope.min.css');
        }
    }
}
