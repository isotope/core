Isotope eCommerce Changelog
===========================

Version 2.8.1 (2022-05-31)
--------------------------

- Fixed PayPal PLUS not correctly retrieving API credentials (#2295)
- Don't use StringUtil::ampersand() to support for Contao 4.9 before 4.9.22 (#2297)
- Correctly support the public web directory (#2293)
- Fixed unsetting range filter on empty input
- Fixed setting range filter field value
- Fixed PHP8 multiple issues (#2291)
- Do not copy the product GTIN and SKU (#2288)
- Fixed mediaManager migration on Contao 4.13
- Remove current list page query parameter when filtering products (#2119)


Version 2.8.0 (2022-04-27)
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
- Added range filter modes to filter for min or max value and range in two fields
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
- Correctly require payment when order has shipping price
- Correctly reset checkout messages on GET request (#2269)
- Include updated order data when sending status notification (#2262)
- Added XOF currency (#2263)
- Do not add unpublished product variants to sitemap XML (#2244)
- Show correct shipping calculator message if no products are to be shipped (#2243)
