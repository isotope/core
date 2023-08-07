<?php

/*
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009 - 2019 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope;

use Contao\FrontendTemplate;
use Contao\StringUtil;
use Contao\TemplateLoader;

/**
 * Provide methods to handle Isotope templates.
 */
class Template extends FrontendTemplate
{

    /**
     * Check the Isotope config directory for a particular template
     *
     * @param string $strTemplate
     * @param string $strFormat
     *
     * @return string
     */
    public static function getTemplate($strTemplate, $strFormat = 'html5')
    {
        $arrAllowed = StringUtil::trimsplit(',', $GLOBALS['TL_CONFIG']['templateFiles'] ?? '');

        if (\is_array($GLOBALS['TL_CONFIG']['templateFiles'] ?? null) && !\in_array($strFormat, $arrAllowed)) {
            throw new \InvalidArgumentException("Invalid output format $strFormat");
        }

        $strKey      = $strTemplate . '.' . $strFormat;
        $strPath     = TL_ROOT . '/templates';
        $strTemplate = basename($strTemplate);

        // Check the templates subfolder
        $strTemplateGroup = str_replace(array('../', 'templates/'), '', Isotope::getConfig()->templateGroup);

        if ($strTemplateGroup != '') {
            $strFile = $strPath . '/' . $strTemplateGroup . '/' . $strKey;

            if (file_exists($strFile)) {
                return $strFile;
            }

            if (file_exists($strFile)) {
                return $strFile;
            }
        }

        return parent::getTemplate($strTemplate, $strFormat);
    }

    /**
     * Find a particular template file and return its path
     *
     * @param string  $strTemplate The name of the template
     * @param string  $strFormat   The file extension
     * @param boolean $blnDefault  If true, the default template path is returned
     *
     * @return string The path to the template file
     */
    protected function getTemplatePath($strTemplate, $strFormat='html5', $blnDefault=false)
    {
        if ($blnDefault)
        {
            return TemplateLoader::getDefaultPath($strTemplate, $strFormat);
        }

        return static::getTemplate($strTemplate, $strFormat);
    }
}
