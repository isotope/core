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

namespace Isotope\Module;

use Isotope\Isotope;
use Isotope\Model\Config;


/**
 * Class ModuleIsotopeConfigSwitcher
 *
 * Front end module Isotope "config switcher".
 * @copyright  Isotope eCommerce Workgroup 2009-2012
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
 */
class ConfigSwitcher extends Module
{

    /**
     * Module template
     * @var string
     */
    protected $strTemplate = 'mod_iso_configswitcher';


    /**
     * Display a wildcard in the back end
     * @return string
     */
    public function generate()
    {
        if (TL_MODE == 'BE') {
            $objTemplate = new \BackendTemplate('be_wildcard');

            $objTemplate->wildcard = '### ISOTOPE ECOMMERCE: STORE CONFIG SWICHER ###';

            $objTemplate->title = $this->headline;
            $objTemplate->id    = $this->id;
            $objTemplate->link  = $this->name;
            $objTemplate->href  = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id=' . $this->id;

            return $objTemplate->parse();
        }

        $this->iso_config_ids = deserialize($this->iso_config_ids);

        if (!is_array($this->iso_config_ids) || !count($this->iso_config_ids)) { // Can't use empty() because its an object property (using __get)
            return '';
        }

        if (\Input::get('config') != '') {
            if (in_array(\Input::get('config'), $this->iso_config_ids)) {
                Isotope::getCart()->config_id = \Input::get('config');
                Isotope::getCart()->save();
            }

            \Controller::redirect(preg_replace(('@[?|&]config=' . \Input::get('config') . '@'), '', \Environment::get('request')));
        }

        return parent::generate();
    }


    /**
     * Compile the module
     * @return void
     */
    protected function compile()
    {
        $arrConfigs = array();
        $objConfigs = Config::findMultipleByIds($this->iso_config_ids);

        if (null !== $objConfigs) {
            while ($objConfigs->next()) {

                $arrConfigs[] = array (
                    'config'    => $objConfigs->current(),
                    'label'     => $objConfigs->current()->getLabel(),
                    'active'    => (Isotope::getConfig()->id == $objConfigs->id ? true : false),
                    'href'      => (\Environment::get('request') . ((strpos(\Environment::get('request'), '?') === false) ? '?' : '&amp;') . 'config=' . $objConfigs->id),
                );
            }
        }

        \Haste\Generator\RowClass::withKey('class')->addFirstLast()->applyTo($arrConfigs);

        $this->Template->configs    = $arrConfigs;
    }
}
