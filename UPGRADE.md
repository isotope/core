

## Version 3.0

- Dropped all first/last/even/odd/row_ etc. classes and list module column settings.
- The Google Analytics integration has been removed.
- The ElevateZoom gallery has been removed.
- The option to add watermarks to gallery images has been removed.
- The DHL Business shipping method has been removed.
- All third-party payment methods have been removed.
- The TrustedShop badge front end module has been removed.
- The favorites feature has been extracted to a commercial extension (see https://extensions.terminal42.ch/p/isotope-favorites).
- The wishlist feature has been removed.
- The FineUploader attribute has been removed.
- The PostSale entry point has been removed. Use your own controller or preferably the symfony/webhook component.
- The `@IsotopeHook` annotation has been replaced by `#[AsIsotopeHook]` attribute.
- The abstract ContentElement class loading Isotope JavaScript & CSS has been removed.
- Removed the QuantitySurcharge, BasePrice, PageTree, Price, PriceTiers, ShippingPrice, Upload and Weigth attributes.
- Downloads, FileTree, Media attributes have been extracted to a commercial extension (see https://extensions.terminal42.ch/p/isotope-file-attribute).
- The "product of current article" scope has been removed from product list category settings
- The option to mark and filter new products has been removed
- Custom SQL queries for product lists has been removed
- The range product list filter module has been removed
- The cumulative product list filter module has been removed 
- The product variant list has been removed
- The "legacy" price display in shop config has been removed
- The automatic currency converter for shop configs has been removed
- Frontend modules have been re-implemented as content elements
- All templates are rewritten using the Twig template engine
