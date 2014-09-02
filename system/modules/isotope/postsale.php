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

use Isotope\Interfaces\IsotopePostsale;
use Isotope\Interfaces\IsotopeProductCollection;
use Isotope\Model\Payment;
use Isotope\Model\ProductCollection\Cart;
use Isotope\Model\Shipping;
use Haste\Http\Response\Response;

/**
 * Set the script name
 */
define('TL_SCRIPT', 'system/modules/isotope/postsale.php');

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
     * Postsale module
     * @var string
     */
    protected $strModule = '';

    /**
     * Postsale module ID
     * @var int
     */
    protected $intModuleId = 0;

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

        // Default parameters
        $this->setModule(strlen(\Input::post('mod')) ? \Input::post('mod') : \Input::get('mod'));
        $this->setModuleId(strlen(\Input::post('id')) ? \Input::post('id') : \Input::get('id'));

        // HOOK: allow to add custom hooks for postsale script
        if (isset($GLOBALS['ISO_HOOKS']['initializePostsale']) && is_array($GLOBALS['ISO_HOOKS']['initializePostsale']))
        {
            foreach ($GLOBALS['ISO_HOOKS']['initializePostsale'] as $callback)
            {
                $objCallback = \System::importStatic($callback[0]);
                $objCallback->$callback[1]($this);
            }
        }
    }

    /**
     * @param int $intModuleId
     */
    public function setModuleId($intModuleId)
    {
        $this->intModuleId = (int) $intModuleId;
    }

    /**
     * @return int
     */
    public function getModuleId()
    {
        return $this->intModuleId;
    }

    /**
     * @param string $strModule
     */
    public function setModule($strModule)
    {
        $this->strModule = $strModule;
    }

    /**
     * @return string
     */
    public function getModule()
    {
        return $this->strModule;
    }

    /**
     * Run the controller
     */
    public function run()
    {
        $objMethod = null;

        try {
            $strMod = $this->getModule();
            $intId = $this->getModuleId();

            if ($strMod == '' || $intId == 0) {
                \System::log('Invalid post-sale request (param error): '.\Environment::get('request'), __METHOD__, TL_ERROR);

                $objResponse = new Response('Bad Request', 400);
                $objResponse->send();
            }

            switch (strtolower($strMod)) {
                case 'pay':
                    $objMethod = Payment::findByPk($intId);
                    break;

                case 'ship':
                    $objMethod = Shipping::findByPk($intId);
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

            Frontend::loadOrderEnvironment($objOrder);

            $objMethod->processPostsale($objOrder);

            $objResponse = new Response();
            $objResponse->send();

        } catch (\Exception $e) {
            \System::log(
                sprintf('Exception in post-sale request. See system/logs/isotope_postsale.log for details.',
                    $e->getFile(),
                    $e->getLine(),
                    $e->getMessage()
                ),
                __METHOD__,
                TL_ERROR
            );

            log_message(
                sprintf(
                    "Exception in post-sale request\n%s\n\n",
                    $e->getTraceAsString()
                ),
                'isotope_postsale.log'
            );

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
