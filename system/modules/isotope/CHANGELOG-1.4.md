Isotope eCommerce Changelog
===========================


Version 1.4.beta1 (2012-??-??)
------------------------------

### New
- New filter module "Cumulative filter"
- Merged the zoom gallery into Isotope eCommerce core
- Added support for frontend-only attributes
- Added new attribute type "upload"
- Can now manage order status in the backend and notify on status changes
- Support for i18nl10n extension
- The backend now lists new orders on the welcome screen (#2111)
- Ever product can now have a CSS class and ID (#2812)
- Rules can now be limited to subtotal in cart
- Can now filter for serialized data (e.g. checkboxes)
- Change page title to reflect current step in checkout module (#2085)
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
- Added Contao 2.11 image crop modes to gallery size configuration

