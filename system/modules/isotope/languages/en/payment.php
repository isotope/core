<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');

/**
 * TYPOlight Open Source CMS
 * Copyright (C) 2005-2010 Leo Feyer
 *
 * This program is free software: you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation, either
 * version 3 of the License, or (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 * 
 * You should have received a copy of the GNU Lesser General Public
 * License along with this program. If not, please visit the Free
 * Software Foundation website at <http://www.gnu.org/licenses/>.
 *
 * PHP version 5
 * @copyright  Winans Creative 2009, Intelligent Spark 2010, iserv.ch GmbH 2010
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
 * @author     Andreas Schempp <andreas@schempp.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */


/**
 * ePay payment modules
 */
$GLOBALS['TL_LANG']['MSG']['epay'][-5604]	= 'Fee can not be calculated for the card type used.';
$GLOBALS['TL_LANG']['MSG']['epay'][-5603]	= 'The store does not allow the type of card.';
$GLOBALS['TL_LANG']['MSG']['epay'][-5602]	= 'An invalid currency code has been used.';
$GLOBALS['TL_LANG']['MSG']['epay'][-5601]	= 'Fee can not be calculated for the card type used.';
$GLOBALS['TL_LANG']['MSG']['epay'][-5600]	= 'The card number is not accurate - invalid prefix (must be 6 characters).';
$GLOBALS['TL_LANG']['MSG']['epay'][-5514]	= 'Customer\'s session has either expired or the payment process has not started correctly.';
$GLOBALS['TL_LANG']['MSG']['epay'][-5511]	= 'An error has occurred! Please restart the payment process.';
$GLOBALS['TL_LANG']['MSG']['epay'][-5509]	= 'You get the error Not valid data when you try to open the Standard Payment window. You get this error because ePay can not find the data to the transaction. This error occurs because the user / client has been inactive for more than 20 minutes!';
$GLOBALS['TL_LANG']['MSG']['epay'][-5508]	= 'You receive the rror "No valid domains created for the company", when you try to open the payment window. You receive this error as you have not entered the domain for your account in the payment system. In the payment system in the menu "Settings" and "Payment system" you can see the domain(s) assigned to your accout.';
$GLOBALS['TL_LANG']['MSG']['epay'][-5507]	= 'You receive the error "URL not allowed for relaying", when you try to open the payment window. You receive this error as the domain you open up the window from, is not entered in the payment system. In the administration to the payment system from the menu "Settings" and "Payment System", you can enter you domain.';
$GLOBALS['TL_LANG']['MSG']['epay'][-5506]	= 'You get the error Invalid merchant number, when you try to open the Standard Payment window. You get this error because it indløsningsnummer / merchant number you use is not established in the payment! Check whether you are using the correct merchant number.';
$GLOBALS['TL_LANG']['MSG']['epay'][-5505]	= 'You get the error No cardtypes defined when you try to open the Standard Payment window. This is because there are NO cards up to your account in the payment system.';
$GLOBALS['TL_LANG']['MSG']['epay'][-5504]	= 'You get the error Invalid currencycode when you try to open the Standard Payment window. You get this error because you are using an invalid currencycode. You can from your administration to thepayment system in the menu "Support" and "Currency Codes" see the list of currency codes available.';
$GLOBALS['TL_LANG']['MSG']['epay'][-5503]	= 'The data that you send to the payment window are not correct! You get a description of the data as it is not listed correctly.';
$GLOBALS['TL_LANG']['MSG']['epay'][-5502]	= 'You receive the error "Invalid company" when you try to open the payment window. You receive this error as you have not activated the payment window yet. You have to activate the window from your administration to the payment system from the menu "Settings" and "Payment window".';
$GLOBALS['TL_LANG']['MSG']['epay'][-5501]	= 'You receive the error "Window not activated", when you try to open the payment window. You receive this error as you have not activated the payment window yet. You have to activate the window from your administration to the payment system from the menu "Settings" and "Payment window".';
$GLOBALS['TL_LANG']['MSG']['epay'][-2003]	= 'Declined - Issuers country / region does not match the country the payment come from.';
$GLOBALS['TL_LANG']['MSG']['epay'][-2002]	= 'Declined - Nonsecure payments from country / region are not accepted.';
$GLOBALS['TL_LANG']['MSG']['epay'][-2001]	= 'Declined - Payments from country / region are not accepted.';
$GLOBALS['TL_LANG']['MSG']['epay'][-2000]	= 'Declined - Payments from your IP Address are not accepted.';
$GLOBALS['TL_LANG']['MSG']['epay'][-1602]	= 'PBS test gateway is unfortunately down at the moment, please try again later.';
$GLOBALS['TL_LANG']['MSG']['epay'][-1601]	= 'The reply sent to the payment system was expected to be from a bank, but is invalid. Invalid data was posted.';
$GLOBALS['TL_LANG']['MSG']['epay'][-1600]	= 'The session of banking has already been used. The same session can not be used again.';
$GLOBALS['TL_LANG']['MSG']['epay'][-1303]	= 'Rejected - Payment could not be increased - rejected by EWIRE.';
$GLOBALS['TL_LANG']['MSG']['epay'][-1302]	= 'Rejected - Error ewire MD5 data. Check the MD5 data is posted in both EWIRE and ePay.';
$GLOBALS['TL_LANG']['MSG']['epay'][-1301]	= 'Rejected - EWIRE emrchant number was not found - Check if your EWIRE merchant number is setup at ePay.';
$GLOBALS['TL_LANG']['MSG']['epay'][-1300]	= 'You try to pay with a credit card which is not accepted by the merchant. Please try another credit card or contact the merchant.';
$GLOBALS['TL_LANG']['MSG']['epay'][-1200]	= 'Unknown currency code. You can only use the currency codes that you can see in the menu "Support" and "Currency Codes" in the administration.';
$GLOBALS['TL_LANG']['MSG']['epay'][-1100]	= 'Invalid data received at the payment system. You must remember to send the amount in the smallest unit / minor units (eg GBP must. Specified in the ear), and may not use the comma or dot (separator).';
$GLOBALS['TL_LANG']['MSG']['epay'][-1019]	= 'Invalid password used for webservice access!';
$GLOBALS['TL_LANG']['MSG']['epay'][-1018]	= 'Invalid test-card used! You find the correct test-information from the menu Support -> Test Information as you are logged into the payment system.';
$GLOBALS['TL_LANG']['MSG']['epay'][-1017]	= 'No access to PCI required function!';
$GLOBALS['TL_LANG']['MSG']['epay'][-1016]	= 'There is disruption at the acquirer. This is an offline procedure. Please wait a moment and try again.';
$GLOBALS['TL_LANG']['MSG']['epay'][-1015]	= 'Currency code was not found. You should check your currency code you can accept payments with.';
$GLOBALS['TL_LANG']['MSG']['epay'][-1014]	= 'Rejected - card type was not valid for 3D secure. The shop has chosen not to accept no 3D secure payments!';
$GLOBALS['TL_LANG']['MSG']['epay'][-1012]	= 'Rejected - Unable to renew this type of card.';
$GLOBALS['TL_LANG']['MSG']['epay'][-1011]	= 'MD5 stamp was not valid.';
$GLOBALS['TL_LANG']['MSG']['epay'][-1010]	= 'The cardtype was not found in the specified list of predefined card types (field cardtype). If you wish to receive this type of card you need to add the card type to this list.';
$GLOBALS['TL_LANG']['MSG']['epay'][-1009]	= 'Subscription was not found.';
$GLOBALS['TL_LANG']['MSG']['epay'][-1008]	= 'The transaction could not be found.';
$GLOBALS['TL_LANG']['MSG']['epay'][-1007]	= 'Differences in the amount captured / available.';
$GLOBALS['TL_LANG']['MSG']['epay'][-1006]	= 'Product Unavailable.';
$GLOBALS['TL_LANG']['MSG']['epay'][-1005]	= 'Disruption - try again later.';
$GLOBALS['TL_LANG']['MSG']['epay'][-1004]	= 'Error code not found.';
$GLOBALS['TL_LANG']['MSG']['epay'][-1003]	= 'No access to the ipaddress for remote interface (API).';
$GLOBALS['TL_LANG']['MSG']['epay'][-1002]	= 'Merchantnumber was not found in the payment system.';
$GLOBALS['TL_LANG']['MSG']['epay'][-1001]	= 'Order number already exists.';
$GLOBALS['TL_LANG']['MSG']['epay'][-1000]	= 'Communication disorders at the acquirer.';
$GLOBALS['TL_LANG']['MSG']['epay'][-23]		= 'PBS test gateway unavailable.';
$GLOBALS['TL_LANG']['MSG']['epay'][-4]		= 'Communication disorders at the acquirer.';
$GLOBALS['TL_LANG']['MSG']['epay'][-3]		= 'Communication disorders at the acquirer.';
$GLOBALS['TL_LANG']['MSG']['epay'][4000]	= 'eDankort / PBS 3D secure / Banking - payment interrupted by user';
$GLOBALS['TL_LANG']['MSG']['epay'][4001]	= 'SOLO - user cut off payment';
$GLOBALS['TL_LANG']['MSG']['epay'][4002]	= 'SOLO - the user was rejected';
$GLOBALS['TL_LANG']['MSG']['epay'][4003]	= 'SOLO - errors in MAC (MD5)';
$GLOBALS['TL_LANG']['MSG']['epay'][4100]	= 'Rejected - No answer';
$GLOBALS['TL_LANG']['MSG']['epay'][4101]	= 'Rejected - Call the card issuer';
$GLOBALS['TL_LANG']['MSG']['epay'][4102]	= 'Rejected - Call the card issuer and keep the card (fraud)';
$GLOBALS['TL_LANG']['MSG']['epay'][4103]	= 'The payment was declined. You might have entered wrong information. Please try again or contact the merchant.';
$GLOBALS['TL_LANG']['MSG']['epay'][4104]	= 'Rejected - System Error - no answer';
$GLOBALS['TL_LANG']['MSG']['epay'][4105]	= 'Rejected - unknown error';
$GLOBALS['TL_LANG']['MSG']['epay'][4106]	= 'Rejected - Card is not approved by VISA / MasterCard / JCB';
$GLOBALS['TL_LANG']['MSG']['epay'][4107]	= 'Rejected - Can not release Euro Line (SEB) payment (not supported)';
$GLOBALS['TL_LANG']['MSG']['epay'][4108]	= 'Rejected - Can not renew Euro Line (SEB) payment (not supported)';
$GLOBALS['TL_LANG']['MSG']['epay'][4109]	= 'Rejected - card could not be approved by the 3D secure';
$GLOBALS['TL_LANG']['MSG']['epay'][4110]	= 'Rejected - An error occurred during the approval of 3D secure';
$GLOBALS['TL_LANG']['MSG']['epay'][4111]	= 'Rejected - The card could not be found in 3D secure';
$GLOBALS['TL_LANG']['MSG']['epay'][10004]	= 'The payment via Danske Bank was aborted.';
$GLOBALS['TL_LANG']['MSG']['epay'][10005]	= 'The payment via Danske Bank was aborted.';
$GLOBALS['TL_LANG']['MSG']['epay'][-2]		= 'Communication disorders at the acquirer.';
$GLOBALS['TL_LANG']['MSG']['epay'][-1]		= 'Communication disorders at the acquirer.';
$GLOBALS['TL_LANG']['MSG']['epay'][0]		= 'Approved';
$GLOBALS['TL_LANG']['MSG']['epay'][1]		= 'Rejected';
$GLOBALS['TL_LANG']['MSG']['epay'][100]		= 'The payment was declined. Try again in a moment or try with another credit card.';
$GLOBALS['TL_LANG']['MSG']['epay'][101]		= 'The payment was declined as the credit card is expired. Please try with another credit card.';
$GLOBALS['TL_LANG']['MSG']['epay'][102]		= 'The payment was declined. Try again in a moment or try with another credit card.';
$GLOBALS['TL_LANG']['MSG']['epay'][103]		= 'The payment was declined of unknown reasons. For more information contact the bank. E.g. try with another credit card.';
$GLOBALS['TL_LANG']['MSG']['epay'][104]		= 'The payment was declined as the credit card can only be used in the card holder home country. Try with another credit card.';
$GLOBALS['TL_LANG']['MSG']['epay'][105]		= 'The payment was declined of unknown reasons. For more information contact the bank. E.g. try with another credit card.';
$GLOBALS['TL_LANG']['MSG']['epay'][106]		= 'The payment was declined of unknown reasons. For more information contact the bank. E.g. try with another credit card.';
$GLOBALS['TL_LANG']['MSG']['epay'][107]		= 'The payment was declined. Try again in a moment or try with another credit card.';
$GLOBALS['TL_LANG']['MSG']['epay'][108]		= 'The payment was declined. Try again in a moment or try with another credit card.';
$GLOBALS['TL_LANG']['MSG']['epay'][109]		= 'The payment was declined as the merchant hos not any aggreement to the credit card used for this transaction. Try with another credit card.';
$GLOBALS['TL_LANG']['MSG']['epay'][110]		= 'The payment was declined. Try again in a moment or try with another credit card.';
$GLOBALS['TL_LANG']['MSG']['epay'][111]		= 'The payment was declined as the credit card number can not be found. Try to enter the credit card information again or try with another credit card.';
$GLOBALS['TL_LANG']['MSG']['epay'][112]		= 'The payment was declined. Try again in a moment or try with another credit card.';
$GLOBALS['TL_LANG']['MSG']['epay'][113]		= 'The payment was declined. Try again in a moment or try with another credit card.';
$GLOBALS['TL_LANG']['MSG']['epay'][114]		= 'The payment was declined. Try again in a moment or try with another credit card.';
$GLOBALS['TL_LANG']['MSG']['epay'][115]		= 'The payment was declined. Try again in a moment or try with another credit card.';
$GLOBALS['TL_LANG']['MSG']['epay'][116]		= 'The payment was declined as there is no coverage of the transaction amount. Try with another credit card.';
$GLOBALS['TL_LANG']['MSG']['epay'][117]		= 'The payment was declined. Try again in a moment or try with another credit card.';
$GLOBALS['TL_LANG']['MSG']['epay'][118]		= 'The payment was declined as the credit card number can not be found. Try to enter the credit card information again or try with another credit card.';
$GLOBALS['TL_LANG']['MSG']['epay'][119]		= 'The payment was declined as there is no coverage of the transaction amount. Try with another credit card.';
$GLOBALS['TL_LANG']['MSG']['epay'][120]		= 'The payment was declined as the merchant hos not any aggreement to the credit card used for this transaction. Try with another credit card.';
$GLOBALS['TL_LANG']['MSG']['epay'][121]		= 'The payment was declined as there is no coverage of the transaction amount. Try with another credit card.';
$GLOBALS['TL_LANG']['MSG']['epay'][122]		= 'The payment was declined. Try again in a moment or try with another credit card.';
$GLOBALS['TL_LANG']['MSG']['epay'][123]		= 'The payment was declined as there is no coverage of the transaction amount. Try with another credit card.';
$GLOBALS['TL_LANG']['MSG']['epay'][124]		= 'The payment was declined. Try again in a moment or try with another credit card.';
$GLOBALS['TL_LANG']['MSG']['epay'][125]		= 'The payment was declined as the credit card is expired. Please try with another credit card.';
$GLOBALS['TL_LANG']['MSG']['epay'][126]		= 'The payment was declined. Try again in a moment or try with another credit card.';
$GLOBALS['TL_LANG']['MSG']['epay'][127]		= 'The payment was declined. Try again in a moment or try with another credit card.';
$GLOBALS['TL_LANG']['MSG']['epay'][128]		= 'The payment was declined. Try again in a moment or try with another credit card.';
$GLOBALS['TL_LANG']['MSG']['epay'][129]		= 'The payment was declined. Try again in a moment or try with another credit card.';
$GLOBALS['TL_LANG']['MSG']['epay'][160]		= 'The payment was declined. Try again in a moment or try with another credit card.';
$GLOBALS['TL_LANG']['MSG']['epay'][161]		= 'The payment was declined. Try again in a moment or try with another credit card.';
$GLOBALS['TL_LANG']['MSG']['epay'][162]		= 'The payment was declined. Try again in a moment or try with another credit card.';
$GLOBALS['TL_LANG']['MSG']['epay'][164]		= 'The payment was declined. Try again in a moment or try with another credit card.';
$GLOBALS['TL_LANG']['MSG']['epay'][165]		= 'The payment was declined. Try again in a moment or try with another credit card.';
$GLOBALS['TL_LANG']['MSG']['epay'][167]		= 'The payment was declined. Try again in a moment or try with another credit card.';
$GLOBALS['TL_LANG']['MSG']['epay'][200]		= 'The payment was declined. Try again in a moment or try with another credit card.';
$GLOBALS['TL_LANG']['MSG']['epay'][201]		= 'The payment was declined as the credit card number can not be found. Try to enter the credit card information again or try with another credit card.';
$GLOBALS['TL_LANG']['MSG']['epay'][202]		= 'The payment was declined. Try again in a moment or try with another credit card.';
$GLOBALS['TL_LANG']['MSG']['epay'][203]		= 'The payment was declined of unknown reasons. For more information contact the bank. E.g. try with another credit card.';
$GLOBALS['TL_LANG']['MSG']['epay'][204]		= 'The payment was declined. Try again in a moment or try with another credit card.';
$GLOBALS['TL_LANG']['MSG']['epay'][205]		= 'The payment was declined of unknown reasons. For more information contact the bank. E.g. try with another credit card.';
$GLOBALS['TL_LANG']['MSG']['epay'][206]		= 'The payment was declined. Try again in a moment or try with another credit card.';
$GLOBALS['TL_LANG']['MSG']['epay'][207]		= 'The payment was declined of unknown reasons. For more information contact the bank. E.g. try with another credit card.';
$GLOBALS['TL_LANG']['MSG']['epay'][208]		= 'The payment was declined of unknown reasons. For more information contact the bank. E.g. try with another credit card.';
$GLOBALS['TL_LANG']['MSG']['epay'][209]		= 'The payment was declined of unknown reasons. For more information contact the bank. E.g. try with another credit card.';
$GLOBALS['TL_LANG']['MSG']['epay'][210]		= 'The payment was declined of unknown reasons. For more information contact the bank. E.g. try with another credit card.';
$GLOBALS['TL_LANG']['MSG']['epay'][900]		= 'Approved';
$GLOBALS['TL_LANG']['MSG']['epay'][901]		= 'Approved';
$GLOBALS['TL_LANG']['MSG']['epay'][902]		= 'The payment was declined as the credit card number can not be found. Try to enter the credit card information again or try with another credit card.';
$GLOBALS['TL_LANG']['MSG']['epay'][903]		= 'The payment was declined. Try again in a moment or try with another credit card.';
$GLOBALS['TL_LANG']['MSG']['epay'][904]		= 'The payment was declined - system errror / timeout. Wait a moment and try again with the current or another credit card.';
$GLOBALS['TL_LANG']['MSG']['epay'][905]		= 'The payment was declined - system errror / timeout. Wait a moment and try again with the current or another credit card.';
$GLOBALS['TL_LANG']['MSG']['epay'][906]		= 'The payment was declined - system errror / timeout. Wait a moment and try again with the current or another credit card.';
$GLOBALS['TL_LANG']['MSG']['epay'][907]		= 'The payment was declined - system errror / timeout. Wait a moment and try again with the current or another credit card.';
$GLOBALS['TL_LANG']['MSG']['epay'][908]		= 'The payment was declined - system errror / timeout. Wait a moment and try again with the current or another credit card.';
$GLOBALS['TL_LANG']['MSG']['epay'][909]		= 'The payment was declined - system errror / timeout. Wait a moment and try again with the current or another credit card.';
$GLOBALS['TL_LANG']['MSG']['epay'][910]		= 'The payment was declined - system errror / timeout. Wait a moment and try again with the current or another credit card.';
$GLOBALS['TL_LANG']['MSG']['epay'][911]		= 'The payment was declined - system errror / timeout. Wait a moment and try again with the current or another credit card.';
$GLOBALS['TL_LANG']['MSG']['epay'][912]		= 'The payment was declined - system errror / timeout. Wait a moment and try again with the current or another credit card.';
$GLOBALS['TL_LANG']['MSG']['epay'][913]		= 'The payment was declined - system errror / timeout. Wait a moment and try again with the current or another credit card.';
$GLOBALS['TL_LANG']['MSG']['epay'][914]		= 'The payment was declined - system errror / timeout. Wait a moment and try again with the current or another credit card.';
$GLOBALS['TL_LANG']['MSG']['epay'][915]		= 'The payment was declined - system errror / timeout. Wait a moment and try again with the current or another credit card.';
$GLOBALS['TL_LANG']['MSG']['epay'][916]		= 'The payment was declined - system errror / timeout. Wait a moment and try again with the current or another credit card.';
$GLOBALS['TL_LANG']['MSG']['epay'][917]		= 'The payment was declined - system errror / timeout. Wait a moment and try again with the current or another credit card.';
$GLOBALS['TL_LANG']['MSG']['epay'][918]		= 'The payment was declined - system errror / timeout. Wait a moment and try again with the current or another credit card.';
$GLOBALS['TL_LANG']['MSG']['epay'][919]		= 'The payment was declined - system errror / timeout. Wait a moment and try again with the current or another credit card.';
$GLOBALS['TL_LANG']['MSG']['epay'][920]		= 'The payment was declined - system errror / timeout. Wait a moment and try again with the current or another credit card.';
$GLOBALS['TL_LANG']['MSG']['epay'][921]		= 'The payment was declined - system errror / timeout. Wait a moment and try again with the current or another credit card.';
$GLOBALS['TL_LANG']['MSG']['epay'][922]		= 'The payment was declined - system errror / timeout. Wait a moment and try again with the current or another credit card.';
$GLOBALS['TL_LANG']['MSG']['epay'][923]		= 'The payment was declined - system errror / timeout. Wait a moment and try again with the current or another credit card.';
$GLOBALS['TL_LANG']['MSG']['epay'][940]		= 'The payment was declined - system errror / timeout. Wait a moment and try again with the current or another credit card.';
$GLOBALS['TL_LANG']['MSG']['epay'][945]		= 'The payment was declined - system errror / timeout. Wait a moment and try again with the current or another credit card.';
$GLOBALS['TL_LANG']['MSG']['epay'][946]		= 'The payment was declined - system errror / timeout. Wait a moment and try again with the current or another credit card.';
$GLOBALS['TL_LANG']['MSG']['epay'][950]		= 'The payment was declined - system errror / timeout. Wait a moment and try again with the current or another credit card.';
$GLOBALS['TL_LANG']['MSG']['epay'][984]		= 'The payment was declined - system errror / timeout. Wait a moment and try again with the current or another credit card.';

