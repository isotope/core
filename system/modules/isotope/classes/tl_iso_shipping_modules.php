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
 * Class tl_iso_shipping_modules
 * Provide miscellaneous methods that are used by the data configuration array.
 */
class tl_iso_shipping_modules extends \Backend
{

    /**
     * Check permissions to edit table tl_iso_shipping_modules
     * @return void
     */
    public function checkPermission()
    {
        // Do not run the permission check on other Isotope modules
        if (\Input::get('mod') != 'shipping')
        {
            return;
        }

        $this->import('BackendUser', 'User');

        if ($this->User->isAdmin)
        {
            return;
        }

        // Set root IDs
        if (!is_array($this->User->iso_shipping_modules) || count($this->User->iso_shipping_modules) < 1) // Can't use empty() because its an object property (using __get)
        {
            $root = array(0);
        }
        else
        {
            $root = $this->User->iso_shipping_modules;
        }

        $GLOBALS['TL_DCA']['tl_iso_shipping_modules']['list']['sorting']['root'] = $root;

        // Check permissions to add shipping modules
        if (!$this->User->hasAccess('create', 'iso_shipping_modulep'))
        {
            $GLOBALS['TL_DCA']['tl_iso_shipping_modules']['config']['closed'] = true;
            unset($GLOBALS['TL_DCA']['tl_iso_shipping_modules']['list']['global_operations']['new']);
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

                    if (is_array($arrNew['tl_iso_shipping_modules']) && in_array(\Input::get('id'), $arrNew['tl_iso_shipping_modules']))
                    {
                        // Add permissions on user level
                        if ($this->User->inherit == 'custom' || !$this->User->groups[0])
                        {
                            $objUser = $this->Database->prepare("SELECT iso_shipping_modules, iso_shipping_modulep FROM tl_user WHERE id=?")
                                                       ->limit(1)
                                                       ->execute($this->User->id);

                            $arrPermissions = deserialize($objUser->iso_shipping_modulep);

                            if (is_array($arrPermissions) && in_array('create', $arrPermissions))
                            {
                                $arrAccess = deserialize($objUser->iso_shipping_modules);
                                $arrAccess[] = \Input::get('id');

                                $this->Database->prepare("UPDATE tl_user SET iso_shipping_modules=? WHERE id=?")
                                               ->execute(serialize($arrAccess), $this->User->id);
                            }
                        }

                        // Add permissions on group level
                        elseif ($this->User->groups[0] > 0)
                        {
                            $objGroup = $this->Database->prepare("SELECT iso_shipping_modules, iso_shipping_modulep FROM tl_user_group WHERE id=?")
                                                       ->limit(1)
                                                       ->execute($this->User->groups[0]);

                            $arrPermissions = deserialize($objGroup->iso_shipping_modulep);

                            if (is_array($arrPermissions) && in_array('create', $arrPermissions))
                            {
                                $arrAccess = deserialize($objGroup->iso_shipping_modules);
                                $arrAccess[] = \Input::get('id');

                                $this->Database->prepare("UPDATE tl_user_group SET iso_shipping_modules=? WHERE id=?")
                                               ->execute(serialize($arrAccess), $this->User->groups[0]);
                            }
                        }

                        // Add new element to the user object
                        $root[] = \Input::get('id');
                        $this->User->iso_shipping_modules = $root;
                    }
                }
                // No break;

            case 'copy':
            case 'delete':
            case 'show':
                if (!in_array(\Input::get('id'), $root) || (\Input::get('act') == 'delete' && !$this->User->hasAccess('delete', 'iso_shipping_modulep')))
                {
                    $this->log('Not enough permissions to '.\Input::get('act').' shipping module ID "'.\Input::get('id').'"', __METHOD__, TL_ERROR);
                    $this->redirect('contao/main.php?act=error');
                }
                break;

            case 'editAll':
            case 'deleteAll':
            case 'overrideAll':
                $session = $this->Session->getData();
                if (\Input::get('act') == 'deleteAll' && !$this->User->hasAccess('delete', 'iso_shipping_modulep'))
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
                    $this->log('Not enough permissions to '.\Input::get('act').' shipping modules', __METHOD__, TL_ERROR);
                    $this->redirect('contao/main.php?act=error');
                }
                break;
        }
    }


    /**
     * Get a list of all shipping modules available
     * @return array
     */
    public function getModules()
    {
        $arrModules = array();

        if (is_array($GLOBALS['ISO_SHIP']) && !empty($GLOBALS['ISO_SHIP']))
        {
            foreach ($GLOBALS['ISO_SHIP'] as $module => $class)
            {
                $arrModules[$module] = (strlen($GLOBALS['ISO_LANG']['SHIP'][$module][0]) ? $GLOBALS['ISO_LANG']['SHIP'][$module][0] : $module);
            }
        }

        return $arrModules;
    }


    /**
     * Callback for options button
     * @param array
     * @param string
     * @param string
     * @param string
     * @param string
     * @param string
     * @return string
     */
    public function optionsButton($row, $href, $label, $title, $icon, $attributes)
    {
        switch ($row['type'])
        {
            case 'order_total':
            case 'weight_total':
                return '<a href="'.$this->addToUrl($href.'&amp;id='.$row['id']).'" title="'.specialchars($title).'"'.$attributes.'>'.$this->generateImage($icon, $label).'</a> ';

            default:
                return '';
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
        return ($this->User->isAdmin || $this->User->hasAccess('create', 'iso_shipping_modulep')) ? '<a href="'.$this->addToUrl($href.'&amp;id='.$row['id']).'" title="'.specialchars($title).'"'.$attributes.'>'.$this->generateImage($icon, $label).'</a> ' : $this->generateImage(preg_replace('/\.gif$/i', '_.gif', $icon)).' ';
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
        return ($this->User->isAdmin || $this->User->hasAccess('delete', 'iso_shipping_modulep')) ? '<a href="'.$this->addToUrl($href.'&amp;id='.$row['id']).'" title="'.specialchars($title).'"'.$attributes.'>'.$this->generateImage($icon, $label).'</a> ' : $this->generateImage(preg_replace('/\.gif$/i', '_.gif', $icon)).' ';
    }
}
