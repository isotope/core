Isotope eCommerce Changelog
===========================

Version 2.3.0-beta1 (2015-??-??)
--------------------------------

### New
- Added payment method for www.innocard.ch
- Added column view to product type backend
- PSP payment methods now support predefining a payment method (implemented in PostFinance only for now)
- Added new InsertTags for current cart and order (see documentation)
- Surcharge details (raw data and object) are now available in templates
- Cumulative filter now shows number of products/variants per option
- Cumulative filter automatically uses OR-condition if attribute can't have multiple options (e.g. not a checkbox)
- Added AND-condition to replace filter on non-multiple attributes 

### Improved
- Product versions can now be compared in the backend (#1283)
- Selecting a language in the backend is now auto-submitted
- Cumulative filter can now have a jumpTo page
- Cumulative filter can now be hidden on a reader page
- Cumulative filter now supports product category scope
- Cumulative filter now shows only actually used product options
- Filter template now uses placeholder instead of javascript for search field default text
- Moved coupon form from collection to cart template to prevent nested form (#1366)
