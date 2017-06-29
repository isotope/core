<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2016 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Backend\Shipping;


use Isotope\Backend\Permission;

class Callback extends Permission
{

    /**
     * Check permissions to edit table tl_iso_shipping
     * @return void
     */
    public function checkPermission()
    {
        // Do not run the permission check on other Isotope modules
        if ('shipping' !== \Input::get('mod')) {
            return;
        }

        if (\BackendUser::getInstance()->isAdmin) {
            return;
        }

        // Set root IDs
        if (!is_array(\BackendUser::getInstance()->iso_shipping_modules)
            || 0 === count(\BackendUser::getInstance()->iso_shipping_modules)
        ) {
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

            /** @noinspection PhpMissingBreakStatementInspection */
            case 'edit':
                // Dynamically add the record to the user profile
                if (!in_array(\Input::get('id'), $root)
                    && $this->addNewRecordPermissions(\Input::get('id'), 'tl_iso_shipping', 'iso_shipping_modules', 'iso_shipping_modulep')
                ) {
                    $root[] = \Input::get('id');
                    \BackendUser::getInstance()->iso_shipping_modules = $root;
                }
            // No break;

            case 'toggle':
            case 'copy':
            case 'delete':
            case 'show':
                if (!in_array(\Input::get('id'), $root)
                    || ('delete' === \Input::get('act')
                        && !\BackendUser::getInstance()->hasAccess('delete', 'iso_shipping_modulep')
                    )
                ) {
                    \System::log('Not enough permissions to ' . \Input::get('act') . ' shipping module ID "' . \Input::get('id') . '"', __METHOD__, TL_ERROR);
                    \Controller::redirect('contao/main.php?act=error');
                }
                break;

            case 'editAll':
            case 'deleteAll':
            case 'overrideAll':
                $session = $this->Session->getData();
                if ('deleteAll' === \Input::get('act') && !\BackendUser::getInstance()->hasAccess('delete', 'iso_shipping_modulep')) {
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
        return (\BackendUser::getInstance()->isAdmin || \BackendUser::getInstance()->hasAccess('create', 'iso_shipping_modulep')) ? '<a href="' . \Backend::addToUrl($href . '&amp;id=' . $row['id']) . '" title="' . specialchars($title) . '"' . $attributes . '>' . \Image::getHtml($icon, $label) . '</a> ' : \Image::getHtml(preg_replace('/\.gif$/i', '_.gif', $icon)) . ' ';
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
        return (\BackendUser::getInstance()->isAdmin || \BackendUser::getInstance()->hasAccess('delete', 'iso_shipping_modulep')) ? '<a href="' . \Backend::addToUrl($href . '&amp;id=' . $row['id']) . '" title="' . specialchars($title) . '"' . $attributes . '>' . \Image::getHtml($icon, $label) . '</a> ' : \Image::getHtml(preg_replace('/\.gif$/i', '_.gif', $icon)) . ' ';
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
            $this->toggleVisibility(\Input::get('tid'), \Input::get('state') == 1);
            \Controller::redirect(\System::getReferer());
        }

        if (!$row['enabled']) {
            $icon = 'invisible.gif';
        }

        if (!\BackendUser::getInstance()->isAdmin
            && !\BackendUser::getInstance()->hasAccess('tl_iso_shipping::enabled', 'alexf')
        ) {
            return \Image::getHtml($icon) . ' ';
        }

        $href .= '&amp;tid=' . $row['id'] . '&amp;state=' . ($row['enabled'] ? '' : 1);

        return '<a href="' . \Backend::addToUrl($href) . '" title="' . specialchars($title) . '"' . $attributes . '>' . \Image::getHtml($icon, $label) . '</a> ';
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
        if (!\BackendUser::getInstance()->isAdmin
            && !\BackendUser::getInstance()->hasAccess('tl_iso_shipping::enabled', 'alexf')
        ) {
            \System::log('Not enough permissions to enable/disable shipping method ID "' . $intId . '"', __METHOD__, TL_ERROR);
            \Controller::redirect('contao/main.php?act=error');
        }

        $objVersions = new \Versions('tl_iso_shipping', $intId);
        $objVersions->initialize();

        // Trigger the save_callback
        if (is_array($GLOBALS['TL_DCA']['tl_iso_shipping']['fields']['enabled']['save_callback'])) {
            foreach ($GLOBALS['TL_DCA']['tl_iso_shipping']['fields']['enabled']['save_callback'] as $callback) {
                $blnVisible = \System::importStatic($callback[0])->{$callback[1]}($blnVisible, $this);
            }
        }

        // Update the database
        \Database::getInstance()->prepare('UPDATE tl_iso_shipping SET tstamp=' . time() . ", enabled='" . ($blnVisible ? 1 : '') . "' WHERE id=?")->execute($intId);

        $objVersions->create();
        \System::log('A new version of record "tl_iso_shipping.id=' . $intId . '" has been created' . $this->getParentEntries('tl_iso_shipping', $intId), __METHOD__, TL_GENERAL);
    }
}
