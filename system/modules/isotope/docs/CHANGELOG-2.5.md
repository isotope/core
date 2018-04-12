Isotope eCommerce Changelog
===========================

Version 2.5.0-beta1 (201?-??-??)
--------------------------------

### New

- Added payment method for Concardis (https://www.concardis.com/)
- Added payment method for PayPal PLUS
- Added shipping method for DHL Business (requires petschko/dhl-php-sdk)
- Added shipping method that calculates from price in product
- Added frontend module to enter coupon code (replaces form in cart)
- Added lightbox option for inline gallery (#1800)
- Added currency symbol for Vietnamese Dong
- Added integrity check for misconfigured multilingual attributes


### Improved

- Shipping and payment methods can now show zero amound in checkout process
- Allow to send multiple notifications in checkout module and order status change
- Added logging option to payment and shipping methods
- Show the database ID in backend order list
- Unified logging for all payment and shipping methods


### Fixed

- Default shipping weight value was not set to KG in products
