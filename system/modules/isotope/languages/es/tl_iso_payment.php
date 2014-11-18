<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 * 
 * Copyright (C) 2009-2014 terminal42 gmbh & Isotope eCommerce Workgroup
 * 
 * Translations are managed using Transifex. To create a new translation
 * or to help to maintain an existing one, please register at transifex.com.
 * 
 * @link http://help.transifex.com/intro/translating.html
 * @link https://www.transifex.com/projects/i/isotope/language/es/
 * 
 * @license http://www.gnu.org/licenses/lgpl-3.0.html
 */

$GLOBALS['TL_LANG']['tl_iso_payment']['name'][0] = 'Nombre de la forma de pago';
$GLOBALS['TL_LANG']['tl_iso_payment']['name'][1] = 'Introduzca un nombre para este método de pago. Esto sólo se se utilizará en el backend.';
$GLOBALS['TL_LANG']['tl_iso_payment']['label'][0] = 'Etiqueta para la forma de pago';
$GLOBALS['TL_LANG']['tl_iso_payment']['label'][1] = 'La etiqueta se mostrará al cliente durante el proceso de pago';
$GLOBALS['TL_LANG']['tl_iso_payment']['type'][0] = 'Tipo de pasarela de pago';
$GLOBALS['TL_LANG']['tl_iso_payment']['note'][0] = 'Nota de pago';
$GLOBALS['TL_LANG']['tl_iso_payment']['note'][1] = 'Esta nota será enviada en el correo de confirmación (##payment_note##)';
$GLOBALS['TL_LANG']['tl_iso_payment']['new_order_status'][0] = 'Estado para nuevos pedidos';
$GLOBALS['TL_LANG']['tl_iso_payment']['new_order_status'][1] = 'Elegir un estado para los nuevos pedidos';
$GLOBALS['TL_LANG']['tl_iso_payment']['minimum_total'][0] = 'Total mínimo';
$GLOBALS['TL_LANG']['tl_iso_payment']['minimum_total'][1] = 'Introducir un número superior a cero para excluir este método de pago a los pedidos de menor precio.';
$GLOBALS['TL_LANG']['tl_iso_payment']['maximum_total'][0] = 'Total máximo';
$GLOBALS['TL_LANG']['tl_iso_payment']['maximum_total'][1] = 'Introducir un número superior a cero para excluir este método de pago a los pedidos de mayor precio.';
$GLOBALS['TL_LANG']['tl_iso_payment']['countries'][0] = 'Países disponibles';
$GLOBALS['TL_LANG']['tl_iso_payment']['countries'][1] = 'Seleccione los países donde esta forma de pago se puede utilizar (la dirección de facturación del cliente).';
$GLOBALS['TL_LANG']['tl_iso_payment']['shipping_modules'][0] = 'Métodos de envío';
$GLOBALS['TL_LANG']['tl_iso_payment']['shipping_modules'][1] = 'Para limitar este método de pago a determinados métodos de envío (por ejemplo, sólo en efectivo al recoger).';
$GLOBALS['TL_LANG']['tl_iso_payment']['product_types'][0] = 'Tipos de productos';
$GLOBALS['TL_LANG']['tl_iso_payment']['product_types'][1] = 'Para limitar este método de pago a determinados tipos de productos. Si la cesta tiene un tipo de producto que no ha seleccionado, el método de pago no está disponible.';
$GLOBALS['TL_LANG']['tl_iso_payment']['config_ids'][0] = 'Configuración de la tienda';
$GLOBALS['TL_LANG']['tl_iso_payment']['price'][0] = 'Precio';
$GLOBALS['TL_LANG']['tl_iso_payment']['tax_class'][0] = 'Clase de Impuesto';
$GLOBALS['TL_LANG']['tl_iso_payment']['trans_type'][0] = 'Tipo de transacción';
$GLOBALS['TL_LANG']['tl_iso_payment']['trans_type'][1] = 'Seleccione si desea cobrar instantáneamente el dinero o autorizar (y mantener) en una transacción posterior (por ejemplo, cuando el envío).';
$GLOBALS['TL_LANG']['tl_iso_payment']['paypal_account'][0] = 'Cuenta PayPal';
$GLOBALS['TL_LANG']['tl_iso_payment']['payflowpro_user'][0] = 'Nombre de usuario Paypal Payflow Pro';
$GLOBALS['TL_LANG']['tl_iso_payment']['payflowpro_vendor'][0] = 'Vendedor Paypal Payflow Pro';
$GLOBALS['TL_LANG']['tl_iso_payment']['payflowpro_vendor'][1] = 'Cadena alfanumérica de unos 10 caracteres.';
$GLOBALS['TL_LANG']['tl_iso_payment']['payflowpro_partner'][0] = 'Socio Paypal Payflow Pro';
$GLOBALS['TL_LANG']['tl_iso_payment']['payflowpro_partner'][1] = 'Distinguir mayúsculas de minúsculas! Ids de socios habituales son o "PayPal" o "PayPalUK".';
$GLOBALS['TL_LANG']['tl_iso_payment']['payflowpro_password'][0] = 'Contraseña Paypal Payflow Pro api';
$GLOBALS['TL_LANG']['tl_iso_payment']['payflowpro_password'][1] = 'Cadena alfanumérica de unos 11 caracteres';
$GLOBALS['TL_LANG']['tl_iso_payment']['payflowpro_transType'][0] = 'Tipo de transacción Paypal Payflow Pro';
$GLOBALS['TL_LANG']['tl_iso_payment']['payflowpro_transType'][1] = 'Por favor selecciona un tipo de transacción.';
$GLOBALS['TL_LANG']['tl_iso_payment']['requireCCV'][0] = 'Necesita una verificación del número de código de la tarjeta (CCV)';
$GLOBALS['TL_LANG']['tl_iso_payment']['requireCCV'][1] = 'Elija esta opción si desea aumentar la seguridad de las transacciones al exigir el código de verificación de la tarjeta.';
$GLOBALS['TL_LANG']['tl_iso_payment']['allowed_cc_types'][0] = 'Tipos de tarjetas de crédito admitidos';
$GLOBALS['TL_LANG']['tl_iso_payment']['allowed_cc_types'][1] = 'Seleccione que tarjetas de crédito el método de pago acepta.';
$GLOBALS['TL_LANG']['tl_iso_payment']['datatrans_id'][0] = 'ID de comerciante';
$GLOBALS['TL_LANG']['tl_iso_payment']['datatrans_id'][1] = 'Introducir su ID de comerciante.';
$GLOBALS['TL_LANG']['tl_iso_payment']['datatrans_sign'][0] = 'clave HMAC';
$GLOBALS['TL_LANG']['tl_iso_payment']['datatrans_sign'][1] = 'Introducir su clave HMAC del panel de control Datatrans.';
$GLOBALS['TL_LANG']['tl_iso_payment']['sparkasse_paymentmethod'][0] = 'Forma de pago';
$GLOBALS['TL_LANG']['tl_iso_payment']['sparkasse_paymentmethod'][1] = 'Por favor, seleccione una forma de pago por este método.';
$GLOBALS['TL_LANG']['tl_iso_payment']['sparkasse_paymentmethod']['creditcard'] = 'Tarjeta de credito';
$GLOBALS['TL_LANG']['tl_iso_payment']['sparkasse_paymentmethod']['maestro'] = 'Tarjeta de debito';
$GLOBALS['TL_LANG']['tl_iso_payment']['sparkasse_paymentmethod']['directdebit'] = 'Direct debit';
$GLOBALS['TL_LANG']['tl_iso_payment']['sparkasse_sslmerchant'][0] = 'ID Vendedor';
$GLOBALS['TL_LANG']['tl_iso_payment']['sparkasse_sslmerchant'][1] = 'Por favor, introduzca su ID de Proveedor (Händlerkennung).';
$GLOBALS['TL_LANG']['tl_iso_payment']['sparkasse_sslpassword'][0] = 'Contraseña';
$GLOBALS['TL_LANG']['tl_iso_payment']['sparkasse_sslpassword'][1] = 'Introducir su SSL-Password.';
$GLOBALS['TL_LANG']['tl_iso_payment']['sparkasse_merchantref'][0] = 'Referencia';
$GLOBALS['TL_LANG']['tl_iso_payment']['sparkasse_merchantref'][1] = 'Una referencia que se mostrará en la página de detalles del vendedor en lugar del ID de la cesta.';
$GLOBALS['TL_LANG']['tl_iso_payment']['expercash_popupId'][0] = 'ExperCash Popup-ID';
$GLOBALS['TL_LANG']['tl_iso_payment']['expercash_popupId'][1] = 'Introducir el Popup ID de tu portal ExperCash.';
$GLOBALS['TL_LANG']['tl_iso_payment']['expercash_profile'][0] = 'Perfil ExperCash';
$GLOBALS['TL_LANG']['tl_iso_payment']['expercash_profile'][1] = 'Introducir el número de perfil de tres dígitos.';
$GLOBALS['TL_LANG']['tl_iso_payment']['expercash_popupKey'][0] = 'ExperCash Popup-Key';
$GLOBALS['TL_LANG']['tl_iso_payment']['expercash_popupKey'][1] = 'Introducir la clave de pop-up de su portal ExperCash.';
$GLOBALS['TL_LANG']['tl_iso_payment']['expercash_paymentMethod'][0] = 'Transacción';
$GLOBALS['TL_LANG']['tl_iso_payment']['expercash_paymentMethod'][1] = 'Se puede predefinir una transacción o el cliente puede elegir.';
$GLOBALS['TL_LANG']['tl_iso_payment']['expercash_paymentMethod']['automatic_payment_method'] = 'La selección del método de pago por el usuario final';
$GLOBALS['TL_LANG']['tl_iso_payment']['expercash_paymentMethod']['elv_buy'] = 'Pago con Débito Directo (ELV)';
$GLOBALS['TL_LANG']['tl_iso_payment']['expercash_paymentMethod']['cc_buy'] = 'Pago con tarjeta de crédito';
$GLOBALS['TL_LANG']['tl_iso_payment']['expercash_paymentMethod']['cc_authorize'] = 'Reserva obligatoria de una tarjeta de crédito para cobro futuro';
$GLOBALS['TL_LANG']['tl_iso_payment']['expercash_paymentMethod']['sofortueberweisung'] = 'Transacción disponible a través Sofortuberweisung';
$GLOBALS['TL_LANG']['tl_iso_payment']['epay_secretkey'][0] = 'Clave secreta';
$GLOBALS['TL_LANG']['tl_iso_payment']['payone_portalid'][1] = 'Introducir su ID del portal PayOne.';
$GLOBALS['TL_LANG']['tl_iso_payment']['payone_key'][0] = 'Clave secreta';
$GLOBALS['TL_LANG']['tl_iso_payment']['payone_key'][1] = 'Ingrese la clave secreta que ha especificado para este portal.';
$GLOBALS['TL_LANG']['tl_iso_payment']['worldpay_description'][0] = 'Descripción';
$GLOBALS['TL_LANG']['tl_iso_payment']['groups'][0] = 'Grupos de miembros';
$GLOBALS['TL_LANG']['tl_iso_payment']['groups'][1] = 'Restringir esta forma de pago a determinados grupos de miembros.';
$GLOBALS['TL_LANG']['tl_iso_payment']['protected'][0] = 'Proteger el método de pago';
$GLOBALS['TL_LANG']['tl_iso_payment']['protected'][1] = 'Mostrar la forma de pago a sólo ciertos grupos de miembros.';
$GLOBALS['TL_LANG']['tl_iso_payment']['guests'][0] = 'Mostrar solo a los invitados.';
$GLOBALS['TL_LANG']['tl_iso_payment']['guests'][1] = 'Ocultar el método de pago, si un miembro se ha autentificado.';
$GLOBALS['TL_LANG']['tl_iso_payment']['debug'][0] = 'Modo Debug';
$GLOBALS['TL_LANG']['tl_iso_payment']['debug'][1] = 'Para las pruebas sin realmente cobrar para el pago.';
$GLOBALS['TL_LANG']['tl_iso_payment']['enabled'][0] = 'Activado';
$GLOBALS['TL_LANG']['tl_iso_payment']['enabled'][1] = 'Marque aquí si la forma de pago debe estar habilitada en la tienda.';
$GLOBALS['TL_LANG']['tl_iso_payment']['new'][0] = 'Nueva forma de pago';
$GLOBALS['TL_LANG']['tl_iso_payment']['edit'][0] = 'Editar forma de pago';
$GLOBALS['TL_LANG']['tl_iso_payment']['edit'][1] = 'Editar forma de pago ID %S';
$GLOBALS['TL_LANG']['tl_iso_payment']['copy'][0] = 'Copiar forma de pago';
$GLOBALS['TL_LANG']['tl_iso_payment']['copy'][1] = 'Copiar forma de pago ID %s';
$GLOBALS['TL_LANG']['tl_iso_payment']['delete'][0] = 'Borrar forma de pago';
$GLOBALS['TL_LANG']['tl_iso_payment']['delete'][1] = 'Borrar forma de pago ID %s';
$GLOBALS['TL_LANG']['tl_iso_payment']['show'][0] = 'Detalles forma de pago';
$GLOBALS['TL_LANG']['tl_iso_payment']['show'][1] = 'Mostrar los detalles de la forma de pago';
$GLOBALS['TL_LANG']['tl_iso_payment']['capture'][0] = 'Autorización y captura';
$GLOBALS['TL_LANG']['tl_iso_payment']['capture'][1] = 'Las transacciones de este tipo serán enviados para su autorización. La transacción será automáticamente recogido para su liquidación si se aprueba.';
$GLOBALS['TL_LANG']['tl_iso_payment']['auth'][0] = 'Autorizar Sólo';
$GLOBALS['TL_LANG']['tl_iso_payment']['auth'][1] = 'Las operaciones de este tipo son enviadas si el comerciante desea validar la tarjeta de crédito por el importe de los bienes vendidos. Si el comerciante no tiene las mercancías almacenadas, o si desea revisar las órdenes antes de enviar las mercancías, este tipo de transacción debe ser presentado.';
$GLOBALS['TL_LANG']['tl_iso_payment']['no_shipping'] = 'Pedidos sin envio';
$GLOBALS['TL_LANG']['tl_iso_payment']['payone']['elv'] = 'Retirada de débito';
$GLOBALS['TL_LANG']['tl_iso_payment']['payone']['cc'] = 'Tarjeta de credito';
$GLOBALS['TL_LANG']['tl_iso_payment']['payone']['dc'] = 'Tarjeta de debito';
$GLOBALS['TL_LANG']['tl_iso_payment']['payone']['vor'] = 'Pago por adelantado';
$GLOBALS['TL_LANG']['tl_iso_payment']['payone']['rec'] = 'Factura';
$GLOBALS['TL_LANG']['tl_iso_payment']['payone']['sb'] = 'Transferencia bancaria en línea';
$GLOBALS['TL_LANG']['tl_iso_payment']['payone']['wlt'] = 'e-Wallet';
$GLOBALS['TL_LANG']['tl_iso_payment']['type_legend'] = 'Nombre y tipo';
$GLOBALS['TL_LANG']['tl_iso_payment']['note_legend'] = 'Notas adicionales';
$GLOBALS['TL_LANG']['tl_iso_payment']['config_legend'] = 'Configuración general';
$GLOBALS['TL_LANG']['tl_iso_payment']['gateway_legend'] = 'Configuración de la pasarela de pago';
$GLOBALS['TL_LANG']['tl_iso_payment']['price_legend'] = 'Precio';
$GLOBALS['TL_LANG']['tl_iso_payment']['template_legend'] = 'Plantilla';
$GLOBALS['TL_LANG']['tl_iso_payment']['expert_legend'] = 'Configuración avanzada';
