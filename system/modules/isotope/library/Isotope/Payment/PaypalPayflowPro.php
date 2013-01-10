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

namespace Isotope\Payment;

use Isotope\Interfaces\IsotopePayment;


/**
 * Class PaypalPayflowPro
 *
 * Handle Paypal Payflow Pro payments
 * @copyright  Isotope eCommerce Workgroup 2009-2012
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
 */
class PaypalPayflowPro extends Payment implements IsotopePayment
{

    /**
     * processPayment function.
     *
     * @access public
     * @return void
     */
    public function processPayment()
    {
        $objAddress = $this->Isotope->Cart->billingAddress;
        $arrSubdivision = explode('-', $objAddress->subdivision);

        //$strExp = str_replace('/','',$_SESSION['CHECKOUT_DATA']['payment'][$this->id]['cc_exp']);

        switch($_SESSION['CHECKOUT_DATA']['payment'][$this->id]['cc_type'])
        {
            case 'mc':
                $strCardType = 'MasterCard';
                break;

            case 'visa':
                $strCardType = 'Visa';
                break;

            case 'amex':
                $strCardType = 'Amex';
                break;

            case 'discover':
                $strCardType = 'Discover';
                break;

            case 'jcb':
                $strCardType = 'Jcb';
                break;

            case 'diners':
                $strCardType = 'Diners';
                break;

            case 'maestro':
                $strCardType = 'Maestro';
                break;
        }

        $arrData = array
        (
            'USER'					=> $this->payflowpro_user,
            'VENDOR'				=> $this->payflowpro_vendor,
            'PARTNER'				=> $this->payflowpro_partner,
            'PWD'					=> $this->payflowpro_password,
            'TENDER'				=> 'C', //Can also be a paypal account.  need to build this in.
            'TRXTYPE'				=> 'S', //$this->payflowpro_transType, //  S = Sale transaction, A = Authorisation, C = Credit, D = Delayed Capture, V = Void
            'ACCT'					=> $_SESSION['CHECKOUT_DATA']['payment'][$this->id]['cc_num'],
            'EXPDATE'				=> ($_SESSION['CHECKOUT_DATA']['payment'][$this->id]['cc_exp_month'].$_SESSION['CHECKOUT_DATA']['payment'][$this->id]['cc_exp_year']),
            'NAME'					=> $strCardType,
            'AMT'					=> round($this->Isotope->Cart->grandTotal, 2),
            'CURRENCY'				=> $this->Isotope->Config->currency,
              'COMMENT1'				=> '',	//TODO: Provide space for order comments.
            'FIRSTNAME'				=> $objAddress->firstname,
            'LASTNAME'				=> $objAddress->lastname,
            'STREET'				=> $objAddress->street_1."\n".$objAddress->street_2."\n".$objAddress->street_3,
            'CITY'					=> $objAddress->city,
            'STATE'					=> $arrSubdivision[1],
            'ZIP'					=> $objAddress->postal,
            'COUNTRY'				=> strtoupper($objAddress->country),
            'NOTIFYURL'				=> ($this->Environment->base . 'system/modules/isotope/postsale.php?mod=pay&id=' . $this->id)
        );

        if ($this->requireCCV)
        {
            $arrData['CVV2'] = $_SESSION['CHECKOUT_DATA']['payment'][$this->id]['cc_ccv'];
        }

        if ($this->Isotope->Config->country == 'UK')
        {
            if($objAddress->country == 'UK' && ($_SESSION['CHECKOUT_DATA']['payment'][$this->id]['cc_type'] == 'maestro' || $_SESSION['CHECKOUT_DATA']['payment'][$this->id]['cc_type'] == 'solo'))
            {
                $arrData['STARTDATE'] = $_SESSION['CHECKOUT_DATA']['payment'][$this->id]['cc_start_date'];
                $arrData['ISSUENUMBER'] = $_SESSION['CHECKOUT_DATA']['payment'][$this->id]['cc_issue_number'];
            }
        }

        $arrData['CLIENTIP'] = $this->Environment->ip;
        $arrData['VERBOSITY'] = 'MEDIUM';



        //$arrFinal = array_map('urlencode', $arrData);

        foreach($arrData as $k=>$v)
        {
            $arrNVP[] .= $k . '['. strlen($v) . ']=' . $v;
        }

        $tempstr = $_SESSION['CHECKOUT_DATA']['payment'][$this->id]['cc_num'] . $this->Isotope->Cart->grandTotal . date('YmdGis') . "1";

          $request_id = md5($tempstr);


        $objRequest = new \Request();

        $arrHeaders = array
        (
            'Content-Type'						=> 'text/namevalue',
            'X-VPS-Request-ID'					=> $request_id,
            'X-VPS-Timeout'						=> '45',
            'X-VPS-VIT-Client-Type'				=> 'PHP/cURL',
            'X-VPS-VIT-Client-Version'			=> '0.01',
            'X-VPS-VIT-Client-Architecture'		=> 'x86',
            'X-VPS-VIT-Integration-Product'		=> 'Isotope E-commerce',
            'X-VPS-VIT-Integration-Version'		=> '0.01'
        );

        foreach($arrHeaders as $k=>$v)
        {
            $objRequest->setHeader($k, $v);
        }

        $objRequest->send('https://' . ($this->debug ? 'pilot-' : '') . 'payflowpro.verisign.com/transaction', implode('&', $arrNVP), 'post');

        $pfpro = explode('&', $objRequest->response);

        foreach($pfpro as $row)
        {
            $arrPair = explode('=', $row);
            $arrResponse[$arrPair[0]] = $arrPair[1];
        }


        if (!isset($arrResponse['RESULT']) || $arrResponse['RESULT'] != 0)
        {
               $_SESSION['CHECKOUT_DATA']['payment'][$this->id]['error'] = $arrResponse['RESPMSG'];

            $this->redirect($this->addToUrl('step=payment', true));
          }
    }


    /**
     * Return the PayPal form.
     *
     * @access public
     * @return string
     */
    public function paymentForm($objCheckoutModule)
    {
        $strBuffer = '';
        $arrPayment = \Input::post('payment');
        $arrCCTypes = deserialize($this->allowed_cc_types);

        $intStartYear = (integer) date('Y', time());	//Requires 4-digit year

        for($i=0;$i<=9;$i++)
            $arrYears[] = (string) $intStartYear+$i;

        $arrFields = array
        (
            'cc_num' => array
            (
                'label'			=> &$GLOBALS['TL_LANG']['ISO']['cc_num'],
                'inputType'		=> 'text',
                'eval'			=> array('mandatory'=>true, 'rgxp'=>'digit', 'tableless'=>true),
            ),
            'cc_type' => array
            (
                'label'			=> &$GLOBALS['TL_LANG']['ISO']['cc_type'],
                'inputType'		=> 'select',
                'options'		=> $arrCCTypes,
                'eval'			=> array('mandatory'=>true, 'rgxp'=>'digit', 'tableless'=>true),
                'reference'		=> &$GLOBALS['ISO_LANG']['CCT'],
            ),
            'cc_exp_month' => array
            (
                'label'			=> &$GLOBALS['TL_LANG']['ISO']['cc_exp_month'],
                'inputType'		=> 'select',
                'options'		=> array('01','02','03','04','05','06','07','08','09','10','11','12'),
                'eval'			=> array('mandatory'=>true, 'tableless'=>true, 'includeBlankOption'=>true)
            ),
            'cc_exp_year'  => array
            (
                'label'			=> &$GLOBALS['TL_LANG']['ISO']['cc_exp_year'],
                'inputType'		=> 'select',
                'options'		=> $arrYears,
                'eval'			=> array('mandatory'=>true, 'tableless'=>true, 'includeBlankOption'=>true)
            ),
            'cc_ccv' => array
            (
                'label'			=> &$GLOBALS['TL_LANG']['ISO']['cc_ccv'],
                'inputType'		=> 'text',
                'eval'			=> array('mandatory'=>true, 'tableless'=>true)
            ),
        );

        if($this->Isotope->Config->country == 'UK' && (in_array('maestro', $arrCCTypes) || in_array('solo', $arrCCTypes)))
        {
            $arrFields['cc_start_date'] = array
            (
                'label'			=> &$GLOBALS['TL_LANG']['ISO']['cc_start_date'],
                'inputType'		=> 'text',
                'eval'			=> array('tableless'=>true)
            );

            $arrFields['cc_issue_number'] = array
            (
                'label'			=> &$GLOBALS['TL_LANG']['ISO']['cc_issue_number'],
                'inputType'		=> 'text',
                'eval'			=> array('maxlength'=>2,'tableless'=>true)
            );
        }

        foreach( $arrFields as $field => $arrData )
        {
            $strClass = $GLOBALS['TL_FFL'][$arrData['inputType']];

            // Continue if the class is not defined
            if (!$this->classFileExists($strClass))
            {
                continue;
            }

            $objWidget = new $strClass($this->prepareForWidget($arrData, 'payment['.$this->id.']['.$field.']', $_SESSION['CHECKOUT_DATA']['payment'][$this->id][$field]));

            // Validate input
            if (\Input::post('FORM_SUBMIT') == 'iso_mod_checkout_payment' && $arrPayment['module'] == $this->id)
            {
                $objWidget->validate();

                if ($objWidget->hasErrors())
                {
                    $objCheckoutModule->doNotSubmit = true;
                }
            }

            $strBuffer .= $objWidget->parse();
        }

        if (\Input::post('FORM_SUBMIT') == 'iso_mod_checkout_payment' && $arrPayment['module'] == $this->id && !$objCheckoutModule->doNotSubmit)
        {
            $strCard = $this->validateCreditCard($arrPayment[$this->id]['cc_num']);

            /*if(!preg_match('/^((0[1-9])|(1[0-2]))\/((20[1-2][0-9]))$/', $arrPayment[$this->id]['cc_exp']))
            {
                $strBuffer = '<p class="error">' . $GLOBALS['TL_LANG']['ERR']['cc_exp'] . '</p>' . $strBuffer;
                $objCheckoutModule->doNotSubmit = true;
            }*/

            if ($strCard === false)
            {
                $strBuffer = '<p class="error">' . $GLOBALS['TL_LANG']['ERR']['cc_num'] . '</p>' . $strBuffer;
                $objCheckoutModule->doNotSubmit = true;
            }
            elseif ($strCard != $arrPayment[$this->id]['cc_type'])
            {
                $strBuffer = '<p class="error">' . $GLOBALS['TL_LANG']['ERR']['cc_match'] . '</p>' . $strBuffer;
                $objCheckoutModule->doNotSubmit = true;
            }

        }

        if (strlen($_SESSION['CHECKOUT_DATA']['payment'][$this->id]['error']))
        {
            $strBuffer = '<p class="error">' . $_SESSION['CHECKOUT_DATA']['payment'][$this->id]['error'] . '</p>' . $strBuffer;
            unset($_SESSION['CHECKOUT_DATA']['payment'][$this->id]['error']);
        }

        return $strBuffer;
    }


    public function checkoutReview()
    {
        $type = $_SESSION['CHECKOUT_DATA']['payment'][$this->id]['cc_type'];
        $num = $_SESSION['CHECKOUT_DATA']['payment'][$this->id]['cc_num'];

        $strCard = implode(' ', str_split((substr($num, 0, 2) . str_repeat('*', (strlen($num)-6)) . substr($num, -4)), 4));

        list($endTag) = \Isotope\Frontend::getElementAndScriptTags();

        return sprintf('%s<br'.$endTag.'%s: %s', $this->label, $GLOBALS['ISO_LANG']['CCT'][$type], $strCard);
    }


    public function getAllowedCCTypes()
    {
        return array('mc', 'visa', 'amex', 'discover', 'jcb', 'diners','maestro','solo');
    }

}
