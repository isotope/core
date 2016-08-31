Isotope eCommerce Changelog
===========================

Version 2.4.0-rc1 (2016-08-??)
------------------------------

### New

- Re-order an order from order history or details frontend module
- Added new favorite products collection
- Shipping weight can now be generated on product and collection
- Cart rules can be limited to the weight of products in cart
- Added insert tag {{cart::weight::kg}} to output weight of product collection
- Added support for ChangeLanguage v3
- Can skip billing address to always use the member address
- Can skip shipping address to always use billing address


### Improved

- Product teaser is now searchable in the frontend
- Merged address DCA into fields so modifyAddressFields hook can modify field config
- Custom FileTree, Downloads and Media attributes are now translatable
- Now uses Contao block templates for template inheritance
- Correctly handle tableless forms in Contao 4
- Language files are now stored as XLIFF
- Products on the home page no longer generates empty action attribute (#1672)
- Remove unsupported characters from phone number in Postfinance payment method (#1662)
- Correctly handle empty cart in shipping calculator (#1697)
- Removed remaining MooTools dependencies (#1694)


### Fixed

- Fixed live payment validation for Open Payment Platform
- Variant attributes must always have a blank option
- Canonical tags generated link to unpublished pages (#1671)
- Subdivision validation failed for certain countries (e.g. great britain) (#1678)
- Shipping and payment method was not displayed if amount was between 0 and 1
- Backend filter options were IDs instead of labels when using foreignKey options (#1683)
- Product alias was not correctly generated when duplicating product (#1659)



Version 2.4.0-beta1 (2016-07-06)
--------------------------------

### New

- Isotope now works with jQuery, MooTools or Vanilla JS
- Added new report for "members vs. guest" orders (#1577)
- Allow to set a default variant for each product (#1565)
- Skip shipping and payment methods if only one option is available (#1217)
- Added the responsive images feature (#1423)
- Added option to edit products that were added to the cart
- Added support for SQL condition, new/old filter and sorting in related product list (#1518)
- Added support for ajax file uploads with terminal42/contao-fineuploader
- The FileTree attribute is now sortable (#1561)
- Added CSS class field to product type (#1532)
- Can now perform reports on order, payment or shipping date (#1620)
- Added config setting to define an order details module for backend view (#1578)
- Validate the price attribute in product type against duplication (#1542)
- Added pagination information to product list template (#1650)
- Enable module tables in Isotope setup after all modules are loaded (#1624)
- Added hook triggered when address data is updated (#1473)
- Added hook to modify fields in checkout process


### Improved

- Product images are now shown in the sort-on-page backend view (#1249)
- Meta data cannot be entered per product variant
- Collection dates (locked, paid, shipped) are now stored as NULL if empty
- Sales report now shows the number of items in totals (#1577)
- Allow custom form field as recipient tokens in notification center
- Do not limit product categories selection by active page filter (#1648)
- Allow backend filter for text, radio and checkbox attributes (#1644)
- Datatrans payment method now supports SHA-256 algorithm (#1640)


### Fixed

- "Description" column in iso_collection_invoice template was not translated (#1652)
- Product image import did not work with subfolders (#1666)
