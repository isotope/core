Isotope eCommerce Changelog
===========================

Version 2.1.1-stable (2014-??-??)
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
