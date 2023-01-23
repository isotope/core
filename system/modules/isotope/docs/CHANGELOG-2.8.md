Isotope eCommerce Changelog
===========================

Version 2.8.9 (2023-01-23)
--------------------------

- Added "other" to gender (#2390)
- Add handling for sorting by variant attributes (#2378)
- Correctly check for default shipping address (#2316)
- Correctly calculate option prices with price modifier (#2342)
- Correctly check for guest permissions in Contao 4.13+
- Fixed various PHP8 warnings


Version 2.8.8 (2022-11-01)
--------------------------

- Correctly store dateAdded for product variants (#2376)
- Correctly handle empty result from multiple category filters
- Correctly set product attributes from URL parameters
- Make sure address format are loaded (#2377)
- Fixed legacy Contao class usages (#2363)
- Fixed unbuffered database queries in migrations (#2367)
- Fixed PHP warning on empty attribute legend (#2369)
- Fixed various PHP 8 warning (#2369, #2381, #2382, #2384)


Version 2.8.7 (2022-08-23)
--------------------------

- Added missing dependency for webmozart/path-util (#2357)
- Fixed insert tags not being replaced on ajax requests
- Fixed various PHP compatibility issues (#2352, #2354, #2358, #2359)
- Fixed missing product sorting icon (#2361)
- Correctly remove field from palette after unsetting it (#2360)


Version 2.8.6 (2022-08-15)
--------------------------

- Allow terminal42/contao-fineuploader v2 and v3 (#2321)
- Use ResponseException instead of exit; call
- Make sure the isotope directory actually exists (#2346)
- Handle case when host matches no roots, and no root with wildcard DNS exists (#2347
- Correctly check for downloads array in collection template (#2330)
- Fixed explanation for uploadable attributes (#2322)
- Fixed array to string conversion (#2324)
- Fixed default translate value of images in MediaManager.php (#2349)
- Fixed translations for currency providers (#2327)
- Fixed lots of PHP8 errors (#2338)
- Fixed translations for member and user groups (#2332)


Version 2.8.5 (2022-07-11)
--------------------------

- Add missing use Contao\Database; (#2313)
- Fixed PHP8 issues (#2315)
- Fixed invalid media on Isotope CSS file


Version 2.8.4 (2022-06-28)
--------------------------

- Fixed broken attribute manager on field error
- Fixed Saferpay notification URL (#2305)
- Fixed invalid cookie path
- Fixed lots of PHP8 issues
- Use response context in Contao 4.13+ (#2310)
- Update checkout and postsale URLs for payment methods
- Add missing redirect config for category filter module (#2185)


Version 2.8.3 (2022-06-08)
--------------------------

- Fixed ajax request without form action (#2303)
- Fixed invalid shipping address ID in orders without shipping (#2014)
- Optimize database columns to fix row size issues


Version 2.8.2 (2022-06-01)
--------------------------

- Fixed Composer compatibility for symfony/polyfill-php80


Version 2.8.1 (2022-06-01)
--------------------------

- Fixed version constraint for symfony/polyfill-php80 (#2302)


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
