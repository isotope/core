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

        $this->objProduct = \Isotope\Frontend::getProductByAlias(\Isotope\Frontend::getAutoItem('product'));

        if (!$this->objProduct)
        {
            // Do not index or cache the page
            $objPage->noSearch = 1;
            $objPage->cache = 0;

            $this->Template = new \Isotope\Template('mod_message');
            $this->Template->type = 'empty';
            $this->Template->message = $GLOBALS['TL_LANG']['MSC']['invalidProductInformation'];

            return;
        }

        $arrConfig = array(
            'module'        => $this,
            'template'      => ($this->iso_reader_layout ?: $this->objProduct->getRelated('type')->reader_template),
            'gallery'       => ($this->iso_gallery ?: $this->objProduct->getRelated('type')->reader_gallery),
            'buttons'       => deserialize($this->iso_buttons, true),
            'useQuantity'   => $this->iso_use_quantity,
            'jumpTo'        => ($objIsotopeListPage ?: $objPage),
        );

        if (\Environment::get('isAjaxRequest') && \Input::post('AJAX_MODULE') == $this->id && \Input::post('AJAX_PRODUCT') == $this->objProduct->id) {
            \Isotope\Frontend::ajaxResponse($this->objProduct->generate($arrConfig));
        }

        $this->Template->product = $this->objProduct->generate($arrConfig);
        $this->Template->product_id = ($this->objProduct->cssID[0] != '') ? ' id="' . $this->objProduct->cssID[0] . '"' : '';
        $this->Template->product_class = trim('product ' . ($this->objProduct->isNew() ? 'new ' : '') . $this->objProduct->cssID[1]);
        $this->Template->referer = 'javascript:history.go(-1)';
        $this->Template->back = $GLOBALS['TL_LANG']['MSC']['goBack'];

        $objPage->pageTitle = strip_insert_tags($this->objProduct->name);
        $objPage->description = $this->prepareMetaDescription($this->objProduct->description_meta);

        $GLOBALS['TL_KEYWORDS'] .= (strlen($GLOBALS['TL_KEYWORDS']) ? ', ' : '') . $this->objProduct->keywords_meta;

        $this->addCanonicalProductUrls();
    }

    /**
     * Adds canonical product URLs to the document
     */
    protected function addCanonicalProductUrls()
    {
        // Only get pages of current root
        global $objPage;
        $arrPages = \Database::getInstance()->getChildRecords($objPage->rootId, 'tl_page');
        $arrPages[] = $objPage->rootId;

        $arrCanonicalCategories = array_intersect($this->objProduct->getCategories(), $arrPages);

        foreach ($arrCanonicalCategories as $intPage) {
            // The current page is not a canonical category
            if ($intPage !== $objPage->id && ($objJumpTo = \PageModel::findByPk($intPage)) !== null) {
                $GLOBALS['TL_HEAD'][] = sprintf('<link rel="canonical" href="%s">', $this->objProduct->generateUrl($objJumpTo));
            }
        }
    }
}
