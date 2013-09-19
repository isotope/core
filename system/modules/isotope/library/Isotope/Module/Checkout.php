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

namespace Isotope\Module;

use Isotope\Isotope;
use Isotope\Interfaces\IsotopeCheckoutStep;
use Isotope\Model\Payment;
use Isotope\Model\Shipping;
use Isotope\Model\ProductCollection\Order;


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
     * Order data. Each checkout step can provide key-value (string) data for the order email.
     * @var array
     */
    public $arrOrderData = array();

    /**
     * Do not submit form
     * @var boolean
     */
    public $doNotSubmit = false;

    /**
     * Template
     * @var string
     */
    protected $strTemplate = 'mod_iso_checkout';

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
     * Checkout info. Contains an overview about each step to show on the review page (eg. address, payment & shipping method).
     * @var array
     */
    protected $arrCheckoutInfo;

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
        if (TL_MODE == 'BE')
        {
            $objTemplate = new \BackendTemplate('be_wildcard');

            $objTemplate->wildcard = '### ISOTOPE CHECKOUT ###';
            $objTemplate->title = $this->headline;
            $objTemplate->id = $this->id;
            $objTemplate->link = $this->name;
            $objTemplate->href = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id=' . $this->id;

            return $objTemplate->parse();
        }

        $this->strCurrentStep = \Isotope\Frontend::getAutoItem('step');

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
     * @return void
     */
    protected function compile()
    {
        // Order has been completed (postsale request)
        if ($this->strCurrentStep == 'complete' && \Input::get('uid') != '')
        {
            if (($objOrder = Order::findOneByUniqid(\Input::get('uid'))) !== null)
            {
                // Order is complete, forward to confirmation page
                if ($objOrder->complete())
                {
                    \Isotope\Frontend::clearTimeout();

                    \Controller::redirect(\Isotope\Frontend::addQueryStringToUrl('uid=' . $objOrder->uniqid, $this->orderCompleteJumpTo));
                }

                // Order is not complete, wait for it
                if (\Isotope\Frontend::setTimeout())
                {
                    $this->Template = new \Isotope\Template('mod_message');
                    $this->Template->type = 'processing';
                    $this->Template->message = $GLOBALS['TL_LANG']['MSC']['payment_processing'];

                    return;
                }
            }
        }

        if (!$this->canCheckout()) {
            return;
        }

        if (\Isotope\Frontend::getAutoItem('step') == '') {
            if ($this->iso_forward_review) {
                static::redirectToStep('review');
            }

            $this->redirectToNextStep();
        }

        // Default template settings. Must be set at beginning so they can be overwritten later (eg. trough callback)
        $this->Template->action = ampersand(\Environment::get('request'), ENCODE_AMPERSANDS);
        $this->Template->formId = $this->strFormId;
        $this->Template->formSubmit = $this->strFormId;
        $this->Template->enctype = 'application/x-www-form-urlencoded';
        $this->Template->previousLabel = specialchars($GLOBALS['TL_LANG']['MSC']['previousStep']);
        $this->Template->nextLabel = specialchars($GLOBALS['TL_LANG']['MSC']['nextStep']);
        $this->Template->nextClass = 'next';
        $this->Template->showPrevious = true;
        $this->Template->showNext = true;
        $this->Template->showForm = true;

        if ($this->strCurrentStep == 'failed') {
            $this->Template->mtype = 'error';
            $this->Template->message = strlen(\Input::get('reason')) ? \Input::get('reason') : $GLOBALS['TL_LANG']['ERR']['orderFailed'];
            $this->strCurrentStep = 'review';
        }

        // Run trough all steps until we find the current one or one reports failure
        $intCurrentStep = 0;
        $intTotalSteps = count($this->getSteps());
        foreach ($this->getSteps() as $step => $arrModules) {
            $this->strFormId = 'iso_mod_checkout_' . $step;
            $this->Template->formId = $this->strFormId;
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

        if ($this->strCurrentStep == 'process') {
            $this->writeOrder();
            $strBuffer = Isotope::getCart()->hasPayment() ? Isotope::getCart()->getPaymentMethod()->checkoutForm($this) : false;

            if ($strBuffer === false) {
                static::redirectToStep('complete');
            }

            $this->Template->showForm = false;
            $this->doNotSubmit = true;
            $arrBuffer = array(array('html'=>$strBuffer, 'class'=>$this->strCurrentStep));
        } else if ($this->strCurrentStep == 'complete') {
            $strBuffer = Isotope::getCart()->hasPayment() ? Isotope::getCart()->getPaymentMethod()->processPayment() : true;

            if ($strBuffer === true) {
                // If checkout is successful, complete order and redirect to confirmation page
                if (($objOrder = Order::findOneBy('source_collection_id', Isotope::getCart()->id)) !== null && $objOrder->checkout() && $objOrder->complete()) {
                    \Controller::redirect(\Isotope\Frontend::addQueryStringToUrl('uid=' . $objOrder->uniqid, $this->orderCompleteJumpTo));
                }

                // Checkout failed, show error message
                static::redirectToStep('failed');
            } elseif ($strBuffer === false) {
                static::redirectToStep('failed');
            } else {
                $this->Template->showNext = false;
                $this->Template->showPrevious = false;
                $arrBuffer = array(array('html'=>$strBuffer, 'class'=>$this->strCurrentStep));
            }
        }

        $this->Template->fields = \Isotope\Frontend::generateRowClass($arrBuffer, '', 'class', 0, ISO_CLASS_FIRSTLAST);

        if (!strlen($this->strCurrentStep))
        {
            $this->strCurrentStep = $step;
        }

        // Show checkout steps
        $this->Template->steps = $this->generateSteps();
        $this->Template->activeStep = $GLOBALS['TL_LANG']['MSC']['activeStep'];

        $arrStepKeys = array_keys($this->getSteps());

        // Hide back buttons it this is the first step
        if (array_search($this->strCurrentStep, $arrStepKeys) === 0)
        {
            $this->Template->showPrevious = false;
        }

        // Show "confirm order" button if this is the last step
        elseif (array_search($this->strCurrentStep, $arrStepKeys) === (count($arrStepKeys) - 1))
        {
            $this->Template->nextClass = 'confirm';
            $this->Template->nextLabel = specialchars($GLOBALS['TL_LANG']['MSC']['confirmOrder']);
        }

        // User pressed "back" button
        if (strlen(\Input::post('previousStep')))
        {
            $this->redirectToPreviousStep();
        }

        // Valid input data, redirect to next step
        elseif (\Input::post('FORM_SUBMIT') == $this->strFormId && !$this->doNotSubmit)
        {
            $this->redirectToNextStep();
        }
    }


    /**
     * Redirect visitor to the next step in ISO_CHECKOUTSTEP
     */
    protected function redirectToNextStep()
    {
        $arrSteps = array_keys($this->getSteps());
        $intKey = array_search($this->strCurrentStep, $arrSteps);

        if (false === $intKey) {
            $intKey = -1;
        }

        // redirect to step "process" if the next step is the last one
        elseif (($intKey+1) == count($arrSteps)) {
            static::redirectToStep('process');
        }

        static::redirectToStep($arrSteps[$intKey+1]);
    }


    /**
     * Redirect visitor to the previous step in ISO_CHECKOUTSTEP
     */
    protected function redirectToPreviousStep()
    {
        $arrSteps = array_keys($this->getSteps());
        $intKey = array_search($this->strCurrentStep, $arrSteps);

        if (false === $intKey || 0 === $intKey) {
            $intKey = 1;
        }

        static::redirectToStep($arrSteps[($intKey-1)]);
    }


    /**
     * Save the order
     * @return void
     */
    protected function writeOrder()
    {
        $objCart = Isotope::getCart();
        $objOrder = Order::findOneBy('source_collection_id', $objCart->id);

        if (null === $objOrder) {
            $objOrder = new Order();
        }

        $objOrder->setSourceCollection($objCart);

        $objOrder->checkout_info        = $this->getCheckoutInfo();
        $objOrder->iso_sales_email      = $this->iso_sales_email ? $this->iso_sales_email : (($GLOBALS['TL_ADMIN_NAME'] != '') ? sprintf('%s <%s>', $GLOBALS['TL_ADMIN_NAME'], $GLOBALS['TL_ADMIN_EMAIL']) : $GLOBALS['TL_ADMIN_EMAIL']);
        $objOrder->iso_mail_admin       = $this->iso_mail_admin;
        $objOrder->iso_mail_customer    = $this->iso_mail_customer;
        $objOrder->iso_addToAddressbook = $this->iso_addToAddressbook;
        $objOrder->email_data = $this->arrOrderData;

        $objOrder->save();
    }


    /**
     * Return the checkout information as array
     * @return array
     */
    public function getCheckoutInfo()
    {
        if (!is_array($this->arrCheckoutInfo))
        {
            $arrCheckoutInfo = array();

            // Run trough all steps to collect checkout information
            foreach ($this->getSteps() as $arrModules)
            {
                foreach ($arrModules as $objModule)
                {
                    $arrInfo = $objModule->review();

                    if (is_array($arrInfo) && !empty($arrInfo))
                    {
                        $arrCheckoutInfo += $arrInfo;
                    }
                }
            }

            reset($arrCheckoutInfo);
            $arrCheckoutInfo[key($arrCheckoutInfo)]['class'] .= ' first';
            end($arrCheckoutInfo);
            $arrCheckoutInfo[key($arrCheckoutInfo)]['class'] .= ' last';

            $this->arrCheckoutInfo = $arrCheckoutInfo;
        }

        return $this->arrCheckoutInfo;
    }


    /**
     * Check if the checkout can be executed
     * @return  bool
     */
    protected function canCheckout()
    {
        // Return error message if cart is empty
        if (Isotope::getCart()->isEmpty()) {
            $this->Template = new \Isotope\Template('mod_message');
            $this->Template->type = 'empty';
            $this->Template->message = $GLOBALS['TL_LANG']['MSC']['noItemsInCart'];

            return false;
        }

        // Insufficient cart subtotal
        if (Isotope::getCart()->hasErrors())
        {
            if ($this->iso_cart_jumpTo > 0) {
                $objJump = \PageModel::findWithDetails($this->iso_cart_jumpTo);

                if (null !== $objJump) {
                    \Controller::redirect(\Controller::generateFrontendUrl($objJump->row(), null, $objJump->language));
                }
            }

            $this->Template = new \Isotope\Template('mod_message');
            $this->Template->type = 'error';
            $this->Template->message = implode("</p>\n<p class=\"error message\">", Isotope::getCart()->getErrors());

            return false;
        }

        // Redirect to login page if not logged in
        if ($this->iso_checkout_method == 'member' && FE_USER_LOGGED_IN !== true)
        {
            $objPage = \Database::getInstance()->prepare("SELECT id,alias FROM tl_page WHERE id=?")->limit(1)->execute($this->iso_login_jumpTo);

            if (!$objPage->numRows)
            {
                $this->Template = new \Isotope\Template('mod_message');
                $this->Template->type = 'error';
                $this->Template->message = $GLOBALS['TL_LANG']['ERR']['isoLoginRequired'];

                return false;
            }

            \Controller::redirect(\Controller::generateFrontendUrl($objPage->row()));
        }
        elseif ($this->iso_checkout_method == 'guest' && FE_USER_LOGGED_IN === true)
        {
            $this->Template = new \Isotope\Template('mod_message');
            $this->Template->type = 'error';
            $this->Template->message = $GLOBALS['TL_LANG']['ERR']['checkoutNotAllowed'];

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
        static $arrSteps;

        if (null === $arrSteps) {

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
        }

        return $arrSteps;
    }

    /**
     * Generate checkout step navigation
     * @return  array
     */
    protected function generateSteps()
    {
        if ($this->strCurrentStep == 'process' || $this->strCurrentStep == 'complete') {
            return array();
        }

        $arrItems = array();
        $blnPassed = true;

        foreach (array_keys($this->getSteps()) as $step) {

            $blnActive = false;
            $href = '';
            $class = standardize($step);

            if ($this->strCurrentStep == $step) {
                $blnPassed = false;
                $blnActive = true;
                $class .= ' active';
            }
            elseif ($blnPassed) {
                $href = static::generateUrlForStep($step);
                $class .= ' passed';
            }

            $arrItems[] = array
            (
                'isActive'  => $blnActive,
                'class'     => $class,
                'link'      => ($GLOBALS['TL_LANG']['MSC']['checkout_' . $step] ?: $step),
                'href'      => $href,
                'title'     => specialchars(sprintf($GLOBALS['TL_LANG']['MSC']['checkboutStepBack'], ($GLOBALS['TL_LANG']['MSC']['checkout_' . $step] ?: $step))),
            );
        }

        if (empty($arrItems)) {
            return array();
        }

        // Add first/last classes
        $arrItems[0]['class'] .= ' first';
        $arrItems[count($arrItems)-1]['class'] .= ' last';

        return $arrItems;
    }


    /**
     * Redirect to given checkout step
     * @param   string
     */
    public static function redirectToStep($strStep)
    {
        \Controller::redirect(static::generateUrlForStep($strStep));
    }

    /**
     * Generate frontend URL for current page including the given checkout step
     * @param   string
     * @return  string
     */
    public static function generateUrlForStep($strStep)
    {
        global $objPage;

        $strUrlParam = Isotope::getConfig()->getUrlParam('step');

        if ($strUrlParam) {
            $strStep = $strUrlParam . '/' . $strStep;
        }

        return \Controller::generateFrontendUrl($objPage->row(), '/' . $strStep);
    }
}
