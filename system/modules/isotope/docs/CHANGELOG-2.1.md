Isotope eCommerce Changelog
===========================

Version 2.1.8-stable (2014-10-01)
---------------------------------

### Fixed
- Restore basic entities in payment methods (#1256)
- Images with same name did overwrite previous uploads (#1247)
- Multiple upload issues on IE browser (#1238)


Version 2.1.7-stable (2014-09-01)
---------------------------------

### Fixed
- Runtime notice on the 2.1.6 upgrade (#1233)
- Recoverable error when viewing backend order details (#1234)
- Broken table (incorrect colspan) when product in collection had an error
- Issue in product type attributes manager when moving variant attributes (#1213)


Version 2.1.6-stable (2014-08-26)
---------------------------------

### Fixed
- Shipping methods included products exempt from shipping in per product and per item calculation mode (#1218)
- Not all product variants were available (#1215)
- New addresses were not added with the correct store ID after checkout (#1216)
- It was not possible to translate product images
- Order details should not link to unavailable products (#1232)
- Do not show products assigned to unpublished pages (#1231)
- TCPDF issue for documents on Contao 3.3 (#1210)


Version 2.1.5-stable (2014-08-11)
---------------------------------

### Improved
- Replace inserttags in backend order view to support 3th-party extensions (#1186)
- Cart was not available in postsale process (#1196)
- Allow to pass addresses to Isotope::calculatePrice for correct tax calculation (#1196)

### Fixed
- Orders were tracked twice in Google Analytics (#1184)
- Google Analytics eCommerce must have a product SKU (#1181)
- New product collections were reported as locked (#1190)
- Asset import could result in uppercase folder names (#1204)
- The backend setup welcome legend was not translatable
- Minimum product quantity was not applied when only one price tier was set (#1183)
- Multiple advanced prices could result in incorrect from price (related to #1183)
- FORM_DATA session was overwritten by checkout condition form (#1211)


Version 2.1.4-stable (2014-07-21)
---------------------------------

### Improved
- Product group view breadcrumb was not working, removed because it does not provide any useful functionality

### Fixed
- Database update trying to add attributes with no internal field name to database
- Toggle group in popup picker did not work in Contao 3.3 (#1148)
- Datepicker icons were missing in reports module in Contao 3.2 (#1161)
- Invalid date input caused exceptions (#1162)
- Creating attributes in DCA caused fatal error (#1166)
- Invalid input in related products field caused database exception (#1158)
- Allow payment modules to change postsale parameters "mod" and "id" (#1137)
- Improved URL input handling to generate 404 pages when using folder URLs (#1131)
- Order status was not always updated correctly in the backend (#1172)
- Product downloads were not counted so the limit was not applied (#1164)
- Total sales summary was always the last value in the column (#1163)
- Sales total chart was broken when reporting days within multiple months (#1163)
- Address book module did not display widget errors (#1176)


Version 2.1.3-stable (2014-06-30)
---------------------------------

### Improved
- Inherited fields can now be multi-edited

### Fixed
- Composer dependencies (#1124)
- Date added was not updated when copying a product in the back end (#1126)
- Another error when setting a redirect page for product search (#1068)
- Disable variant checkbox if no variant attributes are available in product type (#1114)
- Product variants did show up even if product was unpublished (#1120)
- URLs for Sparkasse and Worldpay payment methods were incorrectly generated (#1141 and #1142)
- CDATA statement in javascript caused issue in Sparkasse payment method (#1140)
- Front end javascript was not compatible with IE8 (#1155)
- Order of fields in product type was not applied to palette (#1135)
- Edit-multiple failed in Contao 3.3 (#1150)
- Copy fallback language was not working (#1151)
- From price was not shown for variants with price tiers (#1146)
- Incorrect tax free total in product collection template
- Tax free total for surcharge was always empty (#1149)
- Tax free total for surcharge was not available in collection template
- Tax free subtotal of order was shown as total in product collection template
- Collection template did not correctly display variant attributes in text mode (#1125)
- Payone cannot handle correctly URL encoded values (#1137)


Version 2.1.2-stable (2014-06-02)
---------------------------------

### Fixed
- Request cache did not consider variant filtering (#1100)
- Wrong column count in collection template again (#1091)
- Assets import calling to classes in wrong namespace (#1113)
- TypeAgent not being compatible with Contao 3.3 changes
- Product translations not being compatible with Contao 3.3 changes (#1109)
- HAVING support was incompatible with Contao 3.3 (#1112)
- Product filter removed all parameters except page instead of the opposite (#1098)
- DatePicker was not compatible with changes introduced in Contao 3.3 (#1119)
- Related products module could get hidden in edge case (#1090)
- Fatal error when setting a redirect page for product search (#1068)
- Prevent Contao from trying to load an empty tinyMCE config (#1111)
- Variants were not shown if product group filter was applied (#1097)
- Attributes were always initialized with array default value (#1021)


Version 2.1.1-stable (2014-05-20)
---------------------------------

### Fixed
- Payment and shipping method name was not shown in product collection (#1052)
- Reports did not show headline and panels (#1051)
- Single variant option was shown on ajax load (#1066)
- Price was zero if minimum quantity is more than one (#1058)
- Show tax class option in group price shipping method (#1064)
- Issue with weight calculation (#1074)
- Could not assign cumulative filters to product list module
- Images were not included in back end document generation (#1053)
- Attribute sorting was ignored in product type (#1083)
- Product filter did not redirect for search results (#1068)
- Dynamically generate header fields for downloads (#1088)
- "Save & New" created a new product instead of variant (#1080)
- Namespace issue in Saferpay payment module (#1089)
- Do not generate sitemap links for unpublished pages (#1092)
- Wrong column count in collection template (#1091)
- Remove page parameter on product filter action (#1098)


Version 2.1.0-stable (2014-04-01)
---------------------------------

### Fixed
- SQL error in product filter module (#1028)


Version 2.1.0-rc1 (2014-03-10)
--------------------------------

### New
- Do not allow to setup variant products if no such attributes are defined (#975)
- Product filter can redirect to a list page (#963)

### Improved
- Added missing <thead> and <tbody> tags to mod_iso_history template (#997)
- Added filter and search panel to product type back end (#987)
- Show error if no variant option is selected in product type (#975)
- Display of the order conditions form in the back end
- Improved variant option usability in product type
- Changed tokens for shipping and billing address (#958)
- Tableless condition form is now configured in the form generator (#978)

### Fixed
- Missing argument in new Google Tracking code (#992)
- Missing argument in new Google Tracking code (#992)
- Using $this in closure for lightbox choice (#988)


Version 2.1.0-beta1 (2014-02-07)
--------------------------------

### New
- Support InsertTags in WHERE condition in filter, list and variant list (#731)
- Set colors for order status to highlight them in the backend view (#781)
- Optionally require login to see member order details (#930)
- Automatically update currencies when changing store config settings (#625)
- Track orders using Google Analytics (#927)

### Improved
- Select moo_ or j_ template to reload gallery on ajax update (#909)
- Added a tax-free label to the price dropdown (#971)
