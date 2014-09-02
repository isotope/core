Isotope eCommerce Changelog
===========================

Version 2.2.0-beta1 (2014-??-??)
--------------------------------

### New
- Added the Integrity Check tool to check for database inconsistency
- Integrated payment provider Paybyway from Finland (https://www.paybyway.com)
- Added media player (audio/video) attribute (#1072)
- Added interfaces for attributes with options and variant attributes
- Added placeholder and minlength options to attributes (#1014)
- Payment and shipping methods can be restricted to shop configurations (#1173)
- Galleries can now have custom templates (#1194)
- Dropped support for Contao 3.2 (#1206)
- Added collection_* Simple Token for orders (#1195)
- Support for custom template for every Isotope front end module (#1191)
- Cart does no longer show shipping and payment surcharges (#1055)
- An order collection is created and locked when "buy now" button is pressed

### Improved
- Product options are now available in calculatePrice hook (#1000)
- Show a hint about module configuration when manually sorting products (#1178)
- No need to add an order details front end module to see order details in back end anymore
- Show source collection (cart) ID in the order backend view (#1208)
- Use a template to generate Sparkasse payment URL (#1207)
- Switched to the new Sparkasse payment URL (#1143)
- Isotope shop configuration template folders are now displayed the same way as in Contao core (#1191)
- The backend shop config intro is now responsive (#1221)
- Load page and language environment when sending order notification from backend (#1242)
- Load page and language environment when generating PDF (related to #1242)