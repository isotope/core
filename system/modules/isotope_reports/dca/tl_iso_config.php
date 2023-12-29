<?php

/*
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009 - 2023 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

use Contao\Database;
use Isotope\Backend;
use Isotope\Model\Config;

$this->loadLanguageFile(Config::getTable());
$this->loadDataContainer(Config::getTable());
/**
 * Extend tl_user palettes
 */
$GLOBALS['TL_DCA']['tl_iso_config']['palettes']['default'] = str_replace('{analytics_legend},ga_enable;', '{analytics_legend},ga_enable;{reports_legend},visitors_config_id;', $GLOBALS['TL_DCA']['tl_iso_config']['palettes']['default']);

/**
 * Add fields to tl_iso_config
 */
$GLOBALS['TL_DCA']['tl_iso_config']['fields']['visitors_config_id'] = array
(
    'label'                 => &$GLOBALS['TL_LANG']['tl_iso_config']['visitors_config_id'],
    'exclude'               => true,
    'inputType'             => 'select',
    'options_callback'      => array('ReportsConfig', 'getVisitorConfigs'),
    'eval'                  => array('includeBlankOption'=>true, 'tl_class'=>'w50'),
    'sql'                   => "int(10) unsigned NOT NULL default '0'",
);

class ReportsConfig extends Backend
{

    public function getVisitorConfigs(): array
    {
        $tableVisitorsExists = Database::getInstance()->tableExists('tl_visitors');
        $arrVisitorConfigs = array();
        if($tableVisitorsExists){
            $objData = Database::getInstance()->query("
            SELECT id, visitors_name
            FROM tl_visitors
            ");

            while ($objData->next()) {
                $arrVisitorConfigs[$objData->id] = $objData->visitors_name;
            }
        }
        return $arrVisitorConfigs;
    }
}
