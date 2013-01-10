<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2012 Isotope eCommerce Workgroup
 *
 * @package    Isotope
 * @link       http://www.isotopeecommerce.com
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 *
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
 */

namespace Isotope;


/**
 * Class tl_iso_payment_modules
 * Provide miscellaneous methods that are used by the data configuration array.
 */
class tl_iso_payment_modules extends \Backend
{

    /**
     * Check permissions to edit table tl_iso_payment_modules
     * @return void
     */
    public function checkPermission()
    {
        // Do not run the permission check on other Isotope modules
        if (\Input::get('mod') != 'payment')
        {
            return;
        }

        $this->import('BackendUser', 'User');

        // Return if user is admin
        if ($this->User->isAdmin)
        {
            return;
        }

        // Set root IDs
        if (!is_array($this->User->iso_payment_modules) || count($this->User->iso_payment_modules) < 1) // Can't use empty() because its an object property (using __get)
        {
            $root = array(0);
        }
        else
        {
            $root = $this->User->iso_payment_modules;
        }

        $GLOBALS['TL_DCA']['tl_iso_payment_modules']['list']['sorting']['root'] = $root;

        // Check permissions to add payment modules
        if (!$this->User->hasAccess('create', 'iso_payment_modulep'))
        {
            $GLOBALS['TL_DCA']['tl_iso_payment_modules']['config']['closed'] = true;
            unset($GLOBALS['TL_DCA']['tl_iso_payment_modules']['list']['global_operations']['new']);
        }

        // Check current action
        switch (\Input::get('act'))
        {
            case 'create':
            case 'select':
                // Allow
                break;

            case 'edit':
                // Dynamically add the record to the user profile
                if (!in_array(\Input::get('id'), $root))
                {
                    $arrNew = $this->Session->get('new_records');

                    if (is_array($arrNew['tl_iso_payment_modules']) && in_array(\Input::get('id'), $arrNew['tl_iso_payment_modules']))
                    {
                        // Add permissions on user level
                        if ($this->User->inherit == 'custom' || !$this->User->groups[0])
                        {
                            $objUser = $this->Database->prepare("SELECT iso_payment_modules, iso_payment_modulep FROM tl_user WHERE id=?")
                                                       ->limit(1)
                                                       ->execute($this->User->id);

                            $arrPermissions = deserialize($objUser->iso_payment_modulep);

                            if (is_array($arrPermissions) && in_array('create', $arrPermissions))
                            {
                                $arrAccess = deserialize($objUser->iso_payment_modules);
                                $arrAccess[] = \Input::get('id');

                                $this->Database->prepare("UPDATE tl_user SET iso_payment_modules=? WHERE id=?")
                                               ->execute(serialize($arrAccess), $this->User->id);
                            }
                        }

                        // Add permissions on group level
                        elseif ($this->User->groups[0] > 0)
                        {
                            $objGroup = $this->Database->prepare("SELECT iso_payment_modules, iso_payment_modulep FROM tl_user_group WHERE id=?")
                                                       ->limit(1)
                                                       ->execute($this->User->groups[0]);

                            $arrPermissions = deserialize($objGroup->iso_payment_modulep);

                            if (is_array($arrPermissions) && in_array('create', $arrPermissions))
                            {
                                $arrAccess = deserialize($objGroup->iso_payment_modules);
                                $arrAccess[] = \Input::get('id');

                                $this->Database->prepare("UPDATE tl_user_group SET iso_payment_modules=? WHERE id=?")
                                               ->execute(serialize($arrAccess), $this->User->groups[0]);
                            }
                        }

                        // Add new element to the user object
                        $root[] = \Input::get('id');
                        $this->User->iso_payment_modules = $root;
                    }
                }
                // No break;

            case 'copy':
            case 'delete':
            case 'show':
                if (!in_array(\Input::get('id'), $root) || (\Input::get('act') == 'delete' && !$this->User->hasAccess('delete', 'iso_payment_modulep')))
                {
                    $this->log('Not enough permissions to '.\Input::get('act').' payment module ID "'.\Input::get('id').'"', __METHOD__, TL_ERROR);
                    $this->redirect('contao/main.php?act=error');
                }
                break;

            case 'editAll':
            case 'deleteAll':
            case 'overrideAll':
                $session = $this->Session->getData();
                if (\Input::get('act') == 'deleteAll' && !$this->User->hasAccess('delete', 'iso_payment_modulep'))
                {
                    $session['CURRENT']['IDS'] = array();
                }
                else
                {
                    $session['CURRENT']['IDS'] = array_intersect($session['CURRENT']['IDS'], $root);
                }
                $this->Session->setData($session);
                break;

            default:
                if (strlen(\Input::get('act')))
                {
                    $this->log('Not enough permissions to '.\Input::get('act').' payment modules', __METHOD__, TL_ERROR);
                    $this->redirect('contao/main.php?act=error');
                }
                break;
        }
    }


    /**
     * Get allowed CC types and return them as array
     * @param DataContainer
     * @return array
     */
    public function getAllowedCCTypes(\DataContainer $dc)
    {
        $arrCCTypes = array();
        $objPayment = $this->Database->prepare("SELECT * FROM tl_iso_payment_modules WHERE id=?")->limit(1)->execute($dc->id);

        if ($objPayment->numRows) {
            try {
                $objMethod = \Isotope\Factory\Payment::build($objPayment->type, $objPayment->row());

                foreach ($objMethod->getAllowedCCTypes() as $type)
                {
                    $arrCCTypes[$type] = $GLOBALS['ISO_LANG']['CCT'][$type];
                }

                return $arrCCTypes;

            } catch (Exception $e) {}
        }

        return $arrCCTypes;
    }


    /**
     * Load shipping modules into the DCA (options_callback would not work due to numeric array keys)
     * @param object
     * @return void
     */
    public function loadShippingModules($dc)
    {
        $arrModules = array(-1=>$GLOBALS['TL_LANG']['tl_iso_payment_modules']['no_shipping']);
        $objShippings = $this->Database->execute("SELECT * FROM tl_iso_shipping_modules ORDER BY name");

        while ($objShippings->next())
        {
            $arrModules[$objShippings->id] = $objShippings->name;
        }

        $GLOBALS['TL_DCA']['tl_iso_payment_modules']['fields']['shipping_modules']['options'] = array_keys($arrModules);
        $GLOBALS['TL_DCA']['tl_iso_payment_modules']['fields']['shipping_modules']['reference'] = $arrModules;
    }


    /**
     * Return the copy payment module button
     * @param array
     * @param string
     * @param string
     * @param string
     * @param string
     * @param string
     * @return string
     */
    public function copyPaymentModule($row, $href, $label, $title, $icon, $attributes)
    {
        return ($this->User->isAdmin || $this->User->hasAccess('create', 'iso_payment_modulep')) ? '<a href="'.$this->addToUrl($href.'&amp;id='.$row['id']).'" title="'.specialchars($title).'"'.$attributes.'>'.$this->generateImage($icon, $label).'</a> ' : $this->generateImage(preg_replace('/\.gif$/i', '_.gif', $icon)).' ';
    }


    /**
     * Return the delete payment module button
     * @param array
     * @param string
     * @param string
     * @param string
     * @param string
     * @param string
     * @return string
     */
    public function deletePaymentModule($row, $href, $label, $title, $icon, $attributes)
    {
        return ($this->User->isAdmin || $this->User->hasAccess('delete', 'iso_payment_modulep')) ? '<a href="'.$this->addToUrl($href.'&amp;id='.$row['id']).'" title="'.specialchars($title).'"'.$attributes.'>'.$this->generateImage($icon, $label).'</a> ' : $this->generateImage(preg_replace('/\.gif$/i', '_.gif', $icon)).' ';
    }
}
