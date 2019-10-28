Isotope eCommerce Changelog
===========================

Version 2.6.4 (2019-11-20)
---------------------------------

- Only enable RTE config in the TextArea attribute
- Escape MySQL 8 keywords (#2088, #2093)
- Force image resize for watermark and size calculation (#2085)
- Helper function to determine if a product collection has tax (#2076)


Version 2.6.3 (2019-10-15)
---------------------------------

### Fixed

- Correctly calculate taxes for product option surcharges (#2059)
- Correctly generate product value for options with groups (#2082)


Version 2.6.2 (2019-09-17)
---------------------------------

### Fixed

- Failed to generate product cache when showing old or new products (#2071)
- StringUtil::trimsplit is not available in Contao 3.5 (#2067)
- Correctly show upload errors in Contao 4 (#2070)
- Filter error in product variant list (#2068)
- Automatically rotate log files in Contao 4 (#2065)


Version 2.6.1 (2019-08-28)
---------------------------------

### Fixed

- Product reader page shows articles of product list page (#2060)


Version 2.6.0 (2019-08-27)
---------------------------------

### New

- Allow shipping price calculation per cart weight (e.g. 10€ * 5kg)
- Allow to redirect to product details if keywords search is an exact match
- Allow to limit coupon code to a single usage (#1580)
- Added product field for GTIN (Global Trade Item Number)
- Added Trusted Shops front end module
- Added support for Saferpay JSON API to Saferpay payment method (#2023)
- Added support for debug mode in Saferpay payments
- Added shipping method limitation depending on address type
- Added front end module to filter products by categories (pages)
- Added module config to hide product options
- Added bank details to the store configuration (#2054)
- Added checkbox to enable chosen script for attributes (#1989)
- Highlight search keywords in product list

### Improved

- Added product type edit popup in product edit view
- Enabled drag & drop for all MultiColumnWizard fields
- Switch the shop config ID if config switcher module has only one option
- Support DCA options in product attributes
- Added template for product actions (#1949)
- Reduced database queries by fetching the product categories from JOIN
- Automatically select the only variant option in dropdown menus (#2052)
- Always link to the current category page if possible (#2000)
- Also generate canonical link to the main product URL

### Fixed

- Use HTTPS URL for ECB currency updates (#2053)
- ChangeLanguage alternate links pointed to custom reader page (#2026)
