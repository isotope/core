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

namespace Isotope;


/**
 * Class Upgrade
 *
 * Perform automatic migration of Isotope database and content
 * @copyright  Isotope eCommerce Workgroup 2009-2012
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
 */
class Upgrade extends \Controller
{

    /**
     * Initialize the object
     */
    public function __construct()
    {
        parent::__construct();

        $GLOBALS['TL_CONFIG']['debugMode'] = false;
    }


    /**
     * Run the controller
     */
    public function run()
    {
        // Check if shop has been installed
        $blnInstalled = \Database::getInstance()->tableExists(\Isotope\Model\Config::getTable());
        $strStep = '';

        foreach (scan(TL_ROOT . '/system/modules/isotope/library/Isotope/Upgrade') as $strFile) {
            $strVersion = pathinfo($strFile, PATHINFO_FILENAME);

            if (preg_match('/To[0-9]{10}/', $strVersion)) {
                $strClass   = 'Isotope\Upgrade\\' . $strVersion;
                $strStep    = 'Version ' . \Haste\Util\Format::repositoryVersion(substr($strVersion, 2));

                try {
                    $objUpgrade = new $strClass();
                    $objUpgrade->run($blnInstalled);
                } catch (\Exception $e) {
                    $this->handleException($strStep, $e);
                }
            }
        }

        if ($blnInstalled) {
            try {
                $this->verifySystemIntegrity();
                $this->purgeCaches();
            } catch (\Exception $e) {
                $this->handleException('Finalization', $e);
            }
        }
    }


    private function handleException($step, \Exception $e)
    {
echo '
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Contao Open Source CMS</title>
<style media="screen">
div { width:520px; margin:64px auto 18px; padding:24px; background:#ffc; border:1px solid #fc0; font-family:Verdana,sans-serif; font-size:13px; }
h1 { font-size:18px; font-weight:normal; margin:0 0 18px; }
</style>
</head>
<body>

<div>

<h1>Isotope eCommerce Upgrade step "' . $step . '" was not run successfully!</h1>

<pre style="white-space:normal">' . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine() . '</pre>

</div>

</body>
</html>
';
        exit;
    }


    private function verifySystemIntegrity()
    {
        // Just make sure no variant or translation has any categories assigned
        \Database::getInstance()->query("
            DELETE FROM tl_iso_product_category
            WHERE pid IN (
                SELECT id
                FROM tl_iso_product
                WHERE pid>0
            )
        ");
    }


    private function purgeCaches()
    {
        \Isotope\Model\ProductCache::purge();
        \Isotope\Model\RequestCache::purge();
    }
}
