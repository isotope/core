Update from Isotope 1.3 to Isotope 1.4
======================================


### Products ###

  * Product group permissions

    You can now limit the access to product groups. An outcome of this new feature is that you can't have any product assigned to no product group
    at all anymore. The update will take care of that for you.


### Frontend CSS ###

  * Addresses are now formatted using hCard markup. You might need to adjust the checkout review step using CSS.

  * In 1.3, Isotope automatically added the class "trail" to pages in the navigation module, if that page belongs to the currently active product.
    This caused issues because it was not possible to separate real trail and Isotope products. The class has been renamed to "product", you might need to update your CSS.


### Templates ###

  * The following templates have been adjusted since 1.3. Make sure you update them:

    - mod_iso_productlist


### Language Files ###

  * The address formatting has been completly rewritten. If you adjusted your address formatting, you must update your file. Isotope is now using Simple Tokens instead of Insert Tags.


### Hooks ###

  * The parameter list of "addCustomAddress" hook has changed! The third parameter is now the default address, the module ($this) has moved to the fourth position.


### Email Templates ###

  * Placeholders ##shippingTotal## and ##taxTotal## have been removed. They never returned useful values.
