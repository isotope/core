Isotope eCommerce Changelog
===========================


Version 0.2.12 (2011-10-11)
---------------------------

### New
- "transferCollection" hook

### Fixed
- availability of unpublished products
- min&max limit in product rule was not applied
- security issue in payment modules (#2221)
- language keys in paypal module (#2245)


Version 0.2.11 (2011-08-28)
------------------------------

### Improved
- checkout is now relying on order pid for member, not the currently logged in user object
- member data is available in checkout confirmation email template

### Fixed
- "text only" checkbox not hiding html input in mail templates
- attribute editor allowing to change database field name in edit all mode
- direct links to edit mode of modules did not take account of themes
- potential issues when having multiple cart modules on the same page
- Image watermark was not applied if the original image was not resized (Ticket #487)
- Asset import added every image to every product due to regex bug (Ticket #512)
- potential issue in rules when no cart ID was available (Ticket #509)


Version 0.2.10 (2011-07-06)
------------------------------

### New
- support for Contao 2.10 "structure" event (see http://dev.contao.org/issues/2504)
- shipping & payment method filter to orders
- includeBlankOption=true for select options using foreignKey (Ticket #292)
- product sku to filter module

### Improved
- billing & shipping country is no longer mandatory in store config
- Changed tl_iso_products::getCategoryList from private to protected function (Ticket #477)

### Fixed
- error message when editing page category (Ticket #469)
- IsotopeProductCollection::getProducts database caching
- rules error on product restrictions when not product in cart (Ticket #471)
- coupon could be applied multiple times by using different letter cases (Ticket #476)
- text email fields, make sure they never contain HTML


Version 0.2.9 (2011-05-23)
------------------------------

### Improved
- Show disabled shipping and payment modules on frontend preview
- Postfinance payment module with new authentication method (Ticket #235)
- Only add href_reader if field is available in product collection items table (Ticket #453)

### Fixed
- bug when products have no SKU
- product rules bug with member limits (Ticket #409)
- specialchars in image gallery and checkout steps (Ticket #426)
- issue when product alias starts with a digit (Ticket #452)
- empty option value ##cart_text## (Ticket #418)


Version 0.2.8 (2011-05-09)
------------------------------

### New
- import/export option for mail templates
- quantity_requested to product template
- support for conditionalselect as conditionField

### Improved
- Do not allow negative cart/order totals
- Do not cache arrData from IsotopeProduct, return NULL for non-existing values
- backend order listing (Ticket #424)

### Fixed
- issue with email address on checkout
- potential product load problem in IsotopeProductCollection
- potential problem with rule product restrictions
- bug with rule limits per store config or member
- potential issue with member ID in rules
- potentially wrong publish-status icon (Ticket #437)
- order items not deleted when deleting orders (Ticket #438)
- issue in cart rules, some were applied even if no product matched
- tax ID is not shown for surcharges (Ticket #449)
- fatal error on order PDF export (Ticket #448)
- potential issues with data container not being loaded (Ticket #444)
- potential issue with CC and BCC email recipients (Ticket #447)
- product filter javascript error in firefox (see http://www.contao-community.de/showthread.php?17678)
- copying mail template did not duplicate languages
- Tax rate amount was not mandatory
- Tax ID information for each product was lost when placing the order (Ticket #417)

### Other
- Removed default list template in tl_content
- Minor code cleanup


Version 0.2.7 (2011-03-09)
------------------------------

### New
- support for language switching on checkout page (changelanguage extension)
- support for language switching on order details page (changelanguage extension)
- support for __isset() to product, collection, gallery, payment and shipping classes (Contao Core Ticket #2897)
- root page option to product list module (Ticket #366)

### Improved
- ajax overlay position and transparency

### Fixed
- potential SQL injection in product collection
- CSS classes in checkout review and order details
- emails not sent to customers (Ticket #406)
- tax calculation for prices including taxes and discount
- float precision on discount rules
- orders not appearing for non-admin users


Version 0.2.6 (2011-03-01)
------------------------------

### New
- label for toggle button in product tree
- missing 'enabled' label to tl_iso_shipping_options (Ticket #373)
- support for language switching on product reader page (changelanguage extension)
- automatically disable page cache for sites with customer-related modules such as Cart (Ticket #392)

### Improved
- Check isDefaultBilling/isDefaultShipping on first checkout
- Label for payment method is no longer mandatory (will use name if not set)
- Made text fields for meta description & meta keywords smaller (Ticket #386)
- Added javascript confirmation before deleting address (Ticket #328)
- Use generic labels for redirecting to payment provider on checkout
- order PDF printing, fixed several bugs (Ticket #376, Ticket #356)

### Fixed
- ##newsletter.css## in HTML email
- issue with simple tokens in mail template subject
- issue with HTML in plain text emails
- CSS classes for new address fields in checkout module
- publish toggle not working for non-admins
- hardcoded labels in order history/details (Ticket #382)
- unlimited downloads only downloadable once (Ticket #383)
- filters overwriting each other (Ticket #363)
- issues with email messages (Ticket #391)
- tax surcharge calculation for prices including taxes
- bug in explode() for the filterFields template variable.
- Address was not added to address book correctly
- Downloads in PDF invoice caused fatal error
- Product filter searched for field default value (Ticket #377)
- Moved cart & checkout table summary to language file (Ticket #338)
- Adjust Order Total Shipping to account for shipping-exempt products (Ticket #398)

### Other
- Adopted Contao Core Ticket #2853


Version 0.2.5 (2011-02-01)
------------------------------

### New
- Isotope version constant to config file

### Improved
- Use trigger_error() not "throw Exception" if no store configuration is available (Ticket #372)

### Fixed
- discount applied to subtotal on cart overrides other restrictions (Ticket #352)
- sql in edit-all mode on attributes
- ajax load spinning icon
- loading message font color
- text area floating in backend product edit mode

### Other
- Dropped unnessesary database fields
- Dropped unnecessary template ce_link_repeater.tpl
- Removed deprecated ups_developersKey


Version 0.2.4 (2011-01-17)
------------------------------

### New
- Use product ID in URL if alias is missing

### Improved
- italian address formatting (Ticket #305)
- UK address formatting (Ticket #301)
- number of SQL queries

### Fixed
- weight total shipping included rates from all shipping modules
- mandatory asterisks not appearing in checkout address fields
- email issues with mail clients not supporting <tfood>
- missing comma in mail text (Ticket #217)
- HTML issue in iso_reader_default.tpl (Ticket #203)
- Custom templates were not always shown in "override all" mode (see Contao Core Ticket #2725)
- shipping and payment method always displaying error message (Ticket #175)
- toggle button not appearing for non-admins (Ticket #253)
- global operation labels in IE7 (Ticket #253)
- order delete button appearing for non-admins (Ticket #192)
- issues with runonce.php database file update on certain systems
- download listing out of view (Ticket #280)
- small error in the mod_iso_addressbook.tpl (Ticket #324)
- invalid name attribute in form tag (Ticket #322)
- Product list did not show unpublished products when frontend preview was used

### Other
- Updated ContentIsotope methods to match ModuleIsotope methods (Ticket #290)

Version 0.2.3 (2010-12-20)
------------------------------

### New
- hooks for altering taxes
- guest and member group restrictions on payment modules
- product type restrictions on shipping modules
- "check all" in product quick edit (Ticket #294)

### Fixed
- countries bug in checkout
- language files still containing search label
- address field translation in mails
- allowed number of downloads, was not multiplicated by the number of purchases
- download availability check bugs
- jumpTo in related product list
- division by zero error message in product list
- mail templates, parseSimpleTokens stripped important HTML tags
- SQL error when adding new category to a product (Ticket #299)
- bug in rule calculation for each product quantity
- order history not showing discount price

### Other
- Dropped italian regions (Ticket #305)


Version 0.2.2 (2010-12-05)
------------------------------
- Attempt to fix the checkout problem


Version 0.2.1 (2010-12-04)
------------------------------
- Fixed allowed country comparison for billing & shipping countries (Ticket #279)


Version 0.2.0 (2010-12-02)
------------------------------

### New
- label for isotope_rules extension folder
- support for percentage surcharge for payment and shipping price
- italian translation for frontend information
- option to "authorize" or "capture" transations for ePay payment modules
- link to admin panel for ePay payment gateway
- listing for "parent category" to product list
- listing for "product categories" to product list
- polish translation, thanks to Radosław Maślanek

### Improved
- address parsing with custom fields

### Fixed
- field tl_iso_rules.applyTo not visible in the palette
- PayPal checkout not charging for shipping price
- back button on attributes page (Ticket #259)
- mail subject appearing in HTML content
- listing module having a template assigned by default
- new mode-4-grouping in shipping options
- issues with checkbox attributes (Ticket #273)
- fallback for deleted products throwing an exception
- potential foreach error in IsotopePOS (Ticket #272)
- Check for array as well as array size on line 96 in ModuleIsotopeProductFilter (Ticket #249)
- Isotope class not able to load data containers
- tax ids for products in checkout and cart
- exception when deleting and creating new attribute (Ticket #260)


Version 0.2.rc1 (2010-10-31)
------------------------------

### New
- "Default" option for tax class
- New mode setting for rules max/min quantity

### Improved
- Replace InsertTags in ##cart_text## and ##cart_html## templates
- Only show price for payment and shipping module if greater 0
- Hide "Default address" checkboxes for guests
- Cleaned and optimized attribute configuration/setup

### Fixed
- FE_USER_LOGGED_IN might be true but no user available (Ticket #213)
- Email language was not correctly detected under certain circumstances
- Potential database caching issue in ePay payment modules
- images did not change on ajax update
- Added default palette for checkout module if no checkout method (guest/member) is selected
- tax total was not rounded according to store config
- PayPal Standard payment charges included taxes twice
- Missing argument 2 for IsotopeRules::findCoupon()
- Incorrect default value for tl_iso_products.shipping_weight
- various issues with PayPal sandbox processing
- address validation in checkout module


Version 0.2.beta2 (2010-09-27)
------------------------------

### New
- New "link target" field and help wizard to media manager
- IsotopeGallery now supports link targets and "rel" attributes
- Support for 13 more currencies in ePay Standard payment module
- New payment module for ePay in-shop payment processing
- CSS class even/odd for product list
- Define custom image sizes using a wizard
- Watermark option for all images
- Addresses are added to address book on checkout (enable in checkout module) (Ticket #24)
- Abstract class ContentIsotope to build content elements
- Frontend modules now support theme templates
- Cybersource payment gateway

### Improved
- Downloads can now be added to product variants (Ticket #116)
- PayPal Standard now shows products on paypal page (Ticket #146)
- Product reader now has a "back" link (Ticket #197)
- Downloads is now a tools option (Ticket #116)
- Remove form errors when selecting a new product variant
- Empty option labels are now taken as blankOptionLabel for variant select menus
- Variant products don't show select menus with only one option
- Cart module now hides button withouth a target page (jump to cart/checkout)
- Added check if form ID exists in IsotopeProduct javascript class
- Added name attribute with random value to product variant forms to prevent firefox from preserving selection on reload
- ModuleIsotopeProductList can now be used for any product listing by replacing findProducts() an a child class.
- authnet_response and authnet_reason to generic transaction_response and transaction_response_code
- Authorize.net now PCI-compliant - no longer storing card data in session

### Fixed
- cart_html and cart_text now include subtotal, surcharges and grand total (Ticket #34)
- Variant price should not show "From" when all variants have the same price
- Now using core widget TimePeriod for shipping weight which has a larger select menu.
- Printing order PDF throwed an exception
- Use first available product type if no default/fallback is set
- Problem with standardize function and wrong image folder (Ticket #201)
- Incorrect reference to bcc property of a mailing object (Ticket #206)
- All variant attributes are now mandatory
- Phone number not filled in PayPal Standard (Ticket #171)
- Line breaks in teaser did not work (Ticket #198)
- tl_iso_addressbook.email is no longer mandatory (Ticket #174)
- Removed redirect page from address book module setting (Ticket #174)
- IsotopeProduct no longer shows attributes not available in product type
- Cart insert tags did not work
- Wrong check for existing attribute (Ticket #218)
- Updated address formats for Contao 2.9 (especially Great Britain)
- Address was not checked when forwarding in checkout module
- Disable grouping in mail template languages
- mod_iso_productlist template contained a CSS ID instead of a class
- Authorize.net delimiter value ignored (Ticket #227),
- Added x_country, x_phone, x_email_address to Authorize.net request (Ticket #228)


Version 0.2.beta1 (2010-08-25)
------------------------------
- Introducing IsotopeProductCollection, abstracting a collection of products (eg. cart, order, wishlist)
- Introducing IsotopeGallery class for creating custom image galleries
- Checkout condition data is now available in order confirmation mail (Ticket #103)

### New
- Product weight field and weight units
- Shipping module for price based on product weight
- New payment module for ePay (www.epay.eu)
- Hooks for altering product before output
- Payment and and shipping module can now provide additional info for each order (trough a button/operation)
- Limit panel to tl_iso_product_categories (Ticket #132)
- Jumpto page support when adding product to cart (Ticket #72)
- Batch invoice printing, by status
- Consolidation of order management global operations into a tools menu
- Backend order notes field
- Option to watermark medium and large images
- Store ID to group carts if you're using Isotope on multiple stores/domains
- (partial) translation for french and danish

### Improved
- New css classes for product list when using grid layout
- Surcharges can now be applied before calculating tax for a product
- Allow custom forms/data after the cart (eg. Coupon input)
- cart + order table are now storing the variant ID instead of base product ID
- MediaManager now standardizes file names to prevent file system issues
- Do not add an MD5 unique key to filename if not necessary.

### Fixed
- overwriting of authorize payment information used for delayed capture upon non-successful transaction
- reference to a undeclared deprecated method in PaymentAuthorizeDotNet
- reference to the cart in Authorize.net - $this->Cart now referred to as $this->Isotope->Cart
- Images are now updated when selecting a product variant
- Null array bug in ModuleIsotopeProductFilter
- Null object bug in IsotopeCart
- Disabled deprecated product storage in checkout module - now copies only product options to order items table.
- Changed all price fields to use decimal(12,2) (payment gateways require 2 digit precision)
- Table naming is not consistent, renamed tl_cart,tl_cart_items to tl_iso_cart,tl_iso_cart_items (Ticket #117)
- Paypal expiration dates now separate select widgets to ensure proper date format (Ticket #162)
- Authorize.net expiration dates now separate select widgets to ensure proper date format
- Javascript error in Internet Explorer 6+7
- Invoice title not showing up in iso_invoice.tpl if no logo image set
- Variant option attributes are no longer shown in base product edit mode
- Product list columns now work with MySQL 4.1
- Only images should be uploaded trough the media manager

### Other
- Removed Paypal Website Payments Pro option - use Paypal Payflow Pro or Paypal Standard
- Removed generatePrice() function, stpl_price and stpl_total_price templates. Now using formatPriceWithCurrency
- Removed old weight field, updated shipping modules to take advantages of new weight calculation
- Removed taxTotalWithShipping simple token


Version 0.1.3 (2010-07-05)
------------------------------

### Fixed
- InsertTags could output HTML in meta description
- Status labels were not available in order history
- sending email as text only did not work (Ticket #37)
- Javascript sorting of images did not work
- No products and no breadcrumb show up if filtered page does not have a product (Ticket #115)
- multiple URL problems in address book module
- multiple URL and template problems in shopping cart module
- bug in runonce.php, must not use TRUNCATE to empty cart table


Version 0.1.2 (2010-06-20)
------------------------------

### Fixed
- Backend variant listing showed unassigned attributes (Ticket #135)
- problem with payment modules accepting only two digit floating point precision (Ticket #136)
- wrong grand total price in cart module (Ticket #137)
- variant price in cart not correct
- a possible bug with attributes not available in the template
- multiple label issues (Ticket #78, Ticket #148, Ticket #150)

### Other
- Moved product tax class field to pricing legend (Ticket #134)
- Now using domready event to submit payment checkout forms (Ticket #145)


Version 0.1.1 (2010-06-06)
------------------------------

### Improved
- Customer email is now sent to member email if no email in address book

### Fixed
- Countries and shipping methods for payment modules are no longer mandatory
- Subdivisions for shipping modules should now work correctly
- Tax was not applied to flat price shipping
- Final price for paypal standard payment module did not include taxes
- Latest order did not appear in order history module
- Checkout condition form did show invisible fields
- Bug with minimum/maximum order total calculation.
- missing price calculation function in Order Total shipping (Ticket #112)
- old image size message (Ticket #119)
- wrong template name for config switcher module
- product alias not generated correctly when alias field is before name (Ticket #123)
- template bug in iso_cart_full (Ticket #129)
- problem with checkout in Internet Explorer 6 (Ticket #128)


Version 0.1.0 stable (2010-05-23)
------------------------------
- Initial stable release
