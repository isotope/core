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

namespace Isotope\Backend\Payment;

use Isotope\Model\Payment;
use Isotope\Model\Shipping;


class Callback extends \Backend
{

    /**
     * Check permissions to edit table tl_iso_payment
     */
    public function checkPermission()
    {
        // Do not run the permission check on other Isotope modules
        if (\Input::get('mod') != 'payment') {
            return;
        }

        $this->import('BackendUser', 'User');

        // Return if user is admin
        if ($this->User->isAdmin) {
            return;
        }

        // Set root IDs
        if (!is_array($this->User->iso_payment_modules) || count($this->User->iso_payment_modules) < 1) // Can't use empty() because its an object property (using __get)
        {
            $root = array(0);
        } else {
            $root = $this->User->iso_payment_modules;
        }

        $GLOBALS['TL_DCA']['tl_iso_payment']['list']['sorting']['root'] = $root;

        // Check permissions to add payment modules
        if (!$this->User->hasAccess('create', 'iso_payment_modulep')) {
            $GLOBALS['TL_DCA']['tl_iso_payment']['config']['closed'] = true;
            unset($GLOBALS['TL_DCA']['tl_iso_payment']['list']['global_operations']['new']);
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

                    if (is_array($arrNew['tl_iso_payment']) && in_array(\Input::get('id'), $arrNew['tl_iso_payment'])) {
                        // Add permissions on user level
                        if ($this->User->inherit == 'custom' || !$this->User->groups[0]) {
                            $objUser = \Database::getInstance()->prepare("SELECT iso_payment_modules, iso_payment_modulep FROM tl_user WHERE id=?")
                                ->limit(1)
                                ->execute($this->User->id);

                            $arrPermissions = deserialize($objUser->iso_payment_modulep);

                            if (is_array($arrPermissions) && in_array('create', $arrPermissions)) {
                                $arrAccess   = deserialize($objUser->iso_payment_modules);
                                $arrAccess[] = \Input::get('id');

                                \Database::getInstance()->prepare("UPDATE tl_user SET iso_payment_modules=? WHERE id=?")
                                    ->execute(serialize($arrAccess), $this->User->id);
                            }
                        } // Add permissions on group level
                        elseif ($this->User->groups[0] > 0) {
                            $objGroup = \Database::getInstance()->prepare("SELECT iso_payment_modules, iso_payment_modulep FROM tl_user_group WHERE id=?")
                                ->limit(1)
                                ->execute($this->User->groups[0]);

                            $arrPermissions = deserialize($objGroup->iso_payment_modulep);

                            if (is_array($arrPermissions) && in_array('create', $arrPermissions)) {
                                $arrAccess   = deserialize($objGroup->iso_payment_modules);
                                $arrAccess[] = \Input::get('id');

                                \Database::getInstance()->prepare("UPDATE tl_user_group SET iso_payment_modules=? WHERE id=?")
                                    ->execute(serialize($arrAccess), $this->User->groups[0]);
                            }
                        }

                        // Add new element to the user object
                        $root[]                          = \Input::get('id');
                        $this->User->iso_payment_modules = $root;
                    }
                }
            // No break;

            case 'copy':
            case 'delete':
            case 'show':
                if (!in_array(\Input::get('id'), $root) || (\Input::get('act') == 'delete' && !$this->User->hasAccess('delete', 'iso_payment_modulep'))) {
                    \System::log('Not enough permissions to ' . \Input::get('act') . ' payment module ID "' . \Input::get('id') . '"', __METHOD__, TL_ERROR);
                    \Controller::redirect('contao/main.php?act=error');
                }
                break;

            case 'editAll':
            case 'deleteAll':
            case 'overrideAll':
                $session = $this->Session->getData();
                if (\Input::get('act') == 'deleteAll' && !$this->User->hasAccess('delete', 'iso_payment_modulep')) {
                    $session['CURRENT']['IDS'] = array();
                } else {
                    $session['CURRENT']['IDS'] = array_intersect($session['CURRENT']['IDS'], $root);
                }
                $this->Session->setData($session);
                break;

            default:
                if (strlen(\Input::get('act'))) {
                    \System::log('Not enough permissions to ' . \Input::get('act') . ' payment modules', __METHOD__, TL_ERROR);
                    \Controller::redirect('contao/main.php?act=error');
                }
                break;
        }
    }


    /**
     * Get allowed CC types and return them as array
     *
     * @param \DataContainer $dc
     *
     * @return array
     * @deprecated Deprecated since 2.2, to be removed in 3.0. Create your own DCA field instead.
     */
    public function getAllowedCCTypes(\DataContainer $dc)
    {
        $arrCCTypes = array();

        /** @type Payment $objPayment */
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
        return ($this->User->isAdmin || $this->User->hasAccess('create', 'iso_payment_modulep')) ? '<a href="' . $this->addToUrl($href . '&amp;id=' . $row['id']) . '" title="' . specialchars($title) . '"' . $attributes . '>' . \Image::getHtml($icon, $label) . '</a> ' : \Image::getHtml(preg_replace('/\.gif$/i', '_.gif', $icon)) . ' ';
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
        return ($this->User->isAdmin || $this->User->hasAccess('delete', 'iso_payment_modulep')) ? '<a href="' . $this->addToUrl($href . '&amp;id=' . $row['id']) . '" title="' . specialchars($title) . '"' . $attributes . '>' . \Image::getHtml($icon, $label) . '</a> ' : \Image::getHtml(preg_replace('/\.gif$/i', '_.gif', $icon)) . ' ';
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
        if (strlen(\Input::get('tid'))) {
            $this->toggleVisibility(\Input::get('tid'), (\Input::get('state') == 1));
            \Controller::redirect($this->getReferer());
        }

        if (!$row['enabled']) {
            $icon = 'invisible.gif';
        }

        if (!\BackendUser::getInstance()->isAdmin && !\BackendUser::getInstance()->hasAccess('tl_iso_payment::enabled', 'alexf')) {
            return \Image::getHtml($icon) . ' ';
        }

        $href .= '&amp;tid=' . $row['id'] . '&amp;state=' . ($row['enabled'] ? '' : 1);

        return '<a href="' . $this->addToUrl($href) . '" title="' . specialchars($title) . '"' . $attributes . '>' . \Image::getHtml($icon, $label) . '</a> ';
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
        \Input::setGet('id', $intId);
        \Input::setGet('act', 'toggle');
        $this->checkPermission();

        // Check permissions to publish
        if (!\BackendUser::getInstance()->isAdmin && !\BackendUser::getInstance()->hasAccess('tl_iso_payment::enabled', 'alexf')) {
            \System::log('Not enough permissions to enable/disable payment method ID "' . $intId . '"', __METHOD__, TL_ERROR);
            \Controller::redirect('contao/main.php?act=error');
        }

        $objVersions = new \Versions('tl_iso_payment', $intId);
        $objVersions->initialize();

        // Trigger the save_callback
        if (is_array($GLOBALS['TL_DCA']['tl_iso_payment']['fields']['enabled']['save_callback'])) {
            foreach ($GLOBALS['TL_DCA']['tl_iso_payment']['fields']['enabled']['save_callback'] as $callback) {
                $objCallback = \System::importStatic($callback[0]);
                $blnVisible  = $objCallback->$callback[1]($blnVisible, $this);
            }
        }

        // Update the database
        \Database::getInstance()->prepare("UPDATE tl_iso_payment SET tstamp=". time() .", enabled='" . ($blnVisible ? 1 : '') . "' WHERE id=?")
                                ->execute($intId);

        $objVersions->create();
        \System::log('A new version of record "tl_iso_payment.id=' . $intId . '" has been created' . $this->getParentEntries('tl_iso_payment', $intId), __METHOD__, TL_GENERAL);
    }
}
