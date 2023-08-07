<?php

/*
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009 - 2019 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope;

use Contao\CoreBundle\Exception\ResponseException;
use Contao\System;
use Isotope\Interfaces\IsotopePostsale;
use Isotope\Interfaces\IsotopeProductCollection;
use Isotope\Model\Payment;
use Isotope\Model\ProductCollection\Order;
use Isotope\Model\Shipping;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PostSale extends \Contao\Frontend
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
     * @var Request
     */
    private $request;

    /**
     * Must be defined cause parent is protected.
     */
    public function __construct(Request $request)
    {
        parent::__construct();

        $this->request = $request;

        $this->removeUnsupportedHooks();

        // Default parameters
        $this->setModule((string) $request->get('mod'));
        $this->setModuleId((int) $request->get('id'));

        // HOOK: allow to add custom hooks for postsale script
        if (isset($GLOBALS['ISO_HOOKS']['initializePostsale']) && \is_array($GLOBALS['ISO_HOOKS']['initializePostsale']))
        {
            foreach ($GLOBALS['ISO_HOOKS']['initializePostsale'] as $callback)
            {
                $objCallback = System::importStatic($callback[0]);
                $objCallback->{$callback[1]}($this, $request);
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

    public function run()
    {
        $this->logRequest();

        $objMethod = null;

        try {
            $strMod = $this->getModule();
            $intId  = $this->getModuleId();

            if ($strMod == '' || $intId == 0) {
                System::log(
                    'Invalid post-sale request (param error): ' . $this->request->getUri(),
                    __METHOD__,
                    TL_ERROR
                );

                return new Response('Bad Request', Response::HTTP_BAD_REQUEST);
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
                System::log(
                    'Invalid post-sale request (model not found): ' . $this->request->getUri(),
                    __METHOD__,
                    TL_ERROR
                );

                return new Response('Not Found', Response::HTTP_NOT_FOUND);
            }

            System::log('New post-sale request: ' . $this->request->getUri(), __METHOD__, TL_ACCESS);

            if (!($objMethod instanceof IsotopePostsale)) {
                System::log(
                    'Invalid post-sale request (interface not implemented): ' . $this->request->getUri(),
                    __METHOD__,
                    TL_ERROR
                );

                return new Response('Not Implemented', Response::HTTP_NOT_IMPLEMENTED);
            }

            /** @type Order $objOrder */
            $objOrder = $objMethod->getPostsaleOrder();

            if (null === $objOrder || !($objOrder instanceof IsotopeProductCollection)) {
                System::log(\get_class($objMethod) . ' did not return a valid order', __METHOD__, TL_ERROR);

                return new Response('Failed Dependency', Response::HTTP_FAILED_DEPENDENCY);
            }

            Frontend::loadOrderEnvironment($objOrder);

            $response = $objMethod->processPostsale($objOrder);

            return $response instanceof Response ? $response : new Response();

        } catch (\Exception $e) {
            if ($e instanceof ResponseException) {
                return $e->getResponse();
            }

            System::log(
                'Exception in post-sale request. See system/logs/isotope_postsale.log for details.',
                __METHOD__,
                TL_ERROR
            );

            log_message((string) $e, 'isotope_postsale-' . date('Y-m-d') . '.log');

            return new Response('Internal Server Error', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Log every postsale request to our log file. Should be OK as the Contao automator does log-rotation.
     */
    private function logRequest()
    {
        log_message(
            sprintf(
                "New request to %s.\n\nHeaders: %s\n\n\$_GET: %s\n\n\$_POST: %s\n\nBody:\n%s\n",
                $this->request->getUri(),
                var_export($this->request->headers->all(), true),
                var_export($this->request->query->all(), true),
                var_export($this->request->request->all(), true),
                $this->request->getContent()
            ),
            'isotope_postsale-' . date('Y-m-d') . '.log'
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
                    'sendNotificationMessage',
                ]
            )
        );
    }
}
