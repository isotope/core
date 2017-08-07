<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2016 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope;

use Isotope\Interfaces\IsotopePostsale;
use Isotope\Interfaces\IsotopeProductCollection;
use Isotope\Model\Payment;
use Isotope\Model\ProductCollection\Order;
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

require_once('initialize.php');


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
     */
    public function __construct()
    {
        parent::__construct();

        $this->removeUnsupportedHooks();

        // Default parameters
        $this->setModule((string) (\Input::post('mod') ?: \Input::get('mod')));
        $this->setModuleId((int) (\Input::post('id') ?: \Input::get('id')));

        // HOOK: allow to add custom hooks for postsale script
        if (isset($GLOBALS['ISO_HOOKS']['initializePostsale']) && is_array($GLOBALS['ISO_HOOKS']['initializePostsale']))
        {
            foreach ($GLOBALS['ISO_HOOKS']['initializePostsale'] as $callback)
            {
                $objCallback = \System::importStatic($callback[0]);
                $objCallback->{$callback[1]}($this);
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
        $this->logRequest();

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

            /** @type Order $objOrder */
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
                'Exception in post-sale request. See system/logs/isotope_postsale.log for details.',
                __METHOD__,
                TL_ERROR
            );

            log_message((string) $e, 'isotope_postsale.log');

            $objResponse = new Response('Internal Server Error', 500);
            $objResponse->send();
        }
    }

    /**
     * Log every postsale request to our log file. Should be OK as the Contao automator does log-rotation.
     */
    private function logRequest()
    {
        $headers = array();

        foreach ($_SERVER as $key => $value) {
            if (0 === strpos($key, 'HTTP_')) {
                $headers[substr($key, 5)] = $value;
            }
        }

        log_message(
            sprintf(
                "New request to %s.\n\nHeaders: %s\n\n\$_GET: %s\n\n\$_POST: %s\n\nBody:\n%s\n",
                \Environment::get('base') . \Environment::get('request'),
                var_export($headers, true),
                var_export($_GET, true),
                var_export($_POST, true),
                file_get_contents("php://input")
            ),
            'isotope_postsale.log'
        );
    }

    private function removeUnsupportedHooks()
    {
        $GLOBALS['TL_HOOKS'] = array_intersect_key(
            $GLOBALS['TL_HOOKS'],
            array_flip(
                [
                    'addCustomRegexp',
                    'getAttributesFromDca',
                    'loadDataContainer',
                    'replaceInsertTags',
                ]
            )
        );
    }
}


/**
 * Instantiate controller
 */
$objPostSale = new PostSale();
$objPostSale->run();
