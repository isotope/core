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

$GLOBALS['TL_LANG']['MOD']['iso_products'][0] = 'Управление товарами';
$GLOBALS['TL_LANG']['MOD']['iso_orders'][0] = 'Заказы';
$GLOBALS['TL_LANG']['MOD']['iso_statistics'][0] = 'Статистика';
$GLOBALS['TL_LANG']['MOD']['iso_setup'][0] = 'Конфигурация магазина';
$GLOBALS['TL_LANG']['FMD']['isotope'] = 'Isotope eCommerce';
$GLOBALS['TL_LANG']['FMD']['iso_productfilter'][0] = 'Фильтр товаров';
$GLOBALS['TL_LANG']['FMD']['iso_productfilter'][1] = 'Определите названия отдельных фильтров для Isotope, таких как деревья категроий и фильтры атрибутов товара.';
$GLOBALS['TL_LANG']['FMD']['iso_productlist'][0] = 'Список продуктов';
$GLOBALS['TL_LANG']['FMD']['iso_productlist'][1] = 'Общий модуль списоков. Может быть использован для вывода списка товаров или их атрибутов. Может комбинироваться с другими модулями (например Модуль Фильтр) для предоставлени более широких возможностей.';
$GLOBALS['TL_LANG']['FMD']['iso_productvariantlist'][0] = 'Спиок модификаций товара';
$GLOBALS['TL_LANG']['FMD']['iso_productvariantlist'][1] = 'Выводит в список каждую модификацию товара. Удостоверьтесь, что выбраои шаблон iso_list_variants.';
$GLOBALS['TL_LANG']['FMD']['iso_productreader'][0] = 'Средство отображения товара';
$GLOBALS['TL_LANG']['FMD']['iso_productreader'][1] = 'Средство отображения товара. Модкль используемый для отображения дополнительной информации о товаре.';
$GLOBALS['TL_LANG']['FMD']['iso_cart'][0] = 'Корзина';
$GLOBALS['TL_LANG']['FMD']['iso_cart'][1] = 'Полнофункциональный модуль корзины покупателя. Тип отображения может быть установлен выбором шаблона: Блок или Показ целиком.';
$GLOBALS['TL_LANG']['FMD']['iso_checkout'][0] = 'Оформить заказ';
$GLOBALS['TL_LANG']['FMD']['iso_checkout'][1] = 'Позволяет покупателям магазина завершить их транзакции.';
$GLOBALS['TL_LANG']['FMD']['iso_addressbook'][0] = 'Адресная книга';
$GLOBALS['TL_LANG']['FMD']['iso_addressbook'][1] = 'Позволяет покупателям управлять своей адресной книге.';
$GLOBALS['TL_LANG']['FMD']['iso_orderhistory'][0] = 'История заказов';
$GLOBALS['TL_LANG']['FMD']['iso_orderhistory'][1] = 'Позволяет покупателям просматривать свою историю заказов';
$GLOBALS['TL_LANG']['FMD']['iso_orderdetails'][0] = 'Детали заказа';
$GLOBALS['TL_LANG']['FMD']['iso_orderdetails'][1] = 'Позовляет подробно просматривать покупателям историю заказов.';
$GLOBALS['TL_LANG']['FMD']['iso_configswitcher'][0] = 'Переключатель конфигураций магазина';
$GLOBALS['TL_LANG']['FMD']['iso_configswitcher'][1] = 'Переключение между конфигурациями магазина для изменения валюты и других настроек.';
$GLOBALS['TL_LANG']['FMD']['iso_relatedproducts'][0] = 'Сопутствующие товары';
$GLOBALS['TL_LANG']['FMD']['iso_relatedproducts'][1] = 'Список товаров сопутсвующих текущему товару.';
$GLOBALS['TL_LANG']['ISO']['config_module'] = 'Конфигурация Isotope eCommerce';
$GLOBALS['TL_LANG']['IMD']['checkout'] = 'Процесс оформления заказа';
$GLOBALS['TL_LANG']['IMD']['product'] = 'Товары';
$GLOBALS['TL_LANG']['IMD']['config'] = 'Общие настройки';
$GLOBALS['TL_LANG']['IMD']['shipping'][0] = 'Способы доставки';
$GLOBALS['TL_LANG']['IMD']['shipping'][1] = 'Настройте спрособы дотсавки, такие как EMS, DHL, Почта России, и т.д.';
$GLOBALS['TL_LANG']['IMD']['payment'][0] = 'Способы оплаты';
$GLOBALS['TL_LANG']['IMD']['payment'][1] = 'Настройте способы оплаты, такие как Authorize.net, PayPal Pro, и другие.';
$GLOBALS['TL_LANG']['IMD']['tax_class'][0] = 'Классы налогов';
$GLOBALS['TL_LANG']['IMD']['tax_class'][1] = 'Настройте классы налогов, которые состоят из наборов ставок налогов.';
$GLOBALS['TL_LANG']['IMD']['tax_rate'][0] = 'Ставки налогов';
$GLOBALS['TL_LANG']['IMD']['tax_rate'][1] = 'Настройте ставки налогов основанные на суммах доставки/местоположении выставления счета и общей сумме заказа.';
$GLOBALS['TL_LANG']['IMD']['attributes'][0] = 'Атрибуты';
$GLOBALS['TL_LANG']['IMD']['attributes'][1] = 'Управляйте и создавайте атрибуты товара, такие как размер, цвет и т.д.';
$GLOBALS['TL_LANG']['IMD']['producttypes'][0] = 'Типы товаров';
$GLOBALS['TL_LANG']['IMD']['producttypes'][1] = 'Управляйте и создавайте типы товаров из наборов атрибутов.';
$GLOBALS['TL_LANG']['IMD']['related_categories'][0] = 'Связанные категории';
$GLOBALS['TL_LANG']['IMD']['related_categories'][1] = 'Определите категории связанные с товаром.';
$GLOBALS['TL_LANG']['IMD']['iso_mail'][0] = 'Написать управляющему';
$GLOBALS['TL_LANG']['IMD']['iso_mail'][1] = 'Настроить форматы e-mail уведомлений администратора и покупателей.';
$GLOBALS['TL_LANG']['IMD']['configs'][0] = 'Конфигурации магазина';
$GLOBALS['TL_LANG']['IMD']['configs'][1] = 'Установить общие настройки для этого магазина.';

