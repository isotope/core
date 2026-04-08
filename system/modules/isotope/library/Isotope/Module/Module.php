<?php

/*
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009 - 2019 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Module;

use Isotope\CompatibilityHelper;
use Isotope\CoreBundle\Product\ProductInterface;
use Isotope\Isotope;
use Isotope\Message;


/**
 * Module implements a parent class for Isotope modules
 *
 * @property string $iso_category_scope
 * @property string $iso_includeMessages
 * @property bool   $iso_hide_list
 * @property bool   $iso_disable_options
 * @property bool   $iso_emptyMessage
 * @property string $iso_noProducts
 * @property bool   $iso_emptyFilter
 * @property string $iso_noFilter
 * @property string $iso_buttons
 * @property bool   $iso_link_primary
 */
abstract class Module extends AbstractFrontendModule
{
    /**
     * Disable caching of the frontend page if this module is in use.
     * Usefule to enable in a child classes.
     * @var bool
     */
    protected $blnDisableCache = false;

    /**
     * Load libraries and scripts
     *
     * @param \Contao\ModuleModel $objModule
     * @param string $strColumn
     */
    public function __construct($objModule, $strColumn = 'main')
    {
        parent::__construct($objModule, $strColumn);

        Isotope::initialize();

        // Load Isotope JavaScript and style sheet
        if (CompatibilityHelper::isFrontend()) {
            // Disable caching for pages with certain modules (eg. Cart)
            if ($this->blnDisableCache) {
                global $objPage;
                $objPage->cache = 0;
            }
        }
    }

    /**
     * @inheritDoc
     */
    protected function getSerializedProperties()
    {
        return ['iso_buttons'];
    }

    /**
     * Include messages if enabled
     *
     * @return string
     */
    public function generate()
    {
        $strBuffer = parent::generate();

        // Prepend any messages to the module output
        if (!CompatibilityHelper::isBackend() && $this->iso_includeMessages) {
            $strBuffer = Message::generate() . $strBuffer;
        }

        return $strBuffer;
    }
}
