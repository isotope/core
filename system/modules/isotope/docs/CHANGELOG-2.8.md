Isotope eCommerce Changelog
===========================

Version 2.8.0 (2022-02-??)
--------------------------

- Now requires Contao 4.9 and PHP 7.2
- Allow installation on PHP8
- Added option to disable reader page for product category
- Added feature to mark products/product types as pickup only
- Added price display configuration for member groups
- Added product picker to related products
- Added support for custom templates in frontend form fields
- Added cssClass to attribute options
- Added new attribute "Surcharge per quantity"
- Added support for notification tokens from payment and shipping method
- Allow payment and shipping method to decide whether they have a backend interface
- Hide the print button in order view if there are no documents
- Added payment method and card number to Datatrans payment
- Allow date and time for product start and stop times (#2253)
- Enable MediaManager to upload allowed file extensions (#2227)
- Added integrity check for invalid prices belonging to wrong products
- Ignore missing product images instead of throwing an exception
- Show variant price and sku in the backend product list
- Rewrite entry points to Symfony routes
- Use signed redirect URL to circumvent POST data issues in Datatrans payment
- Pass the product model to attribute widgets
- Improve performance by using non-deprecated methods
- Fixed encoding of PSP/VIVEUM data
