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
 */

namespace Isotope\Backend\DCA;


class tl_iso_document extends \Backend
{
    /**
     * Return list templates as array
     * @param DataContainer
     * @return array
     */
    public function getDocumentTemplates(\DataContainer $dc)
    {
        return \Isotope\Backend::getTemplates('iso_document_');
    }
}