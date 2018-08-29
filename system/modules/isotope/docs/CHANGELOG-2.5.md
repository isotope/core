Isotope eCommerce Changelog
===========================


Version 2.5.2-stable (2018-05-??)
---------------------------------

### Fixed

- Correctly show locked and shipping date including time
- Filter module did not work if internal cache was not built
- Prevent exception in address book with Contao 4.5 (#1954)
- Mandatory field configuration in product type was ignored (#1948)
- Misspelled CSS class in mod_iso_orderhistory template (#1942)
- Do not abort for warnings during upgrade migrations (#1939)


Version 2.5.1-stable (2018-04-25)
---------------------------------

### Fixed

- DHL account number must include the product name (14 digits)
- Added null-check for custom product class implementations
- Create an empty product object for BC with product actions (#1930)
- Check method to add links for mini cart template (#1934)


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
