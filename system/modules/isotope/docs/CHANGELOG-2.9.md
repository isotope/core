Isotope eCommerce Changelog
===========================


Version 2.9.2 (2024-10-23)
--------------------------

- Added config for URLs in sitemap (#2512)
- Fixed cannonical URL in product reader with query attributes (#2512)
- Fixed error handling in MediaManager (#2468)
- Fixed missing translation in coupon module (#2539)
- Fixed invalid HTML in reports (#2538)


Version 2.9.1 (2024-09-06)
--------------------------

- Fixed return path from hook for downloads not used (#2534)
- Fixed member country limit in front end (#2535)


Version 2.9.0 (2024-07-30)
--------------------------

- Raised minimum dependency to Contao 4.13 and PHP 7.4
- Added product hit counter
- Added PayPal Checkout payment method
- Make favorites feature available for guests
- Added option to remove product from favorites when added to cart
- Added insert tags for favorites collection
- Added default sorting field config when cumulative filter is applied
- Added filter for product or variant to product collection view
- Added hook to modify if shipping or payment method is available
- Added hook to determine availability of rules
- Added support for text field filtering
- Allow getGallery to use different Isotope Galleries (#2461)
- Added support for filter modules as fragments
- Trigger the save_callback for address fields (#2438)
- Added support for dcawizard v3
- Extend isotope dashboard / overview page (#2482)
- Added customer turnover report (#2416)
- Added member registrations report (#2492)
- Migrated some reports to apexcharts library (#2483)
- Added option to show skipped checkout steps
- Added option to render order details in history module
- Use slugger to generate attribute field names (#2519)
- Implement payment backend interface for Saferpay (#2526)
- Replace insert tags on order notification tokens
- Updated subdivisions of Ireland (#2529)
- Fixed incorrect download expiration calculation
- Fixed tax free price calculation if price display is set to fixed (#2531)
