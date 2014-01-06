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

namespace Isotope\Backend\Shipping;


class Callback extends \Backend
{

    /**
     * Check permissions to edit table tl_iso_shipping
     * @return void
     */
    public function checkPermission()
    {
        // Do not run the permission check on other Isotope modules
        if (\Input::get('mod') != 'shipping') {
            return;
        }

        if (\BackendUser::getInstance()->isAdmin) {
            return;
        }

        // Set root IDs
        if (!is_array(\BackendUser::getInstance()->iso_shipping_modules) || count(\BackendUser::getInstance()->iso_shipping_modules) < 1) // Can't use empty() because its an object property (using __get)
        {
            $root = array(0);
        } else {
            $root = \BackendUser::getInstance()->iso_shipping_modules;
        }

        $GLOBALS['TL_DCA']['tl_iso_shipping']['list']['sorting']['root'] = $root;

        // Check permissions to add shipping modules
        if (!\BackendUser::getInstance()->hasAccess('create', 'iso_shipping_modulep')) {
            $GLOBALS['TL_DCA']['tl_iso_shipping']['config']['closed'] = true;
            unset($GLOBALS['TL_DCA']['tl_iso_shipping']['list']['global_operations']['new']);
        }

        // Check current action
        switch (\Input::get('act')) {
            case 'create':
            case 'select':
                // Allow
                break;

            case 'edit':
            case 'toggle':
                // Dynamically add the record to the user profile
                if (!in_array(\Input::get('id'), $root)) {
                    $arrNew = $this->Session->get('new_records');

                    if (is_array($arrNew['tl_iso_shipping']) && in_array(\Input::get('id'), $arrNew['tl_iso_shipping'])) {
                        // Add permissions on user level
                        if (\BackendUser::getInstance()->inherit == 'custom' || !\BackendUser::getInstance()->groups[0]) {
                            $objUser        = \Database::getInstance()->prepare("SELECT iso_shipping_modules, iso_shipping_modulep FROM tl_user WHERE id=?")->limit(1)->execute(\BackendUser::getInstance()->id);
                            $arrPermissions = deserialize($objUser->iso_shipping_modulep);

                            if (is_array($arrPermissions) && in_array('create', $arrPermissions)) {
                                $arrAccess   = deserialize($objUser->iso_shipping_modules);
                                $arrAccess[] = \Input::get('id');

                                \Database::getInstance()->prepare("UPDATE tl_user SET iso_shipping_modules=? WHERE id=?")->execute(serialize($arrAccess), \BackendUser::getInstance()->id);
                            }
                        } // Add permissions on group level
                        elseif (\BackendUser::getInstance()->groups[0] > 0) {
                            $objGroup       = \Database::getInstance()->prepare("SELECT iso_shipping_modules, iso_shipping_modulep FROM tl_user_group WHERE id=?")->limit(1)->execute(\BackendUser::getInstance()->groups[0]);
                            $arrPermissions = deserialize($objGroup->iso_shipping_modulep);

                            if (is_array($arrPermissions) && in_array('create', $arrPermissions)) {
                                $arrAccess   = deserialize($objGroup->iso_shipping_modules);
                                $arrAccess[] = \Input::get('id');

                                \Database::getInstance()->prepare("UPDATE tl_user_group SET iso_shipping_modules=? WHERE id=?")->execute(serialize($arrAccess), \BackendUser::getInstance()->groups[0]);
                            }
                        }

                        // Add new element to the user object
                        $root[] = \Input::get('id');
                        \BackendUser::getInstance()->iso_shipping_modules = $root;
                    }
                }
            // No break;

            case 'copy':
            case 'delete':
            case 'show':
                if (!in_array(\Input::get('id'), $root) || (\Input::get('act') == 'delete' && !\BackendUser::getInstance()->hasAccess('delete', 'iso_shipping_modulep'))) {
                    \System::log('Not enough permissions to ' . \Input::get('act') . ' shipping module ID "' . \Input::get('id') . '"', __METHOD__, TL_ERROR);
                    \Controller::redirect('contao/main.php?act=error');
                }
                break;

            case 'editAll':
            case 'deleteAll':
            case 'overrideAll':
                $session = $this->Session->getData();
                if (\Input::get('act') == 'deleteAll' && !\BackendUser::getInstance()->hasAccess('delete', 'iso_shipping_modulep')) {
                    $session['CURRENT']['IDS'] = array();
                } else {
                    $session['CURRENT']['IDS'] = array_intersect($session['CURRENT']['IDS'], $root);
                }
                $this->Session->setData($session);
                break;

            default:
                if (strlen(\Input::get('act'))) {
                    \System::log('Not enough permissions to ' . \Input::get('act') . ' shipping modules', __METHOD__, TL_ERROR);
                    \Controller::redirect('contao/main.php?act=error');
                }
                break;
        }
    }


    /**
     * Return the copy shipping module button
     * @param array
     * @param string
     * @param string
     * @param string
     * @param string
     * @param string
     * @return string
     */
    public function copyShippingModule($row, $href, $label, $title, $icon, $attributes)
    {
        return (\BackendUser::getInstance()->isAdmin || \BackendUser::getInstance()->hasAccess('create', 'iso_shipping_modulep')) ? '<a href="' . $this->addToUrl($href . '&amp;id=' . $row['id']) . '" title="' . specialchars($title) . '"' . $attributes . '>' . \Image::getHtml($icon, $label) . '</a> ' : \Image::getHtml(preg_replace('/\.gif$/i', '_.gif', $icon)) . ' ';
    }


    /**
     * Return the delete shipping module button
     * @param array
     * @param string
     * @param string
     * @param string
     * @param string
     * @param string
     * @return string
     */
    public function deleteShippingModule($row, $href, $label, $title, $icon, $attributes)
    {
        return (\BackendUser::getInstance()->isAdmin || \BackendUser::getInstance()->hasAccess('delete', 'iso_shipping_modulep')) ? '<a href="' . $this->addToUrl($href . '&amp;id=' . $row['id']) . '" title="' . specialchars($title) . '"' . $attributes . '>' . \Image::getHtml($icon, $label) . '</a> ' : \Image::getHtml(preg_replace('/\.gif$/i', '_.gif', $icon)) . ' ';
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
            \Controller::redirect($this->getReferer());
        }

        if (!$row['enabled']) {
            $icon = 'invisible.gif';
        }

        if (!\BackendUser::getInstance()->isAdmin && !\BackendUser::getInstance()->hasAccess('tl_iso_shipping::enabled', 'alexf')) {
            return \Image::getHtml($icon) . ' ';
        }

        $href .= '&amp;tid=' . $row['id'] . '&amp;state=' . ($row['enabled'] ? '' : 1);

        return '<a href="' . $this->addToUrl($href) . '" title="' . specialchars($title) . '"' . $attributes . '>' . \Image::getHtml($icon, $label) . '</a> ';
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
        $this->checkPermission();

        // Check permissions to publish
        if (!\BackendUser::getInstance()->isAdmin && !\BackendUser::getInstance()->hasAccess('tl_iso_shipping::enabled', 'alexf')) {
            \System::log('Not enough permissions to enable/disable shipping method ID "' . $intId . '"', __METHOD__, TL_ERROR);
            \Controller::redirect('contao/main.php?act=error');
        }

        $objVersions = new \Versions('tl_iso_shipping', $intId);
        $objVersions->initialize();

        // Trigger the save_callback
        if (is_array($GLOBALS['TL_DCA']['tl_iso_shipping']['fields']['enabled']['save_callback'])) {
            foreach ($GLOBALS['TL_DCA']['tl_iso_shipping']['fields']['enabled']['save_callback'] as $callback) {
                $objCallback = \System::importStatic($callback[0]);
                $blnVisible  = $objCallback->$callback[1]($blnVisible, $this);
            }
        }

        // Update the database
        \Database::getInstance()->prepare("UPDATE tl_iso_shipping SET tstamp=" . time() . ", enabled='" . ($blnVisible ? 1 : '') . "' WHERE id=?")->execute($intId);

        $objVersions->create();
        \System::log('A new version of record "tl_iso_shipping.id=' . $intId . '" has been created' . $this->getParentEntries('tl_iso_shipping', $intId), __METHOD__, TL_GENERAL);
    }
}
