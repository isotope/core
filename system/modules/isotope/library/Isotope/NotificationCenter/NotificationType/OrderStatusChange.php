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
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
 * @author     Christian de la Haye <service@delahaye.de>
 * @author     Kamil Kuzminski <kamil.kuzminski@codefog.pl>
 */


namespace Isotope\NotificationCenter\NotificationType;

use NotificationCenter\NotificationType\NotificationTypeInterface;
use NotificationCenter\NotificationType\Base;


class OrderStatusChange extends Base implements NotificationTypeInterface
{
    /**
     * {@inheritdoc}
     */
    public function getRecipientTokens()
    {
        return array
        (
            'recipient_email' // @todo
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getTextTokens()
    {
        return array
        (
            'billing_*', // All the billing address model fields
            'billing_address', // Billing address as HTML
            'billing_address_text', // Billing address as text
            'shipping_*', // All the shipping address model fields
            'shipping_address', // Shipping address as HTML
            'shipping_address_text', // Shipping address as text
            'form_*', // All the order condition form fields
            'items', // Number of items in order
            'products', // Number of single products in order
            'subTotal', // Subtotal
            'grandTotal', // Grand total
            'cart_text', // Order/Cart as text
            'cart_html', // Order/Cart as HTML
            'payment_id', // Payment method ID
            'payment_label', // Payment method label
            'payment_note', // Payment method note
            'payment_note_text', // Payment method note without HTML tags
            'shipping_id', // Shipping method ID
            'shipping_label', // Shipping method label
            'shipping_note', // Shipping method note
            'shipping_note_text', // Shipping method note without HTML tags
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getFileTokens()
    {
        return array
        (
            'form_*', // All the order condition form fields
        );
    }
}