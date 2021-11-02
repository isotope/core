Isotope eCommerce Changelog
===========================

Version 2.7.4 (2021-11-02)
--------------------------

- Product SKU should not be mandatory in the database
- Added confirmation message when product is updated in the cart
- Improved product group permission check when listing products
- Make sure models are only created from the current class type
- Override language in request and translator (#2245)
- Do not match PayPal email case sensitive (#2260)
- Do not add messages to fragment templates (#2255)
- Fixed moving multiple products to groups (#2234)
- Fixed default value for document type (#2241)
- Fixed encoding issues in price format config (#2249)
- Fixed binary instead of OR operator in address
- Fixed editing multiple orders in the back end (#2235)


Version 2.7.3 (2021-07-06)
--------------------------

- Show order information in the details view
- Allow sendNotificationMessage hook in postsale script (#2231)
- Fixed open payment platform authentication
- Fixed missing tstamp for content element
- Improved type safety when generating product URL
- Fixed SQL definition for integer attributes
- Fixed product count did include number of categories in cumulative filter
- Fixed caching when migrating product collection log table
- Fixed mandatory file uploads in order conditions not working (#2228)


Version 2.7.2 (2021-05-25)
--------------------------

- Performance improvements by preloading product list data
- Fixed version conflict when editing multiple product prices (#2188)
- Fixed cannonical link to following category order (#2218)
- Fixed rootNodes config and label for pageTree attribute (#2225)
- Improved layout of order details in back end (#2223)
- Fixed order list view when using permissions (#2222)
- Removed system log error entry for missing tl_module.iso_notifications (#2214)
- Fixed warning if no options have been created yet (#2203)
- Dropped not-implemented reports (#2218)


Version 2.7.1 (2021-04-01)
--------------------------

- Rewrite the order log feature to allow "edit multiple" and custom views in orders (#2207)
- Fixed compatibility with Contao 4.11/Symfony 5 (#2198)
- Performance optimization in the backend product list (#2206)
- Fixed configuring checkout step forms (#2194)
- Fixed hiding the range filter in product detail view (#2192)
- Fixed generating order checkout forms if none is configured (#2205)
- Fixed labels for the new checkout tokens (#2208)


Version 2.7.0 (2021-02-15)
--------------------------

- Added product collection change log and status notifications
- Added SwissBilling payment method
- Added option to exclude payment method for certain product types (#2072)
- Added attribute to pick a page from the page tree
- Added option to limit back end users to only see orders of certain member groups
- Added options to calculate product shipping price by different rules (#2012)
- Added "active" class if a product list is placed on a reader page
- Added compatibility with DC_Multilingual v4 (#2164)
- Added group cart rule to apply only one of several rules in the cart
- Added insert tag for product price (#2160)
- Added configurable forms in every step of the checkout process
- Make attribute price label configurable through language files (#2097)
- Allow payment methods to return a response object
- Updated mPay24 API to include billing and shipping address (#2184)
- Fixed PayPal PLUS checkout iframe (#2145)
- Fixed possibly incorrect price in PayPal Standard when product has no name (#2176)
- Fix possible missing order date with PayPal PLUS checkout (#2165)
