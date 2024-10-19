<?php

/*
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009 - 2019 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Interfaces;

/**
 * Documents print a collection
 */
interface IsotopeDocument
{

    /**
     * Generate the document and send it to browser
     */
    public function outputToBrowser(IsotopeProductCollection $objCollection);

    /**
     * Generate the document and store it to a given path
     *
     * @param string                   $strDirectoryPath Absolute path to the directory the file should be stored in
     * @return string Absolute path to the file
     */
    public function outputToFile(IsotopeProductCollection $objCollection, $strDirectoryPath);
}
