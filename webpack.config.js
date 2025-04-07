const Encore = require('@terminal42/contao-build-tools');

module.exports = Encore('assets', false)
    .setOutputPath('public/')
    .setPublicPath('/bundles/isotopecore')

    .addEntry('backend', './assets/scripts/backend.js')
    .addEntry('isotope', './assets/scripts/isotope.js')

    .copyFiles({
        from: './assets/images',
        to: 'images/[path][name].[ext]',
    })

    .copyFiles({
        from: './assets/plugins',
        to: 'plugins/[path][name].[ext]',
    })

    .getWebpackConfig()
;
