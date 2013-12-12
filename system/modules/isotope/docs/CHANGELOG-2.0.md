Isotope eCommerce Changelog
===========================

Version 2.0.0 (????-??-??)
----------------------------

### Fixed
- Two missing `use` statements (#829)
- Wrong Model reference in order history module
- Incorrect CSS ID in product list (#854)
- Order ID could not be generated when using prefix (#828)
- Filter did not auto-submit when using jQuery (#842)
- PostFinance requires customer ID to be only 17 chars
- Calling Product::setActive() when there's no product available
- Surcharges were not available in notifications
- PSP did not take order to check currency
- Potential foreach warning in Cumulative Filter
- mod_iso_messages template could not be loaded (#857)
- Percentage price in payment method did not work (#865)
- Several exceptions did not use global namespace (#871)
- Several places did not support UUID for files (#852)
- Label translation for config model threw exception
- Store config selector did not work (#835)
- Product cache broke the pagination (#843)
- Recursively create upload directory if it does not exist (#837)
- Missing translations for reports module (#873)
- Mandatory asterisk hidden on ajax refresh (#846)


Version 2.0.rc2 (2013-11-22)
----------------------------

### Fixed
- Variant generation did not work
- Missing payment templates for PSP payment methods
- Row class in checkout step address generated an exception (#791)
- Database was not updated when creating a new attribute (#800)
- Exception when attribute has no type (#792)
- Warning when creating a product before having any product types (#804)
- Using base price calculation caused an exception (#808)
- Fatal error when using filters/sorting but no products were found (#797)
- Fatal error when product has variant prices but no variants (#811)
- Asset import did not work at all (#801)
- PDF images with whitespace did not work (#798)
- Do not redirect if no sorting page is selected (#789)
- Exception in checkout module config when using group shipping method
- Order condition form caused checkout to fail (#793)
- Invalid SQL query in order history module
- Spinner icon did not show in "loading data" message
- The shipping address was not hidden by default (#814)
- Warning if no products were found for the current page (#816)
- Exception in frontend if no config was available
- Do not add sitemap-excluded sites to XML (#794)
- Prevent line break for sorting arrows in address field configuration (#790)
- Watermarking for galleries did not work
- From price was not correctly determined (#772)
- Show strike price only if amount is lower (#212)


Version 2.0.rc1 (2013-11-11)
----------------------------
- Initial release of Isotope 2.0
