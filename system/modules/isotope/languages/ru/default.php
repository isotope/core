<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');

/**
 * Contao Open Source CMS
 * Copyright (C) 2005-2010 Leo Feyer
 *
 * Formerly known as TYPOlight Open Source CMS.
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
 * @copyright  2009-2011 Isotope eCommerce Workgroup
 * @author     D S <dreel@bk.ru>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 * @version    $Id$
 */

$GLOBALS['TL_LANG']['ERR']['systemColumn'] = 'Имя `%s` зарезервировано системой. Пожалуйста, выберите другое имя.';
$GLOBALS['TL_LANG']['ERR']['missingButtonTemplate'] = 'Вы должны указать шаблон для кнопки"%s".';
$GLOBALS['TL_LANG']['ERR']['order_conditions'] = 'Для продолжения Вы должны принять условия соглашения';
$GLOBALS['TL_LANG']['ERR']['noStoreConfigurationSet'] = 'Конфигурация магазина недоступна';
$GLOBALS['TL_LANG']['ERR']['noDefaultStoreConfiguration'] = 'Пожалуйста, создайте конфигурацию магазина по-умолчанию.';
$GLOBALS['TL_LANG']['ERR']['productNameMissing'] = '<на найдено название товара>';
$GLOBALS['TL_LANG']['ERR']['noSubProducts'] = 'сопровождающих товаров не найдено';
$GLOBALS['TL_LANG']['ERR']['emptyOrderHistory'] = 'Вы не разместили ещё ни одного заказа.';
$GLOBALS['TL_LANG']['ERR']['orderNotFound'] = 'Запрошенный заказ не найден.';
$GLOBALS['TL_LANG']['ERR']['missingCurrencyFormat'] = 'Формат записи валюты не найден';
$GLOBALS['TL_LANG']['ERR']['searchNotEnabled'] = 'Поиск отключен!';
$GLOBALS['TL_LANG']['ERR']['isoLoginRequired'] = 'Вы должны войти в систему для оформления заказа.';
$GLOBALS['TL_LANG']['ERR']['mandatoryOption'] = 'Выберите опции.';
$GLOBALS['TL_LANG']['ERR']['noAddressData'] = 'Ваш адрес необходим для расчета налогов!';
$GLOBALS['TL_LANG']['ERR']['variantDuplicate'] = "Вариант с этими атрибутами уже есть.\nПожалуйста выберите другую комбинцию.";
$GLOBALS['TL_LANG']['ERR']['breadcrumbEmpty'] = 'Категория фильтрации очищена, теперь все товары отображаются.';
$GLOBALS['TL_LANG']['ERR']['discount'] = 'Пожалуйста введите целые или десятичные со знаком + или - числа, или со знаком процента.';
$GLOBALS['TL_LANG']['ERR']['surcharge'] = 'Пожалуйста введите целые или десятичные числа, или со знаком процента.';
$GLOBALS['TL_LANG']['ERR']['orderFailed'] = 'Оформление заказа не удалось. Попробуйте еще раз позднее или выберите другой метод оплаты.';
$GLOBALS['TL_LANG']['ERR']['specifyBillingAddress'] = 'Адрес для выставления счета не найден. Укажите его.';
$GLOBALS['TL_LANG']['ERR']['cc_num'] = 'Пожалуйста введите верный номер банковской карты.';
$GLOBALS['TL_LANG']['ERR']['cc_type'] = 'Пожалуйста выберите тип банковской карты.';
$GLOBALS['TL_LANG']['ERR']['cc_exp'] = 'Пожалуйста введите дату истечения вашей карты в формате мм/гг.';
$GLOBALS['TL_LANG']['ERR']['cc_ccv'] = 'Пожалуйста введите код подтверждения (3 или 4 цифры на лицевой или обратной стороне карты).';
$GLOBALS['TL_LANG']['ERR']['cc_match'] = 'Номер вашей карты не соответсвует выбранному типу карты.';
$GLOBALS['TL_LANG']['ERR']['addressDoesNotExist'] = 'Этот адрес не существует в вашей адересной книге.';
$GLOBALS['TL_LANG']['ERR']['noAddressBookEntries'] = 'Ваша адресная книга пуста.';
$GLOBALS['TL_LANG']['ERR']['cartMinSubtotal'] = 'Минимальная сумма заказа %s. Пожалуйста добавьте еще товаров перед оформлением заказа.';
$GLOBALS['TL_LANG']['MSC']['editLanguage'] = 'Изменить';
$GLOBALS['TL_LANG']['MSC']['deleteLanguage'] = 'Удалить';
$GLOBALS['TL_LANG']['MSC']['defaultLanguage'] = 'Запас';
$GLOBALS['TL_LANG']['MSC']['editingLanguage'] = 'Внимание! Вы изменяете специфичные для языка данные!';
$GLOBALS['TL_LANG']['MSC']['deleteLanguageConfirm'] = 'Вы уверены, что хотите удалить этот язык? Отмена не возможно!';
$GLOBALS['TL_LANG']['MSC']['undefinedLanguage'] = 'не определено';
$GLOBALS['TL_LANG']['MSC']['copyFallback'] = 'Удвоенный запас';
$GLOBALS['TL_LANG']['MSC']['noSurcharges'] = 'Дополнительных налогов не найдено.';
$GLOBALS['TL_LANG']['MSC']['ajaxLoadingMessage'] = 'Загрузка...';
$GLOBALS['TL_LANG']['MSC']['orderDetailsHeadline'] = 'Заказ № %s / %s';
$GLOBALS['TL_LANG']['MSC']['payment_processing'] = 'Ваша платеж обрабатывается. Пожалуйста, подождите...';
$GLOBALS['TL_LANG']['MSC']['authorizedotnet_process_failed'] = 'Ваш платеж не может быть обработан.<br /><br />Причина: %s';
$GLOBALS['TL_LANG']['MSC']['mmNoUploads'] = 'Файлы не загружены.';
$GLOBALS['TL_LANG']['MSC']['mmUpload'] = 'Загрузить новый файл';
$GLOBALS['TL_LANG']['MSC']['quantity'] = 'Количество';
$GLOBALS['TL_LANG']['MSC']['order_conditions'] = 'Я согласен с условиями соглашения';
$GLOBALS['TL_LANG']['MSC']['defaultSearchText'] = 'Найти товары';
$GLOBALS['TL_LANG']['MSC']['blankSelectOptionLabel'] = '-';
$GLOBALS['TL_LANG']['MSC']['emptySelectOptionLabel'] = 'Пожалуйста выберите...';
$GLOBALS['TL_LANG']['MSC']['downloadsLabel'] = 'Ваши товары доступные для загрузки';
$GLOBALS['TL_LANG']['MSC']['priceRangeLabel'] = '<span class="from">От</span> %s';
$GLOBALS['TL_LANG']['MSC']['detailLabel'] = 'Подробно';
$GLOBALS['TL_LANG']['MSC']['searchTextBoxLabel'] = 'Критерий поиска:';
$GLOBALS['TL_LANG']['MSC']['searchFieldsLabel'] = 'Поля для поиска:';
$GLOBALS['TL_LANG']['MSC']['perPageLabel'] = 'Товаров на странице';
$GLOBALS['TL_LANG']['MSC']['searchTermsLabel'] = 'Ключевые слова';
$GLOBALS['TL_LANG']['MSC']['submitLabel'] = 'Отправить';
$GLOBALS['TL_LANG']['MSC']['clearFiltersLabel'] = 'Очистить фильтры';
$GLOBALS['TL_LANG']['MSC']['buttonLabel']['update'] = 'Обновить';
$GLOBALS['TL_LANG']['MSC']['buttonLabel']['add_to_cart'] = 'Добавить в корзину';
$GLOBALS['TL_LANG']['MSC']['pagerSectionTitleLabel'] = 'Страница:';
$GLOBALS['TL_LANG']['MSC']['orderByLabel'] = 'Сопртировать по:';
$GLOBALS['TL_LANG']['MSC']['buttonActionString']['add_to_cart'] = 'Добавить %s товар в корзину';
$GLOBALS['TL_LANG']['MSC']['noProducts'] = 'Товаров не найдено.';
$GLOBALS['TL_LANG']['MSC']['invalidProductInformation'] = 'К сожалению, информация по запрошенному товару не отбнаружена в нашем хранилище. Для далнейшей поддержки свяжитесь с нами.';
$GLOBALS['TL_LANG']['MSC']['productOptionsLabel'] = 'Опции';
$GLOBALS['TL_LANG']['MSC']['previousStep'] = 'Назад';
$GLOBALS['TL_LANG']['MSC']['nextStep'] = 'Продолжить';
$GLOBALS['TL_LANG']['MSC']['confirmOrder'] = 'Заказ';
$GLOBALS['TL_LANG']['MSC']['noCategoriesAssociated'] = 'Категории не связаны с этим товаром .';
$GLOBALS['TL_LANG']['MSC']['labelPerPage'] = 'На странице';
$GLOBALS['TL_LANG']['MSC']['labelSortBy'] = 'Сортировать по';
$GLOBALS['TL_LANG']['MSC']['labelSubmit'] = 'Отправить';
$GLOBALS['TL_LANG']['MSC']['labelProductVariants'] = 'Пожалуйста выберите';
$GLOBALS['TL_LANG']['MSC']['removeProductLinkText'] = 'Удалить';
$GLOBALS['TL_LANG']['MSC']['noItemsInCart'] = 'В вашей корзине нето товаров.';
$GLOBALS['TL_LANG']['MSC']['removeProductLinkTitle'] = 'Удалить %s из корзины';
$GLOBALS['TL_LANG']['MSC']['subTotalLabel'] = 'Подытог заказа:';
$GLOBALS['TL_LANG']['MSC']['shippingLabel'] = 'Доставка';
$GLOBALS['TL_LANG']['MSC']['paymentLabel'] = 'Оплата';
$GLOBALS['TL_LANG']['MSC']['taxLabel'] = '%s налог:';
$GLOBALS['TL_LANG']['MSC']['grandTotalLabel'] = 'Итого:';
$GLOBALS['TL_LANG']['MSC']['shippingOptionsLabel'] = 'Выбранные опции доставки:';
$GLOBALS['TL_LANG']['MSC']['noVariants'] = 'Других модфикаций товара не найдено.';
$GLOBALS['TL_LANG']['MSC']['generateSubproducts'] = 'Создать соопровожающие товары';
$GLOBALS['TL_LANG']['MSC']['selectItemPrompt'] = '(выбрать)';
$GLOBALS['TL_LANG']['MSC']['actualPrice'] = 'Реальная цена';
$GLOBALS['TL_LANG']['MSC']['noPaymentModules'] = 'Опций оплаты сейчас недоступны.';
$GLOBALS['TL_LANG']['MSC']['noShippingModules'] = 'Опций доставки сейчас недоступны.';
$GLOBALS['TL_LANG']['MSC']['noOrderEmails'] = 'Электронные письма заказов не найдены.';
$GLOBALS['TL_LANG']['MSC']['noOrders'] = 'Заказов не найдено.';
$GLOBALS['TL_LANG']['MSC']['downloadsRemaining'] = '<br />%s загрузок остается';
$GLOBALS['TL_LANG']['ISO']['couponsInputLabel'] = 'Рекламный код';
$GLOBALS['TL_LANG']['ISO']['couponsHeadline'] = 'Применить рекламные коды';
$GLOBALS['TL_LANG']['ISO']['couponsSubmitLabel'] = 'Применить';
$GLOBALS['TL_LANG']['MSC']['cartBT'] = 'Корзина покупок';
$GLOBALS['TL_LANG']['MSC']['checkoutBT'] = 'Оформить заказ';
$GLOBALS['TL_LANG']['MSC']['continueShoppingBT'] = 'Продолжить покупки';
$GLOBALS['TL_LANG']['MSC']['updateCartBT'] = 'Обновить корзину';
$GLOBALS['TL_LANG']['MSC']['orderStatusHeadline'] = 'Состояние заказа: %s';
$GLOBALS['TL_LANG']['MSC']['checkboutStepBack'] = 'Вернуться к шагу "%s"';
$GLOBALS['TL_LANG']['MSC']['createNewAddressLabel'] = 'Создать новый адрес';
$GLOBALS['TL_LANG']['MSC']['useBillingAddress'] = 'Использовать адрес для выставления счета';
$GLOBALS['TL_LANG']['MSC']['useCustomerAddress'] = 'Использовать адрес покупателя';
$GLOBALS['TL_LANG']['MSC']['differentShippingAddress'] = 'Разные адреса доставки';
$GLOBALS['TL_LANG']['MSC']['addressBookLabel'] = 'Адрес';
$GLOBALS['TL_LANG']['MSC']['editAddressLabel'] = 'Изменить';
$GLOBALS['TL_LANG']['MSC']['deleteAddressLabel'] = 'Удалить';
$GLOBALS['TL_LANG']['MSC']['deleteAddressConfirm'] = 'Вы действительно хотите удалить этот адрес? Действие не может быть отменено.';
$GLOBALS['TL_LANG']['MSC']['iso_invoice_title'] = 'Счет';
$GLOBALS['TL_LANG']['MSC']['iso_order_status'] = 'Состояние';
$GLOBALS['TL_LANG']['MSC']['iso_order_date'] = 'Дата заказа';
$GLOBALS['TL_LANG']['MSC']['iso_billing_address_header'] = 'Адрес для выставления счета';
$GLOBALS['TL_LANG']['MSC']['iso_shipping_address_header'] = 'Адрес доставки';
$GLOBALS['TL_LANG']['MSC']['iso_tax_header'] = 'Налог';
$GLOBALS['TL_LANG']['MSC']['iso_subtotal_header'] = 'Подытог';
$GLOBALS['TL_LANG']['MSC']['iso_order_shipping_header'] = 'Доставка и погрузка/разгрузка';
$GLOBALS['TL_LANG']['MSC']['iso_order_grand_total_header'] = 'Конечная сумма';
$GLOBALS['TL_LANG']['MSC']['iso_order_items'] = 'Позиции';
$GLOBALS['TL_LANG']['MSC']['iso_order_sku'] = 'учетная единица';
$GLOBALS['TL_LANG']['MSC']['iso_quantity_header'] = 'Количество';
$GLOBALS['TL_LANG']['MSC']['iso_price_header'] = 'Цена';
$GLOBALS['TL_LANG']['MSC']['iso_sku_header'] = 'учетная единица';
$GLOBALS['TL_LANG']['MSC']['iso_product_name_header'] = 'Название товара';
$GLOBALS['TL_LANG']['MSC']['iso_card_name_title'] = 'Имя на банковской карте';
$GLOBALS['TL_LANG']['ORDER']['pending'] = 'В процессе';
$GLOBALS['TL_LANG']['ORDER']['processing'] = 'Обработка';
$GLOBALS['TL_LANG']['ORDER']['complete'] = 'Выполнено';
$GLOBALS['TL_LANG']['ORDER']['on_hold'] = 'Задержано';
$GLOBALS['TL_LANG']['ORDER']['cancelled'] = 'Отменено';
$GLOBALS['TL_LANG']['MSC']['low_to_high'] = 'мал. к больш.';
$GLOBALS['TL_LANG']['MSC']['high_to_low'] = 'больш. к мал.';
$GLOBALS['TL_LANG']['MSC']['a_to_z'] = 'А до Я';
$GLOBALS['TL_LANG']['MSC']['z_to_a'] = 'Я до А';
$GLOBALS['TL_LANG']['MSC']['old_to_new'] = 'ранее до поздее';
$GLOBALS['TL_LANG']['MSC']['new_to_old'] = 'поздее до ранее';
$GLOBALS['ISO_LANG']['MSC']['useDefault'] = 'Использовать значение по-умолчанию';
$GLOBALS['TL_LANG']['ISO']['productSingle'] = '1 товар';
$GLOBALS['TL_LANG']['ISO']['productMultiple'] = '%s товаров';
$GLOBALS['TL_LANG']['ISO']['shipping_address_message'] = 'Введите данные для доставки или выберите сущкствующий адрес.';
$GLOBALS['TL_LANG']['ISO']['billing_address_message'] = 'Введите ваши данные для выставления счета или выберите сущкствующий адрес.';
$GLOBALS['TL_LANG']['ISO']['billing_address_guest_message'] = 'Введите ваши данные для выставления счета';
$GLOBALS['TL_LANG']['ISO']['customer_address_message'] = 'Введите данные  вашего покупателя или выберите сущкствующий адрес.';
$GLOBALS['TL_LANG']['ISO']['customer_address_guest_message'] = 'Введите данные  вашего покупателя';
$GLOBALS['TL_LANG']['ISO']['shipping_method_message'] = 'Выберите метод доствки.';
$GLOBALS['TL_LANG']['ISO']['shipping_method_missing'] = 'Пожалуйста, выберите метод доствки.';
$GLOBALS['TL_LANG']['ISO']['payment_method_message'] = 'Введите ваши платежные данные.';
$GLOBALS['TL_LANG']['ISO']['payment_method_missing'] = 'Пожалуйста, выберите метод оплаты.';
$GLOBALS['TL_LANG']['ISO']['order_review_message'] = 'Проверить и подтвердить детали вашего заказа.';
$GLOBALS['TL_LANG']['ISO']['checkout_address'] = 'Адрес';
$GLOBALS['TL_LANG']['ISO']['checkout_shipping'] = 'Доставка';
$GLOBALS['TL_LANG']['ISO']['checkout_payment'] = 'Оплата';
$GLOBALS['TL_LANG']['ISO']['checkout_review'] = 'Проверка';
$GLOBALS['TL_LANG']['ISO']['billing_address'] = 'Адрес для выставления счета';
$GLOBALS['TL_LANG']['ISO']['shipping_address'] = 'Адрес доствки';
$GLOBALS['TL_LANG']['ISO']['billing_shipping_address'] = 'Адреса для выставления счета и доствки';
$GLOBALS['TL_LANG']['ISO']['customer_address'] = 'Пдрес покупателя';
$GLOBALS['TL_LANG']['ISO']['shipping_method'] = 'Метод доставки';
$GLOBALS['TL_LANG']['ISO']['payment_method'] = 'Метод оплаты';
$GLOBALS['TL_LANG']['ISO']['order_conditions'] = 'Условия заказа';
$GLOBALS['TL_LANG']['ISO']['order_review'] = 'Проверить заказ';
$GLOBALS['TL_LANG']['ISO']['changeCheckoutInfo'] = 'Изменить';
$GLOBALS['TL_LANG']['ISO']['cc_num'] = 'Номер банковской карты';
$GLOBALS['TL_LANG']['ISO']['cc_type'] = 'Тип карты';
$GLOBALS['TL_LANG']['ISO']['cc_ccv'] = 'CCV номер (3 или 4 цифры)';
$GLOBALS['TL_LANG']['ISO']['cc_exp_paypal'] = 'Дата истечения срока';
$GLOBALS['TL_LANG']['ISO']['cc_exp_date'] = 'Месяц/год истечения срока';
$GLOBALS['TL_LANG']['ISO']['cc_exp_month'] = 'Месяц истечения';
$GLOBALS['TL_LANG']['ISO']['cc_exp_year'] = 'Год  истечения';
$GLOBALS['TL_LANG']['ISO']['cc_issue_number'] = 'Номер выпуска банковской карты, 2 цифры (требуется для карт Maestro и Solo).';
$GLOBALS['TL_LANG']['ISO']['cc_start_date'] = 'Дата начала кредитной карты (требуется для карт Maestro и Solo).';
$GLOBALS['TL_LANG']['MSC']['pay_with_cc'][0] = 'Обработка полатежа';
$GLOBALS['TL_LANG']['MSC']['pay_with_cc'][1] = 'Пожалйста введите требуемую информацию для обработки вашего платежа.';
$GLOBALS['TL_LANG']['MSC']['pay_with_cc'][2] = 'Оплатить сейчас';
$GLOBALS['TL_LANG']['MSC']['pay_with_redirect'][0] = 'Обработка полатежа';
$GLOBALS['TL_LANG']['MSC']['pay_with_redirect'][1] = 'Вы будете перенаправлены на сайт платежного шлюза. Если вы не были пренаправлены автоматически, то нажмте кнопку "Оплатить сейчас".';
$GLOBALS['TL_LANG']['MSC']['pay_with_redirect'][2] = 'Оплатить сейчас';
$GLOBALS['TL_LANG']['MSC']['backendPaymentEPay'] = 'Пожалуйста используйте этот адрес для доступа к административной панели ePay.';
$GLOBALS['TL_LANG']['ISO']['backendPaymentNotFound'] = 'Платежный модуль не найден!';
$GLOBALS['TL_LANG']['ISO']['backendShippingNotFound'] = 'Модуль доставки не найден!';
$GLOBALS['TL_LANG']['ISO']['backendPaymentNoInfo'] = 'Этот платежный модуль не предоставляет дополнительной информации.';
$GLOBALS['TL_LANG']['ISO']['backendShippingNoInfo'] = 'Этот модуль доставки не предоставляет дополнительной информации.';
$GLOBALS['ISO_LANG']['SHIP']['flat'][0] = 'Отгрузка по единой цене';
$GLOBALS['ISO_LANG']['SHIP']['weight_total'][0] = 'Доставка на основе общего веса';
$GLOBALS['ISO_LANG']['SHIP']['order_total'][0] = 'Доставка на основе общей суммы';
$GLOBALS['ISO_LANG']['SHIP']['collection'][0] = 'Коллекция';
$GLOBALS['ISO_LANG']['PAY']['cash'][0] = 'Наличными';
$GLOBALS['ISO_LANG']['PAY']['authorizedotnet'][0] = 'Authorize.net';
$GLOBALS['ISO_LANG']['CCT']['mc'] = 'MasterCard';
$GLOBALS['ISO_LANG']['CCT']['visa'] = 'Visa';
$GLOBALS['ISO_LANG']['CCT']['amex'] = 'American Express';
$GLOBALS['ISO_LANG']['CCT']['discover'] = 'Discover';
$GLOBALS['ISO_LANG']['CCT']['jcb'] = 'JCB';
$GLOBALS['ISO_LANG']['CCT']['diners'] = 'Diner\'s Club';
$GLOBALS['ISO_LANG']['CCT']['enroute'] = 'EnRoute';
$GLOBALS['ISO_LANG']['CCT']['carte_blanche'] = 'Carte Blanche';
$GLOBALS['ISO_LANG']['CCT']['jal'] = 'JAL';
$GLOBALS['ISO_LANG']['CCT']['maestro'] = 'Maestro UK';
$GLOBALS['ISO_LANG']['CCT']['delta'] = 'Delta';
$GLOBALS['ISO_LANG']['CCT']['solo'] = 'Solo';
$GLOBALS['ISO_LANG']['CCT']['visa_electron'] = 'Visa Electron';
$GLOBALS['ISO_LANG']['CCT']['dankort'] = 'Dankort';
$GLOBALS['ISO_LANG']['CCT']['laser'] = 'Laser';
$GLOBALS['ISO_LANG']['CCT']['carte_bleue'] = 'Carte Bleue';
$GLOBALS['ISO_LANG']['CCT']['carta_si'] = 'Carta Si';
$GLOBALS['ISO_LANG']['CCT']['enc_acct_num'] = 'Encoded Account Number';
$GLOBALS['ISO_LANG']['CCT']['uatp'] = 'Universal Air Travel Program';
$GLOBALS['ISO_LANG']['CCT']['maestro_intl'] = 'Maestro International';
$GLOBALS['ISO_LANG']['CCT']['ge_money_uk'] = 'GE Money UK';
$GLOBALS['ISO_LANG']['WGT']['mg'][0] = 'Миллиграмм (мг)';
$GLOBALS['ISO_LANG']['WGT']['g'][0] = 'Грамм (г)';
$GLOBALS['ISO_LANG']['WGT']['kg'][0] = 'Килограмм (кг)';
$GLOBALS['ISO_LANG']['WGT']['t'][0] = 'Тонна (т)';
$GLOBALS['ISO_LANG']['WGT']['ct'][0] = 'Карат (кар)';
$GLOBALS['ISO_LANG']['CUR_SYMBOL']['USD'] = '$';
$GLOBALS['ISO_LANG']['CUR_SYMBOL']['EUR'] = '€';
$GLOBALS['ISO_LANG']['CUR_SYMBOL']['GBP'] = '£';
$GLOBALS['ISO_LANG']['CUR_SYMBOL']['JPY'] = '¥';

