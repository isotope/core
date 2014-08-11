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

namespace Isotope\Module;

use Haste\Generator\RowClass;
use Isotope\Interfaces\IsotopeCheckoutStep;
use Isotope\Interfaces\IsotopeProductCollection;
use Isotope\Isotope;
use Isotope\Model\Payment;
use Isotope\Model\ProductCollection\Order;
use Isotope\Model\Shipping;


/**
 * Class ModuleIsotopeCheckout
 * Front end module Isotope "checkout".
 *
 * @copyright  Isotope eCommerce Workgroup 2009-2012
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
 * @author     Yanick Witschi <yanick.witschi@terminal42.ch>
 */
class Checkout extends Module
{

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
     * Form ID
     * @var string
     */
    protected $strFormId = 'iso_mod_checkout';


    /**
     * Display a wildcard in the back end
     * @return string
     */
    public function generate()
    {
        if (TL_MODE == 'BE') {
            $objTemplate = new \BackendTemplate('be_wildcard');

            $objTemplate->wildcard = '### ISOTOPE CHECKOUT ###';
            $objTemplate->title    = $this->headline;
            $objTemplate->id       = $this->id;
            $objTemplate->link     = $this->name;
            $objTemplate->href     = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id=' . $this->id;

            return $objTemplate->parse();
        }

        $this->strCurrentStep = \Haste\Input\Input::getAutoItem('step');

        if ($this->strCurrentStep == '') {
            $this->redirectToNextStep();
        }

        return parent::generate();
    }

    /**
     * Returns the current form ID
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
        $this->Template->action        = ampersand(\Environment::get('request'), ENCODE_AMPERSANDS);
        $this->Template->formId        = $this->strFormId;
        $this->Template->formSubmit    = $this->strFormId;
        $this->Template->enctype       = 'application/x-www-form-urlencoded';
        $this->Template->previousLabel = specialchars($GLOBALS['TL_LANG']['MSC']['previousStep']);
        $this->Template->nextLabel     = specialchars($GLOBALS['TL_LANG']['MSC']['nextStep']);
        $this->Template->nextClass     = 'next';
        $this->Template->showPrevious  = true;
        $this->Template->showNext      = true;
        $this->Template->showForm      = true;
        $this->Template->steps         = array();

        // These steps are handled internally by the checkout module and are not in the config array
        switch ($this->strCurrentStep) {

            // Complete order after successful payment
            // At this stage, we do no longer use the client's cart but the order through UID in URL
            case 'complete':
                if (($objOrder = Order::findOneBy('uniqid', (string) \Input::get('uid'))) === null) {
                    if (Isotope::getCart()->isEmpty()) {
                        /** @type \PageError404 $objHandler */
                        $objHandler = new $GLOBALS['TL_PTY']['error_404']();
                        $objHandler->generate((int) $GLOBALS['objPage']->id);
                    } else {
                        static::redirectToStep('failed');
                    }
                }

                $strBuffer = $objOrder->hasPayment() ? $objOrder->getPaymentMethod()->processPayment($objOrder, $this) : true;

                // true means the payment is successful and order should be completed
                if ($strBuffer === true) {
                    // If checkout is successful, complete order and redirect to confirmation page
                    if ($objOrder->checkout() && $objOrder->complete()) {
                        \Controller::redirect(\Haste\Util\Url::addQueryString('uid=' . $objOrder->uniqid, $this->orderCompleteJumpTo));
                    }

                    // Checkout failed, show error message
                    static::redirectToStep('failed');
                }

                // False means payment has failed
                elseif ($strBuffer === false) {
                    static::redirectToStep('failed');
                }

                // Otherwise we assume a string that shows a message to customer
                else {
                    $this->Template->showNext     = false;
                    $this->Template->showPrevious = false;
                    $arrBuffer                    = array(array('html' => $strBuffer, 'class' => $this->strCurrentStep));
                }
                break;

            // Process order and initiate payment method if necessary
            case 'process':

                $arrSteps = $this->getSteps();

                // Make sure all steps have passed successfully
                foreach ($arrSteps as $step => $arrModules) {
                    foreach ($arrModules as $objModule) {
                        $objModule->generate();

                        if ($objModule->hasError()) {
                            static::redirectToStep($step);
                        }
                    }
                }

                $objOrder = Order::createFromCollection(Isotope::getCart());

                $objOrder->checkout_info        = $this->getCheckoutInfo($arrSteps);
                $objOrder->nc_notification      = $this->nc_notification;
                $objOrder->iso_addToAddressbook = $this->iso_addToAddressbook;
                $objOrder->email_data           = $this->getNotificationTokensFromSteps($arrSteps, $objOrder);

                $objOrder->save();

                $strBuffer = Isotope::getCart()->hasPayment() ? Isotope::getCart()->getPaymentMethod()->checkoutForm($objOrder, $this) : false;

                if ($strBuffer === false) {
                    static::redirectToStep('complete', $objOrder);
                }

                $this->Template->showForm = false;
                $this->doNotSubmit        = true;
                $arrBuffer                = array(array('html' => $strBuffer, 'class' => $this->strCurrentStep));
                break;

            // Checkout/payment has failed, show the review page again with an error message
            case 'failed':
                $this->Template->mtype   = 'error';
                $this->Template->message = strlen(\Input::get('reason')) ? \Input::get('reason') : $GLOBALS['TL_LANG']['ERR']['orderFailed'];
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
     * @param   array
     * @return  array
     */
    protected function generateSteps(array $arrSteps)
    {
        $intCurrentStep = 0;
        $intTotalSteps  = count($arrSteps);

        if (!isset($arrSteps[$this->strCurrentStep])) {
            $this->redirectToNextStep();
        }

        // Run trough all steps until we find the current one or one reports failure
        foreach ($arrSteps as $step => $arrModules) {
            $this->strFormId            = 'iso_mod_checkout_' . $step;
            $this->Template->formId     = $this->strFormId;
            $this->Template->formSubmit = $this->strFormId;

            $intCurrentStep += 1;
            $arrBuffer = array();

            foreach ($arrModules as $objModule) {

                $arrBuffer[] = array(
                    'class' => standardize($step) . ' ' . standardize($objModule->getStepClass()),
                    'html'  => $objModule->generate()
                );

                if ($objModule->hasError()) {
                    $this->doNotSubmit = true;
                }

                // the user wanted to proceed but the current step is not completed yet
                if ($this->doNotSubmit && $step != $this->strCurrentStep) {
                    static::redirectToStep($step);
                }
            }

            if ($step == $this->strCurrentStep) {
                global $objPage;
                $objPage->pageTitle = sprintf($GLOBALS['TL_LANG']['MSC']['checkoutStep'], $intCurrentStep, $intTotalSteps, ($GLOBALS['TL_LANG']['MSC']['checkout_' . $step] ?: $step)) . ($objPage->pageTitle ?: $objPage->title);
                break;
            }
        }

        $arrStepKeys = array_keys($arrSteps);

        $this->Template->steps      = $this->generateStepNavigation($arrStepKeys);
        $this->Template->activeStep = $GLOBALS['TL_LANG']['MSC']['activeStep'];

        // Hide back buttons it this is the first step
        if (array_search($this->strCurrentStep, $arrStepKeys) === 0) {
            $this->Template->showPrevious = false;
        } // Show "confirm order" button if this is the last step
        elseif (array_search($this->strCurrentStep, $arrStepKeys) === (count($arrStepKeys) - 1)) {
            $this->Template->nextClass = 'confirm';
            $this->Template->nextLabel = specialchars($GLOBALS['TL_LANG']['MSC']['confirmOrder']);
        }

        // User pressed "back" button
        if (strlen(\Input::post('previousStep'))) {
            $this->redirectToPreviousStep();
        } // Valid input data, redirect to next step
        elseif (\Input::post('FORM_SUBMIT') == $this->strFormId && !$this->doNotSubmit) {
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
        elseif (($intKey + 1) == count($arrSteps)) {
            static::redirectToStep('process');
        }

        static::redirectToStep($arrSteps[$intKey + 1]);
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

        static::redirectToStep($arrSteps[($intKey - 1)]);
    }

    /**
     * Return the checkout information as array
     * @param   array|null
     * @return  array
     */
    public function getCheckoutInfo(array $arrSteps = null)
    {
        if (null === $arrSteps) {
            $arrSteps = $this->getSteps();
        }

        $arrCheckoutInfo = array();

        // Run trough all steps to collect checkout information
        foreach ($arrSteps as $arrModules) {
            foreach ($arrModules as $objModule) {

                $arrInfo = $objModule->review();

                if (!empty($arrInfo) && is_array($arrInfo)) {
                    $arrCheckoutInfo += $arrInfo;
                }
            }
        }

        RowClass::withKey('class')->addFirstLast()->applyTo($arrCheckoutInfo);

        return $arrCheckoutInfo;
    }

    /**
     * Retrieve the array of notification data for parsing simple tokens
     * @param   array
     * @param   IsotopeProductCollection
     * @return  array
     */
    protected function getNotificationTokensFromSteps(array $arrSteps, IsotopeProductCollection $objOrder)
    {
        $arrTokens = array();

        // Run trough all steps to collect checkout information
        foreach ($arrSteps as $arrModules) {
            foreach ($arrModules as $objModule) {
                $arrTokens = array_merge($arrTokens, $objModule->getNotificationTokens($objOrder));
            }
        }

        return $arrTokens;
    }

    /**
     * Check if the checkout can be executed
     * @return  bool
     */
    protected function canCheckout()
    {
        // Redirect to login page if not logged in
        if ($this->iso_checkout_method == 'member' && FE_USER_LOGGED_IN !== true) {
            $objPage = \Database::getInstance()->prepare("SELECT id,alias FROM tl_page WHERE id=?")->limit(1)->execute($this->iso_login_jumpTo);

            if (!$objPage->numRows) {
                $this->Template          = new \Isotope\Template('mod_message');
                $this->Template->type    = 'error';
                $this->Template->message = $GLOBALS['TL_LANG']['ERR']['isoLoginRequired'];

                return false;
            }

            \Controller::redirect(\Controller::generateFrontendUrl($objPage->row()));

        } elseif ($this->iso_checkout_method == 'guest' && FE_USER_LOGGED_IN === true) {
            $this->Template          = new \Isotope\Template('mod_message');
            $this->Template->type    = 'error';
            $this->Template->message = $GLOBALS['TL_LANG']['ERR']['checkoutNotAllowed'];

            return false;
        }

        // Return error message if cart is empty
        if (Isotope::getCart()->isEmpty()) {
            $this->Template          = new \Isotope\Template('mod_message');
            $this->Template->type    = 'empty';
            $this->Template->message = $GLOBALS['TL_LANG']['MSC']['noItemsInCart'];

            return false;
        }

        // Insufficient cart subtotal
        if (Isotope::getCart()->hasErrors()) {
            if ($this->iso_cart_jumpTo > 0) {
                $objJump = \PageModel::findWithDetails($this->iso_cart_jumpTo);

                if (null !== $objJump) {
                    \Controller::redirect(\Controller::generateFrontendUrl($objJump->row(), null, $objJump->language));
                }
            }

            $this->Template          = new \Isotope\Template('mod_message');
            $this->Template->type    = 'error';
            $this->Template->message = implode("</p>\n<p class=\"error message\">", Isotope::getCart()->getErrors());

            return false;
        }

        return true;
    }

    /**
     * Return array of instantiated checkout step modules
     * @return  array
     */
    protected function getSteps()
    {
        $arrSteps = array();

        foreach ($GLOBALS['ISO_CHECKOUTSTEP'] as $strStep => $arrModules) {
            foreach ($arrModules as $strClass) {

                $objModule = new $strClass($this);

                if (!$objModule instanceof IsotopeCheckoutStep) {
                    throw new \RuntimeException("$strClass has to implement Isotope\Interfaces\IsotopeCheckoutStep");
                }

                if ($objModule->isAvailable()) {
                    $arrSteps[$strStep][] = $objModule;
                }
            }
        }

        return $arrSteps;
    }

    /**
     * Generate checkout step navigation
     * @param   array
     * @return  array
     */
    protected function generateStepNavigation(array $arrStepKeys)
    {
        $arrItems  = array();
        $blnPassed = true;

        foreach ($arrStepKeys as $step) {

            $blnActive = false;
            $href      = '';
            $class     = standardize($step);

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
                'link'     => ($GLOBALS['TL_LANG']['MSC']['checkout_' . $step] ? : $step),
                'href'     => $href,
                'title'    => specialchars(sprintf($GLOBALS['TL_LANG']['MSC']['checkboutStepBack'], ($GLOBALS['TL_LANG']['MSC']['checkout_' . $step] ? : $step))),
            );
        }

        // Add first/last classes
        RowClass::withKey('class')->addFirstLast()->applyTo($arrItems);

        return $arrItems;
    }

    /**
     * Redirect to given checkout step
     * @param   string
     * @param   IsotopeProductCollection
     */
    public static function redirectToStep($strStep, IsotopeProductCollection $objCollection = null)
    {
        \Controller::redirect(static::generateUrlForStep($strStep, $objCollection));
    }

    /**
     * Generate frontend URL for current page including the given checkout step
     * @param   string
     * @param   IsotopeProductCollection
     * @param   \PageModel $objPage
     * @return  string
     */
    public static function generateUrlForStep($strStep, IsotopeProductCollection $objCollection = null, \PageModel $objTarget = null)
    {
        if (null === $objTarget) {
            global $objPage;
            $objTarget = $objPage;
        }

        if (!$GLOBALS['TL_CONFIG']['useAutoItem'] || !in_array('step', $GLOBALS['TL_AUTO_ITEM'])) {
            $strStep = 'step/' . $strStep;
        }

        $strUrl = \Controller::generateFrontendUrl($objTarget->row(), '/' . $strStep, $objTarget->language);

        if (null !== $objCollection) {
            $strUrl = \Haste\Util\Url::addQueryString('uid=' . $objCollection->uniqid, $strUrl);
        }

        return $strUrl;
    }
}
