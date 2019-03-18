<?php

/*
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009 - 2019 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Upgrade;


abstract class Assistant extends Base
{

    /**
     * Template
     * @var \BackendTemplate
     */
    protected $Template;

    /**
     * Template name
     * @var string
     */
    protected $strTemplate = 'be_iso_upgrade';


    public function generate()
    {
        \System::loadLanguageFile('iso_upgrade');

        $this->Template = new \BackendTemplate($this->strTemplate);
        $this->Template->base = \Environment::get('base');
        $this->Template->action = \Environment::get('request');
        $this->Template->slabel = $GLOBALS['TL_LANG']['UPG']['submit'];

        $this->compile();

        $this->Template->output();
        exit;
    }

    abstract public function run($blnInstalled);

    abstract protected function compile();
}
