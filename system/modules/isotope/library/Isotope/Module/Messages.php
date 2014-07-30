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
        if (TL_MODE == 'BE') {
            $objTemplate           = new \BackendTemplate('be_wildcard');
            $objTemplate->wildcard = '### ISOTOPE ECOMMERCE: MESSAGES ###';
            $objTemplate->title    = $this->headline;
            $objTemplate->id       = $this->id;
            $objTemplate->link     = $this->name;
            $objTemplate->href     = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id=' . $this->id;

            return $objTemplate->parse();
        }

        // Never prepend messages
        $this->iso_includeMessages = false;

        $strBuffer = parent::generate();

        if (count($this->Template->messages['value']) > 0) {
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
