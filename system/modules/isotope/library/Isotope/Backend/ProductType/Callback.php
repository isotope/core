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

namespace Isotope\Backend\ProductType;

use Isotope\Interfaces\IsotopeAttributeForVariants;
use Isotope\Model\Product;
use Isotope\Model\ProductType;


class Callback extends \Backend
{

    /**
     * Check permissions to edit table tl_iso_producttype
     * @return void
     */
    public function checkPermission()
    {
        // Do not run the permission check on other Isotope modules
        if (\Input::get('mod') != 'producttypes') {
            return;
        }

        $this->import('BackendUser', 'User');

        if ($this->User->isAdmin) {
            return;
        }

        // Set root IDs
        if (!is_array($this->User->iso_product_types) || count($this->User->iso_product_types) < 1) // Can't use empty() because its an object property (using __get)
        {
            $root = array(0);
        } else {
            $root = $this->User->iso_product_types;
        }

        $GLOBALS['TL_DCA']['tl_iso_producttype']['list']['sorting']['root'] = $root;

        // Check permissions to add product types
        if (!$this->User->hasAccess('create', 'iso_product_typep')) {
            $GLOBALS['TL_DCA']['tl_iso_producttype']['config']['closed'] = true;
            unset($GLOBALS['TL_DCA']['tl_iso_producttype']['list']['global_operations']['new']);
        }

        // Check current action
        switch (\Input::get('act')) {
            case 'create':
            case 'select':
                // Allow
                break;

            case 'edit':
                // Dynamically add the record to the user profile
                if (!in_array(\Input::get('id'), $root)) {
                    $arrNew = $this->Session->get('new_records');

                    if (is_array($arrNew['tl_iso_producttype']) && in_array(\Input::get('id'), $arrNew['tl_iso_producttype'])) {
                        // Add permissions on user level
                        if ($this->User->inherit == 'custom' || !$this->User->groups[0]) {
                            $objUser        = \Database::getInstance()->prepare("SELECT iso_product_types, iso_product_typep FROM tl_user WHERE id=?")->limit(1)->execute($this->User->id);
                            $arrPermissions = deserialize($objUser->tl_iso_producttypep);

                            if (is_array($arrPermissions) && in_array('create', $arrPermissions)) {
                                $arrAccess   = deserialize($objUser->iso_product_types);
                                $arrAccess[] = \Input::get('id');

                                \Database::getInstance()->prepare("UPDATE tl_user SET iso_product_types=? WHERE id=?")->execute(serialize($arrAccess), $this->User->id);
                            }
                        } // Add permissions on group level
                        elseif ($this->User->groups[0] > 0) {
                            $objGroup       = \Database::getInstance()->prepare("SELECT iso_product_types, iso_product_typep FROM tl_user_group WHERE id=?")->limit(1)->execute($this->User->groups[0]);
                            $arrPermissions = deserialize($objGroup->iso_product_typep);

                            if (is_array($arrPermissions) && in_array('create', $arrPermissions)) {
                                $arrAccess   = deserialize($objGroup->iso_product_types);
                                $arrAccess[] = \Input::get('id');

                                \Database::getInstance()->prepare("UPDATE tl_user_group SET iso_product_types=? WHERE id=?")->execute(serialize($arrAccess), $this->User->groups[0]);
                            }
                        }

                        // Add new element to the user object
                        $root[]                        = \Input::get('id');
                        $this->User->iso_product_types = $root;
                    }
                }
            // No break;

            case 'copy':
            case 'delete':
            case 'show':
                if (!in_array(\Input::get('id'), $root) || (\Input::get('act') == 'delete' && !$this->User->hasAccess('delete', 'iso_product_typep'))) {
                    \System::log('Not enough permissions to ' . \Input::get('act') . ' product type ID "' . \Input::get('id') . '"', __METHOD__, TL_ERROR);
                    \Controller::redirect('contao/main.php?act=error');
                }
                break;

            case 'editAll':
            case 'deleteAll':
            case 'overrideAll':
                $session = $this->Session->getData();
                if (\Input::get('act') == 'deleteAll' && !$this->User->hasAccess('delete', 'iso_product_typep')) {
                    $session['CURRENT']['IDS'] = array();
                } else {
                    $session['CURRENT']['IDS'] = array_intersect($session['CURRENT']['IDS'], $root);
                }
                $this->Session->setData($session);
                break;

            default:
                if (strlen(\Input::get('act'))) {
                    \System::log('Not enough permissions to ' . \Input::get('act') . ' product types', __METHOD__, TL_ERROR);
                    \Controller::redirect('contao/main.php?act=error');
                }
                break;
        }
    }


    /**
     * Return the copy product type button
     * @param array
     * @param string
     * @param string
     * @param string
     * @param string
     * @param string
     * @return string
     */
    public function copyProductType($row, $href, $label, $title, $icon, $attributes)
    {
        $this->import('BackendUser', 'User');

        return ($this->User->isAdmin || $this->User->hasAccess('create', 'iso_product_typep')) ? '<a href="' . $this->addToUrl($href . '&amp;id=' . $row['id']) . '" title="' . specialchars($title) . '"' . $attributes . '>' . \Image::getHtml($icon, $label) . '</a> ' : \Image::getHtml(preg_replace('/\.gif$/i', '_.gif', $icon)) . ' ';
    }


    /**
     * Return the delete product type button
     * @param array
     * @param string
     * @param string
     * @param string
     * @param string
     * @param string
     * @return string
     */
    public function deleteProductType($row, $href, $label, $title, $icon, $attributes)
    {
        if (Product::countBy('type', $row['id']) > 0) {
            return \Image::getHtml(preg_replace('/\.gif$/i', '_.gif', $icon)) . ' ';
        }

        return ($this->User->isAdmin || $this->User->hasAccess('delete', 'iso_product_typep')) ? '<a href="' . $this->addToUrl($href . '&amp;id=' . $row['id']) . '" title="' . specialchars($title) . '"' . $attributes . '>' . \Image::getHtml($icon, $label) . '</a> ' : \Image::getHtml(preg_replace('/\.gif$/i', '_.gif', $icon)) . ' ';
    }

    /**
     * Returns all allowed product types as array
     * @return array
     */
    public function getOptions()
    {
        $arrTypes = \BackendUser::getInstance()->iso_product_types;

        if (!\BackendUser::getInstance()->isAdmin && (!is_array($arrTypes) || empty($arrTypes))) {
            $arrTypes = array(0);
        }

        $arrProductTypes = array();
        $objProductTypes = \Database::getInstance()->execute("
            SELECT id,name FROM " . ProductType::getTable() . "
            WHERE tstamp>0" . (\BackendUser::getInstance()->isAdmin ? '' : (" AND id IN (" . implode(',', $arrTypes) . ")")) . "
            ORDER BY name
        ");

        while ($objProductTypes->next()) {
            $arrProductTypes[$objProductTypes->id] = $objProductTypes->name;
        }

        return $arrProductTypes;
    }

    /**
     * Make sure at least one variant attribute is enabled
     * @param   mixed
     * @return  mixed
     * @throws  UnderflowException
     */
    public function validateVariantAttributes($varValue)
    {
        \Haste\Haste::getInstance()->call('loadDataContainer', 'tl_iso_product');

        $blnError = true;
        $arrAttributes = deserialize($varValue);
        $arrVariantAttributeLabels = array();

        if (!empty($arrAttributes) && is_array($arrAttributes)) {
            foreach ($arrAttributes as $arrAttribute) {
                $objAttribute = $GLOBALS['TL_DCA']['tl_iso_product']['attributes'][$arrAttribute['name']];

                if (null !== $objAttribute && /* @todo in 3.0: $objAttribute instanceof IsotopeAttributeForVariants && */$objAttribute->isVariantOption()) {
                    $arrVariantAttributeLabels[] = $objAttribute->name;

                    if ($arrAttribute['enabled']) {
                        $blnError = false;
                    }
                }
            }
        }

        if ($blnError) {
            \System::loadLanguageFile('explain');
            throw new \UnderflowException(
                sprintf($GLOBALS['TL_LANG']['tl_iso_producttype']['noVariantAttributes'],
                    implode(', ', $arrVariantAttributeLabels)
                )
            );
        }

        return $varValue;
    }
}
