Isotope eCommerce Changelog
===========================

Version 2.2.0-beta1 (2014-??-??)
--------------------------------

### New
- Integrated payment provider Paybyway from Finland (https://www.paybyway.com)
- Added media player (audio/video) attribute (#1072)
- Added interfaces for attributes with options and variant attributes
- Added placeholder and minlength options to attributes (#1014)
- Payment and shipping methods can be restricted to shop configurations (#1173)
- Galleries can now have custom templates (#1194)
- Dropped support for Contao 3.2 (#1206)
- Added the {{formatted_datetime}} InsertTag to format timestamp values (#1195)
- Isotope shop configuration template folders are now displayed the same way as in Contao core (#1191)
- Support for custom template for every Isotope front end module (#1191)

### Improved
- Product options are now available in calculatePrice hook (#1000)
- Show a hint about module configuration when manually sorting products (#1178)
- No need to add an order details front end module to see order details in back end anymore
- Show source collection (cart) ID in the order backend view (#1208)
- Use a template to generate Sparkasse payment URL (#1207)
- Switched to the new Sparkasse payment URL (#1143)