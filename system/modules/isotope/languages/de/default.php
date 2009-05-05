<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');

/**
 * TYPOlight webCMS
 *
 * The TYPOlight webCMS is an accessible web content management system that 
 * specializes in accessibility and generates W3C-compliant HTML code. It 
 * provides a wide range of functionality to develop professional websites 
 * including a built-in search engine, form generator, file and user manager, 
 * CSS engine, multi-language support and many more. For more information and 
 * additional TYPOlight applications like the TYPOlight MVC Framework please 
 * visit the project website http://www.typolight.org.
 *
 * Default language file (en).
 *
 * PHP version 5
 * @copyright  Martin Komara 2007 
 * @author     Martin Komara 
 * @package    Language 
 * @license    GPL 
 * @filesource
 */


/**
 * Content Elements
 *
 */
$GLOBALS['TL_LANG']['CTE']['attributeLinkRepeater']   = array('Artikelmerkmal-Filter Auflistung', 'Dieses Element generiert eine Sammlung von Links eines Artikelmerkmal-Filters.');

 
/**
 * Miscellaneous
 */
 
$GLOBALS['TL_LANG']['MSC']['buttonLabel']['add_to_cart'] = 'Bestellen';
$GLOBALS['TL_LANG']['MSC']['buttonActionString']['add_to_cart'] = '%s bestellen';
		
$GLOBALS['TL_LANG']['MSC']['labelSortBy'] = 'Sortieren nach';
$GLOBALS['TL_LANG']['MSC']['deleteImage'] = 'Entfernen';
$GLOBALS['TL_LANG']['MSC']['noItemsInCart'] = 'Ihr Warenkorb ist leer.';
$GLOBALS['TL_LANG']['MSC']['removeProductLinkTitle'] = '%s aus dem Warenkorb entfernen';

//Checkout language entries 
$GLOBALS['TL_LANG']['MSC']['CHECKOUT_STEP']['PROMPT']['billing_information'] = 'Bitte geben Sie Ihre Adresse ein.';
$GLOBALS['TL_LANG']['MSC']['CHECKOUT_STEP']['PROMPT']['shipping_method'] = 'Wählen Sie eine Versandart.';
$GLOBALS['TL_LANG']['MSC']['CHECKOUT_STEP']['PROMPT']['payment_method'] = 'Wählen Sie eine Zahlungsart.';

$GLOBALS['TL_LANG']['MSC']['CHECKOUT_STEP']['HEADLINE']['billing_information'] = 'Rechnungsinformationen';
$GLOBALS['TL_LANG']['MSC']['CHECKOUT_STEP']['HEADLINE']['shipping_method'] = 'Versandinformationen';
$GLOBALS['TL_LANG']['MSC']['CHECKOUT_STEP']['HEADLINE']['payment_method'] = 'Zahlungsart';

$GLOBALS['TL_LANG']['MSC']['confirmOrder'] = 'Bestellen';



$GLOBALS['TL_LANG']['ISO']['productSingle']		= '1 Produkt';
$GLOBALS['TL_LANG']['ISO']['productMultiple']	= '%s Produkte';


/**
 * Currencies
 */
$GLOBALS['TL_LANG']['CUR']['USD'] = 'USD - US Dollar';
$GLOBALS['TL_LANG']['CUR']['EUR'] = 'EUR - Euro';
$GLOBALS['TL_LANG']['CUR']['CHF'] = 'CHF - Schweizer Franken';