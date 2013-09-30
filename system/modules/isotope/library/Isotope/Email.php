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

use Isotope\Interfaces\IsotopeCollection;


/**
 * Class Isotope\Email
 *
 * Provide methods to send Isotope e-mails.
 * @copyright  Isotope eCommerce Workgroup 2009-2012
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
 */
class Email extends \Controller
{

    /**
     * The unterlying Contao Email object
     * @var object
     */
    protected $objEmail;

    /**
     * Contain the simple tokens for this email
     * @var array
     */
    protected $arrSimpleTokens = array();

    /**
     * The current language for the email
     * @var string
     */
    protected $strLanguage;

    /**
     * Email template file
     * @var string
     */
    protected $strTemplate;

    /**
     * Contao CSS file to include
     * @var string
     */
    protected $strCssFile = 'isotope';

    /**
     * if attachments have been added (= reset $objEmail if language changes)
     * @var boolean
     */
    protected $attachmentsDone = false;

    /**
     * the id of the mail template
     * @var integer
     */
    protected $intId;


    /**
     * Construct object
     * @param integer
     * @param string
     * @param object
     */
    public function __construct($intId, $strLanguage=null, $objCollection=null)
    {
        parent::__construct();

        // Verify collection object type
        if (!($objCollection instanceof IsotopeCollection))
        {
            $objCollection = null;
        }

        $this->intId = $intId;
        $this->initializeTemplate($strLanguage, $objCollection);
    }


    /**
     * Set an object property
     * @param string
     * @param mixed
     */
    public function __set($strKey, $varValue)
    {
        switch ($strKey)
        {
            case 'simpleTokens':
                $arrTokens = array();
                $arrValue = deserialize($varValue, true);

                foreach( $arrValue as $k => $v )
                {
                    if (is_array($v))
                    {
                        $arrTokens[$k] = $this->recursiveImplode(', ', $v);
                        continue;
                    }

                    $arrTokens[$k] = $v;
                }

                $this->arrSimpleTokens = $arrTokens;
                break;

            case 'language':
                $strLanguage = substr($varValue, 0, 2);

                if ($strLanguage != $this->strLanguage)
                {
                    $this->initializeTemplate($strLanguage);
                }
                break;

            case 'collection':
                if ($varValue instanceof IsotopeCollection)
                {
                    $this->initializeTemplate($this->strLanguage, $objCollection);
                }
                break;

            default:
                if (is_object($varValue))
                {
                    $this->$strKey = $varValue;
                }
                else
                {
                    $this->objEmail->__set($strKey, $varValue);
                }
                break;
        }
    }

    /**
     * Return an object property
     * @param string
     * @return mixed
     */
    public function __get($strKey)
    {
        switch ($strKey)
        {
            case 'language':
                return $this->strLanguage;
                break;

            default:
                return $this->$strKey ? $this->$strKey : $this->objEmail->__get($strKey);
                break;
        }
    }


    /**
     * Call parent Email object method
     * @param string
     * @param array
     * @return mixed
     */
    public function __call($function, array $param_arr)
    {
        return call_user_func_array(array($this->objEmail, $function), $param_arr);
    }


    /**
     * Send to give address with tokens
     * @param mixed
     * @param array
     * @param string
     */
    public function send($varRecipients, $arrTokens=null, $strLanguage=null)
    {
        if ($strLanguage)
        {
            $this->language = $strLanguage;
        }

        if (is_array($arrTokens))
        {
            $this->simpleTokens = $arrTokens;
        }

        return $this->sendTo($varRecipients);
    }


    /**
     * Set the data and send the email.
     * DON'T CALL THIS METHOD BEFORE YOU HAVE DONE ALL MODIFICATIONS ON THE MAIL TEMPLATE
     */
    public function sendTo()
    {
        // Use current page language if none is set
        if (!$this->strLanguage)
        {
            $this->strLanguage = $GLOBALS['TL_LANGUAGE'];
        }

        // Get the data for the active language
        $objLanguage = \Database::getInstance()->prepare("SELECT * FROM tl_iso_mail_content WHERE pid={$this->intId} AND (language='{$this->strLanguage}' OR fallback='1') ORDER BY fallback")
                                               ->limit(1)
                                               ->execute();

        if (!$objLanguage->numRows)
        {
            throw new \UnderflowException('No fallback language found for mail template ID '.$this->intId);
        }

        $this->strLanguage = $objLanguage->language;

        $arrData = $this->arrSimpleTokens;

        $this->objEmail->subject = strip_tags($this->recursiveReplaceTokensAndTags($objLanguage->subject, $arrData));
        $this->objEmail->text = strip_tags($this->recursiveReplaceTokensAndTags($objLanguage->text, $arrData));

        // Generate HTML
        if (!$objLanguage->textOnly && $objLanguage->html != '')
        {
            $arrData['head_css'] = '';

            // Add style sheet
            if (is_file(TL_ROOT . '/system/scripts/' . $this->strCssFile . '.css'))
            {
                $buffer = file_get_contents(TL_ROOT . '/system/scripts/' . $this->strCssFile . '.css');
                $buffer = preg_replace('@/\*\*.*\*/@Us', '', $buffer);

                $css  = '<style type="text/css">' . "\n";
                $css .= trim($buffer) . "\n";
                $css .= '</style>' . "\n";
                $arrData['head_css'] = $css;
            }

            $objTemplate = new \Isotope\Template($this->strTemplate);
            $objTemplate->body = $objLanguage->html;
            $objTemplate->charset = $GLOBALS['TL_CONFIG']['characterSet'];
            $objTemplate->css = '##head_css##';

            // Prevent parseSimpleTokens from stripping important HTML tags
            $GLOBALS['TL_CONFIG']['allowedTags'] .= '<doctype><html><head><meta><style><body>';
            $strHtml = str_replace('<!DOCTYPE', '<DOCTYPE', $objTemplate->parse());
            $strHtml = $this->recursiveReplaceTokensAndTags($strHtml, $arrData);
            $strHtml = $this->convertRelativeUrls($strHtml);
            $strHtml = str_replace('<DOCTYPE', '<!DOCTYPE', $strHtml);

            // Parse template
            $this->objEmail->html = $strHtml;
        }

        // Add attachments
        if (!$this->attachmentsDone)
        {
            foreach (deserialize($objLanguage->attachments, true) as $file)
            {
                if ($file != '' && is_file(TL_ROOT . '/' . $file))
                {
                    $this->objEmail->attachFile(TL_ROOT . '/' . $file);
                }
            }

            $this->attachmentsDone = true;
        }

        return call_user_func_array(array($this->objEmail, 'sendTo'), func_get_args());
    }


    /**
     * Initialize from template and reset attachments if language changes
     * @param string
     * @param object
     * @throws Exception
     */
    protected function initializeTemplate($strLanguage, $objCollection)
    {
        $this->objEmail = new \Email();
        $this->attachmentsDone = false;

        $objTemplate = \Database::getInstance()->execute("SELECT * FROM tl_iso_mail WHERE id=" . $this->intId);

        if ($objTemplate->numRows < 1)
        {
            throw new \UnderflowException('No mail template with ID "' . $this->intId . '" found.');
        }

        $this->strLanguage = $strLanguage;

        // Set the options
        $this->objEmail->imageDir = TL_ROOT . '/';
        $this->objEmail->fromName = $objTemplate->senderName ? $objTemplate->senderName : $GLOBALS['TL_ADMIN_NAME'];
        $this->objEmail->from = $objTemplate->sender ? $objTemplate->sender : $GLOBALS['TL_ADMIN_EMAIL'];
        $this->objEmail->priority = $objTemplate->priority;

        // Add CC and BCC recipients
        $this->addRecipients($objTemplate->cc, 'sendCc');
        $this->addRecipients($objTemplate->bcc, 'sendBcc');

        $this->strTemplate = $objTemplate->template ? $objTemplate->template : 'mail_default';
    }


    /**
     * Romanize a friendly name and return it as string
     * @param string
     * @return string
     */
    public static function romanizeFriendlyName($strName)
    {
        $strName = html_entity_decode($strName, ENT_QUOTES, $GLOBALS['TL_CONFIG']['characterSet']);
        $strName = strip_insert_tags($strName);
        $strName = utf8_romanize($strName);
        $strName = preg_replace('/[^A-Za-z0-9\.!#$%&\'*+-\/=?^_ `{\|}~]+/i', '_', $strName);

        return $strName;
    }


    /**
     * Recursivly implode an array
     * @param string
     * @param array
     * @return string
     */
    protected function recursiveImplode($strGlue, $arrPieces)
    {
        $arrReturn = array();

        foreach ($arrPieces as $varPiece)
        {
            if (is_array($varPiece))
            {
                $arrReturn[] = $this->recursiveImplode($strGlue, $varPiece);
            }
            else
            {
                $arrReturn[] = $varPiece;
            }
        }

        return implode($strGlue, $arrReturn);
    }


    /**
     * Recursively replace the simple tokens and the insert tags
     * @param string
     * @param array tokens
     * @return string
     */
    protected function recursiveReplaceTokensAndTags($strText, $arrTokens)
    {
        // first parse the tokens as they might have if-else clauses
        $strBuffer = \String::parseSimpleTokens($strText, $arrTokens);

        // then replace the insert tags
        $strBuffer = $this->replaceInsertTags($strBuffer);

        // check if the inserttags have returned a simple token or an insert tag to parse
        if (strpos($strBuffer, '##') !== false || strpos($strBuffer, '{{') !== false)
        {
            // Prevent infinite loop
            if ($strBuffer == $strText)
            {
                return $strBuffer;
            }

            $strBuffer = $this->recursiveReplaceTokensAndTags($strBuffer, $arrTokens);
        }

        $strBuffer = \String::restoreBasicEntities($strBuffer);

        return $strBuffer;
    }


    /**
     * Add (blind) carbon copy recipients to the email object
     * @param string
     * @param string
     */
    protected function addRecipients($strRecipients, $strMethod='sendCc')
    {
        $arrAdd = array();
        $arrRecipients = (array) trimsplit(',', $strRecipients);

        foreach ($arrRecipients as $email)
        {
            if ($email == '' || !$this->isValidEmailAddress($email))
            {
                continue;
            }

            $arrAdd[] = $email;
        }

        if (!empty($arrAdd))
        {
            $this->objEmail->{$strMethod}($arrAdd);
        }
    }
}
