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

namespace Isotope\Model;

use Isotope\Interfaces\IsotopeProductCollection;

/**
 * Class Document
 *
 * Parent class for all documents.
 * @copyright  Isotope eCommerce Workgroup 2009-2012
 * @author     Yanick Witschi <yanick.witschi@terminal42.ch>
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 */
abstract class Document extends TypeAgent
{

    /**
     * Table name
     * @var string
     */
    protected static $strTable = 'tl_iso_document';

    /**
     * Interface to validate document
     * @var string
     */
    protected static $strInterface = '\Isotope\Interfaces\IsotopeDocument';

    /**
     * List of types (classes) for this model
     * @var array
     */
    protected static $arrModelTypes = array();


    /**
     * Prepares the collection tokens
     * @param   IsotopeProductCollection
     * @return  array
     */
    protected function prepareCollectionTokens(IsotopeProductCollection $objCollection)
    {
        $arrTokens = array();

        foreach ($objCollection->row() as $k => $v) {
            $arrTokens['collection_' . $k] = $v;
        }

        return $arrTokens;
    }

    /**
     * Prepare file name
     * @param   string File name
     * @param   array Simple tokens (optional)
     * @param   string Path (optional)
     * @return  string Sanitized file name
     */
    protected function prepareFileName($strName, $arrTokens = array(), $strPath = '')
    {
        // Replace simple tokens
        $strName = \StringUtil::parseSimpleTokens($strName, $arrTokens);
        $strName = $this->sanitizeFileName($strName);

        if ($strPath) {
            // Make sure the path contains a trailing slash
            $strPath = preg_replace('/([^\/]+)$/', '$1/', $strPath);

            $strName = $strPath . $strName;
        }

        return $strName;
    }

    /**
     * Sanitize file name
     * @param   string File name
     * @param   boolean Preserve uppercase (true by default)
     * @return  string Sanitized file name
     */
    protected function sanitizeFileName($strName, $blnPreserveUppercase = true)
    {
        return standardize(ampersand($strName, false), $blnPreserveUppercase);
    }
}
