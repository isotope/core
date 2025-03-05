<?php

/*
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009 - 2019 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Backend\Shipping;


use Contao\Backend;
use Contao\BackendUser;
use Contao\Controller;
use Contao\CoreBundle\Exception\AccessDeniedException;
use Contao\Database;
use Contao\Image;
use Contao\Input;
use Contao\StringUtil;
use Contao\System;
use Contao\Versions;
use Isotope\Backend\Permission;
use Isotope\Model\Shipping;
use Symfony\Component\HttpFoundation\Session\Attribute\AttributeBagInterface;

class Callback extends Permission
{

    /**
     * Check permissions to edit table tl_iso_shipping
     * @return void
     */
    public function checkPermission()
    {
        // Do not run the permission check on other Isotope modules
        if ('shipping' !== Input::get('mod')) {
            return;
        }

        $user = BackendUser::getInstance();

        if ($user->isAdmin) {
            return;
        }

        // Set root IDs
        if (!\is_array($user->iso_shipping_modules)
            || 0 === \count($user->iso_shipping_modules)
        ) {
            $root = array(0);
        } else {
            $root = $user->iso_shipping_modules;
        }

        $GLOBALS['TL_DCA']['tl_iso_shipping']['list']['sorting']['root'] = $root;

        // Check permissions to add shipping modules
        if (!$user->hasAccess('create', 'iso_shipping_modulep')) {
            $GLOBALS['TL_DCA']['tl_iso_shipping']['config']['closed'] = true;
            unset($GLOBALS['TL_DCA']['tl_iso_shipping']['list']['global_operations']['new']);
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
                    && $this->addNewRecordPermissions(Input::get('id'), 'tl_iso_shipping', 'iso_shipping_modules', 'iso_shipping_modulep')
                ) {
                    $root[] = Input::get('id');
                    $user->iso_shipping_modules = $root;
                }
            // No break;

            case 'toggle':
            case 'copy':
            case 'delete':
            case 'show':
                if (!\in_array(Input::get('id'), $root)
                    || ('delete' === Input::get('act')
                        && !$user->hasAccess('delete', 'iso_shipping_modulep')
                    )
                ) {
                    throw new AccessDeniedException('Not enough permissions to ' . Input::get('act') . ' shipping module ID "' . Input::get('id') . '"');
                }
                break;

            case 'editAll':
            case 'deleteAll':
            case 'overrideAll':
                /** @var AttributeBagInterface $sessionBag */
                $sessionBag = System::getContainer()->get('request_stack')->getSession()->getBag('contao_backend');

                $session = $sessionBag->all();
                if ('deleteAll' === Input::get('act') && !BackendUser::getInstance()->hasAccess('delete', 'iso_shipping_modulep')) {
                    $session['CURRENT']['IDS'] = array();
                } else {
                    $session['CURRENT']['IDS'] = array_intersect($session['CURRENT']['IDS'], $root);
                }
                $sessionBag->replace($session);
                break;

            default:
                if (\strlen(Input::get('act'))) {
                    throw new AccessDeniedException('Not enough permissions to ' . Input::get('act') . ' shipping modules');
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
        return (BackendUser::getInstance()->isAdmin || BackendUser::getInstance()->hasAccess('create', 'iso_shipping_modulep')) ? '<a href="' . Backend::addToUrl($href . '&amp;id=' . $row['id']) . '" title="' . StringUtil::specialchars($title) . '"' . $attributes . '>' . Image::getHtml($icon, $label) . '</a> ' : Image::getHtml(preg_replace('/\.svg$/i', '_.svg', $icon)) . ' ';
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
        return (BackendUser::getInstance()->isAdmin || BackendUser::getInstance()->hasAccess('delete', 'iso_shipping_modulep')) ? '<a href="' . Backend::addToUrl($href . '&amp;id=' . $row['id']) . '" title="' . StringUtil::specialchars($title) . '"' . $attributes . '>' . Image::getHtml($icon, $label) . '</a> ' : Image::getHtml(preg_replace('/\.svg$/i', '_.svg', $icon)) . ' ';
    }

    public function hideLabelAndNotes($dc)
    {
        if ($dc->id) {
            $shipping = Shipping::findByPk($dc->id);

            if ($shipping->type === 'group' && $shipping->inherit) {
                unset(
                    $GLOBALS['TL_DCA']['tl_iso_shipping']['fields']['label'],
                    $GLOBALS['TL_DCA']['tl_iso_shipping']['fields']['note']
                );
            }
        }
    }
}
