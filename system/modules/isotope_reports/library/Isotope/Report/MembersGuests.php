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

namespace Isotope\Report;


class MembersGuests extends Report
{

    public function generate()
    {
        return '<p class="tl_gerror">This report is not implemented yet. It will show your sales comparison between members and guests.</p>';
    }

    protected function compile()
    {
    }
}

