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

namespace Isotope\Model\ProductCollection;

use Isotope\Interfaces\IsotopeProductCollection;
use Isotope\Isotope;
use Isotope\Model\Address;
use Isotope\Model\Config;
use Isotope\Model\ProductCollection;


/**
 * Class Cart

 * Provide methods to handle Isotope cart.
 *
 * @property mixed id
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
     * Get billing address or create if none exists
     * @return  Address
     */
    public function getBillingAddress()
    {
        $objAddress = parent::getBillingAddress();

        if (null === $objAddress && FE_USER_LOGGED_IN === true) {
            $objAddress = Address::findDefaultBillingForMember(\FrontendUser::getInstance()->id);

            if (null === $objAddress) {
                $objAddress = Address::createForMember(\FrontendUser::getInstance()->id, Isotope::getConfig()->getBillingFields());
            }
        }

        if (null === $objAddress) {
            $objAddress          = new Address();
            $objAddress->country = (Isotope::getConfig()->billing_country ? : Isotope::getConfig()->country);
        }

        $objAddress->pid = (int) $this->id;
        $objAddress->ptable = 'tl_iso_product_collection';
        $objAddress->isDefaultBilling = '1';

        return $objAddress;
    }

    /**
     * Get shipping address or create if none exists
     * @return  Address
     */
    public function getShippingAddress()
    {
        $objAddress = parent::getShippingAddress();

        if (null === $objAddress && FE_USER_LOGGED_IN === true) {
            $objAddress = Address::findDefaultShippingForMember(\FrontendUser::getInstance()->id);

            if (null === $objAddress) {
                $objAddress = Address::createForMember(\FrontendUser::getInstance()->id, Isotope::getConfig()->getShippingFields());
            }
        }

        if (null === $objAddress) {
            $objAddress          = new Address();
            $objAddress->country = Isotope::getConfig()->shipping_country;
        }

        $objAddress->pid = (int) $this->id;
        $objAddress->ptable = 'tl_iso_product_collection';
        $objAddress->isDefaultShipping = '1';

        return $objAddress;
    }

    /**
     * Merge guest cart if necessary
     */
    public function mergeGuestCart()
    {
        $this->ensureNotLocked();

        $strHash = \Input::cookie(static::$strCookie);

        // Temporary cart available, move to this cart. Must be after creating a new cart!
        if (FE_USER_LOGGED_IN === true && $strHash != '' && $this->member > 0) {
            $blnMerge = $this->countItems() > 0 ? true : false;

            if (($objTemp = static::findOneBy(array('uniqid=?', 'store_id=?'), array($strHash, $this->store_id))) !== null) {
                $arrIds = $this->copyItemsFrom($objTemp);

                if ($blnMerge && !empty($arrIds)) {
                    $_SESSION['ISO_CONFIRM'][] = $GLOBALS['TL_LANG']['MSC']['cartMerged'];
                }

                $objTemp->delete();
            }

            // Delete cookie
            \System::setCookie(static::$strCookie, '', (time() - 3600), $GLOBALS['TL_CONFIG']['websitePath']);
            \System::reload();
        }
    }

    /**
     * Check if minimum order amount is reached
     * @return  bool
     */
    public function hasErrors()
    {
        if (Isotope::getConfig()->cartMinSubtotal > 0 && Isotope::getConfig()->cartMinSubtotal > $this->getSubtotal()) {
            return true;
        }

        return parent::hasErrors();
    }

    /**
     * Get error messages for the cart
     * @return  array
     */
    public function getErrors()
    {
        $arrErrors = parent::getErrors();

        if (Isotope::getConfig()->cartMinSubtotal > 0 && Isotope::getConfig()->cartMinSubtotal > $this->getSubtotal()) {
            $arrErrors[] = sprintf($GLOBALS['TL_LANG']['ERR']['cartMinSubtotal'], Isotope::formatPriceWithCurrency(Isotope::getConfig()->cartMinSubtotal));
        }

        return $arrErrors;
    }

    /**
     * Get a collection-specific error message for items with errors
     * @return  string
     */
    protected function getMessageIfErrorsInItems()
    {
        return $GLOBALS['TL_LANG']['ERR']['cartErrorInItems'];
    }

    /**
     * Clear all cache properties
     */
    protected function clearCache()
    {
        parent::clearCache();

        $this->objDraftOrder = null;
    }

    /**
     * Load the current cart
     * @param   Config
     * @return  Cart
     */
    public static function findForCurrentStore()
    {
        global $objPage;

        if (TL_MODE != 'FE' || null === $objPage || $objPage->rootId == 0) {
            return null;
        }

        $time     = time();
        $strHash  = \Input::cookie(static::$strCookie);
        $intStore = (int) \PageModel::findByPk($objPage->rootId)->iso_store_id;

        //  Check to see if the user is logged in.
        if (FE_USER_LOGGED_IN !== true) {
            if ($strHash == '') {
                $strHash = sha1(session_id() . (!$GLOBALS['TL_CONFIG']['disableIpCheck'] ? \Environment::get('ip') : '') . $intStore . static::$strCookie);
                \System::setCookie(static::$strCookie, $strHash, $time + $GLOBALS['TL_CONFIG']['iso_cartTimeout'], $GLOBALS['TL_CONFIG']['websitePath']);
            }

            $objCart = static::findOneBy(array('uniqid=?', 'store_id=?'), array($strHash, $intStore));
        } else {
            $objCart = static::findOneBy(array('member=?', 'store_id=?'), array(\FrontendUser::getInstance()->id, $intStore));
        }

        // Create new cart
        if ($objCart === null) {

            $objConfig = Config::findByRootPageOrFallback($objPage->rootId);
            $objCart   = new static();

            // Can't call the individual rows here, it would trigger markModified and a save()
            $objCart->setRow(array_merge($objCart->row(), array(
                'tstamp'    => $time,
                'member'    => (FE_USER_LOGGED_IN === true ? \FrontendUser::getInstance()->id : 0),
                'uniqid'    => (FE_USER_LOGGED_IN === true ? '' : $strHash),
                'config_id' => $objConfig->id,
                'store_id'  => $intStore,
            )));

        } else {
            $objCart->tstamp = $time;
        }

        return $objCart;
    }
}
