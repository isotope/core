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
 * Class Upgrade
 *
 * Perform automatic migration of Isotope database and content
 * @copyright  Isotope eCommerce Workgroup 2009-2012
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
 */
class Runonce extends \Controller
{

    /**
     * Initialize the object
     */
    public function __construct()
    {
        parent::__construct();

        // Fix potential Exception on line 0 because of __destruct method (see http://dev.contao.org/issues/2236)
        $this->import((TL_MODE=='BE' ? 'BackendUser' : 'FrontendUser'), 'User');
        $this->import('Database');
    }


    /**
     * Run the controller
     */
    public function run()
    {
        $this->upgradeSystemConfiguration();

        // Check if shop has been installed (tl_store is the name for config table in version < 0.2)
        $blnInstalled = (\Database::getInstance()->tableExists('tl_iso_config') || \Database::getInstance()->tableExists('tl_store'));

        foreach (scan(TL_ROOT . '/system/modules/isotope/library/Isotope/Upgrade') as $strFile) {
            $strVersion = pathinfo($strFile, PATHINFO_FILENAME);
            $strClass = 'Isotope\Upgrade\\' . $strVersion;

            if (preg_match('/To[0-9]{10}/', $strVersion)) {
                try {
                    $step = 'Version ' . \Repository::formatVersion(substr($strVersion, 2));
                    $objUpgrade = new $strClass();
                    $objUpgrade->run($blnInstalled);
                } catch (\Exception $e) {
                    $this->handleException($step, $e);
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

        \System::log('Upgraded Isotope eCommerce to ' . $step, TL_INFO, __METHOD__);
    }


    private function handleException($step, $e)
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

<pre style="white-space:normal">' . $e->getMessage() . '</pre>

</div>

</body>
</html>
';
        exit;
    }


    private function upgradeSystemConfiguration()
    {
        // Make sure file extension .imt (Isotope Mail Template) is allowed for up- and download
        if (!in_array('imt', trimsplit(',', $GLOBALS['TL_CONFIG']['uploadTypes']))) {
            $this->Config->update('$GLOBALS[\'TL_CONFIG\'][\'uploadTypes\']', $GLOBALS['TL_CONFIG']['uploadTypes'].',imt');
        }
    }


    private function verifySystemIntegrity()
    {
        // Just make sure no variant or translation has any categories assigned
        \Database::getInstance()->query("DELETE FROM tl_iso_product_categories WHERE pid IN (SELECT id FROM tl_iso_products WHERE pid>0)");
    }


    private function purgeCaches()
    {
        \Isotope\Model\ProductCache::purge();
        \Isotope\Model\RequestCache::purge();
    }
}
