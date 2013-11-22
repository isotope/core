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

use Isotope\Interfaces\IsotopePostsale;
use Isotope\Interfaces\IsotopeProductCollection;
use Isotope\Model\Payment;
use Isotope\Model\Shipping;
use Haste\Http\Response\Response;


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
                $objCallback = \System::importStatic($callback[0]);
                $objCallback->$callback[1]();
            }
        }
    }


    /**
     * Run the controller
     */
    public function run()
    {
        $objMethod = null;

        try {
            $strMod = strlen(\Input::post('mod')) ? \Input::post('mod') : \Input::get('mod');
            $strId = strlen(\Input::post('id')) ? \Input::post('id') : \Input::get('id');

            if ($strMod == '' || $strId == '') {
                \System::log('Invalid post-sale request (param error): '.\Environment::get('request'), __METHOD__, TL_ERROR);

                $objResponse = new Response('Bad Request', 400);
                $objResponse->send();
            }

            switch (strtolower($strMod)) {
                case 'pay':
                    $objMethod = Payment::findByPk($strId);
                    break;

                case 'ship':
                    $objMethod = Shipping::findByPk($strId);
                    break;
            }

            if (null === $objMethod) {
                \System::log('Invalid post-sale request (model not found): '.\Environment::get('request'), __METHOD__, TL_ERROR);

                $objResponse = new Response('Not Found', 404);
                $objResponse->send();
            }

            \System::log('New post-sale request: '.\Environment::get('request'), __METHOD__, TL_ACCESS);

            if (!($objMethod instanceof IsotopePostsale)) {
                \System::log('Invalid post-sale request (interface not implemented): '.\Environment::get('request'), __METHOD__, TL_ERROR);

                $objResponse = new Response('Not Implemented', 501);
                $objResponse->send();
            }

            $objOrder = $objMethod->getPostsaleOrder();

            if (null === $objOrder || !($objOrder instanceof IsotopeProductCollection)) {
                \System::log(get_class($objMethod) . ' did not return a valid order', __METHOD__, TL_ERROR);

                $objResponse = new Response('Failed Dependency', 424);
                $objResponse->send();
            }

            $objCart = $objOrder->getRelated('source_collection_id');
            Isotope::setCart($objCart);
            Isotope::setConfig($objCart->getRelated('config_id'));

            return $objMethod->processPostsale($objOrder);

        } catch (\Exception $e) {
            \System::log(
                sprintf('Exception in post-sale request in file "%s" on line "%s" with message "%s".',
                    $e->getFile(),
                    $e->getLine(),
                    $e->getMessage()
                ), __METHOD__, TL_ERROR);

            $objResponse = new Response('Internal Server Error', 500);
            $objResponse->send();
        }
    }
}


/**
 * Instantiate controller
 */
$objPostSale = new PostSale();
$objPostSale->run();
