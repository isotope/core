<?php

/*
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009 - 2019 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\CheckoutStep;

use Contao\FormModel;
use Contao\Input;
use Contao\StringUtil;
use Contao\System;
use Haste\Form\Form;
use Isotope\Interfaces\IsotopeCheckoutStep;
use Isotope\Interfaces\IsotopeNotificationTokens;
use Isotope\Interfaces\IsotopeProductCollection;
use Isotope\Module\Checkout;

class OrderConditions extends CheckoutStep implements IsotopeCheckoutStep, IsotopeNotificationTokens
{
    /**
     * @var int
     */
    private $formId;

    /**
     * Haste form
     * @var \Haste\Form\Form
     */
    protected $objForm;

    public function __construct(Checkout $objModule, $formId)
    {
        parent::__construct($objModule);

        $this->formId = $formId;
    }


    /**
     * Returns true if order conditions are defined
     * @return  bool
     */
    public function isAvailable()
    {
        return true;
    }

    /**
     * Generate the checkout step
     * @return  string
     */
    public function generate()
    {
        $this->objForm = new Form($this->objModule->getFormId(), 'POST', function (Form $form) {
            return Input::post('FORM_SUBMIT') === $form->getFormId();
        });

        $objFormConfig = FormModel::findByPk($this->formId);

        if (null === $objFormConfig) {
            throw new \InvalidArgumentException('Order condition form "' . $this->formId . '" not found.');
        }

        if (isset($objFormConfig->tableless)) {
            $this->objForm->setTableless($objFormConfig->tableless);
        }

        $this->objForm->addFieldsFromFormGenerator(
            $this->formId,
            function ($strName, &$arrDca) {
                $arrDca['value'] = $_SESSION['CHECKOUT_DATA'][$strName] ?? $arrDca['value'];

                return true;
            }
        );

        if (!empty($GLOBALS['ISO_HOOKS']['orderConditions']) && \is_array($GLOBALS['ISO_HOOKS']['orderConditions'])) {
            foreach ($GLOBALS['ISO_HOOKS']['orderConditions'] as $callback) {
                System::importStatic($callback[0])->{$callback[1]}($this->objForm, $this->objModule);
            }
        }

        if (!$this->objForm->hasFields()) {
            $this->blnError = false;
            return '';
        }

        // Change enctype if there are uploads
        if ($this->objForm->hasUploads()) {
            $this->objModule->Template->enctype = 'multipart/form-data';
        }

        if ($this->objForm->isSubmitted()) {
            $this->blnError = !$this->objForm->validate();

            $_SESSION['CHECKOUT_DATA'] = \is_array($_SESSION['CHECKOUT_DATA'] ?? null) ? $_SESSION['CHECKOUT_DATA'] : array();
            foreach (array_keys($this->objForm->getFormFields()) as $strField) {
                if ($this->objForm->getWidget($strField) instanceof \uploadable) {
                    if (isset($_SESSION['FILES'][$strField])) {
                        $arrFile = $_SESSION['FILES'][$strField];
                        $varValue = str_replace(TL_ROOT . '/', '', \dirname($arrFile['tmp_name'])) . '/' . rawurlencode($arrFile['name']);
                    } else {
                        $varValue = null;
                    }
                } else {
                    $varValue = $this->objForm->fetch($strField);
                }

                $_SESSION['CHECKOUT_DATA'][$strField] = $varValue;
            }

        } else {
            $blnError = false;

            $_SESSION['CHECKOUT_DATA'] = \is_array($_SESSION['CHECKOUT_DATA'] ?? null) ? $_SESSION['CHECKOUT_DATA'] : array();
            foreach (array_keys($this->objForm->getFormFields()) as $strField) {

                // Clone widget because otherwise we add errors to the original widget instance
                $objClone = clone $this->objForm->getWidget($strField);
                if ($objClone instanceof \uploadable) {
                    $_FILES[$strField] = $_SESSION['FILES'][$strField] ?? null;
                } else {
                    Input::setPost($strField, $_SESSION['CHECKOUT_DATA'][$strField] ?? null);
                }
                $objClone->validate();

                if ($objClone->hasErrors()) {
                    $blnError = true;
                    break;
                }
            }

            $this->blnError = $blnError;
        }

        $objTemplate = new \Isotope\Template('iso_checkout_order_conditions');
        $this->objForm->addToTemplate($objTemplate);

        return $objTemplate->parse();
    }

    /**
     * Return review information for last page of checkout
     * @return  string
     */
    public function review()
    {
        return '';
    }

    /**
     * Return array of tokens for notification
     *
     *
     * @return array
     */
    public function getNotificationTokens(IsotopeProductCollection $objCollection)
    {
        $arrTokens = array();

        foreach ($this->objForm->getFormFields() as $strField => $arrConfig) {
            $varValue = null;

            if (isset($_SESSION['CHECKOUT_DATA'][$strField])) {
                $varValue = $_SESSION['CHECKOUT_DATA'][$strField];

                if ('textarea' === $arrConfig['type']) {
                    $varValue = nl2br($varValue);
                }
            }

            if (isset($GLOBALS['ISO_HOOKS']['getOrderConditionsValue'])
                && \is_array($GLOBALS['ISO_HOOKS']['getOrderConditionsValue'])
            ) {
                foreach ($GLOBALS['ISO_HOOKS']['getOrderConditionsValue'] as $callback) {
                    $varValue = System::importStatic($callback[0])->{$callback[1]}(
                        $strField,
                        $varValue,
                        $arrConfig,
                        $this->objForm
                    );
                }
            }

            if (null !== $varValue) {
                $arrTokens['form_' . $strField] = $varValue;
            }
        }

        return $arrTokens;
    }

    /**
     * Return short name of current class (e.g. for CSS)
     * @return string
     */
    public function getStepClass()
    {
        $strClass = get_parent_class($this);
        $strClass = substr($strClass, strrpos($strClass, '\\') + 1);

        return parent::getStepClass() . ' ' . StringUtil::standardize($strClass);
    }
}
