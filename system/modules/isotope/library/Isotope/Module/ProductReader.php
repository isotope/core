<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2012 Isotope eCommerce Workgroup
 *
 * @package    Isotope
 * @link       http://www.isotopeecommerce.com
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 */

namespace Isotope\Module;

use Isotope\Interfaces\IsotopeProduct;
use Isotope\Model\Product;


/**
 * Class ProductReader
 *
 * Front end module Isotope "product reader".
 * @copyright  Isotope eCommerce Workgroup 2009-2012
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
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
    protected $objProduct = null;


    /**
     * Display a wildcard in the back end
     * @return string
     */
    public function generate()
    {
        if (TL_MODE == 'BE')
        {
            $objTemplate = new \BackendTemplate('be_wildcard');

            $objTemplate->wildcard = '### ISOTOPE ECOMMERCE: PRODUCT READER ###';

            $objTemplate->title = $this->headline;
            $objTemplate->id = $this->id;
            $objTemplate->link = $this->name;
            $objTemplate->href = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id=' . $this->id;

            return $objTemplate->parse();
        }

        // Return if no product has been specified
        if (\Isotope\Frontend::getAutoItem('product') == '')
        {
            return '';
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

        $objProduct = Product::findAvailableByIdOrAlias(\Isotope\Frontend::getAutoItem('product'));

        if (null === $objProduct) {
            // Display a 404 page
            if ($this->iso_display404Page) {
                $objHandler = new $GLOBALS['TL_PTY']['error_404']();
                $objHandler->generate($objPage->id);
                exit;
            } else {
                // Do not index or cache the page
                $objPage->noSearch = 1;
                $objPage->cache = 0;

                $this->Template = new \Isotope\Template('mod_message');
                $this->Template->type = 'empty';
                $this->Template->message = $GLOBALS['TL_LANG']['MSC']['invalidProductInformation'];

                return;
            }
        }

        $arrConfig = array(
            'module'        => $this,
            'template'      => ($this->iso_reader_layout ?: $objProduct->getRelated('type')->reader_template),
            'gallery'       => ($this->iso_gallery ?: $objProduct->getRelated('type')->reader_gallery),
            'buttons'       => deserialize($this->iso_buttons, true),
            'useQuantity'   => $this->iso_use_quantity,
            'jumpTo'        => ($objIsotopeListPage ?: $objPage),
        );

        if (\Environment::get('isAjaxRequest') && \Input::post('AJAX_MODULE') == $this->id && \Input::post('AJAX_PRODUCT') == $objProduct->getProductId()) {
            \Isotope\Frontend::ajaxResponse($objProduct->generate($arrConfig));
        }

        $this->Template->product = $objProduct->generate($arrConfig);
        $this->Template->product_id = ($objProduct->cssID[0] != '') ? ' id="' . $objProduct->cssID[0] . '"' : '';
        $this->Template->product_class = trim('product ' . ($objProduct->isNew() ? 'new ' : '') . $objProduct->cssID[1]);
        $this->Template->referer = 'javascript:history.go(-1)';
        $this->Template->back = $GLOBALS['TL_LANG']['MSC']['goBack'];

        $this->addMetaTags($objProduct);
        $this->addCanonicalProductUrls($objProduct);
    }

    /**
     * Add meta header fields to the current page
     * @param   IsotopeProduct
     */
    protected function addMetaTags(IsotopeProduct $objProduct)
    {
        global $objPage;

        $objPage->pageTitle = $this->prepareMetaDescription($objProduct->meta_title ?: $objProduct->name);
        $objPage->description = $this->prepareMetaDescription($objProduct->meta_description ?: ($objProduct->teaser ?: $objProduct->description));

        if ($objProduct->meta_keywords) {
            $GLOBALS['TL_KEYWORDS'] .= ($GLOBALS['TL_KEYWORDS'] != '' ? ', ' : '') . $objProduct->meta_keywords;
        }
    }

    /**
     * Adds canonical product URLs to the document
     * @param   IsotopeProduct
     */
    protected function addCanonicalProductUrls(IsotopeProduct $objProduct)
    {
        global $objPage;
        $arrPageIds = \Database::getInstance()->getChildRecords($objPage->rootId, \PageModel::getTable());
        $arrPageIds[] = $objPage->rootId;

        // Find the categories in the current root
        $arrCategories = array_intersect($objProduct->getCategories(), $arrPageIds);

        foreach ($arrCategories as $intPage) {

            // Do not use the index page as canonical link
            if ($objPage->alias == 'index' && count($arrCategories) > 1) {
                continue;
            }

            // Current page is the primary one, do not generate canonical link
            if ($intPage == $objPage->id) {
                break;
            }

            if (($objJumpTo = \PageModel::findWithDetails($intPage)) !== null) {

                $strDomain = \Environment::get('base');

                // Overwrite the domain
                if ($objJumpTo->dns != '') {
                    $strDomain = ($objJumpTo->useSSL ? 'https://' : 'http://') . $objJumpTo->dns . TL_PATH . '/';
                }

                $GLOBALS['TL_HEAD'][] = sprintf('<link rel="canonical" href="%s">', $strDomain . $objProduct->generateUrl($objJumpTo));

                break;
            }
        }
    }
}
