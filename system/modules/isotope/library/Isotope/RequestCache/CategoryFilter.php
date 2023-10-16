<?php

/*
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009 - 2019 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\RequestCache;


class CategoryFilter extends Filter
{
    public function __construct(array $ids)
    {
        parent::__construct('c.page_id');
        $this->inArray($ids);
    }
}
