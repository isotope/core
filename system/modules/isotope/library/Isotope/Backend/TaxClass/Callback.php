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

namespace Isotope\Backend\TaxClass;


class Callback extends \Backend
{

    /**
     * Check permissions to edit table tl_iso_tax_class
     * @return void
     */
    public function checkPermission()
    {
        // Do not run the permission check on other Isotope modules
        if (\Input::get('mod') != 'tax_class') {
            return;
        }

        $this->import('BackendUser', 'User');

        if ($this->User->isAdmin) {
            return;
        }

        // Set root IDs
        if (!is_array($this->User->iso_tax_classes) || count($this->User->iso_tax_classes) < 1) // Can't use empty() because its an object property (using __get)
        {
            $root = array(0);
        } else {
            $root = $this->User->iso_tax_classes;
        }

        $GLOBALS['TL_DCA']['tl_iso_tax_class']['list']['sorting']['root'] = $root;

        // Check permissions to add tax classes
        if (!$this->User->hasAccess('create', 'iso_tax_classp')) {
            $GLOBALS['TL_DCA']['tl_iso_tax_class']['config']['closed'] = true;
            unset($GLOBALS['TL_DCA']['tl_iso_tax_class']['list']['global_operations']['new']);
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

                    if (is_array($arrNew['tl_iso_tax_class']) && in_array(\Input::get('id'), $arrNew['tl_iso_tax_class'])) {
                        // Add permissions on user level
                        if ($this->User->inherit == 'custom' || !$this->User->groups[0]) {
                            $objUser        = \Database::getInstance()->prepare("SELECT iso_tax_classes, iso_tax_classp FROM tl_user WHERE id=?")->limit(1)->execute($this->User->id);
                            $arrPermissions = deserialize($objUser->iso_tax_classp);

                            if (is_array($arrPermissions) && in_array('create', $arrPermissions)) {
                                $arrAccess   = deserialize($objUser->iso_tax_classes);
                                $arrAccess[] = \Input::get('id');

                                \Database::getInstance()->prepare("UPDATE tl_user SET iso_tax_classes=? WHERE id=?")->execute(serialize($arrAccess), $this->User->id);
                            }
                        } // Add permissions on group level
                        elseif ($this->User->groups[0] > 0) {
                            $objGroup       = \Database::getInstance()->prepare("SELECT iso_tax_classes, iso_tax_classp FROM tl_user_group WHERE id=?")->limit(1)->execute($this->User->groups[0]);
                            $arrPermissions = deserialize($objGroup->iso_tax_classp);

                            if (is_array($arrPermissions) && in_array('create', $arrPermissions)) {
                                $arrAccess   = deserialize($objGroup->iso_tax_classes);
                                $arrAccess[] = \Input::get('id');

                                \Database::getInstance()->prepare("UPDATE tl_user_group SET iso_tax_classes=? WHERE id=?")->execute(serialize($arrAccess), $this->User->groups[0]);
                            }
                        }

                        // Add new element to the user object
                        $root[] = \Input::get('id');
                        $this->User->iso_tax_classes = $root;
                    }
                }
            // No break;

            case 'copy':
            case 'delete':
            case 'show':
                if (!in_array(\Input::get('id'), $root) || (\Input::get('act') == 'delete' && !$this->User->hasAccess('delete', 'iso_tax_classp'))) {
                    \System::log('Not enough permissions to ' . \Input::get('act') . ' tax class ID "' . \Input::get('id') . '"', __METHOD__, TL_ERROR);
                    \Controller::redirect('contao/main.php?act=error');
                }
                break;

            case 'editAll':
            case 'deleteAll':
            case 'overrideAll':
                $session = $this->Session->getData();
                if (\Input::get('act') == 'deleteAll' && !$this->User->hasAccess('delete', 'iso_tax_classp')) {
                    $session['CURRENT']['IDS'] = array();
                } else {
                    $session['CURRENT']['IDS'] = array_intersect($session['CURRENT']['IDS'], $root);
                }
                $this->Session->setData($session);
                break;

            default:
                if (strlen(\Input::get('act'))) {
                    \System::log('Not enough permissions to ' . \Input::get('act') . ' tax classes', __METHOD__, TL_ERROR);
                    \Controller::redirect('contao/main.php?act=error');
                }
                break;
        }
    }


    /**
     * Return the copy tax class button
     * @param array
     * @param string
     * @param string
     * @param string
     * @param string
     * @param string
     * @return string
     */
    public function copyTaxClass($row, $href, $label, $title, $icon, $attributes)
    {
        return ($this->User->isAdmin || $this->User->hasAccess('create', 'iso_tax_classp')) ? '<a href="' . $this->addToUrl($href . '&amp;id=' . $row['id']) . '" title="' . specialchars($title) . '"' . $attributes . '>' . \Image::getHtml($icon, $label) . '</a> ' : \Image::getHtml(preg_replace('/\.gif$/i', '_.gif', $icon)) . ' ';
    }


    /**
     * Return the delete tax class button
     * @param array
     * @param string
     * @param string
     * @param string
     * @param string
     * @param string
     * @return string
     */
    public function deleteTaxClass($row, $href, $label, $title, $icon, $attributes)
    {
        return ($this->User->isAdmin || $this->User->hasAccess('delete', 'iso_tax_classp')) ? '<a href="' . $this->addToUrl($href . '&amp;id=' . $row['id']) . '" title="' . specialchars($title) . '"' . $attributes . '>' . \Image::getHtml($icon, $label) . '</a> ' : \Image::getHtml(preg_replace('/\.gif$/i', '_.gif', $icon)) . ' ';
    }
}
