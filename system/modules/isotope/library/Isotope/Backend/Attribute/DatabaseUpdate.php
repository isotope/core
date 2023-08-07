<?php

/*
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009 - 2019 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Backend\Attribute;

use Contao\Controller;
use Contao\DataContainer;
use Contao\DcaExtractor;
use Contao\File;
use Contao\System;
use Isotope\DatabaseUpdater;
use Symfony\Component\Filesystem\Filesystem;

class DatabaseUpdate extends DcaExtractor
{
    /** @noinspection MagicMethodsValidityInspection
     * @noinspection PhpMissingParentConstructorInspection
     */
    public function __construct()
    {
        $this->strTable = 'tl_iso_product';
    }

    /**
     * Update tl_iso_product table in database based on attribute configuration.
     *
     * @param \DataContainer $dc
     */
    public function onSubmit($dc)
    {
        if (!$dc->activeRecord->field_name) {
            return;
        }

        Controller::loadDataContainer($this->strTable, true);

        $this->dumpCacheFile();

        $objUpdater = new DatabaseUpdater();
        $objUpdater->autoUpdateTables(array($this->strTable));
    }

    /**
     * Rebuild the DcaExtractor cache when deleting an attribute.
     */
    public function onDelete(DataContainer $dc)
    {
        if (!$dc->activeRecord->field_name) {
            return;
        }

        Controller::loadDataContainer($this->strTable, true);

        unset($GLOBALS['TL_DCA'][$this->strTable]['fields'][$dc->activeRecord->field_name]);
        unset($GLOBALS['TL_DCA'][$this->strTable]['config']['sql']['keys'][$dc->activeRecord->field_name]);

        $this->dumpCacheFile();
    }

    /**
     * Override to allow call as DCA callback.
     *
     * {@inheritdoc}
     */
    public static function getInstance($strTable = null)
    {
        return new static();
    }

    /**
     * Write DcaExtractor config to cache file depending on Contao version.
     */
    private function dumpCacheFile()
    {
        $this->createExtract();

        $filesystem = new Filesystem();
        $cacheDir = System::getContainer()->getParameter('kernel.cache_dir');
        $file = sprintf(
            '%s/contao/sql/%s.php',
            $filesystem->makePathRelative($cacheDir, TL_ROOT),
            $this->strTable
        );

        // Create the file
        $objFile = new File($file, true);
        $objFile->write("<?php\n\n");
        $objFile->append(sprintf("\$this->arrMeta = %s;\n", var_export($this->getMeta(), true)));
        $objFile->append(sprintf("\$this->arrFields = %s;\n", var_export($this->getFields(), true)));
        $objFile->append(sprintf("\$this->arrOrderFields = %s;\n", var_export($this->getOrderFields(), true)));

        if (method_exists($this, 'getUniqueFields')) {
            $objFile->append(sprintf("\$this->arrUniqueFields = %s;\n", var_export($this->getUniqueFields(), true)));
        }

        $objFile->append(sprintf("\$this->arrKeys = %s;\n", var_export($this->getKeys(), true)));
        $objFile->append(sprintf("\$this->arrRelations = %s;\n", var_export($this->getRelations(), true)));
        // Set the database table flag
        $objFile->append("\$this->blnIsDbTable = true;", "\n");
        // Close the file (moves it to its final destination)
        $objFile->close();

        unset(parent::$arrInstances[$this->strTable]);
    }
}
