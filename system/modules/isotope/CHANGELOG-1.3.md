Isotope eCommerce Changelog
===========================

Version 1.3.7 stable (2012-09-10)
---------------------------------

### Fixed
- Javascript was displayed in raw format when using the inherit widget
- Language bug when rebuilding the search index
- Setting default billing or shipping address did not work for new addresses
- AttributeWizard was not correctly displayed
- Better permission check for product type access (#3372)


Version 1.3.6 stable (2012-08-21)
---------------------------------

### Improved
- Do not show cart merge warning if member cart is empty

### Fixed
- Rebuilding the search index did not consider the language fragment
- Wrong address title when no shipping was required (only download products)
- Convert relative URLs to absolute in email templates


Version 1.3.5 stable (2012-08-06)
---------------------------------

### Improved
- The first shipping & payment option was not always selected in checkout module (#3206)

### Fixed
- Error message when using a system column name for an attribute did not work
- The visible toggle only worked once and triggered overload detection
- SKU did not show up in the backend product list
- Back link in label translator did not work (#3309)
- Single checkbox container was not disabled in inherit wizard (#3318)
- Displaying multiple Isotope messages did not work (#3316)


Version 1.3.4 stable (2012-07-12)
---------------------------------

### Improved
- Better error handling in the runonce script

### Fixed
- Filter module did not work correctly (#3291)
- Product options did now show up in order details if the product has been deleted (#3288)


Version 1.3.3 stable (2012-07-09)
---------------------------------

### Improved
- Resolved potential layout issue when using advanced prices
- Do not show rule discounts for low_price when rule is restricted to product variants (#3189)

### Fixed
- Added missing german translations (#3226 & #3284)
- Fixed a slight possibility to generate duplicate order IDs
- Grand total label for backend is now using the correct translation (#3270)
- Custom sorting did not work when using a lister reference page
- Attribute rule filter could be incorrectly applied


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
