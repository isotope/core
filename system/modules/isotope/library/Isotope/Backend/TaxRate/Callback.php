<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2013 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @package    Isotope
 * @link       http://isotopeecommerce.org
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Backend\TaxRate;

use Isotope\Isotope;
use Isotope\Model\Config;
use Isotope\Model\TaxRate;


class Callback extends \Backend
{

    /**
     * Check permissions to edit table tl_iso_tax_rate
     * @return void
     */
    public function checkPermission()
    {
        // Do not run the permission check on other Isotope modules
        if (\Input::get('mod') != 'tax_rate')
        {
            return;
        }

        $this->import('BackendUser', 'User');

        if ($this->User->isAdmin)
        {
            return;
        }

        // Set root IDs
        if (!is_array($this->User->iso_tax_rates) || count($this->User->iso_tax_rates) < 1) // Can't use empty() because its an object property (using __get)
        {
            $root = array(0);
        }
        else
        {
            $root = $this->User->iso_tax_rates;
        }

        $GLOBALS['TL_DCA']['tl_iso_tax_rate']['list']['sorting']['root'] = $root;

        // Check permissions to add tax rates
        if (!$this->User->hasAccess('create', 'iso_tax_ratep'))
        {
            $GLOBALS['TL_DCA']['tl_iso_tax_rate']['config']['closed'] = true;
            unset($GLOBALS['TL_DCA']['tl_iso_tax_rate']['list']['global_operations']['new']);
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

                    if (is_array($arrNew['tl_iso_tax_rate']) && in_array(\Input::get('id'), $arrNew['tl_iso_tax_rate']))
                    {
                        // Add permissions on user level
                        if ($this->User->inherit == 'custom' || !$this->User->groups[0])
                        {
                            $objUser = \Database::getInstance()->prepare("SELECT iso_tax_rates, iso_tax_ratep FROM tl_user WHERE id=?")->limit(1)->execute($this->User->id);
                            $arrPermissions = deserialize($objUser->iso_tax_ratep);

                            if (is_array($arrPermissions) && in_array('create', $arrPermissions))
                            {
                                $arrAccess = deserialize($objUser->iso_tax_rates);
                                $arrAccess[] = \Input::get('id');

                                \Database::getInstance()->prepare("UPDATE tl_user SET iso_tax_rates=? WHERE id=?")->execute(serialize($arrAccess), $this->User->id);
                            }
                        }

                        // Add permissions on group level
                        elseif ($this->User->groups[0] > 0)
                        {
                            $objGroup = \Database::getInstance()->prepare("SELECT iso_tax_rates, iso_tax_ratep FROM tl_user_group WHERE id=?")->limit(1)->execute($this->User->groups[0]);
                            $arrPermissions = deserialize($objGroup->iso_tax_ratep);

                            if (is_array($arrPermissions) && in_array('create', $arrPermissions))
                            {
                                $arrAccess = deserialize($objGroup->iso_tax_rates);
                                $arrAccess[] = \Input::get('id');

                                \Database::getInstance()->prepare("UPDATE tl_user_group SET iso_tax_rates=? WHERE id=?")->execute(serialize($arrAccess), $this->User->groups[0]);
                            }
                        }

                        // Add new element to the user object
                        $root[] = \Input::get('id');
                        $this->User->iso_tax_rates = $root;
                    }
                }
                // No break;

            case 'copy':
            case 'delete':
            case 'show':
                if (!in_array(\Input::get('id'), $root) || (\Input::get('act') == 'delete' && !$this->User->hasAccess('delete', 'iso_tax_ratep')))
                {
                    \System::log('Not enough permissions to '.\Input::get('act').' tax rate ID "'.\Input::get('id').'"', __METHOD__, TL_ERROR);
                    \Controller::redirect('contao/main.php?act=error');
                }
                break;

            case 'editAll':
            case 'deleteAll':
            case 'overrideAll':
                $session = $this->Session->getData();
                if (\Input::get('act') == 'deleteAll' && !$this->User->hasAccess('delete', 'iso_tax_ratep'))
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
                    \System::log('Not enough permissions to '.\Input::get('act').' tax rates', __METHOD__, TL_ERROR);
                    \Controller::redirect('contao/main.php?act=error');
                }
                break;
        }
    }


    /**
     * List all records with formatted currency
     * @param array
     * @return string
     */
    public function listRow($row)
    {
        $arrRate = deserialize($row['rate']);

        if ($row['config'] && !$arrRate['unit'])
        {
            Isotope::setConfig(Config::findByPk($row['config']));

            $strRate = Isotope::formatPriceWithCurrency($arrRate['value'], false);
        }
        else
        {
            $strRate = $arrRate['value'] . '%';
        }

        return sprintf('%s <span style="color:#b3b3b3; padding-left:3px;">[%s]</span>', $row['name'], $strRate);
    }


    /**
     * Set the currency rate from selected store config
     * @param object
     */
    public function addCurrencyRate($dc)
    {
        $objTaxRate = TaxRate::findByPk($dc->id);

        if ($objTaxRate->config > 0 && null !== $objTaxRate->getRelated('config'))
        {
            $GLOBALS['TL_DCA']['tl_iso_tax_rate']['fields']['rate']['options'][''] = $objTaxRate->getRelated('config')->currency;
        }
    }


    /**
     * Return the copy tax rate button
     * @param array
     * @param string
     * @param string
     * @param string
     * @param string
     * @param string
     * @return string
     */
    public function copyTaxRate($row, $href, $label, $title, $icon, $attributes)
    {
        return ($this->User->isAdmin || $this->User->hasAccess('create', 'iso_tax_ratep')) ? '<a href="'.$this->addToUrl($href.'&amp;id='.$row['id']).'" title="'.specialchars($title).'"'.$attributes.'>'.\Image::getHtml($icon, $label).'</a> ' : \Image::getHtml(preg_replace('/\.gif$/i', '_.gif', $icon)).' ';
    }


    /**
     * Return the delete tax rate button
     * @param array
     * @param string
     * @param string
     * @param string
     * @param string
     * @param string
     * @return string
     */
    public function deleteTaxRate($row, $href, $label, $title, $icon, $attributes)
    {
        return ($this->User->isAdmin || $this->User->hasAccess('delete', 'iso_tax_ratep')) ? '<a href="'.$this->addToUrl($href.'&amp;id='.$row['id']).'" title="'.specialchars($title).'"'.$attributes.'>'.\Image::getHtml($icon, $label).'</a> ' : \Image::getHtml(preg_replace('/\.gif$/i', '_.gif', $icon)).' ';
    }
}
