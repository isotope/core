Isotope eCommerce Changelog
===========================

Version 2.3.0-beta1 (2015-??-??)
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
