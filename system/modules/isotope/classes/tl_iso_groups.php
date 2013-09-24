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
 * @author     Kamil Kuzminski <kamil.kuzminski@codefog.pl>
 */

namespace Isotope;

use Isotope\Model\Group;


/**
 * Class tl_iso_groups
 * Provide miscellaneous methods that are used by the data configuration array.
 */
class tl_iso_groups extends \Backend
{

    public function __construct()
    {
        parent::__construct();
        $this->import('BackendUser', 'User');
    }


    /**
     * Check access permissions
     */
    public function checkPermission($dc)
    {
        if ($this->User->isAdmin)
        {
            return;
        }

        // Load permissions in tl_iso_products
        if ($dc->table == 'tl_iso_products' || stripos(\Environment::get('request'), 'group.php') !== false)
        {
            $arrGroups = $this->User->iso_groups;

            if (!is_array($arrGroups) || empty($arrGroups))
            {
                $GLOBALS['TL_DCA']['tl_iso_groups']['list']['sorting']['filter'][] = array('id=?', 0);
            }
            else
            {
                $GLOBALS['TL_DCA']['tl_iso_groups']['list']['sorting']['root'] = $arrGroups;
            }

            return;
        }

        if (!is_array($this->User->iso_groupp) || empty($this->User->iso_groupp))
        {
            \System::log('Unallowed access to product groups!', __METHOD__, TL_ERROR);
            \Controller::redirect('contao/main.php?act=error');
        }

        // Set root IDs
        if (!is_array($this->User->iso_groups) || empty($this->User->iso_groups))
        {
            $root = array();
        }
        else
        {
            try {
                $root = $this->eliminateNestedPages($this->User->iso_groups, 'tl_iso_groups');
            }
            catch (\Exception $e) {
                $root = array();
            }
        }

        $GLOBALS['TL_DCA']['tl_iso_groups']['list']['sorting']['root'] = (empty($root) ? true : $root);

        if (in_array('rootPaste', $this->User->iso_groupp))
        {
            $GLOBALS['TL_DCA']['tl_iso_groups']['list']['sorting']['rootPaste'] = true;
        }

        // Check permissions to add product group
        if (!in_array('create', $this->User->iso_groupp))
        {
            $GLOBALS['TL_DCA']['tl_iso_groups']['config']['closed'] = true;
        }

        // Check current action
        switch (\Input::get('act'))
        {
            case 'create':
            case 'select':
            case 'paste':
                // Allow
                break;

            case 'edit':
                // Dynamically add the record to the user profile
                if (!in_array(\Input::get('id'), $root))
                {
                    $arrNew = $this->Session->get('new_records');

                    if (is_array($arrNew['tl_iso_groups']) && in_array(\Input::get('id'), $arrNew['tl_iso_groups']))
                    {
                        // Add permissions on user level
                        if ($this->User->inherit == 'custom' || !$this->User->groups[0])
                        {
                            $objUser = \Database::getInstance()->prepare("SELECT iso_groups, iso_groupp FROM tl_user WHERE id=?")
                                                               ->limit(1)
                                                               ->executeUncached($this->User->id);

                            $arrPermissions = deserialize($objUser->iso_groupp);

                            if (is_array($arrPermissions) && in_array('create', $arrPermissions))
                            {
                                $arrAccess = deserialize($objUser->iso_groups);
                                $arrAccess[] = \Input::get('id');

                                \Database::getInstance()->prepare("UPDATE tl_user SET iso_groups=? WHERE id=?")
                                                        ->execute(serialize($arrAccess), $this->User->id);
                            }
                        }

                        // Add permissions on group level
                        elseif ($this->User->groups[0] > 0)
                        {
                            $objGroup = \Database::getInstance()->prepare("SELECT iso_groups, iso_groupp FROM tl_user_group WHERE id=?")
                                                                ->limit(1)
                                                                ->executeUncached($this->User->groups[0]);

                            $arrPermissions = deserialize($objGroup->iso_groupp);

                            if (is_array($arrPermissions) && in_array('create', $arrPermissions))
                            {
                                $arrAccess = deserialize($objGroup->iso_groups);
                                $arrAccess[] = \Input::get('id');

                                \Database::getInstance()->prepare("UPDATE tl_user_group SET iso_groups=? WHERE id=?")
                                                        ->execute(serialize($arrAccess), $this->User->groups[0]);
                            }
                        }

                        // Add new element to the user object
                        $root[] = \Input::get('id');
                        $this->User->iso_groups = $root;
                    }
                }
                // No break;

            case 'copy':
            case 'delete':
            case 'show':
                if (!in_array(\Input::get('id'), $root) || (\Input::get('act') == 'delete' && !$this->User->hasAccess('delete', 'iso_groupp')))
                {
                    \System::log('Not enough permissions to '.\Input::get('act').' group ID "'.\Input::get('id').'"', __METHOD__, TL_ERROR);
                    \Controller::redirect('contao/main.php?act=error');
                }
                break;

            case 'editAll':
            case 'deleteAll':
            case 'overrideAll':
                $session = $this->Session->getData();
                if (\Input::get('act') == 'deleteAll' && !$this->User->hasAccess('delete', 'iso_groupp'))
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
                    \System::log('Not enough permissions to '.\Input::get('act').' groups', __METHOD__, TL_ERROR);
                    \Controller::redirect('contao/main.php?act=error');
                }
                break;
        }
    }


    /**
     * Add the breadcrumb menu
     */
    public function addBreadcrumb()
    {
        $GLOBALS['TL_DCA']['tl_iso_groups']['list']['sorting']['breadcrumb'] = \Isotope\Backend::generateGroupsBreadcrumb($this->Session->get('iso_products_gid'));

        if ($this->Session->get('iso_products_gid') > 0) {
            $GLOBALS['TL_DCA']['tl_iso_groups']['list']['sorting']['root'] = array($this->Session->get('iso_products_gid'));
        }
    }


    /**
     * Add an image to each group in the tree
     * @param array
     * @param string
     * @param DataContainer
     * @param string
     * @param boolean
     * @return string
     */
    public function addIcon($row, $label, \DataContainer $dc=null, $imageAttribute='', $blnReturnImage=false)
    {
        if ($dc->table == 'tl_iso_products')
        {
            return \Image::getHtml('system/modules/isotope/html/folder-network.png', '', $imageAttribute) . ' <span style="font-weight:bold">' . $label . '</span>';
        }
        else
        {
            $strProductType = '';

            if (($objProductType = Group::findByPk($row['id'])->getRelated('product_type')) !== null)
            {
                $strProductType = ' <span style="color:#b3b3b3; padding-left:3px;">[' . $objProductType->name . ']</span>';
            }

            return '<a href="' . $this->addToUrl('gid=' . $row['id']) . '" title="' . specialchars($row['name'] . ' (ID ' . $row['id'] . ')') . '">' . \Image::getHtml('system/modules/isotope/html/folder-network.png', '', $imageAttribute) . ' ' . $label . '</a>' . $strProductType;
        }
    }


    /**
     * Reassign products to no group when group is deleted
     * @param object
     * @return void
     */
    public function deleteGroup($dc)
    {
        $arrGroups = \Database::getInstance()->getChildRecords($dc->id, 'tl_iso_groups');
        $arrGroups[] = $dc->id;

        \Database::getInstance()->query("UPDATE tl_iso_products SET gid=0 WHERE gid IN (" . implode(',', $arrGroups) . ")");
    }


    /**
     * Disable copy button if user has no permission to create groups
     * @param array
     * @param string
     * @param string
     * @param string
     * @param string
     * @param string
     * @return string
     */
    public function copyButton($row, $href, $label, $title, $icon, $attributes)
    {
        if (!$this->User->isAdmin && (!is_array($this->User->iso_groupp) || !in_array('create', $this->User->iso_groupp)))
        {
            return \Image::getHtml(preg_replace('/\.gif$/i', '_.gif', $icon)) . ' ';
        }

        return '<a href="'.$this->addToUrl($href.'&amp;id='.$row['id']).'" title="'.specialchars($title).'"'.$attributes.'>'.\Image::getHtml($icon, $label).'</a> ';
    }


    /**
     * Disable delete button if user has no permission to delete groups
     * @param array
     * @param string
     * @param string
     * @param string
     * @param string
     * @param string
     * @return string
     */
    public function deleteButton($row, $href, $label, $title, $icon, $attributes)
    {
        if (!$this->User->isAdmin && (!is_array($this->User->iso_groupp) || !in_array('delete', $this->User->iso_groupp)))
        {
            return \Image::getHtml(preg_replace('/\.gif$/i', '_.gif', $icon)) . ' ';
        }

        return '<a href="'.$this->addToUrl($href.'&amp;id='.$row['id']).'" title="'.specialchars($title).'"'.$attributes.'>'.\Image::getHtml($icon, $label).'</a> ';
    }
}
