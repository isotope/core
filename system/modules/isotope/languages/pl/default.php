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
 * @author     Radosław Maślanek <radek@dziupla.pl>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */


/**
 * Errors
 */
$GLOBALS['TL_LANG']['ERR']['systemColumn']					= 'Nazwa `%s` jest zarezerwowana przez system. Spróbuj innej nazwy.';
$GLOBALS['TL_LANG']['ERR']['missingButtonTemplate']			= 'Musisz zdefinować szablon dla przycisku "%s".';
$GLOBALS['TL_LANG']['ERR']['order_conditions']				= 'Musisz zaakceptować regulamin aby kontynuować';
$GLOBALS['TL_LANG']['ERR']['noStoreConfigurationSet']		= 'Nie ma żadnej dostępnej konfiguracji sklepu';
$GLOBALS['TL_LANG']['ERR']['noDefaultStoreConfiguration']	= 'Stwórz domyślną konfigurację sklepu.';
$GLOBALS['TL_LANG']['ERR']['productNameMissing']			= '<nie znaleziono żadnej nazwy porduktu>';
$GLOBALS['TL_LANG']['ERR']['noSubProducts']					= 'nie znaleziono sub-produktów';
$GLOBALS['TL_LANG']['ERR']['emptyOrderHistory']				= 'Nie złożono jeszcze żadnych zamówień.';
$GLOBALS['TL_LANG']['ERR']['orderNotFound']					= 'Żądane zamówienie nie zostało odnalezione.';
$GLOBALS['TL_LANG']['ERR']['missingCurrencyFormat']			= 'Format waluty nie znaleziony.';
$GLOBALS['TL_LANG']['ERR']['searchNotEnabled']				= 'Wyszukiwarka nie jest dostępna!';
$GLOBALS['TL_LANG']['ERR']['isoLoginRequired']				= 'Musisz być zalogowany aby przejść do kasy.';
$GLOBALS['TL_LANG']['ERR']['mandatoryOption']				= 'Proszę wybrać opcje.';
$GLOBALS['TL_LANG']['ERR']['noAddressData']					= 'Dane adresowe są wymagane do celów podatkowych!';
$GLOBALS['TL_LANG']['ERR']['variantDuplicate']				= 'Taki sam wariant produktu już istnieje. Utwórz inny wariant';
$GLOBALS['TL_LANG']['ERR']['breadcrumbEmpty']				= 'Filtr był pusty, wyświetlono wszystkie produkty.';
$GLOBALS['TL_LANG']['ERR']['discount']						= 'Proszę wprowadzić liczby całkowite dziesietne poprzedzone  + lub - dozwolony jest także procent.';
$GLOBALS['TL_LANG']['ERR']['orderFailed']					= 'Kasa. Prosze spróbować ponownie lub wybrać inny moduł płatności.';
$GLOBALS['TL_LANG']['ERR']['specifyBillingAddress'] 		= 'Nie znaleziono adresu billingowego.  Prosze uzupełnić adres.';
$GLOBALS['TL_LANG']['ERR']['cc_num']						= 'Proszę podać poprawny numer karty kredytowej.';
$GLOBALS['TL_LANG']['ERR']['cc_type']						= 'Proszę wybrać rodzaj karty.';
$GLOBALS['TL_LANG']['ERR']['cc_exp']						= 'Data ważności karty.';
$GLOBALS['TL_LANG']['ERR']['cc_ccv']						= 'Podaj kod zabezpieczający karty (CVC - najczęściej 3 lub 4 cyfry znajdujące się na odwrocie karty).';
$GLOBALS['TL_LANG']['ERR']['cc_match']						= 'Podany numer karty kredytowej nie pasuje do wybranego typu karty..';
$GLOBALS['TL_LANG']['ERR']['addressDoesNotExist']			= 'Ten adres nie istnieje w Twojej książce adresowej.';
$GLOBALS['TL_LANG']['ERR']['noAddressBookEntries']			= 'Nie masz żadnych wpisów adresowych.';


/**
 * Miscellaneous
 */
$GLOBALS['TL_LANG']['MSC']['labelLanguage']			= 'Język';
$GLOBALS['TL_LANG']['MSC']['editLanguage']			= 'Edytuj';
$GLOBALS['TL_LANG']['MSC']['deleteLanguage']		= 'Skasuj';
$GLOBALS['TL_LANG']['MSC']['defaultLanguage']		= 'Podstawowy';
$GLOBALS['TL_LANG']['MSC']['deleteLanguageConfirm']	= 'Czy na pewno chcesz skasować ten język? Ta operacja jest nieodwracalna!';
$GLOBALS['TL_LANG']['MSC']['undefinedLanguage']		= 'niezdefiniowany';
$GLOBALS['TL_LANG']['MSC']['noSurcharges']			= 'Niczego nie znaleziono (surcharges - poprawic).';
$GLOBALS['TL_LANG']['MSC']['ajaxLoadingMessage']	= 'Wczytywanie...';
$GLOBALS['TL_LANG']['MSC']['orderDetailsHeadline']	= 'Zamówienie nr %s / %s';
$GLOBALS['TL_LANG']['MSC']['payment_processing']	= 'Trwa dokonywanie płatności. Prosimy cierpliwie czekać...';
$GLOBALS['TL_LANG']['MSC']['authorizedotnet_process_failed']	= 'Twoja płatnośc nie mogła zostać zrealizowana.<br /><br />Powód: %s';
$GLOBALS['TL_LANG']['MSC']['mmNoUploads']			= 'Nie załadowano plików.';
$GLOBALS['TL_LANG']['MSC']['mmUpload']				= 'Załaduj nowy plik';
$GLOBALS['TL_LANG']['MSC']['quantity']				= 'Ilość';
$GLOBALS['TL_LANG']['MSC']['order_conditions']		= 'Akceptuję postanowienia regulaminu';

$GLOBALS['TL_LANG']['MSC']['defaultSearchText'] = 'wyszukaj...';
$GLOBALS['TL_LANG']['MSC']['blankSelectOptionLabel'] = '- wybierz -';
$GLOBALS['TL_LANG']['MSC']['emptySelectOptionLabel'] = 'Proszę wybrać...';
$GLOBALS['TL_LANG']['MSC']['downloadsLabel']			= 'Produkty do pobrania';
$GLOBALS['TL_LANG']['MSC']['priceRangeLabel'] = '<span class="from">już od: %s</span>';
$GLOBALS['TL_LANG']['MSC']['detailLabel'] = 'Zobacz więcej';
$GLOBALS['TL_LANG']['MSC']['searchTextBoxLabel'] = 'Szukane wyrażenie: ';
$GLOBALS['TL_LANG']['MSC']['searchFieldsLabel'] = 'Pola wyszukiwania: ';
$GLOBALS['TL_LANG']['MSC']['perPageLabel'] = 'Wyników na stronie';
$GLOBALS['TL_LANG']['MSC']['searchTermsLabel'] = 'Słowa kluczowe';
$GLOBALS['TL_LANG']['MSC']['searchLabel'] = 'Szukaj';
$GLOBALS['TL_LANG']['MSC']['submitLabel'] = 'Wyślij';
$GLOBALS['TL_LANG']['MSC']['clearFiltersLabel'] = 'Wyczyść filtry';
$GLOBALS['TL_LANG']['MSC']['buttonLabel']['update'] = 'Aktualizuj';
$GLOBALS['TL_LANG']['MSC']['buttonLabel']['add_to_cart'] = 'Dodaj do koszyka';
$GLOBALS['TL_LANG']['MSC']['pagerSectionTitleLabel'] = 'Strona:';
$GLOBALS['TL_LANG']['MSC']['orderByLabel'] = 'Porządkuj wg.:';

$GLOBALS['TL_LANG']['MSC']['buttonActionString']['add_to_cart'] = 'Dodaj produkt %s do koszyka';
$GLOBALS['TL_LANG']['MSC']['noProducts'] = 'Niestety, nie odnaleziono żadnego produktu.';

$GLOBALS['TL_LANG']['MSC']['productOptionsLabel'] = 'Opcje';

$GLOBALS['TL_LANG']['MSC']['previousStep']	= 'Wróć';
$GLOBALS['TL_LANG']['MSC']['nextStep']		= 'Dalej';
$GLOBALS['TL_LANG']['MSC']['confirmOrder']	= 'Zamów';

$GLOBALS['TL_LANG']['MSC']['noCategoriesAssociated'] = 'Ten produkt nie należy do żadnej z kategorii.';
$GLOBALS['TL_LANG']['MSC']['labelPerPage'] = 'Na stronę';
$GLOBALS['TL_LANG']['MSC']['labelSortBy'] = 'Porządkuj wg.';
$GLOBALS['TL_LANG']['MSC']['labelSubmit'] = 'Wyślij';
$GLOBALS['TL_LANG']['MSC']['labelProductVariants'] = 'Prosze wybrać';
$GLOBALS['TL_LANG']['MSC']['removeProductLinkText'] = 'Usuń';
$GLOBALS['TL_LANG']['MSC']['noItemsInCart'] = 'Twój koszyk jest w tej chwili pusty';
$GLOBALS['TL_LANG']['MSC']['removeProductLinkTitle'] = 'Usuń %s z koszyka';
$GLOBALS['TL_LANG']['MSC']['subTotalLabel'] = 'Podsuma zamówienia: ';
$GLOBALS['TL_LANG']['MSC']['shippingLabel'] = 'Dostawa';
$GLOBALS['TL_LANG']['MSC']['paymentLabel'] = 'Płatność';
$GLOBALS['TL_LANG']['MSC']['taxLabel'] = '%s podatku: ';
$GLOBALS['TL_LANG']['MSC']['grandTotalLabel'] = 'Suma zamówienia: ';
$GLOBALS['TL_LANG']['MSC']['shippingOptionsLabel'] = 'Wybierz opcje dostawy: ';
$GLOBALS['TL_LANG']['MSC']['noVariants'] = 'Nie znaleziono żadnych wariantów tego produktu.';
$GLOBALS['TL_LANG']['MSC']['generateSubproducts'] = 'Generuj sub-produkty';
$GLOBALS['TL_LANG']['MSC']['selectItemPrompt'] = "(wybierz)";
$GLOBALS['TL_LANG']['MSC']['actualPrice'] = 'Aktualna cena';
$GLOBALS['TL_LANG']['MSC']['noPaymentModules'] = 'W chwili obecnej nie ma dostępnych metod płatności.';
$GLOBALS['TL_LANG']['MSC']['noShippingModules'] = 'W chwili obecnej nie ma dostepnych metod dostawy.';
$GLOBALS['TL_LANG']['MSC']['noOrderEmails'] = 'Nie znaleziono emaili z zamówieniami.';
$GLOBALS['TL_LANG']['MSC']['noOrders'] = 'Nie znaleziono żadnego zamówienia.';

$GLOBALS['TL_LANG']['ISO']['couponsInputLabel'] = 'Kod promocji';
$GLOBALS['TL_LANG']['ISO']['couponsHeadline'] = 'Zastosuj kod promocji';
$GLOBALS['TL_LANG']['ISO']['couponsSubmitLabel'] = 'Zastosuj';

$GLOBALS['TL_LANG']['MSC']['cartBT']					= 'Koszyk';
$GLOBALS['TL_LANG']['MSC']['checkoutBT']				= 'Przejdź do kasy';
$GLOBALS['TL_LANG']['MSC']['continueShoppingBT']		= 'Wróć do sklepu';
$GLOBALS['TL_LANG']['MSC']['updateCartBT']				= 'Aktualizuj koszyk';
$GLOBALS['TL_LANG']['MSC']['orderStatusHeadline']		= 'Status zamówienia: %s';


//Addresses
$GLOBALS['TL_LANG']['MSC']['createNewAddressLabel'] = 'Doddaj nowy adres';
$GLOBALS['TL_LANG']['MSC']['useBillingAddress'] = 'Użyj adresu z rachunku';
$GLOBALS['TL_LANG']['MSC']['differentShippingAddress'] = 'Inny adres dostawy';

$GLOBALS['TL_LANG']['MSC']['addressBookLabel'] = 'Adresy';
$GLOBALS['TL_LANG']['MSC']['editAddressLabel'] = 'Edytuj';
$GLOBALS['TL_LANG']['MSC']['deleteAddressLabel'] = 'Skasuj';

//Invoice language Entries
$GLOBALS['TL_LANG']['MSC']['iso_invoice_title'] = 'Faktura';
$GLOBALS['TL_LANG']['MSC']['iso_order_status'] = 'Status';
$GLOBALS['TL_LANG']['MSC']['iso_order_date'] = 'Data zamówienia';
$GLOBALS['TL_LANG']['MSC']['iso_billing_address_header'] = 'Adres zamawiającego';
$GLOBALS['TL_LANG']['MSC']['iso_shipping_address_header'] = 'Adres odbiorcy';
$GLOBALS['TL_LANG']['MSC']['iso_tax_header'] = 'podatek';
$GLOBALS['TL_LANG']['MSC']['iso_subtotal_header'] = 'Podsumowanie';
$GLOBALS['TL_LANG']['MSC']['iso_order_shipping_header'] = 'Dostawa';
$GLOBALS['TL_LANG']['MSC']['iso_order_grand_total_header'] = 'Podsumowanie';
$GLOBALS['TL_LANG']['MSC']['iso_order_items'] = 'Przedmioty';
$GLOBALS['TL_LANG']['MSC']['iso_quantity_header'] = 'Ilość';
$GLOBALS['TL_LANG']['MSC']['iso_price_header'] = 'Cena';
$GLOBALS['TL_LANG']['MSC']['iso_sku_header'] = 'SKU';
$GLOBALS['TL_LANG']['MSC']['iso_product_name_header'] = 'Nazwa produktu';
$GLOBALS['TL_LANG']['MSC']['iso_card_name_title'] = 'Posiadacz karty kredytowej';


// Order status options
$GLOBALS['TL_LANG']['ORDER']['pending']		= 'Oczekujące';
$GLOBALS['TL_LANG']['ORDER']['processing']	= 'W trakcie realizacji';
$GLOBALS['TL_LANG']['ORDER']['complete']	= 'Zrealizowane';
$GLOBALS['TL_LANG']['ORDER']['on_hold']		= 'Wstrzymane';
$GLOBALS['TL_LANG']['ORDER']['cancelled']	= 'Anulowane';


$GLOBALS['TL_LANG']['MSC']['low_to_high'] = 'rosnąco';
$GLOBALS['TL_LANG']['MSC']['high_to_low'] = 'malejąco';
$GLOBALS['TL_LANG']['MSC']['a_to_z'] = 'A do Z';
$GLOBALS['TL_LANG']['MSC']['z_to_a'] = 'Z do A';
$GLOBALS['TL_LANG']['MSC']['old_to_new'] = 'od najstarszego';
$GLOBALS['TL_LANG']['MSC']['new_to_old'] = 'od najnowszego';



/**
 * Isotope module labels
 */
$GLOBALS['TL_LANG']['ISO']['productSingle']		= '1 Produkt';
$GLOBALS['TL_LANG']['ISO']['productMultiple']	= '%s Produkty';

$GLOBALS['TL_LANG']['ISO']['shipping_address_message']	= 'Wprowadź dane do dostawy lub wybierz spośród zdefiniowanych.';
$GLOBALS['TL_LANG']['ISO']['billing_address_message']	= 'Wybierz dane do faktury lub wybierz spośród zdefiniowanych';
$GLOBALS['TL_LANG']['ISO']['billing_address_guest_message'] = 'Wprowadź dane do faktury';
$GLOBALS['TL_LANG']['ISO']['shipping_method_message']	= 'Wybierz opcję dostawy.';
$GLOBALS['TL_LANG']['ISO']['shipping_method_missing']	= 'Proszę wybrać opcję dostawy.';
$GLOBALS['TL_LANG']['ISO']['payment_method_message']	= 'Wybierz sposób płatności.';
$GLOBALS['TL_LANG']['ISO']['payment_method_missing']	= 'Proszę wybrać sposób płatności.';
$GLOBALS['TL_LANG']['ISO']['order_review_message']		= 'Przejżyj i potwierdż zamówienie.';

$GLOBALS['TL_LANG']['ISO']['checkout_address']				= 'Adres';
$GLOBALS['TL_LANG']['ISO']['checkout_shipping']				= 'Dostawa';
$GLOBALS['TL_LANG']['ISO']['checkout_payment']				= 'Płatność';
$GLOBALS['TL_LANG']['ISO']['checkout_review']				= 'Podsumowanie';
$GLOBALS['TL_LANG']['ISO']['billing_address']				= 'Dane do faktury';
$GLOBALS['TL_LANG']['ISO']['shipping_address']				= 'Dane do dostawy';
$GLOBALS['TL_LANG']['ISO']['billing_shipping_address']		= 'Adres dostawy i fktury';
$GLOBALS['TL_LANG']['ISO']['shipping_method']				= 'Sposób dostawy';
$GLOBALS['TL_LANG']['ISO']['payment_method']				= 'Sposób płatności';
$GLOBALS['TL_LANG']['ISO']['order_conditions']				= 'Warunki zamówienia';
$GLOBALS['TL_LANG']['ISO']['order_review']					='Szczegóły zamówienia';
$GLOBALS['TL_LANG']['ISO']['changeCheckoutInfo']			= 'Zmień';
$GLOBALS['TL_LANG']['ISO']['cc_num']						= 'Numer karty kredytowej';
$GLOBALS['TL_LANG']['ISO']['cc_type']						= 'Typ karty kredytowej';
$GLOBALS['TL_LANG']['ISO']['cc_ccv']						= 'numer CCV (3 lub 4 cyfry)';
$GLOBALS['TL_LANG']['ISO']['cc_exp_date']					= 'Ważność - dzień';
$GLOBALS['TL_LANG']['ISO']['cc_exp_month']					= 'Ważność - miesiąc';
$GLOBALS['TL_LANG']['ISO']['cc_exp_year']					= 'Ważność - rok';
$GLOBALS['TL_LANG']['ISO']['backendPaymentNotFound']		= 'Moduł płatności nie został znaleziony!';
$GLOBALS['TL_LANG']['ISO']['backendShippingNotFound']		= 'Moduł dostawy nie został znaleziony!';
$GLOBALS['TL_LANG']['ISO']['backendPaymentNoInfo']			= 'O tym module płatności nie ma dodatkowych informacji.';
$GLOBALS['TL_LANG']['ISO']['backendShippingNoInfo']			= 'O tym module dostawy nie ma dodatkowych informacji.';


/**
 * Shipping modules
 */
$GLOBALS['TL_LANG']['SHIP']['flat']				= array('Stała cena dostawy');
$GLOBALS['TL_LANG']['SHIP']['weight_total']		= array('Koszt odstawy wg. wagi całkowitej');
$GLOBALS['TL_LANG']['SHIP']['order_total']		= array('Koszt wg. wagi zamówienia');


/**
 * Payment modules
 */
$GLOBALS['TL_LANG']['PAY']['cash']				= array('Gotówka', 'Użyj tego w przypadku rozliczeń gotówkowych');


/**
 * Galleries
 */
$GLOBALS['TL_LANG']['GAL']['default']			= array('Standardowa galeria (Lightbox/Mediabox)', '<p>używa lightbox/mediabox do powiększania obrazków z galerii. Upewnij sie, że wybrany jest odpowiedni moo_ template w konfiguracji szablonów/themes.</p><p>Możesz doać paramert "rel" do linku wg schematu: (np. "tl_files/video.mov|lightbox[400 300]"). Jeżeli nie podasz "rel" link otworzy się w nowym oknie.</p>');


/**
 * Credit card types
 */
$GLOBALS['TL_LANG']['CCT']['mc']					= 'MasterCard';
$GLOBALS['TL_LANG']['CCT']['visa']					= 'Visa';
$GLOBALS['TL_LANG']['CCT']['amex']					= 'American Express';
$GLOBALS['TL_LANG']['CCT']['discover']				= 'Discover';
$GLOBALS['TL_LANG']['CCT']['jcb']					= 'JCB';
$GLOBALS['TL_LANG']['CCT']['diners']				= 'Diner\'s Club';
$GLOBALS['TL_LANG']['CCT']['enroute']				= 'EnRoute';
$GLOBALS['TL_LANG']['CCT']['carte_blanche']			= 'Carte Blanche';
$GLOBALS['TL_LANG']['CCT']['jal']					= 'JAL';
$GLOBALS['TL_LANG']['CCT']['maestro']				= 'Maestro UK';
$GLOBALS['TL_LANG']['CCT']['delta']					= 'Delta';
$GLOBALS['TL_LANG']['CCT']['solo']					= 'Solo';
$GLOBALS['TL_LANG']['CCT']['visa_electron']			= 'Visa Electron';
$GLOBALS['TL_LANG']['CCT']['dankort']				= 'Dankort';
$GLOBALS['TL_LANG']['CCT']['laser']					= 'Laser';
$GLOBALS['TL_LANG']['CCT']['carte_bleue']			= 'Carte Bleue';
$GLOBALS['TL_LANG']['CCT']['carta_si']				= 'Carta Si';
$GLOBALS['TL_LANG']['CCT']['enc_acct_num']			= 'Encoded Account Number';
$GLOBALS['TL_LANG']['CCT']['uatp']					= 'Universal Air Travel Program';
$GLOBALS['TL_LANG']['CCT']['maestro_intl']			= 'Maestro International';
$GLOBALS['TL_LANG']['CCT']['ge_money_uk']			= 'GE Money UK';


/**
 * Weight Units
 * http://www.metric-conversions.org/weight/weight-conversions.htm
 */
$GLOBALS['TL_LANG']['WGT']['mg']					= array('Milligram (mg)', 'Metryczna jednostka wagi odpowiadająca - 0.0001kg 100mg=1g');
$GLOBALS['TL_LANG']['WGT']['g']						= array('Gram (g)', 'Metryczna jednostka wagi odpowiadająca 0.001kg - 1000mg=1kg');
$GLOBALS['TL_LANG']['WGT']['kg']					= array('Kilogram (kg)', 'jeden kilogram równa się 1000 gram lub 2.2 funta; masie 1 litra wody.');
$GLOBALS['TL_LANG']['WGT']['t']						= array('Metric Ton (t)', 'Metryczna jednostaka wagi odpowiadająca 1000 kg lub 2,204.6 funta.');


/**
 * Attributes
 */
$GLOBALS['TL_LANG']['ATTR']['text']					= array('Linia tekstu', 'A single-line input field for a short or medium text.');
$GLOBALS['TL_LANG']['ATTR']['textarea']				= array('Pole tekstu', 'A multi-line input field for a medium or long text.');
$GLOBALS['TL_LANG']['ATTR']['select']				= array('wybór - lista/menu', 'A single- or multi-line drop-down menu.<br /><i>This field type is suitable for product variants.</i>');
$GLOBALS['TL_LANG']['ATTR']['radio']				= array('Wybór - guzik/select', 'A list of multiple options from which one can be selected.<br /><i>This field type is suitable for product variants.</i>');
$GLOBALS['TL_LANG']['ATTR']['checkbox']				= array('Wybór - pole wielokrotnego' , 'A list of multiple options from which any can be selected.');
$GLOBALS['TL_LANG']['ATTR']['mediaManager']			= array('Manager plików', 'Upload images and other files to the Isotope eCommerce file system. Output is processed trough an IsotopeGallery class.');
$GLOBALS['TL_LANG']['ATTR']['conditionalselect']	= array('Warunkowe menu-wyboru', 'pokazuje pocje w zależności od poprzedniego wyboru.');


/**
 * Currencies
 */
$GLOBALS['TL_LANG']['CUR']['AED'] = 'AED - United Arab Emirates Dirham';
$GLOBALS['TL_LANG']['CUR']['AFN'] = 'AFN - Afghani';
$GLOBALS['TL_LANG']['CUR']['ALL'] = 'ALL - Lek';
$GLOBALS['TL_LANG']['CUR']['AMD'] = 'AMD - Dram';
$GLOBALS['TL_LANG']['CUR']['ANG'] = 'ANG - Netherlands Antilles Guilder';
$GLOBALS['TL_LANG']['CUR']['AOA'] = 'AOA - Kwanza';
$GLOBALS['TL_LANG']['CUR']['ARS'] = 'ARS - Argentinian Nuevo Peso';
$GLOBALS['TL_LANG']['CUR']['AUD'] = 'AUD - Australian Dollar';
$GLOBALS['TL_LANG']['CUR']['AWG'] = 'AWG - Aruban Guilder';
$GLOBALS['TL_LANG']['CUR']['AZN'] = 'AZN - Azerbaijani Manat';
$GLOBALS['TL_LANG']['CUR']['BAM'] = 'BAM - Convertible Mark';
$GLOBALS['TL_LANG']['CUR']['BBD'] = 'BBD - Barbados Dollar';
$GLOBALS['TL_LANG']['CUR']['BDT'] = 'BDT - Taka';
$GLOBALS['TL_LANG']['CUR']['BGN'] = 'BGN - Bulgarian Lev';
$GLOBALS['TL_LANG']['CUR']['BHD'] = 'BHD - Bahraini Dinar';
$GLOBALS['TL_LANG']['CUR']['BIF'] = 'BIF - Burundi Franc';
$GLOBALS['TL_LANG']['CUR']['BMD'] = 'BMD - Bermudian Dollar';
$GLOBALS['TL_LANG']['CUR']['BND'] = 'BND - Brunei Dollar';
$GLOBALS['TL_LANG']['CUR']['BOB'] = 'BOB - Boliviano';
$GLOBALS['TL_LANG']['CUR']['BRL'] = 'BRL - Brazilian real';
$GLOBALS['TL_LANG']['CUR']['BSD'] = 'BSD - Bahamian Dollar';
$GLOBALS['TL_LANG']['CUR']['BTN'] = 'BTN - Ngultrum';
$GLOBALS['TL_LANG']['CUR']['BWP'] = 'BWP - Pula';
$GLOBALS['TL_LANG']['CUR']['BYR'] = 'BYR - Belarussian Rouble';
$GLOBALS['TL_LANG']['CUR']['BZD'] = 'BZD - Belize Dollar';
$GLOBALS['TL_LANG']['CUR']['CAD'] = 'CAD - Canadian Dollar';
$GLOBALS['TL_LANG']['CUR']['CDZ'] = 'CDZ - New Zaïre';
$GLOBALS['TL_LANG']['CUR']['CHF'] = 'CHF - Swiss Franc';
$GLOBALS['TL_LANG']['CUR']['CLF'] = 'CLF - Unidades de Fomento';
$GLOBALS['TL_LANG']['CUR']['CLP'] = 'CLP - Chilean Peso';
$GLOBALS['TL_LANG']['CUR']['CNY'] = 'CNY - Yuan Renminbi';
$GLOBALS['TL_LANG']['CUR']['COP'] = 'COP - Colombian Peso';
$GLOBALS['TL_LANG']['CUR']['CRC'] = 'CRC - Costa Rican Colón';
$GLOBALS['TL_LANG']['CUR']['CUP'] = 'CUP - Cuban Peso';
$GLOBALS['TL_LANG']['CUR']['CVE'] = 'CVE - Escudo Caboverdiano';
$GLOBALS['TL_LANG']['CUR']['CZK'] = 'CZK - Czech Koruna';
$GLOBALS['TL_LANG']['CUR']['DJF'] = 'DJF - Djibouti Franc';
$GLOBALS['TL_LANG']['CUR']['DKK'] = 'DKK - Danish Krone';
$GLOBALS['TL_LANG']['CUR']['DOP'] = 'DOP - Dominican Republic Peso';
$GLOBALS['TL_LANG']['CUR']['DZD'] = 'DZD - Algerian Dinar';
$GLOBALS['TL_LANG']['CUR']['EEK'] = 'EEK - Kroon';
$GLOBALS['TL_LANG']['CUR']['EGP'] = 'EGP - Egyptian Pound';
$GLOBALS['TL_LANG']['CUR']['ERN'] = 'ERN - Eritrean Nakfa';
$GLOBALS['TL_LANG']['CUR']['ETB'] = 'ETB - Ethiopian Birr';
$GLOBALS['TL_LANG']['CUR']['EUR'] = 'EUR - Euro';
$GLOBALS['TL_LANG']['CUR']['FJD'] = 'FJD - Fiji Dollar';
$GLOBALS['TL_LANG']['CUR']['FKP'] = 'FKP - Falkland Pound';
$GLOBALS['TL_LANG']['CUR']['GBP'] = 'GBP - Pound Sterling';
$GLOBALS['TL_LANG']['CUR']['GEL'] = 'GEL - Lari';
$GLOBALS['TL_LANG']['CUR']['GHS'] = 'GHS - Ghanaian cedi';
$GLOBALS['TL_LANG']['CUR']['GIP'] = 'GIP - Gibraltar Pound';
$GLOBALS['TL_LANG']['CUR']['GMD'] = 'GMD - Dalasi';
$GLOBALS['TL_LANG']['CUR']['GNS'] = 'GNS - Syli (also known as Guinea Franc)';
$GLOBALS['TL_LANG']['CUR']['GTQ'] = 'GTQ - Quetzal';
$GLOBALS['TL_LANG']['CUR']['GYD'] = 'GYD - Guyana Dollar';
$GLOBALS['TL_LANG']['CUR']['HKD'] = 'HKD - Hong Kong Dollar';
$GLOBALS['TL_LANG']['CUR']['HNL'] = 'HNL - Lempira';
$GLOBALS['TL_LANG']['CUR']['HRD'] = 'HRD - Croatian Dinar';
$GLOBALS['TL_LANG']['CUR']['HRK'] = 'HRK - Croatian Kuna';
$GLOBALS['TL_LANG']['CUR']['HTG'] = 'HTG - Gourde';
$GLOBALS['TL_LANG']['CUR']['HUF'] = 'HUF - Forint';
$GLOBALS['TL_LANG']['CUR']['IDR'] = 'IDR - Rupiah';
$GLOBALS['TL_LANG']['CUR']['ILS'] = 'ILS - Shekel';
$GLOBALS['TL_LANG']['CUR']['INR'] = 'INR - Indian Rupee';
$GLOBALS['TL_LANG']['CUR']['IQD'] = 'IQD - Iraqi Dinar';
$GLOBALS['TL_LANG']['CUR']['IRR'] = 'IRR - Iranian Rial';
$GLOBALS['TL_LANG']['CUR']['ISK'] = 'ISK - Icelandic Króna';
$GLOBALS['TL_LANG']['CUR']['JMD'] = 'JMD - Jamaican Dollar';
$GLOBALS['TL_LANG']['CUR']['JOD'] = 'JOD - Jordanian Dinar';
$GLOBALS['TL_LANG']['CUR']['JPY'] = 'JPY - Yen';
$GLOBALS['TL_LANG']['CUR']['KES'] = 'KES - Kenyan Shilling';
$GLOBALS['TL_LANG']['CUR']['KGS'] = 'KGS - Kyrgyzstani Som';
$GLOBALS['TL_LANG']['CUR']['KHR'] = 'KHR - Riel';
$GLOBALS['TL_LANG']['CUR']['KMF'] = 'KMF - Comorian Franc';
$GLOBALS['TL_LANG']['CUR']['KPW'] = 'KPW - Democratic People\'s Republic of Korean Won';
$GLOBALS['TL_LANG']['CUR']['KRW'] = 'KRW - Republic of Korean Won';
$GLOBALS['TL_LANG']['CUR']['KWD'] = 'KWD - Kuwaiti Dinar';
$GLOBALS['TL_LANG']['CUR']['KYD'] = 'KYD - Cayman Islands Dollar';
$GLOBALS['TL_LANG']['CUR']['KZT'] = 'KZT - Tenge';
$GLOBALS['TL_LANG']['CUR']['LAK'] = 'LAK - Kip';
$GLOBALS['TL_LANG']['CUR']['LBP'] = 'LBP - Lebanese Pound';
$GLOBALS['TL_LANG']['CUR']['LKR'] = 'LKR - Sri Lankan Rupee';
$GLOBALS['TL_LANG']['CUR']['LRD'] = 'LRD - Liberian Dollar';
$GLOBALS['TL_LANG']['CUR']['LSL'] = 'LSL - Loti';
$GLOBALS['TL_LANG']['CUR']['LSM'] = 'LSM - Maloti';
$GLOBALS['TL_LANG']['CUR']['LTL'] = 'LTL - Litas';
$GLOBALS['TL_LANG']['CUR']['LVL'] = 'LVL - Lats';
$GLOBALS['TL_LANG']['CUR']['LYD'] = 'LYD - Libyan Dinar';
$GLOBALS['TL_LANG']['CUR']['MAD'] = 'MAD - Moroccan Dirham';
$GLOBALS['TL_LANG']['CUR']['MDL'] = 'MDL - Moldavian Leu';
$GLOBALS['TL_LANG']['CUR']['MGA'] = 'MGA - Malagasy ariary';
$GLOBALS['TL_LANG']['CUR']['MKD'] = 'MKD - Macedonian Dinar';
$GLOBALS['TL_LANG']['CUR']['MMK'] = 'MMK - Kyat';
$GLOBALS['TL_LANG']['CUR']['MNT'] = 'MNT - Tugrik';
$GLOBALS['TL_LANG']['CUR']['MOP'] = 'MOP - Pataca';
$GLOBALS['TL_LANG']['CUR']['MRO'] = 'MRO - Ouguiya';
$GLOBALS['TL_LANG']['CUR']['MUR'] = 'MUR - Mauritius Rupee';
$GLOBALS['TL_LANG']['CUR']['MVR'] = 'MVR - Rufiyaa';
$GLOBALS['TL_LANG']['CUR']['MWK'] = 'MWK - Malawian Kwacha';
$GLOBALS['TL_LANG']['CUR']['MYR'] = 'MYR - Ringgit (Malaysian Dollar)';
$GLOBALS['TL_LANG']['CUR']['MZN'] = 'MZN - Mozambican metical';
$GLOBALS['TL_LANG']['CUR']['NAD'] = 'NAD - Namibia Dollar';
$GLOBALS['TL_LANG']['CUR']['NGN'] = 'NGN - Naira';
$GLOBALS['TL_LANG']['CUR']['NIC'] = 'NIC - Córdoba';
$GLOBALS['TL_LANG']['CUR']['NOK'] = 'NOK - Norwegian Krone';
$GLOBALS['TL_LANG']['CUR']['NPR'] = 'NPR - Nepalese Rupee';
$GLOBALS['TL_LANG']['CUR']['NZD'] = 'NZD - New Zealand Dollar';
$GLOBALS['TL_LANG']['CUR']['OMR'] = 'OMR - Omani Rial';
$GLOBALS['TL_LANG']['CUR']['PAB'] = 'PAB - Balboa';
$GLOBALS['TL_LANG']['CUR']['PEN'] = 'PEN - New Sol';
$GLOBALS['TL_LANG']['CUR']['PGK'] = 'PGK - Kina';
$GLOBALS['TL_LANG']['CUR']['PHP'] = 'PHP - Philippines Peso';
$GLOBALS['TL_LANG']['CUR']['PKR'] = 'PKR - Pakistani Rupee';
$GLOBALS['TL_LANG']['CUR']['PLN'] = 'PLN - Polski Złoty';
$GLOBALS['TL_LANG']['CUR']['PYG'] = 'PYG - Guarani';
$GLOBALS['TL_LANG']['CUR']['QAR'] = 'QAR - Qatari Riyal';
$GLOBALS['TL_LANG']['CUR']['RON'] = 'RON - Romanian New Leu';
$GLOBALS['TL_LANG']['CUR']['RSD'] = 'RSD - Serbian Dinar';
$GLOBALS['TL_LANG']['CUR']['RUB'] = 'RUB - Russian Federation Rouble';
$GLOBALS['TL_LANG']['CUR']['RWF'] = 'RWF - Rwandan Franc';
$GLOBALS['TL_LANG']['CUR']['SAR'] = 'SAR - Saudi Riyal';
$GLOBALS['TL_LANG']['CUR']['SBD'] = 'SBD - Solomon Islands Dollar';
$GLOBALS['TL_LANG']['CUR']['SCR'] = 'SCR - Seychelles Rupee';
$GLOBALS['TL_LANG']['CUR']['SDG'] = 'SDG - Sudanese Pound';
$GLOBALS['TL_LANG']['CUR']['SEK'] = 'SEK - Swedish Krona';
$GLOBALS['TL_LANG']['CUR']['SGD'] = 'SGD - Singapore Dollar';
$GLOBALS['TL_LANG']['CUR']['SHP'] = 'SHP - St Helena Pound';
$GLOBALS['TL_LANG']['CUR']['SLL'] = 'SLL - Leone';
$GLOBALS['TL_LANG']['CUR']['SOS'] = 'SOS - Somali Shilling';
$GLOBALS['TL_LANG']['CUR']['SRD'] = 'SRD - Surinamese Dollar';
$GLOBALS['TL_LANG']['CUR']['STD'] = 'STD - Dobra';
$GLOBALS['TL_LANG']['CUR']['SYP'] = 'SYP - Syrian Pound';
$GLOBALS['TL_LANG']['CUR']['SZL'] = 'SZL - Lilangeni';
$GLOBALS['TL_LANG']['CUR']['THB'] = 'THB - Baht';
$GLOBALS['TL_LANG']['CUR']['TJS'] = 'TJS - Tajikistani Somoni';
$GLOBALS['TL_LANG']['CUR']['TMT'] = 'TMT - Turkmenistani Manat';
$GLOBALS['TL_LANG']['CUR']['TND'] = 'TND - Tunisian Dinar';
$GLOBALS['TL_LANG']['CUR']['TOP'] = 'TOP - Pa\'anga';
$GLOBALS['TL_LANG']['CUR']['TPE'] = 'TPE - Timorian Escudo';
$GLOBALS['TL_LANG']['CUR']['TRY'] = 'TRY - New Turkish Lira';
$GLOBALS['TL_LANG']['CUR']['TTD'] = 'TTD - Trinidad and Tobago Dollar';
$GLOBALS['TL_LANG']['CUR']['TWD'] = 'TWD - Taiwan Dollar';
$GLOBALS['TL_LANG']['CUR']['TZS'] = 'TZS - Tanzanian Shilling';
$GLOBALS['TL_LANG']['CUR']['UAH'] = 'UAH - Hryvna';
$GLOBALS['TL_LANG']['CUR']['UGX'] = 'UGX - Ugandan Shilling';
$GLOBALS['TL_LANG']['CUR']['USD'] = 'USD - United States Dollar';
$GLOBALS['TL_LANG']['CUR']['UYU'] = 'UYU - Uruguayan Peso';
$GLOBALS['TL_LANG']['CUR']['UZS'] = 'UZS - Uzbekistani Som';
$GLOBALS['TL_LANG']['CUR']['VEF'] = 'VEF - Bolivar Fuerte';
$GLOBALS['TL_LANG']['CUR']['VND'] = 'VND - Viet Nam Dông';
$GLOBALS['TL_LANG']['CUR']['VUV'] = 'VUV - Vatu';
$GLOBALS['TL_LANG']['CUR']['WST'] = 'WST - Tala';
$GLOBALS['TL_LANG']['CUR']['YER'] = 'YER - Yemeni Riyal';
$GLOBALS['TL_LANG']['CUR']['ZAR'] = 'ZAR - Rand';
$GLOBALS['TL_LANG']['CUR']['ZMK'] = 'ZMK - Zambian Kwacha';
$GLOBALS['TL_LANG']['CUR']['ZWL'] = 'ZWL - Zimbabwe Dollar';


/**
 * Currency symbols
 */
$GLOBALS['TL_LANG']['CUR_SYMBOL']['USD'] = '$';
$GLOBALS['TL_LANG']['CUR_SYMBOL']['EUR'] = '€';
$GLOBALS['TL_LANG']['CUR_SYMBOL']['GBP'] = '£';
$GLOBALS['TL_LANG']['CUR_SYMBOL']['JPY'] = '¥';
$GLOBALS['TL_LANG']['CUR_SYMBOL']['PLN'] = ' zł';
