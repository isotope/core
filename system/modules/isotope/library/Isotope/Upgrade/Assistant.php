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


use Contao\BackendTemplate;
use Contao\CoreBundle\Exception\ResponseException;
use Contao\Environment;
use Contao\System;

abstract class Assistant extends Base
{

    /**
     * Template
     * @var BackendTemplate
     */
    protected $Template;

    /**
     * Template name
     * @var string
     */
    protected $strTemplate = 'be_iso_upgrade';


    public function generate()
    {
        System::loadLanguageFile('iso_upgrade');

        $this->Template = new BackendTemplate($this->strTemplate);
        $this->Template->base = Environment::get('base');
        $this->Template->slabel = $GLOBALS['TL_LANG']['UPG']['submit'];

        $this->compile();

        throw new ResponseException($this->Template->getResponse());
    }

    abstract public function run($blnInstalled);

    abstract protected function compile();
}
