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
 * Initialize the system
 */
define('TL_MODE', 'FE');
define('BYPASS_TOKEN_CHECK', true);

require '../../initialize.php';


/**
 * Class PostSale
 *
 * Handle postsale (server-to-server) communication
 * @copyright  Isotope eCommerce Workgroup 2009-2012
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
 */
class PostSale extends \Frontend
{

    /**
     * Must be defined cause parent is protected.
     *
     * @access public
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        // Contao Hooks are not save to be run on the postsale script (e.g. parseFrontendTemplate)
        unset($GLOBALS['TL_HOOKS']);

        // Need to load our own Hooks (e.g. loadDataContainer)
        include(TL_ROOT . '/system/modules/isotope/config/hooks.php');

        // HOOK: allow to add custom hooks for postsale script
        if (isset($GLOBALS['ISO_HOOKS']['initializePostsale']) && is_array($GLOBALS['ISO_HOOKS']['initializePostsale']))
        {
            foreach ($GLOBALS['ISO_HOOKS']['initializePostsale'] as $callback)
            {
                $this->import($callback[0]);
                $this->$callback[0]->$callback[1]();
            }
        }
    }


    /**
     * Run the controller
     */
    public function run()
    {
        $strMod = strlen(\Input::post('mod')) ? \Input::post('mod') : \Input::get('mod');
        $strId = strlen(\Input::post('id')) ? \Input::post('id') : \Input::get('id');

        if (!strlen($strMod) || !strlen($strId))
        {
            \System::log('Invalid post-sale request (param error): '.\Environment::get('request'), __METHOD__, TL_ERROR);

            return;
        }

        \System::log('New post-sale request: '.\Environment::get('request'), __METHOD__, TL_ACCESS);

        switch( strtolower($strMod) )
        {
            case 'pay':
                $objModule = $this->Database->prepare("SELECT * FROM tl_iso_payment_modules WHERE id=?")->limit(1)->execute($strId);
                break;

            case 'ship':
                $objModule = $this->Database->prepare("SELECT * FROM tl_iso_shipping_modules WHERE id=?")->limit(1)->execute($strId);
                break;
        }

        if (!$objModule->numRows)
        {
            \System::log('Invalid post-sale request (module not found): '.\Environment::get('request'), __METHOD__, TL_ERROR);

            return;
        }

        $strClass = $GLOBALS['ISO_'.strtoupper($strMod)][$objModule->type];
        if (!strlen($strClass) || !$this->classFileExists($strClass))
        {
            \System::log('Invalid post-sale request (class not found): '.\Environment::get('request'), __METHOD__, TL_ERROR);

            return;
        }

        try {
            $objModule = new $strClass($objModule->row());

            return $objModule->processPostSale();

        } catch (\Exception $e) {
            \System::log('Exception in post-sale request: '.$e->getMessage(), __METHOD__, TL_ERROR);
        }

        return;
    }
}


/**
 * Instantiate controller
 */
$objPostSale = new PostSale();
$objPostSale->run();
