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

namespace Isotope\Backend\OrderStatus;

use Isotope\Model\OrderStatus;


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

        if (!$row['published'] || (strlen($row['start']) && $row['start'] > time()) || (strlen($row['stop']) && $row['stop'] < time())) {
            $image = 'un' . $image;
        }

        return sprintf('<div class="list_icon" style="background-image:url(\'system/themes/%s/images/%s.gif\');">%s</div>', \Backend::getTheme(), $image, $label);
    }


    /**
     * Return the paste button
     *
     * @param \DataContainer $dc
     * @param array          $row
     * @param string         $table
     * @param boolean        $cr
     * @param array|bool     $arrClipboard
     *
     * @return string
     */
    public function pasteButton(\DataContainer $dc, $row, $table, $cr, $arrClipboard = false)
    {
        if ($row['id'] == 0) {
            $imagePasteInto = \Image::getHtml('pasteinto.gif', sprintf($GLOBALS['TL_LANG'][$dc->table]['pasteinto'][1], $row['id']));

            return $cr ? \Image::getHtml('pasteinto_.gif') . ' ' : '<a href="' . \Backend::addToUrl('act=' . $arrClipboard['mode'] . '&mode=2&pid=' . $row['id'] . '&id=' . $arrClipboard['id']) . '" title="' . specialchars(sprintf($GLOBALS['TL_LANG'][$dc->table]['pasteinto'][1], $row['id'])) . '" onclick="Backend.getScrollOffset();">' . $imagePasteInto . '</a> ';
        }

        $imagePasteAfter = \Image::getHtml('pasteafter.gif', sprintf($GLOBALS['TL_LANG'][$dc->table]['pasteafter'][1], $row['id']));

        return (('cut' === $arrClipboard['mode'] && $arrClipboard['id'] == $row['id']) || $cr) ? \Image::getHtml('pasteafter_.gif') . ' ' : '<a href="' . \Backend::addToUrl('act=' . $arrClipboard['mode'] . '&mode=1&pid=' . $row['id'] . '&id=' . $arrClipboard['id']) . '" title="' . specialchars(sprintf($GLOBALS['TL_LANG'][$dc->table]['pasteafter'][1], $row['id'])) . '" onclick="Backend.getScrollOffset();">' . $imagePasteAfter . '</a> ';
    }

    /**
     * Add default order status options if none are set
     */
    public function addDefault()
    {
        if (\Input::get('act') != '' || OrderStatus::countAll() > 0) {
            return;
        }

        $arrStatus = array(
            array(
                'name'          => 'Pending',
                'welcomescreen' => '1',
            ),
            array(
                'name' => 'Processing',
            ),
            array(
                'name' => 'Complete',
                'paid' => '1',
            ),
            array(
                'name' => 'On Hold',
            ),
            array(
                'name' => 'Cancelled',
            )
        );

        $sorting = 0;

        foreach ($arrStatus as $arrData) {
            $objStatus = new OrderStatus();
            $objStatus->setRow($arrData);
            $objStatus->sorting = $sorting;
            $objStatus->save();

            $sorting += 128;
        }
    }
}
