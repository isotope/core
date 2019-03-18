<?php

/*
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009 - 2019 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Module;

use Haste\Http\Response\HtmlResponse;
use Haste\Input\Input;
use Isotope\Interfaces\IsotopeProduct;
use Isotope\Model\Product;

/**
 * Class ProductReader
 *
 * @property bool   $iso_use_quantity
 * @property bool   $iso_display404Page
 * @property bool   $iso_addProductJumpTo
 * @property string $iso_reader_layout
 * @property int    $iso_gallery
 */
class ProductReader extends Module
{
    /**
     * Template
     * @var string
     */
    protected $strTemplate = 'mod_iso_productreader';

    /**
     * Product
     * @var IsotopeProduct
     */
    protected $objProduct;


    /**
     * Display a wildcard in the back end
     * @return string
     */
    public function generate()
    {
        if ('BE' === TL_MODE) {
            return $this->generateWildcard();
        }

        // Return if no product has been specified
        if (Input::getAutoItem('product', false, true) == '') {
            if ($this->iso_display404Page) {
                $this->generate404();
            } else {
                return '';
            }
        }

        return parent::generate();
    }


    /**
     * Generate module
     * @return void
     */
    protected function compile()
    {
        global $objPage;
        global $objIsotopeListPage;

        $objProduct = Product::findAvailableByIdOrAlias(Input::getAutoItem('product'));

        if (null === $objProduct) {
            $this->generate404();
        }

        $arrConfig = array(
            'module'      => $this,
            'template'    => $this->iso_reader_layout ? : $objProduct->getType()->reader_template,
            'gallery'     => $this->iso_gallery ? : $objProduct->getType()->reader_gallery,
            'buttons'     => $this->iso_buttons,
            'useQuantity' => $this->iso_use_quantity,
            'disableOptions' => $this->iso_disable_options,
            'jumpTo'      => $objIsotopeListPage ? : $objPage,
        );

        if (\Environment::get('isAjaxRequest')
            && \Input::post('AJAX_MODULE') == $this->id
            && \Input::post('AJAX_PRODUCT') == $objProduct->getProductId()
            && !$this->iso_disable_options
        ) {
            try {
                $objResponse = new HtmlResponse($objProduct->generate($arrConfig));
                $objResponse->send();
            } catch (\InvalidArgumentException $e) {
                return;
            }
        }

        $this->addMetaTags($objProduct);
        $this->addCanonicalProductUrls($objProduct);

        $this->Template->product       = $objProduct->generate($arrConfig);
        $this->Template->product_id    = $this->getCssId($objProduct);
        $this->Template->product_class = $this->getCssClass($objProduct);
        $this->Template->referer       = 'javascript:history.go(-1)';
        $this->Template->back          = $GLOBALS['TL_LANG']['MSC']['goBack'];
    }

    /**
     * Add meta header fields to the current page
     *
     * @param Product $objProduct
     */
    protected function addMetaTags(Product $objProduct)
    {
        global $objPage;

        $descriptionFallback = ($objProduct->teaser ?: $objProduct->description);

        $objPage->pageTitle   = $this->prepareMetaDescription($objProduct->meta_title ?: $objProduct->getName());
        $objPage->description = $this->prepareMetaDescription($objProduct->meta_description ?: $descriptionFallback);

        if ($objProduct->meta_keywords) {
            $GLOBALS['TL_KEYWORDS'] .= ($GLOBALS['TL_KEYWORDS'] != '' ? ', ' : '') . $objProduct->meta_keywords;
        }
    }

    /**
     * Adds canonical product URLs to the document
     *
     * @param Product $objProduct
     */
    protected function addCanonicalProductUrls(Product $objProduct)
    {
        global $objPage;
        $arrPageIds   = \Database::getInstance()->getChildRecords($objPage->rootId, \PageModel::getTable());
        $arrPageIds[] = $objPage->rootId;

        // Find the categories in the current root
        $arrCategories = array_intersect($objProduct->getCategories(), $arrPageIds);

        foreach ($arrCategories as $intPage) {

            // Do not use the index page as canonical link
            if ('index' === $objPage->alias && \count($arrCategories) > 1) {
                continue;
            }

            // Current page is the primary one, do not generate canonical link
            if ($intPage == $objPage->id) {
                break;
            }

            if (($objJumpTo = \PageModel::findPublishedById($intPage)) !== null) {

                $objJumpTo->loadDetails();
                $strDomain = \Environment::get('base');

                // Overwrite the domain
                if ($objJumpTo->dns != '') {
                    $strDomain = ($objJumpTo->useSSL ? 'https://' : 'http://') . $objJumpTo->dns . TL_PATH . '/';
                }

                $href = $strDomain . $objProduct->generateUrl($objJumpTo);
                $GLOBALS['TL_HEAD'][] = '<link rel="canonical" href="' . $href . '">';

                break;
            }
        }
    }

    /**
     * Gets the CSS ID for this product
     *
     * @param Product $objProduct
     *
     * @return string|null
     */
    protected function getCssId(Product $objProduct)
    {
        $css = deserialize($objProduct->cssID, true);

        return $css[0] ? ' id="' . $css[0] . '"' : null;
    }

    /**
     * Gets the CSS classes for this product
     *
     * @param Product $objProduct
     *
     * @return string
     */
    protected function getCssClass(Product $objProduct)
    {
        $classes = ['product'];

        if ($objProduct->isNew()) {
            $classes[] = 'new';
        }

        $arrCSS = deserialize($objProduct->cssID, true);
        if ('' !== (string) $arrCSS[1]) {
            $classes[] = (string) $arrCSS[1];
        }

        return implode(' ', $classes);
    }

    /**
     * Generates a 404 page and stops page output.
     */
    private function generate404()
    {
        global $objPage;
        /** @var \PageError404 $objHandler */
        $objHandler = new $GLOBALS['TL_PTY']['error_404']();
        $objHandler->generate($objPage->id);
        exit;
    }
}
