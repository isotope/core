Isotope eCommerce Changelog
===========================

Version 2.5.0-stable (2018-04-17)
---------------------------------

### New

- Added payment method for Concardis (https://www.concardis.com/)
- Added payment method for mPAY24 (https://www.mpay24.com/)
- Added payment method for PayPal PLUS
- Added shipping method for DHL Business (requires petschko/dhl-php-sdk)
- Added shipping method that calculates from price in product
- Added wishlist product collection
- Added range (from-to) product filter module
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
- Store source of product collection surcharge in database table
- Fire javascript event when products are reloaded in the front end


### Fixed

- Default shipping weight value was not set to KG in products
