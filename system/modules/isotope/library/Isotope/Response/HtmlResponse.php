<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2013 terminal42 gmbh
 *
 * @package    Isotope
 * @link       http://isotopeecommerce.org
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 */

namespace Isotope\Response;


class HtmlResponse extends Response
{
    /**
     * Creates a new HTML HTTP response
     * @param   string The response content
     * @param   integer The response HTTP status code
     * @throws \InvalidArgumentException When the HTTP status code is not valid
     */
    public function __construct($strContent, $intStatus = 200)
    {
        parent::__construct($strContent, $intStatus);

        $this->setHeader('Content-Type', 'text/html');
    }
}