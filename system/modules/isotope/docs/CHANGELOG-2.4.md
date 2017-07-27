Isotope eCommerce Changelog
===========================


Version 2.4.3 (2017-07-28)
--------------------------

### Fixed

- Address data was not correctly saved in orders (#1828)
- Remove edit button if product in collection has errors (#1829)


Version 2.4.2 (2017-07-26)
--------------------------

### Improved

- Updated navigation icon for Contao 4.4 backend (#1808)
- Pass DataContainer object when generating widgets (#1758)


### Fixed

- Removed line break on version panel in product edit mode
- Integrity Check could not disable rules module in Contao 4 (#1764)
- Removed usage of mysql_real_escape_string (#1786)
- Fixed PHP7 compatibility for backend overview callback (#1795)
- Always store the country in order addresses (#1811)
- Member country restrictions were not applicable in Contao 4 (#1803)
- ImageSize widget is no longer usable for custom options (#1761)
- Shimane was missing in the japanese subdivisions list (#1810)
- Custom template is not supported for related products module (#1809)
- DC clipboard is stored in non-persistent session in Contao 4 (#1806)
- Group and page selection in products backend did not reload (#1801)
- Help wizard was empty for payment, shipping, gallery and producttype (#1821)
- TinyMCE options were missing in textarea attribute (#1815)
- Back button was not visible in Contao 4.4 picker (#1813)


Version 2.4.1 (2017-03-07)
--------------------------

### Fixed

- Product was not shown in the breadcrumb (#1690)
- Back button in checkout process sent to next step (#1749)
- FineUploader product attribute did not upload files correctly
- FineUploader product attribute did not display correctly in cart
- Giropay requires order ID with a least 4 digits (#1744)
- Disable buttons for order collection in backend (#1781)
- Load TCPDF config from core-bundle in Contao 4 (#1763)
- Improved CSS styles for backend modules (#1765, #1766, #1767, #1768, #1769, #1770, #1772, #1774)
- Subdivision name was empty for address tokens (#1780)
- Gracefully handle SOAP issues in EU Vat Validation (#1755)
- Coupon codes were not transferred from guest to member cart on login (#1754)
- Sofortüberweisung payment validation failed on special characters (#1034)
- Templates in Isotope config folder were not found for status notifications (#1773)
- Orders were always seen as being paid (resulting in download being available)


### Improved

- Added itemscope attributes to product list (#1751)


Version 2.4.0 (2016-12-21)
--------------------------

### New

- Added plugin for Contao Manager


### Improved

- Only select menu variant options must have blank option (#1738)
- Remove product category and price version info from welcome screen (#1725)
- Remove pagination parameter from cumulative filter (#1739)


### Fixed

- Prevent error message when no buttons are enabled (#1735)
- Name and help wizard for model types were no longer translated (#1716)
- Shipping and payment quantity mode is mandatory (#1743)
- Prevent duplicate collection error if cart cookie could not be set (#1721)
- Incorrect tax free total calculation in net-calculation mode (#1711)
- PHP7 does not allow callback value by reference in array_filter (#1746)


Version 2.4.0-rc1 (2016-10-07)
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
- Rule discount can now be configured to always round up, down or commercially


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
- Pre-fill country from member for new address book entries
- Use Guzzle or HttpRequestExtended to support HTTP/1.1 for PayPal
- Use ipnpb.paypal.com URL for PayPal data confirmation (#1657)
- Do not display product in breadcrumb for 404 and 403 page types (#1690)
- Correctly handle single checkbox options in the backend (#1658)
- Sanitize product names and address data for payment providers (#1256)
- Placeholder for text attributes is now translatable (#1707)


### Fixed

- Fixed live payment validation for Open Payment Platform
- Variant attributes must always have a blank option
- Canonical tags generated link to unpublished pages (#1671)
- Subdivision validation failed for certain countries (e.g. great britain) (#1678)
- Shipping and payment method was not displayed if amount was between 0 and 1
- Backend filter options were IDs instead of labels when using foreignKey options (#1683)
- Product alias was not correctly generated when duplicating product (#1659)
- Inline and ElevateZoom gallery only worked once per page due to duplicate CSS ID (#1674)
- Guest carts were deleted every day (#1709)
- Page picker was filtered if the product category filter is active (#1701)
- Rounding issues in product quantity summary and net price on gross shop config
- Default sorting field in product filter module was not applied to dropdown



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
