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

        if (\BackendUser::getInstance()->isAdmin)
        {
            return;
        }

        // Set root IDs
        if (!is_array(\BackendUser::getInstance()->iso_shipping_modules) || count(\BackendUser::getInstance()->iso_shipping_modules) < 1) // Can't use empty() because its an object property (using __get)
        {
            $root = array(0);
        }
        else
        {
            $root = \BackendUser::getInstance()->iso_shipping_modules;
        }

        $GLOBALS['TL_DCA']['tl_iso_shipping_modules']['list']['sorting']['root'] = $root;

        // Check permissions to add shipping modules
        if (!\BackendUser::getInstance()->hasAccess('create', 'iso_shipping_modulep'))
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
                        if (\BackendUser::getInstance()->inherit == 'custom' || !\BackendUser::getInstance()->groups[0])
                        {
                            $objUser = $this->Database->prepare("SELECT iso_shipping_modules, iso_shipping_modulep FROM tl_user WHERE id=?")
                                                       ->limit(1)
                                                       ->execute(\BackendUser::getInstance()->id);

                            $arrPermissions = deserialize($objUser->iso_shipping_modulep);

                            if (is_array($arrPermissions) && in_array('create', $arrPermissions))
                            {
                                $arrAccess = deserialize($objUser->iso_shipping_modules);
                                $arrAccess[] = \Input::get('id');

                                $this->Database->prepare("UPDATE tl_user SET iso_shipping_modules=? WHERE id=?")
                                               ->execute(serialize($arrAccess), \BackendUser::getInstance()->id);
                            }
                        }

                        // Add permissions on group level
                        elseif (\BackendUser::getInstance()->groups[0] > 0)
                        {
                            $objGroup = $this->Database->prepare("SELECT iso_shipping_modules, iso_shipping_modulep FROM tl_user_group WHERE id=?")
                                                       ->limit(1)
                                                       ->execute(\BackendUser::getInstance()->groups[0]);

                            $arrPermissions = deserialize($objGroup->iso_shipping_modulep);

                            if (is_array($arrPermissions) && in_array('create', $arrPermissions))
                            {
                                $arrAccess = deserialize($objGroup->iso_shipping_modules);
                                $arrAccess[] = \Input::get('id');

                                $this->Database->prepare("UPDATE tl_user_group SET iso_shipping_modules=? WHERE id=?")
                                               ->execute(serialize($arrAccess), \BackendUser::getInstance()->groups[0]);
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
                if (!in_array(\Input::get('id'), $root) || (\Input::get('act') == 'delete' && !\BackendUser::getInstance()->hasAccess('delete', 'iso_shipping_modulep')))
                {
                    $this->log('Not enough permissions to '.\Input::get('act').' shipping module ID "'.\Input::get('id').'"', __METHOD__, TL_ERROR);
                    $this->redirect('contao/main.php?act=error');
                }
                break;

            case 'editAll':
            case 'deleteAll':
            case 'overrideAll':
                $session = $this->Session->getData();
                if (\Input::get('act') == 'deleteAll' && !\BackendUser::getInstance()->hasAccess('delete', 'iso_shipping_modulep'))
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
        return (\BackendUser::getInstance()->isAdmin || \BackendUser::getInstance()->hasAccess('create', 'iso_shipping_modulep')) ? '<a href="'.$this->addToUrl($href.'&amp;id='.$row['id']).'" title="'.specialchars($title).'"'.$attributes.'>'.$this->generateImage($icon, $label).'</a> ' : $this->generateImage(preg_replace('/\.gif$/i', '_.gif', $icon)).' ';
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
        return (\BackendUser::getInstance()->isAdmin || \BackendUser::getInstance()->hasAccess('delete', 'iso_shipping_modulep')) ? '<a href="'.$this->addToUrl($href.'&amp;id='.$row['id']).'" title="'.specialchars($title).'"'.$attributes.'>'.$this->generateImage($icon, $label).'</a> ' : $this->generateImage(preg_replace('/\.gif$/i', '_.gif', $icon)).' ';
    }
}
