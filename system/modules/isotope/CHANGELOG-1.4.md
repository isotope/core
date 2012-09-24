Isotope eCommerce Changelog
===========================

Version 1.4.? (2012-??-??)
------------------------------

### New
- Can now apply an autocompleter on the search field of a product filter module

### Improved
- Using MultiColumnWizard for attribute options
- Hide "default" and "group" checkboxes for variant options

### Fixed
- Rules for cart subtotal was not correctly applied
- Price tiers showed as "from" price even if not enabled
- Fallback image was not rendered


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
