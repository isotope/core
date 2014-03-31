Isotope eCommerce Changelog
===========================


Version 2.0.6 (2014-??-??)
--------------------------

### Fixed
- Saferpay payment method did not work if magic_quotes_gpc is enabled
- More InsertTag caching issues
- Store config switcher only worked if a product was in the cart (#1008)
- Filetree attribute did not generate frontend output (#1023)
- Shipping or payment method could be invalid when cart changes (#1002)
- Shipping address was unavailable when no shipping method was set (#985)
- Potential error message when no filter modules were found (#974)
- in_array error message when editing product as regular user
- Regular users could not filter by product group
- Product group filter was always applied for regular users
- SQL exception when coupon code was dropped (#1043)
- Help wizard for product type class was not available
- Help wizard for product type was not available (#1045)
- Document number not available in notification for cash payment (#1044)


Version 2.0.5 (2014-03-24)
--------------------------

### Fixed
- Possible recursion in group shipping method
- Missing return value in load_callback in DC_ProductData driver
- Missing palette for messages module
- Product names were not listed in related categories
- PayPal payment method did not work at all
- Cache-enabled insert tags were not replaced in order prefix
- "Default" checkbox for custom attributes had no effect (#1017)
- "Group" checkbox for custom attributes destroyed select menus (#1017)


Version 2.0.4 (2014-03-03)
--------------------------

### Improved
- Clarified Payone merchant id label

### Fixed
- Attribute FileTree did not correctly save checkbox values (#990)
- Attribute Downloads did not work (#993)
- Exception when using shipping weight as variant attribute (#940)
- Missing user instance in back end overview (#998)
- Orders were not filterable in the backend (#973)
- Filter module did not work with latest Contao due to security fixes (#991)
- Skip incomplete payment and shipping methods (#1004)
- Downloads were not available for variants (#1005)
- Date picker did not work in the frontend (#1013)


Version 2.0.3 (2014-02-10)
--------------------------

### Improved
- Missing check for empty array could trigger warning (#965)

### Fixed
- Type agent help wizard was not restricted to type agent models only (#962)
- Back button in variant list did not return to product overview (#883)
- Wrong directory path in document file output (#957)
- Document file names were not sanitized (#959)
- CSS class was missing a space (#966)
- Exception in shipping and payment checkout step when using frontend preview
- Product variant list showed base product
- Request cache generated duplicate entries for identical configurations
- Product was unavailable in cart if it had variants but not variant prices
- Spelling mistake in method call (#977)
- Incorrect database field type for Downloads attribute (#983)
- Could not set billing or shipping default for address in backend (#967)
- Cache redirect was not applied for cumulative filter (#974)


Version 2.0.2 (2014-01-23)
--------------------------

### Improved
- Filter and sorting fields were available even if disabled in all product types

### Fixed
- Automated currency converting did not work
- Help wizard for type agents did not work (#937)
- Multi-Edit view in variants did not show correct columns
- Advanced filters were applied to product variant view (#931)
- Missing import statement in cumulative filter (#943)
- Missing request token on "saveN"-buttons
- Cumulative filter template was not found (#944)
- Sorting on price attribute did not work (#945)
- Download expiration was calculated incorrectly (#932)
- Old collections were not deleted
- New order messages caused error on the home screen for non-admins (#955)
- Customer defined options were only shown for products with variants (#936)
- Product list sorting was bypassed by the cache (#952)


Version 2.0.1 (2014-01-06)
--------------------------

### Improved
- Payment method templates are using pure JavaScript now

### Fixed
- Back end interface for PayPal payment method
- Missing payment template for Sofortueberweisung
- Prevent warning when no filter modules were activated (#922)
- Radio option for rule condition was not active (#913)
- Workaround for SQL error due to Contao bug #6623 (#912)
- Variant generator set wrong inherit values (#914)
- Attributes allowed having "-" in internal names (#929)
- Product collection template could link to home page


Version 2.0.0 (2013-12-24)
--------------------------

### Improved
- Use Haste Form to render address book fields
- Orders are deleted after a timeout if they are not completed (#890)

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
- Checkout payment selection was not mandatory (#864)
- Surcharges were ignored in PayPal payment (#831)
- The media manager now uploads photos to the temporary folder first (#876)
- Reports missed report about total products (#872)
- Documents did not have access to config when printing in back end (#862)
- Skipping address step as member was possible
- Address module did not work (#840)
- Customer defined attributes did not work properly (#844)
- PSP performed checkout on error-status (#881)
- Order count in sales report was wrong (#872)
- JavaScript error in inline gallery (#859)
- Disabled sorting by invalid column "variantFields" (#838)
- Restored coupon form to the cart template (#868)
- Disabled Contao cache could result in error message in attribute wizard (#884)
- Gallery could not be set for related products (#887)
- Used products and product types could be deleted in select mode (#882)
- Customer defined attributes incorrectly showed up in cart (#888)
- Error when setting new address as default (#895)
- Breadcrumb was broken if no reader page was configured (#845)
- Mediabox/Slimbox/Colorbox were not reinitialized after product option selection (#886)
- Custom addresses in checkout were not correctly shown/hidden (#906)


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
