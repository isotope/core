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

namespace Isotope\Model\ProductCollection;

use Isotope\Isotope;
use Isotope\Interfaces\IsotopeProductCollection;
use Isotope\Model\Address;
use Isotope\Model\ProductCollection;


/**
 * Class Cart
 *
 * Provide methods to handle Isotope cart.
 * @copyright  Isotope eCommerce Workgroup 2009-2012
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
 */
class Cart extends ProductCollection implements IsotopeProductCollection
{

    /**
     * Cookie hash value
     * @var string
     */
    protected $strHash = '';

    /**
     * Name of the temporary cart cookie
     * @var string
     */
    protected static $strCookie = 'ISOTOPE_TEMP_CART';


    /**
     * Import a front end user
     */
    public function __construct(\Database\Result $objResult=null)
    {
        parent::__construct($objResult);

        if (FE_USER_LOGGED_IN === true)
        {
            $this->import('FrontendUser', 'User');
        }
    }


    /**
     * Return the cart data
     * @param string
     * @return mixed
     */
    public function __get($strKey)
    {
        $objDatabase = \Database::getInstance();

        switch ($strKey)
        {
            case 'billing_address':
                if ($this->arrSettings['billingAddress_id'] > 0)
                {
                    $objAddress = $objDatabase->prepare("SELECT * FROM tl_iso_addresses WHERE id=?")->limit(1)->execute($this->arrSettings['billingAddress_id']);

                    if ($objAddress->numRows)
                    {
                        return $objAddress->fetchAssoc();
                    }
                }
                elseif ($this->arrSettings['billingAddress_id'] === 0 && is_array($this->arrSettings['billingAddress_data']))
                {
                    return $this->arrSettings['billingAddress_data'];
                }

                if (FE_USER_LOGGED_IN === true)
                {
                    $objAddress = $objDatabase->prepare("SELECT * FROM tl_iso_addresses WHERE pid=? AND store_id=? AND isDefaultBilling='1'")->limit(1)->execute($this->User->id, Isotope::getConfig()->store_id);

                    if ($objAddress->numRows)
                    {
                        return $objAddress->fetchAssoc();
                    }

                    // Return the default user data, but ID should be 0 to know that it is a custom/new address
                    // Trying to guess subdivision by country and state
                    return array_intersect_key(array_merge($this->User->getData(), array('id'=>0, 'street_1'=>$this->User->street, 'subdivision'=>strtoupper($this->User->country . '-' . $this->User->state))), array_flip(Isotope::getConfig()->billing_fields_raw));
                }

                return array('id'=>-1, 'country' => Isotope::getConfig()->billing_country);

            case 'shipping_address':
                if ($this->arrSettings['shippingAddress_id'] == -1)
                {
                    return array_merge($this->billing_address, array('id' => -1));
                }

                if ($this->arrSettings['shippingAddress_id'] > 0)
                {
                    $objAddress = $objDatabase->prepare("SELECT * FROM tl_iso_addresses WHERE id=?")->limit(1)->execute($this->arrSettings['shippingAddress_id']);

                    if ($objAddress->numRows)
                    {
                        return $objAddress->fetchAssoc();
                    }
                }

                if ($this->arrSettings['shippingAddress_id'] == 0 && count($this->arrSettings['shippingAddress_data']))
                {
                    return $this->arrSettings['shippingAddress_data'];
                }

                if (FE_USER_LOGGED_IN === true)
                {
                    $objAddress = $objDatabase->prepare("SELECT * FROM tl_iso_addresses WHERE pid=? AND store_id=? AND isDefaultShipping='1'")->limit(1)->execute($this->User->id, Isotope::getConfig()->store_id);

                    if ($objAddress->numRows)
                    {
                        return $objAddress->fetchAssoc();
                    }
                }

                $arrBilling = $this->billing_address;

                if ($arrBilling['id'] != -1)
                {
                    return $arrBilling;
                }

                return array('id'=>-1, 'country' => Isotope::getConfig()->shipping_country);

            case 'billingAddress':
                $objAddress = new Address();
                $objAddress->setRow($this->billing_address);

                return $objAddress;

            case 'shippingAddress':
                $objAddress = new Address();
                $objAddress->setRow($this->shipping_address);

                return $objAddress;

            default:
                return parent::__get($strKey);
        }
    }


    /**
     * Set the cart data
     * @param string
     * @param mixed
     */
    public function __set($strKey, $varValue)
    {
        switch ($strKey)
        {
            case 'billingAddress':
            case 'billing_address':
                if (is_array($varValue))
                {
                    $this->arrSettings['billingAddress_id'] = 0;
                    $this->arrSettings['billingAddress_data'] = $varValue;
                }
                else
                {
                    $this->arrSettings['billingAddress_id'] = $varValue;
                }

                $this->blnModified = true;
                $this->arrCache = array();
                break;

            case 'shippingAddress':
            case 'shipping_address':
                if (is_array($varValue))
                {
                    $this->arrSettings['shippingAddress_id'] = 0;
                    $this->arrSettings['shippingAddress_data'] = $varValue;
                }
                else
                {
                    $this->arrSettings['shippingAddress_id'] = $varValue;
                }

                $this->blnModified = true;
                $this->arrCache = array();
                break;

            default:
                parent::__set($strKey, $varValue);
        }
    }


    /**
     * Load the current cart
     * @param integer
     * @param integer
     */
    public static function getDefaultForStore($intConfig, $intStore)
    {
        $time = time();
        $strHash = \Input::cookie(static::$strCookie);

        //  Check to see if the user is logged in.
        if (FE_USER_LOGGED_IN !== true)
        {
            if ($strHash == '')
            {
                $strHash = sha1(session_id() . (!$GLOBALS['TL_CONFIG']['disableIpCheck'] ? \Environment::get('ip') : '') . $intConfig . static::$strCookie);
                \System::setCookie(static::$strCookie, $strHash, $time+$GLOBALS['TL_CONFIG']['iso_cartTimeout'], $GLOBALS['TL_CONFIG']['websitePath']);
            }

            $objCart = static::findOneBy(array('uniqid=?', 'store_id=?'), array($strHash, $intStore));
        }
        else
        {
            $objCart = static::findOneBy(array('member=?', 'store_id=?'), array(\FrontendUser::getInstance()->id, $intStore));
        }

        // Create new cart
        if ($objCart === null)
        {
            $objCart = new static();

            $objCart->member    = (FE_USER_LOGGED_IN === true ? $this->User->id : 0);
            $objCart->uniqid    = (FE_USER_LOGGED_IN === true ? '' : $strHash);
            $objCart->store_id  = $intStore;
        }

        $objCart->tstamp = $time;

        // Temporary cart available, move to this cart. Must be after creating a new cart!
         if (FE_USER_LOGGED_IN === true && $strHash != '')
         {
             $blnMerge = $objCart->products ? true : false;

            if (($objTemp = static::findOneBy(array('uniqid=?', 'store_id=?'), array($strHash, $intStore))) !== null)
            {
                $arrIds = $objCart->transferFromCollection($objTemp, false);

                if ($blnMerge && !empty($arrIds))
                {
                    $_SESSION['ISO_CONFIRM'][] = $GLOBALS['TL_LANG']['MSC']['cartMerged'];
                }

                $objTemp->delete();
            }

            // Delete cookie
            \System::setCookie(static::$strCookie, '', ($time - 3600), $GLOBALS['TL_CONFIG']['websitePath']);
            \System::reload();
         }

         return $objCart;
    }
}
