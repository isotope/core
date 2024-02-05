<?php

/*
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009 - 2019 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\BackendModule;

use Contao\BackendModule;
use Contao\BackendUser;
use Contao\Controller;
use Contao\Environment;
use Contao\Input;
use Contao\System;
use Isotope\Interfaces\IsotopeIntegrityCheck;


/**
 * Class Integrity
 *
 * @property \Contao\Template|object $Template
 */
class Integrity extends BackendModule
{

    /**
     * Template
     * @var string
     */
    protected $strTemplate = 'be_iso_integrity';

    /**
     * Generate the module
     * @return string
     */
    public function generate()
    {
        if (!BackendUser::getInstance()->isAdmin) {
            return '<p class="tl_gerror">'.$GLOBALS['TL_LANG']['tl_iso_integrity']['permission'].'</p>';
        }

        System::loadLanguageFile('tl_iso_integrity');

        return parent::generate();
    }

    /**
     * Generate the module
     */
    protected function compile()
    {
        /** @var IsotopeIntegrityCheck[] $arrChecks */
        $arrChecks = array();
        $arrTasks = array();
        $blnReload = false;

        if ('tl_iso_integrity' === Input::post('FORM_SUBMIT')) {
            $arrTasks = (array) Input::post('tasks');
        }

        $this->Template->hasFixes = false;

        foreach ($GLOBALS['ISO_INTEGRITY'] as $strClass) {

            /** @var IsotopeIntegrityCheck $objCheck */
            $objCheck = new $strClass();

            if (!($objCheck instanceof IsotopeIntegrityCheck)) {
                throw new \LogicException('Class "' . $strClass . '" must implement IsotopeIntegrityCheck interface');
            }

            if (\in_array($objCheck->getId(), $arrTasks) && $objCheck->hasError() && $objCheck->canRepair()) {

                $objCheck->repair();
                $blnReload = true;

            } else {

                $blnError = $objCheck->hasError();
                $blnRepair = $objCheck->canRepair();

                $arrChecks[] = [
                    'id'          => $objCheck->getId(),
                    'name'        => $objCheck->getName(),
                    'description' => $objCheck->getDescription(),
                    'error'       => $blnError,
                    'repair'      => $blnError && $blnRepair,
                ];

                if ($blnError && $blnRepair) {
                    $this->Template->hasFixes = true;
                }
            }
        }

        if ($blnReload) {
            Controller::reload();
        }

        $this->Template->checks = $arrChecks;
        $this->Template->back = str_replace('&mod=integrity', '', Environment::get('request'));
    }
}
