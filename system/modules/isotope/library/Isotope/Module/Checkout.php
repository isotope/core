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
use Isotope\Model\Address;
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

        // Set the step from the auto_item parameter
        if ($GLOBALS['TL_CONFIG']['useAutoItem'] && isset($_GET['auto_item']))
        {
            \Input::setGet('step', \Input::get('auto_item'));
        }

        // Do not cache the page
        global $objPage;
        $objPage->cache = 0;

        $this->strCurrentStep = \Input::get('step');

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

                    $this->redirect(\Isotope\Frontend::addQueryStringToUrl('uid=' . $objOrder->uniqid, $this->orderCompleteJumpTo));
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

        if (\Input::get('step') == '') {
            if ($this->iso_forward_review) {
                $this->redirect($this->addToUrl('step=review', true));
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

        if ($this->strCurrentStep == 'failed')
        {
            $this->Database->prepare("UPDATE tl_iso_product_collection SET order_status=? WHERE source_collection_id=?")->execute(Isotope::getConfig()->orderstatus_error, Isotope::getCart()->id);
            $this->Template->mtype = 'error';
            $this->Template->message = strlen(\Input::get('reason')) ? \Input::get('reason') : $GLOBALS['TL_LANG']['ERR']['orderFailed'];
            $this->strCurrentStep = 'review';
        }

        // Run trough all steps until we find the current one or one reports failure
        $intCurrentStep = 0;
        $intTotalSteps = count($this->getSteps());
        foreach ($this->getSteps() as $step => $arrModules)
        {
            $this->strFormId = 'iso_mod_checkout_' . $step;
            $this->Template->formId = $this->strFormId;
            $this->Template->formSubmit = $this->strFormId;
            ++$intCurrentStep;
            $strBuffer = '';

            foreach ($arrModules as $objModule)
            {
                $strBuffer .= $objModule->generate();

                if ($objModule->hasError()) {
                    $this->doNotSubmit = true;
                    $this->Template->showNext = false;
                }

                // the user wanted to proceed but the current step is not completed yet
                if ($this->doNotSubmit && $step != $this->strCurrentStep) {
                    $this->redirect($this->addToUrl('step=' . $step, true));
                }
            }

            if ($step == $this->strCurrentStep)
            {
                global $objPage;
                $objPage->pageTitle = sprintf($GLOBALS['TL_LANG']['MSC']['checkoutStep'], $intCurrentStep, $intTotalSteps, (strlen($GLOBALS['TL_LANG']['MSC']['checkout_' . $step]) ? $GLOBALS['TL_LANG']['MSC']['checkout_' . $step] : $step)) . ($objPage->pageTitle ? $objPage->pageTitle : $objPage->title);
                break;
            }
        }

        if ($this->strCurrentStep == 'process')
        {
            $this->writeOrder();
            $strBuffer = Isotope::getCart()->hasPayment() ? Isotope::getCart()->Payment->checkoutForm($this) : false;

            if ($strBuffer === false)
            {
                $this->redirect($this->addToUrl('step=complete', true));
            }

            $this->Template->showForm = false;
            $this->doNotSubmit = true;
        }

        if ($this->strCurrentStep == 'complete')
        {
            $strBuffer = Isotope::getCart()->hasPayment() ? Isotope::getCart()->Payment->processPayment() : true;

            if ($strBuffer === true)
            {
                // If checkout is successful, complete order and redirect to confirmation page
                if (($objOrder = Order::findOneBy('source_collection_id', Isotope::getCart()->id)) !== null && $objOrder->checkout() && $objOrder->complete())
                {
                    $this->redirect(\Isotope\Frontend::addQueryStringToUrl('uid=' . $objOrder->uniqid, $this->orderCompleteJumpTo));
                }

                // Checkout failed, show error message
                $this->redirect($this->addToUrl('step=failed', true));
            }
            elseif ($strBuffer === false)
            {
                $this->redirect($this->addToUrl('step=failed', true));
            }
            else
            {
                $this->Template->showNext = false;
                $this->Template->showPrevious = false;
            }
        }

        $this->Template->fields = $strBuffer;

        if (!strlen($this->strCurrentStep))
        {
            $this->strCurrentStep = $step;
        }

        // Show checkout steps
        $arrStepKeys = array_keys($this->getSteps());
        $blnPassed = true;
        $total = count($arrStepKeys) - 1;
        $arrSteps = array();

        if ($this->strCurrentStep != 'process' && $this->strCurrentStep != 'complete')
        {
            foreach ($arrStepKeys as $i => $step)
            {
                if ($this->strCurrentStep == $step)
                {
                    $blnPassed = false;
                }

                $blnActive = $this->strCurrentStep == $step ? true : false;

                $arrSteps[] = array
                (
                    'isActive'    => $blnActive,
                    'class'        => 'step_' . $i . (($i == 0) ? ' first' : '') . ($i == $total ? ' last' : '') . ($blnActive ? ' active' : '') . ($blnPassed ? ' passed' : '') . ((!$blnPassed && !$blnActive) ? ' upcoming' : '') . ' '. $step,
                    'label'        => (strlen($GLOBALS['TL_LANG']['MSC']['checkout_' . $step]) ? $GLOBALS['TL_LANG']['MSC']['checkout_' . $step] : $step),
                    'href'        => ($blnPassed ? $this->addToUrl('step=' . $step, true) : ''),
                    'title'        => specialchars(sprintf($GLOBALS['TL_LANG']['MSC']['checkboutStepBack'], (strlen($GLOBALS['TL_LANG']['MSC']['checkout_' . $step]) ? $GLOBALS['TL_LANG']['MSC']['checkout_' . $step] : $step))),
                );
            }
        }

        $this->Template->steps = $arrSteps;
        $this->Template->activeStep = $GLOBALS['TL_LANG']['MSC']['activeStep'];

        // Hide back buttons it this is the first step
        if (array_search($this->strCurrentStep, $arrStepKeys) === 0)
        {
            $this->Template->showPrevious = false;
        }

        // Show "confirm order" button if this is the last step
        elseif (array_search($this->strCurrentStep, $arrStepKeys) === $total)
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
     * @return void
     */
    protected function redirectToNextStep()
    {
        $arrSteps = array_keys($this->getSteps());

        if (!in_array($this->strCurrentStep, $arrSteps))
        {
            $this->redirect($this->addToUrl('step='.array_shift($arrSteps), true));
        }
        else
        {
            // key of the next step
            $intKey = array_search($this->strCurrentStep, $arrSteps) + 1;

            // redirect to step "process" if the next step is the last one
            if ($intKey == count($arrSteps))
            {
                $this->redirect($this->addToUrl('step=process', true));
            }
            else
            {
                $this->redirect($this->addToUrl('step='.$arrSteps[$intKey], true));
            }
        }
    }


    /**
     * Redirect visitor to the previous step in ISO_CHECKOUTSTEP
     * @return void
     */
    protected function redirectToPreviousStep()
    {
        $arrSteps = array_keys($this->getSteps());

        if (!in_array($this->strCurrentStep, $arrSteps))
        {
            $this->redirect($this->addToUrl('step='.array_shift($arrSteps), true));
        }
        else
        {
            $strKey = array_search($this->strCurrentStep, $arrSteps);
            $this->redirect($this->addToUrl('step='.$arrSteps[($strKey-1)], true));
        }
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
    protected function getCheckoutInfo()
    {
        if (!is_array($this->arrCheckoutInfo))
        {
            $arrCheckoutInfo = array();

            // Run trough all steps to collect checkout information
            foreach ($GLOBALS['ISO_CHECKOUT_STEPS'] as $step => $arrCallbacks)
            {
                foreach ($arrCallbacks as $callback)
                {
                    if ($callback[0] == 'ModuleIsotopeCheckout')
                    {
                        $arrInfo = $this->{$callback[1]}(true);
                    }
                    else
                    {
                        $this->import($callback[0]);
                        $arrInfo = $this->{$callback[0]}->{$callback[1]}($this, true);
                    }

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
     * Override parent addToUrl function. Use generateFrontendUrl if we want to remove all parameters.
     * @param string
     * @param boolean
     * @return string
     */
    public static function addToUrl($strRequest, $blnIgnoreParams=false)
    {
        if ($blnIgnoreParams)
        {
            global $objPage;

            // Support for auto_item parameter
            if ($GLOBALS['TL_CONFIG']['useAutoItem'])
            {
                $strRequest = str_replace('step=', '', $strRequest);
            }

            return \Controller::generateFrontendUrl($objPage->row(), '/' . str_replace(array('=', '&amp;', '&'), '/', $strRequest));
        }

        return parent::addToUrl($strRequest, $blnIgnoreParams);
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
        if (Isotope::getConfig()->cartMinSubtotal > 0 && Isotope::getConfig()->cartMinSubtotal > Isotope::getCart()->getSubtotal())
        {
            $this->Template = new \Isotope\Template('mod_message');
            $this->Template->type = 'error';
            $this->Template->message = sprintf($GLOBALS['TL_LANG']['ERR']['cartMinSubtotal'], Isotope::formatPriceWithCurrency(Isotope::getConfig()->cartMinSubtotal));

            return false;
        }

        // Redirect to login page if not logged in
        if ($this->iso_checkout_method == 'member' && FE_USER_LOGGED_IN !== true)
        {
            $objPage = $this->Database->prepare("SELECT id,alias FROM tl_page WHERE id=?")->limit(1)->execute($this->iso_login_jumpTo);

            if (!$objPage->numRows)
            {
                $this->Template = new \Isotope\Template('mod_message');
                $this->Template->type = 'error';
                $this->Template->message = $GLOBALS['TL_LANG']['ERR']['isoLoginRequired'];

                return false;
            }

            $this->redirect($this->generateFrontendUrl($objPage->row()));
        }
        elseif ($this->iso_checkout_method == 'guest' && FE_USER_LOGGED_IN === true)
        {
            $this->Template = new \Isotope\Template('mod_message');
            $this->Template->type = 'error';
            $this->Template->message = 'User checkout not allowed';

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

                    if ($objModule->isAvailable()) {
                        $arrSteps[$strStep][] = $objModule;
                    }
                }
            }
        }

        return $arrSteps;
    }
}
