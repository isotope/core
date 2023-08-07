<?php

/*
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009 - 2019 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Backend\TaxRate;

use Contao\Backend;
use Contao\CoreBundle\Exception\AccessDeniedException;
use Contao\Image;
use Contao\Input;
use Contao\Session;
use Contao\StringUtil;
use Isotope\Backend\Permission;
use Isotope\Isotope;
use Isotope\Model\Config;
use Isotope\Model\TaxRate;


class Callback extends Permission
{

    /**
     * Check permissions to edit table tl_iso_tax_rate
     * @return void
     */
    public function checkPermission()
    {
        // Do not run the permission check on other Isotope modules
        if ('tax_rate' !== Input::get('mod')) {
            return;
        }

        $this->import('BackendUser', 'User');

        if ($this->User->isAdmin) {
            return;
        }

        // Set root IDs
        if (!\is_array($this->User->iso_tax_rates) || \count($this->User->iso_tax_rates) < 1) // Can't use empty() because its an object property (using __get)
        {
            $root = array(0);
        } else {
            $root = $this->User->iso_tax_rates;
        }

        $GLOBALS['TL_DCA']['tl_iso_tax_rate']['list']['sorting']['root'] = $root;

        // Check permissions to add tax rates
        if (!$this->User->hasAccess('create', 'iso_tax_ratep')) {
            $GLOBALS['TL_DCA']['tl_iso_tax_rate']['config']['closed'] = true;
            unset($GLOBALS['TL_DCA']['tl_iso_tax_rate']['list']['global_operations']['new']);
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
                    && $this->addNewRecordPermissions(Input::get('id'), 'tl_iso_tax_rate', 'iso_tax_rates', 'iso_tax_ratep')
                ) {
                    $root[] = Input::get('id');
                    $this->User->iso_tax_rates = $root;
                }
            // No break;

            case 'copy':
            case 'delete':
            case 'show':
                if (!\in_array(Input::get('id'), $root)
                    || ('delete' === Input::get('act') && !$this->User->hasAccess('delete', 'iso_tax_ratep'))
                ) {
                    throw new AccessDeniedException('Not enough permissions to ' . Input::get('act') . ' tax rate ID "' . Input::get('id') . '"');
                }
                break;

            case 'editAll':
            case 'deleteAll':
            case 'overrideAll':
                $session = Session::getInstance()->getData();
                if ('deleteAll' === Input::get('act') && !$this->User->hasAccess('delete', 'iso_tax_ratep')) {
                    $session['CURRENT']['IDS'] = array();
                } else {
                    $session['CURRENT']['IDS'] = array_intersect($session['CURRENT']['IDS'], $root);
                }
                Session::getInstance()->setData($session);
                break;

            default:
                if (\strlen(Input::get('act'))) {
                    throw new AccessDeniedException('Not enough permissions to ' . Input::get('act') . ' tax rates');
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
        $arrRate = StringUtil::deserialize($row['rate']);

        if ($row['config'] && !$arrRate['unit']) {
            Isotope::setConfig(Config::findByPk($row['config']));

            $strRate = Isotope::formatPriceWithCurrency($arrRate['value'], false);
        } else {
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

        if (null !== $objTaxRate && $objTaxRate->config > 0 && null !== $objTaxRate->getRelated('config')) {
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
        return ($this->User->isAdmin || $this->User->hasAccess('create', 'iso_tax_ratep')) ? '<a href="' . Backend::addToUrl($href . '&amp;id=' . $row['id']) . '" title="' . StringUtil::specialchars($title) . '"' . $attributes . '>' . Image::getHtml($icon, $label) . '</a> ' : Image::getHtml(preg_replace('/\.svg$/i', '_.svg', $icon)) . ' ';
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
        return ($this->User->isAdmin || $this->User->hasAccess('delete', 'iso_tax_ratep')) ? '<a href="' . Backend::addToUrl($href . '&amp;id=' . $row['id']) . '" title="' . StringUtil::specialchars($title) . '"' . $attributes . '>' . Image::getHtml($icon, $label) . '</a> ' : Image::getHtml(preg_replace('/\.svg$/i', '_.svg', $icon)) . ' ';
    }
}
