<?php

/*
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009 - 2019 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Backend\AttributeOption;

use Contao\Backend;
use Contao\BackendUser;
use Contao\Controller;
use Contao\CoreBundle\Exception\AccessDeniedException;
use Contao\Database;
use Contao\Image;
use Contao\Input;
use Contao\StringUtil;
use Contao\System;
use Contao\Versions;
use Isotope\Model\Attribute;
use Isotope\Model\AttributeOption;

class Callback extends Backend
{

    /**
     * Initialize the group wrappers (fake tl_content)
     */
    public function initWrappers()
    {
        $act = Input::get('act');

        if ('' == $act || 'select' === $act) {
            $GLOBALS['TL_WRAPPERS'] = array(
                'start' => array('group'),
                'separator' => array(),
                'stop' => array(),
                'single' => array()
            );
        }
    }

    /**
     * Modify DCA
     */
    public function checkPermission()
    {
        // Attribute options for products can always have a price
        if ('iso_products' !== Input::get('do')) {

            /** @var Attribute $objAttribute */
            $objAttribute = null;

            switch (Input::get('act')) {

                case 'edit':
                case 'delete':
                case 'paste':
                    if (($objOption = AttributeOption::findByPk(Input::get('id'))) !== null) {
                        $objAttribute = Attribute::findByPk($objOption->pid);
                    }
                    break;

                case '':
                case 'select':
                case 'editAll':
                case 'overwriteAll':
                    $objAttribute = Attribute::findByPk(Input::get('id'));
                    break;
            }

            if (null === $objAttribute || $objAttribute->isVariantOption()) {
                unset($GLOBALS['TL_DCA'][AttributeOption::getTable()]['fields']['price']);
            }
        }
    }

    /**
     * Store the attribute field name for product options
     *
     * @param $dc
     */
    public function storeFieldName($dc)
    {
        if ('iso_products' === Input::get('do') && $dc->activeRecord->field_name == '') {
            Database::getInstance()->prepare('
                UPDATE tl_iso_attribute_option
                SET field_name=?
                WHERE id=?
            ')->execute(Input::get('field'), $dc->id);
        }
    }

    /**
     * List child records of the table
     *
     * @param $row
     *
     * @return string
     */
    public function listRecords($row)
    {
        if ('group' === $row['type']) {
            $GLOBALS['TL_WRAPPERS']['stop'][] = 'group';
        }

        $label = $row['label'];

        if ($row['isDefault']) {
            $label = '<strong>'.$label.'</strong>';
        }

        if ($row['price'] != '' && isset($GLOBALS['TL_DCA'][AttributeOption::getTable()]['fields']['price'])) {
            $label .= ' <span style="color:#b3b3b3; padding-left:3px;">(' . $row['price'] . ')</span>';
        }

        return $label;
    }

    /**
     * Disable "isDefault" for option groups
     *
     * @param $varValue
     * @param $dc
     *
     * @return mixed
     */
    public function saveType($varValue, $dc)
    {
        if ('group' === $varValue) {
            Database::getInstance()
                ->prepare("UPDATE tl_iso_attribute_option SET isDefault='' WHERE id=?")
                ->execute($dc->id)
            ;
        }

        return $varValue;
    }

    /**
     * Return the "toggle visibility" button
     * @param array
     * @param string
     * @param string
     * @param string
     * @param string
     * @param string
     * @return string
     */
    public function toggleIcon($row, $href, $label, $title, $icon, $attributes)
    {
        if (!empty(Input::get('tid'))) {
            $this->toggleVisibility(Input::get('tid'), Input::get('state') == 1);
            Controller::redirect(System::getReferer());
        }

        // Check permissions AFTER checking the tid, so hacking attempts are logged
        if (!BackendUser::getInstance()->hasAccess('tl_iso_attribute_option::published', 'alexf')) {
            return '';
        }

        $href .= '&amp;tid='.$row['id'].'&amp;state='.($row['published'] ? '' : 1);

        if (!$row['published']) {
            $icon = 'invisible.svg';
        }

        return '<a href="'.Backend::addToUrl($href).'" title="'.StringUtil::specialchars($title).'"'.$attributes.'>'.Image::getHtml($icon, $label).'</a> ';
    }


    /**
     * Disable/enable a user group
     * @param integer
     * @param boolean
     */
    public function toggleVisibility($intId, $blnVisible)
    {
        // Check permissions to edit
        Input::setGet('id', $intId);
        Input::setGet('act', 'toggle');
        //$this->checkPermission();

        // Check permissions to publish
        if (!BackendUser::getInstance()->hasAccess('tl_iso_attribute_option::published', 'alexf')) {
            throw new AccessDeniedException('Not enough permissions to publish/unpublish attribute option ID "'.$intId.'"');
        }

        $objVersions = new Versions('tl_iso_attribute_option', $intId);
        $objVersions->initialize();

        // Trigger the save_callback
        if (\is_array($GLOBALS['TL_DCA']['tl_iso_attribute_option']['fields']['published']['save_callback'] ?? null)) {
            foreach ($GLOBALS['TL_DCA']['tl_iso_attribute_option']['fields']['published']['save_callback'] as $callback) {
                if (\is_array($callback)) {
                    $blnVisible = System::importStatic($callback[0])->{$callback[1]}($blnVisible, $this);
                } elseif (\is_callable($callback)) {
                    $blnVisible = $callback($blnVisible, $this);
                }
            }
        }

        // Update the database
        Database::getInstance()->prepare('
            UPDATE tl_iso_attribute_option
            SET tstamp='. time() .", published='" . ($blnVisible ? 1 : '') . "'
            WHERE id=?
        ")->execute($intId);

        $objVersions->create();
        System::log('A new version of record "tl_iso_attribute_option.id='.$intId.'" has been created'.$this->getParentEntries('tl_iso_attribute_option', $intId), __METHOD__, TL_GENERAL);
    }
}
