

## Version 3.0

- Dropped all first/last/even/odd/row_ etc. classes and list module column settings
- The Google Analytics integration has been removed
- The ElevateZoom gallery has been removed
- The DHL Business shipping method has been removed
- The `/system/modules/isotope/postsale.php` entry point has been removed. Use `/_isotope/postsale/{mod}/{id}` instead.
- The `@IsotopeHook` annotation has been replaced by `#[AsIsotopeHook]` attribute
- The abstract ContentElement class loading Isotope JavaScript & CSS has been removed
