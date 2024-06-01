<?php

/**
 * Haste utilities for Contao Open Source CMS
 *
 * Copyright (C) 2012-2016 Codefog & terminal42 gmbh
 *
 * @package    Haste
 * @link       http://github.com/codefog/contao-haste/
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 */

namespace Isotope\Module;

use Contao\BackendTemplate;
use Contao\Module;

abstract class AbstractFrontendModule extends Module
{
    /**
     * @var bool
     */
    private $wildcard = true;

    public function __construct($objModule, $strColumn = 'main')
    {
        parent::__construct($objModule, $strColumn);

        foreach ($this->getSerializedProperties() as $k => $name) {
            $force = true;

            if (is_bool($name)) {
                $force = $name;
                $name  = $k;
            }

            if (array_key_exists($name, $this->arrData)) {
                $this->arrData[$name] = deserialize($this->arrData[$name], $force);
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function generate()
    {
        if ('BE' === TL_MODE && $this->showWildcard()) {
            return $this->generateWildcard();
        }

        return parent::generate();
    }

    /**
     * Generate wildcard template for backend output.
     *
     * @return string
     */
    protected function generateWildcard()
    {
        $objTemplate = new BackendTemplate('be_wildcard');

        $objTemplate->wildcard = '### ' . mb_strtoupper($GLOBALS['TL_LANG']['FMD'][$this->type][0] ?? '') . ' ###';
        $objTemplate->title    = $this->headline;
        $objTemplate->id       = $this->id;
        $objTemplate->link     = $this->name;
        $objTemplate->href     = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id=' . $this->id;

        return $objTemplate->parse();
    }

    /**
     * Returns whether the wildcard should be shown in the backend.
     *
     * @return bool
     */
    protected function showWildcard()
    {
        return $this->wildcard;
    }

    /**
     * Sets whether the wildcard should be shown in the backend.
     *
     * @param bool $wildcard
     */
    protected function setWildcard($wildcard)
    {
        $this->wildcard = $wildcard;
    }

    /**
     * An array of serialized properties that will be deserialized on construction.
     * If you don't want to control the $forceArray option of deserialize(),
     * set the property name as key and a boolean value in the array.
     *
     * @return array
     */
    protected function getSerializedProperties()
    {
        return [];
    }
}
