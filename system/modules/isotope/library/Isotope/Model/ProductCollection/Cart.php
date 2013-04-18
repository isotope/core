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


    public function getBillingAddress()
    {
        $objAddress = parent::getBillingAddress();

        if (null === $objAddress && FE_USER_LOGGED_IN === true) {
            $objAddress = Address::findDefaultBillingForMember($this->User->id);

            if (null === $objAddress) {
                $objAddress = Address::createForMember(FrontendUser::getInstance()->id, Isotope::getConfig()->billing_fields_raw);
            }
        }

        if (null === $objAddress) {
            $objAddress = new Address();
            $objAddress->country = Isotope::getConfig()->billing_country;
        }

        return $objAddress;
    }


    public function getShippingAddress()
    {
        $objAddress = parent::getShippingAddress();

        if (null === $objAddress && FE_USER_LOGGED_IN === true) {
            $objAddress = Address::findDefaultShippingForMember($this->User->id);

            if (null === $objAddress) {
                $objAddress = Address::createForMember(FrontendUser::getInstance()->id, Isotope::getConfig()->shipping_fields_raw);
            }
        }

        if (null === $objAddress) {
            $objAddress = new Address();
            $objAddress->country = Isotope::getConfig()->shipping_country;
        }

        return $objAddress;
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
             $blnMerge = $objCart->countItems() > 0 ? true : false;

            if (($objTemp = static::findOneBy(array('uniqid=?', 'store_id=?'), array($strHash, $intStore))) !== null)
            {
                $arrIds = $objCart->copyItemsFrom($objTemp);

                if ($blnMerge && !empty($arrIds)) {
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
