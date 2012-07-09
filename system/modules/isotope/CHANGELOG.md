Isotope eCommerce Changelog
===========================

Version 1.3.3 stable (2012-07-09)
---------------------------------

### Improved
- Resolved potential layout issue when using advanced prices

### Fixed
- Added missing german translations (#3226 & #3284)
- Fixed a slight possibility to generate duplicate orrder IDs


Version 1.3.2 stable (2012-06-11)
---------------------------------

### Improved
- Surcharges with percent as price were not rounded
- Prevent the filter from generating useless "isorc" params
- Plain text emails could contain HTML when using InsertTags (#3172)
- Added some german labels
- Hide the "invoice logo" option in store config, if the server does not support it (#3124)

### Fixed
- Multiple CC and BCC recipients on emails templates did not work
- Fixed medium size width & height in inline gallery
- IsotopeAutomator::convertCurrencies function was not working (#3141)
- Palette selectors not working correctly in rules (#3138)
- Authorize.net payment module not proceeding to review (#3223)
- Price tiers did not show "from"-price


Version 1.3.1 stable (2012-04-30)
---------------------------------

### Improved
- misspelling in german language files
- updated english language files (#3083)

### Fixed
- The gallery lightbox did not work in Contao 2.11 (#3079)
- Show templates from theme and store config folders in mail template configuration (#3080)
- IsotopeProduct doesn't support AJAX calls from Content Elements (#3095)
- the third parameter to postCheckout hook could contain wrong data (#3100)
- potential error message in frontend preview (#3093)
- the filter template did not have a button label on javascript fallback (#3113)
- potential exception on product sorting view in the backend (#3094)
- The inherit wizard caused javascript errors when no tooltip was given on a field (#3092)
- Advanced pricing did not work on product variants (#3052)


Version 1.3.0 stable (2012-04-04)
---------------------------------

### Improved
- PayPal Standard does no longer override the address in debug mode. It could cause issues with international addresses in the sandbox.
- Use the BYPASS_TOKEN_CHECK constant for postsale script
- Write log protocol when deleting guest cards
- Updated palettes and legend labels in tl_module (#2975)
- Now uses the "tableless" setting of the form generator in checkout module's order review form
- When member countries are limited and only one result is available, set it as the default field value.

### Fixed
- Do not initialize a cart when running the Contao cron job
- Force type comparison when checking for BE_/FE_USER_LOGGED_IN (fixes #2976)
- The product cache caused an endless loop when no results were found
- Missing whitespace between HTML tags
- ID attribute for quantity_requested was incorrect in list templates
- Incorrect usage of "rowClass" in mod_iso_addressbook templates
- Missing address labels are not hidden, leaving empty h2 tags in mod_iso_addressbook templates
- Default county is not set in Address Book edit mode for new address entry form
- Fixed warning when product was not assigned to any category (#2998)
- The back button in shop config list did not work
- Address book module did not correctly handle default address checkbox (#3000)
- Flat shipping price was not calculate correctly (#3010)
- PayPal Standard payment did not support negative amounts (discounts) (#3014)


Version 1.3.rc2 (2012-03-19)
----------------------------

### New
- Added hook generateOrderId (#2841)
- Added hook addAssetImportRegexp (#2744)
- Added hook transferredCollection (#2865)
- Added new Isotope::calculateSurcharge() function. Can generate surcharges with tax, without tax or with tax splitted according to collection products!

### Improved
- Product sorting is now case-insensitive
- Hide "new" button when pasting product or variant
- German translation
- Reordered category scope options in module settings
- Include store config template folder in template selection (#2786)
- Load form data containers in IsotopeFrontend::prepareForm
- Improvement to IsotopeGallery::generateMainImage() and InlineGallery::generateGallery() html rendering of css selectors for image and gallery containers (#2771)
- Added "pages" and "inherit" to the list of core fields that cannot be overwritten in attributes (#2843)
- Added support for exception error message in save_callback for IsotopeProduct options
- Pass checkout module when executing IsotopePayment::checkoutForm() (#2858)
- Core product attribute "SKU" should ben search-enabled
- Renamed tl_iso_orders.date_payed to date_paid (#2275)

### Fixed
- Pasting new product variant into root node (#2767)
- Product variant could not be created (#2769)
- Editing multiple products did not work (#2745)
- Keep requested quantity on product error (#2772)
- Rules could not be deleted (#2798)
- array_multisort error message when search matched no product
- Bug in tax calculations that would not calculate tax on both billing & shipping addresses.
- Bug in sitemap.xml generation that would include product urls from a different root page
- Possible error message with date validation (#2917)
- tl_iso_productcache is emptied every time loading a product list module (#2897)
- Coupons are now transferred from guest to member cart on login (#2865)
- Potential array issue with member groups (#2870)
- Products Search Indexing would fail if page root=0
- trim extra backslash from URLs returned in Products Search Indexing
- The sorting for Order ID was not correct (#2871)
- Duplicating an attribute caused an empty field to be added in database.sql (#2955)
- saveNcreate did not work in ModuleIsotopeSetup (#2933)
- new product was not created in the selected group
- possible error message when page trail is empty (#2797)
- sorting of customer defined and variant attributes was incorrect (#2829)

### Other
- Introducing the "provider" directory for complex callback handlers


Version 1.3.rc1 (2012-01-02)
----------------------------

### New
- new product attribute "File tree"
- new product attribute "Downloads"
- "continue shopping" feature (Ticket #295)
- new function IsotopeFrontend::prepareForm() to ease including custom forms created by the form generator
- CSS class "trail" to product category pages when in reader mode (Ticket #76)
- native SQL filtering for static attributes (#2045)
- SQL index keys for filtered attributes (#2134)
- IsotopeEmail class to improve email handling (#2158)
- frontend preview button when in product category (page) view
- "dateAdded" for product so we know what's new (#2054)
- "advanced filters" for backend product list
- "hasImages()" method to the gallery to ease checking for existance of images
- deferred (lazy) loading of product data if backend is overloaded
- option to force displaying variants of a product type (#2232)
- new function IsotopeFrontend::generateRowClass() to add useful CSS classes to any array
- "empty message" option to cart module (#2197)
- indexing of products for the Contao search module (#2105)
- automatic currency conversion routines (including Hooks for data providers)
- new hook generateFilters (#2146)
- new hook saveCollection
- inserttag to retrieve data of the current product or a certain product id (#2047)
- support for postal code ranges in flat shipping module
- Permission settings now available for all Isotope backend modules
- Payment and shipping methods can be sorted in checkout module (#2705)

### Improved
- loading products is now centralized in IsotopeFrontend static functions
- "Loading product data" is now in the language file (#2215)
- Removed size and added maxlength attribute to quantity input fields (#2118)
- updated currency list according to ISO 4217 (#2082)
- the reader page id is now set in the site structure but you can still overwrite it per module (#2105)
- the rounding increment can now also be applied for tax classes (#2505)
- the rounding increment is now applied on the grand total too (#2341)
- Make Company Details accessible in Invoice Template (#2239)
- Add uniqid to order mail wildcards (#2531)
- disable checkout navigation on payment and processing steps (#2529)
- start and stop date are now fixed fields, available on all product types
- Can now cut & paste multiple products between groups (#2229)
- Provide insert tags on order confirmation page (#2260)
- Simplified order insert tags code
- Addresses can now optionally be labelled by the customer (#2051 and #2052)
- The isotope folder now gets generated on-the-fly (#2044)
- SKU must now be unique
- Tax rates now have more flexible postal code settings
- make arrCache data available in product template (#2340)
- Duplicating a product will sort it at the bottom of a category (#2618)
- Now generates an error message if a coupon code could not be applied
- Can now multi-edit all features of a product. Can not multi-edit variants anymore (#2209)
- The attribute wizard now supports any kind of CSS class (e.g. w50 for half width)  (#2227)
- The attribute wizard now allows the user to specify which fields ar mandatory

### Fixed
- radio button events in Internet Explorer (Ticket #484)
- products DCA not loaded in rare cases
- hide customer definied fields in product edit mode
- low price calculation for advanced prices
- sorting by module configuration (#2037)
- backend category sorting, updated DC_TablePageId driver (#2101)
- invisible subgroups when no product in parent group (#2121)
- potential issue in email addresses (#2041)
- invalid markup in several templates (#2081)
- related products loaded from product cache (#2236)
- product cache not considering product start date (#2230)
- issue with variant generator and prices (#2046)
- copy/paste of product variants into its own product
- order of country and states (#2486)
- replace $ in mootools with document.id for isotope.js and backend.js (#2200)
- assignment of uniqid in ModuleIsotopeCheckout::writeOrder() (#2531)
- Added missing comma after "sortBy" field in tl_iso_attributes
- Checkout gives an error on single quotes (#2576)
- Shipment template does not show forms (#2479)
- Sorting is ambiguous in ModuleIsotopeProductList.php (#2697)
- Non-Admins cannot create a product type (#2261)
- Order Id doesn't get incremented (#2222)
- SQL condition in product list is wrong (#2189)
- "Edit multiple" does not work in Internet Explorer (#2680)
- Invoice PDF had no title when printing (#2036)
- Isotope::formatValue will throw error if not page category selected on a product (#2699)
- Coupon code usage was not saved in database (#2656 + #2679)
- Old guest cart was not available on login until after a reload (#2684)

### Other
- Dropped unimplemented enableGoogleAnalytics checkbox in store config (#2102)


Version 1.3.beta2 (2011-08-28)
------------------------------

### New
- runonce script will automatically generate groups based on categories (pages)
- support for multilingual foreignKey in attribute table
- a salutation text field to addresses (Ticket #329)
- invisible hint to active step in checkout module (Ticket #331)
- new rule product conditions to limit by product attribute
- option to invert the rule condition (match/match not)
- product list caching with intelligent self-configuration to display a "please wait" message only when necessary
- caching tables to the maintenance cleaing options
- .html5 & .xhtml templates for Contao 2.10 (Ticket #457)
- checkout stepcount classes (Ticket #491)
- consistent surcharge classes to cart, checkout and order details (Ticket #494)
- "hide in reader mode" option to product listing
- SQL condition field to product listing (Ticket #114)
- automated table/list rendering option for array attributes
- price tiers to product template (Ticket #466)

### Improved
- Allow subclasses of ModuleIsotopeProductFilter for filter modules
- Allow subclasses of ModuleIsotopeProductFilter to cache request
- new function generateRequestUrl to remove GET-parameters from request string
- backend product list, no more "limit" option but can search & filter through all products and variants and translations (Ticket #495)
- error logging in postsale script (Ticket #390)
- PostFinance postsale check

### Fixed
- a bug in runonce script preventing the database file from being refreshed
- a bug with Contao 2.10 and the new foreignKey implementation
- product translations not working (Ticket #448)
- quotes in "remove from cart" title (Ticket #478)
- request token issues in Isotope javascript class
- template search in theme folders (Ticket #467)
- "redirect to first product" not working (Ticket #481)

### Other
- Started using static functions if they don't need the class (see IsotopeFrontend)


Version 1.3.beta1 (2011-07-18)
------------------------------

### New
- Added "advanced prices". Allow to define a prices for store configs, member groups, start/stop dates
- availability check for products
- new "field wizard" to set up address fields with custom label and mandatory option in store configuration
- option the define a custom template folder for each store configuration
- store config option to limit tl_member countries to billing and shipping countries
- new IsotopeOrder class, checkout can now be done from postsale request
- button to duplicate the fallback language to a language record
- multi-edit support for products and variant data
- minimum order subtotal amount (store config option)
- sku to email product template
- iso_checkout_order_conditions template
- productTree widget, similar to pageTree but for products/variants
- "tableless" option for checkout module (Ticket #325)
- ability to apply a rule to a product variant
- Copy child records (variants, languages) when copying a product
- Allow foreignKey for variant attribute options
- Show theme templates in product type select menu
- System messaging support (Ticket #13)
- Attach optional PDF invoice document to order mails (Ticket #9)
- Digits (min. length) for order number
- The Order ID is starting from 1 when changing the prefix
- "Edit Multiple" to Order Management (Ticket #439)
- Use cart template for empty message (Ticket #427)
- Images and image descriptions can be translated (Ticket #401)
- IsotopeTemplate now supports Contao 2.10 template types (Ticket #456)
- Support for subqueries in foreignKey field
- Show email data (including notes for orders) in backend order details (Ticket #103)
- Implemented Contao 2.10 request tokens in Isotope (Ticket #470)
- product groups, allow to categorize products in the backend

### Improved
- Totally rewrote filters, allow to apply (multiple) from-to filters, language- and variant-aware sorting (Ticket #230)
- Better Isotope module handling, including callback support
- Moved multilingual support to its own extension "isotope_multilingual" with lot more options
- Hide payment information if checkout total is 0
- Authorize.net failed transaction response handling & log data
- Better usage of IsotopeBackend class
- Delay loading of subdivions against memory issues (Ticket #223)
- Updated ePay form payment to use a template
- Show customer address on ePay backend
- SQL key/index optimizations
- number of SQL queries
- Better formatting to attributes list. Show attribute name in product type setup wizard.
- Show field label not value in quick edit mode
- Show "invisible" icon for products with start/stop date outside today
- More unique element IDs to load ajax into the right container (Ticket #291)
- Use <div> not <span> for attribute containers (Ticket #203)
- Only create cart in database if necessary (will save a lot of auto_increment IDs)
- Blair has rewritten Authorize.net's payment calls
- Related products & quick products now use the product tree and allow for variant selection
- All product collections can be printed to HTML/PDF
- Addresses are now linked to the store config ID
- Drag & Drop of product sorting in categories
- Additional CSS classes for product list (Ticket #432)
- Moved $GLOBALS['ISO_PERPAGE'] to filter module setting (Ticket #70)
- Removed product type setting for languages. If isotope_multilingual is installed, all language will appear as an option.
- Include old and new item ID in transferFromCollection return value
- Empty list message checkbox behavior changed (Ticket #414)
- Hooks for Isotope eCommerce are now in ISO_HOOKS array
- Labels for Isotope eCommerce are now in ISO_LANG array
- New icon theme, based on fugue icons (http://p.yusukekamiyamane.com/) (Ticket #422)
- Deleting orders when deleting old carts
- Ignore quotes when sorting products (Ticket #77)
- Auto-set fallback checkbox if there is no fallback store config (Ticket #458)
- Renamed attributes for frontend & backend filter, sorting and limit fields
- Removed styles from address book. Added class for default billing/shipping address (Ticket #207)
- Checkout steps now follow Contao navigation scheme

### Fixed
- start & stop publishing dates not working
- Authorize.net failed transaction bug
- enter key trapping on checkout payment method

### Other
- Removed pre-0.1 update code from runonce.php
- Removed postsale_mail from Paypal and Postfinance
- Removed obsolete table tl_payment_options
- Removed obsolete table tl_filter_values_to_categories
- Removed moduleOperations callback from payment and shipping modules. The same can be achieved through standard DCA operations.
- Moved ePay payment modules to own extension


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
