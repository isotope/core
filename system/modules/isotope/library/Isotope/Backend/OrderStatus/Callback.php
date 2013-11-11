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
 */

namespace Isotope\Backend\OrderStatus;


class Callback extends \Backend
{

    /**
     * Add an image to each record
     * @param array
     * @param string
     * @return string
     */
    public function addIcon($row, $label)
    {
        $image = 'published';

        if (!$row['published'] || (strlen($row['start']) && $row['start'] > time()) || (strlen($row['stop']) && $row['stop'] < time()))
        {
            $image = 'un'.$image;
        }

        return sprintf('<div class="list_icon" style="background-image:url(\'system/themes/%s/images/%s.gif\');">%s</div>', $this->getTheme(), $image, $label);
    }


    /**
     * Return the paste button
     * @param object
     * @param array
     * @param string
     * @param boolean
     * @param array
     * @return string
     */
    public function pasteButton(\DataContainer $dc, $row, $table, $cr, $arrClipboard=false)
    {
        if ($row['id'] == 0)
        {
            $imagePasteInto = \Image::getHtml('pasteinto.gif', sprintf($GLOBALS['TL_LANG'][$dc->table]['pasteinto'][1], $row['id']));

            return $cr ? \Image::getHtml('pasteinto_.gif').' ' : '<a href="'.$this->addToUrl('act='.$arrClipboard['mode'].'&mode=2&pid='.$row['id'].'&id='.$arrClipboard['id']).'" title="'.specialchars(sprintf($GLOBALS['TL_LANG'][$dc->table]['pasteinto'][1], $row['id'])).'" onclick="Backend.getScrollOffset();">'.$imagePasteInto.'</a> ';
        }

        $imagePasteAfter = \Image::getHtml('pasteafter.gif', sprintf($GLOBALS['TL_LANG'][$dc->table]['pasteafter'][1], $row['id']));

        return (($arrClipboard['mode'] == 'cut' && $arrClipboard['id'] == $row['id']) || $cr) ? \Image::getHtml('pasteafter_.gif').' ' : '<a href="'.$this->addToUrl('act='.$arrClipboard['mode'].'&mode=1&pid='.$row['id'].'&id='.$arrClipboard['id']).'" title="'.specialchars(sprintf($GLOBALS['TL_LANG'][$dc->table]['pasteafter'][1], $row['id'])).'" onclick="Backend.getScrollOffset();">'.$imagePasteAfter.'</a> ';
    }
}
