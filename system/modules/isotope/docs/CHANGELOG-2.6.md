Isotope eCommerce Changelog
===========================


Version 2.6.0-stable (2018-??-??)
---------------------------------

### New

- Allow shipping price calculation per cart weight (e.g. 10€ * 5kg)
- Allow to redirect to product details if keywords search is an exact match
- Added product field for GTIN (Global Trade Item Number)
- Added Trusted Shops front end module
- Added support for Saferpay JSON API to Saferpay payment method (#2023)
- Added support for debug mode in Saferpay payments
- Added shipping method limitation depending on address type
- Added front end module to filter products by categories (pages)
- Added module config to hide product options
- Highlight search keywords in product list

### Improved

- Added product type edit popup in product edit view
- Enabled drag & drop for all MultiColumnWizard fields
- Switch the shop config ID if config switcher module has only one option
- Support DCA options in product attributes
- Added template for product actions (#1949)
- Reduced database queries by fetching the product categories from JOIN
