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

namespace Isotope\Backend\Gallery;

use Isotope\Model\Gallery;

class Callback extends \Backend
{

    public function showJsLibraryHint()
    {
        if ('edit' === \Input::get('act')) {
            $gallery = Gallery::findByPk(\Input::get('id'));

            if (null !== $gallery && 'elevatezoom' === $gallery->type) {
                \Message::addInfo($GLOBALS['TL_LANG']['tl_iso_gallery']['includeJQuery']);
            }
        }
    }

    public function showImageSizeHint()
    {
        if ('edit' === \Input::get('act')) {
            $gallery = Gallery::findByPk(\Input::get('id'));

            if (null !== $gallery && ('elevatezoom' === $gallery->type || 'inline' === $gallery->type)) {
                \Message::addInfo($GLOBALS['TL_LANG']['tl_iso_gallery']['pictureNotSupported']);
            }
        }
    }
}
