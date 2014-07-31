<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2014 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @package    Isotope
 * @link       http://isotopeecommerce.org
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\BackendModule;

use Isotope\Interfaces\IsotopeIntegrityCheck;


/**
 * Class Integrity
 *
 * @property \Template|object Template
 */
class Integrity extends \BackendModule
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
        if (!\BackendUser::getInstance()->isAdmin) {
            return '<p class="tl_gerror">'.$GLOBALS['TL_LANG']['tl_iso_integrity']['permission'].'</p>';
        }

        \System::loadLanguageFile('tl_iso_integrity');

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

        if (\Input::post('FORM_SUBMIT') == 'tl_iso_integrity') {
            $arrTasks = (array) \Input::post('tasks');
        }

        foreach ($GLOBALS['ISO_INTEGRITY'] as $strClass) {

            /** @var IsotopeIntegrityCheck $objCheck */
            $objCheck = new $strClass();

            if (!($objCheck instanceof IsotopeIntegrityCheck)) {
                throw new \LogicException('Class "' . $strClass . '" must implement IsotopeIntegrityCheck interface');
            }

            if (in_array($objCheck->getId(), $arrTasks) && $objCheck->hasError() && $objCheck->canRepair()) {

                $objCheck->repair();
                $blnReload = true;

            } else {

                $arrChecks[] = array(
                    'id' => $objCheck->getId(),
                    'name' => $objCheck->getName(),
                    'description' => $objCheck->getDescription(),
                    'error' => $objCheck->hasError(),
                    'repair' => ($objCheck->hasError() && $objCheck->canRepair()),
                );
            }
        }

        if ($blnReload) {
            \Controller::reload();
        }

        $this->Template->checks = $arrChecks;
        $this->Template->action = \Environment::get('request');
        $this->Template->back = str_replace('&mod=integrity', '', \Environment::get('request'));
    }
}
