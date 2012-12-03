<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2012 Isotope eCommerce Workgroup
 *
 * @package    Isotope
 * @link       http://www.isotopeecommerce.com
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 *
 * @author     Andreas Burg <ab@andreasburg.de>
 * @author     Nikolas Runde <info@nrmedia.de>
 * @author     Patrick Grob <grob@a-sign.ch>
 * @author     Frank Berger <berger@mediastuff.de>
 * @author     Oliver Hoff <oliver@hoff.com>
 * @author     Stefan Preiss <stefan@preiss-at-work.de>
 * @author     Nina Gerling <gerling@ena-webstudio.de>
 */

$GLOBALS['TL_LANG']['tl_iso_payment_modules']['type'][0] = 'Zahlungsmodul';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['type'][1] = 'Wählen Sie eine der unterstützen Zahlungsmethoden.';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['name'][0] = 'Name';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['name'][1] = 'Geben Sie einen Namen für dieses Modul ein.';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['label'][0] = 'Bezeichnung';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['label'][1] = 'Dieser Text wird dem Kunden bei der Bestellung angezeigt.';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['note'][0] = 'Hinweise';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['note'][1] = 'Die Hinweise können im Bestätigungs-Mail mitgesendet werden (##payment_note##).';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['price'][0] = 'Preis';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['tax_class'][0] = 'Steuerklasse';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['new_order_status'][0] = 'Status für neue Bestellungen';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['new_order_status'][1] = 'Wählen Sie einen zutreffenden Status für neue Bestellungen im System.';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['minimum_total'][0] = 'Minimaler Betrag';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['minimum_total'][1] = 'Geben Sie ein Zahl grösser als 0 ein, wenn diese Zahlungsart erst ab einem gewissen Totalbetrag zur Verfügung steht.';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['maximum_total'][0] = 'Maximaler Betrag';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['maximum_total'][1] = 'Geben Sie ein Zahl grösser als 0 ein, wenn diese Zahlungsart nur bis einem gewissen Totalbetrag zur Verfügung steht.';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['countries'][0] = 'Aktive Länder';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['countries'][1] = 'Falls diese Zahlungsart nur in gewissen Ländern unterstütz wird (Rechnungsadresse des Kunden).';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['shipping_modules'][0] = 'Versandarten';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['shipping_modules'][1] = 'Falls diese Zahlungsart nur für bestimmte Versandarten unterstützt wird (z.B. Barzahlung nur bei Abholung).';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['product_types'][0] = 'Produkttypen';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['product_types'][1] = 'Sie können diese Bezahlmethode auf bestimmte Produkttypen einschränken. Wenn der Warenkorb einen Produkttypen enthält, den Sie nicht gewählt haben, ist die Bezahlmethode nicht verfügbar.';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['trans_type'][0] = 'Transaktions-Typ';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['trans_type'][1] = 'Wählen Sie ob Sie das Geld sofort einnehmen oder für eine spätere Transaktion (z. B. wenn versandt wird) authorisieren (und abwarten) wollen.';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['paypal_account'][0] = 'PayPal-Konto';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['paypal_account'][1] = 'Die E-Mail Adresse des PayPal Kontos, auf welches die Zahlung empfangen werden soll.';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['payflowpro_user'][0] = 'Paypal Payflow Pro Nutzername';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['payflowpro_vendor'][0] = 'Paypal Payflow Pro Händler';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['payflowpro_vendor'][1] = 'Ein alphanumerischer String mit ca. 10 Zeichen';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['payflowpro_partner'][0] = 'Paypal Payflow Pro Partner';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['payflowpro_partner'][1] = 'Groß-/Kleinschreibung beachten! Normalerweise sind Partner IDs entweder "PayPal" oder "PayPalUK".';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['payflowpro_password'][0] = 'Paypal Payflow Pro API-Passwort';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['payflowpro_password'][1] = 'Ein alphanumerischer String mit ca. 11 Zeichen';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['payflowpro_transType'][0] = 'Paypal Payflow Pro Transaktionstyp';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['payflowpro_transType'][1] = 'Bitte wählen Sie einen Transaktionstypen.';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['postfinance_pspid'][0] = 'Postfinance PSPID';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['postfinance_pspid'][1] = 'Die PSPID ist Ihr eindeutiger Erkennungsname im Postfinance-System.';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['postfinance_secret'][0] = 'Postfinance Geheimschlüssel';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['postfinance_secret'][1] = 'Diese Daten werden zur Prüfung der Zahlungsübergabe verwendet (Punkt 3.2 in den technischen Einstellungen).';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['postfinance_method'][0] = 'Postfinance Methode';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['postfinance_method'][1] = 'Art der Datenübermittlung seitens Postfinance (Punkt 1.1 in den technischen Einstellungen).';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['epay_merchantnumber'][0] = 'Händlernummer';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['epay_merchantnumber'][1] = 'Die einzigartige Händlernummer wird in ePay erstellt. Diese Händlernummer finden Sie in Ihren PBS-Vertrag.';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['epay_secretkey'][0] = 'Geheimer Schlüssel';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['epay_secretkey'][1] = 'Der geheime Schlüssel (secret key) wird in Ihren ePay Konfigurationen festgelegt.';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['button'][0] = 'Bestellen-Button';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['button'][1] = 'Wählen Sie ein Bild falls Sie einen eigenen Bestellen-Button darstellen möchten.';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['requireCCV'][0] = 'Benötigt den Prüfcode (Card Code Verification - CCV)';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['requireCCV'][1] = 'Wählen Sie diese Option wenn sie die Transaktionssicherheit durch die Abfrage des Prüfcodes erhöhen möchten.';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['cybersource_merchant_id'][0] = 'Cybersource Händler-ID';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['cybersource_merchant_id'][1] = 'Geben Sie Ihre Cybersource Händler-ID ein.';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['cybersource_trans_key'][0] = 'Cybersource Transaktions-Schlüssel';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['cybersource_trans_key'][1] = 'Sie erhalten den Cybersource Transaktions-Schlüssel (transaction key) wenn Sie die Anmeldung für die Schnittstelle (Gateway) abgeschlossen haben.';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['cybersource_trans_type'][0] = 'Cybersource Transaktions-Typ';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['cybersource_trans_type'][1] = 'Authorisieren und Einnehmen, zum Beispiel - der erste Schritt ist die Authorisierung bei der die durch den Kunden eingegebenen Daten überprüft werden. Im zweiten Schritt werden sie zur Abwicklung übertragen, was man als "capture" (Einnehmen) bezeichnet.';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['datatrans_id'] = array('Merchant-ID', 'Bitte geben Sie ihre Datatrans Merchant-ID ein.');
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['datatrans_sign']	= array('HMAC-Schlüssel', 'Bitte geben Sie den HMAC-Schlüssel aus Ihrem Datatrans Control Panel ein.');
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['sparkasse_paymentmethod'] = array('Zahlungsart', 'Bitte wählen Sie die Zahlungsart für dieses Modul.');
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['sparkasse_sslmerchant'] = array('Händlerkennung', 'Bitte geben Sie Ihre Händlerkennung ein.');
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['sparkasse_sslpassword'] = array('Passwort', 'Bitte geben Sie Ihre SSL-Passwort ein.');
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['sparkasse_merchantref'] = array('Händler-Referenz', 'Transaktionsreferenz, die auf der Kreditkartenabrechnung des Händlers statt der Warenkorbnummer angezeigt wird.');
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['allowed_cc_types'][0] = 'Erlaubte Kreditkarten-Typen';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['allowed_cc_types'][1] = 'Wählen Sie welche Kreditkarten die Bezahlmethode akzeptiert.';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['authorize_login'][0] = 'Authorize.net Login';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['authorize_login'][1] = 'Wird bereitgestellt wenn Sie die Anmeldung für die Schnittstelle (Gateway) abgeschlossen haben.';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['authorize_trans_key'][0] = 'Authorize.net Transaktions-Schlüssel';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['authorize_trans_key'][1] = 'Der Transaktions-Schlüssel (transaction key) wird nach der erfolgreichen Anmeldung für die Schnittstelle (Gateway) bereitgestellt.';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['authorize_delimiter'][0] = 'Authorize.net Trennzeichen';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['authorize_delimiter'][1] = 'Welchens Zeichen soll als Trennzeichen für die Antwort eingefügt werden?';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['authorize_trans_type'][0] = 'Authorize.net Transaktionstyp';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['authorize_trans_type'][1] = 'Authorisieren und Einnehmen, zum Beispiel - der erste Schritt ist die Authorisierung bei der die durch den Kunden eingegebenen Daten überprüft werden. Im zweiten Schritt werden sie zur Abwicklung übertragen, was man als "capture" (Einnehmen) bezeichnet.';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['groups'][0] = 'Mitgliedergruppen';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['groups'][1] = 'Diese Bezahlmethode auf bestimmte Mitgliedergruppen beschränken.';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['protected'][0] = 'Modul schützen';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['protected'][1] = 'Diese Bezahlmethode nur bestimmten Mitgliedergruppen anzeigen.';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['guests'][0] = 'Nur Gästen zeigen';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['guests'][1] = 'Diese Bezahlmethode nicht für eingeloggte Mitglieder anzeigen.';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['debug'][0] = 'Testsystem verwenden';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['debug'][1] = 'Aktivieren Sie diese Option um ein Testsystem zu verwenden, auf dem keine echten Zahlungen ausgeführt werden.';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['enabled'][0] = 'Modul aktivieren';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['enabled'][1] = 'Klicken Sie hier wenn dieses Modul für Besucher sichtbar sein soll.';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['capture'] = 'Authorisieren und Einnehmen';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['auth'] = 'Nur Authorisieren';
$GLOBALS['TL_LANG']['ISO_PAY']['authorizedotnet']['modes']['AUTH_CAPTURE'] = 'Authorisieren und Einnehmen';
$GLOBALS['TL_LANG']['ISO_PAY']['authorizedotnet']['modes']['AUTH_ONLY'] = 'Nur Authorisieren';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['no_shipping'] = 'Bestellungen ohne Versand';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['AUTH_CAPTURE'][0] = 'Authorisieren und Einnehmen';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['AUTH_CAPTURE'][1] = 'Transaktionen dieses Typs werden zur Authorisierung weitergeleitet. Die Transaktion wird nach der Freigabe automatisch zur Abwicklung übertragen. Dies ist der Standard-Transaktionstyp dieser Schnittstelle (Gateway). Wenn bei der Übertragung der Transaktion an die Schnittstelle kein Typ vorgegeben ist,  nimmt die Schnittstelle an, dass die Transaktion diesem Typ entspricht.';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['AUTH_ONLY'][0] = 'Nur Authorisieren';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['AUTH_ONLY'][1] = 'Transaktionen dieses Typs werden übertragen, wenn der Händler die Kreditkarte auf die Menge der verkauften Waren prüfen lassen möchte. Wenn der Händler nicht genügend Waren im Lager hat oder die Bestellungen vor der Warenlieferung prüfen möchte, wird dieser Transaktiontyps übertragen. Die Schnittstelle (Gateway) wird diesen Transaktionstyp für die Bestätigung zum Finanzinstitut senden. Die Transaktion wird aber nicht zur Abwicklung übertragen. Wenn der Händler nicht binnen 30 Tagen auf die Transaktion reagiert, steht die Transaktion nicht mehr zum Einnehmen zur Verfügung.';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['CAPTURE_ONLY'][0] = 'Nur Einnehmen';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['CAPTURE_ONLY'][1] = 'Dies ist eine Anfrage für das Abwickeln einer Transaktion die nicht zur Authorisierung durch die Bezahlschnittstelle übergeben wurde. Die Schnittstelle (Gateway) wird diese Transaktion akzeptieren, wenn ein Authorisierungscode mit übertragen wird. x_auth_code ist ein Pflichtfeld für Transaktionen des Typs "Nur Einnehmen".';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['CREDIT'][0] = 'Guthaben';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['CREDIT'][1] = 'Diese Transaktion wird auch als "Rückzahlung" bezeichnet und gibt der Schnittstelle an, dass Geld vom Händler zum Kunden fließen soll. Die Schnittstelle wird eine Kreditkarte oder eine Rückzahlungsanfrage akzeptieren, wenn die übertragene Transaktionen die folgenden Vorgaben erfüllt:<ul><li>Die Transaktion wird mit der ID der ursprünglichen Transaktion übertragen, für die die Kreditkarte genutzt wurde.</li><li>Die Schnittstelle (Gateway) besitzt eine Aufnahme der ursprünglichen Transaktion.</li><li>Die Summe der Menge die mit der Guthaben-Transaktion übertragen wurde und aller Guthaben die mit der ursprünglichen Transaktion erfolgten, ist geriner als der Gesamtwert der ursprünglichen Transaktion.</li><li>Der komplette Nummernsatz oder die letzten vier Stellen der Kreditkartennummer die mit der Guthaben-Transaktion übertragen werden, stimmen mit dem kompletten Nummernsatz oder den letzten vier Stellen überein, die bei der ursprünglichen Transaktion für die Kreditkarte übertragen wurden.</li><li>Die Transaktion wurde binnen 120 Tagen des Abwicklungszeitpunktes der ursprünglichen Transaktion übertragen.</li></ul>Es wird ein Transaktions-Schlüssel benötigt um ein Guthaben an das System zu übertragen.';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['VOID'][0] = 'Fehlbuchung';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['VOID'][1] = 'Diese Transaktion ist eine Aktion auf einer vorhergehenden Transaktion und wird genutzt um diese vorhergehende Transaktion zu annullieren um sicher zu stellen, dass sie nicht zur Abwicklung gesendet wird. Sie kan auf jeden Transaktionstyp angewandt werden (z. B. CREDIT, AUTH_CAPTURE, CAPTURE_ONLY und AUTH_ONLY). Die Transaktion wird von der Schnittstelle (Gateway) akzeptiert, wenn die folgenden Vorgaben erfüllt werden: <ul><li>Die Transaktion wird mit der ID der Transaktion übertragen, die annulliert werden soll.</li><li>Die Schnittstelle besitzt eine Aufnahme der Transaktion mit dieser ID.</li><li>Die Transaktion wurde noch nicht zur Abwicklung weitergesendet.</li></ul>';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['PRIOR_AUTH_CAPTURE'][0] = 'Vorrangiges Autorisieren und Einnehmen';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['PRIOR_AUTH_CAPTURE'][1] = 'Diese Transaktion wird benutzt um die Abwicklung für eine Transaktion anzufordern, die zuvor als Transaktionstyp "Nur autorisieren" (AUTH_ONLY) übertragen werden. Die Schnittestelle (Gateway) wird diese  Transaktion akzeptieren und die Abwicklung starten, wenn die folgenden Vorgaben erfüllt sind:<ul><li>Die Transaktion wird mit der ID der ursprünglichen "Nur Autorisieren"-Transaktion übertragen.</li><li>Die Transaktions-ID ist gültig und das System hat eine Aufnahme der ursprünglich übertragenen "Nur Autorisieren"-Transaktion vorliegen.</li><li>Die ursprüngliche Transaktion ist noch nicht abgewickelt, abgelaufen oder fehlerhaft.</li><li>Die angefragte Menge füe die Abwicklung dieser Transaktion ist geringer oder identisch mit dem ursprünglich autorisierten Betrag.</li></ul>Wenn in dieser Transaktion kein Wert mit übertragen wurde, führt die Schnittstelle die Abwicklung für diese Transaktion auf Basis der Menge der ursprünglich autorisierten Transaktion durch. <em>Hinweis: Wenn mit der ursprünglichen Transaktion Informationen wie Extended Line Item, Steuer, Frachtgebühren oder Zolldaten übertragen wurden, werden angepasste Informationen übertragen falls sich die Transaktionssumme geändert hat. Wenn keine derartigen zusätzlichen Informationen übertragen wurden, sind die Informationen der ursprünglichen Transaktion gültig.</em>';
$GLOBALS['TL_LANG']['tl_payment_module']['payflowpro_transTypes']['Sale'] = 'Autorisieren und Einnehmen';
$GLOBALS['TL_LANG']['tl_payment_module']['payflowpro_transTypes']['Authorization'] = 'Nur Autorisation';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['sparkasse_paymentmethod']['creditcard'] = 'Kreditkarte';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['sparkasse_paymentmethod']['maestro'] = 'Maestro-Karte';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['sparkasse_paymentmethod']['directdebit'] = 'Lastschrift';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['type_legend'] = 'Name & Typ';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['note_legend'] = 'Bestellhinweis';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['config_legend'] = 'Allgemeine Einstellungen';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['gateway_legend'] = 'Konfiguration des Zahlungsanbieters';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['price_legend'] = 'Preis';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['template_legend'] = 'Template';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['expert_legend'] = 'Experteneinstellungen';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['enabled_legend'] = 'Aktivierte Einstellungen';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['new'][0] = 'Neue Zahlungsart';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['new'][1] = 'Erstellen Sie eine neue Zahlungsart';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['edit'][0] = 'Zahlungsart bearbeiten';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['edit'][1] = 'Zahlungsart ID %s bearbeiten';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['copy'][0] = 'Zahlungsart kopieren';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['copy'][1] = 'Zahlungsart ID %s kopieren';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['delete'][0] = 'Zahlungsart löschen';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['delete'][1] = 'Zahlungsart ID %s löschen';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['show'][0] = 'Zahlungsart-Details';
$GLOBALS['TL_LANG']['tl_iso_payment_modules']['show'][1] = 'Details der Zahlungsart ID %s anzeigen';

