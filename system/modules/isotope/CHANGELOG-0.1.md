Isotope eCommerce Changelog
===========================


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
