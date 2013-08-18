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
 * Class tl_iso_producttypes
 * Provide miscellaneous methods that are used by the data configuration array.
 */
class tl_iso_producttypes extends \Backend
{

    /**
     * Check permissions to edit table tl_iso_producttypes
     * @return void
     */
    public function checkPermission()
    {
        // Do not run the permission check on other Isotope modules
        if (\Input::get('mod') != 'producttypes')
        {
            return;
        }

        $this->import('BackendUser', 'User');

        if ($this->User->isAdmin)
        {
            return;
        }

        // Set root IDs
        if (!is_array($this->User->iso_product_types) || count($this->User->iso_product_types) < 1) // Can't use empty() because its an object property (using __get)
        {
            $root = array(0);
        }
        else
        {
            $root = $this->User->iso_product_types;
        }

        $GLOBALS['TL_DCA']['tl_iso_producttypes']['list']['sorting']['root'] = $root;

        // Check permissions to add product types
        if (!$this->User->hasAccess('create', 'iso_product_typep'))
        {
            $GLOBALS['TL_DCA']['tl_iso_producttypes']['config']['closed'] = true;
            unset($GLOBALS['TL_DCA']['tl_iso_producttypes']['list']['global_operations']['new']);
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

                    if (is_array($arrNew['tl_iso_producttypes']) && in_array(\Input::get('id'), $arrNew['tl_iso_producttypes']))
                    {
                        // Add permissions on user level
                        if ($this->User->inherit == 'custom' || !$this->User->groups[0])
                        {
                            $objUser = $this->Database->prepare("SELECT iso_product_types, iso_product_typep FROM tl_user WHERE id=?")
                                                       ->limit(1)
                                                       ->execute($this->User->id);

                            $arrPermissions = deserialize($objUser->tl_iso_producttypep);

                            if (is_array($arrPermissions) && in_array('create', $arrPermissions))
                            {
                                $arrAccess = deserialize($objUser->iso_product_types);
                                $arrAccess[] = \Input::get('id');

                                $this->Database->prepare("UPDATE tl_user SET iso_product_types=? WHERE id=?")
                                               ->executeUncached(serialize($arrAccess), $this->User->id);
                            }
                        }

                        // Add permissions on group level
                        elseif ($this->User->groups[0] > 0)
                        {
                            $objGroup = $this->Database->prepare("SELECT iso_product_types, iso_product_typep FROM tl_user_group WHERE id=?")
                                                       ->limit(1)
                                                       ->execute($this->User->groups[0]);

                            $arrPermissions = deserialize($objGroup->iso_product_typep);

                            if (is_array($arrPermissions) && in_array('create', $arrPermissions))
                            {
                                $arrAccess = deserialize($objGroup->iso_product_types);
                                $arrAccess[] = \Input::get('id');

                                $this->Database->prepare("UPDATE tl_user_group SET iso_product_types=? WHERE id=?")
                                               ->executeUncached(serialize($arrAccess), $this->User->groups[0]);
                            }
                        }

                        // Add new element to the user object
                        $root[] = \Input::get('id');
                        $this->User->iso_product_types = $root;
                    }
                }
                // No break;

            case 'copy':
            case 'delete':
            case 'show':
                if (!in_array(\Input::get('id'), $root) || (\Input::get('act') == 'delete' && !$this->User->hasAccess('delete', 'iso_product_typep')))
                {
                    \System::log('Not enough permissions to '.\Input::get('act').' product type ID "'.\Input::get('id').'"', __METHOD__, TL_ERROR);
                    \Controller::redirect('contao/main.php?act=error');
                }
                break;

            case 'editAll':
            case 'deleteAll':
            case 'overrideAll':
                $session = $this->Session->getData();
                if (\Input::get('act') == 'deleteAll' && !$this->User->hasAccess('delete', 'iso_product_typep'))
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
                    \System::log('Not enough permissions to '.\Input::get('act').' product types', __METHOD__, TL_ERROR);
                    \Controller::redirect('contao/main.php?act=error');
                }
                break;
        }
    }


    /**
     * Return list templates as array
     * @param DataContainer
     * @return array
     */
    public function getListTemplates(\DataContainer $dc)
    {
        return \Isotope\Backend::getTemplates('iso_list_');
    }


    /**
     * Return reader templates as array
     * @param DataContainer
     * @return array
     */
    public function getReaderTemplates(\DataContainer $dc)
    {
        return \Isotope\Backend::getTemplates('iso_reader_');
    }


    /**
     * Return the copy product type button
     * @param array
     * @param string
     * @param string
     * @param string
     * @param string
     * @param string
     * @return string
     */
    public function copyProductType($row, $href, $label, $title, $icon, $attributes)
    {
        $this->import('BackendUser', 'User');

        return ($this->User->isAdmin || $this->User->hasAccess('create', 'iso_product_typep')) ? '<a href="'.$this->addToUrl($href.'&amp;id='.$row['id']).'" title="'.specialchars($title).'"'.$attributes.'>'.$this->generateImage($icon, $label).'</a> ' : $this->generateImage(preg_replace('/\.gif$/i', '_.gif', $icon)).' ';
    }


    /**
     * Return the delete product type button
     * @param array
     * @param string
     * @param string
     * @param string
     * @param string
     * @param string
     * @return string
     */
    public function deleteProductType($row, $href, $label, $title, $icon, $attributes)
    {
        return ($this->User->isAdmin || $this->User->hasAccess('delete', 'iso_product_typep')) ? '<a href="'.$this->addToUrl($href.'&amp;id='.$row['id']).'" title="'.specialchars($title).'"'.$attributes.'>'.$this->generateImage($icon, $label).'</a> ' : $this->generateImage(preg_replace('/\.gif$/i', '_.gif', $icon)).' ';
    }


    public function prepareAttributeWizard($objWidget)
    {
        $this->loadDataContainer('tl_iso_products');

        return array
        (
            'enabled' => array
            (
                'inputType'             => 'checkbox',
                'eval'                  => array('hideHead'=>true),
            ),
            'name' => array
            (
                'input_field_callback'  => array('Isotope\tl_iso_producttypes', 'getAttributeName'),
                'eval'                  => array('hideHead'=>true, 'tl_class'=>'mcwUpdateFields'),
            ),
            'legend' => array
            (
                'label'                 => &$GLOBALS['TL_LANG']['tl_iso_producttypes']['attributes']['legend'],
                'inputType'             => 'select',
                'options_callback'      => array('Isotope\tl_iso_producttypes', 'getLegends'),
                'eval'                  => array('style'=>'width:150px', 'class'=>'extendable'),
            ),
            'tl_class' => array
            (
                'label'                 => &$GLOBALS['TL_LANG']['tl_iso_producttypes']['attributes']['tl_class'],
                'inputType'             => 'text',
                'eval'                  => array('style'=>'width:80px'),
            ),
            'mandatory' => array
            (
                'label'                 => &$GLOBALS['TL_LANG']['tl_iso_producttypes']['attributes']['mandatory'],
                'inputType'             => 'select',
                'options'               => array('yes', 'no'),
                'reference'             => &$GLOBALS['TL_LANG']['MSC'],
                'eval'                  => array('style'=>'width:80px', 'includeBlankOption'=>true, 'blankOptionLabel'=>&$GLOBALS['TL_LANG']['tl_iso_producttypes']['fields']['default']),
            ),
        );
    }


    public function getAttributeName($objWidget, $xlabel)
    {
        static $arrValues;
        static $strWidget;
        static $i = 0;

        if ($strWidget != $objWidget->name) {
            $strWidget = $objWidget->name;
            $arrValues = $objWidget->value;
            $i = 0;
        }

        $arrField = array_shift($arrValues);
        $strName = $arrField['name'];

        return sprintf(
            '<input type="hidden" name="%s[%s][name]" id="ctrl_%s_row%s_name" value="%s"><div style="width:300px">%s <span style="color:#b3b3b3; padding-left:3px;">[%s]</span></div>',
            $objWidget->name,
            $i,
            $objWidget->name,
            $i++,
            $strName,
            $GLOBALS['TL_DCA']['tl_iso_products']['fields'][$strName]['label'][0] ?: $strName,
            $strName
        );
    }


    public function loadAttributeWizard($varValue, $dc)
    {
        $arrDCA = &$GLOBALS['TL_DCA']['tl_iso_products']['fields'];

        $arrFields = array();
        $arrValues = deserialize($varValue);
        $blnInherit = ($dc->field != 'attributes');

        if (!is_array($arrValues)) {
            $arrValues = array();
        }

        foreach ($arrValues as $arrField) {

            if ($arrField['name'] == '' || !isset($arrDCA[$arrField['name']]) || $arrDCA[$arrField['name']]['attributes']['legend'] == '' || ($blnInherit && $arrDCA[$arrField['name']]['attributes']['inherit'])) {
                continue;
            }

            if ($arrField['legend'] == '') {
                $arrField['legend'] = $arrDCA[$arrField['name']]['attributes']['legend'];
            }

            $arrFields[$arrField['name']] = $arrField;
        }

        foreach (array_diff_key($arrDCA, $arrFields) as $strName => $arrField) {

            if (!is_array($arrField['attributes']) || $arrField['attributes']['legend'] == '' || ($blnInherit && $arrField['attributes']['inherit'])) {
                continue;
            }

            $arrFields[$strName] = array(
                'enabled'   => ($arrField['attributes']['fixed'] ? '1' : ''),
                'name'      => $strName,
                'legend'    => $arrField['attributes']['legend'],
            );
        }

        return array_values($arrFields);
    }


    public function getLegends($objWidget)
    {
        $arrLegends = $GLOBALS['TL_DCA']['tl_iso_attributes']['fields']['legend']['options'];
        $arrLegends = array_intersect_key($GLOBALS['TL_LANG']['tl_iso_products'], array_flip($arrLegends));

        $varValue = $objWidget->value;

        if (!empty($varValue) && is_array($varValue)) {
            foreach ($varValue as $arrField) {
                if ($arrField['legend'] != '' && !isset($arrLegends[$arrField['legend']])) {
                    $arrLegends[$arrField['legend']] = $arrField['legend'];
                }
            }
        }

        return $arrLegends;
    }
}
