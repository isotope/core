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
 * @author     Stefan Johannsen <stefan@reklamehuset.dk>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */

$GLOBALS['TL_LANG']['ERR']['systemColumn'] = 'Navnet "%s" er reserveret til systemet. Vælg venligst et andet navn.';
$GLOBALS['TL_LANG']['ERR']['missingButtonTemplate'] = 'Du skal angive en skabelon til knappen "%s"';
$GLOBALS['TL_LANG']['ERR']['order_conditions'] = 'Du skal godkende salgs- og leveringsbetingelserne';
$GLOBALS['TL_LANG']['ERR']['noStoreConfigurationSet'] = 'Ingen shop konfiguration fundet';
$GLOBALS['TL_LANG']['ERR']['noDefaultStoreConfiguration'] = 'Opret venligst en standard shop konfiguration';
$GLOBALS['TL_LANG']['ERR']['productNameMissing'] = '<ingen produktnavne fundet>';
$GLOBALS['TL_LANG']['ERR']['noSubProducts'] = 'ingen under-produkter fundet';
$GLOBALS['TL_LANG']['ERR']['emptyOrderHistory'] = 'Du har endnu ikke angivet en ordre';
$GLOBALS['TL_LANG']['ERR']['orderNotFound'] = 'Den ønskede ordre blev ikke fundet';
$GLOBALS['TL_LANG']['ERR']['missingCurrencyFormat'] = 'Kurs format ikke fundet';
$GLOBALS['TL_LANG']['ERR']['searchNotEnabled'] = 'Søgefunktion er ikke slået til!';
$GLOBALS['TL_LANG']['ERR']['isoLoginRequired'] = 'Du skal være logget ind for at afslutte ordren.';
$GLOBALS['TL_LANG']['ERR']['mandatoryOption'] = 'Træf venligst et valg.';
$GLOBALS['TL_LANG']['ERR']['noAddressData'] = 'Adresseoplysninger er påkrævet for at beregne moms!';
$GLOBALS['TL_LANG']['ERR']['variantDuplicate'] = 'En variant med denne egenskab findes allerede. Brug venligst en anden kombination.';
$GLOBALS['TL_LANG']['ERR']['breadcrumbEmpty'] = 'Filtreringen gav intet resultat. Alle produkter bliver vist';
$GLOBALS['TL_LANG']['ERR']['discountFactors'] = 'Indtast venligst hele numre og decimaler afsluttet med -, og evt. med en procent';
$GLOBALS['TL_LANG']['ERR']['generalFactors'] = 'Indtast venligst hele numre og decimaler afsluttet med + eller -, og evt. med en procent';
$GLOBALS['TL_LANG']['ERR']['specifyBillingAddress'] = 'Der blev ikke fundet en betalingsadresse. Angiv venligst en betalingsadresse.';
$GLOBALS['TL_LANG']['ERR']['cc_num'] = 'Indtast venligst et gyldigt kreditkort nummer';
$GLOBALS['TL_LANG']['ERR']['cc_type'] = 'Vælg venligst en kreditkort type.';
$GLOBALS['TL_LANG']['ERR']['cc_exp'] = 'Indtast venligst udløbsdato for kreditkort som mm/åå.';
$GLOBALS['TL_LANG']['ERR']['cc_ccv'] = 'Indtast verificeringskoden (3 eller 4 tal, findes bag på dit kreditkort).';
$GLOBALS['TL_LANG']['ERR']['cc_match'] = 'Det indtastede kreditkort nummer passer ikke til typen af kreditkort.';
$GLOBALS['TL_LANG']['ERR']['cc_exp_paypal'] = 'Indtast venligst udløbsdato for kreditkort som mm/åååå.';
$GLOBALS['TL_LANG']['ERR']['addressDoesNotExist'] = 'Denne adresse findes ikke i din adressebog';
$GLOBALS['TL_LANG']['ERR']['noAddressBookEntries'] = 'Du har ingen adresser i din adressebog';
$GLOBALS['TL_LANG']['MSC']['labelLanguage'] = 'Sprog';
$GLOBALS['TL_LANG']['MSC']['editLanguage'] = 'Rediger';
$GLOBALS['TL_LANG']['MSC']['deleteLanguage'] = 'Slet';
$GLOBALS['TL_LANG']['MSC']['defaultLanguage'] = 'Sprogsuppleant';
$GLOBALS['TL_LANG']['MSC']['editingLanguage'] = 'BEMÆRK! Du redigerer i sprog-specifik data';
$GLOBALS['TL_LANG']['MSC']['deleteLanguageConfirm'] = 'Er du sikker på at vil slette dette sprog. Det kan ikke fortrydes!';
$GLOBALS['TL_LANG']['MSC']['undefinedLanguage'] = 'Ikke angivet';
$GLOBALS['TL_LANG']['MSC']['noSurcharges'] = 'Ingen tillæg fundet.';
$GLOBALS['TL_LANG']['MSC']['ajaxLoadingMessage'] = 'Henter...';
$GLOBALS['TL_LANG']['MSC']['orderDetailsHeadline'] = 'Ordre nr. %s / %s';
$GLOBALS['TL_LANG']['MSC']['payment_processing'] = 'Din betaling bliver behandlet. Vent venligst...';
$GLOBALS['TL_LANG']['MSC']['payment_processing_failed'] = 'Din betaling kunne ikke gennemføres.';
$GLOBALS['TL_LANG']['MSC']['authorizedotnet_process_failed'] = 'Din betaling kunne ikke gennemføres. <br /><br />Fejlesked: %s';
$GLOBALS['TL_LANG']['MSC']['quantity'] = 'Antal';
$GLOBALS['TL_LANG']['MSC']['order_conditions'] = 'Jeg godkender salgs- og leveringsbetingelser';
$GLOBALS['TL_LANG']['MSC']['defaultSearchText'] = 'Søg produkter';
$GLOBALS['TL_LANG']['MSC']['blankSelectOptionLabel'] = '-';
$GLOBALS['TL_LANG']['MSC']['emptySelectOptionLabel'] = 'Vælg venlisgt...';
$GLOBALS['TL_LANG']['MSC']['downloadsLabel'] = 'Dine produkter til download';
$GLOBALS['TL_LANG']['MSC']['priceRangeLabel'] = '<span class="from">Fra</span> %s';
$GLOBALS['TL_LANG']['MSC']['detailLabel'] = 'Se detaljer';
$GLOBALS['TL_LANG']['MSC']['searchTextBoxLabel'] = 'Søg beskrivelse:';
$GLOBALS['TL_LANG']['MSC']['searchFieldsLabel'] = 'Søg felter:';
$GLOBALS['TL_LANG']['MSC']['perPageLabel'] = 'Produkter pr. side';
$GLOBALS['TL_LANG']['MSC']['searchTermsLabel'] = 'Nøgleord';
$GLOBALS['TL_LANG']['MSC']['searchLabel'] = 'Søg';
$GLOBALS['TL_LANG']['MSC']['submitLabel'] = 'OK';
$GLOBALS['TL_LANG']['MSC']['clearFiltersLabel'] = 'Nulstil filtre';
$GLOBALS['TL_LANG']['MSC']['buttonLabel']['update'] = 'Opdater';
$GLOBALS['TL_LANG']['MSC']['buttonLabel']['add_to_cart'] = 'Læg i kurv';
$GLOBALS['TL_LANG']['MSC']['pagerSectionTitleLabel'] = 'Side:';
$GLOBALS['TL_LANG']['MSC']['orderByLabel'] = 'Sorter efter:';
$GLOBALS['TL_LANG']['MSC']['buttonActionString']['add_to_cart'] = 'Læg produktet %s i din kurv';
$GLOBALS['TL_LANG']['MSC']['noProducts'] = 'Ingen produkter blev fundet.';
$GLOBALS['TL_LANG']['MSC']['invalidProductInformation'] = 'Beklager, men den ønskede produkt information kan ikke vises. Kontakt os for at få hjælp.';
$GLOBALS['TL_LANG']['MSC']['productOptionsLabel'] = 'Varianter';
$GLOBALS['TL_LANG']['MSC']['previousStep'] = 'Tilbage';
$GLOBALS['TL_LANG']['MSC']['nextStep'] = 'Fortsæt';
$GLOBALS['TL_LANG']['MSC']['confirmOrder'] = 'Ordre';
$GLOBALS['TL_LANG']['MSC']['noCategoriesAssociated'] = 'Ingen kategorier er knyttet til dette produkt.';
$GLOBALS['TL_LANG']['MSC']['labelPerPage'] = 'Pr. side';
$GLOBALS['TL_LANG']['MSC']['labelSortBy'] = 'Sorter efter';
$GLOBALS['TL_LANG']['MSC']['labelSubmit'] = 'OK';
$GLOBALS['TL_LANG']['MSC']['labelProductVariants'] = 'Vælg venligst';
$GLOBALS['TL_LANG']['MSC']['removeProductLinkText'] = 'Fjern';
$GLOBALS['TL_LANG']['MSC']['noItemsInCart'] = 'Der er ingen emner i din kurv';
$GLOBALS['TL_LANG']['MSC']['removeProductLinkTitle'] = 'Fjern %s fra din kurv';
$GLOBALS['TL_LANG']['MSC']['subTotalLabel'] = 'Ordre subtotal';
$GLOBALS['TL_LANG']['MSC']['shippingLabel'] = 'Levering';
$GLOBALS['TL_LANG']['MSC']['paymentLabel'] = 'Betaling';
$GLOBALS['TL_LANG']['MSC']['taxLabel'] = '%s moms:';
$GLOBALS['TL_LANG']['MSC']['grandTotalLabel'] = 'Ordre total';
$GLOBALS['TL_LANG']['MSC']['shippingOptionsLabel'] = 'Valgte leveringsmetode:';
$GLOBALS['TL_LANG']['MSC']['noVariants'] = 'Ingen varianter fundet.';
$GLOBALS['TL_LANG']['MSC']['generateSubproducts'] = 'Tilføj underprodukter';
$GLOBALS['TL_LANG']['MSC']['selectItemPrompt'] = '(vælg)';
$GLOBALS['TL_LANG']['MSC']['actualPrice'] = 'Aktuel pris';
$GLOBALS['TL_LANG']['MSC']['noPaymentModules'] = 'Der er ingen betalingsmetoder tilgængelig';
$GLOBALS['TL_LANG']['MSC']['noShippingModules'] = 'Der er ingen leveringsmetoder tilgængelig';
$GLOBALS['TL_LANG']['MSC']['noOrderEmails'] = 'Ingen ordre-email angivet';
$GLOBALS['TL_LANG']['ISO']['couponsInputLabel'] = 'Rabat kode';
$GLOBALS['TL_LANG']['ISO']['couponsHeadline'] = 'Brug rabatkode';
$GLOBALS['TL_LANG']['ISO']['couponsSubmitLabel'] = 'Tilføj';
$GLOBALS['TL_LANG']['MSC']['cartBT'] = 'Indkøbskurv';
$GLOBALS['TL_LANG']['MSC']['checkoutBT'] = 'Gå til kassen';
$GLOBALS['TL_LANG']['MSC']['continueShoppingBT'] = 'Køb mere';
$GLOBALS['TL_LANG']['MSC']['updateCartBT'] = 'Opdater kurv';
$GLOBALS['TL_LANG']['MSC']['orderStatusHeadline'] = 'Ordre status %s';
$GLOBALS['TL_LANG']['MSC']['addressBookLabel'] = 'Adresse';
$GLOBALS['TL_LANG']['MSC']['editAddressLabel'] = 'Rediger';
$GLOBALS['TL_LANG']['MSC']['deleteAddressLabel'] = 'Slet';
$GLOBALS['TL_LANG']['MSC']['createNewAddressLabel'] = 'Opret ny adresse';
$GLOBALS['TL_LANG']['MSC']['useBillingAddress'] = 'Brug betalingsadresse';
$GLOBALS['TL_LANG']['MSC']['differentShippingAddress'] = 'Anden leveringsadresse';
$GLOBALS['TL_LANG']['MSC']['iso_invoice_title'] = 'Faktura';
$GLOBALS['TL_LANG']['MSC']['iso_order_status'] = 'Status';
$GLOBALS['TL_LANG']['MSC']['iso_order_date'] = 'Ordre dato';
$GLOBALS['TL_LANG']['MSC']['iso_billing_address_header'] = 'Betalingsadresse';
$GLOBALS['TL_LANG']['MSC']['iso_shipping_address_header'] = 'Leveringsadresse';
$GLOBALS['TL_LANG']['MSC']['iso_tax_header'] = 'Moms';
$GLOBALS['TL_LANG']['MSC']['iso_subtotal_header'] = 'Subtotal';
$GLOBALS['TL_LANG']['MSC']['iso_order_shipping_header'] = 'Salgs- og leveringsbetingelser';
$GLOBALS['TL_LANG']['MSC']['iso_order_grand_total_header'] = 'Total';
$GLOBALS['TL_LANG']['MSC']['iso_order_items'] = 'Emner';
$GLOBALS['TL_LANG']['MSC']['iso_quantity_header'] = 'Antal';
$GLOBALS['TL_LANG']['MSC']['iso_price_header'] = 'Pris';
$GLOBALS['TL_LANG']['MSC']['iso_sku_header'] = 'Varenummer';
$GLOBALS['TL_LANG']['MSC']['iso_product_name_header'] = 'Produktnavn';
$GLOBALS['TL_LANG']['MSC']['iso_card_name_title'] = 'Navn på kreditkort';
$GLOBALS['TL_LANG']['ORDER']['pending'] = 'Venter';
$GLOBALS['TL_LANG']['ORDER']['processing'] = 'Bliver behandlet';
$GLOBALS['TL_LANG']['ORDER']['complete'] = 'Afsluttet';
$GLOBALS['TL_LANG']['ORDER']['on_hold'] = 'På hold';
$GLOBALS['TL_LANG']['ORDER']['cancelled'] = 'Annuleret';
$GLOBALS['TL_LANG']['MSC']['low_to_high'] = 'lav til høj';
$GLOBALS['TL_LANG']['MSC']['high_to_low'] = 'høj til lav';
$GLOBALS['TL_LANG']['MSC']['a_to_z'] = 'A til Z';
$GLOBALS['TL_LANG']['MSC']['z_to_a'] = 'Z til A';
$GLOBALS['TL_LANG']['MSC']['old_to_new'] = 'tidligere til senere';
$GLOBALS['TL_LANG']['MSC']['new_to_old'] = 'senere til tidligere';
$GLOBALS['TL_LANG']['ISO']['productSingle'] = '1 produkt';
$GLOBALS['TL_LANG']['ISO']['productMultiple'] = '%s produkter';
$GLOBALS['TL_LANG']['ISO']['shipping_address_message'] = 'Angiv en leveringsadresse eller vælg en bestående adresse.';
$GLOBALS['TL_LANG']['ISO']['billing_address_message'] = 'Angiv en betalingsadresse eller vælg en bestående adresse.';
$GLOBALS['TL_LANG']['ISO']['billing_address_guest_message'] = 'Indtast dine kontaktoplysninger';
$GLOBALS['TL_LANG']['ISO']['shipping_method_message'] = 'Vælg en leveringsmetode.';
$GLOBALS['TL_LANG']['ISO']['shipping_method_missing'] = 'Vælg venligst en leveringsmetode.';
$GLOBALS['TL_LANG']['ISO']['payment_method_message'] = 'Vælg en betalingsmetode';
$GLOBALS['TL_LANG']['ISO']['payment_method_missing'] = 'Vælg venligts en betalingsmetode';
$GLOBALS['TL_LANG']['ISO']['order_review_message'] = 'Kontroller og godkend venligst din ordre';
$GLOBALS['TL_LANG']['ISO']['checkout_address'] = 'Adresse';
$GLOBALS['TL_LANG']['ISO']['checkout_shipping'] = 'Levering';
$GLOBALS['TL_LANG']['ISO']['checkout_payment'] = 'Betaling';
$GLOBALS['TL_LANG']['ISO']['checkout_review'] = 'Bedøm';
$GLOBALS['TL_LANG']['ISO']['billing_address'] = 'Betalingsadresse';
$GLOBALS['TL_LANG']['ISO']['shipping_address'] = 'Leveringsadresse';
$GLOBALS['TL_LANG']['ISO']['billing_shipping_address'] = 'Betaling- og leveringsadresse';
$GLOBALS['TL_LANG']['ISO']['shipping_method'] = 'Leveringsmetode';
$GLOBALS['TL_LANG']['ISO']['payment_method'] = 'Betalingsmetode';
$GLOBALS['TL_LANG']['ISO']['order_conditions'] = 'Ordrebetingelser';
$GLOBALS['TL_LANG']['ISO']['order_review'] = 'Ordrebedømmelse';
$GLOBALS['TL_LANG']['ISO']['changeCheckoutInfo'] = 'Rediger';
$GLOBALS['TL_LANG']['ISO']['cc_num'] = 'Kreditkort nummer';
$GLOBALS['TL_LANG']['ISO']['cc_type'] = 'Kreditkort type';
$GLOBALS['TL_LANG']['ISO']['cc_ccv'] = 'Verificeringsnummer (3 eller 4 tal)';
$GLOBALS['TL_LANG']['ISO']['cc_exp_paypal'] = 'Kredit kort udløbsdato (mm/åååå)';
$GLOBALS['TL_LANG']['ISO']['cc_exp_month'] = 'Udløbs måned';
$GLOBALS['TL_LANG']['ISO']['cc_exp_year'] = 'Udløbs år';
$GLOBALS['TL_LANG']['ISO']['cc_issue_number'] = 'Credit card issue number, 2 digits (required for Maestro and Solo cards).';
$GLOBALS['TL_LANG']['ISO']['cc_start_date'] = 'Credit card start date (required for Maestro and Solo cards).';
$GLOBALS['TL_LANG']['ISO']['pay_with_paypal'][0] = 'Betal med PayPal';
$GLOBALS['TL_LANG']['ISO']['pay_with_paypal'][1] = 'Du vil blive viderestillet til PayPal betalingssiden. Hvis du ikke automatisk bliver viderestillet, klik på knappen "Betal nu"';
$GLOBALS['TL_LANG']['ISO']['pay_with_paypal'][2] = 'Betal nu';
$GLOBALS['TL_LANG']['ISO']['pay_with_epay'][0] = 'Betal med ePay';
$GLOBALS['TL_LANG']['ISO']['pay_with_epay'][1] = 'Du vil blive viderestillet til ePay betalingssiden. Hvis du ikke automatisk bliver viderestillet, klik på knappen "Betal nu"';
$GLOBALS['TL_LANG']['ISO']['pay_with_epay'][2] = 'Betal nu';
$GLOBALS['TL_LANG']['ISO']['backendPaymentNotFound'] = 'Betalingsmodul ikke fundet!';
$GLOBALS['TL_LANG']['ISO']['backendShippingNotFound'] = 'Leveringsmodul ikke fundet!';
$GLOBALS['TL_LANG']['ISO']['backendPaymentNoInfo'] = 'Dette betalingsmodul tillader ikke yderligere information.';
$GLOBALS['TL_LANG']['ISO']['backendShippingNoInfo'] = 'Dette leveringsmodul tillader ikke yderligere information.';
$GLOBALS['TL_LANG']['SHIP']['flat'][0] = 'Flat-price levering';
$GLOBALS['TL_LANG']['SHIP']['weight_total'][0] = 'Vægt-baseret levering';
$GLOBALS['TL_LANG']['SHIP']['order_total'][0] = 'Ordre total-baseret levering';
$GLOBALS['TL_LANG']['SHIP']['collection'][0] = 'Kollektion';
$GLOBALS['TL_LANG']['SHIP']['ups'][0] = 'UPS Live Rates and Service shipping';
$GLOBALS['TL_LANG']['SHIP']['usps'][0] = 'USPS Live Rates and Service shipping';
$GLOBALS['TL_LANG']['PAY']['cash'][0] = 'Kontant';
$GLOBALS['TL_LANG']['PAY']['cash'][1] = 'Brug denne til alle offline betalinger';
$GLOBALS['TL_LANG']['PAY']['paypal'][0] = 'PayPal Standard Checkout';
$GLOBALS['TL_LANG']['PAY']['paypal'][1] = 'Dette PayPal modul understøtter IPN (Instant Payment Notifications).';
$GLOBALS['TL_LANG']['PAY']['paypalpayflowpro'][0] = 'PayPal Payflow Pro';
$GLOBALS['TL_LANG']['PAY']['paypalpayflowpro'][1] = 'PayPal Payflow modulet er en full-service kreditkort gateway, og en mere stabil løsning for de fleste shopløsninger.';
$GLOBALS['TL_LANG']['PAY']['postfinance'][0] = 'Postfinance';
$GLOBALS['TL_LANG']['PAY']['postfinance'][1] = 'Payment gateway for the swiss post payment system that supports various card types. The store will be instantly notified about successfull transactions.';
$GLOBALS['TL_LANG']['PAY']['authorizedotnet'][0] = 'Authorize.net';
$GLOBALS['TL_LANG']['PAY']['authorizedotnet'][1] = 'An Authorize.net payment gateway';
$GLOBALS['TL_LANG']['GAL']['default'][0] = 'Standard galleri (Lightbox/Mediabox)';
$GLOBALS['TL_LANG']['GAL']['default'][1] = 'Bruger Lightbox/Mediabox til at vise store billeder. Husk at vælge den rigtige moo_template i dit sidelayout.';
$GLOBALS['TL_LANG']['ISO_PRODUCT']['regular'][0] = 'Regulert produkt';
$GLOBALS['TL_LANG']['ISO_PRODUCT']['regular'][1] = 'Et standard produkt. Vælg denne hvis ikke andet passer.';
$GLOBALS['TL_LANG']['CCT']['mc'] = 'MasterCard';
$GLOBALS['TL_LANG']['CCT']['visa'] = 'Visa';
$GLOBALS['TL_LANG']['CCT']['amex'] = 'American Express';
$GLOBALS['TL_LANG']['CCT']['discover'] = 'Discover';
$GLOBALS['TL_LANG']['CCT']['jcb'] = 'JCB';
$GLOBALS['TL_LANG']['CCT']['diners'] = 'Diner\'s Club';
$GLOBALS['TL_LANG']['CCT']['enroute'] = 'EnRoute';
$GLOBALS['TL_LANG']['WGT']['mg'][0] = 'Milligram (mg)';
$GLOBALS['TL_LANG']['WGT']['mg'][1] = 'Svarer til en tusindedel af et gram';
$GLOBALS['TL_LANG']['WGT']['g'][0] = 'Gram (g)';
$GLOBALS['TL_LANG']['WGT']['g'][1] = 'Svarer til en tusindedel af et kilo';
$GLOBALS['TL_LANG']['WGT']['kg'][0] = 'Kilo (kg)';
$GLOBALS['TL_LANG']['WGT']['kg'][1] = 'Et kilo svarer til 1000 gram.';
$GLOBALS['TL_LANG']['WGT']['t'][0] = 'Ton (t)';
$GLOBALS['TL_LANG']['WGT']['t'][1] = 'Svarer til 1000 kilo';
$GLOBALS['TL_LANG']['WGT']['ct'][0] = 'Carats (ct)';
$GLOBALS['TL_LANG']['WGT']['ct'][1] = 'Et mål for vægt, der anvendes til smykkesten. En karat er lig med 1 / 5 af et gram (200 mg). Bemærk, at karat med "K" er et mål for renheden af en guldlegering.';
$GLOBALS['TL_LANG']['WGT']['oz'][0] = 'Ounce (oz)';
$GLOBALS['TL_LANG']['WGT']['oz'][1] = 'En vægtenhed svarende til 1/16 af et pund eller 28,35 gram.';
$GLOBALS['TL_LANG']['WGT']['lb'][0] = 'Pund (lb)';
$GLOBALS['TL_LANG']['WGT']['lb'][1] = 'En enhed af masse svarende til 16 ounce';
$GLOBALS['TL_LANG']['WGT']['st'][0] = 'Stone (st)';
$GLOBALS['TL_LANG']['WGT']['st'][1] = 'En britisk måling af masse, der svarer til 14 pund.';
$GLOBALS['TL_LANG']['WGT']['grain'][0] = 'Grain';
$GLOBALS['TL_LANG']['WGT']['grain'][1] = '1/7000 pund; svarer til en troy korn eller 64,799 mg.';
$GLOBALS['TL_LANG']['ATTR']['text'][0] = 'Tekst (op til 255 tegn)';
$GLOBALS['TL_LANG']['ATTR']['textarea'][0] = 'Lang tekst (mere end 255 tegn)';
$GLOBALS['TL_LANG']['ATTR']['select'][0] = 'Valgliste (flere muligheder)';
$GLOBALS['TL_LANG']['ATTR']['checkbox'][0] = 'Afkrydsningsfelt';
$GLOBALS['TL_LANG']['ATTR']['radio'][0] = 'Valgliste (en mulighed)';
$GLOBALS['TL_LANG']['ATTR']['media'][0] = 'Media (billeder, film, MP3 m.m.)';
$GLOBALS['TL_LANG']['ATTR']['conditionalselect'][0] = 'Betinget valgliste';
$GLOBALS['TL_LANG']['CUR']['AED'] = 'AED - United Arab Emirates Dirham';
$GLOBALS['TL_LANG']['CUR']['AFN'] = 'AFN - Afghani';
$GLOBALS['TL_LANG']['CUR']['ALL'] = 'ALL - Lek';
$GLOBALS['TL_LANG']['CUR']['AMD'] = 'AMD - Dram';
$GLOBALS['TL_LANG']['CUR']['ANG'] = 'ANG - Netherlands Antilles Guilder';
$GLOBALS['TL_LANG']['CUR']['AOA'] = 'AOA - Kwanza';
$GLOBALS['TL_LANG']['CUR_SYMBOL']['USD'] = '$';
$GLOBALS['TL_LANG']['CUR_SYMBOL']['EUR'] = '€';
$GLOBALS['TL_LANG']['CUR_SYMBOL']['GBP'] = '£';
$GLOBALS['TL_LANG']['CUR_SYMBOL']['JPY'] = '¥';

