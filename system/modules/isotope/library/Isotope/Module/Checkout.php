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

use Contao\CoreBundle\Exception\PageNotFoundException;
use Contao\CoreBundle\Exception\RedirectResponseException;
use Contao\CoreBundle\Exception\ResponseException;
use Contao\CoreBundle\Routing\ResponseContext\HtmlHeadBag\HtmlHeadBag;
use Contao\PageModel;
use Contao\StringUtil;
use Contao\System;
use Haste\Generator\RowClass;
use Haste\Input\Input;
use Haste\Util\Url;
use Isotope\CheckoutStep\OrderConditions;
use Isotope\Interfaces\IsotopeCheckoutStep;
use Isotope\Interfaces\IsotopeNotificationTokens;
use Isotope\Interfaces\IsotopeProductCollection;
use Isotope\Isotope;
use Isotope\Model\ProductCollection\Order;
use Isotope\Template;
use Symfony\Component\HttpFoundation\Response;


/**
 * Class ModuleIsotopeCheckout
 * Front end module Isotope "checkout".
 *
 * @property array $iso_payment_modules
 * @property array $iso_shipping_modules
 * @property bool  $iso_forward_review
 * @property array $iso_notifications
 * @property bool  $iso_addToAddressbook
 * @property array $iso_checkout_skippable
 * @property array $iso_order_conditions
 * @property int   $orderCompleteJumpTo
 */
class Checkout extends Module
{
    const STEP_ADDRESS = 'address';
    const STEP_SHIPPING = 'shipping';
    const STEP_PAYMENT = 'payment';
    const STEP_REVIEW = 'review';
    const STEP_PROCESS = 'process';
    const STEP_COMPLETE = 'complete';
    const STEP_FAILED = 'failed';

    /**
     * Template
     * @var string
     */
    protected $strTemplate = 'mod_iso_checkout';

    /**
     * Do not continue to next step
     * @var boolean
     */
    public $doNotSubmit = false;

    /**
     * Disable caching of the frontend page if this module is in use.
     * @var boolean
     */
    protected $blnDisableCache = true;

    /**
     * Current step
     * @var string
     */
    protected $strCurrentStep;

    /**
     * Checkout steps that can be skipped
     * @var array
     */
    protected $skippableSteps = array();

    /**
     * Form ID
     * @var string
     */
    protected $strFormId = 'iso_mod_checkout';

    /**
     * @inheritDoc
     */
    protected function getSerializedProperties()
    {
        $props = parent::getSerializedProperties();

        $props[] = 'iso_checkout_skippable';

        return $props;
    }


    /**
     * Display a wildcard in the back end
     *
     * @return string
     */
    public function generate()
    {
        if ('BE' === TL_MODE) {
            return $this->generateWildcard();
        }

        $this->strCurrentStep = Input::getAutoItem('step');

        if ($this->strCurrentStep == '') {
            $this->redirectToNextStep();
        }

        return parent::generate();
    }

    /**
     * Returns the current form ID
     *
     * @return string
     */
    public function getFormId()
    {
        return $this->strFormId;
    }

    /**
     * Generate module
     */
    protected function compile()
    {
        $arrBuffer = array();

        // Default template settings. Must be set at beginning so they can be overwritten later (eg. trough callback)
        $this->Template->formId = $this->strFormId;
        $this->Template->formSubmit = $this->strFormId;
        $this->Template->enctype = 'application/x-www-form-urlencoded';
        $this->Template->previousLabel = StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['previousStep']);
        $this->Template->nextLabel = StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['nextStep']);
        $this->Template->nextClass = 'next';
        $this->Template->showPrevious = true;
        $this->Template->showNext = true;
        $this->Template->showForm = true;
        $this->Template->steps = array();

        // These steps are handled internally by the checkout module and are not in the config array
        switch ($this->strCurrentStep) {

            // Complete order after successful payment
            // At this stage, we do no longer use the client's cart but the order through UID in URL
            case self::STEP_COMPLETE:
                /** @var Order $objOrder */
                if (($objOrder = Order::findOneBy('uniqid', (string) Input::get('uid'))) === null) {
                    if (Isotope::getCart()->isEmpty()) {
                        throw new PageNotFoundException();
                    }

                    static::redirectToStep(self::STEP_FAILED);
                }

                // Order already completed (see #1441)
                if ($objOrder->checkout_complete) {
                    throw new RedirectResponseException(
                        PageModel::findByPk($this->orderCompleteJumpTo)->getAbsoluteUrl().'?uid=' . $objOrder->uniqid
                    );
                }

                $strBuffer = $objOrder->hasPayment() ? $objOrder->getPaymentMethod()->processPayment($objOrder, $this) : true;

                // true means the payment is successful and order should be completed
                if ($strBuffer === true) {
                    // If checkout is successful, complete order and redirect to confirmation page
                    if ($objOrder->checkout() && $objOrder->complete()) {
                        throw new RedirectResponseException(
                            PageModel::findByPk($this->orderCompleteJumpTo)->getAbsoluteUrl().'?uid=' . $objOrder->uniqid
                        );
                    }

                    // Checkout failed, show error message
                    static::redirectToStep(self::STEP_FAILED);
                }

                // False means payment has failed
                elseif ($strBuffer === false) {
                    static::redirectToStep(self::STEP_FAILED);
                }

                // Otherwise we assume a string that shows a message to customer
                else {
                    $this->Template->showNext     = false;
                    $this->Template->showPrevious = false;
                    $arrBuffer                    = array(array('html' => $strBuffer, 'class' => $this->strCurrentStep));
                }
                break;

            // Process order and initiate payment method if necessary
            case self::STEP_PROCESS:

                // canCheckout will override the template and show a message
                if (!$this->canCheckout()) {
                    return;
                }

                $arrSteps = $this->getSteps();

                // Make sure all steps have passed successfully
                foreach ($arrSteps as $step => $arrModules) {
                    /** @var IsotopeCheckoutStep $objModule */
                    foreach ($arrModules as $objModule) {
                        $objModule->generate();

                        if ($objModule->hasError()) {
                            static::redirectToStep($step);
                        }
                    }
                }

                $objOrder = Isotope::getCart()->getDraftOrder();
                $objOrder->checkout_info        = $this->getCheckoutInfo($arrSteps);
                $objOrder->nc_notification      = $this->iso_notifications;
                $objOrder->iso_addToAddressbook = $this->iso_addToAddressbook;
                $objOrder->iso_checkout_skippable = $this->iso_checkout_skippable;
                $objOrder->orderdetails_page = $this->orderCompleteJumpTo;
                $objOrder->email_data           = $this->getNotificationTokensFromSteps($arrSteps, $objOrder);

                // !HOOK: pre-process checkout
                if (isset($GLOBALS['ISO_HOOKS']['preCheckout']) && \is_array($GLOBALS['ISO_HOOKS']['preCheckout'])) {
                    foreach ($GLOBALS['ISO_HOOKS']['preCheckout'] as $callback) {

                        if (System::importStatic($callback[0])->{$callback[1]}($objOrder, $this) === false) {
                            System::log('Callback ' . $callback[0] . '::' . $callback[1] . '() cancelled checkout for Order ID ' . $this->id, __METHOD__, TL_ERROR);

                            static::redirectToStep(self::STEP_FAILED);
                        }
                    }
                }

                $objOrder->lock();

                $strBuffer = $objOrder->hasPayment() ? $objOrder->getPaymentMethod()->checkoutForm($objOrder, $this) : false;

                if ($strBuffer instanceof Response) {
                    throw new ResponseException($strBuffer);
                }

                if (false === $strBuffer) {
                    static::redirectToStep(self::STEP_COMPLETE, $objOrder);
                }

                $this->Template->showForm = false;
                $this->doNotSubmit        = true;
                $arrBuffer                = array(array('html' => $strBuffer, 'class' => $this->strCurrentStep));
                break;

            // Checkout/payment has failed, show the review page again with an error message
            /** @noinspection PhpMissingBreakStatementInspection */
            case self::STEP_FAILED:
                $this->Template->mtype   = 'error';
                $this->Template->message = \strlen(Input::get('reason')) ? Input::get('reason') : $GLOBALS['TL_LANG']['ERR']['orderFailed'];
                $this->strCurrentStep    = 'review';
                // no break

            default:

                // canCheckout will override the template and show a message
                if (!$this->canCheckout()) {
                    return;
                }

                $arrBuffer = $this->generateSteps($this->getSteps());
                break;
        }

        RowClass::withKey('class')->addFirstLast()->applyTo($arrBuffer);

        $this->Template->fields = $arrBuffer;
    }

    /**
     * Run through all steps until we find the current one or one reports failure
     *
     * @param array $arrSteps
     *
     * @return array
     */
    protected function generateSteps(array $arrSteps)
    {
        $arrBuffer = array();
        $intCurrentStep = 0;
        $intTotalSteps  = \count($arrSteps);

        if (!isset($arrSteps[$this->strCurrentStep])) {
            $this->redirectToNextStep();
        }

        $arrStepKeys = array_keys($arrSteps);
        $this->skippableSteps = array();

        /**
         * Run trough all steps until we find the current one or one reports failure
         * @var string                $step
         * @var IsotopeCheckoutStep[] $arrModules
         */
        foreach ($arrSteps as $step => $arrModules) {
            $this->strFormId            = 'iso_mod_checkout_' . $step;
            $this->Template->formId     = $this->strFormId;
            $this->Template->formSubmit = $this->strFormId;

            ++$intCurrentStep;

            $arrBuffer                   = array();
            $this->skippableSteps[$step] = true;

            foreach ($arrModules as $objModule) {
                $arrBuffer[] = array(
                    'class' => StringUtil::standardize($step) . ' ' . $objModule->getStepClass(),
                    'html'  => $objModule->generate()
                );

                if (!$objModule->isSkippable()) {
                    $this->skippableSteps[$step] = false;
                }

                if ($objModule->hasError()) {
                    $this->doNotSubmit = true;
                    $this->skippableSteps[$step]  = false;
                }

                // the user wanted to proceed but the current step is not completed yet
                if ($this->doNotSubmit && $step != $this->strCurrentStep) {
                    static::redirectToStep($step);
                }
            }

            if ($this->skippableSteps[$step] ?? false) {
                unset($arrStepKeys[array_search($step, $arrStepKeys)]);
                $intCurrentStep -= 1;
                $intTotalSteps -= 1;
            }

            if ($step == $this->strCurrentStep) {
                if ($this->skippableSteps[$step] ?? false) {
                    $this->redirectToNextStep();
                }

                global $objPage;
                $pageTitle = sprintf($GLOBALS['TL_LANG']['MSC']['checkoutStep'], $intCurrentStep, $intTotalSteps, ($GLOBALS['TL_LANG']['MSC']['checkout_' . $step] ?: $step)) . ($objPage->pageTitle ?: $objPage->title);

                // Support response context in Contao 4.13
                if (System::getContainer()->has('contao.routing.response_context_accessor')) {
                    $responseContext = System::getContainer()->get('contao.routing.response_context_accessor')->getResponseContext();
                    $htmlDecoder = System::getContainer()->get('contao.string.html_decoder');

                    if ($responseContext && $htmlDecoder && $responseContext->has(HtmlHeadBag::class)) {
                        $responseContext
                            ->get(HtmlHeadBag::class)
                            ->setTitle($htmlDecoder->inputEncodedToPlainText($pageTitle))
                        ;
                        break;
                    }
                }

                $objPage->pageTitle = $pageTitle;
                break;
            }
        }

        $arrStepKeys = array_values($arrStepKeys);

        $this->Template->steps      = $this->generateStepNavigation($arrStepKeys);
        $this->Template->activeStep = $GLOBALS['TL_LANG']['MSC']['activeStep'];

        // Hide back buttons it this is the first step
        if (array_search($this->strCurrentStep, $arrStepKeys) === 0) {
            $this->Template->showPrevious = false;
        }

        // Show "confirm order" button if this is the last step
        if (array_search($this->strCurrentStep, $arrStepKeys) === (\count($arrStepKeys) - 1)) {
            $this->Template->nextClass = 'confirm';
            $this->Template->nextLabel = StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['confirmOrder']);
        }

        // User pressed "back" button
        if (\strlen(Input::post('previousStep'))) {
            $this->redirectToPreviousStep();
        } // Valid input data, redirect to next step
        elseif (Input::post('FORM_SUBMIT') == $this->strFormId && !$this->doNotSubmit) {
            $this->redirectToNextStep();
        }

        return $arrBuffer;
    }

    /**
     * Redirect visitor to the next step in ISO_CHECKOUTSTEP
     */
    protected function redirectToNextStep()
    {
        $arrSteps = array_keys($this->getSteps());
        $intKey   = array_search($this->strCurrentStep, $arrSteps);

        if (false === $intKey) {
            if ($this->iso_forward_review) {
                static::redirectToStep('review');
            }

            $intKey = -1;
        } // redirect to step "process" if the next step is the last one
        elseif (($intKey + 1) == \count($arrSteps)) {
            static::redirectToStep(self::STEP_PROCESS);
        }

        $step = $arrSteps[$intKey + 1];

        if ($this->skippableSteps[$step] ?? false) {
            $this->strCurrentStep = $step;
            $this->redirectToNextStep();
        }

        static::redirectToStep($step);
    }

    /**
     * Redirect visitor to the previous step in ISO_CHECKOUTSTEP
     */
    protected function redirectToPreviousStep()
    {
        $arrSteps = array_keys($this->getSteps());
        $intKey   = array_search($this->strCurrentStep, $arrSteps);

        if (false === $intKey || 0 === $intKey) {
            $intKey = 1;
        }

        $step = $arrSteps[$intKey - 1];

        if ($this->skippableSteps[$step] ?? false) {
            $this->strCurrentStep = $step;
            $this->redirectToPreviousStep();
        }

        static::redirectToStep($step);
    }

    /**
     * Return the checkout information as array
     *
     * @param array $arrSteps
     *
     * @return array
     */
    public function getCheckoutInfo(array $arrSteps = null)
    {
        if (null === $arrSteps) {
            $arrSteps = $this->getSteps();
        }

        $arrCheckoutInfo = array();

        // Run trough all steps to collect checkout information
        /** @var IsotopeCheckoutStep[] $arrModules */
        foreach ($arrSteps as $arrModules) {
            foreach ($arrModules as $objModule) {

                $arrInfo = $objModule->review();

                if (!empty($arrInfo) && \is_array($arrInfo)) {
                    /** @noinspection AdditionOperationOnArraysInspection */
                    $arrCheckoutInfo += $arrInfo;
                }
            }
        }

        RowClass::withKey('class')->addFirstLast()->applyTo($arrCheckoutInfo);

        return $arrCheckoutInfo;
    }

    /**
     * Retrieve the array of notification data for parsing simple tokens
     *
     * @param array                    $arrSteps
     * @param IsotopeProductCollection $objOrder
     *
     * @return array
     */
    protected function getNotificationTokensFromSteps(array $arrSteps, IsotopeProductCollection $objOrder)
    {
        $arrTokens = array();

        // Run through all steps to collect checkout information
        foreach ($arrSteps as $arrModules) {

            /** @var IsotopeCheckoutStep $objModule */
            foreach ($arrModules as $objModule) {
                // Method check is BC for when IsotopeCheckoutStep contained "getNotificationTokens" method
                if ($objModule instanceof IsotopeNotificationTokens || \method_exists($objModule, 'getNotificationTokens')) {
                    $arrTokens = array_merge($arrTokens, $objModule->getNotificationTokens($objOrder));
                }
            }
        }

        return $arrTokens;
    }

    /**
     * Check if the checkout can be executed
     *
     * @return bool
     */
    protected function canCheckout()
    {
        // Redirect to login page if not logged in
        if ('member' === $this->iso_checkout_method && true !== FE_USER_LOGGED_IN) {

            /** @var PageModel $objJump */
            $objJump = PageModel::findPublishedById($this->iso_login_jumpTo);

            if (null === $objJump) {
                $this->Template          = new Template('mod_message');
                $this->Template->type    = 'error';
                $this->Template->message = $GLOBALS['TL_LANG']['ERR']['isoLoginRequired'];

                return false;
            }

            throw new RedirectResponseException($objJump->getAbsoluteUrl());

        }

        if ('guest' === $this->iso_checkout_method && true === FE_USER_LOGGED_IN) {
            $this->Template          = new Template('mod_message');
            $this->Template->type    = 'error';
            $this->Template->message = $GLOBALS['TL_LANG']['ERR']['checkoutNotAllowed'];

            return false;
        }

        // Return error message if cart is empty
        if (Isotope::getCart()->isEmpty()) {
            $this->Template          = new Template('mod_message');
            $this->Template->type    = 'empty';
            $this->Template->message = $GLOBALS['TL_LANG']['MSC']['noItemsInCart'];

            return false;
        }

        // Insufficient cart subtotal
        if (Isotope::getCart()->hasErrors()) {
            if ($this->iso_cart_jumpTo > 0) {

                /** @var PageModel $objJump */
                $objJump = PageModel::findPublishedById($this->iso_cart_jumpTo);

                if (null !== $objJump) {
                    throw new RedirectResponseException($objJump->getAbsoluteUrl());
                }
            }

            $this->Template          = new Template('mod_message');
            $this->Template->type    = 'error';
            $this->Template->message = implode("</p>\n<p class=\"error message\">", Isotope::getCart()->getErrors());

            return false;
        }

        return true;
    }

    /**
     * Returns whether the checkout step by given name can be skipped.
     *
     * @param string $step
     *
     * @return bool
     */
    public function canSkipStep($step)
    {
        return \in_array($step, $this->iso_checkout_skippable, true);
    }

    /**
     * Return array of instantiated checkout step modules
     *
     * @return array
     */
    protected function getSteps()
    {
        $arrOrderConditions = [];
        $arrSteps = [];

        $productTypeIds = [];
        foreach (Isotope::getCart()->getItems() as $objItem) {
            if ($objItem->hasProduct()) {
                $productType = $objItem->getProduct()->getType();
                $productTypeIds[] = null === $productType ? 0 : (int) $productType->id;
            }
        }
        $productTypeIds = array_unique($productTypeIds);

        foreach (StringUtil::deserialize($this->iso_order_conditions, true) as $config) {
            if (empty($config['form'])) {
                continue;
            }

            $configProductTypes = StringUtil::deserialize($config['product_types']);

            if (!empty($configProductTypes) && \is_array($configProductTypes)) {
                switch ($config['product_types_condition']) {
                    case 'onlyAvailable':
                        if (\count(array_diff($productTypeIds, $configProductTypes)) > 0) {
                            continue(2);
                        }
                        break;

                    case 'oneAvailable':
                        if (\count(array_intersect($configProductTypes, $productTypeIds)) === 0) {
                            continue(2);
                        }
                        break;

                    case 'allAvailable':
                        if (\count(array_intersect($configProductTypes, $productTypeIds)) !== \count($configProductTypes)) {
                            continue(2);
                        }
                        break;
                }
            }

            $arrOrderConditions[$config['step']][$config['position']][] = $config['form'];
        }

        foreach ($GLOBALS['ISO_CHECKOUTSTEP'] as $strStep => $arrModules) {
            foreach ($arrModules as $strClass) {
                $objModule = new $strClass($this);

                if (!$objModule instanceof IsotopeCheckoutStep) {
                    throw new \RuntimeException("$strClass has to implement Isotope\\Interfaces\\IsotopeCheckoutStep");
                }

                if (isset($arrOrderConditions[$strClass]['before'])) {
                    foreach ($arrOrderConditions[$strClass]['before'] as $form) {
                        $arrSteps[$strStep][] = new OrderConditions($this, $form);
                    }
                }

                if ($objModule->isAvailable()) {
                    $arrSteps[$strStep][] = $objModule;
                }

                if (isset($arrOrderConditions[$strClass]['after'])) {
                    foreach ($arrOrderConditions[$strClass]['after'] as $form) {
                        $arrSteps[$strStep][] = new OrderConditions($this, $form);
                    }
                }
            }
        }

        return $arrSteps;
    }

    /**
     * Generate checkout step navigation
     *
     * @param array $arrStepKeys
     *
     * @return array
     */
    protected function generateStepNavigation(array $arrStepKeys)
    {
        $arrItems  = array();
        $blnPassed = true;

        foreach ($arrStepKeys as $step) {

            $blnActive = false;
            $href      = '';
            $class     = StringUtil::standardize($step);

            if ($this->strCurrentStep == $step) {
                $blnPassed = false;
                $blnActive = true;
                $class .= ' active';
            } elseif ($blnPassed) {
                $href = static::generateUrlForStep($step);
                $class .= ' passed';
            }

            $arrItems[] = array
            (
                'isActive' => $blnActive,
                'class'    => $class,
                'link'     => $GLOBALS['TL_LANG']['MSC']['checkout_' . $step] ? : $step,
                'href'     => $href,
                'title'    => StringUtil::specialchars(sprintf($GLOBALS['TL_LANG']['MSC']['checkboutStepBack'], ($GLOBALS['TL_LANG']['MSC']['checkout_' . $step] ? : $step))),
            );
        }

        // Add first/last classes
        RowClass::withKey('class')->addFirstLast()->applyTo($arrItems);

        return $arrItems;
    }

    /**
     * Redirect to given checkout step
     *
     * @param string                   $strStep
     * @param IsotopeProductCollection $objCollection
     */
    public static function redirectToStep($strStep, IsotopeProductCollection $objCollection = null)
    {
        Isotope::getCart()->save();

        throw new RedirectResponseException(static::generateUrlForStep($strStep, $objCollection, null, true));
    }

    /**
     * Generate frontend URL for current page including the given checkout step
     *
     * @param string $strStep
     *
     * @return string
     */
    public static function generateUrlForStep($strStep, IsotopeProductCollection $objCollection = null, PageModel $objTarget = null, bool $absolute = false)
    {
        if (null === $objTarget) {
            global $objPage;
            $objTarget = $objPage;
        }

        if (!$GLOBALS['TL_CONFIG']['useAutoItem'] || !\in_array('step', $GLOBALS['TL_AUTO_ITEM'], true)) {
            $strStep = 'step/' . $strStep;
        }

        $strUrl = $absolute ? $objTarget->getAbsoluteUrl('/' . $strStep) : $objTarget->getFrontendUrl('/' . $strStep);

        if (null !== $objCollection) {
            $strUrl = Url::addQueryString('uid=' . $objCollection->getUniqueId(), $strUrl);
        }

        return $strUrl;
    }
}
