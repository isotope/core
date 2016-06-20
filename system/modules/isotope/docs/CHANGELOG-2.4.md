Isotope eCommerce Changelog
===========================


Version 2.4.0-beta1 (201?-??-??)
--------------------------------

### New

- Allow to set a default variant for each product (#1565)
- Skip shipping and payment methods if only one option is available (#1217)
- Added support for ajax file uploads with terminal42/contao-fineuploader
- Added hook to modify fields in checkout process
- Added option to edit products that were added to the cart
- Added config setting to define an order details module for backend view (#1578)
- Enable module tables in Isotope setup after all modules are loaded (#1624)
- Validate the price attribute in product type against duplication (#1542)
- Added hook triggered when address data is updated (#1473)
- The FileTree attribute is now sortable (#1561)
- Added CSS class field to product type (#1532)
- Can now perform reports on order, payment or shipping date (#1620)


### Improved

- Meta data cannot be entered per product variant
- Product images are now shown in the sort-on-page backend view (#1249)
- Collection dates (locked, paid, shipped) are now stored as NULL if empty