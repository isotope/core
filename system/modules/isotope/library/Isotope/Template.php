<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2012 Isotope eCommerce Workgroup
 *
 * @package    Isotope
 * @link       http://www.isotopeecommerce.com
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 */

namespace Isotope;


/**
 * Class Isotope\Template
 *
 * Provide methods to handle Isotope templates.
 * @copyright  Isotope eCommerce Workgroup 2009-2012
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
 */
class Template extends \FrontendTemplate
{

    /**
     * Add support for callable variables (usually closures)
     * @param   string
     * @param   array
     * @return  mixed
     * @todo    remove when minimum is Contao 3.2
     */
    public function __call($name, $arguments)
    {
        return call_user_func_array($this->$name, $arguments);
    }

    /**
     * Check the Isotope config directory for a particular template
     * @param string
     * @return string
     * @throws Exception
     */
    public static function getTemplate($strTemplate, $strFormat='html5')
    {
        $arrAllowed = trimsplit(',', $GLOBALS['TL_CONFIG']['templateFiles']);

        if (is_array($GLOBALS['TL_CONFIG']['templateFiles']) && !in_array($strFormat, $arrAllowed))
        {
            throw new \InvalidArgumentException("Invalid output format $strFormat");
        }

        $strKey = $strTemplate . '.' . $strFormat;
        $strPath = TL_ROOT . '/templates';
        $strTemplate = basename($strTemplate);

        // Check the templates subfolder
        if (TL_MODE == 'FE')
        {
            $strTemplateGroup = str_replace(array('../', 'templates/'), '', Isotope::getConfig()->templateGroup);

            if ($strTemplateGroup != '')
            {
                $strFile = $strPath . '/' . $strTemplateGroup . '/' . $strKey;

                if (file_exists($strFile))
                {
                    return $strFile;
                }

                // Also check for .tpl files (backwards compatibility)
                $strFile = $strPath . '/' . $strTemplateGroup . '/' . $strTemplate . '.tpl';

                if (file_exists($strFile))
                {
                    return $strFile;
                }
            }
        }

        return parent::getTemplate($strTemplate, $strFormat);
    }
}
