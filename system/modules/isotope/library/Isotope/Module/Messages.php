<?php

/*
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009 - 2019 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Module;

use Isotope\CompatibilityHelper;
use Isotope\Message;


/**
 * Class \Isotope\Module\Messages
 * Front end module Isotope "messages".
 */
class Messages extends Module
{

    /**
     * Template
     * @var string
     */
    protected $strTemplate = 'mod_iso_messages';


    /**
     * Display a wildcard in the back end and the never prepend messages in the front end
     * @return string
     */
    public function generate()
    {
        if (isBackend()) {
            return $this->generateWildcard();
        }

        // Never prepend messages
        $this->iso_includeMessages = false;

        $strBuffer = parent::generate();

        if (\count($this->Template->messages['value']) > 0) {
            return $strBuffer;
        }

        return '';
    }


    /**
     * Generate the module
     */
    protected function compile()
    {
        $this->Template->messages = Message::getAll();
    }
}
