Isotope eCommerce Changelog
===========================

Version 2.3.5-stable (????-??-??)
---------------------------------

### Improved
- Alias was not generated from product name in override-all mode (#1638)
- Prevent umlauts and other special chars in coupon code (#1608)

### Fixed
- The external image paths in documents did not work (#1616)
- Installation on Contao 4 failed due to invalid dependency (#1633)
- Problem with custom media attributes when upgrading from Isotope 2.0 (#1632)
- Multilingual attribute options from foreignKey did not work for variants (#1631)
- Variant selection resulted in empty page if debug bar is enabled (#1639)
- The customer defined attribute options set in base product were not available in the variants
- Saferpay payment method dumped empty amount to postsale debug file


Version 2.3.4-stable (2016-03-09)
---------------------------------

### Improved
- Added support for installation on Contao 4
 
### Fixed
- Potential backend error message when saving prices
- Issue when inheriting mandatory fields in the backend (#1600)
- Weekly report for first weeks of the year did not work as expected (#1610)
- Product filter did not support multiple choice attributes (##1538)


Version 2.3.3-stable (2016-01-27)
---------------------------------

### Fixed
- Callbacks were not compatible with PHP7 (#1584)
- Some Postfinance fields are limited to 35 characters (#1586)
- Regular users could not always access new records (#1503)
- Product version restore could lead to database error (#1506)
- Custom attributes did not show in edit-multiple mode (#1589)
- Backend order status update notification did not contain latest data (#1593)
- Show price and category changes in product diff (#1598)
- Tax was removed twice from product option price (#1595)
- Currency in documents and emails can be wrong with multiple store configs (#1590)
- Incorrect options were shown for attributes with product source (#1563)
- Sales tax was not correctly calculated on shipping address (#1509)


Version 2.3.2-stable (2015-12-01)
---------------------------------

### Improved
- Considerable improved performance of product list
- Allow to get unformatted values from attribute (#1558)
- Added missing clearing types for PayOne 
- Extend the guest cart cookie lifetime on every access to the shop

### Fixed
- Ajax loading did not work when the list or reader module was included using InsertTags
- Variant options must not have a default value (#1546)
- Support for member_grouped template in address book and cart address was broken (#1536)
- Updated transaction feedback handling for PayOne
- Show customer defined fields in product variant list
- Hidden options in product list contained invalid values (#1566)
- Prevent reuse of unique cookie hash for the cart (#1556)
- Download attribute with single selection was not correctly rendered (#1555)


Version 2.3.1-stable (2015-09-14)
---------------------------------

### Fixed
- Correctly pass the checkout module to *preCheckout* hook (#1520)
- Gallery model state must be reinitialized (#1512)
- Wildcard edit link did not work for cumulative filter
- Image size fetching failed when encoded characters in thumbnail path
- Correctly generate labels instead of values from checkbox attributes (#1521)
- Download attributes with radio button did not work (#1527)


Version 2.3.0-stable (2015-09-01)
---------------------------------

### Improved
- Store options manager attributes as integer database fields (#1507)
- foreignKey attribute values can also be stored as CSV
- Select/Radio/Checkbox fields are not searchable in the frontend (#1508)
- Allow to override the product languages to support i18ln10n extension (#1385)
- PayPal payment method should only handle "Complete" status (#1419)

### Fixed
- Correctly generate label for attribute options from Options Manager or Products


Version 2.3.0-rc2 (2015-07-31)
------------------------------

### Improved
- Payment methods can now be configured in QuickPay module
- Combine CSS and JS with core files to reduce server requests (#1490) 
- Enable PHP 7 compatiblity (requires Contao 3.5.2 now)

### Fixed
- Buttons in product list and reader were not shown
- Overriding the product reader page resulted in 404 error (#1463)
- Serialized values were not converted to CSV
- The language selector in products table was not accessible (#1476)
- File name for elevateZoom was not capitalized in script link (#1496)


Version 2.3.0-rc1 (2015-06-26)
------------------------------

### New
- Added payment method for QuickPay (https://quickpay.net) / Pensopay (http://www.pensopay.com)
- Added Config::getOwnerAddress to get address model of the shop config address

### Improved
- Default invoice template now looks way better
- Added blank label "tax free" to all price fields (#1427)
- Show tax ID column only if there are multiple tax rates (#1425)
- Using minute rounding for DB queries (#1411)
- Product filter should also check for new/old product configuration
- The product count in cumulative filter is now updated to match the existing filters
- Moved methods to allow overriding of meta tags in product template (#1446)
- Better performance when having translated products (#1460)

### Fixed
- Duplicate key for member carts
- Product filter did not redirect to current page without argument, if reference page is set
- API break in Standard gallery class
- Inline gallery did not correctly generate main image
- Removed unnecessary whitespaces in address formatting
- Prevent potential duplicate key SQL error (#1436)
- Language selector was not visible if there were no product versions (#1424)
- Incorrect calculation with multiple tax rates on one tax class (#1428)


Version 2.3.0-beta1 (2015-04-13)
--------------------------------

### New
- Net or gross price display can now be configured per shop configuration
- Added interface to validate tax ID and influence tax calculation
- Added validation for EU taxes using [VIES](http://en.wikipedia.org/wiki/VAT_Information_Exchange_System) 
- Added payment method for www.innocard.ch
- Added column view to product type backend
- PSP payment methods now support predefining a payment method (implemented in PostFinance only for now)
- Added new InsertTags for current cart and order (see documentation)
- Surcharge details (raw data and object) are now available in templates
- Cumulative filter now shows number of products/variants per option
- Cumulative filter automatically uses OR-condition if attribute can't have multiple options (e.g. not a checkbox)
- Added AND-condition to replace filter on non-multiple attributes 
- Added $this->hasAttribute() callback to list/reader and collection templates
- Replaced product filter autocompleter with awesomplete (#1386)
- Added hook to customize product downloads (#1311)
- Added minimum_quantity and maximum_quantity restrictions to payment methods (#1305)
- Added minimum_quantity and maximum_quantity restrictions to shipping methods (#1306)
- Add TL_ASSETS_URL/TL_FILES_URL to product images (#1407)
- Added tax_free_subtotal and tax_free_total to product collection templates (#1345)

### Improved
- Product versions can now be compared in the backend (#1283)
- Selecting a language in the backend is now auto-submitted
- Cumulative filter can now have a jumpTo page
- Cumulative filter can now be hidden on a reader page
- Cumulative filter now supports product category scope
- Cumulative filter now shows only actually used product options
- Filter template now uses placeholder instead of javascript for search field default text
- Moved coupon form from collection to cart template to prevent nested form (#1366)
- Images in galleries are no lazy-loaded (should improve product list performance)
- Prices are now only rounded before being displayed (should improve decimal place calculation)
- Call postDeleteCollection hook after a product collection is deleted
