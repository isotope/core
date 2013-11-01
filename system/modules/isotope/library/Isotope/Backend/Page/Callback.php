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

namespace Isotope\Backend\Page;


class Callback extends \Backend
{

    /**
     * Limit reader page choices for categories to the current root
     * Everything else does not make sense
     *
     */
    public function limitReaderPageChoice(\DataContainer $dc)
    {
        if (\Input::get('do') == 'page' && \Input::get('table') == 'tl_page' && \Input::get('field') == 'iso_readerJumpTo') {
            if (($objPage = \PageModel::findWithDetails($dc->id)) !== null) {
                $GLOBALS['TL_DCA']['tl_page']['fields']['iso_readerJumpTo']['rootNodes'] = array($objPage->rootId);
            }
        }
    }
}
