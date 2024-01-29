<?php

/*
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009 - 2019 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Backend\OrderStatus;

use Contao\Backend;
use Contao\Database;
use Contao\DataContainer;
use Contao\Image;
use Contao\Input;
use Contao\StringUtil;
use Isotope\Model\OrderStatus;


class Callback extends Backend
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

        if (!$row['published'] || (\strlen($row['start']) && $row['start'] > time()) || (\strlen($row['stop']) && $row['stop'] < time())) {
            $image = 'un' . $image;
        }

        return sprintf('<div class="list_icon" style="background-image:url(\'system/themes/%s/images/%s.svg\');">%s</div>', Backend::getTheme(), $image, $label);
    }


    /**
     * Return the paste button
     *
     * @param DataContainer $dc
     * @param array          $row
     * @param string         $table
     * @param boolean        $cr
     * @param array|bool     $arrClipboard
     *
     * @return string
     */
    public function pasteButton(DataContainer $dc, $row, $table, $cr, $arrClipboard = false)
    {
        if ($row['id'] == 0) {
            $imagePasteInto = Image::getHtml('pasteinto.svg', sprintf($GLOBALS['TL_LANG'][$dc->table]['pasteinto'][1], $row['id']));

            return $cr ? Image::getHtml('pasteinto_.svg') . ' ' : '<a href="' . Backend::addToUrl('act=' . $arrClipboard['mode'] . '&mode=2&pid=' . $row['id'] . '&id=' . $arrClipboard['id']) . '" title="' . StringUtil::specialchars(sprintf($GLOBALS['TL_LANG'][$dc->table]['pasteinto'][1], $row['id'])) . '" onclick="Backend.getScrollOffset();">' . $imagePasteInto . '</a> ';
        }

        $imagePasteAfter = Image::getHtml('pasteafter.svg', sprintf($GLOBALS['TL_LANG'][$dc->table]['pasteafter'][1], $row['id']));

        return (('cut' === $arrClipboard['mode'] && $arrClipboard['id'] == $row['id']) || $cr) ? Image::getHtml('pasteafter_.svg') . ' ' : '<a href="' . Backend::addToUrl('act=' . $arrClipboard['mode'] . '&mode=1&pid=' . $row['id'] . '&id=' . $arrClipboard['id']) . '" title="' . StringUtil::specialchars(sprintf($GLOBALS['TL_LANG'][$dc->table]['pasteafter'][1], $row['id'])) . '" onclick="Backend.getScrollOffset();">' . $imagePasteAfter . '</a> ';
    }

    /**
     * Add default order status options if none are set
     */
    public function addDefault()
    {
        if (Input::get('act') != '' || OrderStatus::countAll() > 0) {
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
        $time = time();

        foreach ($arrStatus as $arrData) {
            $objStatus = new OrderStatus();
            $objStatus->setRow($arrData);
            $objStatus->tstamp = $time;
            $objStatus->sorting = $sorting;
            $objStatus->save();

            $sorting += 128;
        }
    }

    /**
     * Gets notification options for order status change.
     *
     * @return array
     */
    public function getNotificationChoices(DataContainer $dc)
    {
        $arrChoices = array();
        $objNotifications = Database::getInstance()->execute(
            "SELECT id,title FROM tl_nc_notification WHERE type='iso_order_status_change' ORDER BY title"
        );

        while ($objNotifications->next()) {
            $arrChoices[$objNotifications->id] = $objNotifications->title;
        }

        return $arrChoices;
    }

    /**
     * Generate the order label and return it as string
     *
     * @param array  $row
     * @param string $label
     *
     * @return string
     */
    public function getColoredLabel($row, $label)
    {
        $status = OrderStatus::findByPk($row['id']);

        if (null === $status) {
            return $label;
        }

        return '<span style="padding: 2px 5px 3px;border-radius: 2px;vertical-align: middle;'.$status->getColorStyles().'">'.$label.'</span>';
    }
}
