<?php

/*
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009 - 2019 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Backend\TaxClass;


use Contao\Backend;
use Contao\BackendUser;
use Contao\CoreBundle\Exception\AccessDeniedException;
use Contao\Image;
use Contao\Input;
use Contao\Session;
use Contao\StringUtil;
use Isotope\Backend\Permission;

class Callback extends Permission
{

    /**
     * Check permissions to edit table tl_iso_tax_class
     * @return void
     */
    public function checkPermission()
    {
        // Do not run the permission check on other Isotope modules
        if (Input::get('mod') != 'tax_class') {
            return;
        }

        $this->import('BackendUser', 'User');
        $user = BackendUser::getInstance();

        if ($user->isAdmin) {
            return;
        }

        // Set root IDs
        if (!\is_array($user->iso_tax_classes) || \count($user->iso_tax_classes) < 1) // Can't use empty() because its an object property (using __get)
        {
            $root = array(0);
        } else {
            $root = $user->iso_tax_classes;
        }

        $GLOBALS['TL_DCA']['tl_iso_tax_class']['list']['sorting']['root'] = $root;

        // Check permissions to add tax classes
        if (!$user->hasAccess('create', 'iso_tax_classp')) {
            $GLOBALS['TL_DCA']['tl_iso_tax_class']['config']['closed'] = true;
            unset($GLOBALS['TL_DCA']['tl_iso_tax_class']['list']['global_operations']['new']);
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
                    && $this->addNewRecordPermissions(Input::get('id'), 'tl_iso_tax_class', 'iso_tax_classes', 'iso_tax_classp')
                ) {
                    $root[] = Input::get('id');
                    $this->User->iso_tax_classes = $root;
                }
            // No break;

            case 'copy':
            case 'delete':
            case 'show':
                if (!\in_array(Input::get('id'), $root) || ('delete' === Input::get('act') && !$this->User->hasAccess('delete', 'iso_tax_classp'))) {
                    throw new AccessDeniedException('Not enough permissions to ' . Input::get('act') . ' tax class ID "' . Input::get('id') . '"');
                }
                break;

            case 'editAll':
            case 'deleteAll':
            case 'overrideAll':
                $session = Session::getInstance()->getData();
                if ('deleteAll' === Input::get('act') && !$user->hasAccess('delete', 'iso_tax_classp')) {
                    $session['CURRENT']['IDS'] = array();
                } else {
                    $session['CURRENT']['IDS'] = array_intersect($session['CURRENT']['IDS'], $root);
                }
                Session::getInstance()->setData($session);
                break;

            default:
                if (\strlen(Input::get('act'))) {
                    throw new AccessDeniedException('Not enough permissions to ' . Input::get('act') . ' tax classes');
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
        $user = BackendUser::getInstance();

        return ($user->isAdmin || $user->hasAccess('create', 'iso_tax_classp')) ? '<a href="' . Backend::addToUrl($href . '&amp;id=' . $row['id']) . '" title="' . StringUtil::specialchars($title) . '"' . $attributes . '>' . Image::getHtml($icon, $label) . '</a> ' : Image::getHtml(preg_replace('/\.svg$/i', '_.svg', $icon)) . ' ';
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
        $user = BackendUser::getInstance();

        return ($user->isAdmin || $user->hasAccess('delete', 'iso_tax_classp')) ? '<a href="' . Backend::addToUrl($href . '&amp;id=' . $row['id']) . '" title="' . StringUtil::specialchars($title) . '"' . $attributes . '>' . Image::getHtml($icon, $label) . '</a> ' : Image::getHtml(preg_replace('/\.svg$/i', '_.svg', $icon)) . ' ';
    }
}
