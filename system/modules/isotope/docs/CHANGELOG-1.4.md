Isotope eCommerce Changelog
===========================


Version 1.4.2 (2013-07-09)
--------------------------

### Improved
- Added title attribute for product list detail links (#621)
- Show error message if someone tries to add an attribute with name "minimum_quantity" (#628)
- Shop config address country is default billing of none is selected (#600)
- Recursively parse inserttags and simple tokens in mail templates (#646)
- Can now select the default sorting in the filter module too

### Fixed
- Unsuccessful orders could show up in the backend (#607)
- Edge case where price tires could show up as minimum price
- Order status for Authorize.NET payment method was buggy (#650)
- Date/time formatting did not use page object (#634)
- Postsale script broke some custom extensions (#643)
- Attribute rules for less-than-equals, starts-with and ends-with did not work
- Attribute rule should only sum up products that match the condition (#633)
- deleteAll in tl_iso_orders could remove unwanted orders (#597)
- Advanced price calculation failed when no variants were found (#644)
- Restore basic entities in mail templates (#646)
- File uploads could be attached to more than one product (#614)
- Could not use two filter modules at the same time (#595)
- Sorting options were not correctly selected
- Order status was reset to shop config value instead of payment method (#623)


Version 1.4.1 (2013-04-15)
--------------------------

### Improved
- Product object is now also available in the product list template
- Truncate product cache when using variants quick edit (#586)
- Pass product reader link to the active breadcrumb item (#579)
- Do not automatically set order to failed through PayPal API (related to #580)
- Shop config is now available in mail templates (##config_xxx##) (#572)

### Fixed
- Sending e-mails did not work in PHP < 5.3 (#576)
- Minimum order amount was incorrect when lowering cart amount (#587)
- Declaration of ModuleIsotopeRelatedProducts::findProducts() should be compatible with ModuleIsotopeProductList (#575)
- Wrong URL in the Google XML sitemap (auto_item) (#581)
- Member-restricted rules did not work with postsale script (#580)
- Order conditions form was not always validated (#591)
- Fixed array_merge() error in address book module (#593)
- Option "redirect to first product" in product list did not work (#583)
- Postfinance payment did not use UTF-8 interface (#589)
- Search index was blocked when Isotope module was on the page (#594)


Version 1.4.0 (2013-03-18)
--------------------------

### New
- Standalone front end module to display Isotope messages (#101)
- Added all missing german translations (#567)
- Added option to prevent negative taxes (#536)
- Added minimum product quantity support (through advanced prices)

### Improved
- Added short name for known "Krone" and "Franc" currencies
- noFilter message in product list is now generated inside the productlist template (#530)
- Automatically set payment date when order status is set to completed
- `getAllowedProductIds` hook can now override "allow all" permissions
- Added checkbox to hide filter option when there is just one option (#42)
- Empty message is now rendered inside product list template (#543)
- Add default order status when installing Isotope eCommerce (#558)
- Do not generate options container if no frontend attributes are available (#533)
- Base price label can now be translated
- New order status can also be set for cash payment method (#571)

### Fixed
- Price was 0.00 if there is only one product variant
- Access rightis on advanced price were not correctly handled
- preOrderStatusUpdate and postOrderStatusUpdate were not triggered correctly
- Labels for addresses were not shown in the order details (#538)
- Do not show filter options of product variant if base product is disabled (#529)
- Invoice PDF did not consider shop config template folder (#534)
- Product translations were not correctly fetched in variants (#465)
- Custom mediamanager widgets now work again (#555)
- Default tax class was not applied for advanced prices (#553)
- Default group was not created when groups existed (#554)
- Allow users to create groups in the root node (#535)
- Potential issue when generating sitemap XML file (#556)
- Product search did not consider translated fields (#551)
- Removed session storage for backend, it does not work :-(


Version 1.4.rc2 (2013-01-22)
----------------------------

### New
- Tax rates can now be applied to multiple countries
- Added hook to check for product access permissions
- Removed PaypalPayflowPro and Cybersource payment methods. They were not maintained anymore.
- PayPal Standard payment now shows additional information in the order payment info

### Improved
- Better german translations
- Payment module will send status email if configured
- Added default value for base price label field
- Show "filesOnly" attribute option only if "files" is checked (#481)
- Added german translations for download attribute fields (#480)
- Prevent access to other users' order when logged in (#126)
- Load page object on postsale order checkout if necessary (#123)
- Price tiers were not calculated through shop config (#431)
- Can disable shipping address in shop config (#129)
- Do not show filter options with just one option (#42)
- Filtered options are now passed as pre-selections to the product reader (#42)
- Do not filter products if everything is allowed (#125)
- Added support for list->sorting->filter in DC_ProductData
- Invoices are now created in the user's language (#524)

### Fixed
- Attributes were not translated in filters etc.
- Payment modules did not correctly handle the auto_item parameter (#113)
- ProductList-noFilter message is shown when keywords are used (#120)
- Replace inserttags in order prefix when generating unique ID (#510)
- Company and name was on same line in textual address representation (#127)
- Labels for rule minimum subtotal fields (#491)
- Exception when manually sorting products in the backend (#124)
- The filter module did not work on the index page (#42)
- Fixed missing parameter "$target" in watermarkImage hook (#67)
- Fixed using $this in static method in IsotopeFrontend::loadPageConfig()
- Media data was not correctly merged from fallback language (#465)


Version 1.4.rc1 (2012-12-03)
----------------------------

### New
- New attribute option to add date picker for frontend and backend
- The Contao core breadcrumb module now tries to show the correct trail on a product reader page
- You can now optionally set a default product type per product group
- Added option to limit tax rate to guests/member groups (#65)
- Added method to purge a product collection
- Added support for the auto_item parameter (#74)
- Can now upload multiple images and supports custom uploaders (#75)
- Added VAT no field to addresses and store config (#100)
- Download can be all files from a given folder
- Use web storage to improve speed of the backend view

### Improved
- Do not cache or search-index a page if user-centered content (filters, cart, checkout) is on it
- Changed navigation CSS class for active product from "trail" to "product" (#49)
- Sorting label for dates now work better (#72)
- Base prices are now calculated by amount and not a factor
- Added CSV support and autofocus in DC_ProductData (#103)
- Pass the product collection to cart, review and order history templates
- Variant downloads are now inherited from parent product (#94)
- Better performance for the backend product view

### Fixed
- Product was hidden if price was not an attribute
- Tooltip was not available when product view was loaded with ajax in the backend
- Caching bug when category scope was "article"
- PayPal checkout threw an exception because of the IsotopeAddressModel
- Variants were not editable for regular users (permission issue)
- Default values for image sizes got lost in the MCW migration (#61)
- Regular users could not add/edit product variants (#60)
- Order id prefix was limited to 5 chars which made usage of insert tags impossible
- Publishing toggle did not work in ajax mode (#77)
- Value field for attribute restrictions in rules was not visible


Version 1.4.beta2 (2012-09-24)
------------------------------

### New
- Can now apply an autocompleter on the search field of a product filter module
- Addresses are now formatted as hCard in the frontend
- Can now list products of a page if it's article is included (e.g. using inserttag) (#3343)
- Added permissions to product groups. You can no longer have products outside of a group.

### Improved
- Using MultiColumnWizard for attribute options
- Hide "default" and "group" checkboxes for variant options
- Added default address parameter to "addCustomAddress" hook (#2142)
- New simple tokens ##id# and ##status_id## for order email (#3035)
- Process input before generating attributes (#2639)

### Fixed
- Rules for cart subtotal was not correctly applied
- Price tiers showed as "from" price even if not enabled
- Fallback image was not rendered
- Shipping module palette was broken due to Chosen selects
- Added new order status fields to exclude list (#3366)
- getOrderEmailData hook was not working correctly


Version 1.4.beta1 (2012-08-24)
------------------------------

### New
- Can now manage order status in the backend and notify on status changes
- Added support for base price calculation
- The backend now lists new orders on the welcome screen (#2111)
- Option to show checkout conditions before products (german "checkout button" law)
- Merged the zoom gallery into Isotope eCommerce core
- Merged isotope_multilingual into Isotope eCommerce core
- Added new attribute type "upload"
- New filter module "Cumulative filter"
- Ever product can now have a CSS class and ID (#2812)
- Exempt all products of a certain type from shipping (#3148)
- Can now filter for serialized data (e.g. checkboxes)
- Rules can now be limited to subtotal in cart
- Support for i18nl10n extension
- Added support for the eval->path attribute (#3342)
- Change page title to reflect current step in checkout module (#2085)
- Added generateCollection hook (#27)
- Added getOrderEmailData hook to add custom simple tokens to order emails
- Added support for frontend-only attributes
- Added generateProductList hook (#17)
- Use multiColumnWizard for all appropriate fields (#2038)

### Improved
- Checkout steps are now a numbered list (#3040)
- Product list can be sorted by dateAdded, this allows to list the newest products
- More modular gallery class, also allows to retrieve the list of images (#3173)
- Added getimagesize data to gallery template (#3173)
- Added edit-header dropdown for mail templates (#3228)
- Allow insert tags in order prefix (#3197)
- Hide "related products" button if there are no categories (#3018)
- Removed filter-by-node functionality, it's no longer useful (#2766)
- No longer shows the "create new address" radio for billing address if no stored address is available
- Insert tags can now be excepted from cache (#3098)
- The payment and shipping order info is now a context menu
- Include file extension when generating download attribute
- generateProduct hook no longer requires template as return value
- Replaced <section> tags with regular <div> tags.
- Disabled forward/back buttons on click in checkout module to prevent a second submit (#3328)
- Use "-" as value to add a variant option that should not be shown in the frontend
- Added Contao 2.11 image crop modes to gallery size configuration

### Fixed
- The watermarkImage hook did not pass the image position
