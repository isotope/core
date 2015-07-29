Isotope eCommerce Changelog
===========================

Version 2.3.0-rc2 (2015-??-??)
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
