Isotope eCommerce Changelog
===========================

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
