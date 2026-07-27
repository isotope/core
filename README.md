# Isotope eCommerce

Isotope is an eCommerce platform built for the Contao Open Source CMS platform and takes advantage of all it has to offer.
It is built for flexibility, extendability and is international-friendly, much like Contao itself. 

Visit [www.isotopeecommerce.org][website] to learn more.


## Installation

Choose the installation method that matches your workflow!

### Installation via Contao Manager

Search for `isotope/isotope-core` in the Contao Manager and add it
to your installation. Apply changes to update the packages.

### Manual installation

Add a composer dependency for this bundle. Therefore, change in the project root and run the following:

```bash
composer require isotope/isotope-core
```

Depending on your environment, the command can differ, i.e. starting with `php composer.phar …` if you do not have
composer installed globally.

Then, update the database via the `contao:migrate` command or the Contao install tool.


## License

This bundle is released under the [LGPL license](LICENSE)


[website]: https://www.isotopeecommerce.org 
