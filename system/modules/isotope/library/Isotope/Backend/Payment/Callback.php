<?php

/*
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009 - 2019 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Backend\Payment;

use Contao\Backend;
use Contao\BackendUser;
use Contao\Controller;
use Contao\CoreBundle\Exception\AccessDeniedException;
use Contao\Database;
use Contao\DataContainer;
use Contao\Image;
use Contao\Input;
use Contao\Session;
use Contao\StringUtil;
use Contao\System;
use Contao\Versions;
use Isotope\Backend\Permission;
use Isotope\Model\Payment;
use Isotope\Model\Shipping;


class Callback extends Permission
{

    /**
     * Check permissions to edit table tl_iso_payment
     */
    public function checkPermission()
    {
        // Do not run the permission check on other Isotope modules
        if ('payment' !== Input::get('mod')) {
            return;
        }

        $user = BackendUser::getInstance();

        // Return if user is admin
        if ($user->isAdmin) {
            return;
        }

        // Set root IDs
        if (!\is_array($user->iso_payment_modules) || \count($user->iso_payment_modules) < 1) // Can't use empty() because its an object property (using __get)
        {
            $root = array(0);
        } else {
            $root = $user->iso_payment_modules;
        }

        $GLOBALS['TL_DCA']['tl_iso_payment']['list']['sorting']['root'] = $root;

        // Check permissions to add payment modules
        if (!$user->hasAccess('create', 'iso_payment_modulep')) {
            $GLOBALS['TL_DCA']['tl_iso_payment']['config']['closed'] = true;
            unset($GLOBALS['TL_DCA']['tl_iso_payment']['list']['global_operations']['new']);
        }

        // Check current action
        switch (Input::get('act')) {
            case 'create':
            case 'select':
                // Allow
                break;

            /** @noinspection PhpMissingBreakStatementInspection */
            case 'edit':
                // Dynamically add the record to the user profile
                if (!\in_array(Input::get('id'), $root)
                    && $this->addNewRecordPermissions(Input::get('id'), 'tl_iso_payment', 'iso_payment_modules', 'iso_payment_modulep')
                ) {
                    $root[]                          = Input::get('id');
                    $user->iso_payment_modules = $root;
                }
            // No break;

            case 'toggle':
            case 'copy':
            case 'delete':
            case 'show':
                if (!\in_array(Input::get('id'), $root) || ('delete' === Input::get('act') && !$user->hasAccess('delete', 'iso_payment_modulep'))) {
                    throw new AccessDeniedException('Not enough permissions to ' . Input::get('act') . ' payment module ID "' . Input::get('id') . '"');
                }
                break;

            case 'editAll':
            case 'deleteAll':
            case 'overrideAll':
                $session = Session::getInstance()->getData();
                if ('deleteAll' === Input::get('act') && !$user->hasAccess('delete', 'iso_payment_modulep')) {
                    $session['CURRENT']['IDS'] = array();
                } else {
                    $session['CURRENT']['IDS'] = array_intersect($session['CURRENT']['IDS'], $root);
                }
                Session::getInstance()->setData($session);
                break;

            default:
                if (\strlen(Input::get('act'))) {
                    throw new AccessDeniedException('Not enough permissions to ' . Input::get('act') . ' payment modules');
                }
                break;
        }
    }


    /**
     * Get allowed CC types and return them as array
     *
     * @return array
     * @deprecated Deprecated since 2.2, to be removed in 3.0. Create your own DCA field instead.
     */
    public function getAllowedCCTypes(DataContainer $dc)
    {
        $arrCCTypes = array();

        /** @var Payment $objPayment */
        if (($objPayment = Payment::findByPk($dc->id)) !== null) {

            try {
                foreach ($objPayment->getAllowedCCTypes() as $type) {
                    $arrCCTypes[$type] = $GLOBALS['TL_LANG']['CCT'][$type];
                }

                return $arrCCTypes;

            } catch (\Exception $e) {
            }
        }

        return $arrCCTypes;
    }


    /**
     * Load shipping modules into the DCA (options_callback would not work due to numeric array keys)
     *
     * @param object $dc
     */
    public function loadShippingModules($dc)
    {
        $arrModules   = array(-1 => $GLOBALS['TL_LANG'][$dc->table]['no_shipping']);
        $objShippings = Shipping::findAll(array('order' => 'name'));

        if (null !== $objShippings) {
            foreach ($objShippings as $objShipping) {
                $arrModules[$objShipping->id] = $objShipping->name;
            }
        }

        $GLOBALS['TL_DCA'][$dc->table]['fields']['shipping_modules']['options']   = array_keys($arrModules);
        $GLOBALS['TL_DCA'][$dc->table]['fields']['shipping_modules']['reference'] = $arrModules;
    }


    /**
     * Return the copy payment module button
     *
     * @param array  $row
     * @param string $href
     * @param string $label
     * @param string $title
     * @param string $icon
     * @param string $attributes
     *
     * @return string
     */
    public function copyPaymentModule($row, $href, $label, $title, $icon, $attributes)
    {
        return (BackendUser::getInstance()->isAdmin || BackendUser::getInstance()->hasAccess('create', 'iso_payment_modulep')) ? '<a href="' . Backend::addToUrl($href . '&amp;id=' . $row['id']) . '" title="' . StringUtil::specialchars($title) . '"' . $attributes . '>' . Image::getHtml($icon, $label) . '</a> ' : Image::getHtml(preg_replace('/\.svg$/i', '_.svg', $icon)) . ' ';
    }


    /**
     * Return the delete payment module button
     *
     * @param array  $row
     * @param string $href
     * @param string $label
     * @param string $title
     * @param string $icon
     * @param string $attributes
     *
     * @return string
     */
    public function deletePaymentModule($row, $href, $label, $title, $icon, $attributes)
    {
        return (BackendUser::getInstance()->isAdmin || BackendUser::getInstance()->hasAccess('delete', 'iso_payment_modulep')) ? '<a href="' . Backend::addToUrl($href . '&amp;id=' . $row['id']) . '" title="' . StringUtil::specialchars($title) . '"' . $attributes . '>' . Image::getHtml($icon, $label) . '</a> ' : Image::getHtml(preg_replace('/\.svg$/i', '_.svg', $icon)) . ' ';
    }


    /**
     * Return the "toggle visibility" button
     *
     * @param array  $row
     * @param string $href
     * @param string $label
     * @param string $title
     * @param string $icon
     * @param string $attributes
     *
     * @return string
     */
    public function toggleIcon($row, $href, $label, $title, $icon, $attributes)
    {
        if (\strlen(Input::get('tid'))) {
            $this->toggleVisibility(Input::get('tid'), Input::get('state') == 1);
            Controller::redirect(System::getReferer());
        }

        if (!$row['enabled']) {
            $icon = 'invisible.svg';
        }

        if (!BackendUser::getInstance()->isAdmin && !BackendUser::getInstance()->hasAccess('tl_iso_payment::enabled', 'alexf')) {
            return Image::getHtml($icon) . ' ';
        }

        $href .= '&amp;tid=' . $row['id'] . '&amp;state=' . ($row['enabled'] ? '' : 1);

        return '<a href="' . Backend::addToUrl($href) . '" title="' . StringUtil::specialchars($title) . '"' . $attributes . '>' . Image::getHtml($icon, $label) . '</a> ';
    }


    /**
     * Disable/enable a user group
     *
     * @param int $intId
     * @param bool $blnVisible
     */
    public function toggleVisibility($intId, $blnVisible)
    {
        // Check permissions to edit
        Input::setGet('id', $intId);
        Input::setGet('act', 'toggle');
        $this->checkPermission();

        // Check permissions to publish
        if (!BackendUser::getInstance()->isAdmin && !BackendUser::getInstance()->hasAccess('tl_iso_payment::enabled', 'alexf')) {
            throw new AccessDeniedException('Not enough permissions to enable/disable payment method ID "' . $intId . '"');
        }

        $objVersions = new Versions('tl_iso_payment', $intId);
        $objVersions->initialize();

        // Trigger the save_callback
        if (\is_array($GLOBALS['TL_DCA']['tl_iso_payment']['fields']['enabled']['save_callback'] ?? null)) {
            foreach ($GLOBALS['TL_DCA']['tl_iso_payment']['fields']['enabled']['save_callback'] as $callback) {
                $blnVisible = System::importStatic($callback[0])->{$callback[1]}($blnVisible, $this);
            }
        }

        // Update the database
        Database::getInstance()->prepare("UPDATE tl_iso_payment SET tstamp=". time() .", enabled='" . ($blnVisible ? 1 : '') . "' WHERE id=?")
                                ->execute($intId);

        $objVersions->create();
        System::log('A new version of record "tl_iso_payment.id=' . $intId . '" has been created' . $this->getParentEntries('tl_iso_payment', $intId), __METHOD__, TL_GENERAL);
    }
}
