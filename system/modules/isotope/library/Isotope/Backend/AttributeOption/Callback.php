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

namespace Isotope\Backend\AttributeOption;

use Isotope\Model\Attribute;
use Isotope\Model\AttributeOption;

class Callback extends \Backend
{

    /**
     * Initialize the group wrappers (fake tl_content)
     */
    public function initWrappers()
    {
        if (\Input::get('act') == '' || \Input::get('act') == 'select') {
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
        if (\Input::get('do') != 'iso_products') {

            /** @type Attribute $objAttribute */
            $objAttribute = null;

            switch (\Input::get('act')) {

                case 'edit':
                case 'delete':
                case 'paste':
                    if (($objOption = AttributeOption::findByPk(\Input::get('id'))) !== null) {
                        $objAttribute = Attribute::findByPk($objOption->pid);
                    }
                    break;

                case '':
                case 'select':
                case 'editAll':
                case 'overwriteAll':
                    $objAttribute = Attribute::findByPk(\Input::get('id'));
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
        if (\Input::get('do') == 'iso_products' && $dc->activeRecord->field_name == '') {
            \Database::getInstance()->prepare("
                UPDATE tl_iso_attribute_option
                SET field_name=?
                WHERE id=?
            ")->execute(\Input::get('field'), $dc->id);
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
        if ($row['type'] == 'group') {
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
        if ($varValue == 'group') {
            \Database::getInstance()->prepare("
                UPDATE tl_iso_attribute_option
                SET isDefault=''
                WHERE id=?
            ")->execute($dc->id);
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
        if (strlen(\Input::get('tid'))) {
            $this->toggleVisibility(\Input::get('tid'), (\Input::get('state') == 1));
            $this->redirect($this->getReferer());
        }

        // Check permissions AFTER checking the tid, so hacking attempts are logged
        if (!\BackendUser::getInstance()->hasAccess('tl_iso_attribute_option::published', 'alexf')) {
            return '';
        }

        $href .= '&amp;tid='.$row['id'].'&amp;state='.($row['published'] ? '' : 1);

        if (!$row['published']) {
            $icon = 'invisible.gif';
        }

        return '<a href="'.$this->addToUrl($href).'" title="'.specialchars($title).'"'.$attributes.'>'.\Image::getHtml($icon, $label).'</a> ';
    }


    /**
     * Disable/enable a user group
     * @param integer
     * @param boolean
     */
    public function toggleVisibility($intId, $blnVisible)
    {
        // Check permissions to edit
        \Input::setGet('id', $intId);
        \Input::setGet('act', 'toggle');
        //$this->checkPermission();

        // Check permissions to publish
        if (!\BackendUser::getInstance()->hasAccess('tl_iso_attribute_option::published', 'alexf')) {
            $this->log('Not enough permissions to publish/unpublish attribute option ID "'.$intId.'"', __METHOD__, TL_ERROR);
            $this->redirect('contao/main.php?act=error');
        }

        $objVersions = new \Versions('tl_iso_attribute_option', $intId);
        $objVersions->initialize();

        // Trigger the save_callback
        if (is_array($GLOBALS['TL_DCA']['tl_iso_attribute_option']['fields']['published']['save_callback'])) {
            foreach ($GLOBALS['TL_DCA']['tl_iso_attribute_option']['fields']['published']['save_callback'] as $callback) {
                if (is_array($callback)) {
                    $this->import($callback[0]);
                    $blnVisible = $this->$callback[0]->$callback[1]($blnVisible, $this);
                } elseif (is_callable($callback)) {
                    $blnVisible = $callback($blnVisible, $this);
                }
            }
        }

        // Update the database
        \Database::getInstance()->prepare("
            UPDATE tl_iso_attribute_option
            SET tstamp=". time() .", published='" . ($blnVisible ? 1 : '') . "'
            WHERE id=?
        ")->execute($intId);

        $objVersions->create();
        $this->log('A new version of record "tl_iso_attribute_option.id='.$intId.'" has been created'.$this->getParentEntries('tl_iso_attribute_option', $intId), __METHOD__, TL_GENERAL);
    }
}
