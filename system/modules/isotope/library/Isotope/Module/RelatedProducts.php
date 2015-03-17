<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2014 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @package    Isotope
 * @link       http://isotopeecommerce.org
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Module;

use Haste\Input\Input;
use Isotope\Model\Product;
use Isotope\Model\RelatedProduct;

/**
 * Class ModuleIsotopeRelatedProducts
 *
 * List products related to the current product reader.
 * @copyright  Isotope eCommerce Workgroup 2009-2012
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
 */
class RelatedProducts extends ProductList
{

    /**
     * Do not cache related products cause the list is different depending on URL parameters
     * @var boolean
     */
    protected $blnCacheProducts = false;


    /**
     * Generate the module
     * @return string
     */
    public function generate()
    {
        if (TL_MODE == 'BE') {
            $objTemplate = new \BackendTemplate('be_wildcard');

            $objTemplate->wildcard = '### ISOTOPE ECOMMERCE: RELATED PRODUCTS ###';
            $objTemplate->title    = $this->headline;
            $objTemplate->id       = $this->id;
            $objTemplate->link     = $this->name;
            $objTemplate->href     = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id=' . $this->id;

            return $objTemplate->parse();
        }

        if (!Input::getAutoItem('product', false, true)) {
            return '';
        }

        $this->iso_related_categories = deserialize($this->iso_related_categories);

        if (!is_array($this->iso_related_categories) || !count($this->iso_related_categories)) { // Can't use empty() because its an object property (using __get)
            return '';
        }

        // Prevent hiding the list which is not supported in this module (see ProductList::generate())
        $this->iso_hide_list = false;

        return parent::generate();
    }


    /**
     * Find all products we need to list.
     * @param   array|null
     * @return  array
     */
    protected function findProducts($arrCacheIds = null)
    {
        $arrIds = array(0);

        $objProduct = Product::findAvailableByIdOrAlias(Input::getAutoItem('product'));

        if (null === $objProduct) {
            return array();
        }

        $objRelated = RelatedProduct::findByProductAndCategories($objProduct, $this->iso_related_categories);

        if (null !== $objRelated) {
            while ($objRelated->next()) {
                $ids = trimsplit(',', $objRelated->products);

                if (!empty($ids) && is_array($ids)) {
                    $arrIds = array_unique(array_merge($arrIds, $ids));
                }
            }
        }

        $objProducts = Product::findAvailableByIds($arrIds, array(
            'order' => \Database::getInstance()->findInSet(Product::getTable().'.id', $arrIds)
        ));
        
        return (null === $objProducts) ? array() : $objProducts->getModels();
    }

    /**
     * {@inheritdoc}
     */
    protected function compileEmptyMessage($disableSearchIndex = true)
    {
        parent::compileEmptyMessage(false);
    }
}
