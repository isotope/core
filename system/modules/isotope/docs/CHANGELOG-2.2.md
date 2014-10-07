Isotope eCommerce Changelog
===========================

Version 2.2.0-rc1 (2014-10-07)
--------------------------------

### New
- Added the an integrity check to check for attribute option orphans (#1237)
- Added price surcharges for attribute options (#1165)
- Added module to set address before checkout (e.g. for price or shipping calculation)
- Added shipping calculation module (#1187)
- Added condition to payment and shipping restriction on product type
- Added inline edit option for advanced prices
- Added payment method for Billpay (using Saferpay gateway)
- Payment and shipping methods can now handle on order status updates
- Saferpay payment method can now capture/cancel payment on order status change

### Improved
- Performance when finding the correct jumpTo page (#1104)
- Redirect message for payment methods when JavaScript is disabled (#1079)
- Better error logging on invalid PayPal email (#1252)
- Allow to pass attribute options in product collection template

### Fixed
- Warning when product attribute had no options
- Address ID was not correctly update in product collection (#1263)
- Show labels from attribute option table in backend filters (#1253)
- Attribute options from manager were not filterable in the frontend (#1253)


Version 2.2.0-beta1 (2014-09-02)
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