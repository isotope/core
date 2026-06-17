const Encore = require('@terminal42/contao-build-tools');

module.exports = Encore('assets')
    .setOutputPath('public/')
    .setPublicPath('/bundles/isotope')

    .copyFiles({
        from: './assets/images',
        to: 'images/[path][name].[ext]',
    })

    .getWebpackConfig()
;
